<?php
class Systemmessages_model extends CI_Model{           
    
    private $company_db;         

	function __construct() {        
        parent::__construct();        
        
        if ((int)$this->session->userdata('companyId') > 0) {
        	$this->company_db = $this->load->database('company_db_'.$this->session->userdata('companyId'), TRUE); 
        }
    }

    function setDatabase($companyId=-1) {
    	if ($companyId > 0) {
    		if (isset($this->company_db)) {
    			$this->company_db->close();
    		}
        	$this->company_db = $this->load->database('company_db_'.$companyId, TRUE); 
        }
    }

   	function getSystemMessages($parameters=NULL){				
		$systemMessages = array('list'=>NULL, 
		                        'totalRecords'=>0);
		
		$filter = "";
		$order = "";
		$limit = "";
		if (isset($parameters)) {			
			if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
				$filter .= " AND systemMessages.id = ".$parameters['idFilter'];				
			}		
			if (isset($parameters['textFilter']) && $parameters['textFilter'] != '') {
				$filter .= " AND systemMessages.title LIKE ".$this->company_db->escape("%".$parameters['textFilter']."%");
			}				
			if (isset($parameters['dateFromFilter']) && $parameters['dateFromFilter'] != "") {
				$filter .= " AND systemMessages.date >= '".$parameters['dateFromFilter']."' ";
			}
			if (isset($parameters['dateToFilter']) && $parameters['dateToFilter'] != "") {
				$filter .= " AND systemMessages.date <= '".$parameters['dateToFilter']."' ";
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
						$order .= "systemMessages.date ".$auxOrder.", systemMessages.id ".$auxOrder;
					break;	
					case 'tit':
						$order .= "systemMessages.title ".$auxOrder.", systemMessages.id ".$auxOrder;
					break;														
				}														
			}
		}
		
		if ($order == "") {
			$order .= "systemMessages.date, systemMessages.id ";
		}			
						
		$sql = "SELECT SQL_CALC_FOUND_ROWS 
		               systemMessages.* 
		        FROM systemMessages 
				WHERE systemMessages.deleted = 0 ".$filter." 
				ORDER BY ".$order.
				$limit;	

		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){			
			$systemMessages = array('list'=>$query->result_array(), 
		                      	    'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $systemMessages;
	}

	function getEmptySystemMessage() {
		$systemMessage = array('id'=>-1,							 						       				        
						       'date'=>'',
						       'userId'=>$this->session->userdata('userId'),				          	  
						       'title'=>'',
						  	   'message'=>'',
						  	   'notification'=>'0',
						  	   'email'=>'0');
						
		return $systemMessage;
	}
	
	function setSystemMessage($data){
		if (!empty($data)){				
			
			$this->company_db->set('date',getCurrentDate());	 
			if ((float)$this->session->userdata('userId') > 0) {
				$this->company_db->set('userId',$this->session->userdata('userId'));
			} else {	
				$this->company_db->set('userId',1); //User Super Admin - Not can null
			}
			$this->company_db->set('title',$data['title']);
			$this->company_db->set('message',$data['message']);
			$this->company_db->set('notification',(int)$data['notification']);
			$this->company_db->set('email',(int)$data['email']);						
			$this->company_db->set('deleted',0);
			$this->company_db->set('deletedDate',NULL);
			$this->company_db->set('deletedUserId',NULL);

			$this->company_db->insert('systemMessages');		
			$data['id'] = $this->company_db->insert_id();			
			
			$error = $this->company_db->error();
			if ((int)$error['code'] != 0) {										
				return -1;
			} else {			
				if ($data['id'] > 0) {
					if (isset($data['roles'])) {
						$roles = $data['roles'];				
						for($i=0; $i < count($roles); $i++) {
							$this->company_db->set('messageId',$data['id']);
							$this->company_db->set('roleId',$roles[$i]);
							$this->company_db->insert('rolesBySystemMessage');
						}	

						$this->_notifyNewSistemMessage($data);
					}
				}

				return $data['id'];
			}						
		} else {
			return -1;	
		}
	}	

	function getRoles($systemMessageId=-1){				
		$roles = array('list'=>NULL, 
		               'totalRecords'=>0);

		$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM
				(
				SELECT roles.id, roles.description, 0 AS active
		        FROM roles 
				WHERE roles.deleted = 0 
				      AND NOT EXISTS(SELECT * FROM rolesBySystemMessage 
				                     WHERE rolesBySystemMessage.messageId = ".$systemMessageId." 
									       AND rolesBySystemMessage.roleId = roles.id)
				UNION ALL				
				SELECT roles.id, roles.description, 1 AS active
		        FROM roles INNER JOIN rolesBySystemMessage 
				ON roles.id = rolesBySystemMessage.roleId
				WHERE roles.deleted = 0 AND rolesBySystemMessage.messageId = ".$systemMessageId." 
				) AS rolesMessage 
				ORDER BY description";		

		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){		
			$roles = array('list'=>$query->result_array(), 
		                   'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $roles;
	}

	function _notifyNewSistemMessage($systemMessage=NULL) {				
		if (!isset($systemMessage)) return;
		if (!isset($systemMessage['roles']) || (isset($systemMessage['roles']) && count($systemMessage['roles']) <= 0)) return;
				
		//--- NOTIFY TO USERS		
		$this->load->model('users_model','users');		

		$parameters['onlyAllowedFilter'] = true;
		$parameters['rolesIdFilter'] = implode(",", $systemMessage['roles']);					
		$users = $this->users->getUsers($parameters);
		$users = $users['list'];

		if (count($users) > 0) {						
			$this->load->model('notifications_model','notifications');	

			if ((int)$systemMessage['email'] == 1) {				

				//--- BODY MAIL			
				$subject = $this->config->item('applicationName').": ".$systemMessage['title'];					

				$idx = 0;								
				$lstMessage[$idx++] = $systemMessage['message'];																											

				$mailMessage = mailBodyFormat($lstMessage);
			}

			for ($i=0; $i < count($users); $i++) {
				if ($users[$i]['id'] != $this->session->userdata('userId')) {	
					
					if ((int)$systemMessage['email'] == 1) {
						//MAIL
						$to = trim($users[$i]['email']);
						if ($to != "") {					
							$sendOK = $this->my_application->sendMail($to,$subject,$mailMessage);
						}
					}

					if ((int)$systemMessage['notification'] == 1) {
						//NOTIFY
						$notification = array('typeId'=>'SM', //SYSTEM MESSAGE
											  'observation'=>$systemMessage['title'],
											  'entity'=>'systemMessage',
											  'entityId'=>$systemMessage['id'],
											  'userId'=>$users[$i]['id']);
						$this->notifications->setNotification($notification);					
					}
				}
			}
		}
	}	
	
}

/* End of file Systemmessages_model.php */
/* Location: ./application/models/Systemmessages_model.php */