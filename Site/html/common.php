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
if (!defined("SITE"))
    die();

function __autoload($class_name) {
    $filename = strtolower($class_name) . '.php';
    $amazonfilepath = './libs/Amazon/EC2/Model/'.strtolower($class_name) . '.php';
    
    if (realpath($filename) == $filename) {
        require_once $filename;
        return;
    }
   
    // Otherwise, treat as relative path
    $paths = explode(PATH_SEPARATOR, get_include_path());

    foreach ($paths as $path) {
        if (substr($path, -1) == DIRECTORY_SEPARATOR) {
            $fullpath = $path.$filename;
        } else {
            $fullpath = $path.DIRECTORY_SEPARATOR.$filename;
        }

        if (file_exists($fullpath)) {
            require_once $filename;
            return;
        }
    }
    if (substr($class_name, 0, strlen('Amazon_EC2_Model_')) == 'Amazon_EC2_Model_')
    {
        require_once substr($class_name,strlen('Amazon_EC2_Model_')).'.php';
    }
    else
    {
       require_once $class_name.'.php';
    }
}

/**
 * Check if a file exists in the include path
 * @param string $filename
 *  Name of the file to look for
 * @return boolean
 *  True if file present in the include dir; false overwise.
 */
function file_present ($filename)
{
    // Check for absolute path
    if (realpath($filename) == $filename) {
        return true;
    }
   
    // Otherwise, treat as relative path
    $paths = explode(PS, get_include_path());
    foreach ($paths as $path) {
        if (substr($path, -1) == DS) {
            $fullpath = $path.$filename;
        } else {
            $fullpath = $path.DS.$filename;
        }
        if (file_exists($fullpath)) {
            return true;
        }
    }
    return false;
}

function exception_handler($exception) {

    $_SESSION['LAST_ERROR'] = $exception->getMessage();

    $exception_message = '['.date('D, d M Y H:i:s')."]:\n";
    $exception_message .= 'Http request: '.$_SERVER['REQUEST_METHOD'].$_SERVER['REQUEST_URI'].$_SERVER['SERVER_PROTOCOL']."\n";
    $exception_message .= 'Host: '.$_SERVER['HTTP_HOST']."\n";
    $exception_message .= 'Connection: '.$_SERVER['HTTP_CONNECTION']."\n";
    $exception_message .= 'User-Agent: '.$_SERVER['USER_AGENT']."\n";
    $exception_message .= 'Accept: '.$_SERVER['HTTP_ACCEPT']."\n";
    $exception_message .= 'Accept-Language: '.$_SERVER['HTTP_ACCEPT_LANGUAGE']."\n";
    $exception_message .= 'Referer: '.$_SERVER['HTTP_REFERER']."\n";
    $exception_message .= 'User IP: '.$_SERVER['REMOTE_ADDR']."\n";
    $exception_message .= "_REQUEST:\n";
    $exception_message .= print_r($_REQUEST, TRUE)."\n";
    $exception_message .= "Exception:\n";
    $exception_message .= $exception."\n";

    $user = Membership::get_current_user();
    
    $event = new WebSiteEvent();
    $event->website_event_date = gmdate('c');
    $event->remote_ip = $_SERVER['REMOTE_ADDR'];
    $event->website_event_message = $exception->getMessage();
    $event->website_event_description = $exception_message;
    $event->user_id = $user == null ? null : $user->user_id;
    $event->save();

    PageRouter::redirect('error_screen');
}
function obfuscate($email) {
    $i=0;
    $obfuscated="";
    while ($i<strlen($email)) {
       if (rand(0,2)) {
          $obfuscated.='%'.dechex(ord($email{$i}));
       } else {
          $obfuscated.=$email{$i};
       }
       $i++;
   }
return $obfuscated;
}

function obfuscate_numeric($plaintext) {
    $i=0;
    $obfuscated="";
    while ($i<strlen($plaintext)) {
       if (rand(0,2)) {
          $obfuscated.='&#'.ord($plaintext{$i}).';';
       } else {
          $obfuscated.=$plaintext{$i};
       }
       $i++;
   }
return $obfuscated;
}

