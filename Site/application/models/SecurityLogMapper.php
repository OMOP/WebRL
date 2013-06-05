<?php

/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    11-Feb-2011

    Mapper for security log

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
class Application_Model_SecurityLogMapper
{
    protected $_dbTable;
    
    protected $_categories;
    
    const ISSUE_TYPE_LOGIN_SUCCESS = 1;
    const ISSUE_TYPE_LOGIN_SSH_TRANSFER = 2;
    const ISSUE_TYPE_LOGIN_MORE_THAN_3_LOGINS = 3;
    const ISSUE_TYPE_LOGIN_SITE_DOWN = 4;
    const ISSUE_TYPE_PASSWORD_CHANGED = 5;
    const ISSUE_TYPE_USER_INFORMATION_CHANGED = 6;
    const ISSUE_TYPE_USER_CREATED = 7;
    const ISSUE_TYPE_SITE_CONFIGURATION_CHANGED = 8;
    const ISSUE_TYPE_INSTANCE_SSH_TRANSFER = 9;
    
    public function __construct($categories = null)
    {
        if (is_array($categories))
            $this->_categories = $categories;
        else
            if ($categories != null)
                $this->_categories = array($categories);
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
            $this->setDbTable('Application_Model_DbTable_SecurityLog');
        }
        return $this->_dbTable;
    }
    
    public function getPaginatorAdapter($sort_column, $sort_dir)
    {
        $columns = array(
            'date' => 'sl.security_log_date',
            'issueType' => 'it.security_issue_type_description',
            'user' => 'u.login_id',
            'ip' => 'sl.remote_ip',
            'instance' => 'i.assigned_name',
            'message' => 'sl.action_message',
            'id' => 'sl.security_log_id'
        );
        
        $sortCols = array_values($columns);
        
        if ($sort_dir != 'asc' )
            $sort_dir = 'desc';
        if ($sort_column >= 0 && $sort_column < 6)
            $sort_column = $sortCols[$sort_column - 1];
        else
            $sort_column = $sortCols[0];
        $select = $this->getDbTable()->getAdapter()->select()
            ->from(array('sl' => 'security_log'), $columns)
            ->joinLeft(
                array('it' => 'security_issue_type_tbl'), 
                'sl.security_issue_type_id = it.security_issue_type_id',
                array()
            )
            ->joinLeft(
                array('u' => 'user_tbl'),
                'u.user_id = sl.user_id',
                array()
            )
            ->joinLeft(
                array('i' => 'instance_tbl'),
                'i.instance_id = sl.instance_id',
                array()
            )
            ->order("$sort_column $sort_dir");
        if ($this->_categories !== null) {
            $select->where('sl.security_issue_type_id IN (?)', $this->_categories);
        }
        return new Zend_Paginator_Adapter_DbSelect($select);
    }
    
    public function find($id, Application_Model_LogEvent $event)
    {
        $resultSet = $this->getDbTable()->find($id);
        
        if (count($resultSet) == 0)
            return;
        
        $row = $resultSet->current();
        
        $event->setId($row->security_log_id)
              ->setIssueType($row->security_issue_type_id)
              ->setIssueTypeDescription($row->security_issue_type_description)
              ->setUserId($row->user_id)
              ->setUserLoginId($row->login_id)
              ->setInstanceId($row->instance_id)
              ->setInstanceName($row->assigned_name)
              ->setMessage($row->action_message)
              ->setDate($row->security_log_date)
              ->setRemoteIp($row->remote_ip);
    }
    
}