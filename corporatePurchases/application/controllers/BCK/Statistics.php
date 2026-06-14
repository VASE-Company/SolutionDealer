<?php
/**
* Controlador Statistics
*
*/

defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Statistics extends CI_Controller {
    
    public function __construct() {
		parent::__construct();
		
	   	$this->output->set_template('default'); 
		$this->output->set_title($this->config->item('applicationName'));	
		$this->form_validation->set_error_delimiters(errorFormatStart(),errorFormatEnd());	
		$this->load->model('statistics_model','statistics');						
		$this->load->js('assets/js/statistics.js');					
		$this->load->js('assets/plugins/chart.js/Chart.min.js');	
   	}
	
	function index(){		
		$this->listing();						
	}			
		
	function listing()	
	{
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("Reports","See")) {						
			redirect('/main/logout');			
		} else {						
			
			$reports = array();			
			$reports[count($reports)] = array('id'=>'general_summary',
											  'description'=>'Resumen General');										
						
			$contentData['reports'] = $reports;				

			$contentData['dateFromFilter'] = getFirstDayMonth();
			$contentData['dateToFilter'] = getCurrentDate(false);					

			$data['menuActive'] = "Statistics";
			$data['title'] = "Estadísticas";
			$data['contentView'] = 'statistics/statistics_view';
			$data['contentData'] = $contentData;
			$this->my_application->loadGeneralTemplate($data);	
		}
	}	

	function _getParameters() {	
				
		$data['type'] = array('getField'=>'type','type'=>2);
		$data['dateFromFilter'] = array('getField'=>'df');	
		$data['dateToFilter'] = array('getField'=>'dt');					

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

		$parameters = NULL;
		$filterExportGet = "";
		$filterFullBudgetsGet = "";
		$filterBudgetsGet = "";
		
		foreach ($data as $key => $values) {
			$parameters[$key] = $values['value'];
			if (isset($values['getField']) && $values['getField'] != "" &&				
		        $values['value'] != NULL && $values['value'] != "") { 	
		        
		        $type = (isset($values['type'])?(int)$values['type']:0);
		    	if ($type == 0) $filterBudgetsGet .= "&".$values['getField']."=".$values['value'];
		        if ($type <= 1) $filterFullBudgetsGet .= "&".$values['getField']."=".$values['value'];	
		        if ($type <= 2) $filterExportGet .= "&".$values['getField']."=".$values['value'];	
			}
		}

		$parametersdData['parameters'] = $parameters;	
		$parametersdData['filterExportGet'] = $filterExportGet;	
		$parametersdData['filterFullBudgetsGet'] = $filterFullBudgetsGet;	
		$parametersdData['filterBudgetsGet'] = $filterBudgetsGet;	
		
		return $parametersdData;	
	}

	function result() {
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("Reports","See")) exit;		

		$parametersdData = $this->_getParameters();										
		$parameters = $parametersdData['parameters'];
						
		$contentData['filterExportGet'] = $parametersdData['filterExportGet'];	
		$contentData['filterFullBudgetsGet'] = $parametersdData['filterFullBudgetsGet'];	
		$contentData['filterBudgetsGet'] = $parametersdData['filterBudgetsGet'];	
		
		$result = $this->_getResult($parameters);		
		if (isset($result)) {							
			$contentData['entities'] = $result['entities'];		
			$contentData['columns'] = $result['columns'];	
			$contentData['data'] = $result['data'];													
		}

		$contentData['allowExport'] = $this->my_application->hasPermission("Reports","Export");				

		$contentData['callback'] = 'initializeResultsStatistics()';							
		$contentData['byAjax'] = true;					
		$view = $this->load->view("statistics/statistics_results_view",$contentData,true);			
		$this->output->set_output($view);	
	}

	function _getResult($parameters) {

		$result = NULL;

		switch ($parameters["type"]) {
			case "general_summary": 									
				$result = $this->statistics->getGeneralStatisticsOfBudgets($parameters);					
			break;			
		}

		return $result;
	}

	function graph() {
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("Reports","See")) exit;							

		$contentData['byAjax'] = true;	
					
		$view = $this->load->view("statistics/statistics_results_graph_view",$contentData,true);
		
		$this->output->set_output($view); 										

	}

	function export()
	{							
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("Reports","Export")) exit;											

		$json = trim($this->input->post('data',FALSE));	
		$rowsCount = 0;
		$colsCount = 0;

		if ($json != "") {		
			$data = json_decode($json);	

			$rowsCount = count($data);
			if ($rowsCount > 0) {
				$colsCount = count($data[0]);
			}
		}
		
		if ($json == "" || $rowsCount == 0 || $colsCount == 0) {				
			$contentData['data'] = "No se pudo exportar los datos.";
			$contentData['byAjax'] = true;	
			$view = $this->load->view("general_data_view",$contentData,true);			
			$this->output->set_output($view);				
		} else {			
			$title = trim($this->input->post('title',FALSE));	 	

			// INITIALITE SPREADSHEET
				
			$spreadsheet = new Spreadsheet();               
			
	        $sheet = $spreadsheet->getActiveSheet();

	        $sheet->setTitle($title);
			
	        // TITLE
	        $row = 1;
	        $sheet->setCellValue('A'.$row,$title); 
	        $styleCell = array('font'=>array('bold'=>true,'size'=>16));
	        $sheet->getStyle('A'.$row)->applyFromArray($styleCell);
	        $sheet->mergeCells('A'.$row.':'.getLetterOfExcelColumn($colsCount).$row);

	        // PARAMETERS  
	        /*      
	        $styleCell = array('font'=>array('bold'=>true));
	        for ($i=0; $i < count($parametersDescription); $i++) {
	        	$row++;
	        	$sheet->setCellValue('A'.$row,$parametersDescription[$i]['label']); 
	        	$sheet->setCellValue('B'.$row,$parametersDescription[$i]['data']);
	        	$sheet->getStyle('A'.$row)->applyFromArray($styleCell);
	        	$sheet->mergeCells('B'.$row.':'.'E'.$row);
	        }
	        */

	        //STYLES
	        $styleCellHeader = array(
						            'fill' => array(
						                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
						                'startColor' => array('argb' => '00000000')
						            	),
						            'font' => array(
						            	'bold'=>true,
						            	'color' => array('argb' => 'FFFFFFFF')
						            	)
							        );

	        $styleCellFooter = array(						            
						            'font' => array(
						            	'bold'=>true					            
						            	)
							        );

	        //DATA	        
	        $row++;
	        for ($i=0; $i < $rowsCount; $i++) {	        	
				$row++;
		        
		        for ($j=0; $j < $colsCount; $j++) {	        	
		        	$col = $j + 1;
		        	$sheet->setCellValue(getLetterOfExcelColumn($col).$row,$data[$i][$j]); 

		        	if ($col == $colsCount) {
			        	if ($i == 0) {		        	
							$sheet->getStyle('A'.$row.":".getLetterOfExcelColumn($col).$row)->applyFromArray($styleCellHeader);
				        } else {
				        	if ($i == ($rowsCount - 1)) {
				        		$sheet->getStyle('A'.$row.":".getLetterOfExcelColumn($col).$row)->applyFromArray($styleCellFooter);
				        	}
				        }
				    }
		        }		        
			}	

			for ($i=1; $i <= $colsCount; $i++) {
				$sheet->getColumnDimension(getLetterOfExcelColumn($i))->setAutoSize(true);
			}

			// CREATE TEMP FILE EXCEL, DOWNLOAD Y DELETE 

	        $writer = new Xlsx($spreadsheet); 

			$folder = $this->config->item('files').$this->session->userdata('companyId').'/tmp/';
			 
			$filename = 'estadísticas_'.strtolower(str_replace(' ','_',$title)).'_'.getCurrentDateId().'.xlsx';
			 
			$writer->save($folder.$filename); 

			$this->load->helper('download');
								
			$fileContent = file_get_contents($folder.$filename);	

			@unlink($folder.$filename);				
								
			force_download($filename,$fileContent);	
		}

	}	
		
}

/* End of file Statistics.php */
/* Location: ./apliccation/controllers/Statistics.php */