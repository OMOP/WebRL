<?php
/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 Dec 2010

    Bootstrap file for OMOP WebRL project

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

==============================================================================*/
require_once("OMOP/WebRL/Configuration/WebRLConfiguration.php");
require_once('OMOP/WebRL/Configuration/DbConfiguration.php');


class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /*
     * Performs initialization of code that specific to OMOP
     * This code should be later moved to Bootstrapper Resource 
     * class, to make reusing of this code easier. 
     */
    protected function _initOMOP()
    {
        //Set all placeholders for project pages
        $loaderOptions = array(
            'namespace'=>'Application', 
            'basePath' => APPLICATION_PATH
        );
        $loader = new Zend_Application_Module_Autoloader($loaderOptions);
        $loader->addResourceType('validator', 'validators/', 'Validator');
        $loader->addResourceType('plugin', 'plugins/', 'Plugin');
        //Register plugins for front controller
        $frontController = Zend_Controller_Front::getInstance();
        $frontController->registerPlugin(new Application_Plugin_PageTitle());
        $frontController->registerPlugin(new Application_Plugin_Acl());
        $backUrl = new BackUrlHelper();
        Zend_Controller_Action_HelperBroker::addHelper($backUrl);
        
        $this->bootstrap('view');
        $view = $this->getResource('view');
        //Add view helpers path
        $helperPath = APPLICATION_PATH . '/views/helpers/';
        $view->addHelperPath($helperPath, 'Application_View_Helper_');
        
        //Set Doctype
        $view->doctype('XHTML1_TRANSITIONAL');
        
        //Set site header
        $view->headTitle('OMOP')
             ->setSeparator(': ');
        
        //Add CSS files
        $view->headLink()->prependStylesheet('/main.css');
        
        //Add favicon
        $view->headLink()->headLink(
            array(
                'rel' => 'icon',
                'type' => 'image/png',
                'href' => '/images/favicon.png'
            )
        );

        //Add JS files
        $onLoad = '
adjust_menu();
adjust_height();
if (typeof page != "undefined" && typeof eval("setup_"+page) == "function") {
    eval("setup_"+page+"();");
}';
        $headStript = $view->headScript(); 
        $headStript->appendFile('/js/jquery-1.3.2.min.js')
            ->appendFile('/js/jquery.validate.js')
            ->appendFile('/js/application.js')
            ->appendScript('$(document).ready(function(){'.$onLoad.'});');

        //Add Meta tags
        $view->headMeta()->appendHttpEquiv("X-UA-Compatible", "IE=7")
            ->appendHttpEquiv("Content-Type", "text/html; charset=UTF-8");
        
        $view->placeholder('pageTitle')
             ->setPrefix('<h1>')
             ->setPostfix('</h1>');
    }

    /*
     * Performs initialization of WebRL specific parts
     * Two database connections, session, authentication,
     * Exception handling policy, configuration.
     */
    protected function _initWebRL() 
    {
        global $configurationManager;
        $this->bootstrap('oracleResultsConnection');
        
        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->config = new WebRLConfiguration($configurationManager);
        
        //Init Authentication
        $auth = Zend_Auth::getInstance();
        
        // For consistensy with non-ZF part of project custom storage 
        // is used here. When migration to ZF will be done, 
        // Zend_Auth_Storage_Session should be used
        $auth->setStorage(new AuthStorage());
        $htmlPath = $_SERVER['DOCUMENT_ROOT'] ? $_SERVER['DOCUMENT_ROOT'] : APPLICATION_PATH . '/../html/';
        require_once($htmlPath . DIRECTORY_SEPARATOR . 'common.php');
    }

    /*
     * Performs initialization of RL Backend Daemon specific parts
     * Two database connections, session, authentication,
     * Exception handling policy, configuration.
     */
    protected function _initBackendDaemon()
    {
        $this->bootstrap('oracleResultsConnection');
        
        //Init Authentication
        $auth = Zend_Auth::getInstance();
    }
    /*
     * Performs initialization of connection to Oracle Results database schema.
     */
    protected function _initOracleResultsConnection()
    {
        global $configurationManager;
        $dbConfig = new DbConfiguration($configurationManager);
        putenv('ORACLE_HOME=' . getenv('ORACLE_HOME'));
        $oracleAdapter = Zend_Db::factory(
            'Oracle', array(
                'username' => $dbConfig->get('oracle_username', ''),
                'password' => $dbConfig->get('oracle_password', ''),
                'dbname' => $dbConfig->get('oracle_tns', '')
            )
        );

        Zend_Registry::set('oracle_adapter', $oracleAdapter);
    }
    /**
     * Initialize all known configuration.
     */
    protected function _initConfiguration()
    {   
        $this->bootstrap('awsConfiguration');
        $this->bootstrap('osim2Configuration');
    }
    /**
     * Initialize AWS specific configuration.
     */
    protected function _initAwsConfiguration()
    {
        $configurationFile = '/etc/webrl/webrl.conf';
        $awsConfig = new Zend_Config_Ini($configurationFile, 'aws');
        Zend_Registry::set('awsConfig', $awsConfig);
    }
    /**
     * Initialize OSIM2 configuration.
     */
    protected function _initOsim2Configuration()
    {
        $configurationFile = '/etc/webrl/webrl.conf';
        $osim2Config = new Zend_Config_Ini($configurationFile, 'osim2');
        Zend_Registry::set('osim2Config', $osim2Config);
    }
    /**
     * Performs initialization of connection to Oracle OSIM2 database schema.
     */
    protected function _initOracleOsim2Connection()
    {
        $this->bootstrap('configuration');
        $osim2Config = Zend_Registry::get('osim2Config');
        
        putenv('ORACLE_HOME=' . getenv('ORACLE_HOME'));
        $oracleAdapter = Zend_Db::factory(
            'Oracle', array(
                'username' => $osim2Config->oracle_username,
                'password' => $osim2Config->oracle_password,
                'dbname' => $osim2Config->oracle_tns,
            )
        );

        Zend_Registry::set('osim2OracleAdapter', $oracleAdapter);
    }

    public function _initPaginator()
    {
        
        $configurationFile = '/etc/webrl/webrl.conf';
        try {
            $pageConfig = new Zend_Config_Ini($configurationFile, 'pager');
            Zend_Paginator::setDefaultScrollingStyle('Sliding');
            Zend_Paginator::setDefaultItemCountPerPage($pageConfig->per_page_content);
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('_paginator.phtml');
            Zend_Registry::set('pagerConfig', $pageConfig);
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
        }
    }

    public function _initMenu()
    {		
        $user = Membership::get_current_user();
        
        // Generate user menu
        $userPages = array();
        $userPages[] = array(
            'label' => 'Running Instances',
            'title' => 'Running Instances',
            'controller' => 'instance',
            'action' => 'list',
            'resource' => 'mvc:instance',
            'tab' => true
        );
        if ($user->admin_flag == 'Y') {
            $userPages[] = array(
                'label' => 'Running Methods',
                'title' => 'Running Methods',
                'controller' => 'running-method',
                'action' => 'list',
                'resource' => 'mvc:running-method',
                'tab' => true
            );
            $userPages[] = array(
                'label' => 'Method Launch',
                'title' => 'Method Launch',
                'controller' => 'instance-launch',
                'action' => 'method',
                'resource' => 'mvc:instance-launch',
                'tab' => true
            );
        }
        $userPages[] = array(
            'label' => 'Launch',
            'title' => 'Launch',
            'controller' => 'instance-launch',
            'action' => 'instance',
            'resource' => 'mvc:instance-launch',
            'tab' => true
        );
        if ($user->svn_access_flag == 'Y') {
            $userPages[] = array(
                'label' => 'Subversion',
                'title' => 'Subversion',
                'uri' => '/websvn',
                'tab' => true
            );
        }
        if ($user->result_access_flag == 'Y') {
$userPages[] = array(
                'label' => 'Load Results',
                'controller' => 'run.results',
                'action' => 'default',
                'resource' => 'mvc:run.results',
                'pages' => array(
                            array(
                                'label' => 'Upload Results',
                                'title' => 'Upload Results',
                                'controller' => 'run.results',
                                'action' => 'index',
                                'tab'=>true
                            ),
                            array(
                                'label' => 'Oracle Results',
                                'title' => 'Oracle Results',
                                'controller' => 'run.results',
                                'action' => 'oracle',
                                'tab'=>true
                            ),
                            array(
                                'label' => 'S3 Results',
                                'title' => 'S3 Results',
                                'controller' => 's3results',
                                'action' => 'list',
                                'tab'=>true
                            ),
                            array(
                                'label' => 'Result Upload Logs',
                                'title' => 'Logs',
                                'controller' => 'run.results',
                                'action' => 'logs',
                                'tab' => true
                            )
                        )
            );     
        }

//////////



        $userPages[] = array(
            'label' => 'Client Install',
            'title' => 'Client Install',
            'controller' => 'tools-download',
            'action' => 'index',
            'resource' => 'mvc:tools-download',
            'tab' => true,
            'class' => 'right'
        );
        $userPages[] = array(
            'label' => 'Edit Account',
            'title' => 'Edit Account',
            'controller' => 'user',
            'action' => 'edit-account',
            'resource' => 'mvc:user',
            'tab' => false
        );
        
        // Generate menu items for admin suers
        $commonPages = array(
            array(
                'label' => 'Instances',
                'controller' => 'instance',
                'action' => 'index',
                'resource' => 'mvc:instance',
                'pages' => array(
                    array(
                        'label' => 'Running Instances',
                        'title' => 'Running Instances',
                        'controller' => 'instance',
                        'action' => 'list',
                        'resource' => 'mvc:instance',
                        'tab' => true
                    ),
                    array(
                        'label' => 'Running Methods',
                        'title' => 'Running Methods',
                        'controller' => 'running-method',
                        'action' => 'list',
                        'resource' => 'mvc:running-method',
                        'tab' => true
                    )
                )
            )
        );
        $commonAdminPages = array(            
            array(
                'label' => 'Users Management',
                'title' => 'Users Management',
                'controller' => 'user',
                'resource' => 'mvc:user',
                'pages' => array(
                    array(
                        'label' => 'User List',
                        'title' => 'User List',
                        'action' => 'list',
                        'controller' => 'user',
                        'tab' => true
                    ),
                    array(
                        'label' => 'Organization List',
                        'title' => 'Organization List',
                        'uri' => '/index.php?page=organizations',
                        'tab' => true
                    ),                    
                    array(
                        'label' => 'Add User',
                        'title' => 'Add User',
                        'action' => 'add',
                        'controller' => 'user',
                        'visible' => false,
                    ),
                    array(
                        'label' => 'Edit User',
                        'title' => 'Edit User',
                        'action' => 'edit',
                        'controller' => 'user',
                        'visible' => false,
                    ),
                    array(
                        'label' => 'User History',
                        'title' => 'User History',
                        'action' => 'history',
                        'controller' => 'user',
                        'visible' => false,
                    )
                )
            )/*,
            array(
                'label' => 'Result Storage',
                'controller' => 'run.results',
                'action' => 'default',
                'resource' => 'mvc:run.results',
                'pages' => array(
                          /*  array(
                                'label' => 'Upload Results',
                                'title' => 'Upload Results',
                                'controller' => 'run.results',
                                'action' => 'index',
                                'tab'=>true
                            ),*/
                            /*array(
                                'label' => 'Oracle Results',
                                'title' => 'Oracle Results',
                                'controller' => 'run.results',
                                'action' => 'oracle',
                                'tab'=>true
                            ),
                            array(
                                'label' => 'S3 Results',
                                'title' => 'S3 Results',
                                'controller' => 's3results',
                                'action' => 'list',
                                'tab'=>true
                            ),
                            array(
                                'label' => 'Result Upload Logs',
                                'title' => 'Logs',
                                'controller' => 'run.results',
                                'action' => 'logs',
                                'tab' => true
                            )
                        )
            ) */           
        );
        $osim2Pages = array(   
            array(
                'label' => 'OSIM2',
                'title' => 'OSIM2',
                'controller' => 'osim2',
                'resource' => 'mvc:osim2',
                'pages' => array(
                    array(
                        'label' => 'List OSIM2',
                        'title' => 'List OSIM2',
                        'controller' => 'osim2',
                        'action' => 'list-datasets',
                        'tab' => true
                    ),
                    array(
                        'label' => 'Summary Sets',
                        'title' => 'Summary Sets',
                        'controller' => 'osim2',
                        'action' => 'list-summary',
                        'tab' => true
                    ),
                    array(
                        'label' => 'Generate OSIM2',
                        'title' => 'Generate OSIM2',
                        'controller' => 'osim2',
                        'action' => 'generate-dataset',
                        'tab' => true
                    ),
                    array(
                        'label' => 'Summary Sets Details',
                        'title' => 'Summary Sets Details',
                        'controller' => 'osim2',
                        'action' => 'summary-details',
                        'visible' => false,
                        'tab' => false
                    ),
                    array(
                        'label' => 'Load Summary',
                        'title' => 'Load Summary Set',
                        'controller' => 'osim2',
                        'action' => 'load-summary',
                        'tab' => true
                    ),
                    array(
                        'label' => 'Load Summary FTP',
                        'title' => 'Load Summary Set FTP',
                        'controller' => 'osim2',
                        'action' => 'load-summary-ftp',
                        'tab' => true
                    ),
                    array(
                        'label' => 'Update Summary',
                        'title' => 'Update Summary Set',
                        'controller' => 'osim2',
                        'action' => 'update-summary',
                        'tab' => false,
                        'visible' => false
                    ),
                    array(
                        'label' => 'Generate Summary',
                        'title' => 'Generate Summary Set',
                        'controller' => 'osim2',
                        'action' => 'generate-summary',
                        'tab' => true
                    ),
                    array(
                        'label' => 'Summary Sets Details',
                        'title' => 'Summary Sets Details',
                        'controller' => 'osim2',
                        'action' => 'view-table',
                        'visible' => false,
                        'tab' => false
                    )
                )
            )
        );
        $sysAdminPages = array(            
            array(
                'label' => 'Budget',
                'title' => 'Budget',
                'controller' => 'budget',
                'action' => 'index',
                'tab' => true,
                'resource' => 'mvc:budget'
            ),            
            array(
                'label' => 'Security Log',
                'title' => 'Security Log',
                'controller' => 'security-log',
                'resource' => 'mvc:security-log',
                'pages' => array(
                    array(
                        'label' => 'Security Log',
                        'title' => 'Security Log',
                        'controller' => 'security-log',
                        'action' => 'list',
                        'tab' => true
                    ),
                    array(
                        'label' => 'Connect Log',
                        'title' => 'Instance Connect Log',
                        'controller' => 'connect-log',
                        'action' => 'instance',
                        'tab' => true
                    ),
                    array(
                        'label' => 'Web Connect Log',
                        'title' => 'Web Connect Log',
                        'controller' => 'connect-log',
                        'action' => 'web',
                        'tab' => true
                    ),
                    array(
                        'label' => 'Error Log',
                        'title' => 'Error Log',
                        'controller' => 'error',
                        'action' => 'log',
                        'tab' => true
                    ),
                    array(
                        'label' => 'Audit Trail',
                        'title' => 'Audit Trail',
                        'controller' => 'audit-trail',
                        'action' => 'list',
                        'tab' => true
                    ),
                    array(
                        'label' => 'Amazon Log',
                        'title' => 'Amazon Log',
                        'controller' => 'amazon-log',
                        'tab' => true
                    ),
                    array(
                        'label' => 'Instance Console Output',
                        'title' => 'Instance Console Output',
                        'controller' => 'amazon-log',
                        'action' => 'details',
                        'visible' => false,
                    )
                )
            ),
            array(
                'label' => 'Configuration',
                'title' => 'Configuration',
                'controller' => 'configuration',
                'action' => 'index',
                'resource' => 'mvc:configuration'
                
            ),
            array(
                'label' => 'Security Event Details',
                'title' => 'Security Event Details',
                'controller' => 'event',
                'action' => 'details',
                'visible' => false
            ),
            array(
                'label' => 'Error Details',
                'title' => 'Error Details',
                'controller' => 'event',
                'action' => 'exception',
                'visible' => false
            )
        ); 
        
        $hoiDoiPages = array(
            array(
                'label' => 'HOI/DOI replacement ',
                'title' => 'HOI/DOI replacement ',
                'controller' => 'methodreplacement',
                'action' => 'list',
                'visible' => false
            ),
            array(
                'label' => 'Edit HOI/DOI',
                'title' => 'Edit HOI/DOI',
                'controller' => 'methodreplacement',
                'action' => 'edit',
                'visible' => false
            ),
            array(
                'label' => 'Add HOI/DOI',
                'title' => 'Add HOI/DOI',
                'controller' => 'methodreplacement',
                'action' => 'add',
                'visible' => false
            )
        );
        $configurationPages = array(
            array(
                'label' => 'Methods',
                'title' => 'Methods List',
                'controller' => 'method',
                'visible' => false,
                'pages' => array(
                    array(
                        'label' => 'Methods',
                        'title' => 'Configure Methods',
                        'controller' => 'method',
                        'action' => 'list',
                        'visible' => false,
                        'tab' => true
                    ),
                    array(
                        'label' => 'Method Analysis',
                        'title' => 'Method Analysis',
                        'controller' => 'method',
                        'action' => 'analysis',
                        'visible' => false,
                        'tab' => true
                    ),
                    array('label' => 'Edit Method',
                        'title' => 'Edit Method',
                        'controller' => 'method',
                        'action' => 'edit',
                        'visible' => false
                    ),
                    array(
                        'label' => 'Add Method',
                        'title' => 'Add Method',
                        'controller' => 'method',
                        'action' => 'add',
                        'visible' => false
                    ),
                    array(
                        'label' => 'Analysis Upload Log',
                        'title' => 'Analysis Upload Log',
                        'controller' => 'method',
                        'action' => 'log',
                        'tab' => true
                    )
                )
            ),
            array(
                'label' => 'Storage Instances',
                'title' => 'Storage Instances',
                'controller' => 'storage-instance',
                'visible' => false,
                'pages' => array(
                    array(
                        'label' => 'Storage Instances',
                        'title' => 'Storage Instances List',
                        'controller' => 'storage-instance',
                        'action' => 'list',
                        'visible' => false,
                    ),
                    array(
                        'label' => 'Edit Storage Instance',
                        'title' => 'Edit Storage Instance',
                        'controller' => 'storage-instance',
                        'action' => 'edit',
                        'visible' => false,
                    ),
                    array('label' => 'Add Storage Instance',
                        'title' => 'Add Storage Instance',
                        'controller' => 'storage-instance',
                        'action' => 'add',
                        'visible' => false
                    )
                )
            ),
            array(
                'label' => 'System Instances',
                'title' => 'System Instances',
                'controller' => 'system-instance',
                'visible' => false,
                'pages' => array(
                    array(
                        'label' => 'Configure System Instances',
                        'title' => 'Configure System Instances',
                        'controller' => 'system-instance',
                        'action' => 'list',
                        'visible' => false,
                    ),
                    array(
                        'label' => 'Edit System Instance',
                        'title' => 'Edit System Instance',
                        'controller' => 'system-instance',
                        'action' => 'edit',
                        'visible' => false,
                    ),
                    array('label' => 'Add System Instance',
                        'title' => 'Add System Instance',
                        'controller' => 'system-instance',
                        'action' => 'add',
                        'visible' => false
                    )
                )
            ),
            array(
                'label' => 'Sources',
                'title' => 'Sources',
                'controller' => 'source',
                'visible' => false,
                'pages' => array(
                    array(
                        'label' => 'Sources',
                        'title' => 'Configure Sources',
                        'controller' => 'source',
                        'action' => 'list',
                        'visible' => false,
                    ),
                    array(
                        'label' => 'Edit Source',
                        'title' => 'Edit Source',
                        'controller' => 'source',
                        'action' => 'edit',
                        'visible' => false,
                    ),
                    array('label' => 'Add Source',
                        'title' => 'Add Source',
                        'controller' => 'source',
                        'action' => 'add',
                        'visible' => false
                    )
                )
            ),        
            array(
                'label' => 'Experiments',
                'title' => 'Experiments',
                'controller' => 'experient',
                'visible' => false,
                'pages' => array(
                    array(
                        'label' => 'Experiments',
                        'title' => 'Configure Experiments',
                        'controller' => 'experiment',
                        'action' => 'list',
                        'visible' => false,
                    ),
                    array(
                        'label' => 'Edit Experiment',
                        'title' => 'Edit Experiment',
                        'controller' => 'experiment',
                        'action' => 'edit',
                        'visible' => false,
                    ),
                    array('label' => 'Add Experiment',
                        'title' => 'Add Experiment',
                        'controller' => 'experiment',
                        'action' => 'add',
                        'visible' => false
                    )
                )
            ), 
            array(
                'label' => 'Temporary Storage EBS',
                'title' => 'Temporary Storage EBS',
                'controller' => 'temporary-storage',
                'visible' => false,
                'pages' => array(
                    array(
                        'label' => 'Temporary Storage EBS',
                        'title' => 'Temporary Storage EBS',
                        'controller' => 'temporary-storage',
                        'action' => 'list',
                        'visible' => false,
                    ),
                    array(
                        'label' => 'Edit Temporary Storage',
                        'title' => 'Edit Temporary Storage',
                        'controller' => 'temporary-storage',
                        'action' => 'edit',
                        'visible' => false,
                    ),
                    array('label' => 'Add Temporary Storage',
                        'title' => 'Add Temporary Storage',
                        'controller' => 'temporary-storage',
                        'action' => 'add',
                        'visible' => false
                    )
                )
            ),
            array(
                'label' => 'Software Types',
                'title' => 'Software Types',
                'controller' => 'software-type',
                'visible' => false,
                'pages' => array(
                    array(
                        'label' => 'Configure Images',
                        'title' => 'Configure Images',
                        'controller' => 'software-type',
                        'action' => 'list',
                        'visible' => false,
                    ),
                    array(
                        'label' => 'Edit Software Type',
                        'title' => 'Edit Software Type',
                        'controller' => 'software-type',
                        'action' => 'edit',
                        'visible' => false,
                    ),
                    array('label' => 'Add Software Type',
                        'title' => 'Add Software Type',
                        'controller' => 'software-type',
                        'action' => 'add',
                        'visible' => false
                    )
                )
            ),            
            array(
                'label' => 'Datasets',
                'title' => 'Datasets',
                'controller' => 'dataset',
                'visible' => false,
                'pages' => array(
                    array(
                        'label' => 'Configure Datasets',
                        'title' => 'Configure Datasets',
                        'controller' => 'dataset',
                        'action' => 'list',
                        'visible' => false,
                    ),
                    array(
                        'label' => 'Edit Dataset',
                        'title' => 'Edit Dataset',
                        'controller' => 'dataset',
                        'action' => 'edit',
                        'visible' => false,
                    ),
                    array('label' => 'Add Dataset',
                        'title' => 'Add Dataset',
                        'controller' => 'dataset',
                        'action' => 'add',
                        'visible' => false
                    )
                )
            ),
            array(
                'label' => 'Instance Launch Defaults',
                'title' => 'Instance Launch Defaults',
                'controller' => 'instance-launch-defaults',
                'visible' => false
            ),
            array(
                'label' => 'Site Setup',
                'title' => 'Site Setup',
                'controller' => 'site-setup',
                'visible' => false
            )
        );
        

        $container = new Zend_Navigation();

        if (Membership::get_app_mode() == ApplicationMode::Admin) {
            $container->addPages($commonPages)
                      ->addPages($commonAdminPages);
        } else {
            $container->addPages($userPages);
        }
        //$container->addPages($osim2Pages)
                  $container->addPages($sysAdminPages)
                  ->addPages($configurationPages)
                  ->addPages($hoiDoiPages);
        
        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->navigation($container);
        $view->navigation()->setAcl(Application_Model_Acl::getInstance());
    }

}

