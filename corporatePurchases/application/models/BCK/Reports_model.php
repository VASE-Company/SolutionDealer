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
		$filterStock = "";	
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
			$filterStock = " WHERE summaryStock.stock <> 0 ";						
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
				$sqlStock = "SELECT articleId, warehouseId, quantity * IF(input=1,1,-1) AS auxQuantity  
							 FROM stockMovements 
							 WHERE deleted = 0 ".$filterMovements." 
							 UNION ALL
							 SELECT articleId, warehouseId, quantity * -1 AS auxQuantity
							 FROM detailsByDeliveryNotes 
							 WHERE deleted = 0 ".$filterMovements."
							 UNION ALL 
							 SELECT detailsByOrder.articleId, orders.warehouseId, -1 * (readyQuantity - deliveredQuantity) AS auxQuantity
							 FROM detailsByOrder INNER JOIN orders 
							 ON detailsByOrder.orderId = orders.id
							 WHERE orders.deleted = 0 AND detailsByOrder.deleted = 0 AND 
							       NOT orders.stateId IN ('ARM','CAN','FIN','NOTAUT','SUS','TOAUT') ".$filterMovements;
			break;

			case 'OUTOFSTOCK':
				$sqlStock = "SELECT detailsByOrder.articleId, orders.warehouseId, (quantity - readyQuantity) AS auxQuantity
				             FROM detailsByOrder INNER JOIN orders 
				             ON detailsByOrder.orderId = orders.id
				  	         WHERE orders.deleted = 0 AND detailsByOrder.deleted = 0 AND 
				  	               NOT orders.stateId IN ('ARM','CAN','FIN','NOTAUT','SUS','TOAUT') ".$filterMovements;				
			break;

			default:
				$sqlStock = "SELECT articleId, warehouseId, quantity * IF(input=1,1,-1) AS auxQuantity  
							 FROM stockMovements 
							 WHERE deleted = 0 ".$filterMovements." 
							 UNION ALL
							 SELECT articleId, warehouseId, quantity * -1 AS auxQuantity
							 FROM detailsByDeliveryNotes 
							 WHERE deleted = 0 ".$filterMovements;
			break;
		}				
		
		$sql = "SELECT 
		               summaryArticles.id,
		               summaryArticles.code,
		               summaryArticles.description,
		               families.description	AS familyDescription,
		               summaryStock.warehouseId, 
		               summaryStock.stock
		        FROM (
		              (SELECT id, code, description, familyId
		               FROM articles 
		               WHERE articles.deleted = 0 ".$filterArticles." 
		               ORDER by description ".
		               $limit.") AS summaryArticles		
		              LEFT JOIN families ON summaryArticles.familyId = families.id) 
		        LEFT JOIN (
		        			SELECT articleId, warehouseId, SUM(auxQuantity) AS stock 
							FROM (
								".$sqlStock." 
							) AS summary 				
							GROUP BY articleId, warehouseId 
		                  ) AS summaryStock ON summaryStock.articleId = summaryArticles.id "
		        .$filterStock." 			
				ORDER BY TRIM(summaryArticles.description), summaryArticles.id  "
		        .$generalLimit;			        

		$query = $this->company_db->query($sql);		
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0) {				

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
}


/* End of file Reports_model.php */
/* Location: ./application/models/Reports_model.php */