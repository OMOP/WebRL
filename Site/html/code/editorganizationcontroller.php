<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    18 August 2010
 
    Page controller for /edit_organization page. 
    Handles all user interaction within /edit_organization page.
 
    �2009-2010 Foundation for the National Institutes of Health (FNIH)
 
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

class EditOrganizationController extends PageController
{
	protected function processCore($page, $action, $parameters)
	{
		global $configurationManager; 
    	
        switch($action)
		{
			
			case "submit":
				$organization_id = $parameters["organization_id"];
				$organization_name = $parameters["organization_name"];
				$organization_city = $parameters["organization_city"];
				$organization_state = $parameters["organization_state"];
				$organization_zip = $parameters["organization_zip"];
				$organization_address_line1 = $parameters["organization_address_line1"];
				$organization_address_line2 = $parameters["organization_address_line2"];
				$active_flag = isset($parameters["active_flag"]) ? $parameters["active_flag"] : '';
				$organization_budget = $parameters["organization_budget"];
				$organization_instances_limit = $parameters["organization_instances_limit"];
				$organization_users_limit = $parameters["organization_users_limit"];
				$organization_admin_factor = $parameters["organization_admin_factor"];
                $organization_svn_folder = $parameters["organization_svn_folder"];
				$storage_sharing_flag = isset($parameters["storage_sharing_flag"]) ? $parameters["storage_sharing_flag"] : '';
				$database_types = $parameters["database_type"];
                $software_types = $parameters["software_type"];
								
				if (false) /* Right now validation performed by client. */
				{
					$has_validation_errors = true;
					$validation_message = "Please provide additional information";
				}
				else
				{
					$has_validation_errors = false;
					$validation_message = "";
				}

				$_SESSION["validation_message"] = $validation_message;
				if ($has_validation_errors)
                {
                    PageRouter::redirect('edit_organization');
                    exit();
                }
				else
				{
                    $current_user = Membership::get_current_user();
					$manager = new OrganizationManager(DbManager::$db);
                    $organization = $manager->save_organization($organization_id, $organization_name,  
                    	$organization_city, $organization_state, $organization_zip, 
                    	$organization_address_line1, $organization_address_line2, $active_flag, 
                    	$organization_budget, $organization_instances_limit, $organization_users_limit, 
                    	$organization_admin_factor, $storage_sharing_flag,
                    	$database_types, $software_types, $current_user, $organization_svn_folder);

					PageRouter::redirect('organizations');
                    exit();
				}
				break;
			default:
				if (is_numeric($action))
				{
					$this->display($action);
				}
			break;
		}
		return true;
	}
	function display($organization_id)
	{
		$back_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : PageRouter::build('user_view');
		$_SESSION['back_url'] = $back_url;
		 
		$manager = new OrganizationManager(DbManager::$db);
        $organization = $manager->get($organization_id);
		$this->view->assign("org", $organization);		
		
		// Do nothing, since we don't need perform binding of data when we first time see the page.
		$this->view->assign("db_types", $this->model->db_types);
		$this->view->assign("soft_types", $this->model->soft_types);
		$this->view->assign("organizations", $this->model->organizations);
		$this->view->assign("da_ids", $this->model->da_ids);
		$this->view->assign("sa_ids", $this->model->sa_ids);
		$this->view->assign("users", $this->model->users);
	}
}

?>