function initializeEditSystemMessage() {
	initializeSummernote('frmData #messageShow','frmData #message');			
}		

function preSaveEditSystemMessage() {	
	transferValueSummernote('frmData #messageShow','frmData #message');	
	preSubmit();	

	return true;
}

function selectRoles() {	
	if ($('#allRoles').prop('checked') == true) {
		$('.rol').prop('checked',true);
	} else {
		$('.rol').prop('checked',false);
	}
}