<?php
class StockMovements_model extends CI_Model{           
    
    private $company_db;         

	function __construct() {        
        parent::__construct();        

        $this->company_db = $this->load->database('default', TRUE); 
    } 

   	function getStockMovements($parameters=NULL){				
		$stockMovements = array('list'=>NULL, 
		                        'totalRecords'=>0);
				
		$stockMovementsFilter = "";
		$deliveryNotesFilter = "";
		$billsFilter = "";
		$order = "";
		$limit = "";	
		if (isset($parameters)) {
			if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0 && isset($parameters['registerTypeFilter']) && $parameters['registerTypeFilter'] != "") {			
				switch ($parameters['registerTypeFilter']) {
					case "SM":
						$stockMovementsFilter .= " AND stockMovements.id = ".$parameters['idFilter'];				
						$deliveryNotesFilter .= " AND detailsByDeliveryNotes.id = -1";				
						$billsFilter .= " AND detailsByBill.id = -1";				
					break;

					case "DN":
						$stockMovementsFilter .= " AND stockMovements.id = -1";				
						$deliveryNotesFilter .= " AND detailsByDeliveryNotes.id = ".$parameters['idFilter'];				
						$billsFilter .= " AND detailsByBill.id = -1";				
					break;

					case "BILL":
						$stockMovementsFilter .= " AND stockMovements.id = -1";				
						$deliveryNotesFilter .= " AND detailsByDeliveryNotes.id = -1";				
						$billsFilter .= " AND detailsByBill.id = ".$parameters['idFilter'];				
					break;
				}		
			}		
			if (isset($parameters['observationFilter']) && trim($parameters['observationFilter']) != '') {
				$stockMovementsFilter .= " AND stockMovements.observation LIKE ".$this->company_db->escape("%".$parameters['observationFilter']."%");
				$deliveryNotesFilter .= " AND CONCAT('Remito Nro. ', orders.id) LIKE ".$this->company_db->escape("%".$parameters['observationFilter']."%");
				$billsFilter .= " AND CONCAT('Factura Nro. ', bills.letter, ' ', bills.serie, ' ', bills.number) LIKE ".$this->company_db->escape("%".$parameters['observationFilter']."%");				
				$includePreviousStock = false;	
			}
			if (isset($parameters['typeIdFilter']) && $parameters['typeIdFilter'] > 0) {
				$stockMovementsFilter .= " AND stockMovements.typeId = ".$parameters['typeIdFilter'];
				$deliveryNotesFilter .= " AND ".$this->config->item('deliveryNoteMovementTypeId')." = ".$parameters['typeIdFilter'];
				$billsFilter .= " AND ".$this->config->item('billMovementTypeId')." = ".$parameters['typeIdFilter'];
			}
			if (isset($parameters['warehouseIdFilter']) && $parameters['warehouseIdFilter'] > 0) {
				$stockMovementsFilter .= " AND stockMovements.warehouseId = ".$parameters['warehouseIdFilter'];
				$deliveryNotesFilter .= " AND detailsByDeliveryNotes.warehouseId = ".$parameters['warehouseIdFilter'];
				$billsFilter .= " AND bills.warehouseId = ".$parameters['warehouseIdFilter'];
			}
			if (isset($parameters['articleFilter']) && $parameters['articleFilter'] != "") {
				$sqlArticles = "SELECT id 
			        			FROM articles  
								WHERE deleted = 0 AND code = ".$this->company_db->escape($parameters['articleFilter']);																
				$queryArticles = $this->company_db->query($sqlArticles);			
				if ($queryArticles->num_rows() > 0){
					$row = $queryArticles->row_array();

					$articleId = $row['id'];					
				} else {
					$articleId = -1;
				}
				$stockMovementsFilter .= " AND stockMovements.articleId = ".$articleId;
				$deliveryNotesFilter .= " AND detailsByDeliveryNotes.articleId = ".$articleId;
				$billsFilter .= " AND detailsByBill.articleId = ".$articleId;
			}
			if (isset($parameters['inputFilter']) && $parameters['inputFilter'] != "") {
				$stockMovementsFilter .= " AND stockMovements.input = ".$parameters['inputFilter'];
				$deliveryNotesFilter .= " AND 0 = ".$parameters['inputFilter'];
				$billsFilter .= " AND 1 = ".$parameters['inputFilter'];
			}			
			if (isset($parameters['dateFromFilter']) && $parameters['dateFromFilter'] != "") {
				$stockMovementsFilter .= " AND stockMovements.date >= '".$parameters['dateFromFilter']." 00:00:00' ";
				$deliveryNotesFilter .= " AND deliveryNotes.date >= '".$parameters['dateFromFilter']." 00:00:00' ";
				$billsFilter .= " AND bills.billDate >= '".$parameters['dateFromFilter']." 00:00:00' ";
			}
			if (isset($parameters['dateToFilter']) && $parameters['dateToFilter'] != "") {
				$stockMovementsFilter .= " AND stockMovements.date <= '".$parameters['dateToFilter']." 23:59:59.99' ";
				$deliveryNotesFilter .= " AND deliveryNotes.date <= '".$parameters['dateToFilter']." 23:59:59.99' ";
				$billsFilter .= " AND bills.billDate <= '".$parameters['dateToFilter']." 23:59:59.99' ";
			}	
			if (isset($parameters['page']) && $parameters['page'] > 0) {
				$fromRecord = ($parameters['page'] == 1?0:(($parameters['page']-1)*$this->config->item('recordsPerPage')));		
				$limit = "LIMIT ".$fromRecord.",".$this->config->item('recordsPerPage');
			}
			
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
					case 'date':						
						$order .= "YEAR(summary.date) ".$auxOrder.", MONTH(summary.date) ".$auxOrder.", DAY(summary.date) ".$auxOrder.", summary.realDate ".$auxOrder;	
					break;				
				}														
			}
		}
		
		if ($order == "") {
			$order .= "YEAR(summary.date) ASC, MONTH(summary.date) ASC, DAY(summary.date) ASC, summary.realDate ASC ";			
		}	

		$sqlMovements = "SELECT stockMovements.id, 
		                        'SM' AS registerType,
								stockMovements.date, 
								stockMovements.date AS realDate,
								stockMovements.userId,
		                        stockMovements.typeId, 
		                        stockMovements.input, 
		                        stockMovements.companyId,
		                        stockMovements.warehouseId, 
		                        stockMovements.articleId, 
		                        stockMovements.quantity,		                        
		                        stockMovements.unitPrice,
		                        stockMovements.observation 
		                 FROM stockMovements 
		                 WHERE stockMovements.deleted = 0 ".$stockMovementsFilter;		                
		
		$sqDeliveryNotes = "SELECT detailsByDeliveryNotes.id, 
								   'DN' AS registerType,	
								   deliveryNotes.date, 
								   deliveryNotes.date AS realDate, 
								   deliveryNotes.userId,
		                           ".$this->config->item('deliveryNoteMovementTypeId')." AS typeId, 
		                           0 AS input, 
		                           branchOffices.companyId,
		                           detailsByDeliveryNotes.warehouseId, 
		                           detailsByDeliveryNotes.articleId, 
		                           detailsByDeliveryNotes.quantity,
		                           0 AS unitPrice,
		                           CONCAT('Remito Nro. ', deliveryNotes.id,' - Pedido Nro. ', orders.id) AS observation 
		                    FROM ((detailsByDeliveryNotes INNER JOIN deliveryNotes ON detailsByDeliveryNotes.deliveryNoteId = deliveryNotes.id)
		                    INNER JOIN orders ON deliveryNotes.orderId = orders.id)
		                    INNER JOIN branchOffices ON orders.branchOfficeId = branchOffices.id
		                    WHERE detailsByDeliveryNotes.deleted = 0 AND deliveryNotes.deleted = 0 AND orders.deleted = 0  ".$deliveryNotesFilter; 

		$sqlBills = "SELECT detailsByBill.id, 
						   'BILL' AS registerType,	
						   bills.billDate AS date, 
						   bills.date AS realDate, 
						   bills.userId,
                           ".$this->config->item('billMovementTypeId')." AS typeId, 
                           1 AS input, 
                           bills.companyId,
                           bills.warehouseId, 
                           detailsByBill.articleId, 
                           detailsByBill.quantity,
                           detailsByBill.unitPrice,
                           CONCAT('Factura Nro. ', bills.letter, ' ', bills.serie, ' ', bills.number) AS observation 
                    FROM detailsByBill INNER JOIN bills ON detailsByBill.billId = bills.id                    
                    WHERE detailsByBill.deleted = 0 AND bills.deleted = 0 ".$billsFilter; 
		                              

		$sql = "SELECT SQL_CALC_FOUND_ROWS
		               summary.*,
		               IF(summary.input = 1,'Entrada','Salida') AS inputDescription, 
		               CONCAT(users.lastName, ', ', users.firstName) AS userDescription, 
		               articles.code AS articleCode, articles.description AS articleDescription,
		               companies.description AS companyDescription,
		               warehouses.description AS warehouseDescription,
		               movementsTypes.description AS typeDescription,
		               families.description AS familyDescription  
		        FROM ((((((".
		        $sqlMovements.
		        " UNION ALL ".
				$sqDeliveryNotes.
		        " UNION ALL ".
		        $sqlBills.") AS summary 
		        INNER JOIN users ON summary.userId = users.id)
		        INNER JOIN articles ON summary.articleId = articles.id)		        
		        INNER JOIN companies ON summary.companyId = companies.id)
		        INNER JOIN warehouses ON summary.warehouseId = warehouses.id)
		        INNER JOIN movementsTypes ON summary.typeId = movementsTypes.id)
		        INNER JOIN families ON articles.familyId = families.id 			             
				ORDER BY ".$order.
				$limit;	

		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){
			$stockMovements = array('list'=>$query->result_array(), 
		                            'totalRecords'=>$queryTotal->row()->totalRecords);

			if ($stockMovements['totalRecords'] == 1) {
				$stockMovements['list'][0]['details'][0] = array('id'=>$stockMovements['list'][0]['id'],
			                                                	  'articleId'=>$stockMovements['list'][0]['articleId'],
			                                            	      'code'=>$stockMovements['list'][0]['articleCode'],
			                                                  	  'description'=>$stockMovements['list'][0]['articleDescription'],
			                                                  	  'familyDescription'=>$stockMovements['list'][0]['familyDescription'],
			                                                  	  'quantity'=>$stockMovements['list'][0]['quantity'],
			                                                  	  'unitPrice'=>$stockMovements['list'][0]['unitPrice']);
			}
		}		
		
		return $stockMovements;
	}

	function getPreviusStock($parameters=NULL){				
		$includePreviusStock = true;		

		$stockMovementsFilter = "";
		$deliveryNotesFilter = "";
		$billsFilter = "";		
		if (isset($parameters)) {
			if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) $includePreviusStock = false;
			if (isset($parameters['registerTypeFilter']) && $parameters['registerTypeFilter'] != "") $includePreviusStock = false;
			if (isset($parameters['observationFilter']) && trim($parameters['observationFilter']) != '') $includePreviusStock = false;
			if (isset($parameters['typeIdFilter']) && $parameters['typeIdFilter'] > 0) $includePreviusStock = false;
			if (isset($parameters['inputFilter']) && $parameters['inputFilter'] != "") $includePreviusStock = false;
			if (isset($parameters['fieldOrder']) && !($parameters['fieldOrder'] == 'date' || $parameters['fieldOrder'] == '')) $includePreviusStock = false;
			if (isset($parameters['typeOrder']) && !($parameters['typeOrder'] == 'asc' || $parameters['typeOrder'] == '')) $includePreviusStock = false;			

			if (isset($parameters['warehouseIdFilter']) && $parameters['warehouseIdFilter'] > 0) {
				$stockMovementsFilter .= " AND stockMovements.warehouseId = ".$parameters['warehouseIdFilter'];
				$deliveryNotesFilter .= " AND detailsByDeliveryNotes.warehouseId = ".$parameters['warehouseIdFilter'];
				$billsFilter .= " AND bills.warehouseId = ".$parameters['warehouseIdFilter'];
			} else {
				$includePreviusStock = false;
			}
			if (isset($parameters['articleFilter']) && $parameters['articleFilter'] != "") {
				$sqlArticles = "SELECT id 
			        			FROM articles  
								WHERE deleted = 0 AND code = ".$this->company_db->escape($parameters['articleFilter']);																
				$queryArticles = $this->company_db->query($sqlArticles);			
				if ($queryArticles->num_rows() > 0){
					$row = $queryArticles->row_array();

					$articleId = $row['id'];					

					$stockMovementsFilter .= " AND stockMovements.articleId = ".$articleId;
					$deliveryNotesFilter .= " AND detailsByDeliveryNotes.articleId = ".$articleId;
					$billsFilter .= " AND detailsByBill.articleId = ".$articleId;
				} else {
					$includePreviusStock = false;
				}				
			} else {
				$includePreviusStock = false;
			}	
			if (isset($parameters['dateFromFilter']) && $parameters['dateFromFilter'] != "") {
				$stockMovementsFilter .= " AND stockMovements.date < '".$parameters['dateFromFilter']." 00:00:00.00' ";
				$deliveryNotesFilter .= " AND deliveryNotes.date < '".$parameters['dateFromFilter']." 00:00:00.00' ";
				$billsFilter .= " AND bills.billDate < '".$parameters['dateFromFilter']." 00:00:00.00' ";
			}		
		} else {
			$includePreviusStock = false;
		}	


		$previusStock = NULL;
		if ($includePreviusStock) {
			$sqlMovements = "SELECT SUM(IF(stockMovements.input = 1,1,-1) * stockMovements.quantity) AS auxQuantity		                        		                        
			                 FROM stockMovements 
			                 WHERE stockMovements.deleted = 0 ".$stockMovementsFilter;		                
			
			$sqDeliveryNotes = "SELECT SUM(-1 * detailsByDeliveryNotes.quantity) AS auxQuantity
			                    FROM ((detailsByDeliveryNotes INNER JOIN deliveryNotes ON detailsByDeliveryNotes.deliveryNoteId = deliveryNotes.id)
			                    INNER JOIN orders ON deliveryNotes.orderId = orders.id)
			                    INNER JOIN branchOffices ON orders.branchOfficeId = branchOffices.id
			                    WHERE detailsByDeliveryNotes.deleted = 0 AND deliveryNotes.deleted = 0 AND orders.deleted = 0  ".$deliveryNotesFilter; 

			$sqlBills = "SELECT SUM(detailsByBill.quantity) AS auxQuantity 
	                    FROM detailsByBill INNER JOIN bills ON detailsByBill.billId = bills.id                    
	                    WHERE detailsByBill.deleted = 0 AND bills.deleted = 0 ".$billsFilter; 
			                              

			$sql = "SELECT SUM(summary.auxQuantity) AS previusStock
			        FROM (".
			        $sqlMovements.
			        " UNION ALL ".
					$sqDeliveryNotes.
			        " UNION ALL ".
			        $sqlBills.") AS summary";	

			$query = $this->company_db->query($sql);		
			if ($query->num_rows() == 1) {            				
				$row = $query->row_array();		

				$previusStock = $row['previusStock'];	
			} else {
				$previusStock = 0;
			}
		}

		$result = array('includePreviusStock'=>$includePreviusStock,
	                    'previusStock'=>$previusStock);
		
		return $result;
	}

	function getEmptyStockMovement() {
		$stockMovement = array('id'=>-1,							 						       				        
				        		'date'=>'',				        			          	 
				        		'userId'=>$this->session->userdata('userId'),
				        		'userDescription'=>$this->session->userdata('userName'),
				        		'typeId'=>0,	
				        		'input'=>0,	
				        		'companyId'=>0,	
				        		'warehouseId'=>0,	
				        		'articleId'=>0,	
				        		'quantity'=>0,
				        		'unitPrice'=>0,
				        		'observation'=>'',
				        		'details'=>null	
				        	    );
						
		return $stockMovement;
	}

	function existsValue($value='', $id=-1, $field='') {
		$exists = false;
		
		if ($value != '' && $field != '') {
			$sql = "SELECT * 
					FROM stockMovements 
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
	
	function setStockMovement($data){		
		if (!empty($data) && isset($data['details']) && count($data['details'])) {	
			$this->load->model('articles_model','articles');									

			$details = $data['details'];			
				
			$sqlTypes = "SELECT input 
		        		 FROM movementsTypes  
						 WHERE deleted = 0 AND id = ".$data['typeId'];																
			$queryTypes = $this->company_db->query($sqlTypes);			
			if ($queryTypes->num_rows() > 0){
				$row = $queryTypes->row_array();

				$input = $row['input'];					
			} else {
				return false;
			}

			$detailNewIds = array();
			$saveOK = true;		
			$date = getCurrentDate();
			for ($i=0; $i < count($details); $i++) {
				$this->company_db->set('date',$date);	        
				$this->company_db->set('userId',$this->session->userdata('userId'));	 													
				$this->company_db->set('typeId',$data['typeId']);			
				$this->company_db->set('input',$input);			
				$this->company_db->set('companyId',$data['companyId']);
				$this->company_db->set('warehouseId',$data['warehouseId']);
				$this->company_db->set('observation',$data['observation']);
				$this->company_db->set('articleId',$details[$i]['articleId']);													
				$this->company_db->set('quantity',$details[$i]['quantity']);	
				if ($input == 1) {					
					$this->company_db->set('unitPrice',$details[$i]['unitPrice']);	
					$this->company_db->set('freeQuantity',$details[$i]['quantity']);	
				} else {
					$this->company_db->set('unitPrice',0);
					$this->company_db->set('freeQuantity',0);
				}

				if ((float)$details[$i]['id'] > 0) {							
					//NOT EDITABLE
					//$this->company_db->where('id',$details[$i]['id']);				
					//$this->company_db->update('stockMovements');				
				} else {																
					$this->company_db->set('deleted',0);
					$this->company_db->set('deletedDate',NULL);
					$this->company_db->set('deletedUserId',NULL);				
					$this->company_db->insert('stockMovements');
					$details[$i]['id'] = $this->company_db->insert_id();

					if ((float)$details[$i]['id'] > 0) {
						$detailNewIds[count($detailNewIds)] = $details[$i]['id'];		

						if ($input == 1) {
							/*
							$originEntity['type'] = "sm";
							$originEntity['id'] = $details[$i]['id'];							
							$originEntity['unitPrice'] = $details[$i]['unitPrice'];

							$this->articles->updatePendingStock($details[$i]['articleId'],$data['warehouseId'],$originEntity);
							*/
							$this->articles->updatePendingStock($details[$i]['articleId'],$data['warehouseId']);

							$this->articles->updateLastUnitPrice($details[$i]['articleId']);
						} else {
							$this->articles->discountStockMovement($details[$i]['id']);
						}
					}
				}

				$error = $this->company_db->error();
				if ((int)$error['code'] != 0) {							
					$saveOK = false;													
				}									
			}

			if (!$saveOK && count($detailNewIds) > 0) {
				$this->company_db->set('deleted',1);	        
				$this->company_db->set('deletedDate',getCurrentDate());	        
				$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	 								
				$this->company_db->where('deleted',0);												
				$this->company_db->where_in('id', $detailNewIds);
				$this->company_db->update('stockMovements');	
			}

			return $saveOK;																		
		} else {
			return false;	
		}
	}	

	function deleteStockMovement($id=-1){
		if ($this->deleteStockMovementValid($id)){	
			$this->company_db->set('deleted',1);	
			$this->company_db->set('deletedDate',getCurrentDate());	        
			$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         
			$this->company_db->where('id',$id);				
			$this->company_db->update('stockMovements');		

			$error = $this->company_db->error();
			if ((int)$error['code'] != 0) {			
				return false;
			} else {				
				$this->load->model('orders_model','orders');

				$sql = "SELECT * 
	        			FROM stocksByOrder  
						WHERE stocksByOrder.deleted = 0 AND stocksByOrder.stockMovementId = ".$id;											

				$query = $this->company_db->query($sql);			
				if ($query->num_rows() > 0){
					$stocksByOrder = $query->result_array();

					for ($i=0; $i < count($stocksByOrder); $i++) {
						$sql = "UPDATE detailsByOrder  			                
				                SET readyQuantity = readyQuantity - ".$stocksByOrder[$i]['quantity']." 
				                WHERE id = ".$stocksByOrder[$i]['detailOrderId'];		
						$query = $this->company_db->query($sql);

						$this->company_db->set('deleted',1);	
						$this->company_db->set('deletedDate',getCurrentDate());	        
						$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         
						$this->company_db->where('id',$stocksByOrder[$i]['id']);				
						$this->company_db->update('stocksByOrder');

						$this->orders->updateTotalDetailByOrden($stocksByOrder[$i]['detailOrderId']);

						$this->orders->updateOrderTotal($stocksByOrder[$i]['orderId']);						
					}										
				}	

				$sql = "SELECT * 
	        			FROM stockMovements  
						WHERE id = ".$id;																
				$query = $this->company_db->query($sql);			
				if ($query->num_rows() > 0){
					$row = $query->row_array();

					$this->load->model('articles_model','articles');	
					$this->articles->updateLastUnitPrice($row['articleId']);					
				}			
				
				return true;
			}
		} else {
			return false;
		}		
	}

	function deleteStockMovementValid($id=-1){
		if ($id > 0){	
			$allowDelete = true;

			if ($allowDelete) {	
				$sql = "SELECT * 
	        			FROM stockMovements  
						WHERE deleted = 0 AND id = ".$id;																
				$query = $this->company_db->query($sql);			
				if ($query->num_rows() > 0){
					$row = $query->row_array();

					if ((int)$row['input'] != 1) $allowDelete = false;

					if ((int)$row['deliveredQuantity'] > 0) $allowDelete = false;					

					if ($allowDelete) {
						$sql = "SELECT *
			        			FROM (stocksByOrder INNER JOIN orders ON stocksByOrder.orderId = orders.id) 
			        			INNER JOIN detailsByOrder ON stocksByOrder.detailOrderId = detailsByOrder.id 
								WHERE stocksByOrder.deleted = 0 AND 									  
								      stocksByOrder.stockMovementId = ".$id." AND 
								      orders.deleted = 0 AND 
								      detailsByOrder.deleted = 0 AND
								      detailsByOrder.deliveredQuantity > 0";								      

						$query = $this->company_db->query($sql);			
						if ($query->num_rows() > 0){
							$allowDelete = false;
						}
					}
				}
			}
												
			return $allowDelete;
		} else {
			return false;
		}		
	}	

	function getDataByStockMovement($stockMovementId=-1){				
		$data = array('list'=>NULL, 
		              'totalRecords'=>0);
		
		if ($stockMovementId > 0) {

			$sql = "SELECT input 
        			FROM stockMovements  
					WHERE deleted = 0 AND id = ".$stockMovementId;																
			$query = $this->company_db->query($sql);			
			if ($query->num_rows() > 0){
				$row = $query->row_array();

				
				if ((int)$row['input'] == 1) {	
					$sql = "SELECT SQL_CALC_FOUND_ROWS 
								stocksByOrder.date,
								stocksByOrder.quantity,
								stocksByOrder.orderId 
					        FROM stocksByOrder INNER JOIN orders 
					        ON stocksByOrder.orderId = orders.id        
							WHERE stocksByOrder.deleted = 0 AND orders.deleted = 0 AND orders.stateId != 'CAN' AND 
							      stocksByOrder.stockMovementId = ".$stockMovementId." 
							ORDER BY stocksByOrder.id";	
						
					$query = $this->company_db->query($sql);
					$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
					if ($query->num_rows() > 0){	
						$auxData = $query->result_array();

						for ($i=0; $i < count($auxData); $i++) {
							$auxData[$i]['description'] = "Pedido Nro. ".$auxData[$i]['orderId'];
						}

						$data = array('list'=>$auxData, 
					                  'totalRecords'=>$queryTotal->row()->totalRecords);
					}				
				} else {
					$sql = "SELECT SQL_CALC_FOUND_ROWS
							       stocksByStockMovement.date,
							       stockMovements.date AS stockMovementDate,
							       stocksByStockMovement.quantity,
							       CONCAT('Factura Nro. ', bills.letter, ' ', bills.serie, ' ', bills.number) AS billNumeration,
							       IF(stocksByStockMovement.stockMovementId > 0,'SM','B') AS type
					        FROM (stocksByStockMovement LEFT JOIN stockMovements ON stocksByStockMovement.stockMovementId = stockMovements.id)    
					        LEFT JOIN bills ON stocksByStockMovement.billId = bills.id 
					        WHERE stocksByStockMovement.deleted = 0 AND 
							      stocksByStockMovement.originId = ".$stockMovementId." 
							ORDER BY stocksByStockMovement.date";								
						
					$query = $this->company_db->query($sql);
					$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
					if ($query->num_rows() > 0){	
						$auxData = $query->result_array();

						for ($i=0; $i < count($auxData); $i++) {
							switch ($auxData[$i]['type']) {
								case 'SM':									
									$auxData[$i]['description'] = "Mov. Stock ingresado el ".dateFormat($auxData[$i]['stockMovementDate'],false);
								break;

								case 'B':
									$auxData[$i]['description'] = $auxData[$i]['billNumeration'];
								break;
								
								default:
									$auxData[$i]['description'] = "";
								break;
							}
							
						}

						$data = array('list'=>$auxData, 
					                  'totalRecords'=>$queryTotal->row()->totalRecords);
					}				
				}
			}						
		}	

		return $data;
	}

	function assignFreeQuantityToStockMovements($articleId=-1,$warehouseId=-1,$freeQuantity=0) {
		if ($articleId > 0 && $warehouseId > 0 && $freeQuantity > 0) {
			
			$sql = "SELECT stockMovements.id, stockMovements.quantity
			        FROM stockMovements       
					WHERE stockMovements.deleted = 0 
					      AND stockMovements.articleId = ".$articleId." 
					      AND stockMovements.warehouseId = ".$warehouseId."  
						  AND stockMovements.input = 1  
					 	  AND stockMovements.freeQuantity = 0  
					 	  AND NOT EXISTS(SELECT stocksByOrder.id 
					 	                 FROM stocksByOrder
					 	                 WHERE stocksByOrder.deleted = 0 AND 
					                           stocksByOrder.stockMovementId = stockMovements.id)
					ORDER BY stockMovements.id DESC";	
				
			$query = $this->company_db->query($sql);
			$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
			if ($query->num_rows() > 0){	
				$stockMovements = $query->result_array();

				for ($i=0; $i < count($stockMovements) && $freeQuantity > 0; $i++) {					
					if ((int)$stockMovements[$i]['quantity'] >= $freeQuantity) {
						$auxQuantity = $freeQuantity;
					} else {
						$auxQuantity = (int)$stockMovements[$i]['quantity'];
					}					
					$freeQuantity -= $auxQuantity;


					$this->company_db->set('freeQuantity',$auxQuantity);	
					$this->company_db->set('unitPrice',-999999);	        					
					$this->company_db->where('id',$stockMovements[$i]['id']);				
					$this->company_db->update('stockMovements');
				}
			}
		}	
	}

	function setStockByStockMovement($data=null) {
		if (!empty($data)) {			

			switch ($data['entity']) {
				case 'sm':					
					$table = "stockMovements";
				break;

				case 'bill':
					$table = "detailsByBill";
				break;

				default:
					$table = "";
				break;
			}	

			if ($table != "") {
				$sql = "UPDATE ".$table." 			                
		                SET freeQuantity = freeQuantity - ".$data['quantity']." 
		                WHERE deleted = 0 AND id = ".$data['entityId'];		
				$query = $this->company_db->query($sql);

				$this->company_db->set('date',getCurrentDate());	        
				$this->company_db->set('originId',$data['originId']);					
				$this->company_db->set('quantity',$data['quantity']);														
				$this->company_db->set('deleted',0);
				$this->company_db->set('deletedDate',NULL);
				$this->company_db->set('deletedUserId',NULL);
				switch ($data['entity']) {
					case 'sm':
						$this->company_db->set('billId',0);  
						$this->company_db->set('detailBillId',0);       		
						$this->company_db->set('stockMovementId',$data['entityId']);									
					break;

					case 'bill':
						$this->company_db->set('billId',$data['entityMainId']);	        				
						$this->company_db->set('detailBillId',$data['entityId']);	        				
						$this->company_db->set('stockMovementId',0);	
					break;
				}					
				$this->company_db->insert('stocksByStockMovement');		
			}																	
		}
	}


	function getFreeQuantityErrorStock($articleId=-1,$warehouseId=-1) {		

		$sql = "SELECT articleId, warehouseId, available, freeQuantity, articles.code AS articleCode, articles.description AS articleDescription, warehouses.description AS warehouseDescription 
				FROM (

					SELECT SUM(auxQuantity) - SUM(auxPending) AS available, SUM(auxFreeQuantity) AS freeQuantity, articleId, warehouseId
					FROM
					(
						SELECT quantity * IF(input=1,1,-1) AS auxQuantity, 0 AS auxPending,   IF(input=1,stockMovements.freeQuantity,0) AS auxFreeQuantity,   articleId, warehouseId   
						FROM stockMovements 
						WHERE deleted = 0 
						
						UNION ALL 
						
						SELECT detailsByBill.quantity AS auxQuantity, 0 AS auxPending, detailsByBill.freeQuantity AS auxFreeQuantity, detailsByBill.articleId, bills.warehouseId     
						FROM detailsByBill INNER JOIN bills ON detailsByBill.billId = bills.id
						WHERE bills.deleted = 0 AND detailsByBill.deleted = 0  
						
						UNION ALL 
						
						SELECT detailsByDeliveryNotes.quantity * -1 AS auxQuantity, 0 AS auxPending, 0 AS auxFreeQuantity, detailsByDeliveryNotes.articleId, detailsByDeliveryNotes.warehouseId
						FROM (detailsByDeliveryNotes INNER JOIN detailsByOrder ON detailsByDeliveryNotes.detailOrderId = detailsByOrder.id)
						INNER JOIN orders ON detailsByOrder.orderId = orders.id
						WHERE detailsByDeliveryNotes.deleted = 0 AND orders.deleted = 0 AND detailsByOrder.deleted = 0 AND NOT orders.stateId IN ('CAN','NOTAUT','SUS')
						
						UNION ALL 
						
						SELECT 0 AS auxQuantity, (readyQuantity - deliveredQuantity) AS auxPending, 0 AS auxFreeQuantity, detailsByOrder.articleId, orders.warehouseId
						FROM detailsByOrder INNER JOIN orders ON detailsByOrder.orderId = orders.id
						WHERE orders.deleted = 0 AND detailsByOrder.deleted = 0 AND NOT orders.stateId IN ('ARM','CAN','FIN','NOTAUT','SUS','TOAUT')
					) AS summary
					GROUP BY articleId, warehouseId

				) AS stock INNER JOIN articles on stock.articleId = articles.id
				INNER JOIN warehouses ON warehouses.id = stock.warehouseId 
				WHERE articles.deleted = 0 AND  (available <> 0 or  freeQuantity  <> 0) and available <> freeQuantity ";
		
		if ($articleId > 0) $sql .= "AND articleId = ".$articleId." ";
		if ($warehouseId > 0) $sql .= "AND warehouseId = ".$warehouseId." ";
		$sql .= "ORDER BY warehouses.description, articles.description";

		$query = $this->company_db->query($sql);		
		if ($query->num_rows() > 0){	
			$data = $query->result_array();			
		} else {
			$data = NULL;
		}

		return $data;
	}

	function updateFreeQuantityStock($articleId=-1,$warehouseId=-1,$freeQuantity=0,$save=false) {
		
		if ($articleId > 0 && $warehouseId > 0 && $freeQuantity <> 0) {
			
			$sql = "SELECT *,
						IF (stock.type = 'sm',
						    (SELECT SUM(quantity) FROM stocksByOrder WHERE stockMovementId = stock.id AND stocksByOrder.deleted = 0)	    
						    ,
						    (SELECT SUM(quantity) FROM stocksByOrder WHERE detailBillId = stock.id AND stocksByOrder.deleted = 0) 	    
						    ) AS quantityAssignedSM,
						IF (stock.type = 'sm',	    
						    (SELECT SUM(quantity) FROM stocksByStockMovement WHERE stockMovementId = stock.id AND stocksByStockMovement.deleted = 0)	    
						    ,	    
						    (SELECT SUM(quantity) FROM stocksByStockMovement WHERE detailBillId = stock.id AND stocksByStockMovement.deleted = 0)	    
						    ) AS quantityAssignedBill 
					FROM (
						SELECT  stockMovements.id,
								0 as billId,
					            stockMovements.date,
								stockMovements.quantity,
					            stockMovements.freeQuantity,
								stockMovements.deliveredQuantity,
					            stockMovements.unitPrice,
					            'sm' AS type,
						 		articleId,
								warehouseId                  
					    FROM stockMovements
						WHERE stockMovements.deleted = 0 and input = 1
						UNION ALL
						SELECT  detailsByBill.id,
								detailsByBill.billId,
					            bills.date,
					            detailsByBill.quantity,
					            detailsByBill.freeQuantity,
					            detailsByBill.deliveredQuantity,
					            detailsByBill.unitPrice,
					            'bill' AS type,
								articleId,
								warehouseId
					    FROM detailsByBill INNER JOIN bills
					    ON detailsByBill.billId = bills.id
						WHERE detailsByBill.deleted = 0 AND bills.deleted = 0 
					) AS stock 
					WHERE warehouseId = ".$warehouseId." AND articleId = ".$articleId." ";
			if ($freeQuantity > 0) {
				//ADD FREE QUANTITY	
				$sql .= "ORDER BY date DESC, id DESC";	
			} else {
				//CANCEL FREE QUANTITY 
				$sql .= "AND freeQuantity > 0 ";
				$sql .= "ORDER BY date, id";	
			}	

			/*
			echo "<br><br>";
			echo $sql;
			echo "<br><br>";
			*/

			$query = $this->company_db->query($sql);			
			if ($query->num_rows() > 0){	
				$data = $query->result_array();

				if ($freeQuantity > 0) {
					//ADD FREE QUANTITY	
					$quantityToAdd = $freeQuantity;

					for ($i=0; $i < count($data) && $quantityToAdd > 0; $i++) {		

						$quantityAssigned = 0;
						if (isset($data[$i]['quantityAssignedSM']) && (int)$data[$i]['quantityAssignedSM'] > 0) $quantityAssigned += (int)$data[$i]['quantityAssignedSM'];
						if (isset($data[$i]['quantityAssignedBill']) && (int)$data[$i]['quantityAssignedBill'] > 0) $quantityAssigned += (int)$data[$i]['quantityAssignedBill'];

						if ((int)$data[$i]['deliveredQuantity'] > $quantityAssigned) {
							$maxQuantityFree = (int)$data[$i]['quantity'] - (int)$data[$i]['deliveredQuantity'] - (int)$data[$i]['freeQuantity'];
						} else {
							$maxQuantityFree = (int)$data[$i]['quantity'] -  $quantityAssigned - (int)$data[$i]['freeQuantity'];
						}								
						
						if ($maxQuantityFree > 0) {							
							if ($maxQuantityFree >= $quantityToAdd) {
								$auxQuantity = (int)$quantityToAdd;
							} else {
								$auxQuantity = $maxQuantityFree;
							}					
							$quantityToAdd -= $auxQuantity;

							switch ($data[$i]['type']) {
								case 'sm':
									$table = "stockMovements";
								break;
								case 'bill':
									$table = "detailsByBill";
								break;
								default:
									$table = "";
								break;
							}

							if ($table != "") {
								$newFreeQuantity = (int)$data[$i]['freeQuantity'] + $auxQuantity;

								echo "<br>".$table." - id: ".$data[$i]['id']." - Free: ".(int)$data[$i]['freeQuantity']." - New: ".$newFreeQuantity." - Dif: + ".$auxQuantity;
								
								if ($save) {
									$this->company_db->set('freeQuantity',$newFreeQuantity);										        				
									$this->company_db->where('id',$data[$i]['id']);				
									$this->company_db->update($table);								
								}
							}

						}
					} 
				} else {
					//$freeQuantity < 0 
					//CANCEL FREE QUANTITY 
					$quantityToDiscount = abs($freeQuantity);

					for ($i=0; $i < count($data) && $quantityToDiscount > 0; $i++) {					
						if ((int)$data[$i]['freeQuantity'] > 0) {
							if ((int)$data[$i]['freeQuantity'] >= $quantityToDiscount) {
								$auxQuantity = (int)$quantityToDiscount;
							} else {
								$auxQuantity = (int)$data[$i]['freeQuantity'];
							}					
							$quantityToDiscount -= $auxQuantity;

							switch ($data[$i]['type']) {
								case 'sm':
									$table = "stockMovements";
								break;
								case 'bill':
									$table = "detailsByBill";
								break;
								default:
									$table = "";
								break;
							}

							if ($table != "") {
								$newFreeQuantity = (int)$data[$i]['freeQuantity'] - $auxQuantity;

								echo "<br>".$table." - id: ".$data[$i]['id']." - Free: ".(int)$data[$i]['freeQuantity']." - New: ".$newFreeQuantity." - Dif: - ".$auxQuantity;
								
								if ($save) {
									$this->company_db->set('freeQuantity',$newFreeQuantity);										        				
									$this->company_db->where('id',$data[$i]['id']);				
									$this->company_db->update($table);								
								}
							}

						}
					} 

				}
			}
		}	
	}
		
}

/* End of file StockMovements_model.php */
/* Location: ./application/models/StockMovements_model.php */