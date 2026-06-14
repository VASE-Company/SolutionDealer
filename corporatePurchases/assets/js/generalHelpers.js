var generalTimer = null;

function initializeGeneral() {
	initializeHTML5IE(); 	
	initializeGeneralTimer();
	initializeKeyDetection();
}

function initializeHTML5IE() {
	webshims.setOptions('waitReady', false);
	webshims.setOptions('forms-ext', {types: 'date'});
	webshims.polyfill('forms forms-ext');
}

function initializeGeneralTimer() {
	if ($('#autoReload').length > 0 && parseInt($('#autoReload').val()) == 1) {
		initializeTimer('reloadPage()',60);
	} else{
		initializeTimer('keepAlive()',60);
	}
}

function initializeTimer(timerFunction,seconds){
	if (timerFunction == null) timerFunction = "";
	if (timerFunction == "") return;

	if (seconds == null) seconds = 60;

	var miliseconds = 1000;
	
	if (generalTimer != null) {
		clearInterval(generalTimer);
	}
	generalTimer = setInterval(timerFunction,seconds*miliseconds);
}

function finalizeTimer(){
	clearInterval(generalTimer);
}

function initializeKeyDetection() {
	$('.noEnterMyApp').keydown( function(e) {
        var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
        if(key == 13) {
            e.preventDefault();            
        }        
    });
}

function keepAlive(){
	if ($('#divNotifications') != null) { //Esto sólo para este sistema, para que recargue las notificaciones
		updateNotifications();
	} else {
		loadDataByAjax($('#baseUrl').val()+'main/keepAlive','divKeepAlive');
	}
}

function updateNotifications() {
	loadDataByAjax($('#baseUrl').val()+'main/updateNotifications','divNotifications',null,true,'none');	
}

function reloadPage() {
	location.reload();
}

function modalMessage(url,title,desBtnOne,funBtnOne,desBtnTwo,funBtnTwo,funBtnClose,funBtnCloseVisible,callback,bodyData,width){
	$('#headerModal').html('');
	$('#bodyModal').html('');
	$('#footerModal').html('');
	
	if (title == "") title = null;
	if (desBtnOne == "") desBtnOne = null;
	if (funBtnOne == "") funBtnOne = null;
	if (desBtnTwo == "") desBtnTwo = null;	
	if (funBtnTwo == "") funBtnTwo = null;
	if (funBtnClose == "") funBtnClose = null
	if (funBtnCloseVisible == null) funBtnCloseVisible = true;
	if (callback == null) callback = "";
	if (bodyData == null) bodyData = "";
	if (width == null) width = 0;
	
	if (title != null) {
		$('#headerModal').html(title);			
	}
	if (funBtnClose != null) {
		$('#headerModalFull #btnClose').attr('onclick',funBtnClose);
	} else {
		$('#headerModalFull #btnClose').attr('onclick','');
		$('#headerModalFull #btnClose').removeAttr('onclick');
	}
	if (title != null || (funBtnClose != null && funBtnCloseVisible)) {		
		$('#headerModalFull').removeClass("hide");
		if (funBtnClose != null && funBtnCloseVisible) {
			$('#headerModalFull #btnClose').removeClass("hide");
		} else {
			$('#headerModalFull #btnClose').addClass("hide");
		}				
	} else {
		$('#headerModalFull').addClass("hide");
	}
	if (width > 0) {
		$('#myModalDialog').css('max-width',width+"px");
	} else {		
		$('#myModalDialog').css('width',"");
	}	
	
	var footer = '';	
	if (desBtnOne != null && funBtnOne != null) {
		footer = footer + '<button type="button" class="btn btn-default btn-sm" onClick="'+funBtnOne+'" id="btnOneModal">'+desBtnOne+'</button>';
	}
	if (desBtnTwo != null && funBtnTwo != null) {
		footer = footer + '<button type="button" class="btn btn-default btn-sm" onClick="'+funBtnTwo+'" id="btnTwoModal">'+desBtnTwo+'</button>';
	}	
	if (footer != "") {
		$('#footerModal').html(footer);
		$('#footerModal').removeClass("hide");
	} else {
		$('#footerModal').addClass("hide");
	}			
	
	if (callback != "") {
		callback = callback+";$('#myModal').modal('show')";
	} else {
		callback = "$('#myModal').modal('show')";
	}
	if (url != null && url != "") {
		loadDataByAjax($('#baseUrl').val()+url,'bodyModal',callback);	
	} else {
		if (bodyData != "") {
			$('#bodyModal').html(bodyData);
		}		
		eval(callback);
	}
}

