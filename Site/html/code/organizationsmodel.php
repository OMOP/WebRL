<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    23 August 2010
 
    Page model for /organizaton page. Manage all user data within /organizaton page.
 
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
require_once('OMOP/WebRL/OrganizationManager.php');

class OrganizationsModel extends PageModel
{
	const VIEW_MODE_TEMPLATE = "/^(\d+)(_sort_(\d+)_(asc|desc))?/";
    const DELETE_MODE_TEMPLATE = '/^delete_(\d+)/';
    
    var $organizations;
	function load()
	{
    	$omanager = new OrganizationManager(DbManager::$db);
        $organizations = $omanager->get_organizations(true);
		
        $this->organizations = $organizations;
	}
    function pagesize()
    {
			$pagerConfig = Zend_Registry::get('pagerConfig');
			if (isset($pagerConfig->per_page_organizations_list))
        return $pagerConfig->per_page_organizations_list;
			elseif (isset($pagerConfig->per_page_content))
				return $pagerConfig->per_page_content;
			else
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
        if (preg_match(self::DELETE_MODE_TEMPLATE, $this->action))
        {
            return "delete";
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
    function all_organizations_count()
    {
        $current_user = Membership::get_current_user();
    	
        $manager = new OrganizationManager(DbManager::$db);
        return $manager->get_organizations_count();
    }
    function get_organizations($page_num, $page_size, $column_name = false, $sort_order = false)
    {
        $manager = new OrganizationManager(DbManager::$db);
        return $manager->get_organizations_paged($page_num, $page_size, $column_name, $sort_order);
    }
    function all_organizations()
    {
    	$omanager = new OrganizationManager(DbManager::$db);
        $organizations = $omanager->get_organizations(true);
    	return $organizations;
    }
    
    function org_id() {
        if ($this->mode() != "delete")
            throw new Exception("org_id available only in the 'terminate'.");
         
         $action = substr($this->action, 1 + strpos($this->action, '_'));
         if (!is_numeric($action))
            throw new Exception("org_id should be numeric_value.");
         return intval($action);
        
    }
}
?>