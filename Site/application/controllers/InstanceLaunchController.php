<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    20-Jun-2011
 
    Controller for pages that perform instance launch.
 
    (c)2009-2010 Foundation for the National Institutes of Health (FNIH)
 
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

class InstanceLaunchController extends Zend_Controller_Action
{
    /**
     *
     * @var Application_Model_Instance Instance model
     */
    private $_model;

    public function init()
    {
        $this->_redirector = $this->_helper->getHelper('redirector');
        $this->_model = new Application_Model_Instance();
    }
    
    public function indexAction()
    {
        $this->_redirector->gotoSimple('instance');
    }
    
    /**
     * Show the instance launch screen
     */
    public function instanceAction()
    {
        $this->_assignInstanceCommonData();
        $this->_assignInstanceLaunchData();
    }
    
    /**
     * Show the method instance launch screen
     */
    public function methodAction()
    {
        $this->_assignMethodCommonData();
        $this->_assignMethodLaunchData();
    }
    
    public function submitMethodAction()
    {
        global $configurationManager;
        
        $user = Membership::get_current_user();
        $userModel = new Application_Model_User();
        $userModel->find($user->user_id);
        
        $request = $this->getRequest();
        
        $instanceType = $request->getParam('instance_type', '');
        $datasetTypes = $request->getParam('database_type', array());
        $softwareType = $request->getParam('tool', '');
        $temporaryEbs = $request->getParam('temporary_ebs', '0');
        
        $methodName = $request->getParam('method_name', '');
        $runParameter = $request->getParam('run_parameters', '');
        $launchMethod = $request->getParam('launch_method', 'N');
        $terminateAfterSuccess = $request->getParam('terminate_after_success', 'N');
        $createUserEbs = $request->getParam('create_user_ebs', 'N');

        $replacementParameters = $request->getParam('replacement_parameters', '');
        $overrideParameters = $request->getParam('override_parameters', 'Y');
        
        $hasValidationErrors = false;
        $errors = array();
        
        $methodManager = new MethodManager($configurationManager);
        $methodParameters = $methodManager->get_method_parameters($methodName);
        $selectedDataset = $datasetTypes[0];
        
        $dataset = new Application_Model_Dataset();
        $dataset->find($selectedDataset);
    			
        $parametersCount = ($runParameter[0] == '' ? count($methodParameters) : count($runParameter));
                
        if ($launchMethod != 'Y') {
            $numberOfInstances = 1;
            $param = $parametersCount == 1 ? $runParameter[0] : $parametersCount.'_param';
            $instanceName = $methodName.'_'.$param.'_'.$dataset->getMethodDatasetName().'_'.$parametersCount;
            $instance_name = $this->_getUniqueName($instanceName); 
            $instanceNames = array($instanceName);
            $instanceNamesMap = array($instanceName => $runParameter);
        } else {
            if ($runParameter[0] == '') {
                $runParameter = $methodParameters;
            }
            $numberOfInstances = $parametersCount;
            $instanceNames = array();
            $instanceNamesMap = array();            	
            foreach($runParameter as $mp) {
                $instanceName = $methodName.'_'.$mp.'_'.$dataset->getMethodDatasetName();
                $instanceName = $this->_getUniqueName($instance_name);
                $instanceNames[] = $instanceName;
                $instanceNamesMap[$instanceName] = array($mp); 					
            }
        }
                
        if (count($instanceNames) == 0) {
            $hasValidationErrors = true;
            $errors["instance_name"] = "Instance name is required.";
        }
        if ($softwareType == '') {
            $hasValidationErrors = true;
            $errors["tool"] = "Tool is required.";
        }
        if ($datasetTypes == '') {
            $hasValidationErrors = true;
            $errors["database_type"] = "Dataset is required.";
        }
        if ($instanceType == '') {
            $hasValidationErrors = true;
            $errors["instance_type"] = "Instance type is required.";
        }
        if ($methodName == '') {
            $hasValidationErrors = true;
            $errors["method_name"] = "Methods name should be selected.";
        }
        if (!InstanceManager::user_could_launch_instances($user, $numberOfInstances)) {
            $hasValidationErrors = true;
            $errors["method_name"] = "You exceeded limit on running instances.";
        }
        
        if ($datasetErrors = $this->_verifyDatasetPasswords($datasetTypes)) {
            $errors = array_merge($errors, $datasetErrors);
            $hasValidationErrors = true;
        }
        
        $st = new Application_Model_SoftwareType();
        $st->find($softwareType);

        $instanceRequest = new Application_Model_InstanceRequest(
            array(
                'userId' => $user->user_id,
                'softwareTypeId' => $st->getId(),
                'sizeId' => $instanceType,
                'temporaryEbsEntryId' => $temporaryEbs,
                'numInstances' => $numberOfInstances,
                'methodLaunchFlag' => 'Y',
                'checkoutMethodCode' => 'Y',
                'attachSharedStorage' => 'N',
                'terminateAfterSuccess' => $terminateAfterSuccess,
                'createdBy' => $user->login_id,
                'createdDate' => gmdate('c'),
                'createUserEbs' => $createUserEbs
            )
        );
        
        if (!$instanceRequest->canUserLaunchInstance($userModel, $numberOfInstances, true)) {
            $hasValidationErrors = true;
            $errors['number_of_instances'] = 'You cannot go over the budget.';
        }
                    
        if ($hasValidationErrors) {
            $this->_assignMethodLaunchData($instanceType, $instanceNames, $datasetTypes, $softwareType, 
                $numberOfInstances, $temporaryEbs, $createUserEbs, $checkoutMethodCode, 
                $attachSharedStorage);
            $this->_assignCommonData();
            $this->view->errors = $errors;
        }
        else
        {
            $instanceRequest->save();

            $this->_saveInstanceRequestDatasets($datasetTypes, 
                                                $instanceRequest->getId(),
                                                $numberOfInstances);
            
            // @todo rework with Zend models
            $instanceSize = new InstanceSize();
            $instanceSize->Load('instance_size_id = ?', $instanceType);

            // @todo rework with Zend models
            $runningInstanceList = InstanceManager::launch_instances($st->getImage(), 
                                                                     $instanceSize->aws_instance_size_name, 
                                                                     $numberOfInstances, 
                                                                     $instanceNames);

            // Check that we correctly run instance set.
            $this->_checkInstancesRunCorrectly($runningInstanceList,
                $numberOfInstances,
                $instanceNames,
                $instanceRequest,
                $st->getImage(),
                $instanceSize->aws_instance_size_name);
            
            $this->_redirector->gotoSimple('list', 'instance');
        }
        
        // these will be processed when the errors were found
        $this->_assignMethodLaunchData();
        $this->_helper->viewRenderer->setRender('method');
    }
    
