<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    03 Feb 2011

    Class that maps storage instance entity with DB.

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
require_once('OMOP/WebRL/Configuration/AwsConfiguration.php');

class Application_Model_StorageInstanceMapper
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
            $this->setDbTable('Application_Model_DbTable_StorageInstance');
        }
        return $this->_dbTable;
    }
    
    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_StorageInstance();
            $entry->setId($row->storage_instance_id)
                  ->setSystemInstanceId($row->system_instance_id);
            $entries[] = $entry;
        }
        return $entries;
    }
    
    public function find($id, Application_Model_StorageInstance $instance)
    {        
        $resultSet = $this->getDbTable()->find($id);
        
        if (0 == count($resultSet))
            return;
        
        $row = $resultSet->current();
        $instance->setId($row->storage_instance_id)
              ->setSystemInstanceId($row->system_instance_id);
    }
    
    public function save(Application_Model_StorageInstance $instance)
    {        
        $data = array(
            'storage_instance_id' => $instance->getId(),
            'system_instance_id' => $instance->getSystemInstanceId()
        );
        
        if (null == ($id = $instance->getId())) {
            $this->getDbTable()->insert($data);
        } else { 
            $this->getDbTable()->update($data, array('storage_instance_id = ?' => $instance->getId()));
        }        
    }
    
    public function getPaginatorAdapter($sortColumn, $sortDirection)
    {
        $columns = array(
            'name' => 'i.system_instance_name',
            'foobar' => 'connect',
            'host' => 'i.system_instance_host',
            'keyName' => 'i.system_instance_key_name',
            'instanceType' => 'is.instance_size_name',
            'osFamily' => 'is.os_family',
            'id' => 'si.storage_instance_id'
        );
        
        if ($sortDirection != 'asc' )
            $sortDirection = 'desc';
        
        $columnOrder = array_values($columns);
        if ($sortColumn <= 9)
            $sortColumn = $columnOrder[$sortColumn - 1];
        else
            $sortColumn = $columns[0];
            
        //This key was added for sorting
        unset($columns['foobar']);
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
            ->from(array('si' => 'storage_instance_tbl'), $columns)
            ->joinLeft(array('i' => 'system_instances_tbl'), 'si.system_instance_id = i.system_instance_id', array())
            ->joinLeft(array('is' => 'instance_size_tbl'), 'i.system_instance_size_id = is.instance_size_id', array())
            ->order("$sortColumn $sortDirection");

        return new Zend_Paginator_Adapter_DbSelect($select);
    }
}
