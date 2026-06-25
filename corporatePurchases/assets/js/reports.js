function loadResultsReports() {	
	if (!validateDataReports()) return;

	$('#data').html("");		

	loadDataByAjax($('#baseUrl').val()+'reports/result','data',null,'frmFilter',null,'fadeIn');			
}

function clearFilterReports() {
	clearFilter('frmFilter',false);

	if ($('#dateFromFilter') != null) $('#dateFromFilter').val($('#dateFromFilter').attr('original'));
	if ($('#dateToFilter') != null) $('#dateToFilter').val($('#dateToFilter').attr('original'));
	if ($('#companyIdsFilterSelected') != null) {
		$('#companyIdsFilter').multiselect('selectAll', false);
		$('#companyIdsFilter').multiselect('updateButtonText');
		$('#companyIdsFilterSelected').val('all');	
	}
	if ($('#familyIdsFilterSelected') != null) {
		$('#familyIdsFilter').multiselect('selectAll', false);
		$('#familyIdsFilter').multiselect('updateButtonText');
		$('#familyIdsFilterSelected').val('all');		
	}
}

function validateDataReports() {
	if ($('#type').val() == 'general' && $('#subtypeFilter').val() == 'PRO') {	
		if ($('#dateFromFilter') != null && $('#dateFromFilter').val() == "") {
			alert('Debe seleccionar el rango de fecha a mostrar.')
			return false;
		}	

		if ($('#dateToFilter') != null && $('#dateToFilter').val() == "") {
			alert('Debe seleccionar el rango de fecha a mostrar.')
			return false;
		}

		if (!isValidPeriod($('#dateFromFilter').val(), $('#dateToFilter').val())) {
			alert('El rango de fecha a mostrar no es válido.')
			return false;
		}

		if (monthsDifference($('#dateFromFilter').val(), $('#dateToFilter').val()) >=12) {
			alert('El rango de fecha para este tipo de reporte no puede contemplar más de 12 meses.')
			return false;	
		}		
	}	

	if ($('#type').val() == 'estimatedpurchase') {	
		if ($('#dateFromFilter') != null && $('#dateFromFilter').val() == "") {
			alert('Debe seleccionar el rango de fechas de referencia.')
			return false;
		}	

		if ($('#dateToFilter') != null && $('#dateToFilter').val() == "") {
			alert('Debe seleccionar el rango de fechas de referencia.')
			return false;
		}

		if (!isValidPeriod($('#dateFromFilter').val(), $('#dateToFilter').val())) {
			alert('El rango de fechas de referencia no es válido.')
			return false;
		}	

		if ($('#estimatedDays') != null && ($('#estimatedDays').val() == "") || isNaN($('#estimatedDays').val()) || parseInt($('#estimatedDays').val()) <= 0) {
			alert('Los días a estimar no son válidos.')
			return false;
		}
	}	
	
	if ($('#companyIdsFilter') != null) getValueComboFilter('companyIdsFilter');	      	
	if ($('#familyIdsFilter') != null) getValueComboFilter('familyIdsFilter');	

	if ($('#type').val() == 'partialdeliveries') {
		if ($('#dateFromFilter') != null && $('#dateToFilter') != null && $('#dateFromFilter').val() != "" && $('#dateToFilter').val() != "") {
			if (!isValidPeriod($('#dateFromFilter').val(), $('#dateToFilter').val())) {
				alert('El rango de fechas de plazo no es valido.')
				return false;
			}
		}
	}

	return true;
}

function selectCompanyReports() {
	$('#branchOfficeIdFilter').html('<option value="">Cargando...</option>');	
	var url = $('#baseUrl').val()+'branchOffices/branchOfficesCombo/'+$('#companyIdFilter').val()+"?fo=ALL_F_S";				
	loadDataByAjax(url,'branchOfficeIdFilter','selectBranchOfficeReports',null,true);
}

