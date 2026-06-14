<?php
class Sectors_model extends CI_Model{           
    
    private $company_db;         

	function __construct() {        
        parent::__construct();        

        $this->company_db = $this->load->database('default', TRUE); 
    } 

   	function getSectors($parameters=NULL){				
		$sectors = array('list'=>NULL, 
		                 'totalRecords'=>0);
		
		$filter = "";
		$order = "";
		$limit = "";
		if (isset($parameters)) {
			if (isset($parameters['activeOrIdFilter']) && $parameters['activeOrIdFilter'] == true) {
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND (sectors.active = 1 OR sectors.id = ".$parameters['idFilter'].")";				
				} else {
					$filter .= " AND sectors.active = 1";				
				}				
			} else {			
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND sectors.id = ".$parameters['idFilter'];				
				}
			}	
			if (isset($parameters['branchOfficeIdFilter']) && $parameters['branchOfficeIdFilter'] > 0) {
				$filter .= " AND EXISTS(SELECT id
				                        FROM sectorsByBranchOffice 
				                        WHERE sectorsByBranchOffice.branchOfficeId = ".$parameters['branchOfficeIdFilter']." 
				                              AND sectorsByBranchOffice.sectorId = sectors.id 
			                            )";
			}	
			if (isset($parameters['textFilter']) && $parameters['textFilter'] != '') {
				$filter .= " AND sectors.description LIKE ".$this->company_db->escape("%".$parameters['textFilter']."%");
			}			
			if (isset($parameters['activeFilter']) && $parameters['activeFilter'] != '') {
				$filter .= " AND sectors.active = ".$parameters['activeFilter'];				
			}
			if (isset($parameters['allowOrdersFilter']) && $parameters['allowOrdersFilter'] != '') {
				$filter .= " AND sectors.allowOrders = ".$parameters['allowOrdersFilter'];				
			}
			if (isset($parameters['allowPaymentsFilter']) && $parameters['allowPaymentsFilter'] != '') {
				$filter .= " AND sectors.allowPayments = ".$parameters['allowPaymentsFilter'];				
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
						$order .= "sectors.description ".$auxOrder;
					break;				
				}														
			}
		}
		
		if ($order == "") {
			$order .= "sectors.description ";
		}	
						
		$sql = "SELECT SQL_CALC_FOUND_ROWS 
		               sectors.*  
		        FROM sectors 
				WHERE sectors.deleted = 0 ".$filter." 
				ORDER BY ".$order.
				$limit;						
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){
			$sectors = array('list'=>$query->result_array(), 
		                     'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $sectors;
	}

	function getEmptySector() {
		$sector = array('id'=>-1,							 						       				        
				        'description'=>'',				        			          	 
				        'allowPayments'=>0,
						'allowOrders'=>0,
				        'active'=>1);
						
		return $sector;
	}

	function existsValue($value='', $id=-1, $field='') {
		$exists = false;
		
		if ($value != '' && $field != '') {
			$sql = "SELECT * 
					FROM sectors 
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
	
	function setSector($data){
		if (!empty($data)){				
			
			$this->company_db->set('description',$data['description']);			
			$this->company_db->set('allowPayments',(int)$data['allowPayments']);
			$this->company_db->set('allowOrders',(int)$data['allowOrders']);
			$this->company_db->set('active',(int)$data['active']);			
			
			if ((float)$data['id'] > 0) {
				$this->company_db->where('id',$data['id']);				
				$this->company_db->update('sectors');				
			} else {				
				$this->company_db->set('deleted',0);
				$this->company_db->set('deletedDate',NULL);
				$this->company_db->set('deletedUserId',NULL);
				
				$this->company_db->insert('sectors');
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

	function deleteSector($id=-1){
		if ($this->deleteSectorValid($id)){	
			$this->company_db->set('deleted',1);	
			$this->company_db->set('deletedDate',getCurrentDate());	        
			$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         
			$this->company_db->where('id',$id);				
			$this->company_db->update('sectors');		

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

	function deleteSectorValid($id=-1){
		if ($id > 0){	
			$allowDelete = true;

			if ($allowDelete) {						
				$sql = "SELECT * 
						FROM users 						
						WHERE users.deleted = 0 AND 
						      users.sectorId = ".$id." 
						LIMIT 0,1";							
				$query = $this->company_db->query($sql);						
				if ($query->num_rows() > 0){   
					$allowDelete = false;
				}							
			}
			
			if ($allowDelete) {						
				$sql = "SELECT sectorsByBranchOffice.* 
						FROM sectorsByBranchOffice INNER JOIN branchOffices						
						ON sectorsByBranchOffice.branchOfficeId = branchOffices.id
						WHERE branchOffices.deleted = 0 AND 
						      sectorsByBranchOffice.sectorId = ".$id." 
						LIMIT 0,1";							
				$query = $this->company_db->query($sql);						
				if ($query->num_rows() > 0){   
					$allowDelete = false;
				}						
			}			

			if ($allowDelete) {						
				$sql = "SELECT * 
						FROM orders 						
						WHERE orders.deleted = 0 AND orders.sectorId = ".$id." 
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

/* End of file Sectors_model.php */
/* Location: ./application/models/Sectors_model.php */