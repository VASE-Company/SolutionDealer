
function initializeBudgetEdit() {	
	initializeCharactersRemaining('observation');	
	calculateTotalBudgetEdit();		
	detectKeySupplierCodeBudgetEdit();	
	detectKeyDetailBudgetEdit();			
}


function calculateTotalBudgetEdit() {			
	var detailsCount = parseInt($('#detailsCount').val());
	var detailTotal = 0;
	var rowTotal = 0;
	
	for (var row=1; row <= detailsCount; row++) {
		if ($('#grDetails #'+row).length > 0) {
			if (isNaN($('#detailUnitPrice'+row).val()) || $('#detailUnitPrice'+row).val() == "" || parseFloat($('#detailUnitPrice'+row).val()) < 0) {
				$('#detailUnitPrice'+row).val("0.00");
			}
			if (isNaN($('#detailQuantity'+row).val()) || $('#detailQuantity'+row).val() == "" || parseFloat($('#detailQuantity'+row).val()) < 0) {
				$('#detailQuantity'+row).val("1");
			}
			
			//var taxPercentage = 1 + (parseFloat($('#detailTaxPercentage'+row).val()) / 100);
			//rowTotal = (parseFloat($('#detailUnitPrice'+row).val()) * taxPercentage * parseFloat($('#detailQuantity'+row).val())).toFixed(2);					

			rowTotal = (parseFloat($('#detailUnitPrice'+row).val()) * parseFloat($('#detailQuantity'+row).val())).toFixed(2);					
			
			$('#detailTotal'+row).val(roundValue(rowTotal,2));

			detailTotal = detailTotal + parseFloat($('#detailTotal'+row).val());
		}
	}
				
	$('#total').val(roundValue(detailTotal,2));		
}

function preSaveBudget() {
	if (!detailsValidBudgetEdit()) {
			return false;
	}

	preSubmit();
}

// ******* FINDER GENERAL *******

function initializeFinderBudgetEdit() {
	initializeKeyDetection();
	$('#frmFinderFilter #textFilter').focus();	
}

function reloadFinderBudgetEdit(url,frm) {	
	if (url == null) url = "";

	if (url != "") {
		loadDataByAjax(url,'bodyModal','initializeFinderBudgetEdit()',frm);			    							
	}		
}

function searchFinderBudgetEdit(type,clear) {	
	if (type == null) type = '';
	if (clear == null) clear = false;

	if (type != '') {
		if (clear) clearFilter('frmFinderFilter',false);

		var url = $('#baseUrl').val()+'budgets/'+type+'Finder/';
		
		reloadFinderBudgetEdit(url,'frmFinderFilter');
	}
}

// ******* END FINDER GENERAL *******

// ******* FINDER SUPPLIERS *******

function openSuppliersFinderBudgetEdit() {		
	var url = 'budgets/suppliersFinder/';

	modalMessage(url,"Buscador de Proveedores",null,null,null,null,'closeModalMessage()',true,"initializeFinderBudgetEdit()",null,600);	
}

function acceptSuppliersFinderBudgetEdit(finderRow) {	
	if (finderRow == null) finderRow = 0;

	if (parseInt(finderRow) > 0) {
		$('#frmData #supplierCode').val($('#bodyModal #sfCode'+finderRow).val());
				
		searchSupplierBudgetEdit();

		closeModalMessage();					
	} else {
		closeModalMessage();
	}
}

function searchSupplierBudgetEdit() {	
	if ($("#frmData #supplierCode").val() != $("#frmData #supplierCodeOriginal").val()) {
		//Change the code of supplier
		var code = $.trim($("#frmData #supplierCode").val());

		loadSupplierBudgetEdit(code);			
	}
}

function loadSupplierBudgetEdit(code) {
	if (code != "") {	 		
 		$("#frmSearchAux #code").val(code);
 		$("#frmSearchAux #resultSearch").html("");

 		loadDataByAjax($('#baseUrl').val()+'budgets/searchSupplier','resultSearch','terminateLoadSupplierBudgetEdit()','frmSearchAux');		 		
		
	} else {	
		$("#frmData #supplierCode").val("");
		$("#frmData #supplierCodeOriginal").val("");					
		$("#frmData #supplierCode").focus();
	}
}

