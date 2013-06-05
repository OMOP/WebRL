<?php
/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 Dec 2010

    Model, representing system snapshot entry.

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

class Application_Model_SnapshotEntry extends Application_Model_Abstract
{
    
    protected $_id;
    protected $_category;
    protected $_userId;
    protected $_description;
    protected $_ebs;
    protected $_ebsSize;
    protected $_defaultFlag;
    protected $_activeFlag;
    protected $_sortOrder;
    protected $_createdDate;
    protected $_updatedDate;
    protected $_instanceId;
    protected $_mapper;


    public function __construct($options = null)
    {
        if (!is_array($options)) {
            parent::__construct(array('category' => $options));
        } else {
            parent::__construct($options);
        }
        
    }
    
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
            $this->setMapper('Application_Model_SnapshotEntryMapper');
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

    public function getCategory() 
    {
        return $this->_category;
    }

    public function setCategory($_category)
    {
        $this->_category = $_category;
        return $this;
    }

    public function getUserId()
    {
        return $this->_userId;
    }

    public function setUserId($_userId)
    {
        $this->_userId = $_userId;
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

    public function getEbs()
    {
        return $this->_ebs;
    }

    public function setEbs($_ebs)
    {
        $this->_ebs = $_ebs;
        return $this;
    }

    public function getEbsSize()
    {
        return $this->_ebsSize;
    }

    public function setEbsSize($_ebsSize)
    {
        $this->_ebsSize = $_ebsSize;
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

    public function getActiveFlag()
    {
        return $this->_activeFlag;
    }

    public function setActiveFlag($_activeFlag)
    {
        $this->_activeFlag = $_activeFlag;
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

    public function getCreatedDate()
    {
        return $this->_createdDate;
    }

    public function setCreatedDate($_createdDate)
    {
        $this->_createdDate = $_createdDate;
        return $this;
    }

    public function getUpdatedDate()
    {
        return $this->_updatedDate;
    }

    public function setUpdatedDate($_updatedDate)
    {
        $this->_updatedDate = $_updatedDate;
        return $this;
    }
    
    public function getInstanceId()
    {
        return $this->_instanceId;
    }

    public function setInstanceId($_instanceId)
    {
        $this->_instanceId = $_instanceId;
        return $this;
    }

    public function save()
    {
        
        $this->getMapper()->save($this);
        
    }
    
    public function find($id)
    {
        
        $this->getMapper()->find($id, $this);
        
    }
    
    public function toArray()
    {
        return array(
            'description' => $this->getDescription(),
            'activeFlag' => $this->getActiveFlag(),
            'category' => $this->getCategory(),
            'createdDate' => $this->getCreatedDate(),
            'defaultFlag' => $this->getDefaultFlag(),
            'ebs' => $this->getEbs(),
            'ebsSize' => $this->getEbsSize(),
            'sortOrder' => $this->getSortOrder(),
            'updatedDate' => $this->getUpdatedDate(),
            'userId' => $this->getUserId(),
           'instanceId' => $this->getInstanceId());
    }


}