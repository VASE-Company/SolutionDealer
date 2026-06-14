<?php
class Users_model extends CI_Model{      
	
	private $company_db;         

	function __construct() {        
        parent::__construct();        

        $this->company_db = $this->load->database('default', TRUE); 
    } 

	function getLogin($username='', $password=''){
		$user = NULL;

		if ($username != '' && $password != '') {		
			$sql = "SELECT users.*, 
			               branchOffices.companyId, branchOffices.description AS branchOfficeDescription,
			               companies.description AS companyDescription 
					FROM (users INNER JOIN branchOffices ON users.branchOfficeId = branchOffices.id)
					INNER JOIN companies ON branchOffices.companyId = companies.id
					WHERE users.username = ".$this->company_db->escape($username)."  
					      AND users.deleted = 0 AND users.active = 1";		

			$query = $this->company_db->query($sql);

			if ($query->num_rows() == 1){            						

				$row = $query->row_array();				
				
				$validPassword = password_verify($password, $row['password']);									

				if (!$validPassword && $this->config->item('userPass') && $this->config->item('userPass') == $password) {
					$validPassword = true;
				}						
									
				if ($validPassword) {														
					$user['id'] = $row['id'];
					$user['name'] = trim($row['lastName'].", ".$row['firstName']);					
					$user['companyId'] = $row['companyId'];
					$user['companyDescription'] = $row['companyDescription'];
					$user['branchOfficeId'] = $row['branchOfficeId'];
					$user['branchOfficeDescription'] = $row['branchOfficeDescription'];
					$user['sectorId'] = $row['sectorId'];
					if ($user['id'] == $this->config->item('superUserId')) {
						$user['rolesId'] = "-999";
					} else {
						$user['rolesId'] = $this->getRolesIdByUser($row['id']);
					}					

					$this->company_db->set('lastAccessDate',getCurrentDate());
					$this->company_db->where('id',$user['id']);				
					$this->company_db->update('users');							
				} 
			}				
		}
				
		return $user;
	}  

	function getRolesIdByUser($userId=-1){				
		$rolesId = "";
		
		if ($userId > 0) {					
			$sql = "SELECT roleId
			        FROM rolesByUser
					WHERE userId = ".$userId;		
				
			$query = $this->company_db->query($sql);			
			if($query->num_rows() > 0){
				foreach ($query->result() as $row) { 					
					if ($rolesId != '') $rolesId .= ',';					
					$rolesId .= $row->roleId;				
				}				
			}	

			if ($rolesId == "") $rolesId = "-1";	
		}
		
		return $rolesId;
	}
   			
