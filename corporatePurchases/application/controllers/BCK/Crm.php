<?php
/**
* Controlador Crm
*
*/

defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Crm extends CI_Controller {
    
    public function __construct() {
		parent::__construct();
		
	   	$this->output->set_template('default'); 
		$this->output->set_title($this->config->item('applicationName'));	
		$this->form_validation->set_error_delimiters(errorFormatStart(),errorFormatEnd());	
		$this->load->model('crm_model','crm');		
		$this->load->model('users_model','users');
		$this->load->model('branchoffices_model','branchOffices');
		$this->load->model('families_model','families');		
		$this->load->model('budgetresponses_model','budgetResponses');		
		$this->load->js('assets/js/crm.js');				
   	}
	
	function index(){		
		$this->listing();						
	}				
		
	function listing()	
	{
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("CRM","See")) {						
			redirect('/main/logout');			
		} else {
			
			$budgetResponses = $this->budgetResponses->getBudgetResponses();
			$budgetResponses = $budgetResponses["list"];	
			$states = $this->config->item('budgetStates');
			for($i=0; $i < count($budgetResponses); $i++) {
				$states[$this->config->item('budgetStateCR').'|'.$budgetResponses[$i]['id']] = "Respuesta: ".$budgetResponses[$i]['description'];
			}
			$contentData['states'] = $states;			
			
			$workshopManagersParameters['typeFilter'] = "workshopManager";
			$workshopManagersParameters['onlyAllowedFilter'] = false;
			$workshopManagers = $this->users->getUsers($workshopManagersParameters);
			$contentData['workshopManagers'] = $workshopManagers["list"];		

			$assessorsParameters['typeFilter'] = "assessor";		
			$assessorsParameters['onlyAllowedFilter'] = false;			
			$assessors = $this->users->getUsers($assessorsParameters);
			$contentData['assessors'] = $assessors["list"];			

			$branchOffices = $this->branchOffices->getBranchOffices();
			$contentData['branchOffices'] = $branchOffices["list"];			
				
			$families = $this->families->getFamilies();
			$contentData['families'] = $families["list"];					
												
			$contentData['dateFromFilter'] = getFirstDayMonth();
			$contentData['dateToFilter'] = getCurrentDate(false);
			$contentData['vehicleYears'] = getListYears();	
					
			$data['menuActive'] = "CRM";
			$data['title'] = "CRM";
			$data['contentView'] = 'crm/crm_view';
			$data['contentData'] = $contentData;
			$this->my_application->loadGeneralTemplate($data);	
		}
	}	

	function _getParameters($page=NULL) {

		//By default notGet=false and notPagination=false		
		$data['page'] = array('value'=>$page);		
		
		$data['dateFromFilter'] = array('getField'=>'df');	
		$data['dateToFilter'] = array('getField'=>'dt');	
		$data['stateFilter'] = array('getField'=>'sta');	
		$data['workshopManagerIdFilter'] = array('getField'=>'wm');	
		$data['assessorIdFilter'] = array('getField'=>'ass');	
		$data['familyIdFilter'] = array('getField'=>'fam');	
		$data['branchOfficeIdFilter'] = array('getField'=>'bo');	
		$data['articleFilter'] = array('getField'=>'art');	
		$data['vehicleFilter'] = array('getField'=>'veh');	
		$data['yearFromFilter'] = array('getField'=>'yf');	
		$data['yearToFilter'] = array('getField'=>'yd');	
		$data['kmsFromFilter'] = array('getField'=>'kf');	
		$data['kmsToFilter'] = array('getField'=>'kt');	

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

		if (!(isset($data['fieldOrder']['value']) && $data[$key]['value'] != "")) $data['fieldOrder']['value'] = "cli";
		if (!(isset($data['typeOrder']['value']) && $data[$key]['value'] != "")) $data['typeOrder']['value'] = "asc";
		
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

	function result($page=1) {
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("CRM","See")) exit;

		$parametersdData = $this->_getParameters($page);
										
		$parameters = $parametersdData['parameters'];

		foreach ($parameters as $key => $val) {
			$contentData[$key] = $val;
		}
		
		$contentData['filterGet'] = $parametersdData['filterGet'];
		$contentData['orderGet'] = $parametersdData['orderGet'];
						
		$data = $this->crm->getData($parameters);
		$contentData['data'] = $data["list"];		
						
		$pageConfiguration = getPageConfiguration(base_url().'crm/result',$this->config->item('recordsPerPage'),$data["totalRecords"],$parametersdData['pageParameters']);
		$this->pagination->initialize($pageConfiguration); 
		$contentData['pagination'] = convertPageToAjax(applyPageStyles($this->pagination->create_links()),'reloadResultsCRM');			
		
		$contentData['allowExport'] = $this->my_application->hasPermission("CRM","Export");																	
		$contentData['byAjax'] = true;					
		$view = $this->load->view("crm/crm_result_view",$contentData,true);			
		$this->output->set_output($view);	
	}

	function export()
	{							
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("CRM","Export")) exit;											
				
		$parametersdData = $this->_getParameters();										
		$parameters = $parametersdData['parameters'];
		$data = $this->crm->getData($parameters);
		$data = $data["list"];	

		$parametersDescription = $this->_getParametersDescription($parameters);

		// INITIALITE SPREADSHEET
				
		$spreadsheet = new Spreadsheet();               
		
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Datos');
		
        // TITLE
        $row = 1;
        $sheet->setCellValue('A'.$row,"DATOS CRM"); 
        $styleCell = array('font'=>array('bold'=>true,'size'=>16));
        $sheet->getStyle('A'.$row)->applyFromArray($styleCell);
        $sheet->mergeCells('A'.$row.':'.'E'.$row);

        // PARAMETERS        
        $styleCell = array('font'=>array('bold'=>true));
        for ($i=0; $i < count($parametersDescription); $i++) {
        	$row++;
        	$sheet->setCellValue('A'.$row,$parametersDescription[$i]['label']); 
        	$sheet->setCellValue('B'.$row,$parametersDescription[$i]['data']);
        	$sheet->getStyle('A'.$row)->applyFromArray($styleCell);
        	$sheet->mergeCells('B'.$row.':'.'E'.$row);
        }

        // GRID HEADERS        
        $col = 0;
        
        $row++;
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Apellido y Nombres"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Email"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Teléfonos"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Vehículo"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Año"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Kms"); 

		$styleCell = array(
				            'fill' => array(
				                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				                'startColor' => array('argb' => '00000000')
				            	),
				            'font' => array(
				            	'bold'=>true,
				            	'color' => array('argb' => 'FFFFFFFF')
				            	)
					        );

		$sheet->getStyle('A'.$row.":".getLetterOfExcelColumn($col).$row)->applyFromArray($styleCell);		

		// GRID BODY

		for ($i=0; $i < count($data); $i++) {
			$row++;
	        $col = 0;
	        
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$data[$i]['clientName']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$data[$i]['clientEmail']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$data[$i]['clientPhones']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$data[$i]['vehicleDescription']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,((float)$data[$i]['vehicleYear'] > 0?$data[$i]['vehicleYear']:"")); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,((float)$data[$i]['vehiclekms'] > 0?$data[$i]['vehiclekms']:"")); 	 
		}		

		for ($i=1; $i <= $col; $i++) {
			$sheet->getColumnDimension(getLetterOfExcelColumn($i))->setAutoSize(true);
		}

		// CREATE TEMP FILE EXCEL, DOWNLOAD Y DELETE 

        $writer = new Xlsx($spreadsheet); 

		$folder = $this->config->item('files').$this->session->userdata('companyId').'/tmp/';
		 
		$filename = 'crm_datos_'.getCurrentDateId().'.xlsx';
		 
		$writer->save($folder.$filename); 

		$this->load->helper('download');
							
		$fileContent = file_get_contents($folder.$filename);	

		@unlink($folder.$filename);				
							
		force_download($filename,$fileContent);	
		
	}	

	function _getParametersDescription($parameters=null) {
		
		$parametersDescription = array();			

		$period = "";
		if (isset($parameters['dateFromFilter']) && $parameters['dateFromFilter'] != "") {				
			if ($period != "") $period .= " ";
			$period .= "desde el ".dateFormat($parameters['dateFromFilter']);											
		}
		if (isset($parameters['dateToFilter']) && $parameters['dateToFilter'] != "") {				
			if ($period != "") $period .= " ";
			$period .= "hasta el ".dateFormat($parameters['dateToFilter']);				
		}								
		if ($period != "") $parametersDescription[count($parametersDescription)] = array('label'=>'Periodo:','data'=>$period);							
		
		if (isset($parameters['stateFilter']) && $parameters['stateFilter'] != "") {					
			$arrState = explode("|",$parameters['stateFilter']);
			$state = strtoupper($arrState[0]);

			$states = $this->config->item('budgetStates');				
			$stateDescription = $states[$state];

			if ($state == $this->config->item('budgetStateCR') && count($arrState) > 1) {
				$auxId = (float)$arrState[1];
				if ($auxId > 0) {
					$budgetResponsesParameters['idFilter'] = $auxId;
					$budgetResponses = $this->budgetResponses->getBudgetResponses($budgetResponsesParameters);						
					$budgetResponses = $budgetResponses["list"];
					if (count($budgetResponses) == 1) {
						$stateDescription .= " '".$budgetResponses[0]['description']."'";							
					}
				}					
			}
			
			$parametersDescription[count($parametersDescription)] = array('label'=>'Estado:','data'=>$stateDescription);													
		}
		if (isset($parameters['workshopManagerIdFilter']) && $parameters['workshopManagerIdFilter'] != "") {	
			$workshopManagersParameters['idFilter'] = $parameters['workshopManagerIdFilter'];				
			$workshopManagers = $this->users->getUsers($workshopManagersParameters);
			if ($workshopManagers["totalRecords"] == 1) {
				$workshopManager = $workshopManagers["list"][0];
				$parametersDescription[count($parametersDescription)] = array('label'=>'Responsable:','data'=>trim($workshopManager['lastName'].", ".$workshopManager['firstName']));						
			}
		}
		if (isset($parameters['assessorIdFilter']) && $parameters['assessorIdFilter'] != "") {
			$assessorsParameters['idFilter'] = $parameters['assessorIdFilter'];				
			$assessors = $this->users->getUsers($assessorsParameters);
			if ($assessors["totalRecords"] == 1) {
				$assessor = $assessors["list"][0];
				$parametersDescription[count($parametersDescription)] = array('label'=>'Asesor:','data'=>trim($assessor['lastName'].", ".$assessor['firstName']));						
			}
		}
		if (isset($parameters['familyIdFilter']) && $parameters['familyIdFilter'] != "") {				
			$familiesParameters['idFilter'] = $parameters['familyIdFilter'];				
			$families = $this->families->getFamilies($familiesParameters);
			if ($families["totalRecords"] == 1) {
				$family = $families["list"][0];
				$parametersDescription[count($parametersDescription)] = array('label'=>'Familia:','data'=>$family['description']);						
			}
		}
		if (isset($parameters['branchOfficeIdFilter']) && $parameters['branchOfficeIdFilter'] != "") {	
			$branchOfficesParameters['idFilter'] = $parameters['branchOfficeIdFilter'];					
			$branchOffices = $this->branchOffices->getBranchOffices($branchOfficesParameters);
			if ($branchOffices["totalRecords"] == 1) {
				$branchOffice = $branchOffices["list"][0];
				$parametersDescription[count($parametersDescription)] = array('label'=>'Sucursal:','data'=>$branchOffice['description']);						
			}
		}	
		if (isset($parameters['articleFilter']) && $parameters['articleFilter'] != "") {	
			$parametersDescription[count($parametersDescription)] = array('label'=>'Artículo:','data'=>$parameters['articleFilter']);					
		}
		if (isset($parameters['vehicleFilter']) && $parameters['vehicleFilter'] != "") {
			$parametersDescription[count($parametersDescription)] = array('label'=>'Vehículo:','data'=>$parameters['vehicleFilter']);					
		}

		$years = "";
		if (isset($parameters['yearFromFilter']) && $parameters['yearFromFilter'] != "") {				
			if ($years != "") $years .= " ";
			$years .= "desde ".$parameters['yearFromFilter'];											
		}
		if (isset($parameters['yearToFilter']) && $parameters['yearToFilter'] != "") {				
			if ($years != "") $years .= " ";
			$years .= "hasta ".$parameters['yearToFilter'];				
		}								
		if ($years != "") {
			$parametersDescription[count($parametersDescription)] = array('label'=>'Año:','data'=>$years);					
		}	

		$kilometers = "";
		if (isset($parameters['kmsFromFilter']) && $parameters['kmsFromFilter'] != "") {				
			if ($kilometers != "") $kilometers .= " ";
			$kilometers .= "desde ".$parameters['kmsFromFilter'];											
		}
		if (isset($parameters['kmsToFilter']) && $parameters['kmsToFilter'] != "") {				
			if ($kilometers != "") $kilometers .= " ";
			$kilometers .= "hasta ".$parameters['kmsToFilter'];				
		}								
		if ($kilometers != "") {
			$parametersDescription[count($parametersDescription)] = array('label'=>'Kilómetros:','data'=>$kilometers);					
		}																					

		return $parametersDescription;
	}
		
}

/* End of file Crm.php */
/* Location: ./apliccation/controllers/Crm.php */