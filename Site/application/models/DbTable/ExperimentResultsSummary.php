<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 Dec 2010

    Zend_Db_Table class for EXPERIMENT_RESULTS_SUMMARY (Oracle) table.

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
class Application_Model_DbTable_ExperimentResultsSummary extends Zend_Db_Table_Abstract
{

    protected $_name = 'EXPERIMENT_RESULTS_SUMMARY';

    protected function _setupDatabaseAdapter()
    {
        $this->_db = Zend_Registry::get('oracle_adapter');
    }

    public function quote($data)
    {
        return $this->_db->quote($data);
    }

    public function query($sql)
    {
        $this->_db->query($sql);
    }
    
    /*
     * Getting Oracle experiment results data
     * @parameters array List parameters
     * @return array
     */
    public function getExperimentResults($parameters) {
        $columns = array(
            'ANALYSIS_ID' => 'A.ANALYSIS_ID',
            'OUTPUT_FILE_NAME' => 'A.OUTPUT_FILE_NAME',
            'AMOUNT' => 'ERS.RECORDS_NUMBER',
            'TOTAL_AMOUNT' => new Zend_Db_Expr('NVL(ERS.RECORDS_NUMBER, -1)'),
            'ADD_DATE' => new Zend_Db_Expr('TO_CHAR(ERS.DB_LOAD_DATE, \'YYYY-MM-DD\')'),
        );
        
        $sel1 = $this->_db->select();
        $sel1->from('SCCS_SUPPLEMENTAL_RESULTS_1', array('count(*)'))
            ->where('SOURCE_ID = ?', $parameters['source_id'])
            ->where('EXPERIMENT_ID = ?', $parameters['experiment_id'])
            ->where('SUPPLEMENTAL1_ID = A.SUPPLEMENTAL1_ID');
        
        if ($parameters['hasSupplementals1']) {
            $columns = array_merge($columns, array("SUPPL1_NUM" => new Zend_Db_Expr('('.$sel1->assemble().')'), 'SUPPLEMENT1_FILENAME' => 'S.FILE_NAME'));
        }

        $sel2 = $this->_db->select();
        $sel2->from('SCCS_SUPPLEMENTAL_RESULTS_2', array('count(*)'))
            ->where('SOURCE_ID = ?', $parameters['source_id'])
            ->where('EXPERIMENT_ID = ?', $parameters['experiment_id'])
            ->where('ANALYSIS_ID = A.ANALYSIS_ID');
        
        if ($parameters['hasSupplementals2']) {
            $columns = array_merge($columns, array("SUPPL2_NUM" => new Zend_Db_Expr('('.$sel2->assemble().')')));            
        }
        
        $select = $this->_db->select()
            ->from(array('ERS' => $this->_name), $columns)
            ->joinRight(array('A' => 'ANALYSIS_REF'), 
                'ERS.ANALYSIS_ID = A.ANALYSIS_ID AND ERS.source_id =' . $parameters['source_id'] 
                . ' AND A.method_id   =' . $parameters['method_id'] 
                . ' AND ERS.EXPERIMENT_ID =' . $parameters['experiment_id'], '')
            ->joinLeft(array('S' => 'SUPPLEMENTAL_1_REF'), 'S.SUPPLEMENTAL1_ID = A.SUPPLEMENTAL1_ID', array())
            ->where('A.METHOD_ID = ?', $parameters['method_id'])
            ->order('A.ANALYSIS_ID');
        
        return $this->_db->fetchAll($select);
    }
}