function generateProtection($email,$label) {
           return sprintf("<a href='%s:%s'>%s</a>",
           obfuscate_numeric('mailto'),
           obfuscate($email),
           obfuscate_numeric($label));
}

function initConfiguration()
{
	global $configurationManager;
    
	$configurationFile = '/etc/webrl/webrl.conf';
	$configurationManager = new ConfigurationManager('/etc/webrl/webrl.conf');
	
	$webrlConfig = new Zend_Config_Ini($configurationFile, 'webrl'); 
    $dbConfig = new Zend_Config_Ini($configurationFile, 'db'); 
    $loggerConfig = new Zend_Config_Ini($configurationFile, 'logger');
    $pagerConfig = new Zend_Config_Ini($configurationFile, 'pager');
    
    Zend_Registry::set('webrl_config', $webrlConfig); 
    Zend_Registry::set('db_config', $dbConfig);
    Zend_Registry::set('loggerConfig', $loggerConfig);
    Zend_Registry::set('pagerConfig', $pagerConfig);
}

function getConfiguration()
{
    global $configurationManager;
    return $configurationManager;
}

function initSession()
{
	$config = array(
	    'name'           => 'session_tbl',
	    'primary'        => 'id',
	    'modifiedColumn' => 'modified',
	    'dataColumn'     => 'data',
	    'lifetimeColumn' => 'lifetime'
	);
	 
	$handler = new Zend_Session_SaveHandler_DbTable($config);
	Zend_Session::setSaveHandler($handler);
	
	Zend_Session::start();
}

function initLogger()
{
    $loggerConfig = Zend_Registry::get('loggerConfig');
    $type = $loggerConfig->type;
    $fullType = 'Zend_Log_Writer_'.$type;
    
    if ($type == 'Stream' && isset($loggerConfig->file))
        $writer = new $fullType($loggerConfig->file);
    else {
        $writer = new $fullType();
    }
    $logger = new Zend_Log($writer);
    Zend_Registry::set('logger', $logger);
}

function init_db()
{
	global $db;
	global $configurationManager;
	
	$dbConfiguration = new DbConfiguration($configurationManager);
	$db = NewADOConnection($dbConfiguration->connectionString());
	ADOdb_Active_Record::SetDatabaseAdapter($db, DbConfiguration::DB_DRIVER_MYSQL);
	
	$db_parameters = array(
		'host'           => $dbConfiguration->host(),
	    'username'       => $dbConfiguration->username(),
	    'password'       => $dbConfiguration->password(),
	    'dbname'         => $dbConfiguration->dbname()
	);
    $dbAdapter = Zend_Db::factory('Mysqli', $db_parameters);
    Zend_Db_Table::setDefaultAdapter($dbAdapter);
}

function init_db_oracle()
{
	global $db_oracle;
	global $configurationManager;

    if (DbManager::$db_oracle) {
        return;
    }
    putenv('ORACLE_HOME=' . getenv('ORACLE_HOME'));
	$dbConfiguration = new DbConfiguration($configurationManager);
    try {
        //$db_oracle = NewADOConnection($dbConfiguration->connectionString(DbConfiguration::DB_DRIVER_ORACLE));
        $db_oracle = NewADOConnection("oci8");
        $db_oracle->Connect(false,
            $dbConfiguration->oracle_username(),
            $dbConfiguration->oracle_password(),
            $dbConfiguration->oracle_tns());
    } catch (Exception $e) {
        //die($e->getMessage());
    }
    //var_dump($db_oracle, $dbConfiguration->connectionString(DbConfiguration::DB_DRIVER_ORACLE));die();
    //$db_oracle->debug = true;
    //ADOdb_Active_Record::SetDatabaseAdapter($db_oracle, DbConfiguration::DB_DRIVER_ORACLE);
    DbManager::$db_oracle = $db_oracle;
}
