<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    10 Jan 2011
 
    Controller for pages that performs configuration of system instances.
 
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
/**
 * System instance controller class 
 * 
 * @category Admin_Pages
 * @package  System_Instances
 * @license  http://omop.fnih.org/publiclicense Apache License, Version 2.0
 */
class SystemInstanceController extends Zend_Controller_Action
{
    
    /**
     * Initialisation of action controller
     * 
     * @return null
     */
    public function init()
    {
        $this->_redirector = $this->_helper->getHelper('redirector');
    }
    
    /**
     * Index action for systeminstance controller
     * 
     * @return null
     */
    
    public function indexAction()
    {
        $this->_redirector->gotoSimple('list');
    }
    
    /**
     * List action for systeminstance controller
     * 
     * @return null
     */
    
    public function listAction()
    {
        
        $request = $this->getRequest();
        $page = $request->getParam('page', 1);
        $sortColumn = $request->getParam('sort', 1);
        $sortDirection = $request->getParam('dir', 'asc');

        $mapper = new Application_Model_SystemInstanceMapper();
        $adapter = $mapper->getPaginatorAdapter($sortColumn, $sortDirection);
        
        $pagerConf = (object) '';
        $pagerConf = Zend_Registry::get('pagerConfig');
        
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($page);
        
        if (isset($pagerConf->per_page_systeminstance_list))
            $paginator->setItemCountPerPage($pagerConf->per_page_systeminstance_list);
            
        if (isset($pagerConf->page_range_systeminstance_list))
            $paginator->setPageRange($pagerConf->page_range_systeminstance_list);
        elseif (isset($pagerConf->page_range))
            $paginator->setPageRange($pagerConf->page_range);
        else
            $paginator->setPageRange(5);        

        $this->view->sort_column = $sortColumn;
        $this->view->sort_dir = $sortDirection;
        $this->view->paginator = $paginator;
        
    }
    
     /**
     * Add action for systeminstance controller
     * 
     * @return null
     */

    public function addAction()
    {        
        $request = $this->getRequest();
        $form = new Application_Form_AddSystemInstance();
        
        if ($request->isGet()) {
            $this->view->form = $form;
        }
        
        if ($request->isPost()) {
            $postData = $request->getPost();
            if ($form->isValid($postData)) {
                $instance = new Application_Model_SystemInstance($postData);
                $instance->save();
                $this->_redirector->gotoSimple('list');
            } else {
                $this->view->form = $form;
            }
        }
    }
    
    /**
     * Edit action for systeminstance controller
     * 
     * @return null
     */
    public function editAction()
    {
        $id = $this->_getParam('id');
        if ($id && is_numeric($id)) {
            $request = $this->getRequest();
            $form = new Application_Form_EditSystemInstance(array('id' => $id));
            $instance = new Application_Model_SystemInstance();
            if ($request->isGet()) {
                $instance->find($id);
                $form->populate($instance->toArray());
                $this->view->form = $form;
            }
            
            if ($request->isPost()) {

                if ($form->isValid($request->getPost())) {
                    $instance = new Application_Model_SystemInstance($request->getPost());
                    $instance->setId($id);
                    $instance->save();
                    $this->_redirector->gotoSimple('list');
                } else {
                    $this->view->form = $form;
                }
            }
        }
    }
}