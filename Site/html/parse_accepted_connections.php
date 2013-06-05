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

// FIXME: Implement logging of invalid calls.
// FIXME: Script should be moved to callback folder.

include('./common.php');
include('./diagnostics.php');

if (!isset($_POST['instance_id'])) die();

$instance_id = $_POST['instance_id'];

$instance = new Instance();
if (!$instance->load("amazon_instance_id = ?", array($instance_id)))
    $instance = null;

$uc = new UserConnectLog();
$log_entries = $uc->Find('instance_id = ?', array($instance_id));

$path = '/var/log/instances/'.$instance_id.'/accepted_connections.log';
$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES | FILE_TEXT);

foreach ($lines as $line_num => $line) {
	
	$command_params = array();
	ereg('Accepted publickey for (.*) from (...?\....?\....?\....?)',$line, $command_params);
	$user_name = $command_params[1];
	$remote_ip = $command_params[2];
		
	ereg('(... ..? ..:..:..) ',$line, $command_params);
	$date = $command_params[1];
	$date = strtotime($date);
	$gmnow = gmdate('c');
	$now = date('c');
	
	$date = gmdate('c', $date);
		
	$ucl = new UserConnectLog();
	$result = $ucl->load('instance_id = ? and TIMESTAMPDIFF(second, connect_date, ?) >= 0 and user_name = ? order by connect_date desc', array($instance->instance_id, $date, $user_name));
	if ($result)
	{
		$ucl->remote_ip = $remote_ip;
		try
		{
			$ucl->Save();
		}
		catch(Exception $e)
		{
			echo $e->getMessage();
		}
	}
}

?>