<?php
/**
* Controlador PriceUpdates
*
*/
class PriceUpdates extends CI_Controller {        
    public function __construct() {
		parent::__construct();
		
	   	$this->output->set_template('default'); 
		$this->output->set_title($this->config->item('applicationName'));	
		$this->form_validation->set_error_delimiters(errorFormatStart(),errorFormatEnd());	
		$this->load->model('priceupdates_model','priceUpdates');	
		$this->load->model('articles_model','articles');	
		$this->load->model('notifications_model','notifications');	
		$this->load->js('assets/js/priceUpdates.js');		
		$this->load->js('assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js');	
   	}
	
	function index(){		
		$this->listing();		
	}
		
	function _getParameters($page=NULL,														
							$fieldOrder=NULL,$typeOrder=NULL) {

		//By default notGet=false and notPagination=false		
		$data['page'] = array('value'=>$page);
		$data['idFilter'] = array('getField'=>'id','notGet'=>true,'notPagination'=>true,'value'=>NULL);		
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
					 $fieldOrder='date',$typeOrder='desc')
	{							
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("PriceUpdates","See")) {						
			redirect('/main/logout');	
		} 

		$contentData['allowInsert'] = $this->my_application->hasPermission("PriceUpdates","Insert");					
		$contentData['allowEdit'] = false;	
		$contentData['allowDelete'] = $this->my_application->hasPermission("PriceUpdates","Delete");		
		$contentData['allowDownloadTemplate'] = false; //$this->my_application->hasPermission("PriceUpdates","DownloadTemplate");	

		if ($this->input->get("id",TRUE) != "") {
			$notificationsParameters = array('entityFilter'=>'priceUpdate',
				                             'entityIdFilter'=>$this->input->get("id",TRUE));	
			$this->notifications->setNotificationRead($notificationsParameters);
		}			

		$parametersdData = $this->_getParameters($page,												 
							                     $fieldOrder, $typeOrder);
										
		$parameters = $parametersdData['parameters'];

		foreach ($parameters as $key => $val) {
			$contentData[$key] = $val;
		}

		$contentData['filterGet'] = $parametersdData['filterGet'];
						
		$priceUpdates = $this->priceUpdates->getPriceUpdates($parameters);
		$contentData['priceUpdates'] = $priceUpdates["list"];		
						
		$pageConfiguration = getPageConfiguration(base_url().'priceUpdates/listing',$this->config->item('recordsPerPage'),$priceUpdates["totalRecords"],$parametersdData['pageParameters']);
		$this->pagination->initialize($pageConfiguration); 
		$contentData['pagination'] = applyPageStyles($this->pagination->create_links());												
				
