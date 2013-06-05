<?php

/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    14-Feb-2011

    Model for Security Log entry

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
class Application_Model_LogEvent extends Application_Model_Abstract
{
    protected $_mapper;
    protected $_id;
    protected $_issueType;
    protected $_issueTypeDescription;
    protected $_userId;
    protected $_userLoginId;
    protected $_instanceId;
    protected $_instanceName;
    protected $_message;
    protected $_date;
    protected $_remoteIp;
    
    
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
            $this->setMapper('Application_Model_SecurityLogMapper');
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

    public function getIssueType()
    {
        return $this->_issueType;
    }

    public function setIssueType($_issueType)
    {
        $this->_issueType = $_issueType;
        return $this;
    }

    public function getIssueTypeDescription()
    {
        return $this->_issueTypeDescription;
    }

    public function setIssueTypeDescription($_issueTypeDescription)
    {
        $this->_issueTypeDescription = $_issueTypeDescription;
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

    public function getUserLoginId()
    {
        return $this->_userLoginId;
    }

    public function setUserLoginId($_userLoginId)
    {
        $this->_userLoginId = $_userLoginId;
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

    public function getInstanceName()
    {
        return $this->_instanceName;
    }

    public function setInstanceName($_instanceName)
    {
        $this->_instanceName = $_instanceName;
        return $this;
    }

    public function getMessage()
    {
        return $this->_message;
    }

    public function setMessage($_message)
    {
        $this->_message = $_message;
        return $this;
    }

    public function getDate()
    {
        return $this->_date;
    }

    public function setDate($_date)
    {
        $this->_date = $_date;
        return $this;
    }
    
    public function getRemoteIp()
    {
        return $this->_remoteIp;
    }

    public function setRemoteIp($_remoteIp)
    {
        $this->_remoteIp = $_remoteIp;
        return $this;
    }    
    
    public function find($id)
    {
        $this->getMapper()->find($id, $this);
    }

    
    
}