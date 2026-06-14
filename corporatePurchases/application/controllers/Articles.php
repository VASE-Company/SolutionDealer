<?php
/**
* Controlador Articles
*
*/

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Articles extends CI_Controller {        
    public function __construct() {
		parent::__construct();
		
	   	$this->output->set_template('default'); 
		$this->output->set_title($this->config->item('applicationName'));	
		$this->form_validation->set_error_delimiters(errorFormatStart(),errorFormatEnd());	
		$this->load->model('articles_model','articles');					
		$this->load->model('families_model','families');
		$this->load->model('warehouses_model','warehouses');
		$this->load->js('assets/js/articles.js');			
   	}
	
	function index(){		
		$this->listing();		
	}
		
	function _getParameters($page=NULL,							
							$textFilter=NULL,$familyId=NULL,
							$fieldOrder=NULL,$typeOrder=NULL) {

		//By default notGet=false and notPagination=false		
		$data['page'] = array('value'=>$page);
		$data['idFilter'] = array('getField'=>'id','notGet'=>true,'notPagination'=>true,'value'=>NULL);
		$data['textFilter'] = array('getField'=>'text','value'=>$textFilter);		
		$data['familyIdFilter'] = array('getField'=>'fam','value'=>$familyId);			
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
					 $textFilter=NULL, $familyId=NULL,
					 $fieldOrder='des',$typeOrder='asc')
	{							
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("Articles","See")) {						
			redirect('/main/logout');	
		} 

		$contentData['allowInsert'] = $this->my_application->hasPermission("Articles","Insert");			
		$contentData['allowEdit'] = $this->my_application->hasPermission("Articles","Edit");
		$contentData['allowDelete'] = $this->my_application->hasPermission("Articles","Delete");	
		$contentData['allowImport'] = $this->my_application->hasPermission("Articles","Insert");				
		$contentData['allowExport'] = $this->my_application->hasPermission("Articles","Export");				

		$parametersdData = $this->_getParameters($page,
												 $textFilter, $familyId,
							                     $fieldOrder, $typeOrder);
										
		$parameters = $parametersdData['parameters'];

		foreach ($parameters as $key => $val) {
			$contentData[$key] = $val;
		}
						
		$contentData['filterGet'] = $parametersdData['filterGet'];
		$contentData['orderGet'] = $parametersdData['orderGet'];

		$families = $this->families->getFamilies();
		$contentData['families'] = $families["list"];
		
		$parameters["fullData"] = true;				
		$articles = $this->articles->getArticles($parameters);
		$contentData['articles'] = $articles["list"];		
						
		$pageConfiguration = getPageConfiguration(base_url().'articles/listing',$this->config->item('recordsPerPage'),$articles["totalRecords"],$parametersdData['pageParameters']);
		$this->pagination->initialize($pageConfiguration); 
		$contentData['pagination'] = applyPageStyles($this->pagination->create_links());	
												
				
		$data['menuActive'] = "Articles";
		$data['title'] = "Artículos";
		$data['contentView'] = 'articles/articles_list_view';
		$data['contentData'] = $contentData;
		$this->my_application->loadGeneralTemplate($data);	
	}

	function search() {			
		$this->form_validation->set_rules('textFilter','Buscar', 'trim|xss_clean');		
		$this->form_validation->set_rules('familyIdFilter','Rubro', 'trim|xss_clean');		
		$this->form_validation->set_rules('fieldOrder','Orden Campo', 'trim|xss_clean');
		$this->form_validation->set_rules('typeOrder','Orden Tipo', 'trim|xss_clean');
		
		if ($this->form_validation->run() != FALSE) {		
			$this->listing(1,
			           	   $this->input->post('textFilter',TRUE),$this->input->post('familyIdFilter',TRUE),
			               $this->input->post('fieldOrder',true),$this->input->post('typeOrder',true)
			           	   );
		} else {
			$this->listing();
		}		
	}
	
	function edit($id=-1, $error='',$values=NULL) {
		if ((!$this->my_application->hasPermission("Articles","Insert") && $id <= 0) || 
			!$this->my_application->hasPermission("Articles","See")) {
			redirect('/main/logout');
		} else {
			
			if ($id > 0) {
				$parameters["idFilter"] = $id;
				$parameters["fullData"] = true;
				$articles = $this->articles->getArticles($parameters);
				if ($articles['totalRecords'] == 1){
					$article = $articles['list'][0];	
				} else {
					$id = -1;
				}
			}
			if ($id <= 0) {
				$article = $this->articles->getEmptyArticle();
			}

			$familiesParameters["activeOrIdFilter"] = true;
			$familiesParameters["idFilter"] = $article['familyId'];			
			$families = $this->families->getFamilies($familiesParameters);	
			$contentData['families'] = $families['list'];	

			if (isset($values) && isset($values['locations'])) $article['locations'] = $values['locations'];
													
			$contentData['article'] = $article;		
			$contentData['states'] = getListBoolean();								
			$contentData['error'] = $error;			
			$contentData['allowSave'] = (($this->my_application->hasPermission("Articles","Insert") && $id <= 0) || ($this->my_application->hasPermission("Articles","Edit") && $id > 0));															

			$data['menuActive'] = "Articles";
			$data['title'] = "Datos del Artículo";
			$data['contentView'] = 'articles/articles_edit_view';
			$data['contentData'] = $contentData;			
			$this->my_application->loadGeneralTemplate($data);
		}
	}
	
	function save() {                             		
		if ((!$this->my_application->hasPermission("Articles","Edit") && $this->input->post('id',TRUE) > 0) || 
		    (!$this->my_application->hasPermission("Articles","Insert") && $this->input->post('id',TRUE) <= 0)) {
			redirect('/articles/listing');	
		} else {
			$this->form_validation->set_rules('code','Código','trim|required|max_length[25]|xss_clean|callback__validateExistsValue[code]');	
			$this->form_validation->set_rules('description','Descripción','trim|required|max_length[100]|xss_clean|callback__validateExistsValue[description]');								
			$this->form_validation->set_rules('familyId','Rubro', 'trim|required|xss_clean');														
			$this->form_validation->set_rules('usual','Habitual','trim|xss_clean');
			$this->form_validation->set_rules('active','Activo','trim|xss_clean');		

			$values = NULL;	

			$locations = array();					  					
			$locationsCount = (int)$this->input->post('locationsCount',TRUE);
			$locationIdx = -1;
			for ($i=1; $i <= $locationsCount; $i++) {
				$locationId = $this->input->post('locationId'.$i,TRUE);

				if (isset($locationId) && $locationId != "") {
					$locationIdx++;
					$locations[$locationIdx]['id'] = (float)$locationId;					
					$locations[$locationIdx]['warehouseId'] = $this->input->post('locationWarehouseId'.$i,TRUE);
					$locations[$locationIdx]['warehouseDescription'] = $this->input->post('locationDescription'.$i,TRUE);
					$locations[$locationIdx]['corridor'] = $this->input->post('locationCorridor'.$i,TRUE);
					$locations[$locationIdx]['shelf'] = $this->input->post('locationShelf'.$i,TRUE);
				
				}
			}									
			$values['locations'] = $locations;			
			
			if ($this->form_validation->run() != FALSE) {																						
				$data = array('id'=>$this->input->post('id',TRUE),									  
							  'code'=>$this->input->post('code',TRUE),							  
							  'description'=>$this->input->post('description',TRUE),								  
							  'familyId'=>$this->input->post('familyId',TRUE),							  						  							 
							  'usual'=>$this->input->post('usual',TRUE),
							  'active'=>$this->input->post('active',TRUE),
							  'locations'=>$locations);
								
				if ($this->articles->setArticle($data)) {						
					redirect('/articles/listing');	
				} else {	
					$error = "No se pudo grabar los datos, intente nuevamente";
					$this->edit($this->input->post('id',TRUE),$error,$values);
				}				
			} else {
				$this->edit($this->input->post('id',TRUE),"",$values);		
			}
		}
	}	
	
	function _validateExistsValue($value='', $field='') {		
		if ($value != '' && $field != '') {
			$articleId = $this->input->post('id',TRUE);
			if ($this->articles->existsValue($value,$articleId,$field)) {
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

	function delete($articleId=-1) {
		$error = "";
		if ($this->session->userdata('userLoggedIn') && $this->my_application->hasPermission("Articles","Delete")) {					
			if ($this->articles->deleteArticleValid($articleId)) {						
				if (!$this->articles->deleteArticle($articleId)) {						
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
		if (!$this->my_application->hasPermission("Articles","Insert")) {
			redirect('/articles/listing');	
		} else {
			$contentData['error'] = $error;		
			$contentData['callback'] = "initializeImportArticles();";		

			$data['menuActive'] = "Articles";
			$data['title'] = "Importar Artículos";
			$data['contentView'] = 'articles/articles_import_view';
			$data['contentData'] = $contentData;			
			$this->my_application->loadGeneralTemplate($data);
		}
	}

	function importUploadFile() {                             		
		if (!$this->my_application->hasPermission("Articles","Insert")) {
			redirect('/articles/listing');	
		} else {									
			$responseSaveFile = $this->_importSaveFile();
			if ($responseSaveFile['code'] == "OK") {
				
				$responsePreLoad = $this->_importPreLoad($responseSaveFile['filename']);
				
				if ($responsePreLoad['code'] == "OK") {					
					$contentData['count'] = $responsePreLoad['count'];				
					$contentData['news'] = $responsePreLoad['news'];														
					$contentData['updated'] = $responsePreLoad['updated'];											
					$contentData['equal'] = $responsePreLoad['equal'];						
					$contentData['filename'] = $responseSaveFile['filename'];	

					$data['menuActive'] = "Articles";
					$data['title'] = "Importar Lista de Artículos - PRE CARGA";
					$data['contentView'] = 'articles/articles_import_pre_load_view';
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
			$folder = $this->config->item('files').'tmp';
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
			$path = $this->config->item('files').'tmp/'.$filename;			

			if (!file_exists($path)) {
				$response = array('code'=>'ERROR','description'=>'El archivo no es válido');
			} else {
				$responsePreLoad = $this->articles->importPreLoad($path);

				if ($responsePreLoad['error'] == "") {					
					$response['code'] = "OK";		
					$response['count'] = $responsePreLoad['count'];	
					$response['news'] = $responsePreLoad['news'];				
					$response['updated'] = $responsePreLoad['updated'];											
					$response['equal'] = $responsePreLoad['equal'];						
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
		if (!$this->my_application->hasPermission("Articles","Insert")) {
			redirect('/articles/listing');		
		} else {									
			$response = $this->articles->importLoad();
		
			if (count($response['error']) == 0) {
				$data['state'] = "OK";
				$data['observation'] = "";
			} else {
				$data['state'] = "ERROR";
				$data['observation'] = "";
				for ($i=0; $i < count($response['error']); $i++) {
					if ($data['observation'] != "") $data['observation'] .= "<br>";
					$data['observation'] .= $response['error'][$i];
				}				
			}			
			$data['count'] = $this->input->post('count',TRUE);
			$data['news'] = $this->input->post('news',TRUE);			
			$data['updated'] = $this->input->post('updated',TRUE);						
			$data['equal'] = $this->input->post('equal',TRUE);		
			$data['filename'] = $this->input->post('filename',TRUE);
								
			$this->articles->setArticlesUpdate($data);
				
			$contentData['error'] = $response['error'];							

			$data['menuActive'] = "Articles";
			$data['title'] = "Importar Artículos - RESULTADO";
			$data['contentView'] = 'articles/articles_import_result_view';
			$data['contentData'] = $contentData;			
			$this->my_application->loadGeneralTemplate($data);									
		}
	}	

	function downloadManual() {		
		if ($this->my_application->hasPermission("Articles","Insert")) {			

			$path = $this->config->item('files')."ArticlesUptade.rar";			

			if (file_exists($path)) {
				$this->load->helper('download');
						
				$fileContent = file_get_contents($path);					
						
				force_download("ImportarArticulos.rar",$fileContent);
			} else {
				echo "No se encontró el archivo.";
			}	
		} else {
			echo "No se encontró el archivo.";
		}
	}	

	function stock($articleId=-1) {
		if (!$this->my_application->hasPermission("Articles","See") || $articleId <= 0) {
			exit;
		} else {					

			$warehousesParameters['activeFilter'] = 1;			
			$warehouses = $this->warehouses->getWarehouses($warehousesParameters);
			$warehouses = $warehouses['list'];

			if (isset($warehouses) && count($warehouses) > 0) {
				for ($i=0; $i < count($warehouses); $i++) {					
					$currentStock = $this->articles->getStockArticle($articleId,$warehouses[$i]['id'],true);

					$warehouses[$i]['stock'] = $currentStock['stock'];
					$warehouses[$i]['availableStock'] = $currentStock['available'];
					$warehouses[$i]['notAvailableStock'] = $currentStock['pending'];
					$warehouses[$i]['outOfStock'] = $currentStock['outOfStock'];

					$warehouses[$i]['location'] = $this->articles->getLocationDescription($articleId,$warehouses[$i]['id']);					
				}
			}			
					
			$contentData['articleId'] = $articleId;
			$contentData['warehouses'] = $warehouses;
															
			$contentData['byAjax'] = true;	
						
			$view = $this->load->view("articles/articles_stock_view",$contentData,true);
			
			$this->output->set_output($view); 										
		}
	}

	function notAvailableStock($articleId=-1,$warehouseId=-1) {
		if (!$this->my_application->hasPermission("Articles","See") || $articleId <= 0 || $warehouseId <= 0) {
			exit;
		} else {					
			$details = $this->articles->getNotAvailableStock($articleId,$warehouseId);
			$details = $details['list'];			
			
			$contentData['details'] = $details;		

			$contentData['articleId'] = $articleId;			
															
			$contentData['byAjax'] = true;	
						
			$view = $this->load->view("articles/articles_not_available_stock_view",$contentData,true);
			
			$this->output->set_output($view); 										
		}
	}

	function outOfStock($articleId=-1,$warehouseId=-1) {
		if (!$this->my_application->hasPermission("Articles","See") || $articleId <= 0 || $warehouseId <= 0) {
			exit;
		} else {					
			$details = $this->articles->getOutOfStock($articleId,$warehouseId);
			$details = $details['list'];			
			
			$contentData['details'] = $details;		

			$contentData['articleId'] = $articleId;			
															
			$contentData['byAjax'] = true;	
						
			$view = $this->load->view("articles/articles_out_of_stock_view",$contentData,true);
			
			$this->output->set_output($view); 										
		}
	}

	function detailedStock($articleId=-1,$warehouseId=-1,$stockType="") {
		if ((!$this->my_application->hasPermission("Articles","See") && !$this->my_application->hasPermission("Reports","See"))
			|| $articleId <= 0 || $warehouseId <= 0 || trim($stockType) == "") {
			exit;
		} else {					
			$details = $this->articles->getDetailedValuedStock($articleId,$warehouseId,$stockType);
			$details = $details['list'];			
			
			$contentData['details'] = $details;						
															
			$contentData['byAjax'] = true;	
						
			$view = $this->load->view("articles/articles_detailed_stock_view",$contentData,true);
			
			$this->output->set_output($view); 										
		}
	}

	function detailedUnitPrice($articleId=-1,$warehouseId=-1,$quantity=-1) {
		if ((!$this->my_application->hasPermission("Articles","See") && !$this->my_application->hasPermission("Reports","See"))
			|| $articleId <= 0) {
			exit;
		} else {		
			$lastUnitPrice = ($warehouseId <= 0);

			if ($lastUnitPrice) {
				$lastPriceDetail = $this->articles->getDetailLastUnitPrice($articleId,$warehouseId);
				if (isset($lastPriceDetail)) {
					$lastPriceDetail['quantity'] = $quantity;
					$details[0] = $lastPriceDetail;
				} else {
					$details = null;
				}
			} else {			
				$details = $this->articles->getDetailedUnitPrice($articleId,$warehouseId);
				$details = $details['list'];			
			}
			
			$contentData['details'] = $details;						
			$contentData['lastUnitPrice'] = $lastUnitPrice;						
			$contentData['showQuantity'] = !($lastUnitPrice && $quantity == 0);						
															
			$contentData['byAjax'] = true;	
						
			$view = $this->load->view("articles/articles_detailed_unit_price_view",$contentData,true);
			
			$this->output->set_output($view); 										
		}
	}
	

	function updateStock() {
		if (!$this->my_application->hasPermission("StockMovements","Insert")) {
			redirect('/articles/listing');	
		} else {			
			$this->load->model('stockMovements_model','stockMovements');	
			$this->load->model('companies_model','companies');			
			
			$stock = $this->articles->getStockToUpdate();
			$stock = $stock['list'];	

			if (isset($stock) && count($stock) > 0) {
				$companies = $this->companies->getCompanies();
				$companies = $companies['list'];

				if (isset($companies) && count($companies) > 0) {

					for ($i=0; $i < count($companies); $i++) {
						$warehouses[$companies[$i]['id']] = $companies[$i]['warehouseId'];
					}

					for ($i=0; $i < count($stock); $i++) {	
						if ($stock[$i]['articleId'] > 0) {
							foreach ($warehouses as $companyId => $warehouseId) {															
								if (isset($stock[$i]['stockCompanyId'.$companyId])) {
									$currentStock = $this->articles->getStockArticle($stock[$i]['articleId'],$warehouseId);

									if ($stock[$i]['stockCompanyId'.$companyId] != $currentStock['stock']) {
										$newStock = $stock[$i]['stockCompanyId'.$companyId];
										$differenceStock = $newStock - $currentStock['stock'];

										$article[0]['id'] = 0;
										$article[0]['articleId'] = $stock[$i]['articleId'];										
										$article[0]['quantity'] = abs($differenceStock);																		

										$stockMovement = array('id'=>0,									  							  						  
															   'typeId'=>($differenceStock < 0?$this->config->item('outputStockMovementTypeId'):$this->config->item('inputStockMovementTypeId')),								  								 
															   'companyId'=>$companyId,								  								 
															   'warehouseId'=>$warehouseId,								  								 
															   'observation'=>"Regularización de stock a ".$newStock.".",
															   'details'=>$article);
									
										$this->stockMovements->setStockMovement($stockMovement);
									}
								}
							}
						}
					}				
				}

			}
		}
	}	

	function _getParametersDescription($parameters=null) {
		
		$parametersDescription = array();			
	
		if (isset($parameters['textFilter']) && $parameters['textFilter'] != "") {	
			$parametersDescription[count($parametersDescription)] = array('label'=>'Código/Descripción:','data'=>$parameters['textFilter']);					
		}
		if (isset($parameters['familyIdFilter']) && $parameters['familyIdFilter'] != "") {				
			$familiesParameters['idFilter'] = $parameters['familyIdFilter'];				
			$families = $this->families->getFamilies($familiesParameters);
			if ($families["totalRecords"] == 1) {
				$family = $families["list"][0];
				$parametersDescription[count($parametersDescription)] = array('label'=>'Rubro:','data'=>$family['description']);						
			}
		}																																																

		return $parametersDescription;
	}

	function export() {				
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("Articles","Export")) exit;											

		$parametersdData = $this->_getParameters();										
		$parameters = $parametersdData['parameters'];
		$articles = $this->articles->getArticles($parameters);
		$articles = $articles["list"];

		$parametersDescription = $this->_getParametersDescription($parameters);

		// INITIALITE SPREADSHEET
				
		$spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Articulos');
		
        // TITLE
        $row = 1;
        $sheet->setCellValue('A'.$row,"LISTADO DE ARTICULOS"); 
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
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Código"); 
        $col++;
		$sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Descripción"); 
		$col++;
		$sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Rubro");  
		$col++;
		$sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Habitual");         
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Activo");        
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Últ. Costo");         
  
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

		for ($i=0; $i < count($articles); $i++) {
			$row++;
	        $col = 0;
	        	        
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$articles[$i]['code']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$articles[$i]['description']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$articles[$i]['familyDescription']); 	         	       	       
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,getBooleanToText($articles[$i]['usual'])); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,getBooleanToText($articles[$i]['active'])); 	
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,decimalFormat($articles[$i]['lastUnitPrice'])); 		               	        
		}		

		for ($i=1; $i <= $col; $i++) {
			$sheet->getColumnDimension(getLetterOfExcelColumn($i))->setAutoSize(true);
		}

		// CREATE TEMP FILE EXCEL, DOWNLOAD Y DELETE 

        $writer = new Xlsx($spreadsheet); 

		$folder = $this->config->item('files').'tmp/';
		 
		$filename = 'articulos_'.getCurrentDateId().'.xlsx';
		 
		$writer->save($folder.$filename); 

		$this->load->helper('download');
							
		$fileContent = file_get_contents($folder.$filename);	

		@unlink($folder.$filename);				
							
		force_download($filename,$fileContent);
	}	
	
	/*
	function resetStock($articleId=-1,$warehouseId=-1) {
		if (!$this->my_application->hasPermission("StockMovements","Insert")) {
			redirect('/articles/listing');	
		} else {
			$this->load->model('reports_model','reports');	
			$this->load->model('stockMovements_model','stockMovements');	
			$this->load->model('warehouses_model','warehouses');

			//$parameters['generalLimit'] = 500;
			$parameters['stockNotZeroFilter'] = true;
			if ($articleId > 0) $parameters['articleIdFilter'] = $articleId;
			if ($warehouseId > 0) $parameters['warehouseIdFilter'] = $warehouseId;

			$data = $this->reports->getSummaryStock($parameters);
			$data = $data['list'];	

			echo "Puesta a 0 de stock: ".getCurrentDate(true,true)."<br>";

			if (isset($data) && count($data) > 0) {
				$warehouses = array();

				for ($i=0; $i < count($data); $i++) {
					if ($data[$i]['stock'] < 0) {		

						if (!isset($companies[$data[$i]['warehouseId']])) {
							$companies[$data[$i]['warehouseId']] = $this->warehouses->getFirstCompanyByWarehouse($data[$i]['warehouseId']);							
						}

						if ($companies[$data[$i]['warehouseId']] > 0) {
							$article[0]['id'] = 0;
							$article[0]['articleId'] = $data[$i]['id'];										
							$article[0]['quantity'] = abs($data[$i]['stock']);																		

							$stockMovement = array('id'=>0,									  							  						  
												   'typeId'=>($data[$i]['stock'] > 0?$this->config->item('outputStockMovementTypeId'):$this->config->item('inputStockMovementTypeId')),								  								 
												   'companyId'=>$companies[$data[$i]['warehouseId']],								  								 
												   'warehouseId'=>$data[$i]['warehouseId'],								  								 
												   'observation'=>"Puesta a 0 de Stock.",
												   'details'=>$article);
							
							if (!$this->stockMovements->setStockMovement($stockMovement)) {
								echo "<br>Hubo un error al guardar:";
								print_r($stockMovement);
								exit;
							}
						}												
					}
				}				

				echo 'Se puso a 0 el stock '.count($data).' artículo(s). <a id="link" href="javascript:'."$('#link').remove();".'reloadPage()">Continuar >></a>';
			} else {
				echo  "No hay artículos con existencias.";
			}			
		}
	}
	

	function assignStock() {
		if (!$this->my_application->hasPermission("StockMovements","Insert")) {
			redirect('/articles/listing');	
		} else {
			echo "Asignando stock disponible: ".getCurrentDate(true,true)."<br>";

			$this->articles->updateAllPendingStock();

			echo  "Se ha asignado el stock a los pedidos.";			
		}
	}
	*/
}

/* End of file Articles.php */
/* Location: ./application/controllers/Articles.php */