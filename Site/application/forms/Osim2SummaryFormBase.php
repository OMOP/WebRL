<?php
/*=============================================================================
  OMOP - Cloud Research Lab

  Observational Medical Outcomes Partnership
  16 March 2011

  Base class for forms which will be working with Summary sets.

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

abstract class Application_Form_Osim2SummaryFormBase extends Zend_Form
{
    protected function createName($readOnly)
    {
        if ($readOnly) {
            $this->addElement(
                'text',
                'name',
                array(
                    'label' => 'Summary Set Abbr',
                    'class' => 'text_disable',
                    'readonly' => 'readonly',
                    'validators' => array(
                        array('StringLength', false, array(0, 64))
                    )
                )
            );   
        } else {
            $this->addElement(
                'text',
                'name',
                array(
                    'label' => 'Summary Set Abbr',
                    'class' => 'text',
                    'required' => true,
                    'maxlength' => 64,
                    'validators' => array(
                        array('StringLength', false, array(0, 64))
                    )
                )
            );
        }
    }
    protected function createDescription($readOnly)
    {
        if ($readOnly) {
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
        } else {
            $this->addElement(
                'text',
                'description',
                array(
                    'label' => 'Description',
                    'class' => 'text',
                    'required' => true,
                    'maxlength' => 2000,
                    'validators' => array(
                        array('StringLength', false, array(0, 2000))
                    )
                )
            );
        }
    }
    protected function createId($readOnly, $required = false)
    {
        if ($readOnly) {
            $this->addElement(
                'text',
                'newId',
                array(
                    'label' => 'Id',
                    'class' => 'text_disable',
                    'readonly' => 'readonly'
                )
            );
        } else {
            $this->addElement(
                'text',
                'newId',
                array(
                    'label' => 'Id',
                    'class' => 'text',
                    'required' => $required,
                    'maxlength' => 10,
                    'validators' => array(
                        array('StringLength', false, array(0, 10))
                    )
                )
            );  
        }
    }
    protected function createDefinition($required)
    {   
        $definitionElement = new Zend_Form_Element_File('definition');
        $definitionElement->setLabel('Zip file with summaries')
                ->setRequired($required)
                //->setAttrib('twolineLabel', true)
                ->addValidator('Count', false, 1)
                ->addValidator('Extension', false, 'zip')
                ->setDecorators(array(
                    'File', 
                    'Label',
                    'Errors',
                    array('HtmlTag', array('tag' => 'div'))
                ));
        $this->addElement($definitionElement, 'definition');
    }
    protected function createOverrideExisting($readOnly)
    {
        if ($readOnly) {
            $this->addElement(
                'checkbox',
                'overrideExisting',
                array(
                    'label' => 'Override',
                    'class' => 'text_disable',
                    'readonly' => 'readonly',
                )
            );   
        } else {
            $this->addElement(
                'checkbox',
                'overrideExisting',
                array(
                    'label' => 'Override',
                )
            );
        }
    }
}
