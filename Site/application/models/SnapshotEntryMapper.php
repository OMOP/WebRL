<?php
/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    31 Jan 2011

    Class that maps snapshor entry entity with DB.

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

==============================================================================*/

class Application_Model_SnapshotEntryMapper
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
            $this->setDbTable('Application_Model_DbTable_SnapshotEntry');
        }
        return $this->_dbTable;
    }
    
    public function fetchAll($category = null)
    {
        $select = $this->getDbTable()->select()->where('active_flag = 1')
                                               ->order('sort_order');
        if (null !== $category) {
            if (! is_int($category)) {
                throw new Exception('Wrong parameter data type.');
            } else {
                $select->where('snapshot_entry_category_id = ?', $category);
            }
        }
        $resultSet = $this->getDbTable()->fetchAll($select);
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_SnapshotEntry();
            $entry->setId($row->snapshot_entry_id)
                  ->setDescription($row->snapshot_entry_description)
                  ->setActiveFlag($row->active_flag)
                  ->setDefaultFlag($row->default_checked_flag)
                  ->setEbs($row->snapshot_entry_ebs)
                  ->setEbsSize($row->snapshot_entry_ebs_size)
                  ->setCreatedDate($row->created_date)
                  ->setUpdatedDate($row->updated_date)
                  ->setSortOrder($row->sort_order)
                  ->setUserId($row->snapshot_entry_user_id)
                  ->setInstanceId($row->snapshot_entry_instance_id)
                  ->setCategory($category);
            $entries[] = $entry;
        }
        return $entries;
    }
    
    public function find($id, Application_Model_SnapshotEntry $snapshot) {
        
        $resultSet = $this->getDbTable()->find($id);
        
        if (0 == count($resultSet))
            return;
        
        $row = $resultSet->current();
        $snapshot->setId($row->snapshot_entry_id)
                 ->setDescription($row->snapshot_entry_description)
                 ->setActiveFlag($row->active_flag)
                 ->setDefaultFlag($row->default_checked_flag)
                 ->setEbs($row->snapshot_entry_ebs)
                 ->setEbsSize($row->snapshot_entry_ebs_size)
                 ->setCreatedDate($row->created_date)
                 ->setUpdatedDate($row->updated_date)
                 ->setSortOrder($row->sort_order)
                 ->setUserId($row->snapshot_entry_user_id)
                 ->setInstanceId($row->snapshot_entry_instance_id)
                 ->setCategory($row->snapshot_entry_category_id);
    }
    
    public function save(Application_Model_SnapshotEntry $snapshot)
    {

        $data = array(
            'snapshot_entry_description' => $snapshot->getDescription(),
            'snapshot_entry_ebs' => $snapshot->getEbs(),
            'snapshot_entry_ebs_size' => $snapshot->getEbsSize(),
            'snapshot_entry_category_id' => $snapshot->getCategory());
        
        if (null !== $snapshot->getActiveFlag()) {
            $data['active_flag'] = $snapshot->getActiveFlag();
        }
                
        
        if (null == ($id = $snapshot->getId())) {
            $data['created_date'] = gmdate('c');
            $data['updated_date'] = gmdate('c');
            $data['default_checked_flag'] = 0;
            $data['active_flag'] = 1;
            $data['sort_order'] = $this->getDbTable()->getNewSortOrder();
            $this->getDbTable()->insert($data);
        } else { 
            $data['updated_date'] = gmdate('c');
            $this->getDbTable()->update(
                $data, 
                array('snapshot_entry_id = ?' => $snapshot->getId())
            );
        }
        
    }
    
    public function getPaginatorAdapter($category = null)
    {
        $columns = array(
            'id' => 'snapshot_entry_id',
            'description' => 'snapshot_entry_description',
            'defaultFlag' => 'default_checked_flag',
            'sortOrder' => 'sort_order',
            'ebs' => 'snapshot_entry_ebs',
            'ebsSize' => 'snapshot_entry_ebs_size'
        );
        
        if ($sort_dir != 'asc' )
            $sort_dir = 'desc';
        
        $select = $this->getDbTable()->select()
            ->from($this->getDbTable(), $columns)
            ->where('active_flag = 1')->order('sort_order');
        if (null !== $category) {
            if (! is_int($category)) {
                throw new Exception('Wrong parameter data type.');
            } else {
                $select->where('snapshot_entry_category_id = ?', $category);
            }
        }

        return new Zend_Paginator_Adapter_DbSelect($select);
    }

    public function getDefaultTemporaryStorageId()
    {
        $select = $this->getDbTable()->select();
        $select->from($this->getDbTable(), 'snapshot_entry_id')
               ->where(
                   'default_checked_flag <> 0 AND snapshot_entry_category_id = ?',
                   Application_Model_SnapshotEntryCategory::TEMPORARY
               );
        return $this->getDbTable()->getAdapter()->fetchOne($select);
    }
    
    public function setDefaultTemporaryStorage($id)
    {
        $sql = 'UPDATE snapshot_entry_tbl
                SET default_checked_flag = IF(snapshot_entry_id='.$id.', 1, 0)
                WHERE snapshot_entry_category_id = '.Application_Model_SnapshotEntryCategory::TEMPORARY;
        
        $this->getDbTable()->getAdapter()->query($sql);
    }
    
    public function getTemporaryStorageList()
    {
        $db = $this->getDbTable()->getAdapter();
        $columns = array('snapshot_entry_id', 'snapshot_entry_description');
        $select = $this->getDbTable()->select()
                ->from($this->getDbTable(), $columns)
                ->where(
                    'active_flag <> 0 AND snapshot_entry_category_id = ?',
                    Application_Model_SnapshotEntryCategory::TEMPORARY
                )
                ->order('sort_order');
        $types = $db->fetchPairs($select);
        return $types;
    }
    
    function getUniqueDescriptionValidator($exclude_id = null) {
        if ($exclude_id) {
            $exclude = array(
                'field' => 'snapshot_entry_id',
                'value' => $exclude_id
            );
        }
        else
            $exclude = null;
        return new Zend_Validate_Db_NoRecordExists('snapshot_entry_tbl','snapshot_entry_description' ,$exclude, $this->getDbTable()->getAdapter());
    }    
}