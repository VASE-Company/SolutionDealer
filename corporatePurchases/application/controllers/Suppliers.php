<?php
/**
* Controlador Suppliers
*
*/

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Suppliers extends CI_Controller {        
    public function __construct() {
		parent::__construct();
		
	   	$this->output->set_template('default'); 
		$this->output->set_title($this->config->item('applicationName'));	
		$this->form_validation->set_error_delimiters(errorFormatStart(),errorFormatEnd());	
		$this->load->model('suppliers_model','suppliers');									
   	}
	
	function index(){		
		$this->listing();		
	}
		
	function _getParameters($page=NULL,							
							$textFilter=NULL,
							$fieldOrder=NULL,$typeOrder=NULL) {

		//By default notGet=false and notPagination=false		
		$data['page'] = array('value'=>$page);
		$data['idFilter'] = array('getField'=>'id','notGet'=>true,'notPagination'=>true,'value'=>NULL);
		$data['textFilter'] = array('getField'=>'text','value'=>$textFilter);				
		$data['fieldOrder'] = array('getField'=>'fOrd','type'=>'order','value'=>$fieldOrder);
		$data['typeOrder'] = array('getField'=>'tOrd','type'=>'order','value'=>$typeOrder);
		foreach ($data as $key => $values) {
			if (isset($values['getField']) && $values['getField'] != "") { 
				if ($this->input->get($values['getField'],TRUE) != "") {
					$data[$key]['value'] = $this->input->get($values['getField'],TRUE);
				}
			}
		}
		
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
		
	function listing($page=1, 
					 $textFilter=NULL,
					 $fieldOrder='tn',$typeOrder='asc')
	{							
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("Suppliers","See")) {						
			redirect('/main/logout');	
		} 

		$contentData['allowInsert'] = $this->my_application->hasPermission("Suppliers","Insert");			
		$contentData['allowEdit'] = $this->my_application->hasPermission("Suppliers","Edit");
		$contentData['allowDelete'] = $this->my_application->hasPermission("Suppliers","Delete");			
		$contentData['allowExport'] = true;

		$parametersdData = $this->_getParameters($page,
												 $textFilter,
							                     $fieldOrder, $typeOrder);
										
		$parameters = $parametersdData['parameters'];

		foreach ($parameters as $key => $val) {
			$contentData[$key] = $val;
		}
						
		$contentData['filterGet'] = $parametersdData['filterGet'];
		$contentData['orderGet'] = $parametersdData['orderGet'];
						
		$suppliers = $this->suppliers->getSuppliers($parameters);
		$contentData['suppliers'] = $suppliers["list"];		
						
		$pageConfiguration = getPageConfiguration(base_url().'suppliers/listing',$this->config->item('recordsPerPage'),$suppliers["totalRecords"],$parametersdData['pageParameters']);
		$this->pagination->initialize($pageConfiguration); 
		$contentData['pagination'] = applyPageStyles($this->pagination->create_links());	
												
				
		$data['menuActive'] = "Suppliers";
		$data['title'] = "Proveedores";
		$data['contentView'] = 'suppliers/suppliers_list_view';
		$data['contentData'] = $contentData;
		$this->my_application->loadGeneralTemplate($data);	
	}

	function search() {			
		$this->form_validation->set_rules('textFilter','Buscar', 'trim|xss_clean');		
		$this->form_validation->set_rules('fieldOrder','Orden Campo', 'trim|xss_clean');
		$this->form_validation->set_rules('typeOrder','Orden Tipo', 'trim|xss_clean');
		
		if ($this->form_validation->run() != FALSE) {		
			$this->listing(1,
			           	   $this->input->post('textFilter',TRUE),
			               $this->input->post('fieldOrder',true),$this->input->post('typeOrder',true)
			           	   );
		} else {
			$this->listing();
		}		
	}
	
	function edit($id=-1, $error='') {
		if ((!$this->my_application->hasPermission("Suppliers","Insert") && $id <= 0) || 
			!$this->my_application->hasPermission("Suppliers","See")) {
			redirect('/main/logout');
		} else {
			
			if ($id > 0) {
				$parameters["idFilter"] = $id;
				$suppliers = $this->suppliers->getSuppliers($parameters);
				if ($suppliers['totalRecords'] == 1){
					$supplier = $suppliers['list'][0];	
				} else {
					$id = -1;
				}
			}
			if ($id <= 0) {
				$supplier = $this->suppliers->getEmptySupplier();
			}
													
			$contentData['supplier'] = $supplier;		
			$contentData['states'] = getListBoolean();								
			$contentData['error'] = $error;			
			$contentData['allowSave'] = (($this->my_application->hasPermission("Suppliers","Insert") && $id <= 0) || ($this->my_application->hasPermission("Suppliers","Edit") && $id > 0));															

			$data['menuActive'] = "Suppliers";
			$data['title'] = "Datos del Proveedor";
			$data['contentView'] = 'suppliers/suppliers_edit_view';
			$data['contentData'] = $contentData;			
			$this->my_application->loadGeneralTemplate($data);
		}
	}
	
	function save() {                             		
		if ((!$this->my_application->hasPermission("Suppliers","Edit") && $this->input->post('id',TRUE) > 0) || 
		    (!$this->my_application->hasPermission("Suppliers","Insert") && $this->input->post('id',TRUE) <= 0)) {
			redirect('/suppliers/listing');	
		} else {
			$this->form_validation->set_rules('code','CUIT','trim|required|max_length[11]|numeric|xss_clean|callback__validateExistsValue[code]');	
			$this->form_validation->set_rules('tradeName','Nombre Comercial','trim|required|max_length[100]|xss_clean');					
			$this->form_validation->set_rules('businessName','Razón Social','trim|required|max_length[100]|xss_clean');						
			$this->form_validation->set_rules('active','Activo','trim|xss_clean');		
			
			if ($this->form_validation->run() != FALSE) {																						
				$data = array('id'=>$this->input->post('id',TRUE),									  
							  'code'=>$this->input->post('code',TRUE),							  
							  'tradeName'=>$this->input->post('tradeName',TRUE),	
							  'businessName'=>$this->input->post('businessName',TRUE),							  					  						  							
							  'active'=>$this->input->post('active',TRUE));
								
				if ($this->suppliers->setSupplier($data)) {						
					redirect('/suppliers/listing');	
				} else {	
					$error = "No se pudo grabar los datos, intente nuevamente";
					$this->edit($this->input->post('id',TRUE),$error);
				}				
			} else {
				$this->edit($this->input->post('id',TRUE));		
			}
		}
	}	
	
	function _validateExistsValue($value='', $field='') {		
		if ($value != '' && $field != '') {
			$supplierId = $this->input->post('id',TRUE);
			if ($this->suppliers->existsValue($value,$supplierId,$field)) {
				$this->form_validation->set_message('_validateExistsValue', 'El valor ya existe');
				return FALSE;		  
			} else {
				return TRUE;
			}
		} else {
			return TRUE;
		}
	}		

	function delete($supplierId=-1) {
		$error = "";
		if ($this->session->userdata('userLoggedIn') && $this->my_application->hasPermission("Suppliers","Delete")) {					
			if ($this->suppliers->deleteSupplierValid($supplierId)) {						
				if (!$this->suppliers->deleteSupplier($supplierId)) {						
					$error = "No se pudo eliminar el registro, intente nuevamente.";					
				}	
			} else {				
				$error = "El registro no se puede eliminar porque existen datos relacionados a él.";
			}			
		} else {
			$error = "No posee permisos para eliminar el registro.";
		}

		if ($error != "") $error = "err/#/".$error;
		
		$contentData['data'] = $error;
		$contentData['byAjax'] = true;	
		$view = $this->load->view("general_data_view",$contentData,true);			
		$this->output->set_output($view);			
	}	

	function export() {				
		if (!$this->session->userdata('userLoggedIn') || !$this->my_application->hasPermission("Suppliers","See")) exit;											

		$parametersdData = $this->_getParameters();										
		$parameters = $parametersdData['parameters'];		
		$suppliers = $this->suppliers->getSuppliers($parameters);
		$suppliers = $suppliers["list"];			

		// INITIALITE SPREADSHEET
				
		$spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Proveedores');
		
        // TITLE
        $row = 1;
        $sheet->setCellValue('A'.$row,"LISTADO DE PROVEEDORES"); 
        $styleCell = array('font'=>array('bold'=>true,'size'=>16));
        $sheet->getStyle('A'.$row)->applyFromArray($styleCell);
        $sheet->mergeCells('A'.$row.':'.'C'.$row);
        
        // GRID HEADERS        
        $col = 0;
        $row++;        

        $row++;
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"CUIT");
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Nombre Comercial"); 
        $col++;
        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,"Razón Social");         
                   
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

		for ($i=0; $i < count($suppliers); $i++) {
			$row++;
	        $col = 0;
	        
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$suppliers[$i]['code']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$suppliers[$i]['tradeName']); 
	        $col++;
	        $sheet->setCellValue(getLetterOfExcelColumn($col).$row,$suppliers[$i]['businessName']); 
		}		

		for ($i=1; $i <= $col; $i++) {
			$sheet->getColumnDimension(getLetterOfExcelColumn($i))->setAutoSize(true);
		}

		// CREATE TEMP FILE EXCEL, DOWNLOAD Y DELETE 

        $writer = new Xlsx($spreadsheet); 

		$folder = $this->config->item('files').'tmp/';
		 
		$filename = 'proveedores_'.getCurrentDateId().'.xlsx';
		 
		$writer->save($folder.$filename); 

		$this->load->helper('download');
							
		$fileContent = file_get_contents($folder.$filename);	

		@unlink($folder.$filename);				
							
		force_download($filename,$fileContent);
	}		

}

/* End of file Suppliers.php */
/* Location: ./application/controllers/Suppliers.php */