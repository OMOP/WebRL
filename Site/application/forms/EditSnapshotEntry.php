<?php
/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    31 Jan 2011

    Class, representing a form for creating new source.

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
class Application_Form_EditSnapshotEntry extends Zend_Form
{
    
    protected $_id;
    
    public function init()
    {
        $mapper = new Application_Model_SnapshotEntryMapper();
        $this->setMethod('post');
        $this->setAttrib('class', 'form2');
        
        $this->addPrefixPath(
            'Application_Form_Decorator', 
            APPLICATION_PATH.'/forms/decorators/', 'decorator'
        );
        
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
        
        $this->addElement(
            'text', 
            'description', 
            array(
                'label' => 'Description',
                'class' => 'text',
                'required' => true,
                'maxlength' => 50,
                'validators' => array(
                    array('StringLength', false, array(0, 50)),
                    $mapper->getUniqueDescriptionValidator($this->getId())
                )
            )
        );

        $this->addElement(
            'text', 
            'ebs', 
            array(
                'label' => 'EBS Snapshot',
                'class' => 'text',
                'required' => true,
                'maxlength' => 50,
                'validators' => array(
                    array('StringLength', false, array(0, 50))
                )
            )
        );
        $digitsValidator = new Zend_Validate_Digits();
        $digitsValidator->setMessages(array("notDigits" => "Contains non-numeric characters"));

        $this->addElement(
            'text', 
            'ebsSize', 
            array(
                'label' => 'Snapshot Size',
                'class' => 'text',
                'required' => true,
                'maxlength' => 50,
                'validators' => array(
                    $digitsValidator,
                    array('StringLength', false, array(0, 50))
                )
            )
        );
        
        $this->addDisplayGroup(
            array(
                'description',
                'ebs',
                'ebsSize'
            ),
            'main'
        );
        
        $this->main->setDecorators(
            array(
                'FormElements',
                'Fieldset',
                array('ViewScript',
                    array(
                        'viewScript' => '_round_corners.phtml',
                        'placement' => 'prepend',
                        'title' => ''
                    )
                ),
                array('HtmlTag', array('tag' => 'div', 'class' => 'left'))
            )
        );
        
        $this->addElement(
            'submit',
            'button_submit', 
            array(
                'ignore' => true,
                'value' => 'Submit',
                'class' => 'button_90',
                'decorators' => array(
                    'Submit',
                    array('HtmlTag', array('tag' => 'div', 'class' => 'right')))
            )
        );
        
    }
    
    public function getId()
    {
        return $this->_id;
    }
    
    public function setId($id)
    {
        $this->_id = $id;
    }
    
}