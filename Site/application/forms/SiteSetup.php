<?php

/* ==============================================================================
  OMOP - Cloud Research Lab

  Observational Medical Outcomes Partnership
  10-Feb-2011

  Form for Site Setup page

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

class Application_Form_SiteSetup extends Zend_Form {

    protected $_id;

    public function init() {
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
                    'Label',
                    'ViewHelper',
                    'Errors',
                    array('HtmlTag', array('tag' => 'div'))
                )
        );
        //Get silent hostname validator for email validator
        $dnsValidator = new Zend_Validate_Hostname();
        $dnsValidator->setMessage(null);
        
        $emailValidator = new Zend_Validate_EmailAddress();
        $emailValidator->setHostnameValidator($dnsValidator);
        
        $this->setElementFilters(array('HtmlEntities'));
        $this->addElement(
            'text',
            'adminEmail',
            array(
                'label' => 'Admin email',
                'class' => 'text',
                'required' => true,
                'maxlength' => 128,
                'validators' => array(
                    array('StringLength', false, array(0, 128)),
                    $emailValidator
                )
            )
        );

        $this->addElement(
            'text',
            'replyTo',
            array(
                'label' => 'Reply-to email',
                'class' => 'text',
                'required' => true,
                'maxlength' => 128,
                'validators' => array(
                    array('StringLength', false, array(0, 128)),
                    $emailValidator
                )
            )
        );
        
        $this->addElement(
            'select',
            'timezone',
            array(
                'label' => 'Timezone',
                'multiOptions' =>
                    Application_Model_SiteConfig::getTimezones()
            )
        );

        $decimalValidator = new Zend_Validate_Regex('/^\d{1,14}(\.\d{0,4})?$/');
        $decimalValidator->setMessage('Invalid decimal value');
        
        $this->addElement(
            'text',
            'defaultMoneyLimit',
            array(
                'label' => 'Default Limit $',
                'class' => 'text',
                'required' => true,
                'maxlength' => 19,
                'validators' => array(
                    $decimalValidator
                )
            )
        ); 
        
        $decimalValidator = new Zend_Validate();
        $decimalValidator->addValidator(new Zend_Validate_Regex('/\d{1,18}(\.\d{0,4})?/'))
                         ->addValidator(new Zend_Validate_StringLength(array('max' => 19)));
        
        $digitsValidator = new Zend_Validate_Digits();
        $digitsValidator->setMessages(array("notDigits" => "Contains non-numeric characters"));
        
        $this->addElement(
            'text',
            'defaultUserStorageSize',
            array(
                'label' => 'Default Personal Storage Size (GB)',
                'twolineLabel' => true,
                'class' => 'text',
                'required' => true,
                'maxlength' => 20,
                'validators' => array(
                    $digitsValidator,
                    array('StringLength', false, array(0, 20))
                )
            )
        );
        
        $this->addElement(
            'select',
            'dateFormat',
            array(
                'label' => 'Date Format',
                'multiOptions' => 
                    Application_Model_SiteConfig::getDateFormats()
            )
        );
        
        $this->addElement(
            'text',
            'methodsPath',
            array(
                'label' => 'Path to methods in SVN',
                'twolineLabel' => true,
                'class' => 'text',
                'required' => true,
                'maxlength' => 256,
                'validators' => array(
                    array('StringLength', false, array(0, 256))
                )
            )
        );
        
        $this->addElement(
            'text',
            'methodsInstancePath',
            array(
                'label' => 'Path to methods on instance',
                'twolineLabel' => true,
                'class' => 'text',
                'required' => true,
                'maxlength' => 256,
                'validators' => array(
                    array('StringLength', false, array(0, 256))
                )
            )
        );
        
        $this->addElement(
            'text',
            'resultsS3Bucket',
            array(
                'label' => 'S3 bucket where store method results',
                'twolineLabel' => true,
                'class' => 'text',
                'required' => true,
                'maxlength' => 256,
                'validators' => array(
                    array('StringLength', false, array(0, 256))
                )
            )
        );        
        
        $this->addElement(
            'text',
            'passwordExpirationPeriod',
            array(
                'label' => 'Password expiration period (in days)',
                'twolineLabel' => true,
                'class' => 'text',
                'required' => true,
                'maxlength' => 20,
                'validators' => array(
                    $digitsValidator
                )
            )
        );
        
        $this->addElement(
            'checkbox',
            'strongPasswordFlag',
            array(
                'label' => 'Strong passwords'
            )
        );
        
        $this->addElement(
            'checkbox',
            'lockInstanceFlag',
            array(
                'label' => 'Lock root access to instance',
                'twolineLabel' => true,
                'value' => 'Y'
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
                    array(
                        'HtmlTag',
                        array(
                            'tag' => 'div',
                            'class' => 'right',
                            'style' => 'margin-right: 10px;width:340px'
                        )
                    )
                )
            )
        );
        

        $this->addDisplayGroup(
                array(
                    'adminEmail',
                    'replyTo',
                    'timezone',
                    'defaultMoneyLimit',
                    'defaultUserStorageSize',
                    'dateFormat',
                    'methodsPath',
                    'methodsInstancePath',
                    'resultsS3Bucket',
                    'passwordExpirationPeriod',
                    'strongPasswordFlag',
                    'lockInstanceFlag',
                    'button_submit'
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
        
        //Add separators
        $this->getElement('dateFormat')->addDecorator(
            array('hrTag' => 'HtmlTag'),
            array('tag' => 'hr', 'placement' => 'append', 'openOnly' => true)
        )->addDecorator(            
            array('divTag' => 'HtmlTag'),
            array('tag' => 'div')
        );
        
        $this->getElement('resultsS3Bucket')->addDecorator(
            array('hrTag' => 'HtmlTag'),
            array('tag' => 'hr', 'placement' => 'append', 'openOnly' => true)
        )->addDecorator(            
            array('divTag' => 'HtmlTag'),
            array('tag' => 'div')
        );

        
    }

    public function getId() {
        return $this->_id;
    }

    public function setId($id) {
        $this->_id = $id;
    }
    
}