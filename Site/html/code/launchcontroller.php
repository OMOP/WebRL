<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Page controller for /launch page. Handles all user interaction within /launch page.
 
    пїЅ2009 Foundation for the National Institutes of Health (FNIH)
 
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
require_once('OMOP/WebRL/MemcachePasswordManager.php');

class LaunchController extends PageController
{
    protected function processCore($page, $action, $parameters)
    {
        switch($action)
        {
            case "view":
                $user = Membership::get_current_user();
				
                $this->display($user);
            break;
            case "submit":
                $user = Membership::get_current_user();
                
                $instance_type = isset($parameters['instance_type']) ? $parameters['instance_type'] : '';
                $instance_names = isset($parameters['instance_name']) ? $parameters['instance_name'] : array();
                $dataset_types = isset($parameters['database_type']) ? $parameters['database_type'] : array();
                $software_type = isset($parameters['tool']) ? $parameters['tool'] : '';
                $number_of_instances = isset($parameters['number_of_instances']) ? (int)$parameters['number_of_instances'] : '';
                $temporary_ebs = isset($parameters['temporary_ebs']) ? (int)$parameters['temporary_ebs'] : '0';
                $create_user_ebs = isset($parameters['create_user_ebs']) ? $parameters['create_user_ebs'] : 'N';
                $checkout_method_code = isset($parameters['checkout_method_code']) ? $parameters['checkout_method_code'] : 'N';
                $has_validation_errors = false;
                $attach_shared_storage = isset($parameters['attach_shared_storage']) ? $parameters['attach_shared_storage'] : 'N';
                $errors = array();
        
                if (count($instance_names) == 0)
                {
                    $has_validation_errors = true;
                    $errors["instance_name"] = "Instance name is required";
                }
                if ($software_type == '')
                {
                    $has_validation_errors = true;
                    $errors["tool"] = "Tool is required";
                }
                if ($instance_type == '')
                {
                    $has_validation_errors = true;
                    $errors["instance_type"] = "Instance type is required";
                }
                if ($number_of_instances == '')
                {
                    $has_validation_errors = true;
                    $errors["number_of_instances"] = "Number of instances is required";
                }
                if ($number_of_instances <= 0)
                {
                    $has_validation_errors = true;
                    $errors["number_of_instances"] = "Number of instances is should be positive number.";
                }
                if (!InstanceManager::user_could_launch_instances($user, $number_of_instances))
                {
                    $has_validation_errors = true;
                    $errors["number_of_instances"] = "You exceeded limit on running instances.";
                }
                if ($number_of_instances != count($instance_names))
                {
                    $has_validation_errors = true;
                    $errors["number_of_instances"] = "Number of instances does not match count of names provided.";
                }
                
        		foreach($dataset_types as $dt)
        		{
        			$password_key = 'dataset_type_'.$dt.'_password';
                    $dataset_type = new DatasetType();
                    $dataset_type->load('dataset_type_id = ?', $dt);
        			if ($dataset_type->encrypted_flag == '1')
                    {
                    	$password = isset($parameters[$password_key]) ? $parameters[$password_key] : '';
                    	if ($dataset_type->password_hash != md5($password))
                    	{
		                    $has_validation_errors = true;
        		            $errors[$password_key] = "Invalid password.";
                    	}
                    }
                }
                
                $st = new SoftwareType();
                    
                $st->load("software_type_id = ?", array($software_type));

                $request = new InstanceRequest();
                $request->user_id = $user->user_id;
                $request->software_type_id = $st->software_type_id;
                $request->instance_size_id = $instance_type;
                $request->num_instances = $number_of_instances;
                $request->method_launch_flag = 'N';
                $request->checkout_method_code = $checkout_method_code;
                $request->temporary_ebs_entry_id = $temporary_ebs;
                $request->create_user_ebs = $create_user_ebs;
                $request->created_by = $user->login_id;
                $request->attach_shared_storage = $attach_shared_storage;
                $request->created_date = gmdate('c');
        		if (!InstanceManager::check_user_budget($user, $request, $number_of_instances))
                {
                    $has_validation_errors = true;
                    $errors["number_of_instances"] = "You cannot go over the budget.";
                }                

                if ($has_validation_errors)
                {
	                $this->assign_entered_data($instance_type, $instance_names, $dataset_types, $software_type, 
	                	$number_of_instances, $temporary_ebs, $create_user_ebs, $checkout_method_code, 
	                	$attach_shared_storage);
                	$this->display($user);
                    $this->view->assign('errors', $errors);
                }
                else
                {
                    $request->save();
                    
                    foreach($dataset_types as $dt)
                    {
                    	$password_key = 'dataset_type_'.$dt.'_password';
                        $ird = new InstanceRequestDataset();
                        $ird->instance_request_id = $request->instance_request_id;
                        $ird->dataset_type_id = $dt;
                        if (isset($parameters[$password_key]))
                        {
                        	$password = $parameters[$password_key]; 
                        	$this->save_password($ird, $number_of_instances, $password);
                        }
                        $ird->save();
                    }
                    
                    $instance_size = new InstanceSize();
                    $instance_size->Load('instance_size_id = ?', $instance_type);
                    
                    $running_instance_list = InstanceManager::launch_instances($st->software_type_image, $instance_size->aws_instance_size_name, $number_of_instances, $instance_names);
                    
                    if (is_array($running_instance_list)) // Check that we correctly run instance set.
                    {
                        $new_instances = $this->create_instance_to_process($number_of_instances, $instance_names, $request, $running_instance_list);
                        sleep(10);
                        $new_instance_names = $this->process_instances($new_instances, $new_instances,$running_instance_list,$instance_names);
                        
                        if (count($new_instance_names) != 0)
                        {
                            $running_instance_list = InstanceManager::launch_instances($st->software_type_image, $instance_size->aws_instance_size_name, count($new_instance_names), $new_instance_names);
                            $new_old = array_intersect($instance_names, $new_instance_names);
                            sleep(10);
                            $new_instance_names2 = $this->process_instances($new_old, $new_instances,$running_instance_list,$instance_names);
                               
                            if (count($new_instance_names2) != 0)
                            {
                                $running_instance_list = InstanceManager::launch_instances($st->software_type_image, $instance_size->aws_instance_size_name, count($new_instance_names2), $new_instance_names2);
                                $new_old = array_intersect($instance_names, $new_instance_names2);
                                sleep(10);
                                $new_instance_names3 = $this->process_instances($new_old, $new_instances,$running_instance_list,$instance_names);
                                
                                $this->raise_error($new_instance_names3, $instance_names);    
                                $this->mark_failed($new_instance_names3, $new_instances);
                         	}
                         }
                    }
                    else
                    {
                    	$this->create_dummy_instances($number_of_instances, $instance_names, $request);
                    }
                    
                    PageRouter::redirect('instances');
                }
                break;
        }           
        return true;
    }
    function save_password($instance_request_dataset, $instances_count, $password)
    {
    	global $configurationManager; 
    	$passwordManager = new MemcachePasswordManager($configurationManager);
    	$passwordManager->save_password($instance_request_dataset, $instances_count, $password);
    }
	/**
	 * Assign user selection from previous request.
	 * 
	 * @param unknown_type $instance_type
	 * @param unknown_type $instance_names
	 * @param unknown_type $dataset_types
	 * @param unknown_type $software_type
	 * @param unknown_type $number_of_instances
	 * @param unknown_type $temporary_ebs
	 * @param unknown_type $create_user_ebs
	 * @param unknown_type $checkout_method_code
	 */
	function assign_entered_data($instance_type, $instance_names, $dataset_types, $software_type, $number_of_instances, 
		$temporary_ebs, $create_user_ebs, $checkout_method_code, $attach_shared_storage)
	{
		$this->view->assign('selected_instance_types', $instance_type);
		$this->view->assign('selected_dataset_types', $dataset_types);
		$this->view->assign('selected_software_type', $software_type);
		$this->view->assign('selected_temporary_ebs', $temporary_ebs);
		$this->view->assign('selected_create_user_ebs', $create_user_ebs);
		$this->view->assign('selected_checkout_method_code', $checkout_method_code);
		$this->view->assign('selected_attach_shared_storage', $attach_shared_storage);
		
		$this->view->assign('number_of_instances', $number_of_instances);
		$this->view->assign('instance_names', $instance_names);
	}
	
