<?php

/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    17-Feb-2011

    Class representing AmazonLog entity

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
class Application_Model_AmazonLog extends Application_Model_Abstract
{
    protected $_mapper;
    protected $_id;
    protected $_instanceId;
    protected $_status;
    protected $_consoleOutput;
    
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
            $this->setMapper('Application_Model_AmazonLogMapper');
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

    public function getInstanceId()
    {
        return $this->_instanceId;
    }

    public function setInstanceId($_instanceId)
    {
        $this->_instanceId = $_instanceId;
        return $this;
    }

    public function getStatus()
    {
        return $this->_status;
    }

    public function setStatus($_status)
    {
        $this->_status = $_status;
        return $this;
    }

    public function getConsoleOutput()
    {
        return $this->_consoleOutput;
    }

    public function setConsoleOutput($_consoleOutput)
    {
        $this->_consoleOutput = $_consoleOutput;
        return $this;
    }
    
    public function find($id)
    {
        $this->getMapper()->find($id, $this);
    }

    public function findByInstanceId($id)
    {
        $this->getMapper()->findByInstanceId($id, $this);
    }
    
    public function save()
    {
        $this->getMapper()->save($this);
    }


    
}