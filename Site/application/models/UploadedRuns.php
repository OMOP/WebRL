<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    23 December 2010

    Model of all uploaded runs.

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

final class Application_Model_UploadedRuns extends Application_Model_Abstract
{
    const FOLDER_SUFFIX = '_$folder$';
    const FOLDER_SEPARATOR = '/';

    private $_logger;
    private $_debug = false;

    protected $_runsDir;
    protected $_s3;
    protected $_AWSKey;
    protected $_AWSSecretKey;
    protected $_data;
    protected $_s3BucketContent;
    protected $_s3cmdConfig;
    protected $_s3cmdKeys;
    private $_s3StoragePath;
    private $_s3ResultsCache;
    private $_experimentId;
    private $_experimentName;

    protected $_datasets;
    protected $_methods;

    /**
     * Debug section
     */
    private $_notMatchedFiles = array();
    private $_dump = array();

    protected $_analysisMapper;

    public function __construct(array $options = null)
    {
        parent::__construct($options);
        $this->_s3 = new Zend_Service_Amazon_S3($this->getAWSKey(), $this->getAWSSecretKey());
        if (!$this->_s3) {
            throw new Exception('Can not connect to S3 with given keys!');
        }

        if (Zend_Registry::isRegistered('logger')) {
            $this->_logger = Zend_Registry::get('logger');
        } else {
            $this->_logger = new Zend_Log(new Zend_Log_Writer_Null());
        }
    }

    
    private function _updateCache($dir) {
        $cmd = "ls ";
        $s3bucket = 's3://'.$dir;
        $cmd .= $s3bucket;
        $cmd = 'sudo '.$this->_formatS3cmd($cmd);
        $cmd .= " > " . $this->getS3ResultsCache().'.new;';
        exec($cmd, $output, $return);
        if ($return == 0) {
            $cmd .= "mv ".$this->getS3ResultsCache().'.new '.$this->getS3ResultsCache();
            exec($cmd, $output, $return);
            return true;
        } else {
            unlink($this->getS3ResultsCache().'.new');
            return false;
        }
            
        
    }
    
    public function getExperimentId() {
        return $this->_experimentId;
    }

    public function setExperimentId($_experimentId) {
        $this->_experimentId = $_experimentId;
    }

    public function getExperimentName() {
        return $this->_experimentName;
    }

    public function setExperimentName($_experimentName) {
        $this->_experimentName = $_experimentName;
    }

    
    protected function getDirectoryContent ($dir = '', $skipDir = true)
    {
        $fileIterator = new Application_Model_UploadedRuns_Internal_FileIterator($this->getS3ResultsCache());
        $xml = array();
        foreach($fileIterator as $line)
        {
            $item = self::_getObjectInformation($line, $skipDir);
            if ($item) {
                $xml[] = $item;
            }
        }
        
        return $xml;
    }

