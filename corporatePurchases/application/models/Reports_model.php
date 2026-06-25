<?php
class Reports_model extends CI_Model{           

    private $company_db;

    function __construct() {        
        parent::__construct();        
        
        $this->company_db = $this->load->database('default', TRUE); 
    }  

    /*
    function _getFilter($parameters=NULL) {
		$filter = "";

		if (isset($parameters)) {						
			if (isset($parameters['dateFromFilter']) && $parameters['dateFromFilter'] != "") {
				//$filter .= " AND budgets.date >= '".$parameters['dateFromFilter']." 00:00:00' ";
			}
			if (isset($parameters['dateToFilter']) && $parameters['dateToFilter'] != "") {
				//$filter .= " AND budgets.date <= '".$parameters['dateToFilter']." 23:59:59' ";
			}	
			if (isset($parameters['warehouseIdFilter']) && $parameters['warehouseIdFilter'] > 0) {
				//$filter .= " AND budgets.branchOfficeId = ".$parameters['branchOfficeIdFilter'];				
			}				
			if (isset($parameters['articleFilter']) && $parameters['articleFilter'] != "") {
				$sqlArticles = "SELECT id 
			        			FROM articles  
								WHERE deleted = 0 AND code = '".$this->company_db->escape($parameters['articleFilter'])."'";																
				$queryArticles = $this->company_db->query($sqlArticles);			
				if ($queryArticles->num_rows() > 0){
					$row = $queryArticles->row_array();

					$articleId = $row['id'];					
				} else {
					$articleId = -1;
				}
				//$filter .= " AND stockMovements.articleId = ".$articleId;				
			}													
		}
		
		return $filter;
	}
	*/
    
