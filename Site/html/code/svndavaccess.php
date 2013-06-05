<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Contains class Application that act as main entry point for the application 
	specific logic. This class handle all logic that are the same for all pages
	across all aplication.
 
    �2009 Foundation for the National Institutes of Health (FNIH)
 
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

class SvnDavAccess
{
    var $authorization_file;
    var $users;
    function __construct($authorization_file = null, $users = null)
    {
        $this->authorization_file = $authorization_file;
        $this->users = $users;
    }
    function update()
    {
        $data = array();
        foreach($this->users as $u)
        {
            $data[] = $u->dav_password_hash;
        }
        file_put_contents($this->authorization_file, implode(PHP_EOL, $data));
        
    }
    
    function update_organization_access($access, $access_file) {
        $data = array();
        foreach ($access as $folder => $users) {
            if (!$folder) {
                $data[] = "[/]";
            } else {
                $folder = trim($folder, '/');
                $data[] = "[/$folder]";
            }
            
            foreach ($users as $user) {
                $data[] = "$user = rw";
            }
            $data[] = "";
        }

        file_put_contents($access_file, implode(PHP_EOL, $data));
        
    }
}

?>