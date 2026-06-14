<?php
class Bills_model extends CI_Model{           
    
    private $company_db;         

	function __construct() {        
        parent::__construct();        
        
        if ((int)$this->session->userdata('companyId') > 0) {
        	$this->company_db = $this->load->database('company_db_'.$this->session->userdata('companyId'), TRUE); 
        }
    }

   	function getBills($parameters=NULL){				
		$bills = array('list'=>NULL, 
		               'totalRecords'=>0);
		
		$filter = "";
		$order = "";
		$limit = "";
		if (isset($parameters)) {
			if (isset($parameters['idFilter']) && $parameters['idFilter'] > 0) {
				$filter .= " AND bills.id = ".$parameters['idFilter'];				
			}		
			if (isset($parameters['textFilter']) && $parameters['textFilter'] != '') {
				$filter .= " AND bills.number LIKE ".$this->company_db->escape("%".$parameters['textFilter']."%");
			}			
			if (isset($parameters['stateIdFilter']) && $parameters['stateIdFilter'] != '') {
				$filter .= " AND bills.stateId = '".$parameters['stateIdFilter']."'";
			}					
			if (isset($parameters['dateFromFilter']) && $parameters['dateFromFilter'] != "") {
				$filter .= " AND bills.date >= '".$parameters['dateFromFilter']."' ";
			}
			if (isset($parameters['dateToFilter']) && $parameters['dateToFilter'] != "") {
				$filter .= " AND bills.date <= '".$parameters['dateToFilter']."' ";
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
						$order .= "bills.date ".$auxOrder;
					break;	
					case 'num':
						$order .= "bills.letter ".$auxOrder.", bills.serie ".$auxOrder.", bills.number ".$auxOrder;
					break;				
				}														
			}
		}
		
		if ($order == "") {
			$order .= "bills.date DESC ";
		}	
						
