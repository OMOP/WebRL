<?php

/* ==============================================================================
  OMOP - Cloud Research Lab

  Observational Medical Outcomes Partnership
  09-Feb-2011

  Form for instance launch defaults page

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

class Application_Form_LaunchDefaults extends Zend_Form {

    protected $_datasets;
    protected $_images;
    protected $_storages;

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
                    'ViewHelper',
                    'Label',
                    'Errors',
                    array('HtmlTag', array('tag' => 'div'))
                )
        );

        $this->setElementFilters(array('HtmlEntities'));
        
        $this->addElement(
            'text',
            'userEbs',
            array(
                'label' => 'Defualt User EBS',
                'class' => 'text',
                'maxlength' => 256,
                'validators' => array(
                    array('StringLength', false, array(0, 256))
                )
            )
        );
        
        $this->addElement(
            'select',
            'dataset',
            array(
                'label' => 'Default Dataset',
                'multiOptions' => $this->getDatasets()
            )
        );

        $this->addElement(
            'select',
            'vocabularyDataset',
            array(
                'label' => 'Vocabulary Dataset',
                'multiOptions' => $this->getDatasets()
            )
        );

        $this->addElement(
            'select',
            'image',
            array(
                'label' => 'Default Image',
                'multiOptions' => $this->getImages()
            )
        );

        
        $this->addElement(
            'select',
            'temporaryStorage',
            array(
                'label' => 'Default Temporary EBS',
                'multiOptions' => $this->getStorages()
            )
        );        

        $this->addDisplayGroup(
                array(
                    'userEbs',
                    'temporaryStorage',
                    'vocabularyDataset',
                    'dataset',
                    'image'
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


    public function getDatasets()
    {
        return $this->_datasets;
    }
    
    public function setDatasets($datasets)
    {
        $this->_datasets = $datasets;
    }

    public function getImages()
    {
        return $this->_images;
    }
    
    public function setImages($images)
    {
        $this->_images = $images;
    }
    
    public function getStorages()
    {
        return $this->_storages;
    }
    
    public function setStorages($storages)
    {
        $this->_storages = $storages;
    }    

    
    
}