function initializeBillEdit() {	
	initializeCharactersRemaining('paymentData');
	bsCustomFileInput.init();
}

function sendMailPendingBills() {
	if (confirm('Realmente desea enviar los mails de aviso de facturas pendientes de pago?')) {
		getUrl($('#baseUrl').val()+'bills/sendMailPending');
	}
}