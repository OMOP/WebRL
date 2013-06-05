<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2010
 
    Controller for instances page.
 
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

class InstanceController extends Zend_Controller_Action
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
        
        if (Membership::get_app_mode() == ApplicationMode::Admin) {
            $sortColumn = $request->getParam('sort', 5);//Default - column Start Date
            if ($sortColumn == 5) {
                $sortDirection = $request->getParam('dir', 'desc');
            } else {
                $sortDirection = $request->getParam('dir', 'asc');
            }
        } else {
            $sortColumn = $request->getParam('sort', 4);//Default - column Start Date
            if ($sortColumn == 4) {
                $sortDirection = $request->getParam('dir', 'desc');
            } else {
                $sortDirection = $request->getParam('dir', 'asc');
            }
        }

        $mapper = new Application_Model_InstanceMapper();
        $user = Membership::get_current_user();
        $adapter = $mapper->getPaginatorAdapter($sortColumn, $sortDirection, $user);
        
        $pagerConf = (object) '';
        $pagerConf = Zend_Registry::get('pagerConfig');
        
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($page);
        
        if (isset($pagerConf->per_page_instance_list))
            $paginator->setItemCountPerPage($pagerConf->per_page_instance_list);
            
        if (isset($pagerConf->page_range_instance_list))
            $paginator->setPageRange($pagerConf->page_range_instance_list);
        elseif (isset($pagerConf->page_range))
            $paginator->setPageRange($pagerConf->page_range);
        else
            $paginator->setPageRange(5);        

        $this->view->sort_column = $sortColumn;
        $this->view->sort_dir = $sortDirection;
        $this->view->page = $page;
        $this->view->paginator = $paginator;

        $this->view->adminMode = Membership::get_app_mode() == ApplicationMode::Admin;
    }

    /**
     * Terminate instance
     * @todo Refactoring needed as old code is used.
     */
    public function terminateAction() {
//Here old code is used. Need to refactor
        $request = $this->getRequest();
        $page = $request->getParam('page', 1);
        
        if (Membership::get_app_mode() == ApplicationMode::Admin) {
            $sortColumn = $request->getParam('sort', 5);//Default - column Start Date
            if ($sortColumn == 5) {
                $sortDirection = $request->getParam('dir', 'desc');
            } else {
                $sortDirection = $request->getParam('dir', 'asc');
            }
        } else {
            $sortColumn = $request->getParam('sort', 4);//Default - column Start Date
            if ($sortColumn == 4) {
                $sortDirection = $request->getParam('dir', 'desc');
            } else {
                $sortDirection = $request->getParam('dir', 'asc');
            }
        }
        $instance_id = $this->_getParam('id');
        $ir = new Instance();
        $ir->load("instance_id = ?", array($instance_id));
        $iUser = $ir->instance_request->user;
        $cUser = Membership::get_current_user();
        if (
            ($cUser->user_id == $iUser->user_id) ||
            ($cUser->admin_flag == 'Y' && ($cUser->organization_id == 0 || $cUser->organization_id == $iUser->organization_id))
        ) {
            $ir->terminate_date = gmdate('c');
            $ir->status_flag = 'S';
            InstanceManager::terminate_instance($ir->amazon_instance_id);
            $ir->save();

            $method_launch = new MethodLaunch();
            if ($method_launch->load('instance_id = ?', array($instance_id)))
            {
                if ($method_launch->status_flag == 'A')
                {
                    $method_launch->complete_date = gmdate('c');
                    $method_launch->status_flag = 'T';
                    $method_launch->Save();
                }
            }
        }
        $this->_redirector->gotoSimple("list", null, null, array("page"=>$page,"sort"=>$sortColumn,"dir"=>$sortDirection));
        
    }
    
    public function changenameAction() {
        
        $responce = (object) array('update'=>false,'error'=>'');
        try {
            $request = $this->getRequest();
            $instanceId = $request->getParam('id', null);
            if ($instanceId == null)
                throw new Zend_Exception("Instance must be selected!",666);
            $newName = $request->getParam('name', null);
            if ($newName == null)
                throw new Zend_Exception("Instance name is rquired!",666);
            $mapper = new Application_Model_InstanceMapper ();
            $mapper->changeName ($instanceId,$newName);
            $responce->update=true;
        }
        catch (Zend_Exception $exc){
            $responce->update=false;
            if ($exc->getCode() == 666)
                $responce->error = $exc->getMessage();
            else
                $responce->error = 'Failed to add instance!';
        }
        echo json_encode($responce);
        flush();
        exit();
    }
    
}