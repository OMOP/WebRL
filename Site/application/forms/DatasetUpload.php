<?php

/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    23-11-2011
 
    Form for dataset ulpoading
 
    © 2009=2011 Foundation for the National Institutes of Health (FNIH)
 
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



class Application_Form_DatasetUpload extends Zend_Form {


    public function init() {
        $this->setMethod('post');
        $this->setAttrib('class', 'form2');
        $this->setAttrib('target', 'iframe');
        $this->setAttrib('onsubmit', 'getProgress()');

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


        $this->addElement('file', 'uploadedfile', array(
            'label' => 'Choose file',
            'required' => true
        ));
        
        $this->addElement('checkbox', 'encrypted', array(
            'label' => 'Is encrypted?'
        ));
        
        $this->getElement('uploadedfile')->clearDecorators()->addDecorators(
                array(
                    'Label',
                    'Errors',
                    'File',
                    array('HtmlTag', array('tag' => 'div', 'style' => 'width:400px')
               )
            )
        );
        
        $this->addDisplayGroup(
            array(
               'uploadedfile',
               'encrypted'
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


}