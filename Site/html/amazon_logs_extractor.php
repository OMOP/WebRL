<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Script gets Amazon Console Output for instances.
 
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
define("SITE", "OMOP Cloud Research Lab");

require_once('OMOP/WebRL/Configuration/ConfigurationManager.php');
require_once('OMOP/WebRL/Configuration/DbConfiguration.php');
require_once(dirname(__FILE__).'/code/instancemanager.php');

global $configurationManager;
global $db;

function init_db()
{
	require_once('adodb/adodb.inc.php');					// ADODB
	require_once('adodb/adodb-exceptions.inc.php');		// ADODB Exception handling
	require_once('adodb/adodb-active-record.inc.php');	// ADODB Active Record
	
	require_once('OMOP/WebRL/dal.php');
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

function get_tasks_list ()
{
	global $db;
	$sql = "SELECT `al`.`id`, `i`.`instance_id`, `i`.`status_flag`, `i`.`amazon_instance_id`
			FROM `instance_tbl` AS `i`
			LEFT JOIN `amazon_log` AS `al` ON `al`.`instance_id`=`i`.`instance_id`
			WHERE `i`.`start_date` + INTERVAL 1 WEEK > NOW() AND
				(`i`.`status_flag`<>`al`.`status_flag` OR `al`.`status_flag` IS NULL)";
	$res = $db->Execute($sql);
	if (!$res)
		throw new Exception($db->errorMsg(), $db->errorNo());
	return $res->GetAll();
}

function exception_handler($exception) {

    $exception_message = '['.date('D, d M Y H:i:s')."]:\n";
    $exception_message .= "Exception:\n";
    $exception_message .= print_r($exception, TRUE)."\n";
    echo $exception_message.PHP_EOL; 
}

set_exception_handler('exception_handler');

echo "Init Configuration".PHP_EOL;
initConfiguration();
echo "Init database".PHP_EOL;
init_db();
echo "Get list of instances".PHP_EOL;
$instances = get_tasks_list();
echo "Total ".count($instances)." instances to proceed".PHP_EOL;
$success_cnt = 0;
foreach ($instances as $ins)
{
	$log = InstanceManager::get_console_output($ins['amazon_instance_id']);
	if ( $log instanceof Exception )
	{
		echo "Failed to get console output for instance {$ins['instance_id']}[{$ins['amazon_instance_id']}]: ".$log->getMessage().PHP_EOL;
		continue;
	}
	
	$new_log = new AmazonLog();
	if (!is_null($ins['id']))
	{
		$new_log->load('id = ?', array('id'=>$ins['id']));
		echo "Updating log for instance {$ins['instance_id']}[{$ins['amazon_instance_id']}]".PHP_EOL;
	}
	$new_log->instance_id = $ins['instance_id'];
    $new_log->log_data = $log;
    $new_log->status_flag = $ins['status_flag'];
    $new_log->save();
    $success_cnt++;
}
echo "$success_cnt records saved".PHP_EOL;
?>