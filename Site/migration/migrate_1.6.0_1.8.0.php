<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    5 November 2010
 
    MIgration procedure for 1.6 to 1.8 storage.
 
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

require_once('OMOP/WebRL/Configuration/ConfigurationManager.php');
require_once('OMOP/WebRL/Configuration/DbConfiguration.php');
require_once('OMOP/WebRL/Configuration/WebRLConfiguration.php');
require_once('OMOP/WebRL/Configuration/UserStorageConfiguration.php');
require_once('OMOP/WebRL/AmazonFactory.php');
require_once('Amazon/EC2/Model/CreateVolumeRequest.php');
require_once('Amazon/EC2/Model/AttachVolumeRequest.php');
require_once('Amazon/EC2/Model/DescribeVolumesRequest.php');

require_once('adodb/adodb.inc.php');					// ADODB
require_once('adodb/adodb-exceptions.inc.php');		// ADODB Exception handling
require_once('adodb/adodb-active-record.inc.php');	// ADODB Active Record
require_once('OMOP/WebRL/dal.php');

global $configurationManager;
global $db;

function init_db()
{
	global $db;
	global $configurationManager;
	
	$dbConfiguration = new DbConfiguration($configurationManager);
	$db = NewADOConnection($dbConfiguration->connectionString());
	ADOdb_Active_Record::SetDatabaseAdapter($db);
}

function initConfiguration()
{
	global $configurationManager;

	$configurationManager = new ConfigurationManager('/etc/webrl/webrl.conf');
}

function migrate_users_data()
{
	global $db;
	global $configurationManager;
	
	$userStorageConfiguration = new UserStorageConfiguration($configurationManager);
	$host = $userStorageConfiguration->storage_host();
	$availability_zone = $userStorageConfiguration->storage_availability_zone();
	$instance_id = $userStorageConfiguration->storage_instance_id();
	 
	$uc = new User();
	$users = $uc->find("active_flag='Y'");
	
	if ($connection = connect_to_instance($host) )
	{
		$client = AmazonFactory::getEC2Client($configurationManager);
		
		$device_letter = 'f';
		$device_number = 1;
		
		foreach($users as $u)
		{	
			if ($u->user_volume != null)
			{
				echo("[".gmdate('c')."] Volume was already assigned".PHP_EOL);
				continue;
			}
			
			if (!$u->user_ebs)
			{
				echo("[".gmdate('c')."] No personal storage found".PHP_EOL);
				continue;
			}
				
			$public_key = $u->certificate_public_key;
			$login_id = $u->login_id;
			$user_ebs = $u->user_ebs;
			
			$createVolumeRequest = new Amazon_EC2_Model_CreateVolumeRequest();
			$createVolumeRequest->withSnapshotId($user_ebs)
				->withAvailabilityZone($availability_zone);
			$createVolumeResponse = $client->createVolume($createVolumeRequest);
			
			$volume = $createVolumeResponse->getCreateVolumeResult()->getVolume()->getVolumeId();
			$volume_size = 20;
			$volume_device = '/dev/sd'.$device_letter.$device_number;
			$u->user_volume = $volume;
			$u->user_volume_size = $volume_size;
			$u->user_volume_device = $volume_device;
			echo("[".gmdate('c')."] Created volume {$volume} for user '{$login_id}'.".PHP_EOL);
			
			$attached = false;
			while(!$attached)
			{
				$describeVolumesRequest = new Amazon_EC2_Model_DescribeVolumesRequest();
				$describeVolumesRequest->withVolumeId($volume);
						
				try {
					$describeVolumesResponse = $client->describeVolumes($describeVolumesRequest);
					if (!$describeVolumesResponse->isSetDescribeVolumesResult())
					{
						sleep(1);
						continue;
					}
					$volumes = $describeVolumesResponse->getDescribeVolumesResult()->getVolume();
					$status = $volumes[0]->getStatus();
					if ($status != 'available')
					{
						sleep(1);
						continue;
					}
				}
				catch (Amazon_EC2_Exception $e)
				{
					sleep(1);
					continue;
				}				
				echo $status;
				
				$attachVolumeRequest = new Amazon_EC2_Model_AttachVolumeRequest();
				$attachVolumeRequest->withInstanceId($instance_id)
					->withDevice($volume_device)
					->withVolumeId($volume);
				try {
					$attachVolumeResponse = $client->attachVolume($attachVolumeRequest);
					if (!$attachVolumeResponse->isSetAttachVolumeResult())
					{
						sleep(1);
						continue;
					}
					$status = $attachVolumeResponse->getAttachVolumeResult()->getAttachment()->getStatus();
					if ($status != 'attaching' && $status != 'attached')
					{
						sleep(1);
						continue;
					}
					echo("[".gmdate('c')."] Launch attaching volume {$volume} to instance {$instance_id} for user '{$login_id}' as device '{$volume_device}'.".PHP_EOL);
					
					while(!$attached)
					{
						$describeVolumesRequest = new Amazon_EC2_Model_DescribeVolumesRequest();
						$describeVolumesRequest->withVolumeId($volume);
						
						try {
							$describeVolumesResponse = $client->describeVolumes($describeVolumesRequest);
							if (!$describeVolumesResponse->isSetDescribeVolumesResult())
							{
								sleep(1);
								continue;
							}
							$volumes = $describeVolumesResponse->getDescribeVolumesResult()->getVolume();
							$attachments = $volumes[0]->getAttachment();
							$status = $attachments[0]->getStatus();
							if ($status == 'attached')
							{
								echo("[".gmdate('c')."] Attaching volume {$volume} to instance {$instance_id} for user '{$login_id}' as device '{$volume_device}' completed.".PHP_EOL);
								$attached = true;
							}
						}
						catch (Amazon_EC2_Exception $e)
						{
							sleep(1);
						}
					}
				}
				catch (Amazon_EC2_Exception $e)
				{
					sleep(1);
				}
			}
			
			$script = 'mount '.$volume_device.' /var/storage/'.$login_id.'  ';
	    	
	    	echo("[".gmdate('c')."] ");
			if (! $stream = ssh2_exec($connection, $script))
	    	{
	    		throw new Exception("Could not migrate data for user '{$login_id}'");  
	    	}
			$script = 'chown '.$login_id.':'.$login_id.' /var/storage/'.$login_id.'  ';
	    	
	    	echo("[".gmdate('c')."] ");
			if (! $stream = ssh2_exec($connection, $script))
	    	{
	    		throw new Exception("Could set permission for user storage '{$login_id}'");  
	    	}
	    	echo("[".gmdate('c')."] Mounting device '{$volume_device}' completed.".PHP_EOL);
								
	    	if ($device_number < 6)
	    	{
	    		$device_number = $device_number+1;
	    	}    	
	    	else
	    	{
	    		if ($device_letter == 'z')
	    		{
	    			throw new Exception('No more devices could be attasched to the instance');
	    		}
	    		$device_letter++;
	    		$device_number = 1;
	    	}
	    	
	    	//$u->Save();
	    	
	    	echo "Data for user '{$login_id}' migrated".PHP_EOL;
		}
	}
} 

