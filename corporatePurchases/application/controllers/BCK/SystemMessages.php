<?php
/**
* Controlador SystemMessages
*
*/
class SystemMessages extends CI_Controller {        
    public function __construct() {
		parent::__construct();
		
	   	$this->output->set_template('default'); 
		$this->output->set_title($this->config->item('applicationName'));	
		$this->form_validation->set_error_delimiters(errorFormatStart(),errorFormatEnd());	
		$this->load->model('systemmessages_model','systemMessages');
		$this->load->model('notifications_model','notifications');			
		$this->load->js('assets/js/systemMessages.js');		
		$this->load->js('assets/plugins/summernote/summernote-bs4.min.js');		
		$this->load->js('assets/plugins/summernote/lang/summernote-es-ES.min.js');					
		$this->load->css('assets/plugins/summernote/summernote-bs4.min.css');		
   	}
	
	function index(){		
		$this->listing();		
	}
		
	function _getParameters($page=NULL,							
							$textFilter=NULL,
							$fieldOrder=NULL,$typeOrder=NULL) {

		//By default notGet=false and notPagination=false		
		$data['page'] = array('value'=>$page);
		$data['idFilter'] = array('getField'=>'id','notGet'=>true,'notPagination'=>true,'value'=>NULL);
		$data['textFilter'] = array('getField'=>'text','value'=>$textFilter);		
		$data['fieldOrder'] = array('getField'=>'fOrd','notGet'=>true,'value'=>$fieldOrder);
		$data['typeOrder'] = array('getField'=>'tOrd','notGet'=>true,'value'=>$typeOrder);

		foreach ($data as $key => $values) {
			if (isset($values['getField']) && $values['getField'] != "") { 
				if ($this->input->get($values['getField'],TRUE) != "") {
					$data[$key]['value'] = $this->input->get($values['getField'],TRUE);
				}
			}
		}
		
		$parameters = NULL;
		$filterGet = "";
		$pageParameters = NULL;		
		
		foreach ($data as $key => $values) {
			$parameters[$key] = $values['value'];
			if (isset($values['getField']) && $values['getField'] != "" &&				
		        $values['value'] != NULL && $values['value'] != "") { 
				if (!(isset($values['notGet']) && $values['notGet'] == true)) {
					$filterGet .= "&".$values['getField']."=".$values['value'];	
				}
				if (!(isset($values['notPagination']) && $values['notPagination'] == true)) {
					$pageParameters[$values['getField']] = $values['value'];
				}
			}
		}

		$parametersdData['parameters'] = $parameters;
		$parametersdData['filterGet'] = $filterGet;
		$parametersdData['pageParameters'] = $pageParameters;
		
		return $parametersdData;	
	}
		
	function listing($page=1, 
					 $textFilter=NULL,
					 $fieldOrder='date',$typeOrder='desc')
	{							
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("SystemMessages","See")) {						
			redirect('/main/logout');	
		} 

		$contentData['allowInsert'] = $this->my_application->hasPermission("SystemMessages","Insert");							

		$parametersdData = $this->_getParameters($page,
												 $textFilter,
							                     $fieldOrder, $typeOrder);
										
		$parameters = $parametersdData['parameters'];

		foreach ($parameters as $key => $val) {
			$contentData[$key] = $val;
		}
						
		$contentData['filterGet'] = $parametersdData['filterGet'];
						
		$systemMessages = $this->systemMessages->getSystemMessages($parameters);
		$contentData['systemMessages'] = $systemMessages["list"];		
						
		$pageConfiguration = getPageConfiguration(base_url().'systemMessages/listing',$this->config->item('recordsPerPage'),$systemMessages["totalRecords"],$parametersdData['pageParameters']);
		$this->pagination->initialize($pageConfiguration); 
		$contentData['pagination'] = applyPageStyles($this->pagination->create_links());												
				
