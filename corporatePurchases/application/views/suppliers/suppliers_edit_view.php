<?php 
	$disabled = "";
	if (!$allowSave) {
		$disabled = ' disabled="disabled" ';
	}		
    if ($supplier['id'] > 0) {
        $id = $supplier['id']; 
    } else {
        $id = set_value('id',$supplier['id']);
    }
?>
<div class="card card-secondary">
    <div class="card-body">
        <form class="form-horizontal" action="<?php echo base_url(); ?>suppliers/save" method="post" accept-charset="utf-8" id="frmData" name="frmData" role="form" onsubmit="return preSubmit();">
            <div class="form-group row">
                <label for="code" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">CUIT:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-7">
                    <input id="code" name="code" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("code"); ?>" <?php echo errorTitle("code"); ?> autocomplete="off" maxlength="11" placeholder="CUIT" required="1" autofocus="1" value="<?php echo set_value('code',$supplier['code']); ?>" <?php echo $disabled; ?> style="width:150px;float:left;">                    
                    <label style="font-size:10px;margin-left: 10px;margin-top:8px;float:left;">(Sin guiones)</label>
                </div>                    
            </div>
            <div class="form-group row">
                <label for="tradeName" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Nombre Comercial:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-5">
                    <input id="tradeName" name="tradeName" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("tradeName"); ?>" <?php echo errorTitle("tradeName"); ?> autocomplete="off" maxlength="100" placeholder="Nombre Comercial" required="1" value="<?php echo set_value('tradeName',$supplier['tradeName']); ?>" <?php echo $disabled; ?>>                    
                </div>                    
            </div>
            <div class="form-group row">
                <label for="businessName" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Razón Social:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-5">
                    <input id="businessName" name="businessName" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("businessName"); ?>" <?php echo errorTitle("businessName"); ?> autocomplete="off" maxlength="100" placeholder="Razón Social" required="1" value="<?php echo set_value('businessName',$supplier['businessName']); ?>" <?php echo $disabled; ?>>                    
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
                                    $selected = ($supplier['active'] == $states[$i]['id']?' selected="selected" ':'');                  
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
                        <button type="button" class="btn btn-danger type-btn-save" onclick="getUrl('<?php echo base_url(); ?>suppliers')">Cancelar</button>                                            
                    <?php } else { ?>
                        <button type="button" class="btn btn-success type-btn-save" onclick="getUrl('<?php echo base_url(); ?>suppliers')">Volver</button>                                            
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
/* End of file suppliers_edit_view.php */
/* Location: ./application/views/suppliers/suppliers_edit_view.php */