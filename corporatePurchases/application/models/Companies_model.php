<?php
class Companies_model extends CI_Model{           
    
    private $company_db;         

	function __construct() {        
        parent::__construct();        

        $this->company_db = $this->load->database('default', TRUE); 
    } 

   	function getCompanies($parameters=NULL){				
		$companies = array('list'=>NULL, 
		                 'totalRecords'=>0);
		
		$filter = "";
		$order = "";
		$limit = "";
		if (isset($parameters)) {
			if (isset($parameters['activeOrIdFilter']) && $parameters['activeOrIdFilter'] == true) {
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND (companies.active = 1 OR companies.id = ".$parameters['idFilter'].")";				
				} else {
					$filter .= " AND companies.active = 1";				
				}				
			} else {			
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND companies.id = ".$parameters['idFilter'];				
				}
			}				
			if (isset($parameters['textFilter']) && $parameters['textFilter'] != '') {
				$filter .= " AND companies.description LIKE ".$this->company_db->escape("%".$parameters['textFilter']."%");
			}			
			if (isset($parameters['activeFilter']) && $parameters['activeFilter'] != '') {
				$filter .= " AND companies.active = ".$parameters['activeFilter'];				
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
						$order .= "companies.description ".$auxOrder;
					break;	
					case 'id':
						$order .= "companies.id ".$auxOrder;
					break;				
				}														
			}
		}
		
		if ($order == "") {
			$order .= "companies.description ";
		}	
						
		$sql = "SELECT SQL_CALC_FOUND_ROWS 
		               companies.*, 
		               warehouses.description AS warehouseDescription   
		        FROM companies LEFT JOIN warehouses ON companies.warehouseId = warehouses.id 
				WHERE companies.deleted = 0 ".$filter." 
				ORDER BY ".$order.
				$limit;		
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){
			$companies = array('list'=>$query->result_array(), 
		                     'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $companies;
	}

	function getEmptyCompany() {
		$company = array('id'=>-1,							 						       				        
				        'description'=>'',				        			          	 
				        'active'=>1,
				        'warehouseId'=>0);
						
		return $company;
	}

	function existsValue($value='', $id=-1, $field='') {
		$exists = false;
		
		if ($value != '' && $field != '') {
			$sql = "SELECT * 
					FROM companies 
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
	
	function setCompany($data){
		if (!empty($data)){				
			
			$this->company_db->set('description',$data['description']);			
			$this->company_db->set('active',(int)$data['active']);
			$this->company_db->set('warehouseId',$data['warehouseId']);
			
			if ((float)$data['id'] > 0) {
				$this->company_db->where('id',$data['id']);				
				$this->company_db->update('companies');				
			} else {				
				$this->company_db->set('deleted',0);
				$this->company_db->set('deletedDate',NULL);
				$this->company_db->set('deletedUserId',NULL);
				
				$this->company_db->insert('companies');
				$data['id'] = $this->company_db->insert_id();
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

	function deleteCompany($id=-1){
		if ($this->deleteCompanyValid($id)){	
			$this->company_db->set('deleted',1);	
			$this->company_db->set('deletedDate',getCurrentDate());	        
			$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         
			$this->company_db->where('id',$id);				
			$this->company_db->update('companies');		

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

	function deleteCompanyValid($id=-1){
		if ($id > 0){	
			$allowDelete = true;
			
			if ($allowDelete) {						
				$sql = "SELECT * 
						FROM branchOffices 						
						WHERE branchOffices.deleted = 0 AND 
						      branchOffices.companyId = ".$id." 
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
						      stockMovements.companyId = ".$id." 
						LIMIT 0,1";							
				$query = $this->company_db->query($sql);						
				if ($query->num_rows() > 0){   
					$allowDelete = false;
				}						
			}

			if ($allowDelete) {						
				$sql = "SELECT * 
						FROM bills 						
						WHERE bills.deleted = 0 AND 
						      bills.companyId = ".$id." 
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
		
}

/* End of file Companies_model.php */
/* Location: ./application/models/Companies_model.php */