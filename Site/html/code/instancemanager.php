<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Manage interaction with instances.
 
    (c)2009 Foundation for the National Institutes of Health (FNIH)
 
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
require_once('Amazon/EC2/Exception.php');
require_once('Amazon/EC2/Amazon_EC2_Client.php');
require_once('Amazon/EC2/Model/RunInstancesRequest.php');
require_once('Amazon/EC2/Model/BlockDeviceMapping.php');
require_once('OMOP/WebRL/Configuration/WebRLConfiguration.php');

class InstanceManager
{
    var $db;
    function __construct($db = false)
    {
        if (!$db)
            $db = DbManager::$db;
        $this->db = $db;
        self::update_pending_instances();
    }
    function get_user_instances($current_user, $organization_id, $user_id, $active_flag=false, $pagenum = false, $pagesize = false, $sort_mode = false, $sort_order = false)
    {
        if (!$pagenum)
            $pagenum = 1;
        if ($pagesize == false)
            $pagesize = 0;
        if (!$sort_mode)
            $sort_mode = 0;
        if (!$sort_order)
            $sort_order = 'ASC';
        $filter = $this->get_instance_active_filter($active_flag);

        $limit = $pagesize * $pagenum;
        $offset = $pagesize * ($pagenum - 1);
        
        if ($organization_id == 0)
        {
        	$organization_id = $current_user->organization_id;
        }
        $parameters = array();
        if ($user_id != 0)
        {
        	$parameters[] = $user_id;
        	if ($organization_id == 0)
        	{
        		$qry = "SELECT i.*, 
ROUND(s.instance_price * ( TIMESTAMPDIFF(SECOND, i.start_date, ifnull(i.terminate_date, now())) )/ 3600) as run_charge
FROM instance_tbl i
    JOIN instance_request_tbl ir on i.instance_request_id = ir.instance_request_id
    JOIN user_tbl u on ir.user_id = u.user_id
    JOIN software_type_tbl st ON ir.software_type_id = st.software_type_id
    JOIN instance_size_tbl s on ir.instance_size_id = s.instance_size_id
WHERE ".$filter." ir.user_id = ? ORDER BY " . self::get_sort_column($sort_mode) . " " . $sort_order;
            	
        	}
        	else
        	{
        		$qry = "SELECT i.*, 
ROUND(s.instance_price * ( TIMESTAMPDIFF(SECOND, i.start_date, ifnull(i.terminate_date, now())) )/ 3600) as run_charge
FROM instance_tbl i
    JOIN instance_request_tbl ir on i.instance_request_id = ir.instance_request_id
    JOIN user_tbl u on ir.user_id = u.user_id
    JOIN software_type_tbl st ON ir.software_type_id = st.software_type_id
    JOIN instance_size_tbl s on ir.instance_size_id = s.instance_size_id
WHERE ".$filter." ir.user_id = ? AND u.organization_id = ? ORDER BY " . self::get_sort_column($sort_mode) . " " . $sort_order;
				$parameters[] = $organization_id;
	            
        	}
        }
        else
        {
        	
        	if ($organization_id == 0)
        	{
	            $qry = "SELECT i.*, 
	    ROUND(s.instance_price * ( TIMESTAMPDIFF(SECOND, i.start_date, ifnull(i.terminate_date, now())) )/ 3600) as run_charge
	    FROM instance_tbl i
	        JOIN instance_request_tbl ir on i.instance_request_id = ir.instance_request_id
	        JOIN user_tbl u on ir.user_id = u.user_id
	        JOIN software_type_tbl st ON ir.software_type_id = st.software_type_id
	        JOIN instance_size_tbl s on ir.instance_size_id = s.instance_size_id
	    WHERE ".$filter." 1 = 1 ORDER BY " . self::get_sort_column($sort_mode) . " " . $sort_order;
        	}
        	else
        	{
		        $qry = "SELECT i.*, 
		    ROUND(s.instance_price * ( TIMESTAMPDIFF(SECOND, i.start_date, ifnull(i.terminate_date, now())) )/ 3600) as run_charge
		    FROM instance_tbl i
		        JOIN instance_request_tbl ir on i.instance_request_id = ir.instance_request_id
		        JOIN user_tbl u on ir.user_id = u.user_id
		        JOIN software_type_tbl st ON ir.software_type_id = st.software_type_id
		        JOIN instance_size_tbl s on ir.instance_size_id = s.instance_size_id
		    WHERE ".$filter." u.organization_id = ? ORDER BY " . self::get_sort_column($sort_mode) . " " . $sort_order;
        		$parameters[] = $organization_id;
        	}            
        }
        if (count($parameters) == 0)
        {
        	if ($pagesize == 0)
                $rs = $this->db->Execute($qry);
            else $rs = $this->db->SelectLimit($qry, $pagesize, $offset);
        }
        else
        {
	        if ($pagesize == 0)
            {
            	$rs = $this->db->Execute($qry, $parameters);
            }
            else 
            {
            	$rs = $this->db->SelectLimit($qry, $pagesize, $offset, $parameters);
            }
        }
        
        $rows = array();
        if ($rs) {
			while (!$rs->EOF) {
				$fields = $rs->fields;
                $count = count($rs->fields) / 2;
                
                unset($fields['run_charge']);
                unset($fields[$count-1]);
				$rows[] = $fields;
                $rs->MoveNext();
			}
		}
        $arr = array();
        foreach($rows as $row) 
        {
            $obj = new Instance();
            if ($obj->ErrorNo()){
                $db->_errorMsg = $obj->ErrorMsg();
                return false;
            }
            $obj->Set($row);
            $arr[] = $obj;
        } 
        return $arr;
    }
    function get_instance_active_filter($active_flag=false)
    {
        if ($active_flag == 'A')
            $filter = " i.status_flag IN ('A','P','I', 'X') AND ";
        if ($active_flag == 'N')
            $filter = " NOT i.status_flag IN ('A','P','I') AND ";

        if ($active_flag == 'R')
            $filter = " i.status_flag IN ('A', 'I', 'X') AND ";
        if ($active_flag == 'P')
            $filter = " i.status_flag IN ('P') AND ";
        if ($active_flag == 'S')
            $filter = " i.status_flag IN ('S') AND ";

        if ($active_flag == false)
            $filter = " 1 = 1 AND ";
        return $filter;
    }
    function get_user_instances_count($current_user, $organization_id, $user_id, $active_flag=false)
    {
        $filter = $this->get_instance_active_filter($active_flag);
        $parameters = array();
        if ($organization_id == 0)
        {
        	$organization_id = $current_user->organization_id;
        }
        if ($user_id != 0)
        {
        	$parameters[] = $user_id;
        	if ($organization_id == 0)
        	{
	            $qry = "
SELECT count(*) 
FROM instance_tbl i 
JOIN instance_request_tbl ir ON i.instance_request_id = ir.instance_request_id 
WHERE ".$filter." ir.user_id = ? ";
        	}
        	else
        	{
	            $qry = "
SELECT count(*) 
FROM instance_tbl i 
JOIN instance_request_tbl ir ON i.instance_request_id = ir.instance_request_id
JOIN user_tbl u ON u.user_id = ir.user_id
WHERE ".$filter." ir.user_id = ? AND u.organization_id = ?";
	            $parameters[] = $organization_id;
            }
	        
        }
        else
        {
        	if ($organization_id == 0)
        	{
            	$qry = "SELECT count(*) FROM instance_tbl i JOIN instance_request_tbl ir ON i.instance_request_id = ir.instance_request_id WHERE ".$filter." 1=1";
        	}
        	else
        	{
        		$qry = "SELECT count(*) 
        		FROM instance_tbl i 
        		JOIN instance_request_tbl ir ON i.instance_request_id = ir.instance_request_id
        		JOIN user_tbl u ON u.user_id = ir.user_id 
        		WHERE ".$filter." u.organization_id = ?";
        		$parameters[] = $organization_id;
        	}
        }
        if (count($parameters) == 0)
			return $this->db->getone($qry);       
        return $this->db->getone($qry, $parameters);
    }
    function get_sort_column($sort_mode)
    {
        $columns = array("i.instance_id","i.assigned_name","i.amazon_instance_id", 
            "u.login_id", "i.start_date","ir.instance_size_id","ir.dataset_type_id",
            "ir.software_type_id","i.terminate_date",
            "CASE i.status_flag WHEN 'A' THEN 'Running' WHEN 'S' THEN 'Terminated' WHEN 'F' THEN 'Failed' ELSE 'Unknown' END", 
            "run_charge", "i.public_dns");
        return $columns[$sort_mode];
    }
    function update_pending_instances()
    {
        $il = new Instance();
        $pending_instances = $il->find("status_flag = 'I'");
        foreach($pending_instances as $instance)
        {
            // Check that instance succesfully started.
            if ($instance->amazon_instance_id == null)
            {
                // Try start this instance again.
                $instance->terminate_date = gmdate('c');
                $instance->status_flag = "F";
                $instance->save();
                continue;
            }
        }
    }

