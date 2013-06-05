<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Manages access to the pages by users.
 
    ?2009 Foundation for the National Institutes of Health (FNIH)
 
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

class PageAuthorization
{
	static function has_access($page)
	{
		$user = Membership::get_current_user();
		return PageAuthorization::user_has_access($user, $page);
	}
	static function user_has_access($user, $page)
	{
        $public_pages = array('login','send_password', 'loginadmin', 'error_screen', 'reset');
		if (in_array($page, $public_pages))
			return true;
		if ($user == null)
			return false;
		$common_pages = array('instances','launch', 
		    'error_screen',  
			'launch_method', 'running_methods');
		$user_pages = array('edit_account', 'tools_download', 'download', 'notify_download_certificates');
			
		if ($user->admin_flag && Membership::get_app_mode() == ApplicationMode::Admin)
		{
			$admin_pages = array(
                'site_setup',
				'configure',
                'instance_launch_defaults', 
				'system_instances',
				'edit_system_instance',
				'create_system_instance',

                'budget'
				);
			$site_security_pages = array('security_log','event_details','user_connect_log','view_exception','audit_trail', 'amazon_log','web_connect_log', 'error_log',);
			$user_management_pages = array('create_user','edit_user','user_history','user_view');
			$dataset_type_management_pages = array('dataset_type_view','create_dataset_type','edit_dataset_type');
			$temporary_ebs_management_pages = array('temporary_ebs_view','edit_temporary_ebs','register_temporary_ebs');
			$software_type_management_pages = array('create_software_type','edit_software_type', 'software_type_view');
			$organization_management_pages = array('organizations', 'organization_charges', 'create_organization', 'edit_organization');
            if ($user->organization_id == 0) {
				$result_array = array_merge($common_pages, $user_pages, $admin_pages, 
					$user_management_pages, $organization_management_pages,
					$site_security_pages, $dataset_type_management_pages,
					$temporary_ebs_management_pages, $software_type_management_pages
                );
			} else {
				$result_array = array_merge($common_pages, $user_management_pages);
			}
			return in_array($page, $result_array);
		}
		else
		{
			$result_array = array_merge($common_pages, $user_pages);
		}
		return in_array($page, $result_array);
	}
}

?>