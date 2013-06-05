<?php

/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    28-Feb-2011

    Validates if organization has enough money for user

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
class Application_Validator_OrganizationBudgetLimit extends Zend_Validate_Abstract
{
    const ERROR='error';
    
    
    protected $_messageTemplates = array(
        self::ERROR => "User Charge Limit should not exceed Organization Budget."
    );
    
    public function isValid($value, $context = null)
    {
        if ($context['organizationId']) {
            $org = new Organization();
            $org->load("organization_id = ?", array($context['organizationId']));
            if ($value > $org->organization_budget) {
                $this->_error(self::ERROR);
                return false;
            }
        }
        return true;
    }
    
}