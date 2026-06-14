function initializeOrders(error) {	
	if (error == null) error = "";

	inicializeComboFilter('companyIdsFilter');
	inicializeComboFilter('managerIdsFilter');
	inicializeComboFilter('familyIdsFilter');

	if (error != "") {
		alert(error);
	}	
}

function clearFilterOrders() {
	$('#companyIdsFilterSelected').val('all');
	$('#managerIdsFilterSelected').val('all');
	$('#familyIdsFilterSelected').val('all');

	clearFilter('frmFilter');
}

function searchOrders() { 
	getValueComboFilter('companyIdsFilter');	      
	getValueComboFilter('managerIdsFilter');	
	getValueComboFilter('familyIdsFilter');	

	generalSearch();
}

function initializeOrderEdit() {	
	initializeCharactersRemaining('userObservation');
	initializeCharactersRemaining('internalObservation');		
	calculateTotalOrderEdit();	
	detectKeyDetailOrderEdit();		
	changePriorityOrderEdit(false);
}

function seeImportsOrderEdit() {
	return ($('#seeImports') != null && parseInt($('#seeImports').val()) == 1);
}

function editImportsOrderEdit() {	
	return ($('#editImports') != null && parseInt($('#editImports').val()) == 1);
}

function calculateTotalOrderEdit() {	
	if (!seeImportsOrderEdit()) return;

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
			if (isNaN($('#detailPendingQuantity'+row).val()) || $('#detailPendingQuantity'+row).val() == "" || parseFloat($('#detailPendingQuantity'+row).val()) < 0) {
				$('#detailPendingQuantity'+row).val("0");
			}					
			 
			var quantity = parseFloat($('#detailQuantity'+row).val());
			if ($('#detailPendingQuantity'+row) != null && $('#detailPendingQuantity'+row).length > 0) quantity = quantity - parseFloat($('#detailPendingQuantity'+row).val());
			rowTotal = (parseFloat($('#detailUnitPrice'+row).val()) * quantity).toFixed(2);						
			
			$('#detailTotal'+row).val(roundValue(rowTotal,2));

			detailTotal = detailTotal + parseFloat($('#detailTotal'+row).val());
		}
	}	
	$('#total').val(roundValue(detailTotal,2));
}

function preSaveOrder() {	
	if (!paymentSectorsValidOrderEdit()) {
			return false;
	}
	
	if (!detailsValidOrderEdit()) {
			return false;
	}	

	if ($("#stateId").prop("disabled") != true && 
		$("#stateId").prop("readonly") != true && 
		$("#stateId").prop('selectedIndex') > 0) {
		if (!confirm("Realmente desea cambiar el estado del pedido a '" + $("#stateId option:selected").text() + "'?")) {
			return false;
		}
	}

	preSubmit();
}

// ******* FINDER ARTICLES *******

function openArticlesFinderOrderEdit(row) {	
	if (row == null) row = 0;

	if (row > 0 && row <= parseInt($('#detailsCount').val())) {
		$('#rowArticlesFinder').val(row);	

		var url = 'orders/articlesFinder/';

		if ($.trim($("#detailCode"+row).val()) != "" && $("#detailCode"+row).val() != $("#detailOriginalCode"+row).val()) {
			url = url + "1?text=" + $.trim($("#detailCode"+row).val());			
		} else {
			url = url + "0";
		}

		modalMessage(url,"Buscador de Artículos",null,null,null,null,'closeModalMessage()',true,"initializeFinderOrderEdit()",null,800);
	}
}

function acceptArticlesFinderOrderEdit(finderRow) {	
	if (finderRow == null) finderRow = 0;

	if (parseInt(finderRow) > 0) {
		var detailRow = $('#rowArticlesFinder').val();
		
		$('#frmData #detailCode'+detailRow).val($('#bodyModal #afCode'+finderRow).val());
				
		searchArticleOrderEdit(detailRow);

		closeModalMessage();
		
		if ($('#frmData #detailCode'+detailRow).val() != "") {
			$("#frmData #detailQuantity"+detailRow).focus();			
		}				
	} else {
		closeModalMessage();
	}
}


// ******* END FINDER ARTICLES *******

// ******* FINDER GENERAL *******

function initializeFinderOrderEdit() {
	initializeKeyDetection();
	$('#frmFinderFilter #textFilter').focus();	
}

function reloadFinderOrderEdit(url,frm) {	
	if (url == null) url = "";

	if (url != "") {
		loadDataByAjax(url,'bodyModal','initializeFinderOrderEdit()',frm);			    							
	}		
}

function searchFinderOrderEdit(type,clear) {	
	if (type == null) type = '';
	if (clear == null) clear = false;

	if (type != '') {
		if (clear) clearFilter('frmFinderFilter',false);

		var url = $('#baseUrl').val()+'orders/'+type+'Finder/';
		
		reloadFinderOrderEdit(url,'frmFinderFilter');
	}
}

// ******* END FINDER GENERAL *******

function searchArticleOrderEdit(row) {	
	if (row == null) row = 0;

	if (row > 0 && row <= parseInt($('#detailsCount').val())) {
		if ($("#detailCode"+row).val() != $("#detailOriginalCode"+row).val()) {
			//Change the code of article
			var code = $.trim($("#detailCode"+row).val());

			loadArticleOrderEdit(row,code);			
		}
	} 
}

