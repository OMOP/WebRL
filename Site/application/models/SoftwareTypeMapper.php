<?php

/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    03-Feb-2011

    Mapper for SoftwareType model

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
class Application_Model_SoftwareTypeMapper implements Zend_Paginator_AdapterAggregate
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
            $this->setDbTable('Application_Model_DbTable_SoftwareType');
        }
        return $this->_dbTable;
    }
    
    public function fetchAll()
    {
        $select = $this->getDbTable()->select()->where('active_flag = 1')
                                               ->order('sort_order');
        $resultSet = $this->getDbTable()->fetchAll($select);
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_SnapshotEntry();
            $entry->setId($row->software_type_id)
                  ->setDescription($row->software_type_description)
                  ->setImage($row->software_type_image)
                  ->setActiveFlag($row->active_flag)
                  ->setDefaultFlag($row->default_checked_flag)
                  ->setEbsFlag($row->ebs_flag)
                  ->setClusterFlag($row->cluster_flag)
                  ->setSortOrder($row->sort_order)
                  ->setGpuFlag($row->gpu_required_flag)
                  ->setPlatform($row->software_type_platform)
                  ->setOsFamily($row->os_family);
            $entries[] = $entry;
        }
        return $entries;
    }
    
    public function find($id, Application_Model_SoftwareType $type) 
    {
        
        $resultSet = $this->getDbTable()->find($id);
        
        if (0 == count($resultSet))
            return;
        
        $row = $resultSet->current();
        $type->setId($row->software_type_id)
             ->setImage($row->software_type_image)
             ->setDescription($row->software_type_description)
             ->setActiveFlag($row->active_flag)
             ->setDefaultFlag($row->default_checked_flag)
             ->setEbsFlag($row->ebs_flag)
             ->setClusterFlag($row->cluster_flag)
             ->setSortOrder($row->sort_order)
             ->setGpuFlag($row->gpu_required_flag)
             ->setPlatform($row->software_type_platform)
             ->setOsFamily($row->os_family);
    }
    
    public function save(Application_Model_SoftwareType $type)
    {

        $data = array(
            'software_type_description' => $type->getDescription(),
            'software_type_image' => $type->getImage(),
            'software_type_platform' => $type->getPlatform(),
            'ebs_flag' => $type->getEbsFlag(),
            'cluster_flag' => $type->getClusterFlag(),
            'gpu_required_flag' => $type->getGpuFlag(),
            'os_family' => $type->getOsFamily());
        
        if (null !== $type->getActiveFlag()) {
            $data['active_flag'] = $type->getActiveFlag();
        }
        if (null !== $type->getSortOrder()) {
            $data['sort_order'] = $type->getSortOrder();
        }
                
        
        if (null == ($id = $type->getId())) {
            $data['default_checked_flag'] = 0;
            $data['active_flag'] = 1;
            $data['sort_order'] = $this->getDbTable()->getNewSortOrder();
            $this->getDbTable()->insert($data);
        } else { 
            $this->getDbTable()->update(
                $data, 
                array('software_type_id = ?' => $type->getId())
            );
        }
        
    }
    
    public function getPaginatorAdapter()
    {
        $columns = array(
            'id' => 'software_type_id',
            'type' => 'software_type_description',
            'defaultFlag' => 'default_checked_flag',
            'sortOrder' => 'sort_order',
            'ami' => 'software_type_image',
            'ebsFlag' => 'ebs_flag',
            'clusterFlag' => 'cluster_flag',
            'gpuFlag' => 'gpu_required_flag',
            'platform' => 'software_type_platform',
            'osFamily' => 'os_family'
        );
        
        $select = $this->getDbTable()->select()
            ->from($this->getDbTable(), $columns)
            ->where('active_flag = 1')->order('sort_order');

        return new Zend_Paginator_Adapter_DbSelect($select);
    }
    
    public function getDefaultTypeId()
    {
        $select = $this->getDbTable()->select();
        $select->from($this->getDbTable(), 'software_type_id')
               ->where('default_checked_flag <> 0');
        return $this->getDbTable()->getAdapter()->fetchOne($select);
    }
    
    public function setDefaultType($id)
    {
        $sql = 'UPDATE software_type_tbl
                SET default_checked_flag = IF(software_type_id='.$id.', 1, 0)';
        
        $this->getDbTable()->getAdapter()->query($sql);
    }
    
    public function getList()
    {
        $db = $this->getDbTable()->getAdapter();
        $columns = array('software_type_id', 'software_type_description');
        $select = $this->getDbTable()->select()
                ->from($this->getDbTable(), $columns)
                ->where('active_flag <> 0')
                ->order('sort_order');
        $types = $db->fetchPairs($select);
        return $types;
    }
    
    public function updateOrder($id, $offset)
    {
        $type = new Application_Model_SoftwareType();
        $type->find($id);
        $oldSortOrder = $type->getSortOrder();
        
        $select = $this->getDbTable()->select();
        $select->where(
            ($offset > 0 ? 'sort_order < ?' : 'sort_order > ?'),
            $oldSortOrder
        );
        $select->order('sort_order '.($offset > 0 ? 'DESC' : 'ASC'));
        $select->limit('1');
        $result = $this->getDbTable()->fetchRow($select);
        $type->setSortOrder($result->sort_order);
        $type->save();
        $result->sort_order = $oldSortOrder;
        $result->save();
    }
    
    public function getUniqueDescriptionValidator($exclude_id = null) {
        if ($exclude_id) {
            $exclude = array(
                'field' => 'software_type_id',
                'value' => $exclude_id
            );
        }
        else
            $exclude = null;
        return new Zend_Validate_Db_NoRecordExists('software_type_tbl','software_type_description' ,$exclude, $this->getDbTable()->getAdapter());
    }    


}    