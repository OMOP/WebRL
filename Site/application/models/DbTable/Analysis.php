<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 Dec 2010

    Zend_Db_Table class for ANALYSIS_REF (Oracle) table.

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
class Application_Model_DbTable_Analysis extends Zend_Db_Table_Abstract
{

    protected $_name = 'ANALYSIS_REF';
    protected $_primary = 'ANALYSIS_ID';
    protected $_sequence = true;

    protected $_datasetTemplates = array('NAME', 'NULL');

    protected function _setupDatabaseAdapter()
    {
        $this->_db = Zend_Registry::get('oracle_adapter');
    }

    public function findByMethod (array $methods)
    {
        if (!empty ($methods)) {
            $condition = array();
            foreach ($methods as $methodName=>$mr) {
                $condition[] = $this->_db->quote($methodName);
            }
            $condition = ' METHOD_ABBR in ('.implode(',', $condition).') ';
        }
        $select = $this->_db->select()->from($this->_name);
        if (!empty ($methods)) {
            $select = $select->where($condition);// TODO think of replacement with method id
        }
        return $this->_db->fetchAll($select);
    }

    public function findByRuns (array $runs)
    {
        if (!empty ($runs)) {
            $condition = array();
            foreach ($runs as $mr) {
                // spliting by methods
                foreach ($mr as $dr) {
                    // spliting by datasets
                    foreach ($dr as $run) {
                        $qname = $this->_db->quote($run->getName());
                        $qmethod = $this->_db->quote($run->getMethod());
                        $condition[] = "RUN_NAME = ".$qname." AND METHOD_ABBR = ".$qmethod;
                    }
                }
            }
            $condition = ' ('.implode(') OR (', $condition).') ';
        }
        $select = $this->_db->select()->from($this->_name);
        if (!empty ($runs)) {
            $select = $select->where($condition);// TODO think of replacement with method id
        }
        return $this->_db->fetchAll($select);
    }

    public function getSampleRuns(array $methods = null)
    {
        $result = array();
        $query = 'SELECT COUNT(*) AS TOTAL_AMOUNT, METHOD_ABBR, RUN_NAME
FROM ANALYSIS_REF
group by METHOD_ABBR, RUN_NAME';
        /*$columns = array('METHOD_ABBR', 'RUN_NAME', 'COUNT(*) AS TOTAL_AMOUNT');
        $select = $this->_db->select()
                       ->from($this->_name, $columns)
                       ->group('METHOD_ABBR')
                       ->group('RUN_NAME');*/
        try {
            $runs = $this->_db->fetchAll($query);
        } catch (Exception $e) {
            $runs = array();
        }
        
        foreach ($runs as $run) {
            if ($methods && !in_array($run['METHOD_ABBR'], $methods)) {
                echo $run['METHOD_ABBR'] .  '<br>';
                continue;
            }
            
            if (!isset ($result[$run['METHOD_ABBR']])) {
                $result[$run['METHOD_ABBR']] = array();
            }
            $result[$run['METHOD_ABBR']] [$run['RUN_NAME']] = $run['TOTAL_AMOUNT'];
        }
        return $result;
    }

    public function fetchByMethod($id, $sortCol, $sortDir, $where = false)
    {
        $select = $this->select()
                       ->from($this);
        if ($where) {
            $select = $select->where('METHOD_ID = ? and ' . $where, array($id));
        } else {
            $select = $select->where('METHOD_ID = ? ', array($id));
        }
        $select = $select->order("$sortCol $sortDir");
        return $this->fetchAll($select);
    }
    
    //Warning! Function can not be used. Field run_name is not exist in database.
    public function findByMethodRunFile($method, $run, $filename)
    {
        $where = array();
        $where[] = $this->getAdapter()->quoteInto('METHOD_ABBR = ?', $method);
        $where[] = $this->getAdapter()->quoteInto('RUN_NAME = ?', $run);
        $where[] = $this->getAdapter()->quoteInto('OUTPUT_FILE_NAME = ?', $filename);
        return $this->fetchRow("METHOD_ABBR = 'USCCS' AND RUN_NAME = 'USCCS_HOI_RUN_1'");
    }

    /**
     * Get dataset renaming template specific for this method
     * @param string method to define template for
     * @return string renaming template or null on error (not found)
     */
    public function getFileRenameTemplate ($methodName)
    {
        $analyses = $this->findByMethod(array($methodName => $methodName));
        $analysis = $analyses[0];
        $sampleFile = $analysis['OUTPUT_FILE_NAME'];
        $result = null;
        foreach ($this->_datasetTemplates as $tpl) {
            if (false !== strpos($sampleFile, $tpl)) {
                $result = $tpl;
                break;
            }
        }
        return $result;
    }
}