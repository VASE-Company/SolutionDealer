<?php
class Orders_model extends CI_Model{           

    private $company_db;    

    function __construct() {        
        parent::__construct();        

        $this->company_db = $this->load->database('default', TRUE); 
    }        	     	

	function getGeneralFilter($parameters=NULL){		
		$filter = "";

		if (isset($parameters)) {						
			if (isset($parameters['idFilter']) && $parameters['idFilter'] != "") {
				$filter .= " AND orders.id = ".(float)$parameters['idFilter'];								
			}	
			if (isset($parameters['numberFilter']) && $parameters['numberFilter'] != "" && (float)$parameters['numberFilter'] > 0) {
				$filter .= " AND (";
				$filter .= "orders.id = ".(float)$parameters['numberFilter'];								
				$filter .= " OR ";
				$filter .= "(SELECT id FROM deliveryNotes WHERE deliveryNotes.orderId = orders.id AND deliveryNotes.id = ".(float)$parameters['numberFilter'].")";								
				$filter .= " OR ";
				$filter .= "purchasesOrders.id = ".(float)$parameters['numberFilter'];								
				$filter .= ")";			
			}	
			if (isset($parameters['deliveryNoteIdFilter']) && $parameters['deliveryNoteIdFilter'] > 0) {
				$filter .= " AND ";				
				$filter .= "(SELECT id FROM deliveryNotes WHERE deliveryNotes.orderId = orders.id AND deliveryNotes.id = ".(float)$parameters['deliveryNoteIdFilter'].")";												
			}
			if (isset($parameters['companyIdsFilter']) && $parameters['companyIdsFilter'] != '' && $parameters['companyIdsFilter'] != 'all') {				
				$filter .= " AND branchOffices.companyId IN(".str_replace("|",",",$parameters['companyIdsFilter']).")";	
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
			if (isset($parameters['userIdFilter']) && $parameters['userIdFilter'] > 0) {
				$filter .= " AND orders.userId = ".$parameters['userIdFilter'];				
			}				
			if (isset($parameters['authorizingUserIdFilter']) && $parameters['authorizingUserIdFilter'] > 0) {
				$filter .= " AND orders.authorizingUserId = ".$parameters['authorizingUserIdFilter'];				
			}	
			if (isset($parameters['managerIdsFilter']) && $parameters['managerIdsFilter'] != '' && $parameters['managerIdsFilter'] != 'all') {				
				$filter .= " AND (";
				$filter .= "orders.managerId IN(".str_replace("|",",",$parameters['managerIdsFilter']).")";	
				$filter .= " OR ";
				$filter .= "orders.submanagerId IN(".str_replace("|",",",$parameters['managerIdsFilter']).")";	
				$filter .= ")";				
			}	
			if (isset($parameters['managerIdFilter']) && $parameters['managerIdFilter'] > 0) {
				$filter .= " AND (";
				$filter .= "orders.managerId = ".$parameters['managerIdFilter'];				
				$filter .= " OR ";
				$filter .= "orders.submanagerId = ".$parameters['managerIdFilter'];				
				$filter .= ")";				
			}				
			if (isset($parameters['generalUserIdFilter']) && $parameters['generalUserIdFilter'] > 0) {
				$filter .= " AND (";
				$filter .= "orders.userId = ".$parameters['generalUserIdFilter'];
				$filter .= " OR ";
				$filter .= "orders.authorizingUserId = ".$parameters['generalUserIdFilter'];
				$filter .= " OR ";
				$filter .= "orders.managerId = ".$parameters['generalUserIdFilter'];
				$filter .= " OR ";
				$filter .= "orders.submanagerId = ".$parameters['generalUserIdFilter'];
				$filter .= ")";				
			}		
			if (isset($parameters['priorityIdFilter']) && $parameters['priorityIdFilter'] > 0) {
				$filter .= " AND orders.priorityId = ".$parameters['priorityIdFilter'];				
			}		
			if (isset($parameters['familyIdsFilter']) && $parameters['familyIdsFilter'] != '' && $parameters['familyIdsFilter'] != 'all') {				
				
				$filter .= " AND EXISTS(SELECT detailsByOrder.id 
								      	FROM detailsByOrder 
								      	LEFT JOIN articles ON detailsByOrder.articleId = articles.id						        
										WHERE detailsByOrder.deleted = 0 
											  AND detailsByOrder.orderId = orders.id
											  AND articles.familyId IN(".str_replace("|",",",$parameters['familyIdsFilter']).")											      
											) ";
			}	
			if (isset($parameters['dateTypeFilter'])) {
				switch ($parameters['dateTypeFilter']) {
					default:
						$dateFiled = "date";
						break;
				}
			} else {
				$dateFiled = "date";
			}						
			if (isset($parameters['dateFromFilter']) && $parameters['dateFromFilter'] != "") {
				$filter .= " AND orders.".$dateFiled." >= '".$parameters['dateFromFilter']."' ";
			}
			if (isset($parameters['dateToFilter']) && $parameters['dateToFilter'] != "") {
				$filter .= " AND orders.".$dateFiled." <= '".$parameters['dateToFilter']."' ";
			}						
			if (isset($parameters['stateIdFilter']) && $parameters['stateIdFilter'] != "") {
				switch ($parameters['stateIdFilter']) {
					case "MAN";
						$stateId = "'PROG','DETUSR','BUDGET','AUTBUY','TOPAY'";
						break;
					
					case "NOTFIN";
						$stateId = "'READY','INDIST','PENSUP'";
						break;

					default:
						$stateId = "'".$parameters['stateIdFilter']."'";
						break;
				}				

				$filter .= " AND orders.stateId IN (".$stateId.")";				
			}
			if (isset($parameters['articleFilter']) && $parameters['articleFilter'] != '') {				
				$filter .= " AND EXISTS(SELECT detailsByOrder.id 
								      	FROM detailsByOrder 		
										WHERE detailsByOrder.deleted = 0 
											  AND detailsByOrder.orderId = orders.id
											  AND (detailsByOrder.code LIKE ".$this->company_db->escape("%".$parameters['articleFilter']."%")." OR 
											       detailsByOrder.description LIKE ".$this->company_db->escape("%".$parameters['articleFilter']."%").") 
											) ";
			}	
			if (isset($parameters['subjectFilter']) && $parameters['subjectFilter'] != '') {
				$filter .= " AND orders.subject LIKE ".$this->company_db->escape("%".$parameters['subjectFilter']."%");
			}		
		}		
		return $filter;
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
						$order .= "orders.id ".$auxOrder;
					break;	
					case 'date':
						$order .= "orders.date ".$auxOrder.", orders.id ".$auxOrder;
					break;
					case 'com':
						$order .= "companies.description ".$auxOrder.", orders.id ".$auxOrder;
					break;	
					case 'bo':
						$order .= "companies.description, branchOffices.description ".$auxOrder.", orders.id ".$auxOrder;
					break;	
					case 'sec':
						$order .= "companies.description, branchOffices.description, sectors.description ".$auxOrder.", orders.id ".$auxOrder;
					break;	
					case 'sta':
						$order .= "ordersStates.description ".$auxOrder;
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

	function getAccessFilter($userId=-1) {
		$this->load->model('users_model','users');	

		$accessFilter = "";
		
		if ($userId > 0) {												
			$sql = "SELECT *, branchOffices.companyId 
		        	FROM users LEFT JOIN branchOffices	
		        	ON users.branchOfficeId = branchOffices.id 
					WHERE users.id = ".$userId;								
			$query = $this->company_db->query($sql);			
			if ($query->num_rows() > 0){
				$user = $query->row_array();

				if ($user['id'] == $this->config->item('superUserId')) {
					$userRolesId = "-999";
				} else {
					$userRolesId = $this->users->getRolesIdByUser($userId);
				}				
				$userCompanyId = $user['companyId'];
				$userBranchOfficeId = $user['branchOfficeId'];
				$userSectorId = $user['sectorId'];
			} else {
				$userRolesId = "";
				$userCompanyId = 0;
				$userBranchOfficeId = 0;
				$userSectorId = 0;
			}			
		} else {
			$userId = $this->session->userdata('userId');
			$userRolesId = $this->session->userdata('userRolesId');
			$userCompanyId = $this->session->userdata('userCompanyId');
			$userBranchOfficeId = $this->session->userdata('userBranchOfficeId');
			$userSectorId = $this->session->userdata('userSectorId');
		}
		
		if ($userRolesId == "-999" || $this->my_application->hasPermission("Orders","FullAccess",$userRolesId)) {
			$accessFilter = "";
		} else {
			if ($this->my_application->hasPermission("Orders","FullAccessInsurance",$userRolesId)) {
				$accessFilter = "(orders.userId = ".$userId." OR orders.authorizingUserId = ".$userId." OR orders.managerId = ".$userId." OR orders.submanagerId = ".$userId.")";
				$accessFilter .= " OR orders.insuranceOrder = 1 ";
			} else {
				//Siempre tiene acceso a los propios		
				$accessFilter = "(orders.userId = ".$userId." OR orders.authorizingUserId = ".$userId." OR orders.managerId = ".$userId." OR orders.submanagerId = ".$userId.")";

				/*
				if ($this->my_application->hasPermission("Orders","IsAuthorizingUser",$userRolesId)) {			
					$accessFilter .= " OR (orders.stateId = 'TOAUT' AND 
					                       orders.authorizingUserId IS NULL AND 
					                       branchOffices.companyId = ".$userCompanyId." AND 
					                       orders.branchOfficeId = ".$userBranchOfficeId." AND 
					                       orders.sectorId = ".$userSectorId." 
					                      )";
				}
				*/	

				if ($this->my_application->hasPermission("Orders","CompanyAccess",$userRolesId) && $userCompanyId > 0) {			
					$accessFilter .= " OR branchOffices.companyId = ".$userCompanyId;				
				}

				if ($this->my_application->hasPermission("Orders","AssignManager",$userRolesId)) {			
					$accessFilter .= " OR (orders.stateId = 'AUT' AND 
					                       orders.managerId IS NULL
					                      ) OR orders.stateId IN ('PROG','DETUSR','BUDGET','AUTBUY','TOPAY') ";
				}			

			}

		}						

