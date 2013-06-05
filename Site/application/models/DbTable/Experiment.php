<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 Dec 2010

    Zend_Db_Table class for EXPERIMENT_REF (Oracle) table.

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
class Application_Model_DbTable_Experiment extends Zend_Db_Table_Abstract
{

    protected $_name = 'EXPERIMENT_REF';
    protected $_primary = 'EXPERIMENT_ID';
    protected $_sequence = true;

    protected function _setupDatabaseAdapter()
    {
        $this->_db = Zend_Registry::get('oracle_adapter');
    }

    public function getNewId()
    {
        $select = $this->select()->from($this, 'MAX(EXPERIMENT_ID)');
        $id = $this->getAdapter()->fetchOne($select);
        return $id + 1;
    }    

    /**
     * Get list of experiments
     * @return <type>
     */
    public function getExperiments($order = 'EXPERIMENT_NAME')
    {
        $select = $this->select()
                       ->from($this, array('EXPERIMENT_ID', 'EXPERIMENT_NAME'))
                       ->order($order);
        return $this->fetchAll($select);
    }

    /**
     * Get experiment by its name
     * @return array experiment data
     */
    public function getExperimentByName($name)
    {
        $select = $this->select()
                       ->from($this)
                       ->where('EXPERIMENT_NAME = ?', $name);
        return $this->fetchRow($select);
    }
}

