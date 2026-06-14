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
        <form class="form-horizontal" action="<?php echo base_url(); ?>branchOffices/save" method="post" accept-charset="utf-8" id="frmData" name="frmData" role="form" onsubmit="return preSaveEditBranchOffice();">
            <div class="form-group row">
                <label for="description" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Descripción:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-5">
                    <input id="description" name="description" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("description"); ?>" <?php echo errorTitle("description"); ?> autocomplete="off" maxlength="100" placeholder="Descripción" required="1" autofocus="1" value="<?php echo set_value('description',$branchOffice['description']); ?>" <?php echo $disabled; ?>>                    
                </div>                    
            </div>
            <div class="form-group row" style="margin-bottom: 10px;">
                <label for="emailNotices" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Mail Avisos Presupuestos:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-5">
                    <input id="emailNotices" name="emailNotices" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("emailNotices"); ?>" <?php echo errorTitle("emailNotices"); ?> autocomplete="off" maxlength="150" placeholder="Lista de Emails separado por ','" value="<?php echo set_value('emailNotices',$branchOffice['emailNotices']); ?>" <?php echo $disabled; ?>>                                        
                </div>                    
            </div>   
            <div class="form-group row" style="margin-top: 0px;">
                <label class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right"></label>
                <div class="col-xs-8 col-sm-8 col-md-8 col-lg-8" style="font-size:12px;">                    
                    * Mails que no pertenezcan a usuarios registrados. Se les enviará un aviso cuando el presupuesto sea RECHAZADO por MUY COSTOSO.
                </div>                    
            </div>   
            <?php if ($allowReassignCompany) { ?>
            <div class="form-group row" style="margin-bottom: 10px;">
                <label for="allowReassignCompany" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Permite Reasignar Presupuestos:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-7">  
                    <select id="allowReassignCompany" name="allowReassignCompany" class="form-control form-control-sm <?php echo errorClass("allowReassignCompany"); ?>" <?php echo errorTitle("allowReassignCompany"); ?> <?php echo $disabled; ?> style="width:auto;">                                                  
                        <?php 
                            for($i=0; $i < count($allowReassign); $i++) {
                                if (validation_errors() != "" || (isset($error) && $error != "")) {
                                    $selected = set_select('allowReassignCompany', $allowReassign[$i]['id']);
                                } else {
                                    $selected = ($branchOffice['allowReassignCompany'] == $allowReassign[$i]['id']?' selected="selected" ':'');                  
                                }
                        ?>
                        <option value="<?php echo $allowReassign[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo $allowReassign[$i]['description'];?></option>
                        <?php
                            }
                        ?>
                    </select>                                            
                </div>
            </div>   
            <div class="form-group row" style="margin-top: 0px;">
                <label class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right"></label>
                <div class="col-xs-8 col-sm-8 col-md-8 col-lg-8" style="font-size:12px;">                    
                    * Reasigna presupuestos RECHAZADOS por MUY COSTOSOS o SIN STOCK a la empresa '<?php echo strtoupper($reassignToCompany); ?>'.
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
            <div class="form-group row">
                <label for="budgetHeader" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Cabecera Ppto:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-7">
                    <textarea id="budgetHeaderShow" name="budgetHeaderShow" class="form-control form-control-sm <?php echo errorClass("budgetHeader"); ?>" rows="4" autocomplete="off" <?php echo errorTitle("budgetHeader"); ?> <?php echo $disabled; ?>></textarea>
                    <input type="hidden" id="budgetHeader" name="budgetHeader" value="<?php echo set_value('budgetHeader',$branchOffice['budgetHeader']); ?>">                                                                                     
                </div>
            </div>
            <div class="form-group row">
                <label for="budgetFooter" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Pie Ppto:</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-7">
                    <textarea id="budgetFooterShow" name="budgetFooterShow" class="form-control form-control-sm <?php echo errorClass("budgetFooter"); ?>" rows="4" autocomplete="off" <?php echo errorTitle("budgetFooter"); ?> <?php echo $disabled; ?>></textarea>
                    <input type="hidden" id="budgetFooter" name="budgetFooter" value="<?php echo set_value('budgetFooter',$branchOffice['budgetFooter']); ?>">                                                                                     
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