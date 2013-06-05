<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    5 Jan 2010

    Class that maps analysis entity with DB.

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
class Application_Model_AnalysisMapper
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
            $this->setDbTable('Application_Model_DbTable_Analysis');
        }
        return $this->_dbTable;
    }

    public function findFilesByMethod(array $methods)
    {
        $result = $this->getDbTable()->findByMethod($methods);

        if (0 == count($result)) {
            return array();
        }

        return self::processAnalysisResults($result);
    }
    
    //Warning! Function can use data with field run_name. This field is not exist in database.
    public function findFilesByRuns(array $runs)
    {
        $result = $this->getDbTable()->findByRuns($runs);
        if (0 == count($result)) {
            return array();
        }

        return self::processAnalysisResults($result);
    }
    /**
     * Process results from ANAYSIS_REF table and return internal data structure.
     * @param Zend_Db_Table_Rowset_Abstract $rowset
     * @return multitype: Data structure that represent set of nested arrays which represent method and run information.
     */
    private function processAnalysisResults($rowset)
    {
        $files = array();
        foreach ($rowset as $row)
        {
            $methodAbbr = $row['METHOD_ABBR'];
            if (!is_array($files[$methodAbbr])) {
                $files[$methodAbbr] = array();
            }
            $analylisId = $row['ANALYSIS_ID'];
            if (!is_array($files[$methodAbbr][$analylisId])) {
                $files[$methodAbbr][$analylisId] = array();
            }
            $files[$methodAbbr][$analylisId] = $row['OUTPUT_FILE_NAME'];
        }
        
        return $files;
    }

    public function fetchByMethod($id, $sort_column = 1, $sort_dir = 'asc') {

        $columns = array(
                    'ANALYSIS_ID',
                    'METHOD_ID',
                    'METHOD_ABBR',
                    'OUTPUT_FILE_NAME');

        for ($i = 1; $i <= 25; $i++) {
            $columns[] = 'PARAM_'.$i.'_VAL';
        }

        if ($sort_dir != 'asc' )
            $sort_dir = 'desc';

        $sort_column = $columns[$sort_column - 1];

        $resultSet = $this->getDbTable()->fetchByMethod($id, $sort_column, $sort_dir);
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_Analysis();
            $i = 1;
            $params = array();
/*            while ($i <= 25) {
                $params['param'.$i] = $row->{'PARAM_'.$i.'_VAL'};
                $i++;
            }*/
            $entry->setId($row->ANALYSIS_ID)
                  ->setMethodId($row->METHOD_ID)
                  ->setOutputFileName($row->OUTPUT_FILE_NAME)
                  ->setMethodAbbrv($row->METHOD_ABBR)
                  ->setParams($params);
            $entries[] = $entry;
        }
        return $entries;

    }

   public function save(Application_Model_Analysis $analysis)
    {
       $data = array(
            'ANALYSIS_ID'   => $analysis->getId(),
            'METHOD_ID'     => $analysis->getMethodId(),
            'METHOD_ABBR' => $analysis->getMethodAbbrv(),
            'OUTPUT_FILE_NAME' => $analysis->getOutputFileName(),
            'TRIAGE_VS_FULL' => $analysis->getTriageVSFull(),
        );
        //Parameters
        $i = 1;
        while ($i <= 25) {
            $data['PARAM_'.$i.'_VAL'] = $analysis->getParam($i);
            $i++;
        }
        $this->_validate($data);
        //Always insert, never update. Maybe need additional check here
        $this->getDbTable()->insert($data);

    }


    public function saveUploadedFile($fileData, $overwrite = false) {
        //Start transaction
        $this->getDbTable()->getAdapter()->beginTransaction();
        //For now only one file, but than maybe couple. If it is change this
        $filename = key($fileData);
        $data = current($fileData);
        //last id passed to save
        $lastId = null;
        $methodAbbr = '';
        try {
            $analysis = array();
            $analysisIds = array();
            $runIds = array();
            $methodId = 0;
            foreach($data as $entry) {
                $temp = new Application_Model_Analysis($entry);
                if (! $methodAbbr) {
                    $methodAbbr = $temp->getMethodAbbrv();
                    $method = new Application_Model_Method();
                    $method->findByAbbr($methodAbbr);
                    if ($method->getId() != $temp->getMethodId()) {
                        throw new Application_Model_AnalysisException('Method Id do not match its Abbr');
                    }
                    else
                        $methodId = $method->getId();
                }
                else {
                    if ($methodAbbr != $temp->getMethodAbbrv())
                        throw new Application_Model_AnalysisException('Different methods specified in file.');
                    if ($methodId != $temp->getMethodId())
                        throw new Application_Model_AnalysisException('Method Id do not match its Abbr');
                }
                $analysis[] = $temp;
                //check for unique pair Run Id, Conf Id
                $runId = $temp->getRunId();
                if (isset($runIds[$runId]) && is_array($runIds[$runId]) && array_search($temp->getConfigurationId(), $runIds[$runId]) !== false)
                    throw new Application_Model_AnalysisException('Duplicate pair (Run Id, Configuration Id): ('.$runId.', '.$temp->getConfigurationId().')');
                else
                    if (is_array($runIds[$runId]))
                        $runIds[$runId][] = $temp->getConfigurationId();
                    else
                        $runIds[$runId] = array($temp->getConfigurationId());
                //store ids for delete
                if (array_search($temp->getId(), $analysisIds) === false)
                    $analysisIds[] = $temp->getId();
                else
                    throw new Application_Model_AnalysisException ('Duplicate Analysis Id: '.$temp->getId());
            }
            $method = new Application_Model_Method();
            $method->findByAbbr($methodAbbr);
            $where = $this->getDbTable()->getAdapter()->quoteInto('METHOD_ID = ?', $method->getId());
            $where .= $this->getDbTable()->getAdapter()->quoteInto('OR ANALYSIS_ID IN (?)', $analysisIds);
            $rowCount = $this->getDbTable()->delete($where);
            
            if (! $overwrite && $rowCount > 0)
                throw new Application_Model_AnalysisException ('Upload failed due to matching records. Choose "Overwrite" option to replace existing analysis data.');

            foreach ($analysis as $entry) {
                $lastId = $entry->getId();
                $this->save($entry);
            }

            //save log entry
            $log = new Application_Model_AnalysisUploadLog();
            $log->setMethodId($method->getId())
                ->setMethodAbbrv($method->getAbbrv())
                ->setUploadDate()
                ->setFilename($filename)
                ->setUserId(Zend_Auth::getInstance()->getIdentity());
            $log->save();

            $this->getDbTable()->getAdapter()->commit();
            return $method->getId();
            
        } catch (Application_Model_AnalysisException $e) {
            $this->getDbTable()->getAdapter()->rollBack();
            $messages = array();
            if ($e->getValidatorMessages()) {
                foreach ($e->getValidatorMessages() as $k=>$v)
                    $messages = 'Analysis ID '.$lastId.'. '.$e->getFieldName().': '.$v;

                $rethrow = new Exception(Zend_Json::encode($messages));
            }
            else
                $rethrow = $e;
            throw $rethrow;
        }
        catch (Exception $e) {
            $this->getDbTable()->getAdapter()->rollBack();
            throw new Exception('System temporarily is unavailable. If issue persist, please contact Administrator.');
        }

    }

    private function _validate(array $data) {

        $dbInfo = $this->getDbTable()->info();
        $metadata = $dbInfo['metadata'];
        foreach ($data as $field => $value) {
            if (isset($metadata[$field])) {
                $validator = Application_Validator_DbMeta::factory($metadata[$field]);
                if (! $validator->isValid((string)$value)) {
                    //@todo: create custom class for this exception
                    $e = new Application_Model_AnalysisException();
                    $e->setFieldName($metadata[$field]['COLUMN_NAME']);
                    $e->setValidatorMessages($validator->getMessages());
                    throw $e;
                }

            }
            else
                throw new Exception('Unknown column '.$field);
        }

    }
    
    public function copyAnalysis($from, $to, $overwrite = false) {
        $this->getDbTable()->getAdapter()->beginTransaction();
        
        try {
            
            $where = $this->getDbTable()->getAdapter()->quoteInto('METHOD_ID = ? AND ANALYSIS_ID IN 
                                                                    (SELECT CONCAT(\''.$to.'\', SUBSTR(ANALYSIS_ID, LENGTH(\''.$from.'\') + 1)) 
                                                                    FROM ANALYSIS_REF WHERE METHOD_ID = \''.$from.'\')', $to);
            $rowCount = $this->getDbTable()->delete($where);

            if ((! $overwrite) && (intval($rowCount) > 0)) {
                throw new Application_Model_AnalysisException('Copy failed due to matching records. Choose "Overwrite" option to replace existing analysis data.');
            }

            $method = new Application_Model_Method();
            $method->find($to);
            //Generate query to copy data
            $paramQuery = '';
            
            $i = 1;
            while ($i <= 25) {
                $paramQuery .= 'PARAM_'.$i.'_VAL, ';
                $i += 1;
            }
            $insertId = ' ANALYSIS_ID, METHOD_ID, METHOD_ABBR,';
            $selectId = ' CONCAT(\''.$to.'\', SUBSTR(ANALYSIS_ID, LENGTH(\''.$from.'\') + 1)), '.$to.', \''.$method->getAbbrv().'\',';
            
            $insertQuery = $paramQuery.$insertId.' OUTPUT_FILE_NAME, TRIAGE_VS_FULL';
            $selectQuery = $paramQuery.$selectId.' OUTPUT_FILE_NAME, TRIAGE_VS_FULL';
            $query = 'INSERT INTO ANALYSIS_REF ('.$insertQuery.') (SELECT '.$selectQuery.' FROM ANALYSIS_REF WHERE METHOD_ID = \''.$from.'\')';

            $this->getDbTable()->getAdapter()->query($query);
            
            $this->getDbTable()->getAdapter()->commit();
            
        } catch (Application_Model_AnalysisException $e) {
            $this->getDbTable()->getAdapter()->rollBack();
            throw $e;
        } 
        catch (Exception $e) {
            $this->getDbTable()->getAdapter()->rollBack();
            throw new Exception('System temporarily is unavailable. If issue persist, please contact Administrator.');
        }
        
    }
}