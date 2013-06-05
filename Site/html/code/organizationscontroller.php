<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    17 August 2010
 
    Page controller for /dataset_type_view page. 
    Handles all user interaction within dataset_type_view page.
 
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

class OrganizationsController extends PageController
{
	protected function processCore($page, $action, $parameters)
	{
		switch($this->model->mode())
		{
            case "delete":
                $this->delete_organization($this->model->org_id());
                PageRouter::redirect('organizations');
                break;
			case "view":
                $currentuserpage = $this->model->current_page();
                $this->display($currentuserpage);                
			break;
            
		}
		return true;
	}
	/*
	Displays specified prgainzations's list page 

	@currentorganizationpage Number of page that will be displayed.
	*/
    private function display($currentorganizationpage)
    {   	
        $pagesize = $this->model->pagesize();
        $allorganizations_count = $this->model->all_organizations_count(); // count($organizations);
        if (($currentorganizationpage - 1) * $pagesize >= $allorganizations_count)
            return;

        $sort_mode = $this->model->sort_mode();
        $sort_order = $this->model->sort_order();
        $data = $this->model->get_organizations($currentorganizationpage, $pagesize, $sort_mode, $sort_order);
        $this->view->assign("organizations", $data);
        
        $high_range = $allorganizations_count!=0 ? intval(($allorganizations_count + $pagesize - 1) / $pagesize) : 1;
        $pagerdata = range(1, $high_range);

        $this->view->assign("pagerdata", $pagerdata);
        $this->view->assign("currentuserpage", $currentorganizationpage);
        $this->view->assign("postfix", '_sort_'.$sort_mode.'_'.$sort_order);
        $this->view->assign("sort_mode", $sort_mode);
        $this->view->assign("sort_order", $sort_order);
    }
    
    private function delete_organization($org_id) {
        $om = new OrganizationManager();
        
        $om->delete_organization($org_id);
    }
}
?>