		$data['menuActive'] = "SystemMessages";
		$data['title'] = "Mensajes de Sistema";
		$data['contentView'] = 'systemMessages/systemMessages_list_view';
		$data['contentData'] = $contentData;
		$this->my_application->loadGeneralTemplate($data);	
	}

	function search() {			
		$this->form_validation->set_rules('textFilter','Buscar', 'trim|xss_clean');		
		$this->form_validation->set_rules('fieldOrder','Orden Campo', 'trim|xss_clean');
		$this->form_validation->set_rules('typeOrder','Orden Tipo', 'trim|xss_clean');
		
		if ($this->form_validation->run() != FALSE) {		
			$this->listing(1,
			           	   $this->input->post('textFilter',TRUE),
			               $this->input->post('fieldOrder',true),$this->input->post('typeOrder',true)
			           	   );
		} else {
			$this->listing();
		}		
	}
	
	function edit($id=-1, $error='') {
		if ((!$this->my_application->hasPermission("SystemMessages","Insert") && $id <= 0) || 
			!$this->my_application->hasPermission("SystemMessages","See")) {
			redirect('/main/logout');
		} else {
			
			if ($id > 0) {
				$parameters["idFilter"] = $id;
				$systemMessages = $this->systemMessages->getSystemMessages($parameters);
				if ($systemMessages['totalRecords'] == 1){
					$systemMessage = $systemMessages['list'][0];	
				} else {
					$id = -1;
				}
			}
			if ($id <= 0) {
				$systemMessage = $this->systemMessages->getEmptySystemMessage();
			}

			$roles = $this->systemMessages->getRoles($systemMessage['id']);	
			$roles = $roles['list'];												
													
			$contentData['systemMessage'] = $systemMessage;		
			$contentData['roles'] = $roles;								
			$contentData['error'] = $error;			
			$contentData['allowSave'] = ($this->my_application->hasPermission("SystemMessages","Insert") && $id <= 0);																								
			$contentData['callback'] = 'initializeEditSystemMessage()';			

			$data['menuActive'] = "SystemMessages";
			$data['title'] = "Datos del Mensaje de Sistema";
			$data['contentView'] = 'systemMessages/systemMessages_edit_view';
			$data['contentData'] = $contentData;			
			$this->my_application->loadGeneralTemplate($data);
		}
	}
	
	function save() {                             		
		if (!$this->my_application->hasPermission("SystemMessages","Insert") && $this->input->post('id',TRUE) <= 0) {
			redirect('/systemMessages/listing');	
		} else {
			$this->form_validation->set_rules('title','Título', 'trim|required|max_length[50]|xss_clean');															
			$this->form_validation->set_rules('message','Mensaje', 'trim');	
			$this->form_validation->set_rules('notification','Por Sistema', 'trim|xss_clean');	
			$this->form_validation->set_rules('email','Por Mail', 'trim|xss_clean');	
			$this->form_validation->set_rules('roles[]','Roles', 'trim|xss_clean');
						
			if ($this->form_validation->run() != FALSE) {			
				$error = "";
				if ($error == "" && (int)$this->input->post('notification',TRUE) == 0 && (int)$this->input->post('email',TRUE) == 0) {					
					$error = "Debe seleccionar si el mensaje se enviará por notitificación y/o email";					
				}
				$roles = $this->input->post('roles',TRUE);
				if ($error == "" && (!isset($roles) || (isset($roles) && count($roles) <= 0))) {					
					$error = "Debe seleccionar al menos un rol";					
				}
				if ($error != "") {
					$this->edit($this->input->post('id',TRUE),$error);
				} else {
					$data = array('id'=>$this->input->post('id',TRUE),									  							  						  
								  'title'=>$this->input->post('title',TRUE),		
								  'message'=>$this->input->post('message',FALSE),
								  'notification'=>$this->input->post('notification',TRUE),						  
								  'email'=>$this->input->post('email',TRUE),
								  'roles'=>$this->input->post('roles',TRUE));

					if ($this->systemMessages->setSystemMessage($data)) {						
						redirect('/systemMessages/listing');	
					} else {	
						$error = "No se pudo grabar los datos, intente nuevamente";
						$this->edit($this->input->post('id',TRUE),$error);
					}	
				}			
			} else {
				$this->edit($this->input->post('id',TRUE));		
			}
		}
	}	

	function _validateType(){		
		if ((int)$this->input->post('notification',TRUE) == 0 && (int)$this->input->post('email',TRUE) == 0) {
			$this->form_validation->set_message('_validateType', 'Debe seleccionar si se enviará por notitificación o email.');
			return false;
		} else {
			return true;
		}
	}

	function popup($id=-1) {		
		if (!$this->session->userdata('userLoggedIn') || $id <= 0) exit;
			
		$parameters["idFilter"] = $id;
		$systemMessages = $this->systemMessages->getSystemMessages($parameters);
		if ($systemMessages['totalRecords'] != 1) exit;		
		$systemMessage = $systemMessages['list'][0];	
		
		$notificationsParameters = array('entityFilter'=>'systemMessage',
			                             'entityIdFilter'=>$id);	
		$this->notifications->setNotificationRead($notificationsParameters);				
		
		$contentData['data'] = $systemMessage['message'];	
		$contentData['callback'] = "intializePopupSystemMessage('".$systemMessage['title']."')";						
		$contentData['byAjax'] = true;					
		$view = $this->load->view("general_data_view",$contentData,true);																		
		
		$this->output->set_output($view); 	
	}
		
}

/* End of file SystemMessages.php */
/* Location: ./application/controllers/SystemMessages.php */