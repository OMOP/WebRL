<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 Dec 2010

    Class that maps system instance entity with DB.

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
require_once('OMOP/WebRL/Configuration/AwsConfiguration.php');


class Application_Model_SystemInstanceMapper
{
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
            $this->setDbTable('Application_Model_DbTable_SystemInstance');
        }
        return $this->_dbTable;
    }
    
    public function fetchAll() {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_SystemInstance();
            $entry->setId($row->system_instance_id)
                  ->setName($row->system_instance_name)
                  ->setHost($row->system_instance_host)
                  ->setKeyName($row->system_instance_key_name)
                  ->setLaunchDate($row->system_instance_launch_date)
                  ->setRegisterDate($row->system_instance_register_date)
                  ->setEndDate($row->system_instance_end_date)
                  ->setSizeId($row->system_instance_size_id);
            $entries[] = $entry;
        }
        return $entries;
    }
    
    public function find($id, Application_Model_SystemInstance $instance) {
        
        $resultSet = $this->getDbTable()->findWithInstanceType($id);
        
        if (0 == count($resultSet))
            return;
        
        $row = $resultSet->current();
        $instance->setId($row->system_instance_id)
              ->setName($row->system_instance_name)
              ->setHost($row->system_instance_host)
              ->setKeyName($row->system_instance_key_name)
              ->setLaunchDate($row->system_instance_launch_date)
              ->setRegisterDate($row->system_instance_register_date)
              ->setEndDate($row->system_instance_end_date)
              ->setSizeId($row->system_instance_size_id)
              ->setOsFamily($row->os_family)
              ->setInstanceType($row->instance_size_name);
    }
    
    public function save(Application_Model_SystemInstance $instance) {
        
        $instanceInfo = $instance->getInstanceInfo();

        $data = array(
            'system_instance_name' => $instance->getName(),
            'system_instance_host' => $instance->getHost(),
            'system_instance_key_name' => $instance->getKeyName(),
            'system_instance_launch_date' => $instance->getLaunchDate(),
            'system_instance_size_id' => $instance->getSizeId(),
            'system_instance_end_date' => $instance->getEndDate()
        );
        
        if (! $data['system_instance_end_date']) $data['system_instance_end_date'] = NULL;
        
        
        if (null == ($id = $instance->getId())) {
            $data['system_instance_register_date'] = gmdate('c');
            $this->getDbTable()->insert($data);
        }
        else { 
            $this->getDbTable()->update($data, array('system_instance_id = ?' => $instance->getId()));
        }
        
    }
    
    public function getPaginatorAdapter($sort_column, $sort_dir) {
        $columns = array(
            'name' => 'si.system_instance_name',
            'foobar' => 'connect',
            'host' => 'si.system_instance_host',
            'keyName' => 'si.system_instance_key_name',
            'instanceType' => 'is.instance_size_name',
            'osFamily' => 'is.os_family',
            'launchDate' => 'si.system_instance_launch_date',
            'registerDate' => 'si.system_instance_register_date',
            'endDate' => 'si.system_instance_end_date',
            'id' => 'si.system_instance_id'
        );
        
        if ($sort_dir != 'asc' )
            $sort_dir = 'desc';
        
        $column_order = array_values($columns);
        if ($sort_column <= 9)
            $sort_column = $column_order[$sort_column - 1];
        else
            $sort_column = $columns[0];
        //This key was added for sorting
        unset($columns['foobar']);
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('si' => 'system_instances_tbl'), $columns)
               ->joinLeft(array('is' => 'instance_size_tbl'), 'si.system_instance_size_id = is.instance_size_id', array())
               ->order("$sort_column $sort_dir");

        return new Zend_Paginator_Adapter_DbSelect($select);
    }
    
    function getUniqueNameValidator($exclude_id = null) {

        if ($exclude_id) {
            /*$exclude = array(
                'field' => 'system_instance_id',
                'value' => $exclude_id
            );*/
            $exclude = ' `system_instance_id` != '.$exclude_id.' AND `system_instance_end_date` IS NULL';
        }
        else
            $exclude = ' `system_instance_end_date` IS NULL';
        return new Zend_Validate_Db_NoRecordExists('system_instances_tbl','system_instance_name' ,$exclude, $this->getDbTable()->getAdapter());
    }
    
    function getUniqueHostValidator($exclude_id = null) {

        if ($exclude_id) {
            /*$exclude = array(
                'field' => 'system_instance_id',
                'value' => $exclude_id
            );*/
            $exclude = ' `system_instance_id` != '.$exclude_id.' AND `system_instance_end_date` IS NULL';
        }
        else
            $exclude = ' `system_instance_end_date` IS NULL';
        return new Zend_Validate_Db_NoRecordExists('system_instances_tbl','system_instance_host' ,$exclude, $this->getDbTable()->getAdapter());
    }

}