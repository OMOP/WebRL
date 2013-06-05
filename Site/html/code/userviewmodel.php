<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Page model for /user_view page. Manage all user data within /user_view page.
 
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

class UserViewModel extends PageModel
{
	const VIEW_MODE_TEMPLATE = "/(\d+)(_sort_(\d+)_(asc|desc))?/";
    var $users;
	function load()
	{
		$user = new User();
		$this->users = $user->find("1 = 1 ORDER BY login_id", array());
	}
    function pagesize()
    {
        return 20;
    }
    function mode()
    {
        if ($this->action == "view")
            return "view";
        if (preg_match(self::VIEW_MODE_TEMPLATE, $this->action))
        {
            return "view";
        }
        throw new Exception("Unsupported mode");
    }
    function current_page()
    {
        if ($this->mode() != "view")
            throw new Exception("current_page available only in the 'view' mode.");
        if ($this->action == "view")
            return 1;
        $user_id = preg_replace(self::VIEW_MODE_TEMPLATE, '$1', $this->action);
        return intval($user_id);
    }
    function sort_mode()
    {
        if ($this->mode() != "view")
            throw new Exception("user_id available only in the 'view' mode.");
        if ($this->action == "view")
            return 1;
        $sort_mode = preg_replace(self::VIEW_MODE_TEMPLATE, '$3', $this->action);
        return intval($sort_mode);
    }
    function sort_order()
    {
        if ($this->mode() != "view")
            throw new Exception("user_id available only in the 'view' mode.");

        if ($this->action == "view")
            return 'asc';
        $sort_order = preg_replace(self::VIEW_MODE_TEMPLATE, '$4', $this->action);
        return $sort_order;
    }
    function all_users_count()
    {
        $current_user = Membership::get_current_user();
    	
        $manager = new UserManager(DbManager::$db);
        return $manager->get_users_count($current_user->organization_id);
    }
    function get_users($page_num, $page_size, $column_name = false, $sort_order = false)
    {
    	$current_user = Membership::get_current_user();
    	
        $manager = new UserManager(DbManager::$db);
        return $manager->get_users($current_user->organization_id, $page_num, $page_size, $column_name, $sort_order);
    }
    function all_users()
    {
    	$current_user = Membership::get_current_user();
    	
        $manager = new UserManager(DbManager::$db);
        return $manager->get_all_users($current_user->organization_id);
    }
}
?>