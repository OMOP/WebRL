<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    21 October 2010
 
    Page controller for /budget page.
    Handles all user interaction within /budget page.
 
    �2009-2010 Foundation for the National Institutes of Health (FNIH)
 
    Licensed under the Apache License, Version 2.0 (the "License"); you may not
    use this file except in compliance with the License. You may obtain a copy
    of the License at http://omop.fnih.org/publiclicense.
 
    Unless required by applicable law or agreed to in writing, software
    distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
    WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. Any
    redistributions of this work or any derivative work or modification based on
    this work should be accompanied by the following source attribution: "This
    work is based on work by the Observational Medical Outcomes Partnership
    (OMOP) and used under license from the FNIH at
    http://omop.fnih.org/publiclicense.
 
    Any scientific publication that is based on this work should include a
    reference to http://omop.fnih.org.
 
================================================================================*/
require_once("OMOP/WebRL/MailManager.php");
require_once("OMOP/WebRL/OrganizationManager.php");

class BudgetController extends PageController
{
    private $DATE_FORMAT_SQL = 'Y-m-d';
    private $DATE_FORMAT_JAVASCRIPT = 'm/d/Y';

    private $PREFIX_ORGANIZATION = 'oid';

    private $organizations;
    private $months;

    public function  __construct() {
        parent::__construct();
        $this->organizations = array();
        $this->organizations['totals'] = array();

        $this->months = array();
    }