    private function _checkInstancesRunCorrectly($runningInstanceList,
        $numberOfInstances,
        $instanceNames,
        $instanceRequest,
        $softwareTypeImage,
        $instanceSizeName)
    {
        if (is_array($runningInstanceList)) {
            $newInstances = $this->_createInstanceToProcess($numberOfInstances, 
                                                            $instanceNames, 
                                                            $instanceRequest, 
                                                            $runningInstanceList);
            sleep(10);
            $newInstanceNames = $this->_processInstances($newInstances, 
                                                         $newInstances,
                                                         $runningInstanceList,
                                                         $instanceNames);

            if (count($newInstanceNames) != 0) {
                $runningInstanceList = InstanceManager::launch_instances($softwareTypeImage,
                    $instanceSizeName,
                    count($newInstanceNames),
                    $newInstanceNames);
                $instancesIntersect = array_intersect($instanceNames, $newInstanceNames);
                sleep(10);
                $newInstanceNames2 = $this->_processInstances($instancesIntersect, 
                    $newInstances,
                    $runningInstanceList, // @todo rework with Zend models
                    $instanceNames);

                if (count($newInstanceNames2) != 0) {
                    $runningInstanceList = InstanceManager::launch_instances($st->getImage(), 
                        $instanceSizeName, 
                        count($newInstanceNames2), 
                        $newInstanceNames2);
                    $instancesIntersect = array_intersect($instanceNames, $newInstanceNames2);
                    sleep(10);
                    $newInstanceNames3 = $this->_processInstances($instancesIntersect, 
                        $newInstances,
                        $runningInstanceList,
                        $instanceNames);

                    $this->_raiseError($newInstanceNames3, $instanceNames);    
                    $this->_markFailed($newInstanceNames3, $newInstances);
                }
             }
        } else {
            $this->_createDummyInstances($numberOfInstances, $instanceNames, $instanceRequest);
        }
    }

