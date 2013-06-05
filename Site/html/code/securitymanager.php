<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Class that handles all security related activities.
 
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

class SecurityManager
{
    const ISSUE_TYPE_LOGIN_SUCCESS = 1;
    const ISSUE_TYPE_LOGIN_SSH_TRANSFER = 2;
    const ISSUE_TYPE_LOGIN_MORE_THAN_3_LOGINS = 3;
    const ISSUE_TYPE_LOGIN_SITE_DOWN = 4;
    const ISSUE_TYPE_PASSWORD_CHANGED = 5;
    const ISSUE_TYPE_USER_INFORMATION_CHANGED = 6;
    const ISSUE_TYPE_USER_CREATED = 7;
    const ISSUE_TYPE_SITE_CONFIGURATION_CHANGED = 8;
    const ISSUE_TYPE_INSTANCE_SSH_TRANSFER = 9;

    var $db;
    function __construct($db = false)
    {
        if (!$db)
            $db = DbManager::$db;
        $this->db = $db;
    }
    function get_security_log_entries_count($event_id = false, $start_date = false, $end_date = false)
    {
        $filter = self::get_security_log_filter($event_id, $start_date, $end_date);
        $qry = "SELECT count(*)
FROM security_log l
JOIN user_tbl u on l.user_id = u.user_id
LEFT JOIN instance_tbl i on l.instance_id = i.instance_id
WHERE ".$filter." 1= 1 ";
        return $this->db->getone($qry);
    }
    function get_website_event_log_entries_count()
    {
        $qry = "SELECT count(*)
FROM website_event_log l
LEFT JOIN user_tbl u on l.user_id = u.user_id
";
        return $this->db->getone($qry);
    }
    function get_user_connect_log_entries_count($mode = false, $event_id = false, $start_date = false, $end_date = false)
    {
        $filter = self::get_user_connect_log_filter($mode, $event_id, $start_date, $end_date);
        $qry = "SELECT count(*)
FROM user_connect_log l
LEFT JOIN user_tbl u on l.user_id = u.user_id
LEFT JOIN instance_tbl i on l.instance_id = i.instance_id
WHERE ".$filter." 1= 1 ";
        return $this->db->getone($qry);
    }
    function get_user_connect_log_entries($mode = false, $event_id = false, $start_date = false, $end_date = false, $pagenum = false, $pagesize = false, $sort_mode = false, $sort_order = false)
    {
        if (!$pagenum)
            $pagenum = 1;
        if (!$pagesize)
            $pagesize = 20;
        if (!$sort_mode)
            $sort_mode = 0;
        if (!$sort_order)
            $sort_order = 'desc';
        $filter = self::get_user_connect_log_filter($mode, $event_id, $start_date, $end_date);

        $limit = $pagesize * $pagenum;
        $offset = $pagesize * ($pagenum - 1);

		$qry = "SELECT l.*,
u.login_id, u.first_name, u.last_name,
i.assigned_name,i.amazon_instance_id
FROM user_connect_log l
LEFT JOIN user_tbl u on l.user_id = u.user_id
LEFT JOIN instance_tbl i on l.instance_id = i.instance_id
WHERE ".$filter." 1 = 1 ORDER BY " . self::get_user_connect_log_sort_column($sort_mode) . " " . $sort_order;

        if ($pagesize == 0)
            $rs = $this->db->Execute($qry);
        else 
            $rs = $this->db->SelectLimit($qry, $pagesize, $offset);
        
        $rows = array();
        if ($rs) {
			while (!$rs->EOF) {
				$fields = $rs->fields;
                $count = count($rs->fields) / 2;
                unset($fields['login_id']);
                unset($fields['first_name']);
                unset($fields['last_name']);
                unset($fields['assigned_name']);
                unset($fields['amazon_instance_id']);
				unset($fields[$count-5]);
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
            $obj = new UserConnectLog();
            if ($obj->ErrorNo()){
                $db->_errorMsg = $obj->ErrorMsg();
                return false;
            }
            $obj->Set($row);
            $arr[] = $obj;
        } 
        return $arr;
    }
    function get_security_log_entries($event_id = false, $start_date = false, $end_date = false, $pagenum = false, $pagesize = false, $sort_mode = false, $sort_order = false)
    {    	
        if (!$pagenum)
            $pagenum = 1;
        if (!$pagesize)
            $pagesize = 20;
        if (!$sort_mode)
            $sort_mode = 0;
        if (!$sort_order)
            $sort_order = 'desc';
        $filter = self::get_security_log_filter($event_id, $start_date, $end_date);

        $limit = $pagesize * $pagenum;
        $offset = $pagesize * ($pagenum - 1);

		$qry = "SELECT l.*,
u.login_id, u.first_name, u.last_name,
i.assigned_name,i.amazon_instance_id
FROM security_log l
LEFT JOIN user_tbl u on l.user_id = u.user_id
LEFT JOIN instance_tbl i on l.instance_id = i.instance_id
WHERE ".$filter." 1= 1 ORDER BY " . self::get_security_log_sort_column($sort_mode) . " " . $sort_order;
        if ($pagesize == 0)
            $rs = $this->db->Execute($qry);
        else 
            $rs = $this->db->SelectLimit($qry, $pagesize, $offset);
        
        $rows = array();
        if ($rs) {
			while (!$rs->EOF) {
				$fields = $rs->fields;
                $count = count($rs->fields) / 2;
                unset($fields['login_id']);
                unset($fields['first_name']);
                unset($fields['last_name']);
                unset($fields['assigned_name']);
                unset($fields['amazon_instance_id']);
				unset($fields[$count-5]);
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
            $obj = new SecurityLog();
            if ($obj->ErrorNo()){
                $db->_errorMsg = $obj->ErrorMsg();
                return false;
            }
            $obj->Set($row);
            $arr[] = $obj;
        } 
        return $arr;
    }

    /**
     * Get the event detsails
     * @param integer event ID
     * @return array event details
     */
    function get_security_event_details($id) {
        $query = "SELECT l.*,
u.login_id, u.first_name, u.last_name,
i.assigned_name,i.amazon_instance_id
FROM security_log l
LEFT JOIN user_tbl u on l.user_id = u.user_id
LEFT JOIN instance_tbl i on l.instance_id = i.instance_id
WHERE l.security_log_id = ?";
        $rs = $this->db->Execute($query, array($id));

        $rows = array();
        if ($rs) {
            while (!$rs->EOF) {
                $fields = $rs->fields;
                $count = count($rs->fields) / 2;
                unset($fields['login_id']);
                unset($fields['first_name']);
                unset($fields['last_name']);
                unset($fields['assigned_name']);
                unset($fields['amazon_instance_id']);
                unset($fields[$count-5]);
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
            $obj = new SecurityLog();
            if ($obj->ErrorNo()){
                $db->_errorMsg = $obj->ErrorMsg();
                return false;
            }
            $obj->Set($row);
            $arr[] = $obj;
        }
        return $arr;
    }

    function get_website_event_log_entries($pagenum = false, $pagesize = false, $sort_mode = false, $sort_order = false)
    {
        if (!$pagenum)
            $pagenum = 1;
        if (!$pagesize)
            $pagesize = 20;
        if (!$sort_mode)
            $sort_mode = 0;
        if (!$sort_order)
            $sort_order = 'desc';

        $limit = $pagesize * $pagenum;
        $offset = $pagesize * ($pagenum - 1);

		$qry = "SELECT l.*,
u.login_id, u.first_name, u.last_name
FROM website_event_log l
LEFT JOIN user_tbl u on l.user_id = u.user_id
ORDER BY " . self::get_website_event_log_sort_column($sort_mode) . " " . $sort_order;
        if ($pagesize == 0)
            $rs = $this->db->Execute($qry);
        else 
            $rs = $this->db->SelectLimit($qry, $pagesize, $offset);
        
        $rows = array();
        if ($rs) {
			while (!$rs->EOF) {
				$fields = $rs->fields;
                $count = count($rs->fields) / 2;
                unset($fields['login_id']);
                unset($fields['first_name']);
                unset($fields['last_name']);
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
            $obj = new WebSiteEvent();
            if ($obj->ErrorNo()){
                $db->_errorMsg = $obj->ErrorMsg();
                return false;
            }
            $obj->Set($row);
            $arr[] = $obj;
        } 
        return $arr;
    }
    function get_security_log_sort_column($sort_mode)
    {
        $column_names = array('security_log_date', 'security_issue_type_id', 'u.last_name', 
            'u.login_id', 'remote_ip', 'i.assigned_name', 'i.amazon_instance_id', 'action_message');
        return $column_names[$sort_mode];
    }
    function get_user_connect_log_sort_column($sort_mode)
    {
        $column_names = array('connect_date', 'u.last_name', 
            'u.login_id', 'remote_ip', 'i.assigned_name', 'i.amazon_instance_id', 'status_flag', 'browser_type', 'os');
        return $column_names[$sort_mode];
    }
    function get_website_event_log_sort_column($sort_mode)
    {
        $column_names = array('website_event_date', 'u.last_name', 
            'u.login_id', 'remote_ip', 'website_event_message');
        return $column_names[$sort_mode];
    }
    private function get_security_log_filter($event_id = false, $start_date = false, $end_date = false)
    {
        $filter = '';
        if ($start_date)
            $filter = $filter." l.security_log_date > ".$start_date." AND ";
        if ($end_date)
            $filter = $filter." l.security_log_date > ".$end_date." AND ";
        if ($event_id)
        {
            if (!is_array($event_id))
                $event_id = array($event_id);
            $filter = $filter." l.security_issue_type_id IN (". implode(',', $event_id).") AND ";
        }
        return $filter;
    }
    private function get_user_connect_log_filter($mode = false, $event_id = false, $start_date = false, $end_date = false)
    {
        $filter = '';
        if ($start_date)
            $filter = $filter." l.security_log_date > ".$start_date." AND ";
        if ($end_date)
            $filter = $filter." l.security_log_date > ".$end_date." AND ";
        if ($envent_id)
            $filter = $filter." i.security_issue_type_id = ".$event_id." AND ";
        if ($mode == "instance")
            $filter .= " l.instance_id IS NOT NULL AND ";
        if ($mode == "web")
            $filter .= " l.instance_id IS NULL AND browser_type is not null AND ";
        return $filter;
    }
    function generate_security_event($security_issue_type_id, $action_message, $user_id, $instance_id, $filename_transfer, $file_size)
    {
        $s = new SecurityLog();
        $s->security_issue_type_id = $security_issue_type_id;
        $s->action_message = $action_message;
        $s->user_id = $user_id;
        $s->instance_id = $instance_id;
        $s->filename_transfer = $filename_transfer;
        $s->file_size = $file_size;
        $s->remote_ip = $_SERVER['REMOTE_ADDR'];
        $s->security_log_date = gmdate('c');
        $s->save();
    }
    /*
    Gets currently used password policy.
    */
    function get_password_policy()
    {
        $config = SiteConfig::get();
        $policy = $config->strong_passwords_flag == 'Y' ? new StrongPasswordPolicy() : new LoosePasswordPolicy();
        return $policy;
    }
    function is_password_valid($user_id, $password_hash)
    {
    	$uph = new UserPasswordHistory();
    	return !$uph->load('user_id = ? and password_hash = ?', array($user_id, $password_hash));
    } 
    function generate_password($length = 8, $case = 'shuffle')
    {  
        $symbols = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 
            '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', 
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 
            'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');  
        $password = '';  
        for ($i = 1; $i < $length; $i++)  
        {    
            switch ($case)    
            {      
                case 'shuffle': 
                    $uppercase = rand(0, 1); 
                break;      
                case 'lower':   
                    $uppercase = 0;          
                break;      
                case 'upper':   
                    $uppercase = 1;          
                break;    
            }    
            switch ($uppercase)    
            {      
                case 0: $password = $password.$symbols[array_rand($symbols)];          
                break;
                case 1: $password = $password.ucfirst($symbols[array_rand($symbols)]); 
                break;
            }  
        }  
        return $password;
    }
}

?>