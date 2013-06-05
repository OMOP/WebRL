<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    edit_account page model
 
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

class EditAccountModel extends PageModel
{
    var $dataset_types;
    var $software_types;
    var $allowed_dataset_types;
    var $allowed_software_types;
    var $user;
    var $da_ids;
    var $sa_ids;
    var $org_users;
    var $allowed_users;
    var $sharing_allowed;
    var $sharing_their_storage_users;
    
    function load()
    {
        $this->user = Membership::get_current_user();
        $id = $this->user->user_id;
	    $current_user = Membership::get_current_user();
		
	    $omanager = new OrganizationManager(DbManager::$db);
		$this->dataset_types = $omanager->get_dataset_types($this->user->organization_id);
		$this->software_types = $omanager->get_software_types($this->user->organization_id);
		
		$this->sharing_allowed = ($this->user->organization_id != 0) && ($this->user->organization->storage_sharing_flag == 'Y'); 
		
		$umanager = new UserManager(DbManager::$db);
		$users = $umanager->get_all_active_users($this->user->organization_id, 1);
        $usersToShare = $umanager->get_users_allowed_sharing($this->user->organization_id, $id, 1);
		$this->usersToShare = $usersToShare;

        $this->sharing_their_storage_users = $umanager->get_accessible_storage($this->user->user_id);
		
		$user_storage_access = new UserStorageAccess();
        $users = $user_storage_access->Find("user_id = ? ", $this->user->user_id);
        
        $this->allowed_users = array();
        foreach ($users as $u)
        {
            $this->allowed_users[] = $u->grantee_id;//'Y';
        }
        foreach ($this->allowed_users as $allowed_user) {
            $flag = false;
            foreach ($this->usersToShare as $user_to_share) {
                if ($user_to_share->user_id == $allowed_user) {
                    $flag = true;
                    break;
                }
            }
            if (! $flag) {
                $new_user = new User();
                $new_user->load("user_id = ?", array($allowed_user));
                $this->usersToShare[] = $new_user;
            }
        }
        
        if ($this->user->organization_id == 0) {
            foreach ($this->usersToShare as $key => $userToShare) {
                if (! in_array($userToShare->user_id, $this->allowed_users))
                    unset($this->usersToShare[$key]);
            }
        }
        
        
        $this->sharing_allowed = $this->sharing_allowed || !empty($this->usersToShare);
        
        $user_access = new UserDatasetAccess();
        $user_types = $user_access->Find("user_id = ? ", $this->user->user_id);
        foreach ($user_types as $user_type)
        {
            $this->allowed_dataset_types[$user_type->dataset_type_id] = 'Y';
        }

        $user_access = new UserSoftwareAccess();
        $user_types = $user_access->Find("user_id = ? ", $this->user->user_id);
        foreach ($user_types as $user_type)
        {
            $this->allowed_software_types[$user_type->software_type_id] = 'Y';
        }
        
        $uda = new UserDatasetAccess();
		$datasetAccessList = $uda->find("user_id=?", array($id));

        $da_ids = array();
        foreach($datasetAccessList as $da_entry)
        {
            $da_ids[] = $da_entry->dataset_type_id;
        }
		$this->da_ids = $da_ids;

        $usa = new UserSoftwareAccess();
		$softwareAccessList = $usa->find("user_id=?", array($id));

        $sa_ids = array();
        foreach($softwareAccessList as $sa_entry)
        {
            $sa_ids[] = $sa_entry->software_type_id;
        }
		$this->sa_ids = $sa_ids;
        
    }
}

?>
