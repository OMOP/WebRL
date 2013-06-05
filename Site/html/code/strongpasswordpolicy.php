<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Page model for /edit_user page. Provide data about current state of /edit_user page.
 
    ©2009 Foundation for the National Institutes of Health (FNIH)
 
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

/*
Class that implements strong password policy.

Password shall contain at least one alphanumeric character, 
one uppercase character, and one lowercase character.  
Passwords shall be at least 8 characters long.
*/
class StrongPasswordPolicy implements PasswordPolicy
{
    /*
    Check that password conforms to the password policy rules.

    Params:
    @password   string password that should be checked for matching policy rules.
    */
	public function is_valid($password)
    {
        if (strlen($password) < 8)
            return false;
        if(preg_match("/^.*(?=.{8,})(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z]).*$/", $password) === 0)
            return false;
//$errPass = 'Password must be at least 8 characters and must contain at least one lower case letter, one upper case letter and one digit';
        return true;
    }
    /*
    Returns description of password policy.
    */
    public function description()
    {
        return "Password shall contain at least one alphanumeric character, one uppercase character, and one lowercase character. Passwords shall be at least 8 characters long.";
    }

}


?>