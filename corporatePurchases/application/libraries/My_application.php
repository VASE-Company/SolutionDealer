<?php if (!defined('BASEPATH')) exit('No permitir el acceso directo al script'); 
class my_application {
	
	function hasPermission($module=NULL,$action=NULL,$rolesId=NULL){		
		$CI =& get_instance();
		
		if ($CI->config->item('suspendedSystem')) {
			return false;
		}		

		if (!$CI->session->userdata('userLoggedIn')) {
			return false;
		}	
		
		if (!isset($rolesId) || (isset($rolesId) && $rolesId == "")) { 
			$rolesId = $CI->session->userdata('userRolesId');
		}
				
		$CI->load->model('roles_model','roles');
		return $CI->roles->hasPermission($rolesId,$module,$action); 
	}

	function _getUserData() {
		$CI =& get_instance();
				
		$data['loggedIn'] = $CI->session->userdata('userLoggedIn');
		if ($CI->session->userdata('userLoggedIn') == true) {
			$data['name'] = $CI->session->userdata('userName');
		   	$data['image'] = $this->imageOfUser($CI->session->userdata('userId')); 
		   	if ($this->hasPermission("MyProfile","See")) {
		   		$data['link'] = base_url().'users/myProfile'; 
		   	} else {
		   		$data['link'] = "#";
		   	}
		}	
		
		return $data;
	}

	function imageOfUser($userId=-1) {
		$CI =& get_instance();	
		
		$image = "";
		if ($userId > 0) {			

			$extensions = $CI->config->item('extensionsOfImageUser');			

			for ($i=0; $i < count($extensions) && $image == ""; $i++) {			
			   	$file = $CI->config->item('images')."users/user_".$userId.".".$extensions[$i];
			   	if (file_exists($file)) {
			   		$image = $file;			   	
			   	}			
			}
		}
		if ($image == "") {
			$image = $CI->config->item('images')."user.jpg";
		}

		return $image;			
	}

	function _getCompanyData() {
		$CI =& get_instance();
				
		$data['loggedIn'] = $CI->session->userdata('userLoggedIn');
		if ($CI->session->userdata('userLoggedIn') == true) {
			$data['companyName'] = strtoupper($CI->session->userdata('userCompanyDescription'));		 
			$data['branchOfficeName'] = strtoupper($CI->session->userdata('userBranchOfficeDescription'));		 

			/*
		   	if (file_exists($CI->config->item('images')."logo.png")) {
				$data['companyLogo'] = $CI->config->item('images')."logo.png";			
			}
			*/
		}	
		
		return $data;
	}


	function _getMainHeaderData($title="") {		
		$data['title'] = $title;
				
		return $data;
	}


