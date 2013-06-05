<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    25 Dec 2010

    Class, representing a form for editing existing method replacement parameters. 
    Used by MethodReplacementController.

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
class Application_Form_EditMethodReplacement extends Zend_Form
{

    protected $_id;
    protected $_organizations;

    public function init()
    {
        $this->setMethod('post');
        $this->setAttrib('class', 'form2');

        $this->addPrefixPath('Application_Form_Decorator', APPLICATION_PATH.'/forms/decorators/', 'decorator');
        Application_Validator_MethodParams::$form = $this;

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
        
        $this->addElement('hidden', 'id', array(
            'label' =>  'Id',
            'class' => 'text_disable',
            'readonly' => 'readonly'
        ));


        $mapper = new Application_Model_MethodReplacementMapper();
        $validator = $mapper->getUniqueNameValidator($this->getId());

        $user_id = $this->addElement('hidden', 'userId', array(
            'value' => Membership::get_current_user()->user_id
        ));

        $this->addElement('text', 'name', array(
            'label' => 'Name',
            'class' => 'text',
            'required' => true,
            'maxlength' => 50,
            'validators' => array(
        		$validator,
                array('StringLength', false, array(0, 50))
            )
        ));

        $this->addElement('checkbox', 'sharedFlag', array(
           'label' => 'Public',
           'value' => 1
        ));

        $this->addElement('textarea', 'hoiReplacement', array(
            'label' => 'Conditions set',
            'class' => 'text',
            'required' => true,
            'rows' => 10,
       	 	'cols' => 10,
            'validators' => array(
        		$validator,
                array('StringLength', false, array(0, 64))
            )
        ));

        $this->addElement('textarea', 'doiReplacement', array(
            'label' => 'Drugs set',
            'class' => 'text',
            'required' => true,
            'rows' => 10,
       	 	'cols' => 10,
            'validators' => array(
        		$validator,
                array('StringLength', false, array(0, 64))
            )
        ));

        $this->addDisplayGroup(array(
            'id',
            'userId',
            'name',
            'sharedFlag',
//        	'button_submit'
        ), 'basic');

        $this->basic->setDecorators(array(
                'FormElements',
                'Fieldset',
                array('ViewScript', array(
                        'viewScript' => '_round_corners.phtml',
                        'placement' => 'prepend',
                        'title' => 'Set'
                )),
                array('HtmlTag', array('tag' => 'div', 'class' => 'left', 'id' => 'replacement_fieldset'))
            ));
        

        $this->addDisplayGroup(array(
        	'hoiReplacement',
        	'doiReplacement',
        ), 'replacement_parameters');

        $this->replacement_parameters->setDecorators(array(
                'FormElements',
                'Fieldset',
                array('ViewScript', array(
                        'viewScript' => '_round_corners.phtml',
                        'placement' => 'prepend',
                        'title' => 'Replacement parameters'
                )),
                array('HtmlTag', array('tag' => 'div', 'class' => 'left_'))
            ));
        
        $this->addElement('submit', 'button_submit', array(
            'ignore' => true,
            'value' => 'Submit',
            'class' => 'button_90',
            'decorators' => array(
                'Submit',
                array('HtmlTag', array('tag' => 'div', 'class' => 'right', 'style'=>'margin-right:25px')))
        ));
        

    }

    public function setId($id) {

        $this->_id = $id;

    }
    public function getId() {

        return $this->_id;

    }

}

