<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 Dec 2010

    Controller plugin that checks access rights for user

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

class Application_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{

    private $_redirectTo = "/index.php?page=login";

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        $acl = Application_Model_Acl::getInstance();
        if ($identity) {
            $userManager = new UserManager(DbManager::$db);
            $user = $userManager->get_by_login($identity);
            if ($user->admin_flag === 'Y') {
                if (Membership::get_app_mode() == ApplicationMode::Admin) {
                    if ($user->organization_id == 0) {
                        $role = Application_Model_Acl::ROLE_ADMIN;
                    } else {
                        $role = Application_Model_Acl::ROLE_ORGADMIN;
                    }
                } else {
                    $role = Application_Model_Acl::ROLE_USER;
                }
            }
            else
                $role = Application_Model_Acl::ROLE_USER;
        }
        else
            $role = null;
        $controller = $request->controller;
        $action = $request->action;
        $resource = 'mvc:'.$controller;

        if ($acl->has($resource)) {
            if (!$acl->isAllowed($role, $resource, $action)) {
                //@TODO: use $request->setController and $request->setAction when login will be migrated to Zend
                $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
                $protocol = 'http'.($request->HTTPS != null? 's':'');
                $gotoUrl = $protocol.'://'.$request->HTTP_HOST.$this->_redirectTo;
                $redirector->gotoUrlAndExit($gotoUrl);
            }
        }

        $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        $view = $bootstrap->getResource('view');
        $activePage = $view->navigation()->setRole($role);
    }
}