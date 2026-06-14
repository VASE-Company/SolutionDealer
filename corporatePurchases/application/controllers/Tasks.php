<?php
/**
* Controlador Tasks
*
*/
class Tasks extends CI_Controller {
    
    public function __construct() {
		parent::__construct();
		
	   	$this->output->set_template('default'); 
		$this->output->set_title($this->config->item('applicationName'));	
		$this->form_validation->set_error_delimiters(errorFormatStart(),errorFormatEnd());				
		$this->load->model('tasks_model','tasks');				
   	}
	
	function clearSessions($key=null) {
		if ($key != $this->config->item('taskKey')) exit;

		$this->tasks->clearSessions();		
	}

	function cancelOrders($key=null) {
		if ($key != $this->config->item('taskKey')) exit;

		$this->tasks->cancelOrders();
	}

	function updateLastUnitPrice($key=null) {
		if ($key != $this->config->item('taskKey')) exit;

		$this->load->model('articles_model','articles');
		$this->articles->updateLastUnitPrice();
	}	

	/*
	function updateStockMovements($articleId=-1,$warehouseId=-1) {
		$this->load->model('reports_model','reports');	
		$this->load->model('articles_model','articles');	
		$this->load->model('stockMovements_model','stockMovements');

		if ($articleId > 0) $parameters['articleIdFilter'] = $articleId;
		if ($warehouseId > 0) $parameters['warehouseIdFilter'] = $warehouseId;		
		$parameters['typeStockFilter'] = 'AVAILABLE';
		$parameters['stockNotZeroFilter'] = true;
		$data = $this->reports->getSummaryStock($parameters);
		$data = $data['list'];

		if (isset($data)) {
			for ($i=0; $i < count($data); $i++) {								
				$details = $this->articles->getDetailedValuedStock($data[$i]['id'],$data[$i]['warehouseId'],'AVAILABLE');
				$details = $details['list'];				

				if (isset($details) && count($details) > 0 && $details[0]['registerType'] == 'SG') {
					echo "Artículo: ".$data[$i]['id']." - Empresa: ".$data[$i]['warehouseId']." - Stock a asignar: ".$details[0]['quantity'];
					$this->stockMovements->assignFreeQuantityToStockMovements($data[$i]['id'],$data[$i]['warehouseId'],$details[0]['quantity']);
					echo "<br><br>";
				}							
			}
		}
	}
	*/

	/*
	function updateFreeQuantityStock($articleId=-1,$warehouseId=-1,$save=0) {				
		$this->load->model('stockMovements_model','stockMovements');
		
		$data = $this->stockMovements->getFreeQuantityErrorStock($articleId,$warehouseId);	

		//http://localhost/development/corporatePurchases/tasks/updateFreeQuantityStock/-1/-1	

		if (isset($data)) {
			for ($i=0; $i < count($data); $i++) {								
				echo "Empresa: ".$data[$i]['warehouseDescription']." (".$data[$i]['warehouseId'].")";
				echo " - ";
				echo "Artículo: ".$data[$i]['articleCode']." - ".$data[$i]['articleDescription']." (".$data[$i]['articleId'].")";
				echo " - ";
				echo "Disp.: ".$data[$i]['available']." - Libre: ".$data[$i]['freeQuantity'];
				$this->stockMovements->updateFreeQuantityStock($data[$i]['articleId'],$data[$i]['warehouseId'],$data[$i]['available']-$data[$i]['freeQuantity'],($save==1));
				echo "<br><br>";							
			}
		} else {
			echo "No hay stock con diferencias.";
		}
	}
	*/
	
}

/* End of file Tasks.php */
/* Location: ./application/controllers/Tasks.php */