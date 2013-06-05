<?php
/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    30 March 2011

    Test suite for Application_Model_Osim2Mapper class

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

final class Osim2MapperTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function tearDown()
    {
    }
    
    /**
     * Perform test that method Application_Model_Osim2Mapper::getSummarySchema
     * returns non null object without compilation errors.
     */
    public function testGetSummarySchema()
    {
        $schema = Application_Model_Osim2Mapper::getSummarySchema();
        $this->assertTrue($schema !== false);
        $this->assertTrue($schema !== null);
    }

    /**
     * Performs test that object returned contains all data in the upper case. 
     */
    public function testSummarySchemaCase()
    {
        $schema = Application_Model_Osim2Mapper::getSummarySchema();
        foreach($schema as $table => $columns) {
            $this->assertEquals($table, strtoupper($table));
            foreach($columns as $columnName) {
                $this->assertEquals(
                    $columnName, 
                    strtoupper($columnName));
            }
        }
    }

    /**
     * Performs test that object returned returns map which has strings as keys 
     * and arrays with values as data. 
     */
    public function testSummarySchemaStructure()
    {
        $schema = Application_Model_Osim2Mapper::getSummarySchema();
        $this->assertTrue(is_array($schema));
        foreach($schema as $table => $columns) {
            $this->assertInternalType('string', $table);
            $this->assertTrue(is_array($columns));
        }
    }
}