    static function get_instance_hour_price(InstanceRequest $request)
    {
	    $instance_size = $request->instance_size;
        $instance_charge = 0;
        if ($instance_size != null)
        {
         	$instance_charge = $instance_size->instance_price;
         	$organization = $request->user->organization;
         	if ($organization != null)
         	{
         		$instance_charge = $instance_charge * (100 + $instance_charge->organization_admin_factor) / 100;
         	}
        }
        return $instance_charge;
    }
	static function get_storage_hour_price(InstanceRequest $request)
    {
    	$temp_size = 0;
        $temp_snapshot_entry = $request->temporary_ebs_entry;
        if ($temp_snapshot_entry != null)
        {
          	$temp_size = $temp_snapshot_entry->snapshot_entry_ebs_size;
        }
            
        $dataset_size = 0;
        $dataset_types = $request->dataset_types;
        foreach($dataset_types as $dt)
        {
        	$dataset_size += $dt->dataset_type_ebs_size;
        }        
        
        $storage_charge = ($temp_size + 20 + $dataset_size) * 0.1 / 730;
        $organization = $request->user->organization;
        if ($organization != null)
        {
         	$storage_charge = $storage_charge * (100 + $organization->organization_admin_factor) / 100;
        }
        return $storage_charge;
    }
    
