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
require_once("OMOP/WebRL/MailManager.php");
require_once("OMOP/WebRL/Configuration/WebRLConfiguration.php");

class SendPasswordController extends PageController
{
    protected function processCore($page, $action, $parameters)
	{
        global $configurationManager; 
    	$configuration = new WebRLConfiguration($configurationManager);
    	
        $mode = $this->model->mode();
        $this->view->assign("mode", $mode);
        switch($mode)
        {
            case "view":
            	$this->view->assign('support_mail_link', 
            		generateProtection($configuration->support_mail(),'here')
            		);
                break;
            case "recovery":
                $email = $this->model->email();
                $user = new User();
                if (!$user->load("email = ? AND active_flag='Y'", array($email)))
                {
                    $success = false;
                }
                else
                {
                    $success = true;
                }
                if ($success)
                {
                    $unique_key = uniqid("", true);
                    $pr = new ResetPaswordRequest();
                    $pr->email = $email;
                    $pr->unique_key = $unique_key;
                    $pr->request_date = gmdate('c');
                    $pr->expiration_date = $this->add_date($pr->request_date, 7);
                    $pr->save();
                    
                    //$new_password = $this->generate_password();
                    //$user->set_password($new_password);
                    //$user->save();
                    
                    $mailer = new MailManager($configurationManager);
                    $mailer->send_recovery_password_request($user, $unique_key);
                }
                $this->view->assign("success", $success);
                //PageRouter::redirect('login');
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