<?php
/*=============================================================================
  OMOP - Cloud Research Lab

  Observational Medical Outcomes Partnership
  16 March 2011

  Form for loading OSIM2 sumary sets.

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

final class Application_Form_Osim2UpdateSummary extends Application_Form_Osim2SummaryFormBase
{
    public function init()
    {
        $this->setMethod('post');
        $this->setAttrib('enctype', 'multipart/form-data');
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

        parent::createId(false);
        parent::createName(false);
        parent::createDescription(false);
               
        $this->addDisplayGroup(
            array(
                'newId',
                'name',
                'description',
                'definition',
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
