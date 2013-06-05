<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Page controller for /create_user page. Handles all user interaction within /create_user page.
 
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
require_once("OMOP/WebRL/MailManager.php");
require_once("OMOP/WebRL/OrganizationManager.php");

class CreateUserController extends PageController
{
	protected function processCore($page, $action, $parameters)
	{
		global $configurationManager; 
    	$current_user = Membership::get_current_user();
    	
        switch($action)
		{
			case "view":
				$this->display();
			break;
			case "submit":
				$user_id = $parameters["user_id"];
				$first_name = $parameters["first_name"];
				$last_name = $parameters["last_name"];
				$email = $parameters["email"];
				$phone = $parameters["phone"];
				$organization_id = isset($parameters["organization_id"]) ? intval($parameters["organization_id"]) : 0;
				if ($organization_id == 0 && $current_user->organizationid)
				{				
					$organization_id = $current_user->organizationid;
				}
				$title = $parameters["title"];
				$active_flag = isset($parameters["active_flag"]) ? $parameters["active_flag"] : '';
                $svn_access_flag = isset($parameters["svn_access_flag"]) ? $parameters["svn_access_flag"] : '';
				$admin_flag = isset($parameters["admin_flag"]) ? $parameters["admin_flag"] : '';
				$money_limit = $parameters["money_limit"];
				$user_volume_size = $parameters["user_volume_size"];
				$num_instances = $parameters["num_instances"];
				$database_types = $parameters["database_type"];
                $software_types = $parameters["software_type"];
				$admin_flag = isset($parameters["admin_flag"]) ? $parameters["admin_flag"] : '';
				
                $smanager = new SecurityManager();
                $password = $smanager->generate_password();
                $certificate = null;

                $errors = array();
                $omanager = new OrganizationManager(DbManager::$db);
                $organization = $omanager->get($organization_id);
                
				if ($organization != null && $organization->organization_budget < $money_limit)
                {
                    $has_validation_errors = true;
                    $errors["money_limit"] = "User Charge Limit should not exceed Organization Budget.";
                }
				if ($organization != null && $organization->organization_instances_limit < $num_instances)
                {
                    $has_validation_errors = true;
                    $errors["num_instances"] = "User Max Instances should not exceed Organization Max Instances.";
                }
                $next_users_count = $omanager->get_active_users($organization_id) + 1;
				if (($organization != null) 
					&& ($active_flag == 'Y') 
					&& ($next_users_count > $organization->organization_users_limit))
                {
                    $has_validation_errors = true;
                    $errors["organization_id"] = "Organization reach limit of total users.";
                }
                
				if ($has_validation_errors)
                {
                    $this->display();
                    self::assign_data($user_id, $first_name, $last_name, $email, $phone, $organization_id, $title, $active_flag == null ? '' : $active_flag, $admin_flag == null ? '' : $admin_flag, $svn_access_flag, $money_limit, $num_instances, $password, $database_types, $software_types, $share_users, $user_volume_size);
                    $this->view->assign('errors', $errors);
                }
				else
				{
                    $manager = new UserManager(DbManager::$db);
                    $user = $manager->create_user($user_id, $first_name, $last_name, $email, $phone, $organization_id, $title, $active_flag == null ? '' : $active_flag, $admin_flag == null ? '' : $admin_flag, $svn_access_flag, $money_limit, $num_instances, $password, $database_types, $software_types, $certificate, $user_volume_size);
                    $manager->create_storage_user($user);
                    
                    $manager->update_dav_authorization();

                    $mmanager = new MailManager($configurationManager);
                    $mmanager->send_user_created($user, $password);

					PageRouter::redirect('user_view');
                    exit();
				}
				break;
		}
		return true;
	}
	function assign_data($user_id, $first_name, $last_name, $email, $phone, $organization_id, $title, $active_flag, $admin_flag, $svn_access_flag, $money_limit, $num_instances, $password, $database_types, $software_types, $share_users, $user_volume_size)
	{
		$this->view->assign("user_id", $user_id);
		$this->view->assign("first_name", $first_name);
		$this->view->assign("last_name", $last_name);
		$this->view->assign("email", $email);
		$this->view->assign("phone", $phone);
		$this->view->assign("organization_id", $organization_id);
		$this->view->assign("user_title", $title);
		$this->view->assign("active_flag", $active_flag);
		$this->view->assign("admin_flag", $admin_flag);
		$this->view->assign("svn_access_flag", $svn_access_flag);
		$this->view->assign("money_limit", $money_limit);
		$this->view->assign("num_instances", $num_instances);
		$this->view->assign("password", $password);
		$this->view->assign("database_types", $database_types);
		$this->view->assign("software_types", $software_types);
		$this->view->assign("user_volume_size", $user_volume_size);
	}
	function display()
	{		 
		$current_user = Membership::get_current_user();
		$organization = $current_user->organization;
		
		$omanager = new OrganizationManager(DbManager::$db);
	    
	    $db_types = $omanager->get_dataset_types(0);
		$this->view->assign("db_types", $db_types);
		$allowed_db_types = $omanager->get_allowed_dataset_types($current_user->organization_id);
		$this->view->assign("allowed_db_types", $allowed_db_types);

		$soft_types = $omanager->get_software_types(0);
		$this->view->assign("soft_types", $soft_types);
		$allowed_soft_types = $omanager->get_allowed_software_types($current_user->organization_id);
		$this->view->assign("allowed_soft_types", $allowed_soft_types);
		
		$omanager = new OrganizationManager(DbManager::$db);
		$organizations = $omanager->get_organizations();
		$this->view->assign("organizations", $organizations);
		
		$this->view->assign("num_instances", 1);
		$this->view->assign("active_flag", 'Y');
		$this->view->assign('org_users', $omanager->get_active_users($current_user->organization_id));
		$this->view->assign("svn_access_flag", 'Y');
		$this->view->assign('sharing_allowed', $this->model->sharing_allowed);
		
		$money_limit = SiteConfig::get()->default_money_limit;
		if ($organization != null)
		{
			$money_limit = $organization->organization_budget;
		}
		$this->view->assign("money_limit", $money_limit);
		
		$default_charge_limit = $this->model->default_charge_limit();
        $this->view->assign("default_charge_limit", $default_charge_limit);
		$default_user_storage_size = $this->model->default_user_storage_size();
        $this->view->assign("user_volume_size", $default_user_storage_size);
        
	}
}

?>