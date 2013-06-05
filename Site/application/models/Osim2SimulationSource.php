<?php
/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    30 March 2011

    Object that represents OSIM2 simulated dataset.

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

final class Application_Model_Osim2SimulationSource
{
    private $_id;
    private $_oldId;
    private $_name;
    private $_description;
    private $_analysisSourceId;
    private $_patientsQty;
    private $_hasSignal;
    private $_status;
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
     * @return Application_Model_Osim2SimulationSource Current object with modified property.
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
     * Sets new value for AnalysisSourceId
     * @param $value New value of AnalysisSourceId
     */
    public function setAnalysisSourceId($value)
    {
        $this->_analysisSourceId = $value;
        return $this;
    }
    
    /**
     * Gets current value of AnalysisSourceId
     * @return Returns value of AnalysisSourceId
     */
    public function getAnalysisSourceId()
    {
        return $this->_analysisSourceId;
    }
    
    /**
     * Sets new value for PatientQty
     * @param $value New value of PatientQty
     */
    public function setPatientQty($value)
    {
        $this->_patientsQty = $value;
        return $this;
    }
    
    /**
     * Gets current value of PatientQty
     * @return Returns value of PatientQty
     */
    public function getPatientQty()
    {
        return $this->_patientsQty;
    }
    
    /**
     * Sets new value for HasSignal
     * @param $value New value of HasSignal
     */
    public function setHasSignal($value)
    {
        $this->_hasSignal = $value;
        return $this;
    }
    
    /**
     * Gets current value of HasSignal
     * @return Returns value of HasSignal
     */
    public function getHasSignal()
    {
        return $this->_hasSignal;
    }
    
    /**
     * Sets new value for Status
     * @param $value New value of Status
     */
    public function setStatus($value)
    {
        $this->_status = $value;
        return $this;
    }
    
    /**
     * Gets current value of Status
     * @return Returns value of Status
     */
    public function getStatus()
    {
        return $this->_status;
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
     * @param number $id Unique identifier of Simulation Source which 
     * should be loaded into the object.   
     */
    public function find($id)
    {
        $mapper = $this->getMapper();
        return $mapper->findSimulationSource($id, $this);
    }
    
    /**
     * Perform loading of object properties by name which 
     * object has in the database.
     * 
     * @param string $name Name of Simulation Source which  
     * should be loaded into the object.   
     */
    public function findByName($name)
    {
        $mapper = $this->getMapper();
        return $mapper->findSimulationSourceByName($name, $this);
    }
    
    /**
     * Saves current object to underlying datastore.
     */
    public function save()
    {
        $mapper = $this->getMapper();
        return $mapper->saveSimulationSource($this);
    }
}
