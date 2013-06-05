<?php
/*================================================================================
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

================================================================================*/
class Application_Form_EditSystemInstance extends Zend_Form
{
    
    protected $_id;
    
    public function init() {
        
        $this->setMethod('post');
        $this->setAttrib('class', 'form2');
        
        $this->addPrefixPath('Application_Form_Decorator', APPLICATION_PATH.'/forms/decorators/', 'decorator');
        
        $this->setDecorators(array(
            'FormElements',
            'Form'
        ));

        $this->setElementDecorators(array(
            'ViewHelper',
            'Label',
            'Errors',
            array('HtmlTag', array('tag' => 'div'))
        ));

        $this->setElementFilters(array('HtmlEntities'));
        
        
        //@todo add validator
        $mapper = new Application_Model_SystemInstanceMapper();
        $validator = $mapper->getUniqueNameValidator($this->getId());
        
        $this->addElement('text', 'name', array(
            'label' => 'Name',
            'class' => 'text',
            'maxlength' => 50,
            'required' => true,
            'validators' => array(
                $validator,
                array('StringLength', false, array(0, 50)),
            )
        ));
        
        $validator = new Application_Validator_AmazonInstanceDns();
        $this->addElement('text', 'host', array(
            'label' => 'Host',
            'class' => 'text',
            'maxlength' => 50,
            'required' => true,
            'validators' => array(
                $validator,
                $mapper->getUniqueHostValidator($this->getId()),
                array('StringLength', false, array(0, 50))
            )
        ));
        
        $this->addElement('text', 'keyName', array(
            'label' => 'Key Name',
            'class' => 'text_disable',
            'readonly' => 'readonly'
        ));
        
        $this->addElement('text', 'instanceType', array(
            'label' => 'Instance Type',
            'class' => 'text_disable',
            'readonly' => 'readonly'
        ));
        
        $this->addElement('select', 'osFamily', array(
            'label' => 'OS Family',
            'multiOptions' => array('linux' => 'Linux',
                                    'windows' => 'Windows'),
            'required' => true
        ));
        
        $this->addElement('text', 'launchDate', array(
            'label' => 'Launch Date',
            'class' => 'text_disable',
            'readonly' => 'readonly'
        ));

        $this->addElement('text', 'registerDate', array(
            'label' => 'Registration Date',
            'class' => 'text_disable',
            'readonly' => 'readonly'
        ));
        
        $this->addElement('text', 'endDate', array(
            'label' => 'End Date',
            'class' => 'text_date',
            'validators' => array(
                array('Date', false)
            )
        ));
        
        
        $this->addDisplayGroup(array(
            'name',
            'host',
            'keyName',
            'instanceType',
            'osFamily',
            'launchDate',
            'registerDate',
            'endDate'
        ), 'main');
        
        $this->main->setDecorators(array(
            'FormElements',
            'Fieldset',
            array('ViewScript', array(
                    'viewScript' => '_round_corners.phtml',
                    'placement' => 'prepend',
                    'title' => ''
            )),
            array('HtmlTag', array('tag' => 'div', 'class' => 'left'))
        ));
        
        $this->addElement('submit', 'button_submit', array(
            'ignore' => true,
            'value' => 'Submit',
            'class' => 'button_90',
            'decorators' => array(
                'Submit',
                array('HtmlTag', array('tag' => 'div', 'class' => 'right')))
        ));
        
        
    }
    
    public function getId() {
        return $this->_id;
    }
    
    public function setId($id) {
        $this->_id = $id;
        return $this;
    }
}