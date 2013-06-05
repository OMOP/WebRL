<?php

/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    08-Feb-2011

    Validator for Dataset Storage Types

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
class Application_Validator_DatasetType extends Zend_Validate_Abstract
{
    protected $_type;
    
    public function __construct($type)
    {
        $this->_type = $type;
    }
    
    
    public function isValid($value, $context = null)
    {
        if ($this->_type == 'S3' && $context['storageType'] == 1) {
            $empty = new Zend_Validate_NotEmpty();
            $result = $empty->isValid($value);
            $this->_messages = $empty->getMessages();
            return $result;
        } elseif ($this->_type == 'EBS' && $context['storageType'] == 0) {
            $empty = new Zend_Validate_NotEmpty();
            $result = $empty->isValid($value);
            $this->_messages = $empty->getMessages();
            return $result;
            
        }
        
        return true;
    }
    
}