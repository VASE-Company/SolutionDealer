<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('errorFormatStart'))
{
	function errorFormatStart($class="",$id="",$style="")
	{ 		
		return "";
	}
}

if (!function_exists('errorFormatEnd'))
{
	function errorFormatEnd()
	{
		return "";
	}
}

if (!function_exists('errorClass'))
{
	function errorClass($id="")
	{
		$class = '';
		
		if ($id != "" && form_error($id) != "") {
			$class = 'is-invalid';
		}

		return $class;
	}
}

if (!function_exists('errorTitle'))
{
	function errorTitle($id="")
	{
		$title = "";
		
		if ($id != "" && form_error($id) != "") {
			$title = ' title="'.form_error($id).'" ';
		}

		return $title;
	}
}

if (!function_exists('messageFormat'))
{
	function messageFormat($type="",$text='',$class="",$id="",$style="",$alwaysFormat=false)
	{
		$textWithFormat = "";

		if ($text != '' || $alwaysFormat) {		
			$typeClass = ($type != ""?'alert-'.$type:"");			
			if ($style != "") $style = ' style="'.$style.'" ';	
			$textWithFormat = '<div class="alert '.$typeClass." ".$class.'" id="'.$id.'" '.$style.'>'.$text.'</div>';
		}
		
		return $textWithFormat;
	}
}

if (!function_exists('errorFormat'))
{
	function errorFormat($text='',$class="",$id="",$style="",$alwaysFormat=false)
	{
		$textWithFormat = "";

		if ($text != '' || $alwaysFormat) {				
			if ($style != "") $style = ' style="'.$style.'" ';	
			$textWithFormat = '<div class="error alert alert-danger '.$class.'" id="'.$id.'" '.$style.'>'.$text.'</div>';
		}
		
		return $textWithFormat;
	}
}

if (!function_exists('getPageConfiguration'))
{
	function getPageConfiguration($url='',$recordsPerPage=10,$totalRecords=0,$parameters=NULL)
	{		
		$configuration['base_url'] = $url;
		$configuration['total_rows'] = $totalRecords;
		$configuration['per_page'] = $recordsPerPage; 
		$configuration['num_links'] = 4;				
		$configuration['use_page_numbers'] = TRUE;		
	   	$configuration['first_link'] = 'Primera';
        $configuration['last_link'] = 'Última';
		
		$segment = str_replace(base_url(),"",$url);
		$arrSegment = explode("/",$segment);
		$configuration['uri_segment'] = count($arrSegment) + 1;
		
		if (isset($parameters)) {
			$configuration['suffix'] = '?'.http_build_query($parameters, '', "&");
			$configuration['first_url'] = $configuration['base_url'].'?'.http_build_query($_GET);
		}		        		

		$configuration['full_tag_open'] = '<div class="dataTables_paginate paging_simple_numbers"><ul class="pagination pagination-sm">';
		$configuration['full_tag_close'] = '</ul></div>';	
		
		$configuration['first_link'] = '<i class="fas fa-angle-double-left"></i>';
		$configuration['first_tag_open'] = '<li class="paginate_button page-item previous">';
		$configuration['first_tag_close'] = '</li>';		
		
		$configuration['last_link'] = '<i class="fas fa-angle-double-right"></i>';
		$configuration['last_tag_open'] = '<li class="paginate_button page-item next">';
		$configuration['last_tag_close'] = '</li>';		
		
		$configuration['next_link'] = '<i class="fas fa-angle-right"></i>';
		$configuration['next_tag_open'] = '<li class="paginate_button page-item next">';
		$configuration['next_tag_close'] = '</li>';		
		
		$configuration['prev_link'] = '<i class="fas fa-angle-left"></i>';
		$configuration['prev_tag_open'] = '<li class="paginate_button page-item previous">';
		$configuration['prev_tag_close'] = '</li>';		
		
		$configuration['cur_tag_open'] = '<li class="paginate_button page-item active"><a href="" class="page-link">';
		$configuration['cur_tag_close'] = '</a></li>';		
		
		$configuration['num_tag_open'] = '<li class="paginate_button page-item">';
		$configuration['num_tag_close'] = '</li>';		
		
		return $configuration;
	} 
}

if (!function_exists('applyPageStyles'))
{
	function applyPageStyles($html="") {
		$html = str_replace("href=",'class="page-link" href=',$html);
		
		return $html;
	}
}