function closeModalMessage() {		
	$('#bodyModal').html("");
	$('#myModal').modal('hide');
}

function selectRow(gridId,rowId){
	$('#'+gridId+' tbody tr').removeClass('active');
	$('#'+gridId+' #'+rowId).addClass('active');			
}

function selectFirstRow(gridId){
	$("#"+gridId+" tbody tr:first").click();
}

function inicializeComboFilter(comboId) {
	$('#'+comboId).multiselect({
    	includeSelectAllOption: true,
	  	selectAllText:'[Todos]',
		selectAllValue:'all',
		nonSelectedText:'[Ninguno]',
		nSelectedText:'Seleccionado(s)',
		selectedClass: null,		
		buttonClass: 'form-control form-control-sm text-left',
		buttonWidth: '100%',
	  	buttonText: function(options, select) {
	        let selectedOptions = '';

	        if (options.length === 0) {
	            selectedOptions = '[Ninguno]';
	        } else if (options.length === $(select).find('option').length) {
	            selectedOptions = '[Todos]';
	        } else {
	            selectedOptions = options.length + ' seleccionados';
	        }

	        let title = $(select).attr('title') || '';
	        if (title !== '') {
	            title = '<strong>' + title + ':</strong> ';
	        }

	        return title + selectedOptions;
	    }
	});
}

function getValueComboFilter(comboId) {
	var arr = $('#'+comboId).val();
	var ids = "";
	
	if (arr != null) {
		if ($('#'+comboId+' option').length == arr.length) {				
			ids = 'all';
		} else {
			ids = $('#'+comboId).val().join('|');			
			if (ids == '') ids = "-1";
		}

	} else {
		ids = "-1";
	}
	
	$('#'+comboId+'Selected').val(ids);	
}

function transferValue(originId,destinationId) {
	if (originId == null) originId = '';
	if (destinationId == null) destinationId = '';

	if (originId != "" && destinationId != "") {
		$('#'+destinationId).val($('#'+originId).val());	
	}		
}


function selectCombo(comboId,value){
	if (comboId == null) comboId = '';
	
	if (comboId != "") {
		var valueFound = false;
		
		var valueSearch = $.trim(value);
		valueSearch = valueSearch.toLowerCase();	
		$('#'+comboId+' option').each(function(){
			var valueCombo = $(this).val().toLowerCase();
			valueCombo = $.trim(valueCombo);
						
			if(valueSearch == valueCombo){ 
				$(this).attr("selected", "selected");
				valueFound = true;				
			} else {
				if ($(this).attr("selected")) {
					$(this).removeAttr("selected");
				}
			}			
		});
		
		if (valueFound) {
			$('#'+comboId).attr("valueSelected",value);
		} else {
			$('#'+comboId).removeAttr("valueSelected");
		}		
	}
}

function enter(e,funcion){  	
	tecla=(document.all) ? e.keyCode : e.which; 
	if(tecla==13){		
		eval(funcion);
	}
}

function getAge(birthdate) {
	if (birthdate != "") {
		var currentDate = new Date()
		var currentDateDay = currentDate.getDate();
		var currentDateMonth = currentDate.getMonth() + 1;
		var currentDateYear = currentDate.getFullYear();
	
		var birthdateArr = birthdate.split("-");
		var birthdateDay = birthdateArr[2];
		var birthdateMonth = birthdateArr[1];
		var birthdateYear = birthdateArr[0];
	
		if (birthdateMonth.substr(0,1) == 0) {
			birthdateMonth= birthdateMonth.substring(1, 2);
		}	
		if (birthdateDay.substr(0, 1) == 0) {
			birthdateDay = birthdateDay.substring(1, 2);
		}
	
		var age = currentDateYear - birthdateYear;

		if ((currentDateMonth < birthdateMonth) || (currentDateMonth == birthdateMonth && currentDateDay < birthdateDay)) {
			age--;
		}
		
		return age;
	} else {
		return "";
	}		
};