function terminateLoadSupplierBudgetEdit() {	
	if ($("#frmSearchAux #supplierCode").length > 0) {
		$("#frmData #supplierId").val($("#frmSearchAux #supplierId").val());	
		$("#frmData #supplierCode").val($("#frmSearchAux #supplierCode").val());	
		$("#frmData #supplierDescription").val($("#frmSearchAux #supplierNameTrade").val());		
		$("#frmData #supplierCodeOriginal").val($("#frmSearchAux #supplierCode").val());					
	} else {
		loadSupplierBudgetEdit("");
	}		
}


// ******* END FINDER SUPPLIERS *******

// ******* FINDER ARTICLES *******

function openArticlesFinderBudgetEdit() {		
	var detailsCount = parseInt($('#detailsCount').val());	
	var exludeIds = "";
	
	for (var row=1; row <= detailsCount; row++) {
		if ($('#grDetails #'+row).length > 0) {				
			if (parseFloat($.trim($("#detailItemOrderId"+row).val())) > 0)  {
				if (exludeIds != "") exludeIds = exludeIds + '_';
				exludeIds = exludeIds + $("#detailItemOrderId"+row).val();
			}
		}
	}

	var url = 'budgets/articlesFinder/'+$('#orderId').val()+'/'+exludeIds;

	modalMessage(url,"Agregar Artículos del Pedido",null,null,null,null,'closeModalMessage()',true,"initializeFinderBudgetEdit()",null,800);
}

function acceptArticlesFinderBudgetEdit(finderRow) {	
	if (finderRow == null) finderRow = 0;

	if (parseInt(finderRow) > 0) {
		newRowDetailBudgetEdit();

		var detailRow = parseInt($('#detailsCount').val());
		
		$('#frmData #detailCode'+detailRow).val($('#bodyModal #afCode'+finderRow).val());
		$('#frmData #detailDescription'+detailRow).val($('#bodyModal #afDescription'+finderRow).val());
		$('#frmData #detailFamily'+detailRow).val($('#bodyModal #afFamily'+finderRow).val());			
		$('#frmData #detailQuantity'+detailRow).val($('#bodyModal #afDetailQuantity'+finderRow).val());					
		$('#frmData #detailItemOrderId'+detailRow).val($('#bodyModal #afDetailOrderId'+finderRow).val());	

		closeModalMessage();
		
		$("#frmData #detailUnitPrice"+detailRow).focus();			
	} else {
		closeModalMessage();
	}
}

// ******* END FINDER ARTICLES *******

function detectKeyDetailBudgetEdit() {	
	$('.detailColumn').keydown( function(e) {
        var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
        if(key == 13) {
            e.preventDefault();
            var inputs = $(this).closest('form').find(':input:visible');
            inputs.eq(inputs.index(this)+ 1).focus();
        }
    });    
}

function detailsValidBudgetEdit() {
	
	var detailsCount = parseInt($('#detailsCount').val());
	var existsDetailValid = false;	
	
	for (var row=1; row <= detailsCount; row++) {
		if ($('#grDetails #'+row).length > 0) {			
			if ($.trim($("#detailDescription"+row).val()) != "") {				
				existsDetailValid = true;
			}		
			if (parseFloat($.trim($("#detailUnitPrice"+row).val())) < 0)  {
				alert("El costo no es válido");
				$("#detailUnitPrice"+row).focus();
				return false;
			}
			if (parseFloat($.trim($("#detailQuantity"+row).val())) <= 0)  {
				alert("La cantidad no es válida");
				$("#detailQuantity"+row).focus();
				return false;
			}
		}
	}

	if (!existsDetailValid) {
		alert("Debe ingresar al menos un insumo.");

		return false;
	}
	
	return true;	
}

