<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Page controller for /launch_method page. Handles all user interaction within /launch_method page.
 
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
require_once('OMOP/WebRL/MethodManager.php');
require_once('OMOP/WebRL/MemcachePasswordManager.php');

class LaunchMethodController extends PageController
{
    protected function processCore($page, $action, $parameters)
    {
        require_once('OMOP/WebRL/MethodManager.php');
    	global $configurationManager;
    	
    	switch($action)
        {
            case "view":
                $user = Membership::get_current_user();

                $this->display($user);
            break;
            case "submit":
        		$user = Membership::get_current_user();
                $instance_type = isset($parameters['instance_type']) ? $parameters['instance_type'] : '';
                //$instance_names = isset($parameters['instance_name']) ? $parameters['instance_name'] : array();
                $dataset_types = isset($parameters['database_type']) ? $parameters['database_type'] : '';
                $software_type = isset($parameters['tool']) ? $parameters['tool'] : '';
                //$number_of_instances = isset($parameters['number_of_instances']) ? (int)$parameters['number_of_instances'] : '';
                $temporary_ebs = isset($parameters['temporary_ebs']) ? (int)$parameters['temporary_ebs'] : '';
                
                $method_name = isset($parameters['method_name']) ? $parameters['method_name'] : '';
                $run_parameter = isset($parameters['run_parameters']) ? $parameters['run_parameters'] : '';
                $launch_method = isset($parameters['launch_method']) ? $parameters['launch_method'] : 'N';
                $terminate_after_success = isset($parameters['terminate_after_success']) ? $parameters['terminate_after_success'] : 'N';
                $create_user_ebs = isset($parameters['create_user_ebs']) ? (int)$parameters['create_user_ebs'] : 'N';
                
                $replacement_parameters = isset($parameters['replacement_parameters']) ? $parameters['replacement_parameters'] : '';
                $override_parameters = isset($parameters['override_parameters']) ? $parameters['override_parameters'] : 'Y';
                
                $has_validation_errors = false;
                
                $method_manager = new MethodManager($configurationManager);
    			$method_parameters = $method_manager->get_method_parameters($method_name);
    			$selected_dataset = $dataset_types[0];
    			$dataset = new DatasetType();
    			$dataset->load('dataset_type_id = ?', array($selected_dataset));
    			
    			$parameters_count = $run_parameter[0] == '' ? count($method_parameters) : count($run_parameter);
                
    			if ($launch_method != 'Y')
                {
                	$number_of_instances = 1;
                	if ($parameters_count == 1) $param = $run_parameter[0];
                	else $param = $parameters_count.'_param';
                	$instance_name = $method_name.'_'.$param.'_'.$dataset->dataset_method_name.'_'.$parameters_count;
                	$instance_name = self::get_unique_name($instance_name); 
                	$instance_names = array($instance_name);
                	$instance_names_map = array($instance_name => $run_parameter);
                }
                else
                {
                	if ($run_parameter[0] == '') $run_parameter = $method_parameters;
                	$number_of_instances = $parameters_count;
					$instance_names = array();
					$instance_names_map = array();            	
					foreach($run_parameter as $mp)
					{
						$instance_name = $method_name.'_'.$mp.'_'.$dataset->dataset_method_name;
						$instance_name = self::get_unique_name($instance_name);
						$instance_names[] = $instance_name;
						$instance_names_map[$instance_name] = array($mp); 					
					}
                }
                
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
                if ($dataset_types == '')
                {
                    $has_validation_errors = true;
                    $errors["database_type"] = "Dataset is required";
                }
                if ($instance_type == '')
                {
                    $has_validation_errors = true;
                    $errors["instance_type"] = "Instance type is required";
                }
                if ($method_name == '')
                {
                    $has_validation_errors = true;
                    $errors["method_name"] = "Methods name should be selected.";
                }
                if (!InstanceManager::user_could_launch_instances($user, $number_of_instances))
                {
                    $has_validation_errors = true;
                    $errors["method_name"] = "You exceeded limit on running instances.";
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
                $request->method_launch_flag = 'Y';
                $request->checkout_method_code = 'Y';
                $request->attach_shared_storage = 'N';
                $request->terminate_after_success = $terminate_after_success;
                $request->temporary_ebs_entry_id = $temporary_ebs;
                $request->create_user_ebs = $create_user_ebs;
                $request->created_by = $user->login_id;
                $request->created_date = gmdate('c');
        		if (!InstanceManager::check_user_budget($user, $request, $number_of_instances))
                {
                    $has_validation_errors = true;
                    $errors["method_name"] = "You cannot go over the budget.";
                }
                    
                if ($has_validation_errors)
                {
                	$this->assign_entered_data($instance_type, $selected_dataset, $software_type, 
                		$temporary_ebs, $create_user_ebs, $method_name, 
                		$run_parameter, $launch_method, $terminate_after_success, 
                		$replacement_parameters, $override_parameters,
                        $method_manager);
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
                        	//$ird->dataset_pwd = $parameters['dataset_type_'.$dt.'_password'];
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
                        $new_instance_names = $this->process_instances($new_instances, $new_instances,$running_instance_list,$instance_names, $method_name, $instance_names_map, $replacement_parameters, $override_parameters);
                        
                        if (count($new_instance_names) != 0)
                        {
                            $running_instance_list = InstanceManager::launch_instances($st->software_type_image, $instance_size->aws_instance_size_name, count($new_instance_names), $new_instance_names);
                            $new_old = array_intersect($instance_names, $new_instance_names);
                            sleep(10);
                            $new_instance_names2 = $this->process_instances($new_old, $new_instances,$running_instance_list,$instance_names, $method_name, $instance_names_map, $replacement_parameters, $override_parameters);
                               
                            if (count($new_instance_names2) != 0)
                            {
                                $running_instance_list = InstanceManager::launch_instances($st->software_type_image, $instance_size->aws_instance_size_name, count($new_instance_names2), $new_instance_names2);
                                $new_old = array_intersect($instance_names, $new_instance_names2);
                                sleep(10);
                                $new_instance_names3 = $this->process_instances($new_old, $new_instances,$running_instance_list,$instance_names, $method_name, $instance_names_map, $replacement_parameters, $override_parameters);
                                
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
    function get_unique_name($name)
    { 
    	$user = Membership::get_current_user();
    	
    	if (!InstanceManager::is_instance_available($user->user_id, $name))
    	{
			$new_name_search = InstanceManager::get_unique_name($user->user_id, $name.'_0');
	        if ($new_name_search['available_index'] != 0)
	        {
	        	return $new_name_search['available_name'];
	        } 
    	}
        return $name;
    }
	
	/**
	 * Assign user selection from previous request.
	 * 
	 * @param unknown_type $instance_type
	 * @param unknown_type $dataset_types
	 * @param unknown_type $software_type
	 * @param unknown_type $temporary_ebs
	 * @param unknown_type $create_user_ebs
	 * @param unknown_type $method_name
	 * @param unknown_type $run_parameter
	 * @param unknown_type $launch_method
	 * @param unknown_type $terminate_after_success
	 */
	function assign_entered_data($instance_type, $selected_dataset, $software_type, $temporary_ebs, $create_user_ebs, 
		$method_name, $run_parameter, $launch_method, $terminate_after_success, $replacement_parameters, $override_parameters,
        $method_manager)
	{
		$this->view->assign('selected_instance_types', $instance_type);
		$this->view->assign('selected_dataset', $selected_dataset);
		$this->view->assign('selected_software_type', $software_type);
		$this->view->assign('selected_temporary_ebs', $temporary_ebs);
		$this->view->assign('selected_create_user_ebs', $create_user_ebs);
		$this->view->assign('selected_method_name', $method_name);

		$method_parameters = $method_manager->get_method_parameters($method_name);
		$this->view->assign('method_parameters', $method_parameters);
		
		$this->view->assign('selected_run_parameter', $run_parameter);
		$this->view->assign('selected_launch_method', $launch_method);
		$this->view->assign('selected_terminate_after_success', $terminate_after_success);
	}
    function display($user)
    {
        // Fill select inputs with options
        $this->view->assign('instance_types', $this->model->instance_types);
        $this->view->assign('methods', $this->model->methods);
        $this->view->assign('method_parameters', $this->model->method_parameters);
        $this->view->assign('dataset_types', $this->model->dataset_types);
        $this->view->assign('vocabulary_dt', $this->model->vocabulary_dataset);
        $this->view->assign('software_types', $this->model->software_types);
        $this->view->assign('method_replacements', $this->model->method_replacements);
        $this->view->assign('temporary_ebs_entries', $this->model->temporary_ebs_entries);
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
			
            $instance_size = $request->instance_size;
            $instance_charge = 0;
            if ($instance_size != null)
            {
            	$instance_charge = $instance_size->instance_price;
            }
            
            $temp_size = 0;
            $temp_snapshot_entry = $request->temporary_ebs_entry;
            if ($temp_snapshot_entry != null)
            {
            	$temp_size = $temp_snapshot_entry->snapshot_entry_ebs_size;
            }
            
            $dataset_size = 0;
            $dataset_types = $request->dataset_types;
            foreach($dataset_types as $dt)
            {
            	$dataset_size += $dt->dataset_type_ebs_size;
            }
            
            $instance->instance_hour_charge = $instance_charge;
            $instance->storage_hour_charge = ($temp_size + 20 + $dataset_size) * 0.1 / 730;
            
            $instance->status_flag='F';
            $instance->Save();
            
            
        	$new_instances[] = $instance;
    	}
    	return $new_instances;
    }
    function process_instances($mapping, $new_instances, $running_instance_list,$instance_names, $method_name, 
    	$instance_names_map, $replacement_parameters, $override_parameters)
    {
    	$new_instance_names = array();
        foreach($mapping as $i => $instance)
        {
        	$instance = $new_instances[$i];
            $amazon_instance_id = $running_instance_list[$i]->getInstanceId();
            $status = InstanceManager::get_status($amazon_instance_id);
            if ($status == "pending" || $status == "running")
            {
            	$assigned_name = $instance_names[$i];
            
	            $parameters = $instance_names_map[$assigned_name];
	            if ($parameters[0] == '')
	            {
	            	$method_launch = new MethodLaunch();
		            $method_launch->instance_request_id = $instance->instance_request_id;
		            $method_launch->instance_id = $instance->instance_id;
		            $method_launch->method_name = $method_name;
		            $method_launch->method_replacement_id = 0;
		            $method_launch->method_parameter = '';
		            $method_launch->status_flag = 'N';
		            $method_launch->Save();
	            }
	            else
	            {
		            foreach($parameters as $parameter)
		            {
			            $method_launch = new MethodLaunch();
			            $method_launch->instance_request_id = $instance->instance_request_id;
			            $method_launch->instance_id = $instance->instance_id;
			            $method_launch->method_name = $method_name;			            
			            if ($override_parameters == 'Y')
		            	{
		            		$method_launch->method_replacement_id = $replacement_parameters[0];
		            	}
		            	else
		            	{
		            		$method_launch->method_replacement_id = 0;
		            	}
		            	$method_launch->method_parameter = $parameter;
			            $method_launch->status_flag = 'N';
			            $method_launch->Save();
		            }
	            }
            	
            	$instance->assigned_name = $assigned_name;
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