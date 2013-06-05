<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    23 December 2010

    Controller for displaying uploaded to S3 results of run.

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

================================================================================*/
class S3resultsController extends Zend_Controller_Action
{
    const DEBUG = false;
    private $_logger;

    /**
     * S3 Results Model
     * @var Application_Model_UploadedRuns
     */
    private $_model;

    public function init()
    {
        if (Zend_Registry::isRegistered('logger')) {
            $this->_logger = Zend_Registry::get('logger');
        } else {
            $this->_logger = new Zend_Log(new Zend_Log_Writer_Null());
        }

        $config = SiteConfig::get();
        if (!isset($config->results_s3_bucket)) {
            throw new Exception('Runs path option not found!');
        }

        $manager = $this->view->config->manager;
        $awsSettings = $manager->settings['aws'];
        $awsAccessKey = $awsSettings['aws_access_key_id'];
        $awsSecretKey = $awsSettings['aws_secret_access_key'];
        $webrlSettings = $manager->settings['webrl'];
        /**
         * s3cmdConfig and s3cmdKeys are specified first to enable s3cmd.
         * On the next step s3cmd will be used to check that the bucket with name 'runsDir' does exist.
         */
        $modelParamters = array(
            's3ResultsCache' => $webrlSettings['s3_results_cache'],
            's3cmdConfig' => $webrlSettings['s3cmd_config'],
            's3cmdKeys' => $webrlSettings['s3cmd_keys'],
            'runsDir' => $config->results_s3_bucket,
            'AWSKey' => $awsAccessKey,
            'AWSSecretKey' => $awsSecretKey,
            'debug' => self::DEBUG
        );

        $this->_model = new Application_Model_UploadedRuns($modelParamters);
        }

    public function indexAction()
    {
        $this->_helper->redirector('list');
    }

    public function listAction()
    {
        
        $exeprimentModel = new Application_Model_DbTable_Experiment();
        
        $experiments = $exeprimentModel->getExperiments();
        $experimentId = $this->_getParam('experiment');
        
        $this->_logger->debug('Starting S3 results log.');
        
        $startTime = $this->_model->getMicrotime();
        
        $this->_logger->debug('Searching ' . $config->results_s3_bucket . ' for results.');

        $cfgMapper = new Application_Model_SiteConfigMapper();
        $config = $cfgMapper->getConfig();
        list($timeFormat) = explode(' ', $config->getDateFormat());

        $this->view->timeFormat = $timeFormat;
        $this->view->emptyDate = '<No Date>';
        $commonMethodsData = array();
        
        if ($experimentId) {
            $this->_model->setExperimentId($experimentId);
            $experiment = new Application_Model_Experiment();
            $experiment->find($experimentId);
            $this->_model->setExperimentName($experiment->getName());
            $commonMethodsData = $this->_model->getData();
            $oscarMethodData = $this->_model->getOscarData();
        }
        /*$allows_dataset = new Application_Model_RunResultsUploading();
        $this->view->datasets = $allows_dataset->get_access_dataset(); 
        */
        $this->view->datasets = $this->_model->getDatasets();
        $this->view->methods = $this->_model->getMethods();
        $this->_logger->debug('Found '.count($commonMethodsData).' method results.');
        $this->_logger->debug('Found '.count($oscarMethodData).' OSCAR results.');
        if (self::DEBUG) {
            $dump  =  'List of files not belonging to any of methods:' . PHP_EOL;
            $dump .= implode(PHP_EOL, $this->_model->getNotMatchedFiles());
            $dump .= PHP_EOL . PHP_EOL;

            $dump .= 'Extended debug information:' . PHP_EOL;
            $dump .= implode(PHP_EOL, $this->_model->getDump());
            if (false === file_put_contents('/var/log/omop/debug_dump', $dump)) {
                throw new Exception('Can not save log file.');
            }
        }
        
        $commonMethodsData['OSCAR'] = $oscarMethodData['OSCAR'];
        unset($oscarMethodData); 

        $endTime = $this->_model->getMicrotime();

        $this->view->methodRuns = $commonMethodsData;
        $this->view->experiments = $experiments;
        $this->view->experiment = $experimentId;
        $this->_logger->debug('Found results for '.count($results).' methods.');
        $this->_logger->debug('S3 results processing time is: '.($endTime-$startTime).' seconds.');

        $this->_logger->debug('Finished logging S3 results.');
    }
    
    public function runsAction()
    {
        $data = array(
            'error' => 0,
            'data' => array()
        );

        $request = $this->getRequest();
        $experiment = $request->getParam('experiment');
        $method  = $request->getParam('method');
        $dataset = $request->getParam('dataset');
        $this->_model->setExperimentId($experiment);
        $experimentModel = new Application_Model_Experiment();
        $experimentModel->find($experiment);
        $this->_model->setExperimentName($experimentModel->getName());
        $data['data'] = $this->_model->getRunList($method, $dataset);
        if (is_null($data['data'])) {
            $data['error'] = 1;
            $data['data']  = array();
        }

        $this->_helper->json($data);
    }

    public function rundetailsAction()
    {
        $data = array(
            'error' => 0,
            'data' => array()
        );

        $request = $this->getRequest();
        $method  = $request->getParam('method');
        $dataset = $request->getParam('dataset');
        $run     = $request->getParam('run');
        $experiment = $request->getParam('experiment');
        $experimentModel = new Application_Model_Experiment();
        $experimentModel->find($experiment);
        $this->_model->setExperimentName($experimentModel->getName());
        $this->_model->setExperimentId($experiment);
        $data['data'] = $this->_model->getRunDetails($method, $dataset, $run);
        if (is_null($data['data'])) {
            $data['error'] = 1;
            $data['data']  = array();
        }

        $this->_helper->json($data);
    }
    
    public function downloadrunAction() {
        $request = $this->getRequest();
        $method = $request->getParam('method');
        $dataset = $request->getParam('dataset');
        $run = $request->getParam('run');
        $experiment = $request->getParam('experiment');
        $experimentModel = new Application_Model_Experiment();
        $experimentModel->find($experiment);
        $this->_model->setExperimentName($experimentModel->getName());
        $this->_model->setExperimentId($experiment);
        $zipFile = $this->_model->downloadRunResults($method, $dataset, $run);
        if ($zipFile) {
            header('Content-Type: application/zip');
            header('Content-Length: '. filesize($zipFile));
            header('Content-Disposition: attachment; filename="'.$run.'.zip"');
            readfile($zipFile);
        
            $this->view->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            unlink($zipFile);
        }
        
    }
}
