<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    23 December 2010

    Model of budget pages.

    (c)2009-2010 Foundation for the National Institutes of Health (FNIH)

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
class Application_Model_Budget extends Zend_Db_Table_Abstract
{
    protected $_name = 'instance_tbl';

    const DATE_FORMAT_SQL = 'Y-m-d';
    const DATE_FORMAT_JAVASCRIPT = 'm/d/Y';

    const PAGE_MODE_BLANK = '';
    const PAGE_MODE_TABLE = 'table';
    const PAGE_MODE_GRAPH = 'graph';

    const ORGANIZATION_PREFIX = 'oid';

    /**
     * List of organizations displayed in report
     * @var array
     */
    private $_organizations = array(
        'totals' => array()
    );

    /**
     * Get organizations data for report
     * @return array organization data list
     */
    public function getOrganizations()
    {
        return $this->_organizations;
    }

    /**
     * Sort users in the org alphabetically
     * @param string organization ID
     */
    private function _sortOrganizationUsers($orgId)
    {
        uasort(
            $this->_organizations[$orgId]['users'],
            array("self", "cmpUserNames")
        );
    }

    /**
     * Add new organization to the list
     * @param array organization data
     */
    public function setOrganization($organization)
    {
        if (!$organization) {
            return;
        }
        if (isset ($this->_organizations[$organization['organization_id']])) {
            throw new Exception('Trying to reinitialize organization');
        }

        $o = array();
        $o['name'] = $organization['organization_name'];
        $o['totals'] = array();
        $o['users'] = array();
        $this->_organizations[$organization['organization_id']] = $o;
    }

    /**
     * List of months displayed in report
     * @var array
     */
    private $_months = array();

    /**
     * Getter of _months: list of months displayed in report
     * @return array
     */
    public function getMonths()
    {
        return $this->_months;
    }

    /**
     * Add new month to report
     * @param string month description for report
     */
    public function setMonths($month)
    {
        $this->_months[] = $month;
    }

    /**
     * Returns the array of months corresponding to the time range between
     * the $dateStart and $dateEnd
     *
     * NOTE: date format is hardcoded
     * define (DATE_FORMAT, 'm/d/Y');
     * define (DATE_FORMAT_JAVASCRIPT, 'mm/dd/yyyy');
     *
     * @param String $dateStart formatted as mm/dd/yyyy
     * @param String $dateEnd formatted as mm/dd/yyyy
     * @return array of months; each array element is {first month day, last month day}
     */
    public function getDateRanges($dateStart, $dateEnd)
    {
        //Init the vars
        $output = array();

        //Parse input data to pieces
        $a1 = explode('/', $dateStart, 3);
        $a2 = explode('/', $dateEnd, 3);
        
        //Assign start and end month
        $startMonth = mktime(0,0,0, $a1[0], $a1[1], $a1[2]);
        $endMonth = mktime(0,0,0, $a2[0], $a2[1], $a2[2]);

        if ($startMonth === false || $endMonth === false) {
            return $output;
        }

        if ($startMonth > $endMonth) {
            $rx = $startMonth;
            $ax = $a1;

            $startMonth = $endMonth; $a1 = $a2;
            $endMonth = $rx; $a2 = $ax;
        }

        // If dates are within the same month, just return them.
        // If not, push the first range to the output array.
        if ($a1[0] == $a2[0] && $a1[2] == $a2[2]) {
            array_push($output, array($startMonth, $endMonth));
            return $output;
        } else {
            $firstMonthEnd = mktime(23, 59, 59, $a1[0], date('t', $startMonth), $a1[2]);
            array_push($output, array($startMonth, $firstMonthEnd));
        }

        $m = $a1[0];
        $y = $a1[2];

        // Assign output array
        $limit = 50;
        while ($limit--) {
            $m++;
            if ($m == 13) {
                $m = 1;
                $y++;
            }
            $startMonth = mktime(0,0,0, $m, 1, $y);
            if ($m == $a2[0] && $y == $a2[2]) {
                $endMonth = mktime(23,59,59, $m, $a2[1], $y);
                array_push($output, array($startMonth, $endMonth));
                break;
            } else {
                $endMonth = mktime(23,59,59, $m, date('t', $startMonth), $y);
                array_push($output, array($startMonth, $endMonth));
            }
        }
        
        return $output;
    }

