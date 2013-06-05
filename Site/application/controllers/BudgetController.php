<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2010
 
    Controller for Run Result Logs pages.
 
    (c)2009-2011 Foundation for the National Institutes of Health (FNIH)
 
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

class BudgetController extends Zend_Controller_Action
{
    /**
     * Budget model
     * @var Application_Model_Budget
     */
    private $_model;

    const MODE_TABLE = 'table';
    const MODE_GRAPH = 'graph';

    public function init()
    {
        if (Zend_Registry::isRegistered('logger')) {
            $this->_logger = Zend_Registry::get('logger');
        } else {
            $this->_logger = new Zend_Log(new Zend_Log_Writer_Null());
        }

        $this->_model = new Application_Model_Budget;

        $this->view->formAction = '/public/budget';
    }
    
    public function indexAction()
    {
        $request = $this->getRequest();
        $mode = $request->getParam('report_type', Application_Model_Budget::PAGE_MODE_BLANK);
        
        $this->view->page = intval($request->getParam('page'));

        // Finish initialization for blank page
        if (Application_Model_Budget::PAGE_MODE_BLANK == $mode) {
            $this->view->dateStart = date(Application_Model_Budget::DATE_FORMAT_JAVASCRIPT,
                                          mktime(0,0,0, date('m'),1,date('Y')));
            $this->view->dateEnd   = date(Application_Model_Budget::DATE_FORMAT_JAVASCRIPT);
            return true;
        }

        $dateStart = $request->getParam('date_start');
        $dateEnd   = $request->getParam('date_end');
        
        $this->view->dateStart = $dateStart;
        $this->view->dateEnd   = $dateEnd;
        $this->view->mode      = $mode;

        $this->_model->setData($dateStart, $dateEnd);
        
        function cmp_organizations($org1, $org2) {
            if ((strpos( $org1["name"], '[') === 0) && strpos($org2["name"], '[') === False)
                    return -1;
            if ((strpos($org2["name"], '[') === 0) && strpos($org1["name"], '[') === False)
                    return 1;
            return strcmp($org1["name"], $org2["name"]);
        }
        
        switch ($mode) {
            case Application_Model_Budget::PAGE_MODE_TABLE:
                $this->_processTable();
                $organizations = $this->_model->getOrganizations();
                uasort($organizations, "cmp_organizations");
//                var_dump($organizations);
                $this->view->organizations = $organizations;
                $this->view->months = $this->_model->getMonths();
                $this->view->oranizationPrefix = Application_Model_Budget::ORGANIZATION_PREFIX;
                break;

            case Application_Model_Budget::PAGE_MODE_GRAPH:
                $this->_processGraph();
                break;
            default:
                // this will never happen (blank mode)
                break;
        }

        return true;
    }

    private function _processTable()
    {
        //get_configuration
        //$date_formats = array("%m-%d-%Y %r" => "mm-dd-yyyy",
        //  "%m/%d/%Y %r" => "mm/dd/yyyy",
        //  "%Y-%m-%d %r" => "yyyy-mm-dd",
        //  "%d-%b-%Y %r" => "dd-MMM-yyyy");

        $config = SiteConfig::get();

        // If the system date format contains time - remove it by removing all after space character
        $dateFormat = $config->default_date_format;
        $i = strpos($dateFormat, ' ');
        if (false !== $i) {
            $dateFormat = substr($dateFormat, 0, $i);
        }

        $request = $this->getRequest();
        $dateStart = $request->getParam('date_start');
        $dateEnd   = $request->getParam('date_end');

        $tplMonths = array();
        $months = $this->_model->getMonths();
        foreach ($months as $m) {
            $monthStart = mktime(0,0,0, date('m',$m), 1, date('Y',$m));
            $monthEnd = mktime(0,0,0, date('m',$m), date('t',$m), date('Y',$m));
            $tplMonths[] = strftime($dateFormat, $monthStart) . ' - ' . strftime($dateFormat, $monthEnd);
        }

        $i = sizeof($months);
        if (1 == $i) {
            // month is one, it is limited by end date and start date
            $monthParts = explode('/', $dateStart);
            $monthStart = mktime(0,0,0, $monthParts[0], $monthParts[1], $monthParts[2]);
            $monthParts = explode('/', $dateEnd);
            $monthEnd   = mktime(0,0,0, $monthParts[0], $monthParts[1], $monthParts[2]);
            $tplMonths[0] = strftime($dateFormat, $monthStart) . ' - ' . strftime($dateFormat, $monthEnd);
        } else {
            $m = $months[0];
            $monthParts = explode('/', $dateStart);
            $monthStart = strftime($dateFormat, mktime(0, 0, 0, $monthParts[0], $monthParts[1], $monthParts[2]));
            $monthEnd = strftime($dateFormat, mktime(0, 0, 0, date('m', $m), date('t', $m), date('Y', $m)));
            $tplMonths[0] = $monthStart . ' - ' . $monthEnd;

            $m = $months[$i - 1];
            $monthParts = explode('/', $dateEnd);
            $monthStart = strftime($dateFormat, mktime(0, 0, 0, $monthParts[0], 1, $monthParts[2]));
            $monthEnd   = strftime($dateFormat, mktime(0, 0, 0, $monthParts[0], $monthParts[1], $monthParts[2]));
            $tplMonths[$i - 1] = $monthStart . ' - ' . $monthEnd;
        }
        $this->view->tplMonths = $tplMonths;
    }

    /**
     * @todo Most of this logic should be moved to Javascript
     */
    private function _processGraph()
    {
        $graphTicks = array();
        $i = 0;
        foreach ($this->_model->getMonths() as $m) {
            ++$i;
            $graphTicks[] = "[".$i.", '".date('M, Y',$m)."']";
        }
        $graphTicks = implode(', ', $graphTicks);
        $this->view->graphTicks = $graphTicks;

        $companiesData = array();
        $i = 0;
        $organizations = $this->_model->getOrganizations();
        foreach ($organizations as $oId=>$organization) {
            if ($oId == 'totals') {
                continue;
            }
            $monthlyData = array();
            $j = 1;
            foreach ($this->_model->getMonths() as $m) {
                $monthlyData[] = '[step*'.$j.' + '.((string)$i).'*bar_width, '.$organizations[$oId] ['totals'] [$m] ['charge'].']';
                ++$j;
            }

            $companiesData[] = "{label: '".$organization['name']."', data: [".implode(', ', $monthlyData)."]},";
            ++$i;
        }

        $graphData = "
var bar_width = 0.2;
var step = ".sizeof($companiesData)." < 5 ? 1 : (amount + 1) * bar_width;
var graph_data = [
".implode("\n", $companiesData)."
];
";
        $this->view->graphData = $graphData;
    }
}