	function _getMenuData($menuActive="") {
		$CI =& get_instance();
		$CI->load->model('roles_model','roles');
		
		$permissions = $CI->roles->getRolePermissions($CI->session->userdata('userRolesId'));	
		
		$menu = array();		
		$idxMenu = -1;	
			
		$idxMenu++;
		$menu[$idxMenu] = array('icon'=>'fas fa-tachometer-alt', 'text'=>'Panel General', 'link'=>base_url().'panel', 'active'=>($menuActive == 'GeneralPanel'));  					
		
		if ($this->_existsPermission('Orders',"See",$permissions)){
			$idxMenu++;
			$menu[$idxMenu] = array('icon'=>'fa fa-briefcase', 'text'=>'Pedidos', 'link'=>base_url().'orders', 'active'=>($menuActive == 'Orders'));  					
		}		

		$subMenu = array();
		$idxSubMenu = -1;		
		if ($this->_existsPermission('Articles',"See",$permissions)){
			$idxSubMenu++;
			$subMenu[$idxSubMenu] = array('text'=>'Artículos', 'link'=>base_url().'articles', 'active'=>($menuActive == 'Articles'));  					
		}
		
		if ($this->_existsPermission('Suppliers',"See",$permissions)){
			$idxSubMenu++;
			$subMenu[$idxSubMenu] = array('text'=>'Proveedores', 'link'=>base_url().'suppliers', 'active'=>($menuActive == 'Suppliers'));  					
		}

		if ($this->_existsPermission('Bills',"See",$permissions)){
			$idxSubMenu++;
			$subMenu[$idxSubMenu] = array('text'=>'Facturas de Compras', 'link'=>base_url().'bills', 'active'=>($menuActive == 'Bills'));  					
		}

		if ($this->_existsPermission('StockMovements',"See",$permissions)){
			$idxSubMenu++;
			$subMenu[$idxSubMenu] = array('text'=>'Mov. de Stock', 'link'=>base_url().'stockMovements', 'active'=>($menuActive == 'StockMovements'));  					
		}
		
		if ($this->_existsPermission('Reports',"Stock",$permissions)){
			$idxSubMenu++;		
			$subMenu[$idxSubMenu] = array('text'=>'Resumen de Stock', 'link'=>base_url().'reports?type=stock', 'active'=>($menuActive == 'ReportStock'));
		}

		if ($idxSubMenu != -1) {
			$active = false;
			for ($j=0; $j < count($subMenu); $j++) {
				if ($subMenu[$j]['active']) {
					$active = true;
					break;
				} 
			}

			$idxMenu++;			
			$menu[$idxMenu] = array('icon'=>'fa fa-cubes', 'text'=>'Stock', 'link'=>'', 'submenu'=>$subMenu, 'active'=>$active);  					
		}	
				
		$subMenu = array();
		$idxSubMenu = -1;	
		if ($this->_existsPermission('Reports',"General",$permissions)){
			$idxSubMenu++;		
			$subMenu[$idxSubMenu] = array('text'=>'Reporte General', 'link'=>base_url().'reports?type=general', 'active'=>($menuActive == 'ReportGeneral'));
		}																				
		if ($this->_existsPermission('Reports',"EstimatedPurchase",$permissions)){
			$idxSubMenu++;		
			$subMenu[$idxSubMenu] = array('text'=>'Estimación de Compras', 'link'=>base_url().'reports?type=estimatedPurchase', 'active'=>($menuActive == 'ReportEstimatedPurchase'));
		}																
		if ($idxSubMenu != -1) {
			$active = false;
			for ($j=0; $j < count($subMenu); $j++) {
				if ($subMenu[$j]['active']) {
					$active = true;
					break;
				} 
			}

			$idxMenu++;
			$menu[$idxMenu] = array('icon'=>'fa fa-chart-line', 'text'=>'Reportes', 'link'=>'', 'submenu'=>$subMenu , 'active'=>$active); 
		}

				
		$subMenu = array();
		$idxSubMenu = -1;				
		if ($this->_existsPermission('Companies',"See",$permissions)){
			$idxSubMenu++;		
			$subMenu[$idxSubMenu] = array('text'=>'Empresas', 'link'=>base_url().'companies', 'active'=>($menuActive == 'Companies'));
		}	
		if ($this->_existsPermission('Sectors',"See",$permissions)){
			$idxSubMenu++;		
			$subMenu[$idxSubMenu] = array('text'=>'Sectores', 'link'=>base_url().'sectors', 'active'=>($menuActive == 'Sectors'));
		}	
		if ($this->_existsPermission('BranchOffices',"See",$permissions)){
			$idxSubMenu++;		
			$subMenu[$idxSubMenu] = array('text'=>'Sucursales', 'link'=>base_url().'branchOffices', 'active'=>($menuActive == 'BranchOffices'));
		}	
		if ($this->_existsPermission('Roles',"See",$permissions)){
			$idxSubMenu++;		
			$subMenu[$idxSubMenu] = array('text'=>'Roles', 'link'=>base_url().'roles', 'active'=>($menuActive == 'Roles'));
		}				
		if ($this->_existsPermission('Users',"See",$permissions)){
			$idxSubMenu++;		
			$subMenu[$idxSubMenu] = array('text'=>'Usuarios', 'link'=>base_url().'users', 'active'=>($menuActive == 'Users'));
		}
		if ($this->_existsPermission('Families',"See",$permissions)){
			$idxSubMenu++;		
			$subMenu[$idxSubMenu] = array('text'=>'Rubros Art.', 'link'=>base_url().'families', 'active'=>($menuActive == 'Families'));
		}	
		if ($this->_existsPermission('Warehouses',"See",$permissions)){
			$idxSubMenu++;		
			$subMenu[$idxSubMenu] = array('text'=>'Depósitos', 'link'=>base_url().'warehouses', 'active'=>($menuActive == 'Warehouses'));
		}
		if ($this->_existsPermission('MovementsTypes',"See",$permissions)){
			$idxSubMenu++;		
			$subMenu[$idxSubMenu] = array('text'=>'Conceptos Mov. Stock', 'link'=>base_url().'movementsTypes', 'active'=>($menuActive == 'MovementsTypes'));
		}
		if ($this->_existsPermission('Backup',"Generate",$permissions)){
			$idxSubMenu++;		
			$subMenu[$idxSubMenu] = array('text'=>'Backup Base de Datos', 'link'=>'javascript:generateBackup()', 'active'=>($menuActive == 'Backup'));
		}															
		if ($idxSubMenu != -1) {
			$active = false;
			for ($j=0; $j < count($subMenu); $j++) {
				if ($subMenu[$j]['active']) {
					$active = true;
					break;
				} 
			}

			$idxMenu++;
			$menu[$idxMenu] = array('icon'=>'fas fa-plus-square', 'text'=>'Más', 'link'=>'', 'submenu'=>$subMenu , 'active'=>$active); 
		}	
										
		$idxMenu++;
		$menu[$idxMenu] = array('icon'=>'fas fa-sign-in-alt', 'text'=>'Cerrar Sesión', 'link'=>'javascript:confirmLogout()');  

		$data['menu'] = $menu;
		
		return $data;
	}	

