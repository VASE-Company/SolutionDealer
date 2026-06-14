<?php
/**
* Controlador Main
*
*/
class Main extends CI_Controller {
    
    public function __construct() {
		parent::__construct();
		
	   	$this->output->set_template('default'); 
		$this->output->set_title($this->config->item('applicationName'));	
		$this->form_validation->set_error_delimiters(errorFormatStart(),errorFormatEnd());							
		$this->load->js('assets/js/main.js');
		$this->load->css('assets/css/stylesLoginMyApp.css');
   	}
	
	function index(){		
		$this->_main();						
	}
	
	function _main($response=null){							

		$criticalNotice = "";									
		
		if ($this->config->item('suspendedSystem')) {
			$criticalNotice = "El sistema se encuentra fuera de servicio por tareas de mantenimiento. Disculpe las molestias.";			
		} 
		
		if ($criticalNotice != "") {			
			$this->_cleanSession();

			$this->_notice($criticalNotice);			
		} else {	

			if (!$this->session->userdata('userLoggedIn')) {						 																
				$data['username'] = $this->input->get('username',true);						
				$data['logo'] = $this->config->item('images')."logoLogin.jpg";
				$data['messageCode'] = (isset($response)?$response['code']:"");
				$data['message'] = (isset($response)?$response['description']:"");
				$data['backgroundClass'] = "login-background";
				$this->load->section('body', 'login/login_view',$data);				
				$this->load->view('templates/template_only_body');
			} else {		
				redirect('/panel');
			}				
		}
	}		

	function _notice($message='') {
		$data['message'] = $message;
		$this->load->section('body', 'login/login_message_view',$data);				
		$this->load->view('templates/template_only_body');
	}	

	function login() {  		
		if ($this->config->item('suspendedSystem')) {						
			$this->_main();	
		} else {    
	                     
			$this->form_validation->set_rules('username','Usuario','trim|required|xss_clean');
			$this->form_validation->set_rules('password','Clave','trim|required|xss_clean');		
			if ($this->form_validation->run() != FALSE) {					
				$this->load->model('users_model','users');															
				
				$user = $this->users->getLogin($this->input->post('username',true),$this->input->post('password',true));																		
				
				if (isset($user)) {									
					$sessionData = array('userId'=>$user['id'],                    										 
										 'userName'=>$user['name'],                
										 'userLogin'=>$this->input->post('username',true),      
										 'userLoggedIn'=>TRUE,
										 'userRolesId'=>$user['rolesId'],
										 'userCompanyId'=>$user['companyId'],
										 'userCompanyDescription'=>$user['companyDescription'],
										 'userBranchOfficeId'=>$user['branchOfficeId'],
										 'userBranchOfficeDescription'=>$user['branchOfficeDescription'],
										 'userSectorId'=>$user['sectorId']);

					$this->session->set_userdata($sessionData);						

					$this->_main();	
						
				} else {	
					$response['code'] = "ERROR";
					$response['description'] = "Usuario o clave incorrecta";
					
					$this->_main($response);	
				 }             						
			} else {
				$response = null;
				if ($response == null && form_error('username') != "") {					
					$response['code'] = "ERROR";
					$response['description'] = form_error('username');
				}
				if ($response == null && form_error('password') != "") {
					$response['code'] = "ERROR";
					$response['description'] = form_error('password');
				}				
			
				$this->_main($response);	
			}
		}
	}	
	
	function logout() {
		$this->_cleanSession();
		           
		redirect('/main');  
	}
				
	function _cleanSession() {				
		$this->session->unset_userdata('userId');
		$this->session->unset_userdata('userName');
		$this->session->unset_userdata('userLogin');		
		$this->session->unset_userdata('userLoggedIn');
		$this->session->unset_userdata('userRolesId');	
		$this->session->unset_userdata('userCompanyId');	
		$this->session->unset_userdata('userCompanyDescription');			
		$this->session->unset_userdata('userBranchOfficeId');	
		$this->session->unset_userdata('userBranchOfficeDescription');			
		$this->session->unset_userdata('userSectorId');	
	}		

	function keepAlive() {
		$contentData['data'] = date('d-m-Y H:i:s');							
		$contentData['byAjax'] = true;					
		$view = $this->load->view("general_data_view",$contentData,true);			
		$this->output->set_output($view);		
	}
	
	function updateNotifications() {		

		$notificationsData = $this->my_application->getMainHeaderNotificationsData();

	   	$contentData['notifications'] = $notificationsData['notifications']; 
	   	$contentData['countOfNotifications'] = $notificationsData['countOfNotifications']; 						
		$contentData['byAjax'] = true;					
		$view = $this->load->view("main/main_header_notifications_view",$contentData,true);			
		$this->output->set_output($view);
	}

