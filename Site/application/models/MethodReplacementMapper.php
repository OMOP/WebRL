<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    25 Dec 2010

    Class that maps method replacement entity with DB.

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
class Application_Model_MethodReplacementMapper
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
            $this->setDbTable('Application_Model_DbTable_MethodReplacement');
        }
        return $this->_dbTable;
    }

   public function save(Application_Model_MethodReplacement $method)
    {
        $data = array(
            'id'   => $method->getId(),
            'user_id' => $method->getUserId(),
            'name' => $method->getName(),
            'shared_flag' => $method->getSharedFlag(),
            'hoi_replacement' => $method->getHoiReplacement(),
            'doi_replacement' => $method->getDoiReplacement()
        );
		$id = $method->getId();
        if (!$id) {
            $this->getDbTable()->insert($data);
        } else {
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
    }

    public function find($id, Application_Model_MethodReplacement $methodReplacement)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return;
        }
        $row = $result->current();

        $methodReplacement->setId($row->id)
                  ->setUserId($row->user_id)
                  ->setName($row->name)
                  ->setSharedFlag($row->shared_flag)
                  ->setHoiReplacement($row->hoi_replacement)
                  ->setDoiReplacement($row->doi_replacement);
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_MethodReplacement();
            
            $entry->setId($row->id)
                  ->setUserId($row->user_id)
                  ->setName($row->name)
                  ->setSharedFlag($row->shared_flag)
                  ->setHoiReplacement($row->hoi_replacement)
                  ->setDoiReplacement($row->doi_replacement);
            $entries[] = $entry;
        }
        return $entries;
    }

    public function getPaginatorAdapter($sort_column, $sort_dir) {
        $columns = array(
            'name',
            'login_id',
            'shared_flag',
            'id',
            'hoi_replacement',
            'doi_replacement'
        );

        if ($sort_dir != 'asc' )
            $sort_dir = 'desc';
        
        $current_user = Membership::get_current_user();
        $sort_column = $columns[$sort_column - 1];
        $select = $this->getDbTable()->select()
        	->from($this->getDbTable(), array(
            'id',
            'name',
            new Zend_Db_Expr('(SELECT `login_id` FROM `user_tbl` WHERE `user_id` = '
                . '`' . $this->getDbTable()->info('name') . '`.`user_id`) as `login_id`'),
            'shared_flag',
            'hoi_replacement',
            'doi_replacement'
        ))
        ->where('user_id = ? or shared_flag = 1', $current_user->user_id)
        ->order("$sort_column $sort_dir");

        return new Zend_Paginator_Adapter_DbSelect($select);
    }

    function getUniqueNameValidator($exclude_id = null) {

        if ($exclude_id) {
            $exclude = array(
                'field' => 'id',
                'value' => $exclude_id
            );
        }
        else
            $exclude = null;
        return new Zend_Validate_Db_NoRecordExists('method_replacement_tbl','name' ,$exclude, $this->getDbTable()->getAdapter());
    }



}