    protected function getPipeContent ($command, $skipDir = true)
    {
        $file = popen($command, "r");
        if (!$file) {
            throw new Exception("Could not read s3results"); 
        }
        
        $xml = array();
        while(!feof($file)) {
            $line = fgets($file);
            $item = self::_getObjectInformation($line, $skipDir);
            if ($item) {
                $xml[] = $item;
            }
        }
        pclose($file);
        
        return $xml;
    }
    /**
     * Returns object that hold information about line that returned from s3cmd.
     * @param string $line Line with S3cmd data.
     * Object that represent information about LastModified date, Size of object and object Key. 
     */
    private function _getObjectInformation($line, $skipDir = true)
    {
        $s3ResponseRegexp = "#(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2})*\s+(\d+|DIR)\s+s3:\/\/(.+)#";
                
        preg_match($s3ResponseRegexp, $line, $m);
        $date = trim($m[1]);
        $size = $m[2];
        $s3FileName = $m[3];
        if ('DIR'==$size || ''===$date || self::_isFolder($s3FileName)) {
            return null;
        }
        if ($skipDir && self::_isFolder($s3FileName)) {
            return null;
        }
        
        $item = new Application_Model_UploadedRuns_InternalFile();
        $nsaRegexp = $this->_getCommonFileRegex();
        $oscarRegexp = $this->_getCommonFileRegex('OSCAR');
        if (preg_match($nsaRegexp, $s3FileName, $m)) {
            $dataset = $m[1];
            $method = $m[2];
            $runName = $m[3];
            $fileName = $m[4];
            
            $item->Dataset = $dataset;
            $item->Method = $method;
            $item->RunName = $runName;
            $item->FileName = $fileName;
        } elseif (preg_match($oscarRegexp, $s3FileName, $m)) {
            $dataset = $m[1];
            $fileName = $m[2];
            
            $item->Method = 'OSCAR';
            $item->RunName = $dataset;
            $item->Dataset = $dataset;
            $item->FileName = $fileName;
        } else {
            $item->Key = $s3FileName;    
        }
        $item->Size = $size;
        $item->LastModified = strtotime($date);
        unset($m);
        return $item;
    }
    /**
     * Check whenether object name is represent fodler metadata.
     * @param string $fileName Object name in S3 which should be checked.  
     * @return boolean Returns true if object name represent directory metadata in S3; false otherwhise. 
     */
    private function _isFolder($fileName)
    {
        return self::FOLDER_SUFFIX === substr($fileName, -strlen(self::FOLDER_SUFFIX));
    }
    /**
     * Get OSCAR uploaded result data 
     */
    public function getOscarData ()
    {
        $files = array(
            'OSCAR' => array()
        );
        
        $fileIterator = new Application_Model_UploadedRuns_Internal_FileIterator($this->getS3ResultsCache());
        
        foreach ($fileIterator as $line) {
            $elem = self::_getObjectInformation($line);
            if (!$elem->Key && $elem->Method == 'OSCAR') {
                $method = $elem->Method;
                $dataset = $elem->Dataset;
                if (!isset($files[$method][$dataset])) {
                    $files[$method][$dataset] = array();
                }
                $runsData = self::_getSparseMatrixItem2D($files, $method, $dataset);
                
                if (!$runsData) {
                    $runsData = array(
                        's3_files_count' => 0,
                        'size' => '',
                        'last_modified' => '',
                        'oracle_files_count' => 0
                    );
                }
                
                $runsData['s3_files_count'] = $runsData['s3_files_count'] + 1;
                $runsData['size'] = number_format(intval($elem->Size));
                $runsData['last_modified'] = $elem->LastModified;
                
                $files = self::_setSparseMatrixItem2D($files, $method, $dataset, $runsData);
            }
        }
        return $files;
    }

    /**
     * Get the list of objects on the selected s3 bucket
     * @return array list of the selected bucket content
     */
    private function _getS3Content()
    {
        if ($this->_s3BucketContent) {
            return $this->_s3BucketContent;
        }

        $this->_s3BucketContent = $this->getDirectoryContent();

        return $this->_s3BucketContent;
    }
    /**
     * Get the list of objects on the selected s3 bucket
     * @return array list of the selected bucket content
     */
    private function _getNSAS3Content($dataset, $method)
    {
        $nsaData = $this->getPipeContent('cat '.$this->getS3ResultsCache().' | grep "'.$dataset.'/'.$method.'"');
        $results = array();
        $regexp = $this->_getCommonFileRegex();
        foreach($nsaData as $elem) {
            if (preg_match($regexp, $elem->Key, $m)) {
                $dataset = $m[1];
                $method = $m[2];
                if ($dataset == $m[1] && $method == $m[2]) {
                    $results[] = $elem;
                } 
            }
        }
        return $results;
    }
    public function getData ()
    {
        $db = Zend_Registry::get('oracle_adapter');
        $statement = $db->query(<<<EOL
SELECT  method_abbr,
        source_abbr, 
        (SELECT COUNT(*) 
        FROM ANALYSIS_REF t2 
        WHERE t1.method_id=t2.method_id)    AS files_count
FROM    source_ref, 
        method_ref t1
EOL
);
        $files = array();
        while ($row = $statement->fetch()) {
            $methodAbbr = $row['METHOD_ABBR'];
            $sourceAbbr = $row['SOURCE_ABBR'];
            $filesCount = $row['FILES_COUNT'];
            
            $runsData = array(
                        's3_files_count' => 0,
                        'size' => '',
                        'last_modified' => '',
                        'oracle_files_count' => $filesCount
                    );
                    
            $files = self::_setSparseMatrixItem2D($files, $methodAbbr, $sourceAbbr, $runsData);
        }
        
        $fileIterator = new Application_Model_UploadedRuns_Internal_FileIterator($this->getS3ResultsCache());
        $methodMapper = new Application_Model_MethodMapper();
        $methodsCache = array();
        foreach ($fileIterator as $line) {
            $elem = self::_getObjectInformation($line, $skipDir);
            if ((!$elem->Key)
                && ($elem->Method != 'OSCAR')
            ) {
                $dataset = $elem->Dataset;
                $method = $elem->Method;
                $runName = $elem->RunName;
                $runsData = self::_getSparseMatrixItem2D($files, $method, $dataset);
                if (!$runsData) {
                    continue;
                }
                if (!isset($methodsCache[$method])) {
                    $methodEntity = new Application_Model_Method();
                    $methodMapper->findByAbbr($method, $methodEntity);
                    $methodsCache[$method] = $methodEntity;
                } else {
                    $methodEntity = $methodsCache[$method];
                }
                if (!fnmatch($methodEntity->getFileNameFormat(), $elem->FileName)) {
                    continue;
                } 
                
                $runsData['s3_files_count'] = $runsData['s3_files_count'] + 1;
                $runsData['size'] = number_format(intval($elem->Size));
                $runsData['last_modified'] = $elem->LastModified;
                
                $files = self::_setSparseMatrixItem2D($files, $method, $dataset, $runsData);
            } else {
                $this->setNotMatchedFiles($elem->Key);
            }
            unset($elem);
        }
        return $files;
    }

