<?php
/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10-Mar-2011

    Mapper for Organizations Model

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

==============================================================================*/

class Application_Model_OrganizationMapper
{
    protected $_dbTable;

    public function getPaginatorAdapter($sortColumn, $sortDir)
    {
        $subqueries = array();
        // 00
        $subqueries['runningInstances'] = "SELECT COUNT(*)
FROM instance_request_tbl r00
JOIN instance_tbl i00 on i00.instance_request_id = r00.instance_request_id
JOIN user_tbl u00 ON u00.user_id = r00.user_id
where i00.status_flag = 'A'
AND o.organization_id = u00.organization_id";

        // 01
        $subqueries['activeUsers'] = "SELECT COUNT(*)
FROM user_tbl u01
WHERE u01.organization_id = o.organization_id AND u01.admin_flag = 'Y'";

        // 02
        $subqueries['chargedTotal'] = "
SELECT ROUND(
    SUM(s02.instance_price *
        TIMESTAMPDIFF(SECOND, i02.start_date, IFNULL(i02.terminate_date, utc_timestamp()) ))/ 3600, 0)
FROM instance_request_tbl r02
JOIN instance_size_tbl s02 on r02.instance_size_id = s02.instance_size_id
JOIN instance_tbl i02 on i02.instance_request_id = r02.instance_request_id
JOIN user_tbl u02 ON u02.user_id = r02.user_id
where o.organization_id = u02.organization_id";

        $columns = array(
            'id' => 'o.organization_id',
            'name' => 'o.organization_name',
            'organization_budget' => 'o.organization_budget',
            //'admin' => '2',
            'activeUsers' => '('.$subqueries['activeUsers'].')',
            'maxUsers' => 'o.organization_users_limit',
            'runningInstances' => '('.$subqueries['runningInstances'].')',
            'instancesLimit' => 'o.organization_instances_limit',
            'chargedTotal' => '('.$subqueries['chargedTotal'].')',
            //'remaining' => 'organization_budget - chargedTotal'
        );

        if ($sortDir != 'asc') {
            $sortDir = 'desc';
        }

        $sortCols = array_keys($columns);
        if ($sortColumn > 0 && $sortColumn < 9) {
            $sortColumn = $sortCols[$sortColumn - 1];
        } else {
            $sortColumn = $sortCols[0];
        }

        $db = $this->getDbTable()->getAdapter();
        $select = $db->select()
                     ->from(array('o' => 'organization_tbl'), $columns);
//                               ->joinLeft(array('o' => 'organization_tbl'),
//                                       'o.organization_id = u.organization_id',
//                                       array()
//                               )
//                               ->order("$sortColumn $sortDir");
        /*if ($organizationId != 0) {
            $select->where('u.organization_id = ?', $organizationId);
        }*/
        return new Zend_Paginator_Adapter_DbSelect($select);
    }

    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Application_Model_DbTable_Organization');
        }
        return $this->_dbTable;
    }

    public function getAdmins()
    {
        $db = $this->getDbTable()->getAdapter();
        $columns = array(
            'id' => 'user_id',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'organization' => 'organization_id'
        );
        $select = $db->select()
                     ->from(array('user_tbl'), $columns)
                     ->where('admin_flag = ?', 'Y')
                     ->order('first_name ASC')
                     ->order('last_name ASC');

        $admins = array();
        foreach ($db->fetchAll($select) as $admin) {
            if (!isset ($admins[$admin['organization']])) {
                $admins[$admin['organization']] = array();
            }
            $admins[$admin['organization']][$admin['id']] = $admin['first_name'] . ' ' . $admin['last_name'];
        }
        return $admins;
    }
    
    public function find($id, Application_Model_Organization $org)
    {
        $resultSet = $this->getDbTable()->find($id);

        if (count($resultSet) == 0) {
            return;
        }
        
        $row = $resultSet->current();
        $org->setId($row->organization_id)
            ->setBudget($row->organization_budget)
            ->setAdminFactor($row->organization_admin_factor);
    }
}

