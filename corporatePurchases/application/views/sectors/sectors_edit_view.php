<?php 
	$disabled = "";
	if (!$allowSave) {
		$disabled = ' disabled="disabled" ';
	}		
    if ($sector['id'] > 0) {
        $id = $sector['id']; 
    } else {
        $id = set_value('id',$sector['id']);
    }
?>
<div class="card card-secondary">
    <div class="card-body">
        <form class="form-horizontal" action="<?php echo base_url(); ?>sectors/save" method="post" accept-charset="utf-8" id="frmData" name="frmData" role="form"  onsubmit="return preSubmit();">
            <div class="form-group row">
                <label for="description" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Descripción:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-5">
                    <input id="description" name="description" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("description"); ?>" <?php echo errorTitle("description"); ?> autocomplete="off" maxlength="100" placeholder="Descripción" required="1" autofocus="1" value="<?php echo set_value('description',$sector['description']); ?>" <?php echo $disabled; ?>>                    
                </div>                    
            </div>        
            <div class="form-group row">
                <label for="allowOrders" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Incluir en Pedidos:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
                    <select id="allowOrders" name="allowOrders" class="form-control form-control-sm <?php echo errorClass("allowOrders"); ?>" <?php echo errorTitle("allowOrders"); ?> <?php echo $disabled; ?> style="width:auto;">                                                  
                        <?php 
                            for($i=0; $i < count($states); $i++) {
                                if (validation_errors() != "" || (isset($error) && $error != "")) {
                                    $selected = set_select('allowOrders', $states[$i]['id']);
                                } else {
                                    $selected = ($sector['allowOrders'] == $states[$i]['id']?' selected="selected" ':'');                  
                                }
                        ?>
                        <option value="<?php echo $states[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo $states[$i]['description'];?></option>
                        <?php
                            }
                        ?>
                    </select>                                            
                </div>
                <label for="allowPayments" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-3 col-lg-2 text-right">Incluir en Pagos:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2"> 
                    <select id="allowPayments" name="allowPayments" class="form-control form-control-sm <?php echo errorClass("active"); ?>" <?php echo errorTitle("active"); ?> <?php echo $disabled; ?> style="width:auto;">                                                  
                        <?php 
                            for($i=0; $i < count($states); $i++) {
                                if (validation_errors() != "" || (isset($error) && $error != "")) {
                                    $selected = set_select('allowPayments', $states[$i]['id']);
                                } else {
                                    $selected = ($sector['allowPayments'] == $states[$i]['id']?' selected="selected" ':'');                  
                                }
                        ?>
                        <option value="<?php echo $states[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo $states[$i]['description'];?></option>
                        <?php
                            }
                        ?>
                    </select>                                            
                </div>
            </div>                           
            <div class="form-group row">
                <label for="active" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Activo:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-7">  
                    <select id="active" name="active" class="form-control form-control-sm <?php echo errorClass("active"); ?>" <?php echo errorTitle("active"); ?> <?php echo $disabled; ?> style="width:auto;">                                                  
                        <?php 
                            for($i=0; $i < count($states); $i++) {
                                if (validation_errors() != "" || (isset($error) && $error != "")) {
                                    $selected = set_select('active', $states[$i]['id']);
                                } else {
                                    $selected = ($sector['active'] == $states[$i]['id']?' selected="selected" ':'');                  
                                }
                        ?>
                        <option value="<?php echo $states[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo $states[$i]['description'];?></option>
                        <?php
                            }
                        ?>
                    </select>                                            
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
                        <button type="button" class="btn btn-danger type-btn-save" onclick="getUrl('<?php echo base_url(); ?>sectors')">Cancelar</button>                                            
                    <?php } else { ?>                        
                        <button type="button" class="btn btn-success type-btn-save" onclick="getUrl('<?php echo base_url(); ?>sectors')">Volver</button>                                            
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
/* End of file sectors_edit_view.php */
/* Location: ./application/views/sectors/sectors_edit_view.php */