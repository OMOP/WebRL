<?php

/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    16-Feb-2011

    Mapper for Website Event model

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
class Application_Model_WebsiteEventMapper
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
            $this->setDbTable('Application_Model_DbTable_WebsiteEvent');
        }
        return $this->_dbTable;
    }
    
    public function getPaginatorAdapter($sort_column, $sort_dir)
    {
        $columns = array(
            'date' => 'wel.website_event_date',
            'user' => 'u.login_id',
            'ip' => 'wel.remote_ip',
            'description' => 'wel.website_event_message',
            'id' => 'wel.website_log_id'
        );
        
        $sortCols = array_values($columns);
        
        if ($sort_dir != 'asc' )
            $sort_dir = 'desc';
        if ($sort_column >= 0 && $sort_column < 5)
            $sort_column = $sortCols[$sort_column - 1];
        else
            $sort_column = $sortCols[0];
        $select = $this->getDbTable()->getAdapter()->select()
            ->from(array('wel' => 'website_event_log'), $columns)
            ->joinLeft(
                array('u' => 'user_tbl'),
                'u.user_id = wel.user_id',
                array()
            )
            ->order("$sort_column $sort_dir");
         
        return new Zend_Paginator_Adapter_DbSelect($select);
    }
    
    public function find($id, Application_Model_WebsiteException $exc)
    {
        
        $resultSet = $this->getDbTable()->find($id);
        if (0 == count($resultSet))
            return;
            
        $row = $resultSet->current();
        $exc->setDate($row->website_event_date)
            ->setUserId($row->user_id)
            ->setUserLogin($row->login_id)
            ->setRemoteIp($row->remote_ip)
            ->setDescription($row->website_event_message)
            ->setDetails($row->website_event_description)
            ->setId($row->website_log_id);
        
    }
    
    
    
}