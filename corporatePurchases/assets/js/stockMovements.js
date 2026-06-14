function initializeStockMovement() {	
	detectKeyFilterStockMovement();
}


function initializeEditStockMovement() {
	loadComboWarehousesStockMovementEdit();
	detectKeyDetailStockMovementEdit();
	selectTypeStockMovementEdit();
}

function preSaveStockMovement() {
	if (!detailsValidStockMovementEdit()) {
		return false;
	}

	preSubmit();

	return true;
}

function selectCompanyStockMovementEdit() {	
	$('#warehouseId').attr('selValue',"0");	
	loadComboWarehousesStockMovementEdit();
}

function loadComboWarehousesStockMovementEdit() {
	var selValue = 0;
	if ($('#warehouseId').attr('selValue') != null) selValue = $('#warehouseId').attr('selValue');
	$('#warehouseId').html('<option value="">Cargando...</option>');	
	var url = $('#baseUrl').val()+'warehouses/warehousesCombo/'+$('#companyId').val()+"/"+selValue;				
	loadDataByAjax(url,'warehouseId',null,null,true);			    						
}

function seeStockOfStockMovementEdit(row) {
	if (row == null) row = 0;

	if (row > 0 && row <= parseInt($('#detailsCount').val())) {
		if (parseFloat($("#detailArticleId"+row).val()) <= 0) {
			alert('Debe cargar un artículo en la linea para poder ver su stock.');
		} else {
			seeStockByArticle($("#detailArticleId"+row).val());
		}
	}
}

function seeStockMovementData(stockMovementId,article) {
	if (stockMovementId == null) stockMovementId = -1;
	if (article == null) article = '';

	if (stockMovementId > 0) {		
		modalMessage('stockMovements/stockMovementData/'+stockMovementId,"Artículo: " + article,null,null,null,null,'closeModalMessage()',true,null,null,600);		
	}
}

function selectTypeStockMovementEdit() {	 
	if (isInputStockMovementEdit()) {		
		$('.colUnitPrice').removeClass("hide");
		$('.inputUnitPrice').attr("disabled",false);		
	} else {		
		$('.colUnitPrice').addClass("hide");
		$('.inputUnitPrice').attr("disabled",true);
	}	
}

function isInputStockMovementEdit() {	 
	return (parseInt($("#typeId option:selected").attr("isInput")) == 1);
}

// ******* FINDER ARTICLES *******

function openArticlesFinderStockMovementEdit(row) {	
	if (row == null) row = 0;

	if (row > 0 && row <= parseInt($('#detailsCount').val())) {
		$('#rowArticlesFinder').val(row);	

		var url = 'stockMovements/articlesFinder/';

		if ($.trim($("#detailCode"+row).val()) != "" && $("#detailCode"+row).val() != $("#detailOriginalCode"+row).val()) {
			url = url + "1?text=" + $.trim($("#detailCode"+row).val());
		} else {
			url = url + "0";
		}

		modalMessage(url,"Buscador de Artículos",null,null,null,null,'closeModalMessage()',true,"initializeFinderStockMovementEdit()",null,900);
	}
}

