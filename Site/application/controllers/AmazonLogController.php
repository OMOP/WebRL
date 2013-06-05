<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    17-Feb-2011
 
    Controller for amazon log pages
 
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
class AmazonLogController extends Zend_Controller_Action
{
    
    public function init()
    {
        $this->_redirector = $this->_helper->getHelper('redirector');
    }
    
    public function indexAction()
    {
        
        $request = $this->getRequest();
        $page = $request->getParam('page', 1);
        //Default is Date column
        $sortColumn = $request->getParam('sort', 5);
        $sortDirection = $request->getParam('dir', 'desc');

        
        $currentUser = Membership::get_current_user();
        $appMode = Membership::get_app_mode();
        if ($appMode == ApplicationMode::Admin) {
            $userId = 0;
        } else {
            $userId = $currentUser->user_id;
        }
        
        $manager = new InstanceManager();
        $pageSize = Zend_Paginator::getDefaultItemCountPerPage();
        $instancesCount = $manager->get_user_instances_count($currentUser, 0, $userId);

        $instances =  $manager->get_user_instances(
            $currentUser, 
            0, 
            $userId, 
            false, 
            $page,
            $pageSize,
            $sortColumn - 1,
            $sortDirection
        );

        $pagerConf = (object) '';
        $pagerConf = Zend_Registry::get('pagerConfig');
        
        $paginator = Zend_Paginator::factory(intval($instancesCount));
        $paginator->setCurrentPageNumber($page);

        if (isset($pagerConf->per_page_amazonlog))
            $paginator->setItemCountPerPage($pagerConf->per_page_amazonlog);
            
        if (isset($pagerConf->page_range))
            $paginator->setPageRange($pagerConf->page_range);
        else
            $paginator->setPageRange(5);

        $this->view->sort_column = $sortColumn;
        $this->view->sort_dir = $sortDirection;
        $this->view->paginator = $paginator;
        $this->view->instances = $instances;
        
    }
    
    public function detailsAction()
    {
        $id = $this->_getParam('id');
        if ($id)
        {
            $log = new Application_Model_AmazonLog();
            $log->findByInstanceId($id);
            if (! $log->getId()) {
                $manager = new InstanceManager();
                $instance = new Instance();
                $instance->load('amazon_instance_id = ?', array($id));
                if ($instance->instance_id) {
                    $output = $manager->get_console_output($id);
                    $log->setInstanceId($instance->instance_id);
                    $log->setStatus($instance->status_flag);
                    
                    if ($output instanceof Exception) {
                        $output = $output->getMessage();
                        $log->setConsoleOutput($output);
                    } else {
                        $log->setConsoleOutput($output);
                        $log->save();
                    }
                    
                }
            }
            $this->view->log = $log;
            
        }
    }
    
    
}