	function getSummaryStock($parameters=NULL){				
		$data = array('list'=>NULL, 
		              'totalRecords'=>0);
		
		$filterArticles = "";
		$filterMovements = "";		
		//HABILITAR USA STOCK
		$filterStock = " WHERE families.affectsStock = 1";			
		$limit = "";	 
		$generalLimit = "";
		
		if (isset($parameters['warehouseIdFilter']) && $parameters['warehouseIdFilter'] > 0) {
			$filterMovements .= " AND warehouseId = ".$parameters['warehouseIdFilter'];						
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
			$filterArticles .= " AND articles.id = ".$articleId;		
			$filterMovements .= " AND articleId = ".$articleId;	
		}		
		if (isset($parameters['familyIdFilter']) && $parameters['familyIdFilter'] > 0) {
			$filterArticles .= " AND articles.familyId = ".$parameters['familyIdFilter'];				
		}
		if (isset($parameters['articleIdFilter']) && $parameters['articleIdFilter'] > 0) {
			$filterArticles .= " AND articles.id = ".$parameters['articleIdFilter'];		
			$filterMovements .= " AND articleId = ".$parameters['articleIdFilter'];				
		}
		if (isset($parameters['articleActiveFilter']) && $parameters['articleActiveFilter'] != '') {
			$filterArticles .= " AND articles.active = ".$parameters['articleActiveFilter'];				
		}
		if (isset($parameters['stockNotZeroFilter']) && $parameters['stockNotZeroFilter'] == true) {
			$filterStock .= " AND summaryStock.stock <> 0 ";						
		}		
		if (isset($parameters['dateFromFilter']) && $parameters['dateFromFilter'] != "") {
			$filterMovements .= " AND [DATE] >= '".$parameters['dateFromFilter']." 00:00:00' ";
		}
		if (isset($parameters['dateToFilter']) && $parameters['dateToFilter'] != "") {
			$filterMovements .= " AND [DATE] <= '".$parameters['dateToFilter']." 23:59:59' ";
		}		

		if (isset($parameters['generalLimit']) && $parameters['generalLimit'] > 0) {			
			$generalLimit = "LIMIT ".$parameters['generalLimit'];
		}

		if (isset($parameters['typeStockFilter']) && $parameters['typeStockFilter'] != "") {			
			$typeStock = $parameters['typeStockFilter'];
		} else {
			$typeStock = "REAL";
		}		


		switch ($typeStock) {
			case 'AVAILABLE':
				$sqlStock = "SELECT articleId, warehouseId, quantity * IF(input=1,1,-1) AS auxQuantity, 0 AS auxUnitPrice, 0 AS realQuantity    
							 FROM stockMovements 
							 WHERE deleted = 0 ".$filterMovements." 
							 UNION ALL
							 SELECT articleId, warehouseId, quantity * -1 AS auxQuantity, 0 AS auxUnitPrice, 0 AS realQuantity   
							 FROM detailsByDeliveryNotes 
							 WHERE deleted = 0 ".$filterMovements."
							 UNION ALL 
							 SELECT detailsByBill.articleId, bills.warehouseId, quantity AS auxQuantity, 0 AS auxUnitPrice, 0 AS realQuantity   
							 FROM detailsByBill INNER JOIN bills 
							 ON detailsByBill.billId = bills.id
							 WHERE bills.deleted = 0 AND detailsByBill.deleted = 0 ".$filterMovements."
							 UNION ALL 
							 SELECT detailsByOrder.articleId, orders.warehouseId, -1 * (readyQuantity - deliveredQuantity) AS auxQuantity, 0 AS auxUnitPrice, 0 AS realQuantity   
							 FROM detailsByOrder INNER JOIN orders 
							 ON detailsByOrder.orderId = orders.id
							 WHERE orders.deleted = 0 AND detailsByOrder.deleted = 0 AND 
							       NOT orders.stateId IN ('ARM','CAN','FIN','NOTAUT','SUS','TOAUT') ".$filterMovements;
			break;

			case 'OUTOFSTOCK':
				$sqlStock = "SELECT detailsByOrder.articleId, orders.warehouseId, (quantity - readyQuantity) AS auxQuantity, 0 AS auxUnitPrice, 0 AS realQuantity   
				             FROM detailsByOrder INNER JOIN orders 
				             ON detailsByOrder.orderId = orders.id
				  	         WHERE orders.deleted = 0 AND detailsByOrder.deleted = 0 AND 
				  	               NOT orders.stateId IN ('ARM','CAN','FIN','NOTAUT','SUS','TOAUT') ".$filterMovements;								  	               
			break;

			default:
				$sqlStock = "SELECT stockMovements.articleId, stockMovements.warehouseId, stockMovements.quantity * IF(stockMovements.input=1,1,-1) AS auxQuantity, 
				                    stockMovements.unitPrice AS auxUnitPrice, 
				                    IF(stockMovements.input = 1 AND
				                       stockMovements.unitPrice > 0 AND
				                       (stockMovements.incompleteData IS NULL OR stockMovements.incompleteData = 0)
				                       ,
				                       (quantity - deliveredQuantity),0) AS realQuantity   
							 FROM stockMovements 							 
							 WHERE deleted = 0 ".str_replace("[DATE]","stockMovements.date",$filterMovements)." 
							 UNION ALL 
							 SELECT detailsByBill.articleId, bills.warehouseId, detailsByBill.quantity AS auxQuantity, 
							        detailsByBill.unitPrice AS auxUnitPrice, 
							        IF(detailsByBill.unitPrice > 0,detailsByBill.quantity - detailsByBill.deliveredQuantity,0) AS realQuantity   
							 FROM detailsByBill INNER JOIN bills ON detailsByBill.billId = bills.id
							 WHERE bills.deleted = 0 AND detailsByBill.deleted = 0 ".str_replace("[DATE]","bills.billDate",$filterMovements)."
							 UNION ALL
							 SELECT detailsByDeliveryNotes.articleId, detailsByDeliveryNotes.warehouseId, detailsByDeliveryNotes.quantity * -1 AS auxQuantity, 
							 		0 AS auxUnitPrice, 
							 		0 AS realQuantity   
							 FROM detailsByDeliveryNotes INNER JOIN deliveryNotes ON detailsByDeliveryNotes.deliveryNoteId = deliveryNotes.id
							 WHERE detailsByDeliveryNotes.deleted = 0 AND deliveryNotes.deleted = 0 ".str_replace("[DATE]","deliveryNotes.date",$filterMovements);		
							 					
			break;
		}					
		
		/*
		
		$sqlStock = "SELECT stockMovements.articleId, stockMovements.warehouseId, stockMovements.quantity * IF(stockMovements.input=1,1,-1) AS auxQuantity, 
				                    stockMovements.unitPrice AS auxUnitPrice, 
				                    IF(stockMovements.input = 1 AND
				                       stockMovements.unitPrice > 0 AND
				                       (stockMovements.quantity - stockMovements.freeQuantity) = IF(summarySM.totalQuantity IS NULL,0,summarySM.totalQuantity)
				                       ,
				                       (quantity - deliveredQuantity),0) AS realQuantity   
							 FROM stockMovements 
							 LEFT JOIN  (
		                             SELECT stocksByOrder.stockMovementId, SUM(quantity) AS totalQuantity
									 FROM stocksByOrder 
									 WHERE stocksByOrder.deleted = 0
	                                 GROUP BY stocksByOrder.stockMovementId
                                     ) AS summarySM
                         	 ON stockMovements.id = summarySM.stockMovementId 
							 WHERE deleted = 0 ".$filterMovements." 
							 UNION ALL 
							 SELECT detailsByBill.articleId, bills.warehouseId, detailsByBill.quantity AS auxQuantity, 
							        detailsByBill.unitPrice AS auxUnitPrice, 
							        IF(detailsByBill.unitPrice > 0 AND 
							           (detailsByBill.quantity - detailsByBill.freeQuantity) = IF(summaryB.totalQuantity IS NULL,0,summaryB.totalQuantity),
							           (detailsByBill.quantity - detailsByBill.deliveredQuantity),0) AS realQuantity   
							 FROM (detailsByBill INNER JOIN bills ON detailsByBill.billId = bills.id)
							 LEFT JOIN  (
			                             SELECT stocksByOrder.detailBillId, SUM(quantity) AS totalQuantity
										 FROM stocksByOrder 
										 WHERE stocksByOrder.deleted = 0
		                                 GROUP BY stocksByOrder.detailBillId
	                                     ) AS summaryB
	                         ON detailsByBill.id = summaryB.detailBillId  
							 WHERE bills.deleted = 0 AND detailsByBill.deleted = 0 ".$filterMovements."
							 UNION ALL
							 SELECT detailsByDeliveryNotes.articleId, detailsByDeliveryNotes.warehouseId, detailsByDeliveryNotes.quantity * -1 AS auxQuantity, 
							 		0 AS auxUnitPrice, 
							 		0 AS realQuantity   
							 FROM detailsByDeliveryNotes 
							 WHERE deleted = 0 ".$filterMovements

		$sql = "SELECT 
		               summaryArticles.id,
		               summaryArticles.code,
		               summaryArticles.description,
		               families.description	AS familyDescription,
		               summaryStock.warehouseId, 
		               summaryStock.stock,
		               summaryStock.unitPrice,    
		               IF(NOT summaryLastPrice.billDetailId IS NULL,summaryLastPrice.unitPrice,0) AS lastUnitPrice,
		               IF(NOT summaryLastPrice.billDetailId IS NULL,summaryLastPrice.billDetailId,0) AS lastUnitPriceId,		               
		               articlesLocation.corridor,
		               articlesLocation.shelf
		        FROM ((((
		              (SELECT id, code, description, familyId
		               FROM articles 
		               WHERE articles.deleted = 0 ".$filterArticles." 
		               ORDER by description ".
		               $limit.") AS summaryArticles		
		              LEFT JOIN families ON summaryArticles.familyId = families.id) 
		        LEFT JOIN (
		        			SELECT articleId, warehouseId, SUM(auxQuantity) AS stock, SUM(auxUnitPrice * realQuantity) / SUM(realQuantity) AS unitPrice		        			       
							FROM (
								".$sqlStock." 
							) AS summary 				
							GROUP BY articleId, warehouseId 
		                  ) AS summaryStock ON summaryStock.articleId = summaryArticles.id)		           
		        LEFT JOIN warehouses ON summaryStock.warehouseId = warehouses.id)
		        LEFT JOIN articlesLocation ON articlesLocation.articleId = summaryStock.articleId AND articlesLocation.warehouseId = summaryStock.warehouseId AND articlesLocation.deleted = 0) 
		        LEFT JOIN (
		        	SELECT *
					FROM (
					SELECT 
						bills.warehouseId, 
					    detailsByBill.articleId, 						
						detailsByBill.unitPrice,
						detailsByBill.id AS billDetailId,
						(ROW_NUMBER() OVER (PARTITION BY bills.warehouseId, detailsByBill.articleId ORDER BY bills.billDate DESC)) AS rowNumber  
					FROM detailsByBill INNER JOIN bills ON detailsByBill.billId = bills.id                    
					WHERE detailsByBill.deleted = 0 AND bills.deleted = 0 AND detailsByBill.unitPrice > 0
					) AS preSummaryLastPrice					
		        ) AS summaryLastPrice
		        ON summaryStock.articleId = summaryLastPrice.articleId AND  
		           summaryStock.warehouseId = summaryLastPrice.warehouseId AND  
		           summaryLastPrice.rowNumber = 1
		        "
		        .$filterStock." 		        		
				ORDER BY TRIM(summaryArticles.description), summaryArticles.id  "
		        .$generalLimit;		
		*/
		/*
		$sqlLastUnitPrice = "SELECT unitPrice 
							 FROM (
			                     SELECT detailsByBill.unitPrice, bills.billDate AS date 
								 FROM detailsByBill INNER JOIN bills ON detailsByBill.billId = bills.id                    
								 WHERE detailsByBill.deleted = 0 AND bills.deleted = 0 AND detailsByBill.unitPrice > 0 AND 
								       detailsByBill.articleId = summaryArticles.id 					
							 	 UNION ALL 
							 	 SELECT stockMovements.unitPrice, stockMovements.date 
								 FROM stockMovements 
								 WHERE stockMovements.deleted = 0 AND stockMovements.input = 1 AND stockMovements.unitPrice > 0 AND 
								       stockMovements.articleId = summaryArticles.id 						
						     ) AS summaryLastUnitPrice 
							 ORDER BY date DESC
							 LIMIT 1 ";
		*/
		$sql = "SELECT 
		               summaryArticles.id,
		               summaryArticles.code,
		               summaryArticles.description,
		               families.description	AS familyDescription,
		               summaryStock.warehouseId, 
		               summaryStock.stock,
		               summaryStock.unitPrice,    
		               summaryArticles.lastUnitPrice,		               
		               articlesLocation.corridor,
		               articlesLocation.shelf
		        FROM (((
		              (SELECT id, code, description, familyId, lastUnitPrice 
		               FROM articles 
		               WHERE articles.deleted = 0 ".$filterArticles." 
		               ORDER by description ".
		               $limit.") AS summaryArticles		
		              LEFT JOIN families ON summaryArticles.familyId = families.id) 
		        LEFT JOIN (
		        			SELECT articleId, warehouseId, SUM(auxQuantity) AS stock, SUM(auxUnitPrice * realQuantity) / SUM(realQuantity) AS unitPrice		        			       
							FROM (
								".$sqlStock." 
							) AS summary 				
							GROUP BY articleId, warehouseId 
		                  ) AS summaryStock ON summaryStock.articleId = summaryArticles.id)		           
		        LEFT JOIN warehouses ON summaryStock.warehouseId = warehouses.id)
		        LEFT JOIN articlesLocation ON articlesLocation.articleId = summaryStock.articleId AND articlesLocation.warehouseId = summaryStock.warehouseId AND articlesLocation.deleted = 0 		  
		        "
		        .$filterStock." 		        		
				ORDER BY TRIM(summaryArticles.description), summaryArticles.id  "
		        .$generalLimit;				        
				       		
		$query = $this->company_db->query($sql);				
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');			
		if ($query->num_rows() > 0) {	
			$arrAux = $query->result_array();
			
			if (isset($parameters['articlesLimit']) && $parameters['articlesLimit'] > 0) {			
				$arrAux = $query->result_array();
				$arrAux2 = NULL;

				$articlesCount = 0;
				$articleId = -1;
				for ($i=0; $i < count($arrAux); $i++) {
					if ($articleId != $arrAux[$i]['id']) {
						$articlesCount++;
						$articleId = $arrAux[$i]['id'];
						if ($articlesCount > $parameters['articlesLimit']) {
							break;
						}
					} 
					$arrAux2[$i] = $arrAux[$i];
				}

				$data = array('list'=>$arrAux2, 
		                      'totalRecords'=>$queryTotal->row()->totalRecords);	
			} else {
				$data = array('list'=>$query->result_array(), 
		                      'totalRecords'=>$queryTotal->row()->totalRecords);	
			}							
		}				

		return $data;		
	}  			

	function getPartialDeliveries($parameters=NULL){

		$data = array('list'=>NULL,
		              'totalRecords'=>0);

		$filter = "";
		$limit = "";

		if (isset($parameters['dateFromFilter']) && $parameters['dateFromFilter'] != "") {
			$filter .= " AND DATE(orders.maximumDate) >= ".$this->company_db->escape($parameters['dateFromFilter']);
		}
		if (isset($parameters['dateToFilter']) && $parameters['dateToFilter'] != "") {
			$filter .= " AND DATE(orders.maximumDate) <= ".$this->company_db->escape($parameters['dateToFilter']);
		}
		if (isset($parameters['stateIdFilter']) && $parameters['stateIdFilter'] != "") {
			$filter .= " AND orders.stateId = ".$this->company_db->escape($parameters['stateIdFilter']);
		}
		if (isset($parameters['companyIdFilter']) && $parameters['companyIdFilter'] > 0) {
			$filter .= " AND branchOffices.companyId = ".$parameters['companyIdFilter'];
		}
		if (isset($parameters['branchOfficeIdFilter']) && $parameters['branchOfficeIdFilter'] > 0) {
			$filter .= " AND orders.branchOfficeId = ".$parameters['branchOfficeIdFilter'];
		}
		if (isset($parameters['familyIdFilter']) && $parameters['familyIdFilter'] > 0) {
			$filter .= " AND articles.familyId = ".$parameters['familyIdFilter'];
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
			$filter .= " AND detailsByOrder.articleId = ".$articleId;
		}
		if (isset($parameters['limit']) && $parameters['limit'] > 0) {
			$limit = " LIMIT ".$parameters['limit'];
		}

		// Las cantidades se calculan desde remitos vigentes para evitar depender del cache del detalle.
		// El plazo se lee de orders.maximumDate porque es la fecha real de cumplimiento.
		$sql = "SELECT SQL_CALC_FOUND_ROWS ";
		$sql .= "orders.id AS orderId, ";
		$sql .= "orders.date AS orderDate, ";
		$sql .= "orders.maximumDays, ";
		$sql .= "IF(orders.maximumDate IS NULL OR DATE(orders.maximumDate) = '0000-00-00','',orders.maximumDate) AS maximumDate, ";
		$sql .= "orders.stateId, ";
		$sql .= "ordersStates.description AS stateDescription, ";
		$sql .= "companies.id AS companyId, ";
		$sql .= "companies.description AS companyDescription, ";
		$sql .= "branchOffices.description AS branchOfficeDescription, ";
		$sql .= "detailsByOrder.id AS detailOrderId, ";
		$sql .= "detailsByOrder.code AS articleCode, ";
		$sql .= "CONCAT(detailsByOrder.code,' - ',detailsByOrder.description) AS articleDescription, ";
		$sql .= "IF(ISNULL(families.id),'',families.description) AS familyDescription, ";
		$sql .= "(detailsByOrder.quantity - IFNULL(detailsByOrder.canceledQuantity,0)) AS requestedQuantity, ";
		$sql .= "IFNULL(deliveryNotesSummary.deliveredQuantity,0) AS deliveredQuantity, ";
		$sql .= "IF(IFNULL(deliveryNotesSummary.deliveredQuantity,0) >= (detailsByOrder.quantity - IFNULL(detailsByOrder.canceledQuantity,0)),'Entrega Completa',IF(IFNULL(deliveryNotesSummary.deliveredQuantity,0) > 0,'Entrega Parcial','Sin Entregar Aun')) AS deliveryStateDescription, ";
		$sql .= "IF(orders.maximumDate IS NULL OR DATE(orders.maximumDate) = '0000-00-00','Sin plazo',IF(DATE(orders.maximumDate) < CURDATE(),'Vencido',IF(DATE(orders.maximumDate) = CURDATE(),'Vence hoy','No vencido'))) AS dueSituationDescription, ";
		$sql .= "IF(orders.maximumDate IS NULL OR DATE(orders.maximumDate) = '0000-00-00','Sin plazo',IF(DATE(orders.maximumDate) < CURDATE(),CONCAT('Vencido hace ',DATEDIFF(CURDATE(),DATE(orders.maximumDate)),' dias'),IF(DATE(orders.maximumDate) = CURDATE(),'Vence hoy',CONCAT('Faltan ',DATEDIFF(DATE(orders.maximumDate),CURDATE()),' dias')))) AS delayDescription, ";
		$sql .= "IF(ISNULL(purchasesOrders.id) OR purchasesOrders.id <= 0,'',CONCAT(purchasesOrders.id,'-',companies.id,'-',DATE_FORMAT(purchasesOrders.date,'%m%y'))) AS purchaseOrderNumber, ";
		$sql .= "IFNULL(deliveryNotesSummary.deliveryNotes,'') AS deliveryNotes ";
		$sql .= "FROM ((((((orders INNER JOIN detailsByOrder ON orders.id = detailsByOrder.orderId) ";
		$sql .= "INNER JOIN branchOffices ON orders.branchOfficeId = branchOffices.id) ";
		$sql .= "INNER JOIN companies ON branchOffices.companyId = companies.id) ";
		$sql .= "LEFT JOIN ordersStates ON orders.stateId = ordersStates.id) ";
		$sql .= "LEFT JOIN articles ON detailsByOrder.articleId = articles.id) ";
		$sql .= "LEFT JOIN families ON articles.familyId = families.id) ";
		$sql .= "LEFT JOIN purchasesOrders ON purchasesOrders.orderId = orders.id ";
		$sql .= "LEFT JOIN ( ";
		$sql .= "	SELECT detailsByDeliveryNotes.detailOrderId, ";
		$sql .= "		   SUM(detailsByDeliveryNotes.quantity) AS deliveredQuantity, ";
		$sql .= "		   GROUP_CONCAT(DISTINCT CONCAT(deliveryNotes.id,'-',branchOffices.companyId,'-',DATE_FORMAT(deliveryNotes.date,'%m%y')) ORDER BY deliveryNotes.id SEPARATOR ', ') AS deliveryNotes ";
		$sql .= "	FROM (detailsByDeliveryNotes INNER JOIN deliveryNotes ON detailsByDeliveryNotes.deliveryNoteId = deliveryNotes.id) ";
		$sql .= "	INNER JOIN orders ON deliveryNotes.orderId = orders.id ";
		$sql .= "	INNER JOIN branchOffices ON orders.branchOfficeId = branchOffices.id ";
		$sql .= "	WHERE detailsByDeliveryNotes.deleted = 0 AND deliveryNotes.deleted = 0 AND orders.deleted = 0 ";
		$sql .= "	GROUP BY detailsByDeliveryNotes.detailOrderId ";
		$sql .= ") AS deliveryNotesSummary ON deliveryNotesSummary.detailOrderId = detailsByOrder.id ";
		$sql .= "WHERE orders.deleted = 0 AND detailsByOrder.deleted = 0 ";
		$sql .= "AND (detailsByOrder.quantity - IFNULL(detailsByOrder.canceledQuantity,0)) > 0 ";
		$sql .= $filter." ";
		$sql .= "ORDER BY TRIM(families.description), TRIM(detailsByOrder.description), orders.maximumDate, orders.id ";
		$sql .= $limit;

		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query("SELECT FOUND_ROWS() AS totalRecords");
		if ($query->num_rows() > 0) {
			$data = array('list'=>$query->result_array(),
		                  'totalRecords'=>$queryTotal->row()->totalRecords);
		}

		return $data;
	}

	function getGeneralReport($parameters=NULL){				
		
		$data = NULL;
		
		$filter = "";		

		if (isset($parameters['dateFromFilter']) && $parameters['dateFromFilter'] != "") {
			$filter .= " AND orders.date >= '".$parameters['dateFromFilter']."' ";
		}
		if (isset($parameters['dateToFilter']) && $parameters['dateToFilter'] != "") {
			$filter .= " AND orders.date <= '".$parameters['dateToFilter']."' ";
		}		
		if (isset($parameters['companyIdFilter']) && $parameters['companyIdFilter'] > 0) {
			$filter .= " AND branchOffices.companyId = ".$parameters['companyIdFilter'];				
		}
		if (isset($parameters['branchOfficeIdFilter']) && $parameters['branchOfficeIdFilter'] > 0) {
			$filter .= " AND orders.branchOfficeId = ".$parameters['branchOfficeIdFilter'];				
		}
		if (isset($parameters['sectorIdFilter']) && $parameters['sectorIdFilter'] > 0) {
			$filter .= " AND orders.sectorId = ".$parameters['sectorIdFilter'];				
		}

		switch ($parameters['groupedByFilter']) {
			case 'COM': //company
				$includeItems = false;
				$groupField = "branchOffices.companyId";
				$valueField = ($parameters['valueTypeIdFilter'] == "QUA"?"COUNT(orders.id)":"SUM(orders.total)");
				$referenceTable = "companies";
				$title = "Empresa";
			break;

			case 'BO': //branch office
				$includeItems = false;
				$groupField = "orders.branchOfficeId";
				$valueField = ($parameters['valueTypeIdFilter'] == "QUA"?"COUNT(orders.id)":"SUM(orders.total)");
				$referenceTable = "(SELECT branchOffices.id, CONCAT(companies.description,' - ',branchOffices.description) AS description, branchOffices.deleted FROM branchOffices INNER JOIN companies ON branchOffices.companyId = companies.id)";
				$title = "Sucursal";
			break;

			case 'SEC': //sector
				$includeItems = false;
				$groupField = "orders.sectorId";
				$valueField = ($parameters['valueTypeIdFilter'] == "QUA"?"COUNT(orders.id)":"SUM(orders.total)");								
				$referenceTable = "sectors";
				$title = "Sector";
			break;

			case 'FAM': //family
				$includeItems = true;
				$groupField = "details.familyId";
				$valueField = ($parameters['valueTypeIdFilter'] == "QUA"?"COUNT(orders.id)":"SUM(details.total)");								
				$referenceTable = "families";
				$title = "Rubro";
			break;
		}

		if ($includeItems) {			
			$sql = "SELECT ";
			$sql .= $groupField." AS referenceId, ";
			if ($parameters['subtypeFilter'] == "PRO") $sql .= "MONTH(orders.date) AS month, YEAR(orders.date) AS year, ";
			$sql .= $valueField." AS value ";
			$sql .= "FROM (orders INNER JOIN branchOffices ON orders.branchOfficeId = branchOffices.id)
					 INNER JOIN (SELECT detailsByOrder.orderId, articles.familyId, SUM(detailsByOrder.total) AS total 
					             FROM detailsByOrder INNER JOIN articles
					             ON detailsByOrder.articleId = articles.id 
					             WHERE detailsByOrder.deleted = 0
					             GROUP BY detailsByOrder.orderId, articles.familyId ) AS details 
					 ON orders.id = details.orderId
					 WHERE orders.deleted = 0 AND orders.stateId = 'FIN'".$filter." 
					 GROUP BY ".$groupField;
			if ($parameters['subtypeFilter'] == "PRO") $sql .= ", MONTH(orders.date), YEAR(orders.date)";			
		} else {
			$sql = "SELECT ";
			$sql .= $groupField." AS referenceId, ";
			if ($parameters['subtypeFilter'] == "PRO") $sql .= "MONTH(orders.date) AS month, YEAR(orders.date) AS year, ";
			$sql .= $valueField." AS value ";
			$sql .= "FROM orders INNER JOIN branchOffices ON orders.branchOfficeId = branchOffices.id
					 WHERE orders.deleted = 0 AND orders.stateId = 'FIN'".$filter." 
					 GROUP BY ".$groupField;
			if ($parameters['subtypeFilter'] == "PRO") $sql .= ", MONTH(orders.date), YEAR(orders.date)";				
		}
		
		if ($sql != "") {
			$query = $this->company_db->query($sql);						
			if ($query->num_rows() > 0) {	
				$auxValues = $query->result_array();			

				for ($i=0; $i < count($auxValues); $i++) {
					if ($parameters['subtypeFilter'] == "PRO") {
						$data['values'][$auxValues[$i]['referenceId']][(int)$auxValues[$i]['month']][(int)$auxValues[$i]['year']] = $auxValues[$i]['value'];
					} else {
						$data['values'][$auxValues[$i]['referenceId']] = $auxValues[$i]['value'];
					}
				}				
			}				
		}

		//Y axis
		$sql = "SELECT t.id, t.description 
				FROM ".$referenceTable." AS t WHERE t.deleted = 0 ORDER BY t.description";
		$query = $this->company_db->query($sql);						
		if ($query->num_rows() > 0) {	
			$data['yAxis'] = $query->result_array();				
		}						
		$data['yAxisTitle'] = $title;		

		//X axis	
		if ($parameters['subtypeFilter'] == "PRO") { //progression report
			$data['xAxis'] = NULL;			
			$i = -1;			

			$fromArr = explode("-",$parameters['dateFromFilter']);
			$fromMonth = (int)$fromArr[1];
			$fromYear = (int)$fromArr[0];
			$toArr = explode("-",$parameters['dateToFilter']);
			$toMonth = (int)$toArr[1];
			$toYear = (int)$toArr[0];

			if ($fromYear == $toYear) {
				for ($month = $fromMonth; $month <= $toMonth; $month++) {
					$i++;
				    $data['xAxis'][$i] = array('month'=>$month,
				                               'year'=>$toYear,
				                               'description'=>getNameMonth($month,true)." '".($toYear % 100));			    
				}				
			} else {
				if ($fromYear < $toYear) {
					for ($month = $fromMonth; $month <= 12; $month++) {
						$i++;
					    $data['xAxis'][$i] = array('month'=>$month,
					                               'year'=>$fromYear,
					                               'description'=>getNameMonth($month,true)." '".($fromYear % 100));			    
					}	
					$fromYear++;
				}
				for ($year = $fromYear; $year <= $toYear - 1; $year++) {
					for ($month = 1; $month <= 12; $month++) {
						$i++;
					    $data['xAxis'][$i] = array('month'=>$month,
					                               'year'=>$fromYear,
					                               'description'=>getNameMonth($month,true)." '".($year % 100));			    
					}
				}
				for ($month = 1; $month <= $toMonth; $month++) {
					$i++;
				    $data['xAxis'][$i] = array('month'=>$month,
				                               'year'=>$toYear,
				                               'description'=>getNameMonth($month,true)." '".($toYear % 100));			    
				}	
			}
		}     

		

		return $data;		
	} 

}


/* End of file Reports_model.php */
/* Location: ./application/models/Reports_model.php */
