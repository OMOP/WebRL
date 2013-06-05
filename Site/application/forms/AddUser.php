<?php

/* ==============================================================================
  OMOP - Cloud Research Lab

  Observational Medical Outcomes Partnership
  28-Feb-2011

  Form for adding a user

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

  ============================================================================== */

class Application_Form_AddUser extends Application_Form_EditUser {


    public function init() {
        
        parent::init();
        
        $this->removeElement('createDate');
        $this->removeElement('resetPassword');
        $this->removeElement('chargeRemaining');
        $this->removeElement('storageUsage');
        $this->removeElement('storageHost');
        $this->removeElement('storageFolder');
        
        $this->setDefault('chargeLimit', '1000');
        $this->setDefault('storageSize', '20');
        $this->setDefault('maxInstances', '1');
        $this->setDefault('active', true);
        $this->setDefault('svnAccess', true);
        
        $datasetMapper = new Application_Model_DatasetMapper();
        $defaultDataset = $datasetMapper->getDefaultDatasetId();
        $this->setDefault('datasetAccess', array($defaultDataset));
        
        $imageMapper = new Application_Model_SoftwareTypeMapper();
        $defaultImage = $imageMapper->getDefaultTypeId();
        $this->setDefault('imageAccess', array($defaultImage));
        
        if (! $this->getElement('organizationId')) {
            $this->addElement('hidden', 'organizationId');
            $orgValid = new Application_Validator_OrganizationUserLimit(
                $this->getId()
            );
            $this->getElement('loginId')->addValidator($orgValid);
        }
        
        $this->getElement('title')->setOptions(
            array('description' => "Account information including password will be emailed to the user.")
        );
        $this->getElement('title')->addDecorator(
            'Description',
            array(
                'tag' => 'div',
                'class' => 'fieldset-description'
            )
        );
        
    }


}