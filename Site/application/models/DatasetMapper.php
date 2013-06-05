<?php

/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    04-Feb-2011

    Mapper for Dataset model

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
class Application_Model_DatasetMapper implements Zend_Paginator_AdapterAggregate
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
            $this->setDbTable('Application_Model_DbTable_DatasetType');
        }
        return $this->_dbTable;
    }
    
    public function getPaginatorAdapter()
    {
        $columns = array(
            'id' => 'dataset_type_id',
            'description' => 'dataset_type_description',
            'sortOrder' => 'sort_order',
            'storageType' => 'dataset_type_mode',
            's3Bucket' => 's3_bucket',
            'ebsSnapshot' => 'dataset_type_ebs',
            'methodDatasetName' => 'dataset_method_name',
            'datasetSize' => 'dataset_type_ebs_size',
            'defaultFlag' => 'default_checked_flag'
        );
        
        $select = $this->getDbTable()->select();
        $select->from($this->getDbTable(), $columns)
               ->where('active_flag = 1')
               ->order('sort_order');
        
        return new Zend_Paginator_Adapter_DbSelect($select);
    }
    
    public function save(Application_Model_Dataset $dataset)
    {
        $data = array(
            'dataset_type_description' => $dataset->getDescription(),
            'dataset_type_mode' => $dataset->getStorageType(),
            'dataset_type_ebs' => 
                $dataset->getStorageType() == 0 ? $dataset->getEbsSnapshot():'',
            'dataset_type_ebs_size' => $dataset->getDatasetSize(),
            'encrypted_flag' => $dataset->getEncryptedFlag(),
            'attach_folder' => $dataset->getAttachFolder(),
            'dataset_method_name' => $dataset->getMethodDatasetName(),
            's3_bucket' =>
                $dataset->getStorageType() == 1 ? $dataset->getS3Bucket(): '');
        
        
        if (null !== $dataset->getActiveFlag()) {
            $data['active_flag'] = $dataset->getActiveFlag();
        }
        
        if (null !== $dataset->getSortOrder()) {
            $data['sort_order'] = $dataset->getSortOrder();
        }
        
        if (! $dataset->getEncryptedFlag()) {
            $data['password_hash'] = '';
        } else {
            if ($dataset->getPassword() != '') {
                $data['password_hash'] = md5($dataset->getPassword());
            }
        }
        
        if (null == ($id = $dataset->getId())) {
            $data['default_checked_flag'] = 0;
            $data['active_flag'] = 1;
            $data['sort_order'] = $this->getDbTable()->getNewSortOrder();
            $this->getDbTable()->insert($data);
        } else { 
            $this->getDbTable()->update(
                $data, 
                array('dataset_type_id = ?' => $dataset->getId())
            );
        }
        
    }
    
    public function find($id, Application_Model_Dataset $dataset)
    {
        $resultSet = $this->getDbTable()->find($id);
        
        if (0 == count($resultSet)) {
            return;
        }
        
        $row = $resultSet->current();
        $dataset->setId($row->dataset_type_id)
                ->setStorageType($row->dataset_type_mode)
                ->setDescription($row->dataset_type_description)
                ->setActiveFlag($row->active_flag)
                ->setDefaultFlag($row->default_checked_flag)
                ->setEncryptedFlag($row->encrypted_flag)
                ->setAttachFolder($row->attach_folder)
                ->setSortOrder($row->sort_order)
                ->setMethodDatasetName($row->dataset_method_name)
                ->setS3Bucket($row->s3_bucket)
                ->setEbsSnapshot($row->dataset_type_ebs)
                ->setDatasetSize($row->dataset_type_ebs_size);
        if ($row->encrypted_flag) {
            $dataset->setPassword($row->password_hash);
        }
    }
    
    public function updateOrder($id, $offset)
    {
        $dataset = new Application_Model_Dataset();
        $dataset->find($id);
        $oldSortOrder = $dataset->getSortOrder();
        
        $select = $this->getDbTable()->select();
        $select->where(
            ($offset > 0 ? 'sort_order < ?' : 'sort_order > ?'),
            $oldSortOrder
        );
        $select->order('sort_order '.($offset > 0 ? 'DESC' : 'ASC'));
        $select->limit('1');
        $result = $this->getDbTable()->fetchRow($select);
        $dataset->setSortOrder($result->sort_order);
        $dataset->save();
        $result->sort_order = $oldSortOrder;
        $result->save();
    }
    
    public function getDefaultDatasetId()
    {
        $select = $this->getDbTable()->select();
        $select->from($this->getDbTable(), 'dataset_type_id')
               ->where('default_checked_flag <> 0');
        return $this->getDbTable()->getAdapter()->fetchOne($select);
    }
    
    public function setDefaultDataset($id)
    {
        $sql = 'UPDATE dataset_type_tbl
                SET default_checked_flag = IF(dataset_type_id='.$id.', 1, 0)';
        
        $this->getDbTable()->getAdapter()->query($sql);
    }
    
    function getUniqueDescriptionValidator($exclude_id = null) {
        if ($exclude_id) {
            $exclude = array(
                'field' => 'dataset_type_id',
                'value' => $exclude_id
            );
        }
        else
            $exclude = null;
        return new Zend_Validate_Db_NoRecordExists('dataset_type_tbl','dataset_type_description' ,$exclude, $this->getDbTable()->getAdapter());
    }    
    
}