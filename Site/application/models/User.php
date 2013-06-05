<?php

/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    24-Feb-2011

    Class representing user entity

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
require_once("OMOP/WebRL/PersonalStorageManager.php");
class Application_Model_User extends Application_Model_Abstract
{
    protected $_mapper;
    
    protected $_id;
    protected $_loginId;
    protected $_firstName;
    protected $_lastName;
    protected $_organizationId;
    protected $_email;
    protected $_phone;
    protected $_createDate;
    protected $_title;
    protected $_active;
    protected $_svnAccess;
    protected $_orgAdmin;
    protected $_totalCharged;
    protected $_chargeLimit;
    protected $_chargeRemaining;
    protected $_storageHost;
    protected $_storageFolder;
    protected $_storageUsage;
    protected $_storageSize;
    protected $_maxInstances;
    protected $_datasetAccess;
    protected $_imageAccess;
    protected $_sharesStorageTo;
    protected $_password;
    protected $_certificate;
    protected $_certificateWithPassword;
    protected $_organizationName;
    protected $_accessibleStorage;
    protected $_loadResult;
    private $_additionalUsersToShareWith;
    private $_oracle_datasets = array();
    
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
            $this->setMapper('Application_Model_UserMapper');
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

    public function getFirstName()
    {
        return $this->_firstName;
    }

    public function setFirstName($_firstName)
    {
        $this->_firstName = $_firstName;
        return $this;
    }

    public function getLastName() {
        return $this->_lastName;
    }

    public function setLastName($_lastName)
	{
        $this->_lastName = $_lastName;
        return $this;
    }

    public function getOrganizationId()
	{
        return $this->_organizationId;
    }

    public function setOrganizationId($_organizationId)
	{
        $this->_organizationId = $_organizationId;
        return $this;
    }

    public function getEmail()
	{
        return $this->_email;
    }

    public function setEmail($_email)
	{
        $this->_email = $_email;
        return $this;
    }

    public function getPhone()
	{
        return $this->_phone;
    }

    public function setPhone($_phone)
	{
        $this->_phone = $_phone;
        return $this;
    }

    public function getCreateDate()
	{
        $d = new Application_View_Helper_DateFormat();
        return $d->dateFormat($this->_createDate);
    }

    public function setCreateDate($_createDate)
	{
        $this->_createDate = $_createDate;
        return $this;
    }

    public function getTitle()
	{
        return $this->_title;
    }

    public function setTitle($_title)
	{
        $this->_title = $_title;
        return $this;
    }

    public function getActive()
	{
        return $this->_active;
    }

    public function setActive($_active)
	{
        $this->_active = $_active;
        return $this;
    }

    public function getSvnAccess()
	{
        return $this->_svnAccess;
    }

    public function setSvnAccess($_svnAccess)
	{
        $this->_svnAccess = $_svnAccess;
        return $this;
    }

    public function getOrgAdmin()
	{
        return $this->_orgAdmin;
    }

    public function setOrgAdmin($_orgAdmin)
	{
        $this->_orgAdmin = $_orgAdmin;
        return $this;
    }

    public function getChargeLimit()
	{
        return sprintf("%d", $this->_chargeLimit);
    }

    public function setChargeLimit($_chargeLimit)
	{
        $this->_chargeLimit = $_chargeLimit;
        return $this;
    }

    public function getChargeRemaining()
	{
        if ($this->_chargeRemaining == null)
            $this->setChargeRemaining ($this->getChargeLimit() - $this->getTotalCharged ());
        return sprintf("%d", $this->_chargeRemaining);
    }

    public function setChargeRemaining($_chargeRemaining)
	{
        $this->_chargeRemaining = $_chargeRemaining;
        return $this;
    }

    public function getStorageHost()
	{
        if ($this->_storageHost == null)
            $this->setStorageHost('ec2-72-44-59-15.compute-1.amazonaws.com');
        return $this->_storageHost;
    }

    public function setStorageHost($_storageHost)
	{
        $this->_storageHost = $_storageHost;
        return $this;
    }

    public function getStorageFolder()
	{
        return $this->_storageFolder;
    }

    public function setStorageFolder($_storageFolder)
	{
        $this->_storageFolder = $_storageFolder;
        return $this;
    }

    public function getStorageUsage()
	{ 
        global $configurationManager;
        if ($this->_storageUsage == null) {
    		$psm = new PersonalStorageManager($configurationManager);
    		$space_usage = $psm->get_space_usage($this->getId());
            $this->setStorageUsage(($space_usage + (1024*1024*1024 - 1) ) / (1024*1024*1024));
        }
        return number_format($this->_storageUsage, 0);
    }

    public function setStorageUsage($_storageUsage)
	{
        $this->_storageUsage = $_storageUsage;
        return $this;
    }

    public function getStorageSize()
	{
        return $this->_storageSize;
    }