function loadArticleOrderEdit(row,code) {
	if (row == null) row = 0;

	if (row > 0 && row <= parseInt($('#detailsCount').val())) {
		if (code != "") {	 		
			for (var i=1; i <= parseInt($('#detailsCount').val()); i++)	{
				if (i != row && code == $("#detailCode"+i).val()) {
					alert('El artículo seleccionado ya está ingresado en el pedido.');
					code = "";
				}
			}
		}

	 	if (code != "") {	 		
	 		$("#frmSearchArticle #code").val(code);	 		
	 		$("#frmSearchArticle #resultSearchArticle").html("");

	 		loadDataByAjax($('#baseUrl').val()+'orders/searchArticle','resultSearchArticle','terminateLoadArticleOrderEdit('+row+')','frmSearchArticle');		 		
			
		} else {	
			$("#detailCode"+row).val("");
			$("#detailOriginalCode"+row).val("");			
			clearRowDetailOrderEdit(row);	
			calculateTotalOrderEdit();			
			$("#detailCode"+row).focus();
		}
	} 
}

function terminateLoadArticleOrderEdit(row) {	
	if (row == null) row = 0;

	if (row > 0 && row <= parseInt($('#detailsCount').val())) {
		if ($("#frmSearchArticle #codeArticle").length > 0) {
			$("#detailCode"+row).val($("#frmSearchArticle #codeArticle").val());	
			$("#detailDescription"+row).val($("#frmSearchArticle #descriptionArticle").val());								
			if ($("#detailUnitPrice"+row) != null) $("#detailUnitPrice"+row).val($("#frmSearchArticle #unitPriceArticle").val());
			$("#detailQuantity"+row).val($("#frmSearchArticle #quantityArticle").val());	
			$("#detailUsual"+row).val($("#frmSearchArticle #usualArticle").val());	
			$("#detailFamily"+row).val($("#frmSearchArticle #familyArticle").val());			
			if ($("#detailTotal"+row) != null) $("#detailTotal"+row).val($("#frmSearchArticle #totalArticle").val());			
			$("#detailArticleId"+row).val($("#frmSearchArticle #articleId").val());
			$("#detailOriginalCode"+row).val($("#frmSearchArticle #codeArticle").val());					

			calculateTotalOrderEdit();
		} else {
			loadArticleOrderEdit(row,"");
		}		
	}
}

function clearRowDetailOrderEdit(row) {	
	if (row == null) row = 0;

	if (row > 0 && row <= parseInt($('#detailsCount').val())) {
		$("#detailDescription"+row).val('');		
		if ($("#detailUnitPrice"+row) != null) $("#detailUnitPrice"+row).val('0.00');
		$("#detailQuantity"+row).val('1');	
		$("#detailUsual"+row).val('');
		$("#detailFamily"+row).val('');		
		if ($("#detailTotal"+row) != null) $("#detailTotal"+row).val('0.00');					
		$("#detailArticleId"+row).val('0');						
	} 
}

function deleteRowDetailOrderEdit(row,confirmation) {
	if (confirmation == null) confirmation = true;

	if (confirmation) {
		if (!confirm('Realmente desea eliminar este registro?')) {
			return;	
		}
	}

	$('#grDetails #'+row).remove();
	$('#details #detail'+row).remove();
}

function newRowDetailOrderEdit() {	
	var row = parseInt($('#detailsCount').val()) + 1;
	
	var rowHtml = "";
	rowHtml = rowHtml + '<tr id="'+row+'">';
	rowHtml = rowHtml + '<td class="text-center without-padding">';
	rowHtml = rowHtml + '<button type="button" class="btn without-padding" onclick="deleteRowDetailOrderEdit('+row+')" title="eliminar"><i class="far fa-minus-square"></i></button>';  
	rowHtml = rowHtml + '</td>';
	rowHtml = rowHtml + '<td class="without-padding"><input type="text" id="detailCode'+row+'" name="detailCode'+row+'" class="form-control form-control-sm detailColumn" maxlength="25" autocomplete="off" value="" onblur="searchArticleOrderEdit('+row+')"></td>';
	rowHtml = rowHtml + '<td class="without-padding"><input type="text" id="detailDescription'+row+'" name="detailDescription'+row+'" class="form-control form-control-sm detailColumn" maxlength="100" autocomplete="off" value="" readonly="readonly"></td>';
	if (seeImportsOrderEdit()) {
		rowHtml = rowHtml + '<td class="without-padding"><input type="number" min="0" step="0.01" id="detailUnitPrice'+row+'" name="detailUnitPrice'+row+'" class="form-control form-control-sm detailColumn text-right" maxlength="12" autocomplete="off" value="0.00" readonly="readonly" onchange="calculateTotalOrderEdit();" onfocus="this.select();"';
		if (!editImportsOrderEdit()) rowHtml = rowHtml + ' readonly="readonly"';
		rowHtml = rowHtml + '></td>';
	}
	rowHtml = rowHtml + '<td class="without-padding"><input type="number" min="0" step="1" id="detailQuantity'+row+'" name="detailQuantity'+row+'" class="form-control form-control-sm detailColumn text-right" maxlength="3" autocomplete="off" value="1" onchange="calculateTotalOrderEdit()" onfocus="this.select();"></td>';
	rowHtml = rowHtml + '<td class="without-padding"><input type="text" id="detailUsual'+row+'" name="detailUsual'+row+'" class="form-control form-control-sm detailColumn text-center" autocomplete="off" value="" readonly="readonly" onfocus="this.select();"></td>';
	rowHtml = rowHtml + '<td class="without-padding"><input type="text" id="detailFamily'+row+'" name="detailFamily'+row+'" class="form-control form-control-sm detailColumn" maxlength="50" autocomplete="off" value="" readonly="readonly"></td>';
	if (seeImportsOrderEdit()) rowHtml = rowHtml + '<td class="without-padding"><input type="number" min="0" step="0.01" id="detailTotal'+row+'" name="detailTotal'+row+'" class="form-control form-control-sm detailColumn text-right" maxlength="12" autocomplete="off" value="0.00" readonly="readonly"></td>';		
	rowHtml = rowHtml + '</tr>';

	$('#grDetails tbody').append(rowHtml);	 
	$('#detailsCount').val(row);                                                                                                                                                                                                                                                                                                                                                                                                                            
                                
	var divHtml = "";
	divHtml = divHtml + '<div id="detail'+row+'">';
	divHtml = divHtml + '<input type="hidden" id="detailId'+row+'" name="detailId'+row+'" value="0">';
	divHtml = divHtml + '<input type="hidden" id="detailArticleId'+row+'" name="detailArticleId'+row+'" value="0">';
	divHtml = divHtml + '<input type="hidden" id="detailOriginalCode'+row+'" name="detailOriginalCode'+row+'" value="">';	
	if (!seeImportsOrderEdit()) divHtml = divHtml + '<input type="hidden" id="detailUnitPrice'+row+'" name="detailUnitPrice'+row+'" value="0">';
	divHtml = divHtml + '</div>';
	$('#details').append(divHtml);	 

	detectKeyDetailOrderEdit();

	$('#detailCode'+row).focus();
}

