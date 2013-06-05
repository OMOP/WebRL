<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Page controller for /edit_account page. Handles all user interaction within /launch page.
 
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

class EditAccountController extends PageController
{
    protected function processCore($page, $action, $parameters)
    {
        switch($action)
        {
            case "view":
                $user = $this->model->user;
                $this->display($user);
            break;
            case "submit":
                
                $email = isset($parameters['email']) ? $parameters['email'] : '';
                $first_name = isset($parameters['first_name']) ? $parameters['first_name'] : '';
                $last_name = isset($parameters['last_name']) ? $parameters['last_name'] : '';
                $phone = isset($parameters['phone']) ? $parameters['phone'] : '';
                $organization_id = isset($parameters['organization_id']) ? $parameters['organization_id'] : '';
                $title = isset($parameters['title']) ? $parameters['title'] : '';
                $password = isset($parameters['password']) ? $parameters['password'] : '';
                $confirmation = isset($parameters['confirmation']) ? $parameters['confirmation'] : '';
                $certificate = isset($parameters['certificate']) ? $parameters["certificate"] : '';
                $share_users = isset($parameters['share_users']) ? $parameters["share_users"] : array();

                $has_validation_errors = false;
                
                $sm = new SecurityManager(DbManager::$db);
                $policy = $sm->get_password_policy();

                $errors = array();

                if ($password && !$policy->is_valid($password))
                {
                    $has_validation_errors = true;
                    $errors["password"] = $policy->description();
                }
                if ($password && !$sm->is_password_valid($this->model->user->user_id, md5($password)))
                {
                    $has_validation_errors = true;
                    $errors["password"] = "Password reuse is not allowed";
                }
                if ($email == '')
                {
                    $has_validation_errors = true;
                    $errors["email"] = "Email is required";
                }
                if (preg_match("/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i", $email) === 0)
                {
                    $has_validation_errors = true;
                    $errors["email"] = "Please enter a valid email address.";
                }
                if ($first_name == '')
                {
                    $has_validation_errors = true;
                    $errors["first_name"] = "First name is required";
                }
                if ($last_name == '')
                {
                    $has_validation_errors = true;
                    $errors["last_name"] = "Last name is required";
                }
                if ($phone == '')
                {
                    $has_validation_errors = true;
                    $errors["phone"] = "Phone is required";
                }
                if ($password != $confirmation)
                {
                    $has_validation_errors = true;
                    $errors["confirmation"] = "Please enter same value for password again";
                }

                if ($has_validation_errors)
                {
                    $user = $this->model->user;
                    $this->apply($user, $email, $first_name, $last_name, $phone, $organization_id, $title, $password);
                    $this->display($user);
                    $this->view->assign('errors', $errors);
                }
                else
                {
                    $user = Membership::get_current_user();
        
                    $this->save($user, $email, $first_name, $last_name, $phone, $organization_id, $title, $password, $share_users, null);
                    if ($password != '' && $password != null)
                    {
                    	PageRouter::redirect('client_install');
                    }
                    else
                    {
                    	PageRouter::redirect('instances');
                    }
                }
                break;
        }           
        return true;
    }
    function display($user)
    {
        $this->view->assign('user', $user);
        $this->view->assign('dataset_types', $this->model->dataset_types);
        $this->view->assign('allowed_dataset_types', $this->model->allowed_dataset_types);
        $this->view->assign('software_types', $this->model->software_types);
        $this->view->assign('allowed_software_types', $this->model->allowed_software_types);
        $this->view->assign('sharing_allowed', $this->model->sharing_allowed);
        $this->view->assign('usersToShare', $this->model->usersToShare);
        $this->view->assign('allowed_users', $this->model->allowed_users);
        $this->view->assign('sharing_their_storage_users', $this->model->sharing_their_storage_users);

        $this->view->assign('da_ids', $this->model->da_ids);
        $this->view->assign('sa_ids', $this->model->sa_ids);
    }
    function apply($user, $email, $first_name, $last_name, $phone, $organization_id, $title)
    {
        $user->email = $email;
        $user->first_name = $first_name;
        $user->last_name = $last_name;
        $user->phone = $phone;
        $user->organization_id = $organization_id;
        $user->title = $title;
        $user->updated_date = gmdate('c');
        $user->updated_by = $user->login_id;
    }
    function save($user, $email, $first_name, $last_name, $phone, $organization_id, $title, $password, $share_users, $certificate)
    {
        $manager = new UserManager(DbManager::$db);
        
        $manager->save_user($user->user_id, $user->login_id, $first_name, $last_name, $email, $phone, $organization_id, $title, $user->active_flag, $user->admin_flag, $user->svn_access_flag, $user->user_money, $user->num_instances, $password, null, null, null);
        $manager->set_storage_access($user->user_id, $share_users);
        
        $manager->update_dav_authorization();
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