function detectKeySupplierCodeBudgetEdit() {	
	$('#frmData #supplierCode').keydown( function(e) {
        var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
        if(key == 13) {
            e.preventDefault();
           
            searchSupplierBudgetEdit();
        }
        if(key == 113) {
        	openSuppliersFinderBudgetEdit();
        }
    });    
}

function deleteRowDetailBudgetEdit(row,confirmation) {
	if (confirmation == null) confirmation = true;

	if (confirmation) {
		if (!confirm('Realmente desea eliminar este registro?')) {
			return;	
		}
	}

	$('#grDetails #'+row).remove();
	$('#details #detail'+row).remove();
}

function newRowDetailBudgetEdit() {	
	var row = parseInt($('#detailsCount').val()) + 1;
	
	var rowHtml = "";
	rowHtml = rowHtml + '<tr id="'+row+'">';
	rowHtml = rowHtml + '<td class="text-center without-padding">';
	rowHtml = rowHtml + '<button type="button" class="btn without-padding" onclick="deleteRowDetailBudgetEdit('+row+')" title="eliminar"><i class="far fa-minus-square"></i></button>';  
	rowHtml = rowHtml + '</td>';
	rowHtml = rowHtml + '<td class="without-padding"><input type="text" id="detailCode'+row+'" name="detailCode'+row+'" class="form-control form-control-sm detailColumn" maxlength="25" autocomplete="off" value="" readonly="readonly"></td>';
	rowHtml = rowHtml + '<td class="without-padding"><input type="text" id="detailDescription'+row+'" name="detailDescription'+row+'" class="form-control form-control-sm detailColumn" maxlength="100" autocomplete="off" value="" readonly="readonly"></td>';	
	rowHtml = rowHtml + '<td class="without-padding"><input type="number" min="0" step="0.01" id="detailUnitPrice'+row+'" name="detailUnitPrice'+row+'" class="form-control form-control-sm detailColumn text-right" maxlength="12" autocomplete="off" value="0.00" onchange="calculateTotalBudgetEdit();" onfocus="this.select();"></td>';	
	rowHtml = rowHtml + '<td class="without-padding">';
	rowHtml = rowHtml + '<select id="detailTaxPercentage'+row+'" name="detailTaxPercentage'+row+'" class="form-control form-control-sm detailColumn" onchange="calculateTotalBudgetEdit();" >';                            
	rowHtml = rowHtml + $('#taxPercentage').html();
	rowHtml = rowHtml + '</select>';	
	rowHtml = rowHtml + '</td>';	
	rowHtml = rowHtml + '<td class="without-padding"><input type="number" min="1" step="1" id="detailQuantity'+row+'" name="detailQuantity'+row+'" class="form-control form-control-sm detailColumn text-right" maxlength="3" autocomplete="off" value="1" readonly="readonly" onchange="calculateTotalBudgetEdit()" onfocus="this.select();"></td>';
	rowHtml = rowHtml + '<td class="without-padding"><input type="text" id="detailFamily'+row+'" name="detailFamily'+row+'" class="form-control form-control-sm detailColumn" maxlength="50" autocomplete="off" value="" readonly="readonly"></td>';
	rowHtml = rowHtml + '<td class="without-padding"><input type="number" min="0" step="0.01" id="detailTotal'+row+'" name="detailTotal'+row+'" class="form-control form-control-sm detailColumn text-right" maxlength="12" autocomplete="off" value="0.00" readonly="readonly"></td>';		
	rowHtml = rowHtml + '</tr>';

	$('#grDetails tbody').append(rowHtml);	 
	$('#detailsCount').val(row);                                                                                                                                                                                                                                                                                                                                                                                                                            
                                
	var divHtml = "";
	divHtml = divHtml + '<div id="detail'+row+'">';
	divHtml = divHtml + '<input type="hidden" id="detailId'+row+'" name="detailId'+row+'" value="0">';
	divHtml = divHtml + '<input type="hidden" id="detailItemOrderId'+row+'" name="detailItemOrderId'+row+'" value="0">';	
	divHtml = divHtml + '</div>';
	$('#details').append(divHtml);	 

	detectKeyDetailBudgetEdit();	
}

