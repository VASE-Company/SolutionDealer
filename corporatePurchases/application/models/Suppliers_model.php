<?php
class Suppliers_model extends CI_Model{    

	private $company_db;         

	function __construct() {        
        parent::__construct();        

        $this->company_db = $this->load->database('default', TRUE);         
    }      
    
   	function getSuppliers($parameters=NULL){				
		$suppliers = array('list'=>NULL, 
		                   'totalRecords'=>0);
		
		$filter = "";
		$order = "";
		$limit = "";
		if (isset($parameters)) {
			if (isset($parameters['activeOrIdFilter']) && $parameters['activeOrIdFilter'] == true) {
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND (suppliers.active = 1 OR suppliers.id = ".$parameters['idFilter'].")";				
				} else {
					$filter .= " AND suppliers.active = 1";				
				}				
			} else {			
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND suppliers.id = ".$parameters['idFilter'];				
				}
			}		
			if (isset($parameters['codeFilter']) && $parameters['codeFilter'] != '') {
				$filter .= " AND suppliers.code = ".$this->company_db->escape($parameters['codeFilter']);				
			}
			if (isset($parameters['textFilter']) && $parameters['textFilter'] != '') {
				$filter .= " AND ";
				$filter .= "(suppliers.code LIKE ".$this->company_db->escape("%".$parameters['textFilter']."%");
				$filter .= " OR ";
				$filter .= "suppliers.tradeName LIKE ".$this->company_db->escape("%".$parameters['textFilter']."%");
				$filter .= " OR ";
				$filter .= "suppliers.businessName LIKE ".$this->company_db->escape("%".$parameters['textFilter']."%").")";
			}
			if (isset($parameters['activeFilter']) && $parameters['activeFilter'] != '') {
				$filter .= " AND suppliers.active = ".$parameters['activeFilter'];				
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
					case 'cod':
						$order .= "suppliers.code ".$auxOrder;
					break;					
					case 'tn':
						$order .= "suppliers.tradeName ".$auxOrder;
					break;	
					case 'bn':
						$order .= "suppliers.businessName ".$auxOrder;
					break;						
				}														
			}
		}
		
		if ($order == "") {
			$order .= "suppliers.tradeName ";
		}	
						
		$sql = "SELECT SQL_CALC_FOUND_ROWS 
		               suppliers.*
		        FROM suppliers 
				WHERE suppliers.deleted = 0 ".$filter." 
				ORDER BY ".$order.
				$limit;		
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){
			$suppliers = array('list'=>$query->result_array(), 
		                      'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $suppliers;
	}
		
	function getEmptySupplier() {
		$supplier = array('id'=>-1,							 						       
				         'code'=>'',
				         'tradeName'=>'',
				         'businessName'=>'',		         
				         'active'=>1);
						
		return $supplier;
	}
	
	function existsValue($value='', $id=-1, $field='') {
		$exists = false;
		
		if ($value != '' && $field != '') {
			$sql = "SELECT * 
					FROM suppliers 
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
	
	function setSupplier($data){
		if (!empty($data)){				

			$this->company_db->set('code',$data['code']);
			$this->company_db->set('tradeName',str_replace('"',"",$data['tradeName']));
			$this->company_db->set('businessName',str_replace('"',"",$data['businessName']));			
			$this->company_db->set('active',(int)$data['active']);
			
			if ((float)$data['id'] > 0) {
				$this->company_db->where('id',$data['id']);				
				$this->company_db->update('suppliers');				
			} else {				
				$this->company_db->set('deleted',0);
				$this->company_db->set('deletedDate',NULL);
				$this->company_db->set('deletedUserId',NULL);
				
				$this->company_db->insert('suppliers');
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
	
	function deleteSupplier($id=-1){
		if ($this->deleteSupplierValid($id)){	
			$this->company_db->set('deleted',1);	
			$this->company_db->set('deletedDate',getCurrentDate());	        
			$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         
			$this->company_db->where('id',$id);				
			$this->company_db->update('suppliers');		

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

	function deleteSupplierValid($id=-1){
		if ($id > 0){	
			$allowDelete = true;	

			if ($allowDelete) {					
				$sql = "SELECT * 
						FROM bills 						
						WHERE bills.deleted = 0 AND 
						      bills.supplierId = ".$id." 
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

/* End of file Suppliers_model.php */
/* Location: ./application/models/Suppliers_model.php */