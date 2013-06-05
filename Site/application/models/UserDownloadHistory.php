<?php

/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    21-Jul-2011

    Class representing the user download history entity

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

class Application_Model_UserDownloadHistory extends Application_Model_Abstract
{
    protected $_mapper;
    
    protected $_id;
    protected $_userId;
    protected $_type;
    protected $_date;
    
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

    public function getUserId()
    {
        return $this->_userId;
    }

    public function setUserId($id)
    {
        $this->_userId = $id;
        return $this;
    }

    public function getType() {
        return $this->_type;
    }

    public function setType($type)
	{
        $this->_type = $type;
        return $this;
    }

    public function getDate()
	{
        return $this->_date;
    }

    public function setDate($date)
	{
        $this->_date = $date;
        return $this;
    }
    
    public function save() 
    {
        $this->getMapper()->saveDownloadHistory($this);
    }
}
