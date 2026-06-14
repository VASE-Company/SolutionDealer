<?php
class Budgets_model extends CI_Model{           

    private $company_db;    

    function __construct() {        
        parent::__construct();        

        $this->company_db = $this->load->database('default', TRUE); 
    }        	     	

	function getBudgets($parameters=NULL){				
		$budgets = array('list'=>NULL, 
		                 'totalRecords'=>0);
		
		$filter = "";		
		$limit = "";		

		if (isset($parameters)) {
			if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
				$filter .= " AND budgets.id = ".$parameters['idFilter'];				
			}
			if (isset($parameters['orderIdFilter']) && $parameters['orderIdFilter'] > 0) {
				$filter .= " AND budgets.orderId = ".$parameters['orderIdFilter'];				
			}
			if (isset($parameters['page']) && $parameters['page'] > 0) {
				$fromRecord = ($parameters['page'] == 1?0:(($parameters['page']-1)*$this->config->item('recordsPerPage')));		
				$limit = "LIMIT ".$fromRecord.",".$this->config->item('recordsPerPage');
			}		
		}

		$sql = "SELECT SQL_CALC_FOUND_ROWS budgets.*,
		               CONCAT(users.lastName, ', ', users.firstName) AS userDescription,
		               budgetOrigin.description AS originDescription,
		               suppliers.code AS supplierCode,
		               suppliers.tradeName AS supplierDescription                   
		        FROM ((budgets INNER JOIN users ON budgets.userId = users.id)
		        INNER JOIN budgetOrigin ON budgets.originId = budgetOrigin.id)
		        INNER JOIN suppliers ON budgets.supplierId = suppliers.id  
				WHERE budgets.deleted = 0  ".$filter." 
				ORDER BY budgets.id ".
				$limit;									
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0) {		
			$auxBudgets = $query->result_array();

			for ($i=0; $i < count($auxBudgets); $i++) {								
				if (isset($parameters['fullData']) && $parameters['fullData'] == true) {										
					$sqlDetails = "SELECT detailsByBudget.*, 		               
						               detailsByOrder.code, 
						               detailsByOrder.description, 
						               IF(ISNULL(families.id),'',families.description) AS familyDescription 		               
						        FROM ((detailsByBudget INNER JOIN detailsByOrder ON detailsByBudget.detailOrderId = detailsByOrder.id)
						        LEFT JOIN articles ON detailsByOrder.articleId = articles.id)
						        LEFT JOIN families ON articles.familyId = families.id 
								WHERE detailsByBudget.deleted = 0 AND  detailsByOrder.deleted = 0 
								      AND detailsByBudget.budgetId = ".$auxBudgets[$i]['id']." 
								ORDER BY detailsByBudget.id";							
						
					$queryDetails = $this->company_db->query($sqlDetails);					
					if ($queryDetails->num_rows() > 0) {		
						$auxBudgets[$i]['details'] = $queryDetails->result_array();	
					} else {
						$auxBudgets[$i]['details'] = null;
					}	

					$attachmentsParameters['budgetIdFilter'] = $auxBudgets[$i]['id'];						
					$attachmentsByBudget = $this->getAttachmentsByBudget($attachmentsParameters);
					$auxBudgets[$i]['attachments']  = $attachmentsByBudget['list'];				
				}				
			}
	
			$budgets = array('list'=>$auxBudgets, 
		                     'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $budgets;
	}

	function getEmptyBudget() {
	 	$budget = array('id'=>-1,
						 'date'=>getCurrentDate(false),						 
						 'userId'=>$this->session->userdata('userId'),					 
						 'userDescription'=>$this->session->userdata('userName'),					 					
						 'supplierId'=>0,	
						 'supplierCode'=>'',
						 'supplierDescription'=>'',
						 'budgetDate'=>'',
						 'originId'=>0,						 
						 'total'=>0,
						 'observation'=>'',
						 'billLetter'=>'',
						 'paymentPlan'=>'',
						 'paymentMethodId'=>0,
						 'selected'=>0,
						 'deleted'=>0,					 
						 'details'=>NULL,
						 'attachments'=>NULL  
				      	);

	 	return $budget;
	}	

	function getBudgetsOrigin($parameters=NULL){				
		$origins = array('list'=>NULL, 
		                 'totalRecords'=>0);
		$filter = "";
		if (isset($parameters)) {			
			if (isset($parameters['activeOrIdFilter']) && $parameters['activeOrIdFilter'] == true) {
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND (budgetOrigin.active = 1 OR budgetOrigin.id = ".$parameters['idFilter'].")";				
				} else {
					$filter .= " AND budgetOrigin.active = 1";				
				}				
			} else {			
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND budgetOrigin.id = ".$parameters['idFilter'];				
				}
			}	
		}
					
		$sql = "SELECT SQL_CALC_FOUND_ROWS * 
		        FROM budgetOrigin 	        
				WHERE budgetOrigin.deleted = 0 ".$filter." 
				ORDER BY budgetOrigin.description";	
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){		
			$origins = array('list'=>$query->result_array(), 
		                     'totalRecords'=>$queryTotal->row()->totalRecords);
		}	

		return $origins;
	}

	function getPaymentMethods($parameters=NULL){				
		$paymentMethods = array('list'=>NULL, 
		                 		'totalRecords'=>0);
		$filter = "";
		if (isset($parameters)) {			
			if (isset($parameters['activeOrIdFilter']) && $parameters['activeOrIdFilter'] == true) {
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND (paymentMethods.active = 1 OR paymentMethods.id = ".$parameters['idFilter'].")";				
				} else {
					$filter .= " AND paymentMethods.active = 1";				
				}				
			} else {			
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND paymentMethods.id = ".$parameters['idFilter'];				
				}
			}	
		}		
					
		$sql = "SELECT SQL_CALC_FOUND_ROWS * 
		        FROM paymentMethods 	        
				WHERE paymentMethods.deleted = 0 ".$filter." 
				ORDER BY paymentMethods.description";					
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){		
			$paymentMethods = array('list'=>$query->result_array(), 
		                     		'totalRecords'=>$queryTotal->row()->totalRecords);
		}	

		return $paymentMethods;
	}

	function deleteBudget($id=-1){
		if ($this->deleteBudgetValid($id)){	
			$this->company_db->set('deleted',1);	
			$this->company_db->set('deletedDate',getCurrentDate());	        
			$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         
			$this->company_db->where('id',$id);				
			$this->company_db->update('budgets');						

			$error = $this->company_db->error();
			if ((int)$error['code'] != 0) {	
				$this->company_db->set('deleted',1);	
				$this->company_db->set('deletedDate',getCurrentDate());	        
				$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         				
				$this->company_db->where('deleted',0);
				$this->company_db->where('budgetId',$id);				
				$this->company_db->update('detailsByBudget');

				return false;
			}
			
			return true;			
		} else {
			return false;
		}		
	}


	function deleteBudgetValid($id=-1){
		if ($id > 0){	
			$allowDelete = true;	

			if ($allowDelete) {		

				//FALTA VALIDAR QUE NO SE ELIMINE UN PRESUPUESTO MARCADO COMO PRINCIPAL		
			}		

			return $allowDelete;
		} else {
			return false;
		}		
	}

	function setBudget($data){
		if (!empty($data)){
						
			$newBudget = ((float)$data['id'] <= 0);			
									
			$this->company_db->set('supplierId',$data['supplierId']);
			$this->company_db->set('budgetDate',$data['budgetDate']);
			$this->company_db->set('originId',$data['originId']);			
			$this->company_db->set('observation',$data['observation']);				
			$this->company_db->set('billLetter',$data['billLetter']);		
			$this->company_db->set('paymentPlan',$data['paymentPlan']);					
			$this->company_db->set('paymentMethodId',$data['paymentMethodId']);					
		
			if (!$newBudget) {
				$this->company_db->where('id',$data['id']);				
				$this->company_db->update('budgets');				
			} else {	
				$this->company_db->set('orderId',$data['orderId']);		
				$this->company_db->set('date',getCurrentDate());				
				$this->company_db->set('userId',$this->session->userdata('userId'));																				
				$this->company_db->set('total',0);									
				$this->company_db->set('deleted',0);
				$this->company_db->set('deletedDate',NULL);
				$this->company_db->set('deletedUserId',NULL);	 					

				$this->company_db->insert('budgets');
			
				$data['id'] = $this->company_db->insert_id();
			}			 
			
			$error = $this->company_db->error();

			if ((int)$error['code'] != 0) {						
				$response = array('budgetId'=>-1);
				return $response;
			} else {																
				if ($newBudget) {
					$this->_setBudgetDetails($data['id'],$data['details']);				

					$this->_updateBudgetTotal($data['id']);	

					if (isset($data['attachments']))
						$this->_setBudgetAttachments($data['id'],$data['attachments']);								
				}
					 							
				$response = array('budgetId'=>$data['id']);
				return $response;
			}						
		} else {
			$response = array('budgetId'=>-1);
			return $response;			
		}
	}

	function _setBudgetDetails($budgetId=-1,$details=NULL) {	
		if ($budgetId > 0) { 

			$detailIds = array();
			if (isset($details) && count($details) > 0) {	

				for ($i=0; $i < count($details); $i++) {	
					$this->company_db->set('detailOrderId',$details[$i]['detailOrderId']);													
					$this->company_db->set('quantity',$details[$i]['quantity']);						
					$this->company_db->set('unitPrice',$details[$i]['unitPrice']);			
					$this->company_db->set('taxPercentage',$details[$i]['taxPercentage']);														
					$details[$i]['total'] = $details[$i]['unitPrice'] * $details[$i]['quantity'];					
					$this->company_db->set('total',$details[$i]['total']);	

					if ((float)$details[$i]['id'] > 0) {
						$this->company_db->where('budgetId',$budgetId);	
						$this->company_db->where('id',$details[$i]['id']);				
						$this->company_db->update('detailsByBudget');				
					} else {																
						$this->company_db->set('budgetId',$budgetId);								
						$this->company_db->set('selected',0);
						$this->company_db->set('deleted',0);
						$this->company_db->set('deletedDate',NULL);
						$this->company_db->set('deletedUserId',NULL);				
						$this->company_db->insert('detailsByBudget');
						$details[$i]['id'] = $this->company_db->insert_id();
					}

					if ((float)$details[$i]['id'] > 0) {
						$detailIds[count($detailIds)] = $details[$i]['id'];									
					}
				}															
			} 
			
			$this->company_db->set('deleted',1);	        
			$this->company_db->set('deletedDate',getCurrentDate());	        
			$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	 
			$this->company_db->where('budgetId',$budgetId);				
			$this->company_db->where('deleted',0);												
			if (count($detailIds) > 0) {
				$this->company_db->where_not_in('id', $detailIds);
			}
			$this->company_db->update('detailsByBudget');	
		}
	}	

	function _updateBudgetTotal($budgetId=-1){
		if ($budgetId > 0) {
			
			$sql = "SELECT SUM(total) AS detailTotal
		        	FROM detailsByBudget  
					WHERE detailsByBudget.deleted = 0 AND detailsByBudget.budgetId = ".$budgetId;					
			
			$query = $this->company_db->query($sql);			
			$detailTotal = 0;	
			if ($query->num_rows() > 0){
				$row = $query->row_array();		

				$detailTotal = $row['detailTotal'];								
			}

			$this->company_db->set('total',$detailTotal);	        				
			$this->company_db->where('id',$budgetId);				
			$this->company_db->update('budgets');	
		}
	}

	function getBudgetsByDetailOrder($detailOrderId=-1){				
		$budgets = array('list'=>NULL, 
		                 'totalRecords'=>0);
		
		$sql = "SELECT SQL_CALC_FOUND_ROWS 
					   budgets.id,
		               budgets.budgetDate,		               
		               suppliers.code AS supplierCode,
		               suppliers.tradeName AS supplierDescription,
		               detailsByBudget.id AS budgetItemId,
		               detailsByBudget.unitPrice,
		               detailsByBudget.selected                     
		        FROM (detailsByBudget INNER JOIN budgets ON detailsByBudget.budgetId = budgets.id)		        
		        INNER JOIN suppliers ON budgets.supplierId = suppliers.id  
				WHERE detailsByBudget.deleted = 0 AND budgets.deleted = 0 AND 
		               detailsByBudget.unitPrice > 0 AND 					  
				      detailsByBudget.detailOrderId = ".$detailOrderId." 
				ORDER BY detailsByBudget.id";												
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0) {		
			$budgets = array('list'=> $query->result_array(), 
		                     'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $budgets;
	}

	function getAttachmentsByBudget($parameters=NULL){				
		$attachments = array('list'=>NULL, 
		                     'totalRecords'=>0);;
		
		$filter = "";		
		$limit = "";		

		if (isset($parameters)) {
			if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
				$filter .= " AND attachmentsByBudget.id = ".$parameters['idFilter'];								
			}
			if (isset($parameters['budgetIdFilter']) && $parameters['budgetIdFilter'] > 0) {
				$filter .= " AND attachmentsByBudget.budgetId = ".$parameters['budgetIdFilter'];				
			}			
			if (isset($parameters['page']) && $parameters['page'] > 0) {
				$fromRecord = ($parameters['page'] == 1?0:(($parameters['page']-1)*$this->config->item('recordsPerPage')));		
				$limit = "LIMIT ".$fromRecord.",".$this->config->item('recordsPerPage');
			}		
		}
				
		$sql = "SELECT SQL_CALC_FOUND_ROWS attachmentsByBudget.*,
					   users.lastName AS userLastName, 
					   users.firstName AS userFirstName 
		        FROM attachmentsByBudget 
		        LEFT JOIN users ON attachmentsByBudget.userId = users.id
				WHERE attachmentsByBudget.deleted = 0 ".$filter." 
				ORDER BY attachmentsByBudget.id ".
				$limit;						
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0) {			
			$attachments = array('list'=>$query->result_array(), 
		                      	 'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $attachments;
	}

	function _setBudgetAttachments($budgetId=-1,$attachments=NULL) {			
		if ($budgetId > 0 && $this->my_application->hasPermission("Budgets","See")) { 
			$attachmentIds = array();
			if (isset($attachments) && count($attachments) > 0) {			
				for ($i=0; $i < count($attachments); $i++) {											
					if ((float)$attachments[$i]['id'] <= 0) {												
						$pathFrom = $this->config->item('files').'tmp/'.$attachments[$i]['internalFilename'];
						
						$pathTo = $this->config->item('files').'budgets';
						checkCreateFolder($pathTo);		
						$pathTo .= "/".$budgetId;
						checkCreateFolder($pathTo);
						$pathTo .= '/'.$attachments[$i]['internalFilename'];

						if (rename($pathFrom, $pathTo)) {
							$this->company_db->set('budgetId',$budgetId);								
							$this->company_db->set('date',getCurrentDate());	        
							$this->company_db->set('userId',$this->session->userdata('userId'));	 												
							$this->company_db->set('filename',$attachments[$i]['filename']);
							$this->company_db->set('internalFilename',$attachments[$i]['internalFilename']);
							$this->company_db->set('deleted',0);
							$this->company_db->set('deletedDate',NULL);
							$this->company_db->set('deletedUserId',NULL);				
							$this->company_db->insert('attachmentsByBudget');
							$attachments[$i]['id'] = $this->company_db->insert_id();
						}
					}

					if ((float)$attachments[$i]['id'] > 0) {
						$attachmentIds[count($attachmentIds)] = $attachments[$i]['id'];						
					}
				}															
			} 
			
			$this->company_db->set('deleted',1);	        
			$this->company_db->set('deletedDate',getCurrentDate());	        
			$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	 
			$this->company_db->where('budgetId',$budgetId);				
			$this->company_db->where('deleted',0);												
			if (count($attachmentIds) > 0) {
				$this->company_db->where_not_in('id', $attachmentIds);
			}
			$this->company_db->update('attachmentsByBudget');	
										
		}
	}	

	function selectBudgetItem($budgetItemId=-1) {
		$updateOk = false;
		
		if ($budgetItemId > 0) {
			$sql = "SELECT detailOrderId
		        	FROM detailsByBudget  
					WHERE detailsByBudget.deleted = 0 AND detailsByBudget.id = ".$budgetItemId;					
			
			$query = $this->company_db->query($sql);						
			if ($query->num_rows() > 0){
				$row = $query->row_array();	

				$detailOrderId = $row["detailOrderId"];

				$this->company_db->set('selected',0);	        				
				$this->company_db->where('detailOrderId',$detailOrderId);				
				$this->company_db->where('deleted',0);												
				$this->company_db->update('detailsByBudget');

				$this->company_db->set('selected',1);	        				
				$this->company_db->where('id',$budgetItemId);				
				$this->company_db->where('deleted',0);												
				$this->company_db->update('detailsByBudget');

				$updateOk = true;
			}			
		}

		return $updateOk;
	}

	function getSummaryByOrder($orderId=-1) {
		$summary = array('budgets'=>NULL,
	                     'orderItems'=>NULL,
	                 	 'budgetItems'=>NULL);

		if ($orderId > 0) {
			//Budgets
			$sql = "SELECT budgets.id,			               
			               suppliers.code AS supplierCode,
			               suppliers.tradeName AS supplierDescription                   
			        FROM budgets INNER JOIN suppliers ON budgets.supplierId = suppliers.id  
					WHERE budgets.deleted = 0 AND budgets.orderId = ".$orderId." 
					ORDER BY budgets.id";									
			
			$query = $this->company_db->query($sql);			
			if ($query->num_rows() > 0) {		
				$summary['budgets'] = $query->result_array();
			}

			//Order Items
			$sql = "SELECT detailsByOrder.id,			               
			               detailsByOrder.code,
			               detailsByOrder.description,
			               detailsByOrder.quantity,
			               detailsByOrder.unitPrice                   
			        FROM detailsByOrder 
					WHERE detailsByOrder.deleted = 0 AND detailsByOrder.orderId = ".$orderId." 
					ORDER BY detailsByOrder.id";									
			
			$query = $this->company_db->query($sql);			
			if ($query->num_rows() > 0) {		
				$summary['orderItems'] = $query->result_array();
			}

			//Budget Items
			$sql = "SELECT detailsByBudget.budgetId,			               
			               detailsByBudget.detailOrderId,
			               detailsByBudget.unitPrice,
			               detailsByBudget.taxPercentage,
			               detailsByBudget.selected,
			               detailsByOrder.quantity 			                                
			        FROM detailsByBudget INNER JOIN detailsByOrder ON detailsByBudget.detailOrderId = detailsByOrder.id 
					WHERE detailsByBudget.deleted = 0 AND detailsByOrder.deleted = 0 AND detailsByBudget.unitPrice > 0 AND 
					      detailsByOrder.orderId = ".$orderId;									
			
			$query = $this->company_db->query($sql);			
			if ($query->num_rows() > 0) {		
				$auxBudgetItems = $query->result_array();
								
				for ($i=0; $i < count($auxBudgetItems); $i++) {					
					$auxBudgetItem['unitPrice'] = (float)decimalFormat($auxBudgetItems[$i]['unitPrice'] * $auxBudgetItems[$i]['quantity'],2);
					$auxBudgetItem['tax'] = (float)decimalFormat(($auxBudgetItem['unitPrice'] * $auxBudgetItems[$i]['taxPercentage']) / 100,2);
					$auxBudgetItem['total'] = (float)decimalFormat($auxBudgetItem['unitPrice'] + $auxBudgetItem['tax'],2);
					$auxBudgetItem['selected'] = $auxBudgetItems[$i]['selected'];				

					$budgetItems[$auxBudgetItems[$i]['detailOrderId']][$auxBudgetItems[$i]['budgetId']] = $auxBudgetItem;
				}				

				$summary['budgetItems'] = $budgetItems;
			}
		}

		return $summary;
	}

	function getBillLetters(){				
		$billLetters[0] = array('id'=>'A','description'=>'A');
		$billLetters[1] = array('id'=>'B','description'=>'B');
		$billLetters[2] = array('id'=>'C','description'=>'C');

		return $billLetters;
	}

	function getTaxPercentages(){						
		$taxPercentages[0] = array('id'=>21,'description'=>'21.00');
		$taxPercentages[1] = array('id'=>10.5,'description'=>'10.50');		

		return $taxPercentages;
	}

}

/* End of file Budgets_model.php */
/* Location: ./application/models/Budgets_model.php */