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

final class Application_Model_Osim2Mapper
{
    protected $_summaryDbTable;
    protected $_sourceDbTable;

    /**
     * Set Zend_Db_Table which will be used for operations under 
     * OSIM2_ANALYSIS_SUMMARY table
     * 
     * @param Zend_Db_Table_Abstract|string $dbTable 
     * Instance of class Zend_Db_Table_Abstract or name of class that 
     * implement that interface and which will be used for 
     * operations under OSIM2_ANALYSIS_SUMMARY table.
     */
    public function setSummaryDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_summaryDbTable = $dbTable;
        return $this;
    }

    /**
     * Gets Zend_Db_Table which used for performing operations 
     * under OSIM2_ANALYSIS_SUMMARY table.
     *    
     * @return Zend_Db_Table which used for performing operations 
     * under OSIM2_ANALYSIS_SUMMARY table.
     */
    public function getSummaryDbTable()
    {
        if (null === $this->_summaryDbTable) {
            $this->setSummaryDbTable(
                new Application_Model_DbTable_Osim2AnalysisSummary()
            );
        }
        return $this->_summaryDbTable;
    }

    /**
     * Set Zend_Db_Table which will be used for operations under 
     * OSIM2_SOURCE_REF table
     * 
     * @param Zend_Db_Table_Abstract|string $dbTable 
     * Instance of class Zend_Db_Table_Abstract or name of class that 
     * implement that interface and which will be used for 
     * operations under OSIM2_SOURCE_REF table.
     */
    public function setSimulationSourceDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_sourceDbTable = $dbTable;
        return $this;
    }

    /**
     * Gets Zend_Db_Table which used for performing operations 
     * under OSIM2_SOURCE_REF table.
     *    
     * @return Zend_Db_Table which used for performing operations 
     * under OSIM2_SOURCE_REF table.
     */
    public function getSimulationSourceDbTable()
    {
        if (null === $this->_sourceDbTable) {
            $this->setSimulationSourceDbTable(
                new Application_Model_DbTable_Osim2SimulationSource()
            );
        }
        return $this->_sourceDbTable;
    }
    
    /**
     * Gets last unique identifier of Summary Set.
     * @return 
     * Last unique identifier of Summary Set; 0 if no sumary sets loaded.
     */
    public function lastSummaryId()
    {
        $summaryDbTable = $this->getSummaryDbTable();
        $select = $summaryDbTable->select()
            ->from($summaryDbTable, "MAX(ANALYSIS_SOURCE_ID) as LAST_ID");
        $lastId = $summaryDbTable->fetchRow($select);
        if ($lastId === null) {
            return 0;
        }
        return $lastId->LAST_ID;
    }

    /**
     * Saves Application_Model_Osim2AnalysisSummary to the underlying database.
     * 
     * @param Application_Model_Osim2AnalysisSummary $summary 
     * Object which should be saved to the underlying database.
     */
    public function saveSummary(
        Application_Model_Osim2AnalysisSummary $summary)
    {
        $data = $this->getSummaryData($summary);
        $id = $summary->getOldId();
        $summaryDbTable = $this->getSummaryDbTable(); 
        if (!$id) {
            return $summaryDbTable->insert($data);
        } else {
            return $summaryDbTable->update($data, 
                array('ANALYSIS_SOURCE_ID = ?' => $id)
            );
        }
    }

    /**
     * Inserts Application_Model_Osim2AnalysisSummary to the underlying 
     * database.
     * 
     * @param Application_Model_Osim2AnalysisSummary $summary 
     * Object which should be inserted to the underlying database.
     */
    public function insertSummary(
        Application_Model_Osim2AnalysisSummary $summary)
    {
        $data = $this->getSummaryData($summary);
        $summaryDbTable = $this->getSummaryDbTable(); 
        return $summaryDbTable->insert($data);
    }

    /**
     * Populates properties of object with data from datasetore
     * based on unique identifier of data in the datastore.
     * 
     * @param mixed $id
     * Unique identifier of object which should be retreived 
     * in the underlying datastore.
     * 
     * @param Application_Model_Osim2AnalysisSummary $summary
     * Object which properties will be populated from datastore.
     * 
     * @return boolean|Application_Model_Osim2AnalysisSummary
     * Return false if object with given unique identifier not 
     * found in the database; or $summary object with udpated 
     * values of properties.
     */
    public function findSummary($id, 
        Application_Model_Osim2AnalysisSummary $summary)
    {
        $columns = self::getFullSummaryColumns();
        $summaryDbTable = $this->getSummaryDbTable(); 
        $select = $summaryDbTable->select()
            ->from($summaryDbTable, $columns)
        ->where('ANALYSIS_SOURCE_ID = ?', $id);
        
        $row = $summaryDbTable->fetchRow($select);
        if (null === $row) {
            return false;
        }
        self::populateSummary($row, $summary);
        return $summary;
    }

    /**
     * Populates properties of object with data from datasetore
     * based on the object name in the datastore.
     * 
     * @param mixed $name
     * Name of object which should be retreived 
     * in the underlying datastore.
     * 
     * @param Application_Model_Osim2AnalysisSummary $summary
     * Object which properties will be populated from datastore.
     * 
     * @return boolean|Application_Model_Osim2AnalysisSummary
     * Return false if object with given name not 
     * found in the database; or $summary object with udpated 
     * values of properties.
     */
    public function findSummaryByName($name, 
        Application_Model_Osim2AnalysisSummary $summary)
    {
        $columns = self::getFullSummaryColumns();
        $summaryDbTable = $this->getSummaryDbTable(); 
        $select = $summaryDbTable->select()
            ->from($summaryDbTable, $columns)
        ->where('ANALYSIS_SOURCE_NAME = ?', $name);
        
        $row = $summaryDbTable->fetchRow($select);
        if (null === $row) {
            return false;
        }
        self::populateSummary($row, $summary);
        return $summary;
    }
    /**
     * Returns array of Application_Model_Osim2AnalysisSummary for all
     * objects that stored in the datastore.
     * 
     * @return array of Application_Model_Osim2AnalysisSummary
     * Array of Application_Model_Osim2AnalysisSummary which 
     * represent all objects in the underlying datastore.
     */
    public function fetchSummaryAll()
    {
        $summaryDbTable = $this->getSummaryDbTable(); 
        $resultSet = $summaryDbTable->fetchAll();
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_Osim2AnalysisSummary();
            self::populateSummary($row, $entry);
            $entries[] = $entry;
        }
        return $entries;
    }
    /**
     * Returns pairs which represent summary sets.
     * 
     * @return array of key value pairs which identifies
     * Summary sets.
     */
    public function fetchSummaryPairs()
    {
        $summaryDbTable = $this->getSummaryDbTable();
        $select = $summaryDbTable->select()
            ->from($summaryDbTable, array(
                'ANALYSIS_SOURCE_ID', 
                'ANALYSIS_SOURCE_NAME')
            )
            ->order('ANALYSIS_SOURCE_NAME ASC'); 
        $entries = $summaryDbTable
            ->getAdapter()
            ->fetchPairs($select);
        return $entries;
    }

    /**
     * Get instance of Zend_Paginator_Adapter_DbSelect
     * which will be used for pagination of Analysis Summary data.
     * 
     * @param string $sortColumnIndex
     * Internal index of columns which will be used for storing.
     *  
     * @param string $sortDirection
     * Sort direction. Could be one of values 'asc' or 'desc'.
     *  
     * @return Zend_Paginator_Adapter_DbSelect
     * Instance of Zend_Paginator_Adapter_DbSelect
     * which will be used for pagination.
     */
    public function getSummaryPaginatorAdapter($sortColumnIndex, 
        $sortDirection)
    {
        $columns = self::getFullSummaryColumns();

        if ($sortDirection != 'asc' )
            $sortDirection = 'desc';
        
        $sortColumn = $columns[$sortColumnIndex - 1];
        $summaryDbTable = $this->getSummaryDbTable(); 
        $select = $summaryDbTable->select()
            ->from($summaryDbTable, $columns)
        ->order("$sortColumn $sortDirection");

        return new Zend_Paginator_Adapter_DbSelect($select);
    }
    /**
     * Gets count of rows in the probabilities table that belongs 
     * to specific dataset.
     *  
     * @param int $analysisSourceId 
     * Unique identifier of Analysis Source set.  
     * @param string $tableName 
     * Name of probability table
     * @return 
     * Count of rows in the probabilities table that belongs 
     * to specific dataset.
     */
    public function countProbabilitiesTable($analysisSourceId, $tableName)
    {
        $db = self::getOsim2Adapter();
        $quotedTableName = $db->quoteIdentifier($tableName);
        if ($quotedTableName != '"'.$tableName.'"') {
            $message = "Invalid table name $tableName.$quotedTableName";
            throw new Exception($message);
        }
        $analysisSourceId = $db->quote($analysisSourceId);
        return $db->fetchOne("
SELECT  COUNT(*) 
FROM    $tableName 
WHERE   analysis_source_id  = $analysisSourceId");
    }
    /**
     * Gets data from probablilty table.
     *  
     * @param int $analysisSourceId Unique identifier of Analysis Source set.  
     * @param string $tableName Name of probability table
     * @return Array of rows from probability table.
     */
    public function getProbabilitiesTableData($analysisSourceId, $tableName)
    {
        $db = self::getOsim2Adapter();
        $quotedTableName = $db->quoteIdentifier($tableName);
        if ($quotedTableName != '"'.$tableName.'"') {
            $message = "Invalid table name $tableName.$quotedTableName";
            throw new Exception($message);
        }
        $schema = self::getSummarySchema();
        if (!isset($schema[$tableName])) {
            $message = "Table $tableName does not belongs to schema.";
            throw new Exception($message);
        } 
        $columns = $schema[$tableName];
        $columnList = implode(',',$columns);
        $analysisSourceId = $db->quote($analysisSourceId);
        $db->setFetchMode(Zend_Db::FETCH_NUM);
        return $db->fetchAll("
SELECT  $columnList
FROM    $tableName 
WHERE   analysis_source_id  = $analysisSourceId");
    }
    
    /**
     * Gets pagination adapter for specific probablilty table.
     *  
     * @param int $analysisSourceId 
     * Unique identifier of Analysis Source set.  
     * @param string $tableName 
     * Name of probability table
     * @param int $sortColumnIndex 
     * Index of column which will be used for sorting.
     * @param string $sortDirection 
     * Sorting direction  
     * @return Array of rows from probability table.
     */
    public function getProbabilitiesTablePaginatorAdapter($analysisSourceId, 
        $tableName, $sortColumnIndex, $sortDirection)
    {
        $db = self::getOsim2Adapter();
        $schema = self::getSummarySchema();
        if (!isset($schema[$tableName])) {
            throw new Exception("Table $tableName does not belongs to schema.");
        } 
        $columns = $schema[$tableName];
        $sortColumn = $columns[$sortColumnIndex];

        $db->setFetchMode(Zend_Db::FETCH_NUM);
        $select = $db->select()
            ->from($tableName)
            ->where("analysis_source_id = ?", $analysisSourceId)
            ->order("$sortColumn $sortDirection");
        return new Zend_Paginator_Adapter_DbSelect($select);
    }
    
    /**
     * Load probability table to the database.
     * 
     * @param $analysisSourceId 
     * Summary Set to which belongs probability table 
     * @param $tableName 
     * Name of table which should be loaded.
     * @param $fileName 
     * File from which data should be loaded.
     */
    public function loadProbabilityTable($analysisSourceId, $tableName, 
        $fileName)
    {
        require_once('CsvParser.php');
        $tableName = strtoupper($tableName);
        $db = self::getOsim2Adapter();
        $analysisSourceId = intval($analysisSourceId);
        $parser = new CsvParser();
        if (!$parser->load($fileName)) {
            throw new Exception("File $fileName is not CSV file");
        }
        if (!$parser->isSymmetric()) {
            $message = "Number of colums does not match "
                . "with length of some rows in the file $fileName.";
            throw new Exception($message);
        }
        $headers = $parser->getHeaders();
        $db->beginTransaction();
        
        try
        {
            $db->delete($tableName, "analysis_source_id  = $analysisSourceId");
            $count = $parser->countRows();
            for ($i=0; $i<$count; $i++) {
                $rowData = $parser->getRow($i);
                $tableData = array(
                    'ANALYSIS_SOURCE_ID' => $analysisSourceId,
                );
                for ($j=0; $j<count($headers); $j++) {
                    $header = strtoupper($headers[$j]);
                    $tableData[$header] = $rowData[$j]; 
                }
                $db->insert($tableName, $tableData);
            }   
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
    /**
     * Change value of Analysis Summary identifier.
     * 
     * @param number $oldId 
     * Previuos value of Analysis Summary identifier. 
     * @param number $newId
     * New value of Analysis Summary identifier.
     */
    public function changeSummaryId($oldId, $newId)
    {
        $db = self::getOsim2Adapter();
        $db->beginTransaction();
        
        $summaryDbTable = $this->getSummaryDbTable();
        $data = array(
            'ANALYSIS_SOURCE_ID' => $newId
        );
        $filter = array(
            'ANALYSIS_SOURCE_ID' => $oldId
        );
        $summaryDbTable->update($data, $filter);
        try
        {
            $schema = self::getSummarySchema();
            $db = Zend_Registry::get('oracle_adapter');
            foreach($schema as $tableName => $columns) {
                $db->update($tableName, $data, $filter);
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Saves Application_Model_Osim2SimulationSource to the underlying database.
     * 
     * @param Application_Model_Osim2SimulationSource $source 
     * Object which should be saved to the underlying database.
     */
    public function saveSimulationSource(
        Application_Model_Osim2SimulationSource $source)
    {
        $data = $this->getSimulationSourceData($source);
        $id = $source->getOldId();
        $sourceDbTable = $this->getSimulationSourceDbTable(); 
        if (!$id) {
            return $sourceDbTable->insert($data);
        } else {
            return $sourceDbTable->update($data, 
                array('OSIM2_SOURCE_ID = ?' => $id));
        }
    }

    /**
     * Populates properties of object with data from datasetore
     * based on unique identifier of data in the datastore.
     * 
     * @param mixed $id
     * Unique identifier of object which should be retreived 
     * in the underlying datastore.
     * 
     * @param Application_Model_Osim2SimulationSource $source
     * Object which properties will be populated from datastore.
     * 
     * @return boolean|Application_Model_Osim2SimulationSource
     * Return false if object with given unique identifier not 
     * found in the database; or $summary object with udpated 
     * values of properties.
     */
    public function findSimulationSource($id, 
        Application_Model_Osim2SimulationSource $source)
    {
        $columns = self::getFullSimulationSourceColumns();
        $sourceDbTable = $this->getSimulationSourceDbTable(); 
        $select = $sourceDbTable->select()
            ->from($sourceDbTable, $columns)
        ->where('OSIM2_SOURCE_ID = ?', $id);
        
        $row = $sourceDbTable->fetchRow($select);
        if (null === $row) {
            return false;
        }
        self::populateSimulationSource($row, $source);
        return $source;
    }

    /**
     * Populates properties of object with data from datasetore
     * based on the object name in the datastore.
     * 
     * @param mixed $name
     * Name of object which should be retreived 
     * in the underlying datastore.
     * 
     * @param Application_Model_Osim2SimulationSource $source
     * Object which properties will be populated from datastore.
     * 
     * @return boolean|Application_Model_Osim2SimulationSource
     * Return false if object with given name not 
     * found in the database; or $summary object with udpated 
     * values of properties.
     */
    public function findSimulationSourceByName($name, 
        Application_Model_Osim2SimulationSource $source)
    {
        $columns = self::getFullSimulationSourceColumns();
        $sourceDbTable = $this->getSimulationSourceDbTable(); 
        $select = $sourceDbTable->select()
            ->from($sourceDbTable, $columns)
        ->where('OSIM2_SOURCE_NAME = ?', $name);
        
        $row = $sourceDbTable->fetchRow($select);
        if (null === $row) {
            return false;
        }
        self::populateSimulationSource($row, $source);
        return $source;
    }

    /**
     * Get instance of Zend_Paginator_Adapter_DbSelect
     * which will be used for pagination of OSIM2 dataset data.
     * 
     * @param string $sortColumnIndex
     * Internal index of columns which will be used for storing.
     *  
     * @param string $sortDirection
     * Sort direction. Could be one of values 'asc' or 'desc'.
     *  
     * @return Zend_Paginator_Adapter_DbSelect
     * Instance of Zend_Paginator_Adapter_DbSelect
     * which will be used for pagination.
     */
    public function getSimulationSourcePaginatorAdapter($sortColumnIndex, 
        $sortDirection)
    {
        $columns = self::getFullSimulationSourceColumns();
        
        if ($sortDirection != 'asc' )
            $sortDirection = 'desc';
        
        $sortColumn = $columns[$sortColumnIndex - 1];
        $sourceDbTable = $this->getSimulationSourceDbTable(); 
        $select = $sourceDbTable->select()
            ->from($sourceDbTable, array('OSIM2_SOURCE_ID'))
        ->order("$sortColumn $sortDirection");

        return new Zend_Paginator_Adapter_DbSelect($select);
    }
    
    /**
     * Gets map which store information about schema to which should conform
     * uploaded CSV files. 
     * 
     * @return array
     * Map that hold name of table or file. In the value corresponding 
     * to key stored array of strings that represent list of columns in 
     * the schema.
     */
    public static function getSummarySchema()
    {
        $requiredFiles = array(
            'OSIM_SRC_DB_ATTRIBUTES' => array(
                'DB_MIN_DATE',
                'DB_MAX_DATE',
                'PERSONS_COUNT',
                'CONDITION_ERAS_COUNT',
                'DRUG_ERAS_COUNT',
                'CONDITION_OCCURRENCE_TYPE',
                'DRUG_EXPOSURE_TYPE'
            ),
            'OSIM_GENDER_PROBABILITY' => array(
                'GENDER_CONCEPT_ID',
                'ACCUMULATED_PROBABILITY'
            ),
            'OSIM_AGE_AT_OBS_PROBABILITY' => array(
                'GENDER_CONCEPT_ID',
                'AGE_AT_OBS',
                'ACCUMULATED_PROBABILITY'
            ),
            'OSIM_COND_COUNT_PROBABILITY' => array(
                'GENDER_CONCEPT_ID',
                'AGE_AT_OBS',
                'COND_ERA_COUNT',
                'COND_CONCEPT_COUNT',
                'ACCUMULATED_PROBABILITY'
            ),
            'OSIM_TIME_OBS_PROBABILITY' => array(
                'GENDER_CONCEPT_ID',
                'AGE_AT_OBS',
                'COND_COUNT_BUCKET',
                'TIME_OBSERVED',
                'ACCUMULATED_PROBABILITY'
            ),
            'OSIM_FIRST_COND_PROBABILITY' => array(
                'GENDER_CONCEPT_ID',
                'AGE_RANGE',
                'COND_COUNT_BUCKET',
                'TIME_REMAINING',
                'CONDITION1_CONCEPT_ID',
                'CONDITION2_CONCEPT_ID',
                'DELTA_DAYS',
                'ACCUMULATED_PROBABILITY'
            ),
            'OSIM_COND_ERA_COUNT_PROB' => array(
                'CONDITION_CONCEPT_ID',
                'COND_COUNT_BUCKET',
                'TIME_REMAINING',
                'COND_ERA_COUNT',
                'ACCUMULATED_PROBABILITY'
            ),
            'OSIM_COND_REOCCUR_PROBABILITY' => array(
                'CONDITION_CONCEPT_ID',
                'AGE_RANGE',
                'TIME_REMAINING',
                'DELTA_DAYS',
                'ACCUMULATED_PROBABILITY'
            ),
            'OSIM_DRUG_COUNT_PROB' => array(
                'GENDER_CONCEPT_ID',
                'AGE_BUCKET',
                'CONDITION_COUNT_BUCKET',
                'DRUG_COUNT',
                'ACCUMULATED_PROBABILITY'
            ),
            'OSIM_COND_DRUG_COUNT_PROB' => array(
                'CONDITION_CONCEPT_ID',
                'AGE_BUCKET',
                'INTERVAL_BUCKET',
                'DRUG_COUNT_BUCKET',
                'CONDITION_COUNT_BUCKET',
                'DRUG_COUNT',
                'ACCUMULATED_PROBABILITY'
            ),
            'OSIM_COND_FIRST_DRUG_PROB' => array(
                'CONDITION_CONCEPT_ID',
                'INTERVAL_BUCKET',
                'GENDER_CONCEPT_ID',
                'AGE_BUCKET',
                'CONDITION_COUNT_BUCKET',
                'DRUG_COUNT_BUCKET',
                'DAY_COND_COUNT',
                'DRUG_CONCEPT_ID',
                'DELTA_DAYS',
                'ACCUMULATED_PROBABILITY'
            ),
            'OSIM_DRUG_ERA_COUNT_PROB' => array(
                'DRUG_CONCEPT_ID',
                'DRUG_COUNT_BUCKET',
                'CONDITION_COUNT_BUCKET',
                'AGE_RANGE',
                'TIME_REMAINING',
                'DRUG_ERA_COUNT',
                'TOTAL_EXPOSURE',
                'ACCUMULATED_PROBABILITY'
            ),
            'OSIM_DRUG_DURATION_PROBABILITY' => array(
                'DRUG_CONCEPT_ID',
                'TIME_REMAINING',
                'DRUG_ERA_COUNT',
                'TOTAL_EXPOSURE',
                'TOTAL_DURATION',
                'ACCUMULATED_PROBABILITY'
            ),
        );
        return $requiredFiles;
    }
    private static function getOsim2Adapter()
    {
        $db = Zend_Registry::get('osim2OracleAdapter');
        return $db;
    }
    private function getSummaryData(
        Application_Model_Osim2AnalysisSummary $summary)
    {
        $data = array(
            'ANALYSIS_SOURCE_ID'   => $summary->getId(),
            'ANALYSIS_SOURCE_NAME' => $summary->getName(),
            'ANALYSIS_SOURCE_DESCRIPTION' => $summary->getDescription(),
            'CREATED_BY' => $summary->getCreatedBy(),
            'CREATED' => $summary->getCreated(),
            'UPDATED_BY' => $summary->getUpdatedBy(),
            'UPDATED' => $summary->getUpdated()
        );
        return $data;
    }
    private function populateSummary($row, 
        Application_Model_Osim2AnalysisSummary $summary)
    {
        if (!$row)
            throw new Exception('Null row passed to populateSummary');

        $summary->setId($row->ANALYSIS_SOURCE_ID)
                ->setOldId($row->ANALYSIS_SOURCE_ID)
                ->setName($row->ANALYSIS_SOURCE_NAME)
                ->setDescription($row->ANALYSIS_SOURCE_DESCRIPTION)
                ->setCreatedBy($row->CREATED_BY)
                ->setCreated($row->CREATED)
                ->setUpdatedBy($row->UPDATED_BY)
                ->setUpdated($row->UPDATED);
    }
    private static function getFullSummaryColumns()
    {
        $columns = array(
            'ANALYSIS_SOURCE_ID',
            'ANALYSIS_SOURCE_NAME',
            'ANALYSIS_SOURCE_DESCRIPTION',
            'CREATED_BY',
            'TO_CHAR( CREATED , \'MM/DD/YYYY HH24:MI:SS\' ) as CREATED',
            'UPDATED_BY',
            'TO_CHAR( UPDATED , \'MM/DD/YYYY HH24:MI:SS\' ) as UPDATED',
        );
        return $columns;
    }

    private function getSimulationSourceData(
        Application_Model_Osim2SimulationSource $source)
    {
        $data = array(
            'OSIM2_SOURCE_ID'   => $source->getId(),
            'OSIM2_SOURCE_NAME' => $source->getName(),
            'OSIM2_SOURCE_DESCRIPTION' => $source->getDescription(),
            'ANALYSIS_SOURCE_ID' => $source->getAnalysisSourceId(),
            'PATIENTS_QTY' => $source->getPatientQty(),
            'SIGNAL_STATUS' => $source->getHasSignal() ? 'Y' : 'N',
            'OSIM2_SOURCE_STATUS' => $source->getStatus(),
            'CREATED_BY' => $source->getCreatedBy(),
            'CREATED' => $source->getCreated(),
            'UPDATED_BY' => $source->getUpdatedBy(),
            'UPDATED' => $source->getUpdated()
        );
        return $data;
    }
    private function populateSimulationSource($row, 
        Application_Model_Osim2SimulationSource $source)
    {
        if (!$row)
            throw new Exception('Null row passed to populateSummary');

        $source->setId($row->OSIM2_SOURCE_ID)
                ->setOldId($row->OSIM2_SOURCE_ID)
                ->setName($row->OSIM2_SOURCE_NAME)
                ->setDescription($row->OSIM2_SOURCE_DESCRIPTION)
                ->setAnalysisSourceId($row->ANALYSIS_SOURCE_ID)
                ->setPatientQty($row->PATIENTS_QTY)
                ->setStatus($row->OSIM2_SOURCE_STATUS)
                ->setHasSignal($row->SIGNAL_STATUS == 'Y')
                ->setCreatedBy($row->CREATED_BY)
                ->setCreated($row->CREATED)
                ->setUpdatedBy($row->UPDATED_BY)
                ->setUpdated($row->UPDATED);
    }
    private static function getFullSimulationSourceColumns()
    {
        $columns = array(
            'OSIM2_SOURCE_ID',
            'OSIM2_SOURCE_NAME',
            'OSIM2_SOURCE_DESCRIPTION',
            'ANALYSIS_SOURCE_ID',
            'PATIENTS_QTY',
            'SIGNAL_STATUS',
            'OSIM2_SOURCE_STATUS',        
            'CREATED_BY',
            'TO_CHAR( CREATED , \'MM/DD/YYYY HH24:MI:SS\' ) as CREATED',
            'UPDATED_BY',
            'TO_CHAR( UPDATED , \'MM/DD/YYYY HH24:MI:SS\' ) as UPDATED',
        );
        return $columns;
    }
}
