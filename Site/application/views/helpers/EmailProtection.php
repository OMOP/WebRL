<?php
/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    28 Jan 2011

    Helper, which helps pretect email from automatic capturing by spam bots. 

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

class Application_View_Helper_EmailProtection extends Zend_View_Helper_Abstract
{

    private function obfuscate($email)
    {
        $i=0;
        $obfuscated="";
        while ($i<strlen($email)) {
            if (rand(0, 2)) {
                $obfuscated.='%'.dechex(ord($email{$i}));
            } else {
                $obfuscated.=$email{$i};
            }
            $i++;
        }
        return $obfuscated;
    }

    private function obfuscate_numeric($plaintext)
    {
        $i=0;
        $obfuscated="";
        while ($i<strlen($plaintext)) {
            if (rand(0, 2)) {
                $obfuscated.='&#'.ord($plaintext{$i}).';';
            } else {
                $obfuscated.=$plaintext{$i};
            }
            $i++;
        }
        return $obfuscated;
    }

    public function emailProtection($email, $label)
    {
        return sprintf(
            "<a href='%s:%s'>%s</a>",
            $this->obfuscate_numeric('mailto'),
            $this->obfuscate($email),
            $this->obfuscate_numeric($label)
        );
    }

}