    public function submitInstanceAction()
    {
        global $configurationManager; 
    	
        $user = Membership::get_current_user();
        $userModel = new Application_Model_User();
        $userModel->find($user->user_id);

        $request = $this->getRequest();
        
        $instanceType = $request->getParam('instance_type', '');
        $instanceNames = $request->getParam('instance_name', array());
        $datasetTypes = $request->getParam('database_type', array());
        $softwareType = $request->getParam('tool', '');
        $numberOfInstances = $request->getParam('number_of_instances', '');
        $temporaryEbs = $request->getParam('temporary_ebs', '0');
        $createUserEbs = $request->getParam('create_user_ebs', 'N');
        $checkoutMethodCode = $request->getParam('checkout_method_code', 'N');
        $hasValidationErrors = false;
        $attachSharedStorage = $request->getParam('attach_shared_storage', 'N');
        $errors = array();

        if (count($instanceNames) == 0) {
            $hasValidationErrors = true;
            $errors['instance_name'] = 'Instance name is required.';
        }
        if ($softwareType == '') {
            $hasValidationErrors = true;
            $errors['tool'] = 'Tool is required.';
        }
        if ($instanceType == '') {
            $hasValidationErrors = true;
            $errors['instance_type'] = 'Instance type is required.';
        }
        if ($numberOfInstances == '') {
            $hasValidationErrors = true;
            $errors['number_of_instances'] = 'Number of instances is required.';
        }
        if ($numberOfInstances <= 0) {
            $hasValidationErrors = true;
            $errors['number_of_instances'] = 'Number of instances is should be positive number.';
        }
        // @todo rework using Zend models
        if (!InstanceManager::user_could_launch_instances($user, $numberOfInstances)) {
            $hasValidationErrors = true;
            $errors['number_of_instances'] = 'You exceeded limit on running instances.';
        }
        if ($numberOfInstances != count($instanceNames))
        {
            $hasValidationErrors = true;
            $errors['number_of_instances'] = 'Number of instances does not match count of names provided.';
        }

        if ($datasetErrors = $this->_verifyDatasetPasswords($datasetTypes)) {
            $errors = array_merge($errors, $datasetErrors);
            $hasValidationErrors = true;
        }
        
        $st = new Application_Model_SoftwareType();
        $st->find($softwareType);
        
        $instanceRequest = new Application_Model_InstanceRequest(
            array(
                'userId' => $user->user_id,
                'softwareTypeId' => $st->getId(),
                'sizeId' => $instanceType,
                'temporaryEbsEntryId' => $temporaryEbs,
                'numInstances' => $numberOfInstances,
                'methodLaunchFlag' => 'N',
                'createdBy' => $user->login_id,
                'createdDate' => gmdate('c'),
                'createUserEbs' => $createUserEbs,
                'checkoutMethodCode' => $checkoutMethodCode,
                'attachSharedStorage' => $attachSharedStorage
            )
        );
        
        if (!$instanceRequest->canUserLaunchInstance($userModel, $numberOfInstances, true)) {
            $hasValidationErrors = true;
            $errors['number_of_instances'] = 'You cannot go over the budget.';
        }
        
        if ($hasValidationErrors) {
            $this->_assignInstanceLaunchData($instanceType, $instanceNames, $datasetTypes, $softwareType, 
                $numberOfInstances, $temporaryEbs, $createUserEbs, $checkoutMethodCode, 
                $attachSharedStorage);
            $this->_assignCommonData();
            $this->view->errors = $errors;
        } else {
            $instanceRequest->save();

            $this->_saveInstanceRequestDatasets($datasetTypes, 
                                                $instanceRequest->getId(),
                                                $numberOfInstances);
            
            // @todo rework with Zend models
            $instanceSize = new InstanceSize();
            $instanceSize->Load('instance_size_id = ?', $instanceType);

            // @todo rework with Zend models
            $runningInstanceList = InstanceManager::launch_instances($st->getImage(), 
                                                                     $instanceSize->aws_instance_size_name, 
                                                                     $numberOfInstances, 
                                                                     $instanceNames);

            // Check that we correctly run instance set.
            $this->_checkInstancesRunCorrectly($runningInstanceList,
                $numberOfInstances,
                $instanceNames,
                $instanceRequest,
                $st->getImage(),
                $instanceSize->aws_instance_size_name);

            $this->_redirector->gotoSimple('list', 'instance');
        }
        
        // these will be processed when the errors were found
        $this->_assignInstanceLaunchData();
        $this->_helper->viewRenderer->setRender('instance');
    }
    