function selectBranchOfficeReports() {
	var branchOfficeId = $('#branchOfficeIdFilter').val();	
	if (branchOfficeId == "") branchOfficeId = -999;

	if ($('#sectorIdFilter').length > 0) {
		$('#sectorIdFilter').html('<option value="">Cargando...</option>');
		var url = $('#baseUrl').val()+'sectors/sectorsCombo/'+branchOfficeId+"?ao=1&fo=ALL_M_S";
		loadDataByAjax(url,'sectorIdFilter',null,null,true);
	}
}

// ----------------- STOCK REPORT -----------------
function intializeStockReport() {
	selectTypeStockReport();	
}

function seeArticlePriceInStockReport(articleId,warehouseId,stockType) {
	if (articleId == null) articleId = -1;
	if (warehouseId == null) warehouseId = -1;
	if (stockType == null) stockType = '';	

	if (articleId > 0 && warehouseId > 0 && stockType != '') {
		modalMessage('articles/detailedStock/'+articleId+'/'+warehouseId+'/'+stockType,"Detalle de Stock",null,null,null,null,'closeModalMessage()',true,null,null,600);		
	}
}

function seeArticleUnitPriceInStockReport(articleId,warehouseId) {
	if (articleId == null) articleId = -1;
	if (warehouseId == null) warehouseId = -1;	

	if (articleId > 0 && warehouseId > 0) {
		modalMessage('articles/detailedUnitPrice/'+articleId+'/'+warehouseId,"Detalle de Costo Histórico",null,null,null,null,'closeModalMessage()',true,null,null,600);		
	}
}

function seeArticleLastUnitPriceInStockReport(articleId,quantity) {
	if (articleId == null) articleId = -1;	
	if (quantity == null) quantity = 0;

	if (articleId > 0 && quantity > 0) {
		modalMessage('articles/detailedUnitPrice/'+articleId+'/0/'+quantity,"Detalle de Costo Actualizado",null,null,null,null,'closeModalMessage()',true,null,null,600);		
	}
}

function selectTypeStockReport() {
	if ($('#stockTypeIdFilter option:selected').val() == 'REAL') {
		$('#divDateToFilter').removeClass('hide');
	} else {
		$('#divDateToFilter').addClass('hide');
	}		
	$('#dateToFilter').val('');	
}

// ------------------------------------------------

// ----------------- GENERAL REPORT -----------------
function intializeGeneralReport() {
	selectCompanyReports();
}

function generateGraphicTotalizedGeneralReport() {		
	modalMessage('reports/graph',null,null,null,null,null,"closeModalMessage()",true,"initializePieChartReports('grData','colTotal')",null,600);					
}

function generateGraphicProgressionGeneralReport() {		
	modalMessage('reports/graph',null,null,null,null,null,"closeModalMessage()",true,"initializeBarChartReports('grData','colY','colX')",null,1000);					
}
// --------------------------------------------------

// ----------------- ESTIMATED PURCHASE REPORT -----------------
function intializeEstimatedPurchaseReport() {
	inicializeComboFilter('companyIdsFilter');
	inicializeComboFilter('familyIdsFilter');
}
// --------------------------------------------------

// ------------- PARTIAL DELIVERIES REPORT -------------
function intializePartialDeliveriesReport() {
	selectCompanyReports();
}

function reloadResultsPartialDeliveries(url) {
	if (url == null) url = "";

	if (url != "") {
		loadDataByAjax(url,'data',null,null,null,'fadeIn');
	}
}

// -----------------------------------------------------

// --------------------- GRAPHIC ---------------------

