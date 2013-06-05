<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 Dec 2010

    Zend_Db_Table class for METHOD_REF (Oracle) table.

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
class Application_Model_DbTable_Method extends Zend_Db_Table_Abstract
{

    protected $_name = 'METHOD_REF';
    protected $_primary = 'METHOD_ID';
    protected $_sequence = true;
    protected $_dependentTables = 'OrganizationMethod';

    protected function _setupDatabaseAdapter()
    {
        $this->_db = Zend_Registry::get('oracle_adapter');
    }

    public function getNewId()
    {
        $select = $this->select()->from($this, 'MAX(METHOD_ID)');
        $id = $this->getAdapter()->fetchOne($select);
        return $id + 1;
    }
    
    /**
     * Get method data by its name
     * @param array method names
     * @return array methods
     */
    public function findByName (array $names)
    {
        $condition = array();
        if (!empty ($names)) {
            foreach ($names as $name) {
                $qname = $this->_db->quote($name);
                $condition[] = $qname;
            }
            $condition = ' METHOD_ABBR in ('.implode(',', $condition).') ';
        }
        
        $select = $this->_db->select()->from($this->_name);
        if ($condition) {
            $select = $select->where($condition);
        }
        return $this->_db->fetchAll($select);
    }
}

