<?php 
    $disabled = "";
    if (!$allowSave) {
        $disabled = ' disabled="disabled" ';
    }
?>
<div class="card card-secondary">
    <div class="card-body">
        <form class="form-horizontal" action="<?php echo base_url(); ?>roles/save" method="post" accept-charset="utf-8" id="frmData" name="frmData" role="form" onsubmit="return preSubmit();">
            <div class="form-group row">
                <label for="description" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-3 text-right">Descripción:</label>
                <div class="col-xs-8 col-sm-8 col-md-8 col-lg-5">
                    <input id="description" name="description" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("description"); ?>" <?php echo errorTitle("description"); ?> maxlength="50" placeholder="Descripción" autocomplete="off" required="1" autofocus="1" value="<?php echo set_value('description',$role['description']); ?>" <?php echo $disabled; ?>>                    
                </div>                    
            </div>
            <div class="form-group row">
                <label for="superiorRoleId" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-3 text-right">Rol Superior:</label>
                <div class="col-xs-8 col-sm-8 col-md-8 col-lg-8">                        
                    <select id="superiorRoleId" name="superiorRoleId" class="form-control form-control-sm <?php echo errorClass("superiorRoleId"); ?>" <?php echo errorTitle("superiorRoleId"); ?> required="1" <?php echo $disabled; ?> style="width:auto;">                          
                        <?php 
                            if (validation_errors() != "" || (isset($error) && $error != "")) {
                                $selected = set_select('superiorRoleId', $role['superiorRoleId']);
                            } else {
                                $selected = ($role['superiorRoleId'] == 0?' selected="selected" ':'');                  
                            }
                        ?>
                        <option value="0" <?php echo $selected; ?>>[Seleccionar]</option>
                        <?php 
                            for($i=0; $i < count($superiorRoles); $i++) {
                                if (validation_errors() != "" || (isset($error) && $error != "")) {
                                    $selected = set_select('superiorRoleId', $superiorRoles[$i]['id']);
                                } else {
                                    $selected = ($role['superiorRoleId'] == $superiorRoles[$i]['id']?' selected="selected" ':'');                   
                                }
                        ?>
                        <option value="<?php echo $superiorRoles[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo $superiorRoles[$i]['description'];?></option>
                        <?php
                            }
                        ?>
                    </select>                                            
                </div>
            </div>
            <div class="form-group row">
                <label for="permissions" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-3 text-right">Permisos:</label>
                <div class="col-xs-8 col-sm-8 col-md-8 col-lg-8 table-responsive" style="max-height:265px;padding-left:7.5px;">
                    <table class="table table-bordered table-head-fixed text-nowrap table-sm">
                        <thead>
                            <tr>
                                <th width="30px" class="text-center">
                                    <?php if ($disabled == "") { ?>
                                    <input type="checkbox" id="allPermissions" name="allPermissions" value="1" class="noEnterMyApp" onclick="selectPermissions()"/>
                                    <?php } ?>
                                </th>                                                                
                                <th class="col-form-label-sm">Acción</th>                                                             
                            </tr>
                        </thead>
                        <?php                             
                            if (isset($permissions)) { 
                        ?>
                        <tbody>
                            <?php
                                for($i=0; $i < count($permissions); $i++) {
                            ?>                                
                            <tr>
                                <?php 
                                    $checked = "";
                                    if (validation_errors() != "" || (isset($error) && $error != "")) {
                                        $checked = set_checkbox('permissions[]', $permissions[$i]['id']);                               
                                    } else {
                                        if ($permissions[$i]['active'] == 1) {
                                            $checked = ' checked="checked" ';
                                        }
                                    }
                                ?>                        
                                <td class="text-center"><input type="checkbox" id="permissions[]" name="permissions[]" class="permission noEnterMyApp" value="<?php echo $permissions[$i]['id']; ?>" <?php echo $checked; ?>  <?php echo $disabled; ?> /></td>
                                <td class="col-form-label-sm"><?php echo $permissions[$i]['description']; ?></td>                                    
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
             <div class="form-group row">           
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">                   
                    <?php if ($allowSave) { ?>
                        <button type="submit" id="btnSend" name="btnSend" class="btn btn-success type-btn-save">Guardar</button>                                            
                        <button type="button" class="btn btn-danger type-btn-save" onclick="getUrl('<?php echo base_url(); ?>roles')">Cancelar</button>                                            
                    <?php } else { ?>                        
                        <button type="button" class="btn btn-success type-btn-save" onclick="getUrl('<?php echo base_url(); ?>roles')">Volver</button>                                            
                    <?php } ?>
                </div>
            </div>            
            <input type="hidden" id="id" name="id" value="<?php echo set_value('id',$role['id']); ?>">
        </form>
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->
<?php 
/* End of file roles_edit_view.php */
/* Location: ./application/views/roles/roles_edit_view.php */