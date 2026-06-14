<?php 
	$disabled = "";
	if (!$allowSave) {
		$disabled = ' disabled="disabled" ';
	}		
    if ($bill['id'] > 0) {
        $id = $bill['id']; 
    } else {
        $id = set_value('id',$bill['id']);
    }
?>
<div class="card card-secondary">
    <div class="card-body">
        <form class="form-horizontal" action="<?php echo base_url(); ?>bills/save" method="post" accept-charset="utf-8" id="frmData" name="frmData" role="form" enctype="multipart/form-data" onsubmit="return preSubmit();">
            <div class="form-group row">                    
                <label for="date" class="col-form-label col-form-label-sm col-xs-3 col-sm-3 col-md-3 col-lg-3 text-right">Fecha:</label>
                <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
                    <input type="date" id="date" name="date" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("date"); ?>" placeholder="--/--/----" <?php echo errorTitle("date"); ?> autocomplete="off" autofocus="1" required="1" value="<?php echo set_value('date',$bill['date']); ?>" style="width:160px;" <?php echo $disabled; ?>>                    
                </div> 
                <label for="typeId" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Tipo:</label>
                <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">                        
                    <select id="typeId" name="typeId" class="form-control form-control-sm <?php echo errorClass("typeId"); ?>" <?php echo errorTitle("typeId"); ?> required="1" <?php echo $disabled; ?> style="width:auto;">                            
                        <?php 
                            for($i=0; $i < count($billsTypes); $i++) {
                                if (validation_errors() != "" || (isset($error) && $error != "")) {
                                    $selected = set_select('typeId', $billTypes[$i]['id']);
                                } else {
                                    $selected = ($bill['typeId'] == $billsTypes[$i]['id']?' selected="selected" ':'');                  
                                }
                        ?>
                        <option value="<?php echo $billsTypes[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo $billsTypes[$i]['description'];?></option>
                        <?php
                            }
                        ?>
                    </select>                                            
                </div>                                
            </div>    
            <div class="form-group row">                    
                <label for="letter" class="col-form-label col-form-label-sm col-xs-3 col-sm-3 col-md-3 col-lg-3 text-right">Comprobante:</label>
                <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
                    <input id="letter" name="letter" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("letter"); ?>" <?php echo errorTitle("letter"); ?> autocomplete="1" maxlength="1" placeholder="X" required="1" value="<?php echo set_value('letter',$bill['letter']); ?>" <?php echo $disabled; ?> style="width:30px;float:left;">    
                    <input id="serie" name="serie" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("serie"); ?>" <?php echo errorTitle("serie"); ?> autocomplete="1" maxlength="4" placeholder="0000" required="1" value="<?php echo set_value('serie',$bill['serie']); ?>" <?php echo $disabled; ?> style="width:50px;float:left;margin-left:5px;">    
                    <input id="number" name="number" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("number"); ?>" <?php echo errorTitle("number"); ?> autocomplete="1" maxlength="8" placeholder="00000000" required="1" value="<?php echo set_value('number',$bill['number']); ?>" <?php echo $disabled; ?> style="width:80px;float:left;margin-left:5px;">    
                </div>
                <label for="date" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Descripción:</label>
                <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
                    <input id="description" name="description" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("description"); ?>" <?php echo errorTitle("description"); ?> autocomplete="off" maxlength="100" placeholder="Descripción" required="1" value="<?php echo set_value('description',$bill['description']); ?>" <?php echo $disabled; ?>>    
                </div>  
            </div>
            <div class="form-group row"> 
                <label for="date" class="col-form-label col-form-label-sm col-xs-3 col-sm-3 col-md-3 col-lg-3 text-right">Importe: $</label>
                <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
                    <input type="number" min="0" step="0.01" id="amount" name="amount" class="form-control form-control-sm noEnterMyApp text-right" autocomplete="off"  required="1" value="<?php echo decimalFormat(set_value('amount',$bill['amount']),2); ?>" style="width:110px;float:left;" <?php echo $disabled; ?>>                                                
                </div> 
                <label for="stateId" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Estado:</label>
                <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">                        
                    <select id="stateId" name="stateId" class="form-control form-control-sm <?php echo errorClass("stateId"); ?>" <?php echo errorTitle("stateId"); ?> required="1" <?php echo $disabled; ?> style="width:auto;">                            
                        <?php 
                            for($i=0; $i < count($billsStates); $i++) {
                                if (validation_errors() != "" || (isset($error) && $error != "")) {
                                    $selected = set_select('stateId', $billTypes[$i]['id']);
                                } else {
                                    $selected = ($bill['stateId'] == $billsStates[$i]['id']?' selected="selected" ':'');                  
                                }
                        ?>
                        <option value="<?php echo $billsStates[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo $billsStates[$i]['description'];?></option>
                        <?php
                            }
                        ?>
                    </select>                                            
                </div>                                
            </div>                  
            <div class="form-group row">                    
                <label for="paymentDate" class="col-form-label col-form-label-sm col-xs-3 col-sm-3 col-md-3 col-lg-3 text-right">Fecha Pago:</label>
                <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
                    <input type="date" id="paymentDate" name="paymentDate" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("paymentDate"); ?>" placeholder="--/--/----" <?php echo errorTitle("paymentDate"); ?> autocomplete="off" value="<?php echo set_value('paymentDate',$bill['paymentDate']); ?>" style="width:160px;" <?php echo $disabled; ?>>                    
                </div>
            </div>           
            <div class="form-group row">
                <label for="paymentData" class="col-form-label col-form-label-sm col-xs-3 col-sm-3 col-md-3 col-lg-3 text-right">Obs. del Pago:</label>
                <div class="col-xs-8 col-sm-8 col-md-8 col-lg-8">                      
                    <textarea id="paymentData" name="paymentData" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("paymentData"); ?>" <?php echo errorTitle("paymentData"); ?> rows="2" maxlength="500" autocomplete="off" <?php echo $disabled; ?>><?php echo set_value('paymentData',$bill['paymentData']); ?></textarea>
                    <div class="remaining-characters-label" id="paymentDataRemaining"></div>                                                            
                </div>                    
            </div>   
            <div class="form-group row">
                <label for="billFile" class="col-form-label col-form-label-sm col-xs-3 col-sm-3 col-md-3 col-lg-3 text-right" style="margin-top:5px;">                                                             
                    Fact. Elect.: 
                </label>
                <div class="col-xs-9 col-sm-9 col-md-7 col-lg-5" style="padding-top:2px;">     
                    <?php if ($hasFile) { ?>   
                    <span class="col-form-label-sm">Archivo Actual</span>
                    <a href="<?php echo base_url(); ?>bills/download/<?php echo $id; ?>" class="btn" title="descargar" alt="_blank" style="padding-left:0px;">
                        <i class="fas fa-download"></i>                        
                    </a>            
                    <?php } ?>          
                    <?php if ($allowSave) { ?>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="billFile" name="billFile">
                        <label class="custom-file-label" for="billFile">Seleccione el archivo</label>                        
                    </div>  
                    <?php } ?>                                                                     
                </div>     
            </div>                                  
            <?php if (isset($error) && $error <> "")  { ?>
            <div class="form-group row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
                <?php echo errorFormat($error); ?>	
                </div>                                        
            </div>
            <?php } ?>                                                    
            <div class="form-group row" style="margin-top:20px;">           
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">      
                    <?php if ($allowSave) { ?>
                        <button type="submit" id="btnSend" name="btnSend" class="btn btn-success type-btn-save">Guardar</button>                                            
                        <button type="button" class="btn btn-danger type-btn-save" onclick="getUrl('<?php echo base_url(); ?>bills')">Cancelar</button>                                            
                    <?php } else { ?>                        
                        <button type="button" class="btn btn-success type-btn-save" onclick="getUrl('<?php echo base_url(); ?>bills')">Volver</button>                                            
                    <?php } ?>
                </div>
            </div>
            <input type="hidden" id="id" name="id" value="<?php echo $id; ?>">                   
        </form>
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->     
<?php 
/* End of file bills_edit_view.php */
/* Location: ./application/views/bills/bills_edit_view.php */