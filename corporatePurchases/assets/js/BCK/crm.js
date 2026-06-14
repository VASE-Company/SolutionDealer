function loadResultsCRM() {	
	loadDataByAjax($('#baseUrl').val()+'crm/result','data',null,'frmFilter',null,'fadeIn');				    					
}

function reloadResultsCRM(url) {	
	if (url == null) url = "";

	if (url != "") {
		loadDataByAjax(url,'data',null,null,null,'fadeIn');			    							
	}		
}

function clearFilterCRM() {
	clearFilter('frmFilter',false);
	$('#dateFromFilter').val($('#dateFromFilter').attr('original'));
	$('#dateToFilter').val($('#dateToFilter').attr('original'));		
}