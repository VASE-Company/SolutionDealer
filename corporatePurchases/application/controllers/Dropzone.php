<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Dropzone extends CI_Controller {
  
	 public function __construct() {
	    parent::__construct();
	    $this->load->helper(array('url','html','form')); 
	    $this->output->set_template('default'); 
		$this->output->set_title($this->config->item('applicationName'));	
		$this->form_validation->set_error_delimiters(errorFormatStart(),errorFormatEnd());			
	 }
	 public function index() { 	
	 	$contentData['byAjax'] = true;	

		$view = $this->load->view("dropzone/dropzone_view",$contentData,true);
				
		$this->output->set_output($view); 
	 }
	 
	 public function upload() {
		 if (!empty($_FILES)) {
			$tempFile = $_FILES['file']['tmp_name'];
			$fileName = $_FILES['file']['name'];
			 
			$targetPath = getcwd() . '/files/'.$this->session->userdata('companyId')."/tmp/";			
			$partsPath = pathinfo($_FILES["file"]["name"]);			
			$targetFile = md5(uniqid()).".".$partsPath['extension'];
			 
			move_uploaded_file($tempFile, $targetPath.$targetFile);		
	    } else {
	    	$targetFile = "";
	    }

	    $contentData['data'] = $targetFile;

		$contentData['byAjax'] = true;	
					
		$view = $this->load->view("general_data_view",$contentData,true);
		
		$this->output->set_output($view); 
	}

	public function delete() {
		$filename = $this->input->post('filename',true);
		
		if ($filename != "") {
			$filePath = $this->config->item('files').$this->session->userdata('companyId')."/tmp/".$filename;

			if (file_exists($filePath)) {		
				@unlink($filePath);
			}
		}
	}

	public function download() {

		$filename = desanitizeGet($this->input->get('filename'));
		$serverFilename = desanitizeGet($this->input->get('serverFilename'));

		if ($filename != "" && $serverFilename != "") {
			$filePath = $this->config->item('files').$this->session->userdata('companyId')."/tmp/".$serverFilename;

			if (file_exists($filePath)) {

				$this->load->helper('download');
						
				$fileContent = file_get_contents($filePath);							
						
				force_download($filename,$fileContent);
			} else {
				echo "No se encontró el archivo.";
			}	
		} else {
			echo "No se encontró el archivo.";
		}			
	}

}
/* End of file dropzone.js */
/* Location: ./application/controllers/dropzone.php */