<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    24 Jan 2011

    Class, representing a form for creating new system instance.

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
class Application_Form_AddStorageInstance extends Application_Form_EditStorageInstance
{
    
    public function init()
    {
        parent::init();
        
        $this->main->removeElement('osFamily');
        $this->main->removeElement('name');
        $this->main->removeElement('host');
        $this->main->removeElement('keyName');
        $this->main->removeElement('instanceType');
        $this->main->removeElement('ebsAttached');
        $this->main->removeElement('totalStorage');
        
        $this->storageLayout->removeElement('ebs1');
        $this->storageLayout->removeElement('ebs2');
        $this->storageLayout->removeElement('ebs3');
        $this->storageLayout->removeElement('ebs4');
        $this->storageLayout->removeElement('ebs5');
        
        $this->storageLayout->removeElement('ebsMapping1');
        $this->storageLayout->removeElement('ebsMapping2');
        $this->storageLayout->removeElement('ebsMapping3');
        $this->storageLayout->removeElement('ebsMapping4');
        $this->storageLayout->removeElement('ebsMapping5');
        
        $this->removeDisplayGroup('storageLayout');
    }   
}
