<?php
class Panel_model extends CI_Model{           
    
    private $company_db;         

	function __construct() {        
        parent::__construct();        

        $this->company_db = $this->load->database('default', TRUE); 
    }

   	function getOrdersSummary(){				
		$this->load->model('orders_model','orders');	

		$summary = NULL;

		$data = array('toAutorizateCount'=>0, 	
		              'validatingAutorizateCount'=>0,
		              'totalAutorizateCount'=>0, 		
		              		
		              'pendingManagerCount'=>0, 	
		              'managingCount'=>0,
		              'totalPendingManagerCount'=>0, 

		              'totalNotFinalizeCount'=>0
		             );		

		$sql = "SELECT SUM(IF(orders.stateId = 'TOAUT',1,0)) AS toAutorizateCount,
					   SUM(IF(orders.stateId = 'VALID',1,0)) AS validatingAutorizateCount,

					   SUM(IF(orders.stateId = 'AUT',1,0)) AS pendingManagerCount,
					   SUM(IF(orders.stateId IN ('PROG','DETUSR','BUDGET','AUTBUY','TOPAY'),1,0)) AS managingCount,
					   
					   SUM(IF(orders.stateId IN ('READY','INDIST','PENSUP'),1,0)) AS totalNotFinalizeCount					   
		        
		        FROM (orders LEFT JOIN branchOffices ON orders.branchOfficeId = branchOffices.id)
		        LEFT JOIN companies ON branchOffices.companyId = companies.id
				WHERE orders.deleted = 0";	

		$accessFilter = $this->orders->getAccessFilter();
		if ($accessFilter != "") $sql .= " AND  (".$accessFilter.")";						

		$query = $this->company_db->query($sql);			
		if ($query->num_rows() > 0) {
			$row = $query->row_array();
				
			$data = array('toAutorizateCount'=>(int)$row['toAutorizateCount'],
	                  	  'validatingAutorizateCount'=>(int)$row['validatingAutorizateCount'],
	                  	  'totalAutorizateCount'=>(int)$row['toAutorizateCount'] + (int)$row['validatingAutorizateCount'],

	                  	  'pendingManagerCount'=>(int)$row['pendingManagerCount'],
	                  	  'managingCount'=>(int)$row['managingCount'],
	                  	  'totalPendingManagerCount'=>(int)$row['pendingManagerCount'] + (int)$row['managingCount'],
	                 		
	                 	  'totalNotFinalizeCount'=>(int)$row['totalNotFinalizeCount']
	              		);
		}
		
		$summary['autorizateOrders']['color'] = '#ff6f6f';
		$summary['autorizateOrders']['icon'] = 'fa fa-gavel';
		$summary['autorizateOrders']['totalCount'] = $data['totalAutorizateCount'];			
		$summary['autorizateOrders']['toAutorizateCount'] = $data['toAutorizateCount'];		
		$summary['autorizateOrders']['toAutorizateLink'] = base_url().'orders?sta=TOAUT';
		$summary['autorizateOrders']['validatingAutorizateCount'] = $data['validatingAutorizateCount'];		
		$summary['autorizateOrders']['validatingAutorizateLink'] = base_url().'orders?sta=VALID';
		
		$summary['managerOrders']['color'] = '#eaea4b';		
		$summary['managerOrders']['icon'] = 'fa  fa-archive';
		$summary['managerOrders']['totalCount'] = $data['totalPendingManagerCount'];		
		$summary['managerOrders']['managingCount'] = $data['managingCount'];		
		$summary['managerOrders']['managingLink'] = base_url().'orders?sta=MAN';
		$summary['managerOrders']['pendingManagerCount'] = $data['pendingManagerCount'];		
		$summary['managerOrders']['pendingManagerLink'] = base_url().'orders?sta=AUT';
		
		$summary['notFinalizeOrders']['color'] = '#00cc99';		
		$summary['notFinalizeOrders']['icon'] = 'fa fa-check-square';
		$summary['notFinalizeOrders']['totalCount'] = $data['totalNotFinalizeCount'];		
		$summary['notFinalizeOrders']['totalLink'] = base_url().'orders?sta=NOTFIN';

		return $summary;
	}

	/*
	function getBillsSummary(){				
		$this->load->model('budgets_model','budgets');	
		$this->load->model('generalconfigurations_model','generalConfigurations');

		$summary = array('pendingCount'=>0, 
						 'pendingAmount'=>0,						 
		                 'link'=>'#',
		                 'color'=>'#fbcf61');		

		$generalConfiguration = $this->generalConfigurations->getGeneralConfigurations();		                 		
		
		$sql = "SELECT COUNT(*) AS pendingCount,
		               SUM(bills.amount) AS pendingAmount,
		               SUM(IF(DATEDIFF(CURDATE(),bills.date) >= ".$generalConfiguration['pendingBillsDays'].",1,0)) AS overdueCount,
		               SUM(IF(DATEDIFF(CURDATE(),bills.date) >= ".$generalConfiguration['pendingBillsDays'].",bills.amount,0)) AS overdueAmount
		        FROM bills  
				WHERE bills.deleted = 0 AND bills.stateId = 'PEN'";			
	
		$query = $this->company_db->query($sql);			
		if ($query->num_rows() > 0){
			$row = $query->row_array();

			$allowSeeBills = $this->my_application->hasPermission("Bills","See");
				
			$summary['pendingCount'] = (int)$row['pendingCount'];
			$summary['pendingAmount'] = (float)$row['pendingAmount'];
			$summary['overdueCount'] = (int)$row['overdueCount'];
			$summary['overdueAmount'] = (float)$row['overdueAmount'];
	        $summary['link'] = ($allowSeeBills?base_url().'bills?sta=PEN':'#');
		}
				
		return $summary;
	}
	*/
	
}

/* End of file Panel_model.php */
/* Location: ./application/models/Panel_model.php */