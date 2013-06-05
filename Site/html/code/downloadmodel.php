<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Page model for /download page. Manage all user data within /launch page.
 
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

class DownloadModel extends PageModel
{   
	var $temp_folder;
	
	function load()
    {
    	$this->create_temp_folder();
    }
    
    function create_temp_folder()
    {
    	$this->temp_folder = '/var/tmp/temp_'.(time() % 1000);
    	mkdir($this->temp_folder, 0777);
    }
    
    function get_temp_folder()
    {
    	return $this->temp_folder;
    }
    function get_temp_location($file_name)
    {
    	return $this->temp_folder.'/'.$file_name;
    }
    function copy_to_temp_folder($folder, $file_name)
    {
    	copy($folder.'/'.$file_name, $this->get_temp_location($file_name));
    }
    
    function delete_from_temp_folder($file_name)
    {
    	unlink($this->temp_folder.'/'.$file_name);
    }
    
    function delete_temp_folder()
    {
    	rmdir($this->temp_folder);
    }
    
}

?>