<?php 
	$disabled = "";
	if (!$allowSave) {
		$disabled = ' disabled="disabled" ';
	}		
    if ($branchOffice['id'] > 0) {
        $id = $branchOffice['id']; 
    } else {
        $id = set_value('id',$branchOffice['id']);
    }
?>
<div class="card card-secondary">
    <div class="card-body">
        <form class="form-horizontal" action="<?php echo base_url(); ?>branchOffices/save" method="post" accept-charset="utf-8" id="frmData" name="frmData" role="form" onsubmit="return preSubmit();">
            <div class="form-group row">
                <label for="description" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Descripción:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-5">
                    <input id="description" name="description" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("description"); ?>" <?php echo errorTitle("description"); ?> autocomplete="off" maxlength="100" placeholder="Descripción" required="1" autofocus="1" value="<?php echo set_value('description',$branchOffice['description']); ?>" <?php echo $disabled; ?>>                    
                </div>                    
            </div>    
            <?php if ($id <= 0) { ?>
            <div class="form-group row">
                <label for="companyId" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Empresa:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-7">                        
                    <select id="companyId" name="companyId" class="form-control form-control-sm <?php echo errorClass("branchOfficeId"); ?>" <?php echo errorTitle("branchOfficeId"); ?> required="1" <?php echo $disabled; ?> style="width:auto;" onchange="selectCompanyUserEdit()">                            
                        <?php 
                            if (validation_errors() != "" || (isset($error) && $error != "")) {
                                $selected = set_select('companyId', $branchOffice['companyId']);
                            } else {
                                $selected = ($branchOffice['companyId'] == 0?' selected="selected" ':'');                  
                            }
                        ?>
                        <option value="" <?php echo $selected; ?>>[Seleccionar]</option>
                        <?php 
                            for($i=0; $i < count($companies); $i++) {
                                if (validation_errors() != "" || (isset($error) && $error != "")) {
                                    $selected = set_select('companyId', $companies[$i]['id']);
                                } else {
                                    $selected = ($branchOffice['companyId'] == $companies[$i]['id']?' selected="selected" ':'');                  
                                }
                        ?>
                        <option value="<?php echo $companies[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo $companies[$i]['description'];?></option>
                        <?php
                            }
                        ?>
                    </select>                                            
                </div>
            </div>    
            <?php } else { ?>
            <div class="form-group row">
                <label for="companyDescription" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Empresa:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-5">
                    <input id="companyDescription" name="companyDescription" class="form-control form-control-sm noEnterMyApp" maxlength="50" autocomplete="off" value="<?php echo $branchOffice['companyDescription']; ?>" disabled="disabled">                    
                    <input type="hidden" id="companyId" name="companyId" value="<?php echo $branchOffice['companyId']; ?>">  
                </div>                    
            </div>    
            <?php } ?>                            
            <div class="form-group row">
                <label for="active" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Activo:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-7">  
                    <select id="active" name="active" class="form-control form-control-sm <?php echo errorClass("active"); ?>" <?php echo errorTitle("active"); ?> <?php echo $disabled; ?> style="width:auto;">                                                  
                        <?php 
                            for($i=0; $i < count($states); $i++) {
                                if (validation_errors() != "" || (isset($error) && $error != "")) {
                                    $selected = set_select('active', $states[$i]['id']);
                                } else {
                                    $selected = ($branchOffice['active'] == $states[$i]['id']?' selected="selected" ':'');                  
                                }
                        ?>
                        <option value="<?php echo $states[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo $states[$i]['description'];?></option>
                        <?php
                            }
                        ?>
                    </select>                                            
                </div>
            </div>   
            <div class="form-group row" style="margin-top:20px;">
                <label for="sectors" class="col-form-label col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Sectores</label>
                <div class="col-xs-8 col-sm-7 col-md-6 col-lg-4 table-responsive" style="max-height:265px;padding-left:7.5px;">
                    <table class="table table-bordered table-head-fixed text-nowrap table-sm">
                        <thead>
                            <tr>
                                <th width="30px" class="text-center">
                                    <?php if ($disabled == "") { ?>
                                    <input type="checkbox" id="allSectors" name="allSectors" value="1" class="noEnterMyApp" onclick="selectAllSectors()"/>
                                    <?php } ?>
                                </th>
                                <th class="col-form-label-sm">Sector</th>                                                                   
                            </tr>
                        </thead>
                        <?php 
                            if (isset($sectors)) { 
                        ?>
                        <tbody>
                            <?php
                                for($i=0; $i < count($sectors); $i++) {
                            ?>                                
                            <tr>
                                <?php 
                                    $checked = "";
                                    if (validation_errors() != "" || (isset($error) && $error != "")) {
                                        $checked = set_checkbox('sectors[]', $sectors[$i]['id']);                               
                                    } else {
                                        if ($sectors[$i]['active'] == 1) {
                                            $checked = ' checked="checked" ';
                                        }
                                    }
                                ?>                        
                                <td class="text-center"><input type="checkbox" id="sectors[]" name="sectors[]" class="sector noEnterMyApp" value="<?php echo $sectors[$i]['id']; ?>" <?php echo $checked; ?>  <?php echo $disabled; ?> /></td>
                                <td class="col-form-label-sm"><?php echo $sectors[$i]['description']; ?></td>                                                                    
                            </tr>
                            <?php } ?>
                        </tbody>
                        <?php } ?>
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
                        <button type="button" class="btn btn-danger type-btn-save" onclick="getUrl('<?php echo base_url(); ?>branchOffices')">Cancelar</button>                                            
                    <?php } else { ?>                        
                        <button type="button" class="btn btn-success type-btn-save" onclick="getUrl('<?php echo base_url(); ?>branchOffices')">Volver</button>
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
/* End of file branchOffices_edit_view.php */
/* Location: ./application/views/branchOffices/branchOffices_edit_view.php */