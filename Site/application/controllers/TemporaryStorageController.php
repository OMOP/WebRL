<?php
/*==============================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    31 Jan 2011
 
    Controller for pages that performs configuration of temporary user storage.
 
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
 
==============================================================================*/
class TemporaryStorageController extends Zend_Controller_Action
{
    
    public function init()
    {
        $this->_redirector = $this->_helper->getHelper('redirector');
    }    
    
    public function indexAction()
    {
        $this->_redirector->gotoSimple('list');
    }    
    
    public function listAction()
    {        
        $request = $this->getRequest();
        $page = $request->getParam('page', 1);

        $mapper = new Application_Model_SnapshotEntryMapper();
        $adapter = $mapper->getPaginatorAdapter(
            Application_Model_SnapshotEntryCategory::TEMPORARY
        );
        
        $pagerConf = (object) '';
        $pagerConf = Zend_Registry::get('pagerConfig');

        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($page);

        if (isset($pagerConf->per_page_temporarystorage_list))
            $paginator->setItemCountPerPage($pagerConf->per_page_temporarystorage_list);
            
        if (isset($pagerConf->page_range_temporarystorage_list))
            $paginator->setPageRange($pagerConf->page_range_temporarystorage_list);
        elseif (isset($pagerConf->page_range))
            $paginator->setPageRange($pagerConf->page_range);
        else
            $paginator->setPageRange(5);
            
        $this->view->paginator = $paginator;
    }
    

    public function addAction()
    {        
        $request = $this->getRequest();
        $form = new Application_Form_AddSnapshotEntry();        
        
        if ($request->isGet()) {
            $this->view->form = $form;            
        }
        
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($form->isValid($data)) {
                $data['category'] = Application_Model_SnapshotEntryCategory::TEMPORARY;
                $snapshot = new Application_Model_SnapshotEntry($data);
                $snapshot->save();
                $this->_redirector->gotoSimple('list');
            } else {
                $this->view->form = $form;
            }            
        }        
    }
    
    public function editAction()
    {
        $id = $this->_getParam('id');
        if ($id && is_numeric($id)) {
            $request = $this->getRequest();
            $form = new Application_Form_EditSnapshotEntry(array('id' => $id));
            $snapshot = new Application_Model_SnapshotEntry(
                Application_Model_SnapshotEntryCategory::TEMPORARY
            );
            if ($request->isGet()) {
                $snapshot->find($id);
                $form->populate($snapshot->toArray());
                $this->view->form = $form;
            }
            
            if ($request->isPost()) {
                $data = $request->getPost();
                if ($form->isValid($data)) {
                    $data['category'] = Application_Model_SnapshotEntryCategory::TEMPORARY;
                    $snapshot = new Application_Model_SnapshotEntry($data);
                    $snapshot->setId($id);
                    $snapshot->save();
                    $this->_redirector->gotoSimple('list');
                } else {
                    $this->view->form = $form;
                }
            }
        }        
    }
    
    public function deleteAction()
    {
        $id = $this->_getParam('id');
        if ($id && is_numeric($id)) {
            $snapshot = new Application_Model_SnapshotEntry();
            $snapshot->find($id);
            $snapshot->setActiveFlag(0);
            $snapshot->save();
        }
        $this->_redirector->gotoSimple('list');
            
    }
}