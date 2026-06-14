<?php
/**
* Controlador Utils
*
*/
class Utils extends CI_Controller {
    
    public function __construct() {
		parent::__construct();
		
	   	$this->output->set_template('default'); 
		$this->output->set_title($this->config->item('applicationName'));	
		$this->form_validation->set_error_delimiters(errorFormatStart(),errorFormatEnd());									
   	}
	
	function index(){		
		
	}

	function backup() {		
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("Backup","Generate")) exit;											

		//$this->db = $this->load->database('another_db_name', TRUE);
		$this->load->dbutil();

		$filename = 'backup_'.getCurrentDateId();		
		
		$prefs = array('format' => 'zip', 'filename' => $filename, 'foreign_key_checks' => FALSE);		
		$backup = $this->dbutil->backup($prefs);
		
		$this->load->helper('download');
		force_download($filename.".zip", $backup); 
	}	
}

/* End of file Utils.php */
/* Location: ./application/controllers/Utils.php */