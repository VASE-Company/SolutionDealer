function initializeBills(error) {	
	if (error == null) error = "";

	if (error != "") {
		alert(error);
	}
}

function initializeBillEdit() {	
	initializeCharactersRemaining('observation');	
	calculateTotalBillEdit();	
	loadComboWarehousesBillEdit();
	detectKeySupplierCodeBillEdit();	
	detectKeyDetailBillEdit();			
}


function calculateTotalBillEdit() {			
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
			rowTotal = (parseFloat($('#detailUnitPrice'+row).val()) * parseFloat($('#detailQuantity'+row).val())).toFixed(2);					
			
			$('#detailTotal'+row).val(roundValue(rowTotal,2));

			detailTotal = detailTotal + parseFloat($('#detailTotal'+row).val());
		}
	}
				
	$('#total').val(roundValue(detailTotal,2));		
}

function loadComboWarehousesBillEdit() {
	var selValue = 0;
	if ($('#warehouseId').attr('selValue') != null) selValue = $('#warehouseId').attr('selValue');
	$('#warehouseId').html('<option value="">Cargando...</option>');	
	var url = $('#baseUrl').val()+'warehouses/warehousesCombo/'+$('#companyId').val()+"/"+selValue;				
	loadDataByAjax(url,'warehouseId',null,null,true);			    						
}

function selectCompanyBillEdit() {	
	$('#warehouseId').attr('selValue',"0");	
	loadComboWarehousesBillEdit();
}


function preSaveBill() {
	if (!detailsValidBillEdit()) {
			return false;
	}

	preSubmit();
}

// ******* FINDER SUPPLIERS *******

function openSuppliersFinderBillEdit() {		
	var url = 'bills/suppliersFinder/';

	modalMessage(url,"Buscador de Proveedores",null,null,null,null,'closeModalMessage()',true,"initializeFinderBillEdit()",null,600);	
}

function acceptSuppliersFinderBillEdit(finderRow) {	
	if (finderRow == null) finderRow = 0;

	if (parseInt(finderRow) > 0) {
		$('#frmData #supplierCode').val($('#bodyModal #sfCode'+finderRow).val());
				
		searchSupplierBillEdit();

		closeModalMessage();					
	} else {
		closeModalMessage();
	}
}

function searchSupplierBillEdit() {	
	if ($("#frmData #supplierCode").val() != $("#frmData #supplierCodeOriginal").val()) {
		//Change the code of supplier
		var code = $.trim($("#frmData #supplierCode").val());

		loadSupplierBillEdit(code);			
	}
}

function loadSupplierBillEdit(code) {
	if (code != "") {	 		
 		$("#frmSearchAux #code").val(code);
 		$("#frmSearchAux #resultSearch").html("");

 		loadDataByAjax($('#baseUrl').val()+'bills/searchSupplier','resultSearch','terminateLoadSupplierBillEdit()','frmSearchAux');		 		
		
	} else {	
		$("#frmData #supplierCode").val("");
		$("#frmData #supplierCodeOriginal").val("");					
		$("#frmData #supplierCode").focus();
	}
}

function terminateLoadSupplierBillEdit() {	
	if ($("#frmSearchAux #supplierCode").length > 0) {
		$("#frmData #supplierId").val($("#frmSearchAux #supplierId").val());	
		$("#frmData #supplierCode").val($("#frmSearchAux #supplierCode").val());	
		$("#frmData #supplierDescription").val($("#frmSearchAux #supplierNameTrade").val());		
		$("#frmData #supplierCodeOriginal").val($("#frmSearchAux #supplierCode").val());					
	} else {
		loadSupplierBillEdit("");
	}		
}


// ******* END FINDER SUPPLIERS *******

// ******* FINDER ARTICLES *******

function openArticlesFinderBillEdit(row) {	
	if (row == null) row = 0;

	if (row > 0 && row <= parseInt($('#detailsCount').val())) {
		$('#rowArticlesFinder').val(row);	

		var url = 'bills/articlesFinder/';

		if ($.trim($("#detailCode"+row).val()) != "" && $("#detailCode"+row).val() != $("#detailOriginalCode"+row).val()) {
			url = url + "1?text=" + $.trim($("#detailCode"+row).val());
		} else {
			url = url + "0";
		}

		modalMessage(url,"Buscador de Artículos",null,null,null,null,'closeModalMessage()',true,"initializeFinderBillEdit()",null,800);
	}
}

function acceptArticlesFinderBillEdit(finderRow) {	
	if (finderRow == null) finderRow = 0;

	if (parseInt(finderRow) > 0) {
		var detailRow = $('#rowArticlesFinder').val();
		
		$('#frmData #detailCode'+detailRow).val($('#bodyModal #afCode'+finderRow).val());
				
		searchArticleBillEdit(detailRow);

		closeModalMessage();
		
		if ($('#frmData #detailCode'+detailRow).val() != "") {
			$("#frmData #detailUnitPrice"+detailRow).focus();					
		}				
	} else {
		closeModalMessage();
	}
}


