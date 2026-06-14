<?php
class Tasks_model extends CI_Model{           
    
    private $company_db;          
    private $company_db_2;  
    private $main_db;

    function __construct() {        
        parent::__construct();        

        $this->load->model('notifications_model','notifications');	
        $this->load->model('users_model','users');	
        $this->load->model('systemmessages_model','systemMessages');
        $this->load->model('generalconfigurations_model','generalConfigurations');
    } 

    function setCompanyDatabase($companyId=-1,$main=true) {
    	if ($companyId > 0) {
    		if ($main) {
    			if (isset($this->company_db)) {
	    			$this->company_db->close();
	    		}
	        	$this->company_db = $this->load->database('company_db_'.$companyId, TRUE); 
    		} else {
	    		if (isset($this->company_db_2)) {
	    			$this->company_db_2->close();
	    		}
	        	$this->company_db_2 = $this->load->database('company_db_'.$companyId, TRUE); 
	        }
        }
    }

   	function generateNotifications($companyId=null,$companyName=""){					   		
   		$this->setCompanyDatabase($companyId);
   		$this->notifications->setDatabase($companyId);
   		$this->users->setDatabase($companyId);
   		$this->systemMessages->setDatabase($companyId);
   		$this->generalConfigurations->setDatabase($companyId);

   		$generalConfiguration = $this->generalConfigurations->getGeneralConfigurations();

		$priceUpdatesDaysReminder = $generalConfiguration['priceUpdatesDaysReminder'];
		$priceUpdatesDays = $generalConfiguration['priceUpdatesDays'];  		
		$callBudgetsDays = $generalConfiguration['callBudgetsDays'];
		$pendingBillsDaysReminder = $generalConfiguration['pendingBillsDaysReminder'];
		$pendingBillsDays = $generalConfiguration['pendingBillsDays'];

		//PRICE UPDATES NOTIFICATIONS		
		if ($priceUpdatesDays > 0) {
			$sql = "SELECT priceUpdates.date, DATEDIFF(CURDATE(),priceUpdates.date) AS days  
			        FROM priceUpdates 
					WHERE priceUpdates.deleted = 0 AND priceUpdates.state = 'OK' 
					ORDER BY priceUpdates.date DESC
					LIMIT 1";									
			$query = $this->company_db->query($sql);		
			if ($query->num_rows() == 1) {		
				$priceUpdate = $query->row_array();	
				$lastDatePriceUpdate = $priceUpdate['date'];		
				$daysFromLastPriceUpdate = $priceUpdate['days'];	
				
				if ($daysFromLastPriceUpdate >= $priceUpdatesDays) {				
					$sql = "SELECT DATEDIFF(CURDATE(),notifications.date) AS days  
					        FROM notifications 
							WHERE notifications.deleted = 0 AND notifications.typeId = 'PUR' AND
							      notifications.date > '".$lastDatePriceUpdate."' 
							ORDER BY notifications.date DESC
							LIMIT 1";					
					$query = $this->company_db->query($sql);					
					if ($query->num_rows() == 1) {
						$notification = $query->row_array();	
						$daysFromLastNotification = $notification['days'];	

						$notifyPriceUpdate = ($daysFromLastNotification >= $priceUpdatesDaysReminder);			
					} else {
						$notifyPriceUpdate = true;
					}	

					if ($notifyPriceUpdate) {
						$this->_notifyPriceUpdateReminder($lastDatePriceUpdate,$companyName);
					}
				}
			}	
		}

		//BUDGETS TO CALL NOTIFICATIONS			
		if ($callBudgetsDays >= 0) {						
			$sql = "SELECT budgets.*  
			        FROM budgets 
					WHERE budgets.deleted = 0 AND budgets.nextDateCalled = '".getCurrentDate(false,$callBudgetsDays)."'
					ORDER BY budgets.id";					
			$query = $this->company_db->query($sql);					
			if ($query->num_rows() > 0){
				$budgets = $query->result_array();

				for ($i=0; $i < count($budgets); $i++) {
					$this->_notifyToAssessorCallBudget($budgets[$i],$companyName);
				}
			}	
		}	


		//PENDING BILLS NOTIFICATIONS
		if ($pendingBillsDays > 0) {		
			$sql = "SELECT *
			        FROM bills 
					WHERE bills.deleted = 0 AND bills.stateId = 'PEN' AND 
						  DATEDIFF(CURDATE(),bills.date) >= ".$pendingBillsDays." 
					ORDER BY bills.date DESC";									
			$query = $this->company_db->query($sql);		
			if ($query->num_rows() > 0) {				
				$bills = $query->result_array();
				
				for ($i=0; $i < count($bills); $i++) {				
					$sql = "SELECT DATEDIFF(CURDATE(),notifications.date) AS days  
					        FROM notifications 
							WHERE notifications.deleted = 0 AND notifications.typeId = 'BPR' AND
							      notifications.date > '".$bills[$i]['date']."' AND 
							      notifications.entityId = ".$bills[$i]['id']." AND notifications.entity = 'bill'
							ORDER BY notifications.date DESC
							LIMIT 1";					
					$query = $this->company_db->query($sql);					
					if ($query->num_rows() == 1) {
						$notification = $query->row_array();	
						$daysFromLastNotification = $notification['days'];						

						$notifyBillPending = ($daysFromLastNotification >= $pendingBillsDaysReminder);									
					} else {
						$notifyBillPending = true;
					}	

					if ($notifyBillPending) {						
						$this->_notifyBillPendingReminder($bills[$i],$companyName);
					}
				}
			}		
		}					
			
		$this->company_db->close();
	}		

