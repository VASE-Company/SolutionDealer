function sendLogin() {
	cleanError();
	
	if ($.trim($('#frmLogin #username').val()) == '') {
		$('#errorLogin').html('Por favor ingrese su usuario');
		$('#errorLogin').css('visibility','visible');
		$('#frmLogin #username').focus();
		return;
	}
	
	if ($.trim($('#frmLogin #password').val()) == '') {
		$('#errorLogin').html('Por favor ingrese su clave');
		$('#errorLogin').css('visibility','visible');
		$('#frmLogin #password').focus();
		return;
	}
	
	$('#errorLogin').css('visibility','hidden');
	$('#frmLogin').attr('action',$('#baseUrl').val()+'main/login');
	$('#frmLogin').submit();
}

function cleanError() {	
	$('#errorLogin').css('visibility','hidden');
	$('#errorLogin').html('');
	$('#messageLogin').css('visibility','hidden');
	$('#messageLogin').html('');
}

function sendRecoverPassword() {
	cleanError();
	
	if ($.trim($('#frmRecover #username').val()) == '') {
		$('#messageLogin').html('Por favor ingrese su usuario');
		$('#messageLogin').css('visibility','visible');
		$('#frmRecover #username').focus();
		return;
	}
	
	$('#messageLogin').css('visibility','hidden');
	$('#frmRecover').attr('action',$('#baseUrl').val()+'main/recoverPassword');
	$('#frmRecover').submit();
}

function sendResetPassword() {
	cleanError();
	
	if ($.trim($('#frmReset #newPassword').val()) == '') {
		$('#messageLogin').html('Por favor ingrese su nuevo password');
		$('#messageLogin').css('visibility','visible');
		$('#frmReset #newPassword').focus();
		return;
	}
	
	if ($.trim($('#frmReset #confirmationPassword').val()) == '') {
		$('#messageLogin').html('Por favor confirme su pasword');
		$('#messageLogin').css('visibility','visible');
		$('#frmReset #confirmationPassword').focus();
		return;
	}
	
	$('#messageLogin').css('visibility','hidden');
	$('#frmReset').attr('action',$('#baseUrl').val()+'main/saveReset');
	$('#frmReset').submit();
}