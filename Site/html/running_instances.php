<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Main entry point for application.
 
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
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

include('./common.php');
include('./diagnostics.php');

global $configurationManager;
global $db;
global $db_oracle;

$home_root = $_SERVER['DOCUMENT_ROOT'];
set_include_path(get_include_path().PATH_SEPARATOR.$home_root.'/libs');
set_include_path(get_include_path().PATH_SEPARATOR.$home_root.'/code');
set_include_path(get_include_path().PATH_SEPARATOR.$home_root.'/../..');
set_include_path(get_include_path().PATH_SEPARATOR.$home_root.'/..');

// Include libraries
require('adodb5/adodb.inc.php');					// ADODB
require('adodb5/adodb-exceptions.inc.php');		// ADODB Exception handling
require('adodb5/adodb-active-record.inc.php');		// ADODB Active Record

require_once('OMOP/WebRL/Configuration/ConfigurationManager.php');
require_once('OMOP/WebRL/Configuration/DbConfiguration.php');

require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();

spl_autoload_register('__autoload');

initConfiguration();
init_db();
initSession();

// Setup legacy DbManager. 
DbManager::$db = $db;
require_once('OMOP/WebRL/dal.php');
set_exception_handler('exception_handler');
// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap();

if (!isset($_GET['id'])) die();

$internal_id = $_GET['id'];

/**
 * Get's list of running instances
 * 
 * @param string $internal_id Internal ID of user for which should be retreived list of running instances.
 */
function getRunningInstance($internal_id)
{
    $user = new User();
    if (preg_match('/^4cf917a05841c/', $internal_id)) {		//4cf917a05841 - system unique id
    	if (!$user->load("internal_id = ?", array(substr(strstr($internal_id, '-'), 1)))) die();
     	$mapper = new Application_Model_SystemInstanceMapper();
    	$running_instances_temp = $mapper->fetchAll();
    	$running_instances = array();
    	foreach ($running_instances_temp as $k=>$i) {
    		$running_instances[$k] = new StdClass();
    		$running_instances[$k]->assigned_name = $i->getName();
    		$running_instances[$k]->public_dns = $i->getHost();
            $certificate_path = $user->last_certificate_path;
            if (preg_match('/\.ppk/', $certificate_path))
                $certificate_path = substr($certificate_path, 0, strpos($certificate_path, '.ppk'));
    		$running_instances[$k]->keyName = $certificate_path;
    	}
    } else {
    	if (!$user->load("internal_id = ?", array($internal_id))) die();
    	$current_user = Membership::get_current_user();
    
    	$manager = new InstanceManager(DbManager::$db);
    	if ($user->admin_flag == 'Y')
    		$running_instances = $manager->get_user_instances($current_user, 0, 0, 'A', false, 0);
    	else
    		$running_instances = $manager->get_user_instances($current_user, 0, $user->user_id, 'A', false, 0);
    }
    return $running_instances;
}

/**
 * Write XML with definition of running instances. 
 * 
 * @param array $running_instances Array of instance description.
 * @param string $uri URI to which should be written XML list.
 */
function writeInstancesXml($running_instances, $uri)
{
    global $internal_id;
    $out =new XMLWriter();
    $out->openURI($uri);
    $out->setIndent(true);
    $out->startDocument();
    $out->startElement("running_instances");
    foreach($running_instances as $i) {
        $out->startElement("instance");
            $out->writeElement("name", $i->assigned_name);
            $out->writeElement("dns", $i->public_dns);
            $hasRequest = $i->instance_request != null;
    
            $os_family = $hasRequest ? $i->instance_request->instance_size->os_family : 'linux';
            $out->writeElement("os_family", $os_family);
            if ($os_family == 'linux') {
                $out->writeElement("user", $hasRequest ? $i->instance_request->user->login_id : 'root');
            } else {
                $out->writeElement("user", 'omop');
            }
            $out->writeElement("keyName", $hasRequest ? $i->instance_request->user->login_id : $i->keyName);
            $out->writeElement("token", $hasRequest ? $i->instance_request->user->internal_id : $internal_id);
        $out->endElement();
    }
    $out->endElement();
    $out->endDocument();
}

$running_instances = getRunningInstance($internal_id);
writeInstancesXml($running_instances, 'php://output');