    /**
     * Create regexp for identifying correct file name belonging to method run
     * @param string method name (false for any method)
     * @param string dataset name (false for any dataset)
     * @return string regular expression for file identification
     */
    private function _getCommonFileRegex($method = false, $dataset = false)
    {
        $s3HomeFolder = $this->getS3StoragePath() ? $this->getS3StoragePath() . '/' : '';
        $bucket = $this->getRunsDir();
        if ('OSCAR' === $method) {
            return "#^".$bucket.'/'.$s3HomeFolder.$this->getExperimentName().'/'."([^/]+)/OSCAR/([^/]+)$#";
        }
        
        if (false === $method) {
            $method = '([^/]+)';
        }
        if (false === $dataset) {
            $method = '([^/]+)';
        }
        $regexp = "#^".$bucket.'/'.$s3HomeFolder.$this->getExperimentName().'/'.$method."/([^/]+)/results/([^/]+)/([^/]+)$#";
        return $regexp;
    }
    
    /**
     * Performs building data for NSA experiment.
     * @param string name of method to filter data by method name
     * @param string name of dataset to filter data by dataset
     */
    private function _getDataInternal($methodFilter = false, $datasetFilter = false)
    {
        //var_dump("Start getDataInternal: ", memory_get_usage(), "<br/>");
        // first let's run through all files and associate them into array.
        $files = array();
        $res = array();
        $regexp = $this->_getCommonFileRegex();
        $notMatchedList = array();

        $fileIterator = new Application_Model_UploadedRuns_Internal_FileIterator($this->getS3ResultsCache());

        foreach ($fileIterator as $line) {
            $elem = self::_getObjectInformation($line, $skipDir);
            if ((!$elem->Key)
                && ($elem->Method != 'OSCAR')
                && (false === $methodFilter || $elem->Method == $methodFilter)
                && (false === $datasetFilter || $elem->Dataset == $datasetFilter)
            ) {
                $dataset = $elem->Dataset;
                $method = $elem->Method;
                $runName = $elem->RunName;
                $fileName = $elem->FileName;
                
                $fileItem = array('file'=>$fileName,
                                  'date'=>$elem->LastModified,
                                  'size'=>number_format(intval($elem->Size))
                            );
                
                $filesArray = self::_getSparseMatrixItem3D($files, $method, $dataset, $runName);
                if (!$filesArray) {
                    $filesArray = array();
                } 
                $filesArray[] = $fileItem;
                $files = self::_setSparseMatrixItem3D($files, $method, $dataset, $runName, $filesArray);
            } else {
                $this->setNotMatchedFiles($elem->Key);
            }
            unset($elem);
        }

        //var_dump("S3 files processed: ", memory_get_usage(), "<br/>");
        // now we are building an array of runs.
        if ($this->_analysisMapper == null) {
            $this->_analysisMapper = new Application_Model_AnalysisMapper();
        }
        $res = array();
        foreach ($files as $method=>$methodsRunArray) {
            $methodResults = self::_buildMethodRunsArray($method, $methodsRunArray);
            $methodResults = $this->_findMissingFiles($method, $methodResults);
            $res[$method] = $methodResults;
        }
        
            //var_dump("Runs array builded for method {$method}: ", memory_get_usage(), "<br/>");
/*            $singleMethodFiles = $this->_mergeMethodRunsWithOracleData($method, $methodResults);
            //var_dump("data merged for for method {$method}: ", memory_get_usage(), "<br/>");
            $res[$method] = $methodResults;
            
            if (!isset ($res[$method])) {
                continue;
            }

            foreach ($methodResults as $dataset=>$datasetRuns) {
                foreach ($singleMethodFiles as $runName => $files) {
                    if(isset ($datasetRuns[$runName])) {
                        continue;
                    }
                    $runObject = new Application_Model_Run(
                        array(
                        // date is not passed, so it will be determined by one of the files.
                            'dataset' => $dataset,
                            'method'  => $method,
                            'name'    => $runName
                        ));
                    $runFiles = array();
                    foreach ($files as $file) {
                        $runFiles[] = array('file'=>$runObject->convertFile($file),
                                            'date'=>'',
                                            'size'=>'');
                    }
                    $runObject->setFiles($runFiles);

                    $res = self::_setSparseMatrixItem3D($res, $method, $dataset, $runName, $runObject);
                }
            }
            unset($files[$method]);
        }
        unset($files);*/
        
        //var_dump("Finish: ", memory_get_usage(), "<br/>");
        $cntFiles = count($res, COUNT_RECURSIVE);
        $cntMethods = count($res);
        $this->_logger->debug('After merging with Oracle data: '.$cntFiles.' files in '.$cntMethods.' methods.');
        return $res;
    }
    private function _getSparseMatrixItem3D($container, $index1, $index2, $index3)
    {
        if (!isset($container[$index1])) {
            $container[$index1] = array();
        }
        if (!isset($res[$index1][$index2])) {
            $res[$index1][$index2] = array();
        }
        if (!isset($container[$index1][$index2][$index3]))
            return null;
        return $container[$index1][$index2][$index3]; 
    }
    private function _setSparseMatrixItem3D($container, $index1, $index2, $index3, $data)
    {
        if (!isset($container[$index1])) {
            $container[$index1] = array();
        }
        if (!isset($res[$index1][$index2])) {
            $res[$index1][$index2] = array();
        }

        $container[$index1][$index2][$index3] = $data; 
        return $container;
    }
    private function _getSparseMatrixItem2D($container, $index1, $index2)
    {
        if (!isset($container[$index1])) {
            $container[$index1] = array();
        }
        if (!isset($container[$index1][$index2]))
            return null;
        return $container[$index1][$index2]; 
    }
    private function _setSparseMatrixItem2D($container, $index1, $index2, $data)
    {
        if (!isset($container[$index1])) {
            $container[$index1] = array();
        }

        $container[$index1][$index2] = $data; 
        return $container;
    }
    /**
     * Transform 2-dimension sparse matrix of raw data to 2-dimension sparse matrix of method runs.
     * @param multitype: $method Method name to which belongs dataset runs list.
     * @param multitype: $datasetRunsList Sparse 2-dimension matrix with indexes by (dataset, run) and contains array of files as elements.
     * @return multitype: Sparse 2-dimension matrix with indexes by (dataset, run) and contains Application_Model_Run as elements.
     */
    private function _buildMethodRunsArray($method, $datasetRunsList)
    {
        $result = array();
        foreach ($datasetRunsList as $dataset=>$_m2) {
            foreach ($_m2 as $runName=>$filesList) {
                $runObject = new Application_Model_Run(array(
                        // date is not passed, so it will be determined by one of the files.
                                                    'dataset' => $dataset,
                                                    'method'  => $method,
                                                    'name'    => $runName,
                                                    'files'   => $filesList
                                                ));
                $result = self::_setSparseMatrixItem2D($result, $dataset, $runName, $runObject);
            }
        }
        return $result;
    }
    /**
     * Find missing files for the run list and remove extra files. Function modifies the parameter.
     * @param array $allRunsData run list
     * @return array list of missing files
     */
    protected function mergeRunsWithOracleData (array &$allRunsData)
    {
        if ($this->_analysisMapper == null) {
            $this->_analysisMapper = new Application_Model_AnalysisMapper();
        }
        $files = array();
        foreach ($allRunsData as $method=>$methodsRunArray) {
            $singleMethodFiles = self::_mergeMethodRunsWithOracleData($method, $methodsRunArray);
            
            $files[$method] = $singleMethodFiles;
        }

        return array( 'sampleFiles' => $files, 'runs' => $allRunsData);
    }
    
