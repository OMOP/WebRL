<?php

/* ==============================================================================
  OMOP - Cloud Research Lab

  Observational Medical Outcomes Partnership
  14-Mar-2011

  Form for Edit Account page

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

class Application_Form_EditAccount extends Application_Form_EditUser {

    protected $_accessibleStorages;
    
    public function init() {

        parent::init();
        
        $this->removeElement('loginId');
        $this->removeElement('createDate');
        $this->removeElement('resetPassword');
        $this->removeElement('storageUsage');
        $this->removeElement('storageHost');
        $this->removeElement('storageFolder');
        $this->removeElement('storageSize');
        $this->removeElement('active');
        $this->removeElement('orgAdmin');
        $this->removeElement('svnAccess');
        
        $this->getElement('chargeLimit')->setOptions(
            array(
                'required' => false,
                'readonly' => 'readonly',
                'class' => 'text_disable'
            )
        );

        $this->getElement('maxInstances')->setOptions(
            array(
                'required' => false,
                'readonly' => 'readonly',
                'class' => 'text_disable'
            )
        );
        
        $this->main->getElement('email')->setOrder('0');
        $orgElement = $this->createElement(
            'text',
            'organizationName',
            array(
                'label' => 'Organization',
                'class' => 'text_disable',
                'readonly' => 'readonly',
                'order' => 4,
                'decorators' => array(
                    'ViewHelper',
                    'Label',
                    'Errors',
                    array('HtmlTag', array('tag' => 'div'))
                )

            )
        );
        
        $passElement = $this->createElement(
            'password',
            'password',
            array(
                'label' => 'Password',
                'class' => 'text',
                'decorators' => array(
                    'ViewHelper',
                    'Label',
                    'Errors',
                    array('HtmlTag', array('tag' => 'div'))
                )
                
            )
        );
        $passAgainElement = $this->createElement(
            'password',
            'passwordAgain',
            array(
                'label' => 'Password Again',
                'class' => 'text',
                'description' => 'Password will be reset only if it is entered',
                'decorators' => array(
                    'ViewHelper',
                    array('Description', array('tag' => 'span', 'style' => 'color:black', 'class' => 'error')),
                    'Label',
                    'Errors',
                    array('HtmlTag', array('tag' => 'div'))
                )
                
            )
        );
        $this->addElement($orgElement);
        $this->addElement($passElement);
        $this->addElement($passAgainElement);
        
        $dg = $this->getDisplayGroup('main');
        $elements = array_keys($dg->getElements());
        $elements = array_merge($elements, array('organizationName', 'password', 'passwordAgain'));
        $decorators = $dg->getDecorators();
        //Need to recreate display group to add new element to it
        $this->removeDisplayGroup('main');
        $this->addDisplayGroup($elements, 'main');
        $this->getDisplayGroup('main')->setDecorators(
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
                    array(array('int_div' => 'HtmlTag'), array('tag' => 'div', 'class' => 'left')),                    
                    array('Callback', array('callback' => array($this, 'render_submit'))),
                    array(array('ext_div' => 'HtmlTag'), array('tag' => 'div', 'class' => 'left no-margin no-border')),
                )
        )->setOrder(-1);
        

        
        $this->getElement('datasetAccess')->setOptions(
            array(
                'disabled' => 'disabled',
                'onlyChecked' => true,
                'order' => 0,
                'required' => false
            )
        );
        
        $this->getElement('imageAccess')->setOptions(
            array(
                'disabled' => 'disabled',
                'onlyChecked' => true,
                'order' => 1,
                'required' => false
            )
        );      

        $this->getElement('sharesStorageTo')->setOptions(
            array (
                'disabled' => false
            )
        );
        
        if ($this->getAccessibleStorages()) {
            $sharesWith = new Zend_Form_Element_MultiCheckbox(
                'accessibleStorages', 
                array(
                    'multiOptions' => $this->getAccessibleStorages(),
                    'disabled' => true,
                    'checked' => true,
                    'label' => ''
                )
            );

            $sharesWith->setDecorators(array(
                'MultiCheckbox',
                array(array('div_checkbox_classStorageSharing' => 'HtmlTag'), 
                      array(
                        'tag' => 'div',
                        'class' => 'checkbox classStorageSharing'
                      )
                ),
                array(array('fieldset' => 'HtmlTag'),
                      array(
                        'tag' => 'fieldset'
                      )
                ),
                array('ViewScript',
                    array(
                        'viewScript' => '_round_corners.phtml',
                        'placement' => 'prepend',
                        'title' => 'Users sharing their storage with me'
                    )
                ),
                array(array('div_left_' => 'HtmlTag'),
                      array(
                        'tag' => 'div',
                        'class' => 'left_'
                    )
                ),
            ));
        } else {
            $sharesWith = new Zend_Form_Element_Hidden('accessibleStorages',
                array(
                    'description' => "Other users haven't shared their storages yet.",
                    'decorators' => array(
                        array('Description', array('tag' => 'em')),
                        array(array('p' => 'HtmlTag'), array('tag' => 'p')),
                        array('HtmlTag', array(
                              'tag' => 'div',
                              'class' => 'left_'
                            )
                        )
                    )
                )
            );
        }
        $this->addElement($sharesWith);
        $this->addElementsToGroup('sharing', array('accessibleStorages'));
        $this->sharing->addDecorators(array(
            array(array('outer' => 'HtmlTag'), 
                  array('tag' => 'div', 'class' => 'left_ no-margin no-border', 'style' => 'width:370px;'))
        ));
        
        if (count($this->getUsersToShare()) == 0) {
            $this->removeElement("sharesStorageTo");
        }
    }
    
    public function getAccessibleStorages() {
        return $this->_accessibleStorages;
    }

    public function setAccessibleStorages($_accessibleStorages) {
        $this->_accessibleStorages = $_accessibleStorages;
    }
}