<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15-Feb-2011
 
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
class ConnectLogController extends Zend_Controller_Action
{
    
    public function init()
    {
        $this->_redirector = $this->_helper->getHelper('redirector');
    }
    
    public function instanceAction()
    {
        $this->commonHandler();
    }

    public function webAction()
    {
        $this->commonHandler();
    }
    
    private function commonHandler()
    {

        $request = $this->getRequest();
        $page = $request->getParam('page', 1);
        $sortColumn = $request->getParam('sort', 1);
        $sortDirection = $request->getParam('dir', 'desc');

        $mapper = new Application_Model_UserConnectLogMapper($this->getRequest()->getActionName());
        
        $adapter = $mapper->getPaginatorAdapter($sortColumn, $sortDirection);
        
        $pagerConf = (object) '';
        $pagerConf = Zend_Registry::get('pagerConfig');
        
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($page);

        if (isset($pagerConf->per_page_connectlog))
            $paginator->setItemCountPerPage($pagerConf->per_page_connectlog);
            
        if (isset($pagerConf->page_range))
            $paginator->setPageRange($pagerConf->page_range);
        else
            $paginator->setPageRange(5);

        $this->view->sort_column = $sortColumn;
        $this->view->sort_dir = $sortDirection;
        $this->view->paginator = $paginator;
        
        
    }
    
    
    
}