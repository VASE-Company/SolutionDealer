<?php
class Reports_model extends CI_Model{           

    private $company_db;

    function __construct() {        
        parent::__construct();        
        
        $this->company_db = $this->load->database('default', TRUE); 
    }  
   
	function getSummaryStock($parameters=NULL){				
		$data = array('list'=>NULL, 
		              'totalRecords'=>0);
		
		$parameters = sanitizeQueryParameters($parameters);
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
				
		$sql = "SELECT SQL_CALC_FOUND_ROWS 
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
		$data = array('list'=>array(),
		              'totalRecords'=>0);

		$filter = "";
		$limit = "";
		$parameters = sanitizeQueryParameters($parameters);

		// Control de Entregas: estos filtros se aplican a nivel item de pedido.
		if (isset($parameters['dateFromFilter']) && $parameters['dateFromFilter'] != "") {
			$filter .= " AND DATE(orders.maximumDate) >= ".$this->company_db->escape($parameters['dateFromFilter']);
		}
		if (isset($parameters['dateToFilter']) && $parameters['dateToFilter'] != "") {
			$filter .= " AND DATE(orders.maximumDate) <= ".$this->company_db->escape($parameters['dateToFilter']);
		}
		if (isset($parameters['stateIdFilter']) && $parameters['stateIdFilter'] != "") {
			$filter .= " AND orders.stateId = ".$this->company_db->escape($parameters['stateIdFilter']);
		}
		// Estado Entrega se calcula igual que la grilla: entregado vs cantidad solicitada vigente.
		if (isset($parameters['deliveryStateIdFilter']) && $parameters['deliveryStateIdFilter'] != "") {
			$requestedQuantity = "detailsByOrder.quantity";
			$deliveredQuantity = "IFNULL(deliveryNotesSummary.deliveredQuantity,0)";
			switch ($parameters['deliveryStateIdFilter']) {
				case "COMPLETE":
					$filter .= " AND ".$deliveredQuantity." >= ".$requestedQuantity;
				break;

				case "PARTIAL":
					$filter .= " AND ".$deliveredQuantity." > 0 AND ".$deliveredQuantity." < ".$requestedQuantity;
				break;

				case "PENDING":
					$filter .= " AND ".$deliveredQuantity." = 0";
				break;
			}
		}
		if (isset($parameters['companyIdFilter']) && $parameters['companyIdFilter'] > 0) {
			$filter .= " AND branchOffices.companyId = ".(int)$parameters['companyIdFilter'];
		}
		if (isset($parameters['branchOfficeIdFilter']) && $parameters['branchOfficeIdFilter'] > 0) {
			$filter .= " AND orders.branchOfficeId = ".(int)$parameters['branchOfficeIdFilter'];
		}
		if (isset($parameters['familyIdFilter']) && $parameters['familyIdFilter'] > 0) {
			$filter .= " AND articles.familyId = ".(int)$parameters['familyIdFilter'];
		}
		if (isset($parameters['articleFilter']) && $parameters['articleFilter'] != "") {
			$articleFilter = trim($parameters['articleFilter']);
			$filter .= " AND (detailsByOrder.code LIKE ".$this->company_db->escape("%".$articleFilter."%")." OR ";
			$filter .= "detailsByOrder.description LIKE ".$this->company_db->escape("%".$articleFilter."%").")";
		}
		if (isset($parameters['recordsPerPage']) && $parameters['recordsPerPage'] > 0) {
			$page = 1;
			if (isset($parameters['page']) && $parameters['page'] > 0) {
				$page = (int)$parameters['page'];
			}
			$fromRecord = ($page == 1?0:(($page - 1) * (int)$parameters['recordsPerPage']));
			$limit = " LIMIT ".$fromRecord.",".(int)$parameters['recordsPerPage'];
		} else if (isset($parameters['limit']) && $parameters['limit'] > 0) {
			$limit = " LIMIT ".(int)$parameters['limit'];
		}

		// Las cantidades se calculan desde remitos vigentes para evitar depender del cache del detalle.
		// El plazo se lee de orders.maximumDate porque es la fecha real de cumplimiento.
		// CAST evita el error de MySQL moderno con el literal DATE '0000-00-00'.
		$withoutMaximumDate = "(orders.maximumDate IS NULL OR CAST(orders.maximumDate AS CHAR) = '0000-00-00')";
		$sql = "SELECT SQL_CALC_FOUND_ROWS ";
		$sql .= "orders.id AS orderId, ";
		$sql .= "orders.date AS orderDate, ";
		$sql .= "orders.maximumDays, ";
		$sql .= "IF(".$withoutMaximumDate.",'',orders.maximumDate) AS maximumDate, ";
		$sql .= "orders.stateId, ";
		$sql .= "ordersStates.description AS stateDescription, ";
		$sql .= "companies.id AS companyId, ";
		$sql .= "companies.description AS companyDescription, ";
		$sql .= "branchOffices.description AS branchOfficeDescription, ";
		$sql .= "detailsByOrder.id AS detailOrderId, ";
		$sql .= "detailsByOrder.code AS articleCode, ";
		$sql .= "CONCAT(detailsByOrder.code,' - ',detailsByOrder.description) AS articleDescription, ";
		$sql .= "IF(ISNULL(families.id),'',families.description) AS familyDescription, ";		
		$sql .= "detailsByOrder.quantity AS requestedQuantity, ";
		$sql .= "IFNULL(deliveryNotesSummary.deliveredQuantity,0) AS deliveredQuantity, ";	
		$sql .= "IF(IFNULL(deliveryNotesSummary.deliveredQuantity,0) >= detailsByOrder.quantity,'Entrega Completa',IF(IFNULL(deliveryNotesSummary.deliveredQuantity,0) > 0,'Entrega Parcial','Sin Entregar Aún')) AS deliveryStateDescription, ";
		$sql .= "IF(".$withoutMaximumDate.",'Sin plazo',IF(orders.maximumDate < CURDATE(),'Vencido',IF(orders.maximumDate = CURDATE(),'Vence hoy','No vencido'))) AS dueSituationDescription, ";
		$sql .= "IF(".$withoutMaximumDate.",'Sin plazo',IF(orders.maximumDate < CURDATE(),CONCAT('Vencido hace ',DATEDIFF(CURDATE(),orders.maximumDate),' días'),IF(orders.maximumDate = CURDATE(),'Vence hoy',CONCAT('Faltan ',DATEDIFF(orders.maximumDate,CURDATE()),' días')))) AS delayDescription, ";
		$sql .= "IF(ISNULL(purchasesOrders.id) OR purchasesOrders.id <= 0,'',CONCAT(purchasesOrders.id,'-',companies.id,'-',DATE_FORMAT(purchasesOrders.date,'%m%y'))) AS purchaseOrderNumber, ";
		$sql .= "IFNULL(deliveryNotesSummary.deliveryNotes,'') AS deliveryNotes ";
		// Se usan nombres reales de tablas en minuscula para que funcione tambien en servidores Linux.
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
		$sql .= "AND detailsByOrder.quantity > 0 ";
		$sql .= $filter." ";
		$sql .= "ORDER BY TRIM(families.description), TRIM(detailsByOrder.description), orders.maximumDate, orders.id ";
		$sql .= $limit;		

		$query = $this->company_db->query($sql);
		if (!$query) {
			$error = $this->company_db->error();
			log_message('error','getPartialDeliveries query failed: '.$error['code'].' - '.$error['message']);
			return $data;
		}

		$queryTotal = $this->company_db->query("SELECT FOUND_ROWS() AS totalRecords");
		$totalRecords = $query->num_rows();
		if ($queryTotal && $queryTotal->num_rows() > 0) {
			$totalRecords = $queryTotal->row()->totalRecords;
		}
		$data = array('list'=>$query->result_array(),
	                  'totalRecords'=>$totalRecords);

		return $data;
	}