    static function get_self_hostname()
    {
    	/*
    	 * Gets host name of Web RL instance. 
    	 */
    	exec('curl http://169.254.169.254/latest/meta-data/public-hostname', $host_ip);
        $host_ip = $host_ip[0];
        return $host_ip;
    }
    static function get_password($instance_request_dataset)
    {
    	require_once('OMOP/WebRL/MemcachePasswordManager.php');
    	global $configurationManager;
    	
    	$passwordManager = new MemcachePasswordManager($configurationManager);
    	return $passwordManager->get_password($instance_request_dataset);
    }
    function get_service()
    {
        require_once('OMOP/WebRL/AmazonFactory.php');
        global $configurationManager; 
        
    	return AmazonFactory::getEC2Client($configurationManager);
    }
    /**
     * Checks that user can launch specified amount of instances. 
     * @param User $user User for which check performed.
     * @param int $instances_count Count of instances that should be launched.
     */
    static function user_could_launch_instances(User $user, $instances_count)
    {
    	// Check user limit
    	$user_limit = $instances_count + $user->running_instances_count <= $user->num_instances;
    	if (!$user_limit)
    		return false;
    	if ($user->remains_limit <= 0)
    		return false;
    	// Dont try calculate organization restrictions since user 
    	if ($user->organization_id == 0)
    		return true;
    	return self::organization_could_launch_instances($user->organization, $instances_count);
    }
    /**
     * Checks that organization is capable launch following amount of instances.
     * @param Organization $organization Organization for which check performed.
     * @param int $instances_count Count of instances that should be launched. 
     */
    static function organization_could_launch_instances(Organization $organization, $instances_count)
    {
    	$organization_limit = $instances_count + $organization->running_instances_count <= $organization->organization_instances_limit;
    	if (!$organization_limit)
    		return false;
    	if ($organization->remains_limit <= 0)
    		return false;
    	return true; 
    }
    /**
     * Checks that user is has enough money to launch specified amount of instances. 
     * @param User $user User for which check performed.
     * @param InstanceRequest $request Instance request which define launched instances. 
     * @param int $instances_count Count of instances that should be launched.
     */
    static function check_user_budget(User $user, InstanceRequest $request, $instances_count)
    {
    	// Check how much this request requres money
    	$instance_charge = self::get_instance_hour_price($request);
    	$storage_charge = self::get_storage_hour_price($request);
    	$total_hour_charge = $instances_count * ($instance_charge + $storage_charge);
    	
    	if ($user->user_money < $total_hour_charge)
    		return false;

    	// Dont apply organization restrictions since user does not belongs to any organization. 
    	if ($user->organization_id == 0)
    		return true;
    		
    	return self::check_organization_budget($user->organization, $request, $instances_count);
    }
    /**
     * Checks that organization is has enough money to launch following amount of instances.
     * @param Organization $organization Organization for which check performed.
     * @param InstanceRequest $request Instance request which define launched instances. 
     * @param int $instances_count Count of instances that should be launched. 
     */
    static function check_organization_budget(Organization $organization, InstanceRequest $request, $instances_count)
    {
    	// Check how much this request requres money
    	$instance_charge = self::get_instance_hour_price($request);
    	$storage_charge = self::get_storage_hour_price($request);
    	$total_hour_charge = $instances_count * ($instance_charge + $storage_charge);
    	
    	if ($organization->organization_budget < $total_hour_charge)
    		return false;
    		
    	return true; 
    }
    static function launch_instances($image_ami, $size_name, $number_of_instances, $instance_names)
    {
    	global $configurationManager; 
    	$configuration = new WebRLConfiguration($configurationManager);
    	
        try
        {
            $service = self::get_service();
            $request = new Amazon_EC2_Model_RunInstancesRequest();
            $request->setImageId($image_ami);
            $request->setMinCount($number_of_instances);
            $request->setMaxCount($number_of_instances);
            $request->setInstanceType($size_name);
            $request->setKeyName($configuration->instance_launch_key());
            $request->setSecurityGroup(array($configuration->instance_launch_group()));
            $request->setInstanceInitiatedShutdownBehavior('terminate');
            $request->setUserData("TEST");
			/*
			Have to call this code when will add EBS support
            $mapping = self::get_block_mapping($size_name);
			
			if ($mapping)
			{
				$request->setBlockDeviceMapping($mapping);
			}*/	       
            
            $response = $service->runInstances($request);
            
            $result = $response->getRunInstancesResult()->getReservation()->getRunningInstance();
            return $result;
        }
        catch(Amazon_EC2_Exception $e)
        {
            self::handle_amazon_exception($e);
        }
        return "";
    }
    
