<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Page controller for /running_methods page.
 
    �2009 Foundation for the National Institutes of Health (FNIH)
 
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

class RunningMethodsController extends PageController
{
	protected function processCore($page, $action, $parameters)
	{
        $current_user = Membership::get_current_user();
        switch($this->model->mode())
        {
            case "view":
                $sort_mode = $this->model->sort_mode();
                $filter_user_id = $this->model->get_filter_user_id();
                $this->display($filter_user_id, $this->model->current_page(), $this->model->sort_mode(), $this->model->sort_order());
                break;
        }
		return true;
	}
    function terminate($user, $instance_id)
    {
        $ir = new Instance();
        $ir->load("instance_id = ?", array($instance_id));
        $ir->terminate_date = gmdate('c');
        $ir->status_flag = 'S';
        InstanceManager::terminate_instance($ir->amazon_instance_id);
        $ir->save();
        PageRouter::redirect('instances');
    }
    function pause($user, $instance_id)
    {
        $ir = new Instance();
        $ir->load("instance_id = ?", array($instance_id));
        $ir->terminate_date = gmdate('c');
        $ir->status_flag = 'P';
        InstanceManager::pause_instance($ir->amazon_instance_id);
        $ir->save();
        PageRouter::redirect('instances');
    }
    function resume($user, $instance_id)
    {
        // first of all mark old instance as terminated in DB
        $ir = new Instance();
        $ir->load("instance_id = ?", array($instance_id));
        $ir->terminate_date = gmdate('c');
        $ir->status_flag = 'S';
        // create new instance in DB that has the same properties
        $nir = new Instance();
        $nir->instance_request_id = $ir->instance_request_id;
        $nir->assigned_name = $ir->assigned_name;
        $nir->amazon_instance_id = $ir->amazon_instance_id;
        $nir->public_dns = $ir->public_dns;
        $nir->start_date = gmdate('c');
        $nir->terminate_date = null;
        $nir->status_flag = 'A';
        
        InstanceManager::resume_instance($ir->amazon_instance_id);
        $ir->save();
        $nir->save();
        PageRouter::redirect('instances');
    }
	function display($user_id, $currentuserpage, $sort_mode, $sort_order)
	{
        require_once('OMOP/WebRL/MethodManager.php');
    	global $configurationManager;
    	
        $current_user = Membership::get_current_user();
        $organization_id = $current_user->organization_id; 
    	
        $users = $this->model->get_users();
    	
    	$manager = new MethodManager($configurationManager);
        $pagesize = $this->model->pagesize();
        $active_flag = false;
        $all_instances_count = $manager->get_user_method_launches_count($organization_id, $user_id,$active_flag);
        $instances =  $manager->get_user_method_launches($organization_id, $user_id, $active_flag, $currentuserpage, $pagesize, $sort_mode, $sort_order);
        
		$this->view->assign("method_launches", $instances);
        $high_range = $all_instances_count!=0 ? intval(($all_instances_count + $pagesize - 1) / $pagesize) : 1;
        $pagerdata = range(1, $high_range);
        
        $this->view->assign("lab_host", $_SERVER['HTTP_HOST']);
        $this->view->assign("pagerdata", $pagerdata);
        $this->view->assign("filter_user_id", $user_id);
        $this->view->assign("users", $users);
        $this->view->assign("currentuserpage", $currentuserpage);
        $this->view->assign("postfix", '_sort_'.$sort_mode.'_'.$sort_order.'_'.$user_id);
        $this->view->assign("sort_mode", $sort_mode);
        $this->view->assign("sort_order", $sort_order);
	}
    function get_sort_order($sort_order)
    {
        $sort_order_names = array('asc', 'desc');
        return $sort_order_names[$sort_order];
    }
}

?>