    protected function processCore($page, $action, $parameters)
    {
        global $configurationManager;

        $this->view->assign('mode', $action);
        $this->view->assign('mode_type', $parameters['report_type']);
        $this->view->assign('page', $parameters['page']);
        $this->view->assign('PREFIX_ORGANIZATION', $this->PREFIX_ORGANIZATION);

        switch($action)
        {
            case 'show':
                $this->view->assign('date_start', $parameters['date_start']);
                $this->view->assign('date_end', $parameters['date_end']);

                $date_ranges = $this->getDateRanges($parameters['date_start'], $parameters['date_end']);
                $date_ranges_text = array();
                foreach ($date_ranges as $key=>$range) {
                    if (!is_array($range)) {
                        break;
                    }
                    $date_ranges_text[$key] = array(
                        date($this->DATE_FORMAT_SQL . ' 00:00:00' ,$range[0]),
                        date($this->DATE_FORMAT_SQL . ' 23:59:59' ,$range[1])
                    );
                }

                foreach ($date_ranges_text as $key=>$range) {
                    $budget_month = $this->model->get_budget($range[0], $range[1]);
                    foreach ($budget_month as $data) {
                        /**
                         * This conditional block should be made server-side by the DB engine,
                         * actually, but this does not work on my computer:
                         * COALESCE(o.organization_id,0), COALESCE(o.organization_name,'[other]')
                         */
                        if (is_null($data['organization_id'])) {
                            $data['organization_id'] = '0';
                            $data['organization_name'] = '[not in Org]';
                        }
                        $data['organization_id'] = $this->PREFIX_ORGANIZATION . $data['organization_id'];
                        $this->push_monthly_statistics($data, $date_ranges[$key][0]);
                    }
                }
                foreach ($this->organizations as $o_id=>$organization) {
                    if (!is_array($organization['users'])) {
                        continue;
                    }
					if ($o_id != 'oidsystem') {
						$this->push_general_statistics('organization', $o_id, $this->model->get_budget_statistics('organization', substr($o_id, strlen($this->PREFIX_ORGANIZATION))));
						foreach ($organization['users'] as $u_id=>$user) {
							$this->push_general_statistics('user', $u_id, $this->model->get_budget_statistics('user', $u_id));
							$this->push_active_instances('user', $u_id, $this->model->get_budget_current('user', $u_id));
							$this->push_budget_remaining('user', $u_id, $this->model->get_budget_remaining('user', $u_id));
						}
					}
					else
						foreach ($organization['users'] as $u_id => $user) {
							$this->push_system_instance($o_id, $u_id);
						}
                    /**
                     * Sort users in the org alphabetically
                     */

					/*BUG: AK: I comment this block because it caused compile time issues. Whole site is down. 
					Please check syntax with version of PHP which used on DEV.  5.2.9-2.fc10
                    Fixed: AS: won't pass the functions as parameters anymore */
                    uasort(
                        $this->organizations[$o_id]['users'],
                        array("self", "cmp_user_names")
                        /*function ($user1, $user2) {
                            return strcasecmp($user1['name'], $user2['name']);
                        }*/
                    );
                }
                $this->view->assign('organizations', $this->organizations);
                $this->view->assign('months', $this->months);

                switch ($parameters['report_type']) {
                    case 'table':
                        //get_configuration
                        /*$date_formats = array("%m-%d-%Y %r" => "mm-dd-yyyy",
                            "%m/%d/%Y %r" => "mm/dd/yyyy",
                            "%Y-%m-%d %r" => "yyyy-mm-dd",
                            "%d-%b-%Y %r" => "dd-MMM-yyyy");
                         */
                        $site_configuration = $this->model->get_configuration();

                        /**
                         * If the system date format contains time - remove it by removing all after space character
                         */
                        $i = strpos($site_configuration->default_date_format, ' ');
                        if (false !== $i) {
                            $site_configuration->default_date_format = substr($site_configuration->default_date_format, 0, $i);
                        }

                        $tpl_monthes = array();
                        foreach ($this->months as $m) {
                            $month_start = mktime(0,0,0, date('m',$m), 1, date('Y',$m));
                            $month_end = mktime(0,0,0, date('m',$m), date('t',$m), date('Y',$m));
                            $tpl_monthes[] = strftime($site_configuration->default_date_format, $month_start) . ' - ' .
                                strftime($site_configuration->default_date_format, $month_end);
                        }
                        $i = sizeof($this->months);
                        if (1 == $i)
                        {
                            //$tpl_monthes[0] = $parameters['date_start'] . ' - ' . $parameters['date_end'];
                            $month_parts = explode('/', $parameters['date_start']);
                            $month_start = mktime(0,0,0, $month_parts[0], $month_parts[1], $month_parts[2]);
                            $month_parts = explode('/', $parameters['date_end']);
                            $month_end   = mktime(0,0,0, $month_parts[0], $month_parts[1], $month_parts[2]);
                            $tpl_monthes[0] = strftime($site_configuration->default_date_format, $month_start) . ' - ' .
                                strftime($site_configuration->default_date_format, $month_end);
                        }
                        else
                        {
                            $m = $this->months[0];
                            $month_parts = explode('/', $parameters['date_start']);
                            $month_start = strftime($site_configuration->default_date_format,
                                mktime(0,0,0, '10', 3, '2010')
                                );
                            $tpl_monthes[0] = $month_start . ' - ' .
                                strftime($site_configuration->default_date_format,
                                    mktime(0,0,0, date('m',$m), 1, date('Y',$m)));

                            $m = $this->months[$i - 1];
                            $month_parts = explode('/', $parameters['date_end']);
                            $month_end   = strftime($site_configuration->default_date_format,
                                mktime(0,0,0, $month_parts[0], $month_parts[1], $month_parts[2]));
                            $tpl_monthes[$i - 1] =
                                strftime($site_configuration->default_date_format,
                                    mktime(0,0,0, date('m',$m), $month_parts[1], date('Y',$m))) .
                                ' - ' .
                                strftime($site_configuration->default_date_format,
                                    mktime(0,0,0, date('m',$m), $month_parts[1], date('Y',$m)));
                        }
                        $this->view->assign('tpl_months', $tpl_monthes);
                        break;

                    case 'graph':
                        $tmp_ticks = array();
                        $i = 0;
                        foreach ($this->months as $m) {
                            ++$i;
                            $tmp_ticks[] = "[".$i.", '".date('M, Y',$m)."']";
                        }
                        $js_graph_options = "
var graph_options = {
        series: {stack: 1,
                 lines: {show: false, steps: false},
                 bars: {show: true, barWidth: 0.2, align: 'center',},},
        xaxis: {
			ticks: [".implode(', ', $tmp_ticks)."],
			tickSize: 3
		},
		yaxis: {
		    //max: 400
            tickFormatter: function (val) {
	val += '';
	x = val.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1;
}

		},

		legend: {
    show: true,
	noColumns: 10
  }
};";

                        $companies_data = array();
                        $i = 0;
                        foreach ($this->organizations as $o_id=>$organization) {
                            if ($o_id == 'totals') {
                                continue;
                            }
                            $i_ = $i . '';
                            $monthly_data = array();
                            $j = 1;
                            foreach ($this->months as $m) {
                                $monthly_data[] = '[step*'.$j.' + '.$i_.'*bar_width, '.$this->organizations[$o_id] ['totals'] [$m] ['charge'].']';
                                ++$j;
                            }
                            
                            $companies_data[] = "{label: '".$organization['name']."', data: [".implode(', ', $monthly_data)."]},";
                            ++$i;
                        }

                        $js_graph_data = "
var bar_width = 0.2;
var step = ".sizeof($companies_data)." < 5 ? 1 : (amount + 1) * bar_width;
//var idx0 = 0; var idx1 = 1; var idx2 = 2;
var graph_data = [
    ".implode("\n", $companies_data)."
];
";
                        $this->view->assign('graph_options', $js_graph_options);
                        $this->view->assign('graph_data', $js_graph_data);
                        break;
                }
                break;

            default:
                $this->view->assign('date_start', date($this->DATE_FORMAT_JAVASCRIPT, mktime(0,0,0, date('m'),1,date('Y'))));
                $this->view->assign('date_end', date($this->DATE_FORMAT_JAVASCRIPT));
                break;
        }
		return true;
	}

