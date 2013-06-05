<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Page controller for /login page. Handles all user interaction within login page.
 
    (c)2009 Foundation for the National Institutes of Health (FNIH)
 
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
require_once("OMOP/WebRL/Configuration/WebRLConfiguration.php");

class LoginController extends PageController
{
    protected function processCore($page, $action, $parameters)
	{
		global $configurationManager; 
    	$configuration = new WebRLConfiguration($configurationManager);
    	
        switch($action)
		{
			case "view":
                if (Membership::get_current_user() != null)
                    Membership::clear_current_user();
                /*Check if we have error message stored in the session. */
                if (array_key_exists('error_message', $_SESSION))
                {
                    $this->view->assign('error_message', $_SESSION['error_message']);
                    $_SESSION['error_message'] = '';
                }
                else
                {
                    $this->view->assign('error_message', '');
                }
                /*Check if we have user login stored in the session. */
                if (array_key_exists('login_id', $_COOKIE))
                {
                    $internal_id = $_COOKIE['login_id'];
                    $last_user = new User();
                    if ($last_user->load('internal_id = ?', array($internal_id)))
                    {
                        $this->view->assign('login_id', $last_user->login_id);
                    }
                    else
                    {
                        $this->view->assign('login_id', '');
                    }
                }
                else
                {
                    $this->view->assign('login_id', '');
                }
			break;
			case "logout":
				$user = Membership::get_current_user();
                if (Membership::get_app_mode() == ApplicationMode::Admin)
                    PageRouter::redirect('loginadmin');
				else PageRouter::redirect('login');
                Membership::clear_current_user();
				exit();
			break;
			case "submit":
				$login = $parameters["login"];
				$password = $parameters["password"];
				$width = $parameters["width"];
				$height = $parameters["height"];
                $is_valid = Membership::is_user_valid($login, $password);
                $is_admin = Membership::is_administrator($login);
                
                $separate_login = $configuration->login_mode() == 'separate';
                $valid_page = (($page == 'loginadmin') && $is_admin)
                    || ($page == 'login');
                $seconds = 120;
                $max_attempts = 3;
                $minutes = ceil($seconds / 60);
                
                $bruteforce_pass = Membership::check_login_attempts_count($login, $max_attempts, $seconds);

                if ($is_valid && $valid_page && $bruteforce_pass)
                {
                    Membership::set_current_user($login);
                    if ($page == 'loginadmin')
                    {
                        Membership::set_app_mode(ApplicationMode::Admin);
                    }
                    else
                    {
                        Membership::set_app_mode(ApplicationMode::User);
                    }
                    Membership::update_last_login_time($login, true, $width, $height);
                    $user = Membership::get_current_user();
                    setcookie("login_id", $user->internal_id, time()+60*60*12);

                    header('Location: /public/instance');
                    exit();
                }
                else
                {
                    Membership::update_last_login_time($login, false, $width, $height);
                    $bruteforce_pass = Membership::check_login_attempts_count($login, $max_attempts, $seconds);
                    if (!$bruteforce_pass)
                    {
                        $_SESSION['error_message'] = "Too many attempts to enter password. <br/>Please wait ".$minutes." minute".($minutes > 1 ? 's' : '');
                        $user = new User();
                        $user->load("login_id = ?", array($login));
                        $smanager = new SecurityManager(DbManager::$db);
                        $smanager->generate_security_event(SecurityManager::ISSUE_TYPE_LOGIN_MORE_THAN_3_LOGINS, 'Too many attempts login as  '.$user->login_id.'.', $user->user_id, null, null, null);
                    }
                    else
                    {
                        $_SESSION['error_message'] = 'Invalid Login ID or Password';
                    }
                    PageRouter::redirect($page); // if user failed
                    exit();
                }
				return false;
			break;
			default:
		}
		return true;
	}
}

?>