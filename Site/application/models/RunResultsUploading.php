<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    23 December 2010

    Model of run result uploading.

    (c)2009-2011 Foundation for the National Institutes of Health (FNIH)

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
require_once('OMOP/WebRL/Configuration/WebRLConfiguration.php');
class Application_Model_RunResultsUploading extends Zend_Db_Table
{
    const OPTIONS_METHOD_DEFAULT = 'method';
    const OPTIONS_METHOD_DOI     = 'doi';
    const OPTIONS_METHOD_HOI     = 'hoi';
    const OPTIONS_METHOD_OSCAR   = 'oscar';
    
    const S3_OSCARRESULTS_FOLDERNAME = "OSCAR";
    /**
     * @property Array $_loadDataHeadersList - array wich contains list of headers for differrent method options
     */
    static private $_loadDataHeadersList = array(
                                            self::OPTIONS_METHOD_DEFAULT => array("Datasets","Methods","Runs","Source filenames"),
                                            self::OPTIONS_METHOD_OSCAR => array("Datasets","Source filenames"),
                                           );
    
    private $_debug = false;
    private $_attachments;

    private $_options = array(
        'override_results' => false,
        'load_s3' => true,
        'load_oracle' => true,
        'method' => '',
    );

    /**
     * List of messages that should be displayed to user
     * @var array messages list
     */
    private $_messages = array();
    private $_errors = array();

    /**
     * @var integer ID of dataset being uploaded
     */
    private $_datasetId = null;
    private $_datasetTypes = array();
    private $_datasetData = array();
    
    /**
     * @var integer ID of experiment being uploaded
     */
    private $_experimentId;
    private $_experimentTypes = array();
    private $_experimentData = array();

    private $_logs = array();
    
    const MAX_LOG_RECORD_LENGTH = 4096;

    private $_s3;
    private $_s3StorageBucket;
    private $_s3StoragePath;
    private $_sourceModel;
    private $_experimentModel;

    private $_uploadedResultsSummary = array();

    private $_isAutoUploading = true;

    private $_runsLoadedToOracle = array();

    private $_logger;
    private $_warnings;
    private $_oracle_error_cause;
    private $_dataset_access_error_type;
    private $_current_user_id;
    private $_dataset;
    private $_methods;
    /**
     *
     * @property Application_Model_Method $_currentMethodModel model of current methid wich hendling
     */
    private $_currentMethodModel = null;
    /**
     * @property Array $_loadDataTree - multidimensional array wich contains inforamtion about
     *                                  datasets, methods in datasets and runs in methods
     */
    private $_loadDataTree = array();
    
    public $_loaded_s3 = false;
    public $_loaded_oracle = false;
    public $_skip_oracle = false;
    public $_skip_s3 = false;
    
    /**
     * Class constructor
     * @param array $config
     * @param unknown_type $definition 
     */
    public function  __construct($config = array(), $definition = null)
    {
        $this->_sourceModel = new Application_Model_DbTable_Source();
        $sources = $this->_sourceModel->getSources();
        foreach ($sources as $source) {
            $this->_datasetTypes[$source['SOURCE_ID']] = $source['SOURCE_ABBR'];
        }
        
        $this->_experimentModel = new Application_Model_DbTable_Experiment();
        
        $experiments = $this->_experimentModel->getExperiments();
        foreach ($experiments as $experiment) {
            $this->_experimentTypes[$experiment['EXPERIMENT_ID']] = $experiment['EXPERIMENT_NAME'];
        }

        if (Zend_Registry::isRegistered('logger')) {
            $this->_logger = Zend_Registry::get('logger');
        } else {
            $this->_logger = new Zend_Log(new Zend_Log_Writer_Null());
        }
        
        parent::__construct($config, $definition);
    }
    
    public function getAttachments() {
        return $this->_attachments;
    }

    
    public function getDataset() {
        return $this->_dataset ? $this->_dataset : '';
        //return $this->_dataset;
    }
    
    public function getDatasetIdByAbbr ($datasetId) {
        if ($datasetId) {
            $datasetInfo= $this->_sourceModel->getSourceByName($datasetId);
            return (!empty($datasetInfo) && isset($datasetInfo["SOURCE_ID"])) ? $datasetInfo["SOURCE_ID"] : null;
        }
        return null;
    }
    
    public function setDataset($dataset) {
        $this->_dataset = $dataset;
        /**
         * Add dataset in $_loadDataTree array
         */
        $this->_loadDataTree[$dataset]=array();
    }

    /**
     * Get the auto uploading flag
     * @return boolean flag: whether the data uploaded automatically
     */
    public function getAutoUploading()
    {
        return $this->_isAutoUploading;
    }

    /**
     * Set the auto uploading flag
     * @param boolean is the data uploaded automatically
     */
    public function setAutoUploading($value)
    {
        $this->_isAutoUploading = $value ? true : false;
    }

    /**
     * Get ID number of the dataset being uploaded
     * @return integer ID of dataset being uploaded
     */
    public function getDatasetId()
    {
        return $this->_datasetId;
    }

    /**
     * Set ID number of the dataset being uploaded
     * @param integer ID of dataset being uploaded
     */
    public function setDatasetId($value)
    {
        if ((int)$value) {
            $this->_datasetId = (int)$value;
        } else {
            throw new Exception('Invalid dataset ID.');
        }
    }
    
    /**
     * 
     * Setting dataset data
     * @param int $datasetId
     * @return void
     */
    public function setDatasetData($datasetId)
    {
        if (empty($datasetId)) {
            return false;
        }
        
        $this->setDatasetId($datasetId);
        
        $datasetData = $this->_sourceModel
            ->find($datasetId)
            ->toArray();
        
        $this->_datasetData = $datasetData[0];
    }
    
    /**
     * 
     * Getting dataset data
     * @return mixed
     */
    public function getDatasetData()
    {
        return $this->_datasetData;
    }
    
    /**
     * Set ID number of the experiment being uploaded
     * @param integer ID of experiment being uploaded
     */
    public function setExperimentId($value)
    {
        if ((int)$value) {
            $this->_experimentId = (int)$value;
        } else {
            throw new Exception('Invalid experiment ID.');
        }
    }

    /**
     * Get dataset types
     * @return array dataset types
     */
    public function getDatasetTypes()
    {
        return $this->_datasetTypes;
    }
    
    /**
     * Get experiment types
     * @return array experiment types
     */
    public function getExperimentTypes()
    {
        return $this->_experimentTypes;
    }

    /**
     * Get all options
     * @return array options
     */
    public function getOptions()
    {
        /**
         * Redefine the options array to meet the default values
         */
        foreach ($this->_options as $key => $value) {
            $this->setOption($key, $value);
        }
        return $this->_options;
    }

        /**
     * Set the storage result option value
     * @param string $name option name
     * @param string $value option value
     */
    public function setOption($name, $value)
    {
        $availableMethods = array(
            self::OPTIONS_METHOD_DEFAULT,
            self::OPTIONS_METHOD_OSCAR
        );
        if ('method' == $name && !in_array($value, $availableMethods)) {
            $value = self::OPTIONS_METHOD_DEFAULT;
        }
        $this->_options[$name] = $value;
    }

    /**
     * Get the storage result option value
     * @param string $name option name
     * @return mixed option value
     */
    public function getOption($name)
    {
        if ('method' == $name && !$this->_options[$name]) {
            $this->_options[$name] = self::OPTIONS_METHOD_DEFAULT;
        }
        return $this->_options[$name];
    }

    /**
     * Adds a new error message to the stack
     * @param string $msg Error description
     */
    public function setErrorMessage($msg, $id = false)
    {
        if (false !== $id) {
            /**
             * @todo set errors for specific elements
             */
        }
        $newId = 'm' . sizeof($this->_errors);
        $this->_errors[$newId] = $msg;
        if ($this->_debug) {
            $this->_logger->debug('Error: ' . $msg);
            if ($this->getAutoUploading()) {
                echo 'Error: ' . $msg;
            }
        }
    }

    /**
     * Return list of error messages
     * @return array error messages
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Update the message list with given message(s)
     * @param mixed $msg single message or list of messages
     */
    public function setMessage($message)
    {
        if (!$message) {
            return;
        }
        if (is_array($message)) {
            foreach ($message as $msg) {
                $this->setMessage($msg);
            }
        } else {
            $this->_messages[] = $message;
        }
    }

    /**
     * Return list of messages
     * @return array messages
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    /**
     * Generates unique name for temporary folder and optionally creates it
     * @todo this function may represent some system layer, it might be useful not only for results uploading
     * @param string $path path to the folder for the new folder to be created within
     * @param boolean $create whether the new folder should be physically created or only its name should be generated
     * @param <type> $suffix
     * @return string generated folder name or null if it wasn't generated
     */
    public function _genTempDir($path, $create = false, $suffix = '')
    {
        $result = true;
        $limit = 10;
        do {
            if ($limit <= 0) {
                break;
            }
            $file = $path . DIRECTORY_SEPARATOR . mt_rand().$suffix;
            --$limit;
        } while(file_exists($file));

        if ($limit>0) {
            if ($create) {
                $oldumask = umask(0);
                $result = mkdir($file, 01700, true);
                umask($oldumask);
            }
        } else {
            $result = false;
        }

        return $result ? $file : null;
    }

