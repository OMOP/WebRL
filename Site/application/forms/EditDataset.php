<?php

/* ==============================================================================
  OMOP - Cloud Research Lab

  Observational Medical Outcomes Partnership
  08-Feb-2011

  Form for editing dataset type

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

class Application_Form_EditDataset extends Zend_Form {

    protected $_encryptedFlag;
    protected $_id = null;

    public function init() {
        $mapper = new Application_Model_DatasetMapper();
        $this->setMethod('post');
        $this->setAttrib('class', 'form2');

        $this->addPrefixPath(
                'Application_Form_Decorator', APPLICATION_PATH . '/forms/decorators/', 'decorator'
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
                'maxlength' => 256,
                'validators' => array(
                    array('StringLength', false, array(0, 256)),
                    $mapper->getUniqueDescriptionValidator($this->getId())
                )
            )
        );

        $this->addElement(
            'select',
            'storageType',
            array(
                'label' => 'Storage Type',
                'required' => true,
                'multiOptions' => array(
                    0 => 'EBS',
                    1 => 'S3'
                )
            )
        );

        $this->addElement(
            'text',
            'ebsSnapshot',
            array(
                'label' => 'EBS Snapshot',
                'required' => true,
                'class' => 'text',
                'maxlength' => 256,
                'validators' => array(
                    new Application_Validator_DatasetType('EBS'),
                    array('StringLength', false, array(0, 256))
                ),
                'decorators' => array(
                    'ViewHelper',
                    'Label',
                    'Errors',
                    array('HtmlTag', array('tag' => 'div', 'id' => 'EBS_wrap', 'style' => 'display:none'))
                )
            )
        );

        $this->addElement(
            'text',
            's3Bucket',
            array(
                'label' => 'Bucket',
                'required' => true,
                'class' => 'text',
                'maxlength' => 256,
                'validators' => array(
                    new Application_Validator_DatasetType('S3'),
                    array('StringLength', false, array(0, 256))
                ),
                'decorators' => array(
                    'ViewHelper',
                    'Label',
                    'Errors',
                    array('HtmlTag', array('tag' => 'div', 'id' => 'Bucket_wrap', 'style' => 'display:none'))
                )
            )
        );
        
        $this->addElement(
            'text',
            'methodDatasetName',
            array(
                'label' => 'Method Dataset Name',
                'required' => true,
                'class' => 'text',
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
            'datasetSize',
            array(
                'label' => 'Dataset Size (GB)',
                'required' => true,
                'class' => 'text',
                'maxlength' => 15,
                'validators' => array(
                    $digitsValidator,
                    array('StringLength', false, array(0, 15))
                )
            )
        );

        $this->addElement(
            'text',
            'attachFolder',
            array(
                'label' => 'Attach Folder',
                'required' => true,
                'class' => 'text',
                'maxlength' => 256,
                'validators' => array(
                    array('StringLength', false, array(0, 256))
                )
            )
        );
        
        $this->addElement(
            'checkbox',
            'encryptedFlag',
            array(
                'label' => 'Encrypted Flag',
            )
        );
        $this->addElement(
            'password',
            'password',
             array(
                 'label' => 'Password',
                 'class' => 'text',
                 'required' => true,
                 'maxlength' => 20,
                 'validators' => array(
                     new Application_Validator_DatasetPassword($this->getEncryptedFlag()),
                     array('StringLength', false, array(0, 20))
                 ),
                'decorators' => array(
                    'ViewHelper',
                    'Label',
                    'Errors',
                    array('HtmlTag', array('tag' => 'div', 'id' => 'Password_wrap', 'style' => 'display:none'))
                )
             )
        );
        
        $this->getElement('password')->setAutoInsertNotEmptyValidator(false);
        $this->getElement('s3Bucket')->setAutoInsertNotEmptyValidator(false);
        $this->getElement('ebsSnapshot')->setAutoInsertNotEmptyValidator(false);
        
        
        $this->addDisplayGroup(
            array(
                'description',
                'storageType',
                'ebsSnapshot',
                's3Bucket',
                'methodDatasetName',
                'datasetSize',
                'attachFolder',
                'encryptedFlag',
                'password'
            ), 'main'
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
                'submit', 'button_submit', array(
            'ignore' => true,
            'value' => 'Submit',
            'class' => 'button_90',
            'decorators' => array(
                'Submit',
                array('HtmlTag', array('tag' => 'div', 'class' => 'right')))
                )
        );
    }

    public function getEncryptedFlag() {
        return $this->_encryptedFlag;
    }

    public function setEncryptedFlag($flag) {
        $this->_encryptedFlag = $flag;
    }
    
    public function setId($id) {
        $this->_id = $id;
    }

    public function getId() {
        return $this->_id;
    }    

}