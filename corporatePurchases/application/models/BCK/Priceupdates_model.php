<?php
class Priceupdates_model extends CI_Model{           
    
    private $company_db;         

	function __construct() {        
        parent::__construct();        
        
        if ((int)$this->session->userdata('companyId') > 0) {
        	$this->company_db = $this->load->database('company_db_'.$this->session->userdata('companyId'), TRUE); 
        }
    }

   	function getPriceUpdates($parameters=NULL){				
		$priceUpdates = array('list'=>NULL, 
		                      'totalRecords'=>0);
		
		$filter = "";
		$order = "";
		$limit = "";
		if (isset($parameters)) {			
			if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
				$filter .= " AND priceUpdates.id = ".$parameters['idFilter'];				
			}								
			if (isset($parameters['dateFromFilter']) && $parameters['dateFromFilter'] != "") {
				$filter .= " AND priceUpdates.date >= '".$parameters['dateFromFilter']."' ";
			}
			if (isset($parameters['dateToFilter']) && $parameters['dateToFilter'] != "") {
				$filter .= " AND priceUpdates.date <= '".$parameters['dateToFilter']."' ";
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
					case 'date':
						$order .= "priceUpdates.date ".$auxOrder.", priceUpdates.id ".$auxOrder;
					break;										
				}														
			}
		}
		
		if ($order == "") {
			$order .= "priceUpdates.date, priceUpdates.id";
		}			
						
		$sql = "SELECT SQL_CALC_FOUND_ROWS 
		               priceUpdates.*
		        FROM priceUpdates 
				WHERE priceUpdates.deleted = 0 ".$filter." 
				ORDER BY ".$order.
				$limit;	
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){
			$priceUpdates = array('list'=>$query->result_array(), 
		                      	  'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $priceUpdates;
	}
	
	function setPriceUpdate($data){
		if (!empty($data)){				
			
			$this->company_db->set('date',getCurrentDate());	 		
			$this->company_db->set('userId',$this->session->userdata('userId'));
			$this->company_db->set('count',$data['count']);
			$this->company_db->set('increase',$data['increase']);
			$this->company_db->set('decrease',$data['decrease']);
			$this->company_db->set('news',$data['news']);
			$this->company_db->set('equal',$data['equal']);
			$this->company_db->set('state',$data['state']);		
			$this->company_db->set('observation',$data['observation']);			
			$this->company_db->set('deleted',0);
			$this->company_db->set('deletedDate',NULL);
			$this->company_db->set('deletedUserId',NULL);
				
			$this->company_db->insert('priceUpdates');	
			$data['id'] = $this->company_db->insert_id();				
			
			$error = $this->company_db->error();
			if ((int)$error['code'] != 0) {							
				return false;
			} else {			
				if ($data['filename'] != "") {					
					$tmpPath = $this->config->item('files').$this->session->userdata('companyId').'/tmp/'.$data['filename'];					
					if (file_exists($tmpPath)) {
						$finalPath = $this->config->item('files').$this->session->userdata('companyId').'/articles/priceUpdates_'.$data['id'].".csv";
						@rename($tmpPath,$finalPath);
					}
				}

				if ($data['state'] == "OK") {
					$this->_notifyNewPriceUpdate($data);
				}

				return true;
			}						
		} else {
			return false;	
		}
	}	

	function _notifyNewPriceUpdate($priceUpdate=null) {				
		if (!isset($priceUpdate)) return;
				
		//--- NOTIFY TO USERS		
		$this->load->model('users_model','users');								

		$users = $this->users->getUsersWithPermission('PriceUpdates','NotifyNewPriceUpdates');

		if (count($users) > 0) {						
			$this->load->model('notifications_model','notifications');				

			$systemMailConfig = $this->config->item('systemMailConfig');	

			//--- BODY MAIL			
			$subject = $this->config->item('applicationName').": Actualización de precios procesada";					

			$idx = 0;								
			$lstMessage[$idx++] = "";
			$lstMessage[$idx++] = "le comunicamos que se ha procesado con éxito la nueva lista de precios para la empresa '".$this->session->userdata('companyName')."'.";																				
			$lstMessage[$idx++] = "";	
			$lstMessage[$idx++] = "Total de artículos: ".$priceUpdate['count'];		
			$lstMessage[$idx++] = "Aumentaron su precio: ".$priceUpdate['increase'];
			$lstMessage[$idx++] = "Disminuyeron su precio: ".$priceUpdate['decrease'];
			$lstMessage[$idx++] = "Mantienen su precio (+/- $ 1 de diferencia): ".$priceUpdate['equal'];
			$lstMessage[$idx++] = "Se dieron de alta: ".$priceUpdate['news'];
			$lstMessage[$idx++] = "";
			$lstMessage[$idx++] = "Ante cualquier duda comuníquese con nosotros a ".$systemMailConfig['responseTo'].".";
			$lstMessage[$idx++] = "Que tenga un buen día.";	
			$lstMessage[$idx++] = "";
			$lstMessage[$idx++] = $this->config->item('applicationName');									

			for ($i=0; $i < count($users); $i++) {
				if ($users[$i]['id'] != $this->session->userdata('userId')) {	
					//MAIL
					$to = trim($users[$i]['email']);
					if ($to != "") {
						$lstMessage[0] = "Estimado/a ".outputFormat(ucwords(trim($users[$i]['firstName']." ".$users[$i]['lastName'])),false).",";
						$mailMessage = mailBodyFormat($lstMessage);

						$sendOK = $this->my_application->sendMail($to,$subject,$mailMessage,$systemMailConfig['responseTo']);
					}

					//NOTIFY
					$notification = array('typeId'=>'PU', //PRICE UPDATE
										  'observation'=>'Act. de Precios Procesada',
										  'entity'=>'priceUpdate',
										  'entityId'=>$priceUpdate['id'],
										  'userId'=>$users[$i]['id']);
					$this->notifications->setNotification($notification);					
				}
			}

			if (isset($systemMailConfig['copyTo']) && trim($systemMailConfig['copyTo']) != "") {				
				$to = trim($systemMailConfig['copyTo']);
				$lstMessage[0] = "Estimado/a ".outputFormat($this->config->item('applicationName'),false).",";
				$mailMessage = mailBodyFormat($lstMessage);
			
				$sendOK = $this->my_application->sendMail($to,$subject,$mailMessage,$systemMailConfig['responseTo']);													
			}
		}								
	}

	function deletePriceUpdate($id=-1){
		if ($this->deletePriceUpdateValid($id)){	
			$this->company_db->set('deleted',1);	
			$this->company_db->set('deletedDate',getCurrentDate());	        
			$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         
			$this->company_db->where('id',$id);				
			$this->company_db->update('priceUpdates');		

			$error = $this->company_db->error();
			if ((int)$error['code'] != 0) {			
				return false;
			} else {
				$this->company_db->set('deleted',1);	
				$this->company_db->set('deletedDate',getCurrentDate());	        
				$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         
				$this->company_db->where('entity','priceUpdate');	
				$this->company_db->where('entityId',$id);				
				$this->company_db->update('notifications');	

				return true;
			}
		} else {
			return false;
		}		
	}

	function deletePriceUpdateValid($id=-1){
		if ($id > 0){	
			$allowDelete = true;
												
			return $allowDelete;
		} else {
			return false;
		}		
	}	
	
}

/* End of file Priceupdates_model.php */
/* Location: ./application/models/Priceupdates_model.php */