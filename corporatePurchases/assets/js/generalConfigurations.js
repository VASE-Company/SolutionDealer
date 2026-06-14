function initializeGralConfigEdit() {	
	selectReassignToCompanyGralConfigEdit();
	selectReassignFromCompanyGralConfigEdit();
}

function selectReassignToCompanyGralConfigEdit() {
	if ($('#reassignToCompanyId option:selected').val() != "0") {
		$('.dataReassignToCompany').removeClass("hide");
	} else {		
		$('.dataReassignToCompany').addClass("hide");		
	}
}

function selectReassignFromCompanyGralConfigEdit() {
	if ($('#allowReassignFromCompany option:selected').val() == "1") {
		$('.dataReassignFromCompany').removeClass("hide");		
	} else {		
		$('.dataReassignFromCompany').addClass("hide");		
	}
}

function loadComboAssesorsGralConfigEdit() {
	$('#defaultReassignASSId').html('<option value="">Cargando...</option>');	
	var url = $('#baseUrl').val()+'users/usersCombo?type=assesor&sel='+$('#defaultReassignASSId').val()+'&bo='+$('#defaultReassignBOId').val();			
	loadDataByAjax(url,'defaultReassignASSId',null,null,true);			    						
}