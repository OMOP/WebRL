<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Page model for /running_methods page.
 
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

class RunningMethodsModel extends PageModel
{
    const VIEW_MODE_TEMPLATE = "/(\d+)(_sort_(\d+)_(asc|desc)_(\d+))?/";
    const TERMINATE_MODE_TEMPLATE = '/^terminate_(\d+)/';
    const RESUME_MODE_TEMPLATE = '/^resume_(\d+)/';
    const PAUSE_MODE_TEMPLATE = '/^pause_(\d+)/';

	function load()
	{
	}
    function pagesize()
    {
        return 20;
    }
    function get_filter_user_id()
    {
    	$current_user = Membership::get_current_user();
    	if (Membership::get_app_mode() != ApplicationMode::Admin)
    	{
    		return $current_user->user_id;
    	}
    	if (!isset($this->parameters["user_id"]))
        {
            return $this->user_id();
        }
        return intval($this->parameters["user_id"]);
    }
    function get_users()
    {
        $current_user = Membership::get_current_user();
        $manager = new UserManager(DbManager::$db);
        return $manager->get_all_users($current_user->organization_id, 10, false);
    }
    function mode()
    {
        if ($this->action == "view" || is_numeric($this->action))
            return "view";
        if (preg_match(self::TERMINATE_MODE_TEMPLATE, $this->action))
        {
            return "terminate";
        }
        if (preg_match(self::RESUME_MODE_TEMPLATE, $this->action))
        {
            return "resume";
        }
        if (preg_match(self::PAUSE_MODE_TEMPLATE, $this->action))
        {
            return "pause";
        }
        if (preg_match(self::VIEW_MODE_TEMPLATE, $this->action))
        {
            return "view";
        }
        throw new Exception("Unsupported mode '$action'");
    }
	function current_page()
    {
        if ($this->mode() != "view")
            throw new Exception("current_page available only in the 'view' mode.");
        if ($this->action == "view")
        {
        	return 1;
        }
        $current_page = preg_replace(self::VIEW_MODE_TEMPLATE, '$1', $this->action);
        return intval($current_page);
    }
    function user_id()
    {
        if ($this->mode() != "view")
            throw new Exception("user_id available only in the 'view' mode.");
        if ($this->action == "view")
        {
        	return 0;
        }
        $user_id = preg_replace(self::VIEW_MODE_TEMPLATE, '$5', $this->action);
        return intval($user_id);
    }
    function sort_mode()
    {
        if ($this->mode() != "view")
            throw new Exception("user_id available only in the 'view' mode.");
        if ($this->action == "view")
            return 4;
        $sort_mode = preg_replace(self::VIEW_MODE_TEMPLATE, '$3', $this->action);
        return intval($sort_mode);
    }
    function sort_order()
    {
        if ($this->mode() != "view")
            throw new Exception("user_id available only in the 'view' mode.");
        
        $sort_mode = preg_replace(self::VIEW_MODE_TEMPLATE, '$4', $this->action);
        if ($sort_mode  != 'asc' && $sort_mode != 'desc')
            return 'desc';
        return $sort_mode;
    }
    function method_launch_id()
    {
        if ($this->mode() != "terminate" && $this->mode() != "resume" && $this->mode() != "pause")
            throw new Exception("method_launch_id available only in the 'terminate', 'resume' and 'pause' modes.");
         
         $action = substr($this->action, 1 + strpos($this->action, '_'));
         if (!is_numeric($action))
            throw new Exception("method_launch_id should be numeric_value.");
         return intval($action);
    }
}

?>