    private function push_active_instances($type, $id, $data) {
        switch (strtolower($type))
        {
            case 'user':
                if(empty($data)) {
                    return;
                }
                if (is_null($data['organization_id'])) {
                    $data['organization_id'] = '0';
                }
                $data['organization_id'] = $this->PREFIX_ORGANIZATION . $data['organization_id'];

                /**
                 * Check user existance
                 */
                if (!isset ($this->organizations[$data['organization_id']] ['users'] [$id] ['current'])) {
                    $this->organizations[$data['organization_id']] ['users'] [$id] ['current'] = array(
                        'instances' => 0,
                    );
                }

                /**
                 * Check organizations statistics records
                 */
                if (!isset ($this->organizations['totals'] ['current']))
                {
                    $this->organizations['totals'] ['current'] = array(
                        'instances' => 0,
                    );
                }
                elseif (!isset ($this->organizations['totals'] ['current'] ['instances']))
                {
                    $this->organizations['totals'] ['current'] ['instances'] =  0;
                }

                if (!isset ($this->organizations [$data['organization_id']] ['totals'] ['current']))
                {
                    $this->organizations [$data['organization_id']] ['totals'] ['current'] = array(
                        'instances' => 0,
                    );
                }
                elseif (!isset ($this->organizations [$data['organization_id']] ['totals'] ['current'] ['instances']))
                {
                    $this->organizations [$data['organization_id']] ['totals'] ['current'] ['instances'] = 0;
                }

                $this->organizations[$data['organization_id']] ['users'] [$id] ['current'] ['instances'] = $data['instances'];
                $this->organizations [$data['organization_id']] ['totals'] ['current'] ['instances'] += $data['instances'];
                $this->organizations['totals'] ['current'] ['instances'] += $data['instances'];
                break;
            case 'organization':
                break;
        }
    }

    private function push_budget_remaining($type, $id, $data) {
        switch (strtolower($type))
        {
            case 'user':
                /**
                 * $row['user_id']
                 * $row['organization_id']
                 * $row['limit']
                 * $row['charge']
                 */
                if(empty($data)) {
                    return;
                }
                if (is_null($data['organization_id'])) {
                    $data['organization_id'] = '0';
                }
                $data['organization_id'] = $this->PREFIX_ORGANIZATION . $data['organization_id'];

                /**
                 * Check user existance
                 */
                if (!isset ($this->organizations[$data['organization_id']] ['users'] [$id] ['current'])) {
                    $this->organizations[$data['organization_id']] ['users'] [$id] ['current'] = array(
                        'remaining' => 0,
                    );
                }

                /**
                 * Check organizations statistics records
                 */
                if (!isset ($this->organizations['totals'] ['current']))
                {
                    $this->organizations['totals'] ['current'] = array(
                        'remaining' => 0,
                    );
                }
                elseif (!isset ($this->organizations['totals'] ['current'] ['remaining']))
                {
                    $this->organizations['totals'] ['current'] ['remaining'] =  0;
                }

                if (!isset ($this->organizations [$data['organization_id']] ['totals'] ['current']))
                {
                    $this->organizations [$data['organization_id']] ['totals'] ['current'] = array(
                        'remaining' => 0,
                    );
                }
                elseif (!isset ($this->organizations [$data['organization_id']] ['totals'] ['current'] ['remaining']))
                {
                    $this->organizations [$data['organization_id']] ['totals'] ['current'] ['remaining'] = 0;
                }

                $this->organizations[$data['organization_id']] ['users'] [$id] ['current'] ['remaining'] = $data['limit'] - $data['charge'];
                if ($this->PREFIX_ORGANIZATION.'0' == $data['organization_id']) {
                    $this->organizations [$data['organization_id']] ['totals'] ['current'] ['remaining'] += ($data['limit'] - $data['charge']);
                    $this->organizations ['totals'] ['current'] ['remaining'] += ($data['limit'] - $data['charge']);
                }
                else
                {
                    $this->organizations [$data['organization_id']] ['totals'] ['current'] ['remaining'] -= $data['charge'];
                    $this->organizations ['totals'] ['current'] ['remaining'] -= $data['charge'];
                }
                
                break;
        }
    }

