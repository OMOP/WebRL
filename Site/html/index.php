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

include('./common.php');
include('./diagnostics.php');

global $configurationManager;
global $db;
global $db_oracle;

$home_root = $_SERVER['DOCUMENT_ROOT'];
set_include_path(get_include_path().PATH_SEPARATOR.$home_root.'/libs');
set_include_path(get_include_path().PATH_SEPARATOR.$home_root.'/code');

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
initLogger();
init_db();
initSession();

// Setup legacy DbManager. 
DbManager::$db = $db;
require_once('OMOP/WebRL/dal.php');
set_exception_handler('exception_handler');

$router = new PageRouter();
$application = new Application($router);
$application->process();
