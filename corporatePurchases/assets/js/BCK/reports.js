function initializeReports() {
	showHideFiltersReports();	
}

function showHideFiltersReports() {
	showHideFiltersReport('fsta','stateFilter');
	showHideFiltersReport('fwm','workshopManagerIdFilter');
	showHideFiltersReport('fass','assessorIdFilter');
	showHideFiltersReport('ffam','familyIdFilter');
	showHideFiltersReport('fbo','branchOfficeIdFilter');	
	showHideFiltersReport('art','articleFilter');	
	showHideFiltersReport('reas','reassignedFilter');
}

function showHideFiltersReport(filter,id) {	
	if ($('#'+id).length > 0) {
		var visible = false;

		if ($('#type option:selected').attr(filter) != null) {
			if (parseInt($('#type option:selected').attr(filter)) == 1) {
				visible = true;				
			}		
		}

		if (visible) {
			$('#'+id+'Container').removeClass('hide');			
		} else {
			$('#'+id+'Container').addClass('hide');
			selectFirstCombo(id);
		}
	}
}

function loadResultsReports() {	
	if (!validateDataReports()) return;

	$('#data').html("");		

	var callback = "";
	if ($('#type option:selected').attr('callback') != null) {
		callback = $('#type option:selected').attr('callback');
	}

	if ($('#type option:selected').attr('stat') != null && parseInt($('#type option:selected').attr('stat')) == 1) {
		loadDataResultsStatistics(callback);
	} else {
		loadDataByAjax($('#baseUrl').val()+'reports/result','data',callback,'frmFilter',null,'fadeIn');		
	}	
}

function clearFilterReports() {
	clearFilter('frmFilter',false);
	$('#dateFromFilter').val($('#dateFromFilter').attr('original'));
	$('#dateToFilter').val($('#dateToFilter').attr('original'));		
}

function validateDataReports() {
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

function initializeGeneralEfficiency() {
	initializePieChart('gral');
	initializePieChart('unresponsed');
	initializePieChart('effFamily');	
	initializePieChart('rejected');
}

function initializePieChart(id) {
	if (id == null) id = "";

	if (id != "") {
		var count = parseInt($('#'+id+'Count').val());
		if (count > 0) {
			var pieLabels = [];
			var pieValues = [];		
			var pieColors = [];			

			for (var i=1; i <= count; i++) {
				pieLabels.push([$('#'+id+'Description'+i).val()]);			
				pieValues.push([parseFloat($('#'+id+'Data'+i).val())]);	
				pieColors.push([$('#'+id+'Color'+i).val()]);		
			}	

			var position = 'right';
		} else {
			var pieLabels = ['Sin datos para representar'];
			var pieValues = [0];		
			var pieColors = ['FFFFFF'];				

			var position = 'top';
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

		var pieChartCanvas = $('#'+id+'Chart').get(0).getContext('2d')    			
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
			      			}							
						 }		

		var pieChart = new Chart(pieChartCanvas, {
													type: 'pie',
													data: pieData,
													options: pieOptions      
												  })				
	}								
}