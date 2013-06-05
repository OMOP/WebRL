<?php

/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    17-Feb-2011

    Mapper for Amazon Log 

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
class Application_Model_AmazonLogMapper
{
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
            $this->setDbTable('Application_Model_DbTable_AmazonLog');
        }
        return $this->_dbTable;
    }
    
    public function find($id, Application_Model_AmazonLog $log)
    {
        $resultSet = $this->getDbTable()->find($id);
        if (0 == count($resultSet))
            return;
        
        $row = $resultSet->current();
        
        $log->setId($row->id)
            ->setInstanceId($row->instance_id)
            ->setStatus($row->status_flag)
            ->setConsoleOutput($row->log_data);
    }
    
    public function findByInstanceId($id, Application_Model_AmazonLog $log)
    {
        $resultSet = $this->getDbTable()->findByInstanceId($id);
        if (0 == count($resultSet))
            return;
        
        $row = $resultSet->current();
        
        $log->setId($row->id)
            ->setInstanceId($row->instance_id)
            ->setStatus($row->status_flag)
            ->setConsoleOutput($row->log_data);
    }
    
    
    public function save(Application_Model_AmazonLog $log)
    {
        $data = array(
            'instance_id' => $log->getInstanceId(),
            'status_flag' => $log->getStatus(),
            'log_data' => $log->getConsoleOutput()
        );
        
        if (null == ($id = $log->getId()))
        {
            $this->getDbTable()->insert($data);
        } else {
            $this->getDbTable()->update($data, array('id => ?' => $id));
        }
    }
    
}