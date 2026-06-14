function selectPermissions() {	
	if ($('#allPermissions').prop('checked') == true) {
		$('.permission').prop('checked',true);
	} else {
		$('.permission').prop('checked',false);
	}
	checkBranchOffices();
}

function selectBranchOffices() {	
	if ($('#allBranchOffices').prop('checked') == true) {
		$('.branchOffice').prop('checked',true);
	} else {
		$('.branchOffice').prop('checked',false);
	}
}

function checkBranchOffices() {
	var enableBranchOffices = false;	

	$('.permission').each(function(){
		if ($(this).hasClass('enableBranchOffices') && $(this).prop('checked')){ 
			enableBranchOffices = true;				
		}			
	});

	if (enableBranchOffices) {
		$('#branchOfficesContainer').removeClass('hide');
	} else {
		$('#branchOfficesContainer').addClass('hide');
		$('#allBranchOffices').prop('checked',false);
		selectBranchOffices();
	}
}