		return $accessFilter;
	}

	function getOrders($parameters=NULL){				
		$orders = array('list'=>NULL, 
		                'totalRecords'=>0);

		$filter = $this->getGeneralFilter($parameters);
		$order = $this->getGeneralOrder($parameters);
		$limit = $this->getGeneralLimit($parameters);
		
		if (!(isset($parameters) && isset($parameters['fullPermissions']) && $parameters['fullPermissions'] == true)) {												
			$accessFilter = $this->getAccessFilter();
			if ($accessFilter != "") $filter .= " AND  (".$accessFilter.")";								
		}

		if ($order == "") {
			$order .= "orders.date DESC, orders.id DESC ";
		}	
						
		$sql = "SELECT SQL_CALC_FOUND_ROWS orders.*,
                       companies.id AS companyId,
                       companies.description AS companyDescription,
                       branchOffices.description AS branchOfficeDescription,
                       sectors.description AS sectorDescription,                       
                       ordersStates.description	AS stateDescription,
                       ordersStates.usersToNotify AS usersToNotify,
                       ordersPriorities.description	AS priorityDescription,                       
                       ordersPriorities.color AS priorityColor,
                       purchasesOrders.id AS purchaseOrderId,
                       purchasesOrders.date AS purchaseOrderDate,
                       createUsers.username AS userMail,
                       createUsers.cellphone AS userCellphone  
		        FROM ((((((orders LEFT JOIN branchOffices ON orders.branchOfficeId = branchOffices.id)
		        LEFT JOIN companies ON branchOffices.companyId = companies.id)
		        LEFT JOIN sectors ON orders.sectorId = sectors.id)		        
		        LEFT JOIN ordersStates ON orders.stateId = ordersStates.id)
		        LEFT JOIN ordersPriorities ON orders.priorityId = ordersPriorities.id)		        
		        LEFT JOIN purchasesOrders ON purchasesOrders.orderId = orders.id)
		        LEFT JOIN users createUsers ON createUsers.id = orders.userId
				WHERE orders.deleted = 0 ".$filter." 
				ORDER BY ".$order.
				$limit;									
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0) {
			$auxOrders = $query->result_array();

			if (isset($parameters)) {
				$this->load->model('budgets_model','budgets');	

				if (isset($parameters['fullData']) && $parameters['fullData'] == true) {
					$this->load->model('companies_model','companies');						
				}

				for ($i=0; $i < count($auxOrders); $i++) {
					$auxOrders[$i]['purchaseOrderNumber'] = ((float)$auxOrders[$i]['purchaseOrderId'] <= 0?"":$auxOrders[$i]['purchaseOrderId']."-".$auxOrders[$i]['companyId']."-".date('my',strtotime($auxOrders[$i]['purchaseOrderDate'])));									

					if (isset($parameters['fullData']) && $parameters['fullData'] == true) {
						if (isset($parameters['idFilter']) && $parameters['idFilter'] != "") {
							$detailsParameters['quantityFree'] = true;	
						}
						$detailsParameters['orderIdFilter'] = $auxOrders[$i]['id'];
						$detailsByOrder = $this->getDetailsByOrder($detailsParameters);
						$auxOrders[$i]['details'] = $detailsByOrder['list'];

						$paymentSectorsParameters['orderIdFilter'] = $auxOrders[$i]['id'];						
						$paymentSectorsByOrder = $this->getPaymentSectorsByOrder($paymentSectorsParameters);
						$auxOrders[$i]['paymentSectors']  = $paymentSectorsByOrder['list'];										

						$observationsParameters['orderIdFilter'] = $auxOrders[$i]['id'];
						$observationsParameters['typeFilter'] = "U";
						$observationsByOrder = $this->getObservationsByOrder($observationsParameters);
						$auxOrders[$i]['userObservations']  = $observationsByOrder['list'];						

						$observationsParameters['orderIdFilter'] = $auxOrders[$i]['id'];			
						$observationsParameters['typeFilter'] = "I";			
						$observationsByOrder = $this->getObservationsByOrder($observationsParameters);						
						$auxOrders[$i]['internalObservations']  = $observationsByOrder['list'];	

						$attachmentsParameters['orderIdFilter'] = $auxOrders[$i]['id'];						
						$attachmentsByOrder = $this->getAttachmentsByOrder($attachmentsParameters);
						$auxOrders[$i]['attachments']  = $attachmentsByOrder['list'];		

						$historyParameters['orderIdFilter'] = $auxOrders[$i]['id'];						
						$historyByOrder = $this->getHistoryByOrder($historyParameters);
						$auxOrders[$i]['history']  = $historyByOrder['list'];														
					}					

					$deliveryNotesParameters['orderIdFilter'] = $auxOrders[$i]['id'];
					$deliveryNotesByOrder = $this->getDeliveryNotes($deliveryNotesParameters);
					$auxOrders[$i]['deliveryNotes'] = $deliveryNotesByOrder['list'];

					$budgetsParameters['orderIdFilter'] = $auxOrders[$i]['id'];
					$budgetsByOrder = $this->budgets->getBudgets($budgetsParameters);
					$auxOrders[$i]['budgets'] = $budgetsByOrder['list'];
				}				
			}

			$orders = array('list'=>$auxOrders,
		                    'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $orders;
	}

	function getDeliveryNotes($parameters=NULL){				
		$deliveryNotes = array('list'=>NULL, 
		                 	   'totalRecords'=>0);
		
		$filter = "";		
		$limit = "";		

		if (isset($parameters)) {
			if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
				$filter .= " AND deliveryNotes.id = ".$parameters['idFilter'];				
			}
			if (isset($parameters['orderIdFilter']) && $parameters['orderIdFilter'] > 0) {
				$filter .= " AND deliveryNotes.orderId = ".$parameters['orderIdFilter'];				
			}
			if (isset($parameters['page']) && $parameters['page'] > 0) {
				$fromRecord = ($parameters['page'] == 1?0:(($parameters['page']-1)*$this->config->item('recordsPerPage')));		
				$limit = "LIMIT ".$fromRecord.",".$this->config->item('recordsPerPage');
			}		
		}

		$sql = "SELECT SQL_CALC_FOUND_ROWS deliveryNotes.*,
		               CONCAT(users.lastName, ', ', users.firstName) AS userDescription,
		               branchOffices.companyId,
		               orders.managerId,
		               orders.stateId AS orderStateId                   
		        FROM ((deliveryNotes INNER JOIN users ON deliveryNotes.userId = users.id)
		        INNER JOIN orders ON deliveryNotes.orderId = orders.id)
		        INNER JOIN branchOffices ON orders.branchOfficeId = branchOffices.id  
				WHERE deliveryNotes.deleted = 0 AND orders.deleted = 0 ".$filter." 
				ORDER BY deliveryNotes.id ".
				$limit;						
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0) {		
			$auxDeliveryNotes = $query->result_array();

			for ($i=0; $i < count($auxDeliveryNotes); $i++) {
				$auxDeliveryNotes[$i]['number'] = $auxDeliveryNotes[$i]['id']."-".$auxDeliveryNotes[$i]['companyId']."-".date('my',strtotime($auxDeliveryNotes[$i]['date']));		

				if (isset($parameters['fullData']) && $parameters['fullData'] == true) {
					$sqlDetails = "SELECT detailsByDeliveryNotes.*, 		               
						               detailsByOrder.code, 
						               detailsByOrder.description, 
						               IF(ISNULL(families.id),'',families.description) AS familyDescription 		               
						        FROM ((detailsByDeliveryNotes INNER JOIN detailsByOrder ON detailsByDeliveryNotes.detailOrderId = detailsByOrder.id)
						        LEFT JOIN articles ON detailsByOrder.articleId = articles.id)
						        LEFT JOIN families ON articles.familyId = families.id 
								WHERE detailsByDeliveryNotes.deleted = 0 AND  detailsByOrder.deleted = 0 
								      AND detailsByDeliveryNotes.deliveryNoteId = ".$auxDeliveryNotes[$i]['id']." 
								ORDER BY detailsByDeliveryNotes.id";							
						
					$queryDetails = $this->company_db->query($sqlDetails);					
					if ($queryDetails->num_rows() > 0) {		
						$auxDeliveryNotes[$i]['details'] = $queryDetails->result_array();	
					} else {
						$auxDeliveryNotes[$i]['details'] = null;
					}
				}
			}
	
			$deliveryNotes = array('list'=>$auxDeliveryNotes, 
		                           'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $deliveryNotes;
	}

	function getDetailsByOrder($parameters=NULL){				
		$details = array('list'=>NULL, 
		                 'totalRecords'=>0);
		
		$filter = "";		
		$limit = "";								

		if (isset($parameters)) {
			if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
				$filter .= " AND detailsByOrder.id = ".$parameters['idFilter'];				
			}
			if (isset($parameters['orderIdFilter']) && $parameters['orderIdFilter'] > 0) {
				$filter .= " AND detailsByOrder.orderId = ".$parameters['orderIdFilter'];				
			}
			if (isset($parameters['affectsStockFilter'])) {
				$filter .= " AND detailsByOrder.affectsStock = ".$parameters['affectsStockFilter'];				
			}
			if (isset($parameters['excludedIdsFilter']) && trim($parameters['excludedIdsFilter']) != "") {
				$excludedIds = str_replace("_",",",$parameters['excludedIdsFilter']);
				$filter .= " AND NOT detailsByOrder.id IN (".$excludedIds.")";				
			}			
			if (isset($parameters['forDeliveyNote']) && $parameters['forDeliveyNote'] == true) {
				$filter .= " AND detailsByOrder.affectsStock = 1 AND (detailsByOrder.readyQuantity - detailsByOrder.deliveredQuantity) > 0 ";				
			}			
			if (isset($parameters['page']) && $parameters['page'] > 0) {
				$fromRecord = ($parameters['page'] == 1?0:(($parameters['page']-1)*$this->config->item('recordsPerPage')));		
				$limit = "LIMIT ".$fromRecord.",".$this->config->item('recordsPerPage');
			}	
		}
				
		$sql = "SELECT SQL_CALC_FOUND_ROWS detailsByOrder.*, 
					   detailsByOrder.readyQuantity - detailsByOrder.deliveredQuantity AS freeQuantity,							   
					   detailsByOrder.quantity - detailsByOrder.readyQuantity AS pendingQuantity,
					   orders.warehouseId, 	               
		               IF(ISNULL(families.id),'',families.description) AS familyDescription,
		               articlesLocation.corridor,
		               articlesLocation.shelf,
		               articles.usual               
		        FROM (((detailsByOrder LEFT JOIN articles ON detailsByOrder.articleId = articles.id)
		        INNER JOIN orders ON detailsByOrder.orderId = orders.id)
		        LEFT JOIN families ON articles.familyId = families.id) 
		        LEFT JOIN articlesLocation ON articlesLocation.articleId = detailsByOrder.articleId AND articlesLocation.warehouseId = orders.warehouseId AND articlesLocation.deleted = 0 
				WHERE detailsByOrder.deleted = 0 ".$filter." 
				ORDER BY detailsByOrder.id ".
				$limit;													
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0) {			

		    $details = array('list'=>$query->result_array(), 
		                     'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $details;
	}

	function getPaymentSectorsByOrder($parameters=NULL){				
		$paymentSectors = array('list'=>NULL, 
		                 		'totalRecords'=>0);
		
		$filter = "";		
		$limit = "";								

		if (isset($parameters)) {
			if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
				$filter .= " AND paymentSectorsByOrder.id = ".$parameters['idFilter'];				
			}
			if (isset($parameters['orderIdFilter']) && $parameters['orderIdFilter'] > 0) {
				$filter .= " AND paymentSectorsByOrder.orderId = ".$parameters['orderIdFilter'];				
			}		
			if (isset($parameters['page']) && $parameters['page'] > 0) {
				$fromRecord = ($parameters['page'] == 1?0:(($parameters['page']-1)*$this->config->item('recordsPerPage')));		
				$limit = "LIMIT ".$fromRecord.",".$this->config->item('recordsPerPage');
			}	
		}
				
		$sql = "SELECT SQL_CALC_FOUND_ROWS paymentSectorsByOrder.*, 					                 
		               IF(ISNULL(companies.id),'[Todas]',companies.description) AS companyDescription,
		               IF(ISNULL(branchOffices.id),'[Todas]',branchOffices.description) AS branchOfficeDescription,
		               IF(ISNULL(sectors.id),'[Todos]',sectors.description) AS sectorDescription		               
		        FROM ((paymentSectorsByOrder LEFT JOIN companies ON paymentSectorsByOrder.companyId = companies.id)
		        LEFT JOIN branchOffices ON paymentSectorsByOrder.branchOfficeId = branchOffices.id)
		        LEFT JOIN sectors ON paymentSectorsByOrder.sectorId = sectors.id		        
				WHERE paymentSectorsByOrder.deleted = 0 ".$filter." 
				ORDER BY paymentSectorsByOrder.id ".
				$limit;													
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0) {			

		    $paymentSectors = array('list'=>$query->result_array(), 
		                     		'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $paymentSectors;
	}

	function getObservationsByOrder($parameters=NULL){				
		$observations = array('list'=>NULL, 
		                      'totalRecords'=>0);;
		
		$filter = "";		
		$limit = "";		

		if (isset($parameters)) {
			if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
				$filter .= " AND observationsByOrder.id = ".$parameters['idFilter'];								
			}
			if (isset($parameters['orderIdFilter']) && $parameters['orderIdFilter'] > 0) {
				$filter .= " AND observationsByOrder.orderId = ".$parameters['orderIdFilter'];				
			}
			if (isset($parameters['typeFilter']) && $parameters['typeFilter'] != "") {
				$filter .= " AND observationsByOrder.type = '".$parameters['typeFilter']."'";				
			}
			if (isset($parameters['page']) && $parameters['page'] > 0) {
				$fromRecord = ($parameters['page'] == 1?0:(($parameters['page']-1)*$this->config->item('recordsPerPage')));		
				$limit = "LIMIT ".$fromRecord.",".$this->config->item('recordsPerPage');
			}		
		}
				
		$sql = "SELECT SQL_CALC_FOUND_ROWS observationsByOrder.*,
					   IF(ISNULL(observationsByOrder.userId),'".$this->config->item('applicationName')."',users.lastName) AS userLastName, 
					   IF(ISNULL(observationsByOrder.userId),'',users.firstName) AS userFirstName 
		        FROM observationsByOrder 
		        LEFT JOIN users ON observationsByOrder.userId = users.id
				WHERE observationsByOrder.deleted = 0 ".$filter." 
				ORDER BY observationsByOrder.id ".
				$limit;						
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0) {			
			$observations = array('list'=>$query->result_array(), 
		                      	  'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $observations;
	}

	function getAttachmentsByOrder($parameters=NULL){				
		$attachments = array('list'=>NULL, 
		                     'totalRecords'=>0);;
		
		$filter = "";		
		$limit = "";		

		if (isset($parameters)) {
			if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
				$filter .= " AND attachmentsByOrder.id = ".$parameters['idFilter'];								
			}
			if (isset($parameters['orderIdFilter']) && $parameters['orderIdFilter'] > 0) {
				$filter .= " AND attachmentsByOrder.orderId = ".$parameters['orderIdFilter'];				
			}			
			if (isset($parameters['page']) && $parameters['page'] > 0) {
				$fromRecord = ($parameters['page'] == 1?0:(($parameters['page']-1)*$this->config->item('recordsPerPage')));		
				$limit = "LIMIT ".$fromRecord.",".$this->config->item('recordsPerPage');
			}		
		}
				
		$sql = "SELECT SQL_CALC_FOUND_ROWS attachmentsByOrder.*,
					   users.lastName AS userLastName, 
					   users.firstName AS userFirstName 
		        FROM attachmentsByOrder 
		        LEFT JOIN users ON attachmentsByOrder.userId = users.id
				WHERE attachmentsByOrder.deleted = 0 ".$filter." 
				ORDER BY attachmentsByOrder.id ".
				$limit;						
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0) {			
			$attachments = array('list'=>$query->result_array(), 
		                      	 'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $attachments;
	}

	function getHistoryByOrder($parameters=NULL){				
		$history = array('list'=>NULL, 
		                 'totalRecords'=>0);;
		
		$filter = "";		
		$limit = "";		

		if (isset($parameters)) {
			if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
				$filter .= " AND historyByOrder.id = ".$parameters['idFilter'];								
			}
			if (isset($parameters['orderIdFilter']) && $parameters['orderIdFilter'] > 0) {
				$filter .= " AND historyByOrder.orderId = ".$parameters['orderIdFilter'];				
			}	
			if (isset($parameters['entityIdFilter']) && $parameters['entityIdFilter'] > 0) {
				$filter .= " AND historyByOrder.entityId = ".$parameters['entityIdFilter'];								
			}		
			if (isset($parameters['page']) && $parameters['page'] > 0) {
				$fromRecord = ($parameters['page'] == 1?0:(($parameters['page']-1)*$this->config->item('recordsPerPage')));		
				$limit = "LIMIT ".$fromRecord.",".$this->config->item('recordsPerPage');
			}		
		}
		if (isset($parameters) && isset($parameters['detailFilter']) && $parameters['detailFilter'] == TRUE) {
			$filter .= " AND historyByOrder.entity = 'detailsByOrder'";		
		} else {
			$filter .= " AND historyByOrder.entity <> 'detailsByOrder'";		
		}
				
		$sql = "SELECT SQL_CALC_FOUND_ROWS historyByOrder.*,
					   IF(ISNULL(historyByOrder.userId),'".$this->config->item('applicationName')."',users.lastName) AS userLastName, 
					   IF(ISNULL(historyByOrder.userId),'',users.firstName) AS userFirstName,
					   historyTypes.description AS typeDescription 
		        FROM (historyByOrder 
		        LEFT JOIN users ON historyByOrder.userId = users.id)
		        LEFT JOIN historyTypes ON historyByOrder.typeId = historyTypes.id
				WHERE historyByOrder.deleted = 0 ".$filter." 
				ORDER BY historyByOrder.date, historyByOrder.id ".
				$limit;									
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0) {		
			$auxHistory = $query->result_array();

			for ($i=0; $i < count($auxHistory); $i++) {
				$auxHistory[$i]['description'] = $auxHistory[$i]['typeDescription'];

				if (trim($auxHistory[$i]['entityId']) != "") {
					
					switch ($auxHistory[$i]['entity']) {
						case 'user':
							$sql = "SELECT lastName, firstName  
						        	FROM users  
									WHERE users.id = ".$auxHistory[$i]['entityId'];												
							
							$query = $this->company_db->query($sql);			
							if ($query->num_rows() > 0){
								$row = $query->row_array();

								$auxHistory[$i]['description'] .= ": <strong>".$row['lastName'].", ".$row['firstName']."</strong>";						
							}
							break;

						case 'state':						
							$sql = "SELECT * 
						        	FROM ordersStates
									WHERE ordersStates.id = '".$auxHistory[$i]['entityId']."'";	

							$query = $this->company_db->query($sql);			
							if ($query->num_rows() > 0){
								$row = $query->row_array();

								$auxHistory[$i]['description'] .= ": <strong>".$row['description']."</strong>";						
							}
							break;

						case 'detailsByOrder':
							$auxHistory[$i]['description'] .= ": <strong>".(int)$auxHistory[$i]['value']."</strong>";						
							break;
												
					}
				}

				$auxHistory[$i]['description'] .= ".";

				if (trim($auxHistory[$i]['observation']) != "") {
					$auxHistory[$i]['description'] .= " ".$auxHistory[$i]['observation'];
				}
			}

			$history = array('list'=>$auxHistory, 
		                     'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $history;
	}


	function hasPermissionOverOrder($id=0) {		
		if ($id > 0) {
			$parameters["idFilter"] = $id;			
			$orders = $this->getOrders($parameters);
			if ($orders['totalRecords'] == 1){
				return true;
			} else {
				return false;	
			}
		} else {
			return false;
		}
	}

	function deleteOrder($id=-1){
		if ($this->deleteOrderValid($id)) {				
			$this->company_db->set('deleted',1);	
			$this->company_db->set('deletedDate',getCurrentDate());	        
			$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         
			$this->company_db->where('id',$id);				
			$this->company_db->update('orders');						

			$error = $this->company_db->error();
			if ((int)$error['code'] != 0) {			
				return false;
			} else {
				$sql = "SELECT stateId
			        	FROM orders  
						WHERE orders.id = ".$id;									
				$query = $this->company_db->query($sql);			
				if ($query->num_rows() > 0){
					$row = $query->row_array();

					if ($row['stateId'] != "CAN") {
						$this->_reassignOrderStock($id);
					}
				}

				$this->company_db->set('deleted',1);	
				$this->company_db->set('deletedDate',getCurrentDate());	        
				$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         
				$this->company_db->where('entity','order');	
				$this->company_db->where('entityId',$id);	
				$this->company_db->where('deleted',0);			
				$this->company_db->update('notifications');					

				$this->company_db->set('deleted',1);	
				$this->company_db->set('deletedDate',getCurrentDate());	        
				$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         				
				$this->company_db->where('deleted',0);
				$this->company_db->where('orderId',$id);				
				$this->company_db->update('detailsByOrder');	

				$this->company_db->set('deleted',1);	
				$this->company_db->set('deletedDate',getCurrentDate());	        
				$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         				
				$this->company_db->where('deleted',0);
				$this->company_db->where('orderId',$id);				
				$this->company_db->update('deliveryNotes');
				
				$sql = "SELECT id
				        FROM deliveryNotes  
						WHERE deliveryNotes.orderId = ".$id;								
				$query = $this->company_db->query($sql);			
				if ($query->num_rows() > 0) {
					$deliveryNotesIds = $query->result_array();

					for ($i=0; $i < count($deliveryNotesIds); $i++) {
						$this->company_db->set('deleted',1);	
						$this->company_db->set('deletedDate',getCurrentDate());	        
						$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         				
						$this->company_db->where('deleted',0);	
						$this->company_db->where('deliveryNoteId',$deliveryNotesIds[$i]['id']);				
						$this->company_db->update('detailsByDeliveryNotes');
					}
				}

				return true;
			}
		} else {
			return false;
		}		
	}

	function deleteOrderValid($id=-1){
		if ($id > 0){	
			$allowDelete = true;		
												
			return $allowDelete;
		} else {
			return false;
		}		
	}

	function getEmptyOrder() {
	 	$order = array('id'=>-1,
						 'date'=>'',						 
						 'userId'=>$this->session->userdata('userId'),
						 'userMail'=>'',
						 'userCellphone'=>'',
						 'companyId'=>$this->session->userdata('userCompanyId'),
						 'branchOfficeId'=>$this->session->userdata('userBranchOfficeId'),
						 'sectorId'=>$this->session->userdata('userSectorId'),						 
						 'authorizingUserId'=>0,
						 'subject'=>'',
						 'priorityId'=>$this->config->item("priorityIdDefault"),	
						 'subpriorityId'=>0,					 
						 'maximumDays'=>0,					 
						 'maximumDate'=>'',						 
						 'total'=>0,						 
						 'stateId'=>$this->config->item("stateIdDefault"),						 
						 'deliveryNoteId'=>0,				 
						 'deliveryNoteDate'=>'',		
						 'deliveryNoteNumber'=>'',		
						 'purchaseOrderId'=>0,
						 'purchaseOrderDate'=>'',
						 'purchaseOrderNumber'=>'',
						 'managerId'=>0,
						 'submanagerId'=>0,
						 'userObservations'=>NULL,
						 'internalObservations'=>NULL, 						 
						 'details'=>NULL,
						 'attachments'=>NULL,
						 'history'=>NULL,
						 'paymentSectors'=>NULL  
				      	);

	 	return $order;
	}	
	
	function existsValue($value='', $id=-1, $field='') {
		$exists = false;
		
		if ($value != '' && $field != '') {
			$sql = "SELECT * 
					FROM orders 
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

	function setOrder($data){
		if (!empty($data)){
			$currentStateId = '';		
			$currentAuthorizingUserId = 0;	
			$currentManagerId = 0;
			$currentSubManagerId = 0;

			$generatePurchaseOrder = false;			

			$newOrder = ((float)$data['id'] <= 0);

			if ((float)$data['id'] > 0) {
				$sql = "SELECT stateId, authorizingUserId, managerId, submanagerId 				
			        	FROM orders  
						WHERE orders.deleted = 0 AND orders.id = ".$data['id'];					
				
				$query = $this->company_db->query($sql);			
				if ($query->num_rows() > 0){
					$row = $query->row_array();

					$currentStateId = $row['stateId'];
					$currentAuthorizingUserId = (float)$row['authorizingUserId'];	
					$currentManagerId = (float)$row['managerId'];
					$currentSubManagerId = (float)$row['submanagerId'];
				}				

				if ($currentStateId != $data['originalStateId']) {
					$response = array('orderId'=>-1,
				                      'message'=>'No se pudo grabar los datos, el pedido ha cambiado su estado desde que se cargó en pantalla');
					return $response;
				}

				if ($currentStateId != $data['stateId']) {
					if ($data['stateId'] == "FIN") {
						if (!$this->getOrderAllDelivered($data['id'],false)) {
							$response = array('orderId'=>-1,
						                      'message'=>'No se pudo grabar los datos, no se puede cambiar el estado a Finalizado si quedan artículos pendientes de entregar');
							return $response;
						}

						$sql = "SELECT deliveryNotes.id 
				        		FROM deliveryNotes  
								WHERE deliveryNotes.deleted = 0 AND deliveryNotes.orderId = ".$data['id']."
								      AND (deliveryNotes.receivedDate IS NULL OR deliveryNotes.receivedBy = '')";					
					
						$query = $this->company_db->query($sql);			
						if ($query->num_rows() > 0){
							$response = array('orderId'=>-1,
						                      'message'=>'No se pudo grabar los datos, existen remitos con datos de recepción incompletos');
							return $response;
						}
					}

					if ($data['stateId'] == "CAN" || $data['stateId'] == "SUS") {
						$sql = "SELECT deliveryNotes.id 
				        		FROM deliveryNotes  
								WHERE deliveryNotes.deleted = 0 AND deliveryNotes.orderId = ".$data['id'];					
					
						$query = $this->company_db->query($sql);			
						if ($query->num_rows() > 0){
							$response = array('orderId'=>-1,
						                      'message'=>'No se pudo grabar los datos, no se puede cambiar el estado a Anulado o Suspendido si ya se han generado remitos.');
							return $response;
						}
					}
				}				
			}				

			if ($newOrder) {

				if ($this->my_application->hasPermission("Orders","InsertByAny")) {		
					$branchOfficeId = (float)$data['branchOfficeId'];			
					$sectorId = (float)$data['sectorId'];			
				} else {
					$branchOfficeId = $this->session->userdata('userBranchOfficeId');
					$sectorId = $this->session->userdata('userSectorId');				
				}

				$sql = "SELECT companies.warehouseId  
			        	FROM branchOffices INNER JOIN companies  
						ON branchOffices.companyId = companies.id
						WHERE branchOffices.id = ".$branchOfficeId;					
				
				$query = $this->company_db->query($sql);			
				if ($query->num_rows() > 0) {
					$row = $query->row_array();

					$warehouseId = $row['warehouseId'];
				} else {
					$warehouseId = 0;
				}

				$this->company_db->set('date',$data['date']);				
				$this->company_db->set('userId',$this->session->userdata('userId'));	
				$this->company_db->set('branchOfficeId',$branchOfficeId);	
				$this->company_db->set('sectorId',$sectorId);	
				$this->company_db->set('warehouseId',$warehouseId);
				$this->company_db->set('authorizingUserId',NULL);	
				$this->company_db->set('managerId',NULL);	
				$this->company_db->set('submanagerId',NULL);	
				$this->company_db->set('maximumDate',NULL);					
				$this->company_db->set('total',0);					

				if ($data['stateId'] == "")
					$data['stateId'] = $this->config->item("stateIdDefault");				
			}
			$this->company_db->set('priorityId',$data['priorityId']);
			$this->company_db->set('subpriorityId',(float)$data['subpriorityId']);
			$this->company_db->set('maximumDays',(isset($data['maximumDays']) && $data['maximumDays'] != ""?$data['maximumDays']:NULL));			
			$this->company_db->set('stateId',$data['stateId']);
			$this->company_db->set('subject',$data['subject']);
			//if ($currentSubManagerId != $data['submanagerId']) $this->company_db->set('submanagerId',$data['submanagerId']);

			$changeState = ($data['stateId'] != $currentStateId);

			if ($changeState) {								
				switch ($data['stateId']) {
					case 'TOAUT':
						if ($currentAuthorizingUserId == 0 && $data['authorizingUserId'] > 0)
							$this->company_db->set('authorizingUserId',$data['authorizingUserId']);
					break;

					case 'VALID':
					case 'AUT':
					case 'NOTAUT':											
						if ($currentAuthorizingUserId == 0)
							$this->company_db->set('authorizingUserId',$this->session->userdata('userId'));

						if ($data['stateId'] == "AUT") {
							$generatePurchaseOrder = true;
														
							$this->company_db->set('maximumDate',getCurrentDate(false,(int)$data['maximumDays']));
						}
					break;

					case 'PROG':
						if ($currentManagerId == 0) {				
							if ((float)$data['managerId'] > 0) {
								$this->company_db->set('managerId',$data['managerId']);
							} else {
								$this->company_db->set('managerId',$this->session->userdata('userId'));
							}	
						} else {
							if ((float)$data['managerId'] > 0) {
								$this->company_db->set('managerId',$data['managerId']);
							}
						}	
						if ((float)$data['submanagerId'] > 0) {
							$this->company_db->set('submanagerId',$data['submanagerId']);
						} else {
							$this->company_db->set('submanagerId',NULL);
						}							
						break;
					
					case 'DETUSR':
					case 'BUDGET':
					case 'AUTBUY':
					case 'TOPAY':
					case 'PENSUP':
					case 'BUDGET':
					case 'INDIST':
					case 'READY':
					case 'FIN':
					case 'CAN':
					case 'SUS':
						if ($this->my_application->hasPermission("Orders","AssignManager")) {
							if ((float)$data['managerId'] > 0) {
								$this->company_db->set('managerId',$data['managerId']);
							} else {
								$this->company_db->set('managerId',$this->session->userdata('userId'));
							}								
						} 
						if ($this->my_application->hasPermission("Orders","AssignManager") || $data['managerId'] == $this->session->userdata('userId')) {
							if ((float)$data['submanagerId'] > 0) {
								$this->company_db->set('submanagerId',$data['submanagerId']);
							} else {
								$this->company_db->set('submanagerId',NULL);
							}															
						}
					break;
				}
			} else {
				switch ($data['stateId']) {
					case 'PROG':
					case 'DETUSR':
					case 'BUDGET':
					case 'AUTBUY':
					case 'TOPAY':
					case 'PENSUP':
					case 'BUDGET':
					case 'INDIST':
					case 'READY':
						if ($this->my_application->hasPermission("Orders","AssignManager")) {
							if ((float)$data['managerId'] > 0) {
								$this->company_db->set('managerId',$data['managerId']);
							}													
						}										
						if ($this->my_application->hasPermission("Orders","AssignManager") || $data['managerId'] == $this->session->userdata('userId')) {
							if ((float)$data['submanagerId'] > 0) {
								$this->company_db->set('submanagerId',$data['submanagerId']);
							} else {
								$this->company_db->set('submanagerId',NULL);
							}
						}															
					break;
				}
			}

			if (!$newOrder) {
				$this->company_db->where('id',$data['id']);				
				$this->company_db->update('orders');				
			} else {																
				$this->company_db->set('deleted',0);
				$this->company_db->set('deletedDate',NULL);
				$this->company_db->set('deletedUserId',NULL);	 					

				$this->company_db->insert('orders');
			
				$data['id'] = $this->company_db->insert_id();
			}			 
			
			$error = $this->company_db->error();

			if ((int)$error['code'] != 0) {							
				$response = array('orderId'=>-1);
				return $response;
			} else {																
				
				$this->_setOrderDetails($data['id'],$data['details'],$generatePurchaseOrder);				

				if (isset($data['paymentSectors']))
					$this->_setOrderPaymentSectors($data['id'],$data['paymentSectors']);

				if (isset($data['userObservation']))
					$this->setOrderObservation($data['id'],"U",$data['userObservation']);
				
				if (isset($data['internalObservation']))
					$this->setOrderObservation($data['id'],"I",$data['internalObservation']);								

				if (isset($data['attachments']))
					$this->_setOrderAttachments($data['id'],$data['attachments']);												

				$this->updateOrderTotal($data['id']);				

				if ($generatePurchaseOrder) $this->_generatePurchaseOrder($data['id']);							

				if ($newOrder) {
					$history = array('orderId'=>$data['id'],
				                     'typeId'=>'CRE');
					$this->setHistory($history);
				}				

				if ($changeState) {
					$history = array('orderId'=>$data['id'],
				                     'typeId'=>'STA',
				                 	 'entity'=>'state',
				                 	 'entityId'=>$data['stateId']);
					$this->setHistory($history);

					$this->notifyStateToUsers($data['id']);

					if ($data['stateId'] == "CAN") {						
						$this->_reassignOrderStock($data['id']);
					}
				}		
				
				if ((float)$data['managerId'] > 0 && $currentManagerId != (float)$data['managerId']) {									
					$history = array('orderId'=>$data['id'],
				                     'typeId'=>'COM',
				                 	 'entity'=>'user',
				                 	 'entityId'=>$data['managerId']);
					$this->setHistory($history);
				}	

				if ((float)$data['submanagerId'] > 0 && $currentSubManagerId != (float)$data['submanagerId']) {				
					$history = array('orderId'=>$data['id'],
				                     'typeId'=>'COA',
				                 	 'entity'=>'user',
				                 	 'entityId'=>$data['submanagerId']);
					$this->setHistory($history);
				}						

				$this->updateOrdersOfInsurance($data['id']);						
					 							
				$response = array('orderId'=>$data['id']);
				return $response;
			}						
		} else {
			$response = array('orderId'=>-1);
			return $response;			
		}
	}

	function _setOrderDetails($orderId=-1,$details=NULL,$validStock=false) {	
		if ($orderId > 0) { 

			$detailIds = array();
			if (isset($details) && count($details) > 0) {	

				if ($validStock) {
					$this->load->model('articles_model','articles');	
				}

				$sql = "SELECT orders.warehouseId
			        	FROM orders 
						WHERE orders.id = ".$orderId;					
				
				$query = $this->company_db->query($sql);			
				if ($query->num_rows() > 0) {
					$row = $query->row_array();

					$warehouseId = (float)$row['warehouseId'];					
				} else {
					$warehouseId = 0;					
				}

				for ($i=0; $i < count($details); $i++) {	
					$articleAffectsStock = false;

					$this->company_db->set('articleId',$details[$i]['articleId']);								
					$this->company_db->set('code',$details[$i]['code']);	
					$this->company_db->set('description',$details[$i]['description']);
					$this->company_db->set('quantity',$details[$i]['quantity']);	
					$this->company_db->set('unitPrice',$details[$i]['unitPrice']);												
					$details[$i]['total'] = $details[$i]['unitPrice'] * $details[$i]['quantity'];					
					$this->company_db->set('total',$details[$i]['total']);	
					
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
						} 																			
					}

					if ($validStock) {
						if ($articleAffectsStock) {
							$currentStock = $this->articles->getStockArticle($details[$i]['articleId'],$warehouseId,true);

							if ($currentStock['available'] > 0) {

								if ($currentStock['available'] >= $details[$i]['quantity']) {
									$details[$i]['readyQuantity'] = $details[$i]['quantity'];									
								} else {
									$details[$i]['readyQuantity']= $currentStock['available'];									
								}
								$this->company_db->set('readyQuantity',$details[$i]['readyQuantity']);

							} else {
								$this->company_db->set('readyQuantity',0);
								$details[$i]['readyQuantity'] = 0;
							}							
						} else {
							$this->company_db->set('readyQuantity',$details[$i]['quantity']);							
							$details[$i]['readyQuantity'] = 0;
						}																								
					}					

					if ((float)$details[$i]['id'] > 0) {
						$this->company_db->where('orderId',$orderId);	
						if ($validStock) $this->company_db->set('affectsStock',($articleAffectsStock?1:0));	
						$this->company_db->where('id',$details[$i]['id']);				
						$this->company_db->update('detailsByOrder');				
					} else {				
						if ($warehouseId <= 0) $this->company_db->set('readyQuantity',0);
						$this->company_db->set('deliveredQuantity',0);
						$this->company_db->set('orderId',$orderId);								
						$this->company_db->set('affectsStock',($articleAffectsStock?1:0));		
						$this->company_db->set('deleted',0);
						$this->company_db->set('deletedDate',NULL);
						$this->company_db->set('deletedUserId',NULL);				
						$this->company_db->insert('detailsByOrder');
						$details[$i]['id'] = $this->company_db->insert_id();
					}
					
					if ((float)$details[$i]['id'] > 0) {
						$detailIds[count($detailIds)] = $details[$i]['id'];						

						if (isset($details[$i]['readyQuantity']) && $details[$i]['readyQuantity'] > 0) {
							$this->_setStockAvailableToDetailOrden($orderId,$details[$i]['id'],$details[$i]['articleId'],$details[$i]['readyQuantity'],$warehouseId);
						}

						$this->updateTotalDetailByOrden($details[$i]['id']);
					}
				}															
			} 
			
			$this->company_db->set('deleted',1);	        
			$this->company_db->set('deletedDate',getCurrentDate());	        
			$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	 
			$this->company_db->where('orderId',$orderId);				
			$this->company_db->where('deleted',0);												
			if (count($detailIds) > 0) {
				$this->company_db->where_not_in('id', $detailIds);
			}
			$this->company_db->update('detailsByOrder');	

			$this->_updateQuantitiesDetailsByOrder($orderId);
		}
	}

	function _setOrderPaymentSectors($orderId=-1,$paymentSectors=NULL) {	
		if ($orderId > 0) { 

			$paymentSectorsIds = array();
			if (isset($paymentSectors) && count($paymentSectors) > 0) {									

				for ($i=0; $i < count($paymentSectors); $i++) {
					$this->company_db->set('companyId',$paymentSectors[$i]['companyId']);								
					$this->company_db->set('branchOfficeId',$paymentSectors[$i]['branchOfficeId']);								
					$this->company_db->set('sectorId',$paymentSectors[$i]['sectorId']);																		
					$this->company_db->set('percent',$paymentSectors[$i]['percent']);	

					if ((float)$paymentSectors[$i]['id'] > 0) {						
						$this->company_db->where('id',$paymentSectors[$i]['id']);				
						$this->company_db->update('paymentSectorsByOrder');				
					} else {																
						$this->company_db->set('orderId',$orderId);								
						$this->company_db->set('deleted',0);
						$this->company_db->set('deletedDate',NULL);
						$this->company_db->set('deletedUserId',NULL);				
						$this->company_db->insert('paymentSectorsByOrder');
						$paymentSectors[$i]['id'] = $this->company_db->insert_id();
					}

					if ((float)$paymentSectors[$i]['id'] > 0) {
						$paymentSectorsIds[count($paymentSectorsIds)] = $paymentSectors[$i]['id'];								
					}
				}															
			} 
			
			$this->company_db->set('deleted',1);	        
			$this->company_db->set('deletedDate',getCurrentDate());	        
			$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	 
			$this->company_db->where('orderId',$orderId);				
			$this->company_db->where('deleted',0);												
			if (count($paymentSectorsIds) > 0) {
				$this->company_db->where_not_in('id', $paymentSectorsIds);
			}
			$this->company_db->update('paymentSectorsByOrder');	
		}
	}	

	function _setStockAvailableToDetailOrden($orderId=0, $detailOrderId=0, $articleId=0, $quantity=0, $warehouseId=0) {
		if ($orderId > 0 && $detailOrderId > 0 && $articleId > 0 && $quantity > 0 && $warehouseId > 0) {
			$sql = "SELECT * FROM (
						SELECT  stockMovements.id,
						        0 AS mainId,
				                stockMovements.date,
				                stockMovements.freeQuantity,
				                stockMovements.unitPrice,
				                'sm' AS type                  
				        FROM stockMovements
						WHERE stockMovements.deleted = 0 AND stockMovements.freeQuantity > 0 AND 
						      stockMovements.articleId = ".$articleId." AND 
						      stockMovements.warehouseId = ".$warehouseId."
						UNION ALL
						SELECT  detailsByBill.id,
								bills.id AS mainId,
				                bills.date,
				                detailsByBill.freeQuantity,
				                detailsByBill.unitPrice,
				                'bill' AS type                  
				        FROM detailsByBill INNER JOIN bills
				        ON detailsByBill.billId = bills.id
						WHERE detailsByBill.deleted = 0 AND bills.deleted = 0 AND detailsByBill.freeQuantity > 0 AND
						      detailsByBill.articleId = ".$articleId." AND 
						      bills.warehouseId = ".$warehouseId." 
					) AS stock 
					ORDER BY date";											
			
			$query = $this->company_db->query($sql);			
			if ($query->num_rows() > 0) {		
				$stock = $query->result_array();

				for ($i=0; $i < count($stock) && $quantity > 0; $i++) {
					if ($stock[$i]['freeQuantity'] >= $quantity) {
						$auxQuantity = $quantity;																	
					} else {
						$auxQuantity = $stock[$i]['freeQuantity'];												
					}
					$quantity = $quantity - $auxQuantity;

					$data['entity'] = $stock[$i]['type'];
					$data['entityId'] = $stock[$i]['id'];
					$data['entityMainId'] = $stock[$i]['mainId'];
					$data['orderId'] = $orderId;
					$data['detailOrderId'] = $detailOrderId;
					$data['quantity'] = $auxQuantity;
					$data['unitPrice'] = $stock[$i]['unitPrice'];

					$this->setStockByOrden($data);				
				}
			}
		}
	}

	function setStockByOrden($data=null) {
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
				$this->company_db->set('orderId',$data['orderId']);	
				$this->company_db->set('detailOrderId',$data['detailOrderId']);	
				$this->company_db->set('quantity',$data['quantity']);								
				$this->company_db->set('unitPrice',$data['unitPrice']);					
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
				$this->company_db->insert('stocksByOrder');		
			}																	
		}
	}

	function setOrderObservation($orderId=-1,$type="",$observation="",$ofSystem=false) {	
		if ($orderId > 0 && $type != "" && trim($observation) != "") { 				
			if (!$ofSystem && $type == "I" && !$this->my_application->hasPermission("Orders","SeeInternalObservations")) return;
			

			$this->company_db->set('orderId',$orderId);								
			$this->company_db->set('date',getCurrentDate());	        
			$this->company_db->set('type',$type);								
			$this->company_db->set('observation',$observation);	
			$this->company_db->set('userId',($ofSystem?NULL:$this->session->userdata('userId')));	 
			$this->company_db->set('deleted',0);
			$this->company_db->set('deletedDate',NULL);
			$this->company_db->set('deletedUserId',NULL);				
			$this->company_db->insert('observationsByOrder');

			if ($type == "U" && !$ofSystem) {
				$usrs = $this->session->userdata('userId');

				$lstUsr = array();

				$sql = "SELECT userId, authorizingUserId, managerId 
			        	FROM orders  
						WHERE orders.deleted = 0 AND orders.id = ".$orderId;					
				
				$query = $this->company_db->query($sql);			
				if ($query->num_rows() > 0){
					$row = $query->row_array();

					if ((float)$row['userId'] > 0 && (float)$row['userId'] != $this->session->userdata('userId')) {
						$lstUsr[count($lstUsr)] = $row['userId'];
						$usrs .= ",".$row['userId'];
					}
					if ((float)$row['authorizingUserId'] > 0 && (float)$row['authorizingUserId'] != $this->session->userdata('userId')) {
						$lstUsr[count($lstUsr)] = $row['authorizingUserId'];
						$usrs .= ",".$row['authorizingUserId'];
					}
					if ((float)$row['managerId'] > 0 && (float)$row['managerId'] != $this->session->userdata('userId')) {
						$lstUsr[count($lstUsr)] = $row['managerId'];
						$usrs .= ",".$row['managerId'];
					}
				}

				$sql = "SELECT DISTINCT userId
				        FROM observationsByOrder  
						WHERE observationsByOrder.deleted = 0 AND observationsByOrder.orderId = ".$orderId." AND observationsByOrder.type = 'U' AND NOT observationsByOrder.userId IN(".$usrs.")";								
				$query = $this->company_db->query($sql);			
				if ($query->num_rows() > 0) {
					$lstHistoryUsr = $query->result_array();

					for ($i=0; $i < count($lstHistoryUsr); $i++) {
						$lstUsr[count($lstUsr)] = $lstHistoryUsr[$i]['userId'];
					}
				}

				$this->load->model('notifications_model','notifications');

				for ($i=0; $i < count($lstUsr); $i++) {				
					$notification = array('typeId'=>'NOO', 										  
										  'observation'=>'Pedido Nro. '.$orderId. " - Nueva Observación",
										  'entity'=>'order',
										  'entityId'=>$orderId,
										  'userId'=>$lstUsr[$i]);
					$this->notifications->setNotification($notification);
				}				
			}			
		}
	}

	function _setOrderAttachments($orderId=-1,$attachments=NULL) {			
		if ($orderId > 0 && $this->my_application->hasPermission("Orders","SeeAttachments")) { 
			$attachmentIds = array();
			if (isset($attachments) && count($attachments) > 0) {			
				for ($i=0; $i < count($attachments); $i++) {											
					if ((float)$attachments[$i]['id'] <= 0) {												
						$pathFrom = $this->config->item('files').'tmp/'.$attachments[$i]['internalFilename'];
						
						$pathTo = $this->config->item('files').'orders';
						checkCreateFolder($pathTo);		
						$pathTo .= "/".$orderId;
						checkCreateFolder($pathTo);
						$pathTo .= '/'.$attachments[$i]['internalFilename'];

						if (rename($pathFrom, $pathTo)) {
							$this->company_db->set('orderId',$orderId);								
							$this->company_db->set('date',getCurrentDate());	        
							$this->company_db->set('userId',$this->session->userdata('userId'));	 												
							$this->company_db->set('filename',$attachments[$i]['filename']);
							$this->company_db->set('internalFilename',$attachments[$i]['internalFilename']);
							$this->company_db->set('deleted',0);
							$this->company_db->set('deletedDate',NULL);
							$this->company_db->set('deletedUserId',NULL);				
							$this->company_db->insert('attachmentsByOrder');
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
			$this->company_db->where('orderId',$orderId);				
			$this->company_db->where('deleted',0);												
			if (count($attachmentIds) > 0) {
				$this->company_db->where_not_in('id', $attachmentIds);
			}
			$this->company_db->update('attachmentsByOrder');	
										
		}
	}		

	function updateOrderTotal($orderId=-1){
		if ($orderId > 0) {
			
			$sql = "SELECT SUM(total) AS detailTotal
		        	FROM detailsByOrder  
					WHERE detailsByOrder.deleted = 0 AND detailsByOrder.orderId = ".$orderId;					
			
			$query = $this->company_db->query($sql);			
			$total = 0;	
			if ($query->num_rows() > 0){
				$row = $query->row_array();		

				$total = $row['detailTotal'];								
			}

			$this->company_db->set('total',$total);	        				
			$this->company_db->where('id',$orderId);				
			$this->company_db->update('orders');
		}
	}

	function updateTotalDetailByOrden($detailOrderId=-1) {
		if ($detailOrderId > 0) {

			$sql = "SELECT affectsStock, readyQuantity
		        	FROM detailsByOrder  
					WHERE detailsByOrder.id = ".$detailOrderId;					
			
			$query = $this->company_db->query($sql);						
			if ($query->num_rows() > 0){
				$row = $query->row_array();		

				$affectsStock = $row['affectsStock'];								
				$readyQuantity = $row['readyQuantity'];	

				if ($affectsStock == 1) {
					$sql = "SELECT SUM(quantity) AS totalQuantity, SUM(quantity * unitPrice) AS totalPrice
				        	FROM stocksByOrder  
							WHERE stocksByOrder.deleted = 0 AND stocksByOrder.unitPrice > 0 AND
							      stocksByOrder.detailOrderId = ".$detailOrderId;					
					
					$query = $this->company_db->query($sql);			
					$unitPrice = 0;	
					if ($query->num_rows() > 0){
						$row = $query->row_array();		

						if ($row['totalQuantity'] > 0) {
							$unitPrice = round($row['totalPrice'] / $row['totalQuantity'],2);								
						}
					}					

					$total = $readyQuantity * $unitPrice;

					$this->company_db->set('unitPrice',$unitPrice);	 
					$this->company_db->set('total',$total);	        				
					$this->company_db->where('id',$detailOrderId);				
					$this->company_db->update('detailsByOrder');
				}
			}
		}
	}

	function getOrderAllDelivered($orderId=-1,$onlyPrinted=false){
		if ($orderId > 0) {
			if ($onlyPrinted) {
				$sqlDeliveryQuantity = "(SELECT SUM(detailsByDeliveryNotes.quantity) 
						                 FROM detailsByDeliveryNotes INNER JOIN deliveryNotes 
						                 ON detailsByDeliveryNotes.deliveryNoteId = deliveryNotes.id
						                 WHERE detailsByDeliveryNotes.deleted = 0 AND 
						                       detailsByDeliveryNotes.detailOrderId = detailsByOrder.id AND
						                       deliveryNotes.printed = 1 
						                )";
			} else {
				$sqlDeliveryQuantity = "(SELECT SUM(detailsByDeliveryNotes.quantity) 
						                 FROM detailsByDeliveryNotes 
						                 WHERE detailsByDeliveryNotes.deleted = 0 AND 
						                       detailsByDeliveryNotes.detailOrderId = detailsByOrder.id
						                )";
			}

			$sql = "SELECT detailsByOrder.quantity, 		               
			               (".$sqlDeliveryQuantity.")  AS deliveredQuantity 
			        FROM detailsByOrder 
					WHERE detailsByOrder.deleted = 0 AND detailsByOrder.affectsStock = 1 AND detailsByOrder.orderId = ".$orderId;
				
			$query = $this->company_db->query($sql);		
			if ($query->num_rows() > 0) {

				$allDelivered = true;
				$auxDetails = $query->result_array();			
				for ($i=0; $i < count($auxDetails); $i++) {
					if ((int)$auxDetails[$i]['deliveredQuantity'] < (int)$auxDetails[$i]['quantity']) {
						$allDelivered = false;
					}
				}
							
			} else {
				$allDelivered = true;
			}
		} else {
			$allDelivered = true;
		}

		return $allDelivered;
	}

	function setHistory($data){
		if (!empty($data)) {				

			$this->company_db->set('orderId',$data['orderId']);
			$this->company_db->set('date',getCurrentDate());	 		
			$this->company_db->set('typeId',$data['typeId']);
			$this->company_db->set('observation',(isset($data['observation'])?$data['observation']:""));
			$this->company_db->set('entity',(isset($data['entity'])?$data['entity']:""));
			$this->company_db->set('entityId',(isset($data['entityId'])?$data['entityId']:0));
			$this->company_db->set('value',(isset($data['value'])?$data['value']:0));										
			$this->company_db->set('userId',(isset($data['userId'])?$data['userId']:$this->session->userdata('userId')));										
			$this->company_db->set('deleted',0);
			$this->company_db->set('deletedDate',NULL);
			$this->company_db->set('deletedUserId',NULL);
				
			$this->company_db->insert('historyByOrder');					
			
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

	function getStates($parameters=NULL){				
		$states = array('list'=>NULL, 
		                'totalRecords'=>0);

		$filter = "";
		if (isset($parameters)) {
			if (isset($parameters['idFilter']) && $parameters['idFilter'] != "") {
				$filter .= " AND ordersStates.id = '".$parameters['idFilter']."'";				
			}
		}		
				
		$sql = "SELECT SQL_CALC_FOUND_ROWS * 
		        FROM ordersStates 		        
				WHERE ordersStates.deleted = 0 ".$filter." 
				ORDER BY ordersStates.step";									
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){		
			if (isset($parameters)) {				
				if (isset($parameters['userRolFilter']) && $parameters['userRolFilter'] != "" && $query->num_rows() == 1) {								
					$row = $query->row_array();						
					
					$ids = $row['id'];
					
					if (isset($parameters['alwaysStateFilter']) && $parameters['alwaysStateFilter'] != "") {
						$ids .= "|".$parameters['alwaysStateFilter'];
					}											
					if (trim($row[$parameters['userRolFilter'].'NextStates']) != "") {
						$ids .= "|".$row[$parameters['userRolFilter'].'NextStates'];
					}

					if ($ids != $row['id']) {
						$ids = "'".str_replace("|","','",$ids)."'";

						$sql = "SELECT SQL_CALC_FOUND_ROWS IF(ordersStates.id = '".$row['id']."',1,0) AS selected, ordersStates.* 
						        FROM ordersStates 		        
								WHERE ordersStates.deleted = 0 AND ordersStates.id IN (".$ids.")
								ORDER BY 1 DESC, ordersStates.step";													
															
						$query = $this->company_db->query($sql);
						$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
					}					
				}
			}	
			
			$states = array('list'=>$query->result_array(), 
		                    'totalRecords'=>$queryTotal->row()->totalRecords);
		}	

		return $states;
	}

	function getPriorities($parameters=NULL){				
		$priorities = array('list'=>NULL, 
		                    'totalRecords'=>0);
		$filter = "";
		if (isset($parameters)) {
			if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
				$filter .= " AND ordersPriorities.id = ".$parameters['idFilter'];				
			}
		}
					
		$sql = "SELECT SQL_CALC_FOUND_ROWS * 
		        FROM ordersPriorities 	        
				WHERE ordersPriorities.deleted = 0 ".$filter." 
				ORDER BY ordersPriorities.description";	
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){		
			$priorities = array('list'=>$query->result_array(), 
		                        'totalRecords'=>$queryTotal->row()->totalRecords);
		}	

		return $priorities;
	}

	function getSubpriorities($parameters=NULL){				
		$subpriorities = array('list'=>NULL, 
		                       'totalRecords'=>0);
		$filter = "";
		if (isset($parameters)) {
			if (isset($parameters['activeOrIdFilter']) && $parameters['activeOrIdFilter'] == true) {
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND (ordersSubpriorities.active = 1 OR ordersSubpriorities.id = ".$parameters['idFilter'].")";				
				} else {
					$filter .= " AND ordersSubpriorities.active = 1";				
				}				
			} else {			
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND ordersSubpriorities.id = ".$parameters['idFilter'];				
				}
			}
			if (isset($parameters['priorityIdFilter']) && $parameters['priorityIdFilter'] > 0) {
				$filter .= " AND ordersSubpriorities.priorityId = ".$parameters['priorityIdFilter'];				
			}
		}
					
		$sql = "SELECT SQL_CALC_FOUND_ROWS * 
		        FROM ordersSubpriorities 	        
				WHERE ordersSubpriorities.deleted = 0 ".$filter." 
				ORDER BY ordersSubpriorities.description";					
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){		
			$subpriorities = array('list'=>$query->result_array(), 
		                           'totalRecords'=>$queryTotal->row()->totalRecords);
		}	

		return $subpriorities;
	}

	function currentUserCanAutorizateOrder($orderId=-1) {
		$canAutorizate = false;

		if ($orderId > 0) {
			if ($this->my_application->hasPermission("Orders","IsAuthorizingUser")) {
				$sql = "SELECT orders.id  
			        	FROM orders LEFT JOIN branchOffices
			        	ON orders.branchOfficeId = branchOffices.id 
						WHERE orders.deleted = 0 AND orders.id = ".$orderId." AND
						      orders.stateId = 'TOAUT' AND 
		                      orders.authorizingUserId IS NULL AND 
		                      branchOffices.companyId = ".$this->session->userdata('userCompanyId')." AND 
		                      orders.branchOfficeId = ".$this->session->userdata('userBranchOfficeId')." AND 
		                      orders.sectorId = ".$this->session->userdata('userSectorId');

				$query = $this->company_db->query($sql);			
				if ($query->num_rows() > 0){
					$canAutorizate = true;
				}
			}
		}

		return $canAutorizate;
	}

	function _generatePurchaseOrder($orderId=-1) {
		if ($orderId > 0) {
			
			$sql = "SELECT *
		        	FROM purchasesOrders  
					WHERE purchasesOrders.orderId = ".$orderId;					
			
			$query = $this->company_db->query($sql);						
			if ($query->num_rows() <= 0){
				$this->company_db->set('orderId',$orderId);		
				$this->company_db->set('date',getCurrentDate());	 				

				$this->company_db->insert('purchasesOrders');						
			}
		}
	}

	function notifyStateToUsers($orderId=-1) {
		if ($orderId > 0) {			
			$parameters["idFilter"] = $orderId;			
			$orders = $this->getOrders($parameters);
			if ($orders['totalRecords'] == 1){
				$order = $orders['list'][0];
			
				if (trim($order['usersToNotify']) != "") {
					$userTypes = explode("|",trim($order['usersToNotify']));
					$lstUsers= array();
					$idxUser = -1;

					for ($i=0; $i < count($userTypes); $i++) {
						switch(strtoupper(trim($userTypes[$i]))) {
							case 'USR':
								if ((float)$order['userId'] > 0 && (float)$order['userId'] != $this->session->userdata('userId') && !in_array((float)$order['userId'],$lstUsers)) {
									$idxUser++;
									$lstUsers[$idxUser] = (float)$order['userId'];
								}
							break;

							case 'AUT':
								if ((float)$order['authorizingUserId'] > 0 && (float)$order['authorizingUserId'] != $this->session->userdata('userId') && !in_array((float)$order['authorizingUserId'],$lstUsers)) {
									$idxUser++;
									$lstUsers[$idxUser] = (float)$order['authorizingUserId'];
								}
							break;

							case 'MAN':
								if ((float)$order['managerId'] > 0) { 						
									if ((float)$order['managerId'] != $this->session->userdata('userId') && !in_array((float)$order['managerId'],$lstUsers)) {
										$idxUser++;
										$lstUsers[$idxUser] = (float)$order['managerId'];
									}	
								} else {									
									$managersParameters['typeFilter'] = 'generalManager';														
									$managers = $this->users->getUsers($managersParameters);							

									if ($managers['totalRecords'] > 0) {
										$managers = $managers['list'];				
										for ($j=0; $j < count($managers); $j++) {
											if ((float)$managers[$j]['id'] != $this->session->userdata('userId') && !in_array((float)$managers[$j]['id'],$lstUsers)) {
												$idxUser++;
												$lstUsers[$idxUser] = $managers[$j]['id'];
											}
										}
									}
								}
							break;
						}
					}

					if (count($lstUsers) > 0) {
						$this->load->model('notifications_model','notifications');

						$logo = $this->config->item('images')."logoOrder.jpg";			
						if (file_exists($logo)) {				
							$logo = base_url().$logo;				
						} else {
							$logo = "";
						}

						for ($i=0; $i < count($lstUsers); $i++) {
							$user = $this->_getUser($lstUsers[$i]);							

							if (isset($user) && trim($user['username']) != "") { 
								
								if ($order['stateId'] == 'READY') {
									$to = $user['username'];
									$subject = $this->config->item('applicationName').": Pedido Nro ".$order['id']." - ".$order['stateDescription'];					

									$idx = 0;							
									$lstMessage[$idx++] = "Estimado/a  ".outputFormat(ucwords(trim($user['firstName']." ".$user['lastName'])),false).",";						
									$lstMessage[$idx++] = "le informamos que el pedido nro. ".$order['id']." de la empresa '".$order['companyDescription']."' ya se encuentra en estado '".$order['stateDescription']."'.";																															
									$lstMessage[$idx++] = "Que tenga un buen día.";														
									$lstMessage[$idx++] = "";			
									$lstMessage[$idx++] = $this->config->item('applicationName');				
									$lstMessage[$idx++] = "(No responder a este email)";	
									if ($logo != "") {
										$lstMessage[$idx++] = '<img src="'.$logo.'" width="130px" border="0">';
										$lstMessage[$idx++] = "";	
									}
												
									$message = mailBodyFormat($lstMessage);										
																			   																		
									$this->my_application->sendMail($to,$subject,$message,null,null,null);	
								}										
								
								$notification = array('typeId'=>'STA', 
													  'observation'=>'Pedido. Nro. '.$order['id'].' - '.$order['stateDescription'],
													  'entity'=>'order',
													  'entityId'=>$order['id'],
													  'userId'=>$user['id']);
								if ($this->notifications->setNotification($notification)) {
									$notifyOK = true;
								}
							}	
						}					
					} 
				}			
			}					
		}
	}

	function _getUser($userId=-1) {
		$user = null;

		if ($userId > 0) {
			$sql = "SELECT *  
		        	FROM users   
					WHERE users.deleted = 0 AND users.id = ".$userId;					
			
			$query = $this->company_db->query($sql);			
			if ($query->num_rows() == 1){
				$user = $query->row_array();				
			}
		}

		return $user;
	}

	function testMail($mail="") {
		if (trim($mail) != "") {
			$to = $mail;
			$subject = $this->config->item('applicationName').": Mail de Prueba";	

			$logo = $this->config->item('images')."logoOrder.jpg";
			
			if (file_exists($logo)) {				
				$logo = base_url().$logo;				
			} else {
				$logo = "";
			}

			$idx = 0;							
			$lstMessage[$idx++] = "Este es un email de prueba";
			$lstMessage[$idx++] = "Que tenga un buen día.";														
			$lstMessage[$idx++] = "";			
			$lstMessage[$idx++] = $this->config->item('applicationName');				
			$lstMessage[$idx++] = "(No responder a este email)";	
			$lstMessage[$idx++] = "";
			if ($logo != "") {
				$lstMessage[$idx++] = '<img src="'.$logo.'" width="130px" border="0">';
				$lstMessage[$idx++] = "";	
			}
						
			$message = mailBodyFormat($lstMessage);										
													   																		
			$this->my_application->sendMail($to,$subject,$message,null,null,null);
		}
	}

	function getOrdersWithDetails($parameters=NULL){				
		$orders = array('list'=>NULL, 
		                'totalRecords'=>0);

		$filter = $this->getGeneralFilter($parameters);
		$order = $this->getGeneralOrder($parameters);
		$limit = $this->getGeneralLimit($parameters);
		
		if (!(isset($parameters) && isset($parameters['fullPermissions']) && $parameters['fullPermissions'] == true)) {												
			$accessFilter = $this->getAccessFilter();
			if ($accessFilter != "") $filter .= " AND  (".$accessFilter.")";								
		}

		if ($order == "") {
			$order .= "orders.date DESC, orders.id DESC ";
		}	
						
		$sql = "SELECT SQL_CALC_FOUND_ROWS orders.*,
					   (SELECT historyByOrder.date FROM historyByOrder WHERE historyByOrder.orderId = orders.id AND historyByOrder.typeId = 'STA' AND historyByOrder.entityId IN ('AUT','NOTAUT') AND historyByOrder.deleted = 0 ORDER by historyByOrder.date DESC LIMIT 1) AS validatedDate,	
					   (SELECT historyByOrder.date FROM historyByOrder WHERE historyByOrder.orderId = orders.id AND historyByOrder.typeId = 'STA' AND historyByOrder.deleted = 0 ORDER by historyByOrder.date DESC LIMIT 1) AS stateDate,	
                       companies.id AS companyId,
                       companies.description AS companyDescription,
                       branchOffices.description AS branchOfficeDescription,
                       sectors.description AS sectorDescription,
                       ordersStates.description	AS stateDescription,                       
                       ordersPriorities.description	AS priorityDescription,                                                                                          
                       purchasesOrders.id AS purchaseOrderId,                       
                       createUsers.lastName AS userLastName,
                       createUsers.firstName AS userFirstName,
                       createUsers.username AS userMail,                       
                       authorizingUsers.lastName AS authorizingUserLastName,
                       authorizingUsers.firstName AS authorizingUserFirstName,
                       managerUsers.lastName AS managerLastName,
                       managerUsers.firstName AS managerFirstName,
                       detailsByOrder.code AS articleCode,
                       detailsByOrder.description AS articleDescription,
                       detailsByOrder.quantity AS articleQuantity,
                       detailsByOrder.unitPrice AS articleUnitPrice,
                       detailsByOrder.total AS articleTotal,
                       families.description AS articleFamily
		        FROM (((((((((((orders LEFT JOIN branchOffices ON orders.branchOfficeId = branchOffices.id)
		        LEFT JOIN companies ON branchOffices.companyId = companies.id)
		        LEFT JOIN sectors ON orders.sectorId = sectors.id)
		        LEFT JOIN ordersStates ON orders.stateId = ordersStates.id)
		        LEFT JOIN ordersPriorities ON orders.priorityId = ordersPriorities.id)		        
		        LEFT JOIN purchasesOrders ON purchasesOrders.orderId = orders.id)
		        LEFT JOIN users createUsers ON createUsers.id = orders.userId)
		        LEFT JOIN users authorizingUsers ON authorizingUsers.id = orders.authorizingUserId)		        
		        LEFT JOIN users managerUsers ON managerUsers.id = orders.managerId)		        
		        LEFT JOIN detailsByOrder ON detailsByOrder.orderId = orders.id AND detailsByOrder.deleted = 0)
		        LEFT JOIN articles ON articles.id = detailsByOrder.articleId)
		        LEFT JOIN families ON families.id = articles.familyId		        		        
				WHERE orders.deleted = 0 ".$filter." 
				ORDER BY ".$order.
				$limit;		
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0) {
			$orders = array('list'=>$query->result_array(),
		                    'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $orders;
	}

	function getOrdersForFullExport($parameters=NULL){				
		$orders = array('list'=>NULL, 
						'fields'=>NULL, 
		                'totalRecords'=>0);

			
		$filter = $this->getGeneralFilter($parameters);
		$order = $this->getGeneralOrder($parameters);
		$limit = $this->getGeneralLimit($parameters);
		
		if (!(isset($parameters) && isset($parameters['fullPermissions']) && $parameters['fullPermissions'] == true)) {												
			$accessFilter = $this->getAccessFilter();
			if ($accessFilter != "") $filter .= " AND  (".$accessFilter.")";								
		}

		if ($order == "") {
			$order .= "orders.date DESC, orders.id DESC ";
		}	
				
		$sql = "SELECT SQL_CALC_FOUND_ROWS 
						orders.id AS 'Nº Pedido',
						DATE_FORMAT(orders.date, '%d/%m/%Y') AS 'Fecha',
						companies.description AS 'Empresa',
						branchOffices.description AS 'Sucursal',
						sectors.description AS 'Sector',
						ordersPriorities.description AS 'Plazo', 
						DATE_FORMAT(orders.maximumDate, '%d/%m/%Y') AS 'Fecha Plazo',
						IF(NOT createUsers.lastName IS NULL AND createUsers.lastName <> '',
						   CONCAT(createUsers.lastName,', ',createUsers.firstName),'') AS 'Solicitado Por',
						createUsers.username AS 'Email', 
						IF(NOT authorizingUsers.lastName IS NULL AND authorizingUsers.lastName <> '',
						   CONCAT(authorizingUsers.lastName,', ',authorizingUsers.firstName),'') AS 'Validado Por',						   
						(SELECT DATE_FORMAT(historyByOrder.date, '%d/%m/%Y') FROM historyByOrder WHERE historyByOrder.orderId = orders.id AND historyByOrder.typeId = 'STA' AND historyByOrder.entityId IN ('AUT','NOTAUT') AND historyByOrder.deleted = 0 ORDER by historyByOrder.date DESC LIMIT 1) AS 'Fecha Validado',	
						IF(NOT managerUsers.lastName IS NULL AND managerUsers.lastName <> '',
						   CONCAT(managerUsers.lastName,', ',managerUsers.firstName),'') AS 'Gestionado Por',
						IF(NOT submanagerUsers.lastName IS NULL AND submanagerUsers.lastName <> '',
						   CONCAT(submanagerUsers.lastName,', ',submanagerUsers.firstName),'') AS 'Asistente de Gestión',
						ordersStates.description AS 'Estado',  
						(SELECT DATE_FORMAT(historyByOrder.date, '%d/%m/%Y')  FROM historyByOrder WHERE historyByOrder.orderId = orders.id AND historyByOrder.typeId = 'STA' AND historyByOrder.deleted = 0 ORDER by historyByOrder.date DESC LIMIT 1) AS 'Fecha Estado',	
					    IF(purchasesOrders.id IS NULL OR purchasesOrders.id <= 0,'',purchasesOrders.id) AS 'Nº Orden', 
						detailsByOrder.code AS 'Código',
                        detailsByOrder.description AS 'Descripción',
                        families.description AS 'Rubro',
                        detailsByOrder.quantity AS 'Cantidad',
                        detailsByOrder.unitPrice AS 'Costo S/IVA',
                        detailsByOrder.total AS 'Total'                        
		        FROM ((((((((((((orders LEFT JOIN branchOffices ON orders.branchOfficeId = branchOffices.id)
		        LEFT JOIN companies ON branchOffices.companyId = companies.id)
		        LEFT JOIN sectors ON orders.sectorId = sectors.id)
		        LEFT JOIN ordersStates ON orders.stateId = ordersStates.id)
		        LEFT JOIN ordersPriorities ON orders.priorityId = ordersPriorities.id)		        
		        LEFT JOIN purchasesOrders ON purchasesOrders.orderId = orders.id)
		        LEFT JOIN users createUsers ON createUsers.id = orders.userId)
		        LEFT JOIN users authorizingUsers ON authorizingUsers.id = orders.authorizingUserId)		        
		        LEFT JOIN users managerUsers ON managerUsers.id = orders.managerId)		        
		        LEFT JOIN users submanagerUsers ON submanagerUsers.id = orders.submanagerId)		        
		        LEFT JOIN detailsByOrder ON detailsByOrder.orderId = orders.id AND detailsByOrder.deleted = 0)
		        LEFT JOIN articles ON articles.id = detailsByOrder.articleId)
		        LEFT JOIN families ON families.id = articles.familyId		        		        
				WHERE orders.deleted = 0 ".$filter." 
				ORDER BY ".$order.
				$limit;						
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0) {
			$orders = array('list'=>$query->result_array(),
							'fields'=>$query->list_fields(),
		                    'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $orders;
	}

	function setPrintedDeliveryNote($deliveryNoteId=-1){
		if ($deliveryNoteId > 0) {
			$this->company_db->set('printed',1);	        
			
			$this->company_db->where('id',$deliveryNoteId);				
			$this->company_db->update('deliveryNotes');	 	
		}
	}
	
	function setDeliveryNote($orderId=-1,$details=NULL) {	
		$hasError = false;
		if ($orderId <= 0) { 
			$hasError = true;
		} else {
			$sql = "SELECT warehouseId, stateId 
		        	FROM orders
					WHERE deleted = 0 AND id = ".$orderId;												
			
			$query = $this->company_db->query($sql);			
			if ($query->num_rows() > 0){
				$row = $query->row_array();
				
				$warehouseId = $row['warehouseId'];
				$stateId = $row['stateId'];

				if ($warehouseId <= 0) $hasError = true;
			} else {
				$hasError = true;
			}
		}

		if (!(isset($details) && count($details) > 0)) $hasError = true; 					

		if ($hasError) {
			$response = array('code'=>'ERROR',
						      'message'=>'No se pudo grabar los datos.');

			return $response;
		}
		 
		$this->load->model('articles_model','articles');	

		$this->_updateQuantitiesDetailsByOrder($orderId);

		for ($i=0; $i < count($details); $i++) {				
			$freeQuantity =0;
			$sql = "SELECT (readyQuantity - deliveredQuantity) AS pendingQuantity 
			        FROM detailsByOrder 
			        WHERE detailsByOrder.deleted = 0 AND 
			              detailsByOrder.id = ".$details[$i]['id'];
			$query = $this->company_db->query($sql);			
			if ($query->num_rows() > 0){
				$row = $query->row_array();
									
				if ($details[$i]['quantity'] > $row['pendingQuantity']) {
					$response = array('code'=>'ERROR',
							      	  'message'=>'No se pudo grabar los datos, alguno de los items supera la cantidad pendiente a entregar.');

					return $response;
				}
			}												
		}

		$this->company_db->set('orderId',$orderId);								
		$this->company_db->set('date',getCurrentDate());	        
		$this->company_db->set('userId',$this->session->userdata('userId'));	         
		$this->company_db->set('printed',0);
		$this->company_db->set('receivedDate',NULL);
		$this->company_db->set('receivedBy',"");
		$this->company_db->set('receivedObservation',"");
		$this->company_db->set('deleted',0);
		$this->company_db->set('deletedDate',NULL);
		$this->company_db->set('deletedUserId',NULL);				
		$this->company_db->insert('deliveryNotes');
		
		$deliveryNoteId = $this->company_db->insert_id();

		$error = $this->company_db->error();
		if ((int)$error['code'] != 0) {							
			$response = array('code'=>'ERROR',
						      'message'=>'No se pudo grabar los datos.');

			return $response;	
		}


		for ($i=0; $i < count($details); $i++) {
			$this->company_db->set('deliveryNoteId',$deliveryNoteId);	
			$this->company_db->set('detailOrderId',$details[$i]['id']);	
			$this->company_db->set('articleId',$details[$i]['articleId']);	
			$this->company_db->set('warehouseId',$warehouseId);	
			$this->company_db->set('quantity',$details[$i]['quantity']);				
			$this->company_db->set('deleted',0);
			$this->company_db->set('deletedDate',NULL);
			$this->company_db->set('deletedUserId',NULL);

			$this->company_db->insert('detailsByDeliveryNotes');	

			$this->_updateQuantitiesDetailsByOrder($orderId,$details[$i]['id']);
		}		

		$this->updateDeliveredQuantity($deliveryNoteId);

		$response = array('code'=>'OK');

		return $response;
	}
	
	function updateRecivedDataDeliveryNote($data){
		if (!empty($data)){				
			
			$this->company_db->set('receivedDate',$data['receivedDate']);			
			$this->company_db->set('receivedBy',$data['receivedBy']);
			$this->company_db->set('receivedObservation',$data['receivedObservation']);
			
			if ((float)$data['id'] > 0) {
				$this->company_db->where('id',$data['id']);				
				$this->company_db->update('deliveryNotes');							
			} else {
				return false;
			}
			
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

	function _updateQuantitiesDetailsByOrder($orderId=-1,$itemId=-1){
		if ($orderId > 0) {
			
			$sqlDeliveryQuantity = "(SELECT SUM(detailsByDeliveryNotes.quantity) 
					                 FROM detailsByDeliveryNotes 
					                 WHERE detailsByDeliveryNotes.deleted = 0 AND 
					                       detailsByDeliveryNotes.detailOrderId = detailsByOrder.id
					                )";

			$sql = "UPDATE detailsByOrder 
			        SET detailsByOrder.deliveredQuantity = IF(detailsByOrder.affectsStock = 1,".$sqlDeliveryQuantity.",detailsByOrder.quantity) 
			        WHERE detailsByOrder.deleted = 0 AND 
			              detailsByOrder.orderId = ".$orderId;
			if ($itemId	> 0) {
				$sql .= " AND detailsByOrder.id = ".$itemId;
			}
			
			$query = $this->company_db->query($sql);
		}
	}

	function getDataByOrderDetail($detailOrderId=-1){				
		$data = array('list'=>NULL, 
		              'totalRecords'=>0);
		
		if ($detailOrderId > 0) {
			
			$sql = "SELECT SQL_CALC_FOUND_ROWS stocksByOrder.*,
			               stockMovements.date AS stockMovementDate, 
			               bills.date AS billDate,
			               bills.letter AS billLetter,
			               bills.serie AS billSerie,
			               bills.number AS billNumber 
			        FROM (stocksByOrder LEFT JOIN bills
			        ON stocksByOrder.billId = bills.id)
			        LEFT JOIN stockMovements 
			        ON stocksByOrder.stockMovementId = stockMovements.id  	        
					WHERE stocksByOrder.deleted = 0 AND 
					      stocksByOrder.detailOrderId = ".$detailOrderId." 
					ORDER BY stocksByOrder.id";	
				
			$query = $this->company_db->query($sql);
			$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
			if ($query->num_rows() > 0){	
				$auxData = $query->result_array();

				for ($i=0; $i < count($auxData); $i++) {
					$auxData[$i]['description'] = '';
					if ($auxData[$i]['stockMovementId'] > 0) {
						$auxData[$i]['description'] = "MOV. STOCK. ingresado el ".dateFormat($auxData[$i]['stockMovementDate'],false);
					}
					if ($auxData[$i]['billId'] > 0) {
						$auxData[$i]['description'] = "FC ";
						if (trim($auxData[$i]['billLetter']) != "") $auxData[$i]['description'] .= $auxData[$i]['billLetter']." ";
						if (trim($auxData[$i]['billSerie']) != "") $auxData[$i]['description'] .= $auxData[$i]['billSerie']." ";
						if (trim($auxData[$i]['billNumber']) != "") $auxData[$i]['description'] .= $auxData[$i]['billNumber']." ";
						$auxData[$i]['description'] .= "ingresada el ".dateFormat($auxData[$i]['billDate'],false);						
					}
				}

				$data = array('list'=>$auxData, 
			                  'totalRecords'=>$queryTotal->row()->totalRecords);
			}
		}	

		return $data;
	}

	function _reassignOrderStock($orderId=-1) {		
		if ($orderId > 0) {
			$sql = "SELECT warehouseId 
		        	FROM orders 
					WHERE orders.id = ".$orderId." AND orders.stateId = 'CAN'";					
			
			$query = $this->company_db->query($sql);			
			if ($query->num_rows() > 0) {
				$this->load->model('articles_model','articles');	

				$order = $query->row_array();

				$sql = "SELECT detailsByOrder.id, detailsByOrder.articleId
				        FROM detailsByOrder 
						WHERE detailsByOrder.deleted = 0 
						      AND detailsByOrder.orderId = ".$orderId." 
						      AND detailsByOrder.affectsStock = 1 
						      AND (detailsByOrder.readyQuantity - detailsByOrder.deliveredQuantity) > 0 
						ORDER BY detailsByOrder.id";										
				
				$query = $this->company_db->query($sql);				
				if ($query->num_rows() > 0) {
					$details = $query->result_array();

					for ($i=0; $i < count($details); $i++) {

						$this->deleteStockByOrdenDetail($details[$i]['id']);					

						$this->articles->updatePendingStock($details[$i]['articleId'],$order['warehouseId']);
					}
				}				

				$this->_updateQuantitiesDetailsByOrder($orderId);
			}
		}		
	}

	function deleteStockByOrdenDetail($detailOrderId=-1) {
		if ($detailOrderId > 0) {			
			$sql = "SELECT * 
			        FROM stocksByOrder 
					WHERE stocksByOrder.deleted = 0 
					      AND stocksByOrder.detailOrderId = ".$detailOrderId." 
					ORDER BY stocksByOrder.id";								
			
			$query = $this->company_db->query($sql);				
			if ($query->num_rows() > 0) {
				$stocks = $query->result_array();
				
				for ($i=0; $i < count($stocks); $i++) {					

					$table = "";
					$entityId = 0;
					if ((float)$stocks[$i]['stockMovementId'] > 0) {
						$table = "stockMovements";
						$entityId = (float)$stocks[$i]['stockMovementId'];
					} else {
						if ((float)$stocks[$i]['detailBillId'] > 0) {
							$table = "detailsByBill";
							$entityId = (float)$stocks[$i]['detailBillId'];
						}
					}

					if ($table != "") {
						$sql = "UPDATE ".$table." 			                
				                SET freeQuantity = freeQuantity + ".$stocks[$i]['quantity'].",
				                    deliveredQuantity = deliveredQuantity - ".$stocks[$i]['deliveredQuantity']."   
				                WHERE id = ".$entityId;		
						$query = $this->company_db->query($sql);											
					}							

					$this->company_db->set('deleted',1);	
					$this->company_db->set('deletedDate',getCurrentDate());	        
					$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         
					$this->company_db->where('id',$stocks[$i]['id']);								
					$this->company_db->update('stocksByOrder');	
				}
			}															
		}
	}

	function unassignStockByOrdenDetail($detailOrderId=-1, $unassignQuantity=-1) {
		if ($detailOrderId > 0 && $unassignQuantity > 0) {			
			$sql = "SELECT * 
			        FROM stocksByOrder 
					WHERE stocksByOrder.deleted = 0
					      AND stocksByOrder.detailOrderId = ".$detailOrderId." 					      
					ORDER BY stocksByOrder.id DESC";								
			
			$query = $this->company_db->query($sql);				
			if ($query->num_rows() > 0) {
				$stocks = $query->result_array();
				
				for ($i=0; $i < count($stocks); $i++) {					

					$unassignableQuantity = $stocks[$i]['quantity'] - $stocks[$i]['deliveredQuantity'];

					if ($unassignableQuantity > 0) {
						
						if ($unassignQuantity >= $unassignableQuantity) {
							$quantity = $unassignableQuantity;
						} else {
							$quantity = $unassignQuantity;
						}
						
						$table = ""; 
						$entityId = 0;
						if ((float)$stocks[$i]['stockMovementId'] > 0) {
							$table = "stockMovements";
							$entityId = (float)$stocks[$i]['stockMovementId'];
						} else {
							if ((float)$stocks[$i]['detailBillId'] > 0) {
								$table = "detailsByBill";
								$entityId = (float)$stocks[$i]['detailBillId'];
							}
						}

						if ($table != "") {
							$sql = "UPDATE ".$table." 			                
					                SET freeQuantity = freeQuantity + ".$quantity." 					
					                WHERE id = ".$entityId;		
							$query = $this->company_db->query($sql);											
						}							

						if ($quantity == $stocks[$i]['quantity']) {
							$this->company_db->set('deleted',1);	
							$this->company_db->set('deletedDate',getCurrentDate());	        
							$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         
							$this->company_db->where('id',$stocks[$i]['id']);								
							$this->company_db->update('stocksByOrder');	
						} else {
							$this->company_db->set('quantity',$stocks[$i]['quantity'] - $quantity);						        
							$this->company_db->where('id',$stocks[$i]['id']);								
							$this->company_db->update('stocksByOrder');												
						}

						$unassignQuantity -= $quantity;						
					}

					if ($unassignQuantity <= 0) {
						break;
					}
				}
			}															
		}
	}

	function updateDeliveredQuantity($deliveryNoteId=-1) {

		if ($deliveryNoteId == -1) {
			$sql = "UPDATE stocksByOrder SET deliveredQuantity = 0";		
			$query = $this->company_db->query($sql);

			$sql = "UPDATE stockMovements SET deliveredQuantity = 0";		
			$query = $this->company_db->query($sql);

			$sql = "UPDATE detailsByBill SET deliveredQuantity = 0";		
			$query = $this->company_db->query($sql);			
		}

		$sql = "SELECT detailsByDeliveryNotes.* 
		        FROM ((detailsByDeliveryNotes INNER JOIN deliveryNotes ON detailsByDeliveryNotes.deliveryNoteId = deliveryNotes.id)		        
		        INNER JOIN detailsByOrder ON detailsByDeliveryNotes.detailOrderId = detailsByOrder.id)
		        INNER JOIN orders ON detailsByOrder.orderId = orders.id
				WHERE detailsByDeliveryNotes.deleted = 0
				      AND deliveryNotes.deleted = 0
				      AND detailsByOrder.deleted = 0
				      AND orders.deleted = 0
				      AND orders.stateId != 'CAN' ";

		if ($deliveryNoteId > 0) {
			$sql .= "AND deliveryNotes.id = ".$deliveryNoteId." ";
		}		      
		$sql .= "ORDER BY detailsByDeliveryNotes.id";						
		
		$query = $this->company_db->query($sql);				
		if ($query->num_rows() > 0) {
			$detailsDN = $query->result_array();
			
			for ($i=0; $i < count($detailsDN); $i++) {					
				
				$sql = "SELECT stocksByOrder.* 
				        FROM stocksByOrder
						WHERE stocksByOrder.deleted = 0
						      AND stocksByOrder.detailOrderId = ".$detailsDN[$i]['detailOrderId']."
						ORDER BY stocksByOrder.id";						
				
				$query = $this->company_db->query($sql);				
				if ($query->num_rows() > 0) {
					$stocks = $query->result_array();

					$quantityDN = $detailsDN[$i]['quantity'];
			
					for ($j=0; $j < count($stocks) && $quantityDN > 0; $j++) {		
						$availableQuantity = $stocks[$j]['quantity'] - $stocks[$j]['deliveredQuantity'];
						if ($availableQuantity >= $quantityDN) {
							$deliveredQuantity  = $quantityDN;
						} else {
							$deliveredQuantity  = $availableQuantity;
						}
						$quantityDN -= $deliveredQuantity;

						$sql = "UPDATE stocksByOrder 
						        SET deliveredQuantity = deliveredQuantity + ".$deliveredQuantity." 
						        WHERE id = ".$stocks[$j]['id'];		
						$query = $this->company_db->query($sql);
						
						$table = "";
						$entityId = 0;
						if ((float)$stocks[$j]['stockMovementId'] > 0) {
							$table = "stockMovements";
							$entityId = (float)$stocks[$j]['stockMovementId'];
						} else {
							if ((float)$stocks[$j]['detailBillId'] > 0) {
								$table = "detailsByBill";
								$entityId = (float)$stocks[$j]['detailBillId'];
							}
						}

						if ($table != "") {
							$sql = "UPDATE ".$table." 			                
					                SET deliveredQuantity = deliveredQuantity + ".$deliveredQuantity." 
					                WHERE id = ".$entityId;		
							$query = $this->company_db->query($sql);					
						}														
					}

				}
				
			}			
		}	
	}

	function cancelableCountOfItemOrder($detailOrderId=-1) {
		$cancelableCount = 0;
		
		if ($detailOrderId > 0) {
			$sql = "SELECT quantity, deliveredQuantity, readyQuantity, managerId, submanagerId    
						FROM detailsByOrder INNER JOIN orders 
						ON detailsByOrder.orderId = orders.id 
						WHERE detailsByOrder.deleted = 0 AND 
						      detailsByOrder.id = ".$detailOrderId." AND 
						      orders.deleted = 0 AND 						      
						      NOT orders.stateId IN ('CAN','FIN','NOTAUT','TOAUT')";							      										
			$query = $this->company_db->query($sql);			
			if ($query->num_rows() > 0){
				$detailOrder = $query->row_array();

				if ($detailOrder['managerId'] == $this->session->userdata('userId') || $detailOrder['submanagerId'] == $this->session->userdata('userId') || $this->my_application->hasPermission("Orders","AssignManager")) {
					$outOfStockCount = $detailOrder['quantity'] - $detailOrder['readyQuantity'];
					if ($outOfStockCount < 0) $outOfStockCount = 0;
					if ($outOfStockCount > $detailOrder['quantity']) $outOfStockCount = $detailOrder['quantity'];
					
					$toDeliverCount = $detailOrder['readyQuantity'] - $detailOrder['deliveredQuantity'];
					if ($toDeliverCount < 0) $toDeliverCount = 0;
					if ($toDeliverCount > $detailOrder['readyQuantity']) $toDeliverCount = $detailOrder['readyQuantity'];

					$cancelableCount = $outOfStockCount + $toDeliverCount;
					if ($cancelableCount > $detailOrder['quantity']) $cancelableCount = $detailOrder['quantity'];				
				}
			}
		}

		return $cancelableCount;
	}

	function cancelItemOrder($orderId=-1,$detailOrderId=-1,$count=-1,$observation="") {		

		if ($orderId > 0 && $detailOrderId > 0 && $count > 0) {
			$sql = "SELECT id, warehouseId 
		        	FROM orders 
					WHERE orders.deleted = 0 AND 
						  NOT orders.stateId IN ('CAN','FIN','NOTAUT','TOAUT') AND 
					      orders.id = ".$orderId;														
			$query = $this->company_db->query($sql);			
			if ($query->num_rows() > 0){
				$order = $query->row_array();

				$sql = "SELECT id, articleId, quantity, deliveredQuantity, affectsStock, readyQuantity 
						FROM detailsByOrder 
						WHERE detailsByOrder.deleted = 0 AND 
						      detailsByOrder.id = ".$detailOrderId." AND 
						      detailsByOrder.orderId = ".$order['id'];													
				$query = $this->company_db->query($sql);			
				if ($query->num_rows() > 0){
					$detailOrder = $query->row_array();
					
					$outOfStockCount = $detailOrder['quantity'] - $detailOrder['readyQuantity'];
					if ($outOfStockCount < 0) $outOfStockCount = 0;
					if ($outOfStockCount > $detailOrder['quantity']) $outOfStockCount = $detailOrder['quantity'];
					
					$toDeliverCount = $detailOrder['readyQuantity'] - $detailOrder['deliveredQuantity'];
					if ($toDeliverCount < 0) $toDeliverCount = 0;
					if ($toDeliverCount > $detailOrder['readyQuantity']) $toDeliverCount = $detailOrder['readyQuantity'];

					$cancelableCount = $outOfStockCount + $toDeliverCount;
					if ($cancelableCount > $detailOrder['quantity']) $cancelableCount = $detailOrder['quantity'];				
				
					if ($count <= $cancelableCount) {
										
						if ($count > $outOfStockCount) {
							$reasignCount = $count -$outOfStockCount;
						} else {
							$reasignCount = 0;
						}

						//Descuenta cantidad en item
						$sql = "UPDATE detailsByOrder 			                
				                SET quantity = quantity - ".$count.",
				                    readyQuantity = readyQuantity - ".$reasignCount.",
				                    canceledQuantity = canceledQuantity + ".$count."      
				                WHERE id = ".$detailOrder['id'];		
						$query = $this->company_db->query($sql);

						if ($reasignCount > 0 && (int)$detailOrder['affectsStock'] == 1) {							
							//Unassign Stock
							$this->unassignStockByOrdenDetail($detailOrder['id'],$reasignCount);

							//Reassign Stock Excluding Current Order							
							$this->articles->updatePendingStock($detailOrder['articleId'],$order['warehouseId'],$order['id']);
						}
						
						//Update Total of Detail
						$this->updateTotalDetailByOrden($detailOrder['id']);
						
						//Update Total of Order
						$this->updateOrderTotal($order['id']);					

						//Generta History
						$history = array('orderId'=>$order['id'],
					                     'typeId'=>'CID',
					                 	 'entity'=>'detailsByOrder',
					                 	 'entityId'=>$detailOrder['id'],
					                 	 'value'=>$count,
					                 	 'observation'=>$observation);
						$this->setHistory($history);

						$response = array('code'=>'OK');
					} else {						
						$response = array('code'=>'ERR', 'description'=>'No se pudo cancelar la cantidad solicitada.');
					}
				} else {
					$response = array('code'=>'ERR', 'description'=>'No se puede editar el item o la mismo no existe.');
				}
			} else {
				$response = array('code'=>'ERR', 'description'=>'No se puede editar la orden o la misma no existe.');
			}
		} else {
			$response = array('code'=>'ERR', 'description'=>'Los datos no son válidos.');
		}

		return $response; 
	}

	function getOrderStateId($parameters=NULL){		
		$stateId = "";

		$filter = "";
		if (isset($parameters)) {						
			if (isset($parameters['idFilter']) && $parameters['idFilter'] != "") {
				$filter .= " AND orders.id = ".(float)$parameters['idFilter'];								
			}	
			if (isset($parameters['detailOrderIdFilter']) && $parameters['detailOrderIdFilter'] != "") {
				$filter .= " AND orders.id IN (SELECT orderId 
				                               FROM detailsByOrder
				                               WHERE detailsByOrder.deleted = 0 AND
				                                     detailsByOrder.id = ".(float)$parameters['detailOrderIdFilter']."
				                              )";								
			}
			if (isset($parameters['budgetItemIdFilter']) && $parameters['budgetItemIdFilter'] != "") {
				$filter .= " AND orders.id IN (SELECT orderId 
				                               FROM detailsByBudget INNER JOIN detailsByOrder
				                               ON detailsByBudget.detailOrderId = detailsByOrder.id 
				                               WHERE detailsByBudget.deleted = 0 AND detailsByOrder.deleted = 0 AND
				                                     detailsByBudget.id = ".(float)$parameters['budgetItemIdFilter']."
				                              )";								
			}			
		}

		if ($filter != "") {
			$sql = "SELECT stateId
		        	FROM orders  
					WHERE orders.deleted = 0 ".$filter;												
			
			$query = $this->company_db->query($sql);			
			if ($query->num_rows() == 1){
				$row = $query->row_array();

				$stateId = $row['stateId'];						
			}
		}

		return $stateId;
	}

	function updateOrdersOfInsurance($orderId=-1) {
		$idInsuranceFamilies = "";

		$sql = "SELECT families.id   
		        FROM families 
				WHERE families.deleted = 0 AND families.isInsuranceItem = 1 and families.active = 1";					
		$query = $this->company_db->query($sql);
		if ($query->num_rows() > 0){
			$families = $query->result_array();
			for ($i=0; $i < count($families); $i++) {
				if ($i > 0) $idInsuranceFamilies .=	",";
				$idInsuranceFamilies .= $families[$i]['id'];	
			}				
		}

		$sql = "UPDATE orders SET orders.insuranceOrder = 0 WHERE orders.deleted = 0 ";	
		if ($orderId > 0) $sql .= "AND orders.id = ".$orderId;
		$query = $this->company_db->query($sql);


		if ($idInsuranceFamilies != "") {
			$sql = "UPDATE orders SET orders.insuranceOrder = 1 WHERE orders.deleted = 0 ";	
			if ($orderId > 0) $sql .= "AND orders.id = ".$orderId." ";
			$sql .= "AND
		        	 EXISTS(SELECT detailsByOrder.id 
		        	       FROM detailsByOrder INNER JOIN articles
		        	       ON detailsByOrder.articleId = articles.id
		        	       WHERE detailsByOrder.deleted = 0 AND 
		        	       		 detailsByOrder.orderId = orders.id AND 
		        	       		 articles.familyId IN (".$idInsuranceFamilies.")
		        	 )";		
			$query = $this->company_db->query($sql);			
		}	
	}

}

/* End of file Orders_model.php */
/* Location: ./application/models/Orders_model.php */