		$sql = "SELECT SQL_CALC_FOUND_ROWS 
		               bills.*,
		               billsTypes.description AS typeDescription,  
		               billsStates.description AS stateDescription 
		        FROM (bills LEFT JOIN billsTypes ON bills.typeId = billsTypes.id)
		        LEFT JOIN billsStates ON bills.stateId = billsStates.id
				WHERE bills.deleted = 0 ".$filter." 
				ORDER BY ".$order.
				$limit;						
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){
			$bills = array('list'=>$query->result_array(), 
		                   'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $bills;
	}

	function getEmptyBill() {
		$bill = array('id'=>-1,							 						       				        
				      'date'=>'',
				      'typeId'=>'FAC',				          	  
				      'letter'=>'',
				  	  'serie'=>'',
				  	  'number'=>'',
				  	  'stateId'=>'PEN',
				  	  'amount'=>0,
				  	  'description'=>'',
				  	  'paymentDate'=>'',
				  	  'paymentData'=>'');
						
		return $bill;
	}

	function existsValue($value='', $id=-1, $field='') {
		$exists = false;
		
		if ($value != '' && $field != '') {
			$sql = "SELECT * 
					FROM bills 
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
	
	function setBill($data){
		if (!empty($data)){		

			$data['letter'] = trim(strtoupper($data['letter']));
			$data['serie'] = completeWith0($data['serie'],4);
			$data['number'] = completeWith0($data['number'],8);
			
			$this->company_db->set('date',$data['date']);			
			$this->company_db->set('typeId',$data['typeId']);	
			$this->company_db->set('letter',$data['letter']);
			$this->company_db->set('serie',$data['serie']);
			$this->company_db->set('number',$data['number']);
			$this->company_db->set('stateId',$data['stateId']);
			$this->company_db->set('amount',$data['amount']);
			$this->company_db->set('description',$data['description']);
			$this->company_db->set('paymentDate',$data['paymentDate']);
			$this->company_db->set('paymentData',$data['paymentData']);

			if ((float)$data['id'] > 0) {				
				$this->company_db->where('id',$data['id']);				
				$this->company_db->update('bills');				
			} else {								
				$this->company_db->set('deleted',0);
				$this->company_db->set('deletedDate',NULL);
				$this->company_db->set('deletedUserId',NULL);
				
				$this->company_db->insert('bills');
				$data['id'] = $this->company_db->insert_id();
			}
			
			$error = $this->company_db->error();
			if ((int)$error['code'] != 0) {							
				return -1;
			} else {	
				return $data['id'];
			}						
		} else {
			return -1;	
		}
	}	

	function deleteBill($id=-1){
		if ($this->deleteBillValid($id)){	
			$this->company_db->set('deleted',1);	
			$this->company_db->set('deletedDate',getCurrentDate());	        
			$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         
			$this->company_db->where('id',$id);				
			$this->company_db->update('bills');					

			$error = $this->company_db->error();
			if ((int)$error['code'] != 0) {			
				return false;
			} else {
				$this->company_db->set('deleted',1);	
				$this->company_db->set('deletedDate',getCurrentDate());	        
				$this->company_db->set('deletedUserId',$this->session->userdata('userId'));	         
				$this->company_db->where('entity','bill');	
				$this->company_db->where('entityId',$id);				
				$this->company_db->update('notifications');		

				return true;
			}
		} else {
			return false;
		}		
	}

	function deleteBillValid($id=-1){
		if ($id > 0){	
			$allowDelete = true;		
												
			return $allowDelete;
		} else {
			return false;
		}		
	}	

	function getBillsTypes(){				
		$billsTypes = array('list'=>NULL, 
		                    'totalRecords'=>0);	
						
		$sql = "SELECT SQL_CALC_FOUND_ROWS 
		               billsTypes.*
		        FROM billsTypes 
				ORDER BY billsTypes.description";		
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){
			$billsTypes = array('list'=>$query->result_array(), 
		                        'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $billsTypes;
	}

	function getBillsStates(){				
		$billsStates = array('list'=>NULL, 
		                     'totalRecords'=>0);	
						
		$sql = "SELECT SQL_CALC_FOUND_ROWS 
		               billsStates.*
		        FROM billsStates 
				ORDER BY billsStates.description";		
			
		$query = $this->company_db->query($sql);
		$queryTotal = $this->company_db->query('SELECT FOUND_ROWS() AS totalRecords');
		if ($query->num_rows() > 0){
			$billsStates = array('list'=>$query->result_array(), 
		                         'totalRecords'=>$queryTotal->row()->totalRecords);
		}		
		
		return $billsStates;
	}

	function notifyNewBill($billId=-1) {				
		if ($billId <= 0) return;

		$sql = "SELECT *  
	        	FROM bills   
				WHERE bills.deleted = 0 AND bills.id = ".$billId;							
		$query = $this->company_db->query($sql);			
		if ($query->num_rows() == 1){
			$bill = $query->row_array();				
		} else {
			return;
		}
				
		//--- NOTIFY TO USERS		
		$this->load->model('users_model','users');								

		$users = $this->users->getUsersWithPermission('Bills','NotifyNewBills');

		if (count($users) > 0) {			
			
			$log['entity'] = "bill";
			$log['entityId'] = $billId;

			$this->load->model('notifications_model','notifications');	

			$systemMailConfig = $this->config->item('systemMailConfig');			

			//--- BODY MAIL			
			$subject = $this->config->item('applicationName').": Ya está disponible su nueva factura";					

			$idx = 0;	
			$lstMessage = NULL;			
			$lstMessage[$idx++] = "";					
			$lstMessage[$idx++] = "le enviamos adjunta su nueva factura, la misma también está disponible para descargar desde el sistema."; 
			$lstMessage[$idx++] = "";
			$lstMessage[$idx++] = "Empresa: <strong>".$this->session->userdata('companyName')."<strong>.";												
			$lstMessage[$idx++] = "Comprobante: <strong>".$bill['typeId']." ".$bill['letter']." ".$bill['serie']." ".$bill['number']."</strong>.";																				
			$lstMessage[$idx++] = "Importe: <strong>$ ".decimalFormat($bill['amount'],2)."</strong>.";																																									
			$lstMessage[$idx++] = "";

			$sql = "SELECT *
			        FROM bills 
					WHERE bills.deleted = 0 AND bills.stateId = 'PEN' AND 
						  bills.id <> ".$billId." 
					ORDER BY bills.date";									
			$query = $this->company_db->query($sql);		
			if ($query->num_rows() > 0) {		
				$pendigBills = $query->result_array();

				if (count($pendigBills) > 0) {
					$lstMessage[$idx++] = "Le recordamos que posee los siguientes comprobantes pendientes";

					for ($i=0; $i < count($pendigBills); $i++) {
						$lstMessage[$idx++] = "* ".dateFormat($pendigBills[$i]['date'],false)." - ".$pendigBills[$i]['typeId']." ".$pendigBills[$i]['letter']." ".$pendigBills[$i]['serie']." ".$pendigBills[$i]['number']." - $ ".decimalFormat($pendigBills[$i]['amount'],2);																										
					}

					$lstMessage[$idx++] = "";
				}
			}

			$lstMessage[$idx++] = "Para enviarnos los comprobantes de pago o ante cualquier duda comuníquese con nosotros a ".$systemMailConfig['responseTo'].".";
			$lstMessage[$idx++] = "Que tenga un buen día.";	
			$lstMessage[$idx++] = "";						
			$lstMessage[$idx++] = $this->config->item('applicationName');							

			$attachments = NULL;
			$path = $this->config->item('files').$this->session->userdata('companyId').'/bills/bill_'.$bill['id'].".pdf";
			if (file_exists($path)) {	
				$attachments[0]['path'] = $path;
				$attachments[0]['filename'] = $bill['typeId']."_".$bill['letter']."_".$bill['serie']."_".$bill['number'].".pdf";
			} 

			for ($i=0; $i < count($users); $i++) {
				if ($users[$i]['id'] != $this->session->userdata('userId')) {	
					//MAIL
					$to = trim($users[$i]['email']);
					if ($to != "") {
						$lstMessage[0] = "Estimado/a ".outputFormat(ucwords(trim($users[$i]['firstName']." ".$users[$i]['lastName'])),false).",";
						$mailMessage = mailBodyFormat($lstMessage);						
 						
						$sendOK = $this->my_application->sendMail($to,$subject,$mailMessage,$systemMailConfig['responseTo'],null,$attachments,$log);													
					}

					//NOTIFY
					$notification = array('typeId'=>'NB', //NEW BILL
										  'observation'=>'Nueva Factura Disponible',
										  'entity'=>'bill',
										  'entityId'=>$bill['id'],
										  'userId'=>$users[$i]['id']);
					$this->notifications->setNotification($notification);					
				}
			}

			if (isset($systemMailConfig['copyTo']) && trim($systemMailConfig['copyTo']) != "") {				
				$to = trim($systemMailConfig['copyTo']);
				$lstMessage[0] = "Estimado/a ".outputFormat($this->config->item('applicationName'),false).",";
				$mailMessage = mailBodyFormat($lstMessage);

				$sendOK = $this->my_application->sendMail($to,$subject,$mailMessage,$systemMailConfig['responseTo'],null,$attachments,$log);													
			}
		}								
	}

	function sendPendingBills() {	
		$response = array('pendigBills'=>0,
	                      'mailsOK'=>0,
	                      'mailsERROR'=>0,
	                  	  'mailInternal'=>"--");		                  	  							

		$sql = "SELECT *
		        FROM bills 
				WHERE bills.deleted = 0 AND bills.stateId = 'PEN'
				ORDER BY bills.date";									
		$query = $this->company_db->query($sql);		
		if ($query->num_rows() > 0) {	

			$pendigBills = $query->result_array();	

			$response['pendigBills'] = count($pendigBills);

			//--- NOTIFY TO USERS		
			$this->load->model('users_model','users');								

			$users = $this->users->getUsersWithPermission('Bills','NotifyNewBills');

			if (count($users) > 0) {													

				$systemMailConfig = $this->config->item('systemMailConfig');			

				//--- BODY MAIL			
				$subject = $this->config->item('applicationName').": Aviso de facturas pendientes";					

				$idx = 0;	
				$lstMessage = NULL;			
				$lstMessage[$idx++] = "";					
				$lstMessage[$idx++] = "le recordamos que posee los siguientes comprobantes pendientes:"; 				

				for ($i=0; $i < count($pendigBills); $i++) {
					$lstMessage[$idx++] = "* ".dateFormat($pendigBills[$i]['date'],false)." - ".$pendigBills[$i]['typeId']." ".$pendigBills[$i]['letter']." ".$pendigBills[$i]['serie']." ".$pendigBills[$i]['number']." - $ ".decimalFormat($pendigBills[$i]['amount'],2);																										
				}

				$lstMessage[$idx++] = "";
				$lstMessage[$idx++] = "Para enviarnos los comprobantes de pago o ante cualquier duda comuníquese con nosotros a ".$systemMailConfig['responseTo'].".";
				$lstMessage[$idx++] = "Que tenga un buen día.";	
				$lstMessage[$idx++] = "";						
				$lstMessage[$idx++] = $this->config->item('applicationName');							

				$attachments = NULL;
				for ($i=0; $i < count($pendigBills); $i++) {
					$path = $this->config->item('files').$this->session->userdata('companyId').'/bills/bill_'.$pendigBills[$i]['id'].".pdf";
					if (file_exists($path)) {	
						$attachments[$i]['path'] = $path;
						$attachments[$i]['filename'] = $pendigBills[$i]['typeId']."_".$pendigBills[$i]['letter']."_".$pendigBills[$i]['serie']."_".$pendigBills[$i]['number'].".pdf";
					}
				} 

				for ($i=0; $i < count($users); $i++) {					
					$to = trim($users[$i]['email']);
					if ($to != "") {
						$lstMessage[0] = "Estimado/a ".outputFormat(ucwords(trim($users[$i]['firstName']." ".$users[$i]['lastName'])),false).",";
						$mailMessage = mailBodyFormat($lstMessage);						
 						
						$sendOK = $this->my_application->sendMail($to,$subject,$mailMessage,$systemMailConfig['responseTo'],null,$attachments);													

						if ($sendOK) {
							$response['mailsOK']++;
						} else {
							$response['mailsERROR']++;
						}						
					}					
				}

				if (isset($systemMailConfig['copyTo']) && trim($systemMailConfig['copyTo']) != "") {				
					$to = trim($systemMailConfig['copyTo']);
					$lstMessage[0] = "Estimado/a ".outputFormat($this->config->item('applicationName'),false).",";
					$mailMessage = mailBodyFormat($lstMessage);

					$sendOK = $this->my_application->sendMail($to,$subject,$mailMessage,$systemMailConfig['responseTo'],null,$attachments);													
					
					$response['mailInternal'] = ($sendOK?"OK":"ERROR");					
				}
			}			
		}				

		return $response;
	}
		
}

/* End of file Bills_model.php */
/* Location: ./application/models/Bills_model.php */