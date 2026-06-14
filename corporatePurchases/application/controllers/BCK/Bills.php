<?php
/**
* Controlador Bills
*
*/
class Bills extends CI_Controller {        
    public function __construct() {
		parent::__construct();
		
	   	$this->output->set_template('default'); 
		$this->output->set_title($this->config->item('applicationName'));	
		$this->form_validation->set_error_delimiters(errorFormatStart(),errorFormatEnd());	
		$this->load->model('bills_model','bills');
		$this->load->model('notifications_model','notifications');	
		$this->load->js('assets/js/bills.js');	
		$this->load->js('assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js');	
   	}
	
	function index(){		
		$this->listing();		
	}
		
	function _getParameters($page=NULL,							
							$textFilter=NULL,$stateIdFilter=NULL,
							$fieldOrder=NULL,$typeOrder=NULL) {

		//By default notGet=false and notPagination=false		
		$data['page'] = array('value'=>$page);
		$data['idFilter'] = array('getField'=>'id','notGet'=>true,'notPagination'=>true,'value'=>NULL);
		$data['textFilter'] = array('getField'=>'text','value'=>$textFilter);		
		$data['stateIdFilter'] = array('getField'=>'sta','value'=>$stateIdFilter);	
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
					 $textFilter=NULL,$stateIdFilter=NULL,
					 $fieldOrder='date',$typeOrder='desc')
	{							
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("Bills","See")) {						
			redirect('/main/logout');	
		} 

		$contentData['allowInsert'] = $this->my_application->hasPermission("Bills","Insert");			
		$contentData['allowEdit'] = $this->my_application->hasPermission("Bills","Edit");
		$contentData['allowDelete'] = $this->my_application->hasPermission("Bills","Delete");		

		if ($this->input->get("id",TRUE) != "") {
			$notificationsParameters = array('entityFilter'=>'bill',
				                             'entityIdFilter'=>$this->input->get("id",TRUE));	
			$this->notifications->setNotificationRead($notificationsParameters);
		}		

		$parametersdData = $this->_getParameters($page,
												 $textFilter,$stateIdFilter,
							                     $fieldOrder, $typeOrder);
										
		$parameters = $parametersdData['parameters'];

		foreach ($parameters as $key => $val) {
			$contentData[$key] = $val;
		}
						
		$contentData['filterGet'] = $parametersdData['filterGet'];
						
		$bills = $this->bills->getBills($parameters);
		$contentData['bills'] = $bills["list"];		

		$states = $this->bills->getBillsStates();
		$contentData['states'] = $states["list"];	
						
		$pageConfiguration = getPageConfiguration(base_url().'bills/listing',$this->config->item('recordsPerPage'),$bills["totalRecords"],$parametersdData['pageParameters']);
		$this->pagination->initialize($pageConfiguration); 
		$contentData['pagination'] = applyPageStyles($this->pagination->create_links());												
				
