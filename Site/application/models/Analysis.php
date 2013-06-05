<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    5 Jan 2010

    Model, representing analysis entity.

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
// XXX stub-model. is not used in other places. [06 Jan 2011]
class Application_Model_Analysis extends Application_Model_Abstract
{

    protected $_id;
    protected $_methodAbbrv;
    protected $_methodId;
    protected $_runId;
    protected $_configurationId;
    protected $_outputFileName;
    protected $_runName;
    protected $_triageVSFull;
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
            $this->setMapper('Application_Model_AnalysisMapper');
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
            } else {
                $matches = array();
                if (preg_match('/param(\d+)/', $key, $matches)) {
                    $this->setParam($matches[1], $value);
                }
            }

        }
        return $this;
    }

    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setMethodId($id)
    {
        $this->_methodId = $id;
        return $this;
    }

    public function getMethodId()
    {
        return $this->_methodId;
    }

    public function setMethodAbbrv($abbrv)
    {
        $this->_methodAbbrv = (string) $abbrv;
        return $this;
    }

    public function getMethodAbbrv()
    {
        return $this->_methodAbbrv;
    }

    public function getParams()
    {
        return $this->_params;
    }

    public function getParam($i)
    {
        return $this->_params['param'.$i];
    }

    public function setParams(array $params)
    {
        if (! is_array($params)) {
            throw new Zend_Exception('Wrong param type');
        }
        $this->_params = $params;
        return $this;
    }

    public function setParam($i, $param)
    {
        if (null == $this->_params) {
            $this->_params = array();
        }
        $this->_params['param'.$i] = $param;
        return $this;
    }

    public function setRunId($id)
    {
        $this->_runId = $id;
        return $this;
    }

    public function getRunId()
    {
        return $this->_runId;
    }

    public function setConfigurationId($id)
    {
        $this->_configurationId = $id;
        return $this;
    }

    public function getConfigurationId()
    {
        return $this->_configurationId;
    }

    public function setOutputFileName($fileName)
    {
        $this->_outputFileName = (string) $fileName;
        return $this;
    }

    public function getOutputFileName()
    {
        return $this->_outputFileName;
    }

    public function setRunName($runName)
    {
        $this->_runName = (string) $runName;
        return $this;
    }

    public function getRunName()
    {
        return $this->_runName;
    }

    public function setTriageVSFull($tvf)
    {
        $this->_triageVSFull = $tvf;
        return $this;
    }

    public function getTriageVSFull()
    {
        return $this->_triageVSFull;
    }
    

}