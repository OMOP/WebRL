<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Page controller for /user_history page.
 
    ©2009 Foundation for the National Institutes of Health (FNIH)
 
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

class UserHistoryController extends PageController
{
	protected function processCore($page, $action, $parameters)
	{
		switch($action)
		{
			default:
				$this->display($action);
		}
		return true;
	}
	function display($user_id)
	{  	
		$current_user = Membership::get_current_user();
        $users = $this->model->get_users();
        $organizations = $this->model->get_oganizations();
        $manager = new InstanceManager();

        $filter_status = $this->model->get_filter_status();
        $filter_user_id = $this->model->get_filter_user_id();
        $filter_organization_id = $this->model->get_filter_organization_id();
        $pagesize = $this->model->pagesize();
        $sort_mode =  $this->model->sort_mode();
        $sort_order = $this->model->sort_order();
        $currentuserpage = $this->model->current_page();
        
        $all_instances_count = $manager->get_user_instances_count($current_user, $filter_organization_id, $filter_user_id,$filter_status);

        $instances = $manager->get_user_instances($current_user, $filter_organization_id, $filter_user_id,$filter_status,$currentuserpage,$pagesize, $sort_mode, $sort_order);

        $high_range = $all_instances_count != 0 ? intval(($all_instances_count + $pagesize - 1) / $pagesize) : 1;
        $pagerdata = range(1, $high_range);
        $this->view->assign("pagerdata", $pagerdata);
        $this->view->assign("currentuserpage", $currentuserpage);
        $this->view->assign("users", $users);
        $this->view->assign("organizations", $organizations);        
        $this->view->assign("instances", $instances);
        $this->view->assign("filter_status", $filter_status);
        $this->view->assign("filter_user_id", $filter_user_id);
        $this->view->assign("filter_organization_id", $filter_organization_id);
        $this->view->assign("prefix", $filter_user_id."_".$filter_organization_id."_");
        $this->view->assign("postfix", '_'.$sort_mode.'_'.$sort_order.'_'.$filter_status);
        $this->view->assign("sort_mode", $sort_mode);
        $this->view->assign("sort_order", $sort_order);
	}
}

?>