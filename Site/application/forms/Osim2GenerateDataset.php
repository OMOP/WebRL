<?php
/*=============================================================================
  OMOP - Cloud Research Lab

  Observational Medical Outcomes Partnership
  14 March 2011

  Form for generating OSIM2 datasets.

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

final class Application_Form_Osim2GenerateDataset extends Zend_Form
{
    private $_sumarySets;

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
        
        $this->addElement(
                'select',
                'summaryId',
                array(
                    'label' => 'Summary Set',
                    'required' => true,
                    'multiOptions' => $this->getSummarySets()
                )
            );
        
        $this->addElement(
                'text',
                'description',
                array(
                    'label' => 'Description',
                    'class' => 'text_disable',
                    'readonly' => 'readonly',
                    'maxlength' => 2000,
                    'validators' => array(
                        array('StringLength', false, array(0, 2000))
                    )
                )
            );
        
        $this->addElement(
                'text',
                'patients',
                array(
                    'label'     => 'Patients Qty (in K)',
                    'class'     => 'text',
                    'required'  => true,
                    'filters'   => array('Digits'),
                    'maxlength' => 64,
                    'validators' => array(
                        array('Digits', array('greaterThan', false, 0))
                    )
                )
            );
            $this->addElement(
                'checkbox',
                'addSignal',
                array(
                    'label' => 'Add Signal',
                )
            );
            $definitionElement = new Zend_Form_Element_File('signal');
            $definitionElement->setLabel('Signal definition')
                ->setRequired(false)
                //->setAttrib('twolineLabel', true)
                ->addValidator('Count', false, 1)
                ->addValidator('Extension', false, 'csv')
                ->setDecorators(array(
                    'File', 
                    'Label',
                    'Errors',
                    array('HtmlTag', array('tag' => 'div'))
                ));
            $this->addElement($definitionElement, 'signal');
            $this->addElement(
                'select',
                'dbEngine',
                array(
                    'label' => 'DB Engine',
                    'required' => true,
                    'multiOptions' => array('oracle' => 'Oracle')
                )
            );
            $this->addElement(
                'checkbox',
                'overrideServer',
                array(
                    'label' => 'Override Server',
                )
            );
        
        	$this->addElement(
                'text',
                'serverHost',
                array(
                    'label'     => 'Server Host or IP',
                    'class'     => 'text',
                    'required'  => false,
                    'maxlength' => 64
                )
            );
        
        	$this->addElement(
                'text',
                'dbName',
                array(
                    'label'     => 'Db Name',
                    'class'     => 'text',
                    'required'  => false,
                    'maxlength' => 64
                )
            );
        
        	$this->addElement(
                'text',
                'dbUsername',
                array(
                    'label'     => 'Db Login',
                    'class'     => 'text',
                    'required'  => false,
                    'maxlength' => 64
                )
            );
        
        	$this->addElement(
                'password',
                'dbPassword',
                array(
                    'label'     => 'Db Password',
                    'class'     => 'text',
                    'required'  => false,
                    'maxlength' => 64
                )
            );
            $this->addElement(
                'text',
                'name',
                array(
                    'label' => 'Dataset Name',
                    'class' => 'text',
                    'required' => true,
                    'maxlength' => 300,
                    'validators' => array(
                        array('StringLength', false, array(0, 300))
                    )
                )
            );
            
            
        $this->addDisplayGroup(
            array(
                'summaryId',
                'description',
                'patients',
                'addSignal',
                'signal',
                'name',
                'dbEngine',
                'overrideServer',
                'serverHost',
                'dbName',
            	'dbUsername',
            	'dbPassword',
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
     * Sets new value for getSummarySet
     * @param $value New value of getSummarySet
     */
    public function setSummarySets($value)
    {
        $this->_sumarySets = $value;
        return $this;
    }
    
    /**
     * Gets current value of getSummarySet
     * @return Returns value of getSummarySet
     */
    public function getSummarySets()
    {
        return $this->_sumarySets;
    }
}
