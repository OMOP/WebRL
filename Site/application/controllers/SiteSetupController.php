<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    10-Feb-2011
 
    Controller for pages that performs site setup.
 
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
require_once("OMOP/WebRL/MailManager.php");
class SiteSetupController extends Zend_Controller_Action
{
    
    public function init()
    {
        $this->_redirector = $this->_helper->getHelper('redirector');
    }
    
    public function indexAction()
    {
        $request = $this->getRequest();
        $form = new Application_Form_SiteSetup();
        $emailForm = new Application_Form_NotifyEmail();
        $mapper = new Application_Model_SiteConfigMapper();
        $config = $mapper->getConfig();
        if ($request->isGet()) {
            $form->populate($config->toArray());
        }
        
        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
                if ($config->getPasswordExpirationPeriod() != $request->getParam('passwordExpirationPeriod'))
                {
                    $um = new UserManager(DbManager::$db);
                    if ($request->getParam('passwordExpirationPeriod') != 0) {
                        $currentDate = new Zend_Date();
                        $currentDate->add($request->getParam('passwordExpirationPeriod'), Zend_Date::DAY);
                        $um->set_password_expiration_date(0, $currentDate->get("Y-MM-dd h:m:s"));
                    } else {
                        $um->set_password_expiration_date(0, null);
                    }
                }
                $config->setOptions($request->getPost());
                $config->save();
                $this->_redirector->gotoSimple('index', 'configuration');
            }
        }
        
        $this->view->form = $form;
        $this->view->emailForm = $emailForm;
    }
    
    public function emailAction()
    {
        global $configurationManager; 
        
        $mail_subject = $this->_getParam('subject');
        $mail_body = $this->_getParam('body');
        $important_flag = $this->_getParam('important');
        
        $mapper = new Application_Model_SiteConfigMapper();
        
        $admin_email = $mapper->getConfig()->getAdminEmail();
        $mailer = new MailManager($configurationManager);
        
        $active_users = $this->get_all_active_users();
        $mailer->send_mail_to_users_list($admin_email, $active_users, $mail_subject, $mail_body, $important_flag);

        $this->_redirector->gotoSimple('');
    }
    
    private function get_all_active_users()
    {
        $manager = new UserManager(DbManager::$db);
        $users = $manager->get_all_active_users(0);
        return $users;
    }
    
    
    
}