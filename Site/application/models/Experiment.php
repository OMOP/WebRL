<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 Dec 2010

    Model, representing method entity.

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
class Application_Model_Experiment extends Application_Model_Abstract
{

    protected $_id;
    protected $_name;
    protected $_descr;
    protected $_directoryPattern;
    protected $_sourceDrugEra;
    protected $_sourceConditionEra;
    protected $_oldId;
    
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
            $this->setMapper('Application_Model_ExperimentMapper');
        }
        return $this->_mapper;
    }


    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function setId($id) {
        $this->_id = $id;
        return $this;
    }

    public function getId() {
        return $this->_id;
    }

    public function setName($name) {
        $this->_name = (string) $name;
        return $this;
    }

    public function getName() {
        return $this->_name;
    }

    public function setDescr($descr) {
        $this->_descr = $descr;
        return $this;
    }

    public function getDescr() {
        return $this->_descr;
    }

    public function setDirectoryPattern($pattern) {
        $this->_directoryPattern = $pattern;
        return $this;
    }

    public function getDirectoryPattern() {
        return $this->_directoryPattern;
    }

    public function setSourceDrugEra($sourceDrugEra) {
        $this->_sourceDrugEra = $sourceDrugEra;
        return $this;
    }

    public function getSourceDrugEra() {
        return $this->_sourceDrugEra;
    }

    public function setSourceConditionEra($sourceConditionEra) {
        $this->_sourceConditionEra = $sourceConditionEra;
        return $this;
    }

    public function getSourceConditionEra() {
        return $this->_sourceConditionEra;
    }

    public function find($id) {
        return $this->getMapper()->find($id, $this);
    }

    public function findByAbbr($abbr) {
        $this->getMapper()->findByAbbr($abbr, $this);
        return $this;
    }
    public function getOldId() {
        return $this->_oldId;
    }

    public function setOldId($_oldId) {
        $this->_oldId = $_oldId;
    }

        public function save() {
        $this->getMapper()->save($this);
    }
}

