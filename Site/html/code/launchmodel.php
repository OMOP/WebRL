<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Page model for /launch page. Manage all user data within /launch page.
 
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

class LaunchModel extends PageModel
{
    var $instance_types;
    var $dataset_types = array();
    var $software_types;
    var $temporary_ebs_entries;
    
    function load()
    {
        $user = Membership::get_current_user();
        
        $instance_type = new InstanceSize();     //instance types, default is first
        $this->instance_types = $instance_type->Find('1 ORDER BY default_flag DESC');
        
        $dataset_type = new DatasetType();      //dataset types, need cheking in user_dataset_access
        $user_access = new UserDatasetAccess();
        $user_types = $user_access->Find("user_id = ? ", $user->user_id);
        foreach ($user_types as $user_type) {
            $user_type_ids[] = $user_type->dataset_type_id;
        }
		
        $omanager = new OrganizationManager(DbManager::$db);
		$org_dataset_types = $omanager->get_dataset_types($user->organization_id);
		$org_software_types = $omanager->get_software_types($user->organization_id);
        
		$dataset_types = array();
		foreach($org_dataset_types as $dataset_type)
		{
			if (in_array($dataset_type->dataset_type_id, $user_type_ids))
				$dataset_types[] = $dataset_type;
		}
		$this->dataset_types = $dataset_types;
        
        $user_software_access = new UserSoftwareAccess();
        $user_software_types = $user_software_access->Find("user_id = ? ", $user->user_id);
        foreach ($user_software_types as $user_software_type) {
            $user_software_type_ids[] = $user_software_type->software_type_id;
        }
        
		$software_types = array();
		foreach($org_software_types as $software_type)
		{
			if (in_array($software_type->software_type_id, $user_software_type_ids))
				$software_types[] = $software_type;
		}
        $this->software_types = $software_types;
        
        $snapshot_entry = new SnapshotEntry();
        $this->temporary_ebs_entries = $snapshot_entry->Find("snapshot_entry_category_id = ? and active_flag = 1", SnapshotEntryCategory::TemporaryEBS);
   }
}

?>