    private function _findMissingFiles($method, $methodsRunArray) {
        $methodFiles = $this->_analysisMapper->findFilesByMethod(array($method=>''));
        $singleMethodFiles = count($methodFiles) == 0 ? array() : $methodFiles[$method];
        
        $availableFiles = array();
        foreach ($methodsRunArray as $dataset => $runData) {
            $analysisRun = new Application_Model_Run();
            $analysisRun->setDataset($dataset);
            foreach ($singleMethodFiles as $key => $method) {
                $singleMethodFiles[$key] = $analysisRun->convertFile($singleMethodFiles[$key]);
            }
            $fileAnalysisMap = array_flip($singleMethodFiles);
            foreach ($runData as $run) {
                foreach ($run->getFiles() as $file) {
                    if (isset($fileAnalysisMap[$file['file']])) {
                        $availableFiles[] = $fileAnalysisMap[$file['file']];
                    }   
                }
            }
            $missedAnalysis = array_diff(array_values($fileAnalysisMap), $availableFiles);
            $missed = array();
            foreach ($missedAnalysis as $id) {
                $missed[] = $singleMethodFiles[$id]; 
            }
            $methodsRunArray[$dataset]['missed'] = $missed;
            
        }
        
        return $methodsRunArray;
    }
    
    private function _mergeMethodRunsWithOracleData($method, $methodsRunArray)
    {
        $methodFiles = $this->_analysisMapper->findFilesByMethod(array($method=>''));
        $singleMethodFiles = count($methodFiles) == 0 ? array() : $methodFiles[$method];
        $fileAnalysisMap = array_flip($singleMethodFiles);
        // spliting by datasets
        foreach ($methodsRunArray as $dataset => $datasetsRunData) {
            foreach ($datasetsRunData as $run) {                
                $method = $run->getMethod();
                $runName = $run->getName();
                $files = $run->getFiles();
                foreach ($files as $file) {
                    if (isset($fileAnalysisMap[$file['file']])) {
                        $file['analysis_id'] = $singleMethodFiles[$file['file']];
                    }
                }
                if (isset($singleMethodFiles[$runName])) {
                    $oracleRunData = $singleMethodFiles[$runName];
                    $sampleFile = $oracleRunData[0];
                    $run->setDatasetTemplate($sampleFile);
                    $runFiles = $run->getFiles();
                    
                    $filterData = self::_foundMissingFiles($oracleRunData, $run);
                    $missingRunFiles = $filterData['missing'];
                    $runFiles = $filterData['files'];
                    
                    //$files[$method][$runName] = $missingRunFiles;
                    $singleMethodFiles[$runName] = $missingRunFiles;
                    // now lets put files left to run as missed ones
                    foreach ($missingRunFiles as $mFile) {
                        $runFiles[] = array('file'=>$run->convertFile($mFile),
                                            'date'=>'',
                                            'size'=>'');
                    }
                    $run->setFiles($runFiles);
                }
            }
        }
        return $singleMethodFiles;
    }
    private function _foundMissingFiles($oracleRunData, $run)
    {
        $runFiles = $run->getFiles();
        foreach ($runFiles as $id=>$file) {
            $fullFileName = $run->unConvertFile($file['file']);
            $file['file'] = $fullFileName;
            // if file exists both on Oracle and S3, then remove it from list.
            if (in_array($fullFileName, $oracleRunData)) {
                $fileFound = array_search($fullFileName, $oracleRunData);
                unset($oracleRunData[$fileFound]);
            } else {
                //Remove invalid files from array (logs, drug lists, etc)
                unset($runFiles[$id]);
            }
        }
        return array(
            'files' => $runFiles,
            'missing' => $oracleRunData
        );
    }
    public function setAWSKey ($key = '')
    {
        $this->_AWSKey = (string) $key;
        return $this;
    }