if (!function_exists('convertPageToAjax'))
{
	function convertPageToAjax($html,$functionName) {
		$htmlWithAjax = "";
		$httpHttps = "";
		
		$arr = NULL;
		
		$separator = 'href="http://';		
		if (strpos($html,$separator) !== false) {	
			$httpHttps = "http";	
			$arr = explode($separator,$html);							
		} else {
			$separator = 'href="https://';		
			if (strpos($html,$separator) !== false) {	
				$httpHttps = "https";	
				$arr = explode($separator,$html);							
			}
		}
			
		if (isset($arr)) {					
			for ($i=0; $i < count($arr); $i++) {			
				if ($i > 0) {					
					$htmlWithAjax .= 'href="javascript:'.$functionName."('".$httpHttps."://";					
					$position = strpos($arr[$i],'"');		
					if ($position !== false) {					
						$arr[$i] = substr($arr[$i],0,$position)."')".substr($arr[$i],$position);
					}
				}
				$htmlWithAjax .= $arr[$i];			
			}
		} else {
			$htmlWithAjax = $html;
		}
					
		return $htmlWithAjax;
	}
}

if (!function_exists('abbreviateTextBySeparator'))
{
	function abbreviateTextBySeparator($text='',$separator='',$numberOfSections=-1) {
		$abbreviatedText = $text;

		if (isset($text) && $text != '' && isset($separator) && $separator != '' && $numberOfSections > 0) {
			$textArr = explode($separator,$text);
			if (count($textArr) > 0 && count($textArr) > $numberOfSections) {
				$abbreviatedText = "";
				for($i=0; $i < $numberOfSections; $i++) {
					$abbreviatedText .= $textArr[$i];
					if ($i == ($numberOfSections - 1)) {
						$abbreviatedText .= "...";
					} else {
						$abbreviatedText .= $separator;
					}
				}
			}			
		}
	
		return $abbreviatedText; 
	} 
}

if (!function_exists('abbreviateText'))
{
	function abbreviateText($text='',$numberOfCharacters=-1) {
		$abbreviatedText = $text;
		if (isset($text) && $text != '' && strlen($text) > $numberOfCharacters) {
			 $abbreviatedText = trim(substr($text,0,$numberOfCharacters))."...";	
		}
	
		return $abbreviatedText; 
	} 
}

if (!function_exists('generateOrderButton'))
{
	function generateOrderButton($upward=true,$selected=false,$url='',$nameFunction=''){
		if ($upward) {
			if ($selected) {
				$icon = 'fas fa-caret-square-up';			
			} else {
				$icon = 'far fa-caret-square-up';
			}
			$description = 'ascendente';			
		} else {
			if ($selected) {
				$icon = 'fas fa-caret-square-down';			
			} else {
				$icon = 'far fa-caret-square-down';			
			}
			$description = 'descendente';
		}
		if ($selected) {
			$class = "text-info";
		} else {
			$class = "";
		}
		if ($nameFunction != "") {
			$buttonUrl = "javascript:".$nameFunction."('".$url."')";
		} else {
			$buttonUrl = base_url().$url;
		}

		$html = '<a href="'.$buttonUrl.'" title="'.$description.'" class="btn without-padding '.$class.'" style="float:right"><i class="'.$icon.'"></i></a>';

		return $html;				   
	}				
}	

if (!function_exists('isValidDate'))
{
	function isValidDate($date='',$isMysqlFormat=false) {
		$validDate = false;
		
		if ($date != '') {
			if ($isMysqlFormat) {
				$dateArr = explode("-",$date);					
			} else {
				$dateArr = explode("/",$date);
			}
			if (count($dateArr) == 3) {
				if ($isMysqlFormat) {
					$validDate = checkdate($dateArr[1],$dateArr[2],$dateArr[0]);
				} else {
					$validDate = checkdate($dateArr[1],$dateArr[0],$dateArr[2]);
				}
			}	
		}
						
		return $validDate;
	} 
}	

