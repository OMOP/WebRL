<?php
/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    24 Jan 2011

    Class, representing a form for editing existing system instance.

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

class Application_Form_EditStorageInstance extends Zend_Form
{
    protected $_id;
    
    public function init()
    {        
        $this->setMethod('post');
        $this->setAttrib('class', 'form2');
        
        $prefixPath = APPLICATION_PATH.'/forms/decorators/';
        $this->addPrefixPath('Application_Form_Decorator', $prefixPath, 'decorator');
        
        $this->setDecorators(
            array(
                'FormElements',
                'Form'
            )
        );

        $this->setElementDecorators(
            array(
                'ViewHelper',
                'Label',
                'Errors',
                array('HtmlTag', array('tag' => 'div'))
            )
        );

        $this->setElementFilters(array('HtmlEntities'));
        
        //@todo add validator
        $mapper = new Application_Model_SystemInstanceMapper();
        $instances = $mapper->fetchAll();
        $instancesData = array(); 
        foreach ($instances as $i) {
            $uiKey = $i->getId(); 
            $uiLabel = $i->getName();
            $instancesData[$uiKey] = $uiLabel; 
        }
        
        $systemInstanceOptions = array(
            'label' => 'System Instance',
            'multiOptions' => $instancesData,
            'required' => true
        );
        $this->addElement('select', 'systemInstance', $systemInstanceOptions);
        
        $nameOptions = array(
            'label' => 'Name',
            'class' => 'text_disable',
            'readonly' => 'readonly'
        );
        $this->addElement('text', 'name', $nameOptions);
        
        $hostOptions = array(
            'label' => 'Host',
            'class' => 'text_disable',
            'readonly' => 'readonly'
        );
        $this->addElement('text', 'host', $hostOptions);
        
        $keyNameOptions = array(
            'label' => 'Key Name',
            'class' => 'text_disable',
            'readonly' => 'readonly'
        );
        $this->addElement('text', 'keyName', $keyNameOptions);
        
        $instanceTypeOptions = array(
            'label' => 'Instance Type',
            'class' => 'text_disable',
            'readonly' => 'readonly'
        );
        $this->addElement('text', 'instanceType', $instanceTypeOptions);
        
        $osFamilyOptions = array(
            'label' => 'OS Family',
            'class' => 'text_disable',
            'readonly' => 'readonly'
        );
        $this->addElement('text', 'osFamily', $osFamilyOptions);
        
        $ebsAttachedOptions = array(
            'label' => 'EBS Attached',
            'class' => 'text_disable',
            'readonly' => 'readonly',
            'value' => 4,
        );
        $this->addElement('text', 'ebsAttached', $ebsAttachedOptions);
        
        $totalStorageOptions = array(
            'label' => 'Total Storage (Gb)',
            'class' => 'text_disable',
            'readonly' => 'readonly',
            'value' => 66,
        );
        $this->addElement('text', 'totalStorage', $totalStorageOptions);
        
        $mainElements = array(
            'systemInstance',
            'name',
            'host',
            'keyName',
            'instanceType',
            'osFamily',
            'ebsAttached',
            'totalStorage',
        );
        $this->addDisplayGroup($mainElements, 'main');
        
        $this->main->setDecorators(
            array(
                'FormElements',
                'Fieldset',
                array(
                    'ViewScript', 
                    array(
                        'viewScript' => '_round_corners.phtml',
                        'placement' => 'prepend',
                        'title' => ''
                    )
                ),
                array('HtmlTag', array('tag' => 'div', 'class' => 'left'))
            )
        );
        
        $mappingInformation = array(
            array(
                'id' => 1,
                'device' => '/dev/sdf1',
                'size' => 5,
                'folder' => '/var/storage/admin_x',
            ),
            array(
                'id' => 2,
                'device' => '/dev/sdf2',
                'size' => 20,
                'folder' => '/var/storage/andreyk',
            ),
            array(
                'id' => 3,
                'device' => '/dev/sdf3',
                'size' => 20,
                'folder' => '/var/storage/andrii',
            ),
            array(
                'id' => 4,
                'device' => '/dev/sdf4',
                'size' => 20,
                'folder' => '/var/storage/mk_admin',
            ),
            array(
                'id' => 5,
                'device' => '/dev/sdh1',
                'size' => 1,
                'folder' => '/var/storage/mk_user',
            ),
        );
        
        $displayGroupElements = array(); 
        foreach ($mappingInformation as $info) {
            $elementName = 'ebs'.$info['id'];
            $elementOptions = array(
                'label' => 'EBS '.$info['id'].' ('.$info['device'].')',
                'class' => 'text_disable',
                'readonly' => 'readonly',
                'value' => $info['size'],
            ); 
            $this->addElement('text', $elementName, $elementOptions);
            $displayGroupElements[] = $elementName;
            
            $elementName = 'ebsMapping'.$info['id'];
            $elementOptions = array(
                'label' => 'Folder mapping '.$info['id'],
                'class' => 'text_disable',
                'readonly' => 'readonly',
                'value' => $info['folder'],
            ); 
            $this->addElement('text', $elementName, $elementOptions);
            $displayGroupElements[] = $elementName;
        }        
        
        $this->addDisplayGroup($displayGroupElements, 'storageLayout');
        
        $this->storageLayout->setDecorators(
            array('FormElements', 'Fieldset', array(
                    'ViewScript', 
                    array(
                        'viewScript' => '_round_corners.phtml',
                        'placement' => 'prepend',
                        'title' => ''
                    )),
                array('HtmlTag', array('tag' => 'div', 'class' => 'left_'))
            )
        );
        
        $submitOptions = array(
            'ignore' => true,
            'value' => 'Submit',
            'class' => 'button_90',
            'decorators' => array(
                'Submit',
                array('HtmlTag', array('tag' => 'div', 'class' => 'right')))
        );
        $this->addElement('submit', 'button_submit', $submitOptions);
    }
    
    public function getId()
    {
        return $this->_id;
    }
    
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }
}