    private function _saveInstanceRequestDatasets(array $datasetTypes, $requestId, $numberOfInstances)
    {
        global $configurationManager;
        
        if (empty ($datasetTypes)) {
            return;
        }
        
        $request = $this->getRequest();
        
        $passwordManager = new MemcachePasswordManager($configurationManager);
        
        foreach($datasetTypes as $dt) {
            $passwordKey = 'dataset_type_' . $dt . '_password';
            // @todo rework with Zend models
            $ird = new InstanceRequestDataset();
            $ird->instance_request_id = $requestId;
            $ird->dataset_type_id = $dt;
            if ($request->getParam($passwordKey, '')) {
                $password = $request->getParam($passwordKey);
                $passwordManager->save_password($ird, $numberOfInstances, $password);
            }
            $ird->save();
        }
    }

        /**
     * Verify selected datasets for errors
     * @param array $datasetTypes list of selected datasets
     * @return array Errors array
     */
    private function _verifyDatasetPasswords(array $datasetTypes)
    {
        $request = $this->getRequest();
        
        $errors = array();
        
        foreach($datasetTypes as $dt) {
            $datasetType = new Application_Model_Dataset();
            $datasetType->find($dt);
            if ($datasetType->getEncryptedFlag() == '1') {
                $passwordKey = 'dataset_type_' . $dt . '_password';
                $password = $request->getParam($passwordKey, '');
                if ($datasetType->getPassword() != Application_Model_Dataset::encryptPassword($password)) {
                    $errors[$passwordKey] = 'Invalid password.';
                }
            }
        }
        
        return $errors;
    }

        /**
     * Assign the view data that is common for all instances
     */
    private function _assignInstanceCommonData()
    {
        $user = Membership::get_current_user();
        
        $userMapper = new Application_Model_UserMapper();
        $storages = $userMapper->getAccessibleStorages($user->user_id);
    	
        $this->view->otherStorageAvailable = count($storages) != 0;
        
        $this->_assignCommonData();
    }
    
    /**
     * Assign the view data that is common for all methods
     */
    private function _assignMethodCommonData()
    {
        $this->view->vocabularyDt = $this->_model->getVocabularyDatasets();
        $this->view->methods = $this->_model->getMethods();
        $this->view->methodParameters = $this->_model->getMethodParameters();
        
        $this->_assignCommonData();
    }
    
