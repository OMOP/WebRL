<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 Dec 2010

    Class, representing a form for editing existing method. Used by MethodController.

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
class Application_Form_EditMethod extends Zend_Form
{

    protected $_id;
    protected $_organizations;

    public function init()
    {
        $this->setMethod('post');
        $this->setAttrib('class', 'form2');

        $this->addPrefixPath('Application_Form_Decorator', APPLICATION_PATH.'/forms/decorators/', 'decorator');
        Application_Validator_MethodParams::$form = $this;
        $mapper = new Application_Model_MethodMapper();

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


        $validator = $mapper->getUniqueAbbrValidator($this->getId());

        $this->addElement('text', 'abbrv', array(
            'label' =>  'Abbreviation',
            'class' => 'text',
            'maxlength' => 10,
            'required' => true,
            'validators' => array(
                $validator,
                array('StringLength', false, array(0, 10))
            )
        ));

        $this->addElement('text', 'name', array(
            'label' => 'Name',
            'class' => 'text',
            'required' => true,
            'maxlength' => 64,
            'validators' => array(
                array('StringLength', false, array(0, 64))
            )
        ));

        $this->addElement('checkbox', 'access', array(
           'label' => 'Public',
           'value' => 1
        ));

        $filePartsLength = 64;
        $this->addElement('text', 'fileNameFormat', array(
            'label' =>  'Run file name format',
            'class' => 'text',
            'maxlength' => $filePartsLength,
            'required' => false,
            'validators' => array(
                $validator,
                array('StringLength', false, array(0, $filePartsLength))
            )
        ));

        $params = array();
        for ($param = 1; $param <= 25; $param += 1) {
            $this->addElement('text', 'param'.$param, array(
                'label' => 'Parameter '.$param,
                'class' => 'text',
                'value' => '',
                'maxlength' => 128,
                'required' => false,
                'validators' => array(
                    array('StringLength', false, array(0, 128))
                )
            ));
            $validator = new Application_Validator_MethodParams($param);
            $this->getElement('param'.$param)->addValidator($validator);
            $params[] = 'param'.$param;
        }


        if ($this->getOrganizatons()) {
            $orgs = new Zend_Form_Element_MultiCheckbox('organizations', array(
                'multiOptions' => $this->getOrganizatons()
            ));

            $orgs->addPrefixPath('Application_Decorator', 'decorators/', 'decorator');
            $orgs->setDecorators(array('MultiCheckbox'));

            $this->addElement($orgs);

            $this->addDisplayGroup(array('organizations'), 'orgs');
            $this->orgs->setDecorators(array(
                'FormElements',
                'Fieldset',
                array('ViewScript', array(
                        'viewScript' => '_round_corners.phtml',
                        'placement' => 'prepend',
                        'title' => 'Organization Permissions'
                    )),
                array('HtmlTag', array('tag' => 'div', 'class' => 'left_', 'id' => 'div-orgs', 'style' => 'opacity:0'))
            ));
        }

        $this->addDisplayGroup($params, 'params');

        $this->params->setDecorators(array(
            'FormElements',
            'Fieldset',
            array('ViewScript', array(
                        'viewScript' => '_round_corners.phtml',
                        'placement' => 'prepend',
                        'title' => 'Method Parameters'
                )),
            array('HtmlTag', array('tag' => 'div', 'class' => 'left_'))
        ));

        $this->addDisplayGroup(array(
            'id',
            'abbrv',
            'name',
            'access'
        ), 'basic');
        
        $this->basic->setDecorators(array(
                'FormElements',
                'Fieldset',
                array('ViewScript', array(
                        'viewScript' => '_round_corners.phtml',
                        'placement' => 'prepend',
                        'title' => 'Method Description'
                )),
                array('HtmlTag', array('tag' => 'div', 'class' => 'left'))
            ));
        
        $this->addDisplayGroup(array(
            'fileNameFormat',
        ), 'resultFile');
        
        $this->resultFile->setDecorators(array(
            'FormElements',
            'Fieldset',
            array('ViewScript', array(
                    'viewScript' => '_round_corners.phtml',
                    'placement' => 'prepend',
                    'title' => 'Result File'
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

    public function setId($id) {

        $this->_id = $id;

    }
    public function getId() {

        return $this->_id;

    }

    public function setOrganizations($orgs) {

        $this->_organizations = $orgs;
    }

    public function getOrganizatons() {

        return $this->_organizations;
    }

    public function validateParams($value, $param_num) {
        if ($param_num > 1) {
            if ($this->getElement('param'.($param_num - 1)) == '') {
                return false;
            }
        }
        return true;
    }

}

