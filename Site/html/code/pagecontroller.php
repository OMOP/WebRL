<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Base class for page controller. Control how page model mapped to page view.
 
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

abstract class PageController
{
	var $model;
	var $view;
	/*
	 Creates new instance of class PageController
	*/
	function __construct()
	{
	}
    /*
	 Sets model that will be used by this controller.
	*/	
	function set_model($model)
	{
		$this->model = $model;
		
	}
	/*
	 Sets view that will be used by this controller.
	*/	
	function set_view($view)
	{
		$this->view = $view;
	}
	/*
	 Performs processing user request based on the current model state.

	 Params:
	 $page			Name of page that will be processed.
	 $action		Name of operation that should be performed.
	 $parameters	Parameters of operation that should be performed.
	*/
	function process($page, $action, $parameters)
	{
		if ($this->model == null)
			throw new Exception('Model not set for the controller. Call setModel() first.');
		$user = Membership::get_current_user();
        $this->view->assign("current_user", $user);
        $this->view->assign("application_mode", Membership::get_app_mode());

        $this->view->assign("page", $page);
        $date_format = SiteConfig::get()->default_date_format;
        $this->view->assign("date_format", $date_format);
		return $this->processCore($page, $action, $parameters);
	}
	/*
	 Implementation that processing that specific to the page.
	 Should be implemented by custom controllers.

	 Params:
	 $page			Name of page that will be processed.
	 $action		Name of operation that should be performed.
	 $parameters	Parameters of operation that should be performed.
	*/
	abstract protected function processCore($page, $action, $parameters);
	protected function set_back_url($default_back_url)
	{
		$back_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $default_back_url;
		$_SESSION['back_url'] = $back_url;
	}
	protected function go_to_back_url($default_back_url)
	{
		$back_url = isset($_SESSION['back_url']) ? $_SESSION['back_url'] : $default_back_url;
	    unset($_SESSION['back_url']);
		header("Location: ".$back_url);
	}
}

?>