    private function _assignCommonData()
    {
        $snapshotMapper = new Application_Model_SnapshotEntryMapper();
        
        $this->view->instanceTypes = $this->_model->getMapper()->getInstanceTypes();
        $this->view->datasetTypes = $this->_model->getMapper()->getDatasetTypes();
        
        $this->view->softwareTypes = $this->_model->getMapper()->getSoftwareTypes();
        $this->view->temporaryEbsEntries = $snapshotMapper->fetchAll(
            Application_Model_SnapshotEntryCategory::TEMPORARY
        );
        
        $this->view->user = Membership::get_current_user();
    }

    /**
     * Assign data entered for the method launch
     * @param type $instanceType
     * @param type $instanceNames
     * @param type $datasetTypes
     * @param type $softwareType
     * @param type $numberOfInstances
     * @param type $temporaryEbs
     * @param type $createUserEbs
     * @param type $checkoutMethodCode
     * @param type $attachSharedStorage 
     */
    private function _assignMethodLaunchData($instanceType = null,
        $instanceNames = null, 
        $datasetType = null, 
        $softwareType = null, 
        $numberOfInstances = null, 
		$temporaryEbs = null, 
        $createUserEbs = null, 
        $checkoutMethodCode = null, 
        $attachSharedStorage = null)
	{
        $this->view->selectedDataset = $datasetType;
        
        $this->_assignCommonEnteredData($instanceType,
            $instanceNames,
            $softwareType, 
            $numberOfInstances, 
            $temporaryEbs, 
            $createUserEbs, 
            $checkoutMethodCode, 
            $attachSharedStorage);
    }
    
    /**
     * Assign data entered for the instance launch
     * @param type $instanceType
     * @param type $instanceNames
     * @param type $datasetTypes
     * @param type $softwareType
     * @param type $numberOfInstances
     * @param type $temporaryEbs
     * @param type $createUserEbs
     * @param type $checkoutMethodCode
     * @param type $attachSharedStorage 
     */
    private function _assignInstanceLaunchData($instanceType = null,
        $instanceNames = null, 
        $datasetTypes = array(), 
        $softwareType = null, 
        $numberOfInstances = null, 
		$temporaryEbs = null, 
        $createUserEbs = null, 
        $checkoutMethodCode = null, 
        $attachSharedStorage = null)
	{
        $this->view->selectedDatasetTypes = $datasetTypes;
        
        $this->_assignCommonEnteredData($instanceType,
            $instanceNames, 
            $softwareType, 
            $numberOfInstances, 
            $temporaryEbs, 
            $createUserEbs, 
            $checkoutMethodCode, 
            $attachSharedStorage);
    }
    
    private function _assignCommonEnteredData($instanceType = null,
        $instanceNames = null, 
        $softwareType = null, 
        $numberOfInstances = null, 
		$temporaryEbs = null, 
        $createUserEbs = null, 
        $checkoutMethodCode = null, 
        $attachSharedStorage = null)
	{
		$this->view->selectedInstanceTypes = $instanceType;
		$this->view->selectedSoftwareType = $softwareType;
		$this->view->selectedTemporaryEbs = $temporaryEbs;
		$this->view->selectedCreateUserEbs = $createUserEbs;
		$this->view->selectedCheckoutMethodCode = $checkoutMethodCode;
		$this->view->selectedAttachSharedStorage = $attachSharedStorage;
		
		$this->view->numberOfInstances = $numberOfInstances;
		$this->view->instanceNames = $instanceNames;
	}
    
    private function _raiseError($newInstanceNames, $instanceNames)
    {    
		if (count($newInstanceNames) != 0) {
        	$names_list = '';
            foreach($newInstanceNames as $name) {
                $names_list += ($names_list == '' ? '' : ',' ) + $instance_names[$name];
            }
        	throw new Exception('Cannot launch instances with names ' . $names_list 
                                . ' cannot start. Try contact administrator to resolve this issue.');
		}
    }
    
