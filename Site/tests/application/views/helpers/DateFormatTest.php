<?php
/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    07 April 2011

    Test suite for Application_View_Helper_DateFormat class

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

final class Application_View_Helper_DateFormatTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    public function testDirectlySpecifiedSourceFormat()
    {
        $target = new Application_View_Helper_DateFormat('%Y-%m-%d %r','US/Eastern');
        $date = $target->dateFormat('11-Feb-11 5:00:00', 'DD-MMM-YY hh:mm:ss');
        
        $this->assertEquals(
            '2011-02-11 12:00:00 AM', 
            $date
        );
    }
    public function testIndirectlySpecifiedSourceFormat()
    {
        $target = new Application_View_Helper_DateFormat('%Y-%m-%d %r','US/Eastern');
        $date = $target->dateFormat('2009-02-12 6:00:00');
        
        $this->assertEquals(
            '2009-02-12 01:00:00 AM', 
            $date
        );
    }
}

