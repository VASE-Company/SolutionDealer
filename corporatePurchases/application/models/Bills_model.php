<?php
class Bills_model extends CI_Model{           

    private $company_db;    

    function __construct() {        
        parent::__construct();        

        $this->company_db = $this->load->database('default', TRUE); 
    }        	     	

	function getGeneralFilter($parameters=NULL){		
		$filter = "";
		$filterDeleted = "bills.deleted = 0 ";		

		if (isset($parameters)) {			
			if (isset($parameters['deletedFilter']) && $parameters['deletedFilter'] == true) {
				$filterDeleted = "TRUE ";		
			}

			if (isset($parameters['idFilter']) && $parameters['idFilter'] != "") {
				$filter .= " AND bills.id = ".(float)$parameters['idFilter'];								
			}	
			if (isset($parameters['numberFilter']) && $parameters['numberFilter'] != "") {
				$filter .= " AND (";
				$filter .= "bills.number = ".$this->company_db->escape(completeWith0($parameters['numberFilter'],8));					
				$filter .= " OR ";				
				$filter .= "bills.id = ".(float)$parameters['numberFilter'];					
				$filter .= ")";				
			}				
			if (isset($parameters['companyIdFilter']) && $parameters['companyIdFilter'] > 0) {
				$filter .= " AND bills.companyId = ".$parameters['companyIdFilter'];				
			}								
			if (isset($parameters['userIdFilter']) && $parameters['userIdFilter'] > 0) {
				$filter .= " AND bills.userId = ".$parameters['userIdFilter'];				
			}				
			if (isset($parameters['dateTypeFilter'])) {
				switch ($parameters['dateTypeFilter']) {
					default:
						$dateFiled = "billDate";
						break;
				}
			} else {
				$dateFiled = "billDate";
			}						
			if (isset($parameters['dateFromFilter']) && $parameters['dateFromFilter'] != "") {
				$filter .= " AND bills.".$dateFiled." >= '".$parameters['dateFromFilter']."' ";
			}
			if (isset($parameters['dateToFilter']) && $parameters['dateToFilter'] != "") {
				$filter .= " AND bills.".$dateFiled." <= '".$parameters['dateToFilter']."' ";
			}						
			if (isset($parameters['supplierCodeFilter']) && $parameters['supplierCodeFilter'] != '') {
				$filter .= " AND suppliers.code LIKE ".$this->company_db->escape($parameters['supplierCodeFilter']);
			}	
			if (isset($parameters['articleFilter']) && $parameters['articleFilter'] != '') {								
				$filter .= " AND EXISTS(SELECT detailsByBill.id 
								      	FROM detailsByBill 		
										WHERE detailsByBill.deleted = 0 
											  AND detailsByBill.billId = bills.id
											  AND (detailsByBill.code LIKE ".$this->company_db->escape("%".$parameters['articleFilter']."%")." OR 
											       detailsByBill.description LIKE ".$this->company_db->escape("%".$parameters['articleFilter']."%").") 
											) ";				
			}					
		}
		
