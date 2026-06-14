<?php
class Budgetresponses_model extends CI_Model{           
    
    private $company_db;         

	function __construct() {        
        parent::__construct();        
        
        if ((int)$this->session->userdata('companyId') > 0) {
        	$this->company_db = $this->load->database('company_db_'.$this->session->userdata('companyId'), TRUE); 
        }
    }

   	function getBudgetResponses($parameters=NULL){				
		$budgetResponses = array('list'=>NULL, 
		                         'totalRecords'=>0);
		
		$filter = "";
		$order = "";
		$limit = "";
		if (isset($parameters)) {
			if (isset($parameters['activeOrIdFilter']) && $parameters['activeOrIdFilter'] == true) {
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND (budgetResponses.active = 1 OR budgetResponses.id = ".$parameters['idFilter'].")";				
				} else {
					$filter .= " AND budgetResponses.active = 1";				
				}				
			} else {			
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND budgetResponses.id = ".$parameters['idFilter'];				
				}
			}		
			if (isset($parameters['textFilter']) && $parameters['textFilter'] != '') {
				$filter .= " AND budgetResponses.description LIKE ".$this->company_db->escape("%".$parameters['textFilter']."%");
			}			
			if (isset($parameters['activeFilter']) && $parameters['activeFilter'] != '') {
				$filter .= " AND budgetResponses.active = ".$parameters['activeFilter'];				
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
						$order .= "budgetResponses.description ".$auxOrder;
					break;				
				}														
			}
		}
		
		if ($order == "") {
			$order .= "budgetResponses.description ";
		}	
						
		$sql = "SELECT SQL_CALC_FOUND_ROWS 
		               budgetResponses.*  
		        FROM budgetResponses 
				WHERE budgetResponses.deleted = 0 ".$filter." 
				ORDER BY ".$order.
				$limit;		
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){
			
			$auxBudgetResponses = $query->result_array();

			if (isset($parameters) && isset($parameters['fullData']) && $parameters['fullData'] == true) {
				for ($i=0; $i < count($auxBudgetResponses); $i++) {						
					$auxBudgetResponses[$i]['hasSubresponses'] = $this->_getHasSubresponses($auxBudgetResponses[$i]['id']);						
				}
			}

			$budgetResponses = array('list'=>$auxBudgetResponses, 
		                             'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $budgetResponses;
	}	
	
	function _getHasSubresponses($budgetResponseId=0) {		
		$sql = "SELECT * 
				FROM budgetSubresponses   
				WHERE deleted = 0 
				      AND responseId = ".$budgetResponseId;							
		$query = $this->company_db->query($sql);						
		
		$hasSubresponses = ($query->num_rows() > 0);

		return $hasSubresponses;
	}
		
}

/* End of file Budgetresponses_model.php */
/* Location: ./application/models/Budgetresponses_model.php */