    /**
     * Removes folder with its content
     * @todo this function may represent some system layer, it might be useful not only for results uploading
     * @param string $dir path to the directory to remove
     */
    public function removeDirectory($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . DIRECTORY_SEPARATOR . $object) == "dir")
                        $this->removeDirectory($dir . DIRECTORY_SEPARATOR . $object);
                    else
                        unlink($dir . DIRECTORY_SEPARATOR . $object);
                }
            }
            rmdir($dir);
        }
    }

    /**
     * Get the Dataset name by its ID in MySQL DB
     * @param integer $dataset_id the internal dataset ID
     * @return string Dataset name
     */
    public function getDatasetName($datasetId)
    {
        if (empty($datasetId)) {
            return false;
        }
        
        $dataset = $this->_sourceModel
                        ->find($datasetId)
                        ->toArray();
        return $dataset[0]['SOURCE_ABBR'];
    }
    
    /**
     * Get the Dataset name by its ID in MySQL DB
     * @return string Dataset name
     */
    public function getExperimentName()
    {
        return $this->_experimentData['EXPERIMENT_NAME'];
    }
    
    /**
     * 
     * Setting experiment data
     * @param int $experimentId
     * @return void
     */
    public function setExperimentData($experimentId)
    {
        if (empty($experimentId)) {
            return false;
        }
        
        if (empty($this->_experimentId)) {
            $this->setExperimentId($experimentId);
        }
        
        $experimentData = $this->_experimentModel
            ->find($experimentId)
            ->toArray();
        
        $this->_experimentData = $experimentData[0];
    }
    
    /**
     * 
     * Getting experiment data
     * @return mixed
     */
    public function getExperimentData()
    {
        return $this->_experimentData;
    }

    /**
     * Validate uploaded zip file structure
     * @param string $path path to the unzipped uploaded file
     * @param string $datasetName name of the dataset to process in the unzipped archive
     * @return array validated methods list
     */
    public function getValidatedMethods($path, $datasetName)
    {
        $error_no_method_results = false;
        /**
         * @todo the filename may be of the different letter case
         */
        $methodMapper = new Application_Model_MethodMapper();
            $refMethods = array_flip($methodMapper->fetchPairs());

        $methods = $this->_getFolders($path . DIRECTORY_SEPARATOR . $datasetName);
        foreach ($methods as $key => $method) {
            $method_path = $path . DIRECTORY_SEPARATOR . $datasetName . DIRECTORY_SEPARATOR . $method;
            /**
             * The method data should be stored in a separate directory.
             * All files are ignored, no errors are generated.
             */
            if (!is_dir($method_path)) {
                unset($methods[$key]);
                continue;
            }
            if (!isset($refMethods[$method])) {
                $this->addWarning('Method ' . $method . ' doesn\'t exist.');
                unset($methods[$key]);
                continue;
            }
            if (!file_exists($method_path . DIRECTORY_SEPARATOR . 'results') || !is_dir($method_path . DIRECTORY_SEPARATOR . 'results')) {
                $this->addWarning($method. ' in '.$datasetName.' has no results folder.');
                unset($methods[$key]);
                continue;
            }
        }
        if (empty($methods)) {
            $this->addWarning($datasetName.' folder has not methods to load.');
        }
        
        return $methods;
    }
    
    /**
     * Save a log messages about the user uploaded files
     */
    public function saveLogs()
    {
        if ($this->getAutoUploading()) {
            $userId = -1;
        } else {
            $userId = $this->get_current_user_id();
        }

        $log = new Application_Model_DbTable_RunResultsUploadingLog();
        
        foreach ($this->_logs as $dataset => $results) {
            /**
             *  save dataset log
             */
            $data = array(
                'user_id' => $userId,
                'dataset' => $dataset,
                'status'  => '0',
                'is_loaded_s3' => $this->getOption('load_s3') ? '1' : '0',
                'is_loaded_oracle' => $this->getOption('load_oracle') ? '1' : '0'
            );
            if ($this->getErrors()) {
                $data['error'] = implode(',', $this->getErrors());
            }
            $logId = $log->insert($data);

            /**
             * List of records to be inserted to the runs log table
             * @var array
             */
            $rows = array();
            $queryParams = array();
            /**
             * @todo do multiple inserts within a transaction using adodb methods instead of pure sql complete insert
             * To do this the project database/tables should support transactions
             * $db->StartTrans();
             * $log1->save(); ... $logN->save();
             * $db->CompleteTrans();
             */
            foreach ($results as $method => $runs) {
                foreach ($runs as $run => $status) {
                    $rows[] = "(" . $logId . ", " . $log->quote($method) . ", " .
                        $log->quote($run) . ", " . $log->quote($status) . ")";
                }
                $is_first_record = false;
            }
            if ($rows) {
                $query = 'insert into result_upload_log_runs_tbl (result_upload_log_id, method, run, status) values ' . implode(',', $rows);
                try {
                    $log->query($query);
                } catch (Exception $e) {
                    $this->setErrorMessage($e->getMessage());
                    $this->setErrorMessage($query);
                }

            }
        }
        
        if (!isset($logId)) {
            $this->saveLogWithError();
        }

    }

    public function getS3()
    {
        if (!$this->_s3) {
            $this->_connectS3();
        }
        return $this->_s3;
    }

    /**
     * Create instance of S3 class to work with S3 files
     * @return S3 class instance
     */
    private function _connectS3()
    {
        if ($this->_s3) {
            return $this->_s3;
        }

        $cfg = getConfiguration();
        /**
         * @todo rewrite this using Zend class for working with S3
         */
        $this->_s3 = new S3Manager($cfg->get_setting('aws', 'aws_access_key_id'), $cfg->get_setting('aws', 'aws_secret_access_key'));
        return $this->_s3;
    }

    /**
     * Get the name of bucket that is used to store S3 results
     * @param boolean $fullName return full bucket name (s3://name/), only bucket name otherwise
     * @return string bucket
     */
    public function getS3ResultsBucket($fullName = false)
    {
        if ($this->_s3StorageBucket) {
            return $this->_s3StorageBucket;
        }
        $site_configuration = SiteConfig::get();
        $this->_s3StorageBucket = $site_configuration->results_s3_bucket;
        if (!$fullName) {
            preg_match('/^s3:\/\/(.+)\//i', $this->_s3StorageBucket, $m);
            /**
             * @todo more complex bucket name validation
             */
            $this->_s3StorageBucket = $m[1] ? $m[1] : false;
        }
        return $this->_s3StorageBucket;
    }

    /**
     * Returns path to the runs storage on S3 instance
     * @return string path
     */
    public function getS3StoragePath()
    {
        if ($this->_s3StoragePath) {
            return $this->_s3StoragePath;
        }
        //$site_configuration = SiteConfig::get();
        //$this->s3_storage_path = $site_configuration->methods_instance_path;
        $this->_s3StoragePath = '';
        return $this->_s3StoragePath;
    }

    /**
     * Upload files from source location to S3 bucket
     * @param string source folder path
     * @param string destination folder on S3
     * @return boolean
     */
    public function uploadRunToS3($src, $dest)
    {
        $result = true;
        $files = scandir($src);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (is_dir($file)) {
                /**
                 * there are no folders inside a run result folder, but I'd like to be sure of that
                 */
                continue;
            }
            $this->_logger->info('Uploading file '. $file. ' to '. $dest . ' in bucket '. $this->getS3ResultsBucket());
            $file_path = $src . DIRECTORY_SEPARATOR . $file;
            if (!$this->getS3()->putObject($this->getS3()->inputFile($file_path),
                $this->getS3ResultsBucket(),
                $dest . $this->getS3()->getFolderSeparator() . $file,
                S3::ACL_PRIVATE))
            {
                $this->_logger->info('Uploading failed');
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Adds information about the Run result to the log file
     * @param string $dataset dataset name
     * @param string $method method name
     * @param string $run name of the run that was uploaded
     * @param integer $status staus of the run 0 if there were no errors, 1 otherwise
     */
    public function setLogRun($dataset, $method, $run, $status)
    {
        if ($status == 1 || $status == '1' || $status == true)
            $status = '1';
        else
            $status = '0';

        if (!isset ($this->_logs[$dataset])) {
            $this->_log[$dataset] = array();
        }
        if (!isset ($this->_logs[$dataset][$method])) {
            $this->_logs[$dataset][$method] = array();
        }
        if (!isset ($this->_logs[$dataset][$method][$run])) {
            $this->_logs[$dataset][$method][$run] = $status;
        }
    }

    /**
     * Get the method run start time, end time, duration
     * @param string $data_path path to uploaded data
     * @param string $dataset dataset name
     * @param string $method method name
     * @return array runs start time, end time, duration
     */
    private function _getMethodTimestamps($runFolderPath) {
        /*$path = $data_path . DIRECTORY_SEPARATOR .
            $dataset . DIRECTORY_SEPARATOR .
            $method . DIRECTORY_SEPARATOR . 'results';

        if (!is_dir($path)) {
            return false;
        }*/

        //$directoryHandle = opendir($path);

        /**
         * Retrieve runs directories list
         */
        /*while ($run = readdir($directoryHandle)) {
            if($run == '.' || $run == '..') {
                continue;
            }*/

        //$runFolderPath = $path . DIRECTORY_SEPARATOR . $run;
        $results = array();
        if (!is_dir($runFolderPath)) {
            throw new Exception($runFolderPath . " is not directory.");
        }
        $runFolderHandler = opendir($runFolderPath);
        /**
         * Search each directory
         */
        while ($file = readdir($runFolderHandler)) {
            /**
             * Find *.log files
             */
            if ('.log' !== substr($file, -4)) {
                continue;
            }
            $results = $this->_parseLogfileTimestamps($runFolderPath, $file);
            break;
        }

        closedir($runFolderHandler);
        //}

        //closedir($directoryHandle);
        return $results;
    }

    /**
     * Get the run start time and duration from the run log file
     * @param string path to log file folder
     * @param string log file name
     * @return array
     */
    private function _parseLogfileTimestamps($path, $file) {
        $file_name = $file;
        $file = $path . DIRECTORY_SEPARATOR . $file;

        $time_start = null;
        $time_start_found = false;

        $task = 0;
        $largest = 0;
        $sastotal = array();
        $sasinit = array();
        $cumulative = array();

        $handler = fopen($file, 'r');
        while (!feof($handler)) {
            $s = $this->_readLogLine($handler);
            
            $time = 0;
            $largest = 0; # largest of the tasks

            /**
             * Find the run start time
             */
            if (!$time_start_found) {
                if ($time_start = $this->_parseDate($s, 'The SAS System')) {
                    $time_start_found = true;
                }
            }

            if (0 !== stripos($s, 'NOTE:')) {
                continue;
            }
            // #1
            if (preg_match('/NOTE: Remote signon to TSK(\d+) commencing/i', $s, $matches)) {
                $task = $matches[1];
            }
            // #2
            elseif (preg_match('/NOTE: Remote signon to TSK(\d+) complete/i', $s, $matches)) {
                //die "Problem with task $task counting" if $task!=$1;
                // print "Task $task signon ends\n";
                $task = 0;

            }
            // #3
            elseif (preg_match('/NOTE: Remote signoff from TSK(\d+) commencing/i', $s, $matches)) {
                $task = $matches[1];
                # print "Task $task signoff starts\n";
            }
            // #4
            elseif (preg_match('/NOTE: Remote signoff from TSK(\d+) complete/i', $s, $matches)) {
                //die "Problem with task $task counting" if $task!=$1;
                // print "Task $task signoff ends\n";
                $task = 0;
            }
            // #5
            elseif (preg_match('/NOTE:(.+)used/i', $s, $matches)) {
                $s_parent = $s;
                $temp = $matches[1];
                $s = $this->_readLogLine($handler);
                // skip over page header/footer and empty lines
                if (preg_match('/\d+\s+\The SAS System\d*$/', $s, $matches)) {
                    $s = $this->_readLogLine($handler);
                    while (($s = $this->_readLogLine($handler)) == '') {}// emptly lines
                }

                // cpu time not captured
                if (false === stripos($s, 'real time')) {
                    continue;
                }
                $result = $this->_parseSeconds($s);
                // not cumulative total numbers
                if (false !== stripos($temp, 'The SAS System')) {
                    if (!isset ($sastotal[$task])) {
                        $sastotal[$task] = 0;
                    }

                    $sastotal[$task] += $result;
                    if ($task==0)
                        continue;
                    if ($result > $sastotal[$largest])
                        $largest = $task;
                }
                // initiatilazion numbers
                elseif (false !== stripos($temp, 'SAS initialization')) {
                    if (!isset ($sasinit[$task])) {
                        $sasinit[$task] = 0;
                    }
                    $sasinit[$task]+=$result;
                }
                // cumulative numbers for each proc and data step
                else {
                    if (!isset ($cumulative[$task])) {
                        $cumulative[$task] = 0;
                    }
                    $cumulative[$task] += $result;
                }
            }
        }
        fclose($handler);

        if ($sastotal[0] < $sastotal[$largest]+$cumulative[0])
            $sastotal[0] = $sastotal[$largest] + $cumulative[0];

        return array(
            'file' => $file_name,
            'total' => $sastotal[0],
            'start' => $time_start
        );
     }

     /**
     * Read a line from the log file
     * @param resource $file_handler
     * @return string
     */
    private function _readLogLine($fileHandler) {
        $s = fgets($fileHandler, self::MAX_LOG_RECORD_LENGTH);
        return rtrim($s);
    }

    /**
     * Parses string to find a timestamp in it (convert SAS time report to seconds)
     * @param string $s
     * @param string the substring after which the date is expected
     * @return int unix timestamp, null otherwise
     */
    private function _parseDate($s, $substr) {
        $p = stripos($s, $substr);
        if (false === $p) {
            return false;
        }
        $tmp = substr($s, $p + strlen($substr));
        $tmp = trim($tmp);

        if (function_exists('date_parse_from_format')) {
            $date = date_parse_from_format('H:i l, F d, Y', $tmp); // 06:37 Tuesday, August 31,
        } else {
            $month_arr = array (
                "January" => 1,
                "February" => 2,
                "March" => 3,
                "April" => 4,
                "May" => 5,
                "June" => 6,
                "July" => 7,
                "August" => 8,
                "September" => 9,
                "October" => 10,
                "November" => 11,
                "December" => 12
            );
            preg_match('/([0-9]{1,2}):([0-9]{1,2}) ([a-zA-Z]{6,9}), ([a-zA-Z]{3,9}) ([0-9]{1,2}), ([0-9]{2,4})/', $tmp, $matches);
            $date = array (
                "year" => intval($matches[6]),
                "month" => intval($month_arr[$matches[4]]),
                "day" => intval($matches[5]),
                "hour" => intval($matches[1]),
                "minute" => intval($matches[2]),
                "second" => 0,

                "fraction"=> false,
                "warning_count" => 0,
                "warnings" => array(),
                "error_count"=> 0,
                "errors" => array(),
                "is_localtime" => false
            );
        }
        if ($date['error_count']) {
            return false;
        }

        return mktime($date['hour'], $date['minute'], 0, $date['month'], $date['day'], $date['year']);
    }

    /**
     * Parses string to find a timestamp in it (convert SAS time report to seconds)
     * @param string $s
     * @return int amount of seconds
     */
    private function _parseSeconds($s) {
        if (preg_match('/time\s+([\d\.\:]+)/', $s, $matches)) {
            $pieces = explode(':', $matches[1]);
            $seconds = array_pop($pieces);
            $minutes = array_pop($pieces);
            $hours = array_pop($pieces);
            $days = array_pop($pieces);
            return (floatval($seconds) + $minutes*60 + $hours*3600 + $days*86400);
        }
        else {
            return null;
        }
    }

    /**
     * Updates method runs timestamps in the Oracle database
     * @param array $data
     */
    private function _updateRunTimestamps($methodId, $analysisId, $datasetId, $data)
    {


        if (!isset ($this->_uploadedResultsSummary [$methodId] [$datasetId][$analysisId])) {
            return false;
        }


        $this->_storeResultSummary($methodId,
                                   $datasetId,
                                   $analysisId,
                                   array(
                                       'total' => $data['total'],
                                       'start' => $data['start']
                                   ));
        $loadedData = $this->_uploadedResultsSummary [$methodId] [$datasetId][$analysisId];

        $errorFix = array();
        if (!isset($loadedData['total'])) {
            $errorFix['total'] = NULL;
        }
        if (!isset($loadedData['start'])) {
            $errorFix['start'] = NULL;
        }
        if (!isset($loadedData['recordsNumber'])) {
            $errorFix['recordsNumber'] = 0;
        }
        if ($errorFix) {
            $this->_storeResultSummary($methodId,
                                       $datasetId,
                                       $analysisId,
                                       $errorFix);
            $loadedData = array_merge($loadedData, $errorFix);
  
        }


        $query = "INSERT "
            . "INTO OMOP_RESULTS.EXPERIMENT_RESULTS_SUMMARY "
            . "(EXPERIMENT_ID, ANALYSIS_ID, SOURCE_ID, METHOD_ID, RUN_DURATION_IN_MIN, START_DATE, DB_LOAD_DATE, RECORDS_NUMBER) VALUES "
            . "(". $this->_experimentId . ", " . $analysisId . ", " . $datasetId . ", " . $methodId 
            . (is_null($loadedData['total']) ? ", NULL" : ", " . round($loadedData['total']/60) )
            . (is_null($loadedData['start']) ? ", NULL" : ", TIMESTAMP '" . date('Y-m-d H:i:s', $loadedData['start']) ."'")
            . ",SYSDATE, " . $loadedData['recordsNumber'] . ")";
        //}
        $resultsSummary = new Application_Model_DbTable_ExperimentResultsSummary();
        $sqlWhereDelete  = 'EXPERIMENT_ID = ' .$this->_experimentId. ' AND METHOD_ID = ' .$methodId . ' AND SOURCE_ID = ' .$datasetId;
        $sqlWhereDelete .= ' AND ANALYSIS_ID = ' . $analysisId;
        $resultsSummary->delete($sqlWhereDelete);
        //if (sizeof($insertsData)) {
        $resultsSummary->query($query);
        //}
    }

    /**
     * Parse OSCAR result file and load parsed results to Oracle database.
     * Code is ported from Korn Shell scripts
     * 
     * @deprecated Now for loading data in Oracle DB uses _loadOscarResultsToOracle wich work with directory iterator and sqlldr
     */
    /*private function _loadOscarResultsToOracle($dataset, $path)
    {
        if (!is_dir($path)) {
            return false;
        }

        $table = new Application_Model_DbTable_OscarResults();
        $resultExists = $table->checkDatasetExist($dataset);
        if (!$resultExists || $this->getOption('override_results')) {
            if ($resultExists) {
                $table->deleteDatasetResult($dataset);
            }

            $datasetId = $table->getDatasetByName($dataset);
            if (!$datasetId) {
                $this->setErrorMessage('Trying to load data for not existing dataset');
            }
            $objects = scandir($path);
            $result = true;
            $filesAmount = 0;
            foreach ($objects as $file) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $filesAmount++;
                try {
                    $table->insertFileData($file, $datasetId);
                    $result = $table->loadFromFile($path . DIRECTORY_SEPARATOR . $file, $datasetId) ? $result : false;
                } catch (Exception $e) {
                    $result = false;
                    $this->setErrorMessage($e->getMessage());
                }
            }
            if (!$filesAmount) {
                $result = false;
                $this->setErrorMessage('There were no result files found.');
            }
        } else {
            $result = true;
            $this->setMessage('Result already exists. Skipping loading to Oracle.');
        }

        $this->setLogRun($dataset, 'OSCAR', '', $result ? '0' : '1');

        return $result;
    }*/
    /**
     * Load OSCAR result files from dataset folder:
     * - if isset option "load_Oracle" load data from result files from dataset in Oracle database with sqlldr utility;
     * - if isset option "load_S3" load data from result files from dataset in S3 server.
     * 
     * @method _loadOscarResults
     * @access private
     * 
     * @param String $dataset Dataset name
     * @param String $path Path to datase folder
     * 
     * @return boolean Flag of the process success
     */
    private function _loadOscarResults($dataset, $path)
    {
        $path = $path . DIRECTORY_SEPARATOR . $dataset;
        if (!is_dir($path)) {
            return false;
        }
        $dirIterator = new DirectoryIterator($path);
        $filesAmount = 0;
        if ($this->getOption('load_oracle'))
            $table = new Application_Model_DbTable_OscarResults();
        foreach ($dirIterator as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $this->_logger->info('Start to load OSCAR results from file '. $file->getFilename() . ' for dataset '.$dataset);
            $result = false;
            $loadedOracle = false;
            $loadedS3 = false;
            if ($this->getOption('load_oracle')) {
               $loadedOracle = $this->_loadOscarResultsToOracle($file,$dataset,$table);
               //$this->_loaded_oracle = $this->_loaded_oracle || $loadedOracle;
               //if (!$loadedOracle)
               //    throw new Exception("Load data from file ".$file->getBasename()." for dataset ".$dataset." in Oracle filed");
            }
            if ($this->getOption('load_s3')) {
                $loadedS3 = $this->_loadOscarResultsToS3($file,$dataset);
                //$this->_loaded_s3 = $this->_loaded_s3 || $loadedS3;
            }
            $result = $loadedOracle || $loadedS3;
            if ($result) {
                $filesAmount++;
                array_push ($this->_loadDataTree[$this->_dataset],$file->getFilename());
            }
            else {
                throw new Exception("Load data from file ".$file->getBasename()." for dataset ".$dataset." filed");
            }
        }
        if (!$filesAmount) {
            $this->setErrorMessage('There were no result files found.');
        }
        return $filesAmount;
    }
    
    private function _loadOscarResultsToOracle ($file,$dataset,$table = null) {
        $result = false;
        $trans = false;
        try {
            if ((!$table) || !($table instanceof  Application_Model_DbTable_OscarResults))
                $table = new Application_Model_DbTable_OscarResults();
            $resultExists = $table->checkDatasetExist($dataset);
            if (!$resultExists || $this->getOption('override_results')) {
                if ($resultExists) {
                    $this->_logger->info('Start to delete existing OSCAR results from table OSCAR_RESULTS for dataset '.$dataset.' in Oracle');
                    Zend_Registry::get('oracle_adapter')->beginTransaction();
                    $trans = true;
                    $table->deleteDatasetResult($dataset);
                    Zend_Registry::get('oracle_adapter')->commit();
                }
                $datasetId = $table->getDatasetByName($dataset);
                if (!$datasetId) {
                    throw new Exception('Trying to load data for not existing dataset');
                }
                $this->_logger->info('Start to load OSCAR results from file '. $file->getFilename() . ' for dataset '.$dataset.' to Oracle');
                if ($result = $this->_loadOscarResultFileInOracle($file, $datasetId)) {
                    $table->insertFileData($file->getBasename(), $datasetId);
                    //Zend_Registry::get('oracle_adapter')->commit();
                    //$trans = false;
                    $this->_loaded_oracle = $result = true;
                }
                else {
                    //if ($trans) {
                        //Zend_Registry::get('oracle_adapter')->rollBack();
                        //$trans = false;
                    //}
                    throw new Exception("Load data from file ".$file->getBasename()." for dataset ".$dataset." in Oracle filed");
                }
            }
            else {
                $this->setMessage('Result already exists. Skipping loading to Oracle.');
                $this->_skip_oracle = $result = true;
            }
            return $result;
        }
        catch (Exception $exc) {
            //if ($trans)
            //    Zend_Registry::get('oracle_adapter')->rollBack();
            throw $exc;
        }
    }
    /**
     * Method load data from Oscar result file in Oracle with calling sqlldr
     * 
     * @method _loadOscarResultFileInOracle
     * @access private
     * 
     * @param DirectoryIterator $file
     * @param String $datasetId
     * 
     * @return boolean Flag of the success load file datas
     */
    private function _loadOscarResultFileInOracle ($file,$datasetId) {
        global $configurationManager;
        $config = new WebRLConfiguration($configurationManager);
        $ctlFilePath = $config->oscarResultsLoadCtlFile();
        $this->_logger->info('Using ctl file '.$ctlFilePath);
        $ctlFile = file_get_contents($ctlFilePath);
        $ctlFile = str_replace(array('<DATASET>'), array($datasetId), $ctlFile);
        $tmpPath = tempnam(sys_get_temp_dir(), 'ctl');
        $tmpCtlPath = $tmpPath.'.ctl';
        file_put_contents($tmpCtlPath, $ctlFile);
        $this->_launchSqlLdr($tmpCtlPath, $file->getPathname());
        unlink($tmpPath);
        unlink($tmpCtlPath);
        return true;
    }
    /**
     * Parse run results files and load parsed results to Oracle database.
     * Code is ported from Korn Shell scripts
     * @todo make it private/protected
     */
    public function loadResults($method, $dataset, $path)
    {
        $result = true;
        try {
    
            $topDir = $path . DIRECTORY_SEPARATOR . $dataset;
    
            // Get list of parameter directories to process
            $dirs = $topDir . DIRECTORY_SEPARATOR . $method . DIRECTORY_SEPARATOR . 'results';
    
            //Get the method list
            //getting data pair method_name => method_id
            $methodModel = new Application_Model_Method();
            $methodModel->findByAbbr($method);
            $this->_currentMethodModel = $methodModel;
            /**
             * Get the method analyses list
             */
            $analysisMapper = new Application_Model_AnalysisMapper();
            $this->_logger->info('Fetching analysis_ref data');
            $analyses = $analysisMapper->fetchByMethod($methodModel->getId());
            $refAnalysis = array();
            foreach ($analyses as $a) {
                $refAnalysis[$a->getOutputFileName()] = $a->getId();
            }
            
            if (empty($refAnalysis)) {
                $this->_logger->info('Analysis list is emlty');
                throw new Exception('Analysis_ref list is empty.');
            } 
            //records for adding to Experiment_Results
            $rows = array();
    
            $existingRuns = array();
            $loadResult = true;
            if ($this->getOption('load_oracle')) {
                $this->_logger->info('Loading results to Oracle');
                $loadResult = $this->loadRunResultsToOracle($dirs, $dataset, $methodModel, $refAnalysis) && $loadResult;
            }
            
            if ($this->getOption('load_s3')) {
                $this->_logger->info('Loading results to S3');
                $loadResult = $this->loadResultsToS3 ($path, $dataset, $methodModel) && $loadResult;
            }

            if (!$loadResult) {
                $this->setErrorMessage('There are no run directories for '.$method.' in '.$dataset.' that match this experiment.');
            }
            
        } catch (Exception $e) {
            $result = false;
            $this->setErrorMessage($e->getMessage());
            if ($startTransaction) {
                Zend_Registry::get('oracle_adapter')->rollBack();
            }
        }
        
        return $result;
    }
    
    private function _findSupplementals1($dir, $run, $method, $dataset) {
        
        $suppl = $method->getSupplementals1();
        foreach ($suppl as $file) {
            $file_name = $file->getConvertedFileName($dataset);
            $this->_logger->info('Searching for supplemental file: '. $file_name);
            if (is_file($dir.DIRECTORY_SEPARATOR.$run.DIRECTORY_SEPARATOR.$file_name)) {
                $this->_logger->info('Found supplemental file: '. $file_name);
                $loadFunction = $file->getLoadFunctionName();
                $this->$loadFunction($dir.DIRECTORY_SEPARATOR.$run.DIRECTORY_SEPARATOR.$file_name, $method, $dataset, $file);
            }
        }
        
    }
    
    private function _loadSuppl1ForSCCS($file_path, $method, $dataset, $suppl) {
        global $configurationManager;
        
        $config = new WebRLConfiguration($configurationManager);
        $ds = array_flip($this->getDatasetTypes());
        $datasetId = $ds[$dataset];                
        $ctlFilePath = $config->suppl1_load_ctl_file();
        $this->_logger->info('Using ctl file '.$ctlFilePath);
        $ctlFile = file_get_contents($ctlFilePath);
        $ctlFile = str_replace(array('<SUPPLEMENT>', '<DATASET>', '<EXPERIMENT>'), array($suppl->getId(), $datasetId, $this->_experimentId), $ctlFile);
        $tmpPath = tempnam(sys_get_temp_dir(), 'ctl');
        $tmpCtlPath = $tmpPath.'.ctl';
        file_put_contents($tmpCtlPath, $ctlFile);
        $this->_launchSqlLdr($tmpCtlPath, $file_path);
        unlink($tmpCtlPath);
        unlink($tmpPath);
    }
    
    private function _loadSuppl2ForSCCS($table, $file_path, $method, $dataset, $analysis_id) {

        global $configurationManager;
        
        $config = new WebRLConfiguration($configurationManager);
        $ds = array_flip($this->getDatasetTypes());
        $datasetId = $ds[$dataset];        
        
        $row = "DELETE FROM SCCS_SUPPLEMENTAL_RESULTS_2 WHERE EXPERIMENT_ID = {$this->_experimentId} AND ANALYSIS_ID = {$analysis_id} AND SOURCE_ID = {$datasetId}";
        $table->query($row);
        
        $ctlFilePath = $config->suppl2_load_ctl_file();
        $this->_logger->info('Using ctl file '.$ctlFilePath);
        $ctlFile = file_get_contents($ctlFilePath);
        $ctlFile = str_replace(array('<ANALYSIS>', '<DATASET>', '<EXPERIMENT>'), array($analysis_id, $datasetId, $this->_experimentId), $ctlFile);
        $tmpPath = tempnam(sys_get_temp_dir(), 'ctl');
        $tmpCtlPath = $tmpPath.'.ctl';
        file_put_contents($tmpCtlPath, $ctlFile);
        $this->_launchSqlLdr($tmpCtlPath, $file_path);
        unlink($tmpCtlPath);
        unlink($tmpPath);
        
    }    
   
    public function loadRunResultsToOracle($dirs, $dataset, $methodModel, $refAnalysis) {
        $table = new Application_Model_DbTable_ExperimentResults();
        if ($this->getOption('override_results')) {
            $existingRuns = $table->getRuns($this->_experimentId, $this->_datasetId, $methodModel->getId());
        }

        if (!$fileNameFormat = $methodModel->getFileNameFormat()) {
           $this->addWarning('Oracle load: File name format is missing for method '.$method);
        }
        // Read run directories within results directory (read run folders)
        $paramList = array();
        $objects = $this->_getFolders($dirs);
        $this->_initRunsLoadedToOracle();
        $oneRunFound = false;
        $oneFileFound = false;
        foreach ($objects as $run) {
            if (!$this->_checkRunName($run)) {
                $this->_logger->info('Run '. $run .' not mathced for this experiment');
                continue;
            }
            $this->_logger->info('Searching for results in run '. $run);
            $oneRunFound = true;
            if (!$this->getOption('override_results') && is_array($existingRuns) && in_array($run, $existingRuns)) {
                $this->addMessage('Oracle load: Results for ' . $run . ' alredy exist. Skipping...');
                continue;
            }
            //getting data from log file
            try {
                $timestamps = $this->_getMethodTimestamps($dirs . "/" . $run);
            } catch (Exception $e) {
                $this->addWarning("Oracle load: Parsing log file error for ".$dataset.'/'.$methodModel->getAbbrv().'/'.$run.". " . $e->getMessage());
            }
            if ($methodModel->hasSupplementals1()) {
                $this->_logger->info('Searching for supplemental files of type 1 for run '. $run);
                $suppl1_file = $this->_findSupplementals1($dirs, $run, $methodModel, $dataset);
                if (!$suppl1_file) {
                    $this->_logger->info('No supplemetnal 1 files found for run '. $run);
                } else {
                    $this->_logFileLoad($run, $suppl1_file);
                }
            } else {
                $this->_logger->info('Not searching Supplemental 1 files for run '. $run);
            }
            $paramList[] = $run;
            $this->_initRunsLoadedToOracle($run);
            ++$cnt;
            /**
             * loop for all run subdirectories. For each processing file found,
             */
            $files = scandir($dirs . DIRECTORY_SEPARATOR . $run);
            $suppl2_rows = array();
            foreach ($files as $file) {
                $startTransaction = false;
                if ('.' === $file || '..' === $file) {
                    continue;
                }
                if (is_dir($dirs . DIRECTORY_SEPARATOR . $file)) {
                    continue;
                }
                
                if (!$methodModel->hasSupplementals2())
                    if (!fnmatch($fileNameFormat, $file)) {
//                        $this->addWarning('Incorrect filename format: "' .$file. '" in run '.$dataset.'/'.$method.'/'.$run);
                        $this->_logger->info('Skipping file '. $file .' because it doesn\'t match file mask for method.');
                        continue;
                    }
                $fileRenamed = str_replace($dataset, '<Dataset>', $file);
                $this->_logger->info('Renamed file '. $file. ' to '. $fileRenamed);
                if (!isset($refAnalysis[$fileRenamed])) {
//                        $this->setErrorMessage('Record with filename ' . $fileRenamed . ' is absent in database.');
                    if ($methodModel->hasSupplementals2()) {
                        $this->_logger->info('Checking if '. $file. ' is supplemental 2 file.');
                        $sup_file = str_replace('ALL_', '', $fileRenamed);
                        if (isset($refAnalysis[$sup_file])) {
                            $this->_logger->info('Reading supplemental 2 data from file '. $file);
                            $funcName = '_loadSuppl2For'.$methodModel->getAbbrv();
                            try {
                                    
                                $this->_logger->info('Uploading supplemental 2 data from file '. $file);
                                $this->$funcName($table, $dirs.DIRECTORY_SEPARATOR.$run.DIRECTORY_SEPARATOR.$file, $methodModel, $dataset, $refAnalysis[$sup_file]);
                                $this->_logFileLoad($run, $file);       

                            } catch (Exception $e) {
                                $this->_logger->info('Error while loading supplemental 2 data from file '. $file);
                                $this->setErrorMessage('Oracle load: Error while loading supplemetnal results file into database for run '.$run.'.');                    
                            }
                            unset($suppl2);
                            continue;
                        } else {
                            continue;
                        }
                    } else {
                        $this->_logger->info('Not searching for supplemental 2 files.');
                        $this->_logger->info('Skipping file '. $file. ' because there\'s no matching analysis_ref data.');
                        continue;
                    }
                }
                $oneFileFound = true;
                $analysisId = $refAnalysis[$fileRenamed];
                $this->_logger->info('Loading results data from file '. $file. ', analysis ID is '. $analysisId);
                $ds = array_flip($this->getDatasetTypes());
                $datasetId = $ds[$dataset];
/*                $rows = $this->_parseAnalysisFile($dirs . DIRECTORY_SEPARATOR . $run . DIRECTORY_SEPARATOR . $file,
                        $methodModel->getId(),
                        $datasetId,
                        $analysisId);
*/              $rows = file($dirs . DIRECTORY_SEPARATOR . $run . DIRECTORY_SEPARATOR . $file);
                $this->_storeResultSummary($methodModel->getId(),
                                           $datasetId,
                                           $analysisId,
                                           array('recordsNumber'=>count($rows) - 1));
                unset($rows);
/*                if (!sizeof($rows)) {
                    $this->_logger->info('No results found in file '. $file);
                    $this->addWarning('Oracle load: File ' . $file . '. There is no data to upload.');
                    continue;
                }*/
                //start transaction
                Zend_Registry::get('oracle_adapter')->beginTransaction();
                $startTransaction = true;

                //deleting old results
                $query = 'DELETE FROM OMOP_RESULTS.EXPERIMENT_RESULTS '
                    . 'WHERE source_id=' . $datasetId . ' AND experiment_id=' . $this->_experimentId . ' AND analysis_id = ' . $analysisId;

                try {
                    $table->query($query);
                } catch (Exception $e) {
                    $this->_logger->info('Error occurred while deleting previous results');
                    $this->setErrorMessage('Oracle load: Error while deleting data from database. Query: ' . $query);
                }
                Zend_Registry::get('oracle_adapter')->commit();
                $this->_logger->info('Deleted previous results');
                //insert experiment results data
                $this->_logger->info('Inserting results into database');
                $rows = $this->_loadAnalysisFile($dirs . DIRECTORY_SEPARATOR . $run . DIRECTORY_SEPARATOR . $file,
                        $methodModel->getId(),
                        $datasetId,
                        $analysisId);
                $this->_logFileLoad($run, $file);
                
                //insert data into Experiment_Results_Summary
                try {
                    $this->_updateRunTimestamps($methodModel->getId(), $analysisId, $datasetId, $timestamps);
                } catch (Exception $e) {
                    throw new Exception('Oracle load: Error while loading summary data for run '.$run.'.');
                }
                
                unset($rows);
                
                $this->_loaded_oracle = true;
                $this->updateRunStatus($run, 1);
            }

//            if (isset($suppl1_rows) && is_array($suppl1_rows)) {
//                try {
//                    $this->_logger->info('Inserting supplemental 1 data into database');
//                    foreach ($suppl1_rows as $row){
//                        $table->query($row);
//                    }
//                } catch (Exception $e) {
//                    $this->_logger->info('Error occurred while uploading supplemetal 1 data');
//                    $this->setErrorMessage('Oracle load: Error while loading supplemetnal results into database for run '.$run.'.');                    
//                }
//            }
            unset($suppl1_rows);
        }
        
        if (!$oneRunFound) {
            $this->_oracle_error_cause = 'No runs found for current experiment.';
        }
        
        if (!$oneFileFound) {
            $this->_oracle_error_cause = 'No files matched to analysis data.';
        }
        
        return ! empty($paramList);

    }

    private function _loadAnalysisFile($file, $methodId, $sourceId, $analysisId) {
        global $configurationManager;
        
        $config = new WebRLConfiguration($configurationManager);
        
      
        $ctlFilePath = $config->analysis_load_ctl_file();
        $this->_logger->info('Using ctl file '.$ctlFilePath);
        $ctlFile = file_get_contents($ctlFilePath);
        $ctlFile = str_replace(array('<METHOD>', '<DATASET>', '<EXPERIMENT>', '<ANALYSIS>'), array($methodId, $sourceId, $this->_experimentId, $analysisId), $ctlFile);
        $tmpPath = tempnam(sys_get_temp_dir(), 'ctl');
        $tmpCtlPath = $tmpPath.'.ctl';
        file_put_contents($tmpCtlPath, $ctlFile);
        $this->_launchSqlLdr($tmpCtlPath, $file);
        unlink($tmpPath);
        unlink($tmpCtlPath);
        
    }
    
    
    public function addResultAttachment($name, $path) {
        if (! is_array($this->_attachments)) {
            $this->_attachments = array();
        }
        $this->_attachments[$name] = $path;
    }
    
    private function _launchSqlLdr($ctlPath, $file) {
        global $configurationManager;
        
        $config = new WebRLConfiguration($configurationManager);
        $cmd = 'sudo -E LD_LIBRARY_PATH="'.getenv('REDIRECT_LD_LIBRARY_PATH').'" /usr/lib/oracle/11.2/client/bin/sqlldr  ';
        $adapter = Zend_Registry::get('oracle_adapter');
        $config = $adapter->getConfig();
        $cmd .= 'USERID='.$config['username'].'/'.$config['password'].'@'.$config['dbname'].' ';
        $cmd .= 'CONTROL='.$ctlPath.' ';
        $cmd .= 'DATA='.$file;
        $cmd .= ' BAD=/tmp/sqlldr.bad ';
        $cmd .= ' LOG=/tmp/sqlldr.log 2>&1';
        $this->_logger->info($cmd);
        exec($cmd, $output, $retval);
        $this->_logger->info(implode("\n",$output));
        $this->_logger->info('Retval: '.$retval);
        if ($retval != 0 ) {
            $badFile = tempnam(sys_get_temp_dir(), 'bad');
            chmod ($badFile,0777);
            $logFile = tempnam(sys_get_temp_dir(), 'log');
            chmod ($logFile,0777);
            if (file_exists("/tmp/sqlldr.bad") )
                file_put_contents($badFile, file_get_contents('/tmp/sqlldr.bad'));
            if (file_exists("/tmp/sqlldr.log") )
                file_put_contents($logFile, file_get_contents('/tmp/sqlldr.log'));
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $this->addResultAttachment($filename.'.bad', $badFile);
            $this->addResultAttachment($filename.'.log', $logFile);
            $dirs = explode(DIRECTORY_SEPARATOR, $file);
            unset($dirs[0]);
            unset($dirs[1]);
            unset($dirs[2]);
            $file_name_in_archive = implode(DIRECTORY_SEPARATOR, $dirs);
            throw new Exception('Error while loading file '.$file_name_in_archive. '. LOG and BAD files are attached to email.');
        }
        return true;
    }
    
    /** 
     * Parse analysis files and return data ready to be loaded to Oracle
     * @param <type> $method
     */
    private function _parseAnalysisFile($file, $methodId, $sourceId, $analysisId)
    {
        //Drug_concept_id,Condition_concept_id,score,se
        $handle = fopen($file, "r");
        $records = array();
        if ($handle) {
            // skip first line with field names
            $line = fgetcsv($handle);
            while (!feof($handle)) {
                $line = fgetcsv($handle);
                if (empty($line)) {
                    continue;
                }
                $params = array();
                $params['DRUG_ID'] = !trim($line[0]) ? 'NULL' : trim($line[0]); // 'INTEGER EXTERNAL NULLIF (DRUG_ID=BLANKS)';
                if ('NULL' === $params['DRUG_ID']) {
                    $this->setErrorMessage('Incorrect line (drug ID is not set): "'.$line.'" in file ' . $file);
                    continue;
                }
                $params['CONDITION_ID'] = !trim($line[1]) ? 'NULL' : trim($line[1]); // INTEGER EXTERNAL NULLIF (CONDITION_ID=BLANKS)
                $params['SCORE'] = !$line[2] ? 'NULL' : $line[2]; // "decode(Trim(:SCORE), \'NA\', NULL, \'.\', NULL, :SCORE)"
                // "TO_NUMBER(decode(:SUPPL_SCORE1, \'.\', NULL, \'\', NULL, :SUPPL_SCORE1))"
                if (!is_numeric($params['SCORE']))
                    $params['SCORE'] = 'NULL';
                for($j=3; $j<8; $j++) {
                    $k = $j - 2;
                    $params['SUPPL_SCORE'.$k] = !isset ($line[$j]) || !$line[$j] ? 'NULL' : $line[$j]; // "decode(Trim(:SCORE), \'NA\', NULL, \'.\', NULL, :SCORE)"
                    if (!is_numeric($params['SUPPL_SCORE'.$k])) {
                        $params['SUPPL_SCORE'.$k] = 'NULL';
                    }
                }
                $params['TIME_ID'] = !isset($line[9]) || !trim($line[9]) ? 'NULL': trim($line[9]); // CHAR NULLIF (TIME_ID=BLANKS)

                $sql = "INSERT INTO OMOP_RESULTS.EXPERIMENT_RESULTS (EXPERIMENT_ID, ANALYSIS_ID, SOURCE_ID, METHOD_ID, DRUG_ID, CONDITION_ID, SCORE, SUPPL_SCORE1, SUPPL_SCORE2, SUPPL_SCORE3, SUPPL_SCORE4, SUPPL_SCORE5, TIME_ID) values(".$this->_experimentId.", ".$analysisId.", ".$sourceId.", ".$methodId.", ".$params['DRUG_ID'].", ".$params['CONDITION_ID'].", ".$params['SCORE'].", ".$params['SUPPL_SCORE1'].", ".$params['SUPPL_SCORE2'].", ".$params['SUPPL_SCORE3'].", ".$params['SUPPL_SCORE4'].", ".$params['SUPPL_SCORE5'].", ".$params['TIME_ID'].")";
                $records[] = $sql;
            }
            fclose($handle);
        }

        return $records;
    }

    private function validateMethodDefault() {
        $experimentDate = $this->getExperimentData();
        if ((empty($this->_experimentId)) || (empty($experimentDate))) {
            throw new Exception('Experiment data is not correct.');
        }
    }
    
    private function validateMethodOscar() {
        $datasetId = $this->getDatasetId();
        $datasetData = $this->getDatasetData();
        if ((empty($datasetId)) || (empty($datasetData))) {
            throw new Exception('Dataset data is not correct.');
        }
    }
    
    /**
     * Upload result file
     * @param string dataset name
     * @param string path to already uploaded file
     * @return boolean uploading result
     */
    public function uploadResults($file = false)
    {
        try {
            $result = true;
            $fileId = false;
            
            $paramValidators = array (
                                        self::OPTIONS_METHOD_DEFAULT => "validateMethodDefault",
                                        self::OPTIONS_METHOD_OSCAR => "validateMethodOscar"
                                    );
            $currentMethod = $this->getOption("method");
            if ($currentMethod && in_array($currentMethod, $paramValidators))
                call_user_func(array($this,$paramValidators[$currentMethod]));
            
            /*if (empty($this->_experimentId)) {
                throw new Exception('Experiment data is not correct.');
            }
            
            if (empty($this->_experimentData)) {
                throw new Exception('Experiment data is not correct.');
            }*/
            
            /**
             * If file was not uploaded from outside then upload it
             */
            $msg = 'Start loading method results data';
            $msg .= ' (' . ($this->getAutoUploading() ? 'auto' : 'web') . ' mode).';
    
            if (false === $file) {
                $fileId = 'results_file';
                if (!isset($_FILES[$fileId]) || !isset($_FILES[$fileId]['tmp_name']) || ! $_FILES[$fileId]['tmp_name'])
                    switch ($_FILES[$fileId]['error']) {
                        case UPLOAD_ERR_INI_SIZE:case UPLOAD_ERR_FORM_SIZE:
                            throw new Exception('File size exceeds upload size limit.');
                        default:
                            throw new Exception('You should choose a file.');
                    }
                $file = $_FILES[$fileId]['tmp_name'];
                
                if (!is_uploaded_file($file)) {
                    throw new Exception('The uploaded result file was not uploaded properly.');
                }
                
                if (!filesize($file)) {
                    throw new Exception('Uploaded file is empty, please.');
                }
            }
            if (!$file) {
                switch ($this->_getUploadType($fileId ? $_FILES[$fileId]['name'] : $file)) {
                    case 'zip':
                        $zip = new ZipArchive;
                        if ($zip->open($file) !== true) {
                            throw new Exception('The .zip archive seems to be corrupted and failed to open.');
                        }

                        /**
                         * Create the temp dir and extract the archive there
                         */
                        $tmpPath = $this->_genTempDir(sys_get_temp_dir(), true);
                        if (!$zip->extractTo($tmpPath)) {
                            throw new Exception('The .zip archive seems to be corrupted and the data can not be extracted.');
                        }
                        $zip->close();
                        break;

                    default: case 'plain':
                        throw new Exception('The format file is not zip.');
                }

                $commandLine = 'chmod -R 777 ' . $tmpPath;
                exec($commandLine, $output, $retval);
                $commandLine = "find ".$tmpPath." -name '*.*' -execdir dos2unix {} \;";
                exec($commandLine, $output, $retval);
            } else {
                $tmpPath = $file;
            }
    
            //@todo add checking that dataset is exist
            
            //Get dataset folders
            $d_folders = $this->_getFolders($tmpPath);
            //Validate datasets
            $diff = array_diff($d_folders, $this->get_access_dataset($d_folders));
            if (!empty($diff)) {
                if ($this->_dataset_access_error_type != '') {
                    throw new Exception($this->_dataset_access_error_type);
                } else {
                    throw new Exception('User does not have permission to load '.implode(',', $diff) . ' dataset(s) results');
                }
            }
            if (empty($d_folders))
                throw new Exception('No datasets found in zip.');
            foreach ($d_folders as $dataset) {
                $this->setDataset($dataset);
                $this->_logger->info('Loading results for dataset '. $dataset);
                $this->_loadResultsForDataset($tmpPath, $dataset);
                $this->saveLogWithError();
            }
            
            $this->removeDirectory($tmpPath);
            $this->_logger->info('Removing temp directory with results');
            $existError = sizeof($this->getErrors()) ? false : true;
            $this->saveLogs();
            $this->_logger->info('Upload logs are saved');
    
        } catch (Exception $e) {
            if (isset($tmpPath) && is_dir($tmpPath))
                $this->removeDirectory($tmpPath);
            $this->setErrorMessage($e->getMessage());
            $result = false;
        }
        if (empty($this->_errors)) {
            if ($this->getOption('load_oracle'))
                if ($this->_loaded_oracle) {
                    if (empty($this->_warnings)) {
                        $this->addMessage('Data is successfully loaded into Oracle');
                    } else {
                        $this->addMessage('Data loaded to Oracle with warning');

                    }
                } elseif (!$this->_skip_oracle && $this->getOption('method')!= self::OPTIONS_METHOD_OSCAR) {
                    $error = 'No data was loaded into Oracle.';
                    if ($this->_oracle_error_cause)
                        $error = $error . ' '. $this->_oracle_error_cause;
                    $this->setErrorMessage ($error);
                }
            if ($this->getOption('load_s3'))
                if ($this->_loaded_s3) {
                    if (empty($this->_warnings)) {
                        $this->addMessage('Data is successfully loaded into S3');
                    } else {
                        $this->addMessage('Data loaded to S3 with warning');

                    }
                } elseif (!$this->_skip_s3 && $this->getOption('method')!= self::OPTIONS_METHOD_OSCAR) {
                    $this->setErrorMessage ('No data was loaded to S3.');
                }    
        }
        else {
            if (!$this->getDataset($dataset))
                $this->setDataset('N/A');
            $this->saveLogWithError();
        }
        
        $msg  = 'Finishing loading method results';
        $msg .= ' (result: "' . ($result ? 'true' : 'false') . '").';
        return $result;
    }

    /**
     * Get the type of loaded file. This could be zip, not compressed file.
     * @param file name of the uploaded file
     */
    private function _getUploadType($file)
    {
        if ('.zip' === substr($file, -4)) {
            return 'zip';
        } else {
            return 'plain';
        }
    }

    /**
     * Load the data into Oracle database
     * @param string $dataPath path to the folder containing the unzipped data
     * @param string $dataset dataset name we are loading methods for
     * @param array $methods array of the names of the methods which has to be processed
     * @return boolean result of the process
     */
    private function _loadMethodResultsToOracle($dataPath, $dataset, $method)
    {
        $result = true;
        if (!$this->loadResultsToOracle($method, $dataset, $dataPath)) {
//            $this->addWarning('Data is not loaded for '.$method.' in '.$dataset.'.');
        }

        foreach ($this->_getRunsLoadedToOracle() as $run) {
            $this->setLogRun($dataset, $method, $run['run'], !$run['status'] ? '0' : '1');
        }

        return $result;
    }

    private function _initRunsLoadedToOracle($run = false, $status = 0)
    {
        if (false === $run) {
            $this->_runsLoadedToOracle = array();
        } else {
            $this->_runsLoadedToOracle[] = array('run' => $run, 'status' => $status);
            /**
             * Add run to the property loadDataTree in method of dataset
             */
            if (($this->_currentMethodModel != null) && ($this->_currentMethodModel instanceof Application_Model_Method)) {
                $this->_loadDataTree[$this->_dataset][$this->_currentMethodModel->getAbbrv()][$run] = array();
            }
        }
    }
    
    private function _logFileLoad($run, $file) {
        if (($this->_currentMethodModel != null) && ($this->_currentMethodModel instanceof Application_Model_Method)) {
            if (isset($this->_loadDataTree[$this->_dataset][$this->_currentMethodModel->getAbbrv()][$run]))
                $this->_loadDataTree[$this->_dataset][$this->_currentMethodModel->getAbbrv()][$run][] = $file;
        }
        
    }

    private function _getRunsLoadedToOracle()
    {
        return $this->_runsLoadedToOracle;
    }

    /**
     * Load the data into S3
     * @param string $dataPath path to the folder containing the unzipped data
     * @param string $dataset dataset name we are loading methods for
     * @param array $methods array of the names of the methods which has to be processed
     * @return boolean result of the process
     */
    private function _loadS3($dataPath, $dataset, array $methods)
    {
        $res = true;

        if (!$this->getS3()) {
            $res = false;
            $this->setErrorMessage('Error loading data to S3. Can\'t connect to S3.');
            return $res;
        }

        if (!$this->getS3ResultsBucket()) {
            $res = false;
            $this->setErrorMessage('Error loading data to S3. Can\'t get S3 bucket name.');
            return $res;
        }


        foreach ($methods as $method) {
            $this->loadResultsToS3($dataPath, $dataset, $method);
        }
        return $res;
    }
    
    public function loadResultsToS3($dataPath, $dataset, $methodModel) {
        $res = true;
        $method = $methodModel->getAbbrv();
        $unzippedResultsPath = $dataPath . DIRECTORY_SEPARATOR .
            $dataset . DIRECTORY_SEPARATOR .
            $method . DIRECTORY_SEPARATOR .
            'results';
        /**
         * @todo If the file already existed check that it is a dir, not a file
         */
        $runs = scandir($unzippedResultsPath);
        foreach ($runs as $idx=>$run) {
            $result = true;
            if ($run == '.' || $run == '..') {
                unset($runs[$idx]);
                continue;
            }
            $s3DirSeparator = $this->getS3()->getFolderSeparator();
            $s3HomeFolder = $this->getS3StoragePath() ? $this->getS3StoragePath() . $s3DirSeparator : '';
            //$s3DirSeparator = $this->getS3()->getFolderSeparator();
            $s3RunPath = $s3HomeFolder .
                $this->getExperimentName(). $s3DirSeparator.
                $dataset . $s3DirSeparator .
                $method . $s3DirSeparator .
                'results' . $s3DirSeparator . $run;
            $isRunDataExists = $this->getS3()->checkFileExists($this->getS3ResultsBucket(), $s3RunPath, true);
            
            if (! $this->_checkRunName($run)) {
                $this->_logger->info('Run '. $run .' not mathced for this experiment');
                continue;
            }
            if ($isRunDataExists)
                $this->_logger->info('Run '. $run . ' is already on S3');
            if (!$isRunDataExists || $this->getOption('override_results')) {
                if ($isRunDataExists) {
                    $this->_logger->info('Will override previous results on S3');
                    $this->getS3()->deleteFolder($this->getS3ResultsBucket(), $s3RunPath);
                }
                $this->_logger->info('Uploading data to S3 for run '. $run);        
                if (!$this->uploadRunToS3($unzippedResultsPath . DIRECTORY_SEPARATOR . $run, $s3RunPath)) {
                    $res = $result = false;
                } else {
                    $this->_loaded_s3 = true;
                }
            }
            
            $this->setLogRun($dataset, $method, $run, $result ? '0' : '1');
        }
        if (!$runs) {
            $res = false;
            $this->setErrorMessage('S3 Load: There were no result runs folder found for '. $method .' in '. $dataset);
        }
        return $res;

    }

    private function _loadOscarResultsToS3($file, $dataset) {
        /**
         * Check existing file on s3 storage
         */
        $s3=$this->getS3();
        if (!$s3) {
            $this->setErrorMessage('Error loading data to S3. Can\'t connect to S3.');
            return false;
        }
        $s3ResultsBucket = $this->getS3ResultsBucket();
        if (!$s3ResultsBucket) {
            $this->setErrorMessage('Error loading data to S3. Can\'t get S3 bucket name (' . $this->getS3ResultsBucket() . ').');
            return false;
        }
        $result = true;
        $s3DirSeparator = $s3->getFolderSeparator();
        $s3HomeFolder = $this->getS3StoragePath() ? $this->getS3StoragePath() . $s3DirSeparator : '';
        $s3DestFolder = $s3HomeFolder.
                        self::S3_OSCARRESULTS_FOLDERNAME. $s3DirSeparator.
                        $dataset . $s3DirSeparator .
                        'results';
        $s3ResultPath =  $s3DestFolder. $s3DirSeparator .
                        $file->getFilename();
        $existInS3 = $s3->checkFileExists($s3ResultsBucket, $s3ResultPath, true);
        $existInS3 && $this->_logger->info('Oscar result file '. $file->getFilename() . ' for dataset '.$dataset.' is already on S3');
        if (!$existInS3 || $this->getOption('override_results')) {
            /**
             * if file exist on s3 delete him
             */
            if ($existInS3) {
                $this->_logger->info('Will override previous results on S3');
                $s3->deleteFolder($s3ResultsBucket, $s3ResultPath);
            }
            $this->_logger->info('Uploading file '. $file->getFilename(). ' to '. $s3DestFolder . ' in bucket '. $s3ResultsBucket);
            $result = $this->uploadOscarResultToS3($file, $s3ResultPath);
            $this->_loaded_s3 = $this->_loaded_s3 || $result;
            if (!$result) {
                $msg = "Uploading failed";
                $this->_logger->info($msg);
                throw new Exception($msg);
            }
        }
        else {
            $this->setMessage('Result already exists. Skipping loading to S3.');
            $this->_skip_s3 = true;
        }
        return $result;
    }
    /**
     * Load the Oscar results data to S3
     * 
     * @param string $dataPath path to the folder containing the unzipped data
     * @param string $dataset dataset name we are loading methods for
     * @return boolean result of the process
     */
    public function uploadOscarResultToS3($file, $s3DestPath = null)
    {
        $s3=$this->getS3();
        if (!$s3) {
            $this->setErrorMessage('Error loading data to S3. Can\'t connect to S3.');
            return false;
        }
        $s3ResultsBucket = $this->getS3ResultsBucket();
        if (!$s3ResultsBucket) {
            $this->setErrorMessage('Error loading data to S3. Can\'t get S3 bucket name (' . $this->getS3ResultsBucket() . ').');
            return false;
        }
        $result = false;
        //$filePath = $file->getPathname();
        if (!$s3DestPath) {
            $s3DirSeparator = $s3->getFolderSeparator();
            $s3HomeFolder = $this->getS3StoragePath() ? $this->getS3StoragePath() . $s3DirSeparator : '';
            $dataset = $this->getDatasetData();
            $dataset = $dataset["source_abbr"];
            $s3DestPath = $s3HomeFolder. 
                          self::S3_OSCARRESULTS_FOLDERNAME. $s3DirSeparator.
                          $dataset . $s3DirSeparator .
                          'results'. $s3DirSeparator .
                          $file->getFilename();
        }
        if ($this->getS3()->putObject($s3->inputFile($file->getPathname()), $s3ResultsBucket, $s3DestPath, S3::ACL_PRIVATE)) {
                $result = true;
        }
        return $result;
    }

    /**
     * Prepare array of data for further updating of METHOD_RESULTS_SUMMARY table
     * @param integer $methodId
     * @param integer $datasetId
     * @param integer $analysisId
     * @param array $data
     */
    private function _storeResultSummary($methodId, $datasetId, $analysisId, $data)
    {
        if (!isset ($this->_uploadedResultsSummary [$methodId])) {
            $this->_uploadedResultsSummary [$methodId] = array();
        }
        if (!isset ($this->_uploadedResultsSummary [$methodId] [$datasetId])) {
            $this->_uploadedResultsSummary [$methodId] [$datasetId] = array();
        }
        if (!isset ($this->_uploadedResultsSummary [$methodId] [$datasetId] [$analysisId])) {
            $this->_uploadedResultsSummary [$methodId] [$datasetId] [$analysisId] = array();
        }
        /**
         * Available fields: total, start, recordsNumber
         */
        foreach ($data as $key => $value) {
            $this->_uploadedResultsSummary [$methodId] [$datasetId] [$analysisId] [$key] = $value;
        }
        
    }
    
    /**
     * 
     * Checking run_name cо значением DIRECTORY_PATTERN из данных Experiment.
     * @param string $runName
     * @return boolean
     */
    private function _checkRunName($runName)
    {
        if (empty($this->_experimentData)) {
            return false;
        }
        if (preg_match("/^<METHOD>(_.*_)\*$/i", $this->_experimentData['DIRECTORY_PATTERN'], $matches)) {
            $pattern = "/^.+.*" . $matches[1] . ".*$/i";
            if (preg_match($pattern, $runName)) {
                return true;
            } else {
                return false;
            }
        } else {
            $this->setErrorMessage('Run '.$runName.' does not match DIRECTORY_PATTERN for experiment.');
            return false;
        }
    }
    
    private function _getFolders($tmpPath) {
        $results = array();
        if (is_dir($tmpPath)) {
            $dir = dir($tmpPath);
            while (false !== ($entry = $dir->read())) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                if (is_dir($tmpPath.DIRECTORY_SEPARATOR.$entry))
                    $results[] = $entry;
            }
        }
        return $results;
        
    }
    
    private function _loadResultsForDataset($resultsPath, $dataset) {

        switch ($this->getOption('method')) {
            case self::OPTIONS_METHOD_DEFAULT:
                if ($this->getOption('method') == self::OPTIONS_METHOD_DEFAULT) {
                    $methodList = $this->getValidatedMethods($resultsPath, $dataset);
                }
                if (empty($methodList)) {
                    $this->_logger->info('No valid mathods found for dataset');
                    throw new Exception('No existing methods found in archive.');
                }
                foreach ($methodList as $method) {
                    $oneMethodFound = true;
                    $this->addMethod($method);
                    $this->_logger->info('Loading data for method '. $method);
                    $this->loadResults($method, $dataset, $resultsPath);
                }
                if ($this->getOption('load_oracle')) {
                    foreach ($this->_getRunsLoadedToOracle() as $run) {
                        $this->setLogRun($dataset, $method, $run['run'], !$run['status'] ? '0' : '1');
                    }
                }
                break;

            case self::OPTIONS_METHOD_OSCAR:
                $this->_logger->info('Load inf OSCAR data');
                $result = false;
                $result = $this->_loadOscarResults($dataset, $resultsPath);
                $this->setLogRun($dataset, 'OSCAR', '', $result ? '0' : '1');
                break;
            default:
                throw new Exception('Method is undefined.');
        }
        return; 
    }
    
    public function updateRunStatus($run, $status) {
        
        foreach ($this->_runsLoadedToOracle as $run) {
            if ($run['run'] == $run) {
                $run['status'] = $status;
            }
        }
        
    }
    
    public function addWarning($warn) {
        
        if (! is_array($this->_warnings)) {
            $this->_warnings = array();
        }
        
        $this->_warnings[] = $warn;
        
    }
    
    public function getWarnings() {
        return $this->_warnings;
    }
    
    public function addMessage($warn) {
        
        if (! is_array($this->_messages)) {
            $this->_messages = array();
        }
        
        $this->_messages[] = $warn;
        
    }    
    
    public function extractZip() {
        $fileId = 'results_file';
        try{
        if (!isset($_FILES[$fileId]) || !isset($_FILES[$fileId]['tmp_name']) || ! $_FILES[$fileId]['tmp_name'])
            switch ($_FILES[$fileId]['error']) {
                case UPLOAD_ERR_INI_SIZE:case UPLOAD_ERR_FORM_SIZE:
                    throw new Exception('File size exceeds upload size limit.');
                default:
                    throw new Exception('You should choose a file.');
            }
        $file = $_FILES[$fileId]['tmp_name'];
      
        if (!is_uploaded_file($file)) {
            throw new Exception('The uploaded result file was not uploaded properly.');
        }

        if (!filesize($file)) {
            throw new Exception('Uploaded file is empty, please.');
        }
        $zip = new ZipArchive;
        if ($zip->open($file) !== true) {
            throw new Exception('The .zip archive seems to be corrupted and failed to open.');
        }

        /**
         * Create the temp dir and extract the archive there
         */
        //$tmpPath = $this->_genTempDir(sys_get_temp_dir(), true);
        switch ($this->getOption("method")) {
            case self::OPTIONS_METHOD_OSCAR :
                $extractPath = $this->_genTempDir(sys_get_temp_dir(), true,DIRECTORY_SEPARATOR.$this->getDatasetName($this->getDatasetId()));
                $tmpPath =realpath(dirname($extractPath));
                break;
            default:
                $extractPath = $this->_genTempDir(sys_get_temp_dir(), true);
                $tmpPath = $extractPath;
        }
        if (!$zip->extractTo($extractPath)) {
            throw new Exception('The .zip archive seems to be corrupted and the data can not be extracted.');
        }
        $zip->close();
        
        $commandLine = 'chmod -R 777 ' . $tmpPath;
        exec($commandLine, $output, $retval);
        $commandLine = "find ".$tmpPath." -name '*.*' -execdir dos2unix {} \;";
        exec($commandLine, $output, $retval);        
        }  catch (Exception $e){
          $this->_errors[] =  $e->getMessage();
          
        }
        
        return $tmpPath;
        
    }
    public function get_access_dataset($dataset) {
        $access_dataset_value = array();
        $array_errors = array();
        $id_array = array();
        if (is_array($dataset)) {
            $array_count = count($dataset);
            for ($i = 0; $i < $array_count; $i++) {
                if (!in_array($dataset[$i], $this->getDatasetTypes()))
                    $array_errors[] = $dataset[$i];
            }
            if (count($array_errors) > 0)
                $this->_dataset_access_error_type = 'Source(s) ' . implode(',', $array_errors) . ' is not defined in source_ref table';
        }
        if (Membership::get_current_user()->admin_flag == 'Y')
            return $this->getDatasetTypes();

        $cur_user_id = (Membership::get_current_user()->user_id ? Membership::get_current_user()->user_id : $this->_current_user_id);
        $user_dataset_access = new UserDatasetAccess();
        $datasetAccessId = $user_dataset_access->find("user_id=?", array($cur_user_id));
        if (is_array($datasetAccessId) && count($datasetAccessId) > 0) {
            foreach ($datasetAccessId as $dataset_id) {
                $id_array[] = $dataset_id->dataset_type_id;
            }

            $in_string = implode(',', array_values($id_array));
            $uda = new DatasetType();
            $datasetAccessList = $uda->find("dataset_type_id in($in_string)");
            foreach ($datasetAccessList as $da_entry) {
                $access_dataset_value[] = $da_entry->dataset_type_description;
            }
        }

        $oracle_mapper = new Application_Model_UserMapper();
        $oracle_access_dataset = $oracle_mapper->GetUserOracleAccess($cur_user_id);
        if (is_array($oracle_access_dataset) && count($oracle_access_dataset) > 0) {
            foreach ($oracle_access_dataset as $dataset_array) {
                $access_dataset_value[$dataset_array['source_id']] = $dataset_array['source_abbr'];
            }
        }
        return (count($access_dataset_value) ? $access_dataset_value : array() );
    }
    public function set_current_user_id($id = ''){
        if(Membership::get_current_user()->user_id == null){
            $this->_current_user_id = (int)$id;
        }else{
            $this->_current_user_id = Membership::get_current_user()->user_id;
        }
        
    }
    
    public function get_current_user_id() {
        if (! $this->_current_user_id) {
            $this->set_current_user_id();
        } 
        return $this->_current_user_id;
    }
    
    private function saveLogWithError() {


        $data = array(
            'user_id' => $this->get_current_user_id(),
            'dataset' => $this->getDataset(),
            'status'  => '0',
            'is_loaded_s3' => '0',
            'is_loaded_oracle' => '0',
            'error' => implode(',', $this->getErrors())
        );
        
        $log = new Application_Model_DbTable_RunResultsUploadingLog();        
        
        $logId = $log->insert($data);
        
        if ($this->getMethods()) {
            foreach($this->getMethods() as $method) {
                $sql = 'INSERT INTO result_upload_log_runs_tbl(result_upload_log_id, method) VALUES ('.$logId.', \''.$method.'\')';
                $log->query($sql);
            }
        }
        
    }
    
    private function addMethod($method) {
        if (! $this->_methods) {
            $this->_methods = array();
        }
        
        $this->_methods[] = $method;
        /**
         * Add method in the property $_loadDataTree in current dataset
         */
        $this->_loadDataTree[$this->_dataset][$method]=array();
    }
    
    private function getMethods() {
        return $this->_methods;
    }
    /**
     * Method return value of property $_loadDataTree.
     * 
     * @method getLoadDataTree
     * @access public
     * 
     * @return Array Value of property $_loadDataTree
     */
    public function getLoadDataTree() {
        if ((!$this->_loaded_oracle && !$this->_loaded_s3) || ($this->_skip_s3 && $this->_skip_oracle))
                return null;
        return $this->_loadDataTree;
    }
    /**
     * Method return array with headers for upload type
     * 
     * @method getLoadDataHeader
     * @access public
     * 
     * @return array 
     */
    public function getLoadDataHeader () {
        return self::$_loadDataHeadersList[$this->getOption('method')];
    }
    
}
