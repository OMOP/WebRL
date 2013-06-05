<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 Dec 2010

    Model, representing system instance entity.

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

================================================================================*/

class Application_Model_SystemInstance extends Application_Model_Abstract {
    
    protected $_id;
    protected $_name;
    protected $_host;
    protected $_keyName;
    protected $_sizeId;
    protected $_registerDate;
    protected $_launchDate;
    protected $_endDate;
    protected $_osFamily;
    protected $_instanceType;
    protected $_mapper;


    public function setMapper($mapper)
    {
        if (is_string($mapper)) {
            $mapper = new $mapper();
        }
        $this->_mapper = $mapper;
        return $this;
    }

    public function getMapper()
    {
        if (null === $this->_mapper) {
            $this->setMapper('Application_Model_SystemInstanceMapper');
        }
        return $this->_mapper;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setId($_id)
    {
        $this->_id = $_id;
        return $this;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setName($_name)
    {
        $this->_name = (string) $_name;
        return $this;
    }

    public function getHost()
    {
        return $this->_host;
    }

    public function setHost($_host)
    {
        $this->_host = (string) $_host;
        return $this;
    }

    public function getKeyName()
    {
        return $this->_keyName;
    }

    public function setKeyName($_keyName)
    {
        $this->_keyName = (string) $_keyName;
        return $this;
    }

    public function getSizeId()
    {
        return $this->_sizeId;
    }

    public function setSizeId($_sizeId)
    {
        $this->_sizeId = $_sizeId;
        return $this;
    }

    public function getRegisterDate()
    {
        $filter = new Application_View_Helper_DateFormat();
        return $filter->dateFormat($this->_registerDate);
    }

    public function setRegisterDate($_registerDate)
    {
        $this->_registerDate = $_registerDate;
        return $this;
    }

    public function getLaunchDate()
    {
        $filter = new Application_View_Helper_DateFormat();
        return $filter->dateFormat($this->_launchDate);
    }

    public function setLaunchDate($_launchDate)
    {
        $this->_launchDate = $_launchDate;
        return $this;
    }

    public function getEndDate()
    {
        return $this->_endDate;
    }

    public function setEndDate($_endDate)
    {
        $this->_endDate = $_endDate;
        return $this;
    }

    public function getOsFamily()
    {
        return $this->_osFamily;
    }

    public function setOsFamily($_osFamily)
    {
        $this->_osFamily = $_osFamily;
        return $this;
    }    

    public function getInstanceType()
    {
        return $this->_instanceType;
    }

    public function setInstanceType($_instanceType)
    {
        $this->_instanceType = $_instanceType;
        return $this;
    }

    public function save()
    {
        $this->getMapper()->save($this);
    }
    
    public function find($id)
    {        
        $this->getMapper()->find($id, $this);
    }
    
    public function getInstanceInfo()
    {        
        global $configurationManager;
        $awsConfiguration = new AwsConfiguration($configurationManager);

        $amazonInstance = new Zend_Service_Amazon_Ec2_Instance($awsConfiguration->aws_access_key_id(), $awsConfiguration->aws_secret_access_key());
        $describeInfo = $amazonInstance->describe();
        if (isset($describeInfo['instances'])) {
            foreach ($describeInfo['instances'] as $instance)
                if ($instance['dnsName'] == $this->getHost()) {
                    $this->setKeyName($instance['keyName']);
                    $this->setLaunchDate(gmdate('c', strtotime($instance['launchTime'])));
                    $instance_size = new InstanceSize();
                    $instance_size->load('aws_instance_size_name = ? and os_family = ?', array($instance['instanceType'], $this->getOsFamily()));
                    if ($instance_size->instance_size_id) {
                        $this->setSizeId($instance_size->instance_size_id);
                    }
                    return true;
                }
        }
        
        return false;
        
    }    
    
    public function toArray()
    {        
        return array(
            'name' => $this->getName(),
            'host' => $this->getHost(),
            'keyName' => $this->getKeyName(),
            'launchDate' => $this->getLaunchDate(),
            'endDate' => $this->getEndDate(),
            'registerDate' => $this->getRegisterDate(),
            'instanceSize' => $this->getSizeId(),
            'osFamily' => $this->getOsFamily(),
            'instanceType' => $this->getInstanceType()
        );
    }


}