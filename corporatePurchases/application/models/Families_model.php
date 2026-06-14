<?php
class Families_model extends CI_Model{           
    
    private $company_db;         

	function __construct() {        
        parent::__construct();        

        $this->company_db = $this->load->database('default', TRUE); 
    } 

   	function getFamilies($parameters=NULL){				
		$families = array('list'=>NULL, 
		                  'totalRecords'=>0);
		
		$filter = "";
		$order = "";
		$limit = "";
		if (isset($parameters)) {
			if (isset($parameters['activeOrIdFilter']) && $parameters['activeOrIdFilter'] == true) {
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND (families.active = 1 OR families.id = ".$parameters['idFilter'].")";				
				} else {
					$filter .= " AND families.active = 1";				
				}				
			} else {			
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND families.id = ".$parameters['idFilter'];				
				}
			}		
			if (isset($parameters['textFilter']) && $parameters['textFilter'] != '') {
				$filter .= " AND families.description LIKE ".$this->company_db->escape("%".$parameters['textFilter']."%");
			}			
			if (isset($parameters['activeFilter']) && $parameters['activeFilter'] != '') {
				$filter .= " AND families.active = ".$parameters['activeFilter'];				
			}
			if (isset($parameters['affectsStockFilter']) && $parameters['affectsStockFilter'] != '') {
				//HABILITAR USA STOCK
				$filter .= " AND families.affectsStock = ".$parameters['affectsStockFilter'];				
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
						$order .= "families.description ".$auxOrder;
					break;				
				}														
			}
		}
		
		if ($order == "") {
			$order .= "families.description ";
		}	
						
		$sql = "SELECT SQL_CALC_FOUND_ROWS 
		               families.*  
		        FROM families 
				WHERE families.deleted = 0 ".$filter." 
				ORDER BY ".$order.
				$limit;		
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){
			$families = array('list'=>$query->result_array(), 
		                      'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $families;
	}

	function getEmptyFamily() {
		$family = array('id'=>-1,							 						       				        
				        'description'=>'',	
				        'affectsStock'=>1,	
				        'isInsuranceItem'=>0,		        			          	 
				        'active'=>1);
						
		return $family;
	}

	function existsValue($value='', $id=-1, $field='') {
		$exists = false;
		
		if ($value != '' && $field != '') {
			$sql = "SELECT * 
					FROM families 
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
	
	function setFamily($data){
		if (!empty($data)){				
			
			$this->company_db->set('description',$data['description']);		
			//HABILITAR USA STOCK			
			$this->company_db->set('affectsStock',(int)$data['affectsStock']);		
			$this->company_db->set('isInsuranceItem',(int)$data['isInsuranceItem']);					
			$this->company_db->set('active',(int)$data['active']);
			
			if ((float)$data['id'] > 0) {
				$this->company_db->where('id',$data['id']);				
				$this->company_db->update('families');				
			} else {				
				$this->company_db->set('deleted',0);
				$this->company_db->set('deletedDate',NULL);
				$this->company_db->set('deletedUserId',NULL);
				
				$this->company_db->insert('families');
				$data['id'] = $this->company_db->insert_id();
			}
			
			$error = $this->company_db->error();
			if ((int)$error['code'] != 0) {							
				return false;
			} else {			
				$this->load->model('orders_model','orders');	

				$this->orders->updateOrdersOfInsurance();

				return true;
			}						
		} else {
			return false;	
		}
	}	

	function deleteFamily($id=-1){
		if ($this->deleteFamilyValid($id)){	
			$this->company_db->set('deleted',1);	
			$this->company_db->set('deletedDate',getCurrentDate());	        
			$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         
			$this->company_db->where('id',$id);				
			$this->company_db->update('families');		

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

	function deleteFamilyValid($id=-1){
		if ($id > 0){	
			$allowDelete = true;

			if ($allowDelete) {						
				$sql = "SELECT * 
						FROM articles 						
						WHERE articles.deleted = 0 AND 
						      articles.familyId = ".$id." 
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

/* End of file Families_model.php */
/* Location: ./application/models/Families_model.php */