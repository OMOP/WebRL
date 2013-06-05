<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 Dec 2010

    Model, representing method entity.

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
require_once 'OMOP/WebRL/Configuration/WebRLConfiguration.php';
class Application_Model_Method extends Application_Model_Abstract
{

    protected $_id;
    protected $_oldId;
    protected $_abbrv;
    protected $_name;
    protected $_access;
    protected $_fileNameFormat;
    protected $_fileRenameMask;
    protected $_mapper;
    protected $_organizations;
    protected $_params;


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
            $this->setMapper('Application_Model_MethodMapper');
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
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
            else {
                $matches = array();
                if (preg_match('/param([1-9][0-9]?|2[0-5])/', $key, $matches)) {
                    $this->setParam($matches[1], $value);
                }
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

    public function setAbbrv($abbrv) {
        $this->_abbrv = (string) $abbrv;
        return $this;
    }

    public function getAbbrv() {
        return $this->_abbrv;
    }

    public function setName($name) {
        $this->_name = (string) $name;
        return $this;
    }

    public function getName() {
        return $this->_name;
    }

    public function setAccess($access) {
        $this->_access = $access;
        return $this;
    }

    public function getAccess() {
        return $this->_access;
    }

    /**
     * Sets value for FILE_NAME_FORMAT field
     * @param string $format
     * @return Application_Model_Method
     */
    public function setFileNameFormat($format) {
        $this->_fileNameFormat = $format;
        return $this;
    }

    /**
     * Returns analysis file name format
     * @return string analysis file name format
     */
    public function getFileNameFormat() {
        return $this->_fileNameFormat;
    }

        /**
     * Sets value for FILE_RENAME_MASK field
     * @param string $mask
     * @return Application_Model_Method
     */
    public function setFileRenameMask($mask) {
        $this->_fileRenameMask = $mask;
        return $this;
    }

    /**
     * Returns analysis file rename mask
     * @return string analysis file rename mask
     */
    public function getFileRenameMask() {
        return $this->_fileRenameMask;
    }

    public function getOrganizations() {
        return $this->_organizations;
    }

    public function setOrganizations($orgs) {
        $this->_organizations = $orgs;
        return $this;
    }

    public function getParams() {
        return $this->_params;
    }

    public function getParam($i) {
        return $this->_params['param'.$i];
    }

    public function setParams($params) {
        if (! is_array($params)) {
            throw new Zend_Exception('Wrong param type');
        }
        $this->_params = $params;
        return $this;
    }

    public function setParam($i, $param) {
        if (null == $this->_params)
            $this->_params = array();
        $this->_params['param'.$i] = $param;
        return $this;
    }

    public function getOldId() {
        return $this->_oldId;
    }

    public function setOldId($_oldId) {
        $this->_oldId = $_oldId;
        return $this;
    }

    
    public function save() {

        return $this->getMapper()->save($this);
    }

    public function find($id) {
        return $this->getMapper()->find($id, $this);
    }

    public function findWithOrganizations($id) {
        return $this->getMapper()->findWithOrganizations($id, $this);
    }

    public function toArray() {

        $data = array(
            'id' => $this->getId(),
            'abbrv' => $this->getAbbrv(),
            'name' => $this->getName(),
            'access' => $this->getAccess(),
            'fileNameFormat' => $this->getFileNameFormat(),
            'fileRenameMask' => $this->getFileRenameMask(),
        );

        if (is_array($this->_params))
            $data = array_merge($data, $this->_params);
        if (isset($this->_organizations) && is_array($this->_organizations)) {
            /*
             * This should be done in OrganizationMapper
             */
            $orgs = array();
            foreach ($this->_organizations as $organization) {
                $orgs[] = $organization['organization_id'];
            }
            $data['organizations'] = $orgs;
        }
        return $data;

    }

    public function findByAbbr($abbr) {
        $this->getMapper()->findByAbbr($abbr, $this);
        return $this;
    }
    
    public function hasSupplementals1() {
        global $configurationManager;
        $config = new WebRLConfiguration($configurationManager);
        $methods_list = $config->methods_with_supplementals();
        return in_array($this->getAbbrv(), $methods_list);
    }
    
    public function hasSupplementals2() {
        global $configurationManager;
        $config = new WebRLConfiguration($configurationManager);
        $methods_list = $config->methods_with_supplementals();
        return in_array($this->getAbbrv(), $methods_list);
    }
    
    public function getSupplementals1() {
        if (! $this->hasSupplementals1()) {
            return array();
        }
        
        $select = $this->getMapper()->getDbTable()->getAdapter()->select();
        $select->from('SUPPLEMENTAL_1_REF')
               ->where('METHOD_ABBR = ?', $this->getAbbrv());
        $result = $this->getMapper()->getDbTable()->getAdapter()->fetchAll($select);
        $ret = array();
        foreach ($result as $row) {
            $suppl = new Application_Model_SupplementalFile();
            $suppl->setId($row['SUPPLEMENTAL1_ID'])
                  ->setMethodAbbr($row['METHOD_ABBR'])
                  ->setType(1)
                  ->setFileName($row['FILE_NAME']);
            $ret[] = $suppl;
        }
        
        return $ret;
        
    }

}

