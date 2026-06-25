<?php
/**
* Controlador Bills
*
*/

defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Bills extends CI_Controller {
    
    public function __construct() {
		parent::__construct();
		
	   	$this->output->set_template('default'); 
		$this->output->set_title($this->config->item('applicationName'));	
		$this->form_validation->set_error_delimiters(errorFormatStart(),errorFormatEnd());	
		$this->load->model('bills_model','bills');				
		$this->load->model('companies_model','companies');				
		$this->load->model('articles_model','articles');				
		$this->load->model('suppliers_model','suppliers');
		$this->load->js('assets/js/bills.js');				
   	}
	
	function index(){		
		$this->listing();						
	}

	function _getParameters($page=NULL,
		                    $idFilter=NULL,$numberFilter=NULL,
							$dateFromFilter=NULL,$dateToFilter=NULL,
				            $companyIdFilter=NULL, $userIdFilter=NULL,
		             		$supplierCodeFilter=NULL,$articleFilter=NULL,
		             		$fieldOrder=NULL,$typeOrder=NULL) {

		//By default type=null (get) and notPagination=false		
		$data['page'] = array('value'=>$page);		
		$data['idFilter'] = array('getField'=>'id','value'=>$idFilter);	
		$data['numberFilter'] = array('getField'=>'num','value'=>$numberFilter);
		$data['dateFromFilter'] = array('getField'=>'df','value'=>$dateFromFilter);	
		$data['dateToFilter'] = array('getField'=>'dt','value'=>$dateToFilter);	
		$data['companyIdFilter'] = array('getField'=>'com','value'=>$companyIdFilter);								
		$data['userIdFilter'] = array('getField'=>'user','value'=>$userIdFilter);	
		$data['supplierCodeFilter'] = array('getField'=>'sup','value'=>$supplierCodeFilter);				
		$data['articleFilter'] = array('getField'=>'art','value'=>$articleFilter);								
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
					 $idFilter=NULL,$numberFilter=NULL,
					 $dateFromFilter=NULL,$dateToFilter=NULL,
		             $companyIdFilter=NULL, $userIdFilter=NULL,
             		 $supplierCodeFilter=NULL,$articleFilter=NULL,
		             $fieldOrder='udate',$typeOrder='desc')
	{							
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("Bills","See")) {						
			redirect('/main/logout');			
		} else {
			if ($fieldOrder=="") $fieldOrder = "id";
			if ($typeOrder=="") $typeOrder = "desc";
											
			$contentData['allowInsert'] = $this->my_application->hasPermission("Bills","Insert");						
			$contentData['allowEdit'] = $this->my_application->hasPermission("Bills","Edit");
			$contentData['allowDelete'] = $this->my_application->hasPermission("Bills","Delete");			
			$contentData['allowExport'] = $this->my_application->hasPermission("Bills","Export");						
			$contentData['allowFullExport'] = $this->my_application->hasPermission("Bills","FullExport");
								
			$parametersdData = $this->_getParameters($page,
													 $idFilter,	$numberFilter,
													 $dateFromFilter, $dateToFilter,
													 $companyIdFilter, $userIdFilter, 
													 $supplierCodeFilter, $articleFilter,
								                     $fieldOrder, $typeOrder);
											
			$parameters = $parametersdData['parameters'];

			foreach ($parameters as $key => $val) {
				$contentData[$key] = $val;
			}
						
			$contentData['filterGet'] = $parametersdData['filterGet'];
			$contentData['orderGet'] = $parametersdData['orderGet'];
			$contentData['backGet'] = $parametersdData['backGet'];

			$companies = $this->companies->getCompanies();	
			$contentData['companies'] = $companies["list"];		

			$parameters['deletedFilter'] = $contentData['allowDelete'];
			$bills = $this->bills->getBills($parameters);
			$contentData['bills'] = $bills["list"];			
							
			$pageConfiguration = getPageConfiguration(base_url().'bills/listing',$this->config->item('recordsPerPage'),$bills["totalRecords"],$parametersdData['pageParameters']);
			$this->pagination->initialize($pageConfiguration); 
			$contentData['pagination'] = applyPageStyles($this->pagination->create_links());												
						
			$contentData['callback'] = "initializeBills('".trim($this->input->get('error',TRUE))."');";	

			$data['menuActive'] = "Bills";
			$data['title'] = "Facturas de Compras";
			$data['contentView'] = 'bills/bills_list_view';
			$data['contentData'] = $contentData;
			$this->my_application->loadGeneralTemplate($data);	
		}
	}	

	function search() {					
		$this->form_validation->set_rules('idFilter','Nro Pedido', 'trim|xss_clean');
		$this->form_validation->set_rules('numberFilter','Nro Factura/Ref. Int.', 'trim|xss_clean');
		$this->form_validation->set_rules('dateFromFilter','Fecha Desde', 'trim|xss_clean');
		$this->form_validation->set_rules('dateToFilter','Fecha Hasta', 'trim|xss_clean');		
		$this->form_validation->set_rules('companyIdFilter','Empresa', 'trim|xss_clean');
		$this->form_validation->set_rules('userIdFilter','Cargado Por', 'trim|xss_clean');
		$this->form_validation->set_rules('supplierCodeFilter','Plazo', 'trim|xss_clean');		
		$this->form_validation->set_rules('articleFilter','Artículo', 'trim|xss_clean');							
		$this->form_validation->set_rules('fieldOrder','Orden Campo', 'trim|xss_clean');
		$this->form_validation->set_rules('typeOrder','Orden Tipo', 'trim|xss_clean');
		
		if ($this->form_validation->run() != FALSE) {					
			$this->listing(1,
						   $this->input->post('idFilter',true),	$this->input->post('numberFilter',true),	
						   $this->input->post('dateFromFilter',true),$this->input->post('dateToFilter',true),
			           	   $this->input->post('companyIdFilter',TRUE),$this->input->post('userIdFilter',TRUE),
			           	   $this->input->post('supplierCodeFilter',TRUE),$this->input->post('articleFilter',TRUE),
			               $this->input->post('fieldOrder',true),$this->input->post('typeOrder',true)
			           	   );
		} else {
			$this->listing();
		}		
	}	
			
	function export() {				
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("Bills","Export")) exit;											

		$parametersdData = $this->_getParameters();										
		$parameters = $parametersdData['parameters'];		
		$bills = $this->bills->getBills($parameters);
		$bills = $bills["list"];	

		$parametersDescription = $this->_getParametersDescription($parameters);

		// INITIALITE SPREADSHEET
				
		$spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Facturas de Compras');
		
        // TITLE
        $row = 1;
        $sheet->setCellValue('A'.$row,"LISTADO DE FACTURAS DE COMPRAS"); 
        $styleCell = array('font'=>array('bold'=>true,'size'=>16));
        $sheet->getStyle('A'.$row)->applyFromArray($styleCell);
        $sheet->mergeCells('A'.$row.':'.'C'.$row);

        // PARAMETERS        
        $styleCell = array('font'=>array('bold'=>true));
        for ($i=0; $i < count($parametersDescription); $i++) {
        	$row++;
        	$sheet->setCellValue('A'.$row,$parametersDescription[$i]['label']); 
        	$sheet->setCellValue('B'.$row,$parametersDescription[$i]['data']);
        	$sheet->getStyle('A'.$row)->applyFromArray($styleCell);
        	$sheet->mergeCells('B'.$row.':'.'C'.$row);
        }

        // GRID HEADERS        
        $col = 0;
        $row++;
        $showColumnTotal = $this->my_application->hasPermission("Bills","SeeImports");        

        $row++;
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Fecha Carga");
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Fecha Factura"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Nº Factura"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"CUIT"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Nombre Comercial"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Imp. Total"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Empresa");         
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Cargado Por"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Ref. Int."); 
                   
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

		for ($i=0; $i < count($bills); $i++) {
			$row++;
	        $col = 0;
	        
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,dateFormat($bills[$i]['date'],false)); 	        
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,dateFormat($bills[$i]['billDate'],false)); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$bills[$i]['fullNumber']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$bills[$i]['supplierCode']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$bills[$i]['supplierDescription']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,decimalFormat($bills[$i]['total'],2)); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$bills[$i]['companyDescription']); 	
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$bills[$i]['userDescription']); 		       
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$bills[$i]['id']); 	
		}		

		for ($i=1; $i <= $col; $i++) {
			$sheet->getColumnDimension(getLetterOfExcelColumn($i))->setAutoSize(true);
		}

		// CREATE TEMP FILE EXCEL, DOWNLOAD Y DELETE 

        $writer = new Xlsx($spreadsheet); 

		$folder = $this->config->item('files').'tmp/';
		 
		$filename = 'facturas_de_compras_'.getCurrentDateId().'.xlsx';
		 
		$writer->save($folder.$filename); 

		$this->load->helper('download');
							
		$fileContent = file_get_contents($folder.$filename);	

		@unlink($folder.$filename);				
							
		force_download($filename,$fileContent);
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
		if (isset($parameters['numberFilter']) && $parameters['numberFilter'] != "") {	
			$parametersDescription[count($parametersDescription)] = array('label'=>'Nº Factura/Ref. Int.:','data'=>$parameters['numberFilter']);					
		}
		if (isset($parameters['companyIdFilter']) && $parameters['companyIdFilter'] != "") {				
			$companiesParameters['idFilter'] = $parameters['companyIdFilter'];				
			$companies = $this->companies->getCompanies($companiesParameters);
			if ($companies["totalRecords"] == 1) {
				$companies = $companies["list"][0];
				$parametersDescription[count($parametersDescription)] = array('label'=>'Empresa:','data'=>$companies['description']);						
			}
		}			
		if (isset($parameters['supplierCodeFilter']) && $parameters['supplierCodeFilter'] != "") {	
			$parametersDescription[count($parametersDescription)] = array('label'=>'CUIT:','data'=>$parameters['supplierCodeFilter']);					
		}
		if (isset($parameters['articleFilter']) && $parameters['articleFilter'] != "") {	
			$parametersDescription[count($parametersDescription)] = array('label'=>'Artículo:','data'=>$parameters['articleFilter']);					
		}																								
																									
		return $parametersDescription;
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

	function edit($id=-1,$error='',$values=NULL) {
		if ((!$this->my_application->hasPermission("Bills","Insert") && $id <= 0) || 
			!$this->my_application->hasPermission("Bills","See")) {
			redirect('/main/logout');
		}else {											
			if ($id > 0) {
				$parameters["idFilter"] = $id;
				$parameters["fullData"] = true;
				$parameters['deletedFilter'] = $this->my_application->hasPermission("Bills","Delete");
				$bills = $this->bills->getBills($parameters);
				if ($bills['totalRecords'] == 1){
					$bill = $bills['list'][0];	
					
				} else {
					$id = -1;
				}
			}

			if ($id <= 0) {
				$bill = $this->bills->getEmptyBill();								;				
			}		

			if (isset($values) && isset($values['details'])) $bill['details'] = $values['details'];			
						
		  	//$contentData['allowEditGeneralData'] = (($this->my_application->hasPermission("Bills","Insert") && $id <= 0) || ($this->my_application->hasPermission("Bills","Edit") && $id > 0));															
		  	//$contentData['allowEditDetail'] = (($this->my_application->hasPermission("Bills","Insert") && $id <= 0) || ($this->my_application->hasPermission("Bills","Edit") && $id > 0));															
		  	$contentData['allowEditNumerationData'] = ($bill["billDate"] == "" || trim($bill["letter"]) == "" || trim($bill["serie"]) == "" || trim($bill["number"]) == "");
		  	$contentData['allowEditGeneralData'] = ($this->my_application->hasPermission("Bills","Insert") && $id <= 0);															
		  	$contentData['allowEditDetail'] = ($this->my_application->hasPermission("Bills","Insert") && $id <= 0);															
		  	$contentData['allowSave'] = ((int)$bill['deleted'] == 0 && ($contentData['allowEditGeneralData'] || $contentData['allowEditDetail'] || $contentData['allowEditNumerationData']));					  	

			$companiesParameters["idFilter"] = $bill["companyId"];				
			if ($contentData['allowSave']) {
				$companiesParameters["activeOrIdFilter"] = true;			
			}
			$companies = $this->companies->getCompanies($companiesParameters);	
			$contentData['companies'] = $companies["list"];				
			
			$contentData['bill'] = $bill;	
			$contentData['error'] = $error;								                             
			
			if ($this->input->get('back',TRUE) != "") {
				$contentData['backGet'] = convertGet($this->input->get('back',TRUE),false);				
			} else {
				$contentData['backGet'] = "";				
			}
	
			$contentData['callback'] = "initializeBillEdit()";

			$data['menuActive'] = "Bills";
			$data['title'] = "Datos de la Factura de Compra";

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
			$id = (float)$this->input->post('id',TRUE);

			$allowEditDetail = true;			
			
			$this->form_validation->set_rules('date','Fecha Carga','trim|required|xss_clean');
			$this->form_validation->set_rules('userDescription','Usuario','trim|xss_clean');
			$this->form_validation->set_rules('companyId','Empresa','trim|required|xss_clean');
			$this->form_validation->set_rules('warehouseId','Depósito','trim|required|xss_clean');
			$this->form_validation->set_rules('billDate','Fecha Factura','trim|required|xss_clean');
			$this->form_validation->set_rules('letter','Letra de la Factura','trim|required|max_length[1]|xss_clean');					
			$this->form_validation->set_rules('serie','Serie de la Factura','trim|required|max_length[5]|xss_clean');			
			$this->form_validation->set_rules('number','Número de la Factura','trim|required|max_length[8]|callback__validateExistsBill|xss_clean');	
			$this->form_validation->set_rules('supplierCode','CUIT','trim|required|xss_clean');		
			$this->form_validation->set_rules('supplierId','Id Proveedor','trim|xss_clean');
			$this->form_validation->set_rules('supplierDescription','Nombre Comercial Proveedor','trim|xss_clean');			
			$this->form_validation->set_rules('total','Total','trim|xss_clean');			 
			$this->form_validation->set_rules('observation','Observación','trim|max_length[1000]|xss_clean');					
			$this->form_validation->set_rules('backGet','Get Volver', 'trim|xss_clean');			
	 		
	 		$values = NULL;	
	 		if ($allowEditDetail) {
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
						$details[$detailIdx]['unitPrice'] = $this->input->post('detailUnitPrice'.$i,TRUE);							
						$details[$detailIdx]['quantity'] = $this->input->post('detailQuantity'.$i,TRUE);																		
						$details[$detailIdx]['familyDescription'] = $this->input->post('detailFamily'.$i,TRUE);												
						$details[$detailIdx]['total'] = $this->input->post('detailTotal'.$i,TRUE);												
					}
				}									
				$values['details'] = $details;						
			}
									
			if ($this->form_validation->run() != FALSE) {					
				$data['id'] = $this->input->post('id',TRUE);				
				$data['date'] = $this->input->post('date',TRUE);
				$data['companyId'] = $this->input->post('companyId',TRUE);
				$data['warehouseId'] = $this->input->post('warehouseId',TRUE);
				$data['billDate'] = $this->input->post('billDate',TRUE);
				$data['letter'] = $this->input->post('letter',TRUE);
				$data['serie'] = $this->input->post('serie',TRUE);	
				$data['number'] = $this->input->post('number',TRUE);	
				$data['supplierId'] = $this->input->post('supplierId',TRUE);						
				$data['observation'] = $this->input->post('observation',TRUE);	

				if ($allowEditDetail) $data['details'] = $details;				
				
				$response = $this->bills->setBill($data);

				$billId = (float)$response['billId'];

				if ($billId > 0) {										
					if ($this->input->post('backGet',TRUE) != "") {
						$backGet = $this->input->post('backGet',TRUE);						
					} else {
						$backGet = "";							
					}						

					if ($error != "") {
						if ($backGet == "") $backGet = "?";						
						$getBack .= "&error=".$error;
					}									
					
					redirect('/bills/listing/'.$backGet);	
				} else {	
					if (isset($response['message']) && trim($response['message']) != "") {
						$error = $response['message'];
					} else {
						$error = "No se pudo grabar los datos, intente nuevamente";
					}					
					$this->edit($this->input->post('id',TRUE),$error,$values);
				}
			} else {
				$this->edit($this->input->post('id',TRUE),"",$values);		
			}
		}
	}	

	function _validateExistsBill() {		
		
		$billId = (float)$this->input->post('id',TRUE);
		$supplierId = (float)$this->input->post('supplierId',TRUE);
		$letter = trim($this->input->post('letter',TRUE));
		$serie = trim($this->input->post('serie',TRUE));
		$number = trim($this->input->post('number',TRUE));

		$iCount = 0;
		if ($letter != "") $iCount++;
		if ($serie != "") $iCount++;
		if ($number != "") $iCount++;

		if ($iCount != 0 && $iCount != 3) {
			$this->form_validation->set_message('_validateExistsBill', 'El número de factura no es válido');
			return FALSE;		
		}

		if ($supplierId > 0 && $letter != "" && $serie != "" && $number != "") {
			if ($this->bills->existsBill($supplierId,$letter,$serie,$number,$billId)) {
				$this->form_validation->set_message('_validateExistsBill', 'El número de factura ya existe para el proveedor');
				return FALSE;		  
			} else {
				return TRUE;
			}	
		} else {
			return TRUE;
		}		
	}	

	function articlesFinder($page=1) {
		if (!$this->my_application->hasPermission("Bills","See")) {
			exit;
		} else {															
			$textFilter = "";			
			if ($this->input->post('textFilter',true) != "") $textFilter = $this->input->post('textFilter',true);
			if ($this->input->get('text',true) != "") $textFilter = $this->input->get('text',true);

			if ($page > 0) {										 
				$parameters['textFilter'] = $textFilter;
				$parameters['activeFilter'] = 1;		
				$parameters['affectsStockFilter'] = 1;													
				$parameters['page'] = $page;															
				$articles = $this->articles->getArticles($parameters);						
				$contentData['articles'] = $articles["list"];								

				$pageParameters = array('text'=>$textFilter);	
				$pageConfiguration = getPageConfiguration(base_url().'bills/articlesFinder',$this->config->item('recordsPerPage'),$articles["totalRecords"],$pageParameters);
				$this->pagination->initialize($pageConfiguration); 
				$contentData['pagination'] = convertPageToAjax(applyPageStyles($this->pagination->create_links()),'reloadFinderBillEdit');	
			} else {
				$contentData['articles'] = NULL;				
				$contentData['pagination'] = NULL;
			}

			$contentData['textFilter'] = $textFilter;					
			
			$contentData['filterGet'] = "";					
			if ($textFilter != "") $contentData['filterGet'] .= "&bus=".$textFilter;						
															
			$contentData['byAjax'] = true;	
						
			$view = $this->load->view("bills/bills_finder_articles_view",$contentData,true);
			
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
					$unitPrice = decimalFormat($articles['price'],2);			
					$familyDescription = str_replace('"',"",$articles['familyDescription']);
					
				}
			} 

			if ($id == 0) {
				$id = "0";
				$code = "";
				$description = "";
				$unitPrice = "0.00";				
				$familyDescription = "";					
			}			                   

			$quantity = "1";             
			$total = "0.00";			
							
			$contentData['data'] = "";					
			$contentData['data'] .= '<input type="hidden" id="articleId" name="articleId" value="'.$id.'">';
			$contentData['data'] .= '<input type="hidden" id="codeArticle" name="codeArticle" value="'.$code.'">';
			$contentData['data'] .= '<input type="hidden" id="descriptionArticle" name="descriptionArticle" value="'.$description.'">';
			$contentData['data'] .= '<input type="hidden" id="unitPriceArticle" name="unitPriceArticle" value="'.$unitPrice.'">';
			$contentData['data'] .= '<input type="hidden" id="quantityArticle" name="quantityArticle" value="'.$quantity.'">';			
			$contentData['data'] .= '<input type="hidden" id="totalArticle" name="totalArticle" value="'.$total.'">';			
			$contentData['data'] .= '<input type="hidden" id="familyArticle" name="familyArticle" value="'.$familyDescription.'">';			

			$contentData['byAjax'] = true;	
						
			$view = $this->load->view("general_data_view",$contentData,true);
			
			$this->output->set_output($view); 		
		}
	}		

	function suppliersFinder($page=1) {
		if (!$this->my_application->hasPermission("Bills","See")) {
			exit;
		} else {															
			$textFilter = "";			
			if ($this->input->post('textFilter',true) != "") $textFilter = $this->input->post('textFilter',true);
			if ($this->input->get('text',true) != "") $textFilter = $this->input->get('text',true);

			if ($page > 0) {										 
				$parameters['textFilter'] = $textFilter;
				$parameters['activeFilter'] = 1;														
				$parameters['page'] = $page;															
				$suppliers = $this->suppliers->getSuppliers($parameters);						
				$contentData['suppliers'] = $suppliers["list"];								

				$pageParameters = array('text'=>$textFilter);	
				$pageConfiguration = getPageConfiguration(base_url().'bills/suppliersFinder',$this->config->item('recordsPerPage'),$suppliers["totalRecords"],$pageParameters);
				$this->pagination->initialize($pageConfiguration); 
				$contentData['pagination'] = convertPageToAjax(applyPageStyles($this->pagination->create_links()),'reloadFinderBillEdit');	
			} else {
				$contentData['suppliers'] = NULL;				
				$contentData['pagination'] = NULL;
			}

			$contentData['textFilter'] = $textFilter;					
			
			$contentData['filterGet'] = "";					
			if ($textFilter != "") $contentData['filterGet'] .= "&bus=".$textFilter;						
															
			$contentData['byAjax'] = true;	
						
			$view = $this->load->view("bills/bills_finder_suppliers_view",$contentData,true);
			
			$this->output->set_output($view); 										
		}
	}

	
	function searchSupplier() {
		if ($this->session->userdata('userLoggedIn')) {									
			
			$code = $this->input->post('code',true);
			
			$id = 0;
			if ($code != "") {				

				$parameters['activeFilter'] = 1;				
				$parameters['codeFilter'] = $code;
				$suppliers = $this->suppliers->getSuppliers($parameters);	
				if ($suppliers['totalRecords'] > 0) {
					$suppliers = $suppliers['list'][0];				
					
					$id = $suppliers['id'];
					$code = $suppliers['code'];
					$tradeName = str_replace('"',"",$suppliers['tradeName']);					
				}
			} 

			if ($id == 0) {
				$id = "0";
				$code = "";
				$tradeName = "";				
			}			                   

			$contentData['data'] = "";					
			$contentData['data'] .= '<input type="hidden" id="supplierId" name="supplierId" value="'.$id.'">';
			$contentData['data'] .= '<input type="hidden" id="supplierCode" name="suppsupplierlierCode" value="'.$code.'">';
			$contentData['data'] .= '<input type="hidden" id="supplierNameTrade" name="supplierNameTrade" value="'.$tradeName.'">';			
			
			$contentData['byAjax'] = true;	
						
			$view = $this->load->view("general_data_view",$contentData,true);
			
			$this->output->set_output($view); 		
		}
	}	

	function billItemData($detailBillId=-1) {
		if (!$this->my_application->hasPermission("Bills","See") || $detailBillId <= 0) {
			exit;
		} else {					
				
			$data = $this->bills->getDataByBillDetail($detailBillId);
			$contentData['data'] = $data['list'];				
															
			$contentData['byAjax'] = true;	
						
			$view = $this->load->view("bills/bills_itemData_view",$contentData,true);
			
			$this->output->set_output($view); 										
		}
	}
}

/* End of file Bills.php */
/* Location: ./apliccation/controllers/Bills.php */