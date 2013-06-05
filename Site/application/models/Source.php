<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    27 Jan 2011

    Model, representing source entity.

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
class Application_Model_Source extends Application_Model_Abstract
{
    
    protected $_id;
    protected $_oldId;
    protected $_abbrv;
    protected $_name;
    protected $_mapper;
    protected $_schemename;
    

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
            $this->setMapper('Application_Model_SourceMapper');
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

    public function getAbbrv() 
    {
        return $this->_abbrv;
    }

    public function setAbbrv($_abbrv) 
    {
        $this->_abbrv = (string)$_abbrv;
        return $this;
    }

    public function getName() 
    {
        return $this->_name;
    }

    public function setName($_name) 
    {
        $this->_name = (string) $_name;
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
    
    public function getOldId() {
        return $this->_oldId;
    }

    public function setOldId($_oldId) {
        $this->_oldId = $_oldId;
        return $this;
    }

        
    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'abbrv' => $this->getAbbrv(),
            'name' => $this->getName(),
        	'schemename' => $this->getSchemename());
    }
    
    public function findByAbbr($abbr) {
        $this->getMapper()->findByAbbr($abbr, $this);
        return $this;
    }
    public function getSchemename(){
    	return $this->_schemename;
    }
    public function setSchemename($_schemename)
    {
    	$this->_schemename = (string) $_schemename;
    	return $this;
    }

}