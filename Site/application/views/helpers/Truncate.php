<?php

/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    16-Feb-2011

    

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
class Application_View_Helper_Truncate extends Zend_View_Helper_Abstract
{
    
    public function truncate($string, $length = 80, $etc = '...',
                                  $break_words = false, $middle = false)
    {
        if ($length == 0)
            return '';

        if (strlen($string) > $length) {
            $length -= min($length, strlen($etc));
            if (!$break_words && !$middle) {
                $string = preg_replace(
                    '/\s+?(\S+)?$/',
                    '',
                    substr($string, 0, $length+1));
            }
            if(!$middle) {
                return substr($string, 0, $length) . $etc;
            } else {
                return substr($string, 0, $length/2) . 
                        $etc . substr($string, -$length/2);
            }
        } else {
            return $string;
        }
}

    
}