<?php
    if (isset($export) && $export) {
        $roundTableStyle = "border-collapse:separate;border-spacing: 0;border:solid black 0px;border-radius: 10px;-moz-border-radius: 10px;-webkit-border-radius: 10px;";

        $headerTableAttr = 'style="background-color:#f4f6f9;'.$roundTableStyle.'"';                    

        if (isset($headerLogo) && $headerLogo != "") {             
            $type = pathinfo($headerLogo, PATHINFO_EXTENSION);
            $data = file_get_contents($headerLogo);
            $headerLogo = 'data:image/'.$type.';base64,'.base64_encode($data);
        }            
    } else {
        //$headerTableAttr = 'class="gradient-table-print round-table"';   
        $roundTableStyle = "border-collapse:separate;border-spacing: 0;border:solid black 0px;border-radius: 10px;-moz-border-radius: 10px;-webkit-border-radius: 10px;";
        $headerTableAttr = 'style="background-color:#f4f6f9;'.$roundTableStyle.'[BREAK_PAGE]"';    
    }  

    switch ($documentType) {
        case "PO":
            $title = "ORDEN DE COMPRA Nº ";
            $number = $order['purchaseOrderNumber'];
            $date = $order['purchaseOrderDate'];
            $referenceOrderNumber = $order['id'];
            $referencePurchaseOrderNumber = "";
            $showObservations = true;
            $showPaymentSectors = true;            
            $showLocations = !$export;
            $copies = 1;            
        break;
        case "DN":
            $title = "REMITO Nº ";
            $number = $order['deliveryNoteNumber'];
            $date = $order['deliveryNoteDate'];
            $referenceOrderNumber = $order['id'];
            $referencePurchaseOrderNumber = $order['purchaseOrderNumber'];
            $showObservations = false;
            $showPaymentSectors = false;            
            $showLocations = false;
            $copies = 2;
        break;
        default:
            $title = "PEDIDO Nº ";
            $number = $order['id'];
            $date = $order['date'];
            $referenceOrderNumber = "";
            $referencePurchaseOrderNumber = "";
            $showObservations = true;
            $showPaymentSectors = false;            
            $showLocations = false;
            $copies = 1;
        break;
    }      
