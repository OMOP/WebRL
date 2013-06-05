<?php
/*=============================================================================
  OMOP - Cloud Research Lab

  Observational Medical Outcomes Partnership
  14 March 2011

  Form for generating OSIM2 summary sets.

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

=============================================================================*/

final class Application_Form_Osim2GenerateSummary extends Application_Form_Osim2SummaryFormBase
{
    private $_datasets;

    public function init()
    {
        $this->setMethod('post');
        //$this->setAttrib('enctype', 'multipart/form-data');
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
                    'Description',
                    array('HtmlTag', array('tag' => 'div'))
                )
        );

        $this->setElementFilters(array('HtmlEntities'));
        
        parent::createId(true);
        parent::createName(true);
        parent::createDescription(true);
        
        $this->addElement(
                'select',
                'sourceId',
                array(
                    'label' => 'Origin Dataset',
                    'required' => true,
                    'multiOptions' => $this->getDatasets()
                )
            );
        parent::createOverrideExisting(false);
        
        $this->overrideExisting
            ->setDescription('Once completed, notification will be emailed to you.');
        
        $this->addDisplayGroup(
            array(
                'newId',
                'name',
                'description',
                'sourceId',
                'overrideExisting',
                'information',
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
                'value' => 'Generate',
                'class' => 'button_90',
                'decorators' => array(
                    'Submit',
                    array('HtmlTag', array('tag' => 'div', 'class' => 'right')))
                    )
        );
    }
    
    /**
     * Sets new value for Datasets
     * @param $value New value of Datasets
     */
    public function setDatasets($value)
    {
        $this->_datasets = $value;
        return $this;
    }
    
    /**
     * Gets current value of Datasets
     * @return Returns value of Datasets
     */
    public function getDatasets()
    {
        return $this->_datasets;
    }
}