    public function getDateRangesText($ranges)
    {
        $dateRangesText = array();
        foreach ($ranges as $key=>$range) {
            if (!is_array($range)) {
                throw new Exception('Invalid range passed to method ' . __FUNCTION__);
            }
            $dateRangesText[$key] = array(
                date(self::DATE_FORMAT_SQL . ' 00:00:00' , $range[0]),
                date(self::DATE_FORMAT_SQL . ' 23:59:59' , $range[1])
            );
        }
        return $dateRangesText;
    }

    /**
     * Get budget statistics for all organizations for given period of time
     * @param string start date
     * @param string end date
     * @return array
     */
    public function getBudget($dateStart, $dateEnd)
	{
        $manager = new UserManager(DbManager::$db);
        return $manager->get_budget($dateStart, $dateEnd);
	}

    /**
     * Initializes organizations array with given data
     * @param array statistics for given month
     * @param integer UNIX timestamp date representing start of month
     */
    private function _initMonthlyStatistics($data, $month) {
        $monthKey = mktime(0, 0, 0, date('m', $month), 1, date('Y', $month));
        if (!in_array($monthKey, $this->getMonths())) {
            $this->setMonths($monthKey);
        }

        if (!$data) {
            return;
        }

        /**
         * Add new organization to the list
         */
        if (!isset ($this->_organizations[$data['organization_id']])) {
            $this->setOrganization($data);
        }

        /**
         * Insert not existing monthly statistics to the organization array
         */
        if (!isset ($this->_organizations['totals'] [$monthKey])) {
            $this->_organizations['totals'] [$monthKey] = array(
                'instances' => 0,
                'charge' => 0,
            );
        }
        if (!isset ($this->_organizations[$data['organization_id']] ['totals'] [$monthKey])) {
            $this->_organizations[$data['organization_id']] ['totals'] [$monthKey] = array(
                'instances' => 0,
                'charge' => 0,
            );
        }

        /**
         * Add new user to the organization
         */
        if (!isset ($this->_organizations[$data['organization_id']] ['users'] [$data['user_id']])) {
            $this->_organizations[$data['organization_id']] ['users'] [$data['user_id']] = array();
            $this->_organizations[$data['organization_id']] ['users'] [$data['user_id']] ['name'] = $data['user_name'];
        }
        /**
         * Push user month data
         */
        $this->_organizations[$data['organization_id']] ['users'] [$data['user_id']] [$monthKey] = array(
            'instances' => $data['inst_amount'],
            'charge' => $data['charge'],
        );
        $this->_organizations['totals'] [$monthKey] ['instances'] += intval($data['inst_amount']);
        $this->_organizations['totals'] [$monthKey] ['charge']    += floatval($data['charge']);
        $this->_organizations[$data['organization_id']] ['totals'] [$monthKey] ['instances']
            += floatval($data['inst_amount']);
        $this->_organizations[$data['organization_id']] ['totals'] [$monthKey] ['charge']
            += floatval($data['charge']);
    }

