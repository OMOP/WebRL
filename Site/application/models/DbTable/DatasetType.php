<?php
/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 Dec 2010

    Zend_Db_Table class for dataset_type_tbl table.

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
class Application_Model_DbTable_DatasetType extends Zend_Db_Table_Abstract
{

    protected $_name = 'dataset_type_tbl';
    protected $_primary = 'dataset_type_id';
    
    /*
     * Gets pairs [$id] => [$name]. Should be moved to Mapper.
     */
    public function getList($active = false) {
        $db = $this->getAdapter();
        $columns = array('dataset_type_id', 'dataset_type_description');
        $select = $this->select()
                ->from($this, $columns)
                ->order('dataset_type_description');
        if ($active) {
            $select->where('active_flag <> 0');
        }
        $datasets = $db->fetchPairs($select);
        return $datasets;
    }

    /*
     * Gets pairs [$id] => [$name]. Should be moved to Mapper.
     */
    public function getListByOrganizationId($organization, $order = false)
    {
        $db = $this->getAdapter();
        $columns = array('dataset_type_id', 'dataset_type_description');
        $datasets = $this->select()
                ->from($this, $columns);
                //->where('active_flag = ?', 'Y');
        if ($organization) {
            /**
             * I'd join it but this crashes:
             * ->join('organization_dataset_access_tbl', 'organization_dataset_access_tbl.organization_id = organization_tbl.organization_id')
             */
            $filter = 'dataset_type_id in (select dataset_type_id from organization_dataset_access_tbl where organization_id = ? )';
            $datasets = $datasets->where($filter, $organization);
        }

        if ($order) {
            $datasets = $datasets->order($order);
        }
        return $db->fetchPairs($datasets);
    }
    
    public function getNewSortOrder()
    {
        $select = $this->select()->from($this, 'max(sort_order)')
                                 ->where('active_flag = 1');
        $id = $this->getAdapter()->fetchOne($select);
        return $id + 1;
    }
    
}

