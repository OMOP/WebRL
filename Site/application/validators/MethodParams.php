<?php
/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    20 Dec 2010

    Form validator for CreateMethod and EditMethod forms.
    Used for method parameters validation.

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

class Application_Validator_MethodParams extends Zend_Validate_Abstract
{
    const ERROR='error';

    public static $form;

    private $_paramNum;

    public function __construct($paramNum)
    {
        $this->_paramNum = intval($paramNum);
    }

    protected $_messageTemplates = array(
        self::ERROR => "Previous parameter is undefined"
    );

    public function isValid($value)
    {
        if ($this->_paramNum > 1) {
            $prefix = 'param';
            $index = $prefix.$this->_paramNum;
            $element = self::$form->getElement($index);
            if ($element->getValue() != '-') {
                $prevIndex = $prefix.($this->_paramNum - 1);
                $prevElement = self::$form->getElement($prevIndex);
                if ($prevElement->getValue() == '-') {
                    $this->_error();
                    return false;
                }
            }
        }
        return true;
    }
}