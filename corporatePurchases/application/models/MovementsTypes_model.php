<?php
class MovementsTypes_model extends CI_Model{           
    
    private $company_db;         

	function __construct() {        
        parent::__construct();        

        $this->company_db = $this->load->database('default', TRUE); 
    } 

   	function getMovementsTypes($parameters=NULL){				
		$movementsTypes = array('list'=>NULL, 
		                  'totalRecords'=>0);
		
		$filter = "";
		$order = "";
		$limit = "";
		if (isset($parameters)) {
			if (isset($parameters['activeOrIdFilter']) && $parameters['activeOrIdFilter'] == true) {
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND (movementsTypes.active = 1 OR movementsTypes.id = ".$parameters['idFilter'].")";				
				} else {
					$filter .= " AND movementsTypes.active = 1";				
				}				
			} else {			
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND movementsTypes.id = ".$parameters['idFilter'];				
				}
			}		
			if (isset($parameters['textFilter']) && $parameters['textFilter'] != '') {
				$filter .= " AND movementsTypes.description LIKE ".$this->company_db->escape("%".$parameters['textFilter']."%");
			}			
			if (isset($parameters['activeFilter']) && $parameters['activeFilter'] != '') {
				$filter .= " AND movementsTypes.active = ".$parameters['activeFilter'];				
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
						$order .= "movementsTypes.description ".$auxOrder;
					break;				
				}														
			}
		}

		if (!(isset($parameters) && isset($parameters['includeInternalFilter']) && $parameters['includeInternalFilter'] == true)) {
			$filter .= " AND movementsTypes.internal = 0";
		}
		
		if ($order == "") {
			$order .= "movementsTypes.description ";
		}	
						
		$sql = "SELECT SQL_CALC_FOUND_ROWS 
		               movementsTypes.*,
		               IF(movementsTypes.input = 1,'Entrada','Salida') AS typeDescription 
		        FROM movementsTypes 
				WHERE movementsTypes.deleted = 0 ".$filter." 
				ORDER BY ".$order.
				$limit;		
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){
			$movementsTypes = array('list'=>$query->result_array(), 
		                      'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $movementsTypes;
	}

	function getEmptyMovementType() {
		$movementType = array('id'=>-1,							 						       				        
				        'description'=>'',				        			          	 
				        'active'=>1,
				    	'input'=>1,
				    	'internal'=>0,
				    	'system'=>0);
						
		return $movementType;
	}

	function existsValue($value='', $id=-1, $field='') {
		$exists = false;
		
		if ($value != '' && $field != '') {
			$sql = "SELECT * 
					FROM movementsTypes 
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
	
	function setMovementType($data){
		if (!empty($data)){				
			
			$this->company_db->set('description',$data['description']);			
			$this->company_db->set('active',(int)$data['active']);
			$this->company_db->set('input',(int)$data['input']);			
			
			if ((float)$data['id'] > 0) {
				$this->company_db->where('id',$data['id']);				
				$this->company_db->where('internal',0);
				$this->company_db->where('system',0);				
				$this->company_db->update('movementsTypes');				
			} else {				
				$this->company_db->set('internal',0);
				$this->company_db->set('system',0);
				$this->company_db->set('deleted',0);
				$this->company_db->set('deletedDate',NULL);
				$this->company_db->set('deletedUserId',NULL);
				
				$this->company_db->insert('movementsTypes');
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

	function deleteMovementType($id=-1){
		if ($this->deleteMovementTypeValid($id)){	
			$this->company_db->set('deleted',1);	
			$this->company_db->set('deletedDate',getCurrentDate());	        
			$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         
			$this->company_db->where('id',$id);				
			$this->company_db->where('internal',0);
			$this->company_db->where('system',0);	
			$this->company_db->update('movementsTypes');		

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

	function deleteMovementTypeValid($id=-1){
		if ($id > 0){	
			$allowDelete = true;

			if ($allowDelete) {						
				$sql = "SELECT * 
						FROM stockMovements 						
						WHERE stockMovements.deleted = 0 AND 
						      stockMovements.typeId = ".$id." 
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

/* End of file MovementsTypes_model.php */
/* Location: ./application/models/MovementsTypes_model.php */