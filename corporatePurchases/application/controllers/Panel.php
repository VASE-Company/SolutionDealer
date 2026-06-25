<?php
/**
* Controlador Panel
*
*/
class Panel extends CI_Controller {        
    public function __construct() {
		parent::__construct();
		
	   	$this->output->set_template('default'); 
		$this->output->set_title($this->config->item('applicationName'));	
		$this->form_validation->set_error_delimiters(errorFormatStart(),errorFormatEnd());	
		$this->load->model('panel_model','panel');	
		$this->load->model('notifications_model','notifications');	
		$this->load->js('assets/js/panel.js');						
   	}
	
	function index(){											
		$this->_loadScreen();			
	}
		
	function _loadScreen() {

		// Guardia de sesion del panel; se deja limpia porque un caracter suelto aca rompe el controlador completo.
		if (!$this->session->userdata('userLoggedIn')) {
			redirect('/main/logout');	
		} else {			

			$data['menuActive'] = "GeneralPanel";
			$data['title'] = "Panel General";
			$data['contentView'] = 'panel/panel_view';
						
			$contentData['allowOrders'] = $this->my_application->hasPermission("Orders","See");	
			if ($contentData['allowOrders']) {
				$contentData['ordersSummary'] = $this->panel->getOrdersSummary();
			}			

			$notificationsTypesParameters['userId'] = $this->session->userdata('userId');
			$notificationsTypes = $this->notifications->getNotificationsTypes($notificationsTypesParameters);
			$contentData['notificationsTypes'] = $notificationsTypes["list"];

			$contentData['readStates'] = getListBoolean();			

			$contentData['readFilter'] = "0";	
			
			$contentData['callback'] = "initializaPanel();";

			$data['contentData'] = $contentData;			
	
			$this->my_application->loadGeneralTemplate($data);	
		}
	}	

	function _getNotificationsParameters($page=NULL) {

		//By default notGet=false and notPagination=false		
		$data['page'] = array('value'=>$page);		
		
		$data['textFilter'] = array('getField'=>'text');	
		$data['dateFromFilter'] = array('getField'=>'df');	
		$data['dateToFilter'] = array('getField'=>'dt');	
		$data['typeIdFilter'] = array('getField'=>'type');			
		$data['readFilter'] = array('getField'=>'read');
		$data['userIdFilter'] = array('value'=>$this->session->userdata('userId'));

		$data['fieldOrder'] = array('getField'=>'fOrd','type'=>'order');
		$data['typeOrder'] = array('getField'=>'tOrd','type'=>'order');

		foreach ($data as $key => $values) {
			if (!(isset($data[$key]['value']) && $data[$key]['value'] != "")) {
				$data[$key]['value'] = NULL;
				if ($this->input->post($key,TRUE) != "") {
					$data[$key]['value'] = $this->input->post($key,TRUE);
				} else {
					if (isset($values['getField']) && $values['getField'] != "") { 
						if ($this->input->get($values['getField'],TRUE) != "") {
							$data[$key]['value'] = $this->input->get($values['getField'],TRUE);
						}
					}
				}
			}
		}

		if (!(isset($data['fieldOrder']['value']) && $data[$key]['value'] != "")) $data['fieldOrder']['value'] = "date";
		if (!(isset($data['typeOrder']['value']) && $data[$key]['value'] != "")) $data['typeOrder']['value'] = "desc";
		
		$parameters = NULL;
		$filterGet = "";
		$orderGet = "";
		$pageParameters = NULL;		
		
		foreach ($data as $key => $values) {
			$parameters[$key] = $values['value'];
			if (isset($values['getField']) && $values['getField'] != "" &&				
		        $values['value'] != NULL && $values['value'] != "") { 
				
				$type = (isset($values['type']) && $values['type'] != ""?$values['type']:"");
				switch ($type) {
					case 'order':
						$orderGet .= "&".$values['getField']."=".$values['value'];	
						break;
					
					default:
						$filterGet .= "&".$values['getField']."=".$values['value'];	
						break;
				}

				if (!(isset($values['notPagination']) && $values['notPagination'] == true)) {
					$pageParameters[$values['getField']] = $values['value'];
				}
			}
		}		

		$parametersdData['parameters'] = $parameters;		
		$parametersdData['filterGet'] = $filterGet;
		$parametersdData['orderGet'] = $orderGet;
		$parametersdData['pageParameters'] = $pageParameters;
		
		return $parametersdData;	
	}

	function notifications($page=1) {
		if (!$this->session->userdata('userLoggedIn')) exit;

		$parametersdData = $this->_getNotificationsParameters($page);
										
		$parameters = $parametersdData['parameters'];		

		foreach ($parameters as $key => $val) {
			$contentData[$key] = $val;
		}
		
		$contentData['filterGet'] = $parametersdData['filterGet'];
		$contentData['orderGet'] = $parametersdData['orderGet'];
						
		$notifications = $this->notifications->getNotifications($parameters);
		$contentData['notifications'] = $notifications["list"];		
						
		$pageConfiguration = getPageConfiguration(base_url().'panel/notifications',$this->config->item('recordsPerPage'),$notifications["totalRecords"],$parametersdData['pageParameters']);
		$this->pagination->initialize($pageConfiguration); 
		$contentData['pagination'] = convertPageToAjax(applyPageStyles($this->pagination->create_links()),'reloadNotifications');			
		
		$contentData['byAjax'] = true;					
		$view = $this->load->view("panel/panel_notifications_list_view",$contentData,true);			
		$this->output->set_output($view);	
	}					
		
}

/* End of file Panel.php */
/* Location: ./application/controllers/Panel.php */