function showCharactersRemaining(obj) {
	if (obj != null) {
		var id = $(obj).attr('id');
		if (id != "") {
			var maxLenght = $(obj).attr('maxlength');					
			maxLenght = parseInt(maxLenght);
			
			if (maxLenght > 0) {
				var textLenght = $('#'+id).val().length;
				var charactersRemaining = maxLenght - textLenght;
			
				$('#'+id+'Remaining').html(charactersRemaining + ' caracteres restantes');
			}
		}
	}
}

function initializeCharactersRemaining(id){
	if (id != "") {
		$('#'+id).bind('keyup', function(){  
			showCharactersRemaining(this);										 				
		});  
		
		showCharactersRemaining($("#"+id));										 	
	}
}

function confirmLogout() {
	if (confirm('Realmente desea salir del sistema?')) {
		location.href = $('#baseUrl').val()+'main/logout';		
	}
}

function showPassword(id) {
	if ($('#'+id).attr('type') == 'password') {
		$('#'+id).removeAttr('type');
		$('#'+id+'Icon').removeClass('fa-eye');
		$('#'+id+'Icon').addClass('fa-eye-slash');		
	} else {
		$('#'+id).attr('type','password');
		$('#'+id+'Icon').removeClass('fa-eye-slash');
		$('#'+id+'Icon').addClass('fa-eye');		
	}
}

function showPasswordRow(id) {
	if ($('#'+id).css("display") == "none") {
		$('#'+id).css("display","inline");
		$('#'+id+'Icon').removeClass('glyphicon-eye-open');
		$('#'+id+'Icon').addClass('glyphicon-eye-close');		
	} else {
		$('#'+id).css("display","none");
		$('#'+id+'Icon').removeClass('glyphicon-eye-close');
		$('#'+id+'Icon').addClass('glyphicon-eye-open');		
	}
}

function showError(containerId,message) {
	if (containerId == null) containerId = "";
	if (message == null) message = "";
	
	if (containerId != "") {
		$('#'+containerId).html(message);
		if (message == "") {
			$('#'+containerId).addClass("hide");
		} else {
			$('#'+containerId).removeClass("hide");
		}
	}
}

function putFocus(id) {
	if (id == null) id = "";

	if (id != "" && $('#'+id).length > 0) {
		$('#'+id).focus();
	}

}

function clearFilter(id,submit) {
	if (id == null) id = "";
	if (submit == null) submit = true;

	if (id != "") {
		$('#'+id+' input:text').val('');
		$('#'+id+' input[type=number]').val('');
		$('#'+id+' input[type=date]').val('');			
		$('#'+id+' select').each(function(){
			if ($(this).attr('id').length > 0) {				
				selectFirstCombo(id+' #'+$(this).attr('id'));
			}
			
		});
		if (submit) {
			$('#'+id).submit();
		}
	}
}

function clearCombo(id,text,value,disabled) {
	if (id == null) id = "";
	if (text == null) text = "";
	if (value == null) value = "";
	if (disabled == null) disabled = false;

	if (id != "") {
		$('#'+id).html("");

		if (text != "") {
			var html = '<option value="'+value+'">'+text+'</option>';	

			$('#'+id).html(html);
			selectFirstCombo(id);	
		}

		$('#'+id).attr("disabled",disabled);
	}
}

function selectFirstCombo(id) {
	if (id == null) id = "";

	if (id != "") {
		$('#'+id+' option:first').prop('selected','selected');			
	}
}


function roundValue(value,decimals) {	
	return formatNumber(value,decimals,'','.');
}

