<?php
class Crm_model extends CI_Model{           

    private $company_db;         

	function __construct() {        
        parent::__construct();        
        
        if ((int)$this->session->userdata('companyId') > 0) {
        	$this->company_db = $this->load->database('company_db_'.$this->session->userdata('companyId'), TRUE); 
        }

        $this->load->model('budgets_model','budgets');
    }   
 	
 	function getData($parameters=NULL) {				
		$data = array('list'=>array(), 
		              'totalRecords'=>0);
		
		$filter = $this->budgets->getGeneralFilter($parameters);
		$order = $this->budgets->getGeneralOrder($parameters);
		$limit = $this->budgets->getGeneralLimit($parameters);	

		if ($order == "") {
			$order .= "budgets.clientName ASC";
		}	
						
		$sql = "SELECT SQL_CALC_FOUND_ROWS DISTINCT 
					   budgets.clientName,
					   budgets.clientEmail,
					   budgets.clientPhones,
					   budgets.vehicleDescription,					   
					   budgets.vehicleYear,
					   budgets.vehiclekms 
		        FROM ((((budgets LEFT JOIN branchOffices
		        ON budgets.branchOfficeId = branchOffices.id)
		        LEFT JOIN families ON budgets.familyId = families.id)
		        LEFT JOIN budgetResponses ON budgets.budgetResponseId = budgetResponses.id)
		        LEFT JOIN users workshopManagers ON budgets.workshopManagerId = workshopManagers.id)
		        LEFT JOIN users assessors ON budgets.assessorId = assessors.id
				WHERE budgets.deleted = 0 ".$filter." 
				ORDER BY ".$order.
				$limit;										
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');														
		if ($query->num_rows() > 0) {			
			$data = array('list'=>$query->result_array(), 
		                  'totalRecords'=>$queryTotal->row()->totalRecords);
		}	

		return $data;
	}
}

/* End of file Crm_model.php */
/* Location: ./application/models/Crm_model.php */