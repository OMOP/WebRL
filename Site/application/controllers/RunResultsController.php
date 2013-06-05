<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2010
 
    Controller for Run Result Logs pages.
 
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

class RunResultsController extends Zend_Controller_Action
{
    private $_model;
    private $_model_access;
    private $_runSeparator = '|';
    private $_logger;
    private $_errors = array();

    public function init()
    {
        if (Zend_Registry::isRegistered('logger')) {
            $this->_logger = Zend_Registry::get('logger');
        } else {
            $this->_logger = new Zend_Log(new Zend_Log_Writer_Null());
        }
    }
    
    public function defaultAction()
    {
        $this->_helper->redirector('index');
    }

    public function indexAction()
    {  
        ini_set('upload_max_filesize', '1000M');
        $this->_model = new Application_Model_RunResultsUploading();

        $request = $this->getRequest();
        if ('upload' === $request->getParam('process', '')) {
            $options = array(
                            'override_results',
                            'load_s3',
                            'load_oracle',
                            );
            foreach ($options as $option) {
                $this->_model->setOption($option, '1' == $request->getParam($option, ''));
            }
            $this->_model->setOption('method', $request->getParam('method'));
            $this->_model->setAutoUploading(false);
            $this->_model->setExperimentData($request->getParam('experiment', ''));
            
            $clientShell = $request->getParam('clientshell', '');
            if ($clientShell) {
                $datasetId = $this->_model->getDatasetIdByAbbr($request->getParam('dataset', ''));
            }
            else {
                $datasetId = $request->getParam('dataset', '');
            }
            $this->_model->setDatasetData($datasetId);
            $folder = $this->_model->extractZip();
            $result = $this->launchUploading(
                    '1' == $request->getParam('load_s3', ''),
                    '1' == $request->getParam('load_oracle', ''),
                    '1' == $request->getParam('override_results', ''),
                    $request->getParam('experiment',null),
                    $datasetId ? $datasetId : null,
                    Membership::get_current_user()->user_id,
                    $folder,
                    $request->getParam('method')
                );
            $this->_errors = $this->_model->getErrors();
            if ($result && count($this->_errors) == 0) {
                $this->view->messages = array('Your upload was successfuly submitted. You will receive an email when it will finish.');
            } else {
                $this->view->errors = array('An error occurred while submitting an upload. Please contact administrator.');
            }
        }

        $this->view->formAction = '/public/run.results';
        $this->view->errors = $this->_model->getErrors();

        $this->view->options = $this->_model->getOptions();
        $this->view->datasetTypes = $this->_model->getDatasetTypes();
        $this->view->availableDatasets = array_intersect($this->view->datasetTypes,$this->_model->get_access_dataset(array()));
        $this->view->experimentTypes = $this->_model->getExperimentTypes();
        $this->view->dataset = $request->getParam('dataset', false);
        $this->view->experiment = $request->getParam('experiment', false);
    }

    public function logsAction()
    {
        $columns = array(
            array('column_name' => 'User', 'order_key' => '1'),
            array('column_name' => 'Date', 'order_key' => '`l`.`added`'),
            array('column_name' => 'Dataset', 'order_key' => '`l`.`dataset`'),
            array('column_name' => 'Method', 'order_key' => '`lr`.`method`'),
            array('column_name' => 'Runs', 'order_key' => '', 'sortable' => false),
            array('column_name' => 'Loaded to S3', 'order_key' => '', 'sortable' => false),
            array('column_name' => 'Loaded to Oracle', 'order_key' => '', 'sortable' => false),
            array('column_name' => 'Status', 'order_key' => '`l`.`error`', 'sortable' => true),
        );
        $defaultColumnId = 2;

        $request = $this->getRequest();
        $page = $request->getParam('page', 1);
        $sortColumn = $request->getParam('sort', $defaultColumnId);
        $sortDir = $request->getParam('dir', 'desc');

        $logsModel = new Application_Model_RunResultsUploadingLog();
        $currentOrderKey = $columns[$sortColumn - 1]['order_key'];
        $defaultOrderKey = $columns[$defaultColumnId]['order_key'];
        $orderKey = $currentOrderKey ? $currentOrderKey : $defaultOrderKey;
        $results = $logsModel->fetchSearchResults(
            $orderKey, 
            $sortDir,
            $this->_runSeparator
        );
        
        $paginator = Zend_Paginator::factory($results);
        $paginator->setCurrentPageNumber($page);

        /**
         * @todo remove hardcoded number
         */
        $paginator->setPageRange(5);
        $this->view->sort_column = $sortColumn;
        $this->view->sort_dir = $sortDir;
        $this->view->paginator = $paginator;
        $this->view->columns = $columns;
        $this->view->runSeparator = $this->_runSeparator;
    }

