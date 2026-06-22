<?php
/**
* Controlador Budgets
*
*/

defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Budgets extends CI_Controller {
    
    public function __construct() {
		parent::__construct();
		
	   	$this->output->set_template('default'); 
		$this->output->set_title($this->config->item('applicationName'));	
		$this->form_validation->set_error_delimiters(errorFormatStart(),errorFormatEnd());	
		$this->load->model('budgets_model','budgets');
		$this->load->model('suppliers_model','suppliers');
		$this->load->model('orders_model','orders');
		$this->load->js('assets/js/budgets.js');		
   	}
	
	function index(){		
							
	}	

	function byOrder($orderId=-1) {
		if (!$this->my_application->hasPermission("Budgets","See") || $orderId <= 0) {
			exit;
		} else {					

			$budgetsParameters['orderIdFilter'] = $orderId;						
			$budgets = $this->budgets->getBudgets($budgetsParameters);
			$contentData['budgets'] = $budgets['list'];	

			$contentData['allowDelete'] = $this->my_application->hasPermission("Budgets","Delete");	
			
			$contentData['byAjax'] = true;	
						
			$view = $this->load->view("budgets/budgets_by_order_view",$contentData,true);
			
			$this->output->set_output($view); 										
		}
	}

	function byDetailOrder($detailOrderId=-1) {
		if (!$this->my_application->hasPermission("Budgets","See") || $detailOrderId <= 0) {
			exit;
		} else {					
										
			$budgets = $this->budgets->getBudgetsByDetailOrder($detailOrderId);
			$contentData['budgets'] = $budgets['list'];	
						
			if (isset($budgets['list']) && count($budgets['list']) > 0) {				
				$parameters["detailOrderIdFilter"] = $detailOrderId;					
				$orderStateId = $this->orders->getOrderStateId($parameters);

				$notEditableStates = array("FIN","CAN", "SUS", "NOTAUT","TOAUT");
				$contentData['allowSave'] = ($orderStateId != "" && !in_array($orderStateId, $notEditableStates) && $this->my_application->hasPermission("Budgets","Insert"));
			} else {
				$contentData['allowSave'] = false;
			}		
			$contentData['byAjax'] = true;							
						
			$view = $this->load->view("budgets/budgets_by_detailOrder_view",$contentData,true);
			
			$this->output->set_output($view); 										
		}
	}

	function edit($id=-1,$orderId=-1,$error='',$values=NULL) {
		if ((!$this->my_application->hasPermission("Budgets","Insert") && $id <= 0) || 
			($this->my_application->hasPermission("Budgets","Insert") && $id <= 0 && $orderId <= 0) || 
			!$this->my_application->hasPermission("Budgets","See")) {
			redirect('/main/logout');
		} else {											
			if ($id > 0) {
				$parameters["idFilter"] = $id;
				$parameters["fullData"] = true;				
				$budgets = $this->budgets->getBudgets($parameters);
				if ($budgets['totalRecords'] == 1){
					$budget = $budgets['list'][0];	
					
				} else {
					$id = -1;
				}
			}

			if ($id <= 0) {
				$budget = $this->budgets->getEmptyBudget();								;				
				$budget['orderId'] = $orderId;
			}



			if (isset($values) && isset($values['details'])) $budget['details'] = $values['details'];
			if (isset($values) && isset($values['attachments'])) $budget['attachments'] = $values['attachments'];			
								  	
		  	$contentData['allowEditDetail'] = ($this->my_application->hasPermission("Budgets","Insert") && $id <= 0);															
		  	$contentData['allowSave'] = $contentData['allowEditDetail'];				  				  

			$originsParameters["idFilter"] = $budget["originId"];				
			if ($contentData['allowSave']) {
				$originsParameters["activeOrIdFilter"] = true;			
			}
			$origins = $this->budgets->getBudgetsOrigin($originsParameters);	
			$contentData['origins'] = $origins["list"];			

			$paymentMethodsParameters["idFilter"] = $budget["paymentMethodId"];				
			if ($contentData['allowSave']) {
				$paymentMethodsParameters["activeOrIdFilter"] = true;			
			}
			$paymentMethods = $this->budgets->getPaymentMethods($paymentMethodsParameters);	
			$contentData['paymentMethods'] = $paymentMethods["list"];		
			
			$contentData['billLetters'] = $this->budgets->getBillLetters();

			$contentData['taxPercentages'] = $this->budgets->getTaxPercentages();
			
			$contentData['budget'] = $budget;	
			$contentData['error'] = $error;								                             		
	
			$contentData['callback'] = "initializeBudgetEdit()";

			$data['menuActive'] = "Orders";
			$data['title'] = "Datos del Presupuesto para el Pedido Nro ".$budget['orderId'];

			$data['contentView'] = 'budgets/budgets_edit_view';
			$data['contentData'] = $contentData;
			$this->my_application->loadGeneralTemplate($data);
		}
	}

	function save() {   		
		if ((!$this->my_application->hasPermission("Budgets","Edit") && $this->input->post('id',TRUE) > 0) || 
		    (!$this->my_application->hasPermission("Budgets","Insert") && $this->input->post('id',TRUE) <= 0)) {
			redirect('/orders/listing');	
		} else {
			$id = (float)$this->input->post('id',TRUE);
			$orderId = (float)$this->input->post('orderId',TRUE);

			$allowEditDetail = true;
			$allowEditAttachments = true;			
			
			$this->form_validation->set_rules('date','Fecha Carga','trim|required|xss_clean');
			$this->form_validation->set_rules('userDescription','Usuario','trim|xss_clean');						
			$this->form_validation->set_rules('budgetDate','Fecha Presupuesto','trim|required|xss_clean');
			$this->form_validation->set_rules('originId','Origen','trim|required|xss_clean');
			$this->form_validation->set_rules('supplierCode','CUIT','trim|required|xss_clean');		
			$this->form_validation->set_rules('supplierId','Id Proveedor','trim|xss_clean');
			$this->form_validation->set_rules('supplierDescription','Nombre Comercial Proveedor','trim|xss_clean');			
			$this->form_validation->set_rules('total','Total','trim|xss_clean');			 
			$this->form_validation->set_rules('observation','Observación','trim|max_length[1000]|xss_clean');								
			$this->form_validation->set_rules('billLetter','Letra Factura','trim|required|max_length[1]|xss_clean');	
			$this->form_validation->set_rules('paymentMethodId','Forma de Pago','trim|required|xss_clean');
			$this->form_validation->set_rules('paymentPlan','Plan de Pago','trim|required|max_length[100]|xss_clean');	
	 		
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
						$details[$detailIdx]['code'] = $this->input->post('detailCode'.$i,TRUE);
						$details[$detailIdx]['description'] = $this->input->post('detailDescription'.$i,TRUE);
						$details[$detailIdx]['unitPrice'] = $this->input->post('detailUnitPrice'.$i,TRUE);							
						$details[$detailIdx]['quantity'] = $this->input->post('detailQuantity'.$i,TRUE);																		
						$details[$detailIdx]['taxPercentage'] = $this->input->post('detailTaxPercentage'.$i,TRUE);
						$details[$detailIdx]['familyDescription'] = $this->input->post('detailFamily'.$i,TRUE);												
						$details[$detailIdx]['total'] = $this->input->post('detailTotal'.$i,TRUE);		
						$details[$detailIdx]['detailOrderId'] = $this->input->post('detailItemOrderId'.$i,TRUE);																	
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
				$data['orderId'] = $orderId;	
				$data['date'] = $this->input->post('date',TRUE);
				$data['budgetDate'] = $this->input->post('budgetDate',TRUE);
				$data['originId'] = $this->input->post('originId',TRUE);				
				$data['supplierId'] = $this->input->post('supplierId',TRUE);						
				$data['observation'] = $this->input->post('observation',TRUE);	
				$data['billLetter'] = $this->input->post('billLetter',TRUE);	
				$data['paymentPlan'] = $this->input->post('paymentPlan',TRUE);					
				$data['paymentMethodId'] = $this->input->post('paymentMethodId',TRUE);					
				
				if ($allowEditDetail) $data['details'] = $details;		
				if ($allowEditAttachments) $data['attachments'] = $attachments;				
				
				$response = $this->budgets->setBudget($data);

				$budgetId = (float)$response['budgetId'];

				if ($budgetId > 0) {																							
					redirect('/orders/edit/'.$orderId);	
				} else {	
					if (isset($response['message']) && trim($response['message']) != "") {
						$error = $response['message'];
					} else {
						$error = "No se pudo grabar los datos, intente nuevamente";
					}					
					$this->edit($this->input->post('id',TRUE),$orderId,$error,$values);
				}
			} else {
				$this->edit($this->input->post('id',TRUE),$orderId,"",$values);		
			}
		}
	}	

	function delete($budgetId=-1) {
		$error = "";
		if ($this->session->userdata('userLoggedIn') && $this->my_application->hasPermission("Budgets","Delete")) {					
			if ($this->budgets->deleteBudgetValid($budgetId)) {						
				if (!$this->budgets->deleteBudget($budgetId)) {						
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

	function suppliersFinder($page=1) {
		if (!$this->my_application->hasPermission("Budgets","See")) {
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
				$pageConfiguration = getPageConfiguration(base_url().'budgets/suppliersFinder',$this->config->item('recordsPerPage'),$suppliers["totalRecords"],$pageParameters);
				$this->pagination->initialize($pageConfiguration); 
				$contentData['pagination'] = convertPageToAjax(applyPageStyles($this->pagination->create_links()),'reloadFinderBudgetEdit');	
			} else {
				$contentData['suppliers'] = NULL;				
				$contentData['pagination'] = NULL;
			}

			$contentData['textFilter'] = $textFilter;					
			
			$contentData['filterGet'] = "";					
			if ($textFilter != "") $contentData['filterGet'] .= "&bus=".$textFilter;						
															
			$contentData['byAjax'] = true;	
						
			$view = $this->load->view("budgets/budgets_finder_suppliers_view",$contentData,true);
			
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

	function articlesFinder($orderId=-1,$exludeDetailOrderId=null) {
		if (!$this->my_application->hasPermission("Budgets","Insert") || $orderId <= 0) {
			exit;
		} else {																		
			$parameters['orderIdFilter'] = $orderId;																																	
			$parameters['excludedIdsFilter'] = $exludeDetailOrderId;	
			$articles = $this->orders->getDetailsByOrder($parameters);						
			$contentData['articles'] = $articles["list"];																		

			$contentData['byAjax'] = true;	
						
			$view = $this->load->view("budgets/budgets_finder_articles_view",$contentData,true);
			
			$this->output->set_output($view); 										
		}
	}

	function attachment($budgetId=-1,$attachmentId=-1) {		
		if ($this->my_application->hasPermission("Budgets","See") && $budgetId > 0 && $attachmentId > 0) {			

			$attachmentsParameters['budgetIdFilter'] = $budgetId;						
			$attachmentsParameters['idFilter'] = $attachmentId;	
			$attachments = $this->budgets->getAttachmentsByBudget($attachmentsParameters);			
			if ($attachments['totalRecords'] == 1){
				$attachment = $attachments['list'][0];	

				$path = $this->config->item('files').'budgets/'.$budgetId.'/'.$attachment['internalFilename'];

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

	function saveSelectBudgetItem($budgetItemId=-1) {
		if (!$this->my_application->hasPermission("Budgets","Insert")) {
			exit;	
		} else {
			
			$descriptionError = "";			
			if ($budgetItemId > 0) {
				$parameters["budgetItemIdFilter"] = $budgetItemId;					
				$orderStateId = $this->orders->getOrderStateId($parameters);

				$notEditableStates = array("FIN","CAN", "SUS", "NOTAUT","TOAUT");
				if ($orderStateId != "" && !in_array($orderStateId, $notEditableStates)) {
					if (!$this->budgets->selectBudgetItem($budgetItemId)) {
						$descriptionError = "No se pudo guardar el registro.";		
					}
				} else {
					$descriptionError = "No se pudo guardar el registro.";	
				}				
			} else {
				$descriptionError = "No se pudo guardar el registro.";
			}				
			
			if ($descriptionError != "") {		
				$data['data'] = "fun/#/errorItemBudgetSelectOrderEdit('".$descriptionError."')"; 
			} else {
				$data['data'] = "fun/#/closeModalMessage()"; 
			}					

			$data['byAjax'] = true;	
						
			$view = $this->load->view("general_data_view",$data,true);
			
			$this->output->set_output($view); 	
		}
	}	

	function orderSummary($orderId=-1) {
		if (!$this->my_application->hasPermission("Budgets","See") || $orderId <= 0) {
			exit;
		} else {					

			$summary = $this->budgets->getSummaryByOrder($orderId);									
			
			$contentData['budgets'] = $summary['budgets'];
			$contentData['orderItems'] = $summary['orderItems'];		
			$contentData['budgetItems'] = $summary['budgetItems'];									
			
			$data['menuActive'] = "Orders";
			$data['title'] = "Resumen de Costos para el Pedido Nro ".$orderId;
			if (isset($contentData['budgets']) && count($contentData['budgets']) > 0) {
				$data['title'] .= '  <a href="javascript:generalExport('."'budgets/orderSummaryExport/".$orderId."'".');" class="btn without-padding" title="exportar"><i class="fas fa-download"></i></a>';
        	}

			$data['contentView'] = 'budgets/budgets_summary_by_order_view';
			$data['contentData'] = $contentData;
			$this->my_application->loadGeneralTemplate($data);							
		}
	}

	function orderSummaryExport($orderId=-1) {
		if (!$this->my_application->hasPermission("Budgets","See") || $orderId <= 0) {
			exit;
		} else {					

			$summary = $this->budgets->getSummaryByOrder($orderId);									
			
			$budgets = $summary['budgets'];
			$orderItems = $summary['orderItems'];		
			$budgetItems = $summary['budgetItems'];									
			

			// INITIALITE SPREADSHEET
					
			$spreadsheet = new Spreadsheet();

	        $sheet = $spreadsheet->getActiveSheet();

	        $sheet->setTitle('Resumen');
			
	        // TITLE
	        $title = "RESUMEN DE COSTOS PARA EL PEDIDO NRO ".$orderId;       
	    	
	        $row = 1;
	        $sheet->setCellValue('A'.$row,$title); 
	        $styleCell = array('font'=>array('bold'=>true,'size'=>16));
	        $sheet->getStyle('A'.$row)->applyFromArray($styleCell);
	        $sheet->mergeCells('A'.$row.':'.'J'.$row);
	        
	        // GRID HEADERS        
	        $col = 0;
	        $row++;        

	        $row++;
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Producto"); 
	        $sheet->mergeCells(getLetterOfExcelColumn($col).$row.':'.getLetterOfExcelColumn($col+1).$row);
	        $col = $col + 1;	        
	        if (isset($budgets)) {
	        	$itemNotSelected = false;
                $bestOptionTotal = 0;
                $worstOptionTotal = 0;
                $selectedOptionTotal = 0;
                for ($i=0; $i < count($budgets); $i++) {   
                    $budgets[$i]['total'] = 0;   

                    $col++;
                    $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$budgets[$i]['supplierDescription']); 
	        		$sheet->mergeCells(getLetterOfExcelColumn($col).$row.':'.getLetterOfExcelColumn($col+2).$row);
	        		$col = $col + 2;
                } 

                $col++;
                $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Mejor Opción"); 
        		$sheet->mergeCells(getLetterOfExcelColumn($col).$row.':'.getLetterOfExcelColumn($col+3).$row);
        		$col = $col + 3;
	        }	                            		        
			$styleCell = array(
					            'borders' => array(
							        'allBorders' => array(
							            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 
							            'color' => array('argb' => '000000')
							        )
							    ),
					            'font' => array(
					            	'bold'=>true,
					            	'color' => array('argb' => '00000000')
					            	)
						        );
			$sheet->getStyle('A'.$row.":".getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);		
			$sheet->getStyle('A'.$row.":".getLetterOfExcelColumn($col).$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

			$col = 0;
	        $row++;      
	        $col++;  
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Cant."); 
	        $col++;  
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Descripción"); 
			if (isset($budgets)) {	        	
                for ($i=0; $i < count($budgets); $i++) {                       
                    $col++;
                    $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Neto"); 
                    $col++;
                    $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Iva"); 
                    $col++;
                    $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Total"); 
                } 
                $col++;
                $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Proveedor"); 
                $col++;
                $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Neto"); 
                $col++;
                $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Iva"); 
                $col++;
                $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Total"); 
	        }	                            		        
			$styleCell = array(
					            'borders' => array(
							        'allBorders' => array(
							            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 
							            'color' => array('argb' => '000000')
							        )
							    ),
					            'font' => array(
					            	'bold'=>true,
					            	'color' => array('argb' => '00000000')
					            	)
						        );
			$sheet->getStyle('A'.$row.":".getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);		

			if (isset($orderItems)) {
				for ($i=0; $i < count($orderItems); $i++) {   	                
	                $row++;
		        	$col = 0;

		        	$col++;
		        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,(int)$orderItems[$i]['quantity']); 
		        	$col++;
		        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$orderItems[$i]['description']); 
		        	if (isset($budgets)) {
		        		$bestOption = null;     
		        		$worstOption = null;    
                        $hasSelectedItem = false;     
                        $hasBudgets = false;                             
                        for ($j=0; $j < count($budgets); $j++) {                                                      
                            if (isset($budgetItems) && isset($budgetItems[$orderItems[$i]['id']][$budgets[$j]['id']])) {                                        
                                $itemBudget = $budgetItems[$orderItems[$i]['id']][$budgets[$j]['id']];                                        
                                $itemBudget['supplierDescription'] = $budgets[$j]['supplierDescription'];

                                if (!isset($bestOption) || (isset($bestOption) && (float)$itemBudget['unitPrice'] < (float)$bestOption['unitPrice'])) {
                                    $bestOption = $itemBudget;
                                } 
                                if (!isset($worstOption) || (isset($worstOption) && (float)$itemBudget['unitPrice'] > (float)$worstOption['unitPrice'])) {
                                    $worstOption = $itemBudget;
                                } 
                                if ((int)$itemBudget['selected'] == 1) {
                                    $selectedOptionTotal += $itemBudget['unitPrice'];
                                    $hasSelectedItem = true;
                                }

                                $hasBudgets = true;

                                $col++;
                                $sheet->setCellValue(getLetterOfExcelColumn($col).$row,decimalFormat($itemBudget['unitPrice'],2));  
                                $col++;
                                $sheet->setCellValue(getLetterOfExcelColumn($col).$row,decimalFormat($itemBudget['tax'],2));  
                                $col++;
                                $sheet->setCellValue(getLetterOfExcelColumn($col).$row,decimalFormat($itemBudget['total'],2));   

                                if ((int)$itemBudget['selected'] == 1) {
                                	$styleCell = array(									            
											            'fill' => array(
											                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
											                'startColor' => array('argb' => 'ffa4ffa4')
											            	)
										        );
									$sheet->getStyle(getLetterOfExcelColumn($col-2).$row.":".getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);
                                }
	                    	} else { 
	                    		$col++;
	                    		$sheet->setCellValue(getLetterOfExcelColumn($col).$row,"no cotiza");  
	                    		$sheet->mergeCells(getLetterOfExcelColumn($col).$row.':'.getLetterOfExcelColumn($col+2).$row);
        						$sheet->getStyle(getLetterOfExcelColumn($col).$row.":".getLetterOfExcelColumn($col).$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        						$col = $col + 2;        						
	                    	} 
	                    }

	                    if (!$hasSelectedItem && $hasBudgets) $itemNotSelected = true;
		        	}

		        	if (!isset($bestOption)) $bestOption = array('unitPrice'=>0, 'tax'=>0, 'total'=>0,'supplierDescription'=>'no cotiza');                                
                    $bestOptionTotal += (float)decimalFormat($bestOption['unitPrice'],2);
                    if (isset($worstOption)) $worstOptionTotal += (float)decimalFormat($worstOption['unitPrice'],2);
                    for ($j=0; $j < count($budgets); $j++) {                                                                                          
                        if (isset($budgetItems) && isset($budgetItems[$orderItems[$i]['id']][$budgets[$j]['id']])) {  
                            $itemBudget = $budgetItems[$orderItems[$i]['id']][$budgets[$j]['id']];                                        
                            $budgets[$j]['total'] += (float)decimalFormat($itemBudget['unitPrice'],2);  
                        } else {
                            $budgets[$j]['total'] += (float)decimalFormat($bestOption['unitPrice'],2);  
                        }
                    }

                    $col++;
		        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$bestOption['supplierDescription']);
		        	$col++;
		        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,decimalFormat($bestOption['unitPrice'],2));
		        	$col++;
		        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,decimalFormat($bestOption['tax'],2));
		        	$col++;
		        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,decimalFormat($bestOption['total'],2));

		        	$styleCell = array(
					            'borders' => array(
							        'allBorders' => array(
							            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 
							            'color' => array('argb' => '000000')
							        )
							    )
						        );
					$sheet->getStyle('A'.$row.":".getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);
	            } 
			}	

			if (isset($budgets)) {
				$row++;
				$row++;
		        $col = 1;		        

		        $col++;
		        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Proveedor"); 
		        $col++;
		        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Valor Final"); 

		        $styleCell = array(
					            'borders' => array(
							        'allBorders' => array(
							            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 
							            'color' => array('argb' => '000000')
							        )
							    ),
					            'font' => array(
					            	'bold'=>true,
					            	'color' => array('argb' => '00000000')
					            	)
						        );
				$sheet->getStyle('B'.$row.":".getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);	

				$styleCell = array(
					            'borders' => array(
							        'allBorders' => array(
							            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 
							            'color' => array('argb' => '000000')
							        )
							    )
						        );					

				for ($i=0; $i < count($budgets); $i++) {                      
                    $row++;
	                $col = 1;
	                $col++;
			        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$budgets[$i]['supplierDescription']); 
			        $col++;
			        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,decimalFormat($budgets[$i]['total'],2));
	                $sheet->getStyle('B'.$row.":".getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);
                }

                $styleCell = array(
					            'borders' => array(
							        'allBorders' => array(
							            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 
							            'color' => array('argb' => '000000')
							        )
							    ),
					            'font' => array(
					            	'bold'=>true,
					            	'color' => array('argb' => '00000000')
					            	)
						        );

                $row++;
                $col = 1;
                $col++;
		        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Mejor Opción"); 
		        $col++;
		        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,decimalFormat($bestOptionTotal,2));
                $sheet->getStyle('B'.$row.":".getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);                 

				//--------------------------------------------------------------------

				$row++;
				$row++;
		        $col = 1;		        

		        if ($itemNotSelected) { 
		        	$col++;
			        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"No se puede mostrar el resultado de la Gestión."); 			        

			        $row++;
			        $col = 1;
			        $col++;
			        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Faltan seleccionar items."); 			        

			        /*	
			        $styleCell = array(
						            'borders' => array(
								        'allBorders' => array(
								            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 
								            'color' => array('argb' => '000000')
								        )
								    )
							        );	
			       	$sheet->getStyle('B'.($row - 1).":".getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);	
			       	*/
		        } else {
			        $col++;
			        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Resultado de la Gestión"); 
			        $col++;
			        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,decimalFormat($worstOptionTotal - $selectedOptionTotal,2)); 
			        /*
			        $row++;
			        $col = 1;
			        $col++;
			        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,decimalFormat($worstOptionTotal,2)); 
			        $col++;
			        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,decimalFormat($selectedOptionTotal,2)); 
*/
			        $row++;
			        $col = 1;
			        $col++;
			        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Porc. mejora c/techo o utilización"); 
			        $col++;
			        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,($worstOptionTotal != 0?decimalFormat((($worstOptionTotal - $selectedOptionTotal) * 100) / $worstOptionTotal,2):"0.00")." %"); 

			        $styleCell = array(
						            'borders' => array(
								        'allBorders' => array(
								            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 
								            'color' => array('argb' => '000000')
								        )
								    )
							        );	
			       	$sheet->getStyle('B'.($row - 1).":".getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);	
			    }

		        $styleCell = array(					            
					            'font' => array(
					            	'bold'=>true,
					            	'color' => array('argb' => '00000000')
					            	)
						        );
				$sheet->getStyle('B'.($row - 1).":"."B".$row)->applyFromArray($styleCell);	

			}		

			for ($i=1; $i <= $col; $i++) {
				$sheet->getColumnDimension(getLetterOfExcelColumn($i))->setAutoSize(true);
			}	

			// CREATE TEMP FILE EXCEL, DOWNLOAD Y DELETE 

	        $writer = new Xlsx($spreadsheet); 

			$folder = $this->config->item('files').'/tmp/';
			 
			$filename = 'resumen_costos_'.getCurrentDateId().'.xlsx';
			 
			$writer->save($folder.$filename); 

			$this->load->helper('download');
								
			$fileContent = file_get_contents($folder.$filename);	

			@unlink($folder.$filename);				
								
			force_download($filename,$fileContent);					
		}
	}
}


/* End of file Budgets.php */
/* Location: ./apliccation/controllers/Budgets.php */