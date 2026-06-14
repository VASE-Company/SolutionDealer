<?php
class Insurancecompanies_model extends CI_Model{           
    
    private $company_db;         

	function __construct() {        
        parent::__construct();        
        
        if ((int)$this->session->userdata('companyId') > 0) {
        	$this->company_db = $this->load->database('company_db_'.$this->session->userdata('companyId'), TRUE); 
        }
    }

   	function getInsuranceCompanies($parameters=NULL){				
		$insuranceCompanies = array('list'=>NULL, 
		                            'totalRecords'=>0);
		
		$filter = "";
		$order = "";
		$limit = "";
		if (isset($parameters)) {
			if (isset($parameters['activeOrIdFilter']) && $parameters['activeOrIdFilter'] == true) {
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND (insuranceCompanies.active = 1 OR insuranceCompanies.id = ".$parameters['idFilter'].")";				
				} else {
					$filter .= " AND insuranceCompanies.active = 1";				
				}				
			} else {			
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND insuranceCompanies.id = ".$parameters['idFilter'];				
				}
			}		
			if (isset($parameters['textFilter']) && $parameters['textFilter'] != '') {
				$filter .= " AND insuranceCompanies.description LIKE ".$this->company_db->escape("%".$parameters['textFilter']."%");
			}			
			if (isset($parameters['activeFilter']) && $parameters['activeFilter'] != '') {
				$filter .= " AND insuranceCompanies.active = ".$parameters['activeFilter'];				
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
						$order .= "insuranceCompanies.description ".$auxOrder;
					break;				
				}														
			}
		}
		
		if ($order == "") {
			$order .= "insuranceCompanies.description ";
		}	
						
		$sql = "SELECT SQL_CALC_FOUND_ROWS 
		               insuranceCompanies.*  
		        FROM insuranceCompanies 
				WHERE insuranceCompanies.deleted = 0 ".$filter." 
				ORDER BY ".$order.
				$limit;		
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){
			$insuranceCompanies = array('list'=>$query->result_array(), 
		                                'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $insuranceCompanies;
	}

	function getEmptyInsuranceCompany() {
		$insuranceCompany = array('id'=>-1,							 						       				        
				                  'description'=>'',	
				                  'discountPercent'=>0,				          	  
				                  'active'=>1);
						
		return $insuranceCompany;
	}

	function existsValue($value='', $id=-1, $field='') {
		$exists = false;
		
		if ($value != '' && $field != '') {
			$sql = "SELECT * 
					FROM insuranceCompanies 
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
	
	function setInsuranceCompany($data){
		if (!empty($data)){				
			
			$this->company_db->set('description',$data['description']);			
			$this->company_db->set('discountPercent',$data['discountPercent']);		
			$this->company_db->set('active',(int)$data['active']);
			
			if ((float)$data['id'] > 0) {
				$this->company_db->where('id',$data['id']);				
				$this->company_db->update('insuranceCompanies');				
			} else {				
				$this->company_db->set('deleted',0);
				$this->company_db->set('deletedDate',NULL);
				$this->company_db->set('deletedUserId',NULL);
				
				$this->company_db->insert('insuranceCompanies');
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

	function deleteInsuranceCompany($id=-1){
		if ($this->deleteInsuranceCompanyValid($id)){	
			$this->company_db->set('deleted',1);	
			$this->company_db->set('deletedDate',getCurrentDate());	        
			$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         
			$this->company_db->where('id',$id);				
			$this->company_db->update('insuranceCompanies');		

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

	function deleteInsuranceCompanyValid($id=-1){
		if ($id > 0){	
			$allowDelete = true;

			if ($allowDelete) {						
				$sql = "SELECT * 
						FROM budgets 						
						WHERE budgets.deleted = 0 AND 
						      budgets.insuranceCompanyId = ".$id." 
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

/* End of file Insurancecompanies_model.php */
/* Location: ./application/models/Insurancecompanies_model.php */