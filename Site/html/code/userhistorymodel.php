<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Page model for /user_history page.
 
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
require_once("OMOP/WebRL/OrganizationManager.php");

class UserHistoryModel extends PageModel
{
	const VIEW_MODE_TEMPLATE = "/(\d+)_(\d+)(_(\d+)_(\d+)_(asc|desc)_([ANRPS]?))?/";
    function load()
	{
	}
    function get_users()
    {
        $current_user = Membership::get_current_user();
        $manager = new UserManager(DbManager::$db);
        return $manager->get_all_users($this->get_filter_organization_id(), 10, false);
    }
    function get_oganizations()
    {
        $manager = new OrganizationManager(DbManager::$db);
        return $manager->get_organizations(true);
    }
    function get_filter_status()
    {
        if (!isset($this->parameters["status"]))
        {
            return $this->status_flag();
        }
        $status = $this->parameters["status"];
        return $status;
    }
    function pagesize()
    {
        return 20;
    }
    function get_filter_user_id()
    {
        if (!isset($this->parameters["user_id"]))
        {
            return $this->user_id();
        }
        return $this->parameters["user_id"];
    }
    function get_filter_organization_id()
    {
    	$current_user = Membership::get_current_user();
    	if ($current_user->organization_id != 0)
    	{
    		return $current_user->organization_id;
    	}
        if (!isset($this->parameters["organization_id"]))
        {
            return $this->organization_id();
        }
        return $this->parameters["organization_id"];
    }

    function organization_id()
    {
       if ($this->mode() != "view")
            throw new Exception("organization_id available only in the 'view' mode.");
            
        if ($this->action == "view")
        {
        	return 0;
        }
        $organization_id = preg_replace(self::VIEW_MODE_TEMPLATE, '$2', $this->action);
        return intval($organization_id);
    }
    function user_id()
    {
       if ($this->mode() != "view")
            throw new Exception("user_id available only in the 'view' mode.");
        $current_user = Membership::get_current_user();
        if ($this->action == "view")
            return $current_user->user_id;
        $user_id = preg_replace(self::VIEW_MODE_TEMPLATE, '$1', $this->action);
        return intval($user_id);
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
            throw new Exception("user_id available only in the 'view' mode.");
        if ($this->action == "view" || is_numeric($this->action))
            return 1;
        $current_page = preg_replace(self::VIEW_MODE_TEMPLATE, '$4', $this->action);
        $current_page = intval($current_page);
        return $current_page == 0 ? 1 : $current_page;
    }
    function sort_mode()
    {
        if ($this->mode() != "view")
            throw new Exception("user_id available only in the 'view' mode.");
        if ($this->action == "view" || is_numeric($this->action))
            return 4;
        $sort_mode = preg_replace(self::VIEW_MODE_TEMPLATE, '$5', $this->action);
        if ($sort_mode == '')
        	return 4;
        return intval($sort_mode);
    }
    function sort_order()
    {
        if ($this->mode() != "view")
            throw new Exception("user_id available only in the 'view' mode.");

        if ($this->action == "view" || is_numeric($this->action))
            return 'desc';
        $sort_order = preg_replace(self::VIEW_MODE_TEMPLATE, '$6', $this->action);
        if ($sort_order == '')
        	return 'desc';
        return $sort_order;
    }
    function status_flag()
    {
        if ($this->mode() != "view")
            throw new Exception("user_id available only in the 'view' mode.");

        if ($this->action == "view" || is_numeric($this->action))
            return 'R';
        $sort_order = preg_replace(self::VIEW_MODE_TEMPLATE, '$7', $this->action);
        return $sort_order;
    }
}

?>