    /**
     * Returns settings for ephemeral storage mappings for specific instance size
     * 
     * @param string $size_name Name of instance size in Amazon EC2 service.
     * @return NULL|Amazon_EC2_Model_BlockDeviceMapping|array(Amazon_EC2_Model_BlockDeviceMapping)
     */
    static function get_block_mapping($size_name)
    {
    	if ($size_name == 't1.micro')
    		return null;
    	if ($size_name == 'm1.small' || $size_name == 'c1.medium')
    	{
    		$mapping = array();
    		$mnt_mapping = new Amazon_EC2_Model_BlockDeviceMapping();
    		$mnt_mapping->withDeviceName('/dev/sda2')
    					->withVirtualName('ephemeral1'); 
    		/*$swap_mapping = new Amazon_EC2_Model_BlockDeviceMapping();
    		$swap_mapping->withDeviceName('/dev/sda3')
    					->withVirtualName('ephemeral2'); 
    		*/
    		$mapping[] = $mnt_mapping;
    		return $mapping;
    	}
    	//m1.large, m1.xlarge, c1.xlarge, cc1.4xlarge, m2.xlarge, m2.2xlarge, and m2.4xlarge 
    	if ($size_name == 'm1.large' || $size_name == 'm1.xlarge'
    	 || $size_name == 'c1.xlarge' || $size_name == 'cc1.4xlarge'
    	 || $size_name == 'm2.xlarge' || $size_name == 'm2.2xlarge'
    	 || $size_name == 'm2.4xlarge')
    	{
    		$mapping = array();
    		$sdb_mapping = new Amazon_EC2_Model_BlockDeviceMapping();
    		$sdb_mapping->withDeviceName('/dev/sdb')
    					->withVirtualName('ephemeral1'); 
    		$sdc_mapping = new Amazon_EC2_Model_BlockDeviceMapping();
    		$sdc_mapping->withDeviceName('/dev/sdc')
    					->withVirtualName('ephemeral2'); 
    		$mapping[] = $sdb_mapping;
    		$mapping[] = $sdc_mapping;

    		if ($size_name == 'c1.xlarge' || $size_name == 'm1.xlarge')
    		{
	    		$sdd_mapping = new Amazon_EC2_Model_BlockDeviceMapping();
	    		$sdd_mapping->withDeviceName('/dev/sdd')
	    					->withVirtualName('ephemeral3'); 
	    		$sde_mapping = new Amazon_EC2_Model_BlockDeviceMapping();
	    		$sde_mapping->withDeviceName('/dev/sde')
	    					->withVirtualName('ephemeral4');
    			$mapping[] = $sdd_mapping;
    			$mapping[] = $sde_mapping;
    		}
    		return $mapping;
    	}
    	throw new Exception('Unsupported type of instance. Type: '.$size_name);
    }
    static function terminate_instance($instance_id)
    {
        try
        {
            $service = self::get_service();
            $service->terminateInstances(array('InstanceId'=> $instance_id));
        }
        catch(Amazon_EC2_Exception $e)
        {
            self::handle_amazon_exception($e);
        }
    }
    