		$data['menuActive'] = "Bills";
		$data['title'] = "Estado de Cuenta";
		$data['contentView'] = 'bills/bills_list_view';
		$data['contentData'] = $contentData;
		$this->my_application->loadGeneralTemplate($data);	
	}

	function search() {			
		$this->form_validation->set_rules('textFilter','Buscar', 'trim|xss_clean');		
		$this->form_validation->set_rules('stateIdFilter','Estado', 'trim|xss_clean');		
		$this->form_validation->set_rules('fieldOrder','Orden Campo', 'trim|xss_clean');
		$this->form_validation->set_rules('typeOrder','Orden Tipo', 'trim|xss_clean');
		
		if ($this->form_validation->run() != FALSE) {		
			$this->listing(1,
			           	   $this->input->post('textFilter',TRUE),$this->input->post('stateIdFilter',TRUE),
			               $this->input->post('fieldOrder',true),$this->input->post('typeOrder',true)
			           	   );
		} else {
			$this->listing();
		}		
	}
	
	function edit($id=-1, $error='') {
		if ((!$this->my_application->hasPermission("Bills","Insert") && $id <= 0) || 
			!$this->my_application->hasPermission("Bills","See")) {
			redirect('/main/logout');
		} else {
			
			if ($id > 0) {
				$parameters["idFilter"] = $id;
				$bills = $this->bills->getBills($parameters);
				if ($bills['totalRecords'] == 1){
					$bill = $bills['list'][0];	

					$notificationsParameters = array('entityFilter'=>'bill',
				                                     'entityIdFilter'=>$id);	
					$this->notifications->setNotificationRead($notificationsParameters);
				} else {
					$id = -1;
				}
			}
			if ($id <= 0) {
				$bill = $this->bills->getEmptyBill();
			}

			$path = $this->config->item('files').$this->session->userdata('companyId').'/bills/bill_'.$bill['id'].".pdf";
			$contentData['hasFile'] = file_exists($path);

			$billsTypes = $this->bills->getBillsTypes();
			$contentData['billsTypes'] = $billsTypes["list"];

			$billsStates = $this->bills->getBillsStates();
			$contentData['billsStates'] = $billsStates["list"];
													
			$contentData['bill'] = $bill;												
			$contentData['error'] = $error;			
			$contentData['allowSave'] = (($this->my_application->hasPermission("Bills","Insert") && $id <= 0) || ($this->my_application->hasPermission("Bills","Edit") && $id > 0));																								

			$contentData['callback'] = "initializeBillEdit();";

			$data['menuActive'] = "Bills";
			$data['title'] = "Datos del Comprobante";
			$data['contentView'] = 'bills/bills_edit_view';
			$data['contentData'] = $contentData;			
			$this->my_application->loadGeneralTemplate($data);
		}
	}
	
	function save() {                             		
		if ((!$this->my_application->hasPermission("Bills","Edit") && $this->input->post('id',TRUE) > 0) || 
		    (!$this->my_application->hasPermission("Bills","Insert") && $this->input->post('id',TRUE) <= 0)) {
			redirect('/bills/listing');	
		} else {
			$this->form_validation->set_rules('date','Fecha','trim|required|xss_clean');
			$this->form_validation->set_rules('typeId','Tipo','trim|required|xss_clean');
			$this->form_validation->set_rules('letter','Letra','trim|required|max_length[1]|xss_clean');	
			$this->form_validation->set_rules('serie','Serie','trim|required|max_length[4]|xss_clean');	
			$this->form_validation->set_rules('number','Número','trim|required|max_length[8]|xss_clean');	
			$this->form_validation->set_rules('stateId','Estado','trim|required|xss_clean');
			$this->form_validation->set_rules('amount','Importe', 'trim|required|xss_clean|numeric|callback__validateValuePositive');											
			$this->form_validation->set_rules('description','Descripción', 'trim|required|max_length[100]|xss_clean');	
			$this->form_validation->set_rules('paymentDate','Fecha Pago','trim|xss_clean');
			$this->form_validation->set_rules('paymentData','Obs. del Pago', 'trim|max_length[500]|xss_clean');															
		
			if ($this->form_validation->run() != FALSE) {																						
				$data = array('id'=>$this->input->post('id',TRUE),									  							  						  
							  'date'=>$this->input->post('date',TRUE),								  
							  'typeId'=>$this->input->post('typeId',TRUE),	
							  'letter'=>$this->input->post('letter',TRUE),	
							  'serie'=>$this->input->post('serie',TRUE),	
							  'number'=>$this->input->post('number',TRUE),	
							  'stateId'=>$this->input->post('stateId',TRUE),	
							  'amount'=>$this->input->post('amount',TRUE),	
							  'description'=>$this->input->post('description',TRUE),	
							  'paymentDate'=>$this->input->post('paymentDate',TRUE),	
							  'paymentData'=>$this->input->post('paymentData',TRUE));

				$billId = $this->bills->setBill($data);
								
				if ($billId > 0) {												
					$error = "";
					
					$responseSaveFile = $this->_saveFile($billId);

					if ((float)$this->input->post('id',TRUE) <= 0 && $this->input->post('typeId',TRUE) == 'FAC' && $this->input->post('stateId',TRUE) == 'PEN') {
						$this->bills->notifyNewBill($billId);
					}

					if ($responseSaveFile['code'] == "ERROR") {
						$error = $responseSaveFile['description'];
						$this->edit($billId,$error);
					} else {
						redirect('/bills/listing');
					}													
				} else {	
					$error = "No se pudo grabar los datos, intente nuevamente";
					$this->edit($this->input->post('id',TRUE),$error);
				}				
			} else {
				$this->edit($this->input->post('id',TRUE));		
			}
		}
	}	

	function _saveFile($billId=-1) {								
		if ($billId > 0) {
			if (isset($_FILES["billFile"]) && trim($_FILES["billFile"]['name']) != "") {
				$folder = $this->config->item('files').$this->session->userdata('companyId').'/bills';
				checkCreateFolder($folder);		
				$folder .= "/";

				$filename = "bill_".$billId.".pdf";

				if (file_exists($folder.$filename)) {
			   		@unlink($folder.$filename);		   	
			   	}

				$configUpload['file_name'] = $filename;				
				$configUpload['upload_path'] = $folder;			
				$configUpload['allowed_types'] = 'pdf';			
				$configUpload['max_size']	= '30720';
				$configUpload['overwrite']  = true;							
				
				$this->load->library('upload', $configUpload);
				if ($this->upload->do_upload("billFile"))
				{				
					$response['code'] = "UPLOAD";								
				}
				else
				{
					$response['code'] = "ERROR";	
					$response['description'] = $this->upload->display_errors('','');				
				}					  					
			} else {
				$response['code'] = "OK";					
			}
		} else {
			$response['code'] = "ERROR";
			$response['description'] = "No se pudo cargar el archivo";
		}
		
		return $response;
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

	function delete($billId=-1) {
		$error = "";
		if ($this->session->userdata('userLoggedIn') && $this->my_application->hasPermission("Bills","Delete")) {					
			if ($this->bills->deleteBillValid($billId)) {						
				if (!$this->bills->deleteBill($billId)) {						
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

	function download($billId=-1) {		
		if ($this->my_application->hasPermission("Bills","See") && $billId > 0) {			

			$parameters["idFilter"] = $billId;
			$bills = $this->bills->getBills($parameters);
			if ($bills['totalRecords'] == 1){
				$bill = $bills['list'][0];	

				$path = $this->config->item('files').$this->session->userdata('companyId').'/bills/bill_'.$bill['id'].".pdf";

				if (file_exists($path)) {
					$notificationsParameters = array('entityFilter'=>'bill',
				                                     'entityIdFilter'=>$billId);	
					$this->notifications->setNotificationRead($notificationsParameters);		

					$this->load->helper('download');
							
					$fileContent = file_get_contents($path);					
							
					force_download($bill['typeId']."_".$bill['letter']."_".$bill['serie']."_".$bill['number'].".pdf",$fileContent);
				} else {
					echo "No se encontró el archivo.";
				}	
			} else {
				echo "No se encontró el archivo.";
			}			
		} else {
			echo "No se encontró el archivo.";
		}
	}

	function sendMailPending() {                             		
		if (!$this->my_application->hasPermission("Bills","Insert")) {
			redirect('/bills/listing');		
		} else {									
			
			$response = $this->bills->sendPendingBills();			

			$contentData['pendigBills'] = $response['pendigBills'];				
			$contentData['mailsOK'] = $response['mailsOK'];				
			$contentData['mailsERROR'] = $response['mailsERROR'];				
			$contentData['mailInternal'] = $response['mailInternal'];			

			$data['menuActive'] = "Bills";
			$data['title'] = "Envío de mails de Aviso de Facturas pendientes de pago";
			$data['contentView'] = 'bills/bills_send_mail_pending_view';
			$data['contentData'] = $contentData;			
			$this->my_application->loadGeneralTemplate($data);									

		}
	}
		
}

/* End of file Bills.php */
/* Location: ./application/controllers/Bills.php */