		$data['menuActive'] = "PriceUpdates";
		$data['title'] = "Actualizaciones de Precios";
		$data['contentView'] = 'priceUpdates/priceUpdates_list_view';
		$data['contentData'] = $contentData;
		$this->my_application->loadGeneralTemplate($data);	
	}

	function delete($priceUpdateId=-1) {
		$error = "";
		if ($this->session->userdata('userLoggedIn') && $this->my_application->hasPermission("PriceUpdates","Delete")) {					
			if ($this->priceUpdates->deletePriceUpdateValid($priceUpdateId)) {						
				if (!$this->priceUpdates->deletePriceUpdate($priceUpdateId)) {						
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

	function import($error='') {
		if (!$this->my_application->hasPermission("PriceUpdates","Insert")) {
			redirect('/priceUpdates/listing');	
		} else {
			$contentData['error'] = $error;		
			$contentData['callback'] = "initializeImportPriceUpdates();";		

			$data['menuActive'] = "PriceUpdates";
			$data['title'] = "Importar Lista de Precios";
			$data['contentView'] = 'priceUpdates/priceUpdates_import_view';
			$data['contentData'] = $contentData;			
			$this->my_application->loadGeneralTemplate($data);
		}
	}

	function importUploadFile() {                             		
		if (!$this->my_application->hasPermission("PriceUpdates","Insert")) {
			redirect('/priceUpdates/listing');	
		} else {									
			$responseSaveFile = $this->_importSaveFile();
			if ($responseSaveFile['code'] == "OK") {
				
				$responsePreLoad = $this->_importPreLoad($responseSaveFile['filename']);
				
				if ($responsePreLoad['code'] == "OK") {					
					$contentData['count'] = $responsePreLoad['count'];				
					$contentData['increase'] = $responsePreLoad['increase'];				
					$contentData['decrease'] = $responsePreLoad['decrease'];				
					$contentData['equal'] = $responsePreLoad['equal'];	
					$contentData['news'] = $responsePreLoad['news'];														
					$contentData['filename'] = $responseSaveFile['filename'];	

					$data['menuActive'] = "priceUpdates";
					$data['title'] = "Importar Lista de Precios - PRE CARGA";
					$data['contentView'] = 'priceUpdates/priceUpdates_import_pre_load_view';
					$data['contentData'] = $contentData;			
					$this->my_application->loadGeneralTemplate($data);	
				} else {					
					$this->import($responsePreLoad['description']);	
				}
				
			} else {				
				$this->import($responseSaveFile['description']);
			}													
		}
	}

	function _importSaveFile() {				
		$response = array('code'=>'ERROR','filename'=>'','description'=>'');
					
		if (isset($_FILES["importFile"]) && trim($_FILES["importFile"]['name']) != "") {
			$folder = $this->config->item('files').$this->session->userdata('companyId').'/tmp';
			checkCreateFolder($folder);		

			$filename = date("YmdHis").".".pathinfo($_FILES["importFile"]['name'], PATHINFO_EXTENSION);	

			$configUpload['file_name'] = $filename;				
			$configUpload['upload_path'] = $folder."/";			
			$configUpload['allowed_types'] = 'csv';			
			$configUpload['max_size']	= '30720';
			$configUpload['overwrite']  = true;							
			
			$this->load->library('upload', $configUpload);
			if ($this->upload->do_upload("importFile"))
			{				
				$response['code'] = "OK";
				$response['filename'] = $filename;
			}
			else
			{
				$response['description'] = $this->upload->display_errors('','');				
			}					  
		} else {
			$response['description'] = "No se pudo cargar el archivo";
		}
		
		return $response;
	}		

	function _importPreLoad($filename="") {		
		if ($filename != "") {
			$path = $this->config->item('files').$this->session->userdata('companyId').'/tmp/'.$filename;			

			if (!file_exists($path)) {
				$response = array('code'=>'ERROR','description'=>'El archivo no es válido');
			} else {
				$responsePreLoad = $this->articles->importPreLoad($path);

				if ($responsePreLoad['error'] == "") {					
					$response['code'] = "OK";		
					$response['count'] = $responsePreLoad['count'];				
					$response['increase'] = $responsePreLoad['increase'];				
					$response['decrease'] = $responsePreLoad['decrease'];				
					$response['equal'] = $responsePreLoad['equal'];	
					$response['news'] = $responsePreLoad['news'];	
				} else {
					$response = array('code'=>'ERROR','description'=>$responsePreLoad['error']);
				}
			}
		} else {
			$response = array('code'=>'ERROR','description'=>'El archivo no es válido');
		}

		return $response;
	}	

	function importLoad() {                             		
		if (!$this->my_application->hasPermission("PriceUpdates","Insert")) {
			redirect('/priceUpdates/listing');		
		} else {									
			$response = $this->articles->importLoad();
		
			if ($response['error'] == "") {
				$data['state'] = "OK";
				$data['observation'] = "";
			} else {
				$data['state'] = "ERROR";
				$data['observation'] = $response['error'];
			}			
			$data['count'] = $this->input->post('count',TRUE);
			$data['increase'] = $this->input->post('increase',TRUE);
			$data['decrease'] = $this->input->post('decrease',TRUE);
			$data['news'] = $this->input->post('news',TRUE);			
			$data['equal'] = $this->input->post('equal',TRUE);		
			$data['filename'] = $this->input->post('filename',TRUE);
								
			$this->priceUpdates->setPriceUpdate($data);
				
			$contentData['error'] = $response['error'];							

			$data['menuActive'] = "PriceUpdates";
			$data['title'] = "Importar Lista de Precios - RESULTADO";
			$data['contentView'] = 'priceUpdates/priceUpdates_import_result_view';
			$data['contentData'] = $contentData;			
			$this->my_application->loadGeneralTemplate($data);									
		}
	}	

	function downloadManual() {		
		if ($this->my_application->hasPermission("PriceUpdates","Insert")) {			

			$path = $this->config->item('files')."PriceUptade.rar";			

			if (file_exists($path)) {
				$this->load->helper('download');
						
				$fileContent = file_get_contents($path);					
						
				force_download("ActualizacionListaDePrecios.rar",$fileContent);
			} else {
				echo "No se encontró el archivo.";
			}	
		} else {
			echo "No se encontró el archivo.";
		}
	}

	/*
	function downloadTemplate() {		
		if ($this->my_application->hasPermission("PriceUpdates","DownloadTemplate")) {			

			$path = $this->config->item('files')."ArticlesTemplate.xlsx";			

			if (file_exists($path)) {
				$this->load->helper('download');
						
				$fileContent = file_get_contents($path);					
						
				force_download("PlantillaArticulos.xlsx",$fileContent);
			} else {
				echo "No se encontró el archivo.";
			}	
		} else {
			echo "No se encontró el archivo.";
		}
	}
	*/
	
	function download($priceUpdateId=-1) {		
		if ($this->my_application->hasPermission("PriceUpdates","See") && $priceUpdateId > 0) {			

			$parameters["idFilter"] = $priceUpdateId;
			$priceUpdates = $this->priceUpdates->getPriceUpdates($parameters);
			if ($priceUpdates['totalRecords'] == 1){
				$priceUpdate = $priceUpdates['list'][0];	

				$path = $this->config->item('files').$this->session->userdata('companyId').'/articles/priceUpdates_'.$priceUpdate['id'].".csv";

				if (file_exists($path)) {					

					$this->load->helper('download');
							
					$fileContent = file_get_contents($path);		

					$date = trim(dateFormat($priceUpdate['date'],true));			
					$date = str_replace(" ","_",$date);
					$date = str_replace("-","_",$date);
					$date = str_replace("/","_",$date);
					$date = str_replace(":","_",$date);
							
					force_download("Actualizacion_Articulos_".$date.".csv",$fileContent);
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
		
}

/* End of file PriceUpdates.php */
/* Location: ./application/controllers/PriceUpdates.php */