	function forgotPassword($response=null) {
		if ($this->session->userdata('userLoggedIn')) {										
			$this->_main();	
		} else {				
			
			$data['logo'] = $this->config->item('images')."logoLogin.jpg";			
			$data['backgroundClass'] = "login-background";
			
			$data['messageCode'] = (isset($response)?$response['code']:"");
			$data['message'] = (isset($response)?$response['description']:"");
			
			$this->load->section('body', 'login/forgot_password_view',$data);				
			$this->load->view('templates/template_only_body');			
		}
	}

	function recoverPassword() {
		if ($this->session->userdata('userLoggedIn')) {										
			$this->_main();	
		} else {
			$this->form_validation->set_rules('username','Usuario','trim|required|xss_clean');				
			if ($this->form_validation->run() != FALSE) {												

				$this->load->model('users_model','users');	

				$response = $this->users->recoverPassword($this->input->post('username',true));

				$this->forgotPassword($response);		           						
			} else {
				$response = null;
				if ($error == "" && form_error('username') != "") {
					$response['code'] = "ERROR";
					$response['description'] = form_error('username');
				}

				$this->forgotPassword($response);	
			}												
		}
	}

	function reset($key="",$response=null) {
		if ($this->session->userdata('userLoggedIn')) {										
			$this->_main();	
		} else {

			$this->_cleanSession();				

			$this->load->model('users_model','users');	

			$user = $this->users->getUserByKeyRecoverPassword($key);						

			if (!isset($user)) {												
				$response['code'] = "ERROR";
				$response['description'] = "El link ya no es válido";

				$this->forgotPassword($response);
			} else {	
						
				$data['logo'] = $this->config->item('images')."logoLogin.jpg";		
				$data['backgroundClass'] = "login-background";

				$data['key'] = $key;									
				$data['user'] = $user;						
				$data['messageCode'] = (isset($response)?$response['code']:"");
				$data['message'] = (isset($response)?$response['description']:"");										
								
				$this->load->section('body', 'login/reset_password_view',$data);				
				$this->load->view('templates/template_only_body');	
				
			}															
		}
	}

	function saveReset() {                             		
		if ($this->session->userdata('userLoggedIn')) {										
			$this->_main();	
		} else {												
			$this->form_validation->set_rules('newPassword','Nueva Clave','trim|required|min_length[6]|max_length[15]|xss_clean|callback__validatePassword');		
			$this->form_validation->set_rules('confirmationPassword','Confirmación','trim|required|matches[newPassword]|xss_clean');						
			$this->form_validation->set_rules('key','Key', 'trim|xss_clean');					
			
			if ($this->form_validation->run() != FALSE) {																		

				$this->load->model('users_model','users');	

				if ($this->users->setResetPassword($this->input->post('key',true),$this->input->post('newPassword',true))) {														
					$response['code'] = "OK";
					$response['description'] = "Los datos se actualizaron correctamente";

					$this->_main($response);
				} else {								
					$response['code'] = "ERROR";
					$response['description'] = "No se pudo grabar los datos, intente nuevamente";	

					$this->reset($this->input->post('key',true),$response);								
				}      
			} else {
				$response = null;
				if (!isset($response) && form_error('newPassword') != "") {
					$response['code'] = "ERROR";
					$response['description'] = form_error('newPassword');
				}
				if (!isset($response) && form_error('confirmationPassword') != "") {
					$response['code'] = "ERROR";
					$response['description'] = form_error('confirmationPassword');
				}

				$this->reset($this->input->post('key',true),$response);		
			}
		}
	}	

	function _validatePassword($password='') {		
		$positionSpace = strpos($password," ");
		$positionQuote = strpos($password,"'");
		if (!($positionSpace === false) || !($positionQuote === false)) {
			$this->form_validation->set_message('_validatePassword', 'La clave no puede contener espacios ni comillas.');
			return FALSE;	
		}
	
		$validPassword = true;	     	   
		if (!preg_match('`[a-z]`',$password)){
			$validPassword = false;
		}
		if (!preg_match('`[A-Z]`',$password)){
			$validPassword = false;
		}
		if (!preg_match('`[0-9]`',$password)){
			$validPassword = false;
		}
		if ($validPassword == false) {
			$this->form_validation->set_message('_validatePassword', 'La clave debe contener letras mayúsculas, minúsculas y números.');
			return FALSE;		
		} else {
			return TRUE;
		}
	}	
}

/* End of file Main.php */
/* Location: ./application/controllers/Main.php */