function acceptArticlesFinderStockMovementEdit(finderRow) {	
	if (finderRow == null) finderRow = 0;

	if (parseInt(finderRow) > 0) {
		var detailRow = $('#rowArticlesFinder').val();
		
		$('#frmData #detailCode'+detailRow).val($('#bodyModal #afCode'+finderRow).val());
				
		searchArticleStockMovementEdit(detailRow);

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

function initializeFinderStockMovementEdit() {
	initializeKeyDetection();
	$('#frmFinderFilter #textFilter').focus();	
}

function reloadFinderStockMovementEdit(url,frm) {	
	if (url == null) url = "";

	if (url != "") {
		loadDataByAjax(url,'bodyModal','initializeFinderStockMovementEdit()',frm);			    							
	}		
}

function searchFinderStockMovementEdit(type,clear) {	
	if (type == null) type = '';
	if (clear == null) clear = false;

	if (type != '') {
		if (clear) clearFilter('frmFinderFilter',false);

		var url = $('#baseUrl').val()+'stockMovements/'+type+'Finder/';
		
		reloadFinderStockMovementEdit(url,'frmFinderFilter');
	}
}

// ******* END FINDER GENERAL *******

function searchArticleStockMovementEdit(row) {	
	if (row == null) row = 0;

	if (row > 0 && row <= parseInt($('#detailsCount').val())) {
		if ($("#detailCode"+row).val() != $("#detailOriginalCode"+row).val()) {
			//Change the code of article
			var code = $.trim($("#detailCode"+row).val());
			var existsCode = false;

			var detailsCount = parseInt($('#detailsCount').val());			
			
			for (var auxRow=1; auxRow <= detailsCount && !existsCode; auxRow++) {
				if ($('#grDetails #'+auxRow).length > 0 && auxRow != row) {					
					if ($.trim($("#detailCode"+auxRow).val()) == code) {
						existsCode = true;
					}
				}
			}

			if (existsCode) {
				alert('El código ya está ingresado en otra línea.');

				$("#detailCode"+row).val("");
				$("#detailOriginalCode"+row).val("");			
				clearRowDetailStockMovementEdit(row);	
				$('#detailCode'+row).focus();
			} else {
				loadArticleStockMovementEdit(row,code);						
			}
		}
	} 
}

function loadArticleStockMovementEdit(row,code) {
	if (row == null) row = 0;

	if (row > 0 && row <= parseInt($('#detailsCount').val())) {
	 	if (code != "") {	 		
	 		$("#frmSearchArticle #code").val(code);
	 		$("#frmSearchArticle #resultSearchArticle").html("");

	 		loadDataByAjax($('#baseUrl').val()+'stockMovements/searchArticle','resultSearchArticle','terminateLoadArticleStockMovementEdit('+row+')','frmSearchArticle');		 		
			
		} else {	
			$("#detailCode"+row).val("");
			$("#detailOriginalCode"+row).val("");			
			clearRowDetailStockMovementEdit(row);					
		}
	} 
}

function terminateLoadArticleStockMovementEdit(row) {	
	if (row == null) row = 0;

	if (row > 0 && row <= parseInt($('#detailsCount').val())) {
		if ($("#frmSearchArticle #codeArticle").length > 0) {
			$("#detailCode"+row).val($("#frmSearchArticle #codeArticle").val());	
			$("#detailDescription"+row).val($("#frmSearchArticle #descriptionArticle").val());											
			$("#detailQuantity"+row).val($("#frmSearchArticle #quantityArticle").val());			
			$("#detailUnitPrice"+row).val($("#frmSearchArticle #unitPriceArticle").val());	
			$("#detailFamily"+row).val($("#frmSearchArticle #familyArticle").val());						
			$("#detailArticleId"+row).val($("#frmSearchArticle #articleId").val());
			$("#detailOriginalCode"+row).val($("#frmSearchArticle #codeArticle").val());								
		} else {
			loadArticleStockMovementEdit(row,"");
		}		
	}
}

function clearRowDetailStockMovementEdit(row) {	
	if (row == null) row = 0;

	if (row > 0 && row <= parseInt($('#detailsCount').val())) {
		$("#detailDescription"+row).val('');				
		$("#detailQuantity"+row).val('1');	
		$("#detailUnitPrice"+row).val('0.00');						
		$("#detailFamily"+row).val('');		
		$("#detailArticleId"+row).val('0');						
	} 
}

function deleteRowDetailStockMovementEdit(row,confirmation) {
	if (confirmation == null) confirmation = true;

	if (confirmation) {
		if (!confirm('Realmente desea eliminar este registro?')) {
			return;	
		}
	}

	$('#grDetails #'+row).remove();
	$('#details #detail'+row).remove();
}

function newRowDetailStockMovementEdit() {	
	var row = parseInt($('#detailsCount').val()) + 1;
	var classColumnUnitPrice="";
	var disabledColumnUnitPrice="";
	if (!isInputStockMovementEdit()) {
		classColumnUnitPrice="hide";
		disabledColumnUnitPrice=' disabled="disabled" ';
	}
	
	var rowHtml = "";
	rowHtml = rowHtml + '<tr id="'+row+'">';
	rowHtml = rowHtml + '<td class="text-center without-padding">';
	rowHtml = rowHtml + '<button type="button" class="btn without-padding" onclick="deleteRowDetailStockMovementEdit('+row+')" title="eliminar"><i class="far fa-minus-square"></i></button>';  
	rowHtml = rowHtml + '</td>';
	rowHtml = rowHtml + '<td class="without-padding"><input type="text" id="detailCode'+row+'" name="detailCode'+row+'" class="form-control form-control-sm detailColumn" maxlength="25" autocomplete="off" value="" onblur="searchArticleStockMovementEdit('+row+')"></td>';
	rowHtml = rowHtml + '<td class="without-padding"><input type="text" id="detailDescription'+row+'" name="detailDescription'+row+'" class="form-control form-control-sm detailColumn" maxlength="100" autocomplete="off" value="" readonly="readonly"></td>';
	rowHtml = rowHtml + '<td class="without-padding"><input type="text" id="detailFamily'+row+'" name="detailFamily'+row+'" class="form-control form-control-sm detailColumn" maxlength="50" autocomplete="off" value="" readonly="readonly"></td>';	
	rowHtml = rowHtml + '<td class="without-padding"><input type="number" min="0" step="1" id="detailQuantity'+row+'" name="detailQuantity'+row+'" class="form-control form-control-sm detailColumn text-right" maxlength="3" autocomplete="off" value="1" onfocus="this.select();"></td>';	
	rowHtml = rowHtml + '<td class="without-padding colUnitPrice '+classColumnUnitPrice+'"><input type="number" min="0.01" step="0.01" id="detailUnitPrice'+row+'" name="detailUnitPrice'+row+'" class="form-control form-control-sm detailColumn text-right inputUnitPrice" maxlength="12" autocomplete="off" value="0.00" onfocus="this.select();" '+disabledColumnUnitPrice+'></td>';
	rowHtml = rowHtml + '<td class="without-padding text-center"><button type="button" class="btn without-padding" onclick="seeStockOfStockMovementEdit('+row+')" title="ver stock"><i class="fa fa-chart-bar"></i></button></td>';
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

	detectKeyDetailStockMovementEdit();

	$('#detailCode'+row).focus();
}

function isRowEmptyDetailStockMovementEdit(row) {
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

function detectKeyDetailStockMovementEdit() {
	$('.detailColumn').keydown( function(e) {
        var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
        if(key == 13) {
            e.preventDefault();
            var inputs = $(this).closest('form').find(':input:visible');
            inputs.eq(inputs.index(this)+ 1).focus();

            var lastRow = lastRowDetailStockMovementEdit();
            var row = $(this).parent().parent().attr("id");

            var lastField = "detailQuantity";
            if (isInputStockMovementEdit()) lastField = "detailUnitPrice";

            if ($(this).attr("id") == lastField+lastRow && $('#btnNewRow') != null && $('#btnNewRow').length > 0 && $.trim($("#detailCode"+row).val()) != "") {
        		newRowDetailStockMovementEdit(row);
        	}
        }
        if(key == 113) {
        	var row = $(this).parent().parent().attr("id");
        	if ($(this).attr("id") == "detailCode"+row) {
        		openArticlesFinderStockMovementEdit(row);
        	}
        }
    });
}

function lastRowDetailStockMovementEdit() {
	var detailsCount = parseInt($('#detailsCount').val());

	for (var row=detailsCount; row >= 1; row--) {
		if ($('#grDetails #'+row).length > 0) {
			return row;		
		}
	}

	return 0;
}

function detailsValidStockMovementEdit() {
	var detailsCount = parseInt($('#detailsCount').val());
	var existsDetailValid = false;	
	
	for (var row=1; row <= detailsCount; row++) {
		if ($('#grDetails #'+row).length > 0) {
			if ($.trim($("#detailDescription"+row).val()) == "") {
				if ($.trim($("#detailCode"+row).val()) == "") {
					deleteRowDetailStockMovementEdit(row,false);
				} else {
					alert("Debe completar la descripción del artículo");
					$("#detailDescription"+row).focus();
					return false;
				}				
			} else {
				existsDetailValid = true;
			}		
		}
	}

	if (!existsDetailValid) {
		alert("Debe ingresar al menos un artículo.");

		return false;
	}

	return true;
}

// ******* FINDER FILTER ARTICLES *******


function detectKeyFilterStockMovement() {
	$('#articleFilter').keydown( function(e) {
        var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;       
        if(key == 113) {
        	openArticlesFinderFilterStockMovement();        	
        }
    });
}

function openArticlesFinderFilterStockMovement() {		
	var url = 'stockMovements/articlesFinder/';

	url = url + "1?from=filter&text=" + $.trim($("#articleFilter").val());

	modalMessage(url,"Buscador de Artículos",null,null,null,null,'closeModalMessage()',true,"initializeFinderStockMovementEdit()",null,600);
}

function acceptArticlesFinderFilterStockMovement(finderRow) {	
	if (finderRow == null) finderRow = 0;

	if (parseInt(finderRow) > 0) {				
		$('#frmFilter #articleFilter').val($('#bodyModal #afCode'+finderRow).val());

		closeModalMessage();
	} else {
		closeModalMessage();
	}
}


// ******* END FINDER ARTICLES *******