function isRowEmptyDetailOrderEdit(row) {
	var isRowEmpty = false;
	if (row == null) row = 0;

	if (row > 0 && row <= parseInt($('#detailsCount').val())) {
		if ($('#grDetails #'+row).length > 0) {
			if ($.trim($('#grDetails #detailCode'+row).val()) == "" &&
				$.trim($('#grDetails #detailDescription'+row).val()) == "") {
				isRowEmpty = true;	  
			}		
		}
	} 

	return isRowEmpty;
}

function detectKeyDetailOrderEdit() {
	$('.detailColumn').keydown( function(e) {
        var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
        if(key == 13) {
            e.preventDefault();
            var inputs = $(this).closest('form').find(':input:visible');
            inputs.eq(inputs.index(this)+ 1).focus();

            var lastRow = lastRowDetailOrderEdit();
            var row = $(this).parent().parent().attr("id");
            if ($(this).attr("id") == "detailQuantity"+lastRow && $('#btnNewRow') != null && $('#btnNewRow').length > 0 && $.trim($("#detailCode"+row).val()) != "") {
        		newRowDetailOrderEdit(row);
        	}
        }
        if(key == 113) {
        	var row = $(this).parent().parent().attr("id");
        	if ($(this).attr("id") == "detailCode"+row) {
        		openArticlesFinderOrderEdit(row);
        	}
        }
    });
}

function lastRowDetailOrderEdit() {
	var detailsCount = parseInt($('#detailsCount').val());

	for (var row=detailsCount; row >= 1; row--) {
		if ($('#grDetails #'+row).length > 0) {
			return row;		
		}
	}

	return 0;
}

function detailsValidOrderEdit() {
	var detailsCount = parseInt($('#detailsCount').val());
	var existsDetailValid = false;	
	
	for (var row=1; row <= detailsCount; row++) {
		if ($('#grDetails #'+row).length > 0) {
			if ($.trim($("#detailDescription"+row).val()) == "") {
				if ($.trim($("#detailCode"+row).val()) == "") {
					deleteRowDetailOrderEdit(row,false);
				} else {
					alert("Debe completar la descripción del insumo");
					$("#detailDescription"+row).focus();
					return false;
				}				
			} else {
				existsDetailValid = true;
			}		
		}
	}

	if (!existsDetailValid) {
		alert("Debe ingresar al menos un insumo.");

		return false;
	}

	return true;
}

function selectActionOrderEdit(action) {
	if (action == null) action = '';
	$('#action').val(action);	
}

function printOrder(id,documentType) {
	if (id == null) id = 0;
	if (documentType == null) documentType = '';

	if (id > 0) {		
		if (documentType == "DN") {			
			if ($('#btnPrintDN'+id) != null) {
				$('#btnPrintDN'+id).attr("disabled",true);
				$('#btnPrintDN'+id).attr("title","el remito ya fue impreso");
			}
		}

		var url = $('#baseUrl').val()+'orders/print/'+id+'/'+documentType;		

		openNewWindow(url,"width=900,height=600,resizable=0");
	}
}

function exportOrder(id,documentType,format) {
	if (id == null) id = 0;
	if (documentType == null) documentType = '';
	if (format == null) format = 'P';

	if (id > 0 && (format == 'P' || format == 'E')) {	
		if (format == 'P') method = 'pdf';	
		if (format == 'E') method = 'excel';
		var url = $('#baseUrl').val()+'orders/'+method+'/'+id+'/'+documentType;		

		openNewTab(url);
	}
}

/* ATTACHMENT FILES */
function openAddFileOrderEdit() {
	openUploadFiles('addFileOrderEdit','removeFileOrderEdit');
}

function addFileOrderEdit(fileName,serverFileName) {
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
		spanHtml = spanHtml + '<button type="button" class="btn btn-default" onclick="downloadFileOrderEdit('+count+')" >' + fileName + '</button>';
		spanHtml = spanHtml + '<button type="button" class="btn btn-default" onclick="confirmRemoveFileOrderEdit('+count+')" title="eliminar archivo"><i class="fas fa-trash-alt"></i></button>';        
        spanHtml = spanHtml + '</div>';

		$('#attachmentsShow').append(spanHtml);
    }
}

