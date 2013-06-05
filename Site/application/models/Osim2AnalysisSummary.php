<?php
/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    14 March 2011

    Object that represents OSIM2 summary probablilistic.

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

==============================================================================*/

final class Application_Model_Osim2AnalysisSummary
{
    private $_id;
    private $_oldId;
    private $_name;
    private $_description;
    private $_createdBy;
    private $_created;
    private $_updatedBy;
    private $_updated;
    
    /**
     * Setup mapper which will be used for data retreival and
     * storing operations 
     * 
     * @param $mapper Mapper object which will be used for data
     * retreival and storing operations.
     * 
     * @return Application_Model_Osim2AnalysisSummary Current object with modified property.
     */
    public function setMapper($mapper)
    {
        if (is_string($mapper)) {
            $mapper = new $mapper();
        }
        $this->_mapper = $mapper;
        return $this;
    }

    /**
     * Gets mapper object which is used for data retrieval
     * and storing operations
     * 
     * @return Mapper object which is used for data retrieval
     * and storing ooperations.
     */
    public function getMapper()
    {
        if (null === $this->_mapper) {
            $this->_mapper = new Application_Model_Osim2Mapper();
        }
        return $this->_mapper;
    }

    /**
     * Sets new value for Id
     * @param $value New value of Id
     */
    public function setId($value)
    {
        $this->_id = $value;
        return $this;
    }
    
    /**
     * Gets current value of Id
     * @return Returns value of Id
     */
    public function getId()
    {
        return $this->_id;
    }
    
    /**
     * Sets new value for OldId
     * @param $value New value of OldId
     */
    public function setOldId($value)
    {
        $this->_oldId = $value;
        return $this;
    }
    
    /**
     * Gets current value of OldId
     * @return Returns value of OldId
     */
    public function getOldId()
    {
        return $this->_oldId;
    }
    
    /**
     * Sets new value for Name
     * @param $value New value of Name
     */
    public function setName($value)
    {
        $this->_name = $value;
        return $this;
    }
    
    /**
     * Gets current value of Name
     * @return Returns value of Name
     */
    public function getName()
    {
        return $this->_name;
    }
    
    /**
     * Sets new value for Description
     * @param $value New value of Description
     */
    public function setDescription($value)
    {
        $this->_description = $value;
        return $this;
    }
    
    /**
     * Gets current value of Description
     * @return Returns value of Description
     */
    public function getDescription()
    {
        return $this->_description;
    }
    
    /**
     * Sets new value for CreatedBy
     * @param $value New value of CreatedBy
     */
    public function setCreatedBy($value)
    {
        $this->_createdBy = $value;
        return $this;
    }
    
    /**
     * Gets current value of CreatedBy
     * @return Returns value of CreatedBy
     */
    public function getCreatedBy()
    {
        return $this->_createdBy;
    }
    
    /**
     * Sets new value for Created
     * @param $value New value of Created
     */
    public function setCreated($value)
    {
        $this->_created = $value;
        return $this;
    }
    
    /**
     * Gets current value of Created
     * @return Returns value of Created
     */
    public function getCreated()
    {
        return $this->_created;
    }
    
    /**
     * Sets new value for UpdatedBy
     * @param $value New value of UpdatedBy
     */
    public function setUpdatedBy($value)
    {
        $this->_updatedBy = $value;
        return $this;
    }
    
    /**
     * Gets current value of UpdatedBy
     * @return Returns value of UpdatedBy
     */
    public function getUpdatedBy()
    {
        return $this->_updatedBy;
    }
    
    /**
     * Sets new value for Updated
     * @param $value New value of Updated
     */
    public function setUpdated($value)
    {
        $this->_updated = $value;
        return $this;
    }
    
    /**
     * Gets current value of Updated
     * @return Returns value of Updated
     */
    public function getUpdated()
    {
        return $this->_updated;
    }
    
    /**
     * Perform loading of object properties by unique identifier 
     * of object in the database.
     * 
     * @param number $id Unique identifier of Analysis Summary which 
     * should be loaded into the object.   
     */
    public function find($id)
    {
        $mapper = $this->getMapper();
        return $mapper->findSummary($id, $this);
    }
    
    /**
     * Perform loading of object properties by name which 
     * object has in the database.
     * 
     * @param string $name Name of Analysis Summary which  
     * should be loaded into the object.   
     */
    public function findByName($name)
    {
        $mapper = $this->getMapper();
        return $mapper->findSummaryByName($name, $this);
    }
    
    /**
     * Saves current object to underlying datastore.
     */
    public function save()
    {
        $mapper = $this->getMapper();
        return $mapper->saveSummary($this);
    }
    /**
     * Insert current object to underlying datastore.
     */
    public function insert()
    {
        $mapper = $this->getMapper();
        return $mapper->insertSummary($this);
    }
    /**
     * Import data from file to the Oracle database.
     * @param string $fileName Name of uploaded file.
     * @return string Error message, or empty string if launch suceeded.
     */
    public function extractDefinition($fileName)
    {
        $location = $this->getCacheLocation();
        exec("unzip {$fileName} -d {$location}");
        unlink($fileName);
        $message = self::validate($location);
        if (!$message) {
            $schema = self::getRequredSchema();
            $mapper = $this->getMapper();
            $id = $this->getId();
            foreach($schema as $table => $columns) {
                $fullPath = $location.'/'.$table.'.csv';
                $mapper->loadProbabilityTable($id, $table, $fullPath);
            }
        }
        $this->cleanDefinition();
        return $message;
    }
    
