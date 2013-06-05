<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 Dec 2010

    Class that maps Experiment entity with DB.

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
class Application_Model_ExperimentMapper
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
            $this->setDbTable('Application_Model_DbTable_Experiment');
        }
        return $this->_dbTable;
    }
    
    public function findByAbbr($name, Application_Model_Experiment $experiment) {
        $select = $this->getDbTable()->select();
        $select->where('EXPERIMENT_NAME = ?', $name);

        $row = $this->getDbTable()->fetchRow($select);
        if ($row) {
            $experiment->setId($row->EXPERIMENT_ID)
                   ->setName($row->EXPERIMENT_NAME)
                   ->setDescr($row->EXPERIMENT_DESCR)
                   ->setDirectoryPattern($row->DIRECTORY_PATTERN)
                   ->setSourceDrugEra($row->SOURCE_DRUG_ERA)
                   ->setSourceConditionEra($row->SOURCE_CONDITION_ERA);
        }
    }
    
    public function find($id, Application_Model_Experiment $experiment)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return;
        }
        $row = $result->current();
        $experiment->setId($row->EXPERIMENT_ID)
               ->setName($row->EXPERIMENT_NAME)
               ->setDescr($row->EXPERIMENT_DESCR)
               ->setDirectoryPattern($row->DIRECTORY_PATTERN)
               ->setSourceDrugEra($row->SOURCE_DRUG_ERA)
               ->setSourceConditionEra($row->SOURCE_CONDITION_ERA);
    }
    
    public function save(Application_Model_Experiment $exp) {
        
        $data = array(
            'EXPERIMENT_ID' => $exp->getId(),
            'EXPERIMENT_NAME' => $exp->getName(),
            'EXPERIMENT_DESCR' => $exp->getDescr(),
            'DIRECTORY_PATTERN' => $exp->getDirectoryPattern(),
            'SOURCE_DRUG_ERA' => $exp->getSourceDrugEra(),
            'SOURCE_CONDITION_ERA' => $exp->getSourceConditionEra()
        );
        
        if (null === $exp->getOldId()) {
            $this->getDbTable()->insert($data);
        } else {
            $this->getDbTable()->update($data, array('EXPERIMENT_ID = ?' => $exp->getOldId()));
        }
        
    }
    
    public function getPaginatorAdapter($sort_column, $sort_dir)
    {
        $columns = array(
            'EXPERIMENT_ID',
            'EXPERIMENT_NAME',
            'EXPERIMENT_DESCR',
        );

        if ($sort_dir != 'asc' )
            $sort_dir = 'desc';
        if ($sort_column < 1) 
            $sort_column - 1;
        if ($sort_column > 3)
            $sort_column = 3;
        $sort_column = $columns[$sort_column - 1];
        $select = $this->getDbTable()->select()
            ->from($this->getDbTable(), $columns)
            ->order("$sort_column $sort_dir");

        return new Zend_Paginator_Adapter_DbSelect($select);
    }    
    
    function getUniqueIdValidator($exclude_id = null)
    {

        if ($exclude_id) {
            $exclude = array(
                'field' => 'EXPERIMENT_ID',
                'value' => $exclude_id
            );
        }
        else
            $exclude = null;
        return new Zend_Validate_Db_NoRecordExists('EXPERIMENT_REF','EXPERIMENT_ID' ,$exclude, $this->getDbTable()->getAdapter());
    }      

    function getUniqueNameValidator($exclude_id = null)
    {

        if ($exclude_id) {
            $exclude = array(
                'field' => 'EXPERIMENT_ID',
                'value' => $exclude_id
            );
        }
        else
            $exclude = null;
        return new Zend_Validate_Db_NoRecordExists('EXPERIMENT_REF','EXPERIMENT_NAME' ,$exclude, $this->getDbTable()->getAdapter());
    }
    
    
    function getUniquePatternValidator($exclude_id = null)
    {

        if ($exclude_id) {
            $exclude = array(
                'field' => 'EXPERIMENT_ID',
                'value' => $exclude_id
            );
        }
        else
            $exclude = null;
        return new Application_Validator_PatternIntersects('EXPERIMENT_REF','DIRECTORY_PATTERN' ,$exclude, $this->getDbTable()->getAdapter());
    }     
    
    
}