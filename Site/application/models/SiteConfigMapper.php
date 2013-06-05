<?php

/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10-Feb-2011

    Site Config Mapper

    (c)2009-2010 Foundation for the National Institutes of Health (FNIH)

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
class Application_Model_SiteConfigMapper
{
    protected $_dbTable;
    
    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Application_Model_DbTable_SiteConfig');
        }
        return $this->_dbTable;
    }
    
    
    public function save(Application_Model_SiteConfig $config)
    {
        $data =  array(
            'admin_email'                =>  $config->getAdminEmail(),
            'replyto_email'              =>  $config->getReplyTo(),
            'time_zone'                  =>  $config->getTimezone(),
            'default_money_limit'        =>  $config->getDefaultMoneyLimit(),
            'default_date_format'        =>  $config->getDateFormat(),
            'default_user_storage_size'  =>  $config->getDefaultUserStorageSize(),
            'methods_path'               =>  $config->getMethodsPath(),
            'methods_instance_path'      =>  $config->getMethodsInstancePath(),
            'results_s3_bucket'          =>  $config->getResultsS3Bucket(),
            'password_expiration_period' =>  $config->getPasswordExpirationPeriod(),
            'strong_passwords_flag'      =>  $config->getStrongPasswordFlag(),
            'lock_instance_flag'         =>  $config->getLockInstanceFlag(),
            'default_user_ebs'           =>  $config->getDefaultUserEbs(),
            'vocabulary_dataset_type_id' =>  $config->getVocabularyDataset()
        );
        $oldConfig = $this->getDbTable()->find(1)->current()->toArray();
        $oldConfig = $this->arrayToString($oldConfig);
        
        $this->getDbTable()->update(
            $data, 
            array('site_config_id = 1')
        );

        $newConfig = $this->getDbTable()->find(1)->current()->toArray();
        $newConfig = $this->arrayToString($newConfig);

        $sm = new SecurityManager();
        $user = Membership::get_current_user();
        $sm->generate_security_event(
            SecurityManager::ISSUE_TYPE_SITE_CONFIGURATION_CHANGED,
            "Site configuration changed. Old configuration:".$oldConfig.' New Configuration:'.$newConfig, $user->user_id, null, null, null);
        
    }
    
    public function find($id, Application_Model_SiteConfig $config)
    {
        $resultSet = $this->getDbTable()->find($id);
        
        if (0 == count($resultSet))
            return;
        
        $row = $resultSet->current();
        $config->setAdminEmail($row->admin_email)
                ->setReplyTo($row->replyto_email)
                ->setTimezone($row->time_zone)
                ->setDateFormat($row->default_date_format)
                ->setDefaultUserStorageSize($row->default_user_storage_size)
                ->setMethodsPath($row->methods_path)
                ->setMethodsInstancePath($row->methods_instance_path)
                ->setResultsS3Bucket($row->results_s3_bucket)
                ->setPasswordExpirationPeriod($row->password_expiration_period)
                ->setStrongPasswordFlag($row->strong_passwords_flag)
                ->setLockInstanceFlag($row->lock_instance_flag)
                ->setDefaultUserEbs($row->default_user_ebs)
                ->setVocabularyDataset($row->vocabulary_dataset_type_id)
                ->setDefaultMoneyLimit($row->default_money_limit);
        
    }
    
    public function getConfig()
    {
        $config = new Application_Model_SiteConfig();
        $this->find(1, $config);
        return $config;
    }
    
    private function arrayToString(array $data)
    {
        $result = '';
        foreach ($data as $key => $entry)
        {
            $result .= $key.'='.$entry.', ';
        }
        return $result;
    }    
    
}