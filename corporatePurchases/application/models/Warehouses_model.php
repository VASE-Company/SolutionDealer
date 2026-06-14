<?php
class Warehouses_model extends CI_Model{           
    
    private $company_db;         

	function __construct() {        
        parent::__construct();        

        $this->company_db = $this->load->database('default', TRUE); 
    } 

   	function getWarehouses($parameters=NULL){				
		$warehouses = array('list'=>NULL, 
		                    'totalRecords'=>0);
		
		$filter = "";
		$order = "";
		$limit = "";
		if (isset($parameters)) {
			if (isset($parameters['activeOrIdFilter']) && $parameters['activeOrIdFilter'] == true) {
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND (warehouses.active = 1 OR warehouses.id = ".$parameters['idFilter'].")";				
				} else {
					$filter .= " AND warehouses.active = 1";				
				}				
			} else {			
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND warehouses.id = ".$parameters['idFilter'];				
				}
			}			
			if (isset($parameters['companyIdFilter']) && $parameters['companyIdFilter'] > 0) {
				$filter .= " AND (";
				$filter .= "EXISTS(SELECT id FROM companies WHERE companies.id = ".$parameters['companyIdFilter']." AND companies.warehouseId = warehouses.id)";					
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {				
					$filter .= " OR warehouses.id = ".$parameters['idFilter'];			
			    }
				$filter .= ")";
			}	
			if (isset($parameters['textFilter']) && $parameters['textFilter'] != '') {
				$filter .= " AND warehouses.description LIKE ".$this->company_db->escape("%".$parameters['textFilter']."%");
			}			
			if (isset($parameters['activeFilter']) && $parameters['activeFilter'] != '') {
				$filter .= " AND warehouses.active = ".$parameters['activeFilter'];				
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
						$order .= "warehouses.description ".$auxOrder;
					break;	
					case 'id':
						$order .= "warehouses.id ".$auxOrder;
					break;				
				}														
			}
		}
		
		if ($order == "") {
			$order .= "warehouses.description ";
		}	
						
		$sql = "SELECT SQL_CALC_FOUND_ROWS 
		               warehouses.*  
		        FROM warehouses 
				WHERE warehouses.deleted = 0 ".$filter." 
				ORDER BY ".$order.
				$limit;		
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){
			$warehouses = array('list'=>$query->result_array(), 
		                     'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $warehouses;
	}

	function getEmptyWarehouse() {
		$warehouse = array('id'=>-1,							 						       				        
				        'description'=>'',					        
				        'active'=>1);
						
		return $warehouse;
	}

	function existsValue($value='', $id=-1, $field='') {
		$exists = false;
		
		if ($value != '' && $field != '') {
			$sql = "SELECT * 
					FROM warehouses 
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
	
	function setWarehouse($data){
		if (!empty($data)){				
			
			$this->company_db->set('description',$data['description']);				
			$this->company_db->set('active',(int)$data['active']);
			
			if ((float)$data['id'] > 0) {
				$this->company_db->where('id',$data['id']);				
				$this->company_db->update('warehouses');				
			} else {				
				$this->company_db->set('deleted',0);
				$this->company_db->set('deletedDate',NULL);
				$this->company_db->set('deletedUserId',NULL);
				
				$this->company_db->insert('warehouses');
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

	function deleteWarehouse($id=-1){
		if ($this->deleteWarehouseValid($id)){	
			$this->company_db->set('deleted',1);	
			$this->company_db->set('deletedDate',getCurrentDate());	        
			$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         
			$this->company_db->where('id',$id);				
			$this->company_db->update('warehouses');		

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

	function deleteWarehouseValid($id=-1){
		if ($id > 0){	
			$allowDelete = true;
			
			if ($allowDelete) {						
				$sql = "SELECT * 
						FROM companies 						
						WHERE companies.deleted = 0 AND 
						      companies.warehouseId = ".$id." 
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
						      orders.warehouseId = ".$id." 
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
						      stockMovements.warehouseId = ".$id." 
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
						      bills.warehouseId = ".$id." 
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
	
	function getFirstCompanyByWarehouse($warehouseId=-1) {
		$companyId = 0;

		if ($warehouseId > 0) {
			$sql = "SELECT id 
		        	FROM companies   
					WHERE companies.deleted = 0 AND companies.warehouseId = ".$warehouseId."
					LIMIT 1";					
			
			$query = $this->company_db->query($sql);			
			if ($query->num_rows() > 0){
				$row = $query->row_array();

				$companyId = $row['id'];				
			}
		}	

		return $companyId;	
	}
}

/* End of file Warehouses_model.php */
/* Location: ./application/models/Warehouses_model.php */