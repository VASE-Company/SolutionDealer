<?php 
	$disabled = "";
	if (!$allowSave) {
		$disabled = ' disabled="disabled" ';
	}		
    if ($systemMessage['id'] > 0) {
        $id = $systemMessage['id']; 
    } else {
        $id = set_value('id',$systemMessage['id']);
    }
?>
<div class="card card-secondary">
    <div class="card-body">
        <form class="form-horizontal" action="<?php echo base_url(); ?>systemMessages/save" method="post" accept-charset="utf-8" id="frmData" name="frmData" role="form" onsubmit="return preSaveEditSystemMessage();">
            <?php if ($id > 0) { ?>
            <div class="form-group row">
                <label for="date" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Fecha:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-5">
                    <input id="date" name="date" class="form-control form-control-sm noEnterMyApp" value="<?php echo dateFormat($systemMessage['date'],true); ?>" disabled="disabled">                    
                </div>                    
            </div>
            <?php } ?>
            <div class="form-group row">
                <label for="title" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Título:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-5">
                    <input id="title" name="title" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("title"); ?>" <?php echo errorTitle("title"); ?> autocomplete="off" maxlength="50" placeholder="Título" required="1" autofocus="1" value="<?php echo set_value('title',$systemMessage['title']); ?>" <?php echo $disabled; ?>>                    
                </div>                    
            </div> 
            <div class="form-group row">
                <label for="message" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Mensaje:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-7">
                    <textarea id="messageShow" name="messageShow" class="form-control form-control-sm" rows="4" autocomplete="off" <?php echo errorClass("message"); ?> <?php echo errorTitle("message"); ?> <?php echo $disabled; ?>></textarea>
                    <input type="hidden" id="message" name="message" value="<?php echo set_value('message',$systemMessage['message']); ?>">                                                                                     
                </div>
            </div> 
            <div class="form-group row">
                <label for="notification" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Notificación:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-7">                    
                    <?php 
                        $checked = "";
                        if (validation_errors() != "" || (isset($error) && $error != "")) {
                            $checked = set_checkbox('notification', '1');                             
                        } else {
                            if ($systemMessage['notification'] == 1) {
                                $checked = ' checked="checked" ';
                            }
                        }
                    ?>
                    <input type="checkbox" id="notification" name="notification" class="noEnterMyApp <?php echo errorClass("notification"); ?>" <?php echo errorTitle("notification"); ?> value="1" <?php echo $checked; ?> <?php echo $disabled; ?> style="float:left;margin-top:10px;" />                                            
                    <label for="email" class="col-form-label col-form-label-sm" style="margin-left:50px;float:left;">Email:</label>
                    <?php 
                        $checked = "";
                        if (validation_errors() != "" || (isset($error) && $error != "")) {
                            $checked = set_checkbox('email', '1');                             
                        } else {
                            if ($systemMessage['email'] == 1) {
                                $checked = ' checked="checked" ';
                            }
                        }
                    ?>
                    <input type="checkbox" id="email" name="email" class="noEnterMyApp <?php echo errorClass("email"); ?>" <?php echo errorTitle("email"); ?> value="1" <?php echo $checked; ?> <?php echo $disabled; ?> style="float:left;margin-top:10px;margin-left:15px;" />                                            
                </div>                    
            </div>     
            <div class="form-group row" style="margin-bottom: 0px;">
                <label for="roles" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Enviar a:</label>
                <div class="col-xs-8 col-sm-8 col-md-4 col-lg-4 table-responsive" style="max-height:265px;padding-left:7.5px;">
                    <table class="table table-bordered table-head-fixed text-nowrap table-sm">
                        <thead>
                            <tr>
                                <th width="30px" class="text-center">
                                    <?php if ($disabled == "") { ?>
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
                                <td class="text-center"><input type="checkbox" id="roles[]" name="roles[]" class="rol noEnterMyApp" value="<?php echo $roles[$i]['id']; ?>" <?php echo $checked; ?>  <?php echo $disabled; ?> /></td>
                                <td class="col-form-label-sm"><?php echo $roles[$i]['description']; ?></td>                                    
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
                        <button type="button" class="btn btn-danger type-btn-save" onclick="getUrl('<?php echo base_url(); ?>systemMessages')">Cancelar</button>                                            
                    <?php } else { ?>                        
                        <button type="button" class="btn btn-success type-btn-save" onclick="getUrl('<?php echo base_url(); ?>systemMessages')">Volver</button>                                            
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
/* End of file systemMessages_edit_view.php */
/* Location: ./application/views/systemMessages/systemMessages_edit_view.php */