	function _notifyToAssessorCallBudget($budget=null) {				
		if (!isset($budget)) return false;				
			
		$assessor = $this->_getUser($budget['assessorId']);	
		if ($assessor == null) return false;

		$notifyOK = false;

		if ($assessor['email'] != "") { 
		
			$to = $assessor['email'];
			$subject = $this->config->item('applicationName').": Recordatorio llamar por el Presupuesto Nro ".$budget['id'];					

			$idx = 0;								
			$lstMessage[$idx++] = "Estimado/a  ".outputFormat(ucwords(trim($assessor['firstName']." ".$assessor['lastName'])),false).",";					
			$lstMessage[$idx++] = "le recordamos que usted tiene agendado llamar el día ".dateFormat($budget['nextDateCalled'],false)." por el presupuesto nro. ".$budget['id']." de la empresa '".$companyName."'.";														
			$lstMessage[$idx++] = "Que tenga un buen día.";	
			$lstMessage[$idx++] = "";			
			$lstMessage[$idx++] = $this->config->item('applicationName');			
			$lstMessage[$idx++] = "(No responder a este email)";		
						
			$message = mailBodyFormat($lstMessage);																																						
													   																		
			$sendOK = $this->my_application->sendMail($to,$subject,$message);						

			if ($sendOK) {				
				$notifyOK = true;
			}
		}
		
		$notification = array('typeId'=>'BCR', //BUDGET CALL REMINDER
							  'observation'=>'Llamar Ppto. Nro. '.$budget['id'],
							  'entity'=>'budget',
							  'entityId'=>$budget['id'],
							  'userId'=>$budget['assessorId']);
		if ($this->notifications->setNotification($notification)) {
			$notifyOK = true;
		}
						
		return $notifyOK;						
	}

