<?php

/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10-Feb-2011

    Site Config model

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
class Application_Model_SiteConfig extends Application_Model_Abstract
{
    
    protected $_adminEmail;
    protected $_replyTo;
    protected $_timezone;
    protected $_defaultMoneyLimit;
    protected $_defaultUserStorageSize;
    protected $_dateFormat;
    protected $_methodsPath;
    protected $_methodsInstancePath;
    protected $_resultsS3Bucket;
    protected $_passwordExpirationPeriod;
    protected $_strongPasswordFlag;
    protected $_lockInstanceFlag;
    protected $_defaultUserEbs;
    protected $_vocabularyDataset;
    
    public function getAdminEmail()
    {
        return $this->_adminEmail;
    }

    public function setAdminEmail($_adminEmail)
    {
        $this->_adminEmail = $_adminEmail;
        return $this;
    }

    public function getReplyTo()
    {
        return $this->_replyTo;
    }

    public function setReplyTo($_replyTo)
    {
        $this->_replyTo = $_replyTo;
        return $this;
    }

    public function getTimezone()
    {
        return $this->_timezone;
    }

    public function setTimezone($_timezone)
    {
        $this->_timezone = $_timezone;
        return $this;
    }

    public function getDefaultMoneyLimit()
    {
        return $this->_defaultMoneyLimit;
    }

    public function setDefaultMoneyLimit($_defaultMoneyLimit)
    {
        $this->_defaultMoneyLimit = $_defaultMoneyLimit;
        return $this;
    }

    public function getDefaultUserStorageSize()
    {
        return $this->_defaultUserStorageSize;
    }

    public function setDefaultUserStorageSize($_defaultUserStorageSize)
    {
        $this->_defaultUserStorageSize = $_defaultUserStorageSize;
        return $this;
    }

    public function getDateFormat()
    {
        return $this->_dateFormat;
    }

    public function setDateFormat($_dateFormat)
    {
        $this->_dateFormat = $_dateFormat;
        return $this;
    }

    public function getMethodsPath()
    {
        return $this->_methodsPath;
    }

    public function setMethodsPath($_methodPath)
    {
        $this->_methodsPath = $_methodPath;
        return $this;
    }

    public function getMethodsInstancePath()
    {
        return $this->_methodsInstancePath;
    }

    public function setMethodsInstancePath($_methodsInstancePath)
    {
        $this->_methodsInstancePath = $_methodsInstancePath;
        return $this;
    }

    public function getResultsS3Bucket()
    {
        return $this->_resultsS3Bucket;
    }

    public function setResultsS3Bucket($_resultsS3Bucket)
    {
        $this->_resultsS3Bucket = $_resultsS3Bucket;
        return $this;
    }

    public function getPasswordExpirationPeriod()
    {
        return $this->_passwordExpirationPeriod;
    }

    public function setPasswordExpirationPeriod($_passwordExpirationPeriod)
    {
        $this->_passwordExpirationPeriod = $_passwordExpirationPeriod;
        return $this;
    }

    public function getStrongPasswordFlag()
    {
        return $this->_strongPasswordFlag;
    }

    public function setStrongPasswordFlag($_strongPasswordFlag)
    {
        $this->_strongPasswordFlag = $_strongPasswordFlag;
        return $this;
    }

    public function getLockInstanceFlag()
    {
        return $this->_lockInstanceFlag;
    }

    public function setLockInstanceFlag($_lockInstanceFlag)
    {
        if ($_lockInstanceFlag && $_lockInstanceFlag != 'N') {
            $_lockInstanceFlag = 'Y';
        } else {
            $_lockInstanceFlag = 'N';
        }

        $this->_lockInstanceFlag = $_lockInstanceFlag;
        return $this;
    }
    
    public function getDefaultUserEbs()
    {
        return $this->_defaultUserEbs;
    }

    public function setDefaultUserEbs($_defaultUserEbs)
    {
        $this->_defaultUserEbs = $_defaultUserEbs;
        return $this;
    }

    public function getVocabularyDataset()
    {
        return $this->_vocabularyDataset;
    }

    public function setVocabularyDataset($_vocabularyDataset)
    {
        $this->_vocabularyDataset = $_vocabularyDataset;
        return $this;
    }

        
    public function save()
    {
        $mapper = new Application_Model_SiteConfigMapper();
        $mapper->save($this);
    }
    
    public function toArray()
    {
        return array(
            'adminEmail'                =>  $this->getAdminEmail(),
            'replyTo'                   =>  $this->getReplyTo(),
            'timezone'                  =>  $this->getTimezone(),
            'dateFormat'                =>  $this->getDateFormat(),
            'defaultUserStorageSize'    =>  $this->getDefaultUserStorageSize(),
            'methodsPath'               =>  $this->getMethodsPath(),
            'methodsInstancePath'       =>  $this->getMethodsInstancePath(),
            'resultsS3Bucket'           =>  $this->getResultsS3Bucket(),
            'passwordExpirationPeriod'  =>  $this->getPasswordExpirationPeriod(),
            'strongPasswordFlag'        =>  $this->getStrongPasswordFlag(),
            'lockInstanceFlag'          =>  ($this->getLockInstanceFlag() == 'Y'),
            'defaultUserEbs'            =>  $this->getDefaultUserEbs(),
            'vocabularyDataset'         =>  $this->getVocabularyDataset(),
            'defaultMoneyLimit'         =>  $this->getDefaultMoneyLimit()
        );
    }

    
    public static function getTimezones()
    {
        $tz = timezone_identifiers_list();
//        $tz = array_filter($tz, array(self, "iscontinenttz"));
        if (!empty ($tz)) {
            return array_combine(array_values($tz), array_values($tz));
        } else {
            return array();
        }
    }
    
    public static function iscontinenttz($tz)
    {
        return preg_match("/(US)\//", $tz) == 1;
    }
    
    public static function getDateFormats()
    {
        return array(
            "%m-%d-%Y %r" => "mm-dd-yyyy", 
            "%m/%d/%Y %r" => "mm/dd/yyyy", 
            "%Y-%m-%d %r" => "yyyy-mm-dd", 
            "%d-%b-%Y %r" => "dd-MMM-yyyy");
        
    }
    
    
    
}