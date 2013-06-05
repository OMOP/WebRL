<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Page controller for /edit_user page. Handles all user interaction within /edit_user page.
 
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
require_once("OMOP/WebRL/MailManager.php");
require_once("OMOP/WebRL/OrganizationManager.php");
require_once("OMOP/WebRL/PersonalStorageManager.php");
require_once("OMOP/WebRL/Configuration/UserStorageConfiguration.php");

class EditUserController extends PageController
{
	protected function processCore($page, $action, $parameters)
	{
        switch($action)
        {
            case "submit":
                $id = $parameters["internal_id"];
                $command = $parameters["command"];
				if (!is_numeric($id) && !$command)
                {
                    PageRouter::redirect('user_view');
                    exit();
                }
                if ($command == 'save')
                {
                	self::save($page, $action, $parameters);
                }
        		if ($command == 'resize')
                {
                	self::resize($page, $action, $parameters);
                }
                break;

            default:
                if (is_numeric($action))
				{
					$user = new User();
					$user->load("user_id = ?", array($action));

					self::set_previous();
					$this->display($user);
				}
                else if (substr($action, 0, 11) == "reset_pass_")
				{
					$this->reset_password(substr($action, 11));
					PageRouter::redirect('user_view');
                    exit();
				}
                else if (substr($action, 0, 9) == "activate_")
				{
					$this->activate(substr($action, 9), 'Y');
					PageRouter::redirect('user_view');
                    exit();
				}
                else if (substr($action, 0, 11) == "deactivate_")
				{
					$this->activate(substr($action, 11), '');
					PageRouter::redirect('user_view');
                    exit();
				}
                else if (substr($action, 0, 7) == "resize_")
				{
					//$this->resize(substr($action, 7), '');
					PageRouter::redirect('user_view');
                    exit();
				}
                else 
				{
                    PageRouter::redirect('user_view');
                    exit();
                }
            break;
		}
		return true;
	}
	function resize($page, $action, $parameters)
	{
		global $configurationManager; 
    	
        $user_id = $parameters["user_id"];
		$new_usage = $parameters["new_usage"];
		$psm = new PersonalStorageManager($configurationManager);
		$space_usage = $psm->resize_storage($user_id, $new_usage);
		PageRouter::redirect('user_view');		
	}
	function save($page, $action, $parameters)
	{
		global $configurationManager; 
    	
        $current_user = Membership::get_current_user();
		
		$id = $parameters["internal_id"];
        
		$user_id = $parameters["user_id"];
		$first_name = $parameters["first_name"];
		$last_name = $parameters["last_name"];
		$email = $parameters["email"];
		$phone = $parameters["phone"];
		$organization_id = isset($parameters["organization_id"]) ? $parameters["organization_id"] : $current_user->organization_id;
		$title = isset($parameters["title"]) ? $parameters["title"] : '';
		$active_flag = isset($parameters["active_flag"]) ? $parameters["active_flag"] : '';
		$admin_flag = isset($parameters["admin_flag"]) ? $parameters["admin_flag"] : '';
		$svn_access_flag = isset($parameters["svn_access_flag"]) ? $parameters["svn_access_flag"] : '';
		$password_reset = isset($parameters["password_reset"]) ? $parameters["password_reset"] : '';
		
		$user_volume_size = $parameters["user_volume_size"];
		
        $money_limit = $parameters["money_limit"];
		$num_instances = $parameters["num_instances"];
        $password = null;
        $confirmpassword = null;
		$database_types = $parameters["database_type"];
        $software_types = $parameters["software_type"];
        $share_users = isset($parameters['share_users']) ? $parameters["share_users"] : array();
        $certificate = null;

		$omanager = new OrganizationManager(DbManager::$db);
        $organization = $omanager->get($organization_id);

        $has_validation_errors = false;
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

        $user = new User();
        $user->load("user_id = ?", array($id));
        $next_users_count = $omanager->get_active_users($organization_id);

        if ($user->active_flag != 'Y' && $active_flag == 'Y')
        {
			$next_users_count = $next_users_count + 1;                	
        }

        if ($organization != null
        	&& ($active_flag == 'Y')
			&& ($next_users_count > $organization->organization_users_limit)
			)
        {
        	$has_validation_errors = true;
            $errors["organization_id"] = "Organization reach limit of total users.";
        }

        if ($has_validation_errors)
        {
           	self::assign($user, $first_name, $last_name, $email, $phone, $organization_id, $title, $active_flag, $admin_flag, $svn_access_flag, $money_limit, $num_instances);
            $this->display($user, $database_types, $software_types, $share_users);
            $this->view->assign('errors', $errors);
        }
        else
        {
        	$smanager = new SecurityManager();
            if ($password_reset == 'Y')
            {
	        	//$user->set_password($password);
                $password = $smanager->generate_password();
			}

            $manager = new UserManager(DbManager::$db);
            $manager->save_user($id, $user_id, $first_name, $last_name, $email, $phone, $organization_id, $title, 
            	$active_flag == null ? '' : $active_flag, $admin_flag == null ? '' : $admin_flag, 
            	$svn_access_flag, $money_limit, $num_instances, $password, $database_types, $software_types, 
            	$certificate);
			
            if ($password != null && $certificate != null)
				$manager->update_dav_authorization();
            if ($password_reset == 'Y')
            {
            	$mmanager = new MailManager($configurationManager);
                $user->email = $email;
                $mmanager->send_user_information_changed($user, $password);
            }
            if ($user->user_volume_size != $user_volume_size)
            {
                $psm = new PersonalStorageManager($configurationManager);
                $space_usage = $psm->resize_storage($user->login_id, $user_volume_size);
                $user->user_volume_size = $user_volume_size;
                $user->Save();
            }
            $this->back_to_previous();
		}
	}
	function set_previous()
	{
		if (isset($_SESSION['back_url']))
		{
	        $back_url = isset($_SERVER['HTTP_REFERER']) 
	        	? $_SERVER['HTTP_REFERER'] 
	        	: PageRouter::build('user_view');
			$_SESSION['back_url'] = $back_url;
		}
	}
    function back_to_previous()
    {
        $back_url = isset($_SESSION['back_url']) ? $_SESSION['back_url'] : PageRouter::build('user_view');
        unset($_SESSION['back_url']);
        header("Location: ".$back_url);
        exit();
    }
    function reset_password($id)
    {
        global $configurationManager; 
    	
        $smanager = new SecurityManager();
        $password = $smanager->generate_password();

        $user = new User();
		$user->load("user_id = ?", array($id));
        $user->set_password($password);

        $mmanager = new MailManager($configurationManager);
        $mmanager->send_recovery_password($user, $password);
        $this->back_to_previous();
    }
    function activate($id, $flag)
	{
		$user = new User();
		$user->load("user_id = ?", array($id));
		$user->active_flag = $flag;
		$user->save();
	}
	function assign($user, $first_name, $last_name, $email, $phone, $organization_id, $title, $active_flag, $admin_flag, $svn_access_flag, $money_limit, $num_instances)
	{
		$user->first_name = $first_name;
		$user->last_name = $last_name;
		$user->email = $email;
		$user->phone = $phone;
		$user->organization_id = $organization_id;
		$user->title = $title;
		$user->active_flag = $active_flag;
		$user->admin_flag = $admin_flag;
		$user->svn_access_flag = $svn_access_flag;
		$user->money_limit = $money_limit;
		$user->num_instances = $num_instances;
	}
	function display($user, $da_ids = false, $sa_ids = false, $share_users = false)
	{
		global $configurationManager; 
		
		$id = $user->user_id;
        $this->view->assign("user", $user);		
		
		$current_user = Membership::get_current_user();
		
	    $omanager = new OrganizationManager(DbManager::$db);
		$db_types = $omanager->get_dataset_types(0);
		$this->view->assign("db_types", $db_types);
		
		$allowed_db_types = $omanager->get_allowed_dataset_types($current_user->organization_id);
		$this->view->assign("allowed_db_types", $allowed_db_types);

		$soft_types = $omanager->get_software_types(0);
		$this->view->assign("soft_types", $soft_types);
		$allowed_soft_types = $omanager->get_allowed_software_types($current_user->organization_id);
		$this->view->assign("allowed_soft_types", $allowed_soft_types);
				
		$organizations = $omanager->get_organizations();
		$this->view->assign("organizations", $organizations);
		
		$umanager = new UserManager(DbManager::$db);
		$org_users = $umanager->get_all_users($user->organization_id, 1);
		$this->view->assign('org_users', $org_users);
		$sharing_allowed = ($user->organization_id != 0) && ($user->organization->storage_sharing_flag == 'Y');
		$this->view->assign('sharing_allowed', $sharing_allowed);

		$user_storage_access = new UserStorageAccess();
        $users = $user_storage_access->Find("user_id = ?", $user->user_id);
        
        foreach ($users as $u)
        {
            $allowed_users[] = $u->grantee_id;//'Y';
        }
        $this->view->assign('allowed_users', $allowed_users);
				
		if (!$da_ids)
		{
	   		$uda = new UserDatasetAccess();
			$datasetAccessList = $uda->find("user_id=?", array($id));

		    $da_ids = array();
	        foreach($datasetAccessList as $da_entry)
	        {
	            $da_ids[] = $da_entry->dataset_type_id;
	        }
		}
		$this->view->assign("da_ids", $da_ids);

        if (!$sa_ids)
        {
			$usa = new UserSoftwareAccess();
			$softwareAccessList = $usa->find("user_id=?", array($id));
	
	        $sa_ids = array();
	        foreach($softwareAccessList as $sa_entry)
	        {
	            $sa_ids[] = $sa_entry->software_type_id;
	        }
        }
		$this->view->assign("sa_ids", $sa_ids);
		
		$psm = new PersonalStorageManager($configurationManager);
		$space_usage = $psm->get_space_usage($user->login_id);
		$space_usage = ($space_usage + (1024*1024*1024 - 1) ) / (1024*1024*1024);
		$this->view->assign("space_usage", $space_usage);
    }
}


?>