if (!function_exists('dateFormat'))
{
	function dateFormat($mysqlDate='',$addHoursMinutes=false,$addSeconds=false) {
		$formattedDate = "";
		if (isset($mysqlDate) && $mysqlDate != '') {
			if (strpos($mysqlDate, " ") > 0) {
				$dateWithHourArr = explode(" ",$mysqlDate);
				$dateArr = explode("-",$dateWithHourArr[0]);
				$hours = " ".substr($dateWithHourArr[1],0,5);	
				$seconds = substr($dateWithHourArr[1],5,3);				
			} else {
				$dateArr = explode("-",$mysqlDate);
				$hours = "";
			}			
			$formattedDate = $dateArr[2]."/".$dateArr[1]."/".$dateArr[0];			
			if ($addHoursMinutes) {				
				$formattedDate .= $hours; 
				if ($addSeconds) {				
					$formattedDate .= $seconds; 
				}
			} 			
			if ($formattedDate == "--" || $formattedDate == "00/00/0000" || $formattedDate == "00/00/0000 00:00" || $formattedDate == "00/00/0000 00:00:00") {						
				$formattedDate = "";
			}
		}
			
		return $formattedDate; 
	} 
}

if (!function_exists('dateFormatMysql'))
{
	function dateFormatMysql($date='',$addHoursMinutes=false) {
		$formattedDate = "";
		if (isset($date) && $date != '') {
			if (strpos($date, " ") > 0) {
				$dateWithHourArr = explode(" ",$date);
				$dateArr = explode("/",$dateWithHourArr[0]);
				$hours = " ".substr($dateWithHourArr[1],0,5);				
			} else {
				$dateArr = explode("/",$date);
				$hours = "";
			}			
			$formattedDate = $dateArr[2]."-".$dateArr[1]."-".$dateArr[0];			
			if ($addHoursMinutes) {				
				$formattedDate .= $hours; 
			} 
			if ($formattedDate == "--" || $formattedDate == "0000-00-00" || $formattedDate == "0000-00-00 00:00") {						
				$formattedDate = "";
			}
		}		
	
		return $formattedDate; 
	} 
}			  

if (!function_exists('timeFormat'))
{
	function timeFormat($date='',$addSeconds=false) {
		$formattedTime = "";
		if (isset($date) && $date != '') {
			if (strpos($date, " ") > 0) {
				$dateArr = explode(" ",$date);				
				$formattedTime = $dateArr[1];				
			} else {				
				$formattedTime = $date;
			}						
			if (!$addSeconds) {				
				$formattedTime = " ".substr($formattedTime,0,5);					
			}
		}
			
		return $formattedTime; 
	} 
}

if (!function_exists('convertGet'))
{
	function convertGet($originalGet='',$convert=true) {
		$convertedGet = trim($originalGet);
		if (isset($convertedGet) && $convertedGet!= '') {
			if ($convert) {
				$convertedGet = str_replace("?","·",$convertedGet);
				$convertedGet = str_replace("&","¬",$convertedGet);
				$convertedGet = str_replace("=","^",$convertedGet);
			} else {
				$convertedGet = str_replace("·","?",$convertedGet);
				$convertedGet = str_replace("¬","&",$convertedGet);				
				$convertedGet = str_replace("^","=",$convertedGet);
			}
		}		
	
		return $convertedGet;
	} 
}

if (!function_exists('outputFormat'))
{
	function outputFormat($text='',$forExport=false) {
		
		if ($forExport) {
			$text = utf8_decode($text);
		}
		
		return $text;
	}
}

if (!function_exists('outputNumberFormat'))
{
	function outputNumberFormat($value='',$forExport=false) {
		
		if ($forExport) {
			$value = str_replace(",","", $value);
			$value = str_replace(".",",", $value);
		}
		
		return $value;
	}
}

if (!function_exists('getDifferenceBetweenDates'))
{
	function getDifferenceBetweenDates($mysqlDate1='',$mysqlDate2='') {
		$formattedDifference = "";
		
		if ($mysqlDate1 != "" && $mysqlDate1 != "0000-00-00 00:00:00" &&
		    $mysqlDate2 != "" && $mysqlDate2 != "0000-00-00 00:00:00") {
			$date1 = new DateTime($mysqlDate1);
			$date2 = new DateTime($mysqlDate2);
			$differenceDate = $date1->diff($date2);
			if ($differenceDate->y > 0) {
				$formattedDifference .= $differenceDate->y." a ";	
			}
			if ($differenceDate->m > 0) {
				$formattedDifference .= $differenceDate->m." m ";	
			}
			if ($differenceDate->d > 0) {
				$formattedDifference .= $differenceDate->d." d ";	
			}
			if ($differenceDate->h > 0) {
				$formattedDifference .= $differenceDate->h." h ";	
			}
			if ($differenceDate->i > 0) {
				$formattedDifference .= $differenceDate->i." m ";	
			}
		}
		
		return $formattedDifference;
	}
}