function initializePieChartReports(gridId,columnId) {
	if (gridId == null) gridId = "";
	if (columnId == null) columnId = "";		

	if (gridId != "" && columnId != "") {
		var graphicLabels = [];
		var graphicValues = [];		
		var graphicColors = [];	
		var position = 'right';
		var count = 0;		
		var title = $('#' + gridId + " thead #" + columnId).attr('description') + " por " + $('#' + gridId).attr('description');

		$('#' + gridId + '  .statRow').each(function(){			
			if (!$(this).hasClass("hide")) {
				var rowId = $(this).attr('id');							
				var description = '';			
				var value = 0;

				$('#' + gridId + ' #' + rowId + ' .statCol').each(function(){
					var colId = $(this).attr('id');								

					if (colId == "title") {
						description = $(this).attr('description');					
					} else {
						if (colId == columnId) {
							value = $(this).html();
						}					
					}																			
				});	

				if (description	!= "") {							
					graphicLabels.push([description + ": " + value]);	
					graphicValues.push([parseFloat(value)]);
					graphicColors.push(["#"+Math.floor(Math.random()*16777215).toString(16)]);						
					count++;
				}
			}										
		});

		if (count <= 0) {		
			graphicLabels = ['Sin datos para representar'];
			graphicValues = [0];		
			graphicColors = ['FFFFFF'];				

			position = 'top';
		}

		var graphicData = {
						labels: graphicLabels,
						datasets: [
									{
										data: graphicValues,
										backgroundColor: graphicColors,
										hoverBackgroundColor: graphicColors,
										borderWidth: 1
									}
								  ]
					  }

		var chartCanvas = $('#reportGraph').get(0).getContext('2d')   		 		
		var graphicOptions = {
							maintainAspectRatio : false,
							responsive : true,
							tooltips: {
							      callbacks: {
							        label: function(tooltipItem, data) {								        	
							        	var sum = data['datasets'][0]['data'].reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
							        	var value = data['datasets'][0]['data'][tooltipItem['index']];								           	
							           	var percentage = roundValue(((parseFloat(value) / parseFloat(sum)) * 100),2) + ' %';
							           	
							           	return percentage;
							        }
							    }
			      			},
			      			plugins: {
				      			legend: {
				      				position: position
				      			},

				      			title: {
							        display: true,
							        text: title,
							        font: {
								        size: 18, 
								        weight: 'bold'
								    },
								    padding: {
								        top: 0,
								        bottom: 30 
								    }
							      }
							}					
						 }		

		var pieChart = new Chart(chartCanvas, {
													type: 'pie',
													data: graphicData,
													options: graphicOptions      
												  })				
		
	}	
}


