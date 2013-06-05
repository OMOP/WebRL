<?php
$pid = pcntl_fork();
if ($pid) {
    exit(0);
}
define("SITE", "OMOP Cloud Research Lab");

include(dirname(__FILE__).'/common.php');
include(dirname(__FILE__).'/diagnostics.php');

global $configurationManager;
global $db;
global $db_oracle;

$home_root = '/var/www/html';
set_include_path(get_include_path().PATH_SEPARATOR.$home_root.'/libs');
set_include_path(get_include_path().PATH_SEPARATOR.$home_root.'/code');

// Include libraries
require('adodb5/adodb.inc.php');					// ADODB
require('adodb5/adodb-exceptions.inc.php');		// ADODB Exception handling
require('adodb5/adodb-active-record.inc.php');		// ADODB Active Record

require_once('OMOP/WebRL/Configuration/ConfigurationManager.php');
require_once('OMOP/WebRL/Configuration/DbConfiguration.php');
require_once('OMOP/WebRL/MailManager.php');

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
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application/'));

defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap();
$model = new Application_Model_DatasetUploading();

$getopt = new Zend_Console_Getopt(
        array(
            'zip|z=s' => 'Zip location',
            'user|u=i' => 'User ID'
        )
    );

try {
    $getopt->parse();
} catch (Zend_Console_Getopt_Exception $e) {
    echo $e->getUsageMessage();
    exit(1);
}

if (!isset($getopt->e) || (!isset($getopt->s) && !isset($getopt->o)) || !isset($getopt->z) || !isset($getopt->u)) {
    echo $getopt->getUsageMessage();
    exit(1);
}
$user = new Application_Model_User();
$user->find($getopt->u);
try {
    $model->makeDatasetFromZip($getopt->z);
    $body = <<<EOL
Dear {$user->getFirstName()} {$user->getLastName()},
<br /><br />
You submitted dataset uploading.<br />
Here's the results:<br />
EOL
;
    $body .= '<p style="color:red">'.implode("<br/>", $model->getErrors()).'</p>';
    $body .= '<p style="color:green">'.implode("<br/>", $model->getMessages()).'</p>';
    $boyy .= <<<EOL
<br />
This is an automatic email. Please do not reply.<br />
<br />
Best Regards,<br />
OMOP Support Team.    
EOL
;
} catch (Exception $exc) {
    $exception_message = "Error during results uploading";
    $event = new WebSiteEvent();
    $event->website_event_date = gmdate('c');
    $event->remote_ip = '127.0.0.1';
    $event->website_event_message = $exc->getMessage();
    $event->website_event_description = $exception_message;
    $event->user_id = $user == null ? null : $user->getId();
    $event->remote_id = 'localhost';
    $event->save();
    $body = "Uploading failed. Please contact administrator";
    
}

$mail = new MailManager($configurationManager);

$from = SiteConfig::get()->admin_email;

$subject = "Method Result Uploading";

$mail->send_mail_to_user($from, $user->getEmail(), $subject, $body);
