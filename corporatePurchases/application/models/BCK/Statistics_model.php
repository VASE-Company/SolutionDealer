<?php
class Statistics_model extends CI_Model{           

    private $company_db;

    function __construct() {        
        parent::__construct();        
        
        if ((int)$this->session->userdata('companyId') > 0) {
        	$this->company_db = $this->load->database('company_db_'.$this->session->userdata('companyId'), TRUE); 
        }
    }  

    function _getFilter($parameters=NULL) {
		$filter = "";

		if (isset($parameters)) {						
			if (isset($parameters['dateFromFilter']) && $parameters['dateFromFilter'] != "") {
				$filter .= " AND budgets.date >= '".$parameters['dateFromFilter']." 00:00:00' ";
			}
			if (isset($parameters['dateToFilter']) && $parameters['dateToFilter'] != "") {
				$filter .= " AND budgets.date <= '".$parameters['dateToFilter']." 23:59:59' ";
			}				
		}

		$this->load->model('budgets_model','budgets');	
		$accessFilter = $this->budgets->getAccessFilter();		
		if ($accessFilter != "") $filter .= " AND  (".$accessFilter.")";

		return $filter;
	}
        
	function getGeneralStatisticsOfBudgets($parameters=NULL){				
		function comparatorElementOfList($element1, $element2) {
	    	if (strcmp(trim(strtolower($element1['description'])),trim(strtolower($element2['description']))) < 0)
			{
			  	return true;
			} else {
				return false;
			}
		} 

		$statistics = array('entities'=>NULL, 
							'columns'=>NULL, 
		                    'data'=>NULL);
				
		$filter = $this->_getFilter($parameters);

		$sql = "SELECT 
					summary.workshopManagerId, 
					IF(workshopManagers.id IS NULL, 'Sin Especificar', CONCAT(workshopManagers.lastName,', ',workshopManagers.firstName)) AS workshopManagerDescription, 

					summary.assessorId, 
					IF(assessors.id IS NULL, 'Sin Especificar', CONCAT(assessors.lastName,', ',assessors.firstName)) AS assessorDescription, 

					summary.branchOfficeId, 
					IF(branchOffices.id IS NULL, 'Sin Especificar', branchOffices.description) AS branchOfficeDescription, 

					summary.familyId, 
					IF(families.id IS NULL, 'Sin Especificar', families.description) AS familyDescription, 

					summary.stateId, 
					
					summary.quantity,
					summary.amount
				FROM ((((
					SELECT budgets.workshopManagerId,
						   budgets.assessorId,
						   budgets.branchOfficeId,
						   budgets.familyId,						   
						   IF (budgets.readByAssessor = 0,'SL',
						   	   IF(budgets.readByAssessor = 1 AND budgets.budgetResponseId <= 0,'SR',
						   	      budgets.budgetResponseId)			
						      ) AS stateId,
						   COUNT(*) AS quantity,
						   SUM(budgets.total) AS amount
					FROM budgets		
					WHERE budgets.deleted = 0 ".$filter." 
					GROUP BY budgets.workshopManagerId, budgets.assessorId, budgets.branchOfficeId, budgets.familyId,
							 IF (budgets.readByAssessor = 0,'SL',
						   	   IF(budgets.readByAssessor = 1 AND budgets.budgetResponseId <= 0,'SR',
						   	      budgets.budgetResponseId)			
						      )					
				) AS summary
				LEFT JOIN users workshopManagers ON summary.workshopManagerId = workshopManagers.id)
				LEFT JOIN users assessors ON summary.assessorId = assessors.id)		        
				INNER JOIN branchOffices ON summary.branchOfficeId = branchOffices.id) 	
				LEFT JOIN families ON summary.familyId = families.id				
				";															

		$query = $this->company_db->query($sql);				
		if($query->num_rows() > 0){												
			$summary = $query->result_array();

			$lstBranchOffices = NULL;
			$lstFamilies = NULL;
			$lstAssessors = NULL;
			$lstWorkshopManagers = NULL;
			$lstStates = NULL;

			for ($i=0; $i < count($summary); $i++) {				
				addToListIdDescription($summary[$i]['branchOfficeId'],$summary[$i]['branchOfficeDescription'],$lstBranchOffices);
				addToListIdDescription($summary[$i]['familyId'],$summary[$i]['familyDescription'],$lstFamilies);
				addToListIdDescription($summary[$i]['assessorId'],$summary[$i]['assessorDescription'],$lstAssessors);
				addToListIdDescription($summary[$i]['workshopManagerId'],$summary[$i]['workshopManagerDescription'],$lstWorkshopManagers);

				if ($summary[$i]['stateId'] != "SL" && $summary[$i]['stateId'] != "SR") {
					$stateId = "CR_".$summary[$i]['stateId']."_-1";
				} else {
					$stateId = $summary[$i]['stateId'];
				}
				if (!existsIdInListIdDescription($stateId,$lstStates)) {

					switch ($summary[$i]['stateId']) {
						case "SL":
							$stateDescription = "Sin Leer";
						break;

						case "SR":
							$stateDescription = "Sin Respuesta";
						break;

						default:						
							$sql = "SELECT description 
						        	FROM budgetResponses  
									WHERE budgetResponses.id = ".$summary[$i]['stateId'];					
							
							$query = $this->company_db->query($sql);			
							if ($query->num_rows() > 0){
								$row = $query->row_array();

								$stateDescription = "Rta: ".$row['description'];
							} else {
								$stateDescription = "Rta: Sin Especificar";
							}
						break;

					}
					addToListIdDescription($stateId,$stateDescription,$lstStates);
				}

				$data[$i] = array('wm' => $summary[$i]['workshopManagerId'],
								  'ass' => $summary[$i]['assessorId'],
								  'bo' => $summary[$i]['branchOfficeId'],
								  'fam' => $summary[$i]['familyId'],
								  'sta' => $stateId,
								  'qua' => $summary[$i]['quantity'],
								  'amo' => $summary[$i]['amount']
			                      );
			}	
					
			usort($lstBranchOffices,'compareElementByDescription');			
			usort($lstFamilies,'compareElementByDescription');
			usort($lstAssessors,'compareElementByDescription');
			usort($lstWorkshopManagers,'compareElementByDescription');			
			usort($lstStates,'compareElementByDescription');	

			$idxEntities = 0;
			$entities[$idxEntities++] = array('id' => 'bo',
				                    		  'title' => 'Sucursales',
				                    		  'gridTitle' => 'Sucursal',
		                            		  'list' => $lstBranchOffices,
		                            	 	  'color' => '#dc3545');
			$entities[$idxEntities++] = array('id' => 'fam',
				                     		  'title' => 'Familias',
				                     		  'gridTitle' => 'Familia',
		                             		  'list' => $lstFamilies,
		                             		  'color' => '#ffc107');
			$entities[$idxEntities++] = array('id' => 'ass',
				                     		  'title' => 'Asesores',
				                     		  'gridTitle' => 'Asesor',
		                             		  'list' => $lstAssessors,
		                             		  'color' => '#28a745');
			$entities[$idxEntities++] = array('id' => 'wm',
				                    		  'title' => 'Responsables de Taller',
				                    		  'gridTitle' => 'Responsable',
		                            		  'list' => $lstWorkshopManagers,
		                            		  'color' => '#942bbf');
			$entities[$idxEntities++] = array('id' => 'sta',
				                    		  'title' => 'Estados',
				                    		  'gridTitle' => 'Estado',
		                            		  'list' => $lstStates,
		                            		  'color' => '#fd7e14');			

			$idxColumns = 0;
			$columns[$idxColumns++] = array('id' => 'qua',
				                    		'title' => 'Cantidad',				                    	    
		                            	 	'graph' => "generatePieChartStatistics('[@entityId]','qua')");
			$columns[$idxColumns++] = array('id' => 'amo',
				                    		'title' => 'Importe ($)',
				                    		'type' => 'decimal',				                    	    
		                            	 	'graph' => "generatePieChartStatistics('[@entityId]','amo')");
			
			$statistics = array('entities'=>$entities,
								'columns'=>$columns,
		                        'data'=>$data);		                  		
		}		
		
		return $statistics;	
	}
	
}
/* End of file Reports_model.php */
/* Location: ./application/models/Reports_model.php */