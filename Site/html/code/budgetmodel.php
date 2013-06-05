<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    18 August 2010
 
    Page model for /edit_organization page. 
    Manage all user data within /edit_organization page.
 
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

class BudgetModel extends PageModel
{
	var $organizations;
	
	function load()
	{
	}
    
	public function get_budget($date_start, $date_end)
	{
        $manager = new UserManager(DbManager::$db);
        return $manager->get_budget($date_start, $date_end);
	}

    public function get_budget_statistics($type, $id)
	{
        $manager = new UserManager(DbManager::$db);
        return $manager->get_budget_statistics($type, $id);
	}

    public function get_budget_current($type, $id)
	{
        $manager = new UserManager(DbManager::$db);
        return $manager->get_budget_current($type, $id);
	}

    public function get_budget_remaining($type, $id)
	{
        $manager = new UserManager(DbManager::$db);
        return $manager->get_budget_remaining($type, $id);
	}

    public function get_configuration()
    {
        $sc = SiteConfig::get();
        return $sc;
    }
}

?>