		return $filterDeleted.$filter;
	}

	function getGeneralOrder($parameters=NULL){		
		$order = "";

		if (isset($parameters)) {												
			if (isset($parameters['fieldOrder']) && $parameters['fieldOrder'] != '') {
				
				$auxOrder = "";
				if (isset($parameters['typeOrder']) && $parameters['typeOrder'] != '') {						
					switch ($parameters['typeOrder']) {
						case 'asc':
							$auxOrder = "ASC ";
						break;
						case 'desc':
							$auxOrder = "DESC ";
						break;
					}
				}
				
				switch ($parameters['fieldOrder']) {
					case 'id':
						$order .= "bills.id ".$auxOrder;
					break;	
					case 'bdate':
						$order .= "bills.billDate ".$auxOrder.", bills.id ".$auxOrder;
					break;
					case 'udate':
						$order .= "bills.date ".$auxOrder.", bills.id ".$auxOrder;
					break;
					case 'num':
						$order .= "bills.letter ".$auxOrder.", bills.serie ".$auxOrder.", bills.number ".$auxOrder.", bills.id ".$auxOrder;
					break;	
					case 'sup':
						$order .= "suppliers.tradeName ".$auxOrder.", bills.billDate, bills.id ".$auxOrder;
					break;		
					case 'com':
						$order .= "companies.description ".$auxOrder.", bills.billDate, bills.id ".$auxOrder;
					break;									
				}														
			}
		}

		return $order;
	}	

	function getGeneralLimit($parameters=NULL){		
		$limit = "";

		if (isset($parameters)) {																
			if (isset($parameters['page']) && $parameters['page'] > 0) {
				$fromRecord = ($parameters['page'] == 1?0:(($parameters['page']-1)*$this->config->item('recordsPerPage')));		
				$limit = "LIMIT ".$fromRecord.",".$this->config->item('recordsPerPage');
			}
		}

		return $limit;
	}	

	function getBills($parameters=NULL){				
		$bills = array('list'=>NULL, 
		                'totalRecords'=>0);

		$filter = $this->getGeneralFilter($parameters);
		$order = $this->getGeneralOrder($parameters);
		$limit = $this->getGeneralLimit($parameters);		

		if ($order == "") {
			$order .= "bills.date, bills.billDate DESC, bills.id DESC ";
		}	
						
		$sql = "SELECT SQL_CALC_FOUND_ROWS bills.*,                       
                       companies.description AS companyDescription,                       
					   suppliers.code AS supplierCode,                       
                       suppliers.tradeName AS supplierDescription,                       
                       CONCAT(users.lastName, ', ', users.firstName) AS userDescription                       
		        FROM ((bills LEFT JOIN companies ON bills.companyId = companies.id)		       
		        LEFT JOIN suppliers ON suppliers.id = bills.supplierId)
		        LEFT JOIN users ON users.id = bills.userId
				WHERE ".$filter." 
				ORDER BY ".$order.
				$limit;		
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0) {
			$auxOrders = $query->result_array();

			if (isset($parameters)) {
				for ($i=0; $i < count($auxOrders); $i++) {
					$auxOrders[$i]['fullNumber'] = $auxOrders[$i]['letter']." ".$auxOrders[$i]['serie']." ".$auxOrders[$i]['number'];									

					if (isset($parameters['fullData']) && $parameters['fullData'] == true) {
						$detailsParameters['billIdFilter'] = $auxOrders[$i]['id'];
						$detailsParameters['deletedFilter'] = (isset($parameters['deletedFilter']) && $parameters['deletedFilter'] == true);
						$detailsByBill = $this->getDetailsByBill($detailsParameters);
						$auxOrders[$i]['details'] = $detailsByBill['list'];												
					}					
				}				
			}

			$bills = array('list'=>$auxOrders,
		                   'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $bills;
	}
		
	function getDetailsByBill($parameters=NULL){				
		$details = array('list'=>NULL, 
		                 'totalRecords'=>0);
		
		$filter = "";		
		$filterDeleted = "detailsByBill.deleted = 0 ";		
		$limit = "";								

		if (isset($parameters)) {
			if (isset($parameters['deletedFilter']) && $parameters['deletedFilter'] == true) {
				$filterDeleted = "TRUE ";		
			}
			if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
				$filter .= " AND detailsByBill.id = ".$parameters['idFilter'];				
			}
			if (isset($parameters['billIdFilter']) && $parameters['billIdFilter'] > 0) {
				$filter .= " AND detailsByBill.billId = ".$parameters['billIdFilter'];				
			}
			if (isset($parameters['page']) && $parameters['page'] > 0) {
				$fromRecord = ($parameters['page'] == 1?0:(($parameters['page']-1)*$this->config->item('recordsPerPage')));		
				$limit = "LIMIT ".$fromRecord.",".$this->config->item('recordsPerPage');
			}	
		}
				
		$sql = "SELECT SQL_CALC_FOUND_ROWS detailsByBill.*, 
		               IF(ISNULL(families.id),'',families.description) AS familyDescription               
		        FROM (detailsByBill LEFT JOIN articles ON detailsByBill.articleId = articles.id)		        
		        LEFT JOIN families ON articles.familyId = families.id 
				WHERE ".$filterDeleted.$filter." 
				ORDER BY detailsByBill.id ".
				$limit;										
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0) {			

		    $details = array('list'=>$query->result_array(), 
		                     'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $details;
	}
	
	function deleteBill($id=-1){
		if ($this->deleteBillValid($id)){	
			$this->company_db->set('deleted',1);	
			$this->company_db->set('deletedDate',getCurrentDate());	        
			$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         
			$this->company_db->where('id',$id);				
			$this->company_db->update('bills');						

			$error = $this->company_db->error();
			if ((int)$error['code'] != 0) {			
				return false;
			} else {

				$this->load->model('orders_model','orders');	
				$this->load->model('articles_model','articles');				

				$this->company_db->set('deleted',1);	
				$this->company_db->set('deletedDate',getCurrentDate());	        
				$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         				
				$this->company_db->where('deleted',0);
				$this->company_db->where('billId',$id);				
				$this->company_db->update('detailsByBill');

				$this->company_db->set('deleted',1);	
				$this->company_db->set('deletedDate',getCurrentDate());	        
				$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         				
				$this->company_db->where('deleted',0);
				$this->company_db->where('billId',$id);				
				$this->company_db->update('stocksByOrder');

				$sql = "SELECT stocksByOrder.detailOrderId, stocksByOrder.quantity, stocksByOrder.orderId
				        FROM stocksByOrder 
						WHERE stocksByOrder.billId = ".$id;								
				$query = $this->company_db->query($sql);			
				if ($query->num_rows() > 0) {
					$auxDetail = $query->result_array();

					for ($i=0; $i < count($auxDetail); $i++) {						
						$sql = "UPDATE detailsByOrder  			                
				                SET readyQuantity = readyQuantity - ".$auxDetail[$i]['quantity']." 
				                WHERE deleted = 0 AND id = ".$auxDetail[$i]['detailOrderId'];		
						$query = $this->company_db->query($sql);

						$this->orders->updateTotalDetailByOrden($auxDetail[$i]['detailOrderId']);

						$this->orders->updateOrderTotal($auxDetail[$i]['orderId']);						
					}
				}

				$sql = "SELECT articleId
				        FROM detailsByBill 
						WHERE detailsByBill.billId = ".$id;								
				$query = $this->company_db->query($sql);			
				if ($query->num_rows() > 0) {
					$details = $query->result_array();

					for ($i=0; $i < count($details); $i++) {						
						$this->articles->updateLastUnitPrice($details[$i]['articleId']);					
					}
				}

			}
			
			return true;			
		} else {
			return false;
		}		
	}


	function deleteBillValid($id=-1){
		if ($id > 0){	
			$allowDelete = true;	

			if ($allowDelete) {		
				/*
				$sql = "SELECT stocksByOrder.* 
						FROM (stocksByOrder INNER JOIN orders ON stocksByOrder.orderId = orders.id)
						     INNER JOIN detailsByDeliveryNotes ON detailsByDeliveryNotes.detailOrderId = stocksByOrder.detailOrderId						
						WHERE stocksByOrder.deleted = 0 AND orders.deleted = 0 AND detailsByDeliveryNotes.deleted = 0 AND
						      NOT orders.stateId IN ('CAN','SUS') AND 						      
						      stocksByOrder.billId = ".$id." 
						LIMIT 0,1";							
				*/
				$sql = "SELECT stocksByOrder.* 
						FROM (stocksByOrder INNER JOIN orders ON stocksByOrder.orderId = orders.id)
						     INNER JOIN detailsByOrder ON stocksByOrder.detailOrderId = detailsByOrder.id						
						WHERE stocksByOrder.deleted = 0 AND orders.deleted = 0 AND detailsByOrder.deleted = 0 AND
						      NOT orders.stateId IN ('CAN','SUS') AND 	
						      stocksByOrder.quantity > (detailsByOrder.readyQuantity - detailsByOrder.deliveredQuantity) AND 
						      stocksByOrder.billId = ".$id." 
						LIMIT 0,1";			
				$query = $this->company_db->query($sql);						
				if ($query->num_rows() > 0){   
					$allowDelete = false;
				}						
			}		

			return $allowDelete;
		} else {
			return false;
		}		
	}
	
	function getEmptyBill() {
	 	$bill = array('id'=>-1,
					 'date'=>getCurrentDate(false),						 
					 'userId'=>$this->session->userdata('userId'),					 
					 'userDescription'=>$this->session->userdata('userName'),					 
					 'companyId'=>0,					 
					 'warehouseId'=>0,	
					 'supplierId'=>0,	
					 'supplierCode'=>'',
					 'supplierDescription'=>'',
					 'billDate'=>'',
					 'letter'=>'',
					 'serie'=>'',
					 'number'=>'',					 
					 'discounts'=>0,
					 'charges'=>0,
					 'total'=>0,
					 'observation'=>'',
					 'deleted'=>0,					 
					 'details'=>NULL  
			      	);

	 	return $bill;
	}	
	
	function existsValue($value='', $id=-1, $field='') {
		$exists = false;
		
		if ($value != '' && $field != '') {
			$sql = "SELECT * 
					FROM bills 
					WHERE deleted = 0 
					      AND id <> ".$id." 
						  AND ".$field." = ".$this->company_db->escape($value);	
						  								
			$query = $this->company_db->query($sql);						
			if ($query->num_rows() > 0){   
				$exists = true;
			}
		}
		
		return $exists;
	}

	function existsBill($supplierId=0, $letter="", $serie="", $number="", $billId=0) {
		$exists = false;

		if ($supplierId > 0 && $letter != "" && $serie != "" && $number != "") {
			$letter = trim(strtoupper($letter));	
			$serie = completeWith0($serie,5);
			$number = completeWith0($number,8);

			$sql = "SELECT * 
					FROM bills 
					WHERE deleted = 0 
					      AND id <> ".$billId." 
					      AND supplierId = ".$supplierId." 
					      AND letter = ".$this->company_db->escape($letter)." 
					      AND serie = ".$this->company_db->escape($serie)." 
					      AND number = ".$this->company_db->escape($number);	
						  								
			$query = $this->company_db->query($sql);						
			if ($query->num_rows() > 0){   
				$exists = true;
			}
		}
		
		return $exists;
	}

	function setBill($data){
		if (!empty($data)){
			
			if (trim($data['letter']) <> '') $data['letter'] = trim(strtoupper($data['letter']));
			if (trim($data['serie']) <> '') $data['serie'] = completeWith0($data['serie'],5);
			if (trim($data['number']) <> '') $data['number'] = completeWith0($data['number'],8);

			$newBill = ((float)$data['id'] <= 0);			
			
			if ($newBill) {
				$this->company_db->set('companyId',$data['companyId']);
				$this->company_db->set('warehouseId',$data['warehouseId']);
				$this->company_db->set('supplierId',$data['supplierId']);			
			}
			$this->company_db->set('billDate',$data['billDate']);
			$this->company_db->set('letter',$data['letter']);
			$this->company_db->set('serie',$data['serie']);
			$this->company_db->set('number',$data['number']);
			$this->company_db->set('observation',$data['observation']);			

			if (!$newBill) {
				$this->company_db->where('id',$data['id']);				
				$this->company_db->update('bills');				
			} else {	
				$this->company_db->set('date',getCurrentDate());				
				$this->company_db->set('userId',$this->session->userdata('userId'));																				
				$this->company_db->set('total',0);					
				$this->company_db->set('deleted',0);
				$this->company_db->set('deletedDate',NULL);
				$this->company_db->set('deletedUserId',NULL);	 					

				$this->company_db->insert('bills');
			
				$data['id'] = $this->company_db->insert_id();
			}			 
			
			$error = $this->company_db->error();

			if ((int)$error['code'] != 0) {					
				$response = array('billId'=>-1);
				return $response;
			} else {																
				if ($newBill) {
					$this->_setBillDetails($data['id'],$data['details']);				

					$this->_updateBillTotal($data['id']);									
				}
					 							
				$response = array('billId'=>$data['id']);
				return $response;
			}						
		} else {
			$response = array('billId'=>-1);
			return $response;			
		}
	}

	function _setBillDetails($billId=-1,$details=NULL) {	
		if ($billId > 0) { 

			$detailIds = array();
			if (isset($details) && count($details) > 0) {	
				$this->load->model('articles_model','articles');	
				
				$sql = "SELECT bills.warehouseId  
			        	FROM bills 							
						WHERE bills.id = ".$billId;					
				
				$query = $this->company_db->query($sql);			
				if ($query->num_rows() > 0) {
					$row = $query->row_array();

					$warehouseId = (float)$row['warehouseId'];
				} else {
					$warehouseId = 0;					
				}

				for ($i=0; $i < count($details); $i++) {
					$this->company_db->set('articleId',$details[$i]['articleId']);								
					$this->company_db->set('code',$details[$i]['code']);	
					$this->company_db->set('description',$details[$i]['description']);
					$this->company_db->set('quantity',$details[$i]['quantity']);	
					$this->company_db->set('freeQuantity',$details[$i]['quantity']);	
					$this->company_db->set('unitPrice',$details[$i]['unitPrice']);												
					$details[$i]['total'] = $details[$i]['unitPrice'] * $details[$i]['quantity'];					
					$this->company_db->set('total',$details[$i]['total']);	

					if ((float)$details[$i]['id'] > 0) {
						$this->company_db->where('billId',$billId);	
						$this->company_db->where('id',$details[$i]['id']);				
						$this->company_db->update('detailsByBill');				
					} else {																
						$this->company_db->set('billId',$billId);								
						$this->company_db->set('deleted',0);
						$this->company_db->set('deletedDate',NULL);
						$this->company_db->set('deletedUserId',NULL);				
						$this->company_db->insert('detailsByBill');
						$details[$i]['id'] = $this->company_db->insert_id();
					}

					if ((float)$details[$i]['id'] > 0) {
						$detailIds[count($detailIds)] = $details[$i]['id'];		

						if ($warehouseId > 0) {										
							$sql = "SELECT families.affectsStock    
						        	FROM articles INNER JOIN families
						        	ON articles.familyId = families.id							
									WHERE articles.id = ".$details[$i]['articleId'];					
							
							$query = $this->company_db->query($sql);			
							if ($query->num_rows() > 0) {
								$row = $query->row_array();
								
								//HABILITAR USA STOCK
								$articleAffectsStock = ((int)$row['affectsStock'] == 1);								

								if ($articleAffectsStock) {
									/*
									$originEntity['type'] = "bill";
									$originEntity['id'] = $details[$i]['id'];	
									$originEntity['mainId'] = $billId;
									$originEntity['unitPrice'] = $details[$i]['unitPrice'];

									$this->articles->updatePendingStock($details[$i]['articleId'],$warehouseId,$originEntity);									
									*/
									$this->articles->updatePendingStock($details[$i]['articleId'],$warehouseId);									
								}
							} 																						
						}							
						
						$this->articles->updateLastUnitPrice($details[$i]['articleId']);		
					}
				}															
			} 
			
			$this->company_db->set('deleted',1);	        
			$this->company_db->set('deletedDate',getCurrentDate());	        
			$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	 
			$this->company_db->where('billId',$billId);				
			$this->company_db->where('deleted',0);												
			if (count($detailIds) > 0) {
				$this->company_db->where_not_in('id', $detailIds);
			}
			$this->company_db->update('detailsByBill');	
		}
	}	
	
	function _updateBillTotal($billId=-1){
		if ($billId > 0) {
			
			$sql = "SELECT SUM(total) AS detailTotal
		        	FROM detailsByBill  
					WHERE detailsByBill.deleted = 0 AND detailsByBill.billId = ".$billId;					
			
			$query = $this->company_db->query($sql);			
			$detailTotal = 0;	
			if ($query->num_rows() > 0){
				$row = $query->row_array();		

				$detailTotal = $row['detailTotal'];								
			}

			$this->company_db->set('total',$detailTotal);	        				
			$this->company_db->where('id',$billId);				
			$this->company_db->update('bills');	
		}
	}
		
	function getDataByBillDetail($detailBillId=-1){				
		$data = array('list'=>NULL, 
		              'totalRecords'=>0);
		
		if ($detailBillId > 0) {
			$sql = "SELECT bills.deleted
		        	FROM detailsByBill INNER JOIN bills 
		        	ON detailsByBill.billId = bills.id
					WHERE detailsByBill.id = ".$detailBillId;												

			$query = $this->company_db->query($sql);						
			if ($query->num_rows() > 0){
				$row = $query->row_array();						

				$sql = "SELECT SQL_CALC_FOUND_ROWS * 
						FROM (
							SELECT stocksByOrder.orderId AS originId, 
							       stocksByOrder.date,
							       stocksByOrder.date AS originDate,
							       stocksByOrder.quantity,
							       'O' AS type
					        FROM stocksByOrder       
							WHERE ".((int)$row['deleted'] == 1?"":"stocksByOrder.deleted = 0 AND ")."
							      stocksByOrder.detailBillId = ".$detailBillId." 
							UNION ALL
							SELECT stocksByStockMovement.originId, 
							       stocksByStockMovement.date,
							       stockMovements.date AS originDate,
							       stocksByStockMovement.quantity,
							       'SM' AS type
					        FROM stocksByStockMovement INNER JOIN stockMovements
					        ON stocksByStockMovement.originId = stockMovements.id      
							WHERE ".((int)$row['deleted'] == 1?"":"stocksByStockMovement.deleted = 0 AND ")."
							      stocksByStockMovement.detailBillId = ".$detailBillId."
					    ) AS summary 
						ORDER BY summary.date";						

				$query = $this->company_db->query($sql);
				$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
				if ($query->num_rows() > 0){	
					$auxData = $query->result_array();

					for ($i=0; $i < count($auxData); $i++) {
						if ($auxData[$i]['type'] == "O") {
							$auxData[$i]['description'] = "Pedido Nro. ".$auxData[$i]['originId'];
						} else {
							$auxData[$i]['description'] = "Mov. Stock ingresado el ".dateFormat($auxData[$i]['originDate'],false);
						}
					}

					$data = array('list'=>$auxData, 
				                  'totalRecords'=>$queryTotal->row()->totalRecords);
				}
			}					
		}	

		return $data;
	}
}

/* End of file Bills_model.php */
/* Location: ./application/models/Bills_model.php */