    static function pause_instance($instance_id)
    {
        try
        {
            $service = self::get_service();
            $service->stopInstances(array('InstanceId'=> $instance_id));
        }
        catch(Amazon_EC2_Exception $e)
        {
            self::handle_amazon_exception($e);
        }
    }

    static function resume_instance($instance_id)
    {
        try
        {
            $service = self::get_service();
            $service->startInstances(array('InstanceId'=> $instance_id));
        }
        catch(Amazon_EC2_Exception $e)
        {
            self::handle_amazon_exception($e);
        }
    }
    
	static function get_console_output ($instance_id)
    {
    	try
    	{
    		$service = self::get_service();
    		$response = $service->getConsoleOutput(array('InstanceId'=> $instance_id));
    		$res = '';
    		if ($response->isSetGetConsoleOutputResult()) {
    			$getConsoleOutputResult = $response->getGetConsoleOutputResult();
    			if ($getConsoleOutputResult->isSetConsoleOutput()) { 
    				$consoleOutput = $getConsoleOutputResult->getConsoleOutput();
    				if ($consoleOutput->isSetOutput())
    					$res = $consoleOutput->getOutput();
    			}
    		}
    		
    		if ($res)
    			$res = base64_decode($res);
    		
    		return $res;
    	}
    	catch(Amazon_EC2_Exception $e)
        {
           return $e;
        }
    }

