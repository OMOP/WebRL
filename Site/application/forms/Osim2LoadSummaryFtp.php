<?php
/*=============================================================================
  OMOP - Cloud Research Lab

  Observational Medical Outcomes Partnership
  14 March 2011

  Form for loading OSIM2 sumary sets using FTP upload.

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

final class Application_Form_Osim2LoadSummaryFtp extends Application_Form_Osim2SummaryFormBase
{
    private $_files;

    public function init()
    {
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
                'Description',
                    array('HtmlTag', array('tag' => 'div'))
                )
        );

        $this->setElementFilters(array('HtmlEntities'));

        parent::createId(false, false);
        parent::createName(false);
        parent::createDescription(false);
        $this->addElement(
                'select',
                'definition',
                array(
                    'label' => 'Select File',
                    'required' => true,
                    'description' => 'Summary archive file must be placed in /var/ftp/pub directory in this instance. Archives should be in Zip or Tar Gz formats.',
                    'multiOptions' => $this->getFiles()
                )
            );
        parent::createOverrideExisting(false);
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
        $this->addDisplayGroup(
            array(
                'newId',
                'name',
                'description',
                'definition',
                'overrideExisting',
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
                'value' => 'Submit',
                'class' => 'button_90',
                'decorators' => array(
                    'Submit',
                    array('HtmlTag', array('tag' => 'div', 'class' => 'right')))
                    )
        );
    }
    
    /**
     * Sets new value for Files
     * @param $value New value of Files
     */
    public function setFiles($value)
    {
        $this->_files = $value;
        return $this;
    }
    
    /**
     * Gets current value of Files
     * @return Returns value of Files
     */
    public function getFiles()
    {
        return $this->_files;
    }
}
