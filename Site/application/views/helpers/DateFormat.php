<?php

/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    14-Feb-2011

    View helper for date formatting

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


/**
 * Helper method for converting date format to settings which .
 * 
 * @author OMOP Team <support@omop.org>
 * @license Apache License, Version 2.0 (http://omop.fnih.org/publiclicense)
 * @package OMOP_WebRL
 */
final class Application_View_Helper_DateFormat extends Zend_View_Helper_Abstract
{
    protected $_format;
    protected $_timezone;
    private $_locale = 'en_US'; 
    private $_initialized = false;
    
    /**
     * Create new instance of class Application_View_Helper_DateFormat
     * with specified format and timezone for correlation.
     * 
     * @param string $format
     * Default format for dates.
     * @param string $timezone
     * Timezone for which dates will be correlated.
     */
    public function __construct($format = null, $timezone = null)
    {
        $this->_format = self::convertFormat($format);
        $this->_timezone = $timezone;
        if ($format && $timezone) {
            $this->_initialized = true;
        }
    }
    /**
     * Convert date string from one format to format specified 
     * on the system settings page with respect to configured timezone.
     *  
     * @param string $string 
     * String that represend the date.
     * @param string $sourceFormat
     * Source format for date passed in the $string parameter
     * @param string $targetFormat 
     * Target format to which should be converted date.
     * Default to format specified in the system settings.
     */
    public function dateFormat($string, $sourceFormat = 'YYYY.MM.dd.hh.mm.ss', $targetFormat = null)
    {
        if (!$this->_initialized) {
            $this->initialize();
        }
        
        if ($string) {
            //If no time specified in $string then do not show time
            if (strpos($string, ':') === False) {
                $targetFormat = str_replace("hh:mm:ss a", '', $this->_format);
            }
            if (strpos($string, ':') !== False) {
                $tz = date_default_timezone_get();
                date_default_timezone_set('GMT');
                $date = new Zend_Date($string, $sourceFormat, $this->_locale);
                $date->setTimezone($this->_timezone);
                date_default_timezone_set($tz);
            } else {
                $date = new Zend_Date($string, $sourceFormat, $this->_locale);
            }
            if ($targetFormat == null)
                return $date->toString($this->_format);
            else
                return $date->toString($targetFormat);
        } else
            return '';
    }
    private function initialize()
    {
        $mapper = new Application_Model_SiteConfigMapper();
        $config = $mapper->getConfig();
        $this->_format = $config->getDateFormat();
        //Translate format to Zend_Date format string
        $this->_format = self::convertFormat($this->_format);
        
        $this->_timezone = $config->getTimezone();
        $this->_initialized = true;
    }
    private static function convertFormat($format)
    {
        $from = array("%Y", "%m", "%d", "%r", "%b");
        $to = array("YYYY", "MM", "dd", "hh:mm:ss a", "MMM");
        return str_replace($from, $to, $format);
    }   
}