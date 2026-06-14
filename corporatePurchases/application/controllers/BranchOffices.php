<?php
/**
* Controlador BranchOffices
*
*/
class BranchOffices extends CI_Controller {        
    public function __construct() {
		parent::__construct();
		
	   	$this->output->set_template('default'); 
		$this->output->set_title($this->config->item('applicationName'));	
		$this->form_validation->set_error_delimiters(errorFormatStart(),errorFormatEnd());	
		$this->load->model('branchoffices_model','branchOffices');				
		$this->load->model('companies_model','companies');	
		$this->load->js('assets/js/branchOffices.js');				
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
					 $fieldOrder='com',$typeOrder='asc')
	{							
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("BranchOffices","See")) {						
			redirect('/main/logout');	
		} 

		$contentData['allowInsert'] = $this->my_application->hasPermission("BranchOffices","Insert");			
		$contentData['allowEdit'] = $this->my_application->hasPermission("BranchOffices","Edit");
		$contentData['allowDelete'] = $this->my_application->hasPermission("BranchOffices","Delete");				

		$parametersdData = $this->_getParameters($page,
												 $textFilter,
							                     $fieldOrder, $typeOrder);
										
		$parameters = $parametersdData['parameters'];

		foreach ($parameters as $key => $val) {
			$contentData[$key] = $val;
		}
						
		$contentData['filterGet'] = $parametersdData['filterGet'];
						
		$branchOffices = $this->branchOffices->getBranchOffices($parameters);
		$contentData['branchOffices'] = $branchOffices["list"];		
						
		$pageConfiguration = getPageConfiguration(base_url().'branchOffices/listing',$this->config->item('recordsPerPage'),$branchOffices["totalRecords"],$parametersdData['pageParameters']);
		$this->pagination->initialize($pageConfiguration); 
		$contentData['pagination'] = applyPageStyles($this->pagination->create_links());												
				
