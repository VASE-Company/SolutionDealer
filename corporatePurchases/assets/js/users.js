function initializeEditUser() {	
	$('#frmData #lastName').focus();
}

function initializeEditMyProfile() {	
	intializeUploadUserImage();	
}


function preSaveEditUser() {
	preSubmit();

	return true;
}

function preSaveEditMyProfile() {	
	preSubmit();

	return true;
}

function intializeUploadUserImage() {
	var btnUpload = $("#fileUserImage"),
		btnOuter = $("#uploadFileBtnOuter");

	if ($("#previewUserImage").attr("src")  == $('#baseUrl').val()+"assets/images/user.jpg") {
		$("#btnDeleteUserImage").hide();
	}

	btnUpload.on("change", function(e){
		var ext = btnUpload.val().split('.').pop().toLowerCase();
		if($.inArray(ext, ['gif','png','jpg','jpeg']) == -1) {
			$("#uploadFileErrorMsg").text("El archivo no es una imagen");
		} else {
			$("#uploadFileErrorMsg").text("");
			btnOuter.addClass("upload-file-uploading");
			
			setTimeout(function(){
				btnOuter.addClass("upload-file-uploaded");
			},3000);

			var uploadedFile = URL.createObjectURL(e.target.files[0]);
			setTimeout(function(){				
				$("#previewUserImage").attr("src",uploadedFile);
				btnOuter.removeClass("upload-file-uploading");
				btnOuter.removeClass("upload-file-uploaded");
				$("#btnDeleteUserImage").show();
				$("#deleteUsrImage").val(0);
			},3500);
		}
	});	

	$("#btnDeleteUserImage").on("click", function(e){		
		$("#previewUserImage").attr("src",$('#baseUrl').val()+"assets/images/user.jpg");	
		$("#fileUserImage").val('');
		$("#uploadFileErrorMsg").text("");	
		$("#btnDeleteUserImage").hide();
		$("#deleteUsrImage").val(1);
	});

}

function selectRoles() {	
	if ($('#allRoles').prop('checked') == true) {
		$('.rol').prop('checked',true);
	} else {
		$('.rol').prop('checked',false);
	}
}

function selectCompanyUserEdit() {		
	loadComboBranchOfficesUserEdit();
}

function loadComboBranchOfficesUserEdit() {
	$('#branchOfficeId').html('<option value="">Cargando...</option>');	
	var url = $('#baseUrl').val()+'branchOffices/branchOfficesCombo/'+$('#companyId').val()+"/"+$('#branchOfficeId').val();				
	loadDataByAjax(url,'branchOfficeId','selectBranchOfficeUserEdit()',null,true);			    						
}

function selectBranchOfficeUserEdit() {		
	loadComboSectorsUserEdit();
}

function loadComboSectorsUserEdit() {
	$('#sectorId').html('<option value="">Cargando...</option>');	
	var url = $('#baseUrl').val()+'sectors/sectorsCombo/'+$('#branchOfficeId').val()+"/"+$('#sectorId').val();				
	loadDataByAjax(url,'sectorId',null,null,true);			    						
}