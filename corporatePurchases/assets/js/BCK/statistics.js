var lstData;
var lstEntities;

function loadResultsStatistics() {	
	if (!validateDataStatistics()) return;

	$('#data').html("");		

	var callback = "";
	if ($('#type option:selected').attr('callback') != null) {
		callback = $('#type option:selected').attr('callback');
	}

	loadDataResultsStatistics(callback);			    					
}

function loadDataResultsStatistics(callback) {
	loadDataByAjax($('#baseUrl').val()+'statistics/result','data',callback,'frmFilter',null,'fadeIn');
}

function clearFilterStatistics() {
	clearFilter('frmFilter',false);
	$('#dateFromFilter').val($('#dateFromFilter').attr('original'));
	$('#dateToFilter').val($('#dateToFilter').attr('original'));		
}

function validateDataStatistics() {
	if ($('#dateFromFilter').val() == "" || $('#dateToFilter').val() == "") {
		alert('Debe seleccionar el rango de fecha a mostrar.')
		return false;
	}
	
	if ($('#type').val() == "") {
		alert('Debe seleccionar el tipo de informe a mostrar.')
		return false;
	}	

	return true;
}

function initializeResultsStatistics() {		
	if ($('#resultData').html() != "") {		
		lstData = $.parseJSON($('#resultData').html());		
	} else {
		lstData = null;
	}
	if ($('#entitiesData').html() != "") {		
		lstEntities = $.parseJSON($('#entitiesData').html());		
	} else {
		lstEntities = null;
	}

	$('#jsonData').remove();

	refreshResultsStatistics();
}

function refreshResultsStatistics() {
	$('.statTable').each(function(){
		var tableId = $(this).attr('id');
		
		$('#' + tableId + '  .statRow').each(function(){			
			var rowId = $(this).attr('id');			
			
			$('#' + tableId + ' #' + rowId + ' .statCol').each(function(){
				if ($(this).attr('id') != null) {
					var colId = $(this).attr('id');
					if (colId != "title") {
						var colType = $(this).attr('colType');

						$(this).html(getTotalByEntityStatistics(tableId,rowId,colId,colType));
					}
				}
			});
				
		});

		showHideRowsStatistics(tableId);

	});
}

function getTotalByEntityStatistics(entityId,itemId,fieldId,dataType) {
	if (entityId == null) entityId ="";
	if (itemId == null) itemId ="";
	if (fieldId == null) fieldId ="";
	if (dataType == null) dataType ="";

	var total = 0;
	if (entityId != "" && itemId != "" && fieldId != "") {
		for (var i = 0; i < lstData.length; i++){
			if (lstData[i][entityId] == itemId || itemId == '-1') {
				if (resultSelectedStatistics(i)) {
					total += parseFloat(lstData[i][fieldId]);
				}
			}
		}
	}
	
	switch (dataType) {
		case 'decimal':
			total = roundValue(total,2);
		break;
	}


	return total;
}

function resultSelectedStatistics(idx) {
	if (idx == null) idx = -1;

	if (idx >= 0 &&  idx < lstData.length) {
		for (var i = 0; i < lstEntities.length; i++) {
			if (!itemOfEntitySelectedStatistics(lstEntities[i],lstData[idx][lstEntities[i]])) {
				return false;
			}
		}

		return true;
	} else {
		return false;
	}
}

function itemOfEntitySelectedStatistics(entityId,itemId) {
	if (entityId == null) entityId ="";
	if (itemId == null) itemId ="";

	if (itemId != "" && itemId != "") {
		return $('#' + entityId + " #cb" + itemId).prop("checked");
	} else {
		return false;
	}
}

function selectAllByEntityStatistics(entityId) {
	if (entityId == null) entityId ="";

	if (entityId != "") {
		$('#' + entityId + " tbody .checkboxStat").prop("checked",$('#' + entityId + " #selectAll").prop("checked"));
	}

	refreshResultsStatistics();
}

function clickBtnShowRowsStatistics(tableId) {
	if (tableId == null) tableId ="";

	if ($('#'+tableId+' #showRowsIcon') != null) {
		if ($('#'+tableId+' #showRowsIcon').hasClass('fa-eye')) {
			$('#'+tableId+' #showRowsIcon').removeClass('fa-eye');
			$('#'+tableId+' #showRowsIcon').addClass('fa-eye-slash');	
		} else {
			$('#'+tableId+' #showRowsIcon').removeClass('fa-eye-slash');
			$('#'+tableId+' #showRowsIcon').addClass('fa-eye');	
		}

		showHideRowsStatistics(tableId);
	}
}

