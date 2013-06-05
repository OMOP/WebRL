<?php
/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    22 June 2011

    Model for Instance Request

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

class Application_Model_InstanceRequest extends Application_Model_Abstract
{
    protected $_mapper;
    
    protected $_id;
    protected $_userId;
    protected $_datasetTypeId;
    protected $_softwareTypeId;
    protected $_sizeId;
    protected $_temporaryEbsEntryId;
    protected $_numInstances;
    protected $_methodLaunchFlag;
    protected $_terminateAfterSuccess;
    protected $_createdBy;
    protected $_createdDate;
    protected $_createUserEbs;
    protected $_checkoutMethodCode;
    protected $_attachSharedStorage;
    
    
    public function setMapper($mapper)
    {
        if (is_string($mapper)) {
            $mapper = new $mapper();
        }
        $this->_mapper = $mapper;
        return $this;
    }

    public function getMapper()
    {
        if (null === $this->_mapper) {
            $this->setMapper('Application_Model_InstanceRequestMapper');
        }
        return $this->_mapper;
    }
    

    public function getId()
    {
        return $this->_id;
    }

    public function setId($_id)
    {
        $this->_id = $_id;
        return $this;
    }
    
    public function getUserId()
    {
        return $this->_userId;
    }

    public function setUserId($value)
    {
        $this->_userId = $value;
        return $this;
    }
    
    public function getDatasetTypeId()
    {
        return $this->_datasetTypeId;
    }

    public function setDatasetTypeId($value)
    {
        $this->_datasetTypeId = $value;
        return $this;
    }

    public function getSoftwareTypeId()
    {
        return $this->_softwareTypeId;
    }

    public function setSoftwareTypeId($value)
    {
        $this->_softwareTypeId = $value;
        return $this;
    }
    
    public function getSizeId()
    {
        return $this->_sizeId;
    }

    public function setSizeId($value)
    {
        $this->_sizeId = $value;
        return $this;
    }
    
    public function getTemporaryEbsEntryId()
    {
        return $this->_temporaryEbsEntryId;
    }

    public function setTemporaryEbsEntryId($value)
    {
        $this->_temporaryEbsEntryId = $value;
        return $this;
    }
    
    public function getNumInstances()
    {
        return $this->_numInstances;
    }

    public function setNumInstances($value)
    {
        $this->_numInstances = $value;
        return $this;
    }
    
    public function getMethodLaunchFlag()
    {
        return $this->_methodLaunchFlag;
    }

    public function setMethodLaunchFlag($value)
    {
        $this->_methodLaunchFlag = $value;
        return $this;
    }
    
    public function getTerminateAfterSuccess()
    {
        return $this->_terminateAfterSuccess;
    }

    public function setTerminateAfterSuccess($value)
    {
        $this->_terminateAfterSuccess = $value;
        return $this;
    }
    
    public function getCreatedBy()
    {
        return $this->_createdBy;
    }

    public function setCreatedBy($value)
    {
        $this->_createdBy = $value;
        return $this;
    }
    
    public function getCreatedDate()
    {
        return $this->_createdDate;
    }

    public function setCreatedDate($value)
    {
        $this->_createdDate = $value;
        return $this;
    }
    
    public function getCreateUserEbs()
    {
        return $this->_createUserEbs;
    }

    public function setCreateUserEbs($value)
    {
        $this->_createUserEbs = $value;
        return $this;
    }
    
    public function getCheckoutMethodCode()
    {
        return $this->_checkoutMethodCode;
    }

    public function setCheckoutMethodCode($value)
    {
        $this->_checkoutMethodCode = $value;
        return $this;
    }
    
    public function getAttachSharedStorage()
    {
        return $this->_attachSharedStorage;
    }

    public function setAttachSharedStorage($value)
    {
        $this->_attachSharedStorage = $value;
        return $this;
    }
    
    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'userId' => $this->getUserId(),
            'datasetTypeId' => $this->getDatasetTypeId(),
            'softwareTypeId' => $this->getSoftwareTypeId(),
            'sizeId' => $this->getSizeId(),
            'temporaryEbsEntryId' => $this->getTemporaryEbsEntryId(),
            'numInstances' => $this->getNumInstances(),
            'methodLaunchFlag' => $this->getMethodLaunchFlag(),
            'terminateAfterSuccess' => $this->getTerminateAfterSuccess(),
            'createdBy' => $this->getCreatedBy(),
            'createdDate' => $this->getCreatedDate(),
            'createUserEbs' => $this->getCreateUserEbs(),
            'checkoutMethodCode' => $this->getCheckoutMethodCode(),
            'attachSharedStorage' => $this->getAttachSharedStorage()
        );
    }
    
    public function find($id)
    {
        $this->getMapper()->find($id, $this);
    }
    
    public function save()
    {
        $this->getMapper()->save($this);
    }
    
    public function getInstanceHourPrice()
    {
        return $this->getMapper()->getInstanceHourPrice($this->getSizeId());
    }
    
    public function getStorageHourPrice(Application_Model_Organization $organization = null)
    {
        return $this->getMapper()->getStorageHourPrice($this, $organization);
    }

    /**
     * Checks that the user has enough money to launch specified amount of instances.
     *  
     * @param Application_Model_User $user user being checked for permissions.
     * @param int $instances_count Count of instances that should be launched.
     * 
     */
    public function canUserLaunchInstance(Application_Model_User $user, $instancesCount)
    {
        $organization = new Application_Model_Organization();
        $organization->find($user->getOrganizationId());

        // Check how much this request requres money
    	$instanceCharge = $this->getInstanceHourPrice();
    	$storageCharge  = $this->getStorageHourPrice($organization);
    	$totalHourCharge = $instancesCount * ($instanceCharge + $storageCharge);

    	if ($user->getChargeLimit() < $totalHourCharge) {
    		return false;
        }
        
    	// Dont apply organization restrictions since user does not belongs to any organization. 
    	if ($user->getOrganizationId() == 0) {
    		return true;
        }
        
    	return $this->canOrganizationLaunchInstance($organization, $instancesCount);
    }
    
    /**
     * Checks that organization has enough money to launch required amount of instances.
     * @param Application_Model_Organization $org Organization the check is being performed for.
     * @param int $instancesCount amount of instances to be launched. 
     */
    public function canOrganizationLaunchInstance(Application_Model_Organization $org, $instancesCount)
    {
        // Check how much this request requres money
    	$instanceCharge = $this->getMapper()->getInstanceHourPrice($this->getSizeId());
    	$storageCharge  = $this->getMapper()->getStorageHourPrice($this, $org);
    	$totalHourCharge = $instancesCount * ($instanceCharge + $storageCharge);
    	
    	if ($org->getBudget() < $totalHourCharge) {
    		return false;
        }
    	return true; 
    }
}