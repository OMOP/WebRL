<?php
$pid = pcntl_fork();
if ($pid) {
    exit(0);
}

/**
 * Making main process
 */
posix_setsid();

declare(ticks=1);

// Pid of process
(defined(PROCESS_PID)) || define("PROCESS_PID",getmypid());

/**
 * Function print information message
 */
function pushMessage ($message) {
    print "[".date("Y-m-d H:i:s")."] Pid: ".PROCESS_PID." ".$message.PHP_EOL;
}

/**
 * Function print information about Exception
 *  - errors chain
 *  - errros stack
 */
function pushError (Exception $exc) {
    /*$prevExc = $exc->getPrevious();
    if ($prevExc != null)
        pushError($prevExc);*/
    print "[".date("Y-m-d H:i:s")."] Pid: ".PROCESS_PID." Error {$exc->getCode()}: {$exc->getMessage()} in file {$exc->getFile()} on line {$exc->getLine()}";
    print " Error stack:".PHP_EOL;
    print $exc->getTraceAsString().PHP_EOL;
}
/**
 * Shutdown function output data from bufer in output stream
 */
function shutdownHandler (){
    /**
     * ВЫгружаем весь буфер в поток вывода
     */
    pushMessage("Finished");
    ob_end_flush();
    global $STDOUT, $STDERR;
    fclose($STDOUT);
    fclose($STDERR);
}
/**
 * Register shutdown function
 */
register_shutdown_function('shutdownHandler');
/**
 * Buffering the output stream. Next is session begins (WTF?)
  */
ob_start();
/**
 * Change standart streams source for demon
 */
$baseDir = dirname(__FILE__);
$logDir = $baseDir;
$logFile = "upload_result.".date("Y-M").".log";
ini_set('error_log',"{$logDir}/{$logFile}");
/**
* Closing the stream of output
*/
fclose(STDOUT);
/**
* Closing the stream of error output
*/
fclose(STDERR);
/**
 * Redeclaration the stream of output
*/
$STDOUT = fopen("{$logDir}/{$logFile}", 'ab');
/**
 * Redeclaration the stream of errors output
 */
$STDERR = fopen("{$logDir}/{$logFile}", 'ab');


pushMessage("Started");

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
$model = new Application_Model_RunResultsUploading();
$getopt = new Zend_Console_Getopt(
        array(
            'experiment|e=i' => 'Experiment ID',
            'dataset|d=i' => 'Dataset ID',
            's3|s' => 'Load to S3',
            'oracle|o' => 'Load to Oracle',
            'override' => 'Override results',
            'zip|z=s' => 'Unarchieved zip location',
            'user|u=i' => 'User ID',
            'method|m=s' =>  'Method type'
        )
    );

try {
    $getopt->parse();
} catch (Zend_Console_Getopt_Exception $e) {
    pushError($e);
    exit(1);
}

if (!(isset($getopt->e) || isset($getopt->d)) || (!isset($getopt->s) && !isset($getopt->o)) || !isset($getopt->z) || !isset($getopt->u)) {
    pushMessage("Error: {$getopt->getUsageMessage()}");
    exit(1);
}

