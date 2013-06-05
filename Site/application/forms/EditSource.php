<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    27 Jan 2011

    Class, representing a form for editing existing source.

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
class Application_Form_EditSource extends Zend_Form
{
    
    protected $_id;
    
    public function init()
    {
        
        $this->setMethod('post');
        $this->setAttrib('class', 'form2');
        
        $this->addPrefixPath('Application_Form_Decorator', APPLICATION_PATH.'/forms/decorators/', 'decorator');
        $mapper = new Application_Model_SourceMapper();

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
        $digitsValidator = new Zend_Validate_Digits();
        $digitsValidator->setMessages(array("notDigits" => "Contains non-numeric characters"));
        
        $this->addElement('text', 'id', array(
            'label' =>  'Id',
            'class' => 'text',
            'maxlength' => 2,
            'required' => true,
            'validators' => array(
                $digitsValidator,
                array('StringLength', false, array(0, 2)),
                $mapper->getUniqueIdValidator($this->getId())
            )
        ));
        
        $this->addElement('text', 'abbrv', array(
            'label' => 'Abbreviation',
            'class' => 'text',
            'maxlength' => 30,
            'required' => true,
            'validators' => array(
                array('StringLength', false, array(0, 30)),
                $mapper->getUniqueAbbrValidator($this->getId())
            )
        ));
        
        $this->addElement('text', 'name', array(
            'label' => 'Name',
            'class' => 'text',
            'maxlength' => 64,
            'required' => true,
            'validators' => array(
                array('StringLength', false, array(0, 64)),
                $mapper->getUniqueNameValidator($this->getId())
            )
        ));

        $this->addElement('text', 'schemename', array(
                    'label' => 'DB Schema Name',
                    'class' => 'text',
                    'maxlength' => 30,
                    'validators' => array(
        array('StringLength', false, array(0, 30))
        )
        ));
        
        
        $this->addDisplayGroup(array(
            'id',
            'abbrv',
            'name',
            'schemename'
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

    public function setId($_id) {
        $this->_id = $_id;
        return $this;
    }


    
}