	function getGeneralReport($parameters=NULL){						
		$data = NULL;
		
		$filter = "";
		$parameters = sanitizeQueryParameters($parameters);		

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

	function getEstimatedPurchase($parameters=NULL){				
		$data = array('list'=>NULL, 
		              'totalRecords'=>0);
				
		$limit = "";	 
		$order = "";
		$filterArticles = "";
		$filterMovements = "";	
		$parameters = sanitizeQueryParameters($parameters);									
		
		if (isset($parameters['dateFromFilter']) && $parameters['dateFromFilter'] != "") {
			$filterMovements .= " AND o.date >= '".$parameters['dateFromFilter']." 00:00:00' ";
		}
		if (isset($parameters['dateToFilter']) && $parameters['dateToFilter'] != "") {
			$filterMovements .= " AND o.date <= '".$parameters['dateToFilter']." 23:59:59' ";
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
			$filterArticles .= " AND a.id = ".$articleId;				
		}		
		if (isset($parameters['articleActiveFilter']) && $parameters['articleActiveFilter'] != '') {
			$filterArticles .= " AND a.active = ".$parameters['articleActiveFilter'];				
		}	
		if (isset($parameters['articleUsualFilter']) && $parameters['articleUsualFilter'] != '') {
			$filterArticles .= " AND a.usual = ".$parameters['articleUsualFilter'];				
		}			
		if (isset($parameters['familyIdsFilter']) && $parameters['familyIdsFilter'] != '' && $parameters['familyIdsFilter'] != 'all') {				
			$filterArticles .= " AND a.familyId IN(".str_replace("|",",",$parameters['familyIdsFilter']).")";	
		}		
		if (isset($parameters['selectedFilter']) && $parameters['selectedFilter'] != '') {				
			$filterArticles .= " AND a.id IN(".str_replace("|",",",$parameters['selectedFilter']).")";	
		}
		if (isset($parameters['notSelectedFilter']) && $parameters['notSelectedFilter'] != '') {				
			$filterArticles .= " AND NOT a.id IN(".str_replace("|",",",$parameters['notSelectedFilter']).")";	
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
				case 'buy':
					$order .= "quantityToBuy ".$auxOrder.", familyDescription, description ";
				break;	
				case 'des':
					$order .= "description ".$auxOrder.", familyDescription, quantityToBuy DESC ";
				break;
				case 'fam':
					$order .= "familyDescription ".$auxOrder.", description, quantityToBuy DESC ";
				break;					
			}														
		}
		if ($order == "") {
			$order .= "quantityToBuy DESC, familyDescription, description ";			
		}

		$estimatedDays = (isset($parameters['estimatedDays']) && (int)$parameters['estimatedDays'] > 0?(int)$parameters['estimatedDays']:0);
		if (isset($parameters['dateFromFilter']) && $parameters['dateFromFilter'] != "" && isset($parameters['dateToFilter']) && $parameters['dateToFilter'] != "") {
			$periodDays = (int)getDifferenceOfDaysBetweenDates($parameters['dateFromFilter'],$parameters['dateToFilter']);
			if ($periodDays < 1) $periodDays = 1;
		} else {
			$periodDays = 1;
		}			
		
		$warehouseIds = ""; 
		$sqlWarehouses = "SELECT DISTINCT w.id FROM warehouses w WHERE w.deleted = 0 AND w.active = 1 ";	
		if (isset($parameters) && isset($parameters['companyIdsFilter']) && $parameters['companyIdsFilter'] != '' && $parameters['companyIdsFilter'] != 'all') {				
			$sqlWarehouses .= " AND w.id IN (SELECT c.warehouseId FROM companies c WHERE c.id IN(".str_replace("|",",",$parameters['companyIdsFilter'])."))";							
		}
		$queryWarehouses = $this->company_db->query($sqlWarehouses);			
		if ($queryWarehouses->num_rows() > 0) {
			$lstWarehouses = $queryWarehouses->result_array();
			
			$estimatedExpr = "";
			$stockPivot = "";
			$baseEstimated = "";
			$baseStock = "";
			$baseStock = "";
			$calcEstimated = "";
			$calcStock = "";
			$calcToBuy = "";
			$calcTotal = "0";
	    
			for ($i=0; $i < count($lstWarehouses); $i++) {											
				$wId = $lstWarehouses[$i]["id"];
				
				if ($warehouseIds != "") $warehouseIds .= ",";
				$warehouseIds .= $wId;
								
				$estimatedExpr 	.= ", CEIL((SUM(IF(o.warehouseId = ".$wId.", d.quantity, 0)) / ".$periodDays.") * ".$estimatedDays.") AS estimatedQuantity".$wId;	
				$stockPivot    	.= ", SUM(IF(warehouseId = ".$wId.", qty, 0)) AS stock".$wId;
	    		$baseEstimated 	.= ", COALESCE(s.estimatedQuantity".$wId.", 0) AS estimatedQuantity".$wId;
	    		$baseStock     	.= ", COALESCE(ss.stock".$wId.", 0) AS stock".$wId;
	    		$calcEstimated 	.= ", estimatedQuantity".$wId;
	    		$calcStock     	.= ", stock".$wId;
	    		$calcToBuy     	.= ", (estimatedQuantity".$wId." - stock".$wId.") AS quantityToBuy".$wId;
	    		$calcTotal     	.= "+ GREATEST(estimatedQuantity".$wId." - stock".$wId.", 0)";				
			}									
		}					

		$sql = "SELECT SQL_CALC_FOUND_ROWS * 
				FROM (
					SELECT id, code, description, familyDescription 
							".$calcEstimated.$calcStock.$calcToBuy.", (".$calcTotal.") AS quantityToBuy 
					FROM (
						SELECT a.id, a.code, TRIM(a.description) AS description, TRIM(f.description) AS familyDescription  
								".$baseEstimated.$baseStock." 
						FROM articles a 
						LEFT JOIN families f ON f.id = a.familyId 

						/* summary */
						LEFT JOIN (
							SELECT d.articleId ".$estimatedExpr."  
							FROM detailsByOrder d 
							INNER JOIN orders o ON d.orderId = o.id 
							INNER JOIN articles a ON a.id = d.articleId 						
							WHERE 	d.deleted = 0 AND o.deleted = 0 AND a.deleted = 0 
								AND d.affectsStock = 1 
								AND d.articleId > 0 
								AND o.warehouseId IN (".$warehouseIds.") 
								AND o.stateId NOT IN ('ARM','TOAUT','NOTAUT','CAN','SUS') 
								AND d.quantity > 0  
								".$filterMovements." 
								".$filterArticles." 
							GROUP BY d.articleId
						) s ON s.articleId = a.id 

						/* summaryStock (stockRaw agregado) */
						LEFT JOIN (
							SELECT articleId ".$stockPivot." 
							FROM (
								SELECT sm.articleId, sm.warehouseId, SUM(sm.quantity * IF(sm.input = 1, 1, -1)) AS qty 
								FROM stockMovements sm 
								WHERE 	sm.deleted = 0 
									AND sm.warehouseId IN (".$warehouseIds.") 
								GROUP BY sm.articleId, sm.warehouseId 
								UNION ALL 
								SELECT dn.articleId, dn.warehouseId, -SUM(dn.quantity) AS qty 
								FROM detailsByDeliveryNotes dn 
								WHERE 	dn.deleted = 0 
									AND dn.warehouseId IN (".$warehouseIds.") 
								GROUP BY dn.articleId, dn.warehouseId 
								UNION ALL 
								SELECT db.articleId, bi.warehouseId, SUM(db.quantity) AS qty 
								FROM detailsByBill db 
								INNER JOIN bills bi ON db.billId = bi.id 
								WHERE 	bi.deleted = 0 AND db.deleted = 0 
									AND bi.warehouseId IN (".$warehouseIds.") 
								GROUP BY db.articleId, bi.warehouseId 
								UNION ALL 								
								SELECT d.articleId, o.warehouseId, -SUM(d.quantity - d.deliveredQuantity) AS qty 
								FROM detailsByOrder d 
								INNER JOIN orders o ON d.orderId = o.id
								WHERE 	o.deleted = 0 AND d.deleted = 0 
									AND d.affectsStock = 1 AND d.articleId > 0 
									AND o.warehouseId IN (".$warehouseIds.") 
									AND o.stateId NOT IN ('ARM','CAN','FIN','NOTAUT','SUS','TOAUT') 
								GROUP BY d.articleId, o.warehouseId
							) stockRaw 
							GROUP BY articleId
						) ss ON ss.articleId = a.id 

						/* candidates = con ventas en el período O con pedidos pendientes */
						WHERE a.deleted = 0 
							AND (
								s.articleId IS NOT NULL 
								OR a.id IN (
									SELECT d.articleId 
									FROM detailsByOrder d 
									INNER JOIN orders o ON d.orderId = o.id 
									INNER JOIN articles a ON a.id = d.articleId 
									WHERE 	o.deleted = 0 AND d.deleted = 0 AND a.deleted = 0 
										AND d.affectsStock = 1 
										AND d.articleId > 0 								
										AND o.warehouseId IN (".$warehouseIds.") 
										AND o.stateId NOT IN ('ARM','CAN','FIN','NOTAUT','SUS','TOAUT') 										
										AND d.quantity - d.deliveredQuantity > 0 
										".$filterArticles."
								)
							)
					) base
				) calc 
				WHERE quantityToBuy > 0 
				ORDER BY ".$order.
				$limit;

		$query = $this->company_db->query($sql);						
		if ($query === false) {
			var_dump($this->company_db->error());			
		    exit;
		} else {		
			$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');	
			if ($query->num_rows() > 0) {							
				$data = array('list'=>$query->result_array(),
			                  'totalRecords'=>$queryTotal->row()->totalRecords);	
			}						
		}

		return $data;		
	}  		

}
/* End of file Reports_model.php */
/* Location: ./application/models/Reports_model.php */