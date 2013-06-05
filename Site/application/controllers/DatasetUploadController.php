<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    18-Jul-2011
 
    Controller for the tools downloading
 
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

class DatasetUploadController extends Zend_Controller_Action {
    
    
    public function init()
    {
    }
    
    public function indexAction() {
        

        $form = new Application_Form_DatasetUpload();
            
        $this->view->form = $form;
    }
    
    public function loadAction() {
        $request = $this->getRequest();

        $form = new Application_Form_DatasetUpload();
        if ($form->isValid($request->getPost())) {
            $transfer = new Zend_File_Transfer_Adapter_Http();
            $transfer->receive();
            $user = Membership::get_current_user();
            $this->launchUploading($transfer->getFileName('uploadedfile'), $user->user_id);
            $this->_helper->layout->disableLayout();
            $this->view->error = false;
        } else {
            $this->view->error = true;
        }
        
        $this->view->form = $form;
        
    }

    public function progressAction()
    {
        $this->_helper->layout->disableLayout();
        $progressBar = new Zend_ProgressBar_Adapter_JsPush();
        $progressBar->setFinishMethodName('Zend_ProgressBar_Finish');
        $upload = null;
        while (!$upload['done']) {
            time_nanosleep(0, 500000000);
            $upload = Zend_File_Transfer_Adapter_Http::getProgress($progressBar);
        }

    }
    
    private function launchUploading($file_path, $user_id) {
        
        $cmd = 'nohup php '.APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'upload_dataset.php ';
        $arguments = ('-u '.$user_id.' ').('-z '.$file_path.' ');
        $cmd .= $arguments;
        $redirect_to = '/tmp/dataset_upload_log';
        $cmd .= ' > /tmp/dataset_upload_log 2>&1 ';
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