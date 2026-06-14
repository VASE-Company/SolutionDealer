<?php
/**
* Controlador Tasks
*
*/
class Tasks extends CI_Controller {
    
    public function __construct() {
		parent::__construct();
		
	   	$this->output->set_template('default'); 
		$this->output->set_title($this->config->item('applicationName'));	
		$this->form_validation->set_error_delimiters(errorFormatStart(),errorFormatEnd());			
		$this->load->model('companies_model','companies');	
		$this->load->model('tasks_model','tasks');				
   	}
	
	function generateNotifications($key=null) {		
		$this->_doTaskByCompany($key,'generateNotifications');
	}	

	function clearTemporalFiles($key=null) {
		$this->_doTaskByCompany($key,'clearTemporalFiles');
	}

	function clearSessions($key=null) {
		if ($key != $this->config->item('taskKey')) exit;

		$this->tasks->clearSessions();		
	}

	function reassingBudgets($key=null) {
		$this->_doTaskByCompany($key,'reassingBudgets');
	}

	/*
	function updateDatabase($key=null) {
		$this->_doTaskByCompany($key,'updateDatabase');
	}
	*/

	function _doTaskByCompany($key=null,$task="") {
		if ($task == "" || $key != $this->config->item('taskKey')) exit;	

		$companies = $this->companies->getCompanies();
			
		if (isset($companies) && count($companies) > 0) {
			for ($i=0; $i < count($companies); $i++) {
				switch ($task) {
					case 'generateNotifications':						
						$this->tasks->generateNotifications($companies[$i]['id'],$companies[$i]['name']);					
						break;
					
					case 'clearTemporalFiles':
						$this->tasks->clearTemporalFiles($companies[$i]['id']);
						break;

					case 'reassingBudgets':
						$this->tasks->reassingBudgets($companies[$i]['id'],$companies[$i]['name']);
						break;

					/*
					case 'updateDatabase':
						$this->tasks->updateDatabase($companies[$i]['id'],$this->_getSQLQueries());
						break;
					*/
				}				
			}			
		}

		/*
		if ($task == 'updateDatabase') {
			$this->tasks->updateDatabase(0,$this->_getSQLQueries());
		}
		*/
	}

	/*
	function testReassing() {
		$this->reassingBudgets($this->config->item('taskKey'));
	}
	
	function _getSQLQueries() {

		$i=0;
		$queries[$i++] = "update permissions set deleted = 0, active = 1 WHERE module = 'GeneralConfiguration' ";
		return $queries;
	}
	*/
	
}

/* End of file Tasks.php */
/* Location: ./application/controllers/Tasks.php */