$user = new Application_Model_User();
$user->find($getopt->u);
try {
$model->set_current_user_id($user->getId());

    $options = array(
            'override_results' => isset($getopt->override),
            'load_s3' => isset($getopt->s),
            'load_oracle' => isset($getopt->o),
            'method' => isset($getopt->method) ? $getopt->method : Application_Model_RunResultsUploading::OPTIONS_METHOD_DEFAULT,
        );
    foreach ($options as $option => $val) {
        $model->setOption($option, $val);
    }
    
    if (isset($getopt->e)) {
        $model->setExperimentData($getopt->e);
        if (!$model->getExperimentName())
            throw new Exception('Unknown experiment');
        $selectName = "Experement: ";
        $expTypes = $model->getExperimentTypes();
        if ((is_array($expTypes)) && isset($expTypes[$getopt->experiment]))
            $selectValue = $expTypes[$getopt->experiment];
        else
            $selectValue = "Unknown experiment";
    }
    elseif (isset($getopt->d)) {
        $model->setDatasetData($getopt->d);
        if (!$model->getDatasetName($getopt->d))
            throw new Exception('Unknown dataset');
        $selectName = "Dataset: ";
        $datasetName = $model->getDatasetName($getopt->dataset);
        if ($datasetName)
            $selectValue = $datasetName;
        else
            $selectValue = "Unknown dataset";
    }
    else {
        throw new Exception("Selectable property doesn't defined");
    }
    pushMessage("Upload results. User Id: {$getopt->u}.");
    $model->uploadResults($getopt->zip);
    
    /*if ($model->getExperimentName()) {
        pushMessage("Upload results. User Id: {$getopt->u}.");
        $model->uploadResults($getopt->zip);
    } else {
        throw new Exception('Unknown experiment');
    }*/
    pushMessage("Start sending email. User email: {$user->getEmail()}.");
    /**
     * Generate content parametrs for mail
     */
    $methodName = (strtolower($getopt->method) == 'method')?'Common Method':'OSCAR';
    $optOverrideResults = (isset($getopt->override)) ? '<span style="color:green">enabled</span>' : '<span style="color:red">disabled</span>';
    $optLoadS3 = (isset($getopt->s)) ? '<span style="color:green">enabled</span>' : '<span style="color:red">disabled</span>';
    $optLoadOracle = (isset($getopt->o)) ? '<span style="color:green">enabled</span>' : '<span style="color:red">disabled</span>';
    $body = <<<EOL
Dear {$user->getFirstName()} {$user->getLastName()},
<br /><br />
You submitted method results uploading with next param:<br />
<table border="0">
    <tr>
        <td><strong>Data type: </strong></td>
        <td>{$methodName}</td>
    </tr>
    <tr>
        <td><strong>{$selectName}</strong></td>
        <td>{$selectValue}</td>
    </tr>
    <tr>
        <td><strong>Load into S3: </strong></td>
        <td>{$optLoadS3}</td>
    </tr>
    <tr>
        <td><strong>Load into Oracle database: </strong></td>
        <td>{$optLoadOracle}</td>
    </tr>
    <tr>
        <td><strong>Overwrite existing results: </strong></td>
        <td>{$optOverrideResults}</td>
    </tr>
</table>
Here's the results of uploading:<br />
EOL
;
    $body .= '<p style="color:red">'.implode("<br/>", $model->getErrors()).'</p>';
    $body .= '<p style="color:green">'.implode("<br/>", $model->getMessages()).'</p>';
    $loadDataTree = $model->getLoadDataTree();
    if (($loadDataTree) && (is_array($loadDataTree)) && (count($loadDataTree)>0)) {
        $dataRender = new LoadDataRender($loadDataTree);
        //$dataRender->setHeader(array("Datasets","Methods","Runs","Source filenames"));
        $dataRender->setHeader($model->getLoadDataHeader());
        $dataRender->setTagStyles("table",array(
                                                      "border"=>"2px double black",
                                                      "border-collapse"=>"collapse",
                                                      "font-size" => "10pt",
                                                      "margin"=>"10px"
                                                      ));
        $dataRender->setTagStyles("td", array(
                                                   "display"=>"table-cell",
                                                   "border" =>"1px double black",
                                                   "vertical-align"=>"inherit"
                                                   ));
        $dataRender->setTagStyles("th", array(
                                                   "display"=>"table-cell",
                                                   "border" =>"1px double black",
                                                   "vertical-align"=>"inherit"
                                                   ));
        $body .= '<strong style="display:block;margin:10px">List of the loaded files</strong>';
        $body .= $dataRender->generateHtmlTable();
    }
    $body .= <<<EOL
<br />
This is an automatic email. Please do not reply.<br />
<br />
Best Regards,<br />
OMOP Support Team.    
EOL
;
    $mailAttachments = $model->getAttachments();
} catch (Exception $exc) {
    $exception_message = "Error during results uploading";
    $event = new WebSiteEvent();
    $event->website_event_date = gmdate('c');
    /*Comented for rollback to r3296*/$event->remote_ip = '127.0.0.1';
    /*Added for rollback to r3296*/ //$event->remote_ip = null;
    $event->website_event_message = $exc->getMessage();
    $event->website_event_description = $exception_message;
    $event->user_id = $user == null ? null : $user->getId();
    $event->remote_id = 'localhost';
    $event->save();
    pushError($exc);
    $body = "Uploading failed. Please contact administrator";
    
}
$mail = new MailManager($configurationManager);

$from = SiteConfig::get()->admin_email;

$subject = "Method Result Uploading";

$mail->send_mail_to_user($from, $user->getEmail(), $subject, $body,false,@$mailAttachments);

/**
 * Unlinks atachment files with upload Oracle
 */
if ((isset($mailAttachments)) && (is_array($mailAttachments)) && (count($mailAttachments) > 0) ) {
    foreach ($mailAttachments as $source)
        unlink ($source);
}

