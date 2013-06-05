<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    22-Feb-2011
 
    Controller for pages that performs user management.
 
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
require_once "OMOP/WebRL/OrganizationManager.php";
require_once "OMOP/WebRL/MailManager.php";
require_once "OMOP/WebRL/Configuration/AwsConfiguration.php";
require_once "OMOP/WebRL/Configuration/WebRLConfiguration.php";

class UserController extends Zend_Controller_Action
{
    
    public function preDispatch()
    {
        $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        $view = $bootstrap->getResource('view');
        $role = $view->navigation()->getRole();
        if ($this->getRequest()->getActionName() == 'edit'
            && $role == Application_Model_Acl::ROLE_USER) {
            $this->_forward('edit-account');
        }
        
    }
    
    public function init()
    {
        $this->_redirector = $this->_helper->getHelper('redirector');
        $this->_backUrl = $this->_helper->getHelper('BackUrl');
        $ajaxContext = $this->_helper->AjaxContext();
        $ajaxContext->addActionContext('get-users', 'json')->initContext('json');
    }
    
    public function indexAction()
    {
        $this->_redirector->gotoSimple('list');
    }
    /*
     * Action that displays list of users.
     */
    public function listAction()
    {
        $request = $this->getRequest();
        $page = $request->getParam('page', 1);
        //Default is Abbr column
        $sortColumn = $request->getParam('sort', 1);
        $sortDirection = $request->getParam('dir', 'asc');

        $mapper = new Application_Model_UserMapper();
        $user = Membership::get_current_user();        
        $adapter = $mapper->getPaginatorAdapter($sortColumn, $sortDirection, $user->organization_id);
        
        $pagerConf = (object) '';
        $pagerConf = Zend_Registry::get('pagerConfig');
        
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($page);
        
        if (isset($pagerConf->per_page_user_list))
            $paginator->setItemCountPerPage($pagerConf->per_page_user_list);
            
        if (isset($pagerConf->page_range_user_list))
            $paginator->setPageRange($pagerConf->page_range_user_list);
        elseif (isset($pagerConf->page_range))
            $paginator->setPageRange($pagerConf->page_range);
        else
            $paginator->setPageRange(5);        

        $this->view->currentUser = $user;
        $this->view->sort_column = $sortColumn;
        $this->view->sort_dir = $sortDirection;
        $this->view->paginator = $paginator;
    }
    /*
     * Action that displays list of users.
     */
    public function listPrintAction()
    {
        $this->_helper->layout->setLayout('print');
        $this->view->placeholder('pageTitle')->append('Users List Report');
        
        $request = $this->getRequest();
        $page = $request->getParam('page', 1);
        //Default is Abbr column
        $sortColumn = $request->getParam('sort', 1);
        $sortDirection = $request->getParam('dir', 'asc');

        $mapper = new Application_Model_UserMapper();
        $user = Membership::get_current_user();        
        $data = $mapper->getUserList($sortColumn, $sortDirection, $user->organization_id);
        $this->view->currentUser = $user;
        $this->view->sort_column = $sortColumn;
        $this->view->sort_dir = $sortDirection;
        $this->view->data = $data;
    }
    /*
     * Action that render list of users to the CSV file.
     */
    public function listCsvAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNeverRender();        
        $request = $this->getRequest();

        // Default sorting order is by Login Id column
        $sortColumn = $request->getParam('sort', 1);
        $sortDirection = $request->getParam('dir', 'asc');

        $mapper = new Application_Model_UserMapper();
        $user = Membership::get_current_user();        
        $data = $mapper->getUserList($sortColumn, $sortDirection, $user->organization_id);

        $this->view->currentUser = $user;
        $this->view->sort_column = $sortColumn;
        $this->view->sort_dir = $sortDirection;
        $this->view->data = $data;
        
