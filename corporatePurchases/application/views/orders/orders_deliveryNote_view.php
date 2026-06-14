<?php
    $allowSaveReceivedData = isset($allowSaveReceivedData) && $allowSaveReceivedData;
    $receivedDataDisabled = ($allowSaveReceivedData?'':' disabled="disabled" ');  
    $itemsCount = (isset($details)?count($details):0);
?>
<form class="form-horizontal" action="" method="post" accept-charset="utf-8" id="frmDataDN" name="frmDataDN" role="form">
    <div class="row" style="margin-bottom: 10px;margin-top: 0px;margin-left: 9px;">         
    <?php if ($deliveryNoteId > 0) { ?>               
        <label for="date" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Fecha:</label>
        <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
            <input id="date" name="date" class="form-control form-control-sm noEnterMyApp" autocomplete="off" value="<?php echo dateFormat($deliveryNote['date'],true); ?>" style="width:120px;float:left;" readonly="readonly">                    
        </div>    
        <label for="userDescription" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Usuario:</label>
        <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
            <input id="userDescription" name="userDescription" class="form-control form-control-sm noEnterMyApp" value="<?php echo $deliveryNote['userDescription']; ?>" style="float:left;" readonly="readonly">                    
        </div>                                         
    <?php } ?>     
    </div> 
    <div class="row">           
        <div class="col-sm-12" style="max-height:250px;overflow-y:auto;">
            <table id="grHistory" class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <?php if ($deliveryNoteId <= 0 && $allowSave && $itemsCount > 0) { ?>     
                        <th width="30px" class="text-center">                        
                            <input type="checkbox" id="allItems" name="allItems" value="1" class="noEnterMyApp" onclick="selectItemsDeliveryNote()"/>                        
                        </th>                                                                                     
                        <?php } ?>                                        
                        <th nowrap="nowrap" width="150px">Código</th>  
                        <th nowrap="nowrap">Descripción</th>                                                                                                                                                                                 
                        <th nowrap="nowrap" width="100px">Cant.</th> 
                        <?php if ($deliveryNoteId <= 0 && $allowSave) { ?>                                                                                                                                           
                        <th nowrap="nowrap" width="100px">P/ Ent.</th>     
                        <th nowrap="nowrap" width="100px">Ent.</th>                                                                                                                           
                        <?php } ?>                                                                                             
                    </tr>
                </thead>   
                <?php if (isset($details)) { ?>                        
                <tbody>
                    <?php                        
                        for($i=0; $i < count($details); $i++) {     
                            $row = $i+1;                                       
                    ?>
                    <tr>        
                        <?php if ($deliveryNoteId <= 0 && $allowSave) { ?>   
                        <?php 
                            $checked = "";
                            if (validation_errors() != "" || (isset($error) && $error != "")) {
                                $checked = set_checkbox('items[]', $details[$i]['id']);                               
                            } else {      
                                if ((int)$details[$i]['freeQuantity'] > 0) {                      
                                    $checked = ' checked="checked" ';                        
                                }
                            }
                        ?>                        
                        <td class="text-center"><input type="checkbox" id="items[]" name="items[]" class="item noEnterMyApp" value="<?php echo $details[$i]['id']; ?>" row="<?php echo $row; ?>" <?php echo $checked; ?> onclick="selectItemDeliveryNote(this,true)" /></td>                                                                                 
                        <?php } ?>                                                            
                        <td nowrap="nowrap"><?php echo $details[$i]['code']; ?></td>  
                        <td nowrap="nowrap"><?php echo $details[$i]['description']; ?></td>                          
                        <td nowrap="nowrap" class="text-center"><?php echo (int)$details[$i]['quantity']; ?></td>  
                        <?php 
                            if ($deliveryNoteId <= 0 && $allowSave) {                                 
                        ?>                         
                        <td nowrap="nowrap" class="text-center"><?php echo (int)$details[$i]['freeQuantity']; ?></td> 
                        <td class="without-padding">
                            <input type="number" min="1" step="1" max="<?php echo (int)$details[$i]['freeQuantity']; ?>" id="dnFreeQuantity<?php echo $row; ?>" name="dnFreeQuantity<?php echo $row; ?>" class="form-control form-control-sm text-right" maxlength="3" autocomplete="off" value="<?php echo (int)$details[$i]['freeQuantity']; ?>"  onfocus="this.select();" style="margin-top:1px;">
                            <input type="hidden" id="dnItemOrderId<?php echo $row; ?>" name="dnItemOrderId<?php echo $row; ?>" value="<?php echo $details[$i]['id']; ?>">  
                            <input type="hidden" id="dnArticleId<?php echo $row; ?>" name="dnArticleId<?php echo $row; ?>" value="<?php echo $details[$i]['articleId']; ?>">  
                        </td>                                
                        <?php } ?>                                                                                                                                                         
                    </tr>  
                    <?php 
                        } 
                    ?>                         
                </tbody>
                <?php 
                    }
                ?>         
            </table>
            <input type="hidden" id="itemsCount" name="itemsCount" value="<?php echo $itemsCount; ?>">              
        </div>        
    </div> 
    <?php if ($showReceivedData) { ?>
    <div class="row" style="margin-bottom: 10px;margin-left: 9px;">                             
        <label for="receivedDate" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Recibido el:</label>
        <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
            <input type="date" id="receivedDate" name="receivedDate" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("receivedDate"); ?>" placeholder="--/--/----" <?php echo errorTitle("receivedDate"); ?> autocomplete="off" value="<?php echo set_value('receivedDate',$deliveryNote['receivedDate']); ?>" style="width:120px;float:left;" required="1" <?php echo $receivedDataDisabled; ?>>                    
        </div> 
        <label for="receivedBy" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Recibido por:</label>
        <div class="col-xs-5 col-sm-5 col-md-5 col-lg-5"> 
            <input id="receivedBy" name="receivedBy" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("receivedBy"); ?>" <?php echo errorTitle("receivedBy"); ?> autocomplete="off" maxlength="50" required="1" value="<?php echo set_value('receivedBy',$deliveryNote['receivedBy']); ?>" <?php echo $receivedDataDisabled; ?>>                    
        </div> 
    </div>
    <div class="row" style="margin-bottom: 10px;margin-left: 9px;">   
        <label for="receivedObservation" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Observación:</label>
        <div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">                      
            <textarea id="receivedObservation" name="receivedObservation" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("receivedObservation"); ?>" <?php echo errorTitle("receivedObservation"); ?> rows="2" maxlength="500" autocomplete="off" <?php echo $receivedDataDisabled; ?>><?php echo set_value('receivedObservation',$deliveryNote['receivedObservation']); ?></textarea>
            <div class="remaining-characters-label" id="receivedObservationRemaining"></div>                                                            
        </div>                    
    </div> 
    <?php } ?>
    <input type="hidden" id="orderId" name="orderId" value="<?php echo $orderId; ?>">  
    <input type="hidden" id="deliveryNoteId" name="deliveryNoteId" value="<?php echo $deliveryNoteId; ?>">  
    <?php if ($allowSave || $allowSaveReceivedData) { ?>
    <div class="error alert alert-danger text-center hide" id="errorDeliveryNote"></div>
    <?php } ?>
    <div class="row without-padding">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">      
            <?php if ($allowSave || $allowSaveReceivedData) { ?>
                <?php if ($allowSave) { ?>
                    <?php if ($itemsCount > 0) { ?>
                    <button type="button" id="btnSend" name="btnSend" class="btn btn-success type-btn-save" onclick="sendDeliveryNotesOrderEdit()">Guardar</button>                             
                    <button type="button" class="btn btn-danger type-btn-save" onclick="closeModalMessage()">Cancelar</button>    
                    <?php } else { ?>
                    <button type="button" class="btn btn-danger type-btn-save" onclick="closeModalMessage()">Cerrar</button>    
                    <?php } ?>                                                            
                <?php } else { ?>
                <button type="button" id="btnSend" name="btnSend" class="btn btn-success type-btn-save" onclick="sendReceivedDataDeliveryNotesOrderEdit()">Guardar</button>                             
                <button type="button" class="btn btn-danger type-btn-save" onclick="seeDeliveryNotesOrderEdit(<?php echo $orderId; ?>)">Volver</button>                                            
                <?php } ?>                                                            
            <?php } else { ?>
                <button type="button" class="btn btn-success type-btn-save" onclick="seeDeliveryNotesOrderEdit(<?php echo $orderId; ?>)">Volver</button>                                            
            <?php } ?>
        </div>   
    </div>     
</form>  
<?php 
/* End of file orders_deliveryNote_view.php */
/* Location: ./application/views/orders/orders_deliveryNote_view.php */