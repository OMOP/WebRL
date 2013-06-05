<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    25 Dec 2010

    Controller for HOI/DOI page.
    Contains next Action Controllers:
    index: does, nothing, redirects to list action
    list: displays a list of HOI/DOI replacement
    add: displays a form for adding a new HOI/DOI replacement
    edit: displays a form for editing existing HOI/DOI replacement

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

class MethodreplacementController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        $this->_helper->AjaxContext()->addActionContext('get', 'json')->initContext('json');
        $this->_redirector = $this->_helper->getHelper('redirector');
        $this->_backUrl = $this->_helper->getHelper('BackUrl');
    }

    public function indexAction()
    {
        $this->_redirector->gotoSimple('list');
    }

    public function listAction()
    {
        $request = $this->getRequest();
        $page = $request->getParam('page', 1);
        $sortColumn = $request->getParam('sort', 1);
        $sortDirection = $request->getParam('dir', 'asc');

        $mapper = new Application_Model_MethodReplacementMapper();
        $adapter = $mapper->getPaginatorAdapter($sortColumn, $sortDirection);
				
        $pagerConf = (object) '';
        $pagerConf = Zend_Registry::get('pagerConfig');				
				
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($page);

        if (isset($pagerConf->per_page_methodreplacement_list))
            $paginator->setItemCountPerPage($pagerConf->per_page_methodreplacement_list);
            
        if (isset($pagerConf->page_range_methodreplacement_list))
            $paginator->setPageRange($pagerConf->page_range_methodreplacement_list);
        elseif (isset($pagerConf->page_range))
            $paginator->setPageRange($pagerConf->page_range);
        else
            $paginator->setPageRange(5);
				
        $this->view->sort_column = $sortColumn;
        $this->view->sort_dir = $sortDirection;
        $this->view->paginator = $paginator;
    }

    public function editAction()
    {
        $id = $this->_getParam('id');
        $request = $this->getRequest();
        $method = new Application_Model_MethodReplacement();

        $form = new Application_Form_EditMethodReplacement(array('id' => $id));

        if ($id) {
            if ($request->isGet()) {
                $this->_backUrl->saveReference();
                $method->find($id);
                $form->populate($method->toArray());
                $this->view->form = $form;
            }
            if ($request->isPost())
                if ($form->isValid($request->getPost())) {
                	$method->find($id);                	
                    $method->setOptions($request->getPost());
                    $method->save();
                    
                    if ($backUrl = $this->_backUrl->getReferer())
                        $this->_redirector->gotoUrl($backUrl);
                    else
                        $this->_redirector->gotoSimple('list');
                }
                else
                    $this->view->form = $form;
        }
    }

    public function addAction()
    {
        $organization = new Application_Model_DbTable_Organization();
        $form = new Application_Form_CreateMethodReplacement();
        $request = $this->getRequest();
        if ($request->isGet()) {
            $mapper = new Application_Model_MethodReplacementMapper();
            $this->view->form = $form;
        }

        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
                $method = new Application_Model_MethodReplacement($request->getPost());
                $method->save();
                $this->_redirector->gotoSimple('list');
            } else {
                $mapper = new Application_Model_MethodReplacementMapper();
                $this->view->form = $form;
            }
        }
    }

    public function getAction()
    {
        $id = $this->_getParam('id');
        if ($id) {
            $request = $this->getRequest();
            $method = new Application_Model_MethodReplacement();
            $method->findWithOrganizations($id);
            $this->view->copy_from = $method->toArray();
        }
    }
}
