<?php

/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    16-Feb-2011

    Class representing Website exception entity

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
class Application_Model_WebsiteException extends Application_Model_Abstract
{
    
    protected $_mapper;
    protected $_id;
    protected $_date;
    protected $_description;
    protected $_details;
    protected $_userId;
    protected $_userLogin;
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
            $this->setMapper('Application_Model_WebsiteEventMapper');
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

    public function getDate()
    {
        return $this->_date;
    }

    public function setDate($_date)
    {
        $this->_date = $_date;
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

    public function getDetails()
    {
        return $this->_details;
    }

    public function setDetails($_details)
    {
        $this->_details = $_details;
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

    public function getUserLogin()
    {
        return $this->_userLogin;
    }

    public function setUserLogin($_userLogin)
    {
        $this->_userLogin = $_userLogin;
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