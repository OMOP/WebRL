
<?php
/*=============================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    10 Mar 2011
 
    Controller for pages that performs OSIM2 management.
 
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

final class Osim2Controller extends Zend_Controller_Action
{
    
    public function init()
    {
        $this->_redirector = $this->_helper->getHelper('redirector');
    }
    
    public function indexAction()
    {
        $this->_redirector->gotoSimple('list-datasets');
    }
    
    public function listSummaryAction()
    {
        $request = $this->getRequest();
        $pageNumber = $request->getParam('page', 1);
        //Default is Abbr column
        $sortColumn = $request->getParam('sort', 2);
        $sortDirection = $request->getParam('dir', 'asc');

        $mapper = new Application_Model_Osim2Mapper();
        $adapter = $mapper->getSummaryPaginatorAdapter($sortColumn, $sortDirection);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($pageNumber);
        $paginator->setPageRange(5);
        $this->view->sort_column = $sortColumn;
        $this->view->sort_dir = $sortDirection;
        $this->view->paginator = $paginator;
    }
    
    public function loadSummaryAction()
    {
        $request = $this->getRequest();
        $form = new Application_Form_Osim2LoadSummary();
        $summary = new Application_Model_Osim2AnalysisSummary();
        
        if ($request->isGet()) {
            $form->populate(array(
                'id' => $summary->nextSummaryId()
            ));
            $this->view->form = $form;            
        }
        
        if ($request->isPost()) {
            $postData = $request->getPost();
            if ($form->isValid($postData)) {
                $user = Membership::get_current_user();
                if ($form->definition->receive()) {
                    $newId = $form->newId->getValue();
                    $name = $form->name->getValue();
                    $description = $form->description->getValue();
                    $overrideExisting = $form->overrideExisting->getValue();
                    $override = false;
                    if ($summary->find($newId)) {
                        if (!$overrideExisting) {
                            $form->newId->addError("Summary set with this Id is already exists.");
                            $this->view->form = $form;
                            return;
                        } else {
                            $override = true;        
                        }
                    }
                    $summary2 = new Application_Model_Osim2AnalysisSummary();
                    if ($summary2->findByName($name)) {
                        if ($summary2->getId() != $newId) {
                            $form->name->addError("This name is already taken");
                            $this->view->form = $form;
                            return;
                        }
                    }
                    $summary->setId($newId);
                    $summary->setName($name);
                    $summary->setDescription($description);
                    
                    $summary->setCreatedBy($user->login_id);
                    date_default_timezone_set('UTC');
                    $currentDateString = date('Y-m-d H:m:s');
                    $currentDate = new Zend_Db_Expr(
                        "to_date('$currentDateString', 'yyyy/mm/dd hh24:mi:ss')"
                    );
                    $summary->setCreated($currentDate);
                    $summary->save();
                    $fileName = $form->definition->getFileName();
                    $errorMessage = $summary->extractDefinition($fileName);
                    if (!$errorMessage) {                        
                        $this->_redirector->gotoSimple('list-summary');                        
                    } else {
                        $form->definition->addError($errorMessage);
                        $this->view->form = $form;
                    }
                } else {
                    $this->view->form = $form;
                }
            } else {
                $this->view->form = $form;
            }            
        }        
    }
    
    public function loadSummaryFtpAction()
    {
        $request = $this->getRequest();
        $summary = new Application_Model_Osim2AnalysisSummary();
        $filesArray = array(
                '' => '-- None --'
            );
        $files = $summary->getPreparedFiles();
        if (count($files)) {
            $filesArray = array_merge(
                $filesArray,
                array_combine($files,$files)
            );
        }
        $form = new Application_Form_Osim2LoadSummaryFtp(array(
            'files' => $filesArray,
        ));
        
        if ($request->isGet()) {
            $form->populate(array(
                'id' => $summary->nextSummaryId()
            ));
            $this->view->form = $form;            
        }
        
        if ($request->isPost()) {
            $postData = $request->getPost();
            if ($form->isValid($postData)) {
                $user = Membership::get_current_user();
                $newId = $form->newId->getValue();
                $name = $form->name->getValue();
                $description = $form->description->getValue();
                $overrideExisting = $form->overrideExisting->getValue();
                $override = false;
                if ($summary->find($newId)) {
                    if (!$overrideExisting) {
                        $form->newId->addError("Summary set with this Id is already exists.");
                        $this->view->form = $form;
                        return;
                    } else {
                        $override = true;        
                    }
                }
                $summary2 = new Application_Model_Osim2AnalysisSummary();
                if ($summary2->findByName($name)) {
                    if ($summary2->getId() != $newId) {
                        $form->name->addError("This name is already taken");
                        $this->view->form = $form;
                        return;
                    }
                }
                $summary->setId($newId);
                $summary->setName($name);
                $summary->setDescription($description);
                
                $summary->setCreatedBy($user->login_id);
                date_default_timezone_set('UTC');
                $currentDateString = date('Y-m-d H:m:s');
                $currentDate = new Zend_Db_Expr(
                    "to_date('$currentDateString', 'yyyy/mm/dd hh24:mi:ss')"
                );
                $summary->setCreated($currentDate);
                $summary->save();
                
                $fileName = $form->definition->getValue();
                $errorMessage = $summary->extractDefinitionFtp($fileName);
                if (!$errorMessage) {                        
                    $this->_redirector->gotoSimple('list-summary');                        
                } else {
                    $form->definition->addError($errorMessage);
                    $this->view->form = $form;
                }
            } else {
                $this->view->form = $form;
            }            
        }        
    }
    
    public function updateSummaryAction()
    {
        $request = $this->getRequest();
        $form = new Application_Form_Osim2UpdateSummary();
        $id = $this->_getParam('id');
        $summary = new Application_Model_Osim2AnalysisSummary();
        if (false === $summary->find($id)) {
            throw new Exception("OSIM2 sumary with ID $id does not found.");
        }
        
        if ($request->isGet()) {
            $form->populate(array(
                'newId' => $summary->getId(),
                'name' => $summary->getName(),
                'description' => $summary->getDescription(),
            ));
            $this->view->form = $form;
        }
        
        if ($request->isPost()) {            
            $postData = $request->getPost();
            if ($form->isValid($postData)) {
                $user = Membership::get_current_user();
                
                $newId = $form->newId->getValue();
                $name = $form->name->getValue();
                $description = $form->description->getValue();
                
                $summary2 = new Application_Model_Osim2AnalysisSummary();
                if ($summary2->findByName($name)) {
                    if ($summary2->getId() != $id) {
                        $form->name->addError("This name is already taken");
                        $this->view->form = $form;
                        return;
                    }
                }
                if ($summary2->find($newId)) {
                    $form->newId->addError("This Id is already in use");
                    $this->view->form = $form;
                    return;    
                }
                //$summary->setId($newId);
                $summary->setName($name);
                $summary->setDescription($description);
                
                $summary->setUpdatedBy($user->login_id);
                date_default_timezone_set('UTC');
                $currentDateString = date('Y-m-d H:m:s');
                $currentDate = new Zend_Db_Expr(
                    "to_date('$currentDateString', 'yyyy/mm/dd hh24:mi:ss')"
                );
                $summary->setUpdated($currentDate);
                $summary->save();
                if ($id != $newId) {
                    $summary->changeId($newId);
                }
                $this->_redirector->gotoSimple('list-summary');
            } else {
                $this->view->form = $form;
            }            
        } else {
            $this->view->form = $form;
        }       
    }
    
    public function generateSummaryAction()
    {
        $mapper = new Application_Model_SourceMapper();
        $sourcePairs = $mapper->fetchPairs();
        
        $form = new Application_Form_Osim2GenerateSummary(array(
            'datasets' => $sourcePairs,
        ));
        $allSource = $mapper->getDbTable()->fetchAll();
        $this->view->form = $form;
        $this->view->allDatasets = $allSource;
    }
    
    public function generateDatasetAction()
    {
        $request = $this->getRequest();
        
        $analysisSummary = new Application_Model_Osim2AnalysisSummary();
        $summarySets = $analysisSummary->getPairs();
        
        $form = new Application_Form_Osim2GenerateDataset(array(
                'summarySets' => $summarySets,
            ));
        $osim2Config = Zend_Registry::get('osim2Config');
        $form->populate(
        	array('dbUsername' => $osim2Config->oracle_username,
        		'serverHost' => '',
        		'dbName' => $osim2Config->oracle_tns)
        );
        if ($request->isPost()) {
            $postData = $request->getPost();
            if ($form->isValid($postData)) {
            	$result = self::processGenerateDataset($request, $form);
                if ($result) {
	            	$this->_redirector->gotoSimple('list-datasets');
	                return;
                }
            }
        }
        
        $allSource = $analysisSummary->getAll();
        $this->view->allSummarySets = $allSource;
        $this->view->form = $form;
    }
    
    public function listDatasetsAction()
    {
        $request = $this->getRequest();
        $pageNumber = $request->getParam('page', 1);
        //Default is Abbr column
        $sortColumn = $request->getParam('sort', 2);
        $sortDirection = $request->getParam('dir', 'asc');

        $mapper = new Application_Model_Osim2Mapper();
        $adapter = $mapper->getSimulationSourcePaginatorAdapter($sortColumn, $sortDirection);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($pageNumber);
        $paginator->setPageRange(5);
        $this->view->sort_column = $sortColumn;
        $this->view->sort_dir = $sortDirection;
        $this->view->paginator = $paginator;
    }

    public function summaryDetailsAction()
    {
        $request = $this->getRequest();
        $id = $this->_getParam('id');
        $summary = new Application_Model_Osim2AnalysisSummary();
        if (false === $summary->find($id)) {
            throw new Exception("OSIM2 sumary with ID $id does not found.");
        }
        
        $this->view->entity = $summary;
        $this->view->tablesData = $summary->getStatistics();
    }
    
    public function downloadTableAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $request = $this->getRequest();
        $id = $this->_getParam('id');
        $tableName = $this->_getParam('table');
        $summary = new Application_Model_Osim2AnalysisSummary();
        if (false === $summary->find($id)) {
            throw new Exception("OSIM2 sumary with ID $id does not found.");
        }
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$tableName.'.csv"');
        echo self::buildCsvString($summary, $tableName);
    }
    
    public function downloadSummarySetAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $request = $this->getRequest();
        $id = $this->_getParam('id');
        $summary = new Application_Model_Osim2AnalysisSummary();
        if (false === $summary->find($id)) {
            throw new Exception("OSIM2 sumary with ID $id does not found.");
        }
        $zip = new ZipArchive;
        $zipFileName = tempnam(sys_get_temp_dir(), 'osim2');
        $res = $zip->open($zipFileName, ZipArchive::CREATE);
        $availableTables = $summary->getAvailableTables();
        if ($res === TRUE) {
            foreach($availableTables as $i => $tableName) {
                $fileName = strtolower($tableName).'.csv';
                $content = self::buildCsvString($summary, $tableName);
                $zip->addFromString($fileName, $content);
            }
            $zip->close();
        } else {
            throw new Exception('Could not create archvie');
        }
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="'.$summary->getName().'.zip"');
        readfile($zipFileName);
        unlink($zipFileName);
    }
    public function viewTableAction()
    {
        $request = $this->getRequest();
        $id = $this->_getParam('id');
        $table = $this->_getParam('table', 0);
        
        $page = $request->getParam('page', 1);
        //Default is Abbr column
        $sortColumn = $request->getParam('sort', 1);
        $sortDirection = $request->getParam('dir', 'asc');
        
        $summary = new Application_Model_Osim2AnalysisSummary();
        if (false === $summary->find($id)) {
            throw new Exception("OSIM2 sumary with ID $id does not found.");
        }
        $availableTables = $summary->getAvailableTables();
        if (!isset($availableTables[$table])) {
            throw new Exception("No such table.");
        }
        $tableName = $availableTables[$table];
        $columns = $summary->getTableColumns($tableName);
        
        $adapter = $summary->getTablePaginatorAdapter($tableName, $sortColumn, $sortDirection);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($page);
        $paginator->setPageRange(5);
        $this->view->currentUser = $user;
        $this->view->sort_column = $sortColumn;
        $this->view->sort_dir = $sortDirection;
        $this->view->paginator = $paginator;
        
        $this->view->currentId = $id;
        $this->view->currentTable = $table;
        $this->view->currentTableName = $tableName;
        $this->view->summarySets = $summary->getPairs();
        $this->view->availableTables = $availableTables;
        $this->view->columns = $columns;
    }
    private function processGenerateDataset($request, $form)
    {
    	$user = Membership::get_current_user();
                
        $summaryId = $form->summaryId->getValue();
        $name = $form->name->getValue();
        $dbEngine = $form->dbEngine->getValue();
        $hasSignal = $form->addSignal->getValue();
        $overrideServer = $form->overrideServer->getValue();
        $serverData = null;
        if ($overrideServer) {
        	$server = $form->serverHost->getValue();
            $dbName = $form->dbName->getValue();
        if (!$server) {
            $datasource = $dbName;
        } else {
        	$datasource = $server.'/'.$dbName;
        }
        $serverData = array(
        	'datasource' => $datasource,
        	'username' => $form->dbUsername->getValue(),
            'password' => $form->dbPassword->getValue());
        }
        $signalDefinition = '';
        if ($hasSignal == '1') {
        	if ($form->signal->receive() && $form->signal->isUploaded()) {
            	$fileName = $form->signal->getFileName();
                $config = self::getConfig();
                $location = $config->loader_working_dir;
                $signalDefinition = 'signal'.$summaryId.'.csv';
                if (!rename($fileName, $location.'/'.$signalDefinition)) {
                	$form->signal->addError("Could not move uploaded file");
	                return false;     
	          	}
	        	chmod($location.'/'.$signalDefinition, 0644);
			} else {
	            $form->signal->addError("Signal definition is required");
	        	return false;
	        }
		}
                
        $analysisSummary = new Application_Model_Osim2AnalysisSummary();
        $analysisSummary->find($summaryId);
        $personsCount = $form->patients->getValue();
        $personsCount = 1000 * $personsCount;
                
        $simulationSource = 
        	new Application_Model_Osim2SimulationSource();
        if ($simulationSource->find($summaryId) === false) {
			$simulationSource->setId($summaryId);
        }
		$simulationSource->setName($name);
       	$simulationSource->setDescription($analysisSummary->getDescription());
        $simulationSource->setAnalysisSourceId($summaryId);
        $simulationSource->setPatientQty($personsCount);
        $simulationSource->setHasSignal($hasSignal);
		$simulationSource->setStatus('P');
        $simulationSource->setCreatedBy($user->login_id);
        date_default_timezone_set('UTC');
        $currentDateString = date('Y-m-d H:m:s');
        $currentDate = new Zend_Db_Expr(
        	"to_date('$currentDateString', 'yyyy/mm/dd hh24:mi:ss')"
        );
        $simulationSource->setCreated($currentDate);
        $simulationSource->save();
        $analysisSummary->startGenerationProcess($dbEngine, $serverData, $personsCount, $signalDefinition);
                
        return true;
    }
    private function buildCsvString(Application_Model_Osim2AnalysisSummary $summary, $tableName)
    {    
        $columns = $summary->getTableColumns($tableName);
        $result = implode(',', $columns).PHP_EOL;
        $rows = $summary->getTableRows($tableName);
        foreach($rows as $rowData) {
            $result .= implode(',', $rowData).PHP_EOL;    
        }
        return $result;
    }
    private static function getConfig()
    {
        $osim2Config = Zend_Registry::get('osim2Config');
        return $osim2Config;    
    }
}