    /**
     * Add general statistics to organization/user data
     * @param string object type (organization/user)
     * @param string object id
     * @param array object data
     */
    public function pushGeneralStatistics($type, $id, $data) {
        switch (strtolower($type)) {
            case 'user':
                if (is_null($data['organization_id'])) {
                    $data['organization_id'] = '0';
                }
                $data['organization_id'] = self::ORGANIZATION_PREFIX . $data['organization_id'];

                $this->_organizations[$data['organization_id']] ['users'] [$id] ['budget'] = array(
                    'instances' => $data['instances'], //num_instances
                    'limit' => $data['limit'], //user_money
                );
                if (!isset ($this->_organizations['totals'] ['budget'])) {
                    $this->_organizations['totals'] ['budget'] = array(
                        'instances' => 0,
                        'limit' => 0,
                    );
                }
                $this->_organizations['totals'] ['budget'] ['instances'] += $data['instances'];

                if ($data['organization_id'] == self::ORGANIZATION_PREFIX . '0') {
                    if (!isset ($this->_organizations [$data['organization_id']] ['totals'] ['budget'])) {
                        $this->_organizations [$data['organization_id']] ['totals'] ['budget'] = array(
                            'instances' => 0,
                            'limit' => 0,
                        );
                    }
                    $this->_organizations [$data['organization_id']] ['totals'] ['budget'] ['instances']
                        += $data['instances'];
                    $this->_organizations [$data['organization_id']] ['totals'] ['budget'] ['limit']
                        += $data['limit'];
                }
                break;
            case 'organization':
                if (!isset ($this->_organizations [$id] ['totals'] ['budget'])) {
                    $this->_organizations [$id] ['totals'] ['budget'] = array(
                        'instances' => 0,
                        'limit' => 0,
                    );
                }
                if (!isset ($this->_organizations ['totals'] ['budget'])) {
                    $this->_organizations ['totals'] ['budget'] = array(
                        'instances' => 0,
                        'limit' => 0,
                    );
                }
                if (!isset ($this->_organizations [$id] ['totals'] ['current']))
                {
                    $this->_organizations [$id] ['totals'] ['current'] = array(
                        'instances' => 0,
                        'remaining' => 0,
                    );
                }
                if (!isset ($this->_organizations['totals'] ['current']))
                {
                    $this->_organizations['totals'] ['current'] = array(
                        'instances' => 0,
                        'remaining' => 0,
                    );
                }
                $this->_organizations [$id] ['totals'] ['budget'] ['instances'] = $data['instances'];
                $this->_organizations [$id] ['totals'] ['budget'] ['limit'] = $data['limit'];
                $this->_organizations ['totals'] ['budget'] ['limit'] += $data['limit'];
                $this->_organizations [$id] ['totals'] ['current']['remaining'] += $data['limit'];
                $this->_organizations ['totals'] ['current']['remaining'] += $data['limit'];
                break;
        }
    }

    public function pushActiveInstances($userId, $data)
    {
        if(empty($data)) {
            return;
        }
        if (is_null($data['organization_id'])) {
            $data['organization_id'] = '0';
        }
        $data['organization_id'] = self::ORGANIZATION_PREFIX . $data['organization_id'];

        //Check user existance
        if (!isset ($this->_organizations[$data['organization_id']] ['users'] [$userId] ['current'])) {
            $this->_organizations[$data['organization_id']] ['users'] [$userId] ['current'] = array(
                'instances' => 0,
            );
        }

        //Check organizations statistics records
        if (!isset ($this->_organizations['totals'] ['current'])) {
            $this->_organizations['totals'] ['current'] = array(
                'instances' => 0,
            );
        } elseif (!isset ($this->_organizations['totals'] ['current'] ['instances'])) {
            $this->_organizations['totals'] ['current'] ['instances'] =  0;
        }

        if (!isset ($this->_organizations [$data['organization_id']] ['totals'] ['current'])) {
            $this->_organizations [$data['organization_id']] ['totals'] ['current'] = array(
                'instances' => 0,
            );
        } elseif (!isset ($this->_organizations [$data['organization_id']] ['totals'] ['current'] ['instances'])) {
            $this->_organizations [$data['organization_id']] ['totals'] ['current'] ['instances'] = 0;
        }

        $this->_organizations[$data['organization_id']] ['users'] [$userId] ['current'] ['instances'] = $data['instances'];
        $this->_organizations [$data['organization_id']] ['totals'] ['current'] ['instances'] += $data['instances'];
        $this->_organizations['totals'] ['current'] ['instances'] += $data['instances'];
    }

