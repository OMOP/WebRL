<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 Dec 2010

    Class that maps Source (dataset) entity with DB.

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
class Application_Model_SourceMapper
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
            $this->setDbTable('Application_Model_DbTable_Source');
        }
        return $this->_dbTable;
    }

    public function fetchPairs() 
    {
        $table = $this->getDbTable();
        $select = $table->select();
        $select->from($table, array('SOURCE_ID', 'SOURCE_ABBR'))
            ->order('SOURCE_ABBR ASC');
        $rowset = $table->getAdapter()
                    ->fetchPairs($select);

        return $rowset;
    }
    
    public function find($id, Application_Model_Source $source)
    {
        $result = $this->getDbTable()->find($id);
        
        if (0 == count($result)) {
            return;
        }
        
        $row = $result->current();
        
        $source->setId($row->SOURCE_ID)
               ->setAbbrv($row->SOURCE_ABBR)
               ->setName($row->SOURCE_NAME)
        	   ->setSchemename($row->SCHEMA_NAME);
        
    }
    
    public function save(Application_Model_Source $source)
    {
        $data = array(
            'SOURCE_ABBR' => $source->getAbbrv(),
            'SOURCE_NAME' => $source->getName(),
            'SOURCE_ID' => $source->getId(),
            'SCHEMA_NAME' => $source->getSchemename()
        );
        if (null === $source->getOldId()) {
            $this->getDbTable()->insert($data);
        } else {
            $this->getDbTable()->update($data, array('SOURCE_ID = ?' => $source->getOldId()));
        }
        
    }
    
    public function getPaginatorAdapter($sort_column, $sort_dir)
    {
        $columns = array(
            'SOURCE_ID',
            'SOURCE_ABBR',
            'SOURCE_NAME',
        	'SCHEMA_NAME');

        if ($sort_dir != 'asc' )
            $sort_dir = 'desc';
        
        $sort_column = $columns[$sort_column - 1];
        $select = $this->getDbTable()->select()
            ->from($this->getDbTable(), $columns)
            ->order("$sort_column $sort_dir");

        return new Zend_Paginator_Adapter_DbSelect($select);
    }
    
    function getUniqueAbbrValidator($exclude_id = null)
    {

        if ($exclude_id) {
            $exclude = array(
                'field' => 'SOURCE_ID',
                'value' => $exclude_id
            );
        }
        else
            $exclude = null;
        return new Zend_Validate_Db_NoRecordExists('SOURCE_REF','SOURCE_ABBR' ,$exclude, $this->getDbTable()->getAdapter());
    }
    
    function getUniqueIdValidator($exclude_id = null)
    {

        if ($exclude_id) {
            $exclude = array(
                'field' => 'SOURCE_ID',
                'value' => $exclude_id
            );
        }
        else
            $exclude = null;
        return new Zend_Validate_Db_NoRecordExists('SOURCE_REF','SOURCE_ID' ,$exclude, $this->getDbTable()->getAdapter());
    }

    function getUniqueNameValidator($exclude_id = null)
    {

        if ($exclude_id) {
            $exclude = array(
                'field' => 'SOURCE_ID',
                'value' => $exclude_id
            );
        }
        else
            $exclude = null;
        return new Zend_Validate_Db_NoRecordExists('SOURCE_REF','SOURCE_NAME' ,$exclude, $this->getDbTable()->getAdapter());
    }		

    public function findByAbbr($abbr, Application_Model_Source $source) {
        $select = $this->getDbTable()->select();
        $select->where('SOURCE_ABBR = ?', $abbr);

        $row = $this->getDbTable()->fetchRow($select);
        if ($row) {
            $source->setId($row->SOURCE_ID)
                   ->setAbbrv($row->SOURCE_ABBR)
                   ->setName($row->SOURCE_NAME)
            	   ->setSchemename($row->SCHEMA_NAME);
        }
    }
    
}