    private function push_general_statistics($type, $id, $data) {
        switch (strtolower($type))
        {
            case 'user':
                if (is_null($data['organization_id'])) {
                    $data['organization_id'] = '0';
                }
                $data['organization_id'] = $this->PREFIX_ORGANIZATION . $data['organization_id'];

                $this->organizations[$data['organization_id']] ['users'] [$id] ['budget'] = array(
                    'instances' => $data['instances'], //num_instances
                    'limit' => $data['limit'], //user_money
                );
                if (!isset ($this->organizations['totals'] ['budget']))
                {
                    $this->organizations['totals'] ['budget'] = array(
                        'instances' => 0,
                        'limit' => 0,
                    );
                }
                $this->organizations['totals'] ['budget'] ['instances'] += $data['instances'];

                if ($data['organization_id'] == $this->PREFIX_ORGANIZATION . '0') {
                    if (!isset ($this->organizations [$data['organization_id']] ['totals'] ['budget']))
                    {
                        $this->organizations [$data['organization_id']] ['totals'] ['budget'] = array(
                            'instances' => 0,
                            'limit' => 0,
                        );
                    }
                    $this->organizations [$data['organization_id']] ['totals'] ['budget'] ['instances'] += $data['instances'];
                    $this->organizations [$data['organization_id']] ['totals'] ['budget'] ['limit']     += $data['limit'];
                }
                break;
            case 'organization':
                if (!isset ($this->organizations [$id] ['totals'] ['budget']))
                {
                    $this->organizations [$id] ['totals'] ['budget'] = array(
                        'instances' => 0,
                        'limit' => 0,
                    );
                }
                if (!isset ($this->organizations ['totals'] ['budget']))
                {
                    $this->organizations ['totals'] ['budget'] = array(
                        'instances' => 0,
                        'limit' => 0,
                    );
                }
                if (!isset ($this->organizations [$id] ['totals'] ['current']))
                {
                    $this->organizations [$id] ['totals'] ['current'] = array(
                        'instances' => 0,
                        'remaining' => 0,
                    );
                }
                if (!isset ($this->organizations['totals'] ['current']))
                {
                    $this->organizations['totals'] ['current'] = array(
                        'instances' => 0,
                        'remaining' => 0,
                    );
                }
                $this->organizations [$id] ['totals'] ['budget'] ['instances'] = $data['instances'];
                $this->organizations [$id] ['totals'] ['budget'] ['limit'] = $data['limit'];
                $this->organizations ['totals'] ['budget'] ['limit'] += $data['limit'];
                $this->organizations [$id] ['totals'] ['current']['remaining'] += $data['limit'];
                $this->organizations ['totals'] ['current']['remaining'] += $data['limit'];
                break;
        }
    }

    private function push_monthly_statistics($data, $month) {
        $month_key = mktime(0,0,0, date('m',$month),1,date('Y',$month)); //$data['m'] . '.' . $data['y'];
        if (!in_array($month_key, $this->months)) {
            $this->months[] = $month_key;
        }

        /**
         * Add new organization to the list
         */
        if (!isset ($this->organizations[$data['organization_id']]))
        {
            $o = array();
            $o['name'] = $data['organization_name'];
            $o['totals'] = array();
            $o['users'] = array();
            $this->organizations[$data['organization_id']] = $o;
        }

        /**
         * Insert not existing monthly statistics to the organization array
         */
        if (!isset ($this->organizations['totals'] [$month_key]))
        {
            $this->organizations['totals'] [$month_key] = array(
                'instances' => 0,
                'charge' => 0,
            );
        }
        if (!isset ($this->organizations[$data['organization_id']] ['totals'] [$month_key]))
        {
            $this->organizations[$data['organization_id']] ['totals'] [$month_key] = array(
                'instances' => 0,
                'charge' => 0,
            );
        }
        
        /**
         * Add new user to the organization
         */
        if (!isset ($this->organizations[$data['organization_id']] ['users'] [$data['user_id']])) {
            $this->organizations[$data['organization_id']] ['users'] [$data['user_id']] = array();
            $this->organizations[$data['organization_id']] ['users'] [$data['user_id']] ['name'] = $data['user_name'];
        }
        /**
         * Push user month data
         */
        $this->organizations[$data['organization_id']] ['users'] [$data['user_id']] [$month_key] = array(
            'instances' => $data['inst_amount'],
            'charge' => $data['charge'],
        );
        $this->organizations['totals'] [$month_key] ['instances'] += intval($data['inst_amount']);
        $this->organizations['totals'] [$month_key] ['charge']    += floatval($data['charge']);
        $this->organizations[$data['organization_id']] ['totals'] [$month_key] ['instances'] += floatval($data['inst_amount']);
        $this->organizations[$data['organization_id']] ['totals'] [$month_key] ['charge']    += floatval($data['charge']);
    }

