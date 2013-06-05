<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    03 Feb 2011

    Model, representing storage instance entity.

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

================================================================================*/

class Application_Model_StorageInstance extends Application_Model_Abstract
{
    protected $_id;
    protected $_systemInstanceId;
    
    protected $_systemInstance;
    
    protected $_name;
    protected $_host;
    protected $_keyName;
    protected $_sizeId;
    protected $_osFamily;
    protected $_instanceType;
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
            $this->setMapper('Application_Model_StorageInstanceMapper');
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

    public function getSystemInstanceId()
    {
        return $this->_systemInstanceId;
    }

    public function setSystemInstanceId($_systemInstanceId)
    {
        $this->_systemInstanceId = $_systemInstanceId;
        
        $systemInstance = new Application_Model_SystemInstance();
        $systemInstance->find($this->_systemInstanceId);
        $this->_systemInstance = $systemInstance;
        $this->_name = $systemInstance->getName();
        $this->_host = $systemInstance->getHost();
        $this->_keyName = $systemInstance->getKeyName();
        $this->_sizeId = $systemInstance->getSizeId();
        $this->_osFamily = $systemInstance->getOsFamily();
        $this->_instanceType = $systemInstance->getInstanceType();
         
        return $this;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getHost()
    {
        return $this->_host;
    }

    public function getKeyName()
    {
        return $this->_keyName;
    }

    public function getSizeId()
    {
        return $this->_sizeId;
    }

    public function getOsFamily()
    {
        return $this->_osFamily;
    } 

    public function getInstanceType()
    {
        return $this->_instanceType;
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
            'name' => $this->getName(),
            'systemInstance' => $this->getSystemInstanceId(),
            'host' => $this->getHost(),
            'keyName' => $this->getKeyName(),
            'instanceSize' => $this->getSizeId(),
            'osFamily' => $this->getOsFamily(),
            'instanceType' => $this->getInstanceType()
        );
    }
}