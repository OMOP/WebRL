<?php

/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    15-Feb-2011

    Mapper for UserConectLog

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
class Application_Model_UserConnectLogMapper
{
    
    protected $_dbTable;
    
    protected $_mode;
    
    public function __construct($mode)
    {
        $this->_mode = $mode;
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
            $this->setDbTable('Application_Model_DbTable_UserConnectLog');
        }
        return $this->_dbTable;
    }
    
    public function getPaginatorAdapter($sort_column, $sort_dir)
    {
        if ($this->_mode == 'instance') {
            $select = $this->getInstanceSelect($sort_column, $sort_dir);
        } else
            $select = $this->getWebSelect($sort_column, $sort_dir);
         
        return new Zend_Paginator_Adapter_DbSelect($select);
    }
    
    private function getInstanceSelect($sort_column, $sort_dir)
    {
        $columns = array(
            'date' => 'ucl.connect_date',
            'user' => 'u.login_id',
            'ip' => 'ucl.remote_ip',
            'instance' => 'i.assigned_name',
            'instanceUser' => 'ur.login_id',
            'status' => 'ucl.status_flag',
            'userName' => 'ucl.user_name',
        );
        
        $sortCols = array_values($columns);
        
        if ($sort_dir != 'asc' )
            $sort_dir = 'desc';
        if ($sort_column >= 0 && $sort_column < 6)
            $sort_column = $sortCols[$sort_column - 1];
        else
            $sort_column = $sortCols[0];
        $select = $this->getDbTable()->getAdapter()->select()
            ->from(array('ucl' => 'user_connect_log'), $columns)
            ->joinLeft(
                array('u' => 'user_tbl'),
                'u.user_id = ucl.user_id',
                array()
            )
            ->joinLeft(
                array('i' => 'instance_tbl'),
                'i.instance_id = ucl.instance_id',
                array()
            )
            ->joinLeft(
                array('ir' => 'instance_request_tbl'),
                'i.instance_request_id = ir.instance_request_id',
                array()
            )
            ->joinLeft(
                array('ur' => 'user_tbl'),
                'ur.user_id = ir.user_id',
                array()
            )
            ->order("$sort_column $sort_dir");
        $select->where('ucl.instance_id IS NOT NULL');
        
        return $select;
        
    }
    
    private function getWebSelect($sort_column, $sort_dir)
    {
        $columns = array(
            'date' => 'ucl.connect_date',
            'user' => 'u.login_id',
            'ip' => 'ucl.remote_ip',
            'status' => 'ucl.status_flag',
            'browserType' => 'ucl.browser_type',
            'os' => 'ucl.os'
        );
        
        $sortCols = array_values($columns);
        
        if ($sort_dir != 'asc' )
            $sort_dir = 'desc';
        if ($sort_column >= 0 && $sort_column < 7)
            $sort_column = $sortCols[$sort_column - 1];
        else
            $sort_column = $sortCols[0];
        $select = $this->getDbTable()->getAdapter()->select()
            ->from(array('ucl' => 'user_connect_log'), $columns)
            ->joinLeft(
                array('u' => 'user_tbl'),
                'u.user_id = ucl.user_id',
                array()
            )
            ->order("$sort_column $sort_dir");
        $select->where('ucl.instance_id IS NULL AND ucl.browser_type IS NOT NULL');
        return $select;
        
        
    }
    
    
}