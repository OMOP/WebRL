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
require_once("OMOP/WebRL/PageView.php");
require_once("OMOP/WebRL/Configuration/WebRLConfiguration.php");
require_once('OMOP/WebRL/Configuration/ConfigurationManager.php');

class Application
{
	var $router;
	var $registration_map;
	var $current_view;
	var $current_model;
	var $current_controller;

	function __construct($router)
	{
		$this->router = $router;
		$this->registration_map = array();
        $this->registerMVC();
	}
	/**
	 * Register all views that will be used in application.  
	 */
	function registerMVC()
	{	
		$this->register('login', 'Login', self::get_view('login'), new LoginController(), new LoginModel());
		$this->register('loginadmin', 'Admin Login', self::get_view('login'), new LoginController(), new LoginModel());
        $this->register('reset', 'Reset Password', self::get_view('reset'), new ResetController(), new ResetModel());
		$this->register('send_password', 'Send Password', self::get_view('send_password'), new SendPasswordController(), new SendPasswordModel());
        $this->register('error_screen', 'Error', self::get_view('error_screen'), new ErrorScreenController(), new ErrorScreenModel());

		$this->register('user_view', 'Users', self::get_view('user_view'), new UserViewController(), new UserViewModel());
		
        $this->register('user_history', 'User History', self::get_view('user_history'), new UserHistoryController(), new UserHistoryModel());
		$this->register('create_user', 'Create User', self::get_view('create_user'), new CreateUserController(), new CreateUserModel());
		$this->register('edit_user', 'Edit User', self::get_view('edit_user'), new EditUserController(), new EditUserModel());

		$this->register('notify_download_certificates', 'Notify Certificates Download', self::get_view('notify_download_certificates'), new StaticController(), new StaticModel());
		
		$this->register('edit_account', 'Edit Account', self::get_view('edit_account'), new EditAccountController(), new EditAccountModel());
        
        $this->register('organizations', 'Organizations', self::get_view('organizations'), new OrganizationsController(), new OrganizationsModel());
        $this->register('organization_charges', 'Organization charges', self::get_view('organization_charges'), new OrganizationPerUserChargesController(), new StaticModel());
        
        $this->register('create_organization', 'Create Organization', self::get_view('create_organization'), new CreateOrganizationController(), new CreateOrganizationModel());
        $this->register('edit_organization', 'Edit Organization', self::get_view('edit_organization'), new EditOrganizationController(), new EditOrganizationModel());
        
        // obsolete screens; they're rewritten on Zend now and can be deleted after testing
        $this->register('running_methods', 'Running methods', self::get_view('running_methods'), new RunningMethodsController(), new RunningMethodsModel());
        $this->register('instances', 'Running Instances', self::get_view('instances'), new InstancesController(), new InstancesModel());
        $this->register('tools_download', 'Client Install', self::get_view('tools_download'), new ToolsDownloadController(), new ToolsDownloadModel());
        $this->register('download', 'Download', self::get_view('download'), new DownloadController(), new DownloadModel());

	}	
	/**
	 * Get Smarty view with specified name.
	 * @param strnig $view_name Name of view which should be loaded.
	 * @return PageView that represent view with specified name.
	 */
	function get_view($view_name)
	{
		global $configurationManager; 
    	$configuration = new WebRLConfiguration($configurationManager);
			
    	$smarty_root = $configuration->root_folder();
    	return new PageView($configurationManager, $smarty_root, $view_name);
	}
	function register($viewName, $title, $view, $controller, $model)
	{
		$this->registration_map[$viewName] = 
			array(
			'title' => $title,
			'controller' => $controller,
			'view' => $view,
			'model' => $model);
	}
	function getController($viewName)
	{
		$entry = $this->registration_map[$viewName];
		if ($entry == null)
			throw new Exception('Invalid registration for view '.$viewName);
		return $entry["controller"];
	}
	/**
	 * Get registered view with specified name
	 * @param string $viewName Name of registered view which should be retreived.
	 * @return PageView Gets registered view with specified name. 
	 * @exception Exception Throws exception if view with give name does not exists. 
	 */
	function getView($viewName)
	{
		$entry = $this->registration_map[$viewName];
		if ($entry == null)
			throw new Exception('Invalid registration for view '.$viewName);
		return $entry["view"];
	}
	function getModel($viewName)
	{
		$entry = $this->registration_map[$viewName];
		if ($entry == null)
			throw new Exception('Invalid registration for view '.$viewName);
		return $entry["model"];
	}
	function getTitle($viewName)
	{
		$entry = $this->registration_map[$viewName];
		if ($entry == null)
			throw new Exception('Invalid registration for view '.$viewName);
		return $entry["title"];
	}
	function isViewRegistered($view)
	{
		$entry = $this->registration_map[$view];
		if ($entry == null)
			return false;
		return true;
	}
	function process()
	{
		global $configurationManager; 
    	$configuration = new WebRLConfiguration($configurationManager);
			
		$page = $this->router->page;
		//check that user is logged in, has access to page and page that user .
		if (!PageAuthorization::has_access($page)
			//|| !$this->isViewRegistered($page)
			)
        {
			$page = 'login';
        }
        else
        {
            $user = Membership::get_current_user();
            if ($user != null)
            {
            	// Update expiration time
            	setcookie("login_id", $user->internal_id, time()+60*60*12);
                if ($page != 'edit_account' && $page != 'login' && $user->password_is_expired)
                {
                    PageRouter::redirect('edit_account');
                    die();
                }
            }
        }
         
		$this->setup($page);
		
		$continue_processing = true;
		$title = $this->getTitle($page);
        if ($this->current_controller)
		{
            $action = $this->router->action;
            $parameters = $this->router->parameters;
            $this->current_controller->set_model($this->current_model);
			$this->current_controller->set_view($this->current_view);
			$this->current_model->set_parameters($page, $action, $parameters);
            $this->current_view->assign("title", $title);
            $continue_processing = $this->current_controller->process($page, $action, $parameters);
		}

		if ($continue_processing)
		{
			$template_output = $this->current_view->fetch();
			$main_view = new PageView($configurationManager, $configuration->root_folder(), 'main');
			$user = Membership::get_current_user();
			
			$main_view->assign("product_name", $configuration->product_name());
			$main_view->assign("support_mail_link", generateProtection($configuration->support_mail(), $configuration->displayed_support_mail()));
			$main_view->assign("support_phone", $configuration->support_phone());
			
			$main_view->assign("current_user", $user);
			$main_view->assign("application_mode", Membership::get_app_mode());
            $main_view->assign("title", $title);
			$main_view->assign("page", $page);
			$main_view->assign("content", $template_output);
			$main_view->display();
		}
	}
	function fetch($pageName)
	{
		$this->setup($pageName);
		$this->current_view->fetch();
	}
	function setup($pageName)
	{
		$this->current_view = $this->getView($pageName);
		$this->current_controller = $this->getController($pageName);
		$this->current_model = $this->getModel($pageName);
	}
}

?>