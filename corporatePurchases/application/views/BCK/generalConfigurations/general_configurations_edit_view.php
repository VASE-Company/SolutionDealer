<?php 
	$disabled = "";
	if (!$allowSave) {
		$disabled = ' disabled="disabled" ';
	}		
?>
<div class="card card-secondary">
    <div class="card-body">
        <form class="form-horizontal" action="<?php echo base_url(); ?>generalConfigurations/save" method="post" accept-charset="utf-8" id="frmData" name="frmData" role="form" onsubmit="return preSubmit();">            
            <div class="form-group row">
                <label for="priceUpdatesDays" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Actualización de Precios (días):</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
                    <input type="number" id="priceUpdatesDays" name="priceUpdatesDays" min="-1" step="1" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("priceUpdatesDays"); ?>" <?php echo errorTitle("priceUpdatesDays"); ?> autocomplete="off" maxlength="3" placeholder="" required="1" value="<?php echo ((int)set_value('priceUpdatesDays',$generalConfiguration['priceUpdatesDays']) >= 0? set_value('priceUpdatesDays',$generalConfiguration['priceUpdatesDays']):"-1"); ?>" style="width:90px;float:left;" <?php echo $disabled; ?>>                    
                </div>                                
                <label for="priceUpdatesDaysReminder" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-2 text-right">Avisos Recordatorios (días):</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
                    <input type="number" id="priceUpdatesDaysReminder" name="priceUpdatesDaysReminder" min="-1" step="1" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("priceUpdatesDaysReminder"); ?>" <?php echo errorTitle("priceUpdatesDaysReminder"); ?> autocomplete="off" maxlength="3" placeholder="" required="1" value="<?php echo ((int)set_value('priceUpdatesDaysReminder',$generalConfiguration['priceUpdatesDaysReminder']) >= 0? set_value('priceUpdatesDaysReminder',$generalConfiguration['priceUpdatesDaysReminder']):"-1"); ?>" style="width:90px;float:left;" <?php echo $disabled; ?>>                    
                </div>                    
            </div> 
            <div class="form-group row">
                <label for="callBudgetsDays" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Avisar Presupuestos a Llamar (días):</label>
                <div class="col-xs-8 col-sm-7 col-md-7 col-lg-5">
                    <input type="number" id="callBudgetsDays" name="callBudgetsDays" min="-1" step="1" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("callBudgetsDays"); ?>" <?php echo errorTitle("callBudgetsDays"); ?> autocomplete="off" maxlength="3" placeholder="" required="1" value="<?php echo ((int)set_value('callBudgetsDays',$generalConfiguration['callBudgetsDays']) >= 0? set_value('callBudgetsDays',$generalConfiguration['callBudgetsDays']):"-1"); ?>" style="width:90px;float:left;" <?php echo $disabled; ?>>                    
                </div>                    
            </div> 
            <div class="form-group row">
                <label for="pendingBillsDays" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Facturas Impagas (días):</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
                    <input type="number" id="pendingBillsDays" name="pendingBillsDays" min="-1" step="1" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("pendingBillsDays"); ?>" <?php echo errorTitle("pendingBillsDays"); ?> autocomplete="off" maxlength="3" placeholder="" required="1" value="<?php echo ((int)set_value('pendingBillsDays',$generalConfiguration['pendingBillsDays']) >= 0? set_value('pendingBillsDays',$generalConfiguration['pendingBillsDays']):"-1"); ?>" style="width:90px;float:left;" <?php echo $disabled; ?>>                    
                </div>                                
                <label for="pendingBillsDaysReminder" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-2 text-right">Avisos Recordatorios (días):</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
                    <input type="number" id="pendingBillsDaysReminder" name="pendingBillsDaysReminder" min="-1" step="1" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("pendingBillsDaysReminder"); ?>" <?php echo errorTitle("pendingBillsDaysReminder"); ?> autocomplete="off" maxlength="3" placeholder="" required="1" value="<?php echo ((int)set_value('pendingBillsDaysReminder',$generalConfiguration['pendingBillsDaysReminder']) >= 0? set_value('pendingBillsDaysReminder',$generalConfiguration['pendingBillsDaysReminder']):"-1"); ?>" style="width:90px;float:left;" <?php echo $disabled; ?>>                    
                </div>                    
            </div> 
            <div class="form-group row">
                <label for="allowHideCodeArticleExportMail" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Permite ocultar Cód. Art. en Pptos.:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
                    <select id="allowHideCodeArticleExportMail" name="allowHideCodeArticleExportMail" class="form-control form-control-sm <?php echo errorClass("allowHideCodeArticleExportMail"); ?>" <?php echo errorTitle("allowHideCodeArticleExportMail"); ?> <?php echo $disabled; ?> style="width:auto;">                                                  
                        <?php 
                            for($i=0; $i < count($lstBoolean); $i++) {
                                if (validation_errors() != "" || (isset($error) && $error != "")) {
                                    $selected = set_select('allowHideCodeArticleExportMail', $lstBoolean[$i]['id']);
                                } else {
                                    $selected = ($generalConfiguration['allowHideCodeArticleExportMail'] == $lstBoolean[$i]['id']?' selected="selected" ':'');                  
                                }
                        ?>
                        <option value="<?php echo $lstBoolean[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo $lstBoolean[$i]['description'];?></option>
                        <?php
                            }
                        ?>
                    </select>         
                </div>                                                                 
            </div> 
            <div class="form-group row">
                <label for="reassignToCompanyId" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Reasigna Pptos. a Cía.:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
                    <select id="reassignToCompanyId" name="reassignToCompanyId" class="form-control form-control-sm <?php echo errorClass("reassignToCompanyId"); ?>" <?php echo errorTitle("reassignToCompanyId"); ?> required="1" <?php echo $disabled; ?> style="width:auto;" onchange="selectReassignToCompanyGralConfigEdit()">                            
                        <?php 
                            if (validation_errors() != "" || (isset($error) && $error != "")) {
                                $selected = set_select('reassignToCompanyId', $generalConfiguration['reassignToCompanyId']);
                            } else {
                                $selected = ($generalConfiguration['reassignToCompanyId'] == 0?' selected="selected" ':'');                  
                            }
                        ?>
                        <option value="0" <?php echo $selected; ?>>[No reasigna]</option>
                        <?php 
                            for($i=0; $i < count($companies); $i++) {
                                if (validation_errors() != "" || (isset($error) && $error != "")) {
                                    $selected = set_select('reassignToCompanyId', $companies[$i]['id']);
                                } else {
                                    $selected = ($generalConfiguration['reassignToCompanyId'] == $companies[$i]['id']?' selected="selected" ':'');                  
                                }
                        ?>
                        <option value="<?php echo $companies[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo $companies[$i]['name'];?></option>
                        <?php
                            }
                        ?>
                    </select>         
                </div>                                                                
            </div> 
            <div class="form-group row">
                <label for="allowReassignFromCompany" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Permite reasignar desde otras Cías.:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
                    <select id="allowReassignFromCompany" name="allowReassignFromCompany" class="form-control form-control-sm <?php echo errorClass("allowReassignFromCompany"); ?>" <?php echo errorTitle("allowReassignFromCompany"); ?> <?php echo $disabled; ?> style="width:auto;" onchange="selectReassignFromCompanyGralConfigEdit()">                                                  
                        <?php 
                            for($i=0; $i < count($lstBoolean); $i++) {
                                if (validation_errors() != "" || (isset($error) && $error != "")) {
                                    $selected = set_select('allowReassignFromCompany', $lstBoolean[$i]['id']);
                                } else {
                                    $selected = ($generalConfiguration['allowReassignFromCompany'] == $lstBoolean[$i]['id']?' selected="selected" ':'');                  
                                }
                        ?>
                        <option value="<?php echo $lstBoolean[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo $lstBoolean[$i]['description'];?></option>
                        <?php
                            }
                        ?>
                    </select>         
                </div>                                                                 
            </div>             
            <div class="form-group row dataReassignFromCompany">
                <label for="defaultReassignBOId" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Reasignar a Sucursal:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
                    <select id="defaultReassignBOId" name="defaultReassignBOId" class="form-control form-control-sm <?php echo errorClass("defaultReassignBOId"); ?>" <?php echo errorTitle("defaultReassignBOId"); ?> required="1" <?php echo $disabled; ?> style="width:auto;" onchange="loadComboAssesorsGralConfigEdit()">                            
                        <?php 
                            if (validation_errors() != "" || (isset($error) && $error != "")) {
                                $selected = set_select('defaultReassignBOId', $generalConfiguration['defaultReassignBOId']);
                            } else {
                                $selected = ($generalConfiguration['defaultReassignBOId'] == 0?' selected="selected" ':'');                  
                            }
                        ?>
                        <option value="0" <?php echo $selected; ?>>[Seleccionar]</option>
                        <?php 
                            for($i=0; $i < count($branchOffices); $i++) {
                                if (validation_errors() != "" || (isset($error) && $error != "")) {
                                    $selected = set_select('defaultReassignBOId', $branchOffices[$i]['id']);
                                } else {
                                    $selected = ($generalConfiguration['defaultReassignBOId'] == $branchOffices[$i]['id']?' selected="selected" ':'');                  
                                }
                        ?>
                        <option value="<?php echo $branchOffices[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo $branchOffices[$i]['description'];?></option>
                        <?php
                            }
                        ?>
                    </select>        
                </div>                                                                 
            </div> 
            <div class="form-group row dataReassignFromCompany">
                <label for="defaultReassignASSId" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Reasignar a Asesor:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
                    <select id="defaultReassignASSId" name="defaultReassignASSId" class="form-control form-control-sm <?php echo errorClass("defaultReassignASSId"); ?>" <?php echo errorTitle("defaultReassignASSId"); ?> <?php echo $disabled; ?> style="width:auto;">                            
                        <?php 
                            if (validation_errors() != "" || (isset($error) && $error != "")) {
                                $selected = set_select('defaultReassignASSId', $generalConfiguration['defaultReassignASSId']);
                            } else {
                                $selected = ($generalConfiguration['defaultReassignASSId'] == 0?' selected="selected" ':'');                  
                            }
                        ?>
                        <option value="0" <?php echo $selected; ?>>[Seleccionar]</option>
                        <?php 
                            for($i=0; $i < count($assessors); $i++) {
                                if (validation_errors() != "" || (isset($error) && $error != "")) {
                                    $selected = set_select('defaultReassignASSId', $assessors[$i]['id']);
                                } else {
                                    $selected = ($generalConfiguration['defaultReassignASSId'] == $assessors[$i]['id']?' selected="selected" ':'');                  
                                }
                        ?>
                        <option value="<?php echo $assessors[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo trim($assessors[$i]['lastName'].", ".$assessors[$i]['firstName']);?></option>
                        <?php
                            }
                        ?>
                    </select>       
                </div>                                                                 
            </div> 
            <div class="form-group row dataReassignFromCompany">
                <label for="defaultReassignWMId" class="col-form-label col-form-label-sm col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right">Reasignar a Resp. Taller:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
                    <select id="defaultReassignWMId" name="defaultReassignWMId" class="form-control form-control-sm <?php echo errorClass("defaultReassignWMId"); ?>" <?php echo errorTitle("defaultReassignWMId"); ?> <?php echo $disabled; ?> style="width:auto;">                            
                        <?php 
                            if (validation_errors() != "" || (isset($error) && $error != "")) {
                                $selected = set_select('defaultReassignWMId', $generalConfiguration['defaultReassignWMId']);
                            } else {
                                $selected = ($generalConfiguration['defaultReassignWMId'] == 0?' selected="selected" ':'');                  
                            }
                        ?>
                        <option value="0" <?php echo $selected; ?>>[Seleccionar]</option>
                        <?php 
                            for($i=0; $i < count($workshopManagers); $i++) {
                                if (validation_errors() != "" || (isset($error) && $error != "")) {
                                    $selected = set_select('defaultReassignWMId', $workshopManagers[$i]['id']);
                                } else {
                                    $selected = ($generalConfiguration['defaultReassignWMId'] == $workshopManagers[$i]['id']?' selected="selected" ':'');                  
                                }
                        ?>
                        <option value="<?php echo $workshopManagers[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo trim($workshopManagers[$i]['lastName'].", ".$workshopManagers[$i]['firstName']);?></option>
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
            <input type="hidden" id="id" name="id" value="<?php echo set_value('id',$generalConfiguration['id']); ?>">                   
        </form>
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->     
<?php 
/* End of file general_configurations_edit_view.php */
/* Location: ./application/views/generalConfigurations/general_configurations_edit_view.php */