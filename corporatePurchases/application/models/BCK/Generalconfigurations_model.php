<?php
class Generalconfigurations_model extends CI_Model{           
    
    private $company_db;         

	function __construct() {        
        parent::__construct();        
        
        if ((int)$this->session->userdata('companyId') > 0) {
        	$this->company_db = $this->load->database('company_db_'.$this->session->userdata('companyId'), TRUE); 
        }
    }

    function setDatabase($companyId=-1) {
    	if ($companyId > 0) {
    		if (isset($this->company_db)) {
    			$this->company_db->close();
    		}
        	$this->company_db = $this->load->database('company_db_'.$companyId, TRUE); 
        }
    }

   	function getGeneralConfigurations() {				
		$generalConfigurations = NULL;			
						
		$sql = "SELECT *
		        FROM generalConfigurations
				WHERE generalConfigurations.deleted = 0 
				ORDER BY generalConfigurations.id
				LIMIT 1";

		$query = $this->company_db->query($sql);		
		if ($query->num_rows() == 1){			
			$generalConfigurations = $query->row_array();	

			if ((float)$generalConfigurations['reassignToCompanyId'] > 0) {
				$this->load->model('companies_model','companies');

				$company = $this->companies->getCompanyById($generalConfigurations['reassignToCompanyId']);
				if (isset($company) && $company['id'] > 0) {
					$generalConfigurations['reassignToCompany'] = $company['name'];
				} else {
					$generalConfigurations['reassignToCompanyId'] = 0;
					$generalConfigurations['reassignToCompany'] = '';	
				}
			} else {
				$generalConfigurations['reassignToCompany'] = '';
			}
		} else {
			$generalConfigurations = array('id'=>0,
											'priceUpdatesDaysReminder' => 5,
										    'priceUpdatesDays' => 31,
											'callBudgetsDays' => 0,
											'pendingBillsDaysReminder' => 5,
											'pendingBillsDays' => 31,
										    'allowHideCodeArticleExportMail' => 0,
											'reassignToCompanyId'=>0,											
										    'reassignToCompany'=>'',
											'allowReassignFromCompany'=>0,
											'defaultReassignASSId'=>0,
											'defaultReassignWMId'=>0,
											'defaultReassignBOId'=>0);	
		}		
		
		return $generalConfigurations;
	}	

	function setGeneralConfigurations($data){
		if (!empty($data)){				
							
			$this->company_db->set('priceUpdatesDays',(int)$data['priceUpdatesDays']);
			$this->company_db->set('priceUpdatesDaysReminder',(int)$data['priceUpdatesDaysReminder']);
			$this->company_db->set('callBudgetsDays',(int)$data['callBudgetsDays']);
			$this->company_db->set('pendingBillsDays',(int)$data['pendingBillsDays']);
			$this->company_db->set('pendingBillsDaysReminder',(int)$data['pendingBillsDaysReminder']);
			$this->company_db->set('allowHideCodeArticleExportMail',(int)$data['allowHideCodeArticleExportMail']);
			$this->company_db->set('reassignToCompanyId',(float)$data['reassignToCompanyId']);
			$this->company_db->set('allowReassignFromCompany',(int)$data['allowReassignFromCompany']);
			if ((int)$data['allowReassignFromCompany'] == 1) {
				$this->company_db->set('defaultReassignBOId',(float)$data['defaultReassignBOId']);
				$this->company_db->set('defaultReassignASSId',(float)$data['defaultReassignASSId']);
				$this->company_db->set('defaultReassignWMId',(float)$data['defaultReassignWMId']);
			} else {
				$this->company_db->set('defaultReassignBOId',0);
				$this->company_db->set('defaultReassignASSId',0);
				$this->company_db->set('defaultReassignWMId',0);
			}
			
			if ((float)$data['id'] > 0) {
				$this->company_db->where('id',$data['id']);				
				$this->company_db->update('generalConfigurations');				
			} else {				
				$this->company_db->set('deleted',0);
				$this->company_db->set('deletedDate',NULL);
				$this->company_db->set('deletedUserId',NULL);
				
				$this->company_db->insert('generalConfigurations');
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
		
}

/* End of file Generalconfigurations_model.php */
/* Location: ./application/models/Generalconfigurations_model.php */