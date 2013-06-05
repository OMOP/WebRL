<?php

/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    28-Feb-2011

    Validates if organization has enough space for new user

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
require_once "OMOP/WebRL/OrganizationManager.php";
class Application_Validator_OrganizationUserLimit extends Zend_Validate_Abstract
{
    const ERROR='error';
    
    private $_id;
    
    public function __construct($id) {
        $this->setId($id);
    }
    
    protected $_messageTemplates = array(
        self::ERROR => "Organization reach limit of total users."
    );
    
    public function isValid($value, $context = null)
    {
        if ($context['active'] && $value != 0) {
            $increment = 0;
            if ($this->getId()) {
                $user = new Application_Model_User();
                $user->find($this->getId());
                if ($user->getOrganizationId() == $value) {
                    if (! $user->getActive()) {
                        $increment = 1;
                    }
                } else {
                    $increment = 1;
                }
            } else {
                $increment = 1;
            }
            
            $organization = new Organization();
            $organization->load("organization_id = ?", array($value));
            $om = new OrganizationManager();
            $activeUsers = $om->get_active_users($value);
            if ($activeUsers + $increment > $organization->organization_users_limit) {
                $this->_error(self::ERROR);
                return false;
            }
        }
        
        return true;
    }
    
    public function getId() {
        return $this->_id;
    }
    
    
    public function setId() {
        $this->_id = $id;
    }

}