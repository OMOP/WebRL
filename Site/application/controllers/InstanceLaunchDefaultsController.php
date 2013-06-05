<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    09-Feb-2011
 
    Controller for pages that performs configuration of instance launch defaults.
 
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
 
================================================================================*/
class InstanceLaunchDefaultsController extends Zend_Controller_Action
{
    
    public function init()
    {
        $this->_redirector = $this->_helper->getHelper('redirector');
    }
    
    public function indexAction()
    {
        $request = $this->getRequest();
        $datasetTable = new Application_Model_DbTable_DatasetType();
        $datasets = $datasetTable->getList(true);
        $imageMapper = new Application_Model_SoftwareTypeMapper();
        $images = $imageMapper->getList();
        $storageMapper = new Application_Model_SnapshotEntryMapper();
        $storages = $storageMapper->getTemporaryStorageList();
        $form = new Application_Form_LaunchDefaults(
            array(
                'datasets' => $datasets,
                'images' => $images,
                'storages' => $storages
            )
        );
        if ($request->isGet()) {
            $form->populate($this->_getDefaults());
            
        }
        
        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
                $this->_saveDefaults($request->getPost());
                $this->_redirector->gotoSimple('index', 'configuration');
            }
        }
        
        $this->view->form = $form;
    }
    
    private function _getDefaults()
    {
        
        $defaults = array();
        
        $mapper = new Application_Model_SiteConfigMapper();
        $config = $mapper->getConfig();
        $defaults['userEbs'] = $config->getDefaultUserEbs();
        
        $defaults['vocabularyDataset'] = $config->getVocabularyDataset();
        
        $datasetMapper = new Application_Model_DatasetMapper();
        $defaults['dataset'] = $datasetMapper->getDefaultDatasetId();

        $imageMapper = new Application_Model_SoftwareTypeMapper();
        $defaults['image'] = $imageMapper->getDefaultTypeId();
        
        $storageMapper = new Application_Model_SnapshotEntryMapper();
        $defaults['temporaryStorage'] = $storageMapper->getDefaultTemporaryStorageId();
        
        return $defaults;
        
    }
    
    private function _saveDefaults($defaults)
    {
        $mapper = new Application_Model_SiteConfigMapper();
        $config = $mapper->getConfig();
        
        $config->setDefaultUserEbs($defaults['userEbs']);
        $config->setVocabularyDataset($defaults['vocabularyDataset']);
        
        $config->save();
        
        $datasetMapper = new Application_Model_DatasetMapper();
        $datasetMapper->setDefaultDataset($defaults['dataset']);
        
        $imageMapper = new Application_Model_SoftwareTypeMapper();
        $imageMapper->setDefaultType($defaults['image']);
        
        $storageMapper = new Application_Model_SnapshotEntryMapper();
        $storageMapper->setDefaultTemporaryStorage($defaults['temporaryStorage']);

        
    }
    
}