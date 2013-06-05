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

class ResetController extends PageController
{
    protected function processCore($page, $action, $parameters)
	{
        if (Membership::get_current_user() != null)
            Membership::clear_current_user();
        $mode = $this->model->mode();
        $this->view->assign("mode", $mode);
        switch($mode)
        {
            case "view":
                $user_id = $this->model->user_id();
                $unique_key = $this->model->unique_key();
                $this->view->assign("user_id", $user_id);
                $this->view->assign("unique_key", $unique_key);
                break;
            case "reset":
                $user_id = $parameters['user_id'];
                $unique_key = $parameters['unique_key'];
                $user = new User();
                $success = $user->load("user_id = ?", array($user_id));
                if ($success)
                {
                    $pr = new ResetPaswordRequest();
                    $success = $pr->load('unique_key = ? and email= ? and expiration_date > UTC_TIMESTAMP() ORDER BY reset_password_request_id DESC', array($unique_key, $user->email, gmdate('c')));

                    $password = $parameters['password'];
                    $confirmation = $parameters['confirmpassword'];
                    if ($password != $confirmation)
                    {
                        $has_validation_errors = true;
                        $errors["confirmpassword"] = "Please enter same value for password again";
                    }
                    if (!$success)
                    {
                        $has_validation_errors = true;
                        $errors["password"] = "Your request for change password is expired or mailformed.";
                    }
                    if ($has_validation_errors)
                    {
                        $this->view->assign('errors', $errors);
                    }
                    else
                    {
                        $user->set_password($password);
                        $pr->expiration_date = gmdate('c');
                        $pr->save();
                        $user->save();
                    }
                    $success = !$has_validation_errors;
                }
                if ($success)
                {
	                $width = $parameters["width"];
					$height = $parameters["height"];
	                setcookie("login_id", $user->internal_id, time()+3600);
	                Membership::set_current_user($user->login_id);
	                Membership::update_last_login_time($user->login_id, true, $width, $height);                
	                PageRouter::redirect('client_install');
                }
                else
                {
                	PageRouter::redirect('login');
                }
                return false;
                break;
        }
        return true;
    }
    function add_date($givendate,$day=0,$mth=0,$yr=0) 
    {
        $cd = strtotime($givendate);
        $newdate = date('Y-m-d h:i:s', mktime(date('h',$cd),
            date('i',$cd), date('s',$cd), date('m',$cd)+$mth,
            date('d',$cd)+$day, date('Y',$cd)+$yr));
        return $newdate;
    }
}
?>