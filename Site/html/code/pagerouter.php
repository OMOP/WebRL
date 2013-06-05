<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Contains class PageRouter. Class represents application strategy for building URLs.
 
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

class PageRouter
{
	var $page;
	var $action;
	var $parameters;
	/*
	Creates new instance of class PageRouter.
	*/
	function __construct()
	{
		$this->page = $this->getPage();
		$this->action = $this->getAction();
		$this->parameters = $this->getParameters();
	}
	/* 
	 Retreive name of view from the URL.
	*/
	private function getPage()
	{
		if (!array_key_exists('page', $_GET))
            return 'login';
        $page = $_GET['page'];
		if ($page == '')
			$page = 'login';
		return $page;
	}
	/* 
	 Retreive action that should be performed on the page from the URL.
	*/
	private function getAction()
	{
		if (!array_key_exists('action', $_GET))
            return 'view';
		$action = $_GET['action'];
		if ($action == '')
			$action = 'view';
		return $action;
	}
	/* 
	 Retreive action that should be performed on the page from the URL.
	*/
	private function getError()
	{
		if (!array_key_exists('error', $_GET))
            return '';
		$error_message = $_GET['error'];
		return $error_message;
	}
	/* 
	 Retreive parameters for action that should be performed on the page from the URL.
	*/
	private function getParameter($name)
	{
        if (!array_key_exists($name, $_POST))
            return null;
		return $_POST[$name];
	}
    /* 
	 Retreive parameters for action that should be performed on the page from the URL.
	*/
	private function getParameters()
	{
		return $_POST;
	}
    static function redirect($page, $action = false)
    {
//		debug_print_backtrace();
		header('Location: '.self::build($page, $action));
    }
    static function build($page, $action = false)
    {
		if (!$action)
			return '/index.php?page='.$page;
        return '/index.php?page='.$page.'&amp;action='.$action;
    }
    static function build_instances($page_num, $sort_mode, $sort_order)
    {
        return self::build('instances', $page_num.'_sort_'.$sort_mode.'_'.$sort_order);
    }
	static function build_amazonlog($page_num, $sort_mode, $sort_order)
    {
        return self::build('amazon_log', $page_num.'_sort_'.$sort_mode.'_'.$sort_order);
    }
    static function build_running_methods($page_num, $sort_mode, $sort_order, $filter_user_id)
    {
    	if ($filter_user_id == '')
    		$filter_user_id = 0;
    	return self::build('running_methods', $page_num.'_sort_'.$sort_mode.'_'.$sort_order.'_'.$filter_user_id);
    	if ($filter_user_id)
    	{
			return self::build('running_methods', $page_num.'_sort_'.$sort_mode.'_'.$sort_order.'_'.$filter_user_id);    		
    	}
        return self::build('running_methods', $page_num.'_sort_'.$sort_mode.'_'.$sort_order);
    }
    static function build_user_view($page_num, $sort_mode, $sort_order)
    {
        return self::build('user_view', $page_num.'_sort_'.$sort_mode.'_'.$sort_order);
    }
    static function build_user_history($organization_id, $user_id, $status, $page_num, $sort_mode, $sort_order)
    {
        return self::build('user_history', $user_id.'_'.$organization_id.'_'.$page_num.'_'.$sort_mode.'_'.$sort_order.'_'.$status);
    }
    static function build_security_log($page_num, $sort_mode, $sort_order)
    {
        return self::build('security_log', $page_num.'_sort_'.$sort_mode.'_'.$sort_order);
    }
    static function build_audit_trail($page_num, $sort_mode, $sort_order)
    {
        return self::build('audit_trail', $page_num.'_sort_'.$sort_mode.'_'.$sort_order);
    }
    static function build_user_connect_log($page_num, $sort_mode, $sort_order)
    {
        return self::build('user_connect_log', $page_num.'_sort_'.$sort_mode.'_'.$sort_order);
    }
    static function build_web_connect_log($page_num, $sort_mode, $sort_order)
    {
        return self::build('web_connect_log', $page_num.'_sort_'.$sort_mode.'_'.$sort_order);
    }
    static function build_website_event_log($page_num, $sort_mode, $sort_order)
    {
        return self::build('error_log', $page_num.'_sort_'.$sort_mode.'_'.$sort_order);
    }
    
    static function build_sys_instances($page_num, $sort_mode, $sort_order)
    {
        return self::build('system_instances', $page_num.'_sort_'.$sort_mode.'_'.$sort_order);
    }

}

?>