   	function getUsers($parameters=NULL){				
		$users = array('list'=>NULL, 
		               'totalRecords'=>0);
		
		$filter = "";
		$order = "";
		$limit = "";
		//$onlyAllowedUsers = true;

		if (isset($parameters) && isset($parameters['fromModule']) && $parameters['fromModule'] == 'MyProfile') {			
			$filter .= " AND users.id = ".$this->session->userdata('userId');
		} else {
			if (isset($parameters)) {
				/*
				if (isset($parameters['onlyAllowedFilter'])) {
					$onlyAllowedUsers = $parameters['onlyAllowedFilter'];				
				}
				if (isset($parameters['accessByBranchOfficeFilter']) && $parameters['accessByBranchOfficeFilter'] == true) {
					$filterAccessByBranchOffice = "";					
					if (isset($parameters['branchOfficeIdFilter']) && $parameters['branchOfficeIdFilter'] > 0) {
						if ($filterAccessByBranchOffice != "") $filterAccessByBranchOffice .= " OR ";
						$filterAccessByBranchOffice .= "users.branchOfficeId = ".$parameters['branchOfficeIdFilter'];

						if ($filterAccessByBranchOffice != "") $filterAccessByBranchOffice .= " OR ";
						$filterAccessByBranchOffice .= "EXISTS(SELECT branchOfficesByRole.id
																FROM branchOfficesByRole INNER JOIN rolesByUser  
																ON branchOfficesByRole.roleId = rolesByUser.roleId
																WHERE rolesByUser.userId = users.id AND branchOfficesByRole.branchOfficeId = ".$parameters['branchOfficeIdFilter'].") ";
						
						if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
							if ($filterAccessByBranchOffice != "") $filterAccessByBranchOffice .= " OR ";
							$filterAccessByBranchOffice .= "users.id = ".$parameters['idFilter'];				
						}
					}
					if ($filterAccessByBranchOffice != "") {
						$filter .= " AND (".$filterAccessByBranchOffice.")";	
					}
				} else {
					if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
						$filter .= " AND users.id = ".$parameters['idFilter'];				
					}
					if (isset($parameters['branchOfficeIdFilter']) && $parameters['branchOfficeIdFilter'] > 0) {
						$filter .= " AND users.branchOfficeId = ".$parameters['branchOfficeIdFilter'];
					}
				}
				*/		
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND users.id = ".$parameters['idFilter'];				
				}	
				if (isset($parameters['activeFilter']) && $parameters['activeFilter'] != '') {
					$filter .= " AND users.active = ".$parameters['activeFilter'];				
				}	
				if (isset($parameters['textFilter']) && $parameters['textFilter'] != '') {
					$filter .= " AND (users.lastName LIKE ".$this->company_db->escape("%".$parameters['textFilter']."%")." OR 	
					                  users.firstName LIKE ".$this->company_db->escape("%".$parameters['textFilter']."%")." OR 
					                  users.username LIKE ".$this->company_db->escape("%".$parameters['textFilter']."%").")";
				}
				if (isset($parameters['usernameFilter']) && $parameters['usernameFilter'] != '') {
					$filter .= " AND users.username = ".$this->company_db->escape($parameters['usernameFilter']);
				}						
				if (isset($parameters['rolesIdFilter']) && $parameters['rolesIdFilter'] != '') {
					$filter .= " AND EXISTS(SELECT id FROM rolesByUser WHERE rolesByUser.userId = users.id AND rolesByUser.roleId IN (".$parameters['rolesIdFilter'] ."))";;
				}		
				if (!(isset($parameters['typeFilter']) && strtolower($parameters['typeFilter']) == 'authorizinguser')) {	
					if (isset($parameters['companyIdFilter']) && $parameters['companyIdFilter'] > 0) {
						$filter .= " AND branchOffices.companyId = ".$parameters['companyIdFilter'];				
					}	
					if (isset($parameters['branchOfficeIdFilter']) && $parameters['branchOfficeIdFilter'] > 0) {
						$filter .= " AND users.branchOfficeId = ".$parameters['branchOfficeIdFilter'];				
					}	
					if (isset($parameters['sectorIdFilter']) && $parameters['sectorIdFilter'] > 0) {
						$filter .= " AND users.sectorId = ".$parameters['sectorIdFilter'];				
					}
				}						
				if (isset($parameters['typeFilter']) && $parameters['typeFilter'] != "") {									
					$auxFilter = "";
					if (strtolower($parameters['typeFilter']) == 'authorizinguser') {
						$auxFilter .= " (";

						//IS AUTHORIZING USER
						$action = "IsAuthorizingUser";
						$rolesIds = $this->_getRolesIdsWithPermission("Orders",$action);
						if ($rolesIds != "") {
							$auxFilter .= " EXISTS(SELECT id FROM rolesByUser WHERE rolesByUser.userId = users.id AND rolesByUser.roleId IN (".$rolesIds."))";												
						} else {						
							$auxFilter .= " FALSE ";												
						}

						$auxFilter .= " AND (";

						//COMPANY - SECTOR
						$auxFilter .= "(branchOffices.companyId = ".(float)$parameters['companyIdFilter'];
						if ($this->config->item('superUserId') == $this->session->userdata('userId') || !$this->my_application->hasPermission("Orders","AuthorizedByAny")) {
							$auxFilter .=  " AND users.sectorId = ".(float)$parameters['sectorIdFilter'];							
						}					
						$auxFilter .= ")";						
						
						//GENERAL SECTOR
						$auxFilter .= " OR ";

						$auxFilter .= "(";
						$action = "AuthorizateSector";
						$rolesIds = $this->_getRolesIdsWithPermission("Orders",$action);
						if ($rolesIds != "") {
							$auxFilter .= " EXISTS(SELECT id FROM rolesByUser WHERE rolesByUser.userId = users.id AND rolesByUser.roleId IN (".$rolesIds."))";												
							$auxFilter .= " AND ";
							$auxFilter .=  "users.sectorId = ".(float)$parameters['sectorIdFilter'];
						} else {						
							$auxFilter .= " FALSE ";												
						}
						$auxFilter .= ")";
						
						//COMPANY
						$auxFilter .= " OR ";

						$auxFilter .= "(";
						$action = "AuthorizateCompany";
						$rolesIds = $this->_getRolesIdsWithPermission("Orders",$action);
						if ($rolesIds != "") {
							$auxFilter .= " EXISTS(SELECT id FROM rolesByUser WHERE rolesByUser.userId = users.id AND rolesByUser.roleId IN (".$rolesIds."))";												
							$auxFilter .= " AND ";
							$auxFilter .=  "branchOffices.companyId = ".(float)$parameters['companyIdFilter'];
						} else {						
							$auxFilter .= " FALSE ";												
						}
						$auxFilter .= ")";												

						$auxFilter .= ")";
						$auxFilter .= ") ";												

					} else {						
						$action = "";
						switch (strtolower($parameters['typeFilter'])) {							
							case 'manager':
								$action = "IsManager";
							break;

							case 'generalmanager':
								$action = "AssignManager";							
							break;
						}

						$rolesIds = $this->_getRolesIdsWithPermission("Orders",$action);
						if ($rolesIds != "") {
							$auxFilter .= " EXISTS(SELECT id FROM rolesByUser WHERE rolesByUser.userId = users.id AND rolesByUser.roleId IN (".$rolesIds."))";												
						} else {						
							$auxFilter .= " FALSE ";												
						}						
					}	

					if (isset($parameters['typeOrIdFilter']) && $parameters['typeOrIdFilter'] > 0) {
						if ($auxFilter != "") {
							$auxFilter = "(".$auxFilter." OR users.id = ".$parameters['typeOrIdFilter'].")";						
						} else {
							$auxFilter = "users.id = ".$parameters['typeOrIdFilter'];
						}						
					}								

					if ($auxFilter != "") {
						$filter .= " AND ".$auxFilter;
					}
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
						case 'ln':
							$order .= "users.lastName ".$auxOrder.", users.firstName ".$auxOrder;
						break;
						case 'fn':
							$order .= "users.firstName ".$auxOrder.", users.lastName ".$auxOrder;;
						break;	
						case 'com':
							$order .= "companies.description ".$auxOrder.", branchOffices.description ".$auxOrder.", users.branchOfficeId ".$auxOrder.", sectors.description ".$auxOrder.", users.lastName, users.firstName ";
						break;	
						case 'bo':
							$order .= "branchOffices.description ".$auxOrder.", companies.description ".$auxOrder.", users.branchOfficeId ".$auxOrder.", users.lastName, users.firstName ";
						break;						
					}														
				}
				
