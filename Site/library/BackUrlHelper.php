<?php

/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    05-Mar-2011

    Helps to store back URL's

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
class BackUrlHelper extends Zend_Controller_Action_Helper_Abstract
{
    private $_name = "BackUrl";
    
    public function saveReference()
    {
            $url = $this->getRequest()->HTTP_REFERER;
        $history = new Zend_Session_Namespace('history');
        $history->last = $url;
    }
    
    public function getReferer()
    {
        $history = new Zend_Session_Namespace('history');
        if (isset($history->last)) {
            $url = $history->last;
            $history->unsetAll();
            return $url;
        }
        
        return '';
    }
    
    public function getName()
    {
        return $this->_name;
    }
}