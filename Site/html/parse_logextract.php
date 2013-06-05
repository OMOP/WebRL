<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Main include file that contains generic startup logic and function in the 
    application.
 
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

define("SITE", "OMOP Cloud Research Lab");

include('./common.php');
include('./diagnostics.php');

if (!isset($_POST['instance_id'])) die();

$instance_id = $_POST['instance_id'];

$instance = new Instance();
if (!$instance->load("amazon_instance_id = ?", array($instance_id)))
    $instance = null;

$uc = new UserConnectLog();
$log_entries = $uc->Find('instance_id = ?', array($instance_id));

$path = '/var/log/instances/'.$instance_id.'/extract.log';
$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES | FILE_TEXT);

$remote_ips = array();

foreach ($lines as $line_num => $line) {
	//scp -r -p -d -f /root/.sync/test Copy test file to the target instance
	$pos = strpos($line, ' -r -p -d -f');
	if ($pos > 0)
	{
		$command_params = array();
		ereg("\[uid:(.*) sid:(.*) cwd:(.*)\]",$line, $command_params);
		$uid = $command_params[1];
		$sid = $command_params[2];
		$cwd = $command_params[3];

		ereg("-r -p -d -f (.*) ",$line, $command_params);
		$filename = $command_params[1];
		$path = $cwd.'/'.$filename;
	
		ereg("(... ..? ..:..:..) ",$line, $command_params);
		$date = $command_params[1];
		$date = strtotime($date);
		$date = gmdate('c', $date);
			
		if (!isset($remote_ips[$sid]))
		{
			$ucl = new UserConnectLog();
			$user_name = $uid == 0 ? 'root' : $instance->instance_request->user->login_id;
			$result = $ucl->load('instance_id = ? and TIMESTAMPDIFF(second, connect_date, ?) >= 0 and user_name = ? order by connect_date desc', array($instance->instance_id, $date, $user_name));
			if ($result)
			{
				$remote_ips[$sid] = $ucl->remote_ip;
			}
			else 
			{
				$remote_ips[$sid] = '0.0.0.0';
			}
		}
		$remote_id = $remote_ips[$sid];
		$user_id = $instance->instance_request->user->login_id;
				
		$sl = new SecurityLog();
		$sl->security_issue_type_id = 9;
		$action_message = 'Copy file '.$path.' from the instance';
		$sl->action_message = $action_message;
		$sl->remote_ip = $remote_id;
		$sl->user_id = $instance->instance_request->user_id;
		$sl->instance_id = $instance->instance_id;
		$sl->filename_transfer = $path;
		$sl->security_log_date = $date;//gmdate('c');
		$slold = new SecurityLog();
		if (!$slold->load('remote_ip = ? and action_message = ? and user_id = ? and instance_id = ? and filename_transfer = ? and TIMESTAMPDIFF(second,security_log_date,?) = 0', 
			array($remote_id, $action_message, $instance->instance_request->user_id, $instance->instance_id, $path, $date)))
		{
			$sl->Save();
		}
	}
}

?>