function confirmRemoveFileOrderEdit(fileNumber) {
	if (fileNumber == null) fileNumber = 0;

	if (fileNumber > 0) {
		if ($('#attachmentFile'+fileNumber) != null && $('#attachmentFile'+fileNumber).length > 0 && $('#attachmentFile'+fileNumber).val() != "") {
			if (confirm('Realmente desea eliminar el archivo?')) {
				if (parseFloat($('#attachmentId'+fileNumber).val()) <= 0) {
					removeFileTemp($('#attachmentFile'+fileNumber).val());
				}
				removeFileOrderEdit($('#attachmentFile'+fileNumber).val());
			}
		}
	}
}

function removeFileOrderEdit(serverFilename) {	
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

function downloadFileOrderEdit(fileNumber) {
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
				url = url + "orders/attachment/" +  $('#frmData #id').val() + "/" + attachmentId;								
			}	

			openNewTab(url);		
		}
	}
}

/* END ATTACHMENT FILES */

function openWhatsAppOrderEdit(mobile) {
	if (mobile == null) mobile = false;

	openWhatsApp($('#userCellphone').val(),'',mobile);
}

function seeHistoryOrderEdit(orderId) {
	if (orderId == null) orderId = -1;

	if (orderId > 0) {
		modalMessage('orders/history/'+orderId,"Pedido Nº "+orderId,null,null,null,null,'closeModalMessage()',true,null,null,800);		
	}
}

function changeStateOrderEdit(userRol) {
	if (userRol == null) userRol = "";

	if (userRol == 'user') {
		switch ($('#stateId option:selected').val()) {
			case "ARM":
				$('#authorizingUserId').html('<option value="0" selected="selected">[Sin Asignar]</option>');
				selectFirstCombo('authorizingUserId');				
				$('#authorizingUserId').attr('readonly','readonly');
			break;

			case "TOAUT":
				$('#authorizingUserId').html('<option value="">Cargando...</option>');	
				var url = $('#baseUrl').val()+'users/usersCombo?type=authorizingUser'
				url = url + '&com='+$('#companyId').val();			
				url = url + '&bo='+$('#branchOfficeId').val();			
				url = url + '&sec='+$('#sectorId').val();			
				loadDataByAjax(url,'authorizingUserId',null,null,true);
				$('#authorizingUserId').removeAttr('readonly');
			break;
		}
	}	

	if ($('#stateId option:selected').val() == 'NOTAUT') {
		$('#userObservation').attr('required','1');
	} else {
		$('#userObservation').removeAttr('required');
	}
}

function changePriorityOrderEdit(chargeDefaultValue) {	
	if (chargeDefaultValue == null) chargeDefaultValue = true;

	$('#priorityId').css('color',$('#priorityId option:selected').attr('color'));

	loadComboSubpriorityOrderEdit();

	if ($('#maximumDate').length == 0) {		
		if ($('#btnSave').length > 0 
			&& $("#priorityId").attr("readonly") != "readonly" 
			&& $('#priorityId option:selected').attr('editDays') != null 
			&& $('#priorityId option:selected').attr('editDays') == '1') {		
			$('#maximumDays').removeAttr('readonly');
		} else {
			$('#maximumDays').attr('readonly','readonly');		
		}
		if ($('#priorityId option:selected').val() == "") {
			$('#maximumDays').addClass("hide");
			$('#lblMaximumDays').addClass("hide");

			$('#maximumDays').val('0');
			$('#maximumDays').attr('readonly','readonly');	
		} else {
			$('#maximumDays').removeClass("hide");
			$('#lblMaximumDays').removeClass("hide");

			if (isNaN($('#maximumDays').val()) || parseFloat($('#maximumDays').val()) <= 0 || chargeDefaultValue) {	
				$('#maximumDays').val($('#priorityId option:selected').attr('defaultDays'));
			}	

			if ($('#priorityId option:selected').attr('minDays') != null 
			    && parseFloat($('#priorityId option:selected').attr('minDays')) > 0) {
				$('#maximumDays').attr("min",$('#priorityId option:selected').attr('minDays'));
			} else {
				$('#maximumDays').attr('min',"0");
			}

			if ($('#priorityId option:selected').attr('maxDays') != null 
			    && parseFloat($('#priorityId option:selected').attr('maxDays')) > 0) {
				$('#maximumDays').attr("max",$('#priorityId option:selected').attr('maxDays'));
			} else {
				$('#maximumDays').removeAttr('max');
			}
		}		
	} 
}

function loadComboSubpriorityOrderEdit() {
	var selValue = 0;
	if ($('#subpriorityId').attr('selValue') != null) selValue = $('#subpriorityId').attr('selValue');
	$('#subpriorityId').html('');	
	$('#subpriorityId').addClass('hide');
	var url = $('#baseUrl').val()+'orders/subprioritiesCombo/'+$('#priorityId').val()+"/"+selValue;		
	if ($('#priorityId').attr("readonly") != null) {
		url = url + "/1";
	}		
	loadDataByAjax(url,'subpriorityId','finalizeComboSubpriorityOrderEdit',null,true);	

	$('#subpriorityId').attr('selValue',"0");		    						
}

function finalizeComboSubpriorityOrderEdit() {
	if ($('#subpriorityId option').length >= 1) {
		$('#subpriorityId').removeClass('hide');
		$('#subpriorityId').attr("disabled",false);		
	} else {
		$('#subpriorityId').addClass('hide');
		$('#subpriorityId').attr("disabled",true);	
	}
	if ($('#priorityId').attr("readonly") != null) {
		$('#subpriorityId').attr("readonly",true);
	} 
}

