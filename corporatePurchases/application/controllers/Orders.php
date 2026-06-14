<?php
/**
* Controlador Orders
*
*/

defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Orders extends CI_Controller {
    
    public function __construct() {
		parent::__construct();
		
	   	$this->output->set_template('default'); 
		$this->output->set_title($this->config->item('applicationName'));	
		$this->form_validation->set_error_delimiters(errorFormatStart(),errorFormatEnd());	
		$this->load->model('orders_model','orders');		
		$this->load->model('users_model','users');
		$this->load->model('companies_model','companies');		
		$this->load->model('branchoffices_model','branchOffices');		
		$this->load->model('sectors_model','sectors');			
		$this->load->model('articles_model','articles');		
		$this->load->model('families_model','families');		
		$this->load->model('notifications_model','notifications');		
		$this->load->js('assets/js/orders.js');				
   	}
	
	function index(){		
		$this->listing();						
	}

	function _getParameters($page=NULL,
		                    $idFilter=NULL,$numberFilter=NULL,
					 		$dateFromFilter=NULL,$dateToFilter=NULL,
					 		//$companyIdFilter=NULL, $branchOfficeIdFilter=NULL, $sectorIdFilter=NULL,
		             		$stateIdFilter=NULL,
		             		//$userIdFilter=NULL,$authorizingUserIdFilter=NULL,$managerIdFilter=NULL,
		             		$priorityIdFilter=NULL,$articleFilter=NULL,$subjectFilter=NULL,
		             		$companyIdsFilter=NULL,$managerIdsFilter=NULL,$familyIdsFilter=NULL,
		             		$fieldOrder=NULL,$typeOrder=NULL) {

		//By default type=null (get) and notPagination=false		
		$data['page'] = array('value'=>$page);		
		$data['idFilter'] = array('getField'=>'id','value'=>$idFilter);	
		$data['numberFilter'] = array('getField'=>'num','value'=>$numberFilter);
		$data['dateFromFilter'] = array('getField'=>'df','value'=>$dateFromFilter);	
		$data['dateToFilter'] = array('getField'=>'dt','value'=>$dateToFilter);	
		//$data['companyIdFilter'] = array('getField'=>'com','value'=>$companyIdFilter);	
		//$data['branchOfficeIdFilter'] = array('getField'=>'bo','value'=>$branchOfficeIdFilter);		
		//$data['sectorIdFilter'] = array('getField'=>'sec','value'=>$sectorIdFilter);	
		$data['stateIdFilter'] = array('getField'=>'sta','value'=>$stateIdFilter);								
		//$data['userIdFilter'] = array('getField'=>'user','value'=>$userIdFilter);	
		//$data['authorizingUserIdFilter'] = array('getField'=>'aut','value'=>$authorizingUserIdFilter);		
		//$data['managerIdFilter'] = array('getField'=>'man','value'=>$managerIdFilter);		
		$data['priorityIdFilter'] = array('getField'=>'pri','value'=>$priorityIdFilter);		
		$data['articleFilter'] = array('getField'=>'art','value'=>$articleFilter);				
		$data['subjectFilter'] = array('getField'=>'sub','value'=>$subjectFilter);			
		$data['companyIdsFilter'] = array('getField'=>'com','value'=>$companyIdsFilter);	
		$data['managerIdsFilter'] = array('getField'=>'man','value'=>$managerIdsFilter);	
		$data['familyIdsFilter'] = array('getField'=>'fam','value'=>$familyIdsFilter);	
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
		             //$companyIdFilter=NULL, $branchOfficeIdFilter=NULL, $sectorIdFilter=NULL,
             		 $stateIdFilter=NULL,
             		 //$userIdFilter=NULL,$authorizingUserIdFilter=NULL,$managerIdFilter=NULL,
             		 $priorityIdFilter=NULL,$articleFilter=NULL,$subjectFilter=NULL,
             		 $companyIdsFilter='all',$managerIdsFilter='all',$familyIdsFilter='all',
		             $fieldOrder='id',$typeOrder='desc')
	{							
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("Orders","See")) {						
			redirect('/main/logout');			
		} else {
			if ($fieldOrder=="") $fieldOrder = "id";
			if ($typeOrder=="") $typeOrder = "desc";
											
			$contentData['allowInsert'] = $this->my_application->hasPermission("Orders","Insert");						
			$contentData['allowEdit'] = $this->my_application->hasPermission("Orders","Edit");
			$contentData['allowDelete'] = $this->my_application->hasPermission("Orders","Delete");			
			$contentData['allowExport'] = $this->my_application->hasPermission("Orders","Export");						
			$contentData['allowFullExport'] = $this->my_application->hasPermission("Orders","FullExport");											
			$contentData['showColumnTotal'] = $this->my_application->hasPermission("Orders","SeeImports");			
			
			$parametersdData = $this->_getParameters($page,
													 $idFilter,	$numberFilter,
													 $dateFromFilter, $dateToFilter,
													 //$companyIdFilter, $branchOfficeIdFilter, $sectorIdFilter,
													 $stateIdFilter,
													 //$userIdFilter, $authorizingUserIdFilter,$managerIdFilter,
													 $priorityIdFilter, $articleFilter,$subjectFilter,													 												 
													 $companyIdsFilter,$managerIdsFilter,$familyIdsFilter,
								                     $fieldOrder, $typeOrder);
											
			$parameters = $parametersdData['parameters'];

			foreach ($parameters as $key => $val) {
				$contentData[$key] = $val;
			}
						
			$contentData['filterGet'] = $parametersdData['filterGet'];
			$contentData['orderGet'] = $parametersdData['orderGet'];
			$contentData['backGet'] = $parametersdData['backGet'];	
			
			$states = $this->orders->getStates();
			$contentData['states'] = $states["list"];	

			$priorities = $this->orders->getPriorities();
			$contentData['priorities'] = $priorities["list"];	

			$contentData['allowCompanyFilter'] = ($this->my_application->hasPermission("Orders","FullAccess") || $this->my_application->hasPermission("Orders","FullAccessInsurance") || $this->my_application->hasPermission("Orders","InsertByAny"));			
			if ($contentData['allowCompanyFilter']) {
				$companies = $this->companies->getCompanies();
				$contentData['companies'] = $companies["list"];	
			}

			$contentData['allowManagerFilter'] = $this->my_application->hasPermission("Orders","AssignManager");
			if ($contentData['allowManagerFilter']) {				
				$managersParameters['typeFilter'] = 'manager';
				$managersParameters['activeFilter'] = 1;	
				$managers = $this->users->getUsers($managersParameters);
				$contentData['managers'] = $managers["list"];
			}

			$families = $this->families->getFamilies();
			$contentData['families'] = $families["list"];

			$orders = $this->orders->getOrders($parameters);
			$contentData['orders'] = $orders["list"];						
			
							
			$pageConfiguration = getPageConfiguration(base_url().'orders/listing',$this->config->item('recordsPerPage'),$orders["totalRecords"],$parametersdData['pageParameters']);
			$this->pagination->initialize($pageConfiguration); 
			$contentData['pagination'] = applyPageStyles($this->pagination->create_links());												
						
			$contentData['callback'] = "initializeOrders('".trim($this->input->get('error',TRUE))."');";						

			$data['menuActive'] = "Orders";
			$data['title'] = "Pedidos";
			$data['contentView'] = 'orders/orders_list_view';
			$data['contentData'] = $contentData;
			$this->my_application->loadGeneralTemplate($data);	
		}
	}	

	function search() {					
		$this->form_validation->set_rules('idFilter','Nro Pedido', 'trim|xss_clean');
		$this->form_validation->set_rules('numberFilter','Nro Pedido/Orden/Remito', 'trim|xss_clean');
		$this->form_validation->set_rules('dateFromFilter','Fecha Desde', 'trim|xss_clean');
		$this->form_validation->set_rules('dateToFilter','Fecha Hasta', 'trim|xss_clean');		
		//$this->form_validation->set_rules('companyIdFilter','Empresa', 'trim|xss_clean');
		//$this->form_validation->set_rules('branchOfficeIdFilter','Sucursal', 'trim|xss_clean');
		//$this->form_validation->set_rules('sectorIdFilter','Sector', 'trim|xss_clean');
		$this->form_validation->set_rules('stateIdFilter','Estado', 'trim|xss_clean');
		//$this->form_validation->set_rules('userIdFilter','Pedido Por', 'trim|xss_clean');
		//$this->form_validation->set_rules('authorizingUserIdFilter','Autorizado Por', 'trim|xss_clean');
		//$this->form_validation->set_rules('managerIdFilter','Gestionado Por', 'trim|xss_clean');
		$this->form_validation->set_rules('priorityIdFilter','Plazo', 'trim|xss_clean');		
		$this->form_validation->set_rules('articleFilter','Artículo', 'trim|xss_clean');			
		$this->form_validation->set_rules('subjectFilter','Asunto', 'trim|xss_clean');		
		$this->form_validation->set_rules('companyIdsFilterSelected','Empresas', 'trim|xss_clean');	
		$this->form_validation->set_rules('managerIdsFilterSelected','Gestionado por', 'trim|xss_clean');	
		$this->form_validation->set_rules('familyIdsFilterSelected','Rubros', 'trim|xss_clean');	
		$this->form_validation->set_rules('fieldOrder','Orden Campo', 'trim|xss_clean');
		$this->form_validation->set_rules('typeOrder','Orden Tipo', 'trim|xss_clean');
		
		if ($this->form_validation->run() != FALSE) {					
			$this->listing(1,
						   $this->input->post('idFilter',true),	$this->input->post('numberFilter',true),	
						   $this->input->post('dateFromFilter',true),$this->input->post('dateToFilter',true),
			           	   //$this->input->post('companyIdFilter',TRUE),$this->input->post('branchOfficeIdFilter',TRUE),$this->input->post('sectorIdFilter',TRUE),
			           	   $this->input->post('stateIdFilter',TRUE),
			           	   //$this->input->post('userIdFilter',TRUE),$this->input->post('authorizingUserIdFilter',TRUE),$this->input->post('managerIdFilter',TRUE),
			           	   $this->input->post('priorityIdFilter',TRUE),$this->input->post('articleFilter',TRUE),$this->input->post('subjectFilter',TRUE),			           	   
			           	   $this->input->post('companyIdsFilterSelected',TRUE),$this->input->post('managerIdsFilterSelected',TRUE),$this->input->post('familyIdsFilterSelected',TRUE),			           	   			           	   			           	   			           	   
			               $this->input->post('fieldOrder',true),$this->input->post('typeOrder',true)
			           	   );
		} else {
			$this->listing();
		}		
	}

	
	public function fullExport() {
        if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("Orders","FullExport")) exit;											
        
        $parametersdData = $this->_getParameters();										
		$parameters = $parametersdData['parameters'];
		$orders = $this->orders->getOrdersForFullExport($parameters);
		$fields = $orders["fields"];
		$orders = $orders["list"];	               
        
        if (isset($orders) && count($orders) > 0) {        
        	$folder = $this->config->item('files').'tmp/';			 			
			$filenameCSV = 'pedidos_'.getCurrentDateId().".csv";			

			// Create file .CSV 

			$outputCSV = fopen($folder.$filenameCSV, 'w');

            fwrite($outputCSV, "\xEF\xBB\xBF");

            fputcsv($outputCSV, $fields, ";");            
            
            foreach ($orders as $row) {
                fputcsv($outputCSV, $row, ";");             
            }
            
            fclose($outputCSV);


			// Download file .XLXS 

            $this->load->helper('download');
								
			$fileContent = file_get_contents($folder.$filenameCSV);	

			@unlink($folder.$filenameCSV);				
								
			force_download($filenameCSV,$fileContent);            

        } else {
            echo "No hay datos para exportar.";
        }
    }

	/*
	public function fullExport3() {
        if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("Orders","FullExport")) exit;											
        
        $parametersdData = $this->_getParameters();										
		$parameters = $parametersdData['parameters'];
		$orders = $this->orders->getOrdersForFullExport($parameters);
		$fields = $orders["fields"];
		$orders = $orders["list"];	               
        
        if (isset($orders) && count($orders) > 0) {           	            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="pedidos_'.getCurrentDateId().'.csv"');
            
            $output = fopen('php://output', 'w');

            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, $fields, ";");
            
            foreach ($orders as $row) {
                fputcsv($output, $row, ";"); 
            }
            
            fclose($output);  
                     
        } else {
            echo "No hay datos para exportar.";
        }
    }
    */
	
	/*
	public function fullExport() {
        if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("Orders","FullExport")) exit;											
        
        $parametersdData = $this->_getParameters();										
		$parameters = $parametersdData['parameters'];
		$orders = $this->orders->getOrdersForFullExport($parameters);
		$fields = $orders["fields"];
		$orders = $orders["list"];	               
        
        if (isset($orders) && count($orders) > 0) {           	

            $folder = $this->config->item('files').'tmp/';			 
			$filename = 'pedidos_'.getCurrentDateId();			
			$filenameCSV = $filename.".csv";
			$filenameXLSX = $filename.".xlsx";			

			// Create file .CSV 

			$outputCSV = fopen($folder.$filenameCSV, 'w');

            fputcsv($outputCSV, $fields);            
            
            foreach ($orders as $row) {
                fputcsv($outputCSV, $row);             
            }
            
            fclose($outputCSV);

            // Converter file .CSV to .XLXS 
           	
            $spreadsheet = new Spreadsheet();
	        
	        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Csv');
	        $reader->setDelimiter(','); // Define el delimitador si es necesario (por defecto es coma)
	        $spreadsheet = $reader->load($folder.$filenameCSV);
	        
	        $writer = new Xlsx($spreadsheet);	        

	        $writer->save($folder.$filenameXLSX);

			@unlink($folder.$filenameCSV);				
			
			// Download file .XLXS 

            $this->load->helper('download');
								
			$fileContent = file_get_contents($folder.$filenameXLSX);	

			@unlink($folder.$filenameXLSX);				
								
			force_download($filenameXLSX,$fileContent);
            
        } else {
            echo "No hay datos para exportar.";
        }
    }    
*/    
    /*
	function fullExport1() {				
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("Orders","FullExport")) exit;											

		$parametersdData = $this->_getParameters();										
		$parameters = $parametersdData['parameters'];
		$orders = $this->orders->getOrdersWithDetails($parameters);
		$orders = $orders["list"];	

		$parametersDescription = $this->_getParametersDescription($parameters);

		// INITIALITE SPREADSHEET
				
		$spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Pedidos');
		
        // TITLE
        $row = 1;
        $sheet->setCellValue('A'.$row,"LISTADO DE PEDIDOS"); 
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

        $row++;
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Nº Pedido"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Fecha"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Empresa"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Sucursal"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Sector"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Plazo");       
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Fecha Plazo");       
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Solicitado Por");
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Email");        
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Validado Por");     
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Fecha Validado"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Gestionado Por");    
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Estado"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Fecha Estado"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Nº Orden");              
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Código");
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Descripción");
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Rubro");        
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Cantidad");
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Costo S/IVA");               
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

		for ($i=0; $i < count($orders); $i++) {
			$row++;
	        $col = 0;
	        
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$orders[$i]['id']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,dateFormat($orders[$i]['date'],false)); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$orders[$i]['companyDescription']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$orders[$i]['branchOfficeDescription']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$orders[$i]['sectorDescription']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$orders[$i]['priorityDescription']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,dateFormat($orders[$i]['maximumDate'],false)); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,($orders[$i]['userLastName'] != ""?$orders[$i]['userLastName'].", ".$orders[$i]['userFirstName']:"")); 	        
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$orders[$i]['userMail']); 
	        $col++;	        
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,($orders[$i]['authorizingUserLastName'] != ""?$orders[$i]['authorizingUserLastName'].", ".$orders[$i]['authorizingUserFirstName']:"")); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,dateFormat($orders[$i]['validatedDate'],false)); 
	        $col++;	        
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,($orders[$i]['managerLastName'] != ""?$orders[$i]['managerLastName'].", ".$orders[$i]['managerFirstName']:"")); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$orders[$i]['stateDescription']);
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,dateFormat($orders[$i]['stateDate'],false));  	
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,((float)$orders[$i]['purchaseOrderId'] > 0?$orders[$i]['purchaseOrderId']:""));     	    
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$orders[$i]['articleCode']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$orders[$i]['articleDescription']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$orders[$i]['articleFamily']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$orders[$i]['articleQuantity']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,decimalFormat($orders[$i]['articleUnitPrice'],2)); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,decimalFormat($orders[$i]['articleTotal'],2)); 
	        	        
		}		

		for ($i=1; $i <= $col; $i++) {
			$sheet->getColumnDimension(getLetterOfExcelColumn($i))->setAutoSize(true);
		}

		// CREATE TEMP FILE EXCEL, DOWNLOAD Y DELETE 

        $writer = new Xlsx($spreadsheet); 

		$folder = $this->config->item('files').'tmp/';
		 
		$filename = 'pedidos_'.getCurrentDateId().'.xlsx';
		 
		$writer->save($folder.$filename); 

		$this->load->helper('download');
							
		$fileContent = file_get_contents($folder.$filename);	

		@unlink($folder.$filename);				
							
		force_download($filename,$fileContent);
	}
	*/
		
	function export() {				
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("Orders","Export")) exit;											

		$parametersdData = $this->_getParameters();										
		$parameters = $parametersdData['parameters'];
		$orders = $this->orders->getOrders($parameters);
		$orders = $orders["list"];	

		$parametersDescription = $this->_getParametersDescription($parameters);

		// INITIALITE SPREADSHEET
				
		$spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Pedidos');
		
        // TITLE
        $row = 1;
        $sheet->setCellValue('A'.$row,"LISTADO DE PEDIDOS"); 
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
        $showColumnTotal = $this->my_application->hasPermission("Orders","SeeImports");        

        $row++;
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Nº Pedido"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Fecha"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Empresa"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Sucursal"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Sector"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Plazo"); 
        if ($showColumnTotal) {
        	$col++;
        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Total s/IVA"); 
        }
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Estado"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Nº Orden");         
  
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

		for ($i=0; $i < count($orders); $i++) {
			$row++;
	        $col = 0;
	        
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$orders[$i]['id']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,dateFormat($orders[$i]['date'],false)); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$orders[$i]['companyDescription']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$orders[$i]['branchOfficeDescription']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$orders[$i]['sectorDescription']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$orders[$i]['priorityDescription']); 
	        if ($showColumnTotal) {
	        	$col++;
	        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,decimalFormat($orders[$i]['total'],2)); 
	        }
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$orders[$i]['stateDescription']); 	
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,((float)$orders[$i]['purchaseOrderId'] > 0?$orders[$i]['purchaseOrderId']:""));     	        
		}		

		for ($i=1; $i <= $col; $i++) {
			$sheet->getColumnDimension(getLetterOfExcelColumn($i))->setAutoSize(true);
		}

		// CREATE TEMP FILE EXCEL, DOWNLOAD Y DELETE 

        $writer = new Xlsx($spreadsheet); 

		$folder = $this->config->item('files').'tmp/';
		 
		$filename = 'pedidos_'.getCurrentDateId().'.xlsx';
		 
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
		if (isset($parameters['idFilter']) && $parameters['idFilter'] != "") {	
			$parametersDescription[count($parametersDescription)] = array('label'=>'Nro Pedido:','data'=>$parameters['idFilter']);					
		}
		if (isset($parameters['numberFilter']) && $parameters['numberFilter'] != "") {	
			$parametersDescription[count($parametersDescription)] = array('label'=>'Número:','data'=>$parameters['numberFilter']);					
		}
		if (isset($parameters['stateIdFilter']) && $parameters['stateIdFilter'] != "") {				
			$statesParameters['idFilter'] = $parameters['stateIdFilter'];				
			$states = $this->orders->getStates($statesParameters);
			if ($states["totalRecords"] == 1) {
				$state = $states["list"][0];
				$parametersDescription[count($parametersDescription)] = array('label'=>'Estado:','data'=>$state['description']);						
			}
		}	
		if (isset($parameters['priorityIdFilter']) && $parameters['priorityIdFilter'] != "") {				
			$prioritiesParameters['idFilter'] = $parameters['stateIdFilter'];				
			$priorities = $this->orders->getPriorities($prioritiesParameters);
			if ($priorities["totalRecords"] == 1) {
				$priority = $priorities["list"][0];
				$parametersDescription[count($parametersDescription)] = array('label'=>'Plazo:','data'=>$priority['description']);						
			}
		}				           	   
		if (isset($parameters['articleFilter']) && $parameters['articleFilter'] != "") {	
			$parametersDescription[count($parametersDescription)] = array('label'=>'Artículo:','data'=>$parameters['articleFilter']);					
		}																								

		return $parametersDescription;
	}

	function delete($orderId=-1) {
		$error = "";
		if ($this->session->userdata('userLoggedIn') && $this->my_application->hasPermission("Orders","Delete") && $this->orders->hasPermissionOverOrder($orderId)) {					
			if ($this->orders->deleteOrderValid($orderId)) {						
				if (!$this->orders->deleteOrder($orderId)) {						
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
		if ((!$this->my_application->hasPermission("Orders","Insert") && $id <= 0) || 
			!$this->my_application->hasPermission("Orders","See")) {
			redirect('/main/logout');
		}else {											
			if ($id > 0) {
				$parameters["idFilter"] = $id;
				$parameters["fullData"] = true;
				$orders = $this->orders->getOrders($parameters);
				if ($orders['totalRecords'] == 1){
					$order = $orders['list'][0];	

					$notificationsParameters = array('entityFilter'=>'order',
				                                     'entityIdFilter'=>$id);	
					$this->notifications->setNotificationRead($notificationsParameters);
				} else {
					$id = -1;
				}
			}

			if ($id <= 0) {
				$order = $this->orders->getEmptyOrder();
				
				$order['date'] = getCurrentDate(false);				
			}	
			$order["originalStateId"] = $order["stateId"];				
			
			if (isset($values) && isset($values['paymentSectors'])) $order['paymentSectors'] = $values['paymentSectors'];
			if (isset($values) && isset($values['details'])) {
				$originalDetails = $order['details'];
				$order['details'] = $values['details'];

				for ($i=0; $i < count($order['details']); $i++) {
					for ($j=0; $j < count($originalDetails); $j++) {					
						if ($order['details'][$i]['id'] == $originalDetails[$j]['id']) {
							$order['details'][$i]['canceledQuantity'] = $originalDetails[$j]['canceledQuantity'];
						}
					}
				}
			} 
			if (isset($values) && isset($values['attachments'])) $order['attachments'] = $values['attachments'];	
			
			$contentData['allowSeeImports'] = $this->my_application->hasPermission("Orders","SeeImports");
			$contentData['allowSeeDetailsImports'] = $contentData['allowSeeImports'] ;					  				
			$contentData['allowSeeInternalObservations'] = $this->my_application->hasPermission("Orders","SeeInternalObservations");
			$contentData['allowSeeAttachments'] = $this->my_application->hasPermission("Orders","SeeAttachments");				
			$contentData['allowPrint'] = $this->my_application->hasPermission("Orders","Print");
			$contentData['allowExport'] = $this->my_application->hasPermission("Orders","Export");								
			$contentData['allowExportPdf'] = $this->my_application->hasPermission("Orders","Export");
			$contentData['allowSelectCompany'] = ($order['id'] <= 0 && $this->my_application->hasPermission("Orders","InsertByAny"));													
			$contentData['seeCancelItem'] = ($this->my_application->hasPermission("Orders","ItemCancel") && $order['id'] > 0 && $order["stateId"] != "TOAUT" && $order["stateId"] != "CAN" && $order["stateId"] != "CAN");			
			$contentData['allowSeeBudgets'] = ($order['id'] > 0 && $this->my_application->hasPermission("Budgets","See"));			
			$contentData['allowInsertBudgets'] = false;
			$contentData['allowEditSubmanager'] = false;
			$contentData['showSubmanager'] = false;			
			
			$onlyCurrentState = false;						
			$userRol = "";	
			$allowAssignManager = $this->my_application->hasPermission("Orders","AssignManager");
			if ($userRol == "" && 
				(
					/* Si el pedido está en estado AUTORIZADO, nadie lo está gestionando y el usuario actual tiene permisos para gestionarlo */
					($order["stateId"] == "AUT" && (float)$order['managerId'] == 0 && $allowAssignManager)
					OR 		
					/* o el usuario actual lo está gestionando */
					((float)$order['managerId'] == $this->session->userdata('userId'))					
					OR 		
					/* o el usuario actual lo está gestionando */
					((float)$order['submanagerId'] == $this->session->userdata('userId'))					
				) 
		  	) {				
		  		$userRol = "manager";	
		  		if ($order["stateId"] == "AUT") $order["stateId"] = "PROG";
				if ((float)$order['managerId'] == 0) {
					$onlyCurrentState = true;
				}
		  	}
		  	if ($userRol == "" && 
				(
					/* Si el pedido está PENDIENTE AUTORIZAR y el usuario actual tiene permisos para autorizarlo */
					($order["stateId"] == "TOAUT" && (float)$order['authorizingUserId'] == 0 && $this->orders->currentUserCanAutorizateOrder($order['id']))
					OR 		
					/* o el usuario actual lo autorizó */
					((float)$order['authorizingUserId'] == $this->session->userdata('userId'))
				) 
		  	) {
				$userRol = "authorizingUser";			
		  	}
		  	if ($userRol == "" && $this->config->item('superUserId') != $this->session->userdata('userId') && $this->my_application->hasPermission("Orders","GenerateDN")) {
		  		$userRol = "delivery";			
		  	}
			if ($userRol == "" && (float)$order['userId'] == $this->session->userdata('userId')) {
				$userRol = "user";			
		  	}		  		

		  	$fullCancelStates = array("TOAUT","AUT","PROG", "DETUSR", "BUDGET", "AUTBUY", "TOPAY");
		  	$allowAFullCancel = (in_array($order["stateId"], $fullCancelStates) && $this->my_application->hasPermission("Orders","FullCancel"));			  			  			  	
		  	if ($allowAFullCancel) {
		  		$onlyCurrentState = false;
		  		$statesParameters["alwaysStateFilter"] = "CAN|SUS";
		  	} 

		  	$statesParameters["idFilter"] = $order["stateId"];				
		  	if (!$onlyCurrentState) {		  		
		  		$statesParameters["userRolFilter"] = $userRol;					
			}			
			$states = $this->orders->getStates($statesParameters);
			$contentData['states'] = $states["list"];								 	

			$contentData['allowEditMinimunGeneralData'] = false;
		  	$contentData['allowEditGeneralData'] = false;
		  	$contentData['allowEditDetail'] = false;
			$contentData['allowEditDetailImports'] = false;	
			$contentData['allowSaveOnlyObservation'] = false;	
			$contentData['allowSave'] = false;	

			/* Si el usuario puede editar habilito según su rol sobre el pedido y si el estado lo permite */
			if (count($contentData['states']) > 0 && $userRol != "") {				
				if (count($contentData['states']) == 1) {
					$allowEditByState = (trim($contentData['states'][0][$userRol.'NextStates']) != "");	
					
					if ($id <= 0 && ($order["stateId"] == "ARM" || $order["stateId"] == "TOAUT")) {	  			
	  					$contentData['allowEditGeneralData'] = true;
				  		$contentData['allowEditDetail'] = true;	
				  		$contentData['allowEditMinimunGeneralData'] = true;				  		
				  	}
				} else {
					$allowEditByState = true;											
				}
			} else {
				$allowEditByState = false;								
			}						
		  		                      		  		;
		  	if ($allowEditByState && (($this->my_application->hasPermission("Orders","Insert") && $id <= 0) || 
		  		                      ($this->my_application->hasPermission("Orders","Edit") && $id > 0))) {	
		  		switch ($userRol) {
		  			case "manager":
		  				$meStates = array("PROG", "DETUSR", "BUDGET", "AUTBUY", "TOPAY","PENSUP","INDIST","READY");
		  				if (in_array($order["stateId"], $meStates) && (float)$order['managerId'] == $this->session->userdata('userId')) {
		  					$contentData['allowEditDetailImports'] = true;					  	
		  				} else {
		  					$contentData['allowSave'] = true;	
		  				}		
		  				$contentData['allowEditGeneralData'] = ($id <= 0);  	
		  				$contentData['allowEditDetail'] = ($id <= 0);  

		  				$contentData['allowEditSubmanager'] = ($allowAssignManager || (float)$order['managerId'] == $this->session->userdata('userId'));
						$contentData['showSubmanager'] = true;	

		  			break;

		  			case "authorizingUser":		  									  	
						$contentData['allowSave'] = true;							
						$contentData['allowEditGeneralData'] = ($id <= 0);  	
						$contentData['allowEditDetail'] = ($order["stateId"] == "TOAUT");								
						$contentData['allowEditMinimunGeneralData'] = ($order["stateId"] == "TOAUT");	
		  			break;

		  			case "user":			  				
		  				if ($order["stateId"] == "ARM" || $order["stateId"] == "TOAUT") {	  			
		  					$contentData['allowEditGeneralData'] = ($id <= 0);
					  		$contentData['allowEditDetail'] = true;			
					  		$contentData['allowEditMinimunGeneralData'] = true;				  										  		
					  	} else {
					  		$contentData['allowSave'] = true;						  		
					  	}					  	
		  			break;

		  			case "delivery":			  				
		  				$contentData['allowSave'] = true;						  	
		  				$contentData['allowEditGeneralData'] = ($id <= 0);  		
		  				$contentData['allowEditDetail'] = ($id <= 0);  		  				
						$meStates = array("PROG", "DETUSR", "BUDGET", "AUTBUY", "TOPAY","PENSUP","INDIST","READY");
		  				if (in_array($order["stateId"], $meStates) && $allowAssignManager) {		  					
		  					$contentData['allowEditSubmanager'] = true;
							$contentData['showSubmanager'] = true;	
		  				}
		  			break;
		  		}		  	
		  	}		  		  	

		  	if (!$allowEditByState) {
		  		switch ($userRol) {
		  			case "manager":
		  				$contentData['allowSave'] = true;						  	
		  				$contentData['showSubmanager'] = true;
						$contentData['allowEditSubmanager'] = ($allowAssignManager || (float)$order['managerId'] == $this->session->userdata('userId'));
		  			break;

		  			case "delivery":			  				
		  				$contentData['allowSave'] = true;						  	
		  				$meStates = array("PROG", "DETUSR", "BUDGET", "AUTBUY", "TOPAY","PENSUP","INDIST","READY");
		  				if (in_array($order["stateId"], $meStates) && $allowAssignManager) {		  					
		  					$contentData['allowEditSubmanager'] = true;
							$contentData['showSubmanager'] = true;	
		  				}
		  			break;
		  		}
		  	}		 

		  	$contentData['allowEditMinimunGeneralData'] = ($contentData['allowEditGeneralData'] || $contentData['allowEditMinimunGeneralData']);	
		  	$contentData['allowSave'] = ($contentData['allowSave'] || $contentData['allowEditGeneralData'] || $contentData['allowEditMinimunGeneralData'] || $contentData['allowEditDetail'] || $contentData['allowEditDetailImports']);					  	
		  	$contentData['allowPrintFull'] = ($contentData['allowPrint'] && ($userRol == 'manager' || $allowAssignManager));
			$contentData['allowExportFull'] = ($contentData['allowExport'] && ($userRol == 'manager' || $allowAssignManager));
			$fqStates = array("READY", "FIN", "PROG", "DETUSR", "BUDGET", "AUTBUY", "TOPAY","PENSUP","INDIST");
			$contentData['allowSeeFreeQuantity'] = (!$contentData['allowEditDetail'] && ($allowAssignManager || $userRol == "manager" || $userRol == "delivery") && in_array($order["stateId"], $fqStates));				                      
			$contentData['allowGenerateDN'] = ($this->my_application->hasPermission("Orders","GenerateDN") && ($order["stateId"] == "READY" || $order["stateId"] == "INDIST" || $order["stateId"] == "PENSUP"));
			if ($contentData['allowGenerateDN']) $contentData['allowGenerateDN'] = !$this->orders->getOrderAllDelivered($order["id"]);
			$autStates = array("AUT","PROG", "DETUSR", "BUDGET", "AUTBUY", "TOPAY","PENSUP","INDIST","READY","FIN");
			if (in_array($order["stateId"], $autStates) && (float)$order['authorizingUserId'] == $this->session->userdata('userId')) {
				$contentData['allowSeeImports'] = true;					  	
				$contentData['allowSeeDetailsImports'] = false;					  	
			}


			if (!$contentData['allowSave'] && $order["stateId"] != "FIN" && $order["stateId"] != "CAN" && $order["stateId"] != "SUS" && $order["stateId"] != "NOTAUT") {
				if ((float)$order['managerId'] == $this->session->userdata('userId') || 
					(float)$order['authorizingUserId'] == $this->session->userdata('userId') ||
					(float)$order['userId'] == $this->session->userdata('userId')) {
					$contentData['allowSaveOnlyObservation'] = true;						
					$contentData['allowSave'] = true;					
				}
			}
			
			if ($order["stateId"] != "FIN" && $order["stateId"] != "CAN" && $order["stateId"] != "SUS" && $order["stateId"] != "NOTAUT" && $order["stateId"] != "TOAUT") {
				$contentData['allowInsertBudgets'] = ($contentData['allowSeeBudgets'] && $order['id'] > 0 && $this->my_application->hasPermission("Budgets","Insert"));
			} 

		  	if (!$contentData['allowEditMinimunGeneralData']) {
		  		$prioritiesParameters["idFilter"] = $order["priorityId"];					  				  	
		  	} else {
		  		$prioritiesParameters = null;
		  	}
			$priorities = $this->orders->getPriorities($prioritiesParameters);
			$contentData['priorities'] = $priorities["list"];	
						
			if ($contentData['allowSelectCompany']) {				
				$companiesParameters["activeOrIdFilter"] = true;
			}			
			$companiesParameters["idFilter"] = $order["companyId"];				
			$companies = $this->companies->getCompanies($companiesParameters);	
			$contentData['companies'] = $companies["list"];			

			if ($contentData['allowSelectCompany']) {				
				$branchOfficesParameters["activeOrIdFilter"] = true;
				$branchOfficesParameters["companyIdFilter"] = $order["companyId"];
			}	
			$branchOfficesParameters["idFilter"] = $order['branchOfficeId'];			
			$branchOffices = $this->branchOffices->getBranchOffices($branchOfficesParameters);	
			$contentData['branchOffices'] = $branchOffices['list'];	
			
			if ($contentData['allowSelectCompany']) {				
				$sectorsParameters["activeOrIdFilter"] = true;
				$sectorsParameters["branchOfficeIdFilter"] = $order['branchOfficeId'];
				$sectorsParameters["allowOrdersFilter"] = "1";				
			}		
			$sectorsParameters["idFilter"] = $order['sectorId'];				
			$sectors = $this->sectors->getSectors($sectorsParameters);	
			$contentData['sectors'] = $sectors['list'];				

			$usersParameters['idFilter'] = $order['userId'];			
			$users = $this->users->getUsers($usersParameters);
			$contentData['users'] = $users["list"];	
			if ($id <= 0 && count($users["list"]) == 1) {
				$order['userMail'] = $users["list"][0]['username'];
				$order['userCellphone'] = $users["list"][0]['cellphone'];
			}

			if (((float)$order['id'] <= 0 || $contentData['allowEditMinimunGeneralData']) && $order['stateId'] == "TOAUT" && (float)$order['userId'] == $this->session->userdata('userId')) {
				$authorizingUsersParameters['typeFilter'] = 'authorizingUser';
				$authorizingUsersParameters['companyIdFilter'] = $order['companyId'];			
				$authorizingUsersParameters['branchOfficeIdFilter'] = $order['branchOfficeId'];			
				$authorizingUsersParameters['sectorIdFilter'] = $order['sectorId'];	
				$authorizingUsersParameters['activeFilter'] = 1;				
				$authorizingUsers = $this->users->getUsers($authorizingUsersParameters);
				$contentData['authorizingUsers'] = $authorizingUsers["list"];				
			} else {	
				if ((float)$order['authorizingUserId'] > 0) { 					
					$authorizingUsersParameters['idFilter'] = $order['authorizingUserId'];							
					$authorizingUsers = $this->users->getUsers($authorizingUsersParameters);
					$contentData['authorizingUsers'] = $authorizingUsers["list"];				
				} else {								
					$contentData['authorizingUsers'] = array();	
				}				
			}	

			$contentData['onChangeState'] = "changeStateOrderEdit('".$userRol."');";	

			if ((float)$order['managerId'] > 0) {
				$meStates = array("PROG", "DETUSR", "BUDGET", "AUTBUY", "TOPAY","PENSUP","INDIST","READY");
		  		if (in_array($order["stateId"], $meStates) && $allowAssignManager) {				
					$managersParameters['typeFilter'] = 'manager';
  					$managersParameters['activeFilter'] = 1;	
				} else {
					$managersParameters['idFilter'] = $order['managerId'];								
				}
				$managers = $this->users->getUsers($managersParameters);
				$contentData['managers'] = $managers["list"];				
			} else {
				if ($order["stateId"] == "PROG" && (float)$order['managerId'] == 0) {					
  					$managersParameters['typeFilter'] = 'manager';
  					$managersParameters['activeFilter'] = 1;	
  					$managers = $this->users->getUsers($managersParameters);
					$contentData['managers'] = $managers["list"];
  				} else {
					$contentData['managers'] = array();
				}
			}	


			if ($contentData['allowEditSubmanager']) {
				$submanagersParameters['typeFilter'] = 'manager';
	  			$submanagersParameters['activeFilter'] = 1;
				$submanagers = $this->users->getUsers($submanagersParameters);
				$contentData['submanagers'] = $submanagers["list"];					
			} else {
				if ((float)$order['submanagerId'] > 0) {					
  					$submanagersParameters['idFilter'] = $order['submanagerId'];								
  					$submanagers = $this->users->getUsers($submanagersParameters);
					$contentData['submanagers'] = $submanagers["list"];
				} else {
					$contentData['submanagers'] = array();
				}
			}		

			$companiesParameters = NULL;
			$companiesParameters["activeFilter"] = 1;
			$companies = $this->companies->getCompanies($companiesParameters);	
			$contentData['paymentCompanies'] = $companies["list"];	
												
			$contentData['order'] = $order;	
			$contentData['error'] = $error;								                             
			
			if ($this->input->get('back',TRUE) != "") {
				$contentData['backGet'] = convertGet($this->input->get('back',TRUE),false);				
			} else {
				$contentData['backGet'] = "";				
			}
	
			$contentData['callback'] = "initializeOrderEdit()";
			if ($id > 0 && isset($values['action']) && trim($values['action']) != "") {
				$actionParameters = explode("|", trim($values['action']));
				if (count($actionParameters) == 2) {
					$action = trim($actionParameters[0]);			
					$type = trim($actionParameters[1]);

					switch ($action) {
						case 'P':
							$contentData['callback'] .= ";printOrder(".$id.",'".$type."')";
						break;
						case 'EP':
							$contentData['callback'] .= ";exportOrder(".$id.",'".$type."','P')";
						break;
						case 'EE':
							$contentData['callback'] .= ";exportOrder(".$id.",'".$type."','E')";
						break;
						case 'NB':
							$contentData['callback'] .= ";newBudgetOrderEdit(".$id.")";
						break;
					}				
				}
			}

			$data['menuActive'] = "Orders";
			if ($id > 0) {
				$data['title'] = 'Pedido Nro '.$id.' <button type="button" class="btn btn-default btn-sm" onclick="seeHistoryOrderEdit('.$id.')" title="ver historial" style="margin-left:10px;"><i class="fas fa-history"></i></button>';
			} else {
				$data['title'] = "Nuevo Pedido";
			}

			$data['contentView'] = 'orders/orders_edit_view';
			$data['contentData'] = $contentData;
			$this->my_application->loadGeneralTemplate($data);
		}
	}

	function save() {   		
		if ((!$this->my_application->hasPermission("Orders","Edit") && $this->input->post('id',TRUE) > 0) || 
		    (!$this->my_application->hasPermission("Orders","Insert") && $this->input->post('id',TRUE) <= 0)) {
			redirect('/orders/listing');	
		} else {
			$id = (float)$this->input->post('id',TRUE);

			$allowEditPaymentSectors = true;			
			$allowEditDetail = true;			
			$allowEditDetailImports = true;
			$allowEditAttachments = $this->my_application->hasPermission("Orders","SeeAttachments");
			$allowEditInternalObservations = $this->my_application->hasPermission("Orders","SeeInternalObservations");
			
			$newRequired = ($id <= 0?"required|":"");
			
			$this->form_validation->set_rules('date','Fecha','trim|'.$newRequired.'xss_clean');
			$this->form_validation->set_rules('priorityId','Plazo','trim|'.$newRequired.'xss_clean');
			$this->form_validation->set_rules('subpriorityId','Plazo','trim|xss_clean');
			$this->form_validation->set_rules('maximumDays','Días Plazo','trim|xss_clean');
			$this->form_validation->set_rules('maximumDate','Fecha Plazo','trim|xss_clean');
			$this->form_validation->set_rules('stateId','Estado','trim|xss_clean');
			$this->form_validation->set_rules('subject','Asunto','trim|'.$newRequired.'xss_clean');
			$this->form_validation->set_rules('companyId','Empresa','trim|'.$newRequired.'xss_clean');
			$this->form_validation->set_rules('branchOfficeId','Sucursal','trim|'.$newRequired.'xss_clean');
			$this->form_validation->set_rules('sectorId','Sector','trim|'.$newRequired.'xss_clean');
			$this->form_validation->set_rules('userId','Solicitado Por','trim|'.$newRequired.'xss_clean');
			$this->form_validation->set_rules('userEmail','Email','trim|xss_clean');
			$this->form_validation->set_rules('userCellphone','Celular','trim|xss_clean');
			$this->form_validation->set_rules('authorizingUserId','Validado Por','trim|xss_clean');
			$this->form_validation->set_rules('purchaseOrderNumber','Nro Orden de Compra','trim|xss_clean');
			$this->form_validation->set_rules('managerId','Gestionado Por','trim|xss_clean');
			$this->form_validation->set_rules('submanagerId','Asiste en Gestión','trim|xss_clean|callback__validateSubmanager');
			$this->form_validation->set_rules('deliveryNoteNumber','Nro Remito','trim|xss_clean');			
			$this->form_validation->set_rules('total','Total','trim|xss_clean');			 
			$this->form_validation->set_rules('userObservation','Observación','trim|'.($this->input->post('stateId',TRUE) == 'NOTAUT'?'required|':'').'max_length[500]|xss_clean');					
			if ($allowEditInternalObservations) $this->form_validation->set_rules('internalObservation','Observación Interna','trim|max_length[500]|xss_clean');					
			$this->form_validation->set_rules('backGet','Get Volver', 'trim|xss_clean');			
	 		
	 		$values = NULL;	

	 		if ($allowEditPaymentSectors) {
	 			$paymentSectors = array();		  						
				$pSectorsCount = (int)$this->input->post('pSectorsCount',TRUE);
				$pSectorsIdx = -1;
				for($i=1; $i <= $pSectorsCount; $i++) {
					$pSectorsId = $this->input->post('pId'.$i,TRUE);

					if (isset($pSectorsId) && $pSectorsId != "") {
						$pSectorsIdx++;
						$paymentSectors[$pSectorsIdx]['id'] = (float)$pSectorsId;
						$paymentSectors[$pSectorsIdx]['companyId'] = $this->input->post('pCompanyId'.$i,TRUE);
						$paymentSectors[$pSectorsIdx]['companyDescription'] = $this->input->post('pCompany'.$i,TRUE);
						$paymentSectors[$pSectorsIdx]['branchOfficeId'] = $this->input->post('pBranchOfficeId'.$i,TRUE);
						$paymentSectors[$pSectorsIdx]['branchOfficeDescription'] = $this->input->post('pBranchOffice'.$i,TRUE);
						$paymentSectors[$pSectorsIdx]['sectorId'] = $this->input->post('pSectorId'.$i,TRUE);
						$paymentSectors[$pSectorsIdx]['sectorDescription'] = $this->input->post('pSector'.$i,TRUE);
						$paymentSectors[$pSectorsIdx]['percent'] = $this->input->post('pPercent'.$i,TRUE);						
					}
				}									
				$values['paymentSectors'] = $paymentSectors;						
			}
	 		
	 		if ($allowEditDetail || $allowEditDetailImports) {
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
						$details[$detailIdx]['deliveredQuantity'] = $this->input->post('detailDeliveredQuantity'.$i,TRUE);												
						$details[$detailIdx]['freeQuantity'] = $this->input->post('detailFreeQuantity'.$i,TRUE);
						$details[$detailIdx]['pendingQuantity'] = $this->input->post('detailPendingQuantity'.$i,TRUE);
						$details[$detailIdx]['familyDescription'] = $this->input->post('detailFamily'.$i,TRUE);												
						$details[$detailIdx]['total'] = $this->input->post('detailTotal'.$i,TRUE);												
						$details[$detailIdx]['affectsStock'] = $this->input->post('detailAffectsStock'.$i,TRUE);	
						$details[$detailIdx]['usual'] = (strtoupper($this->input->post('detailUsual'.$i,TRUE))=="SI"?1:0);	
					}
				}									
				$values['details'] = $details;						
			}

			if ($allowEditAttachments) {
				$attachments = array();		  						
				$attachmentsCount = (int)$this->input->post('attachmentsCount',TRUE);
				$attachmentIdx = -1;
				for($i=1; $i <= $attachmentsCount; $i++) {
					$attachmentId = $this->input->post('attachmentId'.$i,TRUE);

					if (isset($attachmentId) && $attachmentId != "") {
						$attachmentIdx++;
						$attachments[$attachmentIdx]['id'] = (float)$attachmentId;
						$attachments[$attachmentIdx]['filename'] = $this->input->post('attachmentName'.$i,FALSE);
						$attachments[$attachmentIdx]['internalFilename'] = $this->input->post('attachmentFile'.$i,FALSE);						
						$attachments[$attachmentIdx]['userId'] = $this->input->post('userId'.$i,FALSE);						
					}
				}									
				$values['attachments'] = $attachments;		
			}
						
			if ($this->form_validation->run() != FALSE) {					
				$data['id'] = $this->input->post('id',TRUE);
				
				$data['date'] = $this->input->post('date',TRUE);
				$data['priorityId'] = $this->input->post('priorityId',TRUE);
				$data['subpriorityId'] = $this->input->post('subpriorityId',TRUE);
				$data['maximumDays'] = $this->input->post('maximumDays',TRUE);
				$data['maximumDate'] = $this->input->post('maximumDate',TRUE);
				$data['stateId'] = $this->input->post('stateId',TRUE);	
				$data['subject'] = $this->input->post('subject',TRUE);	
				$data['originalStateId'] = $this->input->post('originalStateId',TRUE);							
				$data['branchOfficeId'] = $this->input->post('branchOfficeId',TRUE);
				$data['sectorId'] = $this->input->post('sectorId',TRUE);				
				$data['authorizingUserId'] = $this->input->post('authorizingUserId',TRUE);
				$data['managerId'] = $this->input->post('managerId',TRUE);				
				$data['submanagerId'] = $this->input->post('submanagerId',TRUE);				
				$data['userObservation'] = $this->input->post('userObservation',TRUE);	
				if ($allowEditPaymentSectors) $data['paymentSectors'] = $paymentSectors;				
				if ($allowEditInternalObservations) $data['internalObservation'] = $this->input->post('internalObservation',TRUE);			
				if ($allowEditDetail || $allowEditDetailImports) $data['details'] = $details;
				if ($allowEditAttachments) $data['attachments'] = $attachments;				
				
				$response = $this->orders->setOrder($data);

				$orderId = (float)$response['orderId'];

				if ($orderId > 0) {										
					$error = "";

					/*
					$pdfPath = "";
					if ($sendMailToClient) {
						$pdfPath = $this->_outputOrder($orderId,"mail");

						$sendMailOK = $this->orders->sendMailToClient($orderId,$pdfPath,!$sendMailTo);											

						if (!$sendMailOK) {
							$error = "No se pudo enviar el mail al cliente, los datos se guardaron correctamente";
						}
					}

					if ($sendMailTo) {
						if ($pdfPath == "") {
							$pdfPath = $this->_outputOrder($orderId,"mail");
						}

						$sendMailOK = $this->orders->sendMailTo($orderId,$pdfPath,$mailTo);											

						if (!$sendMailOK) {
							$error = "No se pudo enviar el mail a alguno de los destinatarios, los datos se guardaron correctamente";
						}
					}	
					*/				

					$action = ($this->input->post('action',TRUE) != "");					

					if ($action) {
						$error = "";
						$values = null;
						$values['action'] = $this->input->post('action',TRUE);
						$this->edit($orderId,$error,$values);
					} else {
						if ($this->input->post('backGet',TRUE) != "") {
							$backGet = $this->input->post('backGet',TRUE);						
						} else {
							$backGet = "";							
						}						

						if ($error != "") {
							if ($backGet == "") $backGet = "?";						
							$getBack .= "&error=".$error;
						}									
						
						redirect('/orders/listing/'.$backGet);	
					}
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

	function _validateSubmanager() {		
		$managerId = (float)$this->input->post('managerId',TRUE);
		$submanagerId = (float)$this->input->post('submanagerId',TRUE);

		if ($managerId == 0 && $submanagerId > 0) {
			$this->form_validation->set_message('_validateSubmanager', 'No se puede seleccionar el Asistente de Gestión si no se ha seleccionado el Gestionador principal.');
			return FALSE;	
		}

		if ($managerId != 0 && $managerId == $submanagerId) {
			$this->form_validation->set_message('_validateSubmanager', 'El Asistente de Gestión no puede ser igual al Gestionador principal.');
			return FALSE;	
		}
		
		return TRUE;
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

	function _validateSendMail(){
		$clientEmail = trim($this->input->post('clientEmail',TRUE));
		$sendMail = ((int)$this->input->post('sendMail',TRUE) == 1);
		
		if ($sendMail && $clientEmail == "") {
			$this->form_validation->set_message('_validateSendMail', 'Debe ingresar el email');
			return false;
		} else {
			return true;
		}
	}


	function _outputOrder($id=0,$documentType='O',$from="print",$format="") {			
		if ($from=="external") {
			/*
			$fields = decryptText($id);
			$arrFields = explode("|", $fields);
			if (count($arrFields) == 4) {				
				$id = $arrFields[0];
				$parameters["workshopManagerIdFilter"] = $arrFields[1];
				$parameters["assessorIdFilter"] = $arrFields[2];
				$parameters["patentFilter"] = $arrFields[3];
				$parameters["fullPermissions"] = true;
			} else {
				$id = -1;
			}	
			*/	
			$id = -1;											
		} else {
			if ($id > 0 && !$this->session->userdata('userLoggedIn')) $id = -1;
			if ($id > 0 && $from != "print" && $from != "export" && $from != "mail") $id = -1;			
			if ($id > 0 && $from == "print" && !$this->my_application->hasPermission("Orders","Print")) $id = -1;
			if ($id > 0 && $from == "export" && !$this->my_application->hasPermission("Orders","Export")) $id = -1;									
		}						

		$documentType = strtoupper($documentType);
		if ($documentType != "PO" && $documentType != "DN") $documentType = "O";																				

		if ($id > 0) {
			if ($documentType == "DN") {
				$deliveryNoteid = $id;
				$parameters["deliveryNoteIdFilter"] = $deliveryNoteid;
			} else {
				$parameters["idFilter"] = $id;
			}
			$parameters["fullData"] = true;						
			$orders = $this->orders->getOrders($parameters);
			if ($orders['totalRecords'] == 1){				
				$order = $orders['list'][0];																		
			} else {
				exit;
			}			
			$orderId = $order['id'];

			if ($documentType == "DN" && $from == "print") {
				$userIsManager = ($order['managerId'] == $this->session->userdata('userId'));
				if (!$userIsManager && !$this->my_application->hasPermission("Orders","AssignManager") && !$this->my_application->hasPermission("Orders","GenerateDN")) {					
					exit;
				}
			}			

			$user = null;
			if ($order['userId'] > 0) {
				$userParameters['idFilter'] = $order['userId'];				
				$users = $this->users->getUsers($userParameters);
				if ($users["totalRecords"] == 1) {
					$user = $users["list"][0];					
				}					
			}

			$authorizingUser = null;
			if ((float)$order['authorizingUserId'] > 0) {
				$userParameters['idFilter'] = $order['authorizingUserId'];				
				$users = $this->users->getUsers($userParameters);
				if ($users["totalRecords"] == 1) {
					$authorizingUser = $users["list"][0];					
				}					
			}

			$manager = null;
			if ((float)$order['managerId'] > 0) {
				$userParameters['idFilter'] = $order['managerId'];				
				$users = $this->users->getUsers($userParameters);
				if ($users["totalRecords"] == 1) {
					$manager = $users["list"][0];					
				}					
			}

			if ($documentType == "DN") {				   
	            $deliveryNotesParameters['fullData'] = true;						
	            $deliveryNotesParameters['idFilter'] = $deliveryNoteid;	
				$deliveryNotes = $this->orders->getDeliveryNotes($deliveryNotesParameters);
				if ($deliveryNotes['totalRecords'] == 1){				
					$deliveryNote = $deliveryNotes['list'][0];																		
				} else {					
					exit;
				}	

				$order['deliveryNoteNumber'] = $deliveryNote['number'];
	            $order['deliveryNoteDate'] = $deliveryNote['date']; 
	            $order['details'] = $deliveryNote['details']; 			   

	            $contentData['showImports'] = false;
			} else {
				$contentData['showImports'] = $this->my_application->hasPermission("Orders","SeeImports");	
			}

			$logo = $this->config->item('images')."logoOrder.jpg";
			
			if (file_exists($logo)) {
				if ($from != "export") {
					$logo = base_url().$logo;
				}
			} else {
				$logo = "";
			}					

			$contentData['documentType'] = $documentType;
			$contentData['order'] = $order;	
			$contentData['user'] = $user;	
			$contentData['authorizingUser'] = $authorizingUser;	
			$contentData['manager'] = $manager;
			$contentData['headerLogo'] = $logo;

			$contentData['export'] = ($from == "export");				
			switch (strtoupper($documentType)) {
				case "O": $filename = "pedido_".$orderId.".pdf"; break;
				case "PO": $filename = "orden_de_compra_".$order['purchaseOrderId'].".pdf"; break;
				case "DN": $filename = "remito_".$deliveryNoteid.".pdf"; break;
				default: $filename = ""; break;
		  	}				  	

			switch ($from) {
				case "export":

					switch (strtoupper($documentType)) {						
						case "PO": 
							$action = "EPO"; 
							$actionDescription = "Nro: ".$order['purchaseOrderId'].".";
							break;
						case "DN": 
							$action = "EDN"; 
							$actionDescription = "Nro: ".$deliveryNoteid.".";
							break;
						default: $action = ""; break;
				  	}
				  	if ($action != "") {
				  		$history = array('orderId'=>$orderId,
				                         'typeId'=>$action,
				                         'observation'=>$actionDescription);
						$this->orders->setHistory($history);
				  	}	

					$contentData['export'] = true;
					
					$this->load->library('pdf');

				  	$this->pdf->load_view("orders/orders_print_view",$contentData);
				  	$this->pdf->render();				  

				  	$this->pdf->stream($filename);
				break;

				case "mail":
					//$contentData['printId'] = encryptText($order["id"]."|".$order["workshopManagerId"]."|".$order["assessorId"]."|".$order["vehiclePatent"]);							
					//$view = $this->load->view("orders/orders_print_view",$contentData,true);

					$folder = $this->config->item('files').'tmp';
					checkCreateFolder($folder);			

					$contentData['export'] = true;
					
					$this->load->library('pdf');

				  	$this->pdf->load_view("orders/orders_print_view",$contentData);
				  	$this->pdf->render();

				  	$pdfPath = $folder."/".$filename;

				  	if (file_exists($pdfPath)) @unlink($pdfPath);
				  						
				  	file_put_contents($pdfPath, $this->pdf->output());

					return $pdfPath;
				break;

				default: //"print"							
					switch (strtoupper($documentType)) {						
						case "PO": 
							$action = "PPO"; 
							$actionDescription = "Nro: ".$order['purchaseOrderId'].".";
							break;
						case "DN": 
							$action = "PDN"; 
							$actionDescription = "Nro: ".$deliveryNoteid.".";

							$this->orders->setPrintedDeliveryNote($deliveryNoteid);
							break;
						default: $action = ""; break;
				  	}
				  	if ($action != "") {
				  		$history = array('orderId'=>$orderId,
				                         'typeId'=>$action,
				                         'observation'=>$actionDescription);
						$this->orders->setHistory($history);
				  	}	

					$contentData['callback'] = "window.print()";	
					
					$this->load->view('orders/orders_print_view',$contentData);
				break;
			}														
		} else {
			if ($from == "mail") {
				return "";
			}
		}
	}	


	function print($id=0,$documentType='') {									
		if ((float)$id > 0 && trim($documentType) != '' &&
			$this->session->userdata('userLoggedIn') && 
			$this->my_application->hasPermission("Orders","Print")) {			
			$this->_outputOrder($id,$documentType,"print");	
		}
	}	

	function pdf($id=0,$documentType='') {		
		if ((float)$id > 0 && (trim($documentType) == 'O' || trim($documentType) == 'PO') && 
			$this->session->userdata('userLoggedIn') && 
			$this->my_application->hasPermission("Orders","Export")) {

			$this->_outputOrder($id,$documentType,"export","pdf");	
		}
	}	

	function excel($id=0,$type='') {											
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("Orders","Export") || (float)$id <= 0 || trim($type) == '') exit;
		
		$type = strtoupper($type);
		if ($type != "PO" && $type != "DN") $type = "O";

		$parameters["idFilter"] = $id;
		$parameters["fullData"] = true;
		$orders = $this->orders->getOrders($parameters);
		if ($orders['totalRecords'] == 1){				
			$order = $orders['list'][0];																		
		} else {
			exit;
		}

		$user = null;
		if ($order['userId'] > 0) {
			$userParameters['idFilter'] = $order['userId'];				
			$users = $this->users->getUsers($userParameters);
			if ($users["totalRecords"] == 1) {
				$user = $users["list"][0];					
			}					
		}

		$authorizingUser = null;
		if ((float)$order['authorizingUserId'] > 0) {
			$userParameters['idFilter'] = $order['authorizingUserId'];				
			$users = $this->users->getUsers($userParameters);
			if ($users["totalRecords"] == 1) {
				$authorizingUser = $users["list"][0];					
			}					
		}

		$manager = null;
		if ((float)$order['managerId'] > 0) {
			$userParameters['idFilter'] = $order['managerId'];				
			$users = $this->users->getUsers($userParameters);
			if ($users["totalRecords"] == 1) {
				$manager = $users["list"][0];					
			}					
		}

		/*
		$logo = $this->config->item('images')."logoOrder.jpg";
		
		if (file_exists($logo)) {
			if ($from != "export") {
				$logo = base_url().$logo;
			}
		} else {
			$logo = "";
		}
		*/
		
		$showImports = $this->my_application->hasPermission("Orders","SeeImports");	

	  	switch ($type) {
	        case "PO":
	            $title = "ORDEN DE COMPRA Nº ";
	            $number = $order['purchaseOrderNumber'];
	            $date = $order['purchaseOrderDate'];
	            $referenceOrderNumber = $order['id'];
            	$referencePurchaseOrderNumber = "";
	            $filename = "orden_de_compra_".$order['purchaseOrderId'].".xlsx";
	            $showPaymentSectors = true;  
	        break;
	        case "DN":
	            $title = "REMITO Nº ";
	            $number = $order['deliveryNoteNumber'];
	            $date = $order['deliveryNoteDate'];
	            $referenceOrderNumber = $order['id'];
            	$referencePurchaseOrderNumber = $order['purchaseOrderNumber'];
	            $filename = "remito_".$order['deliveryNoteId'].".xlsx";
	            $showPaymentSectors = false;  
	        break;
	        default:
	            $title = "PEDIDO Nº ";
	            $number = $order['id'];
	            $date = $order['date'];
	            $referenceOrderNumber = "";
            	$referencePurchaseOrderNumber = "";
	            $filename = "pedido_".$id.".xlsx"; 
	            $showPaymentSectors = false;  
	        break;
	    }


		// INITIALITE SPREADSHEET
				
		$spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle($title.$number);
		
		$row = 0;

		$colsCount = 7;

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
		$styleCell2 = array(
				            'fill' => array(
				                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				                'startColor' => array('argb' => '00F4F6F9')
				            	),
				            'font' => array(
				            	'bold'=>false,
				            	'color' => array('argb' => '00000000')
				            	)
					        );
       
        $row++;
        $col = 1;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$title);                 
        $sheet->getStyle(getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);	          	
        $col += 2;	        
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Fecha");         
        $sheet->getStyle(getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);	
        
        $row++;
        $col = 1;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$number);        
        $sheet->getStyle(getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell2);           
        $col += 2;	        
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,dateFormat($date,false));        
        $sheet->getStyle(getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell2);


        if ($referenceOrderNumber != "" || $referencePurchaseOrderNumber != "") {
	        $row += 2;        
	        $col = 1;
	        if ($referenceOrderNumber != "") {		                
		        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Pedido Nº");                         
		        $sheet->getStyle(getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);        	
		    }
		    if ($referencePurchaseOrderNumber != "") {	
		    	$col += 2;		                
		        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Orden de Compra Nº");                         
		        $sheet->getStyle(getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);        	
		    }
		    $row++;
		    $col = 1;
	        if ($referenceOrderNumber != "") {		                
		        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$referenceOrderNumber);         
        		$sheet->getStyle(getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell2);       	
		    }
		    if ($referencePurchaseOrderNumber != "") {	
		    	$col += 2;		                
		        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$referencePurchaseOrderNumber);         
        		$sheet->getStyle(getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell2);        	
		    }
		}
                
        $row += 2;
        $col = 1;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Empresa");           
        $sheet->getStyle(getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);	      
        $col += 2;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Sucursal"); 
        $sheet->getStyle(getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);	
        $col += 2;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Sector");                         
        $sheet->getStyle(getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);	
        $row++;
        $col = 1;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$order['companyDescription']);                 
        $sheet->getStyle(getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell2); 
        $col += 2;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$order['branchOfficeDescription']);   
        $sheet->getStyle(getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell2); 
        $col += 2;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$order['sectorDescription']);
        $sheet->getStyle(getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell2);  

        $row += 2;
        $col = 1;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Solicitado Por");           
        $sheet->getStyle(getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);	      
        if (isset($authorizingUser)) {
        	$col += 2;
        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Validado Por"); 
        	$sheet->getStyle(getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);	
        }
        if (isset($manager)) {
	        $col += 2;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Gestionado Por");                         
	        $sheet->getStyle(getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);	
	    }
        $row++;
        $col = 1;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,(isset($user)?$user['lastName'].", ".$user['firstName']:""));                 
        $sheet->getStyle(getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell2); 
        if (isset($authorizingUser)) {
        	$col += 2;
        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,(isset($authorizingUser)?$authorizingUser['lastName'].", ".$authorizingUser['firstName']:""));                 
        	$sheet->getStyle(getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell2); 
        }
        if (isset($manager)) {
	        $col += 2;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,(isset($manager)?$manager['lastName'].", ".$manager['firstName']:""));                 
	        $sheet->getStyle(getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell2);
	    }

        // GRID HEADERS        
        $col = 0;
        $row += 2;

        $row++;
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Código"); 
        $col++;
    	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Descripción");     	
    	if ($showImports) {
        	$col++;
        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Precio ($)"); 
        }
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Cant."); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Rubro");         
        if ($showImports) {
        	$col++;
        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Total ($)");   
        }

        $colsCount = $col;

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

		$sheet->getStyle('A'.$row.":".getLetterOfExcelColumn($colsCount).$row)->applyFromArray($styleCell);		

		// GRID BODY
		$details = $order['details'];
		$detailsCount = (isset($details)?count($details):0);        

        for ($i=0; $i < $detailsCount; $i++) {
			$row++;
	        $col = 0;

	    	$col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$details[$i]['code']); 
        	$col++;
        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$details[$i]['description']); 
	        if ($showImports) {
		        $col++;
		        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,decimalFormat($details[$i]['unitPrice'],2)); 
		    }
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$details[$i]['quantity']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$details[$i]['familyDescription']); 
	        if ($showImports) {
		        $col++;
		        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,decimalFormat($details[$i]['total'],2)); 
		    }
		}		

		$row++;

		if ($showImports) {			
			$row++;
			$col = $colsCount - 2;
			$sheet->setCellValue(getLetterOfExcelColumn($col).$row,"TOTAL s/IVA $");  
			$sheet->mergeCells(getLetterOfExcelColumn($col).$row.':'.getLetterOfExcelColumn($col+1).$row);
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
			$sheet->getStyle(getLetterOfExcelColumn($col).$row.":".getLetterOfExcelColumn($col+1).$row)->applyFromArray($styleCell);		
			$col = $colsCount;
			$sheet->setCellValue(getLetterOfExcelColumn($col).$row,decimalFormat($order['total'],2));
			$styleCell = array(
					            'fill' => array(
					                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
					                'startColor' => array('argb' => '00E5E9EF')
					            	),
					            'font' => array(
					            	'bold'=>true,
					            	'color' => array('argb' => '00000000')
					            	)
						        );
			$sheet->getStyle(getLetterOfExcelColumn($col).$row.":".getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);		
		}

		for ($i=1; $i <= $colsCount; $i++) {
			$sheet->getColumnDimension(getLetterOfExcelColumn($i))->setAutoSize(true);
		}		

		$row++;
				
		$row++;
		$col = 1;
		$sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Observaciones:");  
		$sheet->mergeCells(getLetterOfExcelColumn($col).$row.':'.getLetterOfExcelColumn($colsCount).$row);
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
		$sheet->getStyle(getLetterOfExcelColumn($col).$row.":".getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);		

		$userObservations = $order['userObservations'];
		if (isset($userObservations) && count($userObservations) > 0) {
            for ($i=0; $i < count($userObservations); $i++) {
            	$row++;
				$col = 1;
				$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$userObservations[$i]['observation']);  
				$sheet->mergeCells(getLetterOfExcelColumn($col).$row.':'.getLetterOfExcelColumn($colsCount).$row);
				$styleCell = array(
				            'fill' => array(
				                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				                'startColor' => array('argb' => '00F4F6F9')
				            	),
				            'font' => array(
				            	'bold'=>false,
				            	'color' => array('argb' => '00000000')
				            	)
					        );
				$sheet->getStyle(getLetterOfExcelColumn($col).$row.":".getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);		
            }
        }


        $paymentSectors = $order['paymentSectors'];        

    	if (isset($paymentSectors) && $showPaymentSectors) { 
    		$row++;	
    		$row++;	    		
    		$col=0;

    		$row++;
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Empresa"); 
	        $col++;
	    	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Sucursal");     		    	
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Sector");         
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"%"); 
	        
	        $colsCountPS = $col;

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

			$sheet->getStyle('A'.$row.":".getLetterOfExcelColumn($colsCountPS).$row)->applyFromArray($styleCell);		


			for ($i=0; $i < count($paymentSectors); $i++) {
				$row++;
		        $col = 0;

		    	$col++;
		        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$paymentSectors[$i]['companyDescription']); 
	        	$col++;
	        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$paymentSectors[$i]['branchOfficeDescription']); 
		        $col++;
	        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$paymentSectors[$i]['sectorDescription']); 
	        	$col++;
	        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,decimalFormat($paymentSectors[$i]['percent'],2)); 
			}
		}

		// CREATE TEMP FILE EXCEL, DOWNLOAD Y DELETE 

        $writer = new Xlsx($spreadsheet); 

		$folder = $this->config->item('files').'tmp/';
		 		
		 
		$writer->save($folder.$filename); 

		$this->load->helper('download');
							
		$fileContent = file_get_contents($folder.$filename);	

		@unlink($folder.$filename);				
							
		force_download($filename,$fileContent);
	}

	function externalPrint($key="") {									
		if ($key != "")	{
			$this->_outputOrder($key,"external");
		}
	}	

	function articlesFinder($page=1) {
		if (!$this->my_application->hasPermission("Orders","See")) {
			exit;
		} else {															
			$textFilter = "";			
			if ($this->input->post('textFilter',true) != "") $textFilter = $this->input->post('textFilter',true);
			if ($this->input->get('text',true) != "") $textFilter = $this->input->get('text',true);
			
			if ($page > 0) {									
				$parameters['textFilter'] = $textFilter;				
				$parameters['activeFilter'] = 1;														
				$parameters['page'] = $page;															
				$articles = $this->articles->getArticles($parameters);						
				$contentData['articles'] = $articles["list"];								

				$pageParameters = array('text'=>$textFilter);	
				$pageConfiguration = getPageConfiguration(base_url().'orders/articlesFinder',$this->config->item('recordsPerPage'),$articles["totalRecords"],$pageParameters);
				$this->pagination->initialize($pageConfiguration); 
				$contentData['pagination'] = convertPageToAjax(applyPageStyles($this->pagination->create_links()),'reloadFinderOrderEdit');	
			} else {
				$contentData['articles'] = NULL;				
				$contentData['pagination'] = NULL;
			}

			$contentData['textFilter'] = $textFilter;								
			
			$contentData['filterGet'] = "";					
			if ($textFilter != "") $contentData['filterGet'] .= "&bus=".$textFilter;						
															
			$contentData['byAjax'] = true;	
						
			$view = $this->load->view("orders/orders_finder_articles_view",$contentData,true);
			
			$this->output->set_output($view); 										
		}
	}

	
	function searchArticle() {
		if ($this->session->userdata('userLoggedIn')) {									
			
			$code = $this->input->post('code',true);
			
			$id = 0;
			if ($code != "") {						
				$parameters['activeFilter'] = 1;				
				$parameters['codeFilter'] = $code;
				$articles = $this->articles->getArticles($parameters);	
				if ($articles['totalRecords'] > 0) {
					$articles = $articles['list'][0];									
					$id = $articles['id'];
					$code = $articles['code'];
					$description = str_replace('"',"",$articles['description']);							
					$familyDescription = str_replace('"',"",$articles['familyDescription']);
					$usual = getBooleanToText($articles['usual']);					
				}
			} 

			if ($id == 0) {
				$id = "0";
				$code = "";
				$description = "";						
				$familyDescription = "";					
				$usual = "";
			}			                   
			$unitPrice = "0.00";		
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
			$contentData['data'] .= '<input type="hidden" id="usualArticle" name="usualArticle" value="'.$usual.'">';			

			$contentData['byAjax'] = true;	
						
			$view = $this->load->view("general_data_view",$contentData,true);
			
			$this->output->set_output($view); 		
		}
	}

	function attachment($orderId=-1,$attachmentId=-1) {		
		if ($this->my_application->hasPermission("Orders","See") && $orderId > 0 && $attachmentId > 0) {			

			$attachmentsParameters['orderIdFilter'] = $orderId;						
			$attachmentsParameters['idFilter'] = $attachmentId;	
			$attachments = $this->orders->getAttachmentsByOrder($attachmentsParameters);			
			if ($attachments['totalRecords'] == 1){
				$attachment = $attachments['list'][0];	

				$path = $this->config->item('files').'orders/'.$orderId.'/'.$attachment['internalFilename'];

				if (file_exists($path)) {
					
					$this->load->helper('download');
							
					$fileContent = file_get_contents($path);					
							
					force_download($attachment['filename'],$fileContent);
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

	function history($orderId=-1) {
		if (!$this->my_application->hasPermission("Orders","See") || $orderId <= 0) {
			exit;
		} else {					

			$historyParameters['orderIdFilter'] = $orderId;						
			$historyByOrder = $this->orders->getHistoryByOrder($historyParameters);
			$contentData['history'] = $historyByOrder['list'];							
															
			$contentData['byAjax'] = true;	
						
			$view = $this->load->view("orders/orders_history_view",$contentData,true);
			
			$this->output->set_output($view); 										
		}
	}

	function testMail() {		
		$mail = "cristianmdq@hotmail.com";
		if (!$this->my_application->hasPermission("Orders","See") || trim($mail) == "") {
			$message = "No se pudo enviar el mail a '".trim($mail)."'.";
		} else {							
			$this->orders->testMail($mail);
			$message = "El mail se envió correctamente .";
		}

		$data['message'] = $message;
		$this->load->section('body', 'login/login_message_view',$data);				
		$this->load->view('templates/template_only_body');
	}

	function deliveryNotes($orderId=-1) {
		if (!$this->my_application->hasPermission("Orders","See") || $orderId <= 0) {
			exit;
		} else {					

			$deliveryNotesParameters['orderIdFilter'] = $orderId;						
			$deliveryNotes = $this->orders->getDeliveryNotes($deliveryNotesParameters);
			$contentData['deliveryNotes'] = $deliveryNotes['list'];	

			$userIsManager = false;
			if (isset($contentData['deliveryNotes'])) {
				$userIsManager = ((float)$contentData['deliveryNotes'][0]['managerId'] == $this->session->userdata('userId'));
			}			
			
			$contentData['allowPrint'] = ($this->my_application->hasPermission("Orders","Print") && ($userIsManager || $this->my_application->hasPermission("Orders","AssignManager") || $this->my_application->hasPermission("Orders","GenerateDN")));
															
			$contentData['byAjax'] = true;	
						
			$view = $this->load->view("orders/orders_deliveryNotes_view",$contentData,true);
			
			$this->output->set_output($view); 										
		}
	}

	function deliveryNote($orderId=-1,$deliveryNoteId=-1) {
		if (!$this->my_application->hasPermission("Orders","See") || $orderId <= 0) {
			exit;
		} else {					

			if ($deliveryNoteId > 0) {
				$deliveryNotesParameters['orderIdFilter'] = $orderId;						
				$deliveryNotesParameters['idFilter'] = $deliveryNoteId;		
				$deliveryNotesParameters['fullData'] = true;
				$deliveryNotes = $this->orders->getDeliveryNotes($deliveryNotesParameters);						
				if ($deliveryNotes['totalRecords'] == 1) {
					$deliveryNote = $deliveryNotes['list'][0];		
					$contentData['deliveryNote'] = $deliveryNote;	
					$contentData['details'] = $deliveryNote['details'];	

					$userIsManager = ((float)$deliveryNote['managerId'] == $this->session->userdata('userId'));

					$contentData['allowPrint'] = ($this->my_application->hasPermission("Orders","Print") && ($userIsManager || $this->my_application->hasPermission("Orders","GenerateDN")) && $deliveryNote['printed'] == 0);										
					$contentData['showReceivedData'] = true;	

					$contentData['allowSaveReceivedData'] = ($this->my_application->hasPermission("Orders","GenerateDN") && ($deliveryNote["orderStateId"] == "READY" || $deliveryNote["orderStateId"] == "INDIST" || $deliveryNote["orderStateId"] == "PENSUP"));					
				} else {
					$deliveryNoteid = -1;	
					$contentData['allowPrint'] = false;					
					$contentData['showReceivedData'] = false;	
				}						
				$contentData['allowSave'] = false;	
			} else {						
				$parameters["idFilter"] = $orderId;			
				$orders = $this->orders->getOrders($parameters);
				if ($orders['totalRecords'] == 1){
					$order = $orders['list'][0];

					$userIsManager = ((float)$order['managerId'] == $this->session->userdata('userId') && $order['stateId'] == "READY");					
				} else {
					$userIsManager = false;
				}	

				$detailsParameters['forDeliveyNote'] = true;				
				$detailsParameters['orderIdFilter'] = $orderId;
				$detailsByOrder = $this->orders->getDetailsByOrder($detailsParameters);
				$contentData['details'] = $detailsByOrder['list'];

				$contentData['allowSave'] = ($this->my_application->hasPermission("Orders","GenerateDN"));				
				$contentData['allowPrint'] = false;
				$contentData['showReceivedData'] = false;	
			}

			
			$contentData['orderId'] = $orderId;
			$contentData['deliveryNoteId'] = $deliveryNoteId;									
															
			$contentData['byAjax'] = true;	
						
			$view = $this->load->view("orders/orders_deliveryNote_view",$contentData,true);
			
			$this->output->set_output($view); 										
		}
	}

	function saveDeliveryNote() {
		$orderId = (float)$this->input->post('orderId',TRUE);
		if (!$this->my_application->hasPermission("Orders","GenerateDN") || !$this->my_application->hasPermission("Orders","Edit") || $orderId <= 0) {
			$error = "No tiene permiso para grabar los datos.";
		} else {
			$error = "";

			$parameters["idFilter"] = $orderId;			
			$orders = $this->orders->getOrders($parameters);
			if ($orders['totalRecords'] == 1){
				$order = $orders['list'][0];	
				/*
				$userIsManager = ((float)$order['managerId'] == $this->session->userdata('userId') && $order['stateId'] == "READY");
				if (!$userIsManager) {
					$error = "No tiene permiso para grabar los datos.";
				}
				*/
			} else {
				$error = "No se pudo grabar los datos.";
			}
								
			if ($error == "") {
				$details = array();		  						
				$detailsCount = (int)$this->input->post('itemsCount',TRUE);
				$detailIdx = -1;
				for($i=1; $i <= $detailsCount; $i++) {
					$itemOrderId = (float)$this->input->post('dnItemOrderId'.$i,TRUE);

					if ($itemOrderId > 0) {
						$detailIdx++;
						$details[$detailIdx]['id'] = (float)$itemOrderId;
						$details[$detailIdx]['quantity'] = $this->input->post('dnFreeQuantity'.$i,TRUE);						
						$details[$detailIdx]['articleId'] = $this->input->post('dnArticleId'.$i,TRUE);												
					}
				}												

				if (count($details) > 0) {					
					$response = $this->orders->setDeliveryNote($orderId,$details);

					if ($response['code'] == "ERROR") {
						$error = $response['message'];	
					}					
				} else {
					$error = "No se pudo grabar los datos.";		
				}				
			}															
		}

		if ($error != "") {		
			$data['data'] = "fun/#/errorDeliveryNotesOrderEdit('".$error."')"; 
		} else {
			$data['data'] = "fun/#/saveOkDeliveryNotesOrderEdit(".$orderId.")"; 
		}
		$data['byAjax'] = true;	
					
		$view = $this->load->view("general_data_view",$data,true);
		
		$this->output->set_output($view); 	
	}	

	function saveReceivedDataDeliveryNote() {
		$orderId = (float)$this->input->post('orderId',TRUE);
		$deliveryNoteId = (float)$this->input->post('deliveryNoteId',TRUE);
		if (!$this->my_application->hasPermission("Orders","GenerateDN") || !$this->my_application->hasPermission("Orders","Edit") || $orderId <= 0 || $deliveryNoteId <= 0) {
			$error = "No tiene permiso para grabar los datos.";
		} else {
			$error = "";

			$parameters["idFilter"] = $deliveryNoteId;			
			$deliveryNotes = $this->orders->getDeliveryNotes($parameters);
			if ($deliveryNotes['totalRecords'] == 1){
				$deliveryNote = $deliveryNotes['list'][0];					
			} else {
				$error = "No se pudo grabar los datos.";
			}
								
			if ($error == "") {		
				$data['id'] = $deliveryNoteId;				
				$data['receivedDate'] = $this->input->post('receivedDate',TRUE);
				$data['receivedBy'] = $this->input->post('receivedBy',TRUE);
				$data['receivedObservation'] = $this->input->post('receivedObservation',TRUE);		
				
				if (!$this->orders->updateRecivedDataDeliveryNote($data)) {										
					$error = "No se pudo grabar los datos.";		
				}								
			}															
		}

		if ($error != "") {		
			$data['data'] = "fun/#/errorDeliveryNotesOrderEdit('".$error."')"; 
		} else {
			$data['data'] = "fun/#/saveOkDeliveryNotesOrderEdit(".$orderId.")"; 
		}
		$data['byAjax'] = true;	
					
		$view = $this->load->view("general_data_view",$data,true);
		
		$this->output->set_output($view); 	
	}	

	function subprioritiesCombo($priorityId=-1,$subpriorityId=-1,$onlySelected=0) {
		if ($this->session->userdata('userLoggedIn')) {						
			$onlySelected = ((int)$onlySelected == 1);
			$combo = "";			
			if ($priorityId > 0) {				
				$parameters['priorityIdFilter'] = $priorityId;	
				$parameters['idFilter'] = $subpriorityId;
				if (!$onlySelected) $parameters['activeOrIdFilter'] = true;					
				$subpriorities = $this->orders->getSubpriorities($parameters);	
				if ($subpriorities['totalRecords'] > 0) {
					if (!$onlySelected || $subpriorityId <= 0) {
						$combo .= '<option value="">[Seleccionar]</option>';					
					}
					if (!$onlySelected || $subpriorityId > 0) {
						$subpriorities = $subpriorities['list'];				
						for ($i=0; $i < count($subpriorities); $i++) {
							$selected = ($subpriorities[$i]['id'] == $subpriorityId || count($subpriorities) == 1?'selected="selected"':'');
							
							$combo .= '<option value="'.$subpriorities[$i]['id'].'" '.$selected.'>'.$subpriorities[$i]['description'].'</option>';
						}
					}
				}
			}			
						
			$contentData['data'] = $combo;			
			
			$contentData['byAjax'] = true;	
						
			$view = $this->load->view("general_data_view",$contentData,true);
			
			$this->output->set_output($view); 			
		}
	}	

	function orderItemData($detailOrderId=-1,$show="ALL") {
		if (!$this->my_application->hasPermission("Orders","See") || $detailOrderId <= 0) {
			exit;
		} else {					

			$contentData['showImports'] = ($show == "ALL" || $show == "IMP");
			$contentData['showQuantities'] = ($show == "ALL" || $show == "QUA");			
								
			$data = $this->orders->getDataByOrderDetail($detailOrderId);
			$contentData['data'] = $data['list'];				
															
			$contentData['byAjax'] = true;	
						
			$view = $this->load->view("orders/orders_itemData_view",$contentData,true);
			
			$this->output->set_output($view); 										
		}
	}

	function updateAllDeliveredQuantity() {
		$this->orders->updateDeliveredQuantity();
	}

	function cancelItemOrder($orderId=-1, $detailOrderId=-1) {
		if (!$this->my_application->hasPermission("Orders","ItemCancel") || $orderId <= 0 || $detailOrderId <= 0) {
			exit;
		} else {					

			$contentData['allowSave'] = true;	
			$contentData['maximumCount'] = $this->orders->cancelableCountOfItemOrder($detailOrderId);							
			$contentData['orderId'] = $orderId;			
			$contentData['detailOrderId'] = $detailOrderId;										

			$historyParameters['detailFilter'] = true;	
			$historyParameters['orderIdFilter'] = $orderId;	
			$historyParameters['entityIdFilter'] = $detailOrderId;							
			$history = $this->orders->getHistoryByOrder($historyParameters);
			$contentData['history'] = $history['list'];							
															
			$contentData['byAjax'] = true;	
						
			$view = $this->load->view("orders/orders_cancelItem_view",$contentData,true);
			
			$this->output->set_output($view); 										
		}
	}

	function saveCancelItemOrder() {
		if (!$this->my_application->hasPermission("Orders","Edit")) {
			exit;	
		} else {
			
			$orderId = (float)$this->input->post('orderId',true);
			$detailOrderId = (float)$this->input->post('detailOrderId',true);
			$count = (float)$this->input->post('count',true);
			$observation = $this->input->post('observation',true);
			
			$response = $this->orders->cancelItemOrder($orderId,$detailOrderId,$count,$observation);
			
			if ($response['code'] == "ERR") {		
				$data['data'] = "fun/#/errorCancelItemOrderEdit('".$response['description']."')"; 
			} else {
				$data['data'] = "fun/#/saveOkCancelItemOrderEdit(".$orderId.")"; 
			}					

			$data['byAjax'] = true;	
						
			$view = $this->load->view("general_data_view",$data,true);
			
			$this->output->set_output($view); 	
		}
	}	

	function budgets($orderId=-1) {
		if (!$this->my_application->hasPermission("Budgets","See") || $orderId <= 0) {
			exit;
		} else {					

			$budgetsParameters['orderIdFilter'] = $orderId;						
			$budgets = $this->orders->getBudgets($budgetsParameters);
			$contentData['budgets'] = $budgets['list'];	
			
			$contentData['byAjax'] = true;	
						
			$view = $this->load->view("orders/orders_budgets_view",$contentData,true);
			
			$this->output->set_output($view); 										
		}
	}

}

/* End of file Orders.php */
/* Location: ./apliccation/controllers/Orders.php */