        $fileName = 'UserList.csv';
        header('Content-type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$fileName.'"');
        
        $outstream = fopen("php://output", 'w');
        $delimiter = ',';
        $enclosure = '"';
        function __outputCSV(&$vals, $key, $filehandler) {
            $delimiter = ',';
            $enclosure = '"';
            $d = array(
                $vals['login'],
                $vals['organization'],
                $vals['name'],
                $vals['email'],
                $vals['active'],
                $vals['runningInstCount'],
                $vals['lastInstanceStart'],
                $vals['lastInstanceTerminate'],
                $vals['totalCharged'],
                $vals['remainingLimit'],
            );
            fputcsv($filehandler, $d, $delimiter, $enclosure);
        }
        fputcsv($outstream, array(
            'Login ID',
            'Organization',
            'User Name',
            'Email',
            'Active',
            'Active Instances',
            'Last Instances Started',
            'Last Instances Ended',
            'Charged',
            'Remaining',
        ), $delimiter, $enclosure);
        array_walk($data, '__outputCSV', $outstream);
        fclose($outstream);
    }
    public function historyAction()
    {
        $request = $this->getRequest();
        $page = $request->getParam('page', 1);
        //Default is Abbr column
        $sortColumn = $request->getParam('sort', 1);
        $sortDirection = $request->getParam('dir', 'desc');
        
        if ($filter = $request->getParam('filter', false)) {
            $this->extractParams($filter);
        }
        
        $user = Membership::get_current_user();
        if ($user->organization_id == 0)
            $organizationId = $request->getParam('organization', false);
        else
            $organizationId = $user->organization_id;
        $userId = $request->getParam('user', false);
        $status = $request->getParam('status', false);
        
        $mapper = new Application_Model_UserMapper();
        $user = Membership::get_current_user();
        $adapter = $mapper->getHistoryPaginatorAdapter(
            $sortColumn,
            $sortDirection,
            $organizationId,
            $userId,
            $status
        );
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($page);
        $paginator->setPageRange(5);
        
        $userManager = new UserManager(DbManager::$db);
        $users =  $userManager->get_all_users($organizationId, 10, false);
        
        $orgManager = new OrganizationManager(DbManager::$db);
        $organizations = $orgManager->get_organizations(true);
        $this->view->users = $users;
        $this->view->organizations = $organizations;
        $this->view->sort_column = $sortColumn;
        $this->view->sort_dir = $sortDirection;
        $this->view->paginator = $paginator;
        $this->view->organizationId = $organizationId;
        $this->view->userId = $userId;
        $this->view->status = $status;
        
    }
    
