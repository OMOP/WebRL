<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    18 August 2010
 
    Page model for /edit_organization page. 
    Manage all user data within /edit_organization page.
 
    ©2009-2010 Foundation for the National Institutes of Health (FNIH)
 
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

class EditOrganizationModel extends PageModel
{
	var $organizations;
	var $soft_types;
	var $db_types;
	var $da_ids;
	var $sa_ids;
	var $users;
	
	function load()
	{
		if (is_numeric($this->action))
		{
			$id = $this->action;
			self::load_organization_data($id);
		}
	}
	function load_organization_data($id)
	{				
		$omanager = new OrganizationManager(DbManager::$db);
		$organizations = $omanager->get_organizations();
		$this->organizations = $organizations;

		$softc = new SoftwareType();
		$this->soft_types = $softc->find("active_flag = 1 order by sort_order");
		
		$dbc = new DatasetType();
		$this->db_types = $dbc->find("active_flag = 1 order by sort_order");
		
		$oda = new OrganizationDatasetAccess();
		$datasetAccessList = $oda->find("organization_id=?", array($id));

        $da_ids = array();
        foreach($datasetAccessList as $da_entry)
        {
            $da_ids[] = $da_entry->dataset_type_id;
        }
		$this->da_ids = $da_ids;

        $osa = new OrganizationSoftwareAccess();
		$softwareAccessList = $osa->find("organization_id=?", array($id));

        $sa_ids = array();
        foreach($softwareAccessList as $sa_entry)
        {
            $sa_ids[] = $sa_entry->software_type_id;
        }
        $this->sa_ids = $sa_ids;
        $this->users = self::all_users();
	}
    function all_users()
    {
        $manager = new UserManager(DbManager::$db);
        return $manager->get_all_users($this->action);
    }
}

?>