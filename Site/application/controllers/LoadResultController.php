<?php 
class LoadResultController extends Zend_Controller_Action
{
	
    private $_model;

    private $_runSeparator = '|';
    private $_logger;


public function preDispatch()
{
	$cUser = Membership::get_current_user();
    $access = $cUser->result_access_flag;
    if($access != 'Y')$this->_helper->redirector('error','error');
}
    
    
    public function __init(){
	
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
            if ($this->_model->getExperimentName()) {
                $this->_model->uploadResults();
            } else {
                $this->_model->setErrorMessage('Unknown experiment specified.');
            }
        }

        $this->view->formAction = '/public/load-result';
        $this->view->errors = $this->_model->getErrors();
        $this->view->messages = $this->_model->getMessages();
        $this->view->warnings = $this->_model->getWarnings();

        $this->view->options = $this->_model->getOptions();
        $this->view->datasetTypes = $this->_model->getDatasetTypes();
        $this->view->experimentTypes = $this->_model->getExperimentTypes();
        $this->view->dataset = $request->getParam('dataset', '');
        $this->view->experiment = $request->getParam('experiment', '');
    }
	
}

?>