    public function editAction() {

        global $configurationManager;
        $id = $this->_getParam('id');
        if ($id && is_numeric($id)) {
            $request = $this->getRequest();
            $cUser = Membership::get_current_user();
            if ($cUser->organization_id == 0) {
                $orgModel = new Application_Model_DbTable_Organization();
                $organizations = $orgModel->getList();
            } else {
                $organizations = array();
            }
            
            $user = new Application_Model_User();
            $user->find($id);
            if ($user->getId() != $id)
                return;
            //Initialize form
            $omanager = new OrganizationManager(DbManager::$db);
            $allowedDatasets = $omanager->get_allowed_dataset_types($cUser->organization_id);
            $allowedImages = $omanager->get_allowed_software_types($cUser->organization_id);

            $datasetModel = new Application_Model_DbTable_DatasetType();
            $imageModel = new Application_Model_DbTable_SoftwareType();
            
            $userMapper = new Application_Model_UserMapper();
            $orgUsers = $userMapper->findByOrganization($cUser->organization_id);
            
            $userStorageAccess = new UserStorageAccess();
            $users = $userStorageAccess->Find("user_id = ?", $id);
            $allowedUsers = array();
            foreach ($users as $u)
            {
                $allowedUsers[] = $u->grantee_id;
            }
            
            $usersToShare = array();
            if ($orgUsers) {
                foreach ($orgUsers as $u) {
                    if ($u->getId() != $id)
                        $usersToShare[$u->getId()] = $u->getLoginId();
                }
            }
            foreach ($allowedUsers as $au) {
            	$u = new Application_Model_User();
            	$u->find($au);
            	$usersToShare[$u->getId()] = $u->getLoginId();
            }
            $userOrg = $omanager->get($user->getOrganizationId());
            $userMapper->getDatasetAccess($user);
            $userMapper->getImageAccess($user);
            $user->setSharesStorageTo($allowedUsers);
            
            $fullDatasetList = $datasetModel->getList(true);
            $datasetList = array();
            foreach ($allowedDatasets as $ad) {
            	$datasetList[$ad] = $fullDatasetList[$ad];
            }
            
            $fullImagesList = $imageModel->getList(true);
            $imageList = array();
            foreach ($allowedImages as $ai) {
            	$imageList[$ai] = $fullImagesList[$ai];
            }
            
            $additionalUsersToShareWith = $userMapper->findAdministratorLogins();
            
            // Sort 
            asort($datasetList);
            asort($imageList);
            
            // Move selected items to the top of the list
            $usersToShare = self::moveSelectionOnTop($usersToShare, $allowedUsers);
            $datasetList = self::moveSelectionOnTop($datasetList, $user->getDatasetAccess());
            $imageList = self::moveSelectionOnTop($imageList, $user->getImageAccess());
            
            $sharingFlag = ($cUser->organization_id == 0) || 
            				($cUser->organization_id != 0 
            				&& $user->getOrganizationId() == $cUser->organization_id 
            				&& $userOrg->storage_sharing_flag == 'Y');
           
            
            $my_mapper = new Application_Model_SourceMapper();
            $datasetList1 = $this->create_merge_list($datasetList, $my_mapper->fetchPairs());
            $form = new Application_Form_EditUser(
                array(
                    'id' => $id,
                    'organizations' => $organizations,
                    'datasets' => $datasetList1,
                    'images' => $imageList,
                    'allowedDatasets' => $allowedDatasets,
                    'allowedImages' => $allowedImages,
                    'usersToShare' => $usersToShare,
                	'additionalUsersToShareWith' => $additionalUsersToShareWith,
                    'sharingFlag' => $sharingFlag,
                    'inOrg' => $user->getOrganizationId() != 0
                )
            );
           
            if ($request->isGet()) {
            $this->_backUrl->saveReference();
            $form->populate($user->toArray());
            $this->view->form = $form;
            }
           
            if ($request->isPost()) { 
                $postData = $request->getPost();
                if ($form->isValid($postData)) {
                    //Get old user login and storage size
                    $oldStorageSize = $user->getStorageSize();
                    $oldLogin = $user->getLoginId();
                    $oldUserInfo = $user->toArray();
                    $user->setOptions($postData);
                    if ($postData['resetPassword']) {
                        $sm = new SecurityManager();
                        $password = $sm->generate_password();
                        $user->setPassword($password);
                    }
                    $user->save();
                    if ($postData['resetPassword']) {
                       	$mmanager = new MailManager($configurationManager);
                        //Use old User class for MailManager
                        $oldUser = new User();
                        $oldUser->load('user_id = ?', array($id));
                        $mmanager->send_user_information_changed($oldUser, $password);
                    }
                    //Change user Login
                    if ($oldLogin != $user->getLoginId()) {
                        $mapper = new Application_Model_UserMapper();
                        $mapper->changeLogin($user->getLoginId(), $oldLogin);
                    }
                    //Resize User storage
                    if ($oldStorageSize != $user->getStorageSize()) {
                        $psm = new PersonalStorageManager($configurationManager);
                        $spaceUsage = $psm->resize_storage($user->getLoginId(), $user->getStorageSize());
                    }
                    
                    if ($backUrl = $this->_backUrl->getReferer())
                        $this->_redirector->gotoUrl($backUrl);
                    else
                        $this->_redirector->gotoSimple('list');
                } else {
                    $this->view->form = $form;
                    $this->view->form->populate(array('sharesStorageTo' => $user->getSharesStorageTo()));
                }
            }     
        }        
        
    }
    private static function moveSelectionOnTop($list, $selection)
    {
    	$result = array();
    	foreach ($list as $key => $item) {
    		if (in_array($key, $selection)) {
    			$result[$key] = $item;
    		}
    	}
    	foreach ($list as $key => $item) {
    		if (!in_array($key, $selection)) {
    			$result[$key] = $item;
    		}
    	}
    	return $result;
    }
    public function addAction()
    {
        global $configurationManager;
        $request = $this->getRequest();
        $cUser = Membership::get_current_user();
        if ($cUser->organization_id == 0) {
            $orgModel = new Application_Model_DbTable_Organization();
            $organizations = $orgModel->getList();
        } else {
            $organizations = array();
        }

        $omanager = new OrganizationManager(DbManager::$db);
        $allowedDatasets = $omanager->get_allowed_dataset_types($cUser->organization_id);
        $allowedImages = $omanager->get_allowed_software_types($cUser->organization_id);

        $datasetModel = new Application_Model_DbTable_DatasetType();
        $imageModel = new Application_Model_DbTable_SoftwareType();

        $uMapper = new Application_Model_UserMapper();

        /*
         * Change dataset list - add Oracle source
         */
        $source_mapper = new Application_Model_SourceMapper();
        $FullDataSet = $this->create_merge_list($datasetModel->getList(true), $source_mapper->fetchPairs());
        
        $form = new Application_Form_AddUser(
            array(
                'id' => $id,
                'organizations' => $organizations,
                'datasets' => $FullDataSet,
                'images' => $imageModel->getList(true),
                'allowedDatasets' => $allowedDatasets,
                'allowedImages' => $allowedImages,
                'usersToShare' => $usersToShare,
                'inOrg' => $cUser->organization_id != 0
            )
        );
        if ($request->isGet()) {
            $form->populate(array('organizationId' => $cUser->organization_id));
            $this->view->form = $form;
        }

        if ($request->isPost()) {
            $postData = $request->getPost();
            if ($form->isValid($postData)) {
                $user = new Application_Model_User($postData);
                $sm = new SecurityManager();
                $password = $sm->generate_password();
                $user->setPassword($password);
                $user->save();
                $mmanager = new MailManager($configurationManager);
                //Use old User class for MailManager
                $oldUser = new User();
                $oldUser->load('user_id = ?', array($user->getId()));
                $um = new UserManager();
                $um->create_storage_user($oldUser);
                $um->update_dav_authorization();
                $mmanager->send_user_created($oldUser, $password);
                $this->createQueriesUser($user);

                $this->_redirector->gotoSimple('list');
            } else {
                $this->view->form = $form;
            }
        }

    }
    