    public function getAWSKey ()
    {
        if (!$this->_AWSKey) throw new Exception("AWS Key not set!");

        return $this->_AWSKey;
    }

    public function setAWSSecretKey ($key)
    {
        $this->_AWSSecretKey = (string) $key;
        return $this;
    }

    public function getAWSSecretKey ()
    {
        if (!$this->_AWSSecretKey) {
            throw new Exception("AWS secret Key not set!");
        }

        return $this->_AWSSecretKey;
    }

    public function setS3cmdConfig ($file)
    {
        $this->_s3cmdConfig = (string) $file;
        return $this;
    }

    public function getS3cmdConfig ()
    {
        return $this->_s3cmdConfig;
    }

    public function setS3cmdKeys ($keys)
    {
        $this->_s3cmdKeys = (string) $keys;
        return $this;
    }

    public function getS3cmdKeys ()
    {
        return $this->_s3cmdKeys;
    }

    public function setS3ResultsCache ($value)
    {
        $this->_s3ResultsCache = (string) $value;
        return $this;
    }

    public function getS3ResultsCache ()
    {
        return $this->_s3ResultsCache;
    }

    /**
     * Setter for runs directory parameter.
     * @param string $dir should be an existing directory.
     * @return Application_Model_UploadedRuns to build chains of methods
     */
    public function setRunsDir ($dir)
    {
        $dir = rtrim($dir, '/');
        if (substr($dir, 0, 5) == 's3://') {
            $dir = substr($dir, 5);
        }
/*        $cmd = $this->_formatS3cmd('ls');
        exec($cmd, $output);
        $isFound = false;
        foreach ($output as $bucket) {
            list($tmp, $bucket) = explode('s3://', $bucket);
            if ($bucket && $dir === $bucket) {
                $isFound = true;
                break;
            }
        }*/
        $isFound = $this->_updateCache($dir);
        if (!$isFound) {
            throw new Exception('S3 bucket can not be found.');
        }
        $this->_runsDir = (string) $dir;
        return $this;
    }