function create_users()
{
	global $db;
	global $configurationManager;
	
	$userStorageConfiguration = new UserStorageConfiguration($configurationManager);
	$host = $userStorageConfiguration->storage_host();
	
	$uc = new User();
	$users = $uc->find("active_flag='Y'");
	
	if ($connection = connect_to_instance($host) )
	{
		foreach($users as $u)
		{
			$public_key = $u->certificate_public_key;
			$login_id = $u->login_id;
	    	$script = '/bin/bash ~/scripts/add_user.sh -u '.$login_id.' -p "'.$public_key.' " ';
	    	
	    	echo("[".gmdate('c')."] ");
			if (! $stream = ssh2_exec($connection, $script))
	    	{
	    		throw new Exception("Could not create user '{$login_id}'");  
	    	}
	    	echo "User '{$login_id}' created".PHP_EOL;	    	
		}
	}
} 
function connect_to_instance($host)
{      
	if (!$connection = ssh2_connect($host, 22, array('hostkey'=>'ssh-rsa')))
		throw new Exception("Cannot connect to host ".$host);
    
	global $configurationManager;
	$configuration = new WebRLConfiguration($configurationManager);

	$instance_public_key = $configuration->instance_public_key();
    $instance_private_key = $configuration->instance_private_key();
    $instance_key_passphrase = $configuration->instance_key_passphrase();
        
    if (ssh2_auth_pubkey_file($connection, 'root', $instance_public_key, $instance_private_key, $instance_key_passphrase))
    	return $connection;
	else
		throw new Exception("Cannot connect to host ".$host." using public key ".$instance_public_key);
}

function exception_handler($exception) {

    $exception_message = '['.date('D, d M Y H:i:s')."]:\n";
    $exception_message .= "Exception:\n";
    $exception_message .= print_r($exception, TRUE)."\n";
    echo $exception_message.PHP_EOL; 
}
set_exception_handler('exception_handler');

echo("[".gmdate('c')."] ");
echo "Init Configuration".PHP_EOL;
initConfiguration();
echo("[".gmdate('c')."] ");
echo "Init database".PHP_EOL;
init_db();
echo "Create users".PHP_EOL;
create_users();
echo "Migrating data".PHP_EOL;
migrate_users_data();

?>
