<?php
/**
* Controlador Users
*
*/
class GeneralConfigurations extends CI_Controller {
    
    public function __construct() {
		parent::__construct();
		
	   	$this->output->set_template('default'); 
		$this->output->set_title($this->config->item('applicationName'));	
		$this->form_validation->set_error_delimiters(errorFormatStart(),errorFormatEnd());		
		$this->load->model('generalconfigurations_model','generalConfigurations');		
		$this->load->model('companies_model','companies');		
		$this->load->model('users_model','users');
		$this->load->model('branchoffices_model','branchOffices');
		$this->load->js('assets/js/generalConfigurations.js');									
   	}
	
	function index(){		
		$this->edit();		
	}			
	
	function edit($error='',$message='') {
		if (!$this->my_application->hasPermission("GeneralConfiguration","See") && !$this->my_application->hasPermission("GeneralConfiguration","Edit")) {
			redirect('/main');				
		} else {			
			$generalConfiguration = $this->generalConfigurations->getGeneralConfigurations();

			$contentData['allowSave'] = $this->my_application->hasPermission("GeneralConfiguration","Edit");	

			$branchOfficesParameters['active'] = true;
			$branchOfficesParameters['idFilter'] = $generalConfiguration['defaultReassignBOId'];			
			$branchOffices = $this->branchOffices->getBranchOffices($branchOfficesParameters);
			$contentData['branchOffices'] = $branchOffices["list"];	;		

			$assessorsParameters['typeFilter'] = "assessor";	
			$assessorsParameters['typeOrIdFilter'] = $generalConfiguration['defaultReassignASSId'];	
			$assessorsParameters['onlyAllowedFilter'] = false;			
			$assessorsParameters['accessByBranchOfficeFilter'] = true;	
			$assessorsParameters['branchOfficeIdFilter'] = $generalConfiguration['defaultReassignBOId'];
			$assessorsParameters['idFilter'] = $generalConfiguration['defaultReassignASSId'];			
			$assessors = $this->users->getUsers($assessorsParameters);
			$contentData['assessors'] = $assessors["list"];	

			$workshopManagersParameters['typeFilter'] = "workshopManager";
			$workshopManagersParameters['typeOrIdFilter'] = $generalConfiguration['defaultReassignWMId'];	
			$workshopManagersParameters['onlyAllowedFilter'] = false;
			$workshopManagers = $this->users->getUsers($workshopManagersParameters);
			$contentData['workshopManagers'] = $workshopManagers["list"];																	
				
			$contentData['generalConfiguration'] = $generalConfiguration;	
			$contentData['lstBoolean'] = getListBoolean();	
			$contentData['companies'] = $this->companies->getCompanies("id <> ".$this->session->userdata('companyId',true));
			$contentData['error'] = $error;					
			$contentData['message'] = $message;		
			$contentData['callback'] = 'initializeGralConfigEdit()';									

			$data['menuActive'] = "GeneralConfiguration";
			$data['title'] = "Configuración General";			
			$data['contentView'] = 'generalConfigurations/general_configurations_edit_view';
			$data['contentData'] = $contentData;			
			$this->my_application->loadGeneralTemplate($data);
		}
	}

	function save() {      	       		
		if (!$this->my_application->hasPermission("GeneralConfiguration","Edit")) {	
			redirect('/main');	
		} else {			
											
			$this->form_validation->set_rules('priceUpdatesDays','Act. Precios','trim|max_length[3]|xss_clean|required|numeric');	
			$this->form_validation->set_rules('priceUpdatesDaysReminder','Record. Act. Precios','trim|max_length[3]|xss_clean|required|numeric');	
			$this->form_validation->set_rules('callBudgetsDays','Llamar a Pptos.','trim|max_length[3]|xss_clean|required|numeric');	
			$this->form_validation->set_rules('pendingBillsDays','Facturas Impagas','trim|max_length[3]|xss_clean|required|numeric');	
			$this->form_validation->set_rules('pendingBillsDaysReminder','Record. Facturas Impagas','trim|max_length[3]|xss_clean|required|numeric');	
			$this->form_validation->set_rules('allowHideCodeArticleExportMail','Permite ocultar Cód. Art. en Pptos.','trim|xss_clean|required');	
			$this->form_validation->set_rules('reassignToCompanyId','Reasigna Pptos. a Cía.','trim|xss_clean|required');	
			$this->form_validation->set_rules('allowReassignFromCompany','Permite reasignar desde otras Cías.','trim|xss_clean|required');		
			$this->form_validation->set_rules('defaultReassignBOId','Reasignar a Sucursal','trim|xss_clean|required');		
			$this->form_validation->set_rules('defaultReassignASSId','Reasignar a Asesor','trim|xss_clean|required');	
			$this->form_validation->set_rules('defaultReassignWMId','Reasignar a Resp. Taller','trim|xss_clean|required');	
	
			if ($this->form_validation->run() != FALSE) {														
				$data = array('id'=>$this->input->post('id',true),									   									   
							  'priceUpdatesDays'=>$this->input->post('priceUpdatesDays',true),
							  'priceUpdatesDaysReminder'=>$this->input->post('priceUpdatesDaysReminder',true),
							  'callBudgetsDays'=>$this->input->post('callBudgetsDays',true),							  
							  'pendingBillsDays'=>$this->input->post('pendingBillsDays',true),	
							  'pendingBillsDaysReminder'=>$this->input->post('pendingBillsDaysReminder',true),	
							  'allowHideCodeArticleExportMail'=>$this->input->post('allowHideCodeArticleExportMail',true),	
							  'reassignToCompanyId'=>$this->input->post('reassignToCompanyId',true),								  
							  'allowReassignFromCompany'=>$this->input->post('allowReassignFromCompany',true),	
							  'defaultReassignBOId'=>$this->input->post('defaultReassignBOId',true),	
							  'defaultReassignASSId'=>$this->input->post('defaultReassignASSId',true),	
							  'defaultReassignWMId'=>$this->input->post('defaultReassignWMId',false));								
				if ($this->generalConfigurations->setGeneralConfigurations($data)) {																
					$this->edit(NULL,"Los datos se grabaron correctamente");
				} else {	
					$error = "No se pudo grabar los datos, intente nuevamente";
					$this->edit($error);
				}      
			} else {
				$this->edit();		
			}
		}		
	}			
	
}
/* End of file Users.php */
/* Location: ./application/controllers/Users.php */