    /**
     * Get path to runs directory
     * @return string path to runs directory
     */
    public function getRunsDir ()
    {
        if (!$this->_runsDir) {
            throw new Exception ("RunsDir param not set!");
        }
        //return 'omop-results';
        return $this->_runsDir;
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
        $this->_s3StoragePath = '';
        return $this->_s3StoragePath;
    }

    /**
     * Update list of files not passed the Method belonging filter
     * @param string file name
     * @param boolean whether to clean the existing array
     */
    public function setNotMatchedFiles($file, $clean = false)
    {
        if (!$this->_debug) {
            return;
        }
        if ($clean) {
            $this->_notMatchedFiles = array();
        }
        $this->_notMatchedFiles[] = $file;
    }

    public function getNotMatchedFiles($separator = false)
    {
        if(!$separator) {
            return $this->_notMatchedFiles;
        }
        return implode($separator, $this->_notMatchedFiles);
    }

    public function setDebug($value)
    {
        $this->_debug = $value;
    }

    public function setDump($obj)
    {
        if (!$this->_debug) {
            return;
        }
        if (is_array($obj)) {
            foreach ($obj as $element) {
                $this->setDump($element);
            }
        } else {
            $this->_dump[] = $obj;
        }
    }

    public function getDump()
    {
        return $this->_dump;
    }

    /**
     * Get folder name from its S3 full path
     * @param string full path to the folder
     * @return folder name
     */
    private function _getS3FolderName($fullPath)
    {
        $folder = rtrim($fullPath, self::FOLDER_SEPARATOR);
        $parts = explode(self::FOLDER_SEPARATOR, $folder);
        $folder = array_pop($parts);
        if (self::_isFolder($folder)) {
            $lengthSuffix = strlen(self::FOLDER_SUFFIX);
            $folder = substr($folder, 0, strlen($folder)-$lengthSuffix);
        }
        return $folder;
    }

    private function _loadMethods()
    {
        $methodsMapper = new Application_Model_MethodMapper();
        $methods = $methodsMapper->fetchPairs();
        if (!in_array('OSCAR', $methods)) {
            $methods[] = 'OSCAR';
        }
        sort($methods);
        $this->_methods = $methods;
        return $this;
    }

    public function getMethods()
    {
        if (!$this->_methods) {
            $this->_loadMethods();
        }
        return $this->_methods;
    }

    private function _loadDatasets()
    {
        $sourceModel = new Application_Model_DbTable_Source();
        $sources = $sourceModel->getSources();

        $datasets = array();
        foreach ($sources as $dbT) {
            $datasets[$dbT['SOURCE_ID']] = $dbT['SOURCE_ABBR'];
        }
        $this->_datasets = $datasets;
        return $this;
    }

