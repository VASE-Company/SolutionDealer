function initializaPanel() {
	//loadNotifications();
}

function loadNotifications() {	
	loadDataByAjax($('#baseUrl').val()+'panel/notifications','notifications',null,'frmFilter',null,'fadeIn');				    					
}

function reloadNotifications(url) {	
	if (url == null) url = "";

	if (url != "") {
		loadDataByAjax(url,'notifications',null,null,null,'fadeIn');			    							
	}		
}