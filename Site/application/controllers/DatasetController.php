<?php
/*==============================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    04-Feb-2011
 
    Controller for pages that performs configuration of datasets.
 
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
class DatasetController extends Zend_Controller_Action
{
    
    public function init()
    {
        $this->_redirector = $this->_helper->getHelper('redirector');
        $this->_backUrl = $this->_helper->getHelper('BackUrl');
    }
    
    public function indexAction()
    {
        $this->_redirector->gotoSimple('list');
    }
    
    public function listAction()
    {
        $pagerConf = (object) '';
        $pagerConf = Zend_Registry::get('pagerConfig');
        $request = $this->getRequest();
        $page = $request->getParam('page', 1);

        $mapper = new Application_Model_DatasetMapper();
        $paginator = new Zend_Paginator($mapper);
        $paginator->setCurrentPageNumber($page);
        if (isset($pagerConf->per_page_dataset_list))
            $paginator->setItemCountPerPage($pagerConf->per_page_dataset_list);
        if (isset($pagerConf->page_range_dataset_list))
            $paginator->setPageRange($pagerConf->page_range_dataset_list);
        elseif (isset($pagerConf->page_range))
            $paginator->setPageRange($pagerConf->page_range);
        else
            $paginator->setPageRange(5);
        $this->view->paginator = $paginator;        
    }
    
    public function addAction()
    {
        $request = $this->getRequest();
        $form = new Application_Form_AddDataset();        
        
        if ($request->isGet()) {
            $this->view->form = $form;            
        }
        
        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
                $dataset = new Application_Model_Dataset($request->getPost());
                $dataset->save();
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
            $dataset = new Application_Model_Dataset();
            $dataset->find($id);
            $form = new Application_Form_EditDataset(
                array('encryptedFlag' => $dataset->getEncryptedFlag(),
                      'id' => $id)
            );
            if ($request->isGet()) {
                $this->_backUrl->saveReference();
                $form->populate($dataset->toArray());
                $this->view->form = $form;
            }
            
            if ($request->isPost()) {
                if ($form->isValid($request->getPost())) {
                    $dataset = new Application_Model_Dataset(
                        $request->getPost()
                    );
                    $dataset->setId($id);
                    $dataset->save();
                    
                    if ($backUrl = $this->_backUrl->getReferer())
                        $this->_redirector->gotoUrl($backUrl);
                    else
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
            $dataset = new Application_Model_Dataset();
            $dataset->find($id);
            $dataset->setActiveFlag(0);
            $dataset->save();
        }
        $this->_redirector->gotoSimple('list');
    }
    
    public function moveDownAction()
    {
        $id = $this->_getParam('id');
        if ($id && is_numeric($id)) {
            $mapper = new Application_Model_DatasetMapper();
            $mapper->updateOrder($id, -1);
        }
        
        $this->_backUrl->saveReference();
        if ($backUrl = $this->_backUrl->getReferer())
            $this->_redirector->gotoUrl($backUrl);
        else
            $this->_redirector->gotoSimple('list');
    }
    
    public function moveUpAction()
    {
        $id = $this->_getParam('id');
        if ($id && is_numeric($id)) {
            $mapper = new Application_Model_DatasetMapper();
            $mapper->updateOrder($id, 1);
        }
        
        $this->_backUrl->saveReference();
        if ($backUrl = $this->_backUrl->getReferer())
            $this->_redirector->gotoUrl($backUrl);
        else
            $this->_redirector->gotoSimple('list');
    }
    
    
}