    /**
     * Assign common values for specific user.
     * @param User $user
     */
    function display($user)
    {
		// Fill select inputs with options
        $this->view->assign('instance_types', $this->model->instance_types);
        $this->view->assign('dataset_types', $this->model->dataset_types);
        $this->view->assign('software_types', $this->model->software_types);
        $this->view->assign('temporary_ebs_entries', $this->model->temporary_ebs_entries);
            
        $usa = new UserStorageAccess();
    	$users = $usa->find('grantee_id = ?', $user->user_id);
    	$other_storage_available = count($users) != 0;
    	
        $this->view->assign('other_storage_available', $other_storage_available);        
        $this->view->assign('user', $user);
    }
    function raise_error($new_instance_names3, $instance_names)
    {    
		if (count($new_instance_names3) != 0)
     	{
        	$names_list = "";
            foreach($new_instance_names3 as $name)
            {
            	if ($names_list == "")
                {
                	$names_list = $instance_names[$name];
                }
                else
                {
                	$names_list += "," + $instance_names[$name];
				}
            }
        	throw new Exception("Cannot launch instances with names ".$names_list." cannot start. Try contact administrator to resolve this issue.");
		}
    }
    function create_instance_to_process($number_of_instances, $instance_names, $request, $running_instance_list)
    {
    	$new_instances = array();
        for($i = 0; $i < $number_of_instances; $i++)
        {
            $instance = new Instance();
			$instance->instance_request_id = $request->instance_request_id;
            $instance->start_date = $running_instance_list[$i]->getLaunchTime();
			
            $instance_charge = InstanceManager::get_instance_hour_price($request);
            $storage_charge = InstanceManager::get_storage_hour_price($request);
            
            $instance->instance_hour_charge = $instance_charge;
            $instance->storage_hour_charge = $storage_charge;
            
            $instance->status_flag='F';
            $instance->Save();
        	$new_instances[] = $instance;
    	}
    	return $new_instances;
    }
    function process_instances($mapping, $new_instances, $running_instance_list,$instance_names)
    {
    	$new_instance_names = array();
        foreach($mapping as $i => $instance)
        {
        	$instance = $new_instances[$i];
            $amazon_instance_id = $running_instance_list[$i]->getInstanceId();
            $status = InstanceManager::get_status($amazon_instance_id);
            if ($status == "pending" || $status == "running")
            {
            	$instance->assigned_name = $instance_names[$i];
                $instance->amazon_instance_id = $amazon_instance_id;
                
                $instance->status_flag='I';
                $instance->save();
            }
            else
            {
            	$new_instance_names[] = $instance_names[$i];
        	}
     	}
     	return $new_instance_names;
    }

    function mark_failed($mapping, $new_instances)
    {
    	foreach($mapping as $i => $instance)
        {
        	$instance = $new_instances[$i];
        	$instance->terminate_date = null;
            $instance->status_flag='I';
            $instance->save();
     	}
    }
    function create_dummy_instances($number_of_instances, $instance_names, $request)
    {
    	for($i = 0;$i < $number_of_instances; $i++)
        {
        	$instance = new Instance();
            $instance->instance_request_id = $request->instance_request_id;
            $instance->assigned_name = $instance_names[$i];
            $instance->amazon_instance_id = "fi-".uniqid();
            $instance->public_dns = 'ec2-'.rand(130, 240)."-".rand(0,255)."-".rand(0,255)."-".rand(0,255)."amazonws.com";
            $instance->start_date = gmdate('c');
            $instance->terminate_date = null;
            $instance->status_flag='A';
            $instance->Save();
       }
    }
}

?>