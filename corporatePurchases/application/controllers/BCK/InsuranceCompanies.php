<?php
/**
* Controlador InsuranceCompanies
*
*/
class InsuranceCompanies extends CI_Controller {        
    public function __construct() {
		parent::__construct();
		
	   	$this->output->set_template('default'); 
		$this->output->set_title($this->config->item('applicationName'));	
		$this->form_validation->set_error_delimiters(errorFormatStart(),errorFormatEnd());	
		$this->load->model('insurancecompanies_model','insuranceCompanies');		
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
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("InsuranceCompanies","See")) {						
			redirect('/main/logout');	
		} 

		$contentData['allowInsert'] = $this->my_application->hasPermission("InsuranceCompanies","Insert");			
		$contentData['allowEdit'] = $this->my_application->hasPermission("InsuranceCompanies","Edit");
		$contentData['allowDelete'] = $this->my_application->hasPermission("InsuranceCompanies","Delete");				

		$parametersdData = $this->_getParameters($page,
												 $textFilter,
							                     $fieldOrder, $typeOrder);
										
		$parameters = $parametersdData['parameters'];

		foreach ($parameters as $key => $val) {
			$contentData[$key] = $val;
		}
						
		$contentData['filterGet'] = $parametersdData['filterGet'];
						
		$insuranceCompanies = $this->insuranceCompanies->getInsuranceCompanies($parameters);
		$contentData['insuranceCompanies'] = $insuranceCompanies["list"];		
						
		$pageConfiguration = getPageConfiguration(base_url().'insuranceCompanies/listing',$this->config->item('recordsPerPage'),$insuranceCompanies["totalRecords"],$parametersdData['pageParameters']);
		$this->pagination->initialize($pageConfiguration); 
		$contentData['pagination'] = applyPageStyles($this->pagination->create_links());												
				
		$data['menuActive'] = "InsuranceCompanies";
		$data['title'] = "Cías. Aseguradoras";
		$data['contentView'] = 'insuranceCompanies/insuranceCompanies_list_view';
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
		if ((!$this->my_application->hasPermission("InsuranceCompanies","Insert") && $id <= 0) || 
			!$this->my_application->hasPermission("InsuranceCompanies","See")) {
			redirect('/main/logout');
		} else {
			
			if ($id > 0) {
				$parameters["idFilter"] = $id;
				$insuranceCompanies = $this->insuranceCompanies->getInsuranceCompanies($parameters);
				if ($insuranceCompanies['totalRecords'] == 1){
					$insuranceCompany = $insuranceCompanies['list'][0];	
				} else {
					$id = -1;
				}
			}
			if ($id <= 0) {
				$insuranceCompany = $this->insuranceCompanies->getEmptyInsuranceCompany();
			}
													
			$contentData['insuranceCompany'] = $insuranceCompany;		
			$contentData['states'] = getListBoolean();								
			$contentData['error'] = $error;			
			$contentData['allowSave'] = (($this->my_application->hasPermission("InsuranceCompanies","Insert") && $id <= 0) || ($this->my_application->hasPermission("InsuranceCompanies","Edit") && $id > 0));																								

			$data['menuActive'] = "InsuranceCompanies";
			$data['title'] = "Datos de la Cía. Aseguradora";
			$data['contentView'] = 'insuranceCompanies/insuranceCompanies_edit_view';
			$data['contentData'] = $contentData;			
			$this->my_application->loadGeneralTemplate($data);
		}
	}
	
	function save() {                             		
		if ((!$this->my_application->hasPermission("InsuranceCompanies","Edit") && $this->input->post('id',TRUE) > 0) || 
		    (!$this->my_application->hasPermission("InsuranceCompanies","Insert") && $this->input->post('id',TRUE) <= 0)) {
			redirect('/insuranceCompanies/listing');	
		} else {
			$this->form_validation->set_rules('description','Descripción', 'trim|required|max_length[100]|xss_clean|callback__validateExistsValue[description]');															
			$this->form_validation->set_rules('discountPercent','% Descuento', 'trim|xss_clean|numeric|callback__validateValuePositive');											
			$this->form_validation->set_rules('active','Activo', 'trim|xss_clean');	
						
			if ($this->form_validation->run() != FALSE) {																						
				$data = array('id'=>$this->input->post('id',TRUE),									  							  						  
							  'description'=>$this->input->post('description',TRUE),								  
							  'discountPercent'=>$this->input->post('discountPercent',TRUE),
							  'active'=>$this->input->post('active',TRUE));
								
				if ($this->insuranceCompanies->setInsuranceCompany($data)) {						
					redirect('/insuranceCompanies/listing');	
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
			$insuranceCompanyId = $this->input->post('id',TRUE);
			if ($this->insuranceCompanies->existsValue($value,$insuranceCompanyId,$field)) {
				$this->form_validation->set_message('_validateExistsValue', 'El valor ya existe');
				return FALSE;		  
			} else {
				return TRUE;
			}
		} else {
			return TRUE;
		}
	}	

	function _validateValuePositive($value=0){
		$value = (float)$value;
		
		if ($value < 0) {
			$this->form_validation->set_message('_validateValuePositive', 'El valor debe ser mayor o igual a 0');
			return false;
		} else {
			return true;
		}
	}

	function delete($insuranceCompanyId=-1) {
		$error = "";
		if ($this->session->userdata('userLoggedIn') && $this->my_application->hasPermission("InsuranceCompanies","Delete")) {					
			if ($this->insuranceCompanies->deleteInsuranceCompanyValid($insuranceCompanyId)) {						
				if (!$this->insuranceCompanies->deleteInsuranceCompany($insuranceCompanyId)) {						
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

/* End of file InsuranceCompanies.php */
/* Location: ./application/controllers/InsuranceCompanies.php */