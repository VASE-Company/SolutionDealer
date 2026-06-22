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
		$this->load->model('sectors_model','sectors');
		$this->load->js('assets/js/reports.js');	
		$this->load->js('assets/plugins/chart.js/chart.js');		
   	}
	
	function index(){		
		$this->listing();						
	}			

	function _hasPermission() {
		$type = strtolower($this->input->get('type',TRUE));
		if ($type == "")  $type = strtolower($this->input->post('type',TRUE));

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
			$type = strtolower($this->input->get('type',TRUE));

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
					$articleStates[1] = array('id'=>'ACT','description'=>'Sólo Activos');											
					$contentData['articleStates'] = $articleStates;
															
					$title = "Reporte de Estimación de Compras";			
					$contentData['callback'] = 'intializeEstimatedPurchaseReport()';
					$contentData['filtersPath'] = "reports_estimatedPurchase_filters_view.php";
				break;			

				default:	
					$hasPermission = false;					
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
			} 						
			
			$contentData['type'] = $type;

			$data['menuActive'] = "ReportsStock";
			$data['title'] = $title;
			$data['contentView'] = 'reports/reports_view';
			$data['contentData'] = $contentData;
			$this->my_application->loadGeneralTemplate($data);	
		}
	}	

	function _getParameters() {	
				
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
		$data['companyIdFilter'] = array('getField'=>'com');	
		$data['companyIdsFilterSelected'] = array('getField'=>'coms');	
		$data['branchOfficeIdFilter'] = array('getField'=>'bo');	
		$data['sectorIdFilter'] = array('getField'=>'sec');	
		$data['groupedByFilter'] = array('getField'=>'grb');	
		$data['subtypeFilter'] = array('getField'=>'stype');	
		$data['valueTypeIdFilter'] = array('getField'=>'vt');			
		$data['estimatedDays'] = array('getField'=>'ed');			

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
		$filterExportGet = "";
		
		foreach ($data as $key => $values) {
			$parameters[$key] = $values['value'];
			if (isset($values['getField']) && $values['getField'] != "" &&				
		        $values['value'] != NULL && $values['value'] != "") { 	
		    	$filterExportGet .= "&".$values['getField']."=".$values['value'];	
			}
		}

		$parameters = array_filter($parameters, function ($value) {
					    return $value !== null && $value !== '';
					});

		$parametersdData['parameters'] = $parameters;	
		$parametersdData['filterExportGet'] = $filterExportGet;	
		
		return $parametersdData;	
	}

	function result() {
		if (!$this->session->userdata('userLoggedIn') || !$this->_hasPermission()) exit;		

		$parametersdData = $this->_getParameters();										
		$parameters = $parametersdData['parameters'];

		if (isset($parameters['valueTypeIdFilter'])) {
			$contentData['decimalValues'] = ($parameters['valueTypeIdFilter'] == "AMO");
		}
						
		$contentData['filterExportGet'] = $parametersdData['filterExportGet'];			
					
		$contentData['data'] = $this->_getData($parameters);												

		$contentData['allowExport'] = $this->my_application->hasPermission("Reports","Export");		
		$contentData['allowSeeBudgets'] = $this->my_application->hasPermission("Budgets","See");																
		$contentData['byAjax'] = true;	

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

	function export()
	{							
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("Reports","Export") || !$this->_hasPermission()) exit;											
				
		$parametersdData = $this->_getParameters();										
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
				$this->_exportEstimatedPurchase($data);
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

	function graph() {
		if (!$this->session->userdata('userLoggedIn')) exit;							

		$contentData['byAjax'] = true;	
					
		$view = $this->load->view("reports/reports_graph_view",$contentData,true);
		
		$this->output->set_output($view); 										

	}

	function _getDataEstimatedPurchase($parameters) {
		
		if (!(isset($parameters['export']) && $parameters['export'] == true)) {
			$parameters['limit'] = 50;				
		}		
		if (isset($parameters['articleStateIdFilter']) && trim($parameters['articleStateIdFilter']) == "ACT") {
			$parameters['articleActiveFilter'] = "1";
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

		$companiesParameters = null;
		if (isset($parameters['companyIdsFilterSelected'])) {
			$companiesParameters['idsFilter'] = $parameters['companyIdsFilterSelected'];			
		}
		$companies = $this->companies->getCompanies($companiesParameters);
		$data['companies'] = $companies["list"];							

		return $data;
	}

	function _exportEstimatedPurchase($data) {

    	$companies = $data['companies'];
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
        
        // GRID HEADERS        
        $row++;
        $col = 0; 


        $row++;    
        $col++;      
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Artículo"); 
        $sheet->mergeCells(getLetterOfExcelColumn($col).$row.':'.getLetterOfExcelColumn($col+2).$row);
        $col += 2;
        if (isset($companies)) { 
            for ($i=0; $i < count($companies); $i++) {
           		$col++;      
        		$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$companies[$i]['description']); 
        		$sheet->mergeCells(getLetterOfExcelColumn($col).$row.':'.getLetterOfExcelColumn($col+2).$row);
        		$col += 2;
           	}
            if (count($companies) > 1) {
            	$col++;
            	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,"General");             	
            }
		}
		$styleCell = array(
				            'fill' => array(
				                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				                'startColor' => array('argb' => '00000000')
				            	),
				            'font' => array(
				            	'bold'=>true,
				            	'color' => array('argb' => 'FFFFFFFF')
				            	),
				            'alignment' => array(
							        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
							        'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
							    )
					        );

		$sheet->getStyle('A'.$row.":".getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);		

        $col = 0;
        $row++;
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Código"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Descripción"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Rubro");   
        if (isset($companies)) { 
            for ($i=0; $i < count($companies); $i++) {
           		$col++;
            	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Stock");             	
            	$col++;
            	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Estimado");             	
            	$col++;
            	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Comprar");             	
           	}
            if (count($companies) > 1) {            	
            	$col++;
            	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Total Comprar");             	
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
		        $col++;
		        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$data[$i]['familyDescription']); 
		        if (isset($companies)) { 
                    for($j=0; $j < count($companies); $j++) {                                                                                            
                        if (isset($data[$i]['companies'][$companies[$j]['id']])) {
                            $stock = $data[$i]['companies'][$companies[$j]['id']]['stock'];
                            $estimatedQuantity = $data[$i]['companies'][$companies[$j]['id']]['estimatedQuantity'];
                            $quantityToBuy = $data[$i]['companies'][$companies[$j]['id']]['quantityToBuy'];
                        } else {
                            $stock = 0;
                            $estimatedQuantity = 0;
                            $quantityToBuy = 0;
                        }  
                        $col++;
				        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$stock); 
				        $col++;
				        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$estimatedQuantity); 
				        $col++;
				        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$quantityToBuy); 
                    }
                    if (count($companies) > 1) {
                        if (isset($data[$i]['companies'][0])) {                            
                            $quantityToBuy = $data[$i]['companies'][0]['quantityToBuy'];
                        } else {                            
                            $quantityToBuy = 0;
                        }                        
				        $col++;
				        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$quantityToBuy); 
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
}

/* End of file Reports.php */
/* Location: ./apliccation/controllers/Reports.php */