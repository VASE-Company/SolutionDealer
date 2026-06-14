<?php
/**
* Controlador Roles
*
*/
class Roles extends CI_Controller {
    
    public function __construct() {
		parent::__construct();
		
	   	$this->output->set_template('default'); 
		$this->output->set_title($this->config->item('applicationName'));	
		$this->form_validation->set_error_delimiters(errorFormatStart(),errorFormatEnd());	
		$this->load->model('roles_model','roles');	
		$this->load->js('assets/js/roles.js');	
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
					 $fieldOrder='des',$typeOrder='asc')
	{							
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("Roles","See")) {						
			redirect('/main/logout');	
		} 

		$contentData['allowInsert'] = $this->my_application->hasPermission("Roles","Insert");			
		$contentData['allowEdit'] = $this->my_application->hasPermission("Roles","Edit");
		$contentData['allowDelete'] = $this->my_application->hasPermission("Roles","Delete");		

		$parametersdData = $this->_getParameters($page,
												 $textFilter,
							                     $fieldOrder, $typeOrder);
										
		$parameters = $parametersdData['parameters'];

		foreach ($parameters as $key => $val) {
			$contentData[$key] = $val;
		}
						
		$contentData['filterGet'] = $parametersdData['filterGet'];
						
		$roles = $this->roles->getRoles($parameters);
		$contentData['roles'] = $roles["list"];			
						
		$pageConfiguration = getPageConfiguration(base_url().'roles/listing',$this->config->item('recordsPerPage'),$roles["totalRecords"],$parametersdData['pageParameters']);
		$this->pagination->initialize($pageConfiguration); 
		$contentData['pagination'] = applyPageStyles($this->pagination->create_links());												
				
		$data['menuActive'] = "Roles";
		$data['title'] = "Roles";
		$data['contentView'] = 'roles/roles_list_view';
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
		if ((!$this->my_application->hasPermission("Roles","Insert") && $id <= 0) || 
			!$this->my_application->hasPermission("Roles","See")) {
			redirect('/main/logout');	
		} else {
			
			$contentData['allowSave'] = (($this->my_application->hasPermission("Roles","Insert") && $id <= 0) || ($this->my_application->hasPermission("Roles","Edit") && $id > 0));

			if ($id > 0) {
				$parameters["idFilter"] = $id;
				$roles = $this->roles->getRoles($parameters);
				if ($roles['totalRecords'] == 1){
					$role = $roles['list'][0];	
				} else {
					$id = -1;
				}
			}
			if ($id <= 0) {
				$role = $this->roles->getEmptyRole();
			}
			$permissions = $this->roles->getPermissions($role['id']); 										
			$superiorRolesParameters["excludeIdFilter"] = $role['id'];
			$superiorRoles = $this->roles->getRoles($superiorRolesParameters);
										
			$contentData['role'] = $role;	
			$contentData['permissions'] = $permissions['list'];				
			$contentData['superiorRoles'] = $superiorRoles['list'];	
			$contentData['error'] = $error;									

			$data['menuActive'] = "Roles";
			$data['title'] = "Datos del Rol";
			$data['contentView'] = 'roles/roles_edit_view';
			$data['contentData'] = $contentData;
			$this->my_application->loadGeneralTemplate($data);
		}
	}

	function save() {                             		
		if ((!$this->my_application->hasPermission("Roles","Edit") && $this->input->post('id',TRUE) > 0) || 
		    (!$this->my_application->hasPermission("Roles","Insert") && $this->input->post('id',TRUE) <= 0)) {
			redirect('/roles/listing');	
		} else {
			$this->form_validation->set_rules('description','Descripcion', 'trim|required|max_length[50]|xss_clean|callback__validateExistsValue[description]');					
			$this->form_validation->set_rules('superiorRoleId','Perfil Superior', "trim|required|xss_clean|callback__validateSuperiorRole");
			$this->form_validation->set_rules('permissions[]','Permisos', 'trim|xss_clean');				
			
			if ($this->form_validation->run() != FALSE) {																						
				$data = array('id'=>$this->input->post('id',TRUE),									  
							  'description'=>$this->input->post('description',TRUE),
							  'superiorRoleId'=>$this->input->post('superiorRoleId',TRUE),
							  'permissions'=>$this->input->post('permissions',TRUE));
								
				if ($this->roles->setRole($data)) {						
					redirect('/roles/listing');	
				} else {	
					$error = "No se pudo grabar los datos, intente nuevamente";
					$this->edit($this->input->post('id',TRUE),$error);
				}				
			} else {
				$this->edit($this->input->post('id',TRUE));		
			}
		}
	}	
	
	function _validateExistsValue($value='', $field='') {		
		if ($value != '' && $field != '') {
			$roleId = $this->input->post('id',TRUE);
			if ($this->roles->existsValue($value,$roleId,$field)) {
				$this->form_validation->set_message('_validateExistsValue', 'El valor ya existe.');
				return FALSE;		  
			} else {
				return TRUE;
			}
		} else {
			return TRUE;
		}
	}
	
	function _validateSuperiorRole($superiorRoleId=0) {		
		if ($superiorRoleId > 0) {
			$roleId = $this->input->post('id',TRUE);
			if (!$this->roles->isSuperiorRoleValid($roleId,$superiorRoleId)) {
				$this->form_validation->set_message('_validateSuperiorRole', 'El Nivel Superior no es válido.');
				return FALSE;		  
			} else {
				return TRUE;
			}
		} else {
			return TRUE;
		}
	}

	function delete($roleId=-1) {
		$error = "";
		if ($this->session->userdata('userLoggedIn') && $this->my_application->hasPermission("Roles","Delete")) {					
			if ($this->roles->deleteRoleValid($roleId)) {						
				if (!$this->roles->deleteRole($roleId)) {						
					$error = "No se pudo eliminar el registro, intente nuevamente.";					
				}	
			} else {				
				$error = "El registro no se puede eliminar porque existen datos relacionados a él.";
			}			
		} else {
			$error = "No posee permisos para eliminar el registro.";
		}

		if ($error != "") $error = "err/#/".$error;
		
		$contentData['data'] = $error;
		$contentData['byAjax'] = true;	
		$view = $this->load->view("general_data_view",$contentData,true);			
		$this->output->set_output($view);			
	}	
		
}

/* End of file Roles.php */
/* Location: ./application/controllers/Roles.php */