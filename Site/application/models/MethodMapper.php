<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 Dec 2010

    Class that maps method entity with DB.

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
class Application_Model_MethodMapper
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
            $this->setDbTable('Application_Model_DbTable_Method');
        }
        return $this->_dbTable;
    }

   public function save(Application_Model_Method $method)
    {
        $data = array(
            'METHOD_ID'   => $method->getId(),
            'METHOD_ABBR' => $method->getAbbrv(),
            'METHOD_NAME' => $method->getName(),
            'FILE_NAME_FORMAT' => $method->getFileNameFormat(),
            'FILE_RENAME_MASK' => $method->getFileRenameMask()
        );
        //Parameters
        $i = 1;
        while ($i <= 25) {
            $data['PARAM_'.$i] = $method->getParam($i);
            $i++;
        }
        $organization_methods = new Application_Model_DbTable_OrganizationMethod();
        if (null === $method->getOldId()) {
            $this->getDbTable()->insert($data);
        } else {
            $this->getDbTable()->update($data, array('METHOD_ID = ?' => $method->getOldId()));
        }
        //Delete preivous organization for this method
        $where = $organization_methods->getAdapter()->quoteInto('method_id IN (?)', array($method->getOldId(), $method->getId()));
        $organization_methods->delete($where);
        //Set new organizations if method is private
        if (! $method->getAccess() && $method->getOrganizations()) {
            foreach ($method->getOrganizations() as $organization) {
                $row = $organization_methods->createRow(array('method_id' => $method->getId(), 'organization_id' => $organization));
                $row->save();
            }
        }

    }

    public function find($id, Application_Model_Method $method)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return;
        }
        $row = $result->current();
        $i = 1;
        $params = array();
        while ($i <= 25) {
            $params['param'.$i] = $row->{'PARAM_'.$i};
            $i++;
        }
        $method->setId($row->METHOD_ID)
                  ->setAbbrv($row->METHOD_ABBR)
                  ->setName($row->METHOD_NAME)
                  ->setFileNameFormat($row->FILE_NAME_FORMAT)
                  ->setFileRenameMask($row->FILE_RENAME_MASK)
                  ->setParams($params);
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_Method();
            $i = 1;
            $params = array();
            while ($i <= 25) {
                $params['param'.$i] = $row->{'PARAM_'.$i};
                $i++;
            }
            $entry->setId($row->METHOD_ID)
                  ->setAbbrv($row->METHOD_ABBR)
                  ->setName($row->METHOD_NAME)
                  ->setFileNameFormat($row->FILE_NAME_FORMAT)
                  ->setFileRenameMask($row->FILE_RENAME_MASK)
                  ->setParams($params);
            $entries[] = $entry;
        }
        return $entries;
    }

    public function fetchPairs() {
        $select = $this->getDbTable()->select();
        $select->from($this->getDbTable(), array('METHOD_ID', 'METHOD_ABBR'))
               ->order('METHOD_ABBR ASC');
        $rowset = $this->getDbTable()->getAdapter()->fetchPairs($select);

        return $rowset;

    }

    public function getPaginatorAdapter($sort_column, $sort_dir) {
        $columns = array(
            'METHOD_ID',
            'METHOD_ABBR',
            'METHOD_NAME',
            'FILE_NAME_FORMAT',
            'FILE_RENAME_MASK'
        );

        if ($sort_dir != 'asc' )
            $sort_dir = 'desc';
        
        $sort_column = $columns[$sort_column - 1];
        $select = $this->getDbTable()->select()->from($this->getDbTable(), array(
            'METHOD_ID',
            'METHOD_NAME',
            'METHOD_ABBR',
            'FILE_NAME_FORMAT',
            'FILE_RENAME_MASK'
        ))->order("$sort_column $sort_dir");

        return new Application_Model_MethodPaginator($select);
    }

    public function findWithOrganizations($id, Application_Model_Method $method)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return;
        }
        $row = $result->current();
        $i = 1;
        $params = array();
        while ($i <= 25) {
            $params['param'.$i] = $row->{'PARAM_'.$i};
            $i++;
        }
        /*
         * TODO: implement OrganizationsMapper
         */
        $omd = new Application_Model_DbTable_OrganizationMethod();
        $where = $omd->getAdapter()->quoteInto('method_id = ?', $row->METHOD_ID);
        
        $orgmethods = $omd->fetchAll($where);
        foreach ($orgmethods as $orgmethod) {
            $organization = $orgmethod->findParentRow('Application_Model_DbTable_Organization');
            $orgs[] = $organization;
        }
//        $orgs = $row->findManyToManyRowset('Application_Model_DbTable_Organization', 'Application_Model_DbTable_OrganizationMethod');
//        $orgs = $orgs->toArray();

        $method->setId($row->METHOD_ID)
               ->setAbbrv($row->METHOD_ABBR)
               ->setName($row->METHOD_NAME)
               ->setAccess(! (count($orgs) > 0))
               ->setFileNameFormat($row->FILE_NAME_FORMAT)
               ->setFileRenameMask($row->FILE_RENAME_MASK)
               ->setOrganizations($orgs)
               ->setParams($params);
    }

    function getUniqueAbbrValidator($exclude_id = null) {

        if ($exclude_id) {
            $exclude = array(
                'field' => 'METHOD_ID',
                'value' => $exclude_id
            );
        }
        else
            $exclude = null;
        return new Zend_Validate_Db_NoRecordExists('METHOD_REF','METHOD_ABBR' ,$exclude, $this->getDbTable()->getAdapter());
    }
    
    function getUniqueIdValidator($exclude_id = null) {

        if ($exclude_id) {
            $exclude = array(
                'field' => 'METHOD_ID',
                'value' => $exclude_id
            );
        }
        else
            $exclude = null;
        return new Zend_Validate_Db_NoRecordExists('METHOD_REF','METHOD_ID' ,$exclude, $this->getDbTable()->getAdapter());
    }    

    public function findByAbbr($abbr, Application_Model_Method $method) {
        $select = $this->getDbTable()->select();
        $select->where('METHOD_ABBR = ?', $abbr);

        $row = $this->getDbTable()->fetchRow($select);
        if ($row) {
            $i = 1;
            $params = array();
            while ($i <= 25) {
                $params['param'.$i] = $row->{'PARAM_'.$i};
                $i++;
            }
            $method->setId($row->METHOD_ID)
                   ->setAbbrv($row->METHOD_ABBR)
                   ->setName($row->METHOD_NAME)
                   ->setFileNameFormat($row->FILE_NAME_FORMAT)
                   ->setFileRenameMask($row->FILE_RENAME_MASK)
                   ->setOrganizations(null) // $orgs was here, but it was undefined
                   ->setParams($params);
        }
    }

    
}

