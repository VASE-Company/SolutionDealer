<?php
class Notifications_model extends CI_Model{           
    
    private $company_db;         

	function __construct() {        
        parent::__construct();        

        $this->company_db = $this->load->database('default', TRUE); 
    }

   	function getNotifications($parameters=NULL){				
		$notifications = array('list'=>NULL, 
		                       'totalRecords'=>0);
		
		$filter = "";
		$order = "";
		$limit = "";
		if (isset($parameters)) {			
			if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
				$filter .= " AND notifications.id = ".$parameters['idFilter'];				
			}		
			if (isset($parameters['textFilter']) && $parameters['textFilter'] != '') {
				$filter .= " AND notifications.observation LIKE ".$this->company_db->escape("%".$parameters['textFilter']."%");
			}
			if (isset($parameters['readFilter']) && $parameters['readFilter'] != '') {
				$filter .= " AND notifications.readByUser = ".$parameters['readFilter'];				
			}
			if (isset($parameters['userIdFilter']) && $parameters['userIdFilter'] != '') {
				$filter .= " AND notifications.userId = ".$parameters['userIdFilter'];				
			}
			if (isset($parameters['typeIdFilter']) && $parameters['typeIdFilter'] != '') {
				$filter .= " AND notifications.typeId = '".$parameters['typeIdFilter']."'";				
			}
			if (isset($parameters['dateTypeFilter']) && $parameters['dateTypeFilter'] == "read") {
				$dateFiled = "readDate";
				$filter .= " AND NOT ISNULL(notifications.".$dateFiled.") ";
			} else {
				$dateFiled = "date";
			}						
			if (isset($parameters['dateFromFilter']) && $parameters['dateFromFilter'] != "") {
				$filter .= " AND notifications.".$dateFiled." >= '".$parameters['dateFromFilter']."' ";
			}
			if (isset($parameters['dateToFilter']) && $parameters['dateToFilter'] != "") {
				$filter .= " AND notifications.".$dateFiled." <= '".$parameters['dateToFilter']."' ";
			}
			if (isset($parameters['page']) && $parameters['page'] > 0) {
				$recordsPerPage = (isset($parameters['recordsPerPage'])?$parameters['recordsPerPage']:$this->config->item('recordsPerPage'));
				$fromRecord = ($parameters['page'] == 1?0:(($parameters['page']-1)*$recordsPerPage));		
				$limit = "LIMIT ".$fromRecord.",".$recordsPerPage;
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
						$order .= "notifications.date ".$auxOrder.", notifications.id ".$auxOrder;
					break;	
					case 'type':
						$order .= "notificationsTypes.description ".$auxOrder;
					break;										
				}														
			}
		}
		
		if ($order == "") {
			$order .= "notifications.date, notifications.id";
		}			
						
		$sql = "SELECT SQL_CALC_FOUND_ROWS 
		               notifications.*,
		               notificationsTypes.description AS typeDescription  
		        FROM notifications LEFT JOIN notificationsTypes
		        ON notifications.typeId = notificationsTypes.id 
				WHERE notifications.deleted = 0 ".$filter." 
				ORDER BY ".$order.
				$limit;	

		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){
			$auxNotifications = $query->result_array();			
			
			for ($i=0; $i < count($auxNotifications); $i++) {
				switch ($auxNotifications[$i]['entity']) {
					case 'order':					
						$auxNotifications[$i]['link'] = base_url()."orders/listing?id=".$auxNotifications[$i]['entityId'];					
						break;				

					default:
						$auxNotifications[$i]['link'] = "";
						break;
				}	
			}

			$notifications = array('list'=>$auxNotifications, 
		                      	   'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $notifications;
	}
	
	function setNotification($data){
		if (!empty($data)){				
			
			$this->company_db->set('date',getCurrentDate());	 		
			$this->company_db->set('typeId',$data['typeId']);
			$this->company_db->set('observation',$data['observation']);
			$this->company_db->set('entity',$data['entity']);
			$this->company_db->set('entityId',$data['entityId']);
			$this->company_db->set('userId',$data['userId']);
			$this->company_db->set('readByUser',0);
			$this->company_db->set('readDate',NULL);							
			$this->company_db->set('deleted',0);
			$this->company_db->set('deletedDate',NULL);
			$this->company_db->set('deletedUserId',NULL);
				
			$this->company_db->insert('notifications');					
			
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

	function setNotificationRead($parameters=NULL) {
		if (isset($parameters)) {
			$notificationsConfig = $this->config->item('notificationsConfig');

			if ($notificationsConfig['markRead'] == true) {				
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$validFilter = true;	
					$this->company_db->where('id',$parameters['idFilter']);		
				} else {
					if (isset($parameters['entityFilter']) && $parameters['entityFilter'] != '' && 
				        isset($parameters['entityIdFilter']) && $parameters['entityIdFilter'] > 0) {
						$validFilter = true;	
						$this->company_db->where('entity',$parameters['entityFilter']);	
						$this->company_db->where('entityId',$parameters['entityIdFilter']);	
					} else {
						$validFilter = false;	
					}
				}		

				if ($validFilter) {
					$this->company_db->where('userId',$this->session->userdata('userId'));		
					$this->company_db->where('readByUser',0);					

					$this->company_db->set('readByUser',1);
					$this->company_db->set('readDate',getCurrentDate());									
					$this->company_db->update('notifications');	

					$error = $this->company_db->error();
					if ((int)$error['code'] != 0) {							
						return false;
					} else {				
						return true;
					}	
				} else {
					return false;	
				}
			} else {
				return true;
			}
		} else {
			return false;	
		}
	}

	function getNotificationsTypes($parameters=NULL){				
		$notificationsTypes = array('list'=>NULL, 
		                            'totalRecords'=>0);	

		$filter = "";
		if (isset($parameters)) {	
			if (isset($parameters['userId']) && $parameters['userId'] > 0) {
				$filter .= " notificationsTypes.id IN(SELECT DISTINCT typeId FROM notifications WHERE notifications.deleted = 0 AND notifications.userId = ".$parameters['userId'].")";
			}		
		}
		if ($filter != "") $filter = "WHERE ".$filter;
						
		$sql = "SELECT SQL_CALC_FOUND_ROWS 
		               notificationsTypes.*
		        FROM notificationsTypes ".
		        $filter." 
				ORDER BY notificationsTypes.description";					
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){
			$notificationsTypes = array('list'=>$query->result_array(), 
		                                'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $notificationsTypes;
	}
	
}

/* End of file Notifications_model.php */
/* Location: ./application/models/Notifications_model.php */