/* ATTACHMENT FILES */
function openAddFileBudgetEdit() {
	openUploadFiles('addFileBudgetEdit','removeFileBudgetEdit');
}

function addFileBudgetEdit(fileName,serverFileName) {
	if (fileName == null) fileName = "";
	if (serverFileName == null) serverFileName = "";

	if (fileName != "" && serverFileName != "") {
		var count = parseInt($('#attachmentsCount').val()) + 1;	
		$('#attachmentsCount').val(count);

		var divHtml = "";
		divHtml = divHtml + '<div id="attachmentData'+count+'">';
		divHtml = divHtml + '<input type="hidden" id="attachmentId'+count+'" name="attachmentId'+count+'" value="0">';
		divHtml = divHtml + '<input type="hidden" id="attachmentName'+count+'" name="attachmentName'+count+'" value="'+fileName+'">';
		divHtml = divHtml + '<input type="hidden" id="attachmentFile'+count+'" name="attachmentFile'+count+'" value="'+serverFileName+'">';
		divHtml = divHtml + '</div>';
		$('#attachmentsData').append(divHtml);	

		var spanHtml = "";
        spanHtml = spanHtml + '<div id="attachmentShow'+count+'" class="btn-group" style="float:left;margin-right:15px;margin-bottom:15px;">';
		spanHtml = spanHtml + '<button type="button" class="btn btn-default" onclick="downloadFileBudgetEdit('+count+')" >' + fileName + '</button>';
		spanHtml = spanHtml + '<button type="button" class="btn btn-default" onclick="confirmRemoveFileBudgetEdit('+count+')" title="eliminar archivo"><i class="fas fa-trash-alt"></i></button>';        
        spanHtml = spanHtml + '</div>';

		$('#attachmentsShow').append(spanHtml);
    }
}

function confirmRemoveFileBudgetEdit(fileNumber) {
	if (fileNumber == null) fileNumber = 0;

	if (fileNumber > 0) {
		if ($('#attachmentFile'+fileNumber) != null && $('#attachmentFile'+fileNumber).length > 0 && $('#attachmentFile'+fileNumber).val() != "") {
			if (confirm('Realmente desea eliminar el archivo?')) {
				if (parseFloat($('#attachmentId'+fileNumber).val()) <= 0) {
					removeFileTemp($('#attachmentFile'+fileNumber).val());
				}
				removeFileBudgetEdit($('#attachmentFile'+fileNumber).val());
			}
		}
	}
}

function removeFileBudgetEdit(serverFilename) {	
	if (serverFilename == null) serverFilename = "";

	if (serverFilename != "") {
		var attachmentsCount = parseInt($('#attachmentsCount').val());

		for (var i=1; i <= attachmentsCount; i++) {
			if ($('#attachmentFile'+i) != null && $('#attachmentFile'+i).length > 0 && $('#attachmentFile'+i).val() == serverFilename) {
				$('#attachmentShow'+i).remove();
				$('#attachmentData'+i).remove()

				return;
			}
		}
	}
}

function downloadFileBudgetEdit(fileNumber) {
	if (fileNumber == null) fileNumber = 0;

	if (fileNumber > 0) {
		if ($('#attachmentData'+fileNumber) != null && $('#attachmentData'+fileNumber).length > 0) {
			var attachmentId = parseFloat($('#attachmentId'+fileNumber).val());

			var url = $('#baseUrl').val()
			if (attachmentId <= 0) {
				url = url + "dropzone/download?";
				url = url + 'filename=' + sanitizeGet($('#attachmentName'+fileNumber).val());	
				url = url + '&serverFilename=' + sanitizeGet($('#attachmentFile'+fileNumber).val());									
			} else {
				url = url + "budgets/attachment/" +  $('#frmData #id').val() + "/" + attachmentId;								
			}	

			openNewTab(url);		
		}
	}
}

/* END ATTACHMENT FILES */