	function display($organization_id)
	{
		$back_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : PageRouter::build('user_view');
		$_SESSION['back_url'] = $back_url;
		/*
		$manager = new OrganizationManager(DbManager::$db);
        $organization = $manager->get($organization_id);
		$this->view->assign("org", $organization);		
		*/
	}

    /**
     * Returns the array of months corresponding to the time range between
     * the $date_start and $date_end
     *
     * NOTE: date format is hardcoded
     * define (DATE_FORMAT, 'm/d/Y');
     * define (DATE_FORMAT_JAVASCRIPT, 'mm/dd/yyyy');
     *
     * @param String $date_start formatted as mm/dd/yyyy
     * @param String $date_end formatted as mm/dd/yyyy
     * @return array of months; each array element is {first month day, last month day}
     */
    private function getDateRanges($date_start, $date_end)
    {
        /**
         * Init the vars
         */
        $output = array();

        /**
         * Parse input data to pieces
         */
        $a1 = explode('/', $date_start, 3);
        $a2 = explode('/', $date_end, 3);
        /*
        if($j1<1)
        {
            throw new Exception("Unsupported mode");
        }
        */

        /**
         * Assign start and end month
         */
        $r1 = mktime(0,0,0, $a1[0], $a1[1], $a1[2]);
        $r2 = mktime(0,0,0, $a2[0], $a2[1], $a2[2]);

        if ($r1 === false || $r2 === false) {
            return $output;
        }

        if ($r1 > $r2) {
            $rx = $r1; $ax = $a1;$this->organizations[$o_id] ['users'] [$u_id] ['current'] = array();
		$this->organizations[$o_id] ['users'] [$u_id] ['current'] ['instances'] = 1;
		if (! isset($this->organizations[$o_id] ['totals'])) {
			$this->organizations[$o_id] ['totals'] = array();
			$this->organizations[$o_id] ['totals'] ['current'] = array();
			$this->organizations[$o_id] ['totals'] ['current'] ['instances'] = 0;
		}
		$this->organizations[$o_id] ['totals'] ['current'] ['instances'] += 1;

            $r1 = $r2; $a1 = $a2;
            $r2 = $rx; $a2 = $ax;
        }

        /**
         * If dates are within the same month, just return them.
         * If not, push the first range to the output array.
         */
        if ($a1[0] == $a2[0] && $a1[2] == $a2[2]) {
            array_push($output, array($r1, $r2));
            return $output;
        }
        else {
            $r2 = mktime(23,59,59, $a1[0], date('t', $r1), $a1[2]);
            array_push($output, array($r1, $r2));
        }

        $m = $a1[0];
        $y = $a1[2];

        /**
         * Assign output array
         */
        $limit = 20;
        while ($limit--)
        {
            $m++;
            if ($m == 13) {
                $m = 1;
                $y++;
            }
            $r1 = mktime(0,0,0, $m, 1, $y);
            if ($m == $a2[0] && $y == $a2[2]) {
                $r2 = mktime(23,59,59, $m, $a2[1], $y);
                array_push($output, array($r1, $r2));
                break;
            }
            else {
                $r2 = mktime(23,59,59, $m, date('t', $r1), $y);
                array_push($output, array($r1, $r2));
            }
        }

        return $output;
    }

	/**
	 * Function adds active instances values for system instances
	 * @param organization id
	 * @param system instance id
	 */
	private function push_system_instance($o_id, $u_id){
		$this->organizations[$o_id] ['users'] [$u_id] ['current'] = array();
		$this->organizations[$o_id] ['users'] [$u_id] ['current'] ['instances'] = 1;
		if (! isset($this->organizations[$o_id] ['totals'])) {
			$this->organizations[$o_id] ['totals'] = array();
			$this->organizations[$o_id] ['totals'] ['current'] = array();
			$this->organizations[$o_id] ['totals'] ['current'] ['instances'] = 0;
		}
		$this->organizations[$o_id] ['totals'] ['current'] ['instances'] += 1;
	}

    /**
     * Function sorts two elements of array by their values with the 'name' keys
     * @param array first value to compare
     * @param array second value to compare
     * @return integer compared value (1, -1)
     */
    public static function cmp_user_names ($user1, $user2) {
        return strcasecmp($user1['name'], $user2['name']);
    }
}

?>