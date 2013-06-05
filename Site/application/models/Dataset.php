<?php

/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    04-Feb-2011

    Model for Dataset

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
class Application_Model_Dataset extends Application_Model_Abstract
{
    protected $_id;
    protected $_mapper;
    protected $_description;
    protected $_sortOrder;
    protected $_storageType;
    protected $_s3Bucket;
    protected $_ebsSnapshot;
    protected $_methodDatasetName;
    protected $_datasetSize;
    protected $_defaultFlag;
    protected $_encryptedFlag;
    protected $_attachFolder;
    protected $_activeFlag;
    protected $_password;
    
    
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
            $this->setMapper('Application_Model_DatasetMapper');
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

    public function getDescription()
    {
        return $this->_description;
    }

    public function setDescription($_description)
    {
        $this->_description = $_description;
        return $this;
    }

    public function getSortOrder()
    {
        return $this->_sortOrder;
    }

    public function setSortOrder($_sortOrder)
    {
        $this->_sortOrder = $_sortOrder;
        return $this;
    }

    public function getStorageType()
    {
        return $this->_storageType;
    }

    public function setStorageType($_storageType)
    {
        $this->_storageType = $_storageType;
        return $this;
    }

    public function getS3Bucket()
    {
        return $this->_s3Bucket;
    }

    public function setS3Bucket($_s3Bucket)
    {
        $this->_s3Bucket = $_s3Bucket;
        return $this;
    }

    public function getEbsSnapshot()
    {
        return $this->_ebsSnapshot;
    }

    public function setEbsSnapshot($_ebsSnapshot)
    {
        $this->_ebsSnapshot = $_ebsSnapshot;
        return $this;
    }

    public function getMethodDatasetName()
    {
        return $this->_methodDatasetName;
    }

    public function setMethodDatasetName($_methodDatasetName)
    {
        $this->_methodDatasetName = $_methodDatasetName;
        return $this;
    }

    public function getDatasetSize()
    {
        return $this->_datasetSize;
    }

    public function setDatasetSize($_datasetSize)
    {
        $this->_datasetSize = $_datasetSize;
        return $this;
    }

    public function getDefaultFlag()
    {
        return $this->_defaultFlag;
    }

    public function setDefaultFlag($_defaultFlag)
    {
        $this->_defaultFlag = $_defaultFlag;
        return $this;
    }

    public function getEncryptedFlag()
    {
        return $this->_encryptedFlag;
    }

    public function setEncryptedFlag($_encryptedFlag)
    {
        $this->_encryptedFlag = $_encryptedFlag;
        return $this;
    }

    public function getAttachFolder()
    {
        return $this->_attachFolder;
    }

    public function setAttachFolder($_attachFolder)
    {
        $this->_attachFolder = $_attachFolder;
        return $this;
    }

    public function getActiveFlag()
    {
        return $this->_activeFlag;
    }

    public function setActiveFlag($_activeFlag)
    {
        $this->_activeFlag = $_activeFlag;
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

    public function toArray()
    {
        return array(
            'description'       => $this->getDescription(),
            'storageType'       => $this->getStorageType(),
            'ebsSnapshot'       => $this->getEbsSnapshot(),
            's3Bucket'          => $this->getS3Bucket(),
            'datasetSize'       => $this->getDatasetSize(),
            'encryptedFlag'     => $this->getEncryptedFlag(),
            'attachFolder'      => $this->getAttachFolder(),
            'methodDatasetName' => $this->getMethodDatasetName()
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

    /**
     * Encrypts unencrypted password
     * @param string $password unencrypted password
     * @return string encrypted password
     */
    static public function encryptPassword($password)
    {
        return md5($password);
    }
}