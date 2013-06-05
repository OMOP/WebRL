<?php

/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    03-Feb-2011

    Model for software type

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
class Application_Model_SoftwareType extends Application_Model_Abstract
{
    
    protected $_id;
    protected $_description;
    protected $_sortOrder;
    protected $_defaultFlag;
    protected $_image;
    protected $_ebsFlag;
    protected $_platform;
    protected $_clusterFlag;
    protected $_gpuFlag;
    protected $_osFamily;
    protected $_activeFlag;
    protected $_mapper;
   
    
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
            $this->setMapper('Application_Model_SoftwareTypeMapper');
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

    public function getDefaultFlag()
    {
        return $this->_defaultFlag;
    }

    public function setDefaultFlag($_defaultFlag)
    {
        $this->_defaultFlag = $_defaultFlag;
        return $this;
    }

    public function getImage()
    {
        return $this->_image;
    }

    public function setImage($_image)
    {
        $this->_image = $_image;
        return $this;
    }

    public function getEbsFlag()
    {
        return $this->_ebsFlag;
    }

    public function setEbsFlag($_ebsFlag)
    {
        if ($_ebsFlag && $_ebsFlag != 'N') {
            $_ebsFlag = 'Y';
        } else {
            $_ebsFlag = 'N';
        }
        $this->_ebsFlag = $_ebsFlag;
        return $this;
    }

    public function getPlatform()
    {
        return $this->_platform;
    }

    public function setPlatform($_platform)
    {
        $this->_platform = $_platform;
        return $this;
    }

    public function getClusterFlag()
    {
        return $this->_clusterFlag;
    }

    public function setClusterFlag($_clusterFlag)
    {
        if ($_clusterFlag && $_clusterFlag != 'N') {
            $_clusterFlag = 'Y';
        } else {
            $_clusterFlag = 'N';
        }
        $this->_clusterFlag = $_clusterFlag;
        return $this;
    }

    public function getGpuFlag()
    {
        return $this->_gpuFlag;
    }

    public function setGpuFlag($_gpuFlag)
    {
        if ($_gpuFlag && $_gpuFlag != 'N') {
            $_gpuFlag = 'Y';
        } else {
            $_gpuFlag = 'N';
        }
        $this->_gpuFlag = $_gpuFlag;
        return $this;
    }

    public function getOsFamily()
    {
        return $this->_osFamily;
    }

    public function setOsFamily($_osFamily)
    {
        $this->_osFamily = $_osFamily;
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
            'image' => $this->getImage(),
            'platform' => $this->getPlatform(),
            'osFamily' => $this->getOsFamily(),
            'ebsFlag' => ($this->getEbsFlag() == 'Y'),
            'clusterFlag' => ($this->getClusterFlag() == 'Y'),
            'gpuFlag' => ($this->getGpuFlag() == 'Y')
        );
    }


    
}