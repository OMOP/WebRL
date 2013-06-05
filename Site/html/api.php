<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    AJAX API of web-site.
 
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

include('./diagnostics.php');
include('./common.php');

global $configurationManager;
global $db;
global $db_oracle;

$home_root = $_SERVER['DOCUMENT_ROOT'];
set_include_path(get_include_path().PATH_SEPARATOR.$home_root.'/libs');
set_include_path(get_include_path().PATH_SEPARATOR.$home_root.'/code');
set_include_path(get_include_path().PATH_SEPARATOR.$home_root.'/../..');

// Include libraries
require('adodb5/adodb.inc.php');					// ADODB
require('adodb5/adodb-exceptions.inc.php');		// ADODB Exception handling
require('adodb5/adodb-active-record.inc.php');		// ADODB Active Record

require_once('OMOP/WebRL/Configuration/ConfigurationManager.php');
require_once('OMOP/WebRL/Configuration/DbConfiguration.php');
require_once('OMOP/WebRL/Configuration/SvnConfiguration.php');
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



global $registered_functions;
$registered_functions = array();
try
{
    init_registered_functions();
    process_request();
}
catch(Exception $ex)
{
    $debug = true;
    echo json_encode(array('status' => 'error', 'message' => 
    	$debug ? $ex->getmessage() : 'Invalid API call'
    ));
    die();
}

/**
 * Registers AJAX functions. 
 */
function init_registered_functions()
{
    register_function('check_email', 'check_email');
    register_function('check_login', 'check_login');
    register_function('check_dataset_password', 'check_dataset_password');
    register_function('check_instance_name', 'check_instance_name');
    register_function('get_instance', 'get_instance');
    register_function('get_method_parameters', 'get_method_parameters');
    register_function('get_organization_users', 'get_organization_users');
    register_function('get_results_run_list', 'get_results_run_list');
    register_function('get_oracle_run_details', 'get_oracle_run_details');
    register_function('save_key_path', 'save_key_path');
    register_function('check_svn_folder', 'check_svn_folder');
    register_function('check_org_name', 'check_org_name');
}

/**
 * Generic handler for all AJAX API calls.
 * Each API call has following signature. /api.php?method=method_name + POST data. 
 * Post data contains parameters to methods.
 */
function process_request()
{
    global $registered_functions;
    if (!isset($_GET['method']))
    {
        throw new Exception("Method not specified");
    }
    $method = $_GET['method'];
    if (!isset($registered_functions[$method]))
    {
        throw new Exception("Invalid attempt to call unregistered method");
    }
    $handler = $registered_functions[$method];
    $handler($method, $_POST);
}

function register_function($name, $callback)
{
    global $registered_functions;
    if (isset($registered_functions[$name]))
        throw new Exception("Method $name already registered");
    $registered_functions[$name] = $callback;
}

function check_email($name, $parameters)
{
    if (!isset($parameters['email']))
    {
        echo json_encode("error");
        return;
    }
    $email = $parameters['email'];
    if (!isset($parameters['user_id']))
    {
        $emails_count = DbManager::$db->getone('SELECT count(*) FROM user_tbl where email = ?', array($email));
    }
    else
    {
        $user_id = $parameters['user_id'];
        $emails_count = DbManager::$db->getone('SELECT count(*) FROM user_tbl where email = ? and user_id <> ?', array($email, $user_id));
    }
    if ($emails_count == 0)
    {
        echo json_encode("true");
    }
    else 
    {
        echo json_encode("Duplicate emails are not allowed");
    }
}


function check_login($name, $parameters)
{
    if (!isset($parameters['login']))
    {
        echo json_encode("error");
        return;
    }
    $login = $parameters['login'];
    $logins_count = -1;
    if (!isset($parameters['user_id']))
    {
        $logins_count = DbManager::$db->getone('SELECT count(*) FROM user_tbl where login_id = ?', array($login));
    }
    else
    {
        $user_id = $parameters['user_id'];
        $logins_count = DbManager::$db->getone('SELECT count(*) FROM user_tbl where login_id = ? and user_id <> ?', array($login, $user_id));
        
    }
    if ($logins_count == 0)
    {
        echo json_encode("true");
    }
    else 
    {
        echo json_encode("Duplicate logins are not allowed");
    }
}

