<?php 
	$disabled = "";
	if (!$allowSave) {
		$disabled = ' disabled="disabled" ';
	}		
?>
<div class="card card-secondary">
    <div class="card-body">
        <form class="form-horizontal" action="<?php echo base_url(); ?>users/saveMyProfile" method="post" accept-charset="utf-8" id="frmData" name="frmData" role="form" onsubmit="return preSaveEditMyProfile();" enctype="multipart/form-data">
            <div class="form-group row">
                <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
                </div>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-5 text-center">                               
                    <div class="upload-file-view">
                        <span class="upload-file-remove" id="btnDeleteUserImage">X</span>
                        <img id="previewUserImage" name="previewUserImage" src="<?php echo base_url().$user['image']."?".filemtime($user['image']); ?>" class="user-edit-img">                  
                    </div>
                    <div class="upload-file-panel">
                        <div class="upload-file-btn-outer" id="uploadFileBtnOuter">
                            <div class="upload-file-btn-upload">
                                <input type="file" id="fileUserImage" name="fileUserImage">
                                Subir Imagen
                            </div>
                            <div class="upload-file-processing-bar"></div>                            
                        </div>
                    </div>
                    <div class="upload-file-error-msg" id="uploadFileErrorMsg" ></div>
                    <input type="hidden" id="deleteUsrImage" name="deleteUsrImage" value="0">  
                </div>                
            </div>
            <div class="form-group row">
                <label for="lastName" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Apellido:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-5">
                    <input id="lastName" name="lastName" class="form-control form-control-sm noEnterMyApp" value="<?php echo $user['lastName']; ?>" disabled="disabled">                    
                </div>                    
            </div>
            <div class="form-group row">
                <label for="firstName" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Nombres:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-5">
                    <input id="firstName" name="firstName" class="form-control form-control-sm noEnterMyApp" value="<?php echo $user['firstName']; ?>" disabled="disabled">                                        
                </div>                    
            </div>
            <div class="form-group row">
                <label for="username" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Usuario:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-4">
                    <input id="username" name="username" class="form-control form-control-sm noEnterMyApp" value="<?php echo $user['username']; ?>" disabled="disabled">                    
                </div>                    
            </div>            
            <div class="form-group row">
                <label for="cellphone" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Celular:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-4">
                    <input id="cellphone" name="cellphone" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("cellphone"); ?>" <?php echo errorTitle("cellphone"); ?> autocomplete="off" maxlength="15" placeholder="Celular" value="<?php echo set_value('cellphone',$user['cellphone']); ?>" <?php echo $disabled; ?>>                    
                </div>                    
            </div>
            <div class="form-group row">
                <label for="companyDescription" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Empresa:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-4">
                    <input id="companyDescription" name="companyDescription" class="form-control form-control-sm noEnterMyApp" value="<?php echo $user['companyDescription']; ?>" disabled="disabled">                    
                </div>                    
            </div>              
            <?php if ($allowSave && $allowEditBranchOffice) { ?>         
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
            <?php } else { ?>     
            <div class="form-group row">
                <label for="branchOfficeDescription" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Sucursal:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-4">
                    <input id="branchOfficeDescription" name="branchOfficeDescription" class="form-control form-control-sm noEnterMyApp" value="<?php echo $user['branchOfficeDescription']; ?>" disabled="disabled">                    
                    <input type="hidden" id="branchOfficeId" name="branchOfficeId" value="<?php echo $user['branchOfficeId']; ?>">  
                </div>                    
            </div>               
            <?php } ?>             
            <?php if ($allowSave && $allowEditSector) { ?>         
            <div class="form-group row">
                <label for="sectorId" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Sector:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-7">                        
                    <select id="sectorId" name="sectorId" class="form-control form-control-sm <?php echo errorClass("branchOfficeId"); ?>" <?php echo errorTitle("branchOfficeId"); ?> required="1" <?php echo $disabled; ?> style="width:auto;">                            
                        <?php 
                            if (validation_errors() != "" || (isset($error) && $error != "")) {
                                $selected = set_select('sectorId', $user['sectorId']);
                            } else {
                                $selected = ($user['sectorId'] == 0?' selected="selected" ':'');                  
                            }
                        ?>
                        <option value="0" <?php echo $selected; ?>>[Seleccionar]</option>
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
            <?php } else { ?>     
            <div class="form-group row">
                <label for="username" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Sector:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-4">
                    <input id="username" name="username" class="form-control form-control-sm noEnterMyApp" value="<?php echo $user['sectorDescription']; ?>" disabled="disabled">                    
                </div>                    
            </div>               
            <?php } ?> 
            <?php if ($allowSave) { ?>
             <div class="form-group row">
                <label for="newPassword" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Nueva Password:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-4">
                    <div class="input-group">
                        <input type="password" id="newPassword" name="newPassword" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("newPassword"); ?>" <?php echo errorTitle("newPassword"); ?> maxlength="15" autocomplete="off" placeholder="Nueva Password" value="" <?php echo $disabled; ?>>                
                        <span class="input-group-append">
                            <button type="button" class="btn btn-default btn-sm" onclick="showPassword('newPassword')">
                                <i class="fas fa-eye" id="passwordIcon"></i>
                            </button>
                         </span>
                    
                    </div>                                             
                </div>
            </div>     
            <div class="form-group row">
                <label for="confirmationPassword" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Conf. Password:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-4">
                    <div class="input-group">
                        <input type="password" id="confirmationPassword" name="confirmationPassword" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("confirmationPassword"); ?>" <?php echo errorTitle("confirmationPassword"); ?> maxlength="15" autocomplete="off" placeholder="Conf. Password" value="" <?php echo $disabled; ?>>                
                        <span class="input-group-append">
                            <button type="button" class="btn btn-default btn-sm" onclick="showPassword('confirmationPassword')">
                                <i class="fas fa-eye" id="passwordIcon"></i>
                            </button>
                         </span>
                    
                    </div>                                             
                </div>
            </div>    
            <?php } ?>                     
            <?php if (isset($error) && $error <> "")  { ?>
            <div class="form-group row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
                <?php echo errorFormat($error); ?>	
                </div>                                        
            </div>
            <?php } ?> 
            <?php if (isset($message) && $message <> "")  { ?>
            <div class="form-group row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
                <?php echo messageFormat('success',$message); ?>  
                </div>                                        
            </div>
            <?php } ?>                                                    
            <div class="form-group row" style="margin-top:20px;">           
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">      
                    <?php if ($allowSave) { ?>
                        <button type="submit" id="btnSend" name="btnSend" class="btn btn-success type-btn-save">Guardar</button>                                            
                        <button type="button" class="btn btn-danger type-btn-save" onclick="getUrl('<?php echo base_url(); ?>main')">Cancelar</button>                                            
                    <?php } else { ?>                        
                        <button type="button" class="btn btn-success type-btn-save" onclick="getUrl('<?php echo base_url(); ?>main')">Volver</button>                                            
                    <?php } ?>
                </div>
            </div>
            <input type="hidden" id="id" name="id" value="<?php echo set_value('id',$user['id']); ?>">                   
        </form>
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->     
<?php 
/* End of file users_edit_view.php */
/* Location: ./application/views/users/users_edit_view.php */