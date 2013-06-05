<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    25 Dec 2010

    Model, representing HOI/DOI replacement entity.

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
class Application_Model_MethodReplacement extends Application_Model_Abstract
{

    protected $_id;
    protected $_name;
    protected $_user_id;
    protected $_shared_flag;
    protected $_hoi_replacement;
    protected $_doi_replacement;
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
            $this->setMapper('Application_Model_MethodReplacementMapper');
        } 
        return $this->_mapper;
    }


    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
        	$key = str_replace('_', ' ', $key);
        	$key = ucwords($key);
        	$key = str_replace(' ', '', $key);
            $method = 'set' . $key;
            if (in_array($method, $methods)) {
                $this->$method($value);
            }        
        }
        return $this;
    }

    public function setId($id) {
        $this->_id = $id;
        return $this;
    }

    public function getId() {
        return $this->_id;
    }

    public function setUserId($user_id) {
        $this->_user_id = (int) $user_id;
        return $this;
    }

    public function getUserId() {
        return $this->_user_id;
    }

    public function setName($name) {
        $this->_name = (string) $name;
        return $this;
    }

    public function getName() {
        return $this->_name;
    }

    public function setSharedFlag($shared_flag) {
        $this->_shared_flag = $shared_flag;
        return $this;
    }

    public function getSharedFlag() {
        return $this->_shared_flag;
    }

    public function setHoiReplacement($hoi_replacement) {
        $this->_hoi_replacement = (string) $hoi_replacement;
        return $this;
    }

    public function getHoiReplacement() {
        return $this->_hoi_replacement;
    }

    public function setDoiReplacement($doi_replacement) {
        $this->_doi_replacement = (string) $doi_replacement;
        return $this;
    }

    public function getDoiReplacement() {
        return $this->_doi_replacement;
    }


    public function save() {

        return $this->getMapper()->save($this);
    }

    public function find($id) {
        return $this->getMapper()->find($id, $this);
    }

    public function toArray() {

        $data = array(
            'id' => $this->getId(),
            'userId' => $this->getUserId(),
            'name' => $this->getName(),
            'sharedFlag' => $this->getSharedFlag(),
        	'hoiReplacement' => $this->getHoiReplacement(),
        	'doiReplacement' => $this->getDoiReplacement(),
        );

        return $data;
    }
}

