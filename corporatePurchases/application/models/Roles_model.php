<?php
class Roles_model extends CI_Model{  

	private $company_db;         

	function __construct() {        
        parent::__construct();        

        $this->company_db = $this->load->database('default', TRUE); 
    } 
          
   	function getRoles($parameters=NULL){				
		$roles = array('list'=>NULL, 
		               'totalRecords'=>0);
		
		$filter = "";
		$order = "";
		$limit = "";
		if (isset($parameters)) {
			if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
				$filter .= " AND roles.id = ".$parameters['idFilter'];				
			}
			if (isset($parameters['textFilter']) && $parameters['textFilter'] != '') {
				$filter .= " AND roles.description LIKE ".$this->company_db->escape("%".$parameters['textFilter']."%");
			}
			if (isset($parameters['excludeIdFilter']) && $parameters['excludeIdFilter'] > 0) {
				$filter .= " AND roles.id <> ".$parameters['excludeIdFilter'];				
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
					case 'des':
						$order .= "roles.description ".$auxOrder;
					break;					
				}														
			}
		}
		
		if ($order == "") {
			$order .= "roles.description ";
		}	
						
		$sql = "SELECT SQL_CALC_FOUND_ROWS 
		               roles.*, 
					   IF(roles.superiorRoleId <= 0,'',superiorRoles.description) AS superiorRoleDescription   
		        FROM roles LEFT JOIN roles superiorRoles 
				ON roles.superiorRoleId = superiorRoles.id 
				WHERE roles.deleted = 0 ".$filter." 
				ORDER BY ".$order.
				$limit;						
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){
			$roles = array('list'=>$query->result_array(), 
		                   'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $roles;
	}

	function getEmptyRole() {
		$role = array('id'=>-1,							 	
					  'description'=>'',
					  'superiorRoleId'=>0);
						
		return $role;
	}
		
	function existsValue($value='', $id=-1, $field='') {
		$exists = false;
		
		if ($value != '' && $field != '') {
			$sql = "SELECT * 
					FROM roles 
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
	
	function setRole($data){
		if (!empty($data)){					
			$this->company_db->set('description',$data['description']);
			$this->company_db->set('superiorRoleId',$data['superiorRoleId']);
			
			if ((float)$data['id'] > 0) {
				$this->company_db->where('id',$data['id']);				
				$this->company_db->update('roles');				
			} else {
				$this->company_db->set('deleted',0);
				$this->company_db->set('deletedDate',NULL);
				$this->company_db->set('deletedUserId',NULL);
				
				$this->company_db->insert('roles');
				$data['id'] = $this->company_db->insert_id();
			}
			
			$error = $this->company_db->error();
			if ((int)$error['code'] != 0) {							
				return false;
			} else {
				if ($data['id'] > 0) {
					$this->company_db->where('roleId',$data['id']);        
					$this->company_db->delete("permissionsByRole");					
					$error = $this->company_db->error();
					if ((int)$error['code'] == 0) {		
						$permissions = $data['permissions'];				
						for($i=0; $i < count($permissions); $i++) {
							$this->company_db->set('roleId',$data['id']);
							$this->company_db->set('permissionId',$permissions[$i]);
							$this->company_db->insert('permissionsByRole');
						}						
					}												
				}
				return true;
			}						
		} else {
			return false;	
		}
	}	
	
	function deleteRole($id=-1){
		if ($this->deleteRoleValid($id)){	
			$this->company_db->set('deleted',1);	
			$this->company_db->set('deletedDate',getCurrentDate());	        
			$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         
			$this->company_db->where('id',$id);				
			$this->company_db->update('roles');		

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
	
	function deleteRoleValid($id=-1){
		if ($id > 0){	
			$allowDelete = true;
			
			if ($allowDelete) {
				$sql = "SELECT * 
						FROM users INNER JOIN rolesByUser
						ON users.id = rolesByUser.userId 
						WHERE users.deleted = 0 
							  AND rolesByUser.roleId = ".$id." 
						LIMIT 0,1";							
				$query = $this->company_db->query($sql);						
				if ($query->num_rows() > 0){   
					$allowDelete = false;
				}
			}
			
			if ($allowDelete) {
				$sql = "SELECT * 
						FROM roles
						WHERE deleted = 0 
							  AND superiorRoleId = ".$id;							
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
	
	function getPermissions($roleId=-1){				
		$permissions = array('list'=>NULL, 
		                     'totalRecords'=>0);
								
		$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM
				(
				SELECT permissions.id, permissions.module, permissions.action, permissions.description, 0 AS active
		        FROM permissions 
				WHERE permissions.deleted = 0 AND permissions.active = 1
				      AND NOT EXISTS(SELECT * FROM permissionsByRole 
				                     WHERE permissionsByRole.roleId = ".$roleId." 
									       AND permissionsByRole.permissionId = permissions.id)
				UNION ALL				
				SELECT permissions.id, permissions.module, permissions.action, permissions.description, 1 AS active
		        FROM permissions INNER JOIN permissionsByRole 
				ON permissions.id = permissionsByRole.permissionId
				WHERE permissions.deleted = 0 AND permissions.active = 1 AND permissionsByRole.roleId = ".$roleId." 
				) AS permissionsRole 
				ORDER BY module, action";		
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){		
			$permissions = array('list'=>$query->result_array(), 
		                         'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $permissions;
	}
					
	function isSuperiorRoleValid($roleId=-1, $superiorRoleId=-1) {
		$validRole = true;
		
		if ($roleId > 0 && $superiorRoleId > 0) {		
			while ($validRole && $superiorRoleId > 0) {
				$sql = "SELECT superiorRoleId
						FROM roles
						WHERE deleted = 0 AND id = ".$superiorRoleId;
				$query = $this->company_db->query($sql);						
				if ($query->num_rows() > 0){  
					$row = $query->row_array();
					
					$superiorRoleId = (float)$row['superiorRoleId']; 
					if ($superiorRoleId == $roleId) {
						$validRole = false;
					}					
				} else {
					$superiorRoleId = 0;
				}
			}
		}
				
		return $validRole;
	}
	
	function hasPermission($rolesId=NULL, $module=NULL, $action=NULL) {
		$validPermission = false;
		
		if ((isset($module) && $module != "") || (isset($action) && $action != "") && isset($rolesId) && ($roleId == "-999" || $rolesId != "")) {
			if ($rolesId == "-999") {
				$validPermission = true;
			} else {
				$filter = "";
				if (isset($module) && $module != "") {
					$filter .= " AND permissions.module = ".$this->company_db->escape($module);
				}
				if (isset($action)  && $action != "") {
					$filter .= " AND permissions.action = ".$this->company_db->escape($action);
				}
				$sql = "SELECT permissions.id 
						FROM permissions INNER JOIN permissionsByRole
						ON permissions.id = permissionsByRole.permissionId 
						WHERE permissions.deleted = 0 AND permissions.active = 1 
						      AND permissionsByRole.roleId IN (".$rolesId.")".
						      $filter;
												      							   
				$query = $this->company_db->query($sql);						
				if ($query->num_rows() > 0){  
					$validPermission = true;
				}
			}
		}
		
		return $validPermission;
	}
	
	function getRolePermissions($rolesId=""){
		$permissions = array();

		if (isset($rolesId) && $rolesId != "") {
			if ($rolesId == "-999") {				
				$sql = "SELECT permissions.module, permissions.action 
						FROM permissions
						WHERE permissions.deleted = 0 AND permissions.active = 1
						ORDER BY permissions.module, permissions.action";				           
			} else {
				$sql = "SELECT DISTINCT permissions.module, permissions.action 
						FROM permissions INNER JOIN permissionsByRole
						ON permissions.id = permissionsByRole.permissionId
						WHERE permissions.deleted = 0 AND permissions.active = 1
							  AND permissionsByRole.roleId IN (".$rolesId.") 
						ORDER BY permissions.module, permissions.action";
			}			
				
			$i=0;
			$query = $this->company_db->query($sql);
			foreach ($query->result() as $row)
			{ 
				$permissions[$i++] = array('module'=>$row->module,
										   'action'=>$row->action);	
				
			}	
		}	
		
		return $permissions;
	}	

	function getInferiorRolesIds($roleId=0) {
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
					
					$auxIds = $this->getInferiorRolesIds($row->id);					
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
}

/* End of file Roles_model.php */
/* Location: ./application/models/Roles_model.php */
