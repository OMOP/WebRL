<?php
/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    22 June 2011

    Mapper for Instance Request model

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

==============================================================================*/
class Application_Model_InstanceRequestMapper 
{
    protected $_dbTable;

    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided.');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Application_Model_DbTable_InstanceRequest');
        }
        return $this->_dbTable;
    }
    
    public function getInstanceHourPrice($instanceSizeId)
    {
        $db = $this->getDbTable()->getAdapter();
        $columns = array(
            'price' => 'instance_price'
        );
        $select = $db->select()
                     ->from(array('instance_size_tbl'), $columns)
                     ->where('instance_size_id = ?', $instanceSizeId);

        return $db->fetchOne($select);
    }
    
    public function getStorageHourPrice(Application_Model_InstanceRequest $instanceRequest,
        Application_Model_Organization $organization = null)
    {
    	$tempSize = 0;
        
        $tempSnapshotEntry = new Application_Model_SnapshotEntry();
        $tempSnapshotEntry->find($instanceRequest->getTemporaryEbsEntryId());
        $tempSize = $tempSnapshotEntry->getEbsSize();
            
        $datasetSize = 0;
        $datasetTypes = $this->getBoundDatasetTypes($instanceRequest->getId());
        foreach($datasetTypes as $dt) {
        	$datasetSize += $dt['dataset_type_ebs_size'];
        }        
        
        /**
         * @todo the formula is unclear
         */
        if (is_null($organization)) {
            $user = Membership::get_current_user();
            $organization = new Application_Model_Organization();
            $organization->find($user->organization_id);
        }
        $storageCharge = ($tempSize + 20 + $datasetSize) * 0.1 / 730;
      	$storageCharge = $storageCharge * (100 + $organization->getAdminFactor()) / 100;
        return $storageCharge;
    }
    
    public function getBoundDatasetTypes($instanceRequestId)
    {
        // $dataset_types = $dataset_type->find("dataset_type_id IN (SELECT dataset_type_id FROM instance_request_dataset_tbl where instance_request_id = ?)", array($this->instance_request_id));
        $db = $this->getDbTable()->getAdapter();
        $columns = array(
            'dataset_type_ebs_size'
        );
        $subquery = 'dataset_type_id IN 
(SELECT dataset_type_id FROM instance_request_dataset_tbl where instance_request_id = ?)';
        $select = $db->select()
                     ->from('dataset_type_tbl', $columns)
                     ->where($subquery, array($instanceRequestId));
        
        return $db->fetchAll($select);
    }
    
    public function save(Application_Model_InstanceRequest $request)
    {
        $data = array(
            'user_id' => $request->getUserId(),
            'dataset_type_id' => $request->getDatasetTypeId(),
            'software_type_id' => $request->getSoftwareTypeId(),
            'instance_size_id' => $request->getSizeId(),
            'temporary_ebs_entry_id' => $request->getTemporaryEbsEntryId(),
            'num_instances' => $request->getNumInstances(),
            'method_launch_flag' => $request->getMethodLaunchFlag(),
            'terminate_after_success' => $request->getTerminateAfterSuccess(),
            'created_by' => $request->getCreatedBy(),
            'created_date' => $request->getCreatedDate(),
            'create_user_ebs' => $request->getCreateUserEbs(),
            'checkout_method_code' => $request->getCheckoutMethodCode(),
            'attach_shared_storage' => $request->getAttachSharedStorage()
        );
        
        if (null == ($id = $request->getId())) {
            $id = $this->getDbTable()->insert($data);
            $request->setId($id);
        } else {
            $this->getDbTable()->update($data, array('id => ?' => $id));
        }
    }
}