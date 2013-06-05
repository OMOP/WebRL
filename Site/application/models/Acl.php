<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 Dec 2010

    Acl model of WebRL project

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
class Application_Model_Acl extends Zend_Acl {

    const ROLE_USER = 'user';
    const ROLE_ADMIN = 'admin';
    const ROLE_ORGADMIN = 'org_admin';

    protected static $_instance;

    protected function __construct()
    {

        $this->addRole(new Zend_Acl_Role(self::ROLE_USER));
        $this->addRole(new Zend_Acl_Role(self::ROLE_ADMIN));
        $this->addRole(new Zend_Acl_Role(self::ROLE_ORGADMIN));

        $this->add(new Zend_Acl_Resource('mvc:method'));
        $this->add(new Zend_Acl_Resource('mvc:configuration'));
        $this->add(new Zend_Acl_Resource('mvc:s3results'));
        $this->add(new Zend_Acl_Resource('mvc:system-instance'));
        $this->add(new Zend_Acl_Resource('mvc:source'));
        $this->add(new Zend_Acl_Resource('mvc:temporary-storage'));
        $this->add(new Zend_Acl_Resource('mvc:software-type'));
        $this->add(new Zend_Acl_Resource('mvc:dataset'));
        $this->add(new Zend_Acl_Resource('mvc:instance-launch-defaults'));
        $this->add(new Zend_Acl_Resource('mvc:instance-launch'));
        $this->add(new Zend_Acl_Resource('mvc:site-setup'));
        $this->add(new Zend_Acl_Resource('mvc:security-log'));
        $this->add(new Zend_Acl_Resource('mvc:connect-log'));
        $this->add(new Zend_Acl_Resource('mvc:audit-trail'));
        $this->add(new Zend_Acl_Resource('mvc:event'));
        $this->add(new Zend_Acl_Resource('mvc:error'));
        $this->add(new Zend_Acl_Resource('mvc:amazon-log'));
        $this->add(new Zend_Acl_Resource('mvc:user'));
        $this->add(new Zend_Acl_Resource('mvc:budget'));
        $this->add(new Zend_Acl_Resource('mvc:osim2'));
        $this->add(new Zend_Acl_Resource('mvc:instance'));
        $this->add(new Zend_Acl_Resource('mvc:running-method'));
        $this->add(new Zend_Acl_Resource('mvc:tools-download'));
        $this->add(new Zend_Acl_Resource('mvc:download'));
        $this->add(new Zend_Acl_Resource('mvc:experiment'));
        
        $this->deny(self::ROLE_USER, 'mvc:method');
        $this->deny(self::ROLE_USER, 'mvc:configuration');
        $this->allow(self::ROLE_USER, 'mvc:s3results');
        $this->deny(self::ROLE_USER, 'mvc:system-instance');
        $this->deny(self::ROLE_USER, 'mvc:source');
        $this->deny(self::ROLE_USER, 'mvc:temporary-storage');
        $this->deny(self::ROLE_USER, 'mvc:software-type');
        $this->deny(self::ROLE_USER, 'mvc:dataset');
        $this->deny(self::ROLE_USER, 'mvc:instance-launch-defaults');
        $this->allow(self::ROLE_USER, 'mvc:instance-launch');
        $this->deny(self::ROLE_USER, 'mvc:site-setup');
        $this->deny(self::ROLE_USER, 'mvc:security-log');
        $this->deny(self::ROLE_USER, 'mvc:connect-log');
        $this->deny(self::ROLE_USER, 'mvc:audit-trail');
        $this->deny(self::ROLE_USER, 'mvc:event');
        $this->deny(self::ROLE_USER, 'mvc:error', 'log');
        $this->deny(self::ROLE_USER, 'mvc:amazon-log');
        $this->deny(self::ROLE_USER, 'mvc:user');
        $this->deny(self::ROLE_USER, 'mvc:experiment');
        $this->allow(self::ROLE_USER, 'mvc:user', 'edit');
        $this->allow(self::ROLE_USER, 'mvc:user', 'edit-account');
        $this->deny(self::ROLE_USER, 'mvc:budget');
        $this->allow(self::ROLE_USER, 'mvc:instance');
        $this->allow(self::ROLE_USER, 'mvc:running-method');
        $this->allow(self::ROLE_USER, 'mvc:tools-download');
        $this->allow(self::ROLE_USER, 'mvc:download');

        $this->allow(self::ROLE_ORGADMIN, 'mvc:s3results');
        $this->allow(self::ROLE_ORGADMIN, 'mvc:user');
        $this->allow(self::ROLE_ORGADMIN, 'mvc:osim2');
        $this->allow(self::ROLE_ORGADMIN, 'mvc:instance');
        $this->allow(self::ROLE_ORGADMIN, 'mvc:running-method');

        $this->allow(self::ROLE_ADMIN, 'mvc:method');
        $this->allow(self::ROLE_ADMIN, 'mvc:configuration');
        $this->allow(self::ROLE_ADMIN, 'mvc:s3results');
        $this->allow(self::ROLE_ADMIN, 'mvc:system-instance');
        $this->allow(self::ROLE_ADMIN, 'mvc:source');
        $this->allow(self::ROLE_ADMIN, 'mvc:temporary-storage');
        $this->allow(self::ROLE_ADMIN, 'mvc:software-type');
        $this->allow(self::ROLE_ADMIN, 'mvc:dataset');
        $this->allow(self::ROLE_ADMIN, 'mvc:instance-launch-defaults');
        $this->allow(self::ROLE_ADMIN, 'mvc:instance-launch');
        $this->allow(self::ROLE_ADMIN, 'mvc:site-setup');
        $this->allow(self::ROLE_ADMIN, 'mvc:security-log');
        $this->allow(self::ROLE_ADMIN, 'mvc:connect-log');
        $this->allow(self::ROLE_ADMIN, 'mvc:audit-trail');
        $this->allow(self::ROLE_ADMIN, 'mvc:event');
        $this->allow(self::ROLE_ADMIN, 'mvc:error', 'log');
        $this->allow(self::ROLE_ADMIN, 'mvc:amazon-log');
        $this->allow(self::ROLE_ADMIN, 'mvc:user');        
        $this->allow(self::ROLE_ADMIN, 'mvc:budget');
        $this->allow(self::ROLE_ADMIN, 'mvc:osim2');
        $this->allow(self::ROLE_ADMIN, 'mvc:instance');
        $this->allow(self::ROLE_ADMIN, 'mvc:running-method');
        $this->allow(self::ROLE_ADMIN, 'mvc:experiment');
        
        $this->allow(null, 'mvc:error', 'error');

        $resource = 'mvc:run.results';
        $this->add(new Zend_Acl_Resource($resource));
        $this->allow(self::ROLE_USER, $resource);
        $this->allow(self::ROLE_ADMIN, $resource);
        $this->allow(self::ROLE_ORGADMIN, $resource);

        /*
        $resource = 'mvc:test';
        $this->add(new Zend_Acl_Resource($resource));
        $this->deny(self::ROLE_USER, $resource);
        $this->allow(self::ROLE_ADMIN, $resource);
        */
    }

    public function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

}