    private function _createDummyInstances($numberOfInstances, 
                                           $instanceNames, 
                                           Application_Model_InstanceRequest $request)
    {
    	for($i = 0; $i < $numberOfInstances; $i++) {
        	$instance = new Application_Model_Instance();
            $instance->setRequestId($request->getId())
                     ->setName($instanceNames[$i])
                     ->setAmazonInstanceId('fi-'.uniqid())
                     ->setPublicDns('ec2-' . rand(130, 240) . '-' . rand(0,255) 
                                    . '-' . rand(0,255).'-'.rand(0,255).'amazonws.com')
                     ->setStartDate(gmdate('c'))
                     ->setTerminateDate(null)
                     ->setStatusFlag('A');
            $instance->save();
       }
    }
    
    private function _createInstanceToProcess($numberOfInstances, 
                                                $instanceNames, 
                                                Application_Model_InstanceRequest $request, 
                                                $runningInstanceList)
    {
    	$newInstances = array();
        for ($i = 0; $i < $numberOfInstances; $i++) {
            $instance = new Application_Model_Instance();
            
            $instanceCharge = $request->getInstanceHourPrice();
            $storageCharge = $request->getStorageHourPrice();
            
            $instance->setRequestId($request->getId())
                     // @todo rework $runningInstanceList with Zend models
                     ->setStartDate($runningInstanceList[$i]->getLaunchTime())
                     ->setInstanceHourCharge($instanceCharge)
                     ->setStorageHourCharge($storageCharge)
                     ->setStatusFlag('F')
                     ->save();
        	$newInstances[] = $instance;
    	}
    	return $newInstances;
    }
    
    private function _processInstances(array $mapping, 
                                       array $newInstances, 
                                       $runningInstanceList, 
                                       $instanceNames)
    {
    	$newInstanceNames = array();
        foreach($mapping as $i => $instance) {
            $instance = $newInstances[$i];
            // @todo rework $runningInstanceList with Zend models
            $amazonInstanceId = $runningInstanceList[$i]->getInstanceId();
            $status = InstanceManager::get_status($amazonInstanceId);
            if ($status == 'pending' || $status == 'running') {
            	$instance->setName($instanceNames[$i])
                         ->setAmazonInstanceId($amazonInstanceId)
                         ->setStatusFlag('I')
                         ->save();
            } else {
            	$newInstanceNames[] = $instanceNames[$i];
        	}
     	}
     	return $newInstanceNames;
    }
    
    private function _markFailed($mapping, $newInstances)
    {
    	foreach($mapping as $i => $instance) {
        	$instance = $newInstances[$i];
        	$instance->setTerminateDate(null)
                     ->setStatusFlag(I) 
                     ->save();
     	}
    }
    
    /**
     * Terminate instance
     */
    public function terminateAction()
    {
        $request = $this->getRequest();
        $instanceId = $request->getParam('id');
        
        $ir = new Application_Model_Instance();
        $ir->find($instanceId);
        $ir->setTerminateDate(gmdate('c'));
        $ir->setStatusFlag('S');
        InstanceManager::terminate_instance($ir->getAmazonInstanceId());
        $ir->save();
        
        $ir->terminateMethods();
        
        if (isset($_SERVER['HTTP_REFERER'])) {
			$this->_redirect($_SERVER['HTTP_REFERER']);
		} else {
        	$this->_redirector->gotoSimple('list', 'instance');
        }
    }
    
    private function _getUniqueName($name)
    { 
    	$user = Membership::get_current_user();
    	
    	if (!InstanceManager::is_instance_available($user->user_id, $name)) {
			$newNameSearch = InstanceManager::get_unique_name($user->user_id, $name.'_0');
	        if ($newNameSearch['available_index'] != 0) {
	        	return $newNameSearch['available_name'];
	        } 
    	}
        return $name;
    }
}