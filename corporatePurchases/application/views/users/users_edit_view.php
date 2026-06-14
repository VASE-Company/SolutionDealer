<?php 
	$disabled = "";
	if (!$allowSave) {
		$disabled = ' disabled="disabled" ';
        $rolesDisabled = ' disabled="disabled" ';
	} else {
        $rolesDisabled = ($allowModifyRole?'':' disabled="disabled" ');
    }		
    if ($user['id'] > 0) {
        $id = $user['id']; 
    } else {
        $id = set_value('id',$user['id']);
    }
?>
<div class="card card-secondary">
    <div class="card-body">
        <form class="form-horizontal" action="<?php echo base_url(); ?>users/save" method="post" accept-charset="utf-8" id="frmData" name="frmData" role="form" onsubmit="return preSaveEditUser();">           
           <div class="form-group row">
                <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
                </div>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-5 text-center">
                    <img src="<?php echo base_url().$user['image']."?".filemtime($user['image']); ?>" class="user-edit-img">                  
                </div>
            </div>
            <div class="form-group row">
                <label for="lastName" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Apellido:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-5">
                    <input id="lastName" name="lastName" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("lastName"); ?>" <?php echo errorTitle("lastName"); ?> autocomplete="off" maxlength="50" placeholder="Apellido" required="1" autofocus="1" value="<?php echo set_value('lastName',$user['lastName']); ?>" <?php echo $disabled; ?>>                    
                </div>                    
            </div>
            <div class="form-group row">
                <label for="firstName" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Nombres:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-5">
                    <input id="firstName" name="firstName" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("firstName"); ?>" <?php echo errorTitle("firstName"); ?> autocomplete="off" maxlength="50" placeholder="Nombres" required="1" value="<?php echo set_value('firstName',$user['firstName']); ?>" <?php echo $disabled; ?>>                    
                </div>                    
            </div>            
            <div class="form-group row">
                <label for="username" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Usuario:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-4">
                    <input type="email" id="username" name="username" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("username"); ?>" <?php echo errorTitle("username"); ?> maxlength="50" autocomplete="off" placeholder="Usuario" required="1" value="<?php echo set_value('username',$user['username']); ?>" <?php echo $disabled; ?>>                    
                </div>                    
            </div>
            <div class="form-group row">
                <label for="password" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Password:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-4">
                    <div class="input-group">
                        <input type="password" id="password" name="password" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("password"); ?>" <?php echo errorTitle("password"); ?> maxlength="15" autocomplete="off" placeholder="Password" value="<?php echo set_value('password',""); ?>" <?php echo $disabled; ?>>                
                        <span class="input-group-append">
                            <button type="button" class="btn btn-default btn-sm" onclick="showPassword('password')">
                                <i class="fas fa-eye" id="passwordIcon"></i>
                            </button>
                        </span>                    
                    </div>                                             
                </div>
            </div>  
            <div class="form-group row">
                <label for="cellphone" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Celular:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-4">
                    <input id="cellphone" name="cellphone" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("cellphone"); ?>" <?php echo errorTitle("cellphone"); ?> autocomplete="off" maxlength="15" placeholder="Celular" value="<?php echo set_value('cellphone',$user['cellphone']); ?>" <?php echo $disabled; ?>>                    
                </div>                    
            </div>
            <?php if ($id <= 0) { ?>
            <div class="form-group row">
                <label for="companyId" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Empresa:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-7">                        
                    <select id="companyId" name="companyId" class="form-control form-control-sm <?php echo errorClass("branchOfficeId"); ?>" <?php echo errorTitle("branchOfficeId"); ?> required="1" <?php echo $disabled; ?> style="width:auto;" onchange="selectCompanyUserEdit()">                            
                        <?php 
                            if (validation_errors() != "" || (isset($error) && $error != "")) {
                                $selected = set_select('companyId', $user['companyId']);
                            } else {
                                $selected = ($user['companyId'] == 0?' selected="selected" ':'');                  
                            }
                        ?>
                        <option value="" <?php echo $selected; ?>>[Seleccionar]</option>
                        <?php 
                            for($i=0; $i < count($companies); $i++) {
                                if (validation_errors() != "" || (isset($error) && $error != "")) {
                                    $selected = set_select('companyId', $companies[$i]['id']);
                                } else {
                                    $selected = ($user['companyId'] == $companies[$i]['id']?' selected="selected" ':'');                  
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
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-4">
                    <input id="companyDescription" name="companyDescription" class="form-control form-control-sm noEnterMyApp" maxlength="50" autocomplete="off" value="<?php echo $user['companyDescription']; ?>" disabled="disabled">                    
                    <input type="hidden" id="companyId" name="companyId" value="<?php echo $user['companyId']; ?>">  
                </div>                    
            </div>    
            <?php } ?>     
            <div class="form-group row">
                <label for="branchOfficeId" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Sucursal:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-7">                        
                    <select id="branchOfficeId" name="branchOfficeId" class="form-control form-control-sm <?php echo errorClass("branchOfficeId"); ?>" <?php echo errorTitle("branchOfficeId"); ?> required="1" <?php echo $disabled; ?> style="width:auto;" onchange="selectBranchOfficeUserEdit()">                            
                        <?php 
                            if (validation_errors() != "" || (isset($error) && $error != "")) {
                                $selected = set_select('branchOfficeId', $user['branchOfficeId']);
                            } else {
                                $selected = ($user['branchOfficeId'] == 0?' selected="selected" ':'');                  
                            }
                        ?>
                        <option value="" <?php echo $selected; ?>>[Seleccionar]</option>
                        <?php 
                            for($i=0; $i < count($branchOffices); $i++) {
                                if (validation_errors() != "" || (isset($error) && $error != "")) {
                                    $selected = set_select('branchOfficeId', $branchOffices[$i]['id']);
                                } else {
                                    $selected = ($user['branchOfficeId'] == $branchOffices[$i]['id']?' selected="selected" ':'');                  
                                }
                        ?>
                        <option value="<?php echo $branchOffices[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo $branchOffices[$i]['description'];?></option>
                        <?php
                            }
                        ?>
                    </select>                                            
                </div>
            </div>          
            <div class="form-group row">
                <label for="sectorId" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Sector:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-7">                        
                    <select id="sectorId" name="sectorId" class="form-control form-control-sm <?php echo errorClass("branchOfficeId"); ?>" <?php echo errorTitle("branchOfficeId"); ?> required="1" <?php echo $disabled; ?> style="width:auto;" required="1">                            
                        <?php 
                            if (validation_errors() != "" || (isset($error) && $error != "")) {
                                $selected = set_select('sectorId', $user['sectorId']);
                            } else {
                                $selected = ($user['sectorId'] == 0?' selected="selected" ':'');                  
                            }
                        ?>
                        <option value="" <?php echo $selected; ?>>[Seleccionar]</option>
                        <?php 
                            for($i=0; $i < count($sectors); $i++) {
                                if (validation_errors() != "" || (isset($error) && $error != "")) {
                                    $selected = set_select('sectorId', $sectors[$i]['id']);
                                } else {
                                    $selected = ($user['sectorId'] == $sectors[$i]['id']?' selected="selected" ':'');                  
                                }
                        ?>
                        <option value="<?php echo $sectors[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo $sectors[$i]['description'];?></option>
                        <?php
                            }
                        ?>
                    </select>                                            
                </div>
            </div>               
            <div class="form-group row" style="margin-bottom: 10px;">
                <label for="roles" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Roles:</label>
                <div class="col-xs-8 col-sm-8 col-md-4 col-lg-4 table-responsive" style="max-height:265px;padding-left:7.5px;">
                    <table class="table table-bordered table-head-fixed text-nowrap table-sm">
                        <thead>
                            <tr>
                                <th width="30px" class="text-center">
                                    <?php if ($rolesDisabled == "") { ?>
                                    <input type="checkbox" id="allRoles" name="allRoles" value="1" class="noEnterMyApp" onclick="selectRoles()"/>
                                    <?php } ?>
                                </th>                                                                
                                <th class="col-form-label-sm">Rol</th>                                                             
                            </tr>
                        </thead>
                        <?php 
                             if (isset($roles)) { 
                        ?>
                        <tbody>
                            <?php
                                for($i=0; $i < count($roles); $i++) {
                            ?>                                
                            <tr>
                                <?php 
                                    $checked = "";
                                    if (validation_errors() != "" || (isset($error) && $error != "")) {
                                        $checked = set_checkbox('roles[]', $roles[$i]['id']);                               
                                    } else {
                                        if ($roles[$i]['active'] == 1) {
                                            $checked = ' checked="checked" ';
                                        }
                                    }
                                ?>                        
                                <td class="text-center"><input type="checkbox" id="roles[]" name="roles[]" class="rol noEnterMyApp" value="<?php echo $roles[$i]['id']; ?>" <?php echo $checked; ?>  <?php echo $rolesDisabled; ?> /></td>
                                <td class="col-form-label-sm"><?php echo $roles[$i]['description']; ?></td>                                    
                            </tr>
                            <?php } ?>
                        </tbody>
                        <?php } ?>
                    </table>                          
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
                                    $selected = ($user['active'] == $states[$i]['id']?' selected="selected" ':'');                  
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
                        <button type="button" class="btn btn-danger type-btn-save" onclick="getUrl('<?php echo base_url(); ?>users')">Cancelar</button>                                            
                    <?php } else { ?>                        
                        <button type="button" class="btn btn-success type-btn-save" onclick="getUrl('<?php echo base_url(); ?>users')">Volver</button>                                            
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
/* End of file users_edit_view.php */
/* Location: ./application/views/users/users_edit_view.php */