    /**
	 * Function adds active instances values for system instances
	 * @param organization id
	 * @param system instance id
	 */
	public function pushSystemInstance($organizationId, $userId)
    {
		$this->_organizations[$organizationId] ['users'] [$userId] ['current'] = array();
		$this->_organizations[$organizationId] ['users'] [$userId] ['current'] ['instances'] = 1;
		if (! isset($this->_organizations[$organizationId] ['totals'])) {
			$this->_organizations[$organizationId] ['totals'] = array();
			$this->_organizations[$organizationId] ['totals'] ['current'] = array();
			$this->_organizations[$organizationId] ['totals'] ['current'] ['instances'] = 0;
		}
		$this->_organizations[$organizationId] ['totals'] ['current'] ['instances'] += 1;
	}

    public function pushUserRemainingBudget($id, $data)
    {
        // $row['user_id']
        // $row['organization_id']
        // $row['limit']
        // $row['charge']
        if(empty($data)) {
            return;
        }
        if (is_null($data['organization_id'])) {
            $data['organization_id'] = '0';
        }
        $data['organization_id'] = self::ORGANIZATION_PREFIX . $data['organization_id'];

        //Check user existance
        if (!isset ($this->_organizations[$data['organization_id']] ['users'] [$id] ['current'])) {
            $this->_organizations[$data['organization_id']] ['users'] [$id] ['current'] = array(
                'remaining' => 0,
            );
        }

        //Check organizations statistics records
        if (!isset ($this->_organizations['totals'] ['current'])) {
            $this->_organizations['totals'] ['current'] = array(
                'remaining' => 0,
            );
        } elseif (!isset ($this->_organizations['totals'] ['current'] ['remaining'])) {
            $this->_organizations['totals'] ['current'] ['remaining'] =  0;
        }

        if (!isset ($this->_organizations [$data['organization_id']] ['totals'] ['current'])) {
            $this->_organizations [$data['organization_id']] ['totals'] ['current'] = array(
                'remaining' => 0,
            );
        } elseif (!isset ($this->_organizations [$data['organization_id']] ['totals'] ['current'] ['remaining'])) {
            $this->_organizations [$data['organization_id']] ['totals'] ['current'] ['remaining'] = 0;
        }

        $this->_organizations[$data['organization_id']] ['users'] [$id] ['current'] ['remaining'] = $data['limit'] - $data['charge'];
        if (self::ORGANIZATION_PREFIX.'0' == $data['organization_id']) {
            $this->_organizations [$data['organization_id']] ['totals'] ['current'] ['remaining'] += ($data['limit'] - $data['charge']);
            $this->_organizations ['totals'] ['current'] ['remaining'] += ($data['limit'] - $data['charge']);
        } else {
            $this->_organizations [$data['organization_id']] ['totals'] ['current'] ['remaining'] -= $data['charge'];
            $this->_organizations ['totals'] ['current'] ['remaining'] -= $data['charge'];
        }
    }

    public function getUserBudgetStatistics($id)
	{
        $query = "select `u`.`user_id`, `u`.`organization_id`, `u`.`user_money` as `limit`, `u`.`num_instances` as `instances`
from `user_tbl` as `u`
where `u`.`user_id` = ?";
        return $this->_db->fetchRow($query, array($id));
	}

    public function getOrganizationBudgetStatistics($id)
	{
        if (!$id) {
            return;
        }
        $query = "select `o`.`organization_id`, `o`.`organization_budget` as `limit`, `o`.`organization_instances_limit` as `instances`
from `organization_tbl` as `o`
where `o`.`organization_id` = ?";
        return $this->_db->fetchRow($query, array($id));
	}