	function _notifyPriceUpdateReminder($lastDatePriceUpdate=null,$companyName='') {												
		$users = $this->users->getUsersWithPermission('PriceUpdates','NotifyNewPriceUpdates');

		if (count($users) > 0) {		
			$systemMailConfig = $this->config->item('systemMailConfig');		

			//--- BODY MAIL			
			$subject = $this->config->item('applicationName').": Lista de Precios desactualizada";					

			$idx = 0;		
			$lstMessage[$idx++] = "";									
			$lstMessage[$idx++] = "le recordamos que ha pasado ya mucho tiempo desde la última actualización de la lista de precios para la empresa '".$companyName."' (".dateFormat($lastDatePriceUpdate,false).").";																							
			$lstMessage[$idx++] = "Si posee una lista actualizada por favor enviela a ".$systemMailConfig['responseTo'].".";
			$lstMessage[$idx++] = "Que tenga un buen día.";	
			$lstMessage[$idx++] = "";
			$lstMessage[$idx++] = $this->config->item('applicationName');		

			$mailMessage = mailBodyFormat($lstMessage);

			//SYSTEM MESSAGE
			$message = "<p><b><u>Advertencia</u>:</b> su lista de precios se encuentra desactualizada.</p><p>Ultima Actualización: <b>".dateFormat($lastDatePriceUpdate,false)."</b></p><p>Por favor envíe una lista actualizada a <b>".$systemMailConfig['responseTo']."</b>.<br></p><p><br></p>";
			$systemMessage = array('title'=>'Lista de Precios Desactualizada',
							  	   'message'=>$message,
							  	   'notification'=>'0',
							  	   'email'=>'0');
			$systemMessageId = $this->systemMessages->setSystemMessage($systemMessage);					

			for ($i=0; $i < count($users); $i++) {				
				//MAIL
				$to = trim($users[$i]['email']);
				if ($to != "") {
					$lstMessage[0] = "Estimado/a ".outputFormat(ucwords(trim($users[$i]['firstName']." ".$users[$i]['lastName'])),false).",";
					$mailMessage = mailBodyFormat($lstMessage);

					$sendOK = $this->my_application->sendMail($to,$subject,$mailMessage,$systemMailConfig['responseTo']);								
				}

				//NOTIFY
				if ($systemMessageId > 0) {
					$notification = array('typeId'=>'PUR', //PRICE UPDATE REMINDER
										  'observation'=>'Lista de Precios Desactualizada',
										  'entity'=>'systemMessage',
										  'entityId'=>$systemMessageId,
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

	function _notifyBillPendingReminder($bill=null,$companyName="") {												
		if (!isset($bill)) return;	
		
		$users = $this->users->getUsersWithPermission('Bills','NotifyNewBills');

		if (count($users) > 0) {		
			$systemMailConfig = $this->config->item('systemMailConfig');		

			//--- BODY MAIL			
			$subject = $this->config->item('applicationName').": Aviso de factura pendiente: ".$bill['letter']." ".$bill['serie']." ".$bill['number'];					

			$idx = 0;				
			$lstMessage[$idx++] = "";							
			$lstMessage[$idx++] = "le recordamos que aún está pendiente el pago de la factura ".$bill['letter']." ".$bill['serie']." ".$bill['number']." del ".dateFormat($bill['date'],false)." por $ ".decimalFormat($bill['amount'],2)." para la empresa '".$companyName."'.";																				
			$lstMessage[$idx++] = "Si usted ya realizó el pago o tiene alguna consulta por favor comuníquese a ".$systemMailConfig['responseTo'].".";
			$lstMessage[$idx++] = "Que tenga un buen día.";	
			$lstMessage[$idx++] = "";
			$lstMessage[$idx++] = $this->config->item('applicationName');		

			$mailMessage = mailBodyFormat($lstMessage);		

			for ($i=0; $i < count($users); $i++) {				
				//MAIL
				$to = trim($users[$i]['email']);
				if ($to != "") {
					$lstMessage[0] = "Estimado/a ".outputFormat(ucwords(trim($users[$i]['firstName']." ".$users[$i]['lastName'])),false).",";
					$mailMessage = mailBodyFormat($lstMessage);

					$sendOK = $this->my_application->sendMail($to,$subject,$mailMessage,$systemMailConfig['responseTo']);								
				}

				//NOTIFY
				$notification = array('typeId'=>'BPR', //BILL PENDING REMINDER
							  		  'observation'=>'Factura Pendiente: '.$bill['letter'].' '.$bill['serie'].' '.$bill['number'],
							  		  'entity'=>'bill',
							  		  'entityId'=>$bill['id'],
							  		  'userId'=>$users[$i]['id']);
				$this->notifications->setNotification($notification);				
			}

			if (isset($systemMailConfig['copyTo']) && trim($systemMailConfig['copyTo']) != "") {				
				$to = trim($systemMailConfig['copyTo']);
				$lstMessage[0] = "Estimado/a ".outputFormat($this->config->item('applicationName'),false).",";
				$mailMessage = mailBodyFormat($lstMessage);

				$sendOK = $this->my_application->sendMail($to,$subject,$mailMessage,$systemMailConfig['responseTo']);													
			}
		}								
	}

	function _getUser($userId=-1) {
		$user = null;

		if ($userId > 0) {
			$sql = "SELECT *  
		        	FROM users   
					WHERE users.deleted = 0 AND users.id = ".$userId;					
			
			$query = $this->company_db->query($sql);			
			if ($query->num_rows() == 1){
				$user = $query->row_array();				
			}
		}

		return $user;
	}

	function clearTemporalFiles($companyId=null){
		$folder = $this->config->item('files').$companyId.'/tmp/*';

		$files = glob($folder);
		foreach($files as $file){
		    if (is_file($file) && strtolower(pathinfo($file, PATHINFO_BASENAME)) != "index.html")		    	
		    	unlink($file);
		}
	} 

	function clearSessions() {		

		$this->main_db = $this->load->database('default', TRUE); 

		$sql = "TRUNCATE ci_sessions";		
		$query = $this->main_db->query($sql);	

	}

	function reassingBudgets($originCompanyId=null,$originCompanyName=""){						   	
   		//Get Destination Company
   		$this->generalConfigurations->setDatabase($originCompanyId);
   		$generalConfiguration = $this->generalConfigurations->getGeneralConfigurations();
   		if ((float)$generalConfiguration['reassignToCompanyId'] <= 0 || (float)$generalConfiguration['reassignToCompanyId'] == (float)$originCompanyId) return true;
   		$destinationCompanyId = $generalConfiguration['reassignToCompanyId'];   		

   		//Get Destination Company Allow Reassign
   		$this->generalConfigurations->setDatabase($destinationCompanyId);
   		$generalConfiguration = $this->generalConfigurations->getGeneralConfigurations();   		
   		if ((int)$generalConfiguration['allowReassignFromCompany'] != 1) return true;

   		//Get Name of Destination Company
   		$this->main_db = $this->load->database('default', TRUE); 
		$sql = "SELECT name FROM companies WHERE id = ".$destinationCompanyId;		
		$query = $this->main_db->query($sql);
		if ($query->num_rows() == 1){            				
			$company = $query->row_array();	

			$destinationCompanyName = $company['name'];								
		} else {
			$destinationCompanyName = "";
		}
		$this->main_db->close();				

   		//Get Budgets To Reassign
   		$this->setCompanyDatabase($originCompanyId);   

   		$filterOutOfWarranty = " AND 
   		                         (budgets.outOfWarranty = 1
   		                          OR 
   		                          EXISTS (
											SELECT id 
											FROM warrantyByModel
											WHERE (
												    budgets.vehicleDescription like CONCAT('%',warrantyByModel.keyword1,'%')
													AND 
													(warrantyByModel.keyword2 IS NULL OR warrantyByModel.keyword2 = '' OR budgets.vehicleDescription like CONCAT('%',warrantyByModel.keyword2,'%'))
													AND
													(warrantyByModel.notKeyword1 IS NULL OR warrantyByModel.notKeyword1 = '' OR NOT budgets.vehicleDescription like CONCAT('%',warrantyByModel.notKeyword1,'%'))
												  )
												  AND
												  (
												  	DATE_ADD(
												  			 STR_TO_DATE(CONCAT('01,',IF(budgets.vehicleMonth = 0,1, budgets.vehicleMonth),',',budgets.vehicleYear),'%d,%m,%Y')
												  	         , 
												  	         INTERVAL (warrantyByModel.months + 1) MONTH
												  	        )
													< NOW()
   		                        					OR
													(warrantyByModel.kms > 0 AND budgets.vehiclekms > warrantyByModel.kms)
												  )
										 )
   		                         ) ";	   		                        

   		$sql = "SELECT budgets.*,
   					   families.description AS familyDescription,
   					   budgetResponses.description AS responseDescription,
   					   budgetSubresponses.description AS subresponseDescription,
   					   insuranceCompanies.description AS insuranceCompanyDescription 
		        FROM ((((budgets INNER JOIN branchOffices ON budgets.branchOfficeId = branchOffices.id)
		        INNER JOIN budgetResponses ON budgets.budgetResponseId = budgetResponses.id)
		        INNER JOIN budgetSubresponses ON budgets.budgetSubresponseId = budgetSubresponses.id)
		        INNER JOIN families ON budgets.familyId = families.id)
		        LEFT JOIN insuranceCompanies ON budgets.insuranceCompanyId = insuranceCompanies.id
				WHERE budgets.deleted = 0 AND (ISNULL(budgets.toCompanyId) OR budgets.toCompanyId = 0)
					  AND branchOffices.deleted = 0 AND branchOffices.allowReassignCompany = 1
					  AND budgetSubresponses.deleted = 0 AND budgetSubresponses.reassignCompany = 1
					  AND families.deleted = 0 AND families.reassignCompany = 1 					  
					  AND budgets.date >= DATE_ADD(NOW(), INTERVAL -1 MONTH)
					  ".$filterOutOfWarranty." 
				ORDER BY budgets.id";	

		$query = $this->company_db->query($sql);	

		$showLog = true;		

		if ($query->num_rows() > 0) {
			$this->setCompanyDatabase($destinationCompanyId,false);			

			$budgets = $query->result_array();					

			for ($i=0; $i < count($budgets); $i++) {
				$budget = $budgets[$i];							

				//budget and details
				$this->company_db_2->set('date',getCurrentDate(false));
				$this->company_db_2->set('orderNumber',$budget['orderNumber']);
				$this->company_db_2->set('sinisterNumber',$budget['sinisterNumber']);
				$this->company_db_2->set('insuranceCompanyId',$this->_getEntityId('insuranceCompanies',$budget['insuranceCompanyDescription'],2)); 
				$this->company_db_2->set('branchOfficeId',(float)$generalConfiguration['defaultReassignBOId']);
				$this->company_db_2->set('familyId',$this->_getEntityId('families',$budget['familyDescription'],2));
				$this->company_db_2->set('assessorId',(float)$generalConfiguration['defaultReassignASSId']);
				$this->company_db_2->set('assessorNotificationDate',NULL);				
				$this->company_db_2->set('workshopManagerId',(float)$generalConfiguration['defaultReassignWMId']);
				$this->company_db_2->set('creationDate',getCurrentDate());
				$this->company_db_2->set('clientName',$budget['clientName']);
				$this->company_db_2->set('clientEmail',$budget['clientEmail']);
				$this->company_db_2->set('clientPhones',$budget['clientPhones']);
				$this->company_db_2->set('vehicleDescription',$budget['vehicleDescription']);
				$this->company_db_2->set('vehicleChassis',$budget['vehicleChassis']);
				$this->company_db_2->set('vehiclePatent',$budget['vehiclePatent']);
				$this->company_db_2->set('vehicleMonth',$budget['vehicleMonth']);
				$this->company_db_2->set('vehicleYear',$budget['vehicleYear']);
				$this->company_db_2->set('vehiclekms',$budget['vehiclekms']);
				$this->company_db_2->set('detailAmount',$budget['detailAmount']);
				$this->company_db_2->set('ivaRate',$budget['ivaRate']);
				$this->company_db_2->set('detailAmountWithIVA',$budget['detailAmountWithIVA']);
				$this->company_db_2->set('sheetQuantity',$budget['sheetQuantity']);
				$this->company_db_2->set('sheetAmount',$budget['sheetAmount']);
				$this->company_db_2->set('paintQuantity',$budget['paintQuantity']);
				$this->company_db_2->set('paintAmount',$budget['paintAmount']);
				$this->company_db_2->set('workforceAmount',$budget['workforceAmount']);
				$this->company_db_2->set('total',$budget['total']);
				$this->company_db_2->set('notifiedClient',0);		
				$this->company_db_2->set('clientNotificationDate',NULL);	
				$this->company_db_2->set('budgetResponseId',0);		
				$this->company_db_2->set('budgetSubresponseId',0); 															
				$this->company_db_2->set('answerDate',NULL);		
				$this->company_db_2->set('readByAssessor',0);									
				$this->company_db_2->set('nextDateCalled',NULL);	
				$this->company_db_2->set('outOfWarranty',0);					
				$this->company_db_2->set('toCompanyId',0);					
				$this->company_db_2->set('toCompanyDate',NULL);	
				$this->company_db_2->set('fromCompanyId',$originCompanyId);					
				$this->company_db_2->set('fromAssessorId',$budget['assessorId']);	
				$this->company_db_2->set('fromCompanyDate',getCurrentDate());
				$this->company_db_2->set('deleted',0);
				$this->company_db_2->set('deletedDate',NULL);
				$this->company_db_2->set('deletedUserId',NULL);	

				$this->company_db_2->insert('budgets');
			
				$newBudgetId = (float)$this->company_db_2->insert_id();

				if ($showLog)
					echo "Presupuesto ".$budget['id']." de ".$originCompanyName." a ".$destinationCompanyName." como ".$newBudgetId."<br>";

				if ($newBudgetId > 0) {					
					//details
					$sql = "SELECT detailsByBudget.*  
					        FROM detailsByBudget					        
							WHERE detailsByBudget.deleted = 0 AND detailsByBudget.budgetId = ".$budget['id']." 
							ORDER BY detailsByBudget.id";			
							
					$query = $this->company_db->query($sql);

					if ($query->num_rows() > 0) {						

						$details = $query->result_array();

						for ($j=0; $j < count($details); $j++) {
							$detail = $details[$j];				

							$this->company_db_2->set('budgetId',$newBudgetId);
							$this->company_db_2->set('articleId',0);
							$this->company_db_2->set('code',$detail['code']);
							$this->company_db_2->set('description',$detail['description']);
							$this->company_db_2->set('unitPrice',$detail['unitPrice']);
							$this->company_db_2->set('quantity',$detail['quantity']);
							$this->company_db_2->set('discountRate',$detail['discountRate']);
							$this->company_db_2->set('total',$detail['total']);
							$this->company_db_2->set('stock',$detail['stock']);
							$this->company_db_2->set('factory',$detail['factory']);
							$this->company_db_2->set('deleted',0);
							$this->company_db_2->set('deletedDate',NULL);
							$this->company_db_2->set('deletedUserId',NULL);	

							$this->company_db_2->insert('detailsByBudget');
						}
					}

					//observations
					$sql = "SELECT observationsByBudget.*  
					        FROM observationsByBudget					        
							WHERE observationsByBudget.deleted = 0 AND observationsByBudget.budgetId = ".$budget['id']." 
							ORDER BY observationsByBudget.id";			
							
					$query = $this->company_db->query($sql);

					if ($query->num_rows() > 0) {						

						$observations = $query->result_array();

						for ($j=0; $j < count($observations); $j++) {
							$observation = $observations[$j];				

							$this->company_db_2->set('budgetId',$newBudgetId);							
							$this->company_db_2->set('date',$observation['date']);
							$this->company_db_2->set('type',$observation['type']);
							$this->company_db_2->set('observation',$observation['observation']);
							$this->company_db_2->set('userId ',NULL);		
							$this->company_db_2->set('deleted',0);
							$this->company_db_2->set('deletedDate',NULL);
							$this->company_db_2->set('deletedUserId',NULL);	

							$this->company_db_2->insert('observationsByBudget');
						}
					}

					//attachments
					$sql = "SELECT attachmentsByBudget.*  
					        FROM attachmentsByBudget					        
							WHERE attachmentsByBudget.deleted = 0 AND attachmentsByBudget.budgetId = ".$budget['id']." 
							ORDER BY attachmentsByBudget.id";			
							
					$query = $this->company_db->query($sql);

					if ($query->num_rows() > 0) {						

						$attachments = $query->result_array();

						for ($j=0; $j < count($attachments); $j++) {
							$attachment = $attachments[$j];				
							
							$pathFrom = $this->config->item('files').$originCompanyId.'/budgets/'.$budget['id']."/".$attachment['internalFilename'];
							if (file_exists($pathFrom)) {								
								$partsPathInternalFilename = pathinfo($attachment['internalFilename']);			
								$attachment['internalFilename'] = md5(uniqid()).".".$partsPathInternalFilename['extension'];

								$pathTo = $this->config->item('files').$destinationCompanyId;
								checkCreateFolder($pathTo);	
								$pathTo = $this->config->item('files').$destinationCompanyId.'/budgets';
								checkCreateFolder($pathTo);		
								$pathTo .= "/".$newBudgetId;
								checkCreateFolder($pathTo);
								$pathTo .= '/'.$attachment['internalFilename'];

								if (copy($pathFrom, $pathTo)) {
									$this->company_db_2->set('budgetId',$newBudgetId);							
									$this->company_db_2->set('date',$attachment['date']);
									$this->company_db_2->set('filename',$attachment['filename']);
									$this->company_db_2->set('internalFilename',$attachment['internalFilename']);
									$this->company_db_2->set('userId ',NULL);		
									$this->company_db_2->set('deleted',0);
									$this->company_db_2->set('deletedDate',NULL);
									$this->company_db_2->set('deletedUserId',NULL);	

									$this->company_db_2->insert('attachmentsByBudget');									
								}
							}							
						}
					}

					//historial	in new budget				
					$observation = "Reasignado desde la empresa <b>".$originCompanyName."</b>.<br>";
					$observation .= "Presupuesto Nro. <b>".$budget['id']."</b> del <b>".dateFormat($budget['date'],false)."</b>.<br>";
					$observation .= "<b>".$budget['responseDescription']."</b> por <b>".$budget['subresponseDescription']."</b> el <b>".dateFormat($budget['answerDate'],false)."</b>";				

					$this->company_db_2->set('budgetId',$newBudgetId);
					$this->company_db_2->set('date',getCurrentDate());	 		
					$this->company_db_2->set('typeId',"RFC");
					$this->company_db_2->set('observation',$observation);
					$this->company_db_2->set('entity',"company");
					$this->company_db_2->set('entityId',$originCompanyId);
					$this->company_db_2->set('userId',NULL);									
					$this->company_db_2->set('deleted',0);
					$this->company_db_2->set('deletedDate',NULL);
					$this->company_db_2->set('deletedUserId',NULL);
						
					$this->company_db_2->insert('historyByBudget');

					if ((float)$generalConfiguration['defaultReassignWMId'] > 0) {
						$this->company_db_2->set('budgetId',$newBudgetId);
						$this->company_db_2->set('date',getCurrentDate());	 		
						$this->company_db_2->set('typeId',"WM");
						$this->company_db_2->set('observation',"");
						$this->company_db_2->set('entity',"user");
						$this->company_db_2->set('entityId',$generalConfiguration['defaultReassignWMId']);
						$this->company_db_2->set('userId',NULL);									
						$this->company_db_2->set('deleted',0);
						$this->company_db_2->set('deletedDate',NULL);
						$this->company_db_2->set('deletedUserId',NULL);
							
						$this->company_db_2->insert('historyByBudget');
					}

					if ((float)$generalConfiguration['defaultReassignASSId'] > 0) {
						$this->company_db_2->set('budgetId',$newBudgetId);
						$this->company_db_2->set('date',getCurrentDate());	 		
						$this->company_db_2->set('typeId',"ASS");
						$this->company_db_2->set('observation',"");
						$this->company_db_2->set('entity',"user");
						$this->company_db_2->set('entityId',$generalConfiguration['defaultReassignASSId']);
						$this->company_db_2->set('userId',NULL);									
						$this->company_db_2->set('deleted',0);
						$this->company_db_2->set('deletedDate',NULL);
						$this->company_db_2->set('deletedUserId',NULL);
							
						$this->company_db_2->insert('historyByBudget');
					}

					//original budgets marked
					$this->company_db->set('toCompanyId',$destinationCompanyId);	
					$this->company_db->set('toCompanyDate',getCurrentDate());	
					$this->company_db->where('id',$budget['id']);	
							
					$this->company_db->update('budgets');	

					//historial in original budget
					$this->company_db->set('budgetId',$budget['id']);
					$this->company_db->set('date',getCurrentDate());	 		
					$this->company_db->set('typeId',"RTC");
					$this->company_db->set('observation',"Reasignado a la empresa <strong>".$destinationCompanyName."</strong> como presupuesto Nro. <strong>".$newBudgetId."</strong>");
					$this->company_db->set('entity',"company");
					$this->company_db->set('entityId',$destinationCompanyId);
					$this->company_db->set('userId',NULL);									
					$this->company_db->set('deleted',0);
					$this->company_db->set('deletedDate',NULL);
					$this->company_db->set('deletedUserId',NULL);
						
					$this->company_db->insert('historyByBudget');
				}
			}
		} else {
			if ($showLog)
				echo "No se encontraron presupuestos para reasignar en la cía ".$originCompanyName."<br>";
		}
		
	} 

	function _getEntityId($table="",$description="",$db=1) {
		if ($table == "" || $description == "") return 0;

		$sql = "SELECT id  
	        	FROM ".$table."   
				WHERE deleted = 0 AND description = '".$description."'";					
		
		if ($db == 2) {
			$query = $this->company_db_2->query($sql);			
		} else {
			$query = $this->company_db->query($sql);			
		}
		if ($query->num_rows() == 1){
			$data = $query->row_array();				

			return $data['id'];
		} else {
			return 0;
		}		
	}

	function updateDatabase($companyId=-1,$queries=null) {
		if ($companyId < 0 || !isset($queries) || count($queries) <= 0) return;

		$this->setCompanyDatabase($companyId);
		for ($i=0; $i < count($queries); $i++) {
			if (trim($queries[$i]) != "") {
				$query = $this->company_db->query($queries[$i]);
			}
		}
	}
}

/* End of file Tasks_model.php */
/* Location: ./application/models/Tasks_model.php */