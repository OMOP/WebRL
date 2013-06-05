<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Page model for /create_user page. Manage all user data within /create_user page.
 
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

class CreateUserModel extends PageModel
{
	var $sharing_allowed;
	var $siteConfig;
	
	function load()
	{
		$current_user = Membership::get_current_user();
		$this->sharing_allowed = ($current_user->organization_id == 0) || ($current_user->organization->storage_sharing_flag == 'Y');
		$this->siteConfig = SiteConfig::get();
	}
    function get_allowed_dataset_types($organization_id)
    {
        $dbc = new DatasetType();
        $db_types = $dbc->find("active_flag=1 order by sort_order"); // Retreive list of available dataset types.

        if ($organization_id == 0)
        {
        	$datasets = $db_types;
        }
        else
        {
        	$oda = new OrganizationDatasetAccess();
        	$datasetAccessList = $oda->find("organization_id=?", array($organization_id));
        	
        	$datasets = $datasetAccessList;
        }

        $da_ids = array();
        foreach($datasets as $da_entry)
        {
            $da_ids[] = $da_entry->dataset_type_id;
        }
        return $da_ids;
    }
	function get_allowed_software_types($organization_id)
    {
        $softc = new SoftwareType();
        $soft_types = $softc->find("active_flag=1"); // Retreive list of available dataset types.
    	
        if ($organization_id == 0)
        {
        	$tools = $soft_types;
        }
        else
        {
       	 	$osa = new OrganizationSoftwareAccess();
       		$softwareAccessList = $osa->find("organization_id=?", array($organization_id));
        	        	
        	$tools = $softwareAccessList;
        }

        $sa_ids = array();
        foreach($tools as $sa_entry)
        {
            $sa_ids[] = $sa_entry->software_type_id;
        }
        return $sa_ids;
    }
    function default_charge_limit()
    {
        return $this->siteConfig->default_money_limit;
    }
    function default_user_storage_size()
    {
        return $this->siteConfig->default_user_storage_size;
    }
    
}

?>