    public function setStorageSize($_storageSize)
	{
        $this->_storageSize = $_storageSize;
        return $this;
    }

    public function getMaxInstances()
	{
        return $this->_maxInstances;
    }

    public function setMaxInstances($_maxInstances)
	{
        $this->_maxInstances = $_maxInstances;
        return $this;
    }

    public function getDatasetAccess()
	{
        if ($this->_datasetAccess === null)
            $this->getMapper()->getDatasetAccess($this);
        return $this->_datasetAccess;
    }

    public function setDatasetAccess($_datasetAccess, $separate = 'ml') {

        $array = array();

        if (strpos(current($_datasetAccess), 'ml') !== false || strpos(current($_datasetAccess), 'or') !== false) {
            $this->_datasetAccess = $_datasetAccess;
        } else {



            if (count($accessed_dataset = $this->getMapper()->GetUserOracleAccess($this->_id)) > 0) {
                $mapper = new Application_Model_SourceMapper();
                $mapper_result_array = $mapper->fetchPairs();
                foreach ($accessed_dataset as $key) {
                    if (in_array($key['source_abbr'], $mapper_result_array)) {
                        $this->_oracle_datasets[] = 'or' . array_search($key['source_abbr'], $mapper_result_array);
                    }
                }
            }
            if (count($this->_oracle_datasets) > 0) {
                $max_id = end(array_keys($this->_oracle_datasets));
                $array = $this->_oracle_datasets;
            }
            foreach ($_datasetAccess as $key => $value) {
                $max_id++;
                $array[$max_id] = $separate . $value;
            }
            $_datasetAccess = $array;
            $this->_datasetAccess = $_datasetAccess;
        }
        return $this;
    }

    public function getImageAccess()
	{
        if ($this->_imageAccess === null)
            $this->getMapper()->getImageAccess($this);
        return $this->_imageAccess;
    }

    public function setImageAccess($_imageAccess)
	{
        $this->_imageAccess = $_imageAccess;
        return $this;
    }

    public function getAdditionalUsersToShareWith()
	{
        if ($this->_additionalUsersToShareWith === null)
            $this->getMapper()->populateAdditionalUsersToShareWith($this);
        return $this->_additionalUsersToShareWith;
    }

    public function setAdditionalUsersToShareWith($_additionalUsersToShareWith)
	{
        $this->_additionalUsersToShareWith = $_additionalUsersToShareWith;
        return $this;
    }

    public function getSharesStorageTo()
	{
        return $this->_sharesStorageTo;
    }

    public function setSharesStorageTo($_sharesStorageTo)
	{
        $this->_sharesStorageTo = $_sharesStorageTo;
        return $this;
    }
    public function getPassword()
    {
        return $this->_password;
    }

    public function setPassword($_password)
    {
        $this->_password = $_password;
        return $this;
    }

    public function getCertificate()
    {
        return $this->_certificate;
    }

    public function setCertificate($_certificate)
    {
        $this->_certificate = $_certificate;
        return $this;
    }

    public function getCertificateWithPassword()
    {
        return $this->_certificateWithPassword;
    }

    public function setCertificateWithPassword($_certificateWithPassword)
    {
        $this->_certificateWithPassword = $_certificateWithPassword;
        return $this;
    }

        
    public function save() {
        $this->getMapper()->save($this);
    }
    
    public function find($id) {
        $this->getMapper()->find($id, $this);
    }
    
    public function getLoginId()
    {
        return $this->_loginId;
    }

    public function setLoginId($_loginId)
    {
        $this->_loginId = $_loginId;
        return $this;
    }
    public function getTotalCharged()
    {
        if ($this->_totalCharged == null)
            $this->getMapper()->getTotalCharged($this);
        return $this->_totalCharged;
    }

    public function setTotalCharged($_totalCharged)
    {
        $this->_totalCharged = $_totalCharged;
        return $this;
    }

    public function getOrganizationName() 
    {
        if (null === $this->_organizationName && $this->getOrganizationId() != 0) {
            $o = new Organization();
            $o->load('organization_id = ?', array($this->getOrganizationId()));
            $this->_organizationName = $o->organization_name;
        }
        return $this->_organizationName;
    }
    
    public function getAccessibleStorages() 
    {
        if ($this->_accessibleStorage == null) {
            $this->_accessibleStorage = $this->getMapper()->getAccessibleStorages($this->getId());
        }
        return $this->_accessibleStorage;
    }

    public function getLoadResult()
	{
        return $this->_loadResult;
    }

    public function setLoadResult($_loadResult)
	{
        $this->_loadResult = $_loadResult;
        return $this;
    }
    public function setOracleDataset($separate = 'or') {
        $mapper = new Application_Model_SourceMapper();
        foreach ($mapper->fetchPairs() as $key => $value) {
            $this->_oracle_datasets[]= $separate.$key;
        }
        
    }
    public function getOracleDataset()
    {
        return $this->_oracle_datasets;
    
    }
    
}
