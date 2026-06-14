<?php 
	$disabled = "";
	if (!$allowSave) {
		$disabled = ' disabled="disabled" ';
	}		
    if ($article['id'] > 0) {
        $id = $article['id']; 
    } else {
        $id = set_value('id',$article['id']);
    }
?>
<div class="card card-secondary">
    <div class="card-body">
        <form class="form-horizontal" action="<?php echo base_url(); ?>articles/save" method="post" accept-charset="utf-8" id="frmData" name="frmData" role="form" onsubmit="return preSubmit();">
            <div class="form-group row">
                <label for="code" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Código:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-7">
                    <input id="code" name="code" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("code"); ?>" <?php echo errorTitle("code"); ?> autocomplete="off" maxlength="25" placeholder="Código" required="1" autofocus="1" value="<?php echo set_value('code',$article['code']); ?>" <?php echo $disabled; ?> style="width:200px">                    
                </div>                    
            </div>
            <div class="form-group row">
                <label for="description" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Descripción:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-5">
                    <input id="description" name="description" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("description"); ?>" <?php echo errorTitle("description"); ?> autocomplete="off" maxlength="100" placeholder="Descripción" required="1" value="<?php echo set_value('description',$article['description']); ?>" <?php echo $disabled; ?>>                    
                </div>                    
            </div>
            <div class="form-group row">
                <label for="familyId" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Rubro:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-7">                        
                    <select id="familyId" name="familyId" class="form-control form-control-sm <?php echo errorClass("familyId"); ?>" <?php echo errorTitle("familyId"); ?> required="1" <?php echo $disabled; ?> style="width:auto;">                            
                        <?php 
                            if (validation_errors() != "" || (isset($error) && $error != "")) {
                                $selected = set_select('familyId', $article['familyId']);
                            } else {
                                $selected = ($article['familyId'] == 0?' selected="selected" ':'');                  
                            }
                        ?>
                        <option value="" <?php echo $selected; ?>>[Seleccionar]</option>
                        <?php 
                            for($i=0; $i < count($families); $i++) {
                                if (validation_errors() != "" || (isset($error) && $error != "")) {
                                    $selected = set_select('familyId', $families[$i]['id']);
                                } else {
                                    $selected = ($article['familyId'] == $families[$i]['id']?' selected="selected" ':'');                  
                                }
                        ?>
                        <option value="<?php echo $families[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo $families[$i]['description'];?></option>
                        <?php
                            }
                        ?>
                    </select>                                            
                </div>
            </div>
            <div class="form-group row">
                <label for="usual" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Habitual:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-7">  
                    <select id="usual" name="usual" class="form-control form-control-sm <?php echo errorClass("usual"); ?>" <?php echo errorTitle("usual"); ?> <?php echo $disabled; ?> style="width:auto;">                                                  
                        <?php 
                            for($i=0; $i < count($states); $i++) {
                                if (validation_errors() != "" || (isset($error) && $error != "")) {
                                    $selected = set_select('usual', $states[$i]['id']);
                                } else {
                                    $selected = ($article['usual'] == $states[$i]['id']?' selected="selected" ':'');                  
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
                                    $selected = ($article['active'] == $states[$i]['id']?' selected="selected" ':'');                  
                                }
                        ?>
                        <option value="<?php echo $states[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo $states[$i]['description'];?></option>
                        <?php
                            }
                        ?>
                    </select>                                            
                </div>
            </div>          
            <div class="form-group row" style="margin-bottom:30px;">
                <label class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">
                    Ubicación:                                    
                </label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-5"> 
                    <table class="table table-bordered table-sm" id="grLocations" style="margin-bottom:0px;margin-top:10px;">
                        <thead>
                            <tr>                                                       
                                <th nowrap="nowrap" class="col-form-label-sm">Depósito</th>                        
                                <th nowrap="nowrap" style="width:100px;" class="col-form-label-sm">Pasillo</th>                                  
                                <th nowrap="nowrap" style="width:100px;" class="col-form-label-sm">Estante</th>                                        
                            </tr>
                        </thead>                            
                        <tbody>
                        <?php 
                            $locations = (isset($article['locations'])?$article['locations']:NULL);
                            
                            if (isset($locations)) {                                 
                        ?>                            
                            <?php                                    
                                for($i=0; $i < count($locations); $i++) {
                                    $row = $i + 1;                                       
                            ?>
                            <tr id="<?php echo $row; ?>">                                                                 
                                <td class="col-form-label-sm">
                                    <?php echo $locations[$i]['warehouseDescription']; ?>
                                    <input type="hidden" id="locationId<?php echo $row; ?>" name="locationId<?php echo $row; ?>" value="<?php echo (float)$locations[$i]['id']; ?>">     
                                    <input type="hidden" id="locationWarehouseId<?php echo $row; ?>" name="locationWarehouseId<?php echo $row; ?>" value="<?php echo $locations[$i]['warehouseId']; ?>">     
                                    <input type="hidden" id="locationDescription<?php echo $row; ?>" name="locationDescription<?php echo $row; ?>" value="<?php echo $locations[$i]['warehouseDescription']; ?>">     
                                </td>                                                                    
                                <td class="without-padding"><input id="locationCorridor<?php echo $row; ?>" name="locationCorridor<?php echo $row; ?>" class="form-control form-control-sm detailColumn" maxlength="5" autocomplete="off" value="<?php echo $locations[$i]['corridor']; ?>" <?php echo $disabled; ?> onfocus="this.select();"></td>                                
                                <td class="without-padding"><input id="locationShelf<?php echo $row; ?>" name="locationShelf<?php echo $row; ?>" class="form-control form-control-sm detailColumn" maxlength="5" autocomplete="off" value="<?php echo $locations[$i]['shelf']; ?>" <?php echo $disabled; ?> onfocus="this.select();"></td>                                
                            </tr>
                            <input type="hidden" id="locationsCount" name="locationsCount" value="<?php echo count($locations); ?>">     
                            <?php } ?>                                                        
                        <?php } ?>
                        </tbody>
                    </table>                    
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
                        <button type="button" class="btn btn-danger type-btn-save" onclick="getUrl('<?php echo base_url(); ?>articles')">Cancelar</button>                                            
                    <?php } else { ?>
                        <button type="button" class="btn btn-success type-btn-save" onclick="getUrl('<?php echo base_url(); ?>articles')">Volver</button>                                            
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
/* End of file articles_edit_view.php */
/* Location: ./application/views/articles/articles_edit_view.php */