    /**
     * Action for viewing Oracle Run Results
     */
    public function oracleAction()
    {
        //@todo checking experiment_id
        $experimentId = $this->getRequest()->getParam('exp', '');
        
        $this->_model = new Application_Model_RunResultsOracle();
	$this->_model_access = new Application_Model_RunResultsUploading();
	$this->view->sources = $this->_model_access->get_access_dataset();        
 	//$this->view->sources = $this->_model->getDatasetTypes();
	$this->_logger->debug('Found '.count($this->view->sources).' datasets');
        
        if ($experimentId != '') {
            $methodResults = $this->_model->getMethodResults(array('experimentId' => $experimentId));
            $oscarResults = $this->_model->getOscarResults();
            
            $this->_logger->debug('Found '.count($methodResults).' method results');
            $this->_logger->debug('Found '.count($oscarResults).' oscar results');
            $results = array_merge(
                $methodResults,
                $oscarResults
            );
            ksort($results);
        } else {
            //@todo adding OSCAR
            $results = $this->_model->getMethods();
        }
        
        $runResultsUploading = new Application_Model_RunResultsUploading();
        
        $this->_logger->debug('Total '.count($results).' results');
        $this->view->results = $results;
        $this->view->experimentTypes = $runResultsUploading->getExperimentTypes();
        $this->view->experiment = $experimentId;
    }

    /**
     * Action for viewing Oracle Run Results
     */
    public function downloadresultAction()
    {
        // disable layout and view
        $this->view->layout()->disableLayout();        
        $this->_helper->viewRenderer->setNoRender(true);
        
        $this->_model = new Application_Model_RunResultsOracle();
        $request    = $this->getRequest();
        $analysisId = $request->getParam('analysisId');
        $dataset = $request->getParam('source');
        $experimentId = $request->getParam('experiment');
        $file       = $request->getParam('file');
        $type = $request->getParam('type', 'file');
        $sourceModel = new Application_Model_Source();
        $source = $sourceModel->findByAbbr($dataset);
        ini_set('memory_limit', '1000M');
        if ($type == 'suppl1') {
            $content = $this->_model->getSuppl1File($analysisId, $source->getId(), $experimentId);
        }
        if ($type == 'suppl2') {
            $content = $this->_model->getSuppl2File($analysisId, $source->getId(), $experimentId);            
        }
        if ($type == 'file') {
            $content = $this->_model->getResultFile($analysisId, $source->getId(), $experimentId);
        }

        header('Content-Type: plain/text');
	    header('Content-Disposition: attachment; filename="'.$file.'"');
        echo $content;
    }
    
    /*
     * Getting data for admin interface->Result Storage->Oracle results page
     */
    public function getOracleRunDetailsAction () {
        try {
            $request = $this->getRequest();
            $result = array();
            $result['error'] = '0';
            $result['data'] = array();
            $result['experiment_name'] = '';
            // disable the view and the layout
            $this->view->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            
            $methodName = $request->getParam('method', '');
            $sourceName = $request->getParam('source', '');
            $experimentId = $request->getParam('experiment', '');
            
            $method = new Application_Model_Method();
            $method->findByAbbr($methodName);
            if (!$method->getId()) {
                throw new Exception("Method is not defined");
            }
            
            $source = new Application_Model_Source();
            $source->findByAbbr($sourceName);
            if (!$source->getId()) {
                throw new Exception("Method is not defined");
            }
            
            $experiment = new Application_Model_Experiment();
            $experiment->find($experimentId);
            
            if (!$experiment->getId()) {
                throw new Exception("Experiment is not defined");
            }
            $result['experiment_name'] = $experiment->getName();
            
            $parameters = array(
                'method_id' => $method->getId(),
                'source_id' => $source->getId(),
                'experiment_id' => $experimentId,
                'source_name' => $sourceName,
                'hasSupplementals1' => $method->hasSupplementals1(),
                'hasSupplementals2' => $method->hasSupplementals2()
            );
            
            $runResults = new Application_Model_RunResultsOracle();
            $result['data'] = $runResults->getOracleResults($parameters);
        } catch (Exception $e) {
            $result['error'] = '1';
        }
        
        echo Zend_Json::encode($result);
    }
    
    private function launchUploading($load_s3, $load_oracle, $override, $experiment_id, $dataset_id, $user_id, $zip_dir,$method) {
        
        $cmd = 'nohup php '.APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'upload_results.php ';
        $arguments = ($override ? '--override ':'').($load_s3 ? '-s ': '').($load_oracle ? '-o ': '').($experiment_id?'-e '.$experiment_id.' ':'').($dataset_id?'-d '.$dataset_id.' ':'').('-u '.$user_id.' ').('-z '.$zip_dir.' ').('-m '.$method.' ');
        $cmd .= $arguments;
        $redirect_to = '/tmp/upload_log';
        $cmd .= ' > /tmp/upload_log 2>&1 ';
        exec($cmd, $output, $returnvar);
        if ($returnvar == 0) 
            return true;
        else {
            $exception_message = "Error during results uploading submit.";
            $event = new WebSiteEvent();
            $event->website_event_date = gmdate('c');
            $event->remote_ip = null;
            $event->website_event_message = file_get_contents($redirect_to);
            $event->website_event_description = $exception_message;
            $event->user_id = $user_id;
            $event->save();            
            return false;
        }
    }
}