function check_instance_name($name, $parameters)
{
    if (!isset($parameters['instance_name']))
    {
        echo json_encode("error");
        return;
    }
    if (!isset($parameters['user_id']))
    {
        echo json_encode("error");
        return;
    }
    $name = $parameters['instance_name'];
    $user_id = $parameters['user_id'];
    if (InstanceManager::is_instance_available($user_id, $name))
    {
    	echo json_encode("true"); 
    }
    else
    {
    	$result = InstanceManager::get_unique_name($user_id, $name); 
    	echo json_encode($result);
    }
}

function check_system_instance_name($name, $parameters)
{
    if (!isset($parameters['instance_name']))
    {
        echo json_encode("error");
        return;
    }
    if (!isset($parameters['instance_id']))
    {
        echo json_encode("error");
        return;
    }
	$name = $parameters['instance_name'];
	$instance_id = $parameters['instance_id'];
    if (SysInstanceManager::is_system_instance_available($name, $instance_id))
    {
    	echo json_encode("true");
    }
    else
    {
    	echo json_encode("false");
    }
}

function check_system_instance_unique_host($name, $parameters)
{
    if (!isset($parameters['instance_host']))
    {
        echo json_encode("error");
        return;
    }
    if (!isset($parameters['instance_id']))
    {
        echo json_encode("error");
        return;
    }
	$host = $parameters['instance_host'];
	$instance_id = $parameters['instance_id'];
    if (SysInstanceManager::is_system_instance_available($host, $instance_id, 'host'))
    {
    	echo json_encode("true");
    }
    else
    {
    	echo json_encode("false");
    }
}

function check_system_instance_host($name, $parameters)
{
    if (!isset($parameters['instance_host']))
    {
        echo json_encode("error");
        return;
    }
	$host = $parameters['instance_host'];
	if (InstanceManager::get_sys_instance_data($host))
		echo json_encode("true");
	else
		echo json_encode("false");
}

function get_instance($name, $parameters)
{
    if (!Membership::is_logged_in())
    {
        echo json_encode("error");
        return;
    }
    if (!isset($parameters['instance_id']))
    {
        echo json_encode("error");
        return;
    }
    $instance_id = $parameters['instance_id'];
    $im = new InstanceManager(DbManager::$db);

    $il = new Instance();
    if (!$il->load("instance_id = ?", array($instance_id)))
    {
        echo json_encode("error");
        return;
    }
    echo json_encode(array(
        'token' => $il->instance_request->user->internal_id,
        'instance_id' => $il->instance_id,
        'instance_request_id' => $il->instance_request_id,
        'assigned_name' => $il->assigned_name,
        'amazon_instance_id' => $il->amazon_instance_id,
        'public_dns' => $il->public_dns,
        'status_flag' => $il->status_flag

    ));
}

function get_method_parameters($name, $parameters)
{
	require_once('OMOP/WebRL/MethodManager.php');
    global $configurationManager;
    
	if (!isset($parameters['method']))
    {
        echo json_encode("error");
        return;
    }
    $method = $parameters['method'];
	
    $method_manager = new MethodManager($configurationManager);
    $parameters = $method_manager->get_method_parameters($method);
    echo json_encode($parameters);
}

function check_dataset_password($name, $parameters)
{
	//require_once('OMOP/WebRL/MethodManager.php');
    global $configurationManager;
    
	if (!isset($parameters['dataset']))
    {
        echo json_encode("error");
        return;
    }
	if (!isset($parameters['password']))
    {
        echo json_encode("error");
        return;
    }
    $dataset = $parameters['dataset'];
    $password = $parameters['password'];
	
    $dataset_type = new DatasetType();
	$dataset_type->load('dataset_type_id = ?', $dataset);
	
	if ($dataset_type->password_hash != $password)
	//if ($dataset_type->password_hash != md5($password))
    {
    	echo json_encode("Entered password didn't match with values saved in the DB.");
    	die();
    }
    echo json_encode("true");
}

function get_organization_users($name, $parameters)
{
    if (!isset($parameters['organization_id']))
    {
        echo json_encode("error");
        return;
    }
    $organization_id = $parameters['organization_id'];

    $umanager = new UserManager(DbManager::$db);
    $users = $umanager->get_all_users($organization_id);
    $data = array();
    foreach($users as $u)
    {
    	$data[] = array('user_id' => $u->user_id, 'login_id' => $u->login_id, 'last_name' => $u->last_name, 'first_name' => $u->first_name); 
    }
    echo json_encode($data);
}