if (!function_exists('getDifferenceOfDaysBetweenDates'))
{
	function getDifferenceOfDaysBetweenDates($mysqlDate1="", $mysqlDate2="")  
	{ 
	    $days = 0;
	    
	    if ($mysqlDate1 != "" && $mysqlDate2 != "") {
    		$difference = strtotime($mysqlDate1) - strtotime($mysqlDate2); 
      		    
    		$days = abs(round($difference / 86400)); 
    	}

    	return $days;
	} 
} 

if (!function_exists('calculateAge'))
{
	function calculateAge($mysqlBirthdate='') {
		$age = "";
		
		if ($mysqlBirthdate != "" && $mysqlBirthdate != "0000-00-00" && 
			isValidDate($mysqlBirthdate,true)) {
			$birthdate = new DateTime($mysqlBirthdate);
			$currentDate = new DateTime(getCurrentDate(false));
			$differenceDate = $birthdate->diff($currentDate);
			if ($differenceDate->y > 0) {
				$age = $differenceDate->y;	
			}
			
		}
		
		return $age;
	}
}

if (!function_exists('getTimeFromDate'))
{
	function getTimeFromDate($mysqlDate='',$abbreviate=false) {
		$formattedDifference = "";
		
		if ($mysqlDate != "" && $mysqlDate != "0000-00-00 00:00:00") {
			$fromDate = new DateTime($mysqlDate);
			$currentDate = new DateTime(getCurrentDate());
			$differenceDate = $fromDate->diff($currentDate);
			if ($differenceDate->y > 0 || $differenceDate->m > 0 || $differenceDate->d > 0) {
				if ($differenceDate->y > 0) {
					if ($formattedDifference != "") $formattedDifference .= " ";
					if ($abbreviate) {
						$formattedDifference .= $differenceDate->y." a";
					} else {
						$formattedDifference .= $differenceDate->y." ".($differenceDate->y == 1?"año":"años");	
					}					
				}
				if ($differenceDate->m > 0) {
					if ($formattedDifference != "") $formattedDifference .= " ";
					if ($abbreviate) {
						$formattedDifference .= $differenceDate->m." m";	
					} else {
						$formattedDifference .= $differenceDate->m." ".($differenceDate->m == 1?"mes":"meses");	
					}					
				}
				if ($differenceDate->d > 0) {
					if ($formattedDifference != "") $formattedDifference .= " ";
					if ($abbreviate) {
						$formattedDifference .= $differenceDate->d." d";
					} else {
						$formattedDifference .= $differenceDate->d." ".($differenceDate->d == 1?"día":"días");	
					}						
				}
			} else {
				if ($differenceDate->h > 0) {
					if ($formattedDifference != "") $formattedDifference .= " ";
					if ($abbreviate) {
						$formattedDifference .= $differenceDate->h." h";
					} else {
						$formattedDifference .= $differenceDate->h." ".($differenceDate->h == 1?"hora":"horas");		
					}						
				}
				if ($differenceDate->i > 0) {
					if ($formattedDifference != "") $formattedDifference .= " ";
					if ($abbreviate) {
						$formattedDifference .= $differenceDate->i." m";
					} else {
						$formattedDifference .= $differenceDate->i." ".($differenceDate->i == 1?"minuto":"minutos");	
					}						
				}
			}						
		}
		
		return $formattedDifference;
	}
}

if (!function_exists('checkCreateFolder'))
{
	function checkCreateFolder($folder="") {
		if ($folder != "") {
			if (!file_exists($folder)){
				@mkdir($folder);								
			}
		}
	}
}	

if (!function_exists('mailBodyFormat'))
{
	function mailBodyFormat($textArr=NULL) {
		$html = "";
		
		if (isset($textArr)) {
			$html .= '<table border="0">';
			for ($i=0; $i < count($textArr); $i++) {
				$html .= '<tr>';
				$html .= '<td height="22">';
				$html .= $textArr[$i];
				$html .= '</td>';
				$html .= '</tr>';
			}
			$html .= '</table>';
		}
	
		return $html;
	}
}

