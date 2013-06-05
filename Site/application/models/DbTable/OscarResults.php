<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 Dec 2010

    Zend_Db_Table class for OSCAR_RESULTS (Oracle) table.

    (c)2009-2011 Foundation for the National Institutes of Health (FNIH)

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
class Application_Model_DbTable_OscarResults extends Zend_Db_Table_Abstract
{

    protected $_name = 'OSCAR_RESULTS';
    //protected $_primary = 'ANALYSIS_ID';
    //protected $_sequence = true;

    protected function _setupDatabaseAdapter()
    {
        $this->_db = Zend_Registry::get('oracle_adapter');
    }

    /**
     * Checks whether the OSCAR results exist for selected dataset
     * @param string dataset name
     * @return boolean whether the OSCAR results exist for selected dataset
     */
    public function checkDatasetExist($dataset)
    {
        $query = 'select count(*) as AMOUNT
from OSCAR_RESULTS O
join SOURCE_REF S on S.SOURCE_ID = O.SOURCE_ID
where S.SOURCE_ABBR = ' . $this->_db->quote($dataset);
        try {
            $row = $this->_db->fetchRow($query);
        } catch (Exception $e) {
            $row['AMOUNT'] = 0;
        }

        return $row['AMOUNT'] > 0;
    }

    /**
     *
     * @param string path to result file
     */
    public function loadFromFile($file, $datasetId)
    {
        $delimiter = '|';

        $rows = array();

        $handle = fopen($file, "r");
        if ($handle) {
            // get parameter names from first line
            $line = trim(fgets($handle, 4096));
            $paramNames = explode($delimiter, $line);
            $datasetExists = in_array('SOURCE_ID', $paramNames);
            if (!$datasetExists) {
                $paramNames[] = 'SOURCE_ID';
            }
            foreach ($paramNames as $key => $value) {
                if (!$value) {
                    unset($paramNames[$key]);
                }
                $paramNames[$key] = strtoupper($value);
            }
            $paramCount = sizeof($paramNames);

            while (!feof($handle)) {
                $line = trim(fgets($handle, 4096));
                if ('' == $line) {
                    continue;
                }
                $params = explode($delimiter, $line);
                $i = 0;
                foreach ($params as $key => $value) {
                    ++$i;
                    /**
                     * Remove extra parameters
                     */
                    if ($i > $paramCount) {
                        unset($params[$key]);
                        continue;
                    }
                    /**
                     * Process values
                     */
                    if ($value === 0) {
                        $value = 0;
                    } elseif (!$value) {
                        $value = 'NULL';
                    } else { //if (!is_numeric ($value)) {
                        /**
                         * String
                         */
                        $value = $this->_db->quote($value);
                    }
                    $params[$key] = $value;
                }
                if (!$datasetExists) {
                    $params[] = $datasetId;
                }

                $sql = "
INTO OMOP_RESULTS.OSCAR_RESULTS (" . implode(',', $paramNames) . ") values
(".  implode(',', $params).")";
                
                $rows[] = $sql;

            }
            fclose($handle);
        }

        if (sizeof($rows)) {
            $maxAmount = 50;

            if (sizeof($rows) < $maxAmount) {
                $query = '
INSERT ALL
' . implode("\n", $rows) . '
SELECT 1 FROM dual';

                try {
                    $this->_db->query($query);
                } catch (Exception $e) {
                    //$this->setErrorMessage('Error while loading data to database (multiple insert).' .
                    //        "\n" . (strlen($e->getMessage())>1000 ? substr($e->getMessage(), 0, 1000) . '...' : $e->getMessage()));
                }
            } else {
                $i = 0;
                $rowsPart = array();
                while($rows) {
                    $rowsPart[] = array_pop($rows);
                    if ($i++ > $maxAmount) {
                        $i = 0;
                        $query = '
INSERT ALL
' . implode("\n", $rowsPart) . '
SELECT 1 FROM dual';
                        try {
                            $this->_db->query($query);
                        } catch (Exception $e) {
                            //$this->setErrorMessage('Error while loading data to database (multiple inserts).' .
                            //    "\n" . (strlen($e->getMessage())>1000 ? substr($e->getMessage(), 0, 1000) . '...' : $e->getMessage()));
                        }
                        $rowsPart = array();
                    }
                }
            }
        }
    }

    /**
     * Delete existing results for specified dataset
     * @param string dataset name (abbr)
     * @return boolean deletion result
     */
    public function deleteDatasetResult($dataset)
    {
        $qDataset = $this->_db->quote($dataset);
        $query = 'delete from OSCAR_RESULTS_FILES
where SOURCE_ID in (select SOURCE_ID from SOURCE_REF where SOURCE_ABBR = ' . $qDataset . ')';
        $result = $this->_db->query($query);

        $query = 'delete from OSCAR_RESULTS
where SOURCE_ID in (select SOURCE_ID from SOURCE_REF where SOURCE_ABBR = ' . $qDataset . ')';
        $result = $result && $this->_db->query($query);
        return $result;
    }

    public function getDatasetByName($dataset)
    {
        /*$select = $this->select()
                       ->from('SOURCE_REF')
                       ->where('SOURCE_ABBR = ?', array($dataset));
        return $this->fetchAll($select);*/
        $query = 'select SOURCE_ID
from SOURCE_REF
where SOURCE_ABBR = ' . $this->_db->quote($dataset);

        try {
            $row = $this->_db->fetchRow($query);
        } catch (Exception $e) {
            $row['SOURCE_ID'] = null;
        }
        return $row['SOURCE_ID'];
    }

    /**
     * Get list of datasets having uploaded OSCAR results
     * @return array datasets list
     */
    public function getResults()
    {
        $query = 'SELECT DISTINCT S.SOURCE_ABBR, ORF.ADD_DATE'
            . ' FROM OSCAR_RESULTS O'
            . ' JOIN SOURCE_REF S ON S.SOURCE_ID = O.SOURCE_ID'
            . ' LEFT JOIN OSCAR_RESULTS_FILES ORF ON ORF.SOURCE_ID = S.SOURCE_ID';
        try {
            $results = $this->_db->fetchAll($query);
        } catch (Exception $e) {
            //echo '<pre>' . $query . '</pre><br />'; var_dump($e->getMessage());
            return array();
        }

        $methods = array(
            'OSCAR' => array()
        );

        foreach ($results as $row) {
            $methods['OSCAR'][$row['SOURCE_ABBR']] = array(
                'ADD_DATE' => $row['ADD_DATE']
            );
        }

        return $methods;
    }


    public function insertFileData($fileName, $datasetId) {
        $query = 'INSERT INTO OSCAR_RESULTS_FILES
            (FILE_NAME, SOURCE_ID) VALUES
            (' . $this->_db->quote($fileName) . ', '.$datasetId.')';
        try {
            $result = $this->_db->query($query);
        } catch (Exception $e) {
            return false;
        }

        return $result;
    }
}