function seeDeliveryNotesOrderEdit(orderId) {
	if (orderId == null) orderId = -1;

	if (orderId > 0) {
		modalMessage('orders/deliveryNotes/'+orderId,"Remitos del Pedido Nº "+orderId,null,null,null,null,'closeModalMessage()',true,null,null,800);		
	}
}

function generateDeliveryNotesOrderEdit(orderId) {
	if (orderId == null) orderId = -1;

	if (orderId > 0) {
		modalMessage('orders/deliveryNote/'+orderId+'/0',"Generar Remito para el Pedido Nº "+orderId,null,null,null,null,'closeModalMessage()',true,'initializateGenerateDeliveryNotesOrderEdit()',null,900);		
	}
}

function initializateGenerateDeliveryNotesOrderEdit() {
	updateItemDeliveryNote();
}

function seeDeliveryNoteOrderEdit(orderId,deliveryNoteId,deliveryNotNumber) {
	if (orderId == null) orderId = -1;
	if (deliveryNoteId == null) deliveryNoteId = -1;

	if (orderId > 0 || deliveryNoteId > 0) {
		modalMessage('orders/deliveryNote/'+orderId+'/'+deliveryNoteId,"Remito Nº "+deliveryNotNumber,null,null,null,null,'closeModalMessage()',true,'initializeDeliveryNoteOrderEdit',null,800);		
	}
}

function initializeDeliveryNoteOrderEdit() {
	if ($('#receivedObservation').attr("disabled") != true) {
		initializeCharactersRemaining('receivedObservation');
	}	
}

function selectItemsDeliveryNote() {	
	if ($('#allItems').prop('checked') == true) {
		$('.item').prop('checked',true);
	} else {
		$('.item').prop('checked',false);
	}

	updateItemDeliveryNote();
}

function  updateItemDeliveryNote() {
	$('.item').each(function(){
		selectItemDeliveryNote(this);
	});
}

function selectItemDeliveryNote(obj,focusInput) {	
	if (obj != null) {
		if (focusInput == null) focusInput = false;

		var id = "dnFreeQuantity" + $(obj).attr("row");

		if ($(obj).prop('checked') == true) {
			if (parseFloat($('#'+id).val()) == 0) {
				$('#'+id).val($('#'+id).attr("max"));
			}
			$('#'+id).attr("disabled",false);			
		} else {
			$('#'+id).attr("disabled",true);
			$('#'+id).val("0");			
		}				

		if (focusInput) $('#'+id).focus();
	}
}

function sendDeliveryNotesOrderEdit() {
   	if (validDeliveryNotesOrderEdit()) {
   		errorDeliveryNotesOrderEdit('');

   		var url = $('#baseUrl').val()+'orders/saveDeliveryNote'

		loadDataByAjax(url,'bodyModal','','frmDataDN');	
   	}
}

function validDeliveryNotesOrderEdit() {
	var itemsCount = parseInt($('#itemsCount').val());
	var selectedItems = false;

	for (var row=1; row <= itemsCount; row++) {
		var id = "#dnFreeQuantity" + row;
		if ($(id).attr('disabled') == null || $(id).attr('disabled') == false) {
			if (parseFloat($(id).val()) <= 0) {
				alert('El valor a entregar no es válido: debe ser mayor a 0.');
				$(id).focus();
				return false;
			} else {
				selectedItems = true;
			}
			if (parseFloat($(id).val()) > parseFloat($(id).attr("max"))) {
				alert('El valor a entregar no es válido, no puede ser mayor a lo pendiente de entregar.');
				$(id).focus();
				return false;
			}
			/*
			if (parseFloat($(id).val()) > parseFloat($(id).attr("stock"))) {
				alert('El valor a entregar no es válido, no puede ser mayor a la cantidad actual en stock.');
				$(id).focus();
				return false;
			}
			*/			
		}
	}

	if (!selectedItems) {
		alert('Debe ingresar un valor a entregar válido en alguno de los items.');				
		return false;
	}

	return true;
}

function errorDeliveryNotesOrderEdit(message) {	
	if (message == null) message = '';

	$('#frmDataDN #errorDeliveryNote').html(message);
	if ($.trim(message) == "") {
		if (!$('#frmDataDN #errorDeliveryNote').hasClass('hide')) {
			$('#frmDataDN #errorDeliveryNote').addClass('hide');
		}		
	} else {
		if ($('#frmDataDN #errorDeliveryNote').hasClass('hide')) {
			$('#frmDataDN #errorDeliveryNote').removeClass('hide');
		}
	}	
}

function saveOkDeliveryNotesOrderEdit(orderId) {
	if (orderId == null) orderId = 0;
	if (orderId > 0) {
		location.href = $('#baseUrl').val()+'orders/edit/'+orderId;		
	}
}

function sendReceivedDataDeliveryNotesOrderEdit() {
   	if (validReceivedDataDeliveryNotesOrderEdit()) {
   		errorDeliveryNotesOrderEdit('');

   		var url = $('#baseUrl').val()+'orders/saveReceivedDataDeliveryNote'

		loadDataByAjax(url,'bodyModal','','frmDataDN');	
   	}
}

function validReceivedDataDeliveryNotesOrderEdit() {
	if ($.trim($('#receivedDate').val()) == "") {
		alert('La fecha de recibido no es válida.');
		$('#receivedDate').focus();
		return false;
	}
	
	if ($.trim($('#receivedBy').val()) == "") {
		alert('El valor de quien recibe no es válido.');
		$('#receivedBy').focus();
		return false;
	}

	return true;
}

