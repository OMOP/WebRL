<?php

/*=============================================================================
  OMOP - Cloud Research Lab

  Observational Medical Outcomes Partnership
  24-Feb-2011

  Form for edit user page

  (c)2009-2011 Foundation for the National Institutes of Health (FNIH)

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

============================================================================*/

class Application_Form_EditUser extends Zend_Form 
{
    protected $_id;
    protected $_organizations;
    protected $_datasets;
    protected $_images;
    protected $_allowedDatasets;
    protected $_allowedImages;
    protected $_sharingFlag;
    protected $_usersToShare;
    protected $_inOrg;
    private $_additionalUsersToShareWith;

    const ACTION_EDIT_USER = 'edit';
    const ACTION_EDIT_ACCOUNT = 'edit-account';
    
    public function init() {
        $actionName = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
        
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

        $mapper = new Application_Model_UserMapper();
        $validator = $mapper->getUniqueLoginValidator($this->getId());
        
        $this->addElement(
            'text',
            'loginId',
            array(
                'label' => 'Login ID',
                'class' => 'text',
                'required' => true,
                'maxlength' => 128,
                'validators' => array(
                    array('StringLength', false, array(0, 128)),
                    $validator
                )
            )
        );
        $this->addElement(
            'text',
            'createDate',
            array(
                'label' => 'Create Date',
                'class' => 'text_disable',
                'readonly' => 'readonly'
            )
        );

        $this->addElement(
            'text',
            'firstName',
            array(
                'label' => 'First Name',
                'class' => 'text',
                'required' => true,
                'maxlength' => 50,
                'validators' => array(
                    array('StringLength', false, array(0, 50))
                )
            )
        );

        $this->addElement(
            'text',
            'lastName',
            array(
                'label' => 'Last Name',
                'class' => 'text',
                'required' => true,
                'maxlength' => 50,
                'validators' => array(
                    array('StringLength', false, array(0, 50))
                )
            )
        );
        
        //Get silent hostname validator for email validator
        $dnsValidator = new Zend_Validate_Hostname();
        $dnsValidator->setMessage(null);
        
        $emailValidator = new Zend_Validate_EmailAddress();
        $emailValidator->setHostnameValidator($dnsValidator);

        $uniqueEmail = $mapper->getUniqueEmailValidator($this->getId());
        
        $this->addElement(
            'text',
            'email',
            array(
                'label' => 'Email',
                'class' => 'text',
                'required' => true,
                'maxlength' => 128,
                'validators' => array(
                    array('StringLength', false, array(0, 128)),
                    $emailValidator,
                    $uniqueEmail
                )
            )
        );
        
        $this->addElement(
            'text',
            'phone',
            array(
                'label' => 'Phone',
                'class' => 'text',
                'required' => true,
                'maxlength' => 20,
                'validators' => array(
                    array('StringLength', false, array(0, 20))
                )
            )
        ); 
        
        $orgValid = new Application_Validator_OrganizationUserLimit(
            $this->getId()
        );
        if ($this->getOrganizations()) {
            $this->addElement(
                'select',
                'organizationId',
                array(
                    'label' => 'Organization',
                    'multiOptions' => $this->getOrganizations(),
                    'validators' => array($orgValid)
                )
            );
        }

        $this->addElement(
            'text',
            'title',
            array(
                'label' => 'Title',
                'class' => 'text',
                'maxlength' => 100,
                'validators' => array(
                    array('StringLength', false, array(0, 100))
                )
            )
        );          
        $this->addElement(
            'checkbox',
            'resetPassword',
            array(
                'label' => 'Reset Password',
                'description' => 'Reset password will be emailed to user.'
            )
        );
        
        $this->getElement('resetPassword')->addDecorator(
            array('tip' => 'Description'), 
            array(
                'tag' => 'span',
                'style' => 'color:black; margin-top: 5px',
                'class' => 'error',
                'placement' => 'append',
            )
        );

        $this->addElement(
            'checkbox',
            'active',
            array(
                'label' => 'Active Status',
            )
        );          
        $this->addElement(
            'checkbox',
            'svnAccess',
            array(
                'label' => 'Has SVN Access?',
            )
        );          

        $this->addElement(
            'checkbox',
            'orgAdmin',
            array(
                'label' => 'Is '.($this->getInOrg()?'Org':'Sys').' Admin?',
            )
        );          
        
          
        $this->addElement(
            'checkbox',
            'loadResult',
            array(
                'label' => 'Has Load Result Access?',
                'twolineLabel' => true,
		'class' => 'load_res_acc'
            )
        );
        
        
        
        $decimalValidator = new Zend_Validate_Regex('/^\d{1,14}(\.\d{0,4})?$/');
        $decimalValidator->setMessage('Invalid decimal value');
        
        $budgetValid = new Application_Validator_OrganizationBudgetLimit();
        $this->addElement(
            'text',
            'chargeLimit',
            array(
                'label' => 'Charge Limit ($)',
                'class' => 'text',
                'required' => true,
                'maxlength' => 19,
                'validators' => array(
                    $decimalValidator,
                    $budgetValid
                )
            )
        );          

        $this->addElement(
            'text',
            'chargeRemaining',
            array(
                'label' => 'Remaining ($)',
                'class' => 'text_disable',
                'readonly' => 'readonly'
            )
        );            
/*        $this->addElement(
            'text',
            'storageHost',
            array(
                'label' => 'Storage Host',
                'class' => 'text_disable',
                'readonly' => 'readonly'
            )
        );    
        $this->addElement(
            'text',
            'storageFolder',
            array(
                'label' => 'Mapping folder on Storage Host',
                'class' => 'text_disable',
                'readonly' => 'readonly',
                'twolineLabel' => true
            )
        );    */
        $this->addElement(
            'text',
            'storageUsage',
            array(
                'label' => 'Personal Storage Usage (GB)',
                'class' => 'text_disable',
                'readonly' => 'readonly',
                'twolineLabel' => true
            )
        );    
        $digitsValidator = new Zend_Validate_Digits();
        $digitsValidator->setMessages(array("notDigits" => "Contains non-numeric characters"));
        
        $this->addElement(
            'text',
            'storageSize',
            array(
                'label' => 'Personal Storage Size (GB)',
                'class' => 'text',
                'required' => true,
                'maxlength' => 20,
                'validators' => array(
                    $digitsValidator,
                    array('Between', false, array(0, 1024,
                        'messages' => 'Storage size should be less then 1TB.'))
                )
            )
        );
        
        $maxValid = new Application_Validator_OrganizationInstancesLimit();
        
        $this->addElement(
            'text',
            'maxInstances',
            array(
                'label' => 'Max instances',
                'class' => 'text',
                'required' => true,
                'maxlength' => 20,
                'validators' => array(
                    $digitsValidator,
                    $maxValid
                )
            )
        );
        if ($this->getDatasets()) {
            $elementName = 'datasetAccess';
            $elementOptions = array(
                'label' => 'Dataset Access',
                'required' => true,
                'multiOptions' => $this->getDatasets(),
                'validators' => array(
                    array('NotEmpty', true, array('messages' => 'At least one dataset must be selected'))
                )
            );
            if (self::ACTION_EDIT_ACCOUNT == $actionName) {
                $dAccess = new Zend_Form_Element_MultiCheckbox($elementName, $elementOptions);
                $dAccess->setDecorators(array(
                    'MultiCheckbox',
                    array(array('div_block_left' => 'HtmlTag'),
                          array(
                              'tag' => 'div',
                              'class' => 'block_left'
                          ),
                    ),
                    'Label'
                ));
            } else {
                $dAccess = new Zend_Form_Element_Multiselect($elementName, $elementOptions);
                $dAccess->addDecorators(
                    array(
                        array('HtmlTag', 
                            array(
                                'tag' => 'div'
                            )
                        )
                    )
                );
            }
            
            $dAccess->setRegisterInArrayValidator(false);
            
            $this->addElement($dAccess);
        }
        
        if ($this->getImages()) {
            $elementName = 'imageAccess';
            $elementOptions = array(
                'label' => 'Image Access',
                'required' => true,
                'multiOptions' => $this->getImages(),
                'validators' => array(
                    array('NotEmpty', true, array('messages' => 'At least one software must be selected'))
                )
            );
            if (self::ACTION_EDIT_ACCOUNT == $actionName) {
                $iAccess = new Zend_Form_Element_MultiCheckbox($elementName, $elementOptions);
                $iAccess->setDecorators(array(
                    'MultiCheckbox',
                    array(array('div_block_left' => 'HtmlTag'),
                          array(
                              'tag' => 'div',
                              'class' => 'block_left'
                          ),
                    ),
                    'Label'
                ));
            } else {
                $iAccess = new Zend_Form_Element_Multiselect($elementName, $elementOptions);
                $iAccess->addDecorators(
                    array(
                        array('HtmlTag', array('tag' => 'div'))
                    )
                );
            }

            $iAccess->setAutoInsertNotEmptyValidator(false);
            
            $this->addElement($iAccess);
        }
        
        if ($this->getSharingFlag() || 'edit-account' == $actionName) {
            $sharesWith = new Zend_Form_Element_MultiCheckbox(
                    'sharesStorageTo', 
                    array(
                        'multiOptions' => $this->getUsersToShare(),
                        'label' => ''
                    )
                );
            $sharesWith->size = 10;
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
                            'title' => 'Allow sharing my storage with'
                        )
                    ),
                    array(array('div_left_' => 'HtmlTag'),
                          array(
                            'tag' => 'div',
                            'class' => 'left_'
                        )
                    ),
                ));
            $this->addElement($sharesWith);
            
        	$this->addDisplayGroup(array('sharesStorageTo'), 'sharing');
            
            $this->sharing->setDecorators(
                array(
                    'FormElements',
                )
            );
        }
        
        $this->addDisplayGroup(
                array(
                    'active',
                    'svnAccess',
                    'orgAdmin',
                	'loadResult',
                    'chargeLimit',
                    'chargeRemaining',
//                    'storageHost',
//                    'storageFolder',
                    'storageUsage',
                    'storageSize',
                    'maxInstances',
                    'datasetAccess',
                    'imageAccess'
                ), 'perms'
        );
        
        $this->perms->setDecorators(
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
                    array('HtmlTag', array('tag' => 'div', 'class' => 'left_'))
                )
        );

        
        $this->addDisplayGroup(
                array(
                    'loginId',
                    'createDate',
                    'firstName',
                    'lastName',
                    'email',
                    $this->getOrganizations()?'organizationId': '',
                    'phone',
                    'title',
                    'resetPassword'
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
                    array(array('int_div' => 'HtmlTag'), array('tag' => 'div', 'class' => 'left')),                    
                    array('Callback', array('callback' => array($this, 'render_submit'))),
                    array(array('ext_div' => 'HtmlTag'), array('tag' => 'div', 'class' => 'left no-margin no-border')),
                )
        );
        
        $this->addElement(
            'submit', 'button_submit', array(
            'ignore' => true,
            'value' => 'Submit',
            'class' => 'button_90',
            'order' => '30',
            'decorators' => array(
                'Submit',
                array('HtmlTag', array('tag' => 'div', 'class' => 'right')))
                )
        );
        
    }

    public function render_submit($content, $element, $options) {
        $c = $this->getElement('button_submit')->render();
        $this->getElement('button_submit')->clearDecorators();
        return $c;
        
    }
    
    public function getId() {
        return $this->_id;
    }

    public function setId($id) {
        $this->_id = $id;
    }
    
    public function getOrganizations() {
        return $this->_organizations;
    }

    public function setOrganizations($orgs) {
        $newOrgs = array(0 => '--None--');
        foreach($orgs as $k=>$org)
            $newOrgs[$k] = $org;
        $this->_organizations = $newOrgs;
    }    
    public function getDatasets() {
        return $this->_datasets;
    }

    public function setDatasets($_datasets) {
        $this->_datasets = $_datasets;
    }

    public function getImages() {
        return $this->_images;
    }

    public function setImages($_images) {
        $this->_images = $_images;
    }
    public function getAllowedDatasets() {
        return $this->_allowedDatasets;
    }

    public function setAllowedDatasets($_allowedDatasets) {
        $this->_allowedDatasets = $_allowedDatasets;
    }
    public function getAllowedImages() {
        return $this->_allowedImages;
    }

    public function setAllowedImages($_allowedImages) {
        $this->_allowedImages = $_allowedImages;
    }

    public function getSharingFlag() {
        return $this->_sharingFlag;
    }

    public function setSharingFlag($_sharingFlag) {
        $this->_sharingFlag = $_sharingFlag;
    }

    public function getUsersToShare() {
        return $this->_usersToShare;
    }

    public function setUsersToShare($_usersToShare) {
        $this->_usersToShare = $_usersToShare;
    }

    public function getInOrg() {
        return $this->_inOrg;
    }

    public function setInOrg($_inOrg) {
        $this->_inOrg = $_inOrg;
    }

    public function getAdditionalUsersToShareWith() {
        return $this->_additionalUsersToShareWith;
    }

    public function setAdditionalUsersToShareWith($value) {
        $this->_additionalUsersToShareWith = $value;
    }
    
    public function addElementsToGroup($groupIndex, $elements)
    {
        if (! is_string($groupIndex)) {
            throw new Zend_Form_Exception(__CLASS__ . '::' . __METHOD__ . ' expects a valid DisplayGroup\'s index');
        }
        $group = $this->getDisplayGroup($groupIndex);
        if (! $group) {
            throw new Zend_Form_Exception(' DisplayGroup not found : ' . $groupIndex);
        }
        if (is_string($elements)) {
            $elements = array($elements);
        } elseif (!is_array($elements)) {
            throw new Zend_Form_Exception(__CLASS__ . '::' . __METHOD__ . ' 2nd arg expects string or array');
        }
        foreach ($elements as $element) {
            if (isset($this->_elements[$element])) {
                $add = $this->getElement($element);
                if (null !== $add) {
                    if (array_key_exists($element, $this->_order)) {
                        unset($this->_order[$element]);
                    }
                    $group->addElement($add);
                }
            }
        }
        $this->_orderUpdated = true;
        return $this;
    }
}
