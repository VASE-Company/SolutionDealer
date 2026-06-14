<?php
class Branchoffices_model extends CI_Model{           
    
    private $company_db;         

	function __construct() {        
        parent::__construct();        

        $this->company_db = $this->load->database('default', TRUE); 
    }

   	function getBranchOffices($parameters=NULL){				
		$branchOffices = array('list'=>NULL, 
		                       'totalRecords'=>0);
		
		$filter = "";
		$order = "";
		$limit = "";
		if (isset($parameters)) {
			if (isset($parameters['activeOrIdFilter']) && $parameters['activeOrIdFilter'] == true) {
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND (branchOffices.active = 1 OR branchOffices.id = ".$parameters['idFilter'].")";				
				} else {
					$filter .= " AND branchOffices.active = 1";				
				}				
			} else {			
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND branchOffices.id = ".$parameters['idFilter'];				
				}
			}		
			if (isset($parameters['companyIdFilter']) && $parameters['companyIdFilter'] > 0) {
				$filter .= " AND branchOffices.companyId = ".$parameters['companyIdFilter'];				
			}
			if (isset($parameters['textFilter']) && $parameters['textFilter'] != '') {
				$filter .= " AND branchOffices.description LIKE ".$this->company_db->escape("%".$parameters['textFilter']."%");
			}			
			if (isset($parameters['activeFilter']) && $parameters['activeFilter'] != '') {
				$filter .= " AND branchOffices.active = ".$parameters['activeFilter'];				
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
						$order .= "branchOffices.description ".$auxOrder.", companies.description ".$auxOrder;
					break;	
					case 'com':
						$order .= "companies.description ".$auxOrder.", branchOffices.description ";
					break;			
				}														
			}
		}

		if (isset($parameters) && isset($parameters['fullAccess']) && $parameters['fullAccess'] == false) {												
			$accessFilter = $this->getAccessFilter();				
			if ($accessFilter != "") {
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$accessFilter .= " OR branchOffices.id = ".$parameters['idFilter'];
				}
				$filter .= " AND  (".$accessFilter.")";
			}											
		}
		
		if ($order == "") {
			$order .= "companies.description, branchOffices.description ";
		}	
						
		$sql = "SELECT SQL_CALC_FOUND_ROWS 
		               branchOffices.*, companies.description AS companyDescription   
		        FROM branchOffices INNER JOIN companies ON branchOffices.companyId = companies.id
				WHERE branchOffices.deleted = 0 ".$filter." 
				ORDER BY ".$order.
				$limit;		
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){
			$branchOffices = array('list'=>$query->result_array(), 
		                           'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $branchOffices;
	}

	function getAccessFilter() {
		/*
		if ($this->session->userdata('userRolesId') == "-999") {
			$accessFilter = "";
		} else {						
			//Siempre tengo acceso a la sucursal propia		
			$accessFilter = "branchOffices.id = ".$this->session->userdata('userBranchOfficeId');
			if ($this->my_application->hasPermission("Budgets","BranchOfficeAccess",$this->session->userdata('userRolesId'))) {			
				//Y además tiene acceso a otras sucursales, tiene acceso a todos los presupuestos
				$accessFilter .= " OR branchOffices.id IN (SELECT branchOfficeId FROM branchOfficesByRole WHERE branchOfficesByRole.roleId IN (".$this->session->userdata('userRolesId')."))";
			}
		}
		*/
		$accessFilter = "";

		return $accessFilter;
	}

	function getEmptyBranchOffice() {
		$branchOffice = array('id'=>-1,							 						       				        
				              'description'=>'',
				          	  'companyId'=>0,
				              'active'=>1);
						
		return $branchOffice;
	}

	function existsValue($value='', $id=-1, $field='', $extraConditions='') {
		$exists = false;
		
		if ($value != '' && $field != '') {
			$sql = "SELECT * 
					FROM branchOffices 
					WHERE deleted = 0 
					      AND id <> ".$id." 
						  AND ".$field." = ".$this->company_db->escape($value);	
			if (trim($extraConditions) != "") {
				$sql .= " AND ".$extraConditions;				
			}
						  					 			
			$query = $this->company_db->query($sql);						
			if ($query->num_rows() > 0){   
				$exists = true;
			}
		}
		
		return $exists;
	}
	
	function setBranchOffice($data){
		if (!empty($data)){				
			
			$this->company_db->set('description',$data['description']);		
			$this->company_db->set('active',(int)$data['active']);
			
			if ((float)$data['id'] > 0) {
				$this->company_db->where('id',$data['id']);				
				$this->company_db->update('branchOffices');				
			} else {				
				$this->company_db->set('companyId',$data['companyId']);

				$this->company_db->set('deleted',0);
				$this->company_db->set('deletedDate',NULL);
				$this->company_db->set('deletedUserId',NULL);
				
				$this->company_db->insert('branchOffices');
				$data['id'] = $this->company_db->insert_id();
			}
			
			$error = $this->company_db->error();
			if ((int)$error['code'] != 0) {							
				return false;
			} else {		

				if ($data['id'] > 0) {
					$this->company_db->where('branchOfficeId',$data['id']);        
					$this->company_db->delete("sectorsByBranchOffice");					
					$error = $this->company_db->error();
					if ((int)$error['code'] == 0) {		
						$sectors = $data['sectors'];				
						for($i=0; $i < count($sectors); $i++) {
							$this->company_db->set('branchOfficeId',$data['id']);
							$this->company_db->set('sectorId',$sectors[$i]);
							$this->company_db->insert('sectorsByBranchOffice');
						}						
					}												
				}		
				
				return true;
			}						
		} else {
			return false;	
		}
	}	

	function deleteBranchOffice($id=-1){
		if ($this->deleteBranchOfficeValid($id)){	
			$this->company_db->set('deleted',1);	
			$this->company_db->set('deletedDate',getCurrentDate());	        
			$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         
			$this->company_db->where('id',$id);				
			$this->company_db->update('branchOffices');		

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

	function deleteBranchOfficeValid($id=-1){
		if ($id > 0){	
			$allowDelete = true;

			if ($allowDelete) {						
				$sql = "SELECT * 
						FROM users 						
						WHERE users.deleted = 0 AND 
						      users.branchOfficeId = ".$id." 
						LIMIT 0,1";							
				$query = $this->company_db->query($sql);						
				if ($query->num_rows() > 0){   
					$allowDelete = false;
				}							
			}

			if ($allowDelete) {						
				$sql = "SELECT * 
						FROM orders 						
						WHERE orders.deleted = 0 AND 
						      orders.branchOfficeId = ".$id." 
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

	function getSectors($branchOfficeId=-1){				
		$sectors = array('list'=>NULL, 
		                 'totalRecords'=>0);
				
		$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM
				(
				SELECT sectors.id, sectors.description, 0 AS active
		        FROM sectors 
				WHERE sectors.deleted = 0 
				      AND NOT EXISTS(SELECT * FROM sectorsByBranchOffice 
				                     WHERE sectorsByBranchOffice.branchOfficeId = ".$branchOfficeId." 
									       AND sectorsByBranchOffice.sectorId = sectors.id)
				UNION ALL				
				SELECT sectors.id, sectors.description, 1 AS active
		        FROM sectors INNER JOIN sectorsByBranchOffice 
				ON sectors.id = sectorsByBranchOffice.sectorId
				WHERE sectors.deleted = 0 AND sectorsByBranchOffice.branchOfficeId = ".$branchOfficeId." 
				) AS sectorsBranchOffice 
				ORDER BY description";	
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){		
			$sectors = array('list'=>$query->result_array(), 
		                     'totalRecords'=>$queryTotal->row()->totalRecords);
		}	

		return $sectors;
	}
		
}

/* End of file Branchoffices_model.php */
/* Location: ./application/models/Branchoffices_model.php */