function seeOrderItemData(row) {
	if (row == null) row = -1;

	if (row > 0) {
		var detailOrderId = $('#detailId'+row).val();
		var article = "Cód. " + $('#detailCode'+row).val() + " / " + $('#detailDescription'+row).val();
		modalMessage('orders/orderItemData/'+detailOrderId,"Artículo: " + article,null,null,null,null,'closeModalMessage()',true,null,null,800);		
	}
}

function selectCompanyOrderEdit() {
	$('#branchOfficeId').attr('selValue',"0");	
	loadBranchOfficesOrderEdit();
}

function loadBranchOfficesOrderEdit() {
	var selValue = 0;
	if ($('#branchOfficeId').attr('selValue') != null) selValue = $('#branchOfficeId').attr('selValue');
	$('#branchOfficeId').html('<option value="">Cargando...</option>');	
	var url = $('#baseUrl').val()+'branchOffices/branchOfficesCombo/'+$('#companyId').val()+"/"+selValue;				
	loadDataByAjax(url,'branchOfficeId','selectBranchOfficeOrderEdit',null,true);			    						
}

function selectBranchOfficeOrderEdit() {
	$('#sectorId').attr('selValue',"0");	
	loadSectorsOrderEdit();
}

function loadSectorsOrderEdit() {
	var selValue = 0;
	if ($('#sectorId').attr('selValue') != null) selValue = $('#sectorId').attr('selValue');
	$('#sectorId').html('<option value="">Cargando...</option>');	
	var url = $('#baseUrl').val()+'sectors/sectorsCombo/'+$('#branchOfficeId').val()+"/"+selValue+"?ao=1";				
	loadDataByAjax(url,'sectorId','loadAuthorizingUsersOrderEdit()',null,true);			    						
}

function loadAuthorizingUsersOrderEdit() {
	var companyId = 0;
	if ($('#companyId option:selected').val() != "") companyId = parseFloat($('#companyId option:selected').val());
	
	var branchOfficeId = $('#branchOfficeId option:selected').val();
	if ($('#branchOfficeId option:selected').val() != "") branchOfficeId = parseFloat($('#branchOfficeId option:selected').val());

	var sectorId = $('#sectorId option:selected').val();
	if ($('#sectorId option:selected').val() != "") sectorId = parseFloat($('#sectorId option:selected').val());

	if (companyId > 0 && branchOfficeId > 0 && sectorId > 0) {
		$('#authorizingUserId').html('<option value="">Cargando...</option>');	
		var url = $('#baseUrl').val()+'users/usersCombo?type=authorizinguser';				
		url = url + '&com=' + companyId;
		url = url + '&bo=' + branchOfficeId;
		url = url + '&sec=' + sectorId;

		loadDataByAjax(url,'authorizingUserId',null,null,true);			    						
	} else {
		$('#authorizingUserId').html('<option value="">[Seleccionar]</option>');	
	}
}

function selectPaymentCompanyOrderEdit() {		
	$('#paymentBranchOfficeId').html('<option value="">Cargando...</option>');	
	var url = $('#baseUrl').val()+'branchOffices/branchOfficesCombo/'+$('#paymentCompanyId').val()+"?fo=ALL_F";				
	loadDataByAjax(url,'paymentBranchOfficeId','selectPaymentBranchOfficeOrderEdit',null,true);			    						
}

function selectPaymentBranchOfficeOrderEdit() {
	$('#paymentSectorId').html('<option value="">Cargando...</option>');	
	var url = $('#baseUrl').val()+'sectors/sectorsCombo/'+$('#paymentBranchOfficeId').val()+"?ap=1&fo=ALL_M";				
	loadDataByAjax(url,'paymentSectorId',null,null,true);	
}

function addPaymentSectorOrderEdit() {
	if (paymentSectorValidOrderEdit()) {
		var row = parseInt($('#pSectorsCount').val()) + 1;
	
		var rowHtml = "";
		rowHtml = rowHtml + '<tr id="'+row+'">';
		rowHtml = rowHtml + '<td class="text-center without-padding">';
		rowHtml = rowHtml + '<button type="button" class="btn without-padding" onclick="deletePaymentSectorOrderEdit('+row+')" title="eliminar"><i class="far fa-minus-square"></i></button>';  
		rowHtml = rowHtml + '</td>';
		rowHtml = rowHtml + '<td class="without-padding"><input type="text" id="pCompany'+row+'" name="pCompany'+row+'" class="form-control form-control-sm" maxlength="100" autocomplete="off" value="'+$('#paymentCompanyId option:selected').text()+'" readonly="readonly"></td>';
		rowHtml = rowHtml + '<td class="without-padding"><input type="text" id="pBranchOffice'+row+'" name="pBranchOffice'+row+'" class="form-control form-control-sm" maxlength="100" autocomplete="off" value="'+$('#paymentBranchOfficeId option:selected').text()+'" readonly="readonly"></td>';
		rowHtml = rowHtml + '<td class="without-padding"><input type="text" id="pSector'+row+'" name="pSector'+row+'" class="form-control form-control-sm" maxlength="100" autocomplete="off" value="'+$('#paymentSectorId option:selected').text()+'" readonly="readonly"></td>';		
		rowHtml = rowHtml + '<td class="without-padding"><input type="text" id="pPercent'+row+'" name="pPercent'+row+'" class="form-control form-control-sm text-right" maxlength="6" autocomplete="off" value="'+$('#paymentPercent').val()+'" onfocus="this.select();"></td>';
		rowHtml = rowHtml + '</tr>';

		$('#grPaymentSectors tbody').append(rowHtml);	 
		$('#pSectorsCount').val(row);                                                                                                                                                                                                                                                                                                                                                                                                                            
	                                
		var divHtml = "";
		divHtml = divHtml + '<div id="pSector'+row+'">';
		divHtml = divHtml + '<input type="hidden" id="pId'+row+'" name="pId'+row+'" value="0">';
		divHtml = divHtml + '<input type="hidden" id="pCompanyId'+row+'" name="pCompanyId'+row+'" value="'+$('#paymentCompanyId option:selected').val()+'">';
		divHtml = divHtml + '<input type="hidden" id="pBranchOfficeId'+row+'" name="pBranchOfficeId'+row+'" value="'+$('#paymentBranchOfficeId option:selected').val()+'">';
		divHtml = divHtml + '<input type="hidden" id="pSectorId'+row+'" name="pSectorId'+row+'" value="'+$('#paymentSectorId option:selected').val()+'">';
		
		divHtml = divHtml + '</div>';
		$('#pSectors').append(divHtml);	 
	}
}

