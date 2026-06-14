<div class="row without-padding">   
    <div class="col-sm-12" style="margin-bottom: 0px;margin-top: 0px;margin-left: 9px;"> 
        
    </div>  
    <?php
        if ($allowSave && $maximumCount > 0) {
    ?>
    <form class="form-horizontal" action="" method="post" accept-charset="utf-8" id="frmDataIC" name="frmDataIC" role="form">
        <div class="form-group row">                    
            <label for="count" class="col-form-label col-form-label-sm text-right" style="width:130px;">Cantidad (Máx. <?php echo $maximumCount; ?>):</label>
            <input type="number" step="1" min="1" max="<?php echo $maximumCount; ?>" id="count" name="count" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("count"); ?>" <?php echo errorTitle("count"); ?> autocomplete="off" value="<?php echo set_value('count',"0"); ?>" style="width:100px;float:left;margin-left:10px;" required="1">                    
            <label for="observation" class="col-form-label col-form-label-sm text-right" style="width:45px;margin-left:15px;">Motivo:</label>
            <input id="observation" name="observation" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("observation"); ?>" <?php echo errorTitle("observation"); ?> autocomplete="off" maxlength="250"  value="<?php echo set_value('observation',""); ?>" required="1" style="width:350px;float:left;margin-left:10px;">                    
            <button type="button" id="btnSend" name="btnSend" class="btn btn-success btn-sm type-btn-save" onclick="sendCancelItemOrderEdit()" style="margin-left:5px;">Guardar</button>            
        </div>        
        <input type="hidden" id="orderId" name="orderId" value="<?php echo $orderId; ?>"> 
        <input type="hidden" id="maximumCount" name="maximumCount" value="<?php echo $maximumCount; ?>"> 
        <input type="hidden" id="detailOrderId" name="detailOrderId" value="<?php echo $detailOrderId; ?>"> 
    </div>    
    <div class="error alert alert-danger text-center hide" id="errorCancelItem"></div>
    <?php
        }
    ?>
    <div class="col-sm-12">  
        <label for="grHistory" class="col-form-label col-form-label-sm">HISTORIAL DE CANCELACIONES:</label>
    </div>              
    <div class="col-sm-12" style="max-height:250px;overflow-y:auto;">
        <table id="grHistory" class="table table-bordered table-sm">
            <thead>
                <tr>                                                                                         
                    <th nowrap="nowrap" width="100px">Fecha</th>                                                          
                    <th nowrap="nowrap" width="100px">Cantidad</th>  
                    <th nowrap="nowrap">Motivo</th>
                    <th nowrap="nowrap" width="200px">Usuario</th>                                                       
                </tr>
            </thead>
            <?php if (isset($history)) { ?>
            <tbody>
                <?php
                    for($i=0; $i < count($history); $i++) {                                            
                ?>
                <tr>                                
                    <td nowrap="nowrap"><?php echo dateFormat($history[$i]['date'],true); ?></td>                     
                    <td nowrap="nowrap" class="text-center"><?php echo (int)$history[$i]['value']; ?></td>  
                    <td nowrap="nowrap"><?php echo $history[$i]['observation']; ?></td> 
                    <?php 
                        if ($history[$i]['userLastName'] != "" || $history[$i]['userFirstName'] != "") {
                            if ($history[$i]['userLastName'] != "" && $history[$i]['userFirstName'] != "") {
                                $user = $history[$i]['userLastName'].", ".$history[$i]['userFirstName'];
                            } else {
                                $user = $history[$i]['userLastName'].$history[$i]['userFirstName'];
                            }
                        } else {
                            $user = "";
                        }
                    ?>
                    <td><?php echo $user; ?></td>                                                                                                                               
                </tr>  
                <?php 
                    } 
                ?>                         
            </tbody>
            <?php } ?>
        </table>
    </div>
</div> 
<?php 
/* End of file orders_history_view.php */
/* Location: ./application/views/orders/orders_history_view.php */