    static function handle_amazon_exception(Amazon_EC2_Exception $e, $notsafe = false)
    {
        if ($e->getStatusCode() != -1)
        {
            if ($notsafe || $e->getErrorCode() != "InvalidInstanceID.NotFound")
            {
                // Rethrow exception;
                throw $e;
            }
            else
            {
                // Do nothing if we didn't find instance. 
            }
        }
        else
        {
            // DO nothing if we cannot connect to Amazon. This is means that we are in test env.
        }
    }
    
    static function get_instance_dns($instance_id)
    {
        try
        {
            $service = self::get_service();
            
            $response = $service->describeInstances(array('InstanceId' => $instance_id));
            
            $reservation = $response->getDescribeInstancesResult()->getReservation();
            
            if (count($reservation) == 0)
                throw new Amazon_EC2_Exception(array("Message"=>"Instance not launched"));
            $runningInstance = $reservation[0]->getRunningInstance();
            if (count($runningInstance) == 0)
                throw new Amazon_EC2_Exception(array("Message"=>"Instance not launched"));
            $dns = $runningInstance[0]->getPublicDnsName();
        }
        catch(Amazon_EC2_Exception $e)
        {
            self::handle_amazon_exception($e, true);
        }
        return $dns;
    }
    static function get_status($instance_id)
    {
        try
        {
            $service = self::get_service();
            
            $response = $service->describeInstances(array('InstanceId' => $instance_id));
            
            $reservation = $response->getDescribeInstancesResult()->getReservation();

            $runningInstance = $reservation[0]->getRunningInstance();
            $dns = $runningInstance[0]->getInstanceState()->getName();
        }
        catch(Amazon_EC2_Exception $e)
        {
            self::handle_amazon_exception($e);
        }
        return $dns;
    }
    static function is_instance_available($user_id, $name)
    {
	    $instances_count = DbManager::$db->getone('SELECT count(*) FROM instance_tbl i join instance_request_tbl ir on i.instance_request_id = ir.instance_request_id where i.assigned_name = ? and ir.user_id = ?', array($name, $user_id));
	    if ($instances_count == 0 || $instances_count == null)
	    {
		     return true;
	    }
	    return false;
  	}
    static function get_unique_name($user_id, $name, $start_new_chain = false)
    {  
	    if ($start_new_chain)
	    	return self::get_unique_name($user_id, $name.'_0');
	    	
    	$result = preg_match('/(\d+)$/', $name, $matches, PREG_OFFSET_CAPTURE);
    	if ($result == 0 || $result == false)
    	{
    		$subname = $name;
    		$available_index = 1;
    	}
    	else
    	{
    		$first_match = $matches[0];
    		$subname = substr($name, 0, $first_match[1]);
    		for($index = 1 + (int)$first_match[0]; 1 == 1; $index++)
			{
				$current_name = $subname.$index;
				$instances_count = DbManager::$db->getone('SELECT count(*) FROM instance_tbl i join instance_request_tbl ir on i.instance_request_id = ir.instance_request_id where i.assigned_name = ? and ir.user_id = ?', array($current_name, $user_id));
				if ($instances_count == 0)
					break;
			}
			$available_index = $index;
    	}
    	$available_name = $subname.$available_index;
    	return array('available_index' => $available_index,
    		'available_name' => $available_name);
    }

	public static function get_sys_instance_data($instance_dns) {

		$service = self::get_service();

        $response = $service->describeInstances(array());

        $reservations = $response->getDescribeInstancesResult()->getReservation();
		foreach ($reservations as $reservation) {
			$instances = $reservation->getRunningInstance();
			foreach ($instances as $instance) {
				$dns = $instance->getPublicDnsName();
				if ($dns == $instance_dns) {
					$key_pair = $instance->getKeyName();
					$instance_size = $instance->getInstanceType();
					$launch_date = strtotime($instance->getLaunchTime());
					return array('key_name' => $key_pair, 'instance_size' => $instance_size, 'launch_date' => $launch_date);
				}
			}
		}

		return false;

	}
}

?>