    public function getDatasets()
    {
        if (!$this->_datasets) {
            $this->_loadDatasets();
        }
        return $this->_datasets;
    }

    public function getMicrotime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * Format s3cmd with required parameters
     * @param string essential part of the command (command and parameters)
     */
    protected function _formatS3cmd($command)
    {
        $cfgFile = $this->getS3cmdConfig() ? ' -c ' . $this->getS3cmdConfig() : '';
        $cfgKeys = $this->getS3cmdKeys();
        $s3cmdParams = implode(' ', array($cfgFile, $cfgKeys));

        return 's3cmd ' . $s3cmdParams . ' ' . $command;
    }

    public function getRunList($method, $dataset)
    {
        $data = array();
        if ('OSCAR' === $method) {
            return $data;
        }

        $runs = $this->_getDataInternal($method, $dataset);
        foreach ($runs[$method][$dataset] as $name => $run) {
            if ($name == 'missed')  {
                if (count($run) != 0)
                    $data[] = array(
                        'name' => 'Missed files',
                        'notUploaded' => 1
                        );
            }
            else
                $data[] = array(
                    'name' => $run->getName(),
                    'notUploaded' => $run->hasNotUploadedFiles() ? 1 : 0
                );
        }
        uasort($data , array($this, '_compareRuns'));        
        return array_values($data);
    }

    /**
     * Compare two run records to define sorting order
     * @param array first run to compare
     * @param array second run to compare
     * @return integer comparison result
     */
    private function _compareRuns($a, $b)
    {
        /**
         * Uploaded runs go first
         */
        if ($a['notUploaded'] > $b['notUploaded']) {
            return -1;
        } elseif ($b['notUploaded'] > $a['notUploaded']) {
            return 1;
        }
        /**
         * Compare run names counting on numeric suffix
         */
        preg_match('/(.*)_(\d+)$/', $a['name'], $ma);
        preg_match('/(.*)_(\d+)$/', $b['name'], $mb);
        $x = strnatcasecmp($ma[0],$mb[0]);
        if ($x != 0)
            return $x;
        if (intval($ma[1]) < intval($mb[1]))
            return -1;
        if (intval($ma[1]) > intval($mb[1]))
            return 1;
        return 0;
    }
    public function getRunDetails($methodName, $datasetName, $runName)
    {
        require_once('AWSSDKforPHP/sdk.class.php');
        require_once('AWSSDKforPHP/lib/requestcore/requestcore.class.php');
        require_once('AWSSDKforPHP/utilities/utilities.class.php');
        require_once('AWSSDKforPHP/utilities/request.class.php');
        require_once('AWSSDKforPHP/utilities/array.class.php');
        require_once('AWSSDKforPHP/utilities/simplexml.class.php');
        require_once('AWSSDKforPHP/utilities/batchrequest.class.php');
        require_once('AWSSDKforPHP/utilities/response.class.php');
        
        require_once('AWSSDKforPHP/services/s3.class.php');
        $data = array();
        if ('OSCAR' === $methodName) {
            return $data;
        }

        $runs = $this->_getDataInternal($methodName, $datasetName);
        $isFound = false;
        foreach ($runs[$methodName][$datasetName] as $name => $run) {
            if ($name == 'missed') {
                if ($runName == 'Missed files') {
                    foreach ($run as $file) {
                        $data[] = array(
                            'notUploaded' => 1,
                            'name' => $file
                        );
                    }
                    return $data;
                }
            } else {
                if ($run->getName() === $runName) {
                    $isFound = true;
                    break;
                }
            }
        }
        if (!$isFound) {
            return $data;
        }

        $cfgMapper = new Application_Model_SiteConfigMapper();
        $config = $cfgMapper->getConfig();
        list($timeFormat) = explode(' ', $config->getDateFormat());
        
        $s3 = new AmazonS3($this->getAWSKey(), $this->getAWSSecretKey());
        foreach ($run->getFiles() as $file) {
            $s3HomeFolder = $this->getS3StoragePath() 
                ? $this->getS3StoragePath() . '/' 
                : '';
            $bucket = $this->getRunsDir();
            $fileName = $file['file'];
            $resultsDir = $s3HomeFolder.$this->getExperimentName().'/'.$datasetName.'/'.$methodName.'/results/';
            $objectPath = $resultsDir.$runName.'/'.$fileName;
            $s3->use_ssl = true;
            $objectStatus = $s3->if_object_exists($bucket, $objectPath);
            if ($objectStatus !== false) {
                if ($objectStatus === null) {
                    $permissionSchema = array( 
                        'id' => AmazonS3::USERS_AUTH,
                        'permission' => AmazonS3::GRANT_READ 
                    );
                    $s3->set_object_acl($bucket, $objectPath, $permissionSchema);
                }
                $s3->update_object($bucket, $objectPath, array(
                    'headers' => array(
                        'Content-Type' => 'plain/text',
                        'Content-Disposition' => 'attachment; filename="'.$fileName.'"'
                    )
                ));
                $url = $s3->get_object_url($bucket, $objectPath, '15 minutes');
            } else {
                $url = '';
                //$url = $s3->get_object_headers($bucket, $objectPath);
                
                //$url = $s3->get_object_url($bucket, $objectPath, time() + (15 * 60));
            }
            $date = $file['date'] 
                ? strftime($timeFormat, $file['date']) 
                : false;
            $data[] = array(
                'name' => $fileName,
                'date' => $date,
                'size' => $file['size'],
                'url' => $url
            );
        }
        return $data;
    }

