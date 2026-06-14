function selectAllSectors() {	
	if ($('#allSectors').prop('checked') == true) {
		$('.sector').prop('checked',true);
	} else {
		$('.sector').prop('checked',false);
	}
}