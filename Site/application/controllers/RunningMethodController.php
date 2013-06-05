<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2010
 
    Controller for running methods page.
 
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
 
================================================================================*/

class RunningMethodController extends Zend_Controller_Action
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
        $isAdminMode = Membership::get_app_mode() == ApplicationMode::Admin;
        
        $cUser = Membership::get_current_user();

        $request = $this->getRequest();
        $page = $request->getParam('page', 1);
        //Default column is Start Date
        $sortColumn = $request->getParam('sort', 4);
        $sortDirection = $request->getParam('dir', 'desc');
        $this->_applyFilter($cUser->user_id);
        
        $mapper = new Application_Model_RunningMethodMapper();
        
        $uMapper = new Application_Model_UserMapper();
        $users = $uMapper->getOrgUsersList($cUser->organization_id);
        
        $adapter = $mapper->getPaginatorAdapter($sortColumn, 
                                                $sortDirection, 
                                                $cUser->organization_id, 
                                                $this->_getParam('filter', $cUser->user_id));
        
        $pagerConf = (object) '';
        $pagerConf = Zend_Registry::get('pagerConfig');
        
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($page);

        if (isset($pagerConf->per_page_runningmethod_list))
            $paginator->setItemCountPerPage($pagerConf->per_page_runningmethod_list);
            
        if (isset($pagerConf->page_range_runningmethod_list))
            $paginator->setPageRange($pagerConf->page_range_runningmethod_list);
        elseif (isset($pagerConf->page_range))
            $paginator->setPageRange($pagerConf->page_range);
        else
            $paginator->setPageRange(5);

        $this->view->sort_column = $sortColumn;
        $this->view->sort_dir = $sortDirection;
        $this->view->paginator = $paginator;
        $this->view->user = $this->_getParam('filter', $cUser->user_id);
        $this->view->users = $users;

        $this->view->adminMode = $isAdminMode;
    }
    
    private function _applyFilter($defaultFilter = null) {
        $filter = $this->_getParam('filter', $defaultFilter);
        if (is_numeric($filter)) {
            $this->_setParam('user', $filter);
        }
    }
}