		$data['menuActive'] = "BranchOffices";
		$data['title'] = "Sucursales";
		$data['contentView'] = 'branchOffices/branchOffices_list_view';
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
		if ((!$this->my_application->hasPermission("BranchOffices","Insert") && $id <= 0) || 
			!$this->my_application->hasPermission("BranchOffices","See")) {
			redirect('/main/logout');
		} else {
			
			if ($id > 0) {
				$parameters["idFilter"] = $id;
				$branchOffices = $this->branchOffices->getBranchOffices($parameters);
				if ($branchOffices['totalRecords'] == 1){
					$branchOffice = $branchOffices['list'][0];	
				} else {
					$id = -1;
				}
			}
			if ($id <= 0) {
				$branchOffice = $this->branchOffices->getEmptyBranchOffice();
			}

			if ($branchOffice['id'] <= 0) {
				$companiesParameters["activeFilter"] = 1;				
				$companies = $this->companies->getCompanies($companiesParameters);	
				$companies = $companies['list'];			
			} else {
				$companies = null;
			}

			$sectors = $this->branchOffices->getSectors($branchOffice['id']);	
			$sectors = $sectors['list'];
													
			$contentData['branchOffice'] = $branchOffice;		
			$contentData['companies'] = $companies;	
			$contentData['sectors'] = $sectors;	
			$contentData['states'] = getListBoolean();											
			$contentData['error'] = $error;			
			
			$contentData['allowSave'] = (($this->my_application->hasPermission("BranchOffices","Insert") && $id <= 0) || ($this->my_application->hasPermission("BranchOffices","Edit") && $id > 0));													

			$data['menuActive'] = "BranchOffices";
			$data['title'] = "Datos de la Sucursal";
			$data['contentView'] = 'branchOffices/branchOffices_edit_view';
			$data['contentData'] = $contentData;			
			$this->my_application->loadGeneralTemplate($data);
		}
	}
	
	function save() {                             		
		if ((!$this->my_application->hasPermission("BranchOffices","Edit") && $this->input->post('id',TRUE) > 0) || 
		    (!$this->my_application->hasPermission("BranchOffices","Insert") && $this->input->post('id',TRUE) <= 0)) {
			redirect('/branchOffices/listing');	
		} else {
			$this->form_validation->set_rules('description','Descripción', 'trim|required|max_length[100]|xss_clean|callback__validateExistsValueInCompany[description]');												
			if ($this->input->post('id',TRUE) <= 0) {
				$this->form_validation->set_rules('companyId','Empresa', 'trim|required|xss_clean');				
			}
			$this->form_validation->set_rules('active','Activo', 'trim|xss_clean');	
			$this->form_validation->set_rules('sectors[]','Sectores', 'trim|xss_clean');
						
			if ($this->form_validation->run() != FALSE) {																						
				$data = array('id'=>$this->input->post('id',TRUE),									  							  						  
							  'description'=>$this->input->post('description',TRUE),	
							  'companyId'=>$this->input->post('companyId',TRUE),				  							  
							  'active'=>$this->input->post('active',TRUE),
							  'sectors'=>$this->input->post('sectors',TRUE));
								
				if ($this->branchOffices->setBranchOffice($data)) {						
					redirect('/branchOffices/listing');	
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
			$branchOfficeId = $this->input->post('id',TRUE);
			if ($this->branchOffices->existsValue($value,$branchOfficeId,$field)) {
				$this->form_validation->set_message('_validateExistsValue', 'El valor ya existe');
				return FALSE;		  
			} else {
				return TRUE;
			}
		} else {
			return TRUE;
		}
	}	

	function _validateExistsValueInCompany($value='', $field='') {		
		if ($value != '' && $field != '') {
			$branchOfficeId = $this->input->post('id',TRUE);
			$companyId = (float)$this->input->post('companyId',TRUE);
			if ($this->branchOffices->existsValue($value,$branchOfficeId,$field,"branchOffices.companyId = ".$companyId)) {
				$this->form_validation->set_message('_validateExistsValueInCompany', 'El valor ya existe para la empresa');
				return FALSE;		  
			} else {
				return TRUE;
			}
		} else {
			return TRUE;
		}
	}

	function delete($branchOfficeId=-1) {
		$error = "";
		if ($this->session->userdata('userLoggedIn') && $this->my_application->hasPermission("BranchOffices","Delete")) {					
			if ($this->branchOffices->deleteBranchOfficeValid($branchOfficeId)) {						
				if (!$this->branchOffices->deleteBranchOffice($branchOfficeId)) {						
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

	function branchOfficesCombo($companyId=-1,$brachOfficeId=-1,$notOptionSelect=0) {
		if ($this->session->userdata('userLoggedIn')) {		
			switch (strtoupper($this->input->get('fo',TRUE))) { //first option
				case "UNS": //unspecified
					$combo = '<option value="0">[Sin Especificar]</option>';
				break;

				case "ALL_F":
					$combo = '<option value="0">[Todas]</option>';
				break;

				case "ALL_M":
					$combo = '<option value="0">[Todos]</option>';
				break;

				case "ALL_F_S":
					$combo = '<option value="">[Todas]</option>';
				break;

				default:
					$combo = '<option value="">[Seleccionar]</option>';		
				break;
			}							
			if ($companyId > 0) {				
				$parameters['companyIdFilter'] = $companyId;	
				$parameters['idFilter'] = $brachOfficeId;
				$parameters['activeOrIdFilter'] = true;					
				$branchOffices = $this->branchOffices->getBranchOffices($parameters);	
				if ($branchOffices['totalRecords'] > 0) {
					$branchOffices = $branchOffices['list'];				
					for ($i=0; $i < count($branchOffices); $i++) {
						$selected = ($branchOffices[$i]['id'] == $brachOfficeId?'selected="selected"':'');
						
						$combo .= '<option value="'.$branchOffices[$i]['id'].'" '.$selected.'>'.$branchOffices[$i]['description'].'</option>';
					}
				}
			}			
						
			$contentData['data'] = $combo;			
			
			$contentData['byAjax'] = true;	
						
			$view = $this->load->view("general_data_view",$contentData,true);
			
			$this->output->set_output($view); 			
		}
	}
		
}

/* End of file BranchOffices.php */
/* Location: ./application/controllers/BranchOffices.php */