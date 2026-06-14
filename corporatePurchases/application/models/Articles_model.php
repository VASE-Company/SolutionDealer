<?php
class Articles_model extends CI_Model{    

	private $company_db;         

	function __construct() {        
        parent::__construct();        

        $this->company_db = $this->load->database('default', TRUE);         
    }      
    
   	function getArticles($parameters=NULL){				
		$articles = array('list'=>NULL, 
		                  'totalRecords'=>0);
		
		$filter = "";
		$order = "";
		$limit = "";
		if (isset($parameters)) {
			if (isset($parameters['activeOrIdFilter']) && $parameters['activeOrIdFilter'] == true) {
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND (articles.active = 1 OR articles.id = ".$parameters['idFilter'].")";				
				} else {
					$filter .= " AND articles.active = 1";				
				}				
			} else {			
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND articles.id = ".$parameters['idFilter'];				
				}
			}		
			if (isset($parameters['codeFilter']) && $parameters['codeFilter'] != '') {
				$filter .= " AND articles.code = ".$this->company_db->escape($parameters['codeFilter']);				
			}
			if (isset($parameters['textFilter']) && $parameters['textFilter'] != '') {
				if (strpos($parameters['textFilter'], ",") != false) {
				    $arr = explode(",",$parameters['textFilter']);	

				    $codes = "";
				    for ($i=0; $i < count($arr); $i++) {
				   		if ($i > 0) $codes .= ",";
				   		$codes .= $this->company_db->escape($arr[$i]);
				    }
				    $filter .= " AND articles.code IN (".$codes.")";
				} else {
					$filter .= " AND ";
					$filter .= "(articles.code LIKE ".$this->company_db->escape("%".$parameters['textFilter']."%");
					$filter .= " OR ";
					$filter .= "articles.description LIKE ".$this->company_db->escape("%".$parameters['textFilter']."%").")";
				}
			}
			if (isset($parameters['familyIdFilter']) && $parameters['familyIdFilter'] > 0) {
				$filter .= " AND articles.familyId = ".$parameters['familyIdFilter'];				
			}
			if (isset($parameters['activeFilter']) && $parameters['activeFilter'] != '') {
				$filter .= " AND articles.active = ".$parameters['activeFilter'];				
			}
			if (isset($parameters['affectsStockFilter']) && $parameters['affectsStockFilter'] != '') {
				//HABILITAR USA STOCK
				$filter .= " AND families.affectsStock = ".$parameters['affectsStockFilter'];				
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
					case 'cod':
						$order .= "articles.code ".$auxOrder;
					break;					
					case 'des':
						$order .= "articles.description ".$auxOrder;
					break;	
					case 'fam':
						$order .= "families.description ".$auxOrder.", articles.description ";
					break;						
				}														
			}
		}
		
		if ($order == "") {
			$order .= "articles.description ";
		}	
						
		$sql = "SELECT SQL_CALC_FOUND_ROWS 
		               articles.*,
		               families.description	AS familyDescription,
		               families.affectsStock  
		        FROM articles LEFT JOIN families
		        ON articles.familyId = families.id 
				WHERE articles.deleted = 0 ".$filter." 
				ORDER BY ".$order.
				$limit;		
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0) {
			$auxArticles = $query->result_array();

			if (isset($parameters) &&isset($parameters['fullData']) && $parameters['fullData'] == true) {
				for ($i=0; $i < count($auxArticles); $i++) {														
					$locationsByArticle = $this->getLocationsByArticle($auxArticles[$i]['id']);
					$auxArticles[$i]['locations'] = $locationsByArticle['list'];
				}
			}
			
			$articles = array('list'=>$auxArticles, 
		                      'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $articles;
	}

	function getLocationsByArticle($articleId=-1){				
		$locations = array('list'=>NULL, 
		                   'totalRecords'=>0);
		
		$sql = "SELECT SQL_CALC_FOUND_ROWS 
					   warehouses.id AS warehouseId,
					   warehouses.description AS warehouseDescription,
					   AL.id, 
					   AL.corridor, 
					   AL.shelf 
		        FROM warehouses 
		        LEFT JOIN (SELECT * FROM articlesLocation 
		                   WHERE articlesLocation.deleted = 0 AND 
		                         articlesLocation.articleId = ".$articleId.") AS AL 			        
		        ON warehouses.id = AL.warehouseId 
				WHERE warehouses.deleted = 0 
				ORDER BY warehouses.description";									
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0) {					
			$locations = array('list'=>$query->result_array(), 
		                       'totalRecords'=>$queryTotal->row()->totalRecords);
		}	
		
		return $locations;
	}

	function getLocationDescription($articleId=-1,$warehouseId=-1) {	
		$location = "";

		if ($articleId > 0 && $warehouseId > 0) {
			$sql = "SELECT * 
			        FROM articlesLocation 
			        WHERE articlesLocation.deleted = 0 AND 
			              articlesLocation.articleId = ".$articleId." AND 
			              articlesLocation.warehouseId = ".$warehouseId;									
				
			$query = $this->company_db->query($sql);
			
			if ($query->num_rows() > 0) {					
				$row = $query->row_array();

				if (trim($row['corridor']) != "") {
					if ($location != "") $location .= " - ";
					$location .= trim($row['corridor']);
				}
				if (trim($row['shelf']) != "") {
					if ($location != "") $location .= " - ";
					$location .= trim($row['shelf']);
				}
			}	
		}

		if ($location == "") $location = "-";
		
		return $location;
	}
		
	function getEmptyArticle() {
		$locationsByArticle = $this->getLocationsByArticle(0);		

		$article = array('id'=>-1,							 						       
				         'code'=>'',
				         'description'=>'',				         
				         'familyId'=>0,				         
				         'usual'=>0,		
				         'active'=>1,
				     	 'locations'=>$locationsByArticle['list'],
				     	 'lasUnitPrice'=>0);
						
		return $article;
	}
	
	function existsValue($value='', $id=-1, $field='') {
		$exists = false;
		
		if ($value != '' && $field != '') {
			$sql = "SELECT * 
					FROM articles 
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
	
	function setArticle($data){
		if (!empty($data)){				

			$this->company_db->set('code',$data['code']);
			$this->company_db->set('description',str_replace('"',"",$data['description']));			
			$this->company_db->set('familyId',$data['familyId']);
			$this->company_db->set('usual',(int)$data['usual']);
			$this->company_db->set('active',(int)$data['active']);
			
			if ((float)$data['id'] > 0) {
				$this->company_db->where('id',$data['id']);				
				$this->company_db->update('articles');				
			} else {				
				$this->company_db->set('deleted',0);
				$this->company_db->set('deletedDate',NULL);
				$this->company_db->set('deletedUserId',NULL);
				
				$this->company_db->insert('articles');
				$data['id'] = $this->company_db->insert_id();
			}
			
			$error = $this->company_db->error();
			if ((int)$error['code'] != 0) {							
				return false;
			} else {		

				if (isset($data['locations'])) 
					$this->_setArticleLocations($data['id'],$data['locations']);

				return true;
			}						
		} else {
			return false;	
		}
	}	
	
	function _setArticleLocations($articleId=-1,$locations=NULL) {			
		if ($articleId > 0) { 			
			if (isset($locations) && count($locations) > 0) {			
				for ($i=0; $i < count($locations); $i++) {						
					$this->company_db->set('corridor',$locations[$i]['corridor']);
					$this->company_db->set('shelf',$locations[$i]['shelf']);
					if ((float)$locations[$i]['id'] > 0) {
						$this->company_db->where('id',$locations[$i]['id']);				
						$this->company_db->update('articlesLocation');				
					} else {				
						$this->company_db->set('articleId',$articleId );								
						$this->company_db->set('warehouseId',$locations[$i]['warehouseId']);
						$this->company_db->set('deleted',0);
						$this->company_db->set('deletedDate',NULL);
						$this->company_db->set('deletedUserId',NULL);				
						$this->company_db->insert('articlesLocation');
						$locations[$i]['id'] = $this->company_db->insert_id();
					}
				}															
			} 					
		}
	}		

	function deleteArticle($id=-1){
		if ($this->deleteArticleValid($id)){	
			$this->company_db->set('deleted',1);	
			$this->company_db->set('deletedDate',getCurrentDate());	        
			$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         
			$this->company_db->where('id',$id);				
			$this->company_db->update('articles');		

			$error = $this->company_db->error();
			if ((int)$error['code'] != 0) {			
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}		
	}

	function deleteArticleValid($id=-1){
		if ($id > 0){	
			$allowDelete = true;
			
			if ($allowDelete) {		
				$sql = "SELECT detailsByOrder.* 
						FROM detailsByOrder INNER JOIN orders ON detailsByOrder.orderId = orders.id						
						WHERE detailsByOrder.deleted = 0 AND orders.deleted = 0 AND 
						      detailsByOrder.articleId = ".$id." 
						LIMIT 0,1";							
				$query = $this->company_db->query($sql);						
				if ($query->num_rows() > 0){   
					$allowDelete = false;
				}						
			}		

			if ($allowDelete) {		
				$sql = "SELECT detailsByDeliveryNotes.* 
						FROM detailsByDeliveryNotes INNER JOIN deliveryNotes ON detailsByDeliveryNotes.deliveryNoteId = deliveryNotes.id						
						WHERE detailsByDeliveryNotes.deleted = 0 AND deliveryNotes.deleted = 0 AND 
						      detailsByDeliveryNotes.articleId = ".$id." 
						LIMIT 0,1";							
				$query = $this->company_db->query($sql);						
				if ($query->num_rows() > 0){   
					$allowDelete = false;
				}						
			}		

			if ($allowDelete) {		
				$sql = "SELECT stockMovements.* 
						FROM stockMovements 
						WHERE stockMovements.deleted = 0 AND 
						      stockMovements.articleId = ".$id." 
						LIMIT 0,1";							
				$query = $this->company_db->query($sql);						
				if ($query->num_rows() > 0){   
					$allowDelete = false;
				}						
			}	

			if ($allowDelete) {						
				$sql = "SELECT detailsByBill.* 
						FROM detailsByBill INNER JOIN bills ON detailsByBill.billId = bills.id						
						WHERE detailsByBill.deleted = 0 AND bills.deleted = 0 AND 
						      detailsByBill.articleId = ".$id." 
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

	function importPreLoad($filename="") {		
		$response['error'] = "";

		if ($filename == "") {
			$response['error'] = "No se encontró el archivo a importar";
		} else {			
			if (!file_exists($filename)) {
				$response['error'] = "No se encontró el archivo a importar";
			} else {
			    if ($response['error'] == "") {
				    $sql = "TRUNCATE TABLE tmpArticles";							
					$query = $this->company_db->query($sql);

					$error = $this->company_db->error();
					if ((int)$error['code'] != 0) {	
						$response['error'] = $error['message'];						
					}
				}

				if ($response['error'] == "") {				
				    $sql = 'LOAD DATA LOCAL INFILE "'.$filename.'"
		                    INTO TABLE tmpArticles 
		                    FIELDS TERMINATED BY \';\'
		                    LINES TERMINATED BY \'\n\'
		                    ';							
					$query = $this->company_db->query($sql);
					$error = $this->company_db->error();
					if ((int)$error['code'] != 0) {	
						$response['error'] = $error['message'];						
					}			     				
				}

				if ($response['error'] == "") {	
					$sql = "UPDATE tmpArticles			                
			                SET tmpArticles.code = TRIM(tmpArticles.code),
			                    tmpArticles.description = TRIM(REPLACE(tmpArticles.description,'".'"'."','')),
			                    tmpArticles.familyDescription = TRIM(REPLACE(tmpArticles.familyDescription,'".'"'."',''))";		
					$query = $this->company_db->query($sql);

					$responseDifferences = $this->_differencesArticlesPreLoad();											

			        if ($responseDifferences['count'] <= 0) {			            
			            $response['error'] = "No se encontraron registros en la pre carga del archivo.";
			        } else {
			        	$response['count'] = $responseDifferences['count'];	
			        	$response['updated'] = $responseDifferences['updated'];				        	
			        	$response['equal'] = $responseDifferences['equal'];	
			        	$response['news'] = $responseDifferences['news'];	
			        }
			    }
			}
		}

		return $response;
	}
	
	function _differencesArticlesPreLoad() {
		
		$sql = "SELECT COUNT(*) count FROM tmpArticles";									
			
		$query = $this->company_db->query($sql);			
		if($query->num_rows() > 0){
			$row = $query->row_array();

			$response['count'] = (int)$row['count'];			
		} else {
			$response['count'] = 0;
		}
		
		//-----------------------------------------------------------------------------

       $sql = "SELECT COUNT(*) count 
               FROM (articles INNER JOIN tmpArticles ON articles.code = tmpArticles.code)
               LEFT JOIN families ON articles.familyId = families.id 
               WHERE tmpArticles.description <> articles.description OR 
                     tmpArticles.familyDescription <> families.description OR 
                     tmpArticles.active <> articles.active";									
		
		$query = $this->company_db->query($sql);			
		if($query->num_rows() > 0){
			$row = $query->row_array();

			$response['updated'] = (int)$row['count'];								
		} else {
			$response['updated'] = 0;						
		}		

		//-----------------------------------------------------------------------------

		$sql = "SELECT COUNT(*) count                                
                FROM tmpArticles 
                WHERE NOT EXISTS(SELECT * FROM articles
                                 WHERE articles.code = tmpArticles.code)";									
		
		$query = $this->company_db->query($sql);			
		if($query->num_rows() > 0){
			$row = $query->row_array();

			$response['news'] = (int)$row['count'];														
		} else {
			$response['news'] = 0;									
		}

		$response['equal'] = $response['count'] - $response['updated'] - $response['news'];
	   	
	   	return $response;
	}


	function importLoad() {		
		$response['error'] = array();		

		//-----------------------------------------------------------------------------
		//-------------------------------- ARTICLES -----------------------------------
		//-----------------------------------------------------------------------------

		//Insert families if not exists

		$sql = "INSERT INTO `families` 
			                            (id,
			                             description,color,active,
			                             deleted,deletedDate,deletedUserId) 
			                		SELECT DISTINCT 
			                             NULL,
			                             familyDescription,'',1,			                             
			                             0,NULL,NULL
			                        FROM tmpArticles
			                        WHERE NOT EXISTS(SELECT * FROM families
			                                         WHERE families.description = tmpArticles.familyDescription AND families.deleted = 0)";							
		$query = $this->company_db->query($sql);

		//-----------------------------------------------------------------------------

		//Update FamilyId in Temp Table

		$sql = "UPDATE tmpArticles INNER JOIN families
                ON tmpArticles.familyDescription = families.description AND families.deleted = 0
                SET tmpArticles.familyId = families.id";							
		$query = $this->company_db->query($sql);		

		//-----------------------------------------------------------------------------

	    //Update fields code, description

	    $sql = "UPDATE articles INNER JOIN tmpArticles
                ON articles.code = tmpArticles.code 
                SET articles.description = tmpArticles.description,                    
                    articles.active = tmpArticles.active";							
		$query = $this->company_db->query($sql);

		//-----------------------------------------------------------------------------

		//Update field familyId in table articles

		$sql = "UPDATE articles INNER JOIN tmpArticles
                ON articles.code = tmpArticles.code 
                SET articles.familyId = tmpArticles.familyId
                WHERE tmpArticles.familyId > 0";							
		$query = $this->company_db->query($sql);		

		//-----------------------------------------------------------------------------

		//Insert articles if not exists

		$sql = "INSERT INTO `articles` 
			                            (id,
			                             code,description,usual,
			                             familyId,active,
			                             deleted,deletedDate,deletedUserId) 
			                		SELECT 
			                             NULL,
			                             code,description,0,
			                             familyId,active,
			                             0,NULL,NULL
			                        FROM tmpArticles
			                        WHERE NOT EXISTS(SELECT * FROM articles
			                                         WHERE articles.code = tmpArticles.code)
			                              AND tmpArticles.familyId > 0";							
		$query = $this->company_db->query($sql);


		//-----------------------------------------------------------------------------

		//Verify differences between tmpArticles and articles

		$sql = "SELECT COUNT(*) count
                 FROM articles INNER JOIN tmpArticles 
                 ON articles.code = tmpArticles.code
                 WHERE tmpArticles.description <> articles.description OR 
                     	tmpArticles.familyId <> articles.familyId OR 
                     	tmpArticles.active <> articles.active";									
		
		$query = $this->company_db->query($sql);			
		if($query->num_rows() > 0){
			$row = $query->row_array();

			$different = (int)$row['count'];                	
		} else {
			$different = 0;
		}

		//-----------------------------------------------------------------------------

		//Update field articleId in table tmpArticles

		$sql = "UPDATE articles INNER JOIN tmpArticles
                ON articles.code = tmpArticles.code 
                SET tmpArticles.articleId = articles.id";							
		$query = $this->company_db->query($sql);

		//-----------------------------------------------------------------------------

		//Verify if not insert codes in articles table

		$sql = "SELECT COUNT(*) count 
                 FROM tmpArticles 
                 WHERE tmpArticles.articleId <= 0 OR tmpArticles.articleId IS NULL";									
		
		$query = $this->company_db->query($sql);			
		if($query->num_rows() > 0){
			$row = $query->row_array();

			$notExists = (int)$row['count'];                	
		} else {
			$notExists = 0;
		}

		//-----------------------------------------------------------------------------		

		if ($different > 0 && $notExists > 0) {
			$response['error'][count($response['error'])] = "ERROR: quedaron $different artículo(s) con datos desactualizados y $notExists no se pudieron dar de alta";
		} else {
			if ($different > 0) { 
				$response['error'][count($response['error'])] = "ERROR: quedaron $different artículo(s) con datos desactualizados";
			}
			if ($notExists > 0) {
				$response['error'][count($response['error'])] = "ERROR: no se pudieron dar de alta $notExists artículo(s)";
			}
		}

		//-----------------------------------------------------------------------------
		//---------------------------------- STOCK .-----------------------------------
		//-----------------------------------------------------------------------------
		/*
		$this->load->model('generalconfigurations_model','generalConfigurations');
		$this->load->model('warehouses_model','warehouses');
		$this->load->model('stockMovements_model','stockMovements');

		$errorStock = 0;

		$generalConfiguration = $this->generalConfigurations->getGeneralConfigurations();		

		$sql = "SELECT tmpArticles.articleId,tmpArticles.stock1, tmpArticles.stock2, tmpArticles.stock3, tmpArticles.stock4 
                 FROM tmpArticles 
                 WHERE tmpArticles.articleId > 0 AND 
                       (tmpArticles.stock1 > 0 OR tmpArticles.stock2 > 0 OR tmpArticles.stock3 > 0 OR tmpArticles.stock4 > 0)";									
		
		$query = $this->company_db->query($sql);		
		if ($query->num_rows() > 0) {		
			$stocks = $query->result_array();

			for ($i=0; $i < count($stocks); $i++) {
				for ($idxWarehouse=1; $idxWarehouse <= 4; $idxWarehouse++) {
					$warehouseId = (float)$generalConfiguration['importWarehouseId'.$idxWarehouse];

					if ($warehouseId  > 0 && (int)$stocks[$i]['stock'.$idxWarehouse] >= 0) {

						if (!isset($companies[$warehouseId])) {
							$companies[$warehouseId] = $this->warehouses->getFirstCompanyByWarehouse($warehouseId);							
						}

						if ($companies[$$warehouseId] > 0) {							

							$currentStock = $this->getStockArticle($stocks[$i]['articleId'],$warehouseId);

							$quantity = (int)$stocks[$i]['stock'.$idxWarehouse] - $currentStock['stock'];							

							if ($quantity <> 0) {
								$article[0]['id'] = 0;
								$article[0]['articleId'] = $stocks[$i]['articleId'];										
								$article[0]['quantity'] = abs($quantity);																		

								$stockMovement = array('id'=>0,									  							  						  
													   'typeId'=>($quantity < 0?$this->config->item('outputStockMovementTypeId'):$this->config->item('inputStockMovementTypeId')),								  								 
													   'companyId'=>$companies[$warehouseId],								  								 
													   'warehouseId'=>$warehouseId,								  								 
													   'observation'=>"Regularización de Stock.",
													   'details'=>$article);
								
								if (!$this->stockMovements->setStockMovement($stockMovement)) {
									$errorStock++;
								}
							}
						}
					}
				}
			}

			if ($errorStock > 0) { 
				$response['error'][count($response['error'])] = "ERROR: se generaron $errorStock error(es) al actualizar el stock";
			}	

		}
		*/	
		//-----------------------------------------------------------------------------
		        
		return $response;
	}


	function setArticlesUpdate($data){
		if (!empty($data)){				
			
			$this->company_db->set('date',getCurrentDate());	 		
			$this->company_db->set('userId',$this->session->userdata('userId'));
			$this->company_db->set('count',$data['count']);
			$this->company_db->set('updated',$data['updated']);			
			$this->company_db->set('news',$data['news']);
			$this->company_db->set('equal',$data['equal']);
			$this->company_db->set('state',$data['state']);		
			$this->company_db->set('observation',$data['observation']);			
			$this->company_db->set('deleted',0);
			$this->company_db->set('deletedDate',NULL);
			$this->company_db->set('deletedUserId',NULL);
				
			$this->company_db->insert('articlesUpdates');	
			$data['id'] = $this->company_db->insert_id();				
			
			$error = $this->company_db->error();
			if ((int)$error['code'] != 0) {							
				return false;
			} else {			
				if ($data['filename'] != "") {					
					$tmpPath = $this->config->item('files').'tmp/'.$data['filename'];					
					if (file_exists($tmpPath)) {
						$finalPath = $this->config->item('files').'articles/articlesUpdates_'.$data['id'].".csv";
						@rename($tmpPath,$finalPath);
					}
				}

				return true;
			}						
		} else {
			return false;	
		}
	}

	function getStockArticle($articleId=-1,$warehouseId=-1,$availableStock=false,$exludeStockMovementId=-1) {				
		$stock = array('stock'=> 0,
	                   'available'=>0,
	                   'ready'=>0,
	                   'pending'=>0);
		
		if ($articleId > 0) {
			$filter = "";
			
			if ($warehouseId > 0) {
				$filter .= " AND warehouseId = ".$warehouseId;				
			}			

			if ($availableStock) {
				$sqlAvailableStock = "UNION ALL 
									  SELECT 0 AS auxQuantity, (readyQuantity - deliveredQuantity) AS auxPending, (quantity - readyQuantity) AS auxOutOfStock
									  FROM detailsByOrder INNER JOIN orders 
									  ON detailsByOrder.orderId = orders.id
									  WHERE orders.deleted = 0 AND detailsByOrder.deleted = 0 AND 
									        NOT orders.stateId IN ('ARM','CAN','FIN','NOTAUT','SUS','TOAUT') AND
									        detailsByOrder.articleId = $articleId  "
									        .str_replace("warehouseId", "orders.warehouseId", $filter);
			} else {
				$sqlAvailableStock = "";
			}

			$sql = "SELECT SUM(auxQuantity) AS stock, SUM(auxPending) AS pending, SUM(auxOutOfStock) AS outOfStock  
			        FROM
				    (
						SELECT quantity * IF(input=1,1,-1) AS auxQuantity, 0 AS auxPending, 0 AS auxOutOfStock   
						FROM stockMovements WHERE deleted = 0 AND articleId = $articleId AND id <> $exludeStockMovementId ".$filter." 
						UNION ALL 
						SELECT detailsByBill.quantity AS auxQuantity, 0 AS auxPending, 0 AS auxOutOfStock  
						FROM detailsByBill INNER JOIN bills 
					    ON detailsByBill.billId = bills.id
					    WHERE bills.deleted = 0 AND detailsByBill.deleted = 0 AND detailsByBill.articleId = $articleId ".$filter." 
						UNION ALL 
						SELECT quantity * -1 AS auxQuantity, 0 AS auxPending, 0 AS auxOutOfStock 
						FROM detailsByDeliveryNotes WHERE deleted = 0  AND articleId = $articleId ".$filter." ".
						$sqlAvailableStock."
					) AS Summary";
			
			$query = $this->company_db->query($sql);			
			if($query->num_rows() > 0){
				$row = $query->row_array();

				$stock['stock'] = (float)$row['stock'];
				$stock['available'] = (float)$row['stock'] - (float)$row['pending'];			
				if ($stock['available'] < 0) $stock['available'] = 0;
				$stock['pending'] = (float)$row['pending'];
				if ($stock['pending'] < 0) $stock['pending'] = 0;												
				$stock['outOfStock'] = (float)$row['outOfStock'];
				if ($stock['outOfStock'] < 0) $stock['outOfStock'] = 0;											
			}
		}

		return $stock;		
	}	

	function getStockToUpdate() {
		$stock = array('list'=>NULL, 
		               'totalRecords'=>0);

		$sql = "SELECT SQL_CALC_FOUND_ROWS 
		               tmpStock.*
		        FROM tmpStock";		
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){
			$stock = array('list'=>$query->result_array(), 
		                   'totalRecords'=>$queryTotal->row()->totalRecords);
		}			

		return $stock;
	}

	function getOutOfStock($articleId=-1,$warehouseId=-1,$excludedOrderId=-1) {
		$details = array('list'=>NULL, 
		                 'totalRecords'=>0);

		if ($articleId > 0 && $warehouseId > 0) {
			$sql = "SELECT orders.id AS orderId, orders.date AS orderDate, orders.maximumDate AS orderMaximumDate, detailsByOrder.id AS detailId, 
			               quantity, (quantity - readyQuantity) AS outOfStock
				    FROM detailsByOrder INNER JOIN orders 
				    ON detailsByOrder.orderId = orders.id
				  	WHERE orders.deleted = 0 AND detailsByOrder.deleted = 0 AND 
				  	      NOT orders.stateId IN ('ARM','CAN','FIN','NOTAUT','SUS','TOAUT') AND
				  	      detailsByOrder.articleId = $articleId AND 
				  	      orders.warehouseId = $warehouseId AND 
				  	      orders.id <> $excludedOrderId AND
				  	      (quantity - readyQuantity) > 0
				  	ORDER BY orders.maximumDate, orders.id ";						  	      
				
			$query = $this->company_db->query($sql);
			$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
			if ($query->num_rows() > 0){
				$details = array('list'=>$query->result_array(), 
			                     'totalRecords'=>$queryTotal->row()->totalRecords);
			}			
		}

		return $details;
	}

	function getNotAvailableStock($articleId=-1,$warehouseId=-1) {
		$details = array('list'=>NULL, 
		                 'totalRecords'=>0);

		if ($articleId > 0 && $warehouseId > 0) {
			$sql = "SELECT orders.id AS orderId, orders.date AS orderDate, detailsByOrder.id AS detailId, 
			               quantity, (readyQuantity - deliveredQuantity) AS notAvailable 
				    FROM detailsByOrder INNER JOIN orders 
				    ON detailsByOrder.orderId = orders.id
				  	WHERE orders.deleted = 0 AND detailsByOrder.deleted = 0 AND 
				  	      NOT orders.stateId IN ('ARM','CAN','FIN','NOTAUT','SUS','TOAUT') AND
				  	      detailsByOrder.articleId = $articleId AND 
				  	      orders.warehouseId = $warehouseId AND 
				  	      (readyQuantity - deliveredQuantity) > 0
				  	ORDER BY orders.id ";						  	      
				
			$query = $this->company_db->query($sql);
			$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
			if ($query->num_rows() > 0){
				$details = array('list'=>$query->result_array(), 
			                     'totalRecords'=>$queryTotal->row()->totalRecords);
			}			
		}

		return $details;
	}

	//function updatePendingStock($articleId=-1,$warehouseId=-1,$originEntity=null) {
	function updatePendingStock($articleId=-1,$warehouseId=-1) {
		$this->load->model('orders_model','orders');

		if ($articleId > 0 && $warehouseId > 0) {			
			$currentStock = $this->getStockArticle($articleId,$warehouseId,true);			
			if ($currentStock['available'] > 0) {
				$availableStock = $currentStock['available'];
				
				$details = $this->getOutOfStock($articleId,$warehouseId);
				$details = $details['list'];

				if (isset($details) && count($details) > 0) {
					for ($i=0; $i < count($details) && $availableStock > 0; $i++) {						
						if ($availableStock >= $details[$i]['outOfStock']) {
							$readyStock = $details[$i]['outOfStock'];
						} else {
							$readyStock = $availableStock;
						}

						$sql = "UPDATE detailsByOrder			                
				                SET detailsByOrder.readyQuantity = detailsByOrder.readyQuantity + ".$readyStock." 
				                WHERE detailsByOrder.deleted = 0 AND detailsByOrder.id = ".$details[$i]['detailId'];		
						$query = $this->company_db->query($sql);
						
						$error = $this->company_db->error();
						if ((int)$error['code'] <= 0) {														
							$availableStock -= $readyStock;							
							
							/*
							if (isset($originEntity)) {
								$data['entity'] = $originEntity['type'];
								$data['entityId'] = $originEntity['id'];
								$data['entityMainId'] = (isset($originEntity['mainId'])?$originEntity['mainId']:null);
								$data['orderId'] = $details[$i]['orderId'];
								$data['detailOrderId'] = $details[$i]['detailId'];
								$data['quantity'] = $readyStock;
								$data['unitPrice'] = (isset($originEntity['unitPrice'])?$originEntity['unitPrice']:0);								

								$this->orders->setStockByOrden($data);

															
							} else {
							*/
								$stockAssign = $readyStock;

								$sql = "SELECT * FROM (
								        SELECT 'sm' AS type, stockMovements.id, 0 AS mainId, stockMovements.freeQuantity, stockMovements.unitPrice, stockMovements.date  
										FROM stockMovements 
										WHERE stockMovements.deleted = 0 
											  AND stockMovements.freeQuantity > 0 
										      AND stockMovements.articleId = ".$articleId."  
										      AND stockMovements.warehouseId = ".$warehouseId."  
										UNION ALL 
										SELECT 'bill' AS type, detailsByBill.id, bills.id AS mainId, detailsByBill.freeQuantity, detailsByBill.unitPrice, bills.date  
										FROM detailsByBill INNER JOIN bills 
									    ON detailsByBill.billId = bills.id
									    WHERE bills.deleted = 0 AND detailsByBill.deleted = 0 
									    	  AND detailsByBill.freeQuantity > 0 
									          AND detailsByBill.articleId = ".$articleId."  
										      AND bills.warehouseId = ".$warehouseId." 
										) AS summary
										ORDER BY date";										
								
								$query = $this->company_db->query($sql);				
								if ($query->num_rows() > 0) {
									$stocks = $query->result_array();

									for ($j=0; $j < count($stocks) && $stockAssign > 0; $j++) {									
										$data['entity'] = $stocks[$j]['type'];
										$data['entityId'] = $stocks[$j]['id'];
										$data['entityMainId'] = ($stocks[$j]['mainId'] > 0?$stocks[$j]['mainId']:null);
										$data['orderId'] = $details[$i]['orderId'];
										$data['detailOrderId'] = $details[$i]['detailId'];										
										$data['quantity'] = ($stocks[$j]['freeQuantity'] >= $stockAssign?$stockAssign:$stocks[$j]['freeQuantity']);
										$data['unitPrice'] = $stocks[$j]['unitPrice'];;								

										$this->orders->setStockByOrden($data);

										$stockAssign -= $data['quantity'];
									}
								}
							/*
							}
							*/							

							$this->orders->updateTotalDetailByOrden($details[$i]['detailId']);	
							
							$this->orders->updateOrderTotal($details[$i]['orderId']);
						}						
					}
				}			
			}
		}
	}

	function discountStockMovement($stockMovementId=-1) {
		$this->load->model('stockMovements_model','stockMovements');

		$sql = "SELECT * 
    			FROM stockMovements  
				WHERE deleted = 0 AND input = 0 AND id = ".$stockMovementId;																
		$query = $this->company_db->query($sql);			
		if ($query->num_rows() > 0){
			$row = $query->row_array();

			$articleId = $row['articleId'];
			$warehouseId = $row['warehouseId'];
			$stockToDiscount = $row['quantity'];			
		
			$currentStock = $this->getStockArticle($articleId,$warehouseId,true,$stockMovementId);
			if ($currentStock['available'] < $stockToDiscount) {
				$stockToDiscount = $currentStock['available'];
			} 						
			
			$sql = "SELECT * FROM (
				        SELECT 'sm' AS type, stockMovements.id, 0 AS mainId, stockMovements.freeQuantity, stockMovements.unitPrice, stockMovements.date, stockMovements.date AS realDate
						FROM stockMovements 
						WHERE stockMovements.deleted = 0 
							  AND stockMovements.freeQuantity > 0 
							  AND stockMovements.input = 1
						      AND stockMovements.articleId = ".$articleId."  
						      AND stockMovements.warehouseId = ".$warehouseId."  
						UNION ALL 
						SELECT 'bill' AS type, detailsByBill.id, bills.id AS mainId, detailsByBill.freeQuantity, detailsByBill.unitPrice, bills.billDate AS date , bills.date AS realDate
						FROM detailsByBill INNER JOIN bills 
					    ON detailsByBill.billId = bills.id
					    WHERE bills.deleted = 0 AND detailsByBill.deleted = 0 
					    	  AND detailsByBill.freeQuantity > 0 
					          AND detailsByBill.articleId = ".$articleId."  
						      AND bills.warehouseId = ".$warehouseId." 
					) AS summary
					ORDER BY YEAR(summary.date) ASC, MONTH(summary.date) ASC, DAY(summary.date) ASC, summary.realDate ASC";										
			
			$query = $this->company_db->query($sql);				
			if ($query->num_rows() > 0) {
				$stocks = $query->result_array();

				for ($i=0; $i < count($stocks) && $stockToDiscount > 0; $i++) {									
					$data['entity'] = $stocks[$i]['type'];
					$data['entityId'] = $stocks[$i]['id'];
					$data['entityMainId'] = ($stocks[$i]['mainId'] > 0?$stocks[$i]['mainId']:null);
					$data['originId'] = $stockMovementId;													
					$data['quantity'] = ($stocks[$i]['freeQuantity'] >= $stockToDiscount?$stockToDiscount:$stocks[$i]['freeQuantity']);																	

					$this->stockMovements->setStockByStockMovement($data);

					$stockToDiscount -= $data['quantity'];
				}
			}				
		}
	}

/*
	function updateAllPendingStock() {
		
		$sql = "SELECT articleId, warehouseId, (SUM(auxQuantity) - SUM(auxPending)) AS available 
		        FROM
			    (
					SELECT articleId, warehouseId, SUM(quantity * IF(input=1,1,-1)) AS auxQuantity, 0 AS auxPending
					FROM stockMovements WHERE deleted = 0 
					GROUP BY articleId, warehouseId
					UNION ALL 
					SELECT articleId, warehouseId, SUM(quantity * -1) AS auxQuantity, 0 AS auxPending
					FROM detailsByDeliveryNotes WHERE deleted = 0 
					GROUP BY articleId, warehouseId
					UNION ALL 
					SELECT articleId, warehouseId, quantity AS auxQuantity, 0 AS auxPending
				    FROM detailsByBill INNER JOIN bills 
				    ON detailsByBill.billId = bills.id
				    WHERE bills.deleted = 0 AND detailsByBill.deleted = 0 
				    GROUP BY detailsByBill.articleId, bills.warehouseId
					UNION ALL 
				    SELECT articleId, warehouseId, 0 AS auxQuantity, SUM(readyQuantity - deliveredQuantity) AS auxPending
				    FROM detailsByOrder INNER JOIN orders 
				    ON detailsByOrder.orderId = orders.id
				    WHERE orders.deleted = 0 AND detailsByOrder.deleted = 0 AND 
				          NOT orders.stateId IN ('ARM','CAN','FIN','NOTAUT','SUS','TOAUT')
				    GROUP BY detailsByOrder.articleId, orders.warehouseId
				) AS Summary 				
				GROUP BY articleId, warehouseId ";
		
		$query = $this->company_db->query($sql);			
		if ($query->num_rows() > 0){	
			$stock = $query->result_array();

			if (isset($stock) && count($stock) > 0) {
				for ($i=0; $i < count($stock); $i++) {	
					if ($stock[$i]['available'] > 0) {
						$this->updatePendingStock($stock[$i]['articleId'],$stock[$i]['warehouseId']);
					}
				}
			}
		}
	}
*/
	function getDetailedValuedStock($articleId=-1,$warehouseId=-1,$stockType=""){				
		$detailsStock = array('list'=>NULL, 
		                      'totalRecords'=>0);

		if ($articleId > 0 && $warehouseId > 0) {		
			$stock = $this->getStockArticle($articleId,$warehouseId,true);

			if ($stockType == 'AVAILABLE') {				
				$quantityField = "freeQuantity AS quantity";
				$totalQuantity = $stock['available'];
			} else {
				$quantityField = "quantity";
				$totalQuantity = $stock['stock'];
			}				

			$sqlMovements = "SELECT stockMovements.id, 
		                        'SM' AS registerType,
								stockMovements.date, 								          		                        		                        
		                        stockMovements.".$quantityField.",
		                        stockMovements.unitPrice,
		                        CONCAT('Mov. Stock ',IF(stockMovements.observation <> '',stockMovements.observation,'')) AS observation 
		                 FROM stockMovements 
		                 WHERE stockMovements.deleted = 0 
		                       AND stockMovements.freeQuantity > 0
		                       AND stockMovements.articleId = ".$articleId." 		                
		                       AND stockMovements.warehouseId = ".$warehouseId; 

		
			$sqlBills = "SELECT bills.id, 
							   'BILL' AS registerType,	
							   bills.billDate AS date, 	                           
	                           detailsByBill.".$quantityField.",
	                           detailsByBill.unitPrice,
	                           CONCAT('Factura Nro. ', bills.letter, ' ', bills.serie, ' ', bills.number) AS observation 
	                    FROM detailsByBill INNER JOIN bills ON detailsByBill.billId = bills.id                    
	                    WHERE detailsByBill.deleted = 0 AND bills.deleted = 0 
	                          AND detailsByBill.freeQuantity > 0
	                          AND detailsByBill.articleId = ".$articleId." 		                
		                      AND bills.warehouseId = ".$warehouseId;

		    $sql = "SELECT 
		               summary.*
		        FROM (".
		        $sqlMovements.
		        " UNION ALL ".				
		        $sqlBills.") AS summary 		        
				ORDER BY summary.date";						

			$movementsTotalQuantity = 0; 
			$auxDetailsStock = array();

			$query = $this->company_db->query($sql);
			$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
			if ($query->num_rows() > 0) {
				$auxDetailsStock = $query->result_array();
				
				for ($i=0; $i < count($auxDetailsStock); $i++) {
					$movementsTotalQuantity += (int)$auxDetailsStock[$i]['quantity'];
				}
			}	

			$lstDetailsStock = Array();
			if ($movementsTotalQuantity < (int)$totalQuantity) {
				$lstDetailsStock[count($lstDetailsStock)] = array('id'=>0,
			                                                      'registerType'=>'SG',
			                                                      'date'=>NULL,
			                                                      'quantity'=>((int)$totalQuantity - $movementsTotalQuantity),
			                                                      'unitPrice'=>0,
			                                                      'observation'=>'Stock General'
			                                                  );
			}
			for ($i=0; $i < count($auxDetailsStock); $i++) {
				$lstDetailsStock[count($lstDetailsStock)] = array('id'=>$auxDetailsStock[$i]['id'],
			                                                      'registerType'=>$auxDetailsStock[$i]['registerType'],
			                                                      'date'=>$auxDetailsStock[$i]['date'],
			                                                      'quantity'=>$auxDetailsStock[$i]['quantity'],
			                                                      'unitPrice'=>$auxDetailsStock[$i]['unitPrice'],
			                                                      'observation'=>$auxDetailsStock[$i]['observation']
			                                                  );
			}				

			$detailsStock = array('list'=>$lstDetailsStock, 
		                   		  'totalRecords'=>count($lstDetailsStock));		
		}
				
		return $detailsStock;
	}

	function getDetailedUnitPrice($articleId=-1,$warehouseId=-1){				
		$detailsUnitPrice = array('list'=>NULL, 
		                      'totalRecords'=>0);

		if ($articleId > 0 && $warehouseId > 0) {					

			$sqlMovements = "SELECT stockMovements.id, 		                        
								stockMovements.date, 								          		                        		                        
		                        (stockMovements.quantity - stockMovements.deliveredQuantity) AS quantity,
		                        stockMovements.unitPrice,
		                        CONCAT('Mov. Stock ',IF(stockMovements.observation <> '',stockMovements.observation,'')) AS observation 
		                 FROM stockMovements 		                 
		                 WHERE stockMovements.deleted = 0 
		                       AND stockMovements.input = 1 
		                       AND stockMovements.unitPrice > 0 
		                       AND (stockMovements.incompleteData IS NULL OR stockMovements.incompleteData = 0)
		                       AND (stockMovements.quantity - stockMovements.deliveredQuantity) > 0
		                       AND stockMovements.articleId = ".$articleId." 		                
		                       AND stockMovements.warehouseId = ".$warehouseId; 

		
			$sqlBills = "SELECT bills.id, 							   
							   bills.billDate AS date, 	                           
	                           (detailsByBill.quantity - detailsByBill.deliveredQuantity) AS quantity,
	                           detailsByBill.unitPrice,
	                           CONCAT('Factura Nro. ', bills.letter, ' ', bills.serie, ' ', bills.number) AS observation 
	                    FROM detailsByBill INNER JOIN bills ON detailsByBill.billId = bills.id	                    
	                    WHERE detailsByBill.deleted = 0 AND bills.deleted = 0 
	                    	  AND detailsByBill.unitPrice > 0 	                    	  
	                          AND (detailsByBill.quantity - detailsByBill.deliveredQuantity) > 0
	                          AND detailsByBill.articleId = ".$articleId." 		                
		                      AND bills.warehouseId = ".$warehouseId;

		    $sql = "SELECT 
		               summary.*
		        FROM (".
		        $sqlMovements.
		        " UNION ALL ".				
		        $sqlBills.") AS summary 		        
				ORDER BY summary.date";						

			$query = $this->company_db->query($sql);
			$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
			if ($query->num_rows() > 0) {				
				$detailsUnitPrice = array('list'=>$query->result_array(), 
		                                  'totalRecords'=>$queryTotal->row()->totalRecords);	
			}	
						
		}
				
		return $detailsUnitPrice;
	}

	function getDetailLastUnitPrice($articleId=-1){				
		$lastPriceDetail = null;

		if ($articleId > 0) {					
			$sql = "SELECT * 
					FROM (
	                     SELECT bills.billDate AS date,
	                            bills.date AS realDate,
	                            detailsByBill.unitPrice, 
	                            CONCAT('Factura Nro. ', bills.letter, ' ', bills.serie, ' ', bills.number) AS observation,
	                            'BILL' AS type 
						 FROM detailsByBill INNER JOIN bills ON detailsByBill.billId = bills.id                    
						 WHERE detailsByBill.deleted = 0 AND bills.deleted = 0 AND detailsByBill.unitPrice > 0 AND 
						       detailsByBill.articleId = ".$articleId." 					
					 	 UNION ALL 
					 	 SELECT stockMovements.date,
					 	        stockMovements.date AS realDate,
					 	        stockMovements.unitPrice, 
					 	        CONCAT('Mov. Stock ',IF(stockMovements.observation <> '',stockMovements.observation,'')) AS observation,
					 	        'SM' AS type 
						 FROM stockMovements 
						 WHERE stockMovements.deleted = 0 AND stockMovements.input = 1 AND stockMovements.unitPrice > 0 AND 
						       stockMovements.articleId = ".$articleId."						
				    ) AS summaryLastUnitPrice 
					ORDER BY YEAR(date) DESC, MONTH(date) DESC, DAY(date) DESC, realDate DESC
					LIMIT 1";		

			$query = $this->company_db->query($sql);
			$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
			if ($query->num_rows() > 0) {				
				$row = $query->row_array();

				$lastPriceDetail = array('date'=>$row['date'],
			                             'unitPrice'=>$row['unitPrice'],
			                             'observation'=>$row['observation']);
			}	
						
		}
				
		return $lastPriceDetail;
	}

	function updateLastUnitPrice($articleId=-1) {
		$sql = "SELECT id 		               
		        FROM articles
		        WHERE articles.deleted = 0";
		if ($articleId > 0) {
			$sql .= " AND id = ".$articleId;
		}		
			
		$query = $this->company_db->query($sql);		
		if ($query->num_rows() > 0){
			$articles = $query->result_array();
		                   
		    for ($i=0; $i < count($articles); $i++) {

		    	$dataLastUnitPrice = $this->getDetailLastUnitPrice($articles[$i]['id']);		   
		    	if (isset($dataLastUnitPrice)) {
		    		$this->company_db->set('lastUnitPrice',$dataLastUnitPrice['unitPrice']);	        				
					$this->company_db->where('id',$articles[$i]['id']);												
					$this->company_db->update('articles');
		    	}		    			    	
		    }
		}
	}
	
}

/* End of file Articles_model.php */
/* Location: ./application/models/Articles_model.php */