<?php

/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    04-Feb-2011

    Mapper for RunningMethod model

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
class Application_Model_RunningMethodMapper {
    
    protected $_dbTable;

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
            $this->setDbTable('Application_Model_DbTable_RunningMethod');
        }
        return $this->_dbTable;
    }
    
    
    public function getPaginatorAdapter($sortColumn = 4, $sortDir = 'asc', $organizationId = 0, $userId = false) {
        
        $columns = array(
            'ownerLogin' => 'u.login_id',
            'name' => 'm.method_name',
            'parameter' => 'm.method_parameter',
            'startDate' => 'm.start_date',
            'completeDate' => 'm.complete_date',
            'instanceName' => 'i.assigned_name',
            'status' => 'm.status_flag',
            'id' => 'i.instance_id',
            'ownerId' => 'u.user_id'
        );
        
        $select = $this->getDbTable()->getAdapter()->select();
        
        $select->from(array('m' => 'method_launch_tbl'), $columns);
        
        $select->join(
            array('i' => 'instance_tbl'), 
            'm.instance_id = i.instance_id',
            array()
        );

        $select->join(
            array('ir' => 'instance_request_tbl'), 
            'ir.instance_request_id = m.instance_request_id',
            array()
        );
        
        $select->join(
            array('u' => 'user_tbl'), 
            'u.user_id = ir.user_id',
            array()
        );
        
        $columnOrder = array_keys($columns);
        $columnsNumber = count($columnOrder);
        
        $sortColumn = $columnOrder[$sortColumn <= $columnsNumber ? $sortColumn - 1 : 0];
        $select->order("$sortColumn $sortDir");
        
        if ($organizationId) {
            $select->where('u.organization_id = ?', $organizationId);
        }
        
        if ($userId) {
            $select->where('u.user_id = ?', $userId);
        }

        return new Zend_Paginator_Adapter_DbSelect($select);
    }
}