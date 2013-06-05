<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    23 December 2010

    Model of one run result.

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
class Application_Model_SupplementalFile extends Application_Model_Abstract
{

    protected $_id;
    protected $_methodAbbr;
    protected $_fileName;
    protected $_type;

    private $_datasetTemplates = array('<Dataset>');
    private $_datasetDefaultTemplate = '<Dataset>';
    private $_datasetTemplate = null;

    public function __construct(array $options = null)
    {
        parent::__construct($options);
    }

    public function getId() {
        return $this->_id;
    }

    public function setId($_id) {
        $this->_id = $_id;
        return $this;
    }

    public function getMethodAbbr() {
        return $this->_methodAbbr;
    }

    public function setMethodAbbr($_methodAbbr) {
        $this->_methodAbbr = $_methodAbbr;
        return $this;
    }

    public function getFileName() {
        return $this->_fileName;
    }

    public function setFileName($_fileName) {
        $this->_fileName = $_fileName;
        return $this;
    }
    
    public function getType() {
        return $this->_type;
    }

    public function setType($_type) {
        $this->_type = $_type;
        return $this;
    }

    
    
    public function getConvertedFileName($dataset) {
        return str_ireplace($this->_datasetTemplates, $dataset, $this->getFileName());
    }
    
    public function getLoadFunctionName() {
        
        return "_loadSuppl".$this->getType()."For".$this->getMethodAbbr();
        
    }

    

}