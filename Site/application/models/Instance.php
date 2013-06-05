<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 Dec 2010

    Model, representing method entity.

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
require_once('OMOP/WebRL/MethodManager.php');

class Application_Model_Instance extends Application_Model_Abstract
{

    protected $_mapper;
    
    protected $_id;
    protected $_requestId;
    protected $_name;
    protected $_amazonInstanceId;
    protected $_publicDns;
    protected $_startDate;
    protected $_terminateDate;
    protected $_instanceHourCharge;
    protected $_storageHourCharge;
    protected $_statusFlag;

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
            $this->setMapper('Application_Model_InstanceMapper');
        }
        return $this->_mapper;
    }
    
    public function save()
    {
        $this->getMapper()->save($this);
    }

    public function getId() {
        return $this->_id;
    }
    
    public function setId($id) {
        $this->_id = $id;
        return $this;
    }

    public function getRequestId() {
        return $this->_requestId;
    }
    
    public function setRequestId($value) {
        $this->_requestId = $value;
        return $this;
    }
    
    public function getName() {
        return $this->_name;
    }
    
    public function setName($value) {
        $this->_name = $value;
        return $this;
    }
    
    public function getAmazonInstanceId() {
        return $this->_amazonInstanceId;
    }
    
    public function setAmazonInstanceId($value) {
        $this->_amazonInstanceId = $value;
        return $this;
    }
    
    public function getPublicDns() {
        return $this->_publicDns;
    }
    
    public function setPublicDns($value) {
        $this->_publicDns = $value;
        return $this;
    }
    
    public function getStartDate() {
        return $this->_startDate;
    }
    
    public function setStartDate($value) {
        $this->_startDate = $value;
        return $this;
    }
    
    public function getTerminateDate() {
        return $this->_terminateDate;
    }
    
    public function setTerminateDate($value) {
        $this->_terminateDate = $value;
        return $this;
    }
    
    public function getStatusFlag() {
        return $this->_statusFlag;
    }
    
    public function setStatusFlag($value) {
        $this->_statusFlag = $value;
        return $this;
    }

    public function getInstanceHourCharge() {
        return $this->_instanceHourCharge;
    }
    
    public function setInstanceHourCharge($value) {
        $this->_instanceHourCharge = $value;
        return $this;
    }
    
    public function getStorageHourCharge() {
        return $this->_storageHourCharge;
    }
    
    public function setStorageHourCharge($value) {
        $this->_storageHourCharge = $value;
        return $this;
    }
    
    public function find($id)
    {
        $this->getMapper()->find($id, $this);
    }
    
    public function terminateMethods()
    {
        $this->getMapper()->terminateMethods($this->getId());
    }
    
    /**
     * Get vocabulary dataset
     */
    public function getVocabularyDatasets()
    {
        $configMapper = new Application_Model_SiteConfigMapper();
        $config = $configMapper->getConfig();
        
        $datasetTypes = $this->getMapper()->getDatasetTypes();
        
        $vocabularyDatasetTypeId = $config->getVocabularyDataset();
		$vocabularyDataset = null;
		foreach($datasetTypes as $datasetType) {
			if ($vocabularyDatasetTypeId == $datasetType['dataset_type_id']) {
				$vocabularyDataset = $datasetType;
			}
		}
		return $vocabularyDataset;
    }
    
    /**
     * Get available methods list
     */
    public function getMethods()
    {
        global $configurationManager;
        
        $manager = new MethodManager($configurationManager);
        return $manager->get_methods();
    }
    
    /**
     * Get available method parameters
     */
    public function getMethodParameters()
    {
        global $configurationManager;
        
        $manager = new MethodManager($configurationManager);
        $methods = $manager->get_methods();
        $parameters = array();
        if (count($methods) != 0) {
        	$currentMethod = $methods[0];
        	$parameters = $manager->get_method_parameters($currentMethod);
        }
        
        return $parameters;
    }
}

