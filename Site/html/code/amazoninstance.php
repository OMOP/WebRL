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
require_once('Amazon/EC2/Exception.php');
require_once('Amazon/EC2/Amazon_EC2_Client.php');
require_once('OMOP/WebRL/Configuration/AwsConfiguration.php');

/*
 Represents controller for instance that performs control over Amazon instances.
*/
class AmazonInstance
{
    var $instance;
    var $configuration;
    /*
     Creates new instance of class AmazonInstance
    */
    function __contruct($instance)
    {
    	global $manager;
        $this->instance = $instance;
        $this->configuration = new AwsConfiguration($manager);
    }
    private function get_service()
    {
        $service = new Amazon_EC2_Client($this->configuration->aws_access_key_id(), 
        	$this->configuration->aws_secret_access_key());
        return $service;
    }
    function launch_instances($image_ami, $size_name, $number_of_instances, $instance_names)
    {
        try
        {
            $service = $this->get_service();
            
            $response = $service->runInstances(array('ImageId' => $image_ami, 'MinCount' => $number_of_instances, 'InstanceType' => $size_name, 'MaxCount' => $number_of_instances, 'KeyName' => 'OMOP-FNIH'));
            
            $result = $response->getRunInstancesResult()->getReservation()->getRunningInstance();
            /*$i = 0;
            foreach($result as $instance_descriptor)
            {
                $service->modifyInstanceAttribute(array('InstanceId' => $instance_descriptor->getInstanceId(), 'Attribute' => 'userData' 'Value' => $instance_names[$i]));
                $i++;
            }*/
            return $result;
        }
        catch(Amazon_EC2_Exception $e)
        {
            $this->handle_amazon_exception($e);
        }
        return "";
    }
    function terminate()
    {
        try
        {
            $service = $this->get_service();
            $service->terminateInstances(array('InstanceId'=> $this->instance->amazon_instance_id));
        }
        catch(Amazon_EC2_Exception $e)
        {
            $this->handle_amazon_exception($e);
        }
    }

    function pause()
    {
        try
        {
            $service = $this->get_service();
            $service->stopInstances(array('InstanceId'=> $this->instance->amazon_instance_id));
        }
        catch(Amazon_EC2_Exception $e)
        {
            $this->handle_amazon_exception($e);
        }
    }

    function resume()
    {
        try
        {
            $service = $this->get_service();
            $service->startInstances(array('InstanceId'=> $this->instance->amazon_instance_id));
        }
        catch(Amazon_EC2_Exception $e)
        {
            $this->handle_amazon_exception($e);
        }
    }
    
    function get_console_output ()
    {
    	try
    	{
    		$service = $this->get_service();
    		$response = $service->getConsoleOutput(array('InstanceId'=> $this->instance->amazon_instance_id));
    		$res = '';
    		if ($response->isSetGetConsoleOutputResult()) {
    			$getConsoleOutputResult = $response->getGetConsoleOutputResult();
    			if ($getConsoleOutputResult->isSetConsoleOutput()) { 
    				$consoleOutput = $getConsoleOutputResult->getConsoleOutput();
    				if ($consoleOutput->isSetOutput())
    					$res = $consoleOutput->getOutput();
    			}
    		}
    		if ($res)
    			$res = base64_decode($res);
    			
    		return $res;
    	}
    	catch(Amazon_EC2_Exception $e)
        {
            $this->handle_amazon_exception($e);
        }
    }

    function handle_amazon_exception(Amazon_EC2_Exception $e)
    {
        if ($e->getStatusCode() != -1)
        {
            if ($e->getErrorCode() != "InvalidInstanceID.NotFound")
            {
                // Rethrow exception;
                throw $e;
            }
            else
            {
                // Do nothing if we didn't find instance. 
            }
        }
        else
        {
            // DO nothing if we cannot connect to Amazon. This is means that we are in test env.
        }
    }
    public function get_public_dns()
    {
        try
        {
            $service = $this->get_service();            
            $response = $service->describeInstances(array('InstanceId' => $this->instance->amazon_instance_id));
            $reservation = $response->getDescribeInstancesResult()->getReservation();
            $runningInstance = $reservation[0]->getRunningInstance();
            $dns = $runningInstance[0]->getPublicDnsName();
        }
        catch(Amazon_EC2_Exception $e)
        {
            $this->handle_amazon_exception($e);
        }
        return $dns;
    }
    

}

?>