function deletePaymentSectorOrderEdit(row,confirmation) {
	if (confirmation == null) confirmation = true;

	if (confirmation) {
		if (!confirm('Realmente desea eliminar este registro?')) {
			return;	
		}
	}

	$('#grPaymentSectors #'+row).remove();
	$('#pSectors #pSector'+row).remove();
}

function paymentSectorValidOrderEdit() {	
	if ($('#paymentCompanyId option:selected').val() == "" || $('#paymentCompanyId option:selected').val() == "0") {
		alert('Debe seleccionar una Empresa');
		return false;
	}	
	if ($('#paymentBranchOfficeId option:selected').val() == "" || $('#paymentBranchOfficeId option:selected').val() == "0") {
		alert('Debe seleccionar una Sucursal');
		return false;
	}
	if ($('#paymentSectorId option:selected').val() == "" || $('#paymentSectorId option:selected').val() == "0") {
		alert('Debe seleccionar un Sector');
		return false;
	}
	
	var pSectorsCount = parseInt($('#pSectorsCount').val());
	var companyId = parseFloat($('#paymentCompanyId option:selected').val());	
	var branchOfficeId = parseFloat($('#paymentBranchOfficeId option:selected').val());	
	var sectorId = parseFloat($('#paymentSectorId option:selected').val());	
	for (var row=1; row <= pSectorsCount; row++) {
		if ($('#pSectors #pSector'+row).length > 0) {	
			var iCompanyId = parseFloat($('#pCompanyId'+row).val());	
			var iBranchOfficeId = parseFloat($('#pBranchOfficeId'+row).val());	
			var iSectorId = parseFloat($('#pSectorId'+row).val());	

			if (companyId == 0) {
				alert("No se puede seleccionar 'Todas' las Empresas si ya hay registros cargados");
				return false;
			}
			if (iCompanyId == 0) {
				alert("Ya se cargaron 'Todas' las empresas");
				return false;
			}

			if (companyId == iCompanyId) {
				if (branchOfficeId == iBranchOfficeId && sectorId == iSectorId) {
					alert('La Empresa/Sucursal/Sector ya se encuentra cargada');
					return false;
				}
				if (branchOfficeId > 0 && branchOfficeId == iBranchOfficeId && sectorId == 0) {
					alert('La Empresa/Sucursal ya posee registros cargados');
					return false;
				}
				if (branchOfficeId == 0) {
					alert('La Empresa ya posee registros cargados');
					return false;
				}
				if (branchOfficeId > 0 && sectorId > 0 && branchOfficeId == iBranchOfficeId && iSectorId == 0) {
					alert('La Empresa/Sucursal ya se encuentra cargada en su totalidad');
					return false;
				}
				if (branchOfficeId > 0 && iBranchOfficeId == 0) {
					alert('La Empresa ya se encuentra cargada en su totalidad');
					return false;
				}
			} 	
		}
	}
	if ($.trim($('#paymentPercent').val()) == "" || isNaN($('#paymentPercent').val())) {
		alert('El Porcentaje no es válido');
		return false;
	}
	if(parseFloat($('#paymentPercent').val()) <= 0 || parseFloat($('#paymentPercent').val()) > 100) {
		alert('El Porcentaje no es válido: el valor debe ser mayor a 0 y menor o igual a 100');
		return false;	
	}	

	return true;
}

function paymentSectorsValidOrderEdit() {	
	var pSectorsCount = parseInt($('#pSectorsCount').val());
	var existsPaymentSectorValid = false;	
	
	var totalPercent = 0;
	for (var row=1; row <= pSectorsCount; row++) {
		if ($('#pSectors #pSector'+row).length > 0) {
			existsPaymentSectorValid = true;			
			if ($.trim($("#pPercent"+row).val()) == "" || isNaN($("#pPercent"+row).val())) {				
				alert('Debe ingresar un Porcentaje');
				openBtnUpDown('btnPaymentSectors');
				$("#pPercent"+row).focus();
				return false;
			} 
			if(parseFloat($("#pPercent"+row).val()) <= 0 || parseFloat($("#pPercent"+row).val()) > 100) {
				alert('El Porcentaje no es válido: el valor debe ser mayor a 0 y menor o igual a 100');				
				openBtnUpDown('btnPaymentSectors');
				$("#pPercent"+row).focus();
				return false;
			}	
			totalPercent = totalPercent + parseFloat($("#pPercent"+row).val()) ;					
		}
	}

	if (existsPaymentSectorValid && totalPercent != 100) {
		alert("La suma total del Porcentaje a imputar a cada sector debe ser igual a 100");
		openBtnUpDown('btnPaymentSectors');
		return false;
	}
	
	return true;
}

