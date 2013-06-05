<?php
/*=============================================================================
   OMOP - Cloud Research Lab

   Observational Medical Outcomes Partnership
   22-Feb-2011

   Mapper for User Model

   (c)2009-2011 Foundation for the National Institutes of Health (FNIH)

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

=============================================================================*/
class Application_Model_UserMapper
{
    protected $_dbTable;
    
    protected $_dbTableDownloadHistory;
    
    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Application_Model_DbTable_User');
        }
        return $this->_dbTable;
    }
    
    public function setDbTableDownloadHistory($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTableDownloadHistory = $dbTable;
        return $this;
    }

    public function getDbTableDownloadHistory()
    {
        if (null === $this->_dbTableDownloadHistory) {
            $this->setDbTableDownloadHistory('Application_Model_DbTable_UserDownloadHistory');
        }
        return $this->_dbTableDownloadHistory;
    }
    
    public function saveDownloadHistory(Application_Model_UserDownloadHistory $history) 
    {
        $data = array(
            'user_id' => $history->getUserId(),
            'download_type' => $history->getType(),
            'download_date' => $history->getDate()
        );
        if (null == $history->getId()) {
            $this->getDbTableDownloadHistory()
                 ->insert($data);
        } else { 
            $this->getDbTableDownloadHistory()
                 ->update($data, array('user_download_history_id = ?' => $history->getId()));
        }
    }
    
    public function save(Application_Model_User $user) 
    {
        $mysql_datasets_access = $this->ClearDatasetId($user->getDatasetAccess(), $separate = 'ml');
        $oracle_datasets_access = $this->ClearDatasetId($user->getDatasetAccess(), $separate = 'or');

        if (is_array($oracle_datasets_access) && count($oracle_datasets_access) > 0) {
            $result_oracle_dataset = array();
            $oracle_dataset_mapper = new Application_Model_SourceMapper();
            $oracle_dataset_list = $oracle_dataset_mapper->fetchPairs();
            foreach ($oracle_datasets_access as $key) {
                if (key_exists($key, $oracle_dataset_list))
                    $result_oracle_dataset[$key] = $oracle_dataset_list[$key];
            }
            unset($oracle_dataset_list);
            unset($oracle_dataset_mapper);
        }
   
        $data = array(
            'login_id' => $user->getLoginId(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'title' => $user->getTitle(),
            'organization_id' => $user->getOrganizationId(),
            'email' => $user->getEmail(),
            'phone' => $user->getPhone(),
            'num_instances' => $user->getMaxInstances(),
            'user_volume_size' => $user->getStorageSize(),
            'admin_flag' => $user->getOrgAdmin() ? 'Y': 'N',
            'active_flag' => $user->getActive() ? 'Y' : 'N',
            'svn_access_flag' => $user->getSvnAccess() ? 'Y' : 'N',
            'result_access_flag' => $user->getLoadResult() ? 'Y' : 'N',
            'user_money' => $user->getChargeLimit()
        );
        if ($user->getPassword()) {
            $passwordInfo = $this->getPasswordInfo($user);
            $data = array_merge($data, $passwordInfo);
        }
        if ($user->getCertificate() == null) {
            $data['certificate_public_key'] = $this->generateKeys($user);
        }
         
        $data['updated_by'] = Membership::get_current_user()->login_id;
        $data['updated_date'] = gmdate('c');        
        
        if ($user->getId() == null) {
            $data['created_date'] = gmdate('c');
            $data['created_by'] = Membership::get_current_user()->login_id;
            $data['internal_id'] = uniqid();
            $newId = $this->getDbTable()->insert($data);
            if ($newId)
                $user->setId($newId);
            else
                throw Exception("Can't persist user data to database");
        } else {
            $oldUser = new Application_Model_User();
            $this->find($user->getId(), $oldUser);
            $this->getDbTable()->update($data, array('user_id = ?'=>$user->getId()));
        }
              if ($user->getPassword())
            $this->savePasswordHistory($user);

        $this->saveCertificateHistory($user, $user->getCertificate());
        
        $this->getDbTable()->getAdapter()->delete(
            'user_dataset_access_tbl',
            array('user_id = ?' => $user->getId())
        );
        foreach($mysql_datasets_access as $dataset) {
            $this->getDbTable()->getAdapter()->insert(
                'user_dataset_access_tbl', 
                array('user_id' => $user->getId(), 'dataset_type_id' => $dataset)
            );
        }
        
        /*
         * Oralce dataset access 
         */
        $this->getDbTable()->getAdapter()->delete(
            'user_oracle_dataset_access_tbl',
            array('user_id = ?' => $user->getId())
        );
        if (isset($result_oracle_dataset) && is_array($result_oracle_dataset) && count($result_oracle_dataset) > 0)
            foreach ($result_oracle_dataset as $type_id => $dataset) {
                $this->getDbTable()->getAdapter()->insert(
                        'user_oracle_dataset_access_tbl', array('user_id' => $user->getId(), 'dataset_type_id' => $type_id, 'source_abbr' => $dataset)
                );
            }
       
        
        $this->getDbTable()->getAdapter()->delete(
            'user_software_access_tbl',
            array('user_id = ?' => $user->getId())
        );
        foreach($user->getImageAccess() as $image) {
            $this->getDbTable()->getAdapter()->insert(
                'user_software_access_tbl', 
                array('user_id' => $user->getId(), 'software_type_id' => $image)
            );
        }
        
        $this->saveAdditionalUsersToShareWith($user);
        
        if ($user->getSharesStorageTo()) {
            $this->getDbTable()->getAdapter()->delete(
                'user_storage_access_tbl',
                array('user_id = ?' => $user->getId())
            );
            
            foreach ($user->getSharesStorageTo() as $grantee) {
                $this->getDbTable()->getAdapter()->insert(
                    'user_storage_access_tbl', 
                    array('user_id' => $user->getId(), 'grantee_id' => $grantee)
                );
            }
        }
        
        $sm = new SecurityManager();
        $currentUserId = Membership::get_current_user()->user_id;
        if (! isset($newId)) {
            $sm->generate_security_event(
                SecurityManager::ISSUE_TYPE_USER_INFORMATION_CHANGED,
                "User information for user {$user->getLoginId()} changed.".
                "Old information: {$this->arrayToString($oldUser->toArray())}",
                $user->getId(),
                null,
                null,
                null
            );        
        } else {
            $sm->generate_security_event(
                SecurityManager::ISSUE_TYPE_USER_CREATED,
                "User {$user->getLoginId()} created.".
                "Information: {$this->arrayToString($user->toArray())}",
                $user->getId(),
                null,
                null,
                null
            );        
            
        }
    }
    
    public function find($id, Application_Model_User $user)
    {
        $resultSet = $this->getDbTable()->find($id);
        if (count($resultSet) == 0)
            return;
        
        $row = $resultSet->current();
        //var_dump($row);die();
        $user->setId($row->user_id)
             ->setLoginId($row->login_id)
             ->setFirstName($row->first_name)
             ->setLastName($row->last_name)
             ->setTitle($row->title)
             ->setOrganizationId($row->organization_id)
             ->setEmail($row->email)
             ->setPhone($row->phone)
             ->setCreateDate($row->created_date)
             ->setMaxInstances($row->num_instances)
             ->setStorageSize($row->user_volume_size)
             ->setStorageFolder('/var/storage/'.$row->login_id)
             ->setOrgAdmin($row->admin_flag == 'Y')
             ->setActive($row->active_flag == 'Y')
             ->setSvnAccess($row->svn_access_flag =='Y')
             ->setLoadResult($row->result_access_flag =='Y')
             ->setChargeLimit($row->user_money)
             ->setCertificate($row->certificate_public_key)
             ->setCertificateWithPassword($row->certificate_has_password);
             
    }
    
    public function findByOrganization($organizationId, $active = true) {
        
        $select = $this->getDbTable()->select();
        if ($organizationId != 0)
            $select->where('organization_id = ?', $organizationId);

        if ($active)
            $select->where('active_flag = "Y"');
        $select->order('login_id');
        
        $resultSet = $this->getDbTable()->fetchAll($select);
        
        $users = array();
        foreach ($resultSet as $row) {
            $user = new Application_Model_User();
            $user->setId($row->user_id)
                 ->setLoginId($row->login_id)
                 ->setFirstName($row->first_name)
                 ->setLastName($row->last_name)
                 ->setTitle($row->title)
                 ->setOrganizationId($row->organization_id)
                 ->setEmail($row->email)
                 ->setPhone($row->phone)
                 ->setCreateDate($row->created_date)
                 ->setMaxInstances($row->num_instances)
                 ->setStorageSize($row->user_volume_size)
                 ->setStorageFolder('/var/storage/'.$row->login_id)
                 ->setOrgAdmin($row->admin_flag == 'Y')
                 ->setActive($row->active_flag == 'Y')
                 ->setSvnAccess($row->svn_access_flag =='Y')
                 ->setLoadResult($row->result_access_flag =='Y')
                 ->setChargeLimit($row->user_money);
            $users[] = $user;
        }
        
        return $users;
        
    }
    
    public function getTotalCharged(Application_Model_User $user)
    {

        $select = $this->getDbTable()->getAdapter()->select();
        $select->from(
                    array('ir' => 'instance_request_tbl'),
                    "ROUND(SUM(s.instance_price * ( TIMESTAMPDIFF(SECOND, i.start_date, ifnull(i.terminate_date, utc_timestamp())) ))/ 3600)"
               )
               ->join(
                   array('s' => 'instance_size_tbl'),
                   "ir.instance_size_id = s.instance_size_id",
                   array()
               )
               ->join(
                   array('i'=> 'instance_tbl'),
                   'i.instance_request_id = ir.instance_request_id',
                   array()
               )
               ->join(
                   array('u' => 'user_tbl'),
                   'ir.user_id = u.user_id',
                   array()
               );
        $select->where('u.user_id = ?', $user->getId());
        
        $user->setTotalCharged($this->getDbTable()->getAdapter()->fetchOne($select));
    }
    
    public function getDatasetAccess(Application_Model_User $user)
    {
        $select = $this->getDbTable()->getAdapter()->select();
        
        $select->from(array('ud' => 'user_dataset_access_tbl'), 'ud.dataset_type_id')
               ->join(
                   array('u' => 'user_tbl'),
                   'u.user_id = ud.user_id',
                   array()
               );
        
        $select->where("u.user_id = ?", $user->getId());
        $db = $this->getDbTable()->getAdapter();
        $user->setDatasetAccess($db->fetchAll($select, array(), Zend_Db::FETCH_COLUMN));
        
    }
    
    public function getImageAccess(Application_Model_User $user)
    {        
        $select = $this->getDbTable()->getAdapter()->select();
        
        $select->from(array('us' => 'user_software_access_tbl'), 'us.software_type_id')
               ->join(
                   array('u' => 'user_tbl'),
                   'u.user_id = us.user_id',
                   array()
               );
        
        $select->where("u.user_id = ?", $user->getId());
        $db = $this->getDbTable()->getAdapter();
        $user->setImageAccess($db->fetchAll($select, array(), Zend_Db::FETCH_COLUMN));        
    }
    
    public function populateAdditionalUsersToShareWith(Application_Model_User $user)
    {        
        $db = $this->getDbTable()->getAdapter();
        $select = $db->select();
        
        $select->from(array('us' => 'additional_share_user_tbl'), 'us.additional_user_id')
               ->join(
                   array('u' => 'user_tbl'),
                   'u.user_id = us.user_id',
                   array()
               );
        
        $select->where("u.user_id = ?", $user->getId());
        $data = $db->fetchAll($select, array(), Zend_Db::FETCH_COLUMN);
        $user->setAdditionalUsersToShareWith($data);        
    }
    
    private function saveAdditionalUsersToShareWith(Application_Model_User $user)
    {
    	$data = $user->getAdditionalUsersToShareWith();
    	$db = $this->getDbTable()->getAdapter(); 
        
    	$db->delete(
            'additional_share_user_tbl',
            array('user_id = ?' => $user->getId())
        );
        foreach($data as $user_id) {
            $db->insert(
                'additional_share_user_tbl', 
                array('user_id' => $user->getId(), 'additional_user_id' => $user_id)
            );
        }
    }
    
    public function findAdministratorLogins($active = true)
    {
    	$userTable = $this->getDbTable();
        $select = $userTable->select();
		
        $columns = array('user_id', 'login_id');
        $select->from($userTable, $columns);
        if ($active)
            $select->where('active_flag = "Y"');
        
        $select->where('admin_flag = "Y"');        
        $select->order('login_id');
        
        $db = $userTable->getAdapter();
        $resultSet = $db->fetchPairs($select);
        return $resultSet;
    }
    /**
     * Creates pagination adapter for list of users.
     * @param int $sortColumn Internal identifier of sort column in the list.
     * @param string $sortDir Sort direction which is used for sorting data.
     * @param int $organizationId If of Organization to which report generated.
     * @return Zend_Paginator_Adapter_DbSelect Pagination adapter for list of users.
     */
    public function getPaginatorAdapter($sortColumn, $sortDir, $organizationId)
    {
        $select = $this->getUserListSelect($sortColumn, $sortDir, $organizationId);
        return new Zend_Paginator_Adapter_DbSelect($select);        
    }
    /**
     * Creates array of objects that represents information about users.
     * @param int $sortColumn Internal identifier of sort column in the list.
     * @param string $sortDir Sort direction which is used for sorting data.
     * @param int $organizationId If of Organization to which report generated.
     * @return array Array of objects with user information.
     */
    public function getUserList($sortColumn, $sortDir, $organizationId)
    {
        $select = $this->getUserListSelect($sortColumn, $sortDir, $organizationId);
        $db = $this->getDbTable()->getAdapter();
        $data = $db->fetchAll($select);
        return $data;
    }
    //todo@ move this to instancemapper
    public function getHistoryPaginatorAdapter($sortColumn, $sortDir, $organizationId, $userId, $statusFlag)
    {
        $columns = array(
            "startDate" => 'i.start_date',
            'terminateDate' => 'i.terminate_date',
            "login" => "u.login_id",
            "instanceName" => 'i.assigned_name',
            'instanceType' => "is.instance_size_name",
            "status" => 'i.status_flag',
            "dataset" => "group_concat(d.dataset_type_description SEPARATOR ', ')",
            "image" => "s.software_type_description",
            "charged" => "ROUND(is.instance_price * ( TIMESTAMPDIFF(SECOND, i.start_date, ifnull(i.terminate_date, utc_timestamp())) )/ 3600)",
            'userId' => "u.user_id",
            'instanceHost' => "i.public_dns"
        );
        
        if ($sortDir != 'asc' )
            $sortDir = 'desc';
        
        $sortCols = array_keys($columns);
        if ($sortColumn > 0 && $sortColumn < 10)
            $sortColumn = $sortCols[$sortColumn - 1];
        else
            $sortColumn = $sortCols[0];
        
        
        $select = $this->getDbTable()->getAdapter()->select();
        $select->from(array('i' => 'instance_tbl'), $columns)
               ->joinLeft(
                   array('ir' => 'instance_request_tbl'),
                   "ir.instance_request_id = i.instance_request_id",
                   array()
               )
               ->joinLeft(
                   array('u' => 'user_tbl'),
                   'u.user_id = ir.user_id',
                   array()
               )
               ->joinLeft(
                   array("s" => "software_type_tbl"),
                   "s.software_type_id = ir.software_type_id",
                   array()
               )
               ->joinLeft(
                   array('is' => 'instance_size_tbl'),
                   "is.instance_size_id = ir.instance_size_id",
                   array()
               )
               ->joinLeft(
                   array("id" => "instance_request_dataset_tbl"),
                   "id.instance_request_id = ir.instance_request_id",
                   array()
               )
               ->joinLeft(
                   array("d" => "dataset_type_tbl"),
                   "d.dataset_type_id = id.dataset_type_id",
                   array()
               );
        if ($organizationId != 0)
            $select->where("u.organization_id = ?", $organizationId);
        if ($userId != 0)
            $select->where("u.user_id = ?", $userId);
        
       if ($statusFlag)
           $select->where("i.status_flag = ?", $statusFlag);
       $select->group("i.instance_id");
       $select->order("$sortColumn $sortDir");
       
       return new Zend_Paginator_Adapter_DbSelect($select);
    }
    function getUniqueLoginValidator($exclude_id = null)
    {
        if ($exclude_id) {
            $exclude = array(
                'field' => 'user_id',
                'value' => $exclude_id
            );
        }
        else
            $exclude = null;
        return new Zend_Validate_Db_NoRecordExists('user_tbl','login_id' ,$exclude, $this->getDbTable()->getAdapter());
    }
    function getUniqueEmailValidator($exclude_id = null)
    {
        if ($exclude_id) {
            $exclude = array(
                'field' => 'user_id',
                'value' => $exclude_id
            );
        }
        else
            $exclude = null;
        return new Zend_Validate_Db_NoRecordExists('user_tbl','email' ,$exclude, $this->getDbTable()->getAdapter());
    }    
    private function generateDav($password, Application_Model_User $user)
    {
        exec('htpasswd -nb '.escapeshellarg($user->getLoginId()).' '.escapeshellarg($password), $output, $retval);
        if ($retval > 0)
        {
            throw new Exception("Cannot generate DAV password for the user {$user->getLoginId()}");
        }
        return $output[0];        
    }
    
    private function getPasswordInfo(Application_Model_User $user)
    {
        $data = array();
        $data['password_hash'] = md5($user->getPassword());
        $data['dav_password_hash'] = $this->generateDav($user->getPassword(), $user);
        $data['certificate_downloaded'] = 'Y';
        if ($user->getId()) {
            $sm = new SecurityManager();
            $sm->generate_security_event(
                SecurityManager::ISSUE_TYPE_PASSWORD_CHANGED,
                "User {$user->getLoginId()} changed password",
                $user->getId(),
                null,
                null,
                null
            );
        }
        $confMapper = new Application_Model_SiteConfigMapper();
        $config = $confMapper->getConfig();
        if ($config->getPasswordExpirationPeriod() == 0) {
            $data['password_expired'] = null;
        } else {
            $currentDate = new Zend_Date();
            $currentDate->add(
                $config->getPasswordExpirationPeriod(),
                Zend_Date::DAY);
            $data['password_expired'] = $currentDate->get("Y-MM-dd h:m:s");
        }
        
        return $data;
    }
    
    public function changeLogin($login, $oldLogin)
    {
        global $configurationManager;
        $configuration = new WebRLConfiguration($configurationManager);
        if (is_dir($configuration->folder_keys().$oldLogin))
        {
            $oldDir = $configuration->folder_keys().$oldLogin;
            $newDir = $configuration->folder_keys().$login;

            // Rename directory that holds scripts.
            rename($oldDir, $newDir);
            // And then all files inside.
            rename($newDir.'/'.$oldLogin, $newDir.'/'.$login);
            rename($newDir.'/'.$oldLogin.'.ppk', $newDir.'/'.$login.'.ppk');
            rename($newDir.'/'.$oldLogin.'.pub', $newDir.'/'.$login.'.pub');
        }
    }
    
    private function arrayToString(array $data)
    {
        $result = '';
        foreach ($data as $key => $entry)
        {
            if ($key != 'password') {
                $result .= $key.'='.$entry.', ';
            } else {
                $result .= $key.'= <Password>, ';
            }
        }
        return $result;
    }
    
    private function generateKeys(Application_Model_User $user)
    {
        global $configurationManager;
    	$configuration = new WebRLConfiguration($configurationManager);
        
        $cmd = '/bin/bash '.$configuration->support_scripts_folder().
            'create_key.sh '.escapeshellcmd($user->getLoginId());
        
        exec($cmd, $output, $retval);
        if ($retval > 0)
        {
            throw new Exception("Cannot generate key files for the user {$user->getLoginId()}");
        }
        $certificate = file_get_contents($configuration->folder_keys().
            $user->getLoginId().'/'.$user->getLoginId().'.pub');
        $user->setCertificate($certificate);

        return $certificate;
    }
    
    private function saveCertificateHistory(Application_Model_User $user, $certificate)
    {
        $currentUser = Membership::get_current_user();
        // Add certificate to history table
        $c = new UserCertificate();
        $c->user_id = $user->getId();
        $c->public_key = $certificate;
        $c->created_by = $currentUser->login_id;
        $c->created_date = gmdate('c');
        $c->has_password = 'N';
        $c->expiration_date = gmdate('c');
        $c->status_flag = 'N';
        $c->save();
        
    }
    
    private function savePasswordHistory(Application_Model_User $user)
    {
        $uph = new UserPasswordHistory();
        $uph->user_id = $user->getId();
        $uph->password_hash = md5($user->getPassword());
        $uph->change_date = gmdate('c');
        $uph->Save();
    }
    
    public function getOrgUsersList($orgId)
    {
        $select = $this->getDbTable()->select();
        $select->from(
            $this->getDbTable(),
            array(
                'id' => 'user_id',
                'name' => 'CONCAT(last_name, ", ", first_name, " (", login_id, ")")'
                )
        );
        if ($orgId)
            $select->where('organization_id = ?', $orgId);
        
        $select->order('name');
        
        return $this->getDbTable()->getAdapter()->fetchAll($select);
    }
    
    public function getAccessibleStorages($userId)
    {
        //not using Zend_Db. Need to refactor
        $db = $this->getDbTable()->getAdapter();
        $select = $db->select();
        $select->from("user_storage_access_tbl", "user_id")
               ->distinct()
               ->join("user_tbl", 
                          "user_tbl.user_id = user_storage_access_tbl.user_id", 
                           array('login_id')
               );
        $select->where("user_storage_access_tbl.grantee_id = ?", $userId);
        $select->where("user_tbl.active_flag = 'Y'");
        $select->order("user_tbl.login_id");
        $result = $db->fetchPairs($select);

        return $result;
    }
    /**
     * Creates SQL query that returns list of users.
     * @param int $sortColumn Internal identifier of sort column in the list.
     * @param string $sortDir Sort direction which is used for sorting data.
     * @param int $organizationId If of Organization to which report generated.
     * @return Zend_Db_Select Select object that represent SQL query for returning data.
     */
    private function getUserListSelect($sortColumn, $sortDir, $organizationId) 
    {
        $db = $this->getDbTable()->getAdapter();
        
        //Define subqueries
        $runningInstCount = $db->select()
                            ->from(array('r' => 'instance_request_tbl'), 'count(*)')
                            ->join(array('i' => 'instance_tbl'),
                                    'i.instance_request_id = r.instance_request_id',
                                    array()
                            )
                            ->where('i.status_flag = "A" AND r.user_id = u.user_id');
        $instCount = $db->select()
                        ->from(array('r' => 'instance_request_tbl'), 'count(*)')
                        ->join(array('i' => 'instance_tbl'),
                                'i.instance_request_id = r.instance_request_id',
                                array()
                        )
                        ->where('r.user_id = u.user_id');
        $charge = $db->select()
                     ->from(
                             array('r' => 'instance_request_tbl'),
                             'ROUND(SUM(s.instance_price * TIMESTAMPDIFF(SECOND, i.start_date, IFNULL(i.terminate_date, utc_timestamp()) ))/ 3600, 0)'
                     )
                     ->join(array('i' => 'instance_tbl'),
                             'i.instance_request_id = r.instance_request_id',
                             array()
                     )
                     ->join(array('s' => 'instance_size_tbl'),
                             's.instance_size_id = r.instance_size_id',
                             array()
                     )
                     ->where('r.user_id = u.user_id');
                     
          $lastInstanceTerminate = "case when (select i.start_date
from instance_tbl i 
join instance_request_tbl ir on i.instance_request_id = ir.instance_request_id 
where ir.user_id = u.user_id
AND i.status_flag in ('A','I')
order by i.start_date desc
limit 1) is not null then NULL ELSE (select i.terminate_date
from instance_tbl i 
join instance_request_tbl ir on i.instance_request_id = ir.instance_request_id 
where ir.user_id = u.user_id
AND i.status_flag = 'S' 
order by i.terminate_date desc
limit 1) END";
        $lastInstanceStart = "select i.start_date from instance_tbl i join instance_request_tbl ir on i.instance_request_id = ir.instance_request_id where ir.user_id = u.user_id order by i.start_date desc limit 1";
        $columns = array(
            'login' => 'u.login_id',
            'organization' => 'o.organization_name',
            'name' => 'CONCAT(u.last_name, ", ",u.first_name)',
            'email' => 'u.email',
            'active' => 'u.active_flag',
            'runningInstCount' => "($runningInstCount)",
            'totalCharged' => "($charge)",
            'remainingLimit' => 'u.user_money - IFNULL(('.$charge.'), 0.0)',
            "($instCount) as instCount",
            "($lastInstanceStart) as lastInstanceStart",
            "$lastInstanceTerminate as lastInstanceTerminate",
            'id' => 'u.user_id',
            'organization_id' => 'o.organization_id'
        );
        
        if ($sortDir != 'asc' )
            $sortDir = 'desc';
        
        $sortCols = array_keys($columns);
        if ($sortColumn > 0 && $sortColumn < 11)
            $sortColumn = $sortCols[$sortColumn - 1];
        else
            $sortColumn = $sortCols[0];
        $select = $db->select()->from(array('u' => 'user_tbl'), $columns)
                               ->joinLeft(array('o' => 'organization_tbl'),
                                       'o.organization_id = u.organization_id',
                                       array()
                               )
                               ->order("$sortColumn $sortDir");
        //$ss = "$select";
        //var_dump($ss );die();
        if ($organizationId != 0) {
            $select->where('u.organization_id = ?', $organizationId);
        }
        return $select;
    }
    private function ClearDatasetId($array, $separate = ''){
        $result_array = array();
        foreach ($array as $key => $value) {
        
            if(strpos($value, $separate) !== false)$result_array[] = str_replace($separate, '', $value);
        }
        return $result_array;
    }
    public function GetUserOracleAccess($user_id){
        $db = $this->getDbTable()->getAdapter();
        
        $select = $db->select()->from(array('u' => 'user_oracle_dataset_access_tbl'))
                
                                ->where('u.user_id = ?', $user_id);
       return $db->fetchAll($select);
        
    }
    
    
}
