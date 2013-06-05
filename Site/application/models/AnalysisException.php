<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    20 Jan 2011

    Exception class used in AnalysisMapper to handle validation errors

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
class Application_Model_AnalysisException extends Exception {
    
    protected $_fieldName;
    protected $_validatorMessages;

    public function getFieldName() {
        return $this->_fieldName;
    }

    public function setFieldName($_fieldName) {
        $this->_fieldName = $_fieldName;
    }

    public function getValidatorMessages() {
        return $this->_validatorMessages;
    }

    public function setValidatorMessages($_validatorMessages) {
        $this->_validatorMessages = $_validatorMessages;
    }

    
}

?>
