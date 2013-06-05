<?php
/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 March 2011

    Test suite for Application_Model_Osim2AnalysisSummary class

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

require_once('PHPUnit/Autoload.php');

final class Osim2AnalysisSummaryTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    public function testValidDataset()
    {
        $model = new Application_Model_Osim2AnalysisSummary();
        $datasetLocation = TESTS_PATH.'/data/osim2/dataset1';
        $this->assertEquals(
            false, 
            $model->validate($datasetLocation)
        );
    }
    public function testDatasetMissingRequiredFile()
    {
        $model = new Application_Model_Osim2AnalysisSummary();
        $datasetLocation = TESTS_PATH.'/data/osim2/datasetMissingFile';
        $this->assertEquals(
            "File osim_cond_first_drug_prob is missing in archive", 
            $model->validate($datasetLocation)
        );
    }
    public function testDatasetWrongColumn()
    {
        $model = new Application_Model_Osim2AnalysisSummary();
        $datasetLocation = TESTS_PATH.'/data/osim2/datasetWrongColumn';
        $this->assertEquals(
            "Column gender_concept_id expected at place 0 in file osim_age_at_obs_probability. gender_concept_id1 found.",
            $model->validate($datasetLocation)
        );
    }
    public function testDatasetWrongColumnsCount()
    {
        $model = new Application_Model_Osim2AnalysisSummary();
        $datasetLocation = TESTS_PATH.'/data/osim2/datasetWrongColumnsCount';
        $this->assertEquals(
            "Number of columns in file osim_time_obs_probability don't match to expected value 5.",
            $model->validate($datasetLocation)
        );
    }
    public function testDatasetNonSymetricData()
    {
        $model = new Application_Model_Osim2AnalysisSummary();
        $datasetLocation = TESTS_PATH.'/data/osim2/datasetNonSymetricData';
        $this->assertEquals(
            "Number of colums does not match with length of some rows in the file osim_src_db_attributes.",
            $model->validate($datasetLocation)
        );
    }
    public function testPreparedFilesEmptyFolder()
    {
        $cacheLocation = TESTS_PATH.'/data/osim2/cache/empty';
        $config = new stdClass();
        $config->loader_working_dir = $cacheLocation;
        
        Zend_Registry::set('osim2Config', $config);
        
        $model = new Application_Model_Osim2AnalysisSummary();
        $this->assertEquals(
            array(),
            $model->getPreparedFiles($cacheLocation)
        );
    }
    public function testPreparedFilesNonEmptyFolder()
    {
        $cacheLocation = TESTS_PATH.'/data/osim2/cache/nonempty';
        $config = new stdClass();
        $config->loader_working_dir = $cacheLocation;
        
        Zend_Registry::set('osim2Config', $config);
        
        $model = new Application_Model_Osim2AnalysisSummary();
        $this->assertEquals(
            array('dataset1.zip', 'datasetMissingFile.zip'),
            $model->getPreparedFiles($cacheLocation)
        );
    }
}

