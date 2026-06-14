<?php
/**
* Controlador Sectors
*
*/
class Sectors extends CI_Controller {        
    public function __construct() {
		parent::__construct();
		
	   	$this->output->set_template('default'); 
		$this->output->set_title($this->config->item('applicationName'));	
		$this->form_validation->set_error_delimiters(errorFormatStart(),errorFormatEnd());	
		$this->load->model('sectors_model','sectors');				
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
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("Sectors","See")) {						
			redirect('/main/logout');	
		} 

		$contentData['allowInsert'] = $this->my_application->hasPermission("Sectors","Insert");			
		$contentData['allowEdit'] = $this->my_application->hasPermission("Sectors","Edit");
		$contentData['allowDelete'] = $this->my_application->hasPermission("Sectors","Delete");				

		$parametersdData = $this->_getParameters($page,
												 $textFilter,
							                     $fieldOrder, $typeOrder);
										
		$parameters = $parametersdData['parameters'];

		foreach ($parameters as $key => $val) {
			$contentData[$key] = $val;
		}
						
		$contentData['filterGet'] = $parametersdData['filterGet'];
						
		$sectors = $this->sectors->getSectors($parameters);
		$contentData['sectors'] = $sectors["list"];		
						
		$pageConfiguration = getPageConfiguration(base_url().'sectors/listing',$this->config->item('recordsPerPage'),$sectors["totalRecords"],$parametersdData['pageParameters']);
		$this->pagination->initialize($pageConfiguration); 
		$contentData['pagination'] = applyPageStyles($this->pagination->create_links());												
				
		$data['menuActive'] = "Sectors";
		$data['title'] = "Sectores";
		$data['contentView'] = 'sectors/sectors_list_view';
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
		if ((!$this->my_application->hasPermission("Sectors","Insert") && $id <= 0) || 
			!$this->my_application->hasPermission("Sectors","See")) {
			redirect('/main/logout');
		} else {
			
			if ($id > 0) {
				$parameters["idFilter"] = $id;
				$sectors = $this->sectors->getSectors($parameters);
				if ($sectors['totalRecords'] == 1){
					$sector = $sectors['list'][0];	
				} else {
					$id = -1;
				}
			}
			if ($id <= 0) {
				$sector = $this->sectors->getEmptySector();
			}					
													
			$contentData['sector'] = $sector;		
			$contentData['states'] = getListBoolean();		
			$contentData['allowReassign'] = getListBoolean();						
			$contentData['error'] = $error;			
			$contentData['allowSave'] = (($this->my_application->hasPermission("Sectors","Insert") && $id <= 0) || ($this->my_application->hasPermission("Sectors","Edit") && $id > 0));																								
		
			$data['menuActive'] = "Sectors";
			$data['title'] = "Datos del Sector";
			$data['contentView'] = 'sectors/sectors_edit_view';
			$data['contentData'] = $contentData;			
			$this->my_application->loadGeneralTemplate($data);
		}
	}
	
	function save() {                             		
		if ((!$this->my_application->hasPermission("Sectors","Edit") && $this->input->post('id',TRUE) > 0) || 
		    (!$this->my_application->hasPermission("Sectors","Insert") && $this->input->post('id',TRUE) <= 0)) {
			redirect('/sectors/listing');	
		} else {
			$this->form_validation->set_rules('description','Descripción', 'trim|required|max_length[100]|xss_clean|callback__validateExistsValue[description]');																		
			$this->form_validation->set_rules('active','Activo', 'trim|xss_clean');	
						
			if ($this->form_validation->run() != FALSE) {																						
				$data = array('id'=>$this->input->post('id',TRUE),									  							  						  
							  'description'=>$this->input->post('description',TRUE),	
							  'allowPayments'=>$this->input->post('allowPayments',TRUE),
							  'allowOrders'=>$this->input->post('allowOrders',TRUE),
							  'active'=>$this->input->post('active',TRUE));
								
				if ($this->sectors->setSector($data)) {						
					redirect('/sectors/listing');	
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
			$sectorId = $this->input->post('id',TRUE);
			if ($this->sectors->existsValue($value,$sectorId,$field)) {
				$this->form_validation->set_message('_validateExistsValue', 'El valor ya existe');
				return FALSE;		  
			} else {
				return TRUE;
			}
		} else {
			return TRUE;
		}
	}	

	function delete($sectorId=-1) {
		$error = "";
		if ($this->session->userdata('userLoggedIn') && $this->my_application->hasPermission("Sectors","Delete")) {					
			if ($this->sectors->deleteSectorValid($sectorId)) {						
				if (!$this->sectors->deleteSector($sectorId)) {						
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

	function sectorsCombo($brachOfficeId=-1,$sectorId=-1) {
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

				case "ALL_M_S":
					$combo = '<option value="0">[Todos]</option>';
				break;

				default:
					$combo = '<option value="">[Seleccionar]</option>';		
				break;
			}			
			if ($brachOfficeId > 0 || $brachOfficeId = -999) {	
				if ((int)$this->input->get('ao',TRUE) == 1) $parameters['allowOrdersFilter'] = 1;				
				if ((int)$this->input->get('ap',TRUE) == 1) $parameters['allowPaymentsFilter'] = 1;
				if ($brachOfficeId > 0) $parameters['branchOfficeIdFilter'] = $brachOfficeId;	
				$parameters['idFilter'] = $sectorId;
				$parameters['activeOrIdFilter'] = true;					
				$sectors = $this->sectors->getSectors($parameters);	
				if ($sectors['totalRecords'] > 0) {
					$sectors = $sectors['list'];				
					for ($i=0; $i < count($sectors); $i++) {
						$selected = ($sectors[$i]['id'] == $sectorId?'selected="selected"':'');
						
						$combo .= '<option value="'.$sectors[$i]['id'].'" '.$selected.'>'.$sectors[$i]['description'].'</option>';
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

/* End of file Sectors.php */
/* Location: ./application/controllers/Sectors.php */