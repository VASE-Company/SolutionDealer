<?php
/**
* Controlador Companies
*
*/
class Companies extends CI_Controller {        
    public function __construct() {
		parent::__construct();
		
	   	$this->output->set_template('default'); 
		$this->output->set_title($this->config->item('applicationName'));	
		$this->form_validation->set_error_delimiters(errorFormatStart(),errorFormatEnd());	
		$this->load->model('companies_model','companies');	
		$this->load->model('warehouses_model','warehouses');				
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
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("Companies","See")) {						
			redirect('/main/logout');	
		} 

		$contentData['allowInsert'] = $this->my_application->hasPermission("Companies","Insert");			
		$contentData['allowEdit'] = $this->my_application->hasPermission("Companies","Edit");
		$contentData['allowDelete'] = $this->my_application->hasPermission("Companies","Delete");				

		$parametersdData = $this->_getParameters($page,
												 $textFilter,
							                     $fieldOrder, $typeOrder);
										
		$parameters = $parametersdData['parameters'];

		foreach ($parameters as $key => $val) {
			$contentData[$key] = $val;
		}
						
		$contentData['filterGet'] = $parametersdData['filterGet'];
						
		$companies = $this->companies->getCompanies($parameters);
		$contentData['companies'] = $companies["list"];		
						
		$pageConfiguration = getPageConfiguration(base_url().'companies/listing',$this->config->item('recordsPerPage'),$companies["totalRecords"],$parametersdData['pageParameters']);
		$this->pagination->initialize($pageConfiguration); 
		$contentData['pagination'] = applyPageStyles($this->pagination->create_links());												
				
		$data['menuActive'] = "Companies";
		$data['title'] = "Empresas";
		$data['contentView'] = 'companies/companies_list_view';
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
		if ((!$this->my_application->hasPermission("Companies","Insert") && $id <= 0) || 
			!$this->my_application->hasPermission("Companies","See")) {
			redirect('/main/logout');
		} else {
			
			if ($id > 0) {
				$parameters["idFilter"] = $id;
				$companies = $this->companies->getCompanies($parameters);
				if ($companies['totalRecords'] == 1){
					$company = $companies['list'][0];	
				} else {
					$id = -1;
				}
			}
			if ($id <= 0) {
				$company = $this->companies->getEmptyCompany();
			}	

			$warehousesParameters["activeOrIdFilter"] = true;
			$warehousesParameters["idFilter"] = $company['warehouseId'];			
			$warehouses = $this->warehouses->getWarehouses($warehousesParameters);	
			$contentData['warehouses'] = $warehouses['list'];					
													
			$contentData['company'] = $company;		
			$contentData['states'] = getListBoolean();		
			$contentData['allowReassign'] = getListBoolean();						
			$contentData['error'] = $error;			
			$contentData['allowSave'] = (($this->my_application->hasPermission("Companies","Insert") && $id <= 0) || ($this->my_application->hasPermission("Companies","Edit") && $id > 0));																								
		
			$data['menuActive'] = "Companies";
			$data['title'] = "Datos de la Empresa";
			$data['contentView'] = 'companies/companies_edit_view';
			$data['contentData'] = $contentData;			
			$this->my_application->loadGeneralTemplate($data);
		}
	}
	
	function save() {                             		
		if ((!$this->my_application->hasPermission("Companies","Edit") && $this->input->post('id',TRUE) > 0) || 
		    (!$this->my_application->hasPermission("Companies","Insert") && $this->input->post('id',TRUE) <= 0)) {
			redirect('/companies/listing');	
		} else {
			$this->form_validation->set_rules('description','Descripción', 'trim|required|max_length[100]|xss_clean|callback__validateExistsValue[description]');																		
			$this->form_validation->set_rules('active','Activo', 'trim|xss_clean');	
			$this->form_validation->set_rules('warehouseId','Despósito', 'trim|required|xss_clean');																		
						
			if ($this->form_validation->run() != FALSE) {																						
				$data = array('id'=>$this->input->post('id',TRUE),									  							  						  
							  'description'=>$this->input->post('description',TRUE),	
							  'active'=>$this->input->post('active',TRUE),
							  'warehouseId'=>$this->input->post('warehouseId',TRUE));
								
				if ($this->companies->setCompany($data)) {						
					redirect('/companies/listing');	
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
			$companyId = $this->input->post('id',TRUE);
			if ($this->companies->existsValue($value,$companyId,$field)) {
				$this->form_validation->set_message('_validateExistsValue', 'El valor ya existe');
				return FALSE;		  
			} else {
				return TRUE;
			}
		} else {
			return TRUE;
		}
	}	

	function delete($companyId=-1) {
		$error = "";
		if ($this->session->userdata('userLoggedIn') && $this->my_application->hasPermission("Companies","Delete")) {					
			if ($this->companies->deleteCompanyValid($companyId)) {						
				if (!$this->companies->deleteCompany($companyId)) {						
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

/* End of file Companies.php */
/* Location: ./application/controllers/Companies.php */