?>
<?php
    for ($copy=1; $copy <= $copies; $copy++) {
?>
<?php
    if (isset($headerLogo) && $headerLogo != "" && false) {
?>
<table width="100%">    
    <tr>                    
        <td nowrap="nowrap" align="left" valign="top" width="50%">            
            <div>    
            <img src="<?php echo $headerLogo."?".time(); ?>" width="80%" style="margin-bottom:10px;" />
            </div>                        
        </td>
        <td nowrap="nowrap" align="left" valign="top" width="50%"></td>
    </tr>       
</table>
<?php
    }
?>  
<?php 
    $mainTitleStyle = "padding-top:15px;padding-bottom:5px;font-size:20px;height:30px;font-weight:bold;text-decoration:underline;color:#000000;";

    $cellMainStyle = "color:black;padding-left:20px;padding-top:15px;padding-bottom:5px;font-size:18px;height:25px;";
    $cellStyle = "color:black;padding-left:20px;padding-top:5px;padding-bottom:5px;font-size:15px;";
    $titleStyle = "";    
    $valueStyle = "background-color:white;color:black;padding-left:10px;padding-right:10px;border-radius:5px;font-weight:bold;";         
?>
<table <?php echo str_replace("[BREAK_PAGE]", ($copy > 1?"break-before: page;":""), $headerTableAttr); ?> width="100%">
    <tr> 
        <td></td>     
        <td nowrap="nowrap" align="center" valign="middle" style="<?php echo $mainTitleStyle; ?>">            
            <div><?php echo outputFormat("FORMULARIO DE COMPRAS"); ?></div>               
        </td> 
        <?php
            if (isset($headerLogo) && $headerLogo != "") {
        ?>                    
        <td nowrap="nowrap" align="right" valign="top">            
            <div style="margin-top:10px; margin-right:15px;overflow:visible; height:10px;" >    
            <img src="<?php echo $headerLogo."?".time(); ?>" width="130px"/>
            </div>                        
        </td>                
        <?php
            }
        ?>            
    </tr>      
    <tr>      
        <td nowrap="nowrap" align="left" valign="middle" width="33%" style="<?php echo $cellMainStyle; ?>font-weight:bold;">            
            <div><?php 
                    if ($copies > 1) {
                        $strCopy = ($copy > 1?" [COPIA]":" [ORIGINAL]");
                    } else {
                        $strCopy = "";
                    }                    
                    echo outputFormat($title.$strCopy); 
                  ?>
            </div>   
            <div style="<?php echo $valueStyle; ?>"><?php echo outputFormat($number); ?></div>  
        </td>              
        <td nowrap="nowrap" align="left" valign="middle" width="33%" style="<?php echo $cellMainStyle; ?>font-weight:bold;">            
            <div><?php echo outputFormat('Fecha'); ?></div>   
            <div style="<?php echo $valueStyle; ?>"><?php echo dateFormat($date,false); ?></div>  
        </td>        
    </tr>  
    <?php if ($referenceOrderNumber != "" || $referencePurchaseOrderNumber != "") { ?>
    <tr>                   
        <?php if ($referenceOrderNumber != "") { ?>
        <td nowrap="nowrap" align="left" valign="middle" width="33%" style="<?php echo $cellStyle; ?>">    
            <div style="<?php echo $titleStyle; ?>"><?php echo outputFormat('Pedido Nº'); ?></div>            
            <div style="<?php echo $valueStyle; ?>"><?php echo outputFormat($referenceOrderNumber); ?></div>                                              
        </td>  
        <?php } ?>    
        <?php if ($referencePurchaseOrderNumber != "") { ?>
        <td nowrap="nowrap" align="left" valign="middle" width="33%" style="<?php echo $cellStyle; ?>">    
            <div style="<?php echo $titleStyle; ?>"><?php echo outputFormat('Orden de Compra Nº'); ?></div>            
            <div style="<?php echo $valueStyle; ?>"><?php echo outputFormat($referencePurchaseOrderNumber); ?></div>                                              
        </td>  
        <?php } ?>    
    </tr> 
    <?php } ?>    
    <tr>      
        <td nowrap="nowrap" align="left" valign="middle" width="33%" style="<?php echo $cellStyle; ?>">            
            <div style="<?php echo $titleStyle; ?>"><?php echo outputFormat('Empresa'); ?></div>   
            <div style="<?php echo $valueStyle; ?>"><?php echo outputFormat($order['companyDescription']); ?></div>  
        </td>              
        <td nowrap="nowrap" align="left" valign="middle" width="33%" style="<?php echo $cellStyle; ?>">
            <div style="<?php echo $titleStyle; ?>"><?php echo outputFormat('Sucursal'); ?></div>     
            <div style="<?php echo $valueStyle; ?>"><?php echo outputFormat($order['branchOfficeDescription']); ?></div>  
        </td>
        <td nowrap="nowrap" align="left" valign="middle" width="33%" style="<?php echo $cellStyle; ?>padding-right:20px;">            
            <div style="<?php echo $titleStyle; ?>"><?php echo outputFormat('Sector'); ?></div>        
            <div style="<?php echo $valueStyle; ?>"><?php echo outputFormat($order['sectorDescription']); ?></div>  
        </td>        
    </tr>      
    <tr>      
        <td nowrap="nowrap" align="left" valign="middle" width="33%" style="<?php echo $cellStyle; ?>">            
            <div style="<?php echo $titleStyle; ?>"><?php echo outputFormat('Solicitado Por'); ?></div>   
            <div style="<?php echo $valueStyle; ?>"><?php echo outputFormat((isset($user)?$user['lastName'].", ".$user['firstName']:"")); ?></div>  
        </td>              
        <?php if (isset($authorizingUser)) { ?>
        <td nowrap="nowrap" align="left" valign="middle" width="33%" style="<?php echo $cellStyle; ?>">
            <div style="<?php echo $titleStyle; ?>"><?php echo outputFormat('Validado Por'); ?></div>     
            <div style="<?php echo $valueStyle; ?>"><?php echo outputFormat((isset($authorizingUser)?$authorizingUser['lastName'].", ".$authorizingUser['firstName']:"")); ?></div>  
        </td>
        <?php } ?>
        <?php if (isset($manager)) { ?>
        <td nowrap="nowrap" align="left" valign="middle" width="33%" style="<?php echo $cellStyle; ?>padding-right:20px;">            
            <div style="<?php echo $titleStyle; ?>"><?php echo outputFormat('Gestionado Por'); ?></div>        
            <div style="<?php echo $valueStyle; ?>"><?php echo outputFormat((isset($manager)?$manager['lastName'].", ".$manager['firstName']:"")); ?></div>  
        </td>     
        <?php } ?>   
    </tr>  
    <tr>      
        <td colspan="3" style="<?php echo $cellStyle; ?>">          
        </td>     
    </tr> 
</table>
<?php 
    $details = $order['details'];

    $columnGralTitleAttr = 'nowrap="nowrap" align="left" valign="middle" style="background-color:#000000;color:#FFFFFF;border:solid;border-width:1px;"';    
    $columnTextAttr = 'align="left" valign="middle" style="padding-left:5px;padding-right:5px;border:solid;border-width:1px;"';
    $columnNumberAttr = 'align="right" valign="middle" style="padding-left:5px;padding-right:5px;border:solid;border-width:1px;"';
    //$columnFooterSubTitleAttr = 'align="right" valign="middle" colspan="1" style="background-color:#7e7e80;color:#FFFFFF;padding-right:15px;border-left:none;"';
    $columnFooterItemAttr = 'align="right" valign="middle" colspan="1" style="background-color:#a8a9ab;color:#FFFFFF;padding-right:15px;border-left:none;"';
    $columnFooterTitleAttr = 'align="right" valign="middle" colspan="1" style="font-weight:bold;background-color:#000000;color:#FFFFFF;padding-right:15px;border-left:none;padding-top:15px;padding-bottom:15px;font-size:18px;"';
    $columnFooterAttr = 'align="right" valign="middle" style="padding-right:5px;background-color:#f4f6f9;"';    
    $columnFooterSubTotalAttr = 'align="right" valign="middle" style="padding-right:5px;font-weight:bold;background-color:#f4f6f9;color:#7e7e80;"';    
    $columnFooterTotalAttr = 'align="right" valign="middle" style="padding-right:5px;font-weight:bold;background-color:#e5e9ef;font-size:18px;"';    
?>    
<table style="margin-top:20px;" width="100%" cellspacing="0">    
    <tr>                   
        <td width="18%" <?php echo $columnGralTitleAttr;?>><?php echo outputFormat("Código"); ?></td>        
        <td width="33%" <?php echo $columnGralTitleAttr;?>><?php echo outputFormat("Descripción"); ?></td>
        <?php if ($showLocations) { ?>
        <td width="10%" <?php echo $columnGralTitleAttr;?>><?php echo outputFormat("Ubicación"); ?></td>
        <?php } ?> 
        <?php if ($showImports) { ?>
        <td width="10%" <?php echo $columnGralTitleAttr;?>><?php echo outputFormat("Costo ($)"); ?></td>
        <?php } ?> 
        <td width="7%" <?php echo $columnGralTitleAttr;?>><?php echo outputFormat("Cant."); ?></td>
        <td width="26%" <?php echo $columnGralTitleAttr;?>><?php echo outputFormat("Rubro"); ?></td>
        <?php if ($showImports) { ?>      
        <td width="12%" <?php echo $columnGralTitleAttr;?>><?php echo outputFormat("Total ($)"); ?></td>
        <?php } ?> 
    </tr>
    <?php 
        $columnsCount = 4;
        if ($showImports) $columnsCount += 2;
        if ($showLocations) $columnsCount += 1;        

        $detailsCount = (isset($details)?count($details):0);        

        for ($i=0; $i < $detailsCount; $i++) {
    ?>
    <tr>                
        <td <?php echo $columnTextAttr;?>><?php echo outputFormat($details[$i]['code']); ?></td>                
        <td <?php echo $columnTextAttr;?>><?php echo outputFormat($details[$i]['description']); ?></td>
        <?php if ($showLocations) { 
                $location = "";
                if (trim($details[$i]['corridor']) != "") {
                    if ($location != "") $location .= " - ";
                    $location .= trim($details[$i]['corridor']);
                }
                if (trim($details[$i]['shelf']) != "") {
                    if ($location != "") $location .= " - ";
                    $location .= trim($details[$i]['shelf']);
                }
        ?>
        <td <?php echo $columnTextAttr;?>><?php echo outputFormat($location); ?></td>
        <?php } ?> 
        <?php if ($showImports) { ?>
        <td <?php echo $columnNumberAttr;?>><?php echo decimalFormat($details[$i]['unitPrice'],2); ?></td>
        <?php } ?> 
        <td <?php echo $columnNumberAttr;?>><?php echo $details[$i]['quantity']; ?></td>
        <td <?php echo $columnTextAttr;?>><?php echo outputFormat($details[$i]['familyDescription']); ?></td>
        <?php if ($showImports) { ?>
        <td <?php echo $columnNumberAttr;?>><?php echo decimalFormat($details[$i]['total'],2); ?></td>
        <?php } ?> 
    </tr>
    <?php 
        }
    ?>    
    <tr>  
        <td colspan="<?php echo $columnsCount; ?>">&nbsp;</td>                        
    </tr>
    <?php if ($showImports) { ?>
    <tr>         
        <td colspan="<?php echo ($columnsCount-2); ?>"></td>              
        <td <?php echo $columnFooterTitleAttr;?>><?php echo outputFormat("TOTAL s/IVA $"); ?></td>
        <td <?php echo $columnFooterTotalAttr;?>><?php echo decimalFormat($order['total'],2); ?></td>
    </tr>
    <?php 
        }
    ?>    
</table>
<?php if ($showObservations) { ?>
<table width="100%" style="margin-top:10px;border:solid;border-width:0.5px;" cellspacing="0">
    <tr>                    
        <td style="padding-left:10px;background-color:#000000;color:#FFFFFF;">
            <?php echo outputFormat("Observaciones:"); ?>
        </td>        
    </tr>
    <?php
        $userObservations = $order['userObservations'];

        if (isset($userObservations) && count($userObservations) > 0) {
            for ($i=0; $i < count($userObservations); $i++) {
    ?>
    <tr>                    
        <td style="padding-left:10px;background-color:#f4f6f9;<?php if ($i == count($userObservations) - 1) echo 'padding-bottom:10px;'; ?>">
            <?php echo outputFormat($userObservations[$i]['observation']); ?>
        </td>        
    </tr>
    <?php 
            }
        } else {
    ?>
    <tr>                    
        <td>&nbsp;</td>        
    </tr>
    <?php 
        }
    ?>
</table>
<?php } ?>
<?php 
     $paymentSectors = $order['paymentSectors'];

    if (isset($paymentSectors) && $showPaymentSectors) { 
?>
<table style="margin-top:15px;" cellspacing="0">
    <tr>                   
        <td nowrap="nowrap" width="200px;" <?php echo $columnGralTitleAttr;?>><?php echo outputFormat("Empresa"); ?></td>        
        <td nowrap="nowrap" width="200px;" <?php echo $columnGralTitleAttr;?>><?php echo outputFormat("Sucursal"); ?></td>        
        <td nowrap="nowrap" width="200px;" <?php echo $columnGralTitleAttr;?>><?php echo outputFormat("Sector"); ?></td>        
        <td nowrap="nowrap" width="60px;"  align="center" <?php echo $columnGralTitleAttr;?>><?php echo outputFormat("%"); ?></td>    
    </tr>    
    <?php                                    
        for($j=0; $j < count($paymentSectors); $j++) {                
    ?>        
    <tr>                
        <td <?php echo $columnTextAttr;?>><?php echo outputFormat($paymentSectors[$j]['companyDescription']); ?></td>  
        <td <?php echo $columnTextAttr;?>><?php echo outputFormat($paymentSectors[$j]['branchOfficeDescription']); ?></td>  
        <td <?php echo $columnTextAttr;?>><?php echo outputFormat($paymentSectors[$j]['sectorDescription']); ?></td>  
        <td <?php echo $columnNumberAttr;?> align="center"><?php echo outputFormat(decimalFormat($paymentSectors[$j]['percent'],2)); ?></td>  
    </tr>        
    <?php } ?>    
</table>
<?php } ?>
<?php if ($documentType == "DN") { ?>
<table width="100%" style="margin-top:30px;" cellspacing="0">
    <tr>                    
        <td width="40%" style="padding-left:10px;background-color:#000000;color:#FFFFFF;">
            <?php echo outputFormat("Entregado por:"); ?>
        </td> 
        <td width="10%"> </td>  
        <td width="40%" style="padding-left:10px;background-color:#000000;color:#FFFFFF;">
            <?php echo outputFormat("Recibido por:"); ?>
        </td>        
        <td width="10%"> </td>   
    </tr>
    <tr>                    
        <td style="padding-left:10px;padding-bottom:5px;padding-top:5px;background-color:#f4f6f9;">
            <?php echo outputFormat("Firma:"); ?>
        </td>  
        <td> </td>  
        <td style="padding-left:10px;padding-bottom:5px;padding-top:5px;background-color:#f4f6f9;">
            <?php echo outputFormat("Firma:"); ?>
        </td>  
        <td> </td>     
    </tr>   
    <tr>                    
        <td style="padding-left:10px;padding-bottom:5px;background-color:#f4f6f9;">
            <?php echo outputFormat("Aclaración:"); ?>
        </td>  
        <td> </td>  
        <td style="padding-left:10px;padding-bottom:5px;background-color:#f4f6f9;">
            <?php echo outputFormat("Aclaración:"); ?>
        </td>  
        <td> </td>      
    </tr>    
    <tr>                    
        <td style="padding-left:10px;padding-bottom:5px;background-color:#f4f6f9;">
            <?php echo outputFormat("DNI:"); ?>
        </td>  
        <td> </td>  
        <td style="padding-left:10px;padding-bottom:5px;background-color:#f4f6f9;">
            <?php echo outputFormat("DNI:"); ?>
        </td>  
        <td> </td>      
    </tr>     
</table>    
<?php } ?>  
<?php
    if (isset($export) && $export) {        
        if (isset($footerLogo) && $footerLogo != "") {             
            $type = pathinfo($footerLogo, PATHINFO_EXTENSION);
            $data = file_get_contents($footerLogo);
            $footerLogo = 'data:image/'.$type.';base64,'.base64_encode($data);
        }                
    }        
?>
<?php
    if (isset($footerLogo) && $footerLogo != "") {
?>
<table width="100%" style="margin-top:15px;">    
    <tr>                    
        <td nowrap="nowrap" align="right" valign="top">            
            <div>    
            <img src="<?php echo $footerLogo."?".time(); ?>" width="130px" style="margin-bottom:10px;" />
            </div>                        
        </td>        
    </tr>       
</table>
<?php
    }
?>  
<br />
<?php
    }
?> 
<?php 
    if (isset($printId) && $printId != "") {
?>
<br />
<table border="0" style="margin-top:20px;" width="100%">
    <tr>                    
        <td nowrap="nowrap">        
            <a href="<?php echo base_url(); ?>orders/externalPrint/<?php echo $printId; ?>" style="color:#000000" target="_blank"><?php echo outputFormat("* Ver Versión Imprimible"); ?></a>                                        
        </td>        
    </tr>    
</table>
<br />
<?php } ?>