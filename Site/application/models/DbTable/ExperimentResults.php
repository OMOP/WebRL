<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 Dec 2010

    Zend_Db_Table class for EXPERIMENT_RESULTS (Oracle) table.

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
class Application_Model_DbTable_ExperimentResults extends Zend_Db_Table_Abstract
{

    protected $_name = 'EXPERIMENT_RESULTS';

    protected function _setupDatabaseAdapter()
    {
        $this->_db = Zend_Registry::get('oracle_adapter');
    }
    
    /**
     * 
     * Getting files names with results of running (experiment, dataset, method) 
     * @param int $experimentId
     * @param int $datasetId
     * @param int $methodId
     * @return array
     */
    public function getRuns($experimentId, $datasetId, $methodId) 
    {
        $runs = array();

        $query = 'select distinct A.OUTPUT_FILE_NAME
            from EXPERIMENT_RESULTS ER
            inner join "ANALYSIS_REF" "A" ON ER.ANALYSIS_ID = A.ANALYSIS_ID
            WHERE ER.EXPERIMENT_ID = ' . $experimentId . ' AND ER.METHOD_ID = ' . $methodId . ' AND SOURCE_ID = ' . $datasetId;
        try {
            $result = $this->_db->fetchAll($query);
        } catch (Exception $e) {
            return $runs;
        }

        foreach ($result as $run) {
            $runs[] = $run['OUTPUT_FILE_NAME'];
        }
        return $runs;
    }
    
    public function getResults($methods, $params = array())
    {
        $emptyDate = '<No Date>';

        $monthList = array(
            'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN',
            'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC',
        );
        $query = '
            SELECT  ER.METHOD_ID,
                    M.METHOD_ABBR,
                    S.SOURCE_ABBR,
                    TO_CHAR(Max(DB_LOAD_DATE), \'YYYY-MM-DD\') AS ADD_DATE,
                    (SELECT count(*) 
                    FROM    ANALYSIS_REF    ar 
                    WHERE   ar.METHOD_ID    = ER.METHOD_ID) AS TOTAL_AMOUNT,
                    count(*) LOADED_AMOUNT
            FROM    EXPERIMENT_RESULTS_SUMMARY ER, 
                    METHOD_REF              M, 
                    SOURCE_REF              S
            WHERE   ER.METHOD_ID    = M.METHOD_ID
                AND ER.SOURCE_ID    = S.SOURCE_ID '
            . ((isset($params['experimentId'])) ? ' AND ER.EXPERIMENT_ID = ' . $params['experimentId'] : '')
            . ' GROUP BY ER.METHOD_ID, 
                     M.METHOD_ABBR, 
                     S.SOURCE_ABBR
        ';
        $results = $this->_db->fetchAll($query);
        
        foreach ($results as $row) {
            /**
             * Parse date components
             */
            if (!$row['ADD_DATE']) {
                $row['ADDED_I'] = 0;
                $row['ADD_DATE'] = $emptyDate;
            } else {
                list($d, $m, $y) = explode('-', $row['ADD_DATE']);
                $m = array_search($m, $monthList);
                $row['ADDED_I'] = checkdate($m, $d, $y) ? mktime(0, 0, 0, $m, $d, $y) : 0;
            }
            
            $methodAbbrev = $row['METHOD_ABBR'];
            $sourceAbbrev = $row['SOURCE_ABBR'];
            
            if (!isset ($methods[$methodAbbrev])) {
                $methods[$methodAbbrev] = array();
            }
            $methodData = $methods[$methodAbbrev]; 
            if (!isset ($methodData[$sourceAbbrev])) {
                $methodData[$sourceAbbrev] = array(
                    'load_complete' => true
                );
            }
            $sourceData = $methodData[$sourceAbbrev];
            
            if (!isset($sourceData ['ADDED_I']) || !$sourceData ['ADDED_I'] || $row['ADDED_I'] > $sourceData ['ADDED_I']) {
                $sourceData ['ADD_DATE'] = $row['ADD_DATE'] === '' ? "<No Date>" : $row['ADD_DATE'];
                $sourceData ['ADDED_I'] = $row['ADDED_I'];
            }

            /**
             * Check that files were completely uploaded
             * Also check by name, not by amount
             */
            if ($row ['TOTAL_AMOUNT'] > $row['LOADED_AMOUNT']) {
                $sourceData ['load_complete'] = false;
            }
            $methodData[$sourceAbbrev] = $sourceData;
            $methods[$methodAbbrev] = $methodData;
        }
        
        return $methods;
    }
    
    /**
     * Execute arbitrary query in the current database
     * @param string sql query
     */
    public function query($sql)
    {
        $this->_db->query($sql);
    }
    
    public function getResultsByAnalysis($analysis, $datasetId, $experimentId)
    {
        $select = $this->_db
                       ->select()
                       ->from($this->_name)
                       ->where('ANALYSIS_ID = ? ', $analysis)
                       ->where('SOURCE_ID = ?', $datasetId)
                       ->where('EXPERIMENT_ID = ?', $experimentId);
        return $this->_db->fetchAll($select);
    } 
    
    
    public function getSupplement1($analysis, $datasetId, $experimentId) {
        $sel1 = $this->_db
                       ->select()
                       ->from('ANALYSIS_REF', array('SUPPLEMENTAL1_ID'))
                       ->where('ANALYSIS_ID = ? ', $analysis);
        $select = $this->_db
                       ->select()
                       ->from('SCCS_SUPPLEMENTAL_RESULTS_1')
                       ->where('SOURCE_ID = ?', $datasetId)
                       ->where('EXPERIMENT_ID = ?', $experimentId)
                       ->where('SUPPLEMENTAL1_ID = (?)', $sel1)
                       ->order('DRUG_CONCEPT_ID ASC');
        return $this->_db->fetchAll($select);
        
    }

    public function getSupplement2($analysis, $datasetId, $experimentId) {
        $select = $this->_db
                       ->select()
                       ->from('SCCS_SUPPLEMENTAL_RESULTS_2')
                       ->where('SOURCE_ID = ?', $datasetId)
                       ->where('EXPERIMENT_ID = ?', $experimentId)
                       ->where('ANALYSIS_ID = ?', $analysis)
                       ->order('CONDITION_CONCEPT_ID ASC');
        return $this->_db->fetchAll($select);
        
    }
    
    
    
}
