<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Page controller for /user_view page. Handles all user interaction within user_view page.
 
    ©2009-10 Foundation for the National Institutes of Health (FNIH)
 
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


class UserViewController extends PageController
{
	protected function processCore($page, $action, $parameters)
	{
		switch($this->model->mode())
		{
			case "view":
                $currentuserpage = $this->model->current_page();
                $this->display($currentuserpage);
			break;
		}
		return true;
	}
	/*
	Displays specified user's list page 

	@currentuserpage Number of page that will be displayed.
	*/
    private function display($currentuserpage)
    {   	
        $pagesize = $this->model->pagesize();
        $allusers_count = $this->model->all_users_count(); // count($users);
        if (($currentuserpage - 1) * $pagesize >= $allusers_count)
            return;

        $sort_mode = $this->model->sort_mode();
        $sort_order = $this->model->sort_order();
        $data = $this->model->get_users($currentuserpage, $pagesize, $sort_mode, $sort_order);
        $this->view->assign("users", $data);
        
        $high_range = $allusers_count!=0 ? intval(($allusers_count + $pagesize - 1) / $pagesize) : 1;
        $pagerdata = range(1, $high_range);

        $this->view->assign("pagerdata", $pagerdata);
        $this->view->assign("currentuserpage", $currentuserpage);
        
        $this->view->assign("postfix", '_sort_'.$sort_mode.'_'.$sort_order);
        $this->view->assign("sort_mode", $sort_mode);
        $this->view->assign("sort_order", $sort_order);
    }
}
?>