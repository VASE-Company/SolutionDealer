<?php
/**
* Controlador Users
*
*/

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Users extends CI_Controller {
    
    public function __construct() {
		parent::__construct();
		
	   	$this->output->set_template('default'); 
		$this->output->set_title($this->config->item('applicationName'));	
		$this->form_validation->set_error_delimiters(errorFormatStart(),errorFormatEnd());		
		$this->load->model('users_model','users');				
		$this->load->model('companies_model','companies');	
		$this->load->model('branchoffices_model','branchOffices');	
		$this->load->model('sectors_model','sectors');	
		$this->load->model('roles_model','roles');	
		$this->load->js('assets/js/users.js');						
   	}
	
	function index(){		
		$this->listing();		
	}
		
	function _getParameters($page=NULL,							
							$textFilter=NULL,$roleIdFilter=NULL,							
							$fieldOrder=NULL,$typeOrder=NULL) {

		//By default notGet=false and notPagination=false		
		$data['page'] = array('value'=>$page);
		$data['idFilter'] = array('getField'=>'id','notPagination'=>true,'value'=>NULL);
		$data['textFilter'] = array('getField'=>'text','value'=>$textFilter);			
		$data['rolesIdFilter'] = array('getField'=>'role','value'=>$roleIdFilter);			
		$data['fieldOrder'] = array('getField'=>'fOrd','type'=>'order','value'=>$fieldOrder);
		$data['typeOrder'] = array('getField'=>'tOrd','type'=>'order','value'=>$typeOrder);

		foreach ($data as $key => $values) {
			if (isset($values['getField']) && $values['getField'] != "") { 
				if ($this->input->get($values['getField'],TRUE) != "") {
					$data[$key]['value'] = $this->input->get($values['getField'],TRUE);
				}
			}
		}
		
		$parameters = NULL;
		$filterGet = "";
		$orderGet = "";
		$pageParameters = NULL;		
		
		foreach ($data as $key => $values) {
			$parameters[$key] = $values['value'];
			if (isset($values['getField']) && $values['getField'] != "" &&				
		        $values['value'] != NULL && $values['value'] != "") { 
				$type = (isset($values['type']) && $values['type'] != ""?$values['type']:"");
				switch ($type) {
					case 'order':
						$orderGet .= "&".$values['getField']."=".$values['value'];	
						break;
					
					default:
						$filterGet .= "&".$values['getField']."=".$values['value'];	
						break;
				}							
				if (!(isset($values['notPagination']) && $values['notPagination'] == true)) {
					$pageParameters[$values['getField']] = $values['value'];
				}
			}
		}

		$parametersdData['parameters'] = $parameters;
		$parametersdData['filterGet'] = $filterGet;
		$parametersdData['orderGet'] = $orderGet;
		$parametersdData['pageParameters'] = $pageParameters;
		
		return $parametersdData;	
	}
		
	function listing($page=1, 
					 $textFilter=NULL,$roleIdFilter=NULL,
					 $fieldOrder='ln',$typeOrder='asc')
	{							
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("Users","See")) {						
			redirect('/main/logout');	
		} 

		$contentData['allowInsert'] = $this->my_application->hasPermission("Users","Insert");			
		$contentData['allowEdit'] = $this->my_application->hasPermission("Users","Edit");
		$contentData['allowDelete'] = $this->my_application->hasPermission("Users","Delete");	
		$contentData['allowExport'] = $this->my_application->hasPermission("Users","Export");				
	

		$parametersdData = $this->_getParameters($page,
												 $textFilter, $roleIdFilter,
							                     $fieldOrder, $typeOrder);
										
		$parameters = $parametersdData['parameters'];

		foreach ($parameters as $key => $val) {
			$contentData[$key] = $val;
		}
						
		$contentData['filterGet'] = $parametersdData['filterGet'];
		$contentData['orderGet'] = $parametersdData['orderGet'];
						
		$users = $this->users->getUsers($parameters);
		$contentData['users'] = $users["list"];			

		$roles = $this->roles->getRoles();
		$contentData['roles'] = $roles["list"];	
						
		$pageConfiguration = getPageConfiguration(base_url().'users/listing',$this->config->item('recordsPerPage'),$users["totalRecords"],$parametersdData['pageParameters']);
		$this->pagination->initialize($pageConfiguration); 
		$contentData['pagination'] = applyPageStyles($this->pagination->create_links());												
				
		$data['menuActive'] = "Users";
		$data['title'] = "Usuarios";
		$data['contentView'] = 'users/users_list_view';
		$data['contentData'] = $contentData;
		$this->my_application->loadGeneralTemplate($data);	
	}
	
	function search() {			
		$this->form_validation->set_rules('textFilter','Buscar', 'trim|xss_clean');		
		$this->form_validation->set_rules('rolesIdFilter','Rol', 'trim|xss_clean');	
		$this->form_validation->set_rules('fieldOrder','Orden Campo', 'trim|xss_clean');
		$this->form_validation->set_rules('typeOrder','Orden Tipo', 'trim|xss_clean');
		
		if ($this->form_validation->run() != FALSE) {		
			$this->listing(1,
			           	   $this->input->post('textFilter',TRUE),$this->input->post('rolesIdFilter',TRUE),
			               $this->input->post('fieldOrder',true),$this->input->post('typeOrder',true)
			           	   );
		} else {
			$this->listing();
		}		
	}	

	function _getParametersDescription($parameters=null) {
		
		$parametersDescription = array();			
	
		if (isset($parameters['textFilter']) && $parameters['textFilter'] != "") {	
			$parametersDescription[count($parametersDescription)] = array('label'=>'Buscar:','data'=>$parameters['textFilter']);					
		}
		if (isset($parameters['rolesIdFilter']) && $parameters['rolesIdFilter'] != "") {				
			$rolesParameters['idFilter'] = $parameters['rolesIdFilter'];				
			$roles = $this->roles->getRoles($rolesParameters);
			if ($roles["totalRecords"] == 1) {
				$rol = $roles["list"][0];
				$parametersDescription[count($parametersDescription)] = array('label'=>'Rol:','data'=>$rol['description']);						
			}
		}																																																

		return $parametersDescription;
	}

	function export() {				
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("Users","Export")) exit;											

		$parametersdData = $this->_getParameters();										
		$parameters = $parametersdData['parameters'];
		$users = $this->users->getUsers($parameters);
		$users = $users["list"];

		$parametersDescription = $this->_getParametersDescription($parameters);

		// INITIALITE SPREADSHEET
				
		$spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Usuarios');
		
        // TITLE
        $row = 1;
        $sheet->setCellValue('A'.$row,"LISTADO DE USUARIOS"); 
        $styleCell = array('font'=>array('bold'=>true,'size'=>16));
        $sheet->getStyle('A'.$row)->applyFromArray($styleCell);
        $sheet->mergeCells('A'.$row.':'.'E'.$row);

        // PARAMETERS        
        $styleCell = array('font'=>array('bold'=>true));
        for ($i=0; $i < count($parametersDescription); $i++) {
        	$row++;
        	$sheet->setCellValue('A'.$row,$parametersDescription[$i]['label']); 
        	$sheet->setCellValue('B'.$row,$parametersDescription[$i]['data']);
        	$sheet->getStyle('A'.$row)->applyFromArray($styleCell);
        	$sheet->mergeCells('B'.$row.':'.'E'.$row);
        }

        // GRID HEADERS        
        $col = 0;
        $row++;        

        $row++;
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Apellido"); 
        $col++;
		$sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Nombres"); 
		$col++;
		$sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Usuario"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Empresa");    
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Sucursal"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Sector"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Activo");              
  
		$styleCell = array(
				            'fill' => array(
				                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				                'startColor' => array('argb' => '00000000')
				            	),
				            'font' => array(
				            	'bold'=>true,
				            	'color' => array('argb' => 'FFFFFFFF')
				            	)
					        );

		$sheet->getStyle('A'.$row.":".getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);		

		// GRID BODY

		for ($i=0; $i < count($users); $i++) {
			$row++;
	        $col = 0;
	        	        
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$users[$i]['lastName']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$users[$i]['firstName']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$users[$i]['username']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$users[$i]['companyDescription']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$users[$i]['branchOfficeDescription']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$users[$i]['sectorDescription']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,getBooleanToText($users[$i]['active'])); 	       	        
		}		

		for ($i=1; $i <= $col; $i++) {
			$sheet->getColumnDimension(getLetterOfExcelColumn($i))->setAutoSize(true);
		}

		// CREATE TEMP FILE EXCEL, DOWNLOAD Y DELETE 

        $writer = new Xlsx($spreadsheet); 

		$folder = $this->config->item('files').'tmp/';
		 
		$filename = 'usuarios_'.getCurrentDateId().'.xlsx';
		 
		$writer->save($folder.$filename); 

		$this->load->helper('download');
							
		$fileContent = file_get_contents($folder.$filename);	

		@unlink($folder.$filename);				
							
		force_download($filename,$fileContent);
	}	
	
	function myProfile($error='',$message='') {
		if (!$this->my_application->hasPermission("MyProfile","See") && !$this->my_application->hasPermission("MyProfile","Edit")) {
			redirect('/main');				
		} else {			
			$parameters['fromModule'] = "MyProfile";
			$users = $this->users->getUsers($parameters);
			if ($users['totalRecords'] == 1){
				$user = $users['list'][0];	
				$user['image'] = $this->my_application->imageOfUser($user['id']);
			} else {
				redirect('/main');					
			}			

			$contentData['allowSave'] = $this->my_application->hasPermission("MyProfile","Edit");																		
			$contentData['allowEditBranchOffice'] = $this->my_application->hasPermission("MyProfile","EditBranchOffice");						
			$contentData['allowEditSector'] = $this->my_application->hasPermission("MyProfile","EditSector");			
			
			if ($contentData['allowEditBranchOffice']) {
				$branchOfficesParameters["activeOrIdFilter"] = true;
				$branchOfficesParameters["idFilter"] = $user['branchOfficeId'];
				$branchOfficesParameters["companyIdFilter"] = $user['companyId'];
				$branchOffices = $this->branchOffices->getBranchOffices($branchOfficesParameters);	
				$contentData['branchOffices'] = $branchOffices['list'];	
			}

			if ($contentData['allowEditSector']) {
				$sectorsParameters["activeOrIdFilter"] = true;
				$sectorsParameters["idFilter"] = $user['sectorId'];
				$sectorsParameters["branchOfficeIdFilter"] = $user['branchOfficeId'];
				$sectors = $this->sectors->getSectors($sectorsParameters);	
				$contentData['sectors'] = $sectors['list'];	
			}
									
			$contentData['user'] = $user;						
			$contentData['error'] = $error;					
			$contentData['message'] = $message;		
			$contentData['callback'] = "initializeEditMyProfile();";									

			$data['menuActive'] = "";
			$data['title'] = "Mi Perfil";
			$data['contentView'] = 'users/users_my_profile_view';
			$data['contentData'] = $contentData;			
			$this->my_application->loadGeneralTemplate($data);
		}
	}

	function saveMyProfile() {                             		
		if (!$this->my_application->hasPermission("MyProfile","Edit")) {	
			redirect('/main');	
		} else {			
			$allowEditBranchOffice = $this->my_application->hasPermission("MyProfile","EditBranchOffice");
			$allowEditSector = $this->my_application->hasPermission("MyProfile","EditSector");	
			
			if ($this->input->post('newPassword',true) != "" || $this->input->post('confirmationPassword',true) != "") {
				$this->form_validation->set_rules('newPassword','Nueva Password','trim|required|min_length[6]|max_length[15]|xss_clean|callback__validatePassword');		
				$this->form_validation->set_rules('confirmationPassword','Conf. Password','trim|required|matches[newPassword]|xss_clean');						
			} else {
				$this->form_validation->set_rules('newPassword','Nueva Password', 'trim|xss_clean');		
				$this->form_validation->set_rules('confirmationPassword','Conf. Password','trim|xss_clean');		
			}	
			if ($allowEditBranchOffice) {
				$this->form_validation->set_rules('branchOfficeId','Sucursal', 'trim|required|xss_clean');			
			}
			if ($allowEditSector) {
				$this->form_validation->set_rules('sectorId','Sector', 'trim|xss_clean');			
			}	
			$this->form_validation->set_rules('cellphone','Celular','trim|max_length[15]|xss_clean');				

			if ($this->form_validation->run() != FALSE) {														
				$data = array('id'=>$this->session->userdata('userId',true),									   									   
							  'password'=>$this->input->post('newPassword',true),							  
							  'branchOfficeId'=>$this->input->post('branchOfficeId',true),							  
							  'sectorId'=>$this->input->post('sectorId',true),
							  'cellphone'=>$this->input->post('cellphone',true));								
				if ($this->users->setMyProfile($data)) {																

					if ($this->_saveUserImage($this->session->userdata('userId',true))) {
						$this->myProfile(NULL,"Los datos se grabaron correctamente");					
					} else {
						$error = "No se pudo subir la imagen, intente nuevamente";
						$this->myProfile($error);	
					}

				} else {	
					$error = "No se pudo grabar los datos, intente nuevamente";
					$this->myProfile($error);
				}      
			} else {
				$this->myProfile();		
			}
		}
	}			

	function _saveUserImage($userId=-1) {				
		
		if ($userId <= 0) {
			return false;
		}

		if (isset($_FILES["fileUserImage"]) && trim($_FILES["fileUserImage"]['name']) != "") {	

			$this->_deleteUserImage($userId);

			$file = "user_".$userId.".".strtolower(pathinfo($_FILES["fileUserImage"]['name'], PATHINFO_EXTENSION));	

			$configUpload['file_name'] = $file;				
			$configUpload['upload_path'] = $this->config->item('images')."users/";			
			$configUpload['allowed_types'] = join("|",$this->config->item('extensionsOfImageUser'));			
			$configUpload['max_size']	= '5120';
			$configUpload['overwrite']  = true;											
			
			$this->load->library('upload', $configUpload);
			if ($this->upload->do_upload("fileUserImage")) {			

				$this->load->library('image_lib');

				$configResize['image_library'] = 'gd2';
				$configResize['source_image'] = $this->config->item('images')."users/".$file;
				$configResize['new_image'] = $this->config->item('images')."users/".$file;
				$configResize['maintain_ratio'] = TRUE;
				$configResize['create_thumb'] = FALSE;
				$configResize['width'] = 500;
				$configResize['height'] = 500;

				$this->image_lib->initialize($configResize);
				$this->image_lib->resize();

				return true;
			}
			else
			{
				return false;			
			}					  
		} else {
			if ((int)$this->input->post('deleteUsrImage',true) == 1) {
				$this->_deleteUserImage($userId);
			}
			
			return true;
		}
	}		

	function _deleteUserImage($userId=-1) {
		if ($userId > 0) {
			$extensions = $this->config->item('extensionsOfImageUser');			
			for ($i=0; $i < count($extensions); $i++) {			
			   	$file = $this->config->item('images')."users/user_".$userId.".".$extensions[$i];
			   	if (file_exists($file)) {
			   		@unlink($file);		   	
			   	}			
			}
		}
	}

	function edit($id=-1, $error='') {					
		if ((!$this->my_application->hasPermission("Users","Insert") && $id <= 0) || 
			 !$this->my_application->hasPermission("Users","See")) {
			redirect('/main/logout');	
		} else {						
			
			if ($id > 0) {
				$parameters["idFilter"] = $id;
				$users = $this->users->getUsers($parameters);
				if ($users['totalRecords'] == 1){
					$user = $users['list'][0];									
				} else {
					$id = -1;
				}
			}
			if ($id <= 0) {
				$user = $this->users->getEmptyUser();
			}

			$user['image'] = $this->my_application->imageOfUser($user['id']);
						
			$contentData['allowModifyRole'] = ($this->my_application->hasPermission("Users","ModifyRole") && $user['id'] != $this->session->userdata('userId'));
			if ($user['id'] == $this->config->item('superUserId')) {
				$roles[0] = array('id'=>-999,'description'=>'Super Administrador','active'=>1);
				$contentData['allowModifyRole'] = false;
			} else {
				$roles = $this->users->getRoles($user['id']); 	
				$roles = $roles['list'];
			}	

			if ($user['id'] <= 0) {
				$companiesParameters["activeFilter"] = 1;				
				$companies = $this->companies->getCompanies($companiesParameters);	
				$companies = $companies['list'];			
			} else {
				$companies = null;
			}

			if ($user['companyId'] > 0) {
				$branchOfficesParameters["activeOrIdFilter"] = true;
				$branchOfficesParameters["idFilter"] = $user['branchOfficeId'];
				$branchOfficesParameters["companyIdFilter"] = $user['companyId'];
				$branchOffices = $this->branchOffices->getBranchOffices($branchOfficesParameters);	
				$branchOffices = $branchOffices['list'];	
			} else {
				$branchOffices = null;
			}

			if ($user['branchOfficeId'] > 0) {
				$sectorsParameters["activeOrIdFilter"] = true;
				$sectorsParameters["idFilter"] = $user['sectorId'];
				$sectorsParameters["branchOfficeIdFilter"] = $user['branchOfficeId'];
				$sectors = $this->sectors->getSectors($sectorsParameters);	
				$sectors = $sectors['list'];	
			} else {
				$sectors = null;
			}

			$contentData['user'] = $user;	
			$contentData['roles'] = $roles;	
			$contentData['companies'] = $companies;
			$contentData['branchOffices'] = $branchOffices;	
			$contentData['sectors'] = $sectors;	
			$contentData['states'] = getListBoolean();
			$contentData['error'] = $error;		
			$contentData['allowSave'] = (($this->my_application->hasPermission("Users","Insert") && $id <= 0) 
				                   		  || 
				                   		  ($this->my_application->hasPermission("Users","Edit") && $id > 0)
				                  		 );				
			$contentData['callback'] = "initializeEditUser();";									

			$data['menuActive'] = "Users";
			$data['title'] = "Datos del Usuario";
			$data['contentView'] = 'users/users_edit_view';
			$data['contentData'] = $contentData;			
			$this->my_application->loadGeneralTemplate($data);
		}
	}
	
	function save() {                             		
		if ((!$this->my_application->hasPermission("Users","Edit") && $this->input->post('id',true) > 0) || 
		    (!$this->my_application->hasPermission("Users","Insert") && $this->input->post('id',true) <= 0)) {
			redirect('/main/logout');	
		} else {			
			$this->form_validation->set_rules('lastName','Apellido', 'trim|required|max_length[50]|xss_clean');
			$this->form_validation->set_rules('firstName','Nombres', 'trim|required|max_length[50]|xss_clean');														
			$this->form_validation->set_rules('username','Usuario', "trim|required|max_length[100]|valid_email|xss_clean|callback__validateUsername");
			if ($this->input->post('password',true) != "") {
				$this->form_validation->set_rules('password','Clave', 'trim|required|min_length[6]|max_length[15]|xss_clean|callback__validatePassword');		
			} else {
				$this->form_validation->set_rules('password','Clave', 'trim|xss_clean');		
			}
			$this->form_validation->set_rules('roles[]','Roles', 'trim|xss_clean');
			$this->form_validation->set_rules('branchOfficeId','Sucursal', 'trim|required|xss_clean');
			$this->form_validation->set_rules('sectorId','Sector', 'trim|required|xss_clean');
			$this->form_validation->set_rules('active','Activo', 'trim|xss_clean');																		
			$this->form_validation->set_rules('cellphone','Celular', 'trim|max_length[15]|xss_clean');		

			if ($this->form_validation->run() != FALSE) {																						
				$data = array('id'=>$this->input->post('id',true),									   
							  'lastName'=>$this->input->post('lastName',true),
							  'firstName'=>$this->input->post('firstName',true),							  
							  'username'=>$this->input->post('username',true),
							  'password'=>$this->input->post('password',true),
							  'roles'=>$this->input->post('roles',TRUE),
							  'branchOfficeId'=>$this->input->post('branchOfficeId',true),
							  'sectorId'=>$this->input->post('sectorId',true),
							  'active'=>$this->input->post('active',true),
							  'cellphone'=>$this->input->post('cellphone',true));
								
				if ($this->users->setUser($data)) {						
					redirect('/users/listing');	
				} else {	
					$error = "No se pudo grabar los datos, intente nuevamente";
					$this->edit($this->input->post('id',true),$error);
				}				
			} else {
				$this->edit($this->input->post('id',true));		
			}
		}
	}	
	
	function _validateExistsValue($value='', $field='') {		
		if ($value != '' && $field != '') {
			$userId = $this->input->post('id',true);
			if ($this->users->existsValue($value,$userId,$field)) {
				$this->form_validation->set_message('_validateExistsValue', 'El valor ya existe.');
				return FALSE;		  
			} else {
				return TRUE;
			}
		} else {
			return TRUE;
		}
	}

	function _validateUsername($value='') {		
		if ($value != '') {
			$userId = (float)$this->input->post('id',true);
			if ($this->users->existsValue($value,$userId,"username")) {
				$this->form_validation->set_message('_validateUsername', 'El usuario ya existe.');
				return FALSE;		  
			} else {
				return TRUE;
			}
		} else {
			return TRUE;
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

	function delete($userId=-1) {
		$error = "";
		if ($this->session->userdata('userLoggedIn') && $this->my_application->hasPermission("Users","Delete")) {					
			if ($this->users->deleteUserValid($userId)) {						
				if (!$this->users->deleteUser($userId)) {						
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

	function usersCombo() {
		if ($this->session->userdata('userLoggedIn')) {		
			$type = $this->input->get('type',TRUE);
			$selectedId = (float)$this->input->get('sel',TRUE);
			$companyId = (float)$this->input->get('com',TRUE);
			$branchOfficeId = (float)$this->input->get('bo',TRUE);
			$sectorId = (float)$this->input->get('sec',TRUE);
						
			if ($type != "") {
				$parameters['typeFilter'] = $type;					
			}
			if ($companyId > 0) {								
				$parameters['companyIdFilter'] = $companyId;				
			}
			if ($branchOfficeId > 0) {								
				$parameters['branchOfficeIdFilter'] = $branchOfficeId;				
			}
			if ($sectorId > 0) {								
				$parameters['sectorIdFilter'] = $sectorId;				
			}					
			
			$combo = "";
			$users = $this->users->getUsers($parameters);	
			if ($users['totalRecords'] <> 1) {
				$combo .= '<option value="">[Seleccionar]</option>';
			}
			if ($users['totalRecords'] > 0) {
				$users = $users['list'];				
				for ($i=0; $i < count($users); $i++) {
					$selected = ($users[$i]['id'] == $selectedId?'selected="selected"':'');
					
					$combo .= '<option value="'.$users[$i]['id'].'" '.$selected.'>'.trim($users[$i]['lastName'].", ".$users[$i]['firstName']).'</option>';
				}
			}		

			$contentData['data'] = $combo;			
			
			$contentData['byAjax'] = true;	
						
			$view = $this->load->view("general_data_view",$contentData,true);
			
			$this->output->set_output($view); 			
		}
	}		
}
/* End of file Users.php */
/* Location: ./application/controllers/Users.php */