    public function editAccountAction()
    {
        global $configurationManager;
        $cUser = Membership::get_current_user();
        $user = new Application_Model_User();
        $user->find($cUser->user_id);
        
        $request = $this->getRequest();
        $datasetModel = new Application_Model_DbTable_DatasetType();
        $imageModel = new Application_Model_DbTable_SoftwareType();
        $omanager = new OrganizationManager(DbManager::$db);

        $userOrg = $omanager->get($user->getOrganizationId());
        $sharingAllowed = $user->getOrganizationId() != 0 && $userOrg->storage_sharing_flag == 'Y';
        
        $uMapper = new Application_Model_UserMapper();
        $orgUsers = $uMapper->findByOrganization($user->getOrganizationId());

        $usersList = array();    
        
        foreach ($orgUsers as $u) {
            if ($u->getId() != $cUser->user_id) {
                $usersList[$u->getId()] = $u->getLoginId();
            }
        }
    
        $additionalUsers = $user->getAdditionalUsersToShareWith();
        foreach ($additionalUsers as $u) {
        	$uid = $u->additional_user_id;
        	$additionalUser =  new Application_Model_User();
        	$uMapper->find($uid, $additionalUser);
            $usersList[$uid] = $additionalUser->getLoginId();
        }

        $userStorageAccess = new UserStorageAccess();
        $users = $userStorageAccess->Find("user_id = ?", $cUser->user_id);
        $allowedUsers = array();
        foreach ($users as $u) {
            $allowedUsers[] = $u->grantee_id;
        }
        
        $user->setSharesStorageTo($allowedUsers);

        $form = new Application_Form_EditAccount(
            array(
                'id' => $cUser->user_id,
                'datasets' => $datasetModel->getList(true),
                'images' => $imageModel->getList(true),
                'sharingFlag' => $sharingAllowed,
                'usersToShare' => $usersList,
                'accessibleStorages' => $user->getAccessibleStorages()
            )
        );

        if ($request->isGet()) {
            $this->_backUrl->saveReference();
            $fields = $user->toArray();
            $fields['chargeLimit']     = number_format($fields['chargeLimit']);
            $fields['chargeRemaining'] = number_format($fields['chargeRemaining']);
            $fields['maxInstances']    = number_format($fields['maxInstances']);
            
            $form->populate($fields);
            $this->view->form = $form;
        }

        if ($request->isPost()) {
           $postData = $request->getPost();
            if ($form->isValid($postData)) {
                $oldUserInfo = $user->toArray();
                $user->setOptions($postData);
                if ($postData['password']) {
                    $sm = new SecurityManager();
                    $password = $postData['password'];
                    $user->setPassword($password);
                }
                $user->save();
                if ($backUrl = $this->_backUrl->getReferer())
                    $this->_redirector->gotoUrl($backUrl);
                else
                    $this->_redirector->gotoSimple('list');
            } else {
                $this->view->form = $form;
            }
        }
    }
    