    /**
     * Start import data from large fiels uploaded by FTP.
     * @param string $fileName Name of uploaded file.
     * @return string Error message, or empty string if launch suceeded.
     */
    public function extractDefinitionFtp($fileName)
    {
        $user = Membership::get_current_user();
        $mail = $user->email;
        
        $fileName = pathinfo($fileName,PATHINFO_BASENAME);
        $message = '';
        $id = $this->getId();
        
        $awsConfig = Zend_Registry::get('awsConfig');
        $awsUserId = $awsConfig->aws_user_id;
        
        $osim2Config = self::getConfig();
        $username = $osim2Config->oracle_username;
        $password = $osim2Config->oracle_password;
        $tns = $osim2Config->oracle_tns;
        
        $osim2Config = self::getConfig();
        $path = $osim2Config->loader_working_dir;
        $db_type= 'oracle';
        $definition_content = <<<EOD
TYPE=$db_type
DATE_FILE=$fileName
SET_ID=$id
DATASOURCE=$tns
DB_USERNAME=$username
DB_PASSWORD=$password
MAIL=$mail
AWS_USER_ID=$awsUserId
EOD;
        $fileName = $path.'/'.$fileName.'.load';
        file_put_contents($fileName, $definition_content);
        chmod($fileName, 0644);
        return $message;
    }
    
