<?php
class Budgetsubresponses_model extends CI_Model{           
    
    private $company_db;         

	function __construct() {        
        parent::__construct();        
        
        if ((int)$this->session->userdata('companyId') > 0) {
        	$this->company_db = $this->load->database('company_db_'.$this->session->userdata('companyId'), TRUE); 
        }
    }

   	function getBudgetSubresponses($parameters=NULL){				
		$budgetSubresponses = array('list'=>NULL, 
		                            'totalRecords'=>0);
		
		$filter = "";
		$order = "";
		$limit = "";
		if (isset($parameters)) {
			if (isset($parameters['activeOrIdFilter']) && $parameters['activeOrIdFilter'] == true) {
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND (budgetSubresponses.active = 1 OR budgetSubresponses.id = ".$parameters['idFilter'].")";				
				} else {
					$filter .= " AND budgetSubresponses.active = 1";				
				}				
			} else {			
				if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
					$filter .= " AND budgetSubresponses.id = ".$parameters['idFilter'];				
				}
			}		
			if (isset($parameters['textFilter']) && $parameters['textFilter'] != '') {
				$filter .= " AND budgetSubresponses.description LIKE ".$this->company_db->escape("%".$parameters['textFilter']."%");
			}			
			if (isset($parameters['activeFilter']) && $parameters['activeFilter'] != '') {
				$filter .= " AND budgetSubresponses.active = ".$parameters['activeFilter'];				
			}
			if (isset($parameters['responseIdFilter']) && $parameters['responseIdFilter'] > 0) {
				$filter .= " AND budgetSubresponses.responseId= ".$parameters['responseIdFilter'];				
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
						$order .= "budgetSubresponses.description ".$auxOrder;
					break;				
				}														
			}
		}
		
		if ($order == "") {
			$order .= "budgetSubresponses.description ";
		}	
						
		$sql = "SELECT SQL_CALC_FOUND_ROWS 
		               budgetSubresponses.*  
		        FROM budgetSubresponses 
				WHERE budgetSubresponses.deleted = 0 ".$filter." 
				ORDER BY ".$order.
				$limit;		
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){
			$budgetSubresponses = array('list'=>$query->result_array(), 
		                                'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $budgetSubresponses;
	}	
		
}

/* End of file Budgetresponses_model.php */
/* Location: ./application/models/Budgetresponses_model.php */