	function getMainHeaderNotificationsData() {		
		$CI =& get_instance();

		$CI->load->model('notifications_model','notifications');		
		$parameters = array('readFilter'=> '0',
			                'userIdFilter'=>$CI->session->userdata('userId'),
			                'fieldOrder'=>'date',
			            	'typeOrder'=>'desc',
			            	'page'=>1,
			            	'recordsPerPage'=>5);
		$notifications = $CI->notifications->getNotifications($parameters);	

		$data['notifications'] = $notifications['list'];
		$data['countOfNotifications'] = $notifications['totalRecords'];
				
		return $data;
	}

	function loadGeneralTemplate($data=null) {
		$CI =& get_instance();

		$CI->load->section('companyData','main/company_data_view',$this->_getCompanyData());	
		
		$CI->load->section('userData','main/user_data_view',$this->_getUserData());		
		
		$title = (isset($data['title'])?$data['title']:"");
		$CI->load->section('mainHeader','main/main_header_view',$this->_getMainHeaderData($title));	
		
		$CI->load->section('mainHeaderNotifications','main/main_header_notifications_view',$this->getMainHeaderNotificationsData());	

		$menuActive = (isset($data['menuActive'])?$data['menuActive']:"");
		$CI->load->section('sidebarMenu','main/sidebar_menu_view',$this->_getMenuData($menuActive));	

		if (isset($data['contentView'])) {
			$contentData = (isset($data['contentData'])?$data['contentData']:NULL);
			$CI->load->section('mainContent',$data['contentView'],$contentData);	
		}

		$CI->load->view('templates/template_general');	
	}

	function _existsPermission($module=NULL, $action=NULL, $permissions=NULL){				
		if (isset($permissions)) {
			for($i=0; $i < count($permissions); $i++) {
				if ($module != NULL && $action != NULL) {
					if ($module == $permissions[$i]['module'] && $action == $permissions[$i]['action']) {
						return true;
					}
				} else {
					if ($module != NULL && $module == $permissions[$i]['module']) {
						return true;
					}
					if ($action != NULL && $action == $permissions[$i]['action']) {
						return true;
					}
				}
			}
		}	
		
		return false;
	}	

	function sendMail($to="",$subject="",$message="",$responseTo="",$fromShow="",$attachments=null,$log=null) {
		if (isset($to) && $to != "" && 
 		    isset($subject) && $subject != "" && 
			isset($message) && $message != "") {
			
			$CI =& get_instance();

			$systemMailConfig = $CI->config->item('systemMailConfig');			
			
			if ($systemMailConfig['send'] == true) {
				$CI->load->library('email');

				$CI->email->clear(TRUE);
							
				$configMail['smtp_host'] = $systemMailConfig['host'];
				$configMail['smtp_user'] = $systemMailConfig['user'];
				$configMail['smtp_pass'] = $systemMailConfig['password'];
				$configMail['validate'] = TRUE;
				$configMail['mailtype'] = 'html';
												
				$CI->email->initialize($configMail);

				if ($fromShow == "") $fromShow = $CI->config->item('applicationName');			
				$fromShow = removeAccents($fromShow);						
				$CI->email->from($configMail['smtp_user'],$fromShow);
				if ($responseTo != "") $CI->email->reply_to($responseTo); 												
				$CI->email->to($to); 			
				$CI->email->subject($subject);
				$CI->email->message($message);		


				if (isset($attachments) && count($attachments) > 0) {					
					for ($i=0; $i < count($attachments); $i++) {
						if ($attachments[$i]['path'] != "" && $attachments[$i]['filename'] != "") { 
							if (file_exists($attachments[$i]['path'])) {								
								$CI->email->attach($attachments[$i]['path'],'attachment',$attachments[$i]['filename']);						
							}
						}
					}
				}

				$sendOK = $CI->email->send(false);					
				//$sendOK = true;
			} else {		
				$sendOK = true;
			}
			
			if (
        			($sendOK && isset($systemMailConfig['logOK']) && $systemMailConfig['logOK'] == true) ||
        			(!$sendOK && isset($systemMailConfig['logERROR']) && $systemMailConfig['logERROR'] == true)                
		       ) 
			{  
				$CI->load->model('notifications_model','notifications');

				$logObservation = $to." - ".$subject;
				if (!$sendOK) {
					$logObservation .= $CI->email->print_debugger();
				}				

				$notification = array('typeId'=>($sendOK?"OK":"ERROR"), 
									  'observation'=>substr($logObservation,0,250),
									  'entity'=>(isset($log['entity'])?$log['entity']:""),
									  'entityId'=>(isset($log['entityId'])?$log['entityId']:""),
									  'userId'=>1); //Por ahora sólo al super administrador
				$CI->notifications->setNotification($notification);
			}

			return $sendOK;																													
		} else {			
			return false;
		}			
	}

}

/* End of file my_application.php */
/* Location: ./application/libraries/my_application.php */