    public function getUserCurrentBudget($id)
	{
        $query = "select count(`i`.`instance_id`) as `instances`, `u`.`user_id`, `u`.`organization_id`
from `instance_tbl` as `i`
left join `instance_request_tbl` as `ir` on `ir`.`instance_request_id` = `i`.`instance_request_id`
left join `user_tbl` as `u` on `u`.`user_id` = `ir`.`user_id`
where `u`.`user_id` = ? and `i`.`status_flag` = 'A'
group by `u`.`user_id`";
        return $this->_db->fetchRow($query, array($id));
	}

    public function getUserRemainingBudget($userId)
	{
        $query = "
select `u`.`user_id`,
  `o`.`organization_id`,
  `u`.`user_money` as `limit`,
  sum(
    (`i`.`instance_hour_charge` + `i`.`storage_hour_charge`) *
	  TIMEDIFF(COALESCE(`i`.`terminate_date`,CURRENT_TIMESTAMP()), `i`.`start_date`) / 3600
  ) as `charge`
from `instance_tbl` as `i`
left join `instance_request_tbl` as `ir` on `ir`.`instance_request_id` = `i`.`instance_request_id`
left join `user_tbl` as `u` on `u`.`user_id` = `ir`.`user_id`
left join `organization_tbl` as `o` on `o`.`organization_id` = `u`.`organization_id`
where
  `u`.`user_id` = ?
group by `u`.`user_id`";
        return $this->_db->fetchRow($query, array($userId));
	}

    /**
     * Function sorts two elements of array by their values with the 'name' keys
     * @param array first value to compare
     * @param array second value to compare
     * @return integer compared value (1, -1)
     */
    public static function cmpUserNames($user1, $user2) {
        return strcasecmp($user1['name'], $user2['name']);
    }

    /**
     * Initialize Budget data for organizations for given period of time
     * @param string start date for obtaining data
     * @param string end date for obtaining data
     */
    public function setData($dateStart, $dateEnd)
    {
        $dateRanges = $this->getDateRanges($dateStart, $dateEnd);
        $dateRangesText = $this->getDateRangesText($dateRanges);

        // Get data for given period of time and init Organizations array
        foreach ($dateRangesText as $key=>$range) {
            $budgetMonth = $this->getBudget($range[0], $range[1]);
            if (!$budgetMonth) {
                $this->_initMonthlyStatistics(array(), $dateRanges[$key][0]);
                continue;
            }

            foreach ($budgetMonth as $data) {
                // This conditional block should be made server-side by the DB engine,
                // actually, but this does not work on my computer:
                // COALESCE(o.organization_id,0), COALESCE(o.organization_name,'[other]')
                if (is_null($data['organization_id'])) {
                    $data['organization_id'] = '0';
                    $data['organization_name'] = '[not in Org]';
                }
                $data['organization_id'] = self::ORGANIZATION_PREFIX . $data['organization_id'];
                $this->_initMonthlyStatistics($data, $dateRanges[$key][0]);
            }
        }

        // Process and sort raw data
        foreach ($this->getOrganizations() as $oId=>$organization) {
            if (!is_array($organization['users'])) {
                continue;
            }
            if ($oId != self::ORGANIZATION_PREFIX.'system') {
                $organizationId = substr($oId, strlen(self::ORGANIZATION_PREFIX));
                $this->pushGeneralStatistics('organization',
                    $oId,
                    $this->getOrganizationBudgetStatistics($organizationId));
                foreach ($organization['users'] as $uId=>$user) {
                    $this->pushGeneralStatistics('user',
                        $uId,
                        $this->getUserBudgetStatistics($uId));
                    $this->pushActiveInstances($uId, $this->getUserCurrentBudget($uId));
                    $this->pushUserRemainingBudget($uId, $this->getUserRemainingBudget($uId));
                }
            } else {
                foreach ($organization['users'] as $uId => $user) {
                    $this->pushSystemInstance($oId, $uId);
                }
            }

            $this->_sortOrganizationUsers($oId);
        }
    }
}