function seeCancelItemOrderEdit(row) {
	if (row == null) row = -1;

	if (row > 0) {
		var orderId = $('#frmData #id').val();
		var detailOrderId = $('#detailId'+row).val();
		var article = "Cód. " + $('#detailCode'+row).val() + " / " + $('#detailDescription'+row).val();
		modalMessage('orders/cancelItemOrder/'+orderId+'/'+detailOrderId,"Artículo: " + article,null,null,null,null,'closeModalMessage()',true,null,null,800);		
	}
}

function sendCancelItemOrderEdit() {
   	if (validCancelItemOrderEdit()) {
   		errorCancelItemOrderEdit('');

   		var url = $('#baseUrl').val()+'orders/saveCancelItemOrder'

		loadDataByAjax(url,'bodyModal','','frmDataIC');			
   	}
}

function validCancelItemOrderEdit() {
	var maximumCount = parseFloat($('#frmDataIC #maximumCount').val());

	if (isNaN($('#frmDataIC #count').val()) || $('#frmDataIC #count').val() == "" || parseFloat($('#frmDataIC #count').val()) <= 0) {
		errorCancelItemOrderEdit('El valor de la cantidad no es válido.')
		$('#frmDataIC #count').focus();
		return false;	
	}
	
	if (parseFloat($('#frmDataIC #count').val()) > maximumCount) {
		errorCancelItemOrderEdit('El valor de la cantidad no puede ser mayor a ' + maximumCount + '.')
		$('#frmDataIC #count').focus();
		return false;	
	}
	
	if ($.trim($('#frmDataIC #observation').val()) == "") {
		errorCancelItemOrderEdit('Debe ingresar el motivo de la cancelación.')
		$('#frmDataIC #observation').focus();
		return false;	
	}

	return true;
}

function errorCancelItemOrderEdit(message) {	
	if (message == null) message = '';

	$('#errorCancelItem').html(message);
	if ($.trim(message) == "") {
		if (!$('#errorCancelItem').hasClass('hide')) {
			$('#errorCancelItem').addClass('hide');
		}		
	} else {
		if ($('#errorCancelItem').hasClass('hide')) {
			$('#errorCancelItem').removeClass('hide');
		}
	}	
}

function saveOkCancelItemOrderEdit(orderId) {
	if (orderId == null) orderId = 0;
	if (orderId > 0) {
		location.href = $('#baseUrl').val()+'orders/edit/'+orderId;		
	}
}

function seeBudgetsOrderEdit(orderId) {
	if (orderId == null) orderId = -1;

	if (orderId > 0) {
		modalMessage('budgets/byOrder/'+orderId,"Presupuestos del Pedido Nº "+orderId,null,null,null,null,'closeModalMessage()',true,null,null,800);		
	}
}

function seeBudgetOrderEdit(budgetId) {	
	if (budgetId == null) budgetId = -1;

	if (budgetId > 0) {
		var url = $('#baseUrl').val()+'budgets/edit/'+budgetId;		

		openNewTab(url);
	}
}

function newBudgetOrderEdit(orderId) {
	if (orderId == null) orderId = -1;

	if (orderId > 0) {
		location.href = $('#baseUrl').val()+'budgets/edit/0/'+orderId;		
	}
}

function seeBudgetsItemOrderEdit(row) {
	if (row == null) row = -1;

	if (row > 0) {
		var detailOrderId = $('#detailId'+row).val();
		if (detailOrderId > 0) {
			var article = "Cód. " + $('#detailCode'+row).val() + " / " + $('#detailDescription'+row).val();
			modalMessage('budgets/byDetailOrder/'+detailOrderId,"Presupuestos asociados al artículo: " + article,null,null,null,null,'closeModalMessage()',true,null,null,600);		
		}
	}
}

function sendItemBudgetSelectedOrderEdit() {
	var budgetItemSelected = $("input[name=budgetItemSelected]:checked").val();
	if (budgetItemSelected == null) budgetItemSelected = 0;

	errorItemBudgetSelectOrderEdit();
	if (budgetItemSelected <= 0) {
		errorItemBudgetSelectOrderEdit('Debe seleccionar uno de los presupuestos.');
		return;
	}

	var url = $('#baseUrl').val()+'budgets/saveSelectBudgetItem/' + budgetItemSelected;

	loadDataByAjax(url,'bodyModal','');			
}

function errorItemBudgetSelectOrderEdit(message) {	
	if (message == null) message = '';

	$('#errorItemBudgetSelect').html(message);
	if ($.trim(message) == "") {
		if (!$('#errorItemBudgetSelect').hasClass('hide')) {
			$('#errorItemBudgetSelect').addClass('hide');
		}		
	} else {
		if ($('#errorItemBudgetSelect').hasClass('hide')) {
			$('#errorItemBudgetSelect').removeClass('hide');
		}
	}	
}

function selectItemBudgetOrderEdit(budgetItemId) {
	$('#grBudgetsItem .itemBudget').removeClass("selBudgetItem");
	$('#grBudgetsItem #itemBudget'+budgetItemId).addClass("selBudgetItem");
}

function seeSummaryBudgetsOrderEdit(orderId) {
	if (orderId == null) orderId = -1;

	if (orderId > 0) {		
		var url = $('#baseUrl').val()+'budgets/orderSummary/'+orderId;		
		openNewTab(url);
	}
}