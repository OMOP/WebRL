<?php

/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    04-Feb-2011

    Mapper for Instance model

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
class Application_Model_InstanceMapper {
    
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
            $this->setDbTable('Application_Model_DbTable_Instance');
        }
        return $this->_dbTable;
    }
    
    
    public function getPaginatorAdapter($sort_column, $sort_dir, $user)
    {
        //it is fields order for user_type = 'admin'
        $columns = array(
            'name' => 'i.assigned_name',
            'host' => 'i.public_dns',
            'amazonId' => 'i.amazon_instance_id',
            'ownerLogin' => 'u.login_id',
            'startDate' => 'i.start_date',
            'instanceType' => 'is.instance_size_name',
            'datasetTypes' => 'group_concat(dt.dataset_type_description SEPARATOR \', \')',
            'softwareType' => 'st.software_type_description',
            'id' => 'i.instance_id',
            'ownerId' => 'u.user_id',
            'status' => 'i.status_flag',
            'ownerInternalId' => 'u.internal_id',
        );
        //Admin and user interface have different set of column
        if (Membership::get_app_mode() == ApplicationMode::Admin) {
            $column_order = array_keys($columns);
            if ($sort_column <= 8) {
                $sort_column = $column_order[$sort_column - 1];
            } else {
                $sort_column = $column_order[0];
            }
        } else {
            unset($columns['amazonId']);
            unset($columns['ownerLogin']);
            $column_order = array_keys($columns);
            if ($sort_column == 1) {
                $sort_column = $column_order[$sort_column - 1];
            } elseif ($sort_column == 2) {//if user choose unsortable Connect column
                $sort_column = $column_order[0];
            } elseif ($sort_column <= 7) {
                $sort_column = $column_order[$sort_column - 2];
            } else {
                $sort_column = $column_order[0];
            }
        }
        
        $select = $this->getDbTable()->getAdapter()->select();
        
        $select->from(array('i' => 'instance_tbl'), $columns);
        
        $select->join(
            array('ir' => 'instance_request_tbl'), 
            'i.instance_request_id = ir.instance_request_id',
            array()
        );
        
        $select->join(
            array('u' => 'user_tbl'), 
            'u.user_id = ir.user_id',
            array()
        );
        
        $select->join(
            array('is' => 'instance_size_tbl'), 
            'is.instance_size_id = ir.instance_size_id',
            array()
        );
        
        $select->join(
            array('st' => 'software_type_tbl'), 
            'st.software_type_id = ir.software_type_id',
            array()
        );
        
        $select->joinLeft(
            array('irdt' => 'instance_request_dataset_tbl'),
            'irdt.instance_request_id = ir.instance_request_id',
            array()
        );
        
        $select->joinLeft(
            array('dt' => 'dataset_type_tbl'),
            'dt.dataset_type_id = irdt.dataset_type_id',
            array()
        );
        
        $select->group('i.instance_id');
        $select->order("$sort_column $sort_dir");
        $select->where("i.status_flag IN ('A','P','I', 'X')");
        if (Membership::get_app_mode() != ApplicationMode::Admin) {
            $select->where('ir.user_id = ?', $user->user_id);
        }
        if ($user->organization_id) {
            $select->where('u.organization_id = ?', $user->organization_id);
        }
        
        return new Zend_Paginator_Adapter_DbSelect($select);
    }
    
    public function getInstanceTypes()
    {
        $select = $this->getDbTable()
                       ->getAdapter()
                       ->select()
                       ->from('instance_size_tbl')
                       ->where('active_flag = ?', 'Y')
                       ->order('default_flag DESC');
        $stmt = $select->query();
        return $stmt->fetchAll();
    }
    
    public function getDatasetTypes()
    {
        $user = Membership::get_current_user();
        
        // get user's datasets
        $select = $this->getDbTable()
                       ->getAdapter()
                       ->select()
                       ->from(array('u' => 'user_dataset_access_tbl'), 'u.*')
                       ->join(array('d' => 'dataset_type_tbl'),
                            'd.dataset_type_id = u.dataset_type_id')
                       ->where('u.user_id = ?', $user->user_id)
                       ->where('d.active_flag = 1')
                       ->order('d.dataset_type_description');
        $stmt = $select->query();
        $userDatasetsRaw = $stmt->fetchAll();
        $userDatasets = array();
        foreach ($userDatasetsRaw as $dataset) {
            $userDatasets[$dataset['dataset_type_id']] = $dataset;
        }
        unset($userDatasetsRaw);
        
        // all datasets are allowed for the admin org
        if ('0' == $user->organization_id) {
            return $userDatasets;
        }
        
        // get user organization's datasets
        $select = $this->getDbTable()
                       ->getAdapter()
                       ->select()
                       ->from(array('o' => 'organization_dataset_access_tbl'), 'dataset_type_id')
                       ->join(array('d' => 'dataset_type_tbl'),
                            'd.dataset_type_id = o.dataset_type_id')
                       ->where('o.organization_id = ?', $user->organization_id)
                       ->where('d.active_flag = 1');
        $stmt = $select->query();
        $orgDatasets = $stmt->fetchAll();
        
        // map user's datasets on org's datasets
        $output = array();
        foreach ($orgDatasets as $dataset) {
            if (array_key_exists($dataset['dataset_type_id'], $userDatasets)) {
                $output[$dataset['dataset_type_id']] = $userDatasets[$dataset['dataset_type_id']];
            }
        }
        
        return $output;
    }
    
    public function getSoftwareTypes()
    {
        $user = Membership::get_current_user();
        
        // get available user software
        $select = $this->getDbTable()
                       ->getAdapter()
                       ->select()
                       ->from(array('u' => 'user_software_access_tbl'), 'u.*')
                       ->join(array('s' => 'software_type_tbl'),
                            's.software_type_id = u.software_type_id')
                       ->where('u.user_id = ?', $user->user_id)
                       ->where('s.active_flag = 1');
        $stmt = $select->query();
        $userSoftwareRaw = $stmt->fetchAll();
        
        $userSoftware = array();
        foreach ($userSoftwareRaw as $software) {
            $userSoftware[$software['software_type_id']] = $software;
        }
        unset($userSoftwareRaw);
        
        // all datasets are allowed for the admin org
        if ('0' == $user->organization_id) {
            return $userSoftware;
        }
        
        // get user organization's software types
        $select = $this->getDbTable()
                       ->getAdapter()
                       ->select()
                       ->from(array('o' => 'organization_software_access_tbl'), 'software_type_id')
                       ->join(array('s' => 'software_type_tbl'),
                            's.software_type_id = o.software_type_id')
                       ->where('o.organization_id = ?', $user->organization_id)
                       ->where('s.active_flag = 1');
        $stmt = $select->query();
        $orgSoftware = $stmt->fetchAll();
        
        // map user's datasets on org's datasets
        $output = array();
        foreach ($orgSoftware as $software) {
            if (array_key_exists($software['software_type_id'], $userSoftware)) {
                $output[] = $userSoftware[$software['software_type_id']];
            }
        }
        
        return $output;
    }
    
    public function save(Application_Model_Instance $instance)
    {
        $data = array(
            'instance_id' => $instance->getId(),
            'instance_request_id' => $instance->getRequestId(),
            'assigned_name' => $instance->getName(),
            'amazon_instance_id' => $instance->getAmazonInstanceId(),
            'public_dns' => $instance->getPublicDns(),
            'start_date' => $instance->getStartDate(),
            'terminate_date' => $instance->getTerminateDate(),
            'instance_hour_charge' => $instance->getInstanceHourCharge(),
            'storage_hour_charge' => $instance->getStorageHourCharge(),
            'status_flag' => $instance->getStatusFlag()
        );
        
        if (null == ($id = $instance->getId())) {
            $newId = $this->getDbTable()->insert($data);
            $instance->setId($newId);
        } else {
            $this->getDbTable()->update($data, array('instance_id = ?' => $id));
        }
    }
    
    public function find($id, Application_Model_Instance $instance)
    {
        $resultSet = $this->getDbTable()->find($id);
        if (0 == count($resultSet)) {
            return;
        }
        
        $row = $resultSet->current();
        
        $instance->setId($row->instance_id)
                 ->setRequestId($row->instance_request_id)
                 ->setName($row->assigned_name)
                 ->setAmazonInstanceId($row->amazon_instance_id)
                 ->setPublicDns($row->public_dns)
                 ->setStartDate($row->start_date)
                 ->setTerminateDate($row->terminate_date)
                 ->setInstanceHourCharge($row->instance_hour_charge)
                 ->setStorageHourCharge($row->storage_hour_charge)
                 ->setStatusFlag($row->status_flag);
    }
    
    public function changeName ($instanceId,$newName) {
        $max = 256;
        $min = 3;
        $nameValid = new Zend_Validate_StringLength($min,$max);
        if (!$nameValid->isValid($newName))
            throw new Zend_Exception("Instance name should have from {$min} to {$max} symbols!",666);
        $instance = $this->getDbTable()->find($instanceId);
        if (0 == count($instance))
            throw new Zend_Exception("Instance not found!",666);
        $owner = $this->getDbTable()->selectInstanceOwner($instanceId);
        $ownerId=$owner[0]["ownerId"];
        $dups = $this->getDbTable()->selectDupName($instanceId,$ownerId,$newName);
        if (count($dups) > 0)
            throw new Zend_Exception("This name already exist!",666);
        $this->getDbTable()->updateName($instanceId,$newName);
        return true;
    }
    
    public function terminateMethods(Application_Model_Instance $instance)
    {
        $dbTable = new Application_Model_DbTable_MethodLaunch();
        $data = array(
            'status_flag' => 'T',
            'complete_date' => gmdate('c')
        );
        $dbTable->update($data, array('instance_id = ?' => $instance->getId(),
                                      'status_flag' => 'A'));
    }
}