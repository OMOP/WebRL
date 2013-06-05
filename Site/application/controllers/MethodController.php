<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 Dec 2010

    Controller for Methods page.
    Contains next Action Controllers:
    index: does, nothing, redirects to list action
    list: displays a list of methods
    add: displays a form for adding a new method
    edit: displays a form for editing existing meyhod

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

class MethodController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        $ajaxContext = $this->_helper->AjaxContext();
        $ajaxContext->addActionContext('get', 'json')->initContext('json');
        $ajaxContext->addActionContext('copy', 'json')->initContext('json');
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addActionContext('upload', 'json')
                      ->initContext('json');
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
        //Default is Abbr column
        $sortColumn = $request->getParam('sort', 2);
        $sortDirection = $request->getParam('dir', 'asc');

        $mapper = new Application_Model_MethodMapper();
        $adapter = $mapper->getPaginatorAdapter($sortColumn, $sortDirection);
        
        $pagerConf = (object) '';
        $pagerConf = Zend_Registry::get('pagerConfig');
        
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($page);
        
        if (isset($pagerConf->per_page_method_list))
            $paginator->setItemCountPerPage($pagerConf->per_page_method_list);
            
        if (isset($pagerConf->page_range_method_list))
            $paginator->setPageRange($pagerConf->page_range_method_list);
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
        $method = new Application_Model_Method();
        
        /*
         * TODO: implement Application_Model_Organization and Application_Model_OrganizationMapper
         */
        $organization = new Application_Model_DbTable_Organization();
        $formParameters = array(
            'id' => $id, 
            'organizations' => $organization->getList()
        );
        $form = new Application_Form_EditMethod($formParameters);
        
        if ($id) {
            if ($request->isGet()) {
                $method->findWithOrganizations($id);
                $form->populate($method->toArray());
                $this->view->form = $form;
            }
            if ($request->isPost()) {
                if ($form->isValid($request->getPost())) {
                    $method->setOptions($request->getPost());
                    $method->setOldId($id);
                    $method->save();
                    $this->_redirector->gotoSimple('list');
                } else {
                    $this->view->form = $form;
                }
            }
        }
    }

    public function addAction()
    {
        $organization = new Application_Model_DbTable_Organization();
        $formParameters = array('organizations' => $organization->getList());
        $form = new Application_Form_CreateMethod($formParameters);
        $request = $this->getRequest();
        if ($request->isGet()) {
            $mapper = new Application_Model_MethodMapper();
            $this->view->methods = $mapper->fetchPairs();
            $newId = $mapper->getDbTable()->getNewId();
            $form->populate(array('id' => $newId));
            $this->view->form = $form;
        }

        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
                $method = new Application_Model_Method($request->getPost());
                $method->save();
                $this->_redirector->gotoSimple('list');
            } else {
                $mapper = new Application_Model_MethodMapper();
                $this->view->methods = $mapper->fetchPairs();
                $this->view->form = $form;
            }
        }
    }

    public function getAction()
    {
        $id = $this->_getParam('id');
        if ($id) {
            $request = $this->getRequest();
            $method = new Application_Model_Method();
            $method->findWithOrganizations($id);
            $data = $method->toArray();
            //unset access cause checkbox decorator create two inputs with access name
            //and jQuery.populate doesn't work good
            unset($data['access']);
            unset($data['id']);
            $this->view->copy_from = $data;
        }
    }

    public function analysisAction()
    {
        $id = $this->_getParam('id');
        $sortColumn = $this->_getParam('sort', 1);
        $sortDirection = $this->_getParam('dir', 'asc');
        $methodMapper = new Application_Model_MethodMapper();
        $methods = $methodMapper->fetchPairs();
        if (! $id)
            $id = key($methods);
        $analysisMapper = new Application_Model_AnalysisMapper();
        $analysisData = $analysisMapper->fetchByMethod($id, $sortColumn, $sortDirection);
        $this->view->selectedMethod = $id;
        $this->view->methods = $methods;
        $this->view->data = $analysisData;
        $this->view->sort_column = $sortColumn;
        $this->view->sort_dir = $sortDirection;

    }

    public function uploadAction()
    {
        //Set header, because with other content type, browser (Chrome) add <pre> tag
        try {
            $overwrite = $this->_getParam('overwrite', false);
            $this->getResponse()->setHeader('Content-Type', 'text/html');
            $methodId = $this->_getParam('methodId');
            if (! $methodId) {
                throw new Exception("Error occured, please contact Administrator.");
            }
            $upload = new Zend_File_Transfer_Adapter_Http();
            //todo: add validators
            if (! $upload->isUploaded('analysis')) {
                throw new Exception('File is not uploaded. Make sure that you had selected a file.');
            }
            if (! $upload->receive('analysis')) {
                throw new Exception(Zend_Json::encode($upload->getMessages()));
            }

            $parser = new CsvParser();
            if (! $parser->load($upload->getFileName('analysis'))) {
                throw new Exception("Can't open file");
            }
            $deleteRow = false;
            if (! $parser->isSymmetric()) {
                //handle files with two-line headers
                if ($parser->countHeaders() == 1) {
                    $parser->symmetrize();
                    $deleteRow = true;
                } else {
                    throw new Exception("Number of header columns should be equal to number of data columns");
                }   
            }
            $headerNbr = $parser->countHeaders();
            $columnHeaders = array('id', 'methodAbbrv', 'methodId', 'runId', 
                'configurationId', 'outputFileName', 'runName', 'triageVSFull'
            );
            $params = array();
            //check that param number is less than 25
            if ($headerNbr - count($columnHeaders) > 25) {
                $message = 'Too many method parameters specified in file. Maximum number of parameters is 25';
                throw new Exception($message);   
            }
            for ($i = 1; $i <= $headerNbr - count($columnHeaders); $i++)
                $params[] = 'param'.$i;
            $columnHeaders = array_merge($columnHeaders, $params);
            $parser->setHeaders($columnHeaders);
            $data = $parser->connect();
            //shift headers
            if ($deleteRow)
                array_shift($data);
            array_shift($data);

            $mapper = new Application_Model_AnalysisMapper();
            $methodId = $mapper->saveUploadedFile(
                array(basename($upload->getFileName('analysis')) => $data), 
                $overwrite
            );
            $this->view->newUrl = $this->view->url(
                array('controller' => 'method', 'action' => 'analysis', 'id' => $methodId), 
                'default', 
                true
            );
            unset($this->view->config);

        } catch (Exception $e) {
            if ($messages = Zend_Json::decode($e->getMessage()))
                $this->view->error = $messages;
            else
                $this->view->error = $e->getMessage();
            return;
        }


    }

    public function logAction()
    {
        $request = $this->getRequest();
        $page = $request->getParam('page', 1);
        //Default is Uplaod Date column
        $sortColumn = $request->getParam('sort', 3);
        $sortDirection = $request->getParam('dir', 'desc');

        $mapper = new Application_Model_AnalysisUploadLogMapper();
        $adapter = $mapper->getPaginatorAdapter($sortColumn, $sortDirection);
        
        $pagerConf = (object) '';
        $pagerConf = Zend_Registry::get('pagerConfig');
        
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($page);
        
        if (isset($pagerConf->per_page_method_log))
            $paginator->setItemCountPerPage($pagerConf->per_page_method_log);
            
        if (isset($pagerConf->page_range_method_log))
            $paginator->setPageRange($pagerConf->page_range_method_log);
        elseif (isset($pagerConf->page_range))
            $paginator->setPageRange($pagerConf->page_range);
        else
            $paginator->setPageRange(5);
        
        $this->view->sort_column = $sortColumn;
        $this->view->sort_dir = $sortDirection;
        $this->view->paginator = $paginator;

    }
    
    public function copyAction()
    {
        unset($this->view->config);
        
        $from = $this->_getParam('from');
        $to = $this->_getParam('to');
        $overwrite = $this->_getParam('overwrite', false);
        if ($overwrite == 'false')
            $overwrite = false;
        
        $this->view->ow = print_r($_POST, true);
        if (! $from || ! $to) {
            $this->view->status = 'error';
            $this->view->error = 'Invalid copy parameters. Please contact Administrator.';
            return;
        }
        
        if ($from == $to) {
            $this->view->status = 'success';
            $this->view->message = 'Same methods selected.';
            return;
        }
        
        try {
            $mapper = new Application_Model_AnalysisMapper();
            $mapper->copyAnalysis($from, $to, $overwrite);
            $this->view->status = 'success';
        } catch (Exception $e) {
            $this->view->status = 'error';
            $this->view->error = $e->getMessage();
        }
    }
}







