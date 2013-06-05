<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    23 December 2010

    Model of one run result.

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
class Application_Model_Run extends Application_Model_Abstract
{

    protected $_name;
    protected $_dataset;
    protected $_method;
    protected $_date;
    protected $_files;
    protected $_isComplete;

    private $_isFilesConverted;
    private $_datasetTemplates = array('NAME', 'NULL');
    private $_datasetDefaultTemplate = 'NAME';
    private $_datasetTemplate = null;

    public function __construct(array $options = null)
    {
        parent::__construct($options);
    }

    public function isComplete ()
    {
        if ($this->_isComplete == null) {
            throw new Exception("Unexpected state.");
        }
        return $this->_isComplete;
    }

    public function setComplete ($isComplete)
    {
        $this->_isComplete = (boolean) $isComplete;
        return $this;
    }

    /**
     * Set file renaming template basing on sample file
     * @param string sample file name
     * @param boolean force setting new template or use existing one (false)
     */
    public function setDatasetTemplate ($sampleFile, $force = false)
    {
        if (!$force && $this->_datasetTemplate) {
            return $this;
        }
        foreach ($this->_datasetTemplates as $tpl) {
            if (false !== strpos($sampleFile, $tpl)) {
                $this->_datasetTemplate = $tpl;
                break;
            }
        }
        return $this;
    }

    public function getDatasetTemplate ($sampleFile = '')
    {
        if (null !== $this->_datasetTemplate) {
            return $this->_datasetTemplate;
        }
        if ($sampleFile) {
            $this->setDatasetTemplate($sampleFile);
        }
        /**
         * @todo should we set default empty template here?
         */
        return $this->_datasetTemplate !== null ? $this->_datasetTemplate : $this->_datasetDefaultTemplate;
    }

    public function getName ()
    {
        return $this->_name;
    }

    public function setName ($aName)
    {
        $this->_name = (string) $aName;
        return $this;
    }

    public function getDataset ()
    {
        return $this->_dataset;
    }

    public function setDataset ($aDs)
    {
        $this->_dataset = $aDs;
        return $this;
    }

    public function getMethod ()
    {
        return $this->_method;
    }

    public function setMethod ($aMethod)
    {
        $this->_method = $aMethod;
        return $this;
    }

    public function getDate ()
    {
        // if date is not set, then take date of one of files
        if (!$this->_date && $this->_files && count($this->_files)) {
            $this->_date = $this->_files[0]['date'];
        }
        return $this->_date;
    }

    public function setDate ($aDate)
    {
        $this->_date = $aDate;
        return $this;
    }

    public function getFiles ()
    {
        if (!$this->_isFilesConverted) {
            $this->convertFiles();
        }
        return $this->_files;
    }

    public function setFiles (array $files)
    {
        $this->_files = $files;
        $this->convertFiles();
        return $this;
    }

    /**
     * Convert file names adding the dataset name instead a template there
     * This can't be done strictly in setter because the dataset name might be
     * unavailable yet
     * Should be run once on each set of files
     */
    private function convertFiles () {
        if (!$this->getDataset()) {
            return;
        }
        $this->_isFilesConverted = true;
        foreach ($this->_files as $key => $file) {
            $this->_files[$key]['file'] = $this->convertFile($file['file']);
        }
    }

    /**
     * Convert file names adding the dataset name instead a template.
     * @param string $filename
     * @return string
     */
    public function convertFile ($filename)
    {
        if (!$this->getDataset()) {
            return $filename;
        }

        return str_replace($this->getDatasetTemplate($filename), $this->getDataset(), $filename);
    }

    /**
     * Deconvert name of specific file by changing a dataset name to a template.
     * @param string $filename
     * @return string
     */
    public function unConvertFile ($filename)
    {
        if (!$this->getDataset()) {
            return $filename;
        }
        return str_replace($this->getDataset(), $this->getDatasetTemplate(), $filename);
    }

    /**
     * If there are files that should be present in Run, but that are not uploaded, then returns true.
     * @return boolean
     */
    public function hasNotUploadedFiles ()
    {
        foreach ($this->getFiles() as $file) {
            if (!$file['date']) {
                return true;
            }
        }

        return false;
    }
}