    private function extractParams($filter)
    {
        $matches = array();
        if (preg_match('/(\d+)_(\d+)(_([APSF]))?/', $filter, $matches)) {
            $this->getRequest()->setParam('organization', $matches[1])
                               ->setParam('user', $matches[2])
                               ->setParam('status', $matches[4]);
        } else {
            return;
        }
    }
    
    //@todo: move to organization controller
    
    public function getUsersAction()
    {
        
        $orgId = $this->_getParam('organization', 0);
        
        $mapper = new Application_Model_UserMapper();
        
        $users = $mapper->getOrgUsersList($orgId);
        
        $this->view->users = $users;
        
    }
    
    public function createQueriesUser(Application_Model_User $user)
    {
        global $configurationManager;
        
        $config = new AwsConfiguration($configurationManager);
        $dConfig = new WebRLConfiguration($configurationManager);
        $datasetMapper = new Application_Model_DatasetMapper();
        $datasetList = $datasetMapper->getDbTable()->getList();
        $sqs = new Zend_Service_Amazon_Sqs($config->aws_access_key_id(), $config->aws_secret_access_key());
        $url = $sqs->create($dConfig->user_sync_queue());
        
        $xml = new XMLWriter();
        
        $xml->setIndent(false);
        $xml->openMemory();
        $xml->startDocument();
        $xml->startElement('message');
        $xml->writeElement('message-type', 'UserAdded');
        $xml->startElement('user');
        
        $xml->writeElement('first-name', $user->getFirstName());
        $xml->writeElement('last-name', $user->getLastName());
        $xml->writeElement('login', $user->getLoginId());
        $xml->writeElement('phone', $user->getPhone());
        $xml->writeElement('user-title', $user->getTitle());
        $xml->writeElement('email', $user->getEmail());
        $xml->writeElement('password', $user->getPassword());
        $xml->writeElement('status', $user->getActive() ? 'active' : 'blocked');
        $xml->writeElement('admin', $user->getOrgAdmin() ? 'yes': 'no');
        $xml->startElement('dataset-access');
        foreach ($user->getDatasetAccess() as $dataset) {
            $xml->writeElement('dataset', $datasetList[$dataset]);
        }
        $xml->endElement();
        
        $xml->endElement();
        $xml->endElement();
        $xml->endDocument();
        
        $message = $xml->outputMemory();
        $sqs->send($url, $message);
    }
    private function create_merge_list($mysql_datasets, $oracle_sourse) {

        foreach ($mysql_datasets as $value) {
            if (in_array($value, $oracle_sourse))
                unset($oracle_sourse[array_search($value, $oracle_sourse)]);
        }
        $oracle_sourse = array_map(array($this, "change_name"), $oracle_sourse);

        $result = array();
        $separate = 'ml';
        foreach ($mysql_datasets as $key => $value) {
            $result[$separate . $key] = $value;
        }
        $separate = 'or';
        foreach ($oracle_sourse as $key => $value) {
            $result[$separate . $key] = $value;
        }
        return $result;
    }
    
    private function change_name($string, $separate = '*') {
        return $separate . $string;
    }
  
    
}
