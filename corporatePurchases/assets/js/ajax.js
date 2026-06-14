function loadDataByAjax(url,divId,callback,formId,noHideDiv,styleShow)
{	
	if (url.indexOf("?") <= -1) {
		url = url + "?";
	} else {
		url = url + "&";
	}
	url = url + Math.random();

	
	if (noHideDiv == null) {
		noHideDiv = false;
	}
	if (styleShow == null) {
		styleShow = '';
	}
	if (callback == null) {
		callback = '';
	}
	
	if (formId){		
		var data = new FormData($("#"+formId)[0]);

		$.ajax({
				url: url,						
				success: function(data){
					finalizeDataByAjax(data,divId,callback,styleShow);
				},
				beforeSend: function(){
					initializeDataByAjax(divId,noHideDiv);
				},
				type:'POST',
				data: data,
				cache: false, 
				contentType: false,
				processData: false, 				
				error: function (data,status,e)
				{
					finalizeDataByAjax('',divId,'','');
				}								
			});				
				
	} else {
		$.ajax({
				url: url,				
				success: function(data){
					finalizeDataByAjax(data,divId,callback,styleShow);
				},
				beforeSend: function(){
					initializeDataByAjax(divId,noHideDiv);
				},
				error: function (data,status,e)
				{
					finalizeDataByAjax('',divId,'','');
				}				
			});
	}
} 	

function initializeDataByAjax(divId,noHideDiv){	
	var obj = $('#'+divId);
	var preLoader = $('#pre'+divId); 
	
	if (obj != null) {		
		if (noHideDiv == false) {
		  	$(obj).hide();						  			  
		}
		if (preLoader != null) {						
		  	$(preLoader).html('<img src="'+$('#baseUrl').val()+'assets/images/loading.gif" border="0">');			
			$(preLoader).show();					
		}			  
	}	  			
}

function finalizeDataByAjax(data,divId,callback,styleShow) {
	var obj = $('#'+divId);
	var preLoader = $('#pre'+divId);	  	
	var executeCallback = true;
	
	if (preLoader != null) {
		$(preLoader).html('');			
		$(preLoader).hide();
	}
	
	if (obj != null) {		  										
		if (data != null) {
			if (data.indexOf("/#/") > -1){
				var response = data.split("/#/");
				
				switch (response['0']) {
					case "err":
						alert(response['1']);
						executeCallback = false;
					break;
					
					case "msg":
						alert(response['1']);
						executeCallback = false;
					break;
					
					case "ok":
						alert(response['1']);
					break;
					
					case "conf":
						if (confirm(response['1'])) {
							executeCallback = true;
						} else {
							executeCallback = false;
						}						
					break;
					
					case "fun":
						callback = response['1'];
						data = "";
					break;
				}					
			}
		} else {
			alert("No se pudo cargar los datos.");
			executeCallback = false;
		}			
		if (executeCallback == true && data != "") {
			$(obj).html(data);
		}
		switch (styleShow) {
			case 'slow':
				$(obj).show("slow");	
				break;
			case 'fadeIn':
				$(obj).fadeIn();	
				break;
			case 'slideUp':
				$(obj).slideUp("slow");	
				break;
			case 'slideDown':
				$(obj).slideDown("slow");	
				break;	
			default:
				$(obj).show("slow");	
		}						
		if (executeCallback == true && callback != '') {
			if (callback.indexOf(";") > -1){
				var items = callback.split(";");
				for (var i=0; i < items.length; i++){
					if (items[i] != "") {
						if (items[i].substring(items[i].length-1,items[i].length) ==  ")") {
							eval(items[i] + ';');
						} else {						
							eval(items[i] + '();');
						}
					}
				} 
			} else {
				if (callback.substring(callback.length-1,callback.length) ==  ")") {
					eval(callback + ';');							
				} else{
					eval(callback + '();');							
				}
			}
		}		
	} else {		
		alert("No se pudo cargar los datos.");
	}	
}
	