/**
 * Get the list of runs for Oracle results page
 * @param <type> $name
 * @param array parameter list
 * @return string
 */
function get_results_run_list($name, $parameters)
{
    /**
     * @todo security check be here. user should have permissions to access this method/dataset
     */
    init_db_oracle();

    $result = array();
    $result['error'] = '0';
    $result['data'] = array();

    $db_oracle = DbManager::$db_oracle;

    $query = "
select METHOD_ID
from METHOD_REF
where METHOD_ABBR = '" . str_replace("'", "\'", $parameters['method']) . "'";
    /**
     * @todo check why adodb does not allow variables here (prepare statements)
     */
    try {
        $method_id = $db_oracle->GetOne($query);
    } catch (Exception $e) {
    }
    if (!$method_id) {
        $result['error'] = '1';
        die(json_encode($result));
    }

	$query = "
select SOURCE_ID
from SOURCE_REF
where SOURCE_ABBR = '" . str_replace("'", "\'", $parameters['dataset']) . "'";
    /**
     * @todo check why adodb does not allow variables here (prepare statements)
     */
    try {
        $source_id = $db_oracle->GetOne($query);
    } catch (Exception $e) {
    }
    if (!$method_id) {
        $result['error'] = '1';
        die(json_encode($result));
    }
//WARNING! Check functional, Group by statement was run_name, run_id. These fields are removed from table.
//Check the sorting order. It was by Run_name.
    $query = '
SELECT  
        COUNT(*)                                AS  TOTAL_AMOUNT,
        NVL(TO_CHAR(MIN(S.START_DATE)), \'-\')  AS  START_TIME,
        NVL(MAX(S.run_duration_in_min), -1)     AS  DURATION,
        (SELECT count(*) 
        FROM METHOD_RESULTS_SUMMARY S
        WHERE   S.ANALYSIS_ID IN (SELECT A1.ANALYSIS_ID 
                                    FROM ANALYSIS_REF A1 
                                    WHERE A1.ANALYSIS_ID = A.ANALYSIS_ID)) AS LOADED_AMOUNT
FROM    ANALYSIS_REF A
    left join   METHOD_RESULTS_SUMMARY S
            on  S.ANALYSIS_ID = A.ANALYSIS_ID
WHERE   A.METHOD_ID = ' . $method_id . '
GROUP BY analysis_id';

	$query = '
SELECT  
        A.ANALYSIS_ID,
		A.RUN_NAME,
        count(OUTPUT_FILE_NAME) 	AS TOTAL_AMOUNT,
		count(MR.ANALYSIS_ID)                   AS LOADED_AMOUNT,
		NVL(TO_CHAR(max(start_date)), \' \')	AS START_TIME, 
		NVL(max(run_duration_in_min), -1)		AS DURATION_MIN
FROM	ANALYSIS_REF					A  
    LEFT JOIN   METHOD_RESULTS_SUMMARY  MRS  
		ON      A.ANALYSIS_ID	= MRS.ANALYSIS_ID 
            AND MRS.source_id	=' . $source_id . '
            AND MRS.method_id   =' . $method_id . '
	LEFT JOIN  METHOD_RESULTS          MR
        ON      A.ANALYSIS_ID   = MR.ANALYSIS_ID
	        AND MR.source_id    =' . $source_id . '
	        AND MR.method_id    =' . $method_id . ' 
WHERE	A.method_id	= ' . $method_id . '
GROUP BY	A.RUN_NAME, 
            A.ANALYSIS_ID
ORDER BY 1
';

    $rs = $db_oracle->Execute($query);
    if (!$rs) {
        $result['error'] = '1';
        return $result;
    }
    $runs = array();
    while ($row = $rs->FetchRow()) {
        if (-1 != $row['DURATION'] && $row['DURATION'] != 0) {
            $parts = explode(' ', $row['DURATION']);
            
            $row['DURATION'] = intval(substr($parts[0], 1)) . ':' . substr($parts[1], 0, strpos($parts[1], '.'));
        } else {
            $row['DURATION'] = '';
        }
        $runName = $row['RUN_NAME'];
        $runBaseName = preg_replace('/(.*)_(\d+?)$/', '$1', $runName);
        $runNameNumber = preg_replace('/(.*)_(\d+?)$/', '$2', $runName);
        $runName = sprintf('%s_%03d', $runBaseName, $runNameNumber); 
        $runs[$runName] = $row;
    }
    ksort($runs);
    $sorted_data = array();
    foreach($runs as $k => $item){
        $sorted_data[] = $item;   
    }
    $result['data'] = $sorted_data;
    echo json_encode($result);
}

/**
 * Get the details of specific Run Result stored in Oracle
 * @param string $name
 * @param array $parameters
 * @return <type>
 */
function get_oracle_run_details($name, $parameters)
{
    /**
     * @todo security check be here. user should have permissions to access this method/dataset
     */
    init_db_oracle();

    /**
     * Parameter structure:
     * ['method'] => method name
     * ['dataset'] => dataset name
     * ['run_name'] => run name
     */
    $result = array();
    $result['error'] = '0';
    $result['data'] = array();

    $db_oracle = DbManager::$db_oracle;

$query = "
select METHOD_ID
from METHOD_REF
where METHOD_ABBR = '" . str_replace("'", "\'", $parameters['method']) . "'";
    /**
     * @todo check why adodb does not allow variables here (prepare statements)
     */
    try {
        $method_id = $db_oracle->GetOne($query);
    } catch (Exception $e) {
    }
    if (!$method_id) {
        $result['error'] = '1';
        die(json_encode($result));
    }

    $query = "
select SOURCE_ID
from SOURCE_REF
where SOURCE_ABBR = '" . str_replace("'", "\'", $parameters['dataset']) . "'";
    /**
     * @todo check why adodb does not allow variables here (prepare statements)
     */
    try {
        $source_id = $db_oracle->GetOne($query);
    } catch (Exception $e) {
    }
    if (!$method_id) {
        $result['error'] = '1';
        die(json_encode($result));
    }
    
    
    foreach (array('method','dataset','run_name') as $param) {
        $parameters[$param] = str_replace("'", "\'", $parameters[$param]);
    }
//Experiment_id has test value
    $query = '
SELECT
        A.ANALYSIS_ID,
        A.OUTPUT_FILE_NAME, 
        MRS.RECORDS_NUMBER                          AS  AMOUNT,
        NVL(MRS.RECORDS_NUMBER, -1)                 AS  TOTAL_AMOUNT,
        NVL(TO_CHAR(MRS.DB_LOAD_DATE), \'-\')  AS  ADD_DATE
from    "ANALYSIS_REF" "A"
    left join   "EXPERIMENT_RESULTS_SUMMARY" "MRS" 
            ON  MRS.ANALYSIS_ID = A.ANALYSIS_ID
            AND MRS.source_id   =' . $source_id . '
            AND A.method_id   =' . $method_id . '
            AND MRS.EXPERIMENT_ID = 1
';


    $result['debug'] = $query;
    $rs = $db_oracle->Execute($query);
    if (!$rs) {
        $result['error'] = '1';
        echo json_encode($result);
        return $result;
    }
    while ($row = $rs->FetchRow()) {
        $row['OUTPUT_FILE_NAME'] = str_replace('_NAME', '_'.$parameters['dataset'], $row['OUTPUT_FILE_NAME']);
        $row['AMOUNT_FORMATTED'] = number_format($row['AMOUNT']);
        $result['data'][] = $row;
    }
    echo json_encode($result);
}

function save_key_path($name, $parameters) {
    if (! isset($parameters['path']))
        return;
    $user = Membership::get_current_user();
    $user->last_certificate_path = $parameters['path'];
    $user->save();
}

function check_svn_folder($name, $parameters) {
    global $configurationManager;
    if (!isset($parameters['svnFolder']))
        die(json_encode("true"));
    $svnFolder = $parameters['svnFolder'];
    $svnConfig = new SvnConfiguration($configurationManager);
    $folderUri = "file://".$svnConfig->repository_path().$svnFolder;
    $cmd = "/usr/bin/svn info ".$folderUri;
    exec($cmd, $output, $exitCode);
    if ($exitCode > 0) {
        die(json_encode("false"));        
    }
    die(json_encode("true"));        

    
}

function check_org_name($name, $parameters) {
    if (!isset($parameters['name']) || !isset($parameters['orgId']))
        die(json_encode("false"));
    $o = new Organization();
    $o->load("organization_name = ? AND organization_id <> ? AND organization_deleted_date IS NULL", array($parameters['name'], $parameters['orgId']));
    
    if ($o->organization_id) {
        die(json_encode("false"));
    }
    die(json_encode("true"));
    
    
}
?>