function showHideRowsStatistics(tableId) {
	if (tableId == null) tableId = "";

	if ($('#'+tableId+' #showRowsIcon') != null) {		
		if ($('#'+tableId+' #showRowsIcon').hasClass('fa-eye')) {
			$('#' + tableId + ' tbody .statRow').removeClass('hide');
		} else {
			$('#' + tableId + ' tbody .statRow').each(function(){							
				if (!$(this).hasClass('statRowFooter')) {
					var rowId = $(this).attr('id');		
					
					if (itemOfEntitySelectedStatistics(tableId,rowId)) {
						$(this).removeClass('hide');
					} else {
						$(this).addClass('hide');
					}
				}				
			});
		}		
	}
}

function generatePieChartStatistics(entityId,columnId1) {
	if (entityId == null) entityId = "";
	if (columnId1 == null) columnId1 = "";	

	if (entityId != "" && columnId1 != "") {		
		modalMessage('statistics/graph',null,null,null,null,null,"closeModalMessage()",true,"initializePieChartStatistics('"+entityId+"','"+columnId1+"')",null,600);				
	}
}

function initializePieChartStatistics(entityId,columnId) {
	if (entityId == null) entityId = "";
	if (columnId == null) columnId = "";		

	if (entityId != "" && columnId != "") {
		var pieLabels = [];
		var pieValues = [];		
		var pieColors = [];	
		var position = 'right';
		var count = 0;		
		var title = $('#' + entityId + " thead #" + columnId).attr('description') + " x " + $('#' + entityId).attr('description');

		$('#' + entityId + '  .statRow').each(function(){			
			var rowId = $(this).attr('id');			
			
			if (itemOfEntitySelectedStatistics(entityId,rowId)) {
				var description = '';			
				var value = 0;

				$('#' + entityId + ' #' + rowId + ' .statCol').each(function(){
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
					pieLabels.push([description + ": " + value]);	
					pieValues.push([parseFloat(value)]);
					pieColors.push(["#"+Math.floor(Math.random()*16777215).toString(16)]);						
					count++;
				}	
			}							
		});

		if (count <= 0) {		
			pieLabels = ['Sin datos para representar'];
			pieValues = [0];		
			pieColors = ['FFFFFF'];				

			position = 'top';
		}

		var pieData = {
						labels: pieLabels,
						datasets: [
									{
										data: pieValues,
										backgroundColor: pieColors,
										hoverBackgroundColor: pieColors,
										borderWidth: 1
									}
								  ]
					  }

		var pieChartCanvas = $('#staticsGraph').get(0).getContext('2d')    			
		var pieOptions = {
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
			      			legend: {
			      				position: position
			      			},
			      			title: {
						        display: true,
						        text: title,
						        fontSize: 18
						      }					
						 }		

		var pieChart = new Chart(pieChartCanvas, {
													type: 'pie',
													data: pieData,
													options: pieOptions      
												  })				
	}	
}

function exportStatistics(entityId) {
	if (entityId == null) entityId = "";	

	if (!confirm('Realmente desea exportar los registros?')) {
		return;
	}	

	clearDataExportStatistics();

	if (entityId != "") {				
		var rows = [];	
		var cols = [];

		//HEADER
		$('#' + entityId + ' #header .statColHeader').each(function(){
			cols.push($(this).attr('description'));																				
		});	
		rows.push(cols);			

		//ROWS
		$('#' + entityId + ' .statRow').each(function(){							
			var rowId = $(this).attr('id');
			var addRow = false;						

			if (!$(this).hasClass('statRowFooter')) {
				addRow = itemOfEntitySelectedStatistics(entityId,rowId);
			} else {
				addRow = true;				
			}

			if (addRow) {				
				cols = [];				

				$('#' + entityId + ' #' + rowId + ' .statCol').each(function(){
					var description = '';

					if ($(this).attr('description') != null) {
						description = $(this).attr('description');
					} else {
						description = $(this).html();
					}
					
					cols.push(description);
				});

				rows.push(cols);
			}					
		});		
		
		$('#frmDataExport #data').val(JSON.stringify(rows));	
		$('#frmDataExport #title').val($('#' + entityId).attr('sectionTitle'));

		$('#frmDataExport').submit();		
	}	
}

function clearDataExportStatistics() {
	$('#frmDataExport #data').val('');
	$('#frmDataExport #title').val('');
}