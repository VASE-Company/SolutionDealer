<?php
/**
* Controlador Reports
*
*/

defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Reports extends CI_Controller {
    
    public function __construct() {
		parent::__construct();
		
	   	$this->output->set_template('default'); 
		$this->output->set_title($this->config->item('applicationName'));	
		$this->form_validation->set_error_delimiters(errorFormatStart(),errorFormatEnd());	
		$this->load->model('reports_model','reports');		
		$this->load->model('warehouses_model','warehouses');	
		$this->load->model('families_model','families');
		$this->load->model('companies_model','companies');
		$this->load->model('warehouses_model','warehouses');
		$this->load->model('sectors_model','sectors');
		$this->load->model('orders_model','orders');
		$this->load->js('assets/js/reports.js');	
		$this->load->js('assets/plugins/chart.js/chart.js');		
   	}
	
	function index(){		
		$this->listing();						
	}			

	function _getType() {
		$type = strtolower($this->input->get('type',TRUE));
		if ($type == "")  $type = strtolower($this->input->post('type',TRUE));

		return $type;
	}

	function _hasPermission() {
		$type = $this->_getType();

		switch ($type) {
			case "stock":									
				$hasPermission = $this->my_application->hasPermission("Reports","Stock");		
			break;

			case "general":									
				$hasPermission = $this->my_application->hasPermission("Reports","General");		
			break;

			case "estimatedpurchase":									
				$hasPermission = $this->my_application->hasPermission("Reports","EstimatedPurchase");						
			break;	

			case "partialdeliveries":
				$hasPermission = $this->my_application->hasPermission("Reports","PartialDeliveries");
			break;

			default:	
				$hasPermission = false;
			break;
		}

		return $hasPermission;
	}
		
	function listing()		
	{			
		if (!$this->session->userdata('userLoggedIn') || !$this->_hasPermission()) {						
			redirect('/main');			
		} else {						
			$type = $this->_getType();

			switch ($type) {
				case "stock":									
					$filters['warehouse'] = true;					
					$filters['family'] = true;						

					$articleStates[0] = array('id'=>'ALL','description'=>'Todos');	
					$articleStates[1] = array('id'=>'ACT','description'=>'Sólo Activos');											
					$contentData['articleStates'] = $articleStates;

					$stockTypes[0] = array('id'=>'REAL','description'=>'Stock Real');	
					$stockTypes[1] = array('id'=>'AVAILABLE','description'=>'Stock Disponible');	
					$stockTypes[2] = array('id'=>'OUTOFSTOCK','description'=>'Sotck Faltante');	
					$contentData['stockTypes'] = $stockTypes;	

					$haveStock[0] = array('id'=>'ALL','description'=>'Todos');	
					$haveStock[1] = array('id'=>'WV','description'=>'Sólo con Stock');	
					$contentData['haveStock'] = $haveStock;										
				
					$title = "Resumen de Stock";			
					$contentData['callback'] = 'intializeStockReport()';
					$contentData['filtersPath'] = "reports_stock_filters_view.php";					
				break;

				case "general":																								
					$filters['company'] = true;
					$filters['sector'] = true;
					
					$groupedBy[0] = array('id'=>'COM','description'=>'Empresa');											
					$groupedBy[1] = array('id'=>'BO','description'=>'Sucursal');											
					$groupedBy[2] = array('id'=>'SEC','description'=>'Sector');											
					$groupedBy[3] = array('id'=>'FAM','description'=>'Rubro');	
					$contentData['groupedBy'] = $groupedBy;		

					$subtypes[0] = array('id'=>'PRO','description'=>'Progresión');	
					$subtypes[1] = array('id'=>'TOT','description'=>'Totalizado');																									
					$contentData['subtypes'] = $subtypes;				
					
					$valueTypes[0] = array('id'=>'QUA','description'=>'Cantidad');	
					$valueTypes[1] = array('id'=>'AMO','description'=>'Importe');																									
					$contentData['valueTypes'] = $valueTypes;	

					$title = "Reporte General";			
					$contentData['callback'] = 'intializeGeneralReport()';
					$contentData['filtersPath'] = "reports_general_filters_view.php";
				break;

				case "estimatedpurchase":																								
					$filters['company'] = true;
					$filters['family'] = true;

					$articleStates[0] = array('id'=>'ALL','description'=>'Todos');	
					$articleStates[1] = array('id'=>'ACT','description'=>'Sólo Activos','selected'=>true);											
					$contentData['articleStates'] = $articleStates;

					$articleUsual[0] = array('id'=>'ALL','description'=>'Todos');	
					$articleUsual[1] = array('id'=>'USU','description'=>'Sí','selected'=>true);											
					$articleUsual[2] = array('id'=>'NOT','description'=>'No');											
					$contentData['articleUsual'] = $articleUsual;

					$contentData['dateFromFilter'] = getCurrentDate(false,null,-6);
					$contentData['dateToFilter'] = getCurrentDate(false);
					$contentData['estimatedDays'] = 90;
					$contentData['companyIdsFilter'] = "2|3|4|5|9";
															
					$title = "Reporte de Estimación de Compras";			
					$contentData['callback'] = 'intializeEstimatedPurchaseReport()';
					$contentData['filtersPath'] = "reports_estimatedpurchase_filters_view.php";
				break;			

				case "partialdeliveries":
					$filters['company'] = true;
					$filters['family'] = true;
					$filters['state'] = true;

					$title = "Control de Entregas";
					$contentData['callback'] = 'intializePartialDeliveriesReport()';
					$contentData['filtersPath'] = "reports_partialdeliveries_filters_view.php";
				break;

				default:	
					redirect('/main');
					return;
				break;
			}			

			if (isset($filters)) {
				if (isset($filters['warehouse']) && $filters['warehouse'] == true) {
					$parametersWarehouses = NULL;
					if (isset($filters['affectsStock']) && $filters['affectsStock'] == true) {
						$parametersWarehouses['affectsStockFilter'] = 1;
					}
					$warehouses = $this->warehouses->getWarehouses($parametersWarehouses);
					$contentData['warehouses'] = $warehouses["list"];				
				} 	

				if (isset($filters['family']) && $filters['family'] == true) {
					$parametersFamilies = NULL;
					if (isset($filters['affectsStock']) && $filters['affectsStock'] == true) {
						$parametersFamilies['affectsStockFilter'] = 1;
					}
					$families = $this->families->getFamilies($parametersFamilies);
					$contentData['families'] = $families["list"];				
				}

				if (isset($filters['company']) && $filters['company'] == true) {
					$companies = $this->companies->getCompanies();
					$contentData['companies'] = $companies["list"];				
				}

				if (isset($filters['sector']) && $filters['sector'] == true) {
					$sectors = $this->sectors->getSectors();
					$contentData['sectors'] = $sectors["list"];				
				}

				if (isset($filters['state']) && $filters['state'] == true) {
					$states = $this->orders->getStates();
					$contentData['states'] = $states["list"];
				}
			} 						
			
			$contentData['type'] = $type;

			switch ($type) {
				case "stock": $data['menuActive'] = "ReportStock"; break;
				case "general": $data['menuActive'] = "ReportGeneral"; break;
				case "estimatedpurchase": $data['menuActive'] = "ReportEstimatedPurchase"; break;
				case "partialdeliveries": $data['menuActive'] = "ReportPartialDeliveries"; break;
				default: $data['menuActive'] = "";
			}
			$data['title'] = $title;
			$data['contentView'] = 'reports/reports_view';
			$data['contentData'] = $contentData;
			$this->my_application->loadGeneralTemplate($data);	
		}
	}	

	function _getParameters($includeSelected=false) {	
				
		$data['type'] = array('getField'=>'type');
		$data['dateFromFilter'] = array('getField'=>'df');	
		$data['dateToFilter'] = array('getField'=>'dt');			
		$data['warehouseIdFilter'] = array('getField'=>'wh');			
		$data['familyIdFilter'] = array('getField'=>'fam');	
		$data['familyIdsFilterSelected'] = array('getField'=>'fams');	
		$data['articleFilter'] = array('getField'=>'art');	
		$data['haveStockFilter'] = array('getField'=>'hs');
		$data['stockTypeIdFilter'] = array('getField'=>'st');	
		$data['articleStateIdFilter'] = array('getField'=>'as');
		$data['articleUsualIdFilter'] = array('getField'=>'au');		
		$data['companyIdFilter'] = array('getField'=>'com');	
		$data['companyIdsFilterSelected'] = array('getField'=>'coms');	
		$data['branchOfficeIdFilter'] = array('getField'=>'bo');	
		$data['sectorIdFilter'] = array('getField'=>'sec');	
		$data['stateIdFilter'] = array('getField'=>'sta');
		// Control de Entregas: este parametro viaja por filtros, paginado y exportacion.
		$data['deliveryStateIdFilter'] = array('getField'=>'dsta');
		$data['groupedByFilter'] = array('getField'=>'grb');	
		$data['subtypeFilter'] = array('getField'=>'stype');	
		$data['valueTypeIdFilter'] = array('getField'=>'vt');	
		$data['estimatedDays'] = array('getField'=>'ed');	
		$data['fieldOrder'] = array('getField'=>'fOrd','type'=>'order');
		$data['typeOrder'] = array('getField'=>'tOrd','type'=>'order');

		foreach ($data as $key => $values) {
			if (!(isset($data[$key]['value']) && $data[$key]['value'] != "")) {
				$data[$key]['value'] = NULL;
				if ($this->input->post($key,TRUE) != "") {
					$data[$key]['value'] = $this->input->post($key,TRUE);
				} else {
					if (isset($values['getField']) && $values['getField'] != "") { 
						if ($this->input->get($values['getField'],TRUE) != "") {
							$data[$key]['value'] = $this->input->get($values['getField'],TRUE);
						}
					}
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
				$pageParameters[$values['getField']] = $values['value'];
			}
		}
				
		if (isset($parameters['type'])) {
			$parameters['type'] = strtolower($parameters['type']);
		}

		if ($includeSelected) {						
			$excludeIds  = $this->input->get_post('eIds');
			$selectedIds = $this->input->get_post('sIds');
			if (!empty($selectedIds)) {
				$parameters['selectedFilter'] = $selectedIds;
			} elseif (!empty($excludeIds)) { 
				$parameters['notSelectedFilter'] = $excludeIds;
			}			
		}

		$parametersdData['parameters'] = $parameters;	
		$parametersdData['filterGet'] = $filterGet;
		$parametersdData['orderGet'] = $orderGet;
		$parametersdData['pageParameters'] = $pageParameters;	
		
		return $parametersdData;	
	}

	function result($page=1) {
		if (!$this->session->userdata('userLoggedIn') || !$this->_hasPermission()) exit;		

		$parametersdData = $this->_getParameters();										
		$parameters = $parametersdData['parameters'];
		$parameters['page'] = $page;		

		if (isset($parameters['valueTypeIdFilter'])) $contentData['decimalValues'] = ($parameters['valueTypeIdFilter'] == "AMO");				
		if (isset($parameters['fieldOrder'])) $contentData['fieldOrder'] = $parameters['fieldOrder'];				
		if (isset($parameters['typeOrder'])) $contentData['typeOrder'] = $parameters['typeOrder'];		
		
		$contentData['filterGet'] = $parametersdData['filterGet'];				
		$contentData['filterExportGet'] = $parametersdData['filterGet'].$parametersdData['orderGet'];			
					
		$contentData['data'] = $this->_getData($parameters);												

		$contentData['allowExport'] = $this->my_application->hasPermission("Reports","Export");		
		$contentData['allowSeeBudgets'] = $this->my_application->hasPermission("Budgets","See");																
		$contentData['byAjax'] = true;	

		if (isset($contentData['data']["paginated"]) && $contentData['data']["paginated"] == true) {
			$pageConfiguration = getPageConfiguration(base_url().'reports/result',$this->config->item('recordsPerPage'),$contentData['data']["totalRecords"],$parametersdData['pageParameters']);
			$pageConfiguration['first_url'] = base_url().'reports/result?'.http_build_query($parametersdData['pageParameters'], '', "&");
			$this->pagination->initialize($pageConfiguration);
			$contentData['data']["pagination"] = convertPageToAjax(applyPageStyles($this->pagination->create_links()),'reloadResults');
		}
	

		$viewName = "reports/reports_".$parameters["type"];
		if (isset($parameters['subtypeFilter'])) {
			switch ($parameters['subtypeFilter']) {
				case "PRO": $viewName .= "_progression"; break;
				case "TOT": $viewName .= "_totalized"; break;
			}
		}
		$viewName .= "_view";
		$view = $this->load->view($viewName,$contentData,true);			
		$this->output->set_output($view);	
	}

	function _getData($parameters) {

		$data = NULL;

		switch ($parameters["type"]) {
			case "stock": 									
				$data = $this->_getDataStock($parameters);
			break;

			case "general": 
				$data = $this->_getDataGeneral($parameters);
			break;

			case "estimatedpurchase": 
				$data = $this->_getDataEstimatedPurchase($parameters);
			break;	

			case "partialdeliveries":
				$data = $this->_getDataPartialDeliveries($parameters);
			break;
		}

		return $data;
	}	

	function _getDataStock($parameters) {

		if (!(isset($parameters['export']) && $parameters['export'] == true)) {
			$parameters['articlesLimit'] = 50;				
		}
		if (isset($parameters['articleStateIdFilter']) && trim($parameters['articleStateIdFilter']) == "ACT") {
			$parameters['articleActiveFilter'] = "1";
		}
		if (isset($parameters['stockTypeIdFilter']) && trim($parameters['stockTypeIdFilter']) != "") {
			$parameters['typeStockFilter'] = $parameters['stockTypeIdFilter'];
			$data['typeStock'] = $parameters['typeStockFilter'];

			if ($parameters['typeStockFilter'] == "OUTOFSTOCK") {
				$data['classWithValue'] = "outOfStock";
			}
		}
		if (isset($parameters['haveStockFilter']) && trim($parameters['haveStockFilter']) != "") {
			$parameters['stockNotZeroFilter'] = ($parameters['haveStockFilter'] == "WV");
		}				

		$auxData = $this->reports->getSummaryStock($parameters);
		$data['data'] = $auxData['list'];	

		$warehousesParameters["affectsStockFilter"] = 1;
		if (isset($parameters['warehouseIdFilter']) && $parameters['warehouseIdFilter'] > 0) {					
			$warehousesParameters["idFilter"] = $parameters['warehouseIdFilter'];	

			if (isset($parameters['typeStockFilter']) && $parameters['typeStockFilter'] != "OUTOFSTOCK") {
				$data['showLocation'] = true;						
			}
			if (isset($parameters['typeStockFilter']) && $parameters['typeStockFilter'] == "REAL") {					
				$data['showUnitPrice'] = true;
			}					
		}
		$warehouses = $this->warehouses->getWarehouses($warehousesParameters);
		$data['warehouses'] = $warehouses["list"];

		return $data;
	}

	function _getDataGeneral($parameters) {
		
		$data = $this->reports->getGeneralReport($parameters);		

		return $data;
	}

	function _getDataPartialDeliveries($parameters) {

		if (!(isset($parameters['export']) && $parameters['export'] == true)) {
			$parameters['recordsPerPage'] = $this->config->item('recordsPerPage');
		}

		$partialDeliveries = $this->reports->getPartialDeliveries($parameters);
		$data['data'] = $partialDeliveries['list'];
		$data['totalRecords'] = $partialDeliveries['totalRecords'];
		$data['paginated'] = true;

		return $data;
	}

	function export()
	{							
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("Reports","Export") || !$this->_hasPermission()) exit;													

		$parametersdData = $this->_getParameters(true);										
		$parameters = $parametersdData['parameters'];		
		$parameters['export'] = true;
		$data = $this->_getData($parameters);

		switch ($parameters["type"]) {
			case "stock": 					
				$this->_exportStock($data);
			break;

			case "general": 									
				if ($parameters['subtypeFilter'] == 'PRO') $this->_exportProgressionGeneral($data);				
				if ($parameters['subtypeFilter'] == 'TOT') $this->_exportTotalizedGeneral($data);								
			break;

			case "estimatedpurchase": 					
				$parametersDescription = $this->_getParametersDescription($parameters);
								
				$this->_exportEstimatedPurchase($data, $parametersDescription);
			break;

			case "partialdeliveries":
				$this->_exportPartialDeliveries($data);
			break;
		}
    }

    function _exportStock($data) {

    	$warehouses = $data['warehouses'];
    	$showLocation = (isset($data['showLocation']) && $data['showLocation'] == true);  
    	$showUnitPrice = (isset($data['showUnitPrice']) && $data['showUnitPrice'] == true);  
    	$typeStock = (isset($data['typeStock'])?$data['typeStock']:"");  
   		$data = $data['data'];   	

		// INITIALITE SPREADSHEET
				
		$spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Resumen de Stock');
		
        // TITLE
        $title = "RESUMEN DE STOCK";        
    	switch ($typeStock) {
    		case 'REAL':
    			$title .= ": Stock Real";
    		break;
    		case 'AVAILABLE':
    			$title .= ": Stock Disponible";
    		break;
    		case 'OUTOFSTOCK':
    			$title .= ": Stock Faltante";
    		break;
    	}        		
        $row = 1;
        $sheet->setCellValue('A'.$row,$title); 
        $styleCell = array('font'=>array('bold'=>true,'size'=>16));
        $sheet->getStyle('A'.$row)->applyFromArray($styleCell);
        $sheet->mergeCells('A'.$row.':'.'D'.$row);
        
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
        if ($showLocation) {
        	$col++;
        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Ubicación");   
        }             
        if (isset($warehouses)) {                                                     
        	for ($i=0; $i < count($warehouses); $i++) {                          
        		$col++;
        		$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$warehouses[$i]['description']);                                                                       
			}
        }
        if ($showUnitPrice) {
        	$col++;
        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Costo Histórico");   
        	$col++;
        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Costo Actualizado");   
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
		if (isset($data)) {
			$i = 0;
                                
            while ($i < count($data)) {

                $articleStock['id'] = $data[$i]['id'];
                $articleStock['code'] = $data[$i]['code'];
                $articleStock['description'] = $data[$i]['description'];
                $articleStock['familyDescription'] = $data[$i]['familyDescription'];
                if ($showLocation) { 
                    if (trim($data[$i]['corridor']) != "") {
                        if ($articleStock['location'] != "") $articleStock['location'] .= " - ";
                        $articleStock['location'] .= trim($data[$i]['corridor']);
                    }
                    if (trim($data[$i]['shelf']) != "") {
                        if ($articleStock['location'] != "") $articleStock['location'] .= " - ";
                        $articleStock['location'] .= trim($data[$i]['shelf']);
                    }
                    if ($articleStock['location'] == "") $articleStock['location'] = "-";
                }   
                $articleStock['unitPrice'] = (float)$data[$i]['unitPrice'];                                       
                $articleStock['lastUnitPrice'] = (float)$data[$i]['lastUnitPrice']; 

                for ($j=0; $j < count($warehouses); $j++) {      
                    $articleStock['stocks'][$warehouses[$j]['id']] = 0; 
                }

                while ($i < count($data) && $data[$i]['id'] == $articleStock['id']) {
                    $articleStock['stocks'][$data[$i]['warehouseId']] = $data[$i]['stock'];

                    $i++;
                }

                $warehouseId = (count($warehouses) == 1?$warehouses[0]['id']:-1); 
                $articleStock['totalPrice'] = ($warehouseId > 0?(int)$articleStock['stocks'][$warehouseId] * (float)$articleStock['unitPrice']:0);                                
                $articleStock['lastUnitPrice'] = ($warehouseId > 0?(int)$articleStock['stocks'][$warehouseId] * (float)$articleStock['lastUnitPrice']:0);                                

                $row++;
	        	$col = 0;

	        	$col++;
	        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$articleStock['code']); 
	        	$col++;
	        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$articleStock['description']); 
	        	$col++;
	        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$articleStock['familyDescription']); 
	        	if ($showLocation) {
	        		$col++;
	        		$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$articleStock['location']); 
	        	}	        	
	        	for ($j=0; $j < count($warehouses); $j++) { 
	        		$col++;
	        		$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$articleStock['stocks'][$warehouses[$j]['id']]);
	        	}
                if ($showUnitPrice) {
	        		$col++;
	        		$sheet->setCellValue(getLetterOfExcelColumn($col).$row,decimalFormat($articleStock['totalPrice'],2)); 
	        		$col++;
	        		$sheet->setCellValue(getLetterOfExcelColumn($col).$row,decimalFormat($articleStock['lastUnitPrice'],2)); 
	        	}
            } 
		}

		for ($i=1; $i <= $col; $i++) {
			$sheet->getColumnDimension(getLetterOfExcelColumn($i))->setAutoSize(true);
		}	

		// CREATE TEMP FILE EXCEL, DOWNLOAD Y DELETE 

        $writer = new Xlsx($spreadsheet); 

		$folder = $this->config->item('files').'/tmp/';
		 
		$filename = 'resumen_de_stock_'.getCurrentDateId().'.xlsx';
		 
		$writer->save($folder.$filename); 

		$this->load->helper('download');
							
		$fileContent = file_get_contents($folder.$filename);	

		@unlink($folder.$filename);				
							
		force_download($filename,$fileContent);
	}

	function _exportProgressionGeneral($data) {	 	
	    	
	    $xAxis = ((isset($data) && isset($data['xAxis']))?$data['xAxis']:array()); 
	    $yAxis = ((isset($data) && isset($data['yAxis']))?$data['yAxis']:array());     
	    $values = ((isset($data) && isset($data['values']))?$data['values']:array());       
	    $yAxisTitle = ((isset($data) && isset($data['yAxisTitle']))?$data['yAxisTitle']:"");   	

		// INITIALITE SPREADSHEET
				
		$spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Reporte');
		
        // TITLE
        $title = "REPORTE GENERAL DE PROGRESIÓN";            	      	
        $row = 1;
        $sheet->setCellValue('A'.$row,$title); 
        $styleCell = array('font'=>array('bold'=>true,'size'=>16));
        $sheet->getStyle('A'.$row)->applyFromArray($styleCell);
        $sheet->mergeCells('A'.$row.':'.'F'.$row);
        
        // GRID HEADERS        
        $col = 0;
        $row++;        

        $row++;
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$yAxisTitle); 
        for ($x=0; $x < count($xAxis); $x++) {
        	$xAxis[$x]['total'] = 0;
        	$col++;
        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$xAxis[$x]['description']); 
        }
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Total");                
                            		         
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
		$firstRow = $row + 1;
		for ($y=0; $y < count($yAxis); $y++) {
			$row++;
	        $col = 0;

			$col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$yAxis[$y]['description']); 

            $rowTotal = 0;
            for ($x=0; $x < count($xAxis); $x++) {
                $value = 0;
                if (isset($values[$yAxis[$y]['id']][(int)$xAxis[$x]['month']][(int)$xAxis[$x]['year']])) {
                    $value = $values[$yAxis[$y]['id']][(int)$xAxis[$x]['month']][(int)$xAxis[$x]['year']];                                    
                }
                $xAxis[$x]['total'] += (float)$value;
                $rowTotal += (float)$value;
                if (isset($decimalValues) && $decimalValues) {
                    $value = decimalFormat((float)$value);
                } else {
                    $value = (int)$value;
                }    
                $col++;                                                                                 		
        		$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$value);                                    
        	}

        	if (isset($decimalValues) && $decimalValues) {
                $rowTotal = decimalFormat((float)$rowTotal);
            } else {
                $rowTotal = (int)$rowTotal;
            } 
            $col++;                                                                                 		
        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$rowTotal);  
        }   
         
        $row++;
        $col = 0;

        $col++;
	    $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Total"); 
                   
        $rowTotal = 0;
        for ($x=0; $x < count($xAxis); $x++) {                                    
            $rowTotal += $xAxis[$x]['total'];
            if (isset($decimalValues) && $decimalValues) {
                $total = decimalFormat((float)$xAxis[$x]['total']);
            } else {
                $total = (int)$xAxis[$x]['total'];
            }                                      
			
			$col++;                                                                                 		
        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$total);    		                                     	           
    	} 

    	if (isset($decimalValues) && $decimalValues) {
            $rowTotal = decimalFormat((float)$rowTotal);
        } else {
            $rowTotal = (int)$rowTotal;
        }
        $col++;                                                                                 		
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$rowTotal);      

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

		$sheet->getStyle('A'.$row.":".'A'.$row)->applyFromArray($styleCell);

		$styleCell = array(				            
				            'font' => array(
				            	'bold'=>true
				            	)
					        );

		$sheet->getStyle('B'.$row.":".getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);	
		$sheet->getStyle(getLetterOfExcelColumn($col).$firstRow.":".getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);		

		for ($i=1; $i <= $col; $i++) {
			$sheet->getColumnDimension(getLetterOfExcelColumn($i))->setAutoSize(true);
		}	

		// CREATE TEMP FILE EXCEL, DOWNLOAD Y DELETE 

        $writer = new Xlsx($spreadsheet); 

		$folder = $this->config->item('files').'/tmp/';
		 
		$filename = 'reporte_progresion_'.getCurrentDateId().'.xlsx';
		 
		$writer->save($folder.$filename); 

		$this->load->helper('download');
							
		$fileContent = file_get_contents($folder.$filename);	

		@unlink($folder.$filename);				
							
		force_download($filename,$fileContent);
	}

	function _exportTotalizedGeneral($data) {	 	
	    		    
	    $yAxis = ((isset($data) && isset($data['yAxis']))?$data['yAxis']:array());     
	    $values = ((isset($data) && isset($data['values']))?$data['values']:array());       
	    $yAxisTitle = ((isset($data) && isset($data['yAxisTitle']))?$data['yAxisTitle']:"");   	
	    $total = 0; 

		// INITIALITE SPREADSHEET
				
		$spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Reporte');
		
        // TITLE
        $title = "REPORTE GENERAL TOTALIZADO";            	      	
        $row = 1;
        $sheet->setCellValue('A'.$row,$title); 
        $styleCell = array('font'=>array('bold'=>true,'size'=>16));
        $sheet->getStyle('A'.$row)->applyFromArray($styleCell);
        $sheet->mergeCells('A'.$row.':'.'F'.$row);
        
        // GRID HEADERS        
        $col = 0;
        $row++;        

        $row++;
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$yAxisTitle);    
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Total");                
                            		         
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
		$firstRow = $row + 1;
		for ($y=0; $y < count($yAxis); $y++) {
			$row++;
	        $col = 0;

			$col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$yAxis[$y]['description']); 
	        $value = 0;
            if (isset($values[$yAxis[$y]['id']])) {
                $value = $values[$yAxis[$y]['id']];                                    
            }
            $total += (float)$value;
            if (isset($decimalValues) && $decimalValues) {
                $value = decimalFormat((float)$value);
            } else {
                $value = (int)$value;
            }  
            $col++;                                                                                 		
        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$value);  
        }   
         
        $row++;
        $col = 0;

        $col++;
	    $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Total General"); 
	    if (isset($decimalValues) && $decimalValues) {
            $total = decimalFormat((float)$total);
        } else {
            $total = (int)$total;
        }  
        $col++;                                                                                 		
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$total);      

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

		$sheet->getStyle('A'.$row.":".'A'.$row)->applyFromArray($styleCell);

		$styleCell = array(				            
				            'font' => array(
				            	'bold'=>true
				            	)
					        );

		$sheet->getStyle('B'.$row.":".getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);			

		for ($i=1; $i <= $col; $i++) {
			$sheet->getColumnDimension(getLetterOfExcelColumn($i))->setAutoSize(true);
		}	

		// CREATE TEMP FILE EXCEL, DOWNLOAD Y DELETE 

        $writer = new Xlsx($spreadsheet); 

		$folder = $this->config->item('files').'/tmp/';
		 
		$filename = 'reporte_totalizado_'.getCurrentDateId().'.xlsx';
		 
		$writer->save($folder.$filename); 

		$this->load->helper('download');
							
		$fileContent = file_get_contents($folder.$filename);	

		@unlink($folder.$filename);				
							
		force_download($filename,$fileContent);
	}

	function _exportPartialDeliveries($data) {

		$data = $data['data'];

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setTitle('Control Entregas');

		$row = 1;
		$sheet->setCellValue('A'.$row,"CONTROL DE ENTREGAS");
		$styleCell = array('font'=>array('bold'=>true,'size'=>16));
		$sheet->getStyle('A'.$row)->applyFromArray($styleCell);
		// El export queda alineado con la grilla: se retiro la columna Remitos.
		$sheet->mergeCells('A'.$row.':'.'N'.$row);

		$headers = array(
			"Pedido",
			"Fecha Pedido",
			"Empresa",
			"Sucursal",
			"Rubro",
			"Artículo",
			"Plazo",
			"Fecha Plazo",
			"Demora",
			"Estado Pedido",
			"Estado Entrega",
			"Cantidad",
			"Situación",
			"N° Orden",
		);

		$row = 3;
		for ($i=0; $i < count($headers); $i++) {
			$sheet->setCellValue(getLetterOfExcelColumn($i+1).$row,$headers[$i]);
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

		$sheet->getStyle('A'.$row.":".getLetterOfExcelColumn(count($headers)).$row)->applyFromArray($styleCell);

		if (isset($data)) {
			for ($i=0; $i < count($data); $i++) {
				$quantityDescription = (int)$data[$i]['deliveredQuantity']."/".(int)$data[$i]['requestedQuantity'];
				$maximumDate = (trim($data[$i]['maximumDate']) != ""?dateFormat($data[$i]['maximumDate'],false):"");

				$row++;
				$col = 0;

				$col++;
				$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$data[$i]['orderId']);
				$col++;
				$sheet->setCellValue(getLetterOfExcelColumn($col).$row,dateFormat($data[$i]['orderDate'],false));
				$col++;
				$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$data[$i]['companyDescription']);
				$col++;
				$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$data[$i]['branchOfficeDescription']);
				$col++;
				$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$data[$i]['familyDescription']);
				$col++;
				$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$data[$i]['articleDescription']);
				$col++;
				$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$data[$i]['maximumDays']);
				$col++;
				$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$maximumDate);
				$col++;
				$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$data[$i]['delayDescription']);
				$col++;
				$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$data[$i]['stateDescription']);
				$col++;
				$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$data[$i]['deliveryStateDescription']);
				$col++;
				$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$quantityDescription);
				$col++;
				$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$data[$i]['dueSituationDescription']);
				$col++;
				$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$data[$i]['purchaseOrderNumber']);
			}
		}

		for ($i=1; $i <= count($headers); $i++) {
			$sheet->getColumnDimension(getLetterOfExcelColumn($i))->setAutoSize(true);
		}

        $writer = new Xlsx($spreadsheet);

		$folder = $this->config->item('files').'/tmp/';
		$filename = 'control_entregas_'.getCurrentDateId().'.xlsx';

		$writer->save($folder.$filename);

		$this->load->helper('download');
		$fileContent = file_get_contents($folder.$filename);
		@unlink($folder.$filename);
		force_download($filename,$fileContent);
	}

	function graph() {
		if (!$this->session->userdata('userLoggedIn')) exit;							

		$contentData['byAjax'] = true;	
					
		$view = $this->load->view("reports/reports_graph_view",$contentData,true);
		
		$this->output->set_output($view); 										

	}

	function _getDataEstimatedPurchase($parameters) {
				
		if (isset($parameters['articleStateIdFilter']) && trim($parameters['articleStateIdFilter']) == "ACT") {
			$parameters['articleActiveFilter'] = "1";
		}
		if (isset($parameters['articleUsualIdFilter']) && trim($parameters['articleUsualIdFilter']) != "ALL") {
			if (trim($parameters['articleUsualIdFilter']) == "USU") $parameters['articleUsualFilter'] = "1";			
			if (trim($parameters['articleUsualIdFilter']) == "NOT") $parameters['articleUsualFilter'] = "0";			
		}
		if (isset($parameters['companyIdsFilterSelected'])) {
			$parameters['companyIdsFilter'] = $parameters['companyIdsFilterSelected'];			
		}
		if (isset($parameters['familyIdsFilterSelected'])) {
			$parameters['familyIdsFilter'] = $parameters['familyIdsFilterSelected'];			
		}		
		if (isset($parameters['estimatedDays'])) {
			$parameters['estimatedDays'] = (int)$parameters['estimatedDays'];			
		}						
		$auxData = $this->reports->getEstimatedPurchase($parameters);
		$data['data'] = $auxData['list'];	
		$data['totalRecords'] = $auxData['totalRecords'];
		$data['paginated'] = true;		

		$warehousesParameters['activeFilter'] = 1;		
		if (isset($parameters['companyIdsFilterSelected'])) {
			$warehousesParameters['companyIdFilter'] = str_replace("|",",",$parameters['companyIdsFilterSelected']);			
		}
		$warehouses = $this->warehouses->getWarehouses($warehousesParameters);
		$data['warehouses'] = $warehouses["list"];							

		return $data;
	}
	
	function _exportEstimatedPurchase($data, $parametersDescription=NULL) {

    	$warehouses = $data['warehouses'];  
    	$data = $data['data'];	    	

		// INITIALITE SPREADSHEET
				
		$spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Reporte');
		
        // TITLE
        $title = "REPORTE DE ESTIMACIÓN DE COMPRAS";        
    	        		
        $row = 1;
        $sheet->setCellValue('A'.$row,$title); 
        $styleCell = array('font'=>array('bold'=>true,'size'=>16));
        $sheet->getStyle('A'.$row)->applyFromArray($styleCell);
        $sheet->mergeCells('A'.$row.':'.'H'.$row);

        // PARAMETERS        
        $styleCell = array('font'=>array('bold'=>true));

        $row++;
    	$sheet->setCellValue('A'.$row,"Fecha Reporte:"); 
    	$sheet->setCellValue('B'.$row,getCurrentDateSpanish(false));
    	$sheet->getStyle('A'.$row)->applyFromArray($styleCell);
    	$sheet->mergeCells('B'.$row.':'.'E'.$row);

        for ($i=0; $i < count($parametersDescription); $i++) {
        	$row++;
        	$sheet->setCellValue('A'.$row,$parametersDescription[$i]['label']); 
        	$sheet->setCellValue('B'.$row,$parametersDescription[$i]['data']);
        	$sheet->getStyle('A'.$row)->applyFromArray($styleCell);
        	$sheet->mergeCells('B'.$row.':'.'E'.$row);
        }
        
        // GRID HEADERS        
        $row++;
        $col = 0; 
        
        $col = 0;
        $row++;
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Código"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Descripción");         
        if (isset($warehouses)) { 
            for($i=0; $i < count($warehouses); $i++) {                                                        
            	$col++;
	        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$warehouses[$i]['description']);          
            }
	        if (count($warehouses) > 1) {        
	        	$col++;
	        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Total a Comprar");          
	        }
	    }
                                     		         
		$styleCell = array(
				            'fill' => array(
				                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				                'startColor' => array('argb' => 'FFA9A9A9')
				            	),
				            'font' => array(
				            	'bold'=>true,
				            	'color' => array('argb' => '00000000')
				            	),
				            'alignment' => array(
							        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
							        'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
							    )
					        );

		$sheet->getStyle('A'.$row.":".getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);		

		// GRID BODY
		if (isset($data)) {
			for ($i=0; $i < count($data); $i++) { 
				$col = 0;
		        $row++;
		        
		        $col++;
		        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$data[$i]['code']); 
		        $col++;
		        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$data[$i]['description']); 
		        if (isset($warehouses)) { 
		            for($j=0; $j< count($warehouses); $j++) {                                                        
		            	if (isset($data[$i]['quantityToBuy'.$warehouses[$j]['id']])) {
                            $quantityToBuy = (int)$data[$i]['quantityToBuy'.$warehouses[$j]['id']];                                            
                            if ($quantityToBuy < 0) $quantityToBuy = 0;
                        } else {                                            
                            $quantityToBuy = 0;                                            
                        } 
		            	$col++;
			        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$quantityToBuy);          
		            }
			        if (count($warehouses) > 1) {        
			        	$col++;
		        		$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$data[$i]['quantityToBuy']); 		        
			        }
			    }		        
			}
		}

		for ($i=1; $i <= $col; $i++) {
			$sheet->getColumnDimension(getLetterOfExcelColumn($i))->setAutoSize(true);
		}	

		// CREATE TEMP FILE EXCEL, DOWNLOAD Y DELETE 

        $writer = new Xlsx($spreadsheet); 

		$folder = $this->config->item('files').'/tmp/';
		 
		$filename = 'reporte_estimación_compras_'.getCurrentDateId().'.xlsx';
		 
		$writer->save($folder.$filename); 

		$this->load->helper('download');
							
		$fileContent = file_get_contents($folder.$filename);	

		@unlink($folder.$filename);				
							
		force_download($filename,$fileContent);
	}

	function _getParametersDescription($parameters=null) {
		
		$parametersDescription = array();			
		/*
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
		*/
		return $parametersDescription;
	}

}

/* End of file Reports.php */
/* Location: ./apliccation/controllers/Reports.php */