// ******* END FINDER ARTICLES *******

// ******* FINDER GENERAL *******

function initializeFinderBillEdit() {
	initializeKeyDetection();
	$('#frmFinderFilter #textFilter').focus();	
}

function reloadFinderBillEdit(url,frm) {	
	if (url == null) url = "";

	if (url != "") {
		loadDataByAjax(url,'bodyModal','initializeFinderBillEdit()',frm);			    							
	}		
}

function searchFinderBillEdit(type,clear) {	
	if (type == null) type = '';
	if (clear == null) clear = false;

	if (type != '') {
		if (clear) clearFilter('frmFinderFilter',false);

		var url = $('#baseUrl').val()+'bills/'+type+'Finder/';
		
		reloadFinderBillEdit(url,'frmFinderFilter');
	}
}

// ******* END FINDER GENERAL *******

function searchArticleBillEdit(row) {	
	if (row == null) row = 0;

	if (row > 0 && row <= parseInt($('#detailsCount').val())) {
		if ($("#detailCode"+row).val() != $("#detailOriginalCode"+row).val()) {
			//Change the code of article
			var code = $.trim($("#detailCode"+row).val());

			loadArticleBillEdit(row,code);			
		}
	} 
}

function loadArticleBillEdit(row,code) {
	if (row == null) row = 0;

	if (row > 0 && row <= parseInt($('#detailsCount').val())) {
		if (code != "") {	 		
			for (var i=1; i <= parseInt($('#detailsCount').val()); i++)	{
				if (i != row && code == $("#detailCode"+i).val()) {
					alert('El artículo selecciondo ya está ingresado en la factura.');
					code = "";
				}
			}
		}

	 	if (code != "") {	 		
	 		$("#frmSearchAux #code").val(code);
	 		$("#frmSearchAux #resultSearch").html("");

	 		loadDataByAjax($('#baseUrl').val()+'bills/searchArticle','resultSearch','terminateLoadArticleBillEdit('+row+')','frmSearchAux');		 		
			
		} else {	
			$("#detailCode"+row).val("");
			$("#detailOriginalCode"+row).val("");			
			clearRowDetailBillEdit(row);	
			calculateTotalBillEdit();			
			$("#detailCode"+row).focus();
		}
	} 
}

function terminateLoadArticleBillEdit(row) {	
	if (row == null) row = 0;

	if (row > 0 && row <= parseInt($('#detailsCount').val())) {
		if ($("#frmSearchAux #codeArticle").length > 0) {
			$("#detailCode"+row).val($("#frmSearchAux #codeArticle").val());	
			$("#detailDescription"+row).val($("#frmSearchAux #descriptionArticle").val());								
			$("#detailUnitPrice"+row).val($("#frmSearchAux #unitPriceArticle").val());
			$("#detailQuantity"+row).val($("#frmSearchAux #quantityArticle").val());			
			$("#detailFamily"+row).val($("#frmSearchAux #familyArticle").val());			
			$("#detailTotal"+row).val($("#frmSearchAux #totalArticle").val());			
			$("#detailArticleId"+row).val($("#frmSearchAux #articleId").val());
			$("#detailOriginalCode"+row).val($("#frmSearchAux #codeArticle").val());					

			calculateTotalBillEdit();
		} else {
			loadArticleBillEdit(row,"");
		}		
	}
}

function clearRowDetailBillEdit(row) {	
	if (row == null) row = 0;

	if (row > 0 && row <= parseInt($('#detailsCount').val())) {
		$("#detailDescription"+row).val('');		
		$("#detailUnitPrice"+row).val('0.00');
		$("#detailQuantity"+row).val('1');	
		$("#detailFamily"+row).val('');
		$("#detailTotal"+row).val('0.00');					
		$("#detailArticleId"+row).val('0');						
	} 
}

function deleteRowDetailBillEdit(row,confirmation) {
	if (confirmation == null) confirmation = true;

	if (confirmation) {
		if (!confirm('Realmente desea eliminar este registro?')) {
			return;	
		}
	}

	$('#grDetails #'+row).remove();
	$('#details #detail'+row).remove();
}

