<?php
class Tasks_model extends CI_Model{           
    
    private $company_db;         

	function __construct() {        
        parent::__construct();        

        $this->company_db = $this->load->database('default', TRUE);         
    }

    function clearSessions() {		
		$sql = "TRUNCATE ci_sessions";		
		$query = $this->company_db->query($sql);	
	}  

   	function cancelOrders(){					   		
   		$this->load->model('generalconfigurations_model','generalConfigurations');
   		$this->load->model('orders_model','orders');

   		$generalConfiguration = $this->generalConfigurations->getGeneralConfigurations();

		$daysCancelOrders = $generalConfiguration['daysCancelOrders'];
		

		//CANCEL ORDERS AUTOMATICALLY		
		if ($daysCancelOrders > 0) {
			$sql = "SELECT orders.id     
			        FROM orders 
					WHERE orders.deleted = 0 AND orders.stateId = 'TOAUT' AND 
						  DATEDIFF(CURDATE(),orders.date) > ".$daysCancelOrders."
					ORDER BY orders.id";	
			$query = $this->company_db->query($sql);
															
			if ($query->num_rows() > 0){
				$orders = $query->result_array();
				
				for ($i=0; $i < count($orders); $i++) {					
					$this->company_db->set('stateId','CAN');
					$this->company_db->where('id',$orders[$i]['id']);				
					$this->company_db->update('orders');	

					$error = $this->company_db->error();

					if ((int)$error['code'] == 0) {	
						$history = array('orderId'=>$orders[$i]['id'],
					                     'typeId'=>'STA',
					                 	 'entity'=>'state',
					                 	 'entityId'=>'CAN',
					                 	 'userId'=>NULL);
						$this->orders->setHistory($history);

						$this->orders->setOrderObservation($orders[$i]['id'],"I","El pedido se anuló automáticamente después de ".$daysCancelOrders." días en estado 'Pendiente Validación'.",true);

						//$this->orders->notifyStateToUsers($orders[$i]['id']);
					}
				}											
			}	
		}	
	}		
			
}

/* End of file Tasks_model.php */
/* Location: ./application/models/Tasks_model.php */