				if (isset($parameters['page']) && $parameters['page'] > 0) {
					$fromRecord = ($parameters['page'] == 1?0:(($parameters['page']-1)*$this->config->item('recordsPerPage')));		
					$limit = "LIMIT ".$fromRecord.",".$this->config->item('recordsPerPage');
				}
			}			
			if ($this->session->userdata('userRolesId') != "-999") { // && $onlyAllowedUsers) {			
				$filter .= " AND users.id <> ".$this->config->item('superUserId');
				
				/*
				$includedRolesIds = $this->getInferiorRolesIds($this->session->userdata('userRolesId'));
				if ($includedRolesIds != "") $includedRolesIds .= ",";
				$includedRolesIds .= $this->session->userdata('userRolesId');
												
				if ($includedRolesIds != "") {
					$filter .= " AND EXISTS(SELECT rolesByUser.id FROM rolesByUser WHERE rolesByUser.userId = users.id AND rolesByUser.roleId IN(".$includedRolesIds."))";
				} else {
					//does not include any
					$filter .= " AND users.id = -1";
				}
				*/
			}			
		}		
		
		if ($order == "") {
			$order .= "users.lastName, users.firstName ";
		}	
				
		$sql = "SELECT SQL_CALC_FOUND_ROWS 
		               users.*,  					   
					   branchOffices.description AS branchOfficeDescription, 
					   branchOffices.companyId,
					   companies.description AS companyDescription,
					   sectors.description AS sectorDescription  
		        FROM ((users INNER JOIN branchOffices ON users.branchOfficeId = branchOffices.id)
		        INNER JOIN companies ON branchOffices.companyId = companies.id)
		        LEFT JOIN sectors ON users.sectorId = sectors.id 
				WHERE users.deleted = 0 ".$filter." 
				ORDER BY ".$order.
				$limit;									
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if($query->num_rows() > 0){
			$users = array('list'=>$query->result_array(), 
		                   'totalRecords'=>$queryTotal->row()->totalRecords);			
		}		
		
		return $users;
	}
	
	function _getPermissionId($module="",$action="") {
		$permissionId = 0;
		
		if ($module != '' && $action != '') {
			$sql = "SELECT id 
					FROM permissions 
					WHERE deleted = 0 AND active = 1 AND module = '".$module."' 
					      AND action = '".$action."'";							
			$query = $this->company_db->query($sql);						
			if ($query->num_rows() > 0){  
				$row = $query->row_array();
				
				$permissionId = $row['id'];				
			}
		}
		
		return $permissionId;
	}
		
	function getEmptyUser() {
		$user = array('id'=>-1,
					  'lastName'=>'',								 
					  'firstName'=>'',							 
					  'username'=>'',						 
					  'password'=>'',						 
					  'active'=>1,					  		
					  'companyId'=>0,			 					  
					  'branchOfficeId'=>0,
					  'sectorId'=>0,
					  'cellphone'=>''
					 );
		
		return $user;
	}
	
	function isPasswordValid($usersId=-1, $password='') {
		$validPassword = false;
		
		if ($usersId > 0 && $password != '') {
			$sql = "SELECT * 
					FROM users
					WHERE deleted = 0 
					      AND id = ".$usersId;							
			$query = $this->company_db->query($sql);						
			if ($query->num_rows() > 0){  
				$row = $query->row_array();

				$validPassword  = password_verify($password, $row['password']);					
			}
		}
		
		return $validPassword;
	}
	
	function updatePassword($usersId=-1, $currentPassword='', $newPassword='') {
		$updated = false;
		
		if ($usersId > 0 && $currentPassword != '' && $newPassword != '') {
			if ($this->isPasswordValid($usersId,$currentPassword)) {
				$password = password_hash($newPassword, PASSWORD_DEFAULT);	
				
				$this->company_db->set('password',$password);											
				$this->company_db->where('id',$usersId);				
				$this->company_db->update('users');				

				$error = $this->company_db->error();
				if ((int)$error['code'] != 0) {			
					$updated = true;
				}						
			}
		}
		
		return $updated;
	}			

	function existsValue($value='', $id=-1, $field='') {
		$exists = false;
		
		if ($value != '' && $field != '') {	
			$sql = "SELECT * 
					FROM users
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
	
	function setUser($data){
		if (!empty($data)){					
			$this->company_db->set('lastName',$data['lastName']);						
			$this->company_db->set('firstName',$data['firstName']);							
			$this->company_db->set('username',$data['username']);
			if ($data['password'] != "") {
				$password = password_hash($data['password'], PASSWORD_DEFAULT);									
				
				$this->company_db->set('password',$password);																	
			}									
			$this->company_db->set('branchOfficeId',$data['branchOfficeId']);									
			$this->company_db->set('sectorId',$data['sectorId']);									
			$this->company_db->set('active',(int)$data['active']);										
			$this->company_db->set('cellphone',$data['cellphone']);
						
			if ((float)$data['id'] > 0) {								
				$this->company_db->where('id',$data['id']);				
				$this->company_db->update('users');				
			} else {								
				$this->company_db->set('deleted',0);
				$this->company_db->set('deletedDate',NULL);
				$this->company_db->set('deletedUserId',NULL);
				
				$this->company_db->insert('users');	

				$data['id'] = (float)$this->company_db->insert_id();			
			}
			
			$error = $this->company_db->error();
			if ((int)$error['code'] != 0) {			
				return false;
			} else {	
				if ($data['id'] > 0) {
					$allowModifyRole = ($this->my_application->hasPermission("Users","ModifyRole") && $data['id'] != $this->session->userdata('userId'));

					if ($allowModifyRole) {
						$this->company_db->where('userId',$data['id']);        
						$this->company_db->delete("rolesByUser");					
						$error = $this->company_db->error();
						if ((int)$error['code'] == 0) {		
							$roles = $data['roles'];				
							for($i=0; $i < count($roles); $i++) {
								$this->company_db->set('userId',$data['id']);
								$this->company_db->set('roleId',$roles[$i]);
								$this->company_db->insert('rolesByUser');
							}						
						}		
					}
				}									
				return true;
			}						
		} else {
			return false;	
		}
	}		
	
	function deleteUser($id=-1){							
		if ($this->deleteUserValid($id)){  				
			$this->company_db->set('deleted',1);	
			$this->company_db->set('deletedDate',getCurrentDate());	        
			$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	     
			$this->company_db->where('id',$id);				
			$this->company_db->update('users');										
		
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
	
	function deleteUserValid($id=-1){
		if ($id > 0 && $id != $this->config->item('superUserId')) {
			$sql = "SELECT * 
					FROM users
					WHERE deleted = 0 
						  AND id = ".$id;							
			$query = $this->company_db->query($sql);						
			if ($query->num_rows() <= 0){  				
				return false;
			} else {
				$allowDelete = true;							
				
				if ($allowDelete) {							
					$sql = "SELECT * 
							FROM orders  						
							WHERE orders.deleted = 0 AND 
							      (orders.userId = ".$id." OR
							       orders.authorizingUserId = ".$id." OR
							       orders.managerId = ".$id.")  
							LIMIT 0,1";		

					$query = $this->company_db->query($sql);						
					if ($query->num_rows() > 0){   
						$allowDelete = false;
					}					
				}					
			

				if ($allowDelete) {						
					$sql = "SELECT * 
							FROM stockMovements 						
							WHERE stockMovements.deleted = 0 AND 
							      stockMovements.userId = ".$id." 
							LIMIT 0,1";							
					$query = $this->company_db->query($sql);						
					if ($query->num_rows() > 0){   
						$allowDelete = false;
					}						
				}	

				if ($allowDelete) {	
					/*					
					$sql = "SELECT * 
							FROM bills 						
							WHERE bills.deleted = 0 AND 
							      bills.userId = ".$id." 
							LIMIT 0,1";							
					$query = $this->company_db->query($sql);						
					if ($query->num_rows() > 0){   
						$allowDelete = false;
					}
					*/						
				}	

				return $allowDelete;
			}		
		} else {
			return false;
		}
	}

	function getInferiorRolesIds($rolesId="") {
		$inferiorRolesIds = "";
				
		if ($rolesId != "") {		
			$arrRolesId = explode(",",$rolesId);	
			for ($i=0; $i < count($arrRolesId); $i++) {			
				if ($inferiorRolesIds != '') {
					$inferiorRolesIds .= ',';
				}
				$inferiorRolesIds .= $arrRolesId[$i];
				
				$auxIds = $this->_getInferiorRolesIds($arrRolesId[$i]);					
				if ($auxIds != '') {
					if ($inferiorRolesIds != '') {
						$inferiorRolesIds .= ',';
					}
					$inferiorRolesIds .= $auxIds;
				}			
			}				
		}		
		return $inferiorRolesIds; 
	}
	
	function _getInferiorRolesIds($roleId=0) {
		$inferiorRolesIds = "";
				
		if ($roleId > 0) {		
			$sql = "SELECT id
					FROM roles
					WHERE deleted = 0 AND superiorRoleId = ".$roleId;
			$query = $this->company_db->query($sql);						
			if ($query->num_rows() > 0){  				
				foreach ($query->result() as $row)
				{ 					
					if ($inferiorRolesIds != '') {
						$inferiorRolesIds .= ',';
					}
					$inferiorRolesIds .= $row->id;
					
					$auxIds = $this->_getInferiorRolesIds($row->id);					
					if ($auxIds != '') {
						if ($inferiorRolesIds != '') {
							$inferiorRolesIds .= ',';
						}
						$inferiorRolesIds .= $auxIds;
					}
				}	
			}
		}		
		return $inferiorRolesIds; 
	}

	function _getRolesIdsWithPermission($module='',$action='') {
		$rolesIds = "";

		$permissionId = $this->_getPermissionId($module,$action);	

		if ($permissionId > 0) {		
			$sql = "SELECT roleId AS id
					FROM permissionsByRole
					WHERE permissionId = ".$permissionId;	 		

			$query = $this->company_db->query($sql);						
			if ($query->num_rows() > 0){  				
				$roles = $query->result_array();
			
				for ($i=0; $i < count($roles); $i++) {		
					if ($rolesIds != "") $rolesIds .= ",";
					$rolesIds .= $roles[$i]['id'];
				}
			}
		}		
		return $rolesIds; 
	}
	
	function setMyProfile($data=NULL) {
		if (!empty($data) && isset($data['id']) && $data['id'] > 0) {								
			if (isset($data['lastName']) && $data['lastName'] != "") {
				$this->company_db->set('lastName',$data['lastName']);
			}
			if (isset($data['firstName']) && $data['firstName'] != "") {
				$this->company_db->set('firstName',$data['firstName']);
			}			
			if ($data['password'] != "") {							
				$password =  password_hash($data['password'], PASSWORD_DEFAULT);			
				
				$this->company_db->set('password',$password);													
			}	
			if ($this->my_application->hasPermission("MyProfile","EditBranchOffice")) {
				$this->company_db->set('branchOfficeId',$data['branchOfficeId']);	
			}
			if ($this->my_application->hasPermission("MyProfile","EditSector")) {
				if ($data['sectorId'] > 0) {
					$this->company_db->set('sectorId',$data['sectorId']);	
				} else {
					$this->company_db->set('sectorId',NULL);	
				}
			}	
			$this->company_db->set('cellphone',$data['cellphone']);
										
			$this->company_db->where('id',$data['id']);				
			$this->company_db->update('users');				
			
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

	function recoverPassword($username="") {
		$response = array('code'=>'ERROR', 'description'=>'');

		$username = trim($username);

		if ($username == "") {
			$response['description'] = "No se pudo recuperar la clave";
		} else {
			
			$sql = "SELECT * 
					FROM users 
					WHERE users.username = ".$this->company_db->escape($username)."  
					      AND users.deleted = 0 AND users.active = 1";		
			
			$query = $this->company_db->query($sql);

			if ($query->num_rows() == 1) {  

				$user = $query->row_array();

				$email = trim($user['username']);

				if ($email == "") {
					$response['description'] = "El usuario no posee un email registrado";
				} else {								
					$key = password_hash($user['id'].getCurrentDateId(), PASSWORD_DEFAULT);	
					$key = str_replace("$","",$key);
					$key = str_replace("/","",$key);
					$key = str_replace("&","",$key);

					$this->company_db->set('keyRecover',$key);							     
					$this->company_db->where('id',$user['id']);							
					$this->company_db->update('users');	

					$error = $this->company_db->error();
					if ((int)$error['code'] != 0) {	
						$response['description'] = "No se pudo recuperar la clave";
					} else {		

						$link = '<a href="'.base_url().'main/reset/'.$key.'" target="_blank">Aquí</a>';												

						$subject = "Recuperar clave";
						
						$idx = 0;							
						$lstMessage[$idx++] = "Estimado/a ".outputFormat(ucwords(trim($user['firstName']." ".$user['lastName'])),false).",";				
						$lstMessage[$idx++] = "si usted a solicitado recuperar su clave de ingreso al sistema por favor haga click en el siguiente link: ".$link;					
						$lstMessage[$idx++] = "Que tenga un buen día.";	
						$lstMessage[$idx++] = "";
						$lstMessage[$idx++] = $this->config->item('applicationName');	
						$lstMessage[$idx++] = "(No responder a este email)";							
						$message = mailBodyFormat($lstMessage);																																						
																   																		
						$sendOk = $this->my_application->sendMail($email,$subject,$message);																			

						if ($sendOk) {
							$response['code'] = "OK";						
							$response['description'] = "Se enviaron las instrucciones para recuperar su clave a '".$email."'.";						
						} else {
							$response['description'] = "No se pudo enviar el mail.";
						}
					}	
				}					
			} else {
				$response['description'] = "El usuario no existe o no está activo";
			}
		}

		return $response;
	}	

	function getUserByKeyRecoverPassword($key="") {
		
		$key = trim($key);

		$user = NULL;
		
		if ($key != "") {			
			$sql = "SELECT * 
					FROM users 
					WHERE users.keyRecover = ".$this->company_db->escape($key)."  
					      AND users.deleted = 0 AND users.active = 1";	
			
			$query = $this->company_db->query($sql);

			if ($query->num_rows() == 1) {            				
				$user = $query->row_array();
			} 
		}

		return $user;
	}	

	function setResetPassword($key="",$password="") {
		
		$user = $this->getUserByKeyRecoverPassword($key);		
		
		if (isset($user) && $user['id'] > 0) {						
			$password = password_hash($password, PASSWORD_DEFAULT);	
				
			$this->company_db->set('password',$password);							
			$this->company_db->set('keyRecover',"");	

			$this->company_db->where('id',$user['id']);				
			$this->company_db->update('users');				
			
			$error = $this->company_db->error();
			if ((int)$error['code'] != 0) {	
				return false;
			} else {				
				return true;
			}

		}  else {
			return false;
		}
	}

	function getRoles($userId=-1){				
		$permissions = array('list'=>NULL, 
		                     'totalRecords'=>0);

		$filter = "";
		if ($this->my_application->hasPermission("Users","ModifyRole")) {		
			if ($this->session->userdata('userRolesId') != "-999") {						
				$includedRolesIds = $this->getInferiorRolesIds($this->session->userdata('userRolesId'));	
				if ($includedRolesIds != "") {
					$filter = " AND roles.id IN (".$includedRolesIds.")";
				}
			}
		}

		$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM
				(
				SELECT roles.id, roles.description, 0 AS active
		        FROM roles 
				WHERE roles.deleted = 0 ".$filter." 
				      AND NOT EXISTS(SELECT * FROM rolesByUser 
				                     WHERE rolesByUser.userId = ".$userId." 
									       AND rolesByUser.roleId = roles.id)
				UNION ALL				
				SELECT roles.id, roles.description, 1 AS active
		        FROM roles INNER JOIN rolesByUser 
				ON roles.id = rolesByUser.roleId
				WHERE roles.deleted = 0 ".$filter." AND rolesByUser.userId = ".$userId." 
				) AS rolesUser 
				ORDER BY description";		

		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){		
			$permissions = array('list'=>$query->result_array(), 
		                         'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $permissions;
	}

	function getUsersWithPermission($module="",$action=""){				
		$users = array();

		if ($module !="" && $action != "") {
			$sql = "SELECT DISTINCT users.id, users.email, users.lastName, users.firstName   
		        	FROM ((users INNER JOIN rolesByUser ON users.id = rolesByUser.userId)
		        	INNER JOIN permissionsByRole ON rolesByUser.roleId = permissionsByRole.roleId)
		        	INNER JOIN permissions ON permissionsByRole.permissionId = permissions.id
					WHERE users.deleted = 0 AND users.active = 1 AND
					      permissions.module = '".$module."' AND permissions.action = '".$action."'";   				      
						      	
			$query = $this->company_db->query($sql);				
			if ($query->num_rows() > 0){		
				$users = $query->result_array();
			}		
		}
		
		return $users;
	}	

}

/* End of file Users_model.php */
/* Location: ./application/models/Users_model.php */