function newRowDetailBillEdit() {	
	var row = parseInt($('#detailsCount').val()) + 1;
	
	var rowHtml = "";
	rowHtml = rowHtml + '<tr id="'+row+'">';
	rowHtml = rowHtml + '<td class="text-center without-padding">';
	rowHtml = rowHtml + '<button type="button" class="btn without-padding" onclick="deleteRowDetailBillEdit('+row+')" title="eliminar"><i class="far fa-minus-square"></i></button>';  
	rowHtml = rowHtml + '</td>';
	rowHtml = rowHtml + '<td class="without-padding"><input type="text" id="detailCode'+row+'" name="detailCode'+row+'" class="form-control form-control-sm detailColumn" maxlength="25" autocomplete="off" value="" onblur="searchArticleBillEdit('+row+')"></td>';
	rowHtml = rowHtml + '<td class="without-padding"><input type="text" id="detailDescription'+row+'" name="detailDescription'+row+'" class="form-control form-control-sm detailColumn" maxlength="100" autocomplete="off" value="" readonly="readonly"></td>';	
	rowHtml = rowHtml + '<td class="without-padding"><input type="number" min="0.01" step="0.01" id="detailUnitPrice'+row+'" name="detailUnitPrice'+row+'" class="form-control form-control-sm detailColumn text-right" maxlength="12" autocomplete="off" value="0.00" onchange="calculateTotalBillEdit();" onfocus="this.select();"></td>';	
	rowHtml = rowHtml + '<td class="without-padding"><input type="number" min="1" step="1" id="detailQuantity'+row+'" name="detailQuantity'+row+'" class="form-control form-control-sm detailColumn text-right" maxlength="3" autocomplete="off" value="1" onchange="calculateTotalBillEdit()" onfocus="this.select();"></td>';
	rowHtml = rowHtml + '<td class="without-padding"><input type="text" id="detailFamily'+row+'" name="detailFamily'+row+'" class="form-control form-control-sm detailColumn" maxlength="50" autocomplete="off" value="" readonly="readonly"></td>';
	rowHtml = rowHtml + '<td class="without-padding"><input type="number" min="0" step="0.01" id="detailTotal'+row+'" name="detailTotal'+row+'" class="form-control form-control-sm detailColumn text-right" maxlength="12" autocomplete="off" value="0.00" readonly="readonly"></td>';		
	rowHtml = rowHtml + '</tr>';

	$('#grDetails tbody').append(rowHtml);	 
	$('#detailsCount').val(row);                                                                                                                                                                                                                                                                                                                                                                                                                            
                                
	var divHtml = "";
	divHtml = divHtml + '<div id="detail'+row+'">';
	divHtml = divHtml + '<input type="hidden" id="detailId'+row+'" name="detailId'+row+'" value="0">';
	divHtml = divHtml + '<input type="hidden" id="detailArticleId'+row+'" name="detailArticleId'+row+'" value="0">';
	divHtml = divHtml + '<input type="hidden" id="detailOriginalCode'+row+'" name="detailOriginalCode'+row+'" value="">';		
	divHtml = divHtml + '</div>';
	$('#details').append(divHtml);	 

	detectKeyDetailBillEdit();

	$('#detailCode'+row).focus();
}

function isRowEmptyDetailBillEdit(row) {
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

function detectKeyDetailBillEdit() {	
	$('.detailColumn').keydown( function(e) {
        var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
        if(key == 13) {
            e.preventDefault();
            var inputs = $(this).closest('form').find(':input:visible');
            inputs.eq(inputs.index(this)+ 1).focus();

            var lastRow = lastRowDetailBillEdit();
            var row = $(this).parent().parent().attr("id");
            if ($(this).attr("id") == "detailQuantity"+lastRow && $('#btnNewRow') != null && $('#btnNewRow').length > 0 && $.trim($("#detailCode"+row).val()) != "") {
        		newRowDetailBillEdit(row);
        	}
        }
        if(key == 113) {
        	var row = $(this).parent().parent().attr("id");
        	if ($(this).attr("id") == "detailCode"+row) {
        		openArticlesFinderBillEdit(row);
        	}
        }
    });    
}

function lastRowDetailBillEdit() {
	var detailsCount = parseInt($('#detailsCount').val());

	for (var row=detailsCount; row >= 1; row--) {
		if ($('#grDetails #'+row).length > 0) {
			return row;		
		}
	}

	return 0;
}

function detailsValidBillEdit() {
	
	var detailsCount = parseInt($('#detailsCount').val());
	var existsDetailValid = false;	
	
	for (var row=1; row <= detailsCount; row++) {
		if ($('#grDetails #'+row).length > 0) {			
			if ($.trim($("#detailDescription"+row).val()) == "") {
				if ($.trim($("#detailCode"+row).val()) == "") {
					deleteRowDetailBillEdit(row,false);
				} else {
					alert("Debe completar la descripción del insumo");
					$("#detailDescription"+row).focus();
					return false;
				}				
			} else {
				existsDetailValid = true;
			}		
			if (parseFloat($.trim($("#detailUnitPrice"+row).val())) <= 0)  {
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

function detectKeySupplierCodeBillEdit() {	
	$('#frmData #supplierCode').keydown( function(e) {
        var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
        if(key == 13) {
            e.preventDefault();
           
            searchSupplierBillEdit();
        }
        if(key == 113) {
        	openSuppliersFinderBillEdit();
        }
    });    
}

function seeBillItemData(row) {
	if (row == null) row = -1;

	if (row > 0) {
		var detailOrderId = $('#detailId'+row).val();
		var article = "Cód. " + $('#detailCode'+row).val() + " / " + $('#detailDescription'+row).val();
		modalMessage('bills/billItemData/'+detailOrderId,"Artículo: " + article,null,null,null,null,'closeModalMessage()',true,null,null,600);		
	}
}