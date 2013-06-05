<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Manage manipulations with users.
 
    (c)2009-2011 Foundation for the National Institutes of Health (FNIH)
 
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
require_once("OMOP/WebRL/Configuration/WebRLConfiguration.php");
require_once("OMOP/WebRL/Configuration/UserStorageConfiguration.php");
require_once("OMOP/WebRL/InstanceRemoteController.php");

class UserManager
{
    var $db;
    function __construct($db)
    {
        $this->db = $db;
    }
    /*
    Returns count of all available users.
    */
    function get_users_count($organization_id = false)
    {
    	if ($organization_id)
    	{
    		$user_count = $this->db->getone("SELECT count(*) from user_tbl where organization_id = ?", array($organization_id));
        	return $user_count;
    	}
        $user_count = $this->db->getone("SELECT count(*) from user_tbl");
        return $user_count;
    }
    function get_instances_price($user_id)
    {
        $user_count = $this->db->getone(
"SELECT ROUND(SUM(s.instance_price * ( TIMESTAMPDIFF(SECOND, i.start_date, ifnull(i.terminate_date, utc_timestamp())) ))/ 3600)
FROM instance_request_tbl r 
JOIN instance_size_tbl s on r.instance_size_id = s.instance_size_id
JOIN instance_tbl i on i.instance_request_id = r.instance_request_id
where r.user_id = ?
", array($user_id));
        return $user_count;
    }

    /**
     * Returns array of all available users.
     * @param int $organization_id ID number of the organization to select users from
     * @param mixed $sort_mode integer, number of the parameter to sort the result set by, the parameter for $this->get_column_name() method; false otherwise
     * @param mixed $sort_order sort order of the result set, string 'ASC' or 'DESC', FALSE for default value
     */
    function get_all_users($organization_id, $sort_mode = false, $sort_order = false)
    {
        return $this->get_users($organization_id, -1, 0, $sort_mode, $sort_order);
    }
    function get_users($organization_id, $page_num, $page_size, $sort_mode = false, $sort_order = false)
    {
        $limit = $page_size * ($page_num);
        $offset = $page_size * ($page_num - 1);

        $extras = array();
		if ($limit >= 0) $extras['limit'] = $limit;
		if ($offset >= 0) $extras['offset'] = $offset;

        if ($sort_mode == false)
            $sort_mode = 2;
        if ($sort_order == false)
            $sort_order = 'ASC';
        
        $where = '';
        if ($organization_id != 0)
        {
         	$where = "WHERE o.organization_id = {$organization_id}";
        }
        $column_name = $this->get_column_name($sort_mode);
$qry = "SELECT u.*, 
(SELECT COUNT(*)
FROM instance_request_tbl r 
JOIN instance_tbl i on i.instance_request_id = r.instance_request_id
where i.status_flag = 'A'
AND u.user_id = r.user_id
) as running_instances_count,
(SELECT COUNT(*)
FROM instance_request_tbl r 
JOIN instance_tbl i on i.instance_request_id = r.instance_request_id
where u.user_id = r.user_id
) as total_instances_count,
(SELECT ROUND(SUM(s.instance_price * TIMESTAMPDIFF(SECOND, i.start_date, IFNULL(i.terminate_date, utc_timestamp()) ))/ 3600, 0)
FROM instance_request_tbl r 
JOIN instance_size_tbl s on r.instance_size_id = s.instance_size_id
JOIN instance_tbl i on i.instance_request_id = r.instance_request_id
where u.user_id = r.user_id) as total_charged,
u.user_money - IFNULL((SELECT ROUND(SUM(s.instance_price * TIMESTAMPDIFF(SECOND, i.start_date, IFNULL(i.terminate_date, utc_timestamp()) ))/ 3600, 0)
FROM instance_request_tbl r 
JOIN instance_size_tbl s on r.instance_size_id = s.instance_size_id
JOIN instance_tbl i on i.instance_request_id = r.instance_request_id
where u.user_id = r.user_id),0.0) as remains_limit
FROM user_tbl u
LEFT JOIN organization_tbl o ON o.organization_id = u.organization_id
".$where."
ORDER BY ".$column_name." ".$sort_order;

        if ($page_size == 0)
            $rs = $this->db->Execute($qry);
        else $rs = $this->db->SelectLimit($qry, $page_size, $offset);
        $rows = array();
        if ($rs) {
			while (!$rs->EOF) {
				$fields = $rs->fields;
                $count = count($rs->fields) / 2;
                unset($fields['total_instances_count']);
                unset($fields['total_charged']);
                unset($fields['running_instances_count']);
                unset($fields['remains_limit']);
                unset($fields[$count-4]);
                unset($fields[$count-3]);
				unset($fields[$count-2]);
                unset($fields[$count-1]);
				$rows[] = $fields;
                $rs->MoveNext();
			}
		}
        $arr = array();
        foreach($rows as $row) 
        {
            $obj = new User();
            if ($obj->ErrorNo()){
                $db->_errorMsg = $obj->ErrorMsg();
                return false;
            }
            $obj->Set($row);
            $arr[] = $obj;
        } 
        return $arr;
    }
    function get_column_name($sort_mode)
    {
        $columns = array("user_id", "login_id", "last_name", "email", 
        "active_flag", "running_instances_count", "total_charged","remains_limit",
        'total_instances_count','user_money', 'last_name, first_name', 'o.organization_name');
        return $columns[$sort_mode];
    }
    /*
    Gets user by it's id.
    */
    function get($id)
    {
        $user = new User();
        $user->load("user_id = ?", array($id));
        return $user;
    }
    /*
    Gets user by it's login.
    */
    function get_by_login($login)
    {
        $user = new User();
        $user->load("login_id = ?", array($login));
        return $user;
    }
    /*
    Create user with given parameters.
    */
    function create_user($login_id, $first_name, $last_name, $email, $phone, $organization_id, $title, $active_flag, $admin_flag, $svn_access_flag, $user_money, $num_instances, $password, $database_types, $software_types, $certificate, $user_volume_size)
    {
        $current_user = Membership::get_current_user();

        $this->db->StartTrans();
        $user = new User();
        $user->login_id = $login_id;
        $user->first_name = $first_name;
        $user->last_name = $last_name;
        $user->email = $email;
        $user->phone = $phone;
        $user->organization_id = $organization_id;
        $user->title = $title;
        $user->active_flag = $active_flag;
        $user->admin_flag = $admin_flag;
        $user->svn_access_flag = $svn_access_flag;
        $user->user_money = $user_money;
        $user->num_instances = $num_instances;
        $user->user_ebs = '';
        $user->user_volume_size = $user_volume_size;
        
        $user->set_password($password);
        $password_expiration_period = SiteConfig::get()->password_expiration_period;
        if ($password_expiration_period != 0)
        {
            $user->password_expired = self::add_date(gmdate('c'), $password_expiration_period);
        }
        else
        {
            $user->password_expired = null;
        }

        $user->internal_id = uniqid();
		
        $user->created_by = $current_user->login_id;
        $user->updated_by = $current_user->login_id;
        $current_date = gmdate('c');
        $user->created_date = $current_date;
        $user->updated_date = $current_date;

        $user->save();
        $new_user_id = $user->user_id; // Check this line of code for Oracle compatibility.
        
        self::record_user_password($user);
        
        if ($database_types)
        {
            foreach($database_types as $dt_id)
            {
                $uda = new UserDatasetAccess();
                $uda->user_id = $new_user_id;
                $uda->dataset_type_id = $dt_id;
                $uda->save();
            }
        }

        if ($software_types)
        {
            foreach($software_types as $st_id)
            {
                $usa = new UserSoftwareAccess();
                $usa->user_id = $new_user_id;
                $usa->software_type_id = $st_id;
                $usa->save();
            }
        }

        $this->db->CompleteTrans();
        $this->generate_key($user, $password);

        $changes_info = $this->get_changes($user, $database_types, $software_types);

        $sm = new SecurityManager();
        $sm->generate_security_event(SecurityManager::ISSUE_TYPE_USER_CREATED, "User $this->login_id created. Information:".$changes_info, $new_user_id, null, null, null);

        if ($certificate != null)
        {
            $this->update_certificate($user, $certificate);
        }
        
        $access = new SvnDavAccess();
        $db = DbManager::$db;
        $om = new OrganizationManager($om);
        $accesses = $om->get_svn_access_by_folders();
        global $configurationManager;
        $configuration = new WebRLConfiguration($configurationManager);
        $access->update_organization_access($accesses, $configuration->svn_access_file());
        
        return $user;
    }
    function create_storage_user(User $user)
    {
        global $configurationManager;
    	$psm = new PersonalStorageManager($configurationManager);
    	$psm->create_storage_user($user);
    }
    function rename_login($user, $login_id)
    {
    	if ($user->login_id != $login_id)
		{
			global $configurationManager; 
    		$configuration = new WebRLConfiguration($configurationManager);
    		if (is_dir($configuration->folder_keys().$user->login_id))
    		{
    			$old_dir = $configuration->folder_keys().$user->login_id;
    			$new_dir = $configuration->folder_keys().$login_id;
    			
    			// Rename directory that holds scripts.
    			rename($old_dir, $new_dir);
    			// And then all files inside.
    			rename($new_dir.'/'.$user->login_id, $new_dir.'/'.$login_id);
    			rename($new_dir.'/'.$user->login_id.'.ppk', $new_dir.'/'.$login_id.'.ppk');
    			rename($new_dir.'/'.$user->login_id.'.pub', $new_dir.'/'.$login_id.'.pub');
    		}
    		$user->login_id = $login_id;
			$user->save();
		}		
    }
    function save_user($id, $login_id, $first_name, $last_name, $email, $phone, $organization_id, $title, $active_flag, $admin_flag, $svn_access_flag, $user_money, $num_instances, $password, $database_types, $software_types, $certificate)
    {
        $current_user = Membership::get_current_user();
					
        $user = new User();
        $user->load("user_id = ?", array($id));
        
        $changes_info = $this->get_changes($user, $database_types, $software_types);
		if ($user->login_id != $login_id)
		{
			self::rename_login($user, $login_id);
		}		
        
        $user->first_name = $first_name;
        $user->last_name = $last_name;
        $user->email = $email;
        $user->phone = $phone;
        $user->organization_id = $organization_id;
        $user->title = $title;
        $user->active_flag = $active_flag == null ? '' : $active_flag;
        $user->admin_flag = $admin_flag;
        $user->svn_access_flag = $svn_access_flag;
        $user->user_money = $user_money;
        $user->num_instances = $num_instances;
        if ($password != '' && $password != null)
        {
            $user->set_password($password);
            $password_expiration_period = SiteConfig::get()->password_expiration_period;
            if ($password_expiration_period != 0)
            {
                $user->password_expired = self::add_date(gmdate('c'), $password_expiration_period);
            }
            else
            {
                $user->password_expired = null;
            }
        }
        $user->updated_by = $current_user->login_id;
        $user->updated_date = gmdate('c');
        $user->save();
        
        self::record_user_password($user);
        
		self::set_database_types($id, $database_types);
        self::set_software_types($id, $software_types);

        if ($user->certificate_public_key == '' || ($password != '' && $password != null && SiteConfig::get()->certificates_with_password == 'Y')
        	|| ($user->certificate_has_password != SiteConfig::get()->certificates_with_password))
        {
            $this->generate_key($user, $password);
        }
        if ($certificate != null)
        {
            $this->update_certificate($user, $certificate);
        }
        
        $access = new SvnDavAccess();
        $db = DbManager::$db;
        $om = new OrganizationManager($db);
        $accesses = $om->get_svn_access_by_folders();
        global $configurationManager;
        $configuration = new WebRLConfiguration($configurationManager);
        $access->update_organization_access($accesses, $configuration->svn_access_file());        
        
        $sm = new SecurityManager();
        $sm->generate_security_event(SecurityManager::ISSUE_TYPE_USER_INFORMATION_CHANGED, "Information for user $this->login_id changed. Old Information:".$changes_info, $user->user_id, null, null, null);
    }

    /**
     * Get the list of users who granted permissions to use their storages to
     * the given user.
     * @param int $id User identifier who is looking for who gave him access
     */
    function get_accessible_storage($id)
    {
        $query = "SELECT distinct `us`.`user_id`, `u`.`login_id`
FROM `user_storage_access_tbl` as `us`
LEFT JOIN `user_tbl` as `u` on `u`.`user_id` = `us`.`user_id`
WHERE `us`.`grantee_id` = ? AND
`u`.`login_id` IS NOT NULL
ORDER BY `u`.`login_id`";
        $params = array($id);

        $rs = $this->db->Execute($query, $params);
        $rows = array();
    	while (!$rs->EOF) {
			$rows[] = $rs->fields;
            $rs->MoveNext();
		}
        return $rows;
    }

    /**
     * Share permissions with specific users.
     * @param int $id User identifier which want change personal storage sharing options. 
     * @param array $share_users Array of User ID's for which user will share their personal storage.
     */
    function set_storage_access($id, $share_users)
    {
    	if (!$share_users)
        {
            $share_users = array();
        }
        $usa = new UserStorageAccess();
        $storageAccessList = $usa->find("user_id=?", array($id));

        $users = self::get_all_active_users(0);
        
        $sa_ids = array();//array_map($f, $datasetAccessList);
        foreach($storageAccessList as $sa_entry)
        {
            $sa_ids[] = $sa_entry->grantee_id;
        }
        $sharing_users = array();             
        foreach($users as $user)
        {
        	$item_id = $user->user_id;
            $value_set_in_db = in_array($item_id, $sa_ids);
            $value_set_in_ui = in_array($item_id, $share_users);
            
            if ($value_set_in_ui)
            {
            	$sharing_users[] = $user->login_id;
            }
            if ($value_set_in_db && $value_set_in_ui)
            {
                // Do nothing because access doesn't changed for this dataset schema
            }
            if (!$value_set_in_db && !$value_set_in_ui)
            {
                // Do nothing because access doesn't changed for this dataset schema
            }
            if (!$value_set_in_db && $value_set_in_ui)
            {
                // Add new record in the user_data_access_tbl
                $usa = new UserStorageAccess();
                $usa->user_id = $id;
                $usa->grantee_id = $item_id;
                
                $usa->save();
            }
            if ($value_set_in_db && !$value_set_in_ui)
            {
                // Do nothing
                $usa = new UserStorageAccess();
                $usa->load("user_id = ? AND grantee_id = ?", array($id, $item_id));
                $usa->delete();
            }
        }
        //if (count($sharing_users) != 0)
        {
        	global $configurationManager;
            $usmanager = new UserStorageConfiguration($configurationManager);
            $users_list = implode(',',$sharing_users); 
            $user = new User();
        	$user->load('user_id = ?', $id);
        	
            $command = 'groupmems -p -g '.$user->login_id.'; ';
            $command = $command.' groupmems -a '.$user->login_id.' -g '.$user->login_id.';';
    		foreach($sharing_users as $usr)
    		{
    			$command = $command.' groupmems -a '.$usr.' -g '.$user->login_id.';';
    		}
    		$controller = new InstanceRemoteController($configurationManager);
            $storage_host = $usmanager->storage_host();
    		$controller->connect_to_instance($storage_host);
    		$controller->execute($command);
        }
    }
	/**
     * Set permissions to which user has access.
     * @param int $id User identifier for which permissions will be set. 
     * @param array $database_types Array of Dataset ID's to which user will have access.
     */
    function set_database_types($id, $database_types)
    {
    	if ($database_types)
        {
            $uda = new UserDatasetAccess();
            $datasetAccessList = $uda->find("user_id=?", array($id));

            $dbc = new DatasetType();
            $db_types = $dbc->find("1=1 order by sort_order"); // Retreive list of available dataset types.
            
            $da_ids = array();//array_map($f, $datasetAccessList);
            foreach($datasetAccessList as $da_entry)
            {
                $da_ids[] = $da_entry->dataset_type_id;
            }
            
            foreach($db_types as $db_type)
            {
                $value_set_in_db = in_array($db_type->dataset_type_id, $da_ids);
                $value_set_in_ui = in_array($db_type->dataset_type_id, $database_types);
                if ($value_set_in_db && $value_set_in_ui)
                {
                    // Do nothing because access doesn't changed for this dataset schema
                }
                if (!$value_set_in_db && !$value_set_in_ui)
                {
                    // Do nothing because access doesn't changed for this dataset schema
                }
                if (!$value_set_in_db && $value_set_in_ui)
                {
                    // Add new record in the user_data_access_tbl
                    $uda = new UserDatasetAccess();
                    $uda->user_id = $id;
                    $uda->dataset_type_id = $db_type->dataset_type_id;
                    
                    $uda->save();
                }
                if ($value_set_in_db && !$value_set_in_ui)
                {
                    // Do nothing
                    $uda = new UserDatasetAccess();
                    $uda->load("user_id = ? AND dataset_type_id = ?", array($id, $db_type->dataset_type_id));
                    $uda->delete();
                }
            }
        }
    }
    /**
     * Set permissions for accessing software types for the user.
     * @param int $id User identifier for which permissions will be set. 
     * @param array $software_types Array of Software ID's to which user will have access.
     */
    function set_software_types($id, $software_types)
    {
    	if ($software_types)
        {
            $usa = new UserSoftwareAccess();
            $softwareAccessList = $usa->find("user_id=?", array($id));

            $softc = new SoftwareType();
            $soft_types = $softc->find("1=1"); // Retreive list of available dataset types.
            
            $sa_ids = array();
            foreach($softwareAccessList as $sa_entry)
            {
                $sa_ids[] = $sa_entry->software_type_id;
            }
            
            foreach($soft_types as $soft_type)
            {
                $value_set_in_db = in_array($soft_type->software_type_id, $sa_ids);
                $value_set_in_ui = in_array($soft_type->software_type_id, $software_types);
                if ($value_set_in_db && $value_set_in_ui)
                {
                    // Do nothing because access doesn't changed for this dataset schema
                }
                if (!$value_set_in_db && !$value_set_in_ui)
                {
                    // Do nothing because access doesn't changed for this dataset schema
                }
                if (!$value_set_in_db && $value_set_in_ui)
                {
                    // Add new record in the user_data_access_tbl
                    $usa = new UserSoftwareAccess();
                    $usa->user_id = $id;
                    $usa->software_type_id = $soft_type->software_type_id;
                    
                    $usa->save();
                }
                if ($value_set_in_db && !$value_set_in_ui)
                {
                    // Do nothing
                    $usa = new UserSoftwareAccess();
                    $usa->load("user_id = ? AND software_type_id = ?", array($id, $soft_type->software_type_id));
                    $usa->delete();
                }
            }
        }
    }
    function record_user_password($user)
    {
        $uph = new UserPasswordHistory();
        $uph->user_id = $user->user_id;
        $uph->password_hash = $user->password_hash;
        $uph->change_date = gmdate('c');
        $uph->Save(); 
    }
    function get_changes($user, $database_types, $software_types)
    {
        $message = "";
        foreach($user->GetAttributeNames() as $name)
        {
            $message .= $name.'='.$user->$name.', ';
        }
        if ($database_types)
        {
            $message .= 'database_types = {'.implode(',', $database_types).'}';
        }
        if ($database_types && $software_types)
            $message .= ', ';
        if ($software_types)
        {
            $message .= ', software_types = {'.implode(',', $software_types).'}';
        }
        return $message;
    }
    function update_certificate($user, $certificate)
    {
        $current_user = Membership::get_current_user();
        // Add certificate to history table
        $c = new UserCertificate();
        $c->user_id = $user->user_id;
        $c->public_key = $current_user->certificate_public_key;
        $c->created_by = $current_user->login_id;
        $c->created_date = gmdate('c');
        $c->has_password = $current_user->certificate_has_password ? $current_user->certificate_has_password : 'N';
        $c->expiration_date = gmdate('c');
        $c->status_flag = 'N';
        $c->save();
        // Set new certiicate for user.
        $user->certificate_public_key = $certificate;
        $user->certificate_has_password = SiteConfig::get()->certificates_with_password;
        $user->Save();
    }
    function generate_key($user, $password)
    {
        global $configurationManager; 
    	$configuration = new WebRLConfiguration($configurationManager);
    	
        if (SiteConfig::get()->certificates_with_password == 'Y')
        {
            $commandLine = '/bin/bash '.$configuration->support_scripts_folder().'create_key.sh '.escapeshellcmd($user->login_id).' '.escapeshellcmd($password);
        }
        else
        {
            $commandLine = '/bin/bash '.$configuration->support_scripts_folder().'create_key.sh '.escapeshellcmd($user->login_id);
        }
        exec($commandLine, $output, $retval);
        if ($retval > 0)
        {
            throw new Exception("Cannot generate key files for the user $user->login_id");
        }
        $certificate = file_get_contents($configuration->folder_keys().$user->login_id.'/'.$user->login_id.'.pub');
        $this->update_certificate($user, $certificate);
    }
    function update_dav_authorization()
    {
        global $configurationManager; 
    	$configuration = new WebRLConfiguration($configurationManager);
    	
        $u = new User();
        $users = $u->find(" active_flag = 'Y' and svn_access_flag = 'Y' ");
        $access = new SvnDavAccess($configuration->dav_authorization_file(), $users);
        $access->update();
        $db = DbManager::$db;
        $om = new OrganizationManager($db);
        $accesses = $om->get_svn_access_by_folders();
        $access->update_organization_access($accesses, $configuration->svn_access_file());
        
        
        }    
    function add_date($givendate,$day=0,$mth=0,$yr=0) 
    {
        $cd = strtotime($givendate);
        $newdate = date('Y-m-d h:i:s', mktime(date('h',$cd),
            date('i',$cd), date('s',$cd), date('m',$cd)+$mth,
            date('d',$cd)+$day, date('Y',$cd)+$yr));
        return $newdate;
    }

    /**
     * Returns array of all active users.
     * @param int $organization_id ID number of the organization to select users from
     * @param mixed $sort_mode integer, number of the parameter to sort the result set by, the parameter for $this->get_column_name() method; false otherwise
     * @param mixed $sort_order sort order of the result set, string 'ASC' or 'DESC', FALSE for default value
     */
    function get_all_active_users($organization_id, $sort_mode = false, $sort_order = false)
    {
        $users = $this->get_all_users($organization_id, $sort_mode, $sort_order);
        $active_users = array();
        foreach($users as $u)
        {
            if ($u->active_flag =='Y') $active_users[] = $u;
        }
        return $active_users;
    }

    function get_users_allowed_sharing($organization_id, $userId, $sort_mode = false, $sort_order = false)
    {
        $orgUsers = $this->get_all_active_users($organization_id, $sort_mode, $sort_order);
        $query = "
SELECT distinct us.additional_user_id, u.login_id
FROM		additional_share_user_tbl 	as us
	JOIN 	user_tbl 					as u 
		on 	u.user_id	= us.additional_user_id
WHERE us.user_id = ?";
		$params = array($userId);
		
    	$rs = $this->db->Execute($query,$params);
    	while (!$rs->EOF) {
			$fields = $rs->fields;
			$user = new User();
			$user->user_id = $fields["user_id"];
			$user->login_id = $fields["login_id"];
			$orgUsers[] = $user;
            $rs->MoveNext();
		}
        return $orgUsers;
    }
    function set_password_expiration_date($organization_id, $date)
    {
    	$all_users = $this->get_all_users($organization_id);
		foreach($all_users as $u)
		{
        	$u->password_expired = $date;
        	$u->Save();
		}
    }

    function get_budget_current($type, $id) {
        switch (strtolower($type))
        {
            case 'user':
                $query = "select count(`i`.`instance_id`) as `instances`, `u`.`user_id`, `u`.`organization_id`
from `instance_tbl` as `i`
left join `instance_request_tbl` as `ir` on `ir`.`instance_request_id` = `i`.`instance_request_id`
left join `user_tbl` as `u` on `u`.`user_id` = `ir`.`user_id`
where `u`.`user_id` = ? and `i`.`status_flag` = 'A'
group by `u`.`user_id`";
                $params = array($id);
                return $this->db->GetRow($query, $params);
                break;
            case 'organization':
                $query = "select `o`.`organization_id`, `o`.`organization_budget` as `limit`, `o`.`organization_instances_limit` as `instances`
from `organization_tbl` as `o`
where `o`.`organization_id` = ?";
                $params = array($id);
                return $this->db->GetRow($query, $params);
                break;
        }
    }

    function get_budget_remaining($type, $id) {
        switch (strtolower($type))
        {
            case 'user':
                $query = "
select `u`.`user_id`,
  `o`.`organization_id`,
  `u`.`user_money` as `limit`,
  sum(
    (`i`.`instance_hour_charge` + `i`.`storage_hour_charge`) *
	  TIMEDIFF(COALESCE(`i`.`terminate_date`,CURRENT_TIMESTAMP()), `i`.`start_date`) / 3600
  ) as `charge`
from `instance_tbl` as `i`
left join `instance_request_tbl` as `ir` on `ir`.`instance_request_id` = `i`.`instance_request_id`
left join `user_tbl` as `u` on `u`.`user_id` = `ir`.`user_id`
left join `organization_tbl` as `o` on `o`.`organization_id` = `u`.`organization_id`
where
  `u`.`user_id` = ?
group by `u`.`user_id`";
                $params = array($id);
                /**
                 * $row['user_id']
                 * $row['organization_id']
                 * $row['limit']
                 * $row['charge']
                 */
                return $this->db->GetRow($query, $params);
                break;
            case 'organization':
                $query = "select `o`.`organization_id`, `o`.`organization_budget` as `limit`, `o`.`organization_instances_limit` as `instances`
from `organization_tbl` as `o`
where `o`.`organization_id` = ?";
                $params = array($id);
                return $this->db->GetRow($query, $params);
                break;
        }
    }

    function get_budget_statistics($type, $id) {
        switch (strtolower($type))
        {
            case 'user':
                $query = "select `u`.`user_id`, `u`.`organization_id`, `u`.`user_money` as `limit`, `u`.`num_instances` as `instances`
from `user_tbl` as `u`
where `u`.`user_id` = ?";
                $params = array($id);
                return $this->db->GetRow($query, $params);
                break;
            case 'organization':
                $query = "select `o`.`organization_id`, `o`.`organization_budget` as `limit`, `o`.`organization_instances_limit` as `instances`
from `organization_tbl` as `o`
where `o`.`organization_id` = ?";
                $params = array($id);
                return $this->db->GetRow($query, $params);
                break;
        }
    }

    function get_budget($date_start, $date_end)
    {
        /*
-- Params
-- D1: datetime, -- 2010-06-03 00:00:00
-- D2: datetime, -- 2010-06-21 00:00:00
-- I1: datetime, -- 2010-06-01 00:00:00 -- month start datetime
-- I2: datetime, -- 2010-06-30 23:59:59 -- month end datetime
-- D1 and D2 are always within the same month

-- IF(COALESCE(i.terminate_date,CURRENT_TIMESTAMP())>cast('2010-06-30 23:59:59' as DATETIME),cast('2010-06-30 23:59:59' as DATETIME),COALESCE(i.terminate_date,CURRENT_TIMESTAMP()))
         */
        $query = "
select `u`.`user_id`, CONCAT(`u`.`last_name`,', ',`u`.`first_name`) as user_name,
  --COALESCE(o.organization_id,0), COALESCE(o.organization_name,'[other]'),
  o.organization_id, o.organization_name,
  count(i.instance_id) as inst_amount,
  sum(
    (i.instance_hour_charge + i.storage_hour_charge) *
	 TIMEDIFF(
	   IF(COALESCE(i.terminate_date,CURRENT_TIMESTAMP())>cast('".$date_end."' as DATETIME),cast('".$date_end."' as DATETIME),COALESCE(i.terminate_date,CURRENT_TIMESTAMP())),
	   IF(i.start_date<cast('".$date_start."' as DATETIME),cast('".$date_start."' as DATETIME),i.start_date)
     ) / 3600
  ) as charge,
  extract(MONTH from i.start_date) as m, extract(YEAR from i.start_date) as y
from `instance_tbl` as `i`
left join `instance_request_tbl` as `ir` on `ir`.`instance_request_id` = `i`.`instance_request_id`
left join `user_tbl` as `u` on `u`.`user_id` = `ir`.`user_id`
left join `organization_tbl` as `o` on `o`.`organization_id` = `u`.`organization_id`
where
  COALESCE(i.terminate_date,CURRENT_TIMESTAMP()) > cast('".$date_start."' as DATETIME) and
  i.start_date < cast('".$date_end."' as DATETIME) and
  (o.organization_deleted_date IS NULL or o.organization_deleted_date > cast('".$date_start."' as DATETIME))
group by `u`.`user_id`
order by `organization_name`, `user_name`";
        //return htmlspecialchars($query);
        $params = array();

        $rs = $this->db->Execute($query);
        $rows = array();
    	while (!$rs->EOF) {
			$rows[] = $rs->fields;
            $rs->MoveNext();
		}

        $query = "
select i.system_instance_id as user_id, i.system_instance_name as user_name,
  'system' as organization_id, '[system]' as organization_name,
  1 as inst_amount,
  
    (is.instance_price) *
	 TIMEDIFF(
	   IF(COALESCE(cast(i.system_instance_end_date as DATETIME),CURRENT_TIMESTAMP())>cast('".$date_end."' as DATETIME),cast('".$date_end."' as DATETIME),COALESCE(cast(i.system_instance_end_date as DATETIME), CURRENT_TIMESTAMP())),
	   IF(i.system_instance_launch_date<cast('".$date_start."' as DATETIME),cast('".$date_start."' as DATETIME),i.system_instance_launch_date)
     ) / 3600
   as charge,
  extract(MONTH from i.system_instance_launch_date) as m, extract(YEAR from i.system_instance_launch_date) as y
from `system_instances_tbl` as `i`
left join `instance_size_tbl` as `is` on `is`.`instance_size_id` = `i`.`system_instance_size_id`
where
  COALESCE(i.system_instance_end_date,CURRENT_TIMESTAMP()) > cast('".$date_start."' as DATETIME) and
  i.system_instance_launch_date < cast('".$date_end."' as DATETIME)
group by user_id
order by user_name DESC
";

        $rs = $this->db->Execute($query);
//        $rows = array();
		if (! empty($rows) && $rows[0]['organization_id'] == 0) 
			$org0 = array_shift($rows);
    	while (!$rs->EOF) {
				array_unshift($rows, $rs->fields);
            $rs->MoveNext();
		}
		if (isset($org0))
			array_unshift($rows, $org0);
		return $rows;
    }
}

?>
