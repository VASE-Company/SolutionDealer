<?php
class Companies_model extends CI_Model{           

    private $main_db;

    function __construct() {        
        parent::__construct();        

        $this->main_db = $this->load->database('default', TRUE); 
    } 
	
	function getCompanyByUsername($username=''){
		$company = NULL;
		
		if ($username != '') {		
			$sql = "SELECT *  
					FROM companies 
					WHERE companies.username = ".$this->main_db->escape($username)."  
					      AND companies.deleted = 0 AND companies.active = 1";		
	
			$query = $this->main_db->query($sql);	
								
			if ($query->num_rows() == 1){            				
				$company = $query->row_array();									
			}				
		}
				
		return $company;
	}   

	function getCompanyById($id=-1){
		$company = NULL;
		
		if ($id > 0) {		
			$sql = "SELECT *  
					FROM companies 
					WHERE companies.id = ".$id."  
					      AND companies.deleted = 0 AND companies.active = 1";		
	
			$query = $this->main_db->query($sql);	
								
			if ($query->num_rows() == 1){            				
				$company = $query->row_array();									
			}				
		}
				
		return $company;
	}      

	function getCompanies($sqlFilter="") {
		$companies = NULL;				

		if ($sqlFilter != "") $sqlFilter = " AND ".$sqlFilter;
						
		$sql = "SELECT companies.*  
		        FROM companies 
				WHERE companies.deleted = 0 AND companies.active = 1 ".$sqlFilter." 
				ORDER BY companies.id";						
			
		$query = $this->main_db->query($sql);		
		if ($query->num_rows() > 0){
			$companies = $query->result_array();
		}		
		
		return $companies;
	}   	

	function getCompanyNameById($id=-1){
		$companyName = "";
		
		if ($id > 0) {		
			$sql = "SELECT name  
					FROM companies
					WHERE companies.id = ".$id;		
	
			$query = $this->main_db->query($sql);	
								
			if ($query->num_rows() == 1){            				
				$row = $query->row_array();		

				$companyName = $row['name'];
			}				
		}
				
		return $companyName;
	}  
}		
/* End of file Companies_model.php */
/* Location: ./application/models/Companies_model.php */