    public function downloadRunResults($methodName, $datasetName, $runName)
    {
        require_once('AWSSDKforPHP/sdk.class.php');
        require_once('AWSSDKforPHP/lib/requestcore/requestcore.class.php');
        require_once('AWSSDKforPHP/utilities/utilities.class.php');
        require_once('AWSSDKforPHP/utilities/request.class.php');
        require_once('AWSSDKforPHP/utilities/array.class.php');
        require_once('AWSSDKforPHP/utilities/simplexml.class.php');
        require_once('AWSSDKforPHP/utilities/batchrequest.class.php');
        require_once('AWSSDKforPHP/utilities/response.class.php');
        
        require_once('AWSSDKforPHP/services/s3.class.php');
        $data = array();
        if ('OSCAR' === $methodName) {
            return $data;
        }

        $s3 = new AmazonS3($this->getAWSKey(), $this->getAWSSecretKey());
        $run_objects = $s3->get_object_list($this->getRunsDir(),
                array('prefix' => $this->getExperimentName().
                    self::FOLDER_SEPARATOR.$datasetName.
                    self::FOLDER_SEPARATOR.$methodName.
                    self::FOLDER_SEPARATOR.'results'.
                    self::FOLDER_SEPARATOR.$runName.self::FOLDER_SEPARATOR )
        );
        if ($run_objects) {
            $zip = new ZipArchive();
            $tempfile = tempnam(sys_get_temp_dir(), 'resutls');
            if (!is_dir($tempfile.'_dir'))
                mkdir($tempfile.'_dir');
            $tmpfolder = $tempfile.'_dir';
            $zip->open($tempfile, ZipArchive::OVERWRITE);
            foreach ($run_objects as $object) {
                $s3->get_object($this->getRunsDir(), $object, array('fileDownload' => $tmpfolder.DIRECTORY_SEPARATOR.basename($object)));
                $zip->addFile($tmpfolder.DIRECTORY_SEPARATOR.basename($object), $run.DIRECTORY_SEPARATOR.basename($object));
            }
            $zip->close();
            $this->removeDirectory($tmpfolder);
            return $tempfile;
        }        
        return $data;
    }
    
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
    
}

final class Application_Model_UploadedRuns_Internal_FileIterator implements Iterator
{
    private $handle;
    private $key;
    private $value;
    
    public function __construct($fileName) {
        $this->handle = fopen($fileName, 'r');
        $this->key = 0;
    }

    function rewind() {
        $this->key = 0;
        rewind($this->handle);
    }

    function current() {
        return $this->value;
    }

    function key() {
        return $this->key;
    }

    function next() {
        $this->value = fgets($this->handle);
        $this->key++;
    }

    function valid() {
        return !feof($this->handle);
    }
    function __destruct() {
       fclose($this->handle);
    }
    
}
final class Application_Model_UploadedRuns_InternalFile
{
    var $Key;
    var $LastModified;
    var $Dataset;
    var $Method;
    var $RunName;
    var $FileName;
}