function initializeBarChartReports(gridId,yColumnId,xColumnId) {
	if (gridId == null) gridId = "";
	if (yColumnId == null) yColumnId = "";		
	if (xColumnId == null) xColumnId = "";		

	if (gridId != "" && yColumnId != "" && xColumnId != "") {
		var graphicLabels = [];
		var graphicValues = [];		
		var graphicColors = [];	
		var graphicDatasets = [];	
		var position = 'right';
		var count = 0;		

		var title = $('#' + gridId).attr('description');
		var xTitle = $('#' + gridId).attr('xTitle');
		var yTitle = $('#' + gridId).attr('yTitle');
		var columns = parseInt($('#' + gridId).attr('columns'));

		for (var i=1; i <= columns; i++) {			
			graphicLabels.push([$('#' + gridId + " thead #" + xColumnId + i).attr('description')]);	
		}		
					
		$('#' + gridId + '  .statRow').each(function(){			
			if (!$(this).hasClass("hide")) {
				var rowId = $(this).attr('id');	

				var graphicValues = [];	
				for (var i=1; i <= columns; i++) {			
					var value = $('#' + gridId + " #" + rowId + " #" + xColumnId + i).html();;	
					graphicValues.push(parseFloat(value));
				}									
								
				graphicColors = ["#"+Math.floor(Math.random()*16777215).toString(16)];		

				console.log(graphicValues);				

				var dataset = {
						label: $(this).attr('description'),
						data: graphicValues,								
						borderColor: graphicColors,
						backgroundColor: 'transparent',
    					fill: false
					  };		
				graphicDatasets.push(dataset);														
				count++;				
			}										
		});		

		if (count <= 0) {								
			graphicValues = [];		
			graphicColors = ['FFFFFF'];	

			var dataset = {
							label: 'Sin datos para representar',
							data: graphicValues,								
							borderColor: 'transparent',
							backgroundColor: 'transparent',
        					fill: false
						  };

			graphicDatasets.push(dataset);	
			
			position = 'top';
		}

		var graphicData = {
						labels: graphicLabels,
						datasets: graphicDatasets
					  }

		var chartCanvas = $('#reportGraph').get(0).getContext('2d')   		 		
		var graphicOptions = {
							maintainAspectRatio : false,
							responsive : true,
							tooltips: {
							      callbacks: {
							        label: function(tooltipItem, data) {								        	
							        	var sum = data['datasets'][0]['data'].reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
							        	var value = data['datasets'][0]['data'][tooltipItem['index']];								           	
							           	var percentage = roundValue(((parseFloat(value) / parseFloat(sum)) * 100),2) + ' %';
							           	
							           	return percentage;
							        }
							    }
			      			},
			      			plugins: {
				      			legend: {
				      				position: position
				      			},

				      			title: {
							        display: true,
							        text: title,
							        font: {
								        size: 18, 
								        weight: 'bold'
								    },
								    padding: {
								        top: 0,
								        bottom: 30 
								    }
							      }
							},	
							hover: {
					            mode: 'nearest',
					            intersect: true
					        },
					        scales: {
					        	x: {
							        title: {
							          display: true,
							          text: xTitle
							        }
							      },
							      y: {
							        beginAtZero: true,
							        title: {
							          display: true,
							          text: yTitle
							        }
							      }	
							}
						 }		


		var pieChart = new Chart(chartCanvas, {
													type: 'line',
													data: graphicData,
													options: graphicOptions      
												  })				
		
	}

	/*
	var ctx = document.getElementById('reportGraph').getContext('2d');

		var chart = new Chart(ctx, {
		    type: 'line',
		    data: {
		        labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
		        datasets: [
		            {
		                label: 'Electrónica',
		                data: [12, 15, 14, 18, 17, 20, 22, 24, 23, 25, 27, 30],
		                borderColor: 'red',
		                backgroundColor: 'transparent',
		                fill: false
		            },
		            {
		                label: 'Ropa',
		                data: [8, 9, 10, 11, 9, 7, 6, 8, 10, 12, 11, 13],
		                borderColor: 'blue',
		                backgroundColor: 'transparent',
		                fill: false
		            },
		            {
		                label: 'Alimentos',
		                data: [20, 22, 25, 24, 26, 28, 29, 31, 30, 33, 32, 35],
		                borderColor: 'green',
		                backgroundColor: 'transparent',
		                fill: false
		            }
		        ]
		    },
		    options: {
		        responsive: true,
		        title: {
		            display: true,
		            text: 'Cantidad por mes según rubro'
		        },
		        tooltips: {
		            mode: 'index',
		            intersect: false
		        },
		        hover: {
		            mode: 'nearest',
		            intersect: true
		        },
		        scales: {
		            xAxes: [{
		                display: true,
		                scaleLabel: {
		                    display: true,
		                    labelString: 'Mes'
		                }
		            }],
		            yAxes: [{
		                display: true,
		                scaleLabel: {
		                    display: true,
		                    labelString: 'Cantidad'
		                },
		                ticks: {
		                    beginAtZero: true
		                }
		            }]
		        }
		    }
		});
		*/
}

function showHideRowsReports(gridId) {
	if (gridId == null) gridId = "";

	if (gridId != "") {			
		if ($('#'+gridId+' #showRowsIcon').hasClass('fa-eye')) {
			$('#'+gridId+' tbody .rowToHide').addClass('hide');
			$('#'+gridId+' #showRowsIcon').removeClass('fa-eye');
			$('#'+gridId+' #showRowsIcon').addClass('fa-eye-slash');
		} else {
			$('#'+gridId+' tbody .rowToHide').removeClass('hide');
			$('#'+gridId+' #showRowsIcon').removeClass('fa-eye-slash');
			$('#'+gridId+' #showRowsIcon').addClass('fa-eye');	
		}				
	}
}

// ---------------------------------------------------
