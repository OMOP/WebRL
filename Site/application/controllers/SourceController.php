<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    27 Jan 2011
 
    Controller for pages that performs configuration of omop_result sources.
 
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

class SourceController extends Zend_Controller_Action
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
        //default is abbr
        $sortColumn = $request->getParam('sort', 2);
        $sortDirection = $request->getParam('dir', 'asc');

        $mapper = new Application_Model_SourceMapper();
        $adapter = $mapper->getPaginatorAdapter($sortColumn, $sortDirection);
        
        $pagerConf = (object) '';
        $pagerConf = Zend_Registry::get('pagerConfig');        
        
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($page);

        if (isset($pagerConf->per_page_source_list))
            $paginator->setItemCountPerPage($pagerConf->per_page_source_list);
            
        if (isset($pagerConf->page_range_source_list))
            $paginator->setPageRange($pagerConf->page_range_source_list);
        elseif (isset($pagerConf->page_range))
            $paginator->setPageRange($pagerConf->page_range);
        else
            $paginator->setPageRange(5);

        $this->view->sort_column = $sortColumn;
        $this->view->sort_dir = $sortDirection;
        $this->view->paginator = $paginator;
        
    }
    
    public function addAction()
    {        
        $request = $this->getRequest();
        $form = new Application_Form_AddSource();
        $mapper = new Application_Model_SourceMapper();
        
        if ($request->isGet()) {
            
            $newId = $mapper->getDbTable()->getNewId();
            $form->populate(array('id' => $newId));
            $this->view->form = $form;            
        }
        
        if ($request->isPost()) {            
            if ($form->isValid($request->getPost())) {
                $source = new Application_Model_Source($request->getPost());
                $source->save();
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
            $form = new Application_Form_EditSource(array('id' => $id));
            $source = new Application_Model_Source();
            if ($request->isGet()) {
                $source->find($id);
                $form->populate($source->toArray());
                $this->view->form = $form;
            }
            
            if ($request->isPost()) {
                $postData = $request->getPost();
                if ($form->isValid($postData)) {
                    $source = new Application_Model_Source($postData);
                    $source->setOldId($id);
                    $source->save();
                    $this->_redirector->gotoSimple('list');
                } else {
                    $this->view->form = $form;
                }
            }
        }        
    }    
}