function formatNumber(number,decimals,thousandsSep,decimalSep) {
	//   var_number.number_format([decimals ,thousand separator,decimal separator]);
	//   var number: number to format
	//   decimals: how many decimal numbers (default value 0);
	//   thousand separator: char that define the thousandecimal_sep (default value ,);
	//   decimal separator: char the defines the decimals (default value .);

	if(isNaN(number)) return undefined;
	if(decimals < 0) return undefined;   
	if(decimals == undefined) decimals = 0;
	if(thousandsSep == undefined) thousandsSep = ',';
	if(decimalSep == undefined) decimalSep = '.';

	var returned = number.toString().split('.'), strBegin, strAfter, tempStr = "", i;
	
	if(returned.length == 1) {
		strBegin = returned[0]
		strAfter = '';
	} else if(returned.length == 2) {
		strBegin = returned[0];
		strAfter = returned[1];
		strAfter = strAfter.substr(0, decimals);
	} else {
		trace("uncaught number format");
	}

	// thousands seperator
	if(strBegin.length > 3) {
		for(i = 0; i < strBegin.length; i++) {
			
			if(((strBegin.length - i) % 3) == 0 && i != strBegin.length - 1) {
				if(tempStr=="")
					tempStr = + strBegin.charAt(i);
				else
					tempStr = tempStr + thousandsSep + strBegin.charAt(i);
			}else {
					tempStr = tempStr + strBegin.charAt(i);
			}
		}
	} else {
		tempStr = strBegin;
	}

	//   ----------------------
	//   decimals
	//   if decimals==0 return
	//   ----------------------
	if(decimals > 0) {
		strAfter = strAfter.substr(0, decimals);

		if(strAfter.length < decimals) {
			while(strAfter.length < decimals) {
				strAfter += '0';
			}
		}
	}

	if(decimals > 0) {
		return tempStr + decimalSep + strAfter;
	} else {
		return tempStr;
	}
}

function openNewTab(url) {
	if (url == null) url = "";

	if (url != "") {
		var link = document.createElement('a');
		link.href = url;
		link.target = '_blank';		
		link.click();
	}
}

function openNewWindow(url,options) {
	if (url == null) url = "";
	if (options == null) options = "";

	if (url != "") {
		window.open(url,"",options);
	}
}

function onClickElement(id) {
	if (id == null) id = "";

	if (id != "") {
		if ($('#'+id).length > 0) {
			if ($('#'+id).attr("href") != null) {
				location.href = $('#'+id).attr("href");
			} else {
				$('#'+id).click();
			}
		}

	}
}

function ucfirst(text){
	return text.charAt(0).toUpperCase() + text.slice(1);
}

function lcfirst(text){
	return text.charAt(0).toLowerCase() + text.slice(1);
}

function chargeCombo(comboId,type,parameters) {
	if (comboId == null) comboId = "";
	if (type == null) type = "";
	if (parameters == null) parameters = "";

	if (comboId == "" || type == "") return;
	if ($('#'+comboId) == null || $('#'+comboId).length <= 0) return;

	var url = $('#baseUrl').val()+'general/options';
	url = url + "?type=" + type;
	if (parameters != "") url = url + "&" + parameters;

	$.ajax({
			url: url,				
			success: function(data){
				$('#'+comboId).html(data);
			},
			beforeSend: function(){
				clearCombo(comboId,"[Cargando...]","");
			},
			error: function (data,status,e)
			{
				clearCombo(comboId,"[No se pudo cargar]","-1");
			}				
		});
}

function generalSearch() {
	$('#frmFilter').submit();
}

function generalDelete(id,method) {	
	if (id == null) id = 0;
	if (method == null) method = '';
	id = parseFloat(id);

	if (id > 0 && method != '') {
		if (confirm('Realmente desea eliminar el registro?')) {
			var url = $('#baseUrl').val()+method+'/'+id;
		
			loadDataByAjax(url,'divAuxAjax','reloadPage()');
		}
	}
}

function generalExport(method,parameters) {	
	if (method == null) method = '';	
	if (parameters == null) parameters = '';		

	if (method != '') {
		if (confirm('Realmente desea exportar los registros?')) {
			openNewTab($('#baseUrl').val()+method+'?'+parameters);		
		}
	}
}

function getHome() {	
	location.href = $('#baseUrl').val();			
}

function onClick(id) {
	if (id == null) id = "";

	if (id != "") {
		if ($('#'+id).length > 0) {
			if ($('#'+id).attr("href") != null) {
				location.href = $('#'+id).attr("href");
			} else {
				$('#'+id).click();
			}
		}

	}
}

function initializeSummernote(id=null,valueFromId=null) {
	if (id == null) id = "";
	if (valueFromId == null) valueFromId = "";

	if (id != "") {	
		disabled = $('#'+id).prop('disabled');

		$('#'+id).summernote({
			  disabled: true,			 
			  height: 100,                 
			  maxHeight: 500,
			  lang: 'es-ES',
			  toolbar: [			    
			    ['style', ['bold', 'italic', 'underline']],			    
			    ['fontname', ['fontname']],
			    ['fontsize', ['fontsize']],
			    ['color', ['color']]			  
			  ]             
		});

		$('#'+id).summernote('lineHeight', 1);

		if (valueFromId != "") {
			$('#'+id).summernote('code', $('#'+valueFromId).val());
		}

		if (disabled) {
			$('#'+id).summernote('disable');
		}
	}
}

function transferValueSummernote(originId=null,destinationId=null) {
	if (originId == null) originId = "";
	if (destinationId == null) destinationId = "";

	if (originId != "" && destinationId != "") {
		$('#'+destinationId).val($('#'+originId).summernote('code'));
	}
}


function openPopupSystemMessage(id) {
	if (id == null) id = -1;	
	if (id > 0) {			
		modalMessage('systemMessages/popup/'+id,"Cargando...",null,null,null,null,'closeModalMessage()',true,null,null,null);		
	}
}

function intializePopupSystemMessage(text) {
	if (text == null) text = "";

	$('#headerModal').html(text);
	updateNotifications();
}

function disabledButtonsTypeSave(frmId,disable) {
	if (frmId == null) frmId = "";
	if (disable == null) disable = true;

	if (disable) {
		if (frmId != "") {
			$('#'+frmId+' .type-btn-save').attr('disabled',true);
		} else {
			$('.type-btn-save').attr('disabled',true);
		}
	} else {
		if (frmId != "") {
			$('#'+frmId+' .type-btn-save').removeAttr('disabled');
		} else {
			$('.type-btn-save').removeAttr('disabled');
		}
	}
}

function preSubmit(frmId) {
	disabledButtonsTypeSave(frmId);

	return true;
}

function getUrl(url) {	
	disabledButtonsTypeSave();

	location.href = url;			
}

function printObject(object) {
	var output = '';

	for (var property in object) {
	  output += property + ': ' + object[property]+'; ';
	}
	
	alert(output);
}


function replaceString(find, replace, subject, count) {    
    var i = 0,
        j = 0,
        temp = '',
        repl = '',
        sl = 0,
        fl = 0,
        f = [].concat(find),
        r = [].concat(replace),
        s = subject,
        ra = Object.prototype.toString.call(r) === '[object Array]',
        sa = Object.prototype.toString.call(s) === '[object Array]';
    s = [].concat(s);
    if (count) {
        this.window[count] = 0;
    }

    for (i = 0, sl = s.length; i < sl; i++) {
        if (s[i] === '') {
            continue;
        }
        for (j = 0, fl = f.length; j < fl; j++) {
            temp = s[i] + '';
            repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0];
            s[i] = (temp).split(f[j]).join(repl);
            if (count && s[i] !== temp) {
                this.window[count] += (temp.length - s[i].length) / f[j].length;
            }
        }
    }
    return sa ? s : s[0];
}

function sanitizeGet(get){
	if (get == null) get = "";

	get = replaceString("Ñ","@D1D1@",get);
	get = replaceString("ñ","@F1F1@",get);
	get = replaceString("á","@E1E1@",get);
	get = replaceString("é","@E9E9@",get);
	get = replaceString("ë","@EBEB@",get);
	get = replaceString("í","@EDED@",get);
	get = replaceString("ï","@EFEF@",get);
	get = replaceString("ó","@F3F3@",get);
	get = replaceString("ö","@F4F4@",get);
	get = replaceString("ú","@FAFA@",get);
	get = replaceString("ü","@FCFC@",get);
	get = replaceString("Á","@C1C1@",get);
	get = replaceString("É","@C9C9@",get);
	get = replaceString("Ë","@CBCB@",get);
	get = replaceString("Í","@CDCD@",get);
	get = replaceString("Ï","@CECE@",get);
	get = replaceString("Ó","@D3D3@",get);
	get = replaceString("Ö","@D6D6@",get);
	get = replaceString("Ú","@DADA@",get);
	get = replaceString("Ü","@DCDC@",get);
	get = replaceString("ã","@E3E3@",get);
	get = replaceString("õ","@F5F5@",get);
	get = replaceString("Ã","@C3C3@",get);
	get = replaceString("Õ","@D5D5@",get);
	get = replaceString("´","@B4B4@",get);
	get = replaceString("#","@2323@",get);
	get = replaceString("\"","@2222@",get);
	get = replaceString("º","@D00D00@",get);
	get = replaceString("°","@D0D0@",get);
	get = replaceString("[","@5B5B@",get);
	get = replaceString("¿","@BFBF@",get);
	get = replaceString("¡","@A1A1@",get);
	get = replaceString("¢","@A2A2@",get);
	get = replaceString("£","@A3A3@",get);
	get = replaceString("¤","@A4A4@",get);
	get = replaceString("¥","@A5A5@",get);
	get = replaceString("¦","@A6A6@",get);
	get = replaceString("§","@A7A7@",get);
	get = replaceString("©","@A9A9@",get);
	get = replaceString("²","@B2B2@",get);
	get = replaceString("³","@B3B3@",get);
	get = replaceString("÷","@F7F7@",get);
	get = replaceString("ª","@F8F8@",get);	
	get = replaceString("&","@2626@",get);
	get = replaceString("%","@2525@",get);

	return get;
}

function openWhatsApp(phone,message,mobile) {
	if (phone == null) phone = "";
	if (message == null) message = "";
	if (mobile == null) mobile = false;

	phone = $.trim(phone);	
	if (phone != "") {
		
		if (!isInteger(phone)) {
			alert('El número de teléfono no es válido. Ingrese sólo números.');
			return;
		}

		if (phone.length < 10) {
			alert('El número de teléfono no es válido.');
			return;
		}

		if (phone.length == 10) {
			phone = "549" + phone;
		}

		phone = "+" + phone;

		if (mobile) {
			var url = "whatsapp://";
		} else {
			var url = "http://web.whatsapp.com/";			
		}		
		url = url + "send?text=" + message + "&phone=" + phone + "&abid=" + phone;

		openNewTab(url);
	} else {
		alert('Debe ingresar el número de teléfono.');
	}
}

function isInteger(str) {
    return /^\+?(0|[1-9]\d*)$/.test(str);
}

function modalProcessing(callback) {
	var data = '<img src="'+$('#baseUrl').val()+'assets/images/loading.gif" border="0">';

	modalMessage(null,"Procesando...",null,null,null,null,null,false,callback,data,400);	
}

function generateBackup() {
	if (confirm('Realmente desea generar un Backup de la base de datos?')) {
		location.href = $('#baseUrl').val()+'utils/backup';		
	}
}

function changeBtnUpDown(id) {
	if (id == null) return;	

	var btnId = "#" + id;
	var iconId = "#icon_" + id;
	var containerId = ".container_" + id;	

	if ($(btnId) == null) return;
	if ($(iconId) == null) return;

	if ($(iconId).hasClass("fa-chevron-circle-down")) {
		$(iconId).removeClass("fa-chevron-circle-down")
		$(iconId).addClass("fa-chevron-circle-up")		
		
		$(btnId).attr("title","ocultar");	

		if ($(containerId) != null) $(containerId).removeClass("hide");		
	} else {		
		$(iconId).removeClass("fa-chevron-circle-up")
		$(iconId).addClass("fa-chevron-circle-down")		
		
		$(btnId).attr("title","mostrar");		

		if ($(containerId) != null) $(containerId).addClass("hide");		
	}
}

function openBtnUpDown(id) {
	if (id == null) return;	

	var btnId = "#" + id;
	var iconId = "#icon_" + id;	

	if ($(btnId) == null) return;
	if ($(iconId) == null) return;

	if ($(iconId).hasClass("fa-chevron-circle-down")) {
		changeBtnUpDown(id);
	}
}

function monthsDifference(date1, date2) {
	if (date1 == null || date1 == '' || date2 == null || date2 == '') return -1;

	var dArr1 = date1.split("-"); 
	var dArr2 = date2.split("-"); 

	var d1 = new Date(dArr1[0], dArr1[1] - 1, dArr1[2]);
  	var d2 = new Date(dArr2[0], dArr2[1] - 1, dArr2[2]);

  	var years = d2.getFullYear() - d1.getFullYear();
  	var months = d2.getMonth() - d1.getMonth();

  	return (years * 12 + months);
}

function isValidPeriod(fromDate,toDate) {
	if (fromDate == null || fromDate == '' || toDate == null || toDate == '') return false;
  	
  	var d1 = new Date(fromDate);
  	var d2 = new Date(toDate);

  	return d1 <= d2;
}