if (!function_exists('getNameMonth'))
{
	function getNameMonth($numberMonth,$abbreviate=false) {
		$nameMonth = "";
		
		switch ($numberMonth) {
			case 1: $nameMonth = ($abbreviate?"Ene":"Enero"); break;
			case 2: $nameMonth = ($abbreviate?"Feb":"Febrero"); break;
			case 3: $nameMonth = ($abbreviate?"Mar":"Marzo"); break;
			case 4: $nameMonth = ($abbreviate?"Abr":"Abril"); break;
			case 5: $nameMonth = ($abbreviate?"May":"Mayo"); break;
			case 6: $nameMonth = ($abbreviate?"Jun":"Junio"); break;
			case 7: $nameMonth = ($abbreviate?"Jul":"Julio"); break;			
			case 8: $nameMonth = ($abbreviate?"Ago":"Agosto"); break;
			case 9: $nameMonth = ($abbreviate?"Sep":"Septiembre"); break;
			case 10: $nameMonth = ($abbreviate?"Oct":"Octubre"); break;
			case 11: $nameMonth = ($abbreviate?"Nov":"Noviembre"); break;
			case 12: $nameMonth = ($abbreviate?"Dic":"Diciembre"); break;
		}				
		
		return $nameMonth;
	}
}

 if (!function_exists('getCurrentDateSpanish'))
{
	function getCurrentDateSpanish() {		
   		$daysArr = array('Domingo', 'Lunes', 'Martes','Miercoles', 'Jueves', 'Viernes', 'Sabado');
     
	    return $daysArr[date('w')].", ".date('d')." de ".getNameMonth(date('m'))." de ".date('Y');
	}
}	

 if (!function_exists('getBooleanToText'))
{
	function getBooleanToText($value=null) {
		$text = ""; 
		if (isset($value)) {
			if ($value == true || (int)$value == 1) {
				$text = "Si";
			} else {
				$text = "No";
			}
		}
     
	    return $text	;
	}
}

if (!function_exists('decimalFormat'))
{
	function decimalFormat($value=0,$numberOfDecimals=2,$forExport=false) {
		$value = (string)$value;
		if ($value == NULL || $value == "") {
			$formattedValue = "";
		} else {
			if ($forExport) {
				$decimalSymbol = ",";
				$thousandSeparator = "";
			} else {
				$decimalSymbol = ".";
				$thousandSeparator = "";
			}			
			$formattedValue = number_format($value,$numberOfDecimals,$decimalSymbol,$thousandSeparator);
		}
			
		return $formattedValue; 
	} 
}

if (!function_exists('getTimeZone'))
{
	function getTimeZone() {		
		return "-3 hour";
	}
}

if (!function_exists('getCurrentDateSystem'))
{
	function getCurrentDateSystem() {		
		return strtotime(getTimeZone());
	}
}


if (!function_exists('getCurrentDate'))
{
	function getCurrentDate($withHMS=true,$daysAdd=0) {		
		if ($daysAdd != 0) {
			$daysAdd = $daysAdd." day ";
		} else {
			$daysAdd = "";
		}

		if ($withHMS) {
			$date = date('Y-m-d H:i:s',strtotime($daysAdd.getTimeZone()));
		} else {			
			$date = date('Y-m-d',strtotime($daysAdd.getTimeZone()));
		}

		return $date;
	}
}


if (!function_exists('getCurrentDateId'))
{
	function getCurrentDateId() {		
		$id = date('YmdHis',getCurrentDateSystem());
		
		return $id;
	}
}

if (!function_exists('getFirstDayMonth'))
{
	function getFirstDayMonth() {		
		$date = date('Y-m-01',getCurrentDateSystem());

		return $date;
	}
}

if (!function_exists('getLastDayMonth'))
{
	function getLastDayMonth() {		
		$date = date('Y-m-t',getCurrentDateSystem());

		return $date;
	}
}

if (!function_exists('rowTitleFormatOutput'))
{
	function rowTitleFormatOutput($text1="",$text2="",$twoColumns=true,$forExport=false,$addTable=false) {		
		$html = "";
		if ($addTable) {
			$html .= "<table>";	
		}
		$html .= "<tr>";
		if ($twoColumns) {			
        	$html .= '<td valign="top" style="font-size:15px; font-weight:bold; padding-right:5px;">'.outputFormat($text1,$forExport).'</td>';            
       		$html .= '<td valign="top" style="font-size:15px;" colspan="15">'.outputFormat($text2,$forExport).'</td>';            
		} else {
			$html .= '<td valign="top" style="font-size:20px; font-weight:bold;" colspan="15">'.outputFormat($text1,$forExport).'</td>';
		}
		$html .= "</tr>";
		if ($addTable) {
			$html .= "</table>";	
		}
			
		return $html; 
	} 
}

if (!function_exists('getListBoolean'))
{
	function getListBoolean() {
		$list[0] = array('id'=>"1",'description'=>"Si");
		$list[1] = array('id'=>"0",'description'=>"No");

		return $list;
	}
}

if (!function_exists('removeAccents'))
{
	function removeAccents($text="") {				
		$text = str_replace ("á", "a" ,$text);
		$text = str_replace ("Á", "A" ,$text);
		$text = str_replace ("é", "e" ,$text);
		$text = str_replace ("É", "E" ,$text);
		$text = str_replace ("í", "i" ,$text);
		$text = str_replace ("Í", "I" ,$text);
		$text = str_replace ("ó", "o" ,$text);
		$text = str_replace ("Ó", "O" ,$text);
		$text = str_replace ("ú", "u" ,$text);
		$text = str_replace ("Ú", "U" ,$text);			
		
		return $text;
	}
}

if (!function_exists('encryptText'))
{	
	function encryptText($text="") {
		$key = "SistemaPostVentaSD";
	 	$decryptedText = strtr(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), serialize($text), MCRYPT_MODE_CBC, md5(md5($key)))), '+/=', '-_.');
	 	return $decryptedText;
	}	
}

if (!function_exists('decryptText'))
{	
	function decryptText($stringArray="") {
		$key = "SistemaPostVentaSD";
		$decryptedText = unserialize(rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode(strtr($text, '-_.', '+/=')), MCRYPT_MODE_CBC, md5(md5($key))), "\0"));
		return $decryptedText;
	}	
}

if (!function_exists('getDescription'))
{	
	function getDescription($dataArr=null, $id=null) {
		if (isset($dataArr) && isset($id) && count($dataArr) > 0) {
			for ($i=0; $i < count($dataArr); $i++) {
				if ($dataArr[$i]['id'] == $id) {
					return $dataArr[$i]['description'];
				}
			}
		}

		return "";
	}	
}

if (!function_exists('getListYears'))
{
	function getListYears($from=1950) {		
		$list = NULL; 
		$idx = 0; 

		if ($from < 0) $from = 0;

		for ($i=date('Y'); $i >= 1950; $i--) {			
			$list[$idx++] = array('id'=>$i,'description'=>$i);
		}

		return $list;
	}
}

if (!function_exists('getListMonths'))
{
	function getListMonths($abbreviate=false) {		
		$list = NULL; 
		$idx = 0; 		

		for ($i=1; $i <= 12; $i++) {			
			$list[$idx++] = array('id'=>$i,'description'=>getNameMonth($i,$abbreviate));
		}

		return $list;
	}
}

if (!function_exists('completeWith0'))
{
	function completeWith0($value="", $lenght=0, $toLeft=true) {		
		
		$newValue = $value;
		for ($i=strlen($value); $i < $lenght; $i++) {	
			if ($toLeft) {
				$newValue = "0".$newValue;
			} else {
				$newValue = $newValue."0";
			}	
		}

		return $newValue;
	}
}

if (!function_exists('getLetterOfExcelColumn'))
{
	function getLetterOfExcelColumn($column=0) {	

		$letters= ["",
		           "A","B","C","D","E","F","G","H","I","J",
		           "K","L","M","N","O","P","Q","R","S","T",
		           "U","V","W","X","Y","Z",
		           "AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ",
		           "AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT",
		           "AU","AV","AW","AX","AY","AZ",
		       	   "BA","BB","BC","BD","BE","BF","BG","BH","BI","BJ",
		           "BK","BL","BM","BN","BO","BP","BQ","BR","BS","BT",
		           "BU","BV","BW","BX","BY","BZ"];

		if ($column >= 1 && $column < count($letters)) {
			$letter = $letters[$column];
		} else {
			$letter = "";
		}
		
		return $letter;
	}
}

if (!function_exists('randomColor'))
{
	function randomColor() {
	    return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
	}
}

if (!function_exists('getSpaceWhite'))
{
	function getSpaceWhite($count=1) {
		$spaceWhite = "";

		for ($i=1; $i <= $count; $i++) {
			$spaceWhite .= "&nbsp;";
		}
	    
	    return $spaceWhite;
	}
}

if (!function_exists('desanitizeGet'))
{
	function desanitizeGet($get="") {
		$get = str_replace("@D1D1@","Ñ",$get);
		$get = str_replace("@F1F1@","ñ",$get);
		$get = str_replace("@E1E1@","á",$get);
		$get = str_replace("@E9E9@","é",$get);
		$get = str_replace("@EBEB@","ë",$get);
		$get = str_replace("@EDED@","í",$get);
		$get = str_replace("@EFEF@","ï",$get);
		$get = str_replace("@F3F3@","ó",$get);
		$get = str_replace("@F4F4@","ö",$get);
		$get = str_replace("@FAFA@","ú",$get);
		$get = str_replace("@FCFC@","ü",$get);
		$get = str_replace("@C1C1@","Á",$get);
		$get = str_replace("@C9C9@","É",$get);
		$get = str_replace("@CBCB@","Ë",$get);
		$get = str_replace("@CDCD@","Í",$get);
		$get = str_replace("@CECE@","Ï",$get);
		$get = str_replace("@D3D3@","Ó",$get);
		$get = str_replace("@D6D6@","Ö",$get);
		$get = str_replace("@DADA@","Ú",$get);
		$get = str_replace("@DCDC@","Ü",$get);
		$get = str_replace("@E3E3@","ã",$get);
		$get = str_replace("@F5F5@","õ",$get);
		$get = str_replace("@C3C3@","Ã",$get);
		$get = str_replace("@D5D5@","Õ",$get);
		$get = str_replace("@B4B4@","´",$get);
		$get = str_replace("@2323@","#",$get);
		$get = str_replace("@2222@","\"",$get);
		$get = str_replace("@D00D00@","º",$get);
		$get = str_replace("@D0D0@","°",$get);
		$get = str_replace("@5B5B@","[",$get);
		$get = str_replace("@BFBF@","¿",$get);
		$get = str_replace("@A1A1@","¡",$get);
		$get = str_replace("@A2A2@","¢",$get);
		$get = str_replace("@A3A3@","£",$get);
		$get = str_replace("@A4A4@","¤",$get);
		$get = str_replace("@A5A5@","¥",$get);
		$get = str_replace("@A6A6@","¦",$get);
		$get = str_replace("@A7A7@","§",$get);
		$get = str_replace("@A9A9@","©",$get);
		$get = str_replace("@B2B2@","²",$get);
		$get = str_replace("@B3B3@","³",$get);
		$get = str_replace("@F7F7@","÷",$get);			
		$get = str_replace("@F8F8@","ª",$get);
		$get = str_replace("@2626@","&",$get);
		$get = str_replace("@2525@","%",$get);

		return $get;
	}
}

if (!function_exists('existsIdInListIdDescription'))
{
	function existsIdInListIdDescription($id='',$list) {
		if ($id != "" && isset($list)) {
			for ($i=0; $i < count($list); $i++) {
				if ($list[$i]['id'] == $id) {
					return true;
				}
			}
		} 
		
		return false;		
	}
}

if (!function_exists('addToListIdDescription'))
{
	function addToListIdDescription($id='',$description='',&$list) {
		if ($id != "") {
			
			if (!isset($list)) $list = array();

			if (!existsIdInListIdDescription($id, $list)) {
				$list[count($list)] = array('id'=>$id,
			                                'description'=>$description);
			}
		}
	}
}

if (!function_exists('compareElementByDescription'))
{
	function compareElementByDescription($element1, $element2) {
    	if (strcmp(trim(strtolower($element1['description'])),trim(strtolower($element2['description']))) < 0)
		{
		  	return false;
		} else {
			return true;
		}
	} 
}

if (!function_exists('formatValue'))
{
	function formatValue($value='', $type='') {
    	switch (trim(strtolower($type))) {
            case 'decimal':
            	$value = decimalFormat((float)$value,2);
            break;
        } 

        return $value;
	} 
}

if (!function_exists('orderSummaryOfBudgetsReassigned'))
{
	function orderSummaryOfBudgetsReassigned(array $elem1, array $elem2) {		
		if (strcasecmp($elem1['companyDescription'],$elem2['companyDescription']) == 0) {
			return strcasecmp($elem1['assessorDescription'],$elem2['assessorDescription']);
		} else {
			return strcasecmp($elem1['companyDescription'],$elem2['companyDescription']);
		}
	} 	
}

/* End of file general_helper.php */
/* Location: ./application/helpers/general_helper.php */