<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    23 December 2010

    Model of view of run results data loaded to Oracle database.

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

/**
 * @todo it seems logical to inherit this from Application_Model_Abstract
 */
class Application_Model_RunResultsOracle extends Zend_Db_Table
{
    protected $_table;

    private $_datasetTypes;
    private $_methods;

    public function getTable()
    {
        if (null === $this->_table) {
            $this->_table = new Application_Model_DbTable_RunResultsUploadingLog();
        }
        return $this->_table;
    }

    public function fetchSearchResults($sortOrder, $sortDirection, $runSeparator = '|')
    {
        $result = $this->getTable()->fetchSearchResults($sortOrder, $sortDirection, $runSeparator = '|');
        return $result;
    }

    /**
     * Dataset Types getter function
     * @return array dataset types
     */
    public function getDatasetTypes()
    {
        if (!$this->_datasetTypes) {
            $sourceModel = new Application_Model_DbTable_Source();
            $this->_datasetTypes = $sourceModel->getSources();
        }
        return $this->_datasetTypes;
    }

    /**
     * Get the results for specific user
     * @param array List additional parameters
     * @return array list of uploaded results
     */
    public function getMethodResults($params = array())
    {
        $results = array();

        $methodResultsModel = new Application_Model_DbTable_ExperimentResults();
        return $methodResultsModel->getResults($this->getMethods(), $params);
    }

    /**
     * Get OSCAR results for specific user
     * @return array list of uploaded results
     */
    public function getOscarResults()
    {
        $results = array();

        $methodResultsModel = new Application_Model_DbTable_OscarResults();
        return $methodResultsModel->getResults();
    }

    /**
     * Retrieves the list of existing methods
     * @return array list of existing methods
     */
    public function getMethods()
    {
        if ($this->_methods) {
            return $this->_methods;
        }

        $methodsMapper = new Application_Model_MethodMapper();
        $methods = $methodsMapper->fetchPairs();
        $this->_methods = array();
        foreach ($methods as $methodName) {
            $this->_methods[$methodName] = array();
        }

        return $this->_methods;
    }

    public function getResultFile($analysisId, $datasetId, $experimentId)
    {
        $experimentResultsModel = new Application_Model_DbTable_ExperimentResults();
        $results = $experimentResultsModel->getResultsByAnalysis($analysisId, $datasetId, $experimentId);
        if (!$results) {
            throw new Exception('There are no results for this analysis.');
        }

        $content = '';
        $isFirstRecord = true;
        foreach ($results as $result) {
            if (!$isFirstRecord) {
                $content .= PHP_EOL;
            } else {
                $isFirstRecord = false;
            }
            $data = array(
                $result['DRUG_ID'],
                $result['CONDITION_ID'],
                $result['SCORE']
            );
            for ($i = 1; $i <= 5; $i++) {
                $data[] = $result['SUPPL_SCORE'.$i];
            }
            $content .= implode(',', $data);
        }
        $headers = "DRUG_ID,CONDITION_ID,SCORE,SUPPL_SCORE1,SUPPL_SCORE2,SUPPL_SCORE3,SUPPL_SCORE4,SUPPL_SCORE5\n";
        $content = $headers . $content;
        return $content;
    }
    
    /*
     * Getting Oracle experiment results data
     * @parameters array List parameters
     * @return array
     */
    public function getOracleResults($parameters = array()) {
        $result = array();
        $experimentResultsModel = new Application_Model_DbTable_ExperimentResultsSummary();
        $experimentResults = $experimentResultsModel->getExperimentResults($parameters);
        
        $helper = new Application_View_Helper_DateFormat();
            
        foreach ($experimentResults as $row) {
            $row['OUTPUT_FILE_NAME'] = str_replace('<Dataset>', $parameters['source_name'], $row['OUTPUT_FILE_NAME']);
            if (isset($row['SUPPLEMENT1_FILENAME'])) 
                $row['SUPPLEMENT1_FILENAME'] = str_replace('<Dataset>', $parameters['source_name'], $row['SUPPLEMENT1_FILENAME']);
            $row['AMOUNT_FORMATTED'] = number_format($row['AMOUNT']);
            $row['ADD_DATE'] = $helper->dateFormat($row['ADD_DATE']);
            $result[] = $row;
        }
        
        return $result;
    }
    
    public function getSuppl1File($analysisId, $datasetId, $experimentId) {
        $experimentResultsModel = new Application_Model_DbTable_ExperimentResults();
        $results = $experimentResultsModel->getSupplement1($analysisId, $datasetId, $experimentId);
        if (!$results) {
            throw new Exception('There are no results for this analysis.');
        }
        foreach ($results as $result) {
            if (!$isFirstRecord) {
                $content .= PHP_EOL;
            } else {
                $isFirstRecord = false;
            }
            $data = array(
                $result['DRUG_CONCEPT_ID'],
                $result['CONDITION_CONCEPT_ID'],
                $result['NUMBER_OF_PERSONS'],
                $result['EXPOSED_EVENTS'],
                $result['EXPOSED_TIME'],
                $result['UNEXPOSED_EVENTS'],
                $result['UNEXPOSED_TIME']
            );
            $content .= implode(',', $data);
        }
        $content .= PHP_EOL;
        $headers = "DRUG_CONCEPT_ID,CONDITION_CONCEPT_ID,NUMBER_OF_PERSONS,EXPOSED_EVENTS,EXPOSED_TIME,UNEXPOSED_EVENTS,UNEXPOSED_TIME";
        $content = $headers . $content;
        return $content;        
    }
    
    public function getSuppl2File($analysisId, $datasetId, $experimentId) {
        $experimentResultsModel = new Application_Model_DbTable_ExperimentResults();
        $results = $experimentResultsModel->getSupplement2($analysisId, $datasetId, $experimentId);
        if (!$results) {
            throw new Exception('There are no results for this analysis.');
        }
        foreach ($results as $result) {
            if (!$isFirstRecord) {
                $content .= PHP_EOL;
            } else {
                $isFirstRecord = false;
            }
            $data = array(
                $result['DRUG_CONCEPT_ID'],
                $result['CONDITION_CONCEPT_ID'],
                $result['SCORE'],
                $result['STANDARD_ERROR'],
                $result['BS_MEAN'],
                $result['BS_LOWER'],
                $result['BS_UPPER'],
                $result['BS_PROB0']
            );
            $content .= implode(',', $data);
        }
        $content .= PHP_EOL;
        $headers = "DRUG_CONCEPT_ID,CONDITION_CONCEPT_ID,SCORE,STANDARD_ERROR,BS_MEAN,BS_LOWER,BS_UPPER,BS_PROB0";
        $content = $headers . $content;
        return $content;        
    }    
}
