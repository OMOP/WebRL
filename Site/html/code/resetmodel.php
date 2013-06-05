<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Page model for /send_password page. Manage all user data within /send_password page.
 
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

class ResetModel extends PageModel
{
    const VIEW_MODE_TEMPLATE = "/(\d+)_([0-9a-f]+.[0-9a-f]+)/";
    function load()
    {
    }
    function mode()
    {
        if (preg_match(self::VIEW_MODE_TEMPLATE, $this->action))
        {
            return "view";
        }
        return $this->action;
    }
    function user_id()
    {
        if ($this->mode() != "view")
            throw new Exception("user_id available only in the 'view' mode.");
        if ($this->action == "view")
            return 1;
        $user_id = preg_replace(self::VIEW_MODE_TEMPLATE, '$1', $this->action);
        return intval($user_id);
    }
    function unique_key()
    {
        if ($this->mode() != "view")
            throw new Exception("unique_key available only in the 'view' mode.");
        if ($this->action == "view")
            return 1;
        $unique_key = preg_replace(self::VIEW_MODE_TEMPLATE, '$2', $this->action);
        return $unique_key;
    }
}

?>