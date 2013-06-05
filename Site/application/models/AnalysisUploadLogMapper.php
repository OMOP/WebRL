<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 Dec 2010

    Class that maps anlysis upload log entity with DB.

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

class Application_Model_AnalysisUploadLogMapper
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
            $this->setDbTable('Application_Model_DbTable_AnalysisUploadLog');
        }
        return $this->_dbTable;
    }

    public function save(Application_Model_AnalysisUploadLog $log)
    {
        $data = array(
            'user_login_id'   => $log->getUserId(),
            'upload_date' => $log->getUploadDate(),
            'filename' => $log->getFileName(),
            'method_id' => $log->getMethodId(),
            'method_abbr' => $log->getMethodAbbrv()
        );
        if (null === ($id = $log->getId())) {
            $this->getDbTable()->insert($data);
        } else {
            $this->getDbTable()->update($data, array('log_id = ?' => $id));
        }
    }

    public function getPaginatorAdapter($sortColumn, $sortDir)
    {
        $columns = array(
            'user_login_id',
            'method_abbr',
            'upload_date',
            'filename',
            'method_id'
        );

        if ($sortDir != 'asc' )
            $sortDir = 'desc';
        $sortColumn = $columns[$sortColumn - 1];
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
               ->from(array('a' => 'analysis_upload_log'), $columns)
               ->join(array('u' => 'user_tbl'), 'a.user_login_id = u.login_id', array('user_id'))
               ->order("$sortColumn $sortDir");
        return new Zend_Paginator_Adapter_DbSelect($select);
    }
        

}