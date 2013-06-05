<?php 
error_reporting( E_ALL | E_STRICT );
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
date_default_timezone_set('Europe/London');

define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

    define('LIBRARY_PATH', realpath(dirname(__FILE__) . '/../library'));
define('TESTS_PATH', realpath(dirname(__FILE__)));

$_SERVER['SERVER_NAME'] = 'http://localhost';

$includePaths = array(LIBRARY_PATH, get_include_path());
set_include_path(implode(PATH_SEPARATOR, $includePaths));

require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();

$loaderOptions = array(
            'namespace'=>'Application', 
            'basePath' => APPLICATION_PATH
        );
$autoLoader = new Zend_Application_Module_Autoloader($loaderOptions);

// Create application, bootstrap, and run
//$application = new Zend_Application(
//    APPLICATION_ENV,
//    APPLICATION_PATH . '/configs/application.ini'
//);
//$application->bootstrap();

//Zend_Session::$_unitTestEnabled = true;
//Zend_Session::start();