    /**
     * Start OSIM2 generation process
     * 
     * @param string $dbEngine
     * DB Engine agains which should be started generation process.
     * @param array $serverData
     * Map which contains following keys 
     * 'datasource' - this is name of Oracle server. Could be TNS name or ServerHost/DbName pair.
     * 'username' - Name of database user.
     * and
     * 'password' - Password for database user.   
     * @param number $personsCount 
     * Number of persons which will be generated data for
     * @param string $signalDefinition 
     * Path relative to working folder which contains signal definition
     * @param number $startFrom
     * Numeric identifier which will be appended to each person identifier.
     */
    public function startGenerationProcess($dbEngine, $serverData, $personsCount, $signalDefinition, $startFrom = 0)
    {
        $user = Membership::get_current_user();
        $mail = $user->email;
        
        $id = $this->getId();
        $awsConfig = Zend_Registry::get('awsConfig');
        $osim2Config = self::getConfig();
        if ($serverData) {
        	$username = $serverData['username'];
	        $password = $serverData['password'];
	        $datasource = $serverData['datasource'];
        } else {
	        $username = $osim2Config->oracle_username;
	        $password = $osim2Config->oracle_password;
	        $datasource = $osim2Config->oracle_tns;
        }
        $osim2Config = self::getConfig();
        $path = $osim2Config->loader_working_dir;
        $awsUserId = $awsConfig->aws_user_id;
        $definition_content = <<<EOD
TYPE=$dbEngine
SET_ID=$id
PERSONS_COUNT=$personsCount
START_FROM=$startFrom
DATASOURCE=$datasource
DB_USERNAME=$username
DB_PASSWORD=$password
SIGNAL=$signalDefinition
MAIL=$mail
AWS_USER_ID=$awsUserId
EOD;
        $fileName = $path.'/dataset'.$id.'.generation';
        file_put_contents($fileName, $definition_content);
        chmod($fileName, 0644);
    }
    /**
     * Validate that files in the specified location match to OSIM2 
     * probabilites tables schema.
     * 
     * @param string $location 
     * Full path to folder where stored OSIM2 probabilities table.
     * @return string 
     * Error message if location contains invalid dataset, or empty 
     * string if validation of dataset files suceeded.
     */
    public function validate($location)
    {
        require_once('CsvParser.php');
        $requiredFiles = self::getRequredSchema();
        foreach($requiredFiles as $table => $columns) {
            $fileName = strtolower($table);
            $fullPath = $location.'/'.$fileName.'.csv';
            if (!file_exists($fullPath)) {
                return "File $fileName is missing in archive";
            }
            $parser = new CsvParser();
            if (!$parser->load($fullPath)) {
                return "File $fileName is not CSV file";
            }
            if (!$parser->isSymmetric()) {
                return "Number of colums does not match with ".
                    "length of some rows in the file $fileName.";
            }
            $headers = $parser->getHeaders();
            $columnsCount = count($columns);
            if (count($headers) != $columnsCount) {
                return "Number of columns in file $fileName ".
                    "don't match to expected value $columnsCount.";
            }
            for($i=0;$i<count($columns);$i++) {
                $expectedColumn = strtolower($columns[$i]);
                $actualColumn = strtolower($headers[$i]);
                if ($expectedColumn != $actualColumn) {
                    return "Column $expectedColumn expected at place $i ".
                        "in file $fileName. $actualColumn found.";
                }
            }
        }
        return false;
    }
    /**
     * Get statistic about probabilites tables.
     * @return array with descrions of all probabilities table. 
     */
    public function getStatistics()
    {
        $statisticsData = array();
        $schema = self::getRequredSchema();
        foreach($schema as $table => $columns) {
            $rowCount = $this->getTableRowsCount($table);
            $statisticsData[] = array(
                'table' => $table,
                'rowCount' => $rowCount
            );
        }
        return $statisticsData;
    }
    /**
     * Gets list of columns which should be presented in the table.
     * 
     * @param string $tableName 
     * Table name for which should be retreived list of required columns.
     * @return array 
     * Array of columns which should be required in the table.
     */
    public function getTableColumns($tableName)
    {
        $schema = self::getRequredSchema();
        if (!isset($schema[$tableName])) {
            throw new Exception("Table $tableName does not belongs to schema.");
        } 
        return $schema[$tableName];
    }
    /**
     * Returns data from probability table
     * @param $tableName
     */
    public function getTableRows($tableName)
    {
        $mapper = $this->getMapper();
        return $mapper->getProbabilitiesTableData($this->getId(), $tableName);
    }
    /**
     * Get pagintion adapter for probability table that soreted by 
     * specified parameters.
     * 
     * @param $tableName 
     * Name of probability table for which should be create pagination adapter.
     * @param $sortColumn
     * Name of column which should be used for sorting.
     * @param $sortDirection
     * Direction of sorting data before paginate.
     * @return
     * Pagination adapter for table that sorded by specified parameters.
     */
    public function getTablePaginatorAdapter($tableName, $sortColumn, 
        $sortDirection)
    {
        $mapper = $this->getMapper();
        return $mapper->getProbabilitiesTablePaginatorAdapter($this->getId(), 
            $tableName, $sortColumn, $sortDirection
        );
    }
    /**
     * Gets last unique identifier of Summary Set.
     * @return Last unique identifier of Summary Set; 0 if no sumary sets present 
     * in datastore.
     */
    public function lastSummaryId()
    {
        $mapper = $this->getMapper();
        return $mapper->lastSummaryId();
    }
    /**
     * Gets unique identifier of Summary Set that will be insterted in the 
     * database next.
     * 
     * @return Unique identifier of Summary Set that goes immidiately after 
     * last unique identifier of Summary Set already presented in DB; 0 if 
     * no sumary sets present in datastore.
     */
    public function nextSummaryId()
    {
        $mapper = $this->getMapper();
        return $mapper->lastSummaryId() + 1;
    }
    /**
     * Returns pairs which represent summary sets.
     * 
     * @return array of key value pairs which identifies
     * Summary sets.
     */
    public function getPairs()
    {
        $mapper = $this->getMapper();
        return $mapper->fetchSummaryPairs();
    }
    /**
     * Returns array of objects that represent all Summary sets.
     * 
     * @return array of objects that represent all Summary sets
     */
    public function getAll()
    {
        $mapper = $this->getMapper();
        return $mapper->fetchSummaryAll();
    }
    /**
     * Returns list of tables available in this summary set.
     * 
     * @return array of strings that represent names of tables
     * that represent probabilites in the specific summary set.
     */
    public function getAvailableTables()
    {
        $schema = self::getRequredSchema();
        $availableTables = array_keys($schema);
        sort($availableTables);
        return $availableTables;
    }
    /**
     * Change summary set ID and update probability data to new value. 
     *  
     * @param $newId New value for Summary set ID
     */
    public function changeId($newId)
    {
        $mapper = $this->getMapper();
        $oldId = $this->getOldId();
        return $mapper->changeSummaryId($oldId, $newId);
    }
    /**
     * Get list of files that prepared for uploading.
     * @return 
     * Array of filenames that relative to cache directory.   
     * Array will contains only files with zip and tar.gz
     * extensions.
     */
    public function getPreparedFiles()
    {
        $cacheLocation = $this->getCacheLocation();
        $oldDir = getcwd();
        chdir($cacheLocation);
        $zipFiles = glob('*.zip');
        $tarGzFiles = glob('*.tar.gz');
        $result = array_merge($zipFiles, $tarGzFiles);
        chdir($oldDir);
        return $result;
    }
    private function cleanDefinition()
    {
        $location = $this->getCacheLocation();
        exec("rm -Rf {$location}");
    }
    private function getTableRowsCount($tableName)
    {
        $mapper = $this->getMapper();
        return $mapper->countProbabilitiesTable($this->getId(), $tableName);
    }
    private static function getRequredSchema()
    {
        return Application_Model_Osim2Mapper::getSummarySchema();
    }
    private function getCacheLocation()
    {
        $osim2Config = self::getConfig();
        $path = $osim2Config->loader_working_dir;
        if (!$path) {
            throw new Exception('Please set "loader_working_dir" value in the [osim2] section of webrl.conf');
        }
        return $path.'/'.$this->_name.'/';
    }
    private static function getConfig()
    {
        $osim2Config = Zend_Registry::get('osim2Config');
        return $osim2Config;    
    }
}
