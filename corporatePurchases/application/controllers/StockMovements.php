<?php
/**
* Controlador StockMovements
*
*/

defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StockMovements extends CI_Controller {     

    public function __construct() {
		parent::__construct();
		
	   	$this->output->set_template('default'); 
		$this->output->set_title($this->config->item('applicationName'));	
		$this->form_validation->set_error_delimiters(errorFormatStart(),errorFormatEnd());	
		$this->load->model('stockMovements_model','stockMovements');				
		$this->load->model('movementsTypes_model','movementsTypes');	
		$this->load->model('warehouses_model','warehouses');	
		$this->load->model('companies_model','companies');	
		$this->load->model('articles_model','articles');	
		$this->load->js('assets/js/stockMovements.js');	
		$this->load->js('assets/js/articles.js');	
   	}
	
	function index(){		
		$this->listing();		
	}
		
	function _getParameters($page=NULL,							
							$dateFromFilter=NULL,$dateToFilter=NULL,
							$inputFilter=NULL,$typeIdFilter=NULL,
							$warehouseIdFilter=NULL,$articleFilter=NULL,
							$observationFilter=NULL,
							$fieldOrder=NULL,$typeOrder=NULL) {

		//By default notGet=false and notPagination=false		
		$data['page'] = array('value'=>$page);		
		$data['dateFromFilter'] = array('getField'=>'df','value'=>$dateFromFilter);	
		$data['dateToFilter'] = array('getField'=>'dt','value'=>$dateToFilter);	
		$data['inputFilter'] = array('getField'=>'in','value'=>$inputFilter);		
		$data['typeIdFilter'] = array('getField'=>'type','value'=>$typeIdFilter);	
		$data['warehouseIdFilter'] = array('getField'=>'wh','value'=>$warehouseIdFilter);	
		$data['articleFilter'] = array('getField'=>'art','value'=>$articleFilter);				
		$data['observationFilter'] = array('getField'=>'obs','value'=>$observationFilter);									
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
		$parametersdData['backGet'] = "?back=".convertGet(((float)$page > 0?$page:"1")."?".$filterGet.$orderGet);
		
		return $parametersdData;	
	}
	
	function listing($page=1, 
					 $dateFromFilter=NULL,$dateToFilter=NULL,
					 $inputFilter=NULL,$typeIdFilter=NULL,
					 $warehouseIdFilter=NULL,$articleFilter=NULL,
					 $observationFilter=NULL,
					 $fieldOrder='date',$typeOrder='desc')
	{							
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("StockMovements","See")) {						
			redirect('/main/logout');	
		} 

		$contentData['allowInsert'] = $this->my_application->hasPermission("StockMovements","Insert");			
		$contentData['allowEdit'] = $this->my_application->hasPermission("StockMovements","Edit");
		$contentData['allowDelete'] = $this->my_application->hasPermission("StockMovements","Delete");				
		$contentData['allowExport'] = $this->my_application->hasPermission("StockMovements","Export");	

		$parametersdData = $this->_getParameters($page,
												 $dateFromFilter, $dateToFilter,
												 $inputFilter, $typeIdFilter,
												 $warehouseIdFilter, $articleFilter,
												 $observationFilter,
							                     $fieldOrder, $typeOrder);
										
		$parameters = $parametersdData['parameters'];

		foreach ($parameters as $key => $val) {
			$contentData[$key] = $val;
		}
						
		$contentData['filterGet'] = $parametersdData['filterGet'];
		$contentData['orderGet'] = $parametersdData['orderGet'];
		$contentData['backGet'] = $parametersdData['backGet'];	

		$types[0] = array('id'=>'1', 'description'=>'Entrada');
		$types[1] = array('id'=>'0', 'description'=>'Salida');
		$contentData['types'] = $types;	

		$typesParameters["includeInternalFilter"] = true;			
		$movementsTypes = $this->movementsTypes->getMovementsTypes($typesParameters);
		$contentData['movementsTypes'] = $movementsTypes["list"];	

		$warehouses = $this->warehouses->getWarehouses();
		$contentData['warehouses'] = $warehouses["list"];	
						
		$stockMovements = $this->stockMovements->getStockMovements($parameters);
		$contentData['stockMovements'] = $stockMovements["list"];		
						
		$pageConfiguration = getPageConfiguration(base_url().'stockMovements/listing',$this->config->item('recordsPerPage'),$stockMovements["totalRecords"],$parametersdData['pageParameters']);
		$this->pagination->initialize($pageConfiguration); 
		$contentData['pagination'] = applyPageStyles($this->pagination->create_links());												
		
		$contentData['callback'] = "initializeStockMovement();";	

		$data['menuActive'] = "StockMovements";
		$data['title'] = "Movimientos de Stock";
		$data['contentView'] = 'stockMovements/stockMovements_list_view';
		$data['contentData'] = $contentData;
		$this->my_application->loadGeneralTemplate($data);	
	}

	function search() {					
		$this->form_validation->set_rules('dateFromFilter','Fecha Desde', 'trim|xss_clean');
		$this->form_validation->set_rules('dateToFilter','Fecha Hasta', 'trim|xss_clean');	
		$this->form_validation->set_rules('inputFilter','Tipo', 'trim|xss_clean');	
		$this->form_validation->set_rules('typeIdFilter','Concepto', 'trim|xss_clean');	
		$this->form_validation->set_rules('warehouseIdFilter','Almacén', 'trim|xss_clean');	
		$this->form_validation->set_rules('articleFilter','Artículo', 'trim|xss_clean');
		$this->form_validation->set_rules('observationFilter','Observación', 'trim|xss_clean');
		$this->form_validation->set_rules('fieldOrder','Orden Campo', 'trim|xss_clean');
		$this->form_validation->set_rules('typeOrder','Orden Tipo', 'trim|xss_clean');
		
		if ($this->form_validation->run() != FALSE) {		
			$this->listing(1,
			           	   $this->input->post('dateFromFilter',TRUE),$this->input->post('dateToFilter',TRUE),
			           	   $this->input->post('inputFilter',TRUE),$this->input->post('typeIdFilter',TRUE),
			           	   $this->input->post('warehouseIdFilter',TRUE),$this->input->post('articleFilter',TRUE),
			           	   $this->input->post('observationFilter',TRUE),
			               $this->input->post('fieldOrder',true),$this->input->post('typeOrder',true)
			           	   );
		} else {
			$this->listing();
		}		
	}
	
	function edit($id=-1,$registerType='',$error='',$values=NULL) {
		if ((!$this->my_application->hasPermission("StockMovements","Insert") && $id <= 0) || 
			!$this->my_application->hasPermission("StockMovements","See")) {
			redirect('/main/logout');
		} else {
			
			if ($id > 0 && $registerType != "") {
				$parameters["idFilter"] = $id;
				$parameters["registerTypeFilter"] = $registerType;
				$stockMovements = $this->stockMovements->getStockMovements($parameters);
				if ($stockMovements['totalRecords'] == 1){
					$stockMovement = $stockMovements['list'][0];	
				} else {
					$id = -1;
				}
			}
			if ($id <= 0) {
				$stockMovement = $this->stockMovements->getEmptyStockMovement();
			}				

			if (isset($values) && isset($values['details'])) $stockMovement['details'] = $values['details'];	

			$companiesParameters["idFilter"] = $stockMovement["companyId"];		
			$companiesParameters["activeOrIdFilter"] = true;					
			$companies = $this->companies->getCompanies($companiesParameters);	
			$contentData['companies'] = $companies["list"];	

			$contentData['stockMovement'] = $stockMovement;												
			$contentData['error'] = $error;						
			$contentData['allowSave'] = (($this->my_application->hasPermission("StockMovements","Insert") && $id <= 0));																														

			$typesParameters["idFilter"] = $stockMovement["typeId"];	
			$typesParameters["activeOrIdFilter"] = true;		
			if (!$contentData['allowSave']) {
				$typesParameters["includeInternalFilter"] = true;					
			}
			$types = $this->movementsTypes->getMovementsTypes($typesParameters);	
			$contentData['types'] = $types["list"];	

			if ($this->input->get('back',TRUE) != "") {
				$contentData['backGet'] = convertGet($this->input->get('back',TRUE),false);				
			} else {
				$contentData['backGet'] = "";				
			}

			$contentData['callback'] = "initializeEditStockMovement();";	

			$data['menuActive'] = "StockMovements";
			$data['title'] = "Datos del Mov. de Stock";
			$data['contentView'] = 'stockMovements/stockMovements_edit_view';
			$data['contentData'] = $contentData;			
			$this->my_application->loadGeneralTemplate($data);
		}
	}
	
	function save() {                             		
		if ((!$this->my_application->hasPermission("StockMovements","Edit") && $this->input->post('id',TRUE) > 0) || 
		    (!$this->my_application->hasPermission("StockMovements","Insert") && $this->input->post('id',TRUE) <= 0)) {
			redirect('/stockMovements/listing');	
		} else {
			$this->form_validation->set_rules('typeId','Concepto', 'trim|required|xss_clean');																		
			$this->form_validation->set_rules('companyId','Almacén', 'trim|required|xss_clean');																		
			$this->form_validation->set_rules('warehouseId','Depósito', 'trim|required|xss_clean');																		
			$this->form_validation->set_rules('observation','Observación', 'trim|xss_clean');	
			$this->form_validation->set_rules('backGet','Get Volver', 'trim|xss_clean');				

			$values = NULL;	
 			$details = array();		  						
			$detailsCount = (int)$this->input->post('detailsCount',TRUE);
			$detailIdx = -1;
			for($i=1; $i <= $detailsCount; $i++) {
				$detailId = $this->input->post('detailId'.$i,TRUE);

				if (isset($detailId) && $detailId != "") {
					$detailIdx++;
					$details[$detailIdx]['id'] = (float)$detailId;
					$details[$detailIdx]['articleId'] = $this->input->post('detailArticleId'.$i,TRUE);
					$details[$detailIdx]['code'] = $this->input->post('detailCode'.$i,TRUE);
					$details[$detailIdx]['description'] = $this->input->post('detailDescription'.$i,TRUE);					
					$details[$detailIdx]['quantity'] = $this->input->post('detailQuantity'.$i,TRUE);												
					$details[$detailIdx]['unitPrice'] = $this->input->post('detailUnitPrice'.$i,TRUE);	
					$details[$detailIdx]['familyDescription'] = $this->input->post('detailFamily'.$i,TRUE);																																																																		
				}
			}									
			$values['details'] = $details;				
						
			if ($this->form_validation->run() != FALSE) {																						
				$outOfStock = false;
				$typesParameters["idFilter"] = (float)$this->input->post('typeId',TRUE);					
				$typesParameters["includeInternalFilter"] = true;					
				$types = $this->movementsTypes->getMovementsTypes($typesParameters);	
				$types = $types["list"];
				if (count($types) == 1) {
					if ((int)$types[0]['input'] == 0 && isset($details)) {

						for ($i=0; $i < count($details); $i++) {
							$currentStock = $this->articles->getStockArticle($details[$i]['articleId'],(float)$this->input->post('warehouseId',TRUE),true);
							if ($currentStock['available'] < $details[$i]['quantity']) {
								$details[$i]['outOfStock'] = true;
								$outOfStock = true;
							}
						}							
					}
				}
				$values['details'] = $details;	

				if ($outOfStock) {
					$error = "Algunos artículos no poseen la cantidad suficiente para generar el movimiento de salida.";
					$this->edit($this->input->post('id',TRUE),"SM",$error,$values);
				} else {							
					$data = array('id'=>$this->input->post('id',TRUE),									  							  						  
								  'typeId'=>$this->input->post('typeId',TRUE),								  								 
								  'companyId'=>$this->input->post('companyId',TRUE),								  								 
								  'warehouseId'=>$this->input->post('warehouseId',TRUE),								  								 
								  'observation'=>$this->input->post('observation',TRUE),
								  'details'=>$details);
			
					if ($this->stockMovements->setStockMovement($data)) {
						if ($this->input->post('backGet',TRUE) != "") {
							$backGet = $this->input->post('backGet',TRUE);						
						} else {
							$backGet = "";							
						}										
						redirect('/stockMovements/listing/'.$backGet);	
					} else {	
						$error = "No se pudo grabar los datos, intente nuevamente";
						$this->edit($this->input->post('id',TRUE),"SM",$error,$values);
					}								
				}
			} else {
				$this->edit($this->input->post('id',TRUE),"SM","",$values);		
			}
		}
	}	
	
	function _validateExistsValue($value='', $field='') {		
		if ($value != '' && $field != '') {
			$stockMovementId = $this->input->post('id',TRUE);
			if ($this->stockMovements->existsValue($value,$stockMovementId,$field)) {
				$this->form_validation->set_message('_validateExistsValue', 'El valor ya existe');
				return FALSE;		  
			} else {
				return TRUE;
			}
		} else {
			return TRUE;
		}
	}	

	function delete($stockMovementId=-1) {
		$error = "";
		if ($this->session->userdata('userLoggedIn') && $this->my_application->hasPermission("StockMovements","Delete")) {					
			if ($this->stockMovements->deleteStockMovementValid($stockMovementId)) {						
				if (!$this->stockMovements->deleteStockMovement($stockMovementId)) {						
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

	function articlesFinder($page=1) {
		if (!$this->my_application->hasPermission("StockMovements","See")) {
			exit;
		} else {															
			$textFilter = "";			
			if ($this->input->post('textFilter',true) != "") $textFilter = $this->input->post('textFilter',true);
			if ($this->input->get('text',true) != "") $textFilter = $this->input->get('text',true);

			$from = "";	
			if ($this->input->post('from',true) != "") $from = $this->input->post('from',true);
			if ($this->input->get('from',true) != "") $from = $this->input->get('from',true);

			switch (strtolower($from)) {
				case "filter":
					$acceptFunction = "acceptArticlesFinderFilterStockMovement";
				break;

				default:
					$acceptFunction = "acceptArticlesFinderStockMovementEdit";
				break;
			}
			$contentData['from'] = $from;		
			$contentData['acceptFunction'] = $acceptFunction;			

			if ($page > 0) {										 
				$parameters['textFilter'] = $textFilter;
				$parameters['activeFilter'] = 1;														
				$parameters['affectsStockFilter'] = 1;	
				$parameters['page'] = $page;															
				$articles = $this->articles->getArticles($parameters);						
				$contentData['articles'] = $articles["list"];								

				$pageParameters = array('text'=>$textFilter,'from'=>$from);					
				$pageConfiguration = getPageConfiguration(base_url().'stockMovements/articlesFinder',$this->config->item('recordsPerPage'),$articles["totalRecords"],$pageParameters);
				$this->pagination->initialize($pageConfiguration); 
				$contentData['pagination'] = convertPageToAjax(applyPageStyles($this->pagination->create_links()),'reloadFinderStockMovementEdit');	
			} else {
				$contentData['articles'] = NULL;				
				$contentData['pagination'] = NULL;
			}

			$contentData['textFilter'] = $textFilter;					
			
			$contentData['filterGet'] = "";					
			if ($textFilter != "") $contentData['filterGet'] .= "&bus=".$textFilter;						
															
			$contentData['byAjax'] = true;	
						
			$view = $this->load->view("stockMovements/stockMovements_finder_articles_view",$contentData,true);
			
			$this->output->set_output($view); 										
		}
	}

	
	function searchArticle() {
		if ($this->session->userdata('userLoggedIn')) {									
			
			$code = $this->input->post('code',true);
			
			$id = 0;
			if ($code != "") {				

				$parameters['activeFilter'] = 1;
				$parameters['affectsStockFilter'] = 1;					
				$parameters['codeFilter'] = $code;
				$articles = $this->articles->getArticles($parameters);	
				if ($articles['totalRecords'] > 0) {
					$articles = $articles['list'][0];				
					
					$id = $articles['id'];
					$code = $articles['code'];
					$description = str_replace('"',"",$articles['description']);							
					$familyDescription = str_replace('"',"",$articles['familyDescription']);
					
				}
			} 

			if ($id == 0) {
				$id = "0";
				$code = "";
				$description = "";						
				$familyDescription = "";					
			}			                   

			$quantity = "1";             			
			$unitPrice = "0.00";   
							
			$contentData['data'] = "";					
			$contentData['data'] .= '<input type="hidden" id="articleId" name="articleId" value="'.$id.'">';
			$contentData['data'] .= '<input type="hidden" id="codeArticle" name="codeArticle" value="'.$code.'">';
			$contentData['data'] .= '<input type="hidden" id="descriptionArticle" name="descriptionArticle" value="'.$description.'">';			
			$contentData['data'] .= '<input type="hidden" id="quantityArticle" name="quantityArticle" value="'.$quantity.'">';						
			$contentData['data'] .= '<input type="hidden" id="unitPriceArticle" name="unitPriceArticle" value="'.$unitPrice.'">';						
			$contentData['data'] .= '<input type="hidden" id="familyArticle" name="familyArticle" value="'.$familyDescription.'">';			

			$contentData['byAjax'] = true;	
						
			$view = $this->load->view("general_data_view",$contentData,true);
			
			$this->output->set_output($view); 		
		}
	}

	function _getParametersDescription($parameters=null) {
		
		$parametersDescription = array();			

		$period = "";
		if (isset($parameters['dateFromFilter']) && $parameters['dateFromFilter'] != "") {				
			if ($period != "") $period .= " ";
			$period .= "desde el ".dateFormat($parameters['dateFromFilter']);											
		}
		if (isset($parameters['dateToFilter']) && $parameters['dateToFilter'] != "") {				
			if ($period != "") $period .= " ";
			$period .= "hasta el ".dateFormat($parameters['dateToFilter']);				
		}	
		if ($period != "") $parametersDescription[count($parametersDescription)] = array('label'=>'Periodo:','data'=>$period);				
		if (isset($parameters['inputFilter']) && $parameters['inputFilter'] != "") {	
			$parametersDescription[count($parametersDescription)] = array('label'=>'Tipo:','data'=>((int)$parameters['inputFilter'] == 1?"Entrada":"Salida"));					
		}
		if (isset($parameters['typeIdFilter']) && $parameters['typeIdFilter'] != "") {				
			$typesParameters['idFilter'] = $parameters['typeIdFilter'];				
			$movementsTypes = $this->movementsTypes->getMovementsTypes($typesParameters);
			if ($movementsTypes["totalRecords"] == 1) {
				$movementsType = $movementsTypes["list"][0];
				$parametersDescription[count($parametersDescription)] = array('label'=>'Concepto:','data'=>$movementsType['description']);						
			}
		}	
		if (isset($parameters['warehouseIdFilter']) && $parameters['warehouseIdFilter'] != "") {				
			$warehousesParameters['idFilter'] = $parameters['warehouseIdFilter'];				
			$warehouses = $this->warehouses->getWarehouses($warehousesParameters);
			if ($warehouses["totalRecords"] == 1) {
				$warehouse = $warehouses["list"][0];
				$parametersDescription[count($parametersDescription)] = array('label'=>'Depósito:','data'=>$warehouse['description']);						
			}
		}	
		if (isset($parameters['articleFilter']) && $parameters['articleFilter'] != "") {	
			$parametersDescription[count($parametersDescription)] = array('label'=>'Cod. Artículo:','data'=>$parameters['articleFilter']);					
		}																								
		if (isset($parameters['observationFilter']) && $parameters['observationFilter'] != "") {	
			$parametersDescription[count($parametersDescription)] = array('label'=>'Observación:','data'=>$parameters['observationFilter']);					
		}																								

		return $parametersDescription;
	}

	function export() {				
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("StockMovements","Export")) exit;											

		$parametersdData = $this->_getParameters();										
		$parameters = $parametersdData['parameters'];
		$stockMovements = $this->stockMovements->getStockMovements($parameters);
		$stockMovements = $stockMovements["list"];
		$dataPreviusStock = $this->stockMovements->getPreviusStock($parameters);
		$includePreviusStock = $dataPreviusStock['includePreviusStock'];
		$previusStock = $dataPreviusStock['previusStock'];

		$parametersDescription = $this->_getParametersDescription($parameters);
		

		// INITIALITE SPREADSHEET
				
		$spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Movimientos_Stock');
		
        // TITLE
        $row = 1;
        $sheet->setCellValue('A'.$row,"LISTADO DE MOVIMIENTOS DE STOCK"); 
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
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Fecha"); 
        $col++;
		$sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Tipo"); 
		$col++;
		$sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Concepto"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Despósito"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Artículo"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Cantidad"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Costo"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Observación");   
        if ($includePreviusStock) {
        	$col++;
        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Stock Acum.");   
        	$previusStockCol = $col;
        }     
  
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
		$stock = 0;

		if ($includePreviusStock && isset($parameters) && isset($parameters['dateFromFilter'])) {
			$row++;
			$sheet->setCellValue(getLetterOfExcelColumn(1).$row,dateFormat($parameters['dateFromFilter']." 00:00:00",true)); 
			$sheet->setCellValue(getLetterOfExcelColumn(5).$row,"Stock a la fecha"); 
			$sheet->setCellValue(getLetterOfExcelColumn(6).$row,trim($previusStock)); 	        
			$stock += $previusStock;
	        $sheet->setCellValue(getLetterOfExcelColumn($previusStockCol).$row,trim($stock));	             
		}

		for ($i=0; $i < count($stockMovements); $i++) {
			$row++;
	        $col = 0;
	        	        
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,dateFormat($stockMovements[$i]['date'],true)); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$stockMovements[$i]['inputDescription']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$stockMovements[$i]['typeDescription']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$stockMovements[$i]['warehouseDescription']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"[".$stockMovements[$i]['articleCode']."] ".$stockMovements[$i]['articleDescription']); 	        
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,trim($stockMovements[$i]['quantity'])); 	
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,((int)$stockMovements[$i]['input'] == 1?decimalFormat($stockMovements[$i]['unitPrice'],2):"")); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$stockMovements[$i]['observation']); 
	        if ($includePreviusStock) {		
	         	$col++;		
	         	if ((int)$stockMovements[$i]['input'] == 1) {		        
					$stock += $stockMovements[$i]['quantity'];
				} else {
					$stock -= $stockMovements[$i]['quantity'];
				}
		        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,trim($stock));	        
			}	        
		}		

		for ($i=1; $i <= $col; $i++) {
			$sheet->getColumnDimension(getLetterOfExcelColumn($i))->setAutoSize(true);
		}

		// CREATE TEMP FILE EXCEL, DOWNLOAD Y DELETE 

        $writer = new Xlsx($spreadsheet); 

		$folder = $this->config->item('files').'tmp/';
		 
		$filename = 'movimientos_stock_'.getCurrentDateId().'.xlsx';
		 
		$writer->save($folder.$filename); 

		$this->load->helper('download');
							
		$fileContent = file_get_contents($folder.$filename);	

		@unlink($folder.$filename);				
							
		force_download($filename,$fileContent);
	}	
	
	function stockMovementData($stockMovementId=-1) {
		if (!$this->my_application->hasPermission("StockMovements","See") || $stockMovementId <= 0) {
			exit;
		} else {					
				
			$data = $this->stockMovements->getDataByStockMovement($stockMovementId);
			$contentData['data'] = $data['list'];				
															
			$contentData['byAjax'] = true;	
						
			$view = $this->load->view("stockMovements/stockMovements_itemData_view",$contentData,true);
			
			$this->output->set_output($view); 										
		}
	}
}

/* End of file StockMovements.php */
/* Location: ./application/controllers/StockMovements.php */