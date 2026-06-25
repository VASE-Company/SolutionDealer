<?php 
    $id = $order['id'];        
    $backGet = set_value('backGet',$backGet);         
    
    $disabled = ($allowSave?'':' disabled="disabled" ');  
    $generalDataReadonly = ($allowSave && $allowEditGeneralData?'':' readonly="readonly" ');
    $generalMinimuDataReadonly = ($allowSave && $allowEditMinimunGeneralData?'':' readonly="readonly" ');    
    $detailReadonly = ($allowEditDetail?'':' readonly="readonly" ');     
?>
<div class="card card-secondary">
    <div class="card-body">
        <form class="form-horizontal" action="<?php echo base_url(); ?>orders/save" method="post" accept-charset="utf-8" id="frmData" name="frmData" role="form" onsubmit="return preSaveOrder();">
            <div class="form-group row">                    
                <label for="date" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Fecha:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
                    <input type="date" id="date" name="date" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("date"); ?>" placeholder="--/--/----" <?php echo errorTitle("date"); ?> autocomplete="off" value="<?php echo set_value('date',$order['date']); ?>" style="width:120px;float:left;" readonly="readonly">                    
                </div> 
                <label for="subject" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Asunto:</label>
                <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3"> 
                    <input id="subject" name="subject" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("subject"); ?>" <?php echo errorTitle("subject"); ?> autocomplete="off" maxlength="50"  value="<?php echo set_value('subject',$order['subject']); ?>" <?php echo ($id <= 0 || set_value('subject',$order['subject']) != ""?' required="1" ':''); ?> <?php echo $generalMinimuDataReadonly; ?> autofocus="1">                    
                </div> 
            </div>
            <div class="form-group row">  
                <label for="stateId" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Estado:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">                        
                    <select id="stateId" name="stateId" class="form-control form-control-sm <?php echo errorClass("stateId"); ?>" <?php echo errorTitle("stateId"); ?> <?php echo (count($states) <= 1?' readonly="readonly"':''); ?> <?php echo $disabled; ?> required="1" style="width:auto;" <?php echo (count($states) > 1 && isset($onChangeState)?' onchange="'.$onChangeState.'" ':''); ?>>                                 
                        <?php if (count($states) <= 0) { ?>
                        <option value="" selected="selected">[Sin Definir]</option>
                        <?php } ?>
                        <?php 
                            for($i=0; $i < count($states); $i++) {
                                if (count($states) == 1) {
                                    $selected = ' selected="selected" ';                  
                                } else {
                                    if (validation_errors() != "" || (isset($error) && $error != "")) {
                                        $selected = set_select('stateId', $states[$i]['id']);
                                    } else {
                                        $selected = ($order['stateId'] == $states[$i]['id']?' selected="selected" ':'');                  
                                    }
                                }
                        ?>
                        <option value="<?php echo $states[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo $states[$i]['description'];?></option>
                        <?php
                            }
                        ?>
                    </select>  
                    <input type="hidden" id="originalStateId" name="originalStateId" value="<?php echo $order['originalStateId']; ?>">  
                </div> 
                <label for="priorityId" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Plazo:</label>
                <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">                        
                    <select id="priorityId" name="priorityId" class="form-control form-control-sm <?php echo errorClass("priorityId"); ?>" <?php echo errorTitle("priorityId"); ?> <?php echo $generalMinimuDataReadonly; ?> required="1" style="width:auto;float:left;" onchange="changePriorityOrderEdit()">                                                    
                        <?php                         
                            if (validation_errors() != "" || (isset($error) && $error != "")) {
                                $selected = set_select('priorityId', $order['priorityId']);
                            } else {
                                $selected = ($order['priorityId'] == 0?' selected="selected" ':'');                  
                            }

                            $defaultColor = "#495057";                            
                        ?>
                        <?php if (count($priorities) != 1) { ?>
                        <option value="" <?php echo $selected; ?> style="color:<?php echo $defaultColor; ?>;" color="<?php echo $defaultColor; ?>" maxDate="" editMaxDate="0">[Seleccionar]</option>
                        <?php } ?>
                        <?php 
                            $maximumDaysReadonly = $generalDataReadonly;
                            for($i=0; $i < count($priorities); $i++) {
                                if (validation_errors() != "" || (isset($error) && $error != "")) {
                                    $selected = set_select('priorityId', $priorities[$i]['id']);
                                } else {
                                    $selected = ($order['priorityId'] == $priorities[$i]['id']?' selected="selected" ':'');  
                                    if ((int)$priorities[$i]['editableDays'] == 0) {
                                        $maximumDaysReadonly = ' readonly="readonly" ';
                                    }                
                                }                                                        
                        ?>
                        <option value="<?php echo $priorities[$i]['id']; ?>" <?php echo $selected; ?> style="color:<?php echo (trim($priorities[$i]['color']) == ""?$defaultColor:trim($priorities[$i]['color']));?>" color="<?php echo (trim($priorities[$i]['color']) == ""?$defaultColor:trim($priorities[$i]['color']));?>" defaultDays="<?php echo $priorities[$i]['days']; ?>" minDays="<?php echo $priorities[$i]['minDays']; ?>" maxDays="<?php echo $priorities[$i]['maxDays']; ?>" editDays=<?php echo ((int)$priorities[$i]['editableDays'] == 1?"1":"0"); ?>><?php echo $priorities[$i]['description'];?></option>
                        <?php
                            }
                        ?>
                    </select>   
                    <select id="subpriorityId" name="subpriorityId" class="form-control form-control-sm <?php echo errorClass("subpriorityId"); ?> hide" <?php echo errorTitle("subpriorityId"); ?> required="1" selValue="<?php echo set_value('subpriorityId',$order['subpriorityId']); ?>" style="width:auto;margin-left:5px;float:left;" <?php echo $disabled; ?>>                                                    
                        <option value="" selected="selected">Cargando...</option>                        
                    </select>  
                    <?php if (isset($order['maximumDate']) && $order['maximumDate'] != "") { ?>                              
                    <input type="date" id="maximumDate" name="maximumDate" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("maximumDate"); ?>" placeholder="--/--/----" <?php echo errorTitle("maximumDate"); ?> autocomplete="off" value="<?php echo set_value('maximumDate',$order['maximumDate']); ?>" style="width:120px;margin-left:5px;float:left;" readonly="readonly">                     
                    <input type="hidden" id="maximumDays" name="maximumDays" value="<?php echo $order['maximumDays']; ?>">
                    <?php } else { ?>
                    <input type="number" id="maximumDays" name="maximumDays" step="1" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("maximumDays"); ?>" placeholder="" <?php echo errorTitle("maximumDays"); ?> autocomplete="off" value="<?php echo set_value('maximumDays',$order['maximumDays']); ?>" style="width:60px;margin-left:5px;float:left;" required= "1" <?php echo $maximumDaysReadonly; ?>>                     
                    <label for="maximumDays" id="lblMaximumDays" class="col-form-label col-form-label-sm text-left" style="width:40px;margin-left:5px;float:left;font-weight:normal;" >días</label>                                                            
                    <?php } ?>
                </div>                                                    
            </div>                   
            <div class="form-group row">  
                <label for="companyId" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Empresa:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">                        
                    <select id="companyId" name="companyId" class="form-control form-control-sm <?php echo errorClass("companyId"); ?>" <?php echo errorTitle("companyId"); ?> required="1" style="width:auto;" <?php echo (count($companies) <= 1 && !$allowSelectCompany?' readonly="readonly"':''); ?> <?php echo $disabled; ?> <?php if ($allowSelectCompany) { echo 'onchange="selectCompanyOrderEdit()"'; } ?>>                            
                        <?php if (count($companies) <= 0 || $allowSelectCompany) { ?>                        
                        <?php 
                            if (validation_errors() != "" || (isset($error) && $error != "")) {
                                $selected = set_select('companyId', $order['companyId']);
                            } else {
                                $selected = ($order['companyId'] == 0 || count($companies) <= 0?' selected="selected" ':'');                  
                            }
                        ?>
                        <option value="" wh="0" <?php echo $selected; ?>>[Seleccionar]</option>
                        <?php } ?>
                        <?php 
                            for($i=0; $i < count($companies); $i++) {
                                if (count($companies) == 1) {
                                    $selected = ' selected="selected" ';                  
                                } else {
                                    if (validation_errors() != "" || (isset($error) && $error != "")) {
                                        $selected = set_select('companyId', $companies[$i]['id']);
                                    } else {
                                        $selected = ($order['companyId'] == $companies[$i]['id']?' selected="selected" ':'');                  
                                    }
                                }
                        ?>
                        <option value="<?php echo $companies[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo $companies[$i]['description'];?></option>
                        <?php
                            }
                        ?>
                    </select>  
                </div>  
                <label for="branchOfficeId" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Sucursal:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">                        
                    <select id="branchOfficeId" name="branchOfficeId" class="form-control form-control-sm <?php echo errorClass("branchOfficeId"); ?>" <?php echo errorTitle("branchOfficeId"); ?> selValue="<?php echo set_value('branchOfficeId',$order['branchOfficeId']); ?>" required="1" style="width:auto;" <?php echo (count($branchOffices) <= 1 && !$allowSelectCompany?' readonly="readonly"':''); ?> <?php echo $disabled; ?> <?php if ($allowSelectCompany) { echo 'onchange="selectBranchOfficeOrderEdit()"'; } ?>>                            
                        <?php if (count($branchOffices) <= 0 || $allowSelectCompany) { ?>
                        <?php 
                            if (validation_errors() != "" || (isset($error) && $error != "")) {
                                $selected = set_select('branchOfficeId', $order['branchOfficeId']);
                            } else {
                                $selected = ($order['branchOfficeId'] == 0 || count($branchOffices) <= 0?' selected="selected" ':'');                  
                            }
                        ?>
                        <option value="" <?php echo $selected; ?>>[Seleccionar]</option>
                        <?php } ?>
                        <?php 
                            for($i=0; $i < count($branchOffices); $i++) {
                                if (count($branchOffices) == 1) {
                                    $selected = ' selected="selected" ';                  
                                } else {
                                    if (validation_errors() != "" || (isset($error) && $error != "")) {
                                        $selected = set_select('branchOfficeId', $branchOffices[$i]['id']);
                                    } else {
                                        $selected = ($order['branchOfficeId'] == $branchOffices[$i]['id']?' selected="selected" ':'');                  
                                    }
                                }
                        ?>
                        <option value="<?php echo $branchOffices[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo $branchOffices[$i]['description'];?></option>
                        <?php
                            }
                        ?>
                    </select>  
                </div>  
                <label for="sectorId" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Sector:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">                                
                    <select id="sectorId" name="sectorId" class="form-control form-control-sm <?php echo errorClass("sectorId"); ?>" <?php echo errorTitle("sectorId"); ?> selValue="<?php echo set_value('sectorId',$order['sectorId']); ?>" required="1" style="width:auto;" <?php echo (count($sectors) <= 1 && !$allowSelectCompany?' readonly="readonly"':''); ?> <?php echo $disabled; ?>  <?php if ($allowSelectCompany) { echo 'onchange="loadAuthorizingUsersOrderEdit()"'; } ?>>                            
                        <?php if (count($sectors) <= 0 || $allowSelectCompany) { ?>
                        <?php 
                            if (validation_errors() != "" || (isset($error) && $error != "")) {
                                $selected = set_select('sectorId', $order['sectorId']);
                            } else {
                                $selected = ($order['sectorId'] == 0 || count($sectors) <= 0?' selected="selected" ':'');                  
                            }
                        ?>
                        <option value="" <?php echo $selected; ?>>[Seleccionar]</option>
                        <?php } ?>
                        <?php 
                            for($i=0; $i < count($sectors); $i++) {
                                if (count($sectors) == 1) {
                                    $selected = ' selected="selected" ';                  
                                } else {
                                    if (validation_errors() != "" || (isset($error) && $error != "")) {
                                        $selected = set_select('sectorId', $sectors[$i]['id']);
                                    } else {
                                        $selected = ($order['sectorId'] == $sectors[$i]['id']?' selected="selected" ':'');                  
                                    }
                                }
                        ?>
                        <option value="<?php echo $sectors[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo $sectors[$i]['description'];?></option>
                        <?php
                            }
                        ?>
                    </select>  
                </div>  
            </div>     
            <div class="form-group row">  
                <label for="userId" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Solicitado Por:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">                                            
                    <select id="userId" name="userId" class="form-control form-control-sm <?php echo errorClass("userId"); ?>" <?php echo errorTitle("userId"); ?> required="1" style="width:auto;" <?php echo (!isset($users) || (isset($users) && count($users) <= 1)?' readonly="readonly"':''); ?> <?php echo $disabled; ?>>                            
                        <?php if (!isset($users) || (isset($users) && count($users) <= 0)) { ?>
                        <option value="" selected="selected">[Seleccionar]</option>
                        <?php } ?>
                        <?php 
                            if (isset($users)) {
                                for($i=0; $i < count($users); $i++) {
                                    if (count($users) == 1) {
                                        $selected = ' selected="selected" ';                  
                                    } else {
                                        if (validation_errors() != "" || (isset($error) && $error != "")) {
                                            $selected = set_select('userId', $users[$i]['id']);
                                        } else {
                                            $selected = ($order['userId'] == $users[$i]['id']?' selected="selected" ':'');                  
                                        }
                                    }
                        ?>
                        <option value="<?php echo $users[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo trim($users[$i]['lastName'].", ".$users[$i]['firstName']);?></option>
                        <?php
                                }
                            }
                        ?>
                    </select>  
                </div>  
                <label for="userMail" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Email:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-3"> 
                    <input id="userMail" name="userMail" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("userMail"); ?>" <?php echo errorTitle("userMail"); ?> autocomplete="off" maxlength="100"  value="<?php echo set_value('userMail',$order['userMail']); ?>" readonly="readonly" <?php echo $disabled; ?>>                    
                </div>  
                <label for="userCellphone" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-1 text-right">Celular:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2"> 
                    <div class="input-group input-group-sm">
                        <input id="userCellphone" name="userCellphone" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("userCellphone"); ?>" <?php echo errorTitle("userCellphone"); ?> autocomplete="off" maxlength="100" placeholder="" value="<?php echo set_value('userCellphone',$order['userCellphone']); ?>" readonly="readonly" <?php echo $disabled; ?>>                                            
                        <span class="input-group-append">
                            <button type="button" class="btn btn-default btn-flat whatsappLink mobile" onclick="openWhatsAppOrderEdit(true)" title="abrir whatsapp"><i class="fas fa-search"></i></button>
                            <button type="button" class="btn btn-default btn-flat whatsappLink desktop" onclick="openWhatsAppOrderEdit(false)" title="abrir whatsapp"><img src="<?php echo base_url()."assets/images/whatsapp.png?".filemtime("assets/images/whatsapp.png"); ?>" width="19px"></button>
                        </span>
                    </div>
                </div>            
            </div>
            <div class="form-group row">                    
                <label for="authorizingUserId" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Validado Por:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">                        
                    <select id="authorizingUserId" name="authorizingUserId" class="form-control form-control-sm <?php echo errorClass("authorizingUserId"); ?>" <?php echo errorTitle("authorizingUserId"); ?> required="1" style="width:auto;" <?php echo (count($authorizingUsers) <= 1?' readonly="readonly"':''); ?> <?php echo $disabled; ?>>                            
                        <?php if (count($authorizingUsers) == 0) { ?>
                        <option value="0" selected="selected">[Sin Asignar]</option>
                        <?php } ?>
                        <?php if (count($authorizingUsers) > 1) { ?>
                        <?php 
                            if (validation_errors() != "" || (isset($error) && $error != "")) {
                                $selected = set_select('authorizingUserId', $order['authorizingUserId']);
                            } else {
                                $selected = ($order['authorizingUserId'] == 0?' selected="selected" ':'');                  
                            }
                        ?>
                        <option value="" <?php echo $selected; ?>>[Seleccionar]</option>
                        <?php } ?>
                        <?php 
                            for($i=0; $i < count($authorizingUsers); $i++) {
                                if (count($authorizingUsers) == 1) {
                                    $selected = ' selected="selected" ';                  
                                } else {
                                    if (validation_errors() != "" || (isset($error) && $error != "")) {
                                        $selected = set_select('authorizingUserId', $authorizingUsers[$i]['id']);
                                    } else {
                                        $selected = ($order['authorizingUserId'] == $authorizingUsers[$i]['id']?' selected="selected" ':'');                  
                                    }
                                }
                        ?>
                        <option value="<?php echo $authorizingUsers[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo trim($authorizingUsers[$i]['lastName'].", ".$authorizingUsers[$i]['firstName']);?></option>
                        <?php
                            }
                        ?>
                    </select>  
                </div>  
                <label for="purchaseOrderNumber" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Nº Orden Compra:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2"> 
                    <?php if (($allowPrintFull || $allowExportFull) && (float)$order['purchaseOrderId'] > 0) { ?>                   
                    <div class="input-group input-group-sm">
                    <?php } ?>    
                        <input id="purchaseOrderNumber" name="purchaseOrderNumber" class="form-control form-control-sm noEnterMyApp" autocomplete="off" value="<?php echo set_value('purchaseOrderNumber',trim($order['purchaseOrderNumber'])); ?>" style="width:120px;" readonly="readonly" <?php echo $disabled; ?>>                    
                    <?php if (($allowPrintFull || $allowExportFull) && $order['purchaseOrderId'] > 0) { ?>                   
                        <span class="input-group-append">                                            
                            <?php if ($allowPrintFull) { ?>                        
                            <button type="<?php echo ($allowSave?"submit":"button"); ?>" class="btn btn-default btn-flat" onclick="<?php echo ($allowSave?"selectActionOrderEdit('P|PO')":"printOrder(".$id.",'PO')"); ?>" title="imprimir" <?php echo ((float)$order['purchaseOrderId'] > 0?"":' disabled="disabled" '); ?>><i class="fa fa-print"></i></button>                            
                            <?php } ?>     
                            <?php if ($allowExportFull) { ?>
                                <?php if ($allowExportPdf) { ?>
                                <button type="<?php echo ($allowSave?"submit":"button"); ?>" class="btn btn-default btn-flat" onclick="<?php echo ($allowSave?"selectActionOrderEdit('EP|PO')":"exportOrder(".$id.",'PO','P')"); ?>" title="expotar pdf" <?php echo ((float)$order['purchaseOrderId'] > 0?"":' disabled="disabled" '); ?>><i class="far fa-file-pdf"></i></button>                            
                                <?php } ?>                   
                                <button type="<?php echo ($allowSave?"submit":"button"); ?>" class="btn btn-default btn-flat" onclick="<?php echo ($allowSave?"selectActionOrderEdit('EE|PO')":"exportOrder(".$id.",'PO','E')"); ?>" title="exportar excel" <?php echo ((float)$order['purchaseOrderId'] > 0?"":' disabled="disabled" '); ?>><i class="far fa-file-excel"></i></button>                            
                            <?php } ?>                   
                        </span>                                        
                    </div>
                    <?php } ?>    
                </div> 
            </div>  
            <div class="form-group row">  
                <label for="managerId" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Gestionado Por:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">                        
                    <select id="managerId" name="managerId" class="form-control form-control-sm <?php echo errorClass("managerId"); ?>" <?php echo errorTitle("managerId"); ?> required="1" style="width:auto;" <?php echo (count($managers) <= 1?' readonly="readonly"':''); ?> <?php echo $disabled; ?>>                            
                        <?php if (count($managers) <= 0) { ?>
                        <option value="0" selected="selected">[Sin Asignar]</option>
                        <?php } ?>
                         <?php if (count($managers) > 1) { ?>
                        <?php 
                            if (validation_errors() != "" || (isset($error) && $error != "")) {
                                $selected = set_select('managerId', $order['managerId']);
                            } else {
                                $selected = ($order['managerId'] == 0?' selected="selected" ':'');                  
                            }
                        ?>
                        <option value="" <?php echo $selected; ?>>[Seleccionar]</option>
                        <?php } ?>
                        <?php 
                            for($i=0; $i < count($managers); $i++) {
                                if (count($managers) == 1) {
                                    $selected = ' selected="selected" ';                  
                                } else {
                                    if (validation_errors() != "" || (isset($error) && $error != "")) {
                                        $selected = set_select('managerId', $managers[$i]['id']);
                                    } else {
                                        $selected = ($order['managerId'] == $managers[$i]['id']?' selected="selected" ':'');                  
                                    }
                                }
                        ?>
                        <option value="<?php echo $managers[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo trim($managers[$i]['lastName'].", ".$managers[$i]['firstName']);?></option>
                        <?php
                            }
                        ?>
                    </select>  
                </div>   
                <label class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Remitos:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">              
                    <?php if ($allowGenerateDN) { ?>   
                    <button type="button" class="btn btn-default btn-sm" onclick="generateDeliveryNotesOrderEdit(<?php echo $order['id']; ?>)" title="generar nuevo remito"><i class="fas fa-plus"></i></button>
                    <?php } ?> 
                    <?php if (isset($order['deliveryNotes']) && count($order['deliveryNotes']) > 0) { ?>                   
                    <button type="button" class="btn btn-default btn-sm" onclick="seeDeliveryNotesOrderEdit(<?php echo $order['id']; ?>)" title="ver remitos"><i class="fas fa-file"></i></button>
                    <?php } ?>                     
                </div>   
                <?php if ($allowSeeBudgets || $allowInsertBudgets) { ?>   
                <label class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Presupuestos:</label>      
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">              
                    <?php if ($allowInsertBudgets) { ?>                       
                    <?php if ($allowSave) { ?>
                    <input type="submit" class="hide" id="btnNewBudget" onclick="selectActionOrderEdit('NB|')">                                                                                                                                                                                                  
                    <button type="button" class="btn btn-default btn-sm" onclick="$('#btnNewBudget').click()" title="generar nuevo presupuesto"><i class="fas fa-plus"></i></button>
                    <?php } else { ?>                        
                    <button type="button" class="btn btn-default btn-sm" onclick="newBudgetOrderEdit(<?php echo $order['id']; ?>)" title="generar nuevo presupuesto"><i class="fas fa-plus"></i></button>
                    <?php } ?> 
                    <?php } ?> 
                    <?php if ($allowSeeBudgets && isset($order['budgets']) && count($order['budgets']) > 0) { ?>   
                    <button type="button" class="btn btn-default btn-sm" onclick="seeBudgetsOrderEdit(<?php echo $order['id']; ?>)" title="ver presupuestos"><i class="fas fa-file"></i></button>                                        
                    <button type="button" class="btn btn-default btn-sm" onclick="generalExport('budgets/orderSummaryExport/<?php echo $order['id']; ?>');" title="descargar resumen de costos"><i class="fas fa-chart-bar"></i></button>
                    <?php if (true) { ?>
                    <button type="button" class="btn btn-default btn-sm" onclick="seeSummaryBudgetsOrderEdit(<?php echo $order['id']; ?>);" title="ver resumen de costos"><i class="fas fa-chart-bar"></i></button>
                    <?php } ?> 
                    <?php } ?> 
                </div> 
                <?php } ?> 
            </div>      
            <?php if ($showSubmanager) { ?>
            <div class="form-group row">  
                <label for="submanagerId" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Asiste en Gestión:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">                        
                    <select id="submanagerId" name="submanagerId" class="form-control form-control-sm <?php echo errorClass("submanagerId"); ?>" <?php echo errorTitle("submanagerId"); ?> style="width:auto;" <?php echo (count($submanagers) <= 1 || !$allowEditSubmanager?' readonly="readonly"':''); ?> <?php echo $disabled; ?>>                            
                        <?php if (count($submanagers) <= 0) { ?>
                        <option value="0" selected="selected">[Sin Asignar]</option>
                        <?php } ?>
                         <?php if (count($submanagers) > 1) { ?>
                        <?php 
                            if (validation_errors() != "" || (isset($error) && $error != "")) {
                                $selected = set_select('submanagerId', $order['submanagerId']);
                            } else {
                                $selected = ($order['submanagerId'] == 0?' selected="selected" ':'');                  
                            }
                        ?>
                        <option value="" <?php echo $selected; ?>>[Seleccionar]</option>
                        <?php } ?>
                        <?php 
                            for($i=0; $i < count($submanagers); $i++) {
                                if (count($submanagers) == 1) {
                                    $selected = ' selected="selected" ';                  
                                } else {
                                    if (validation_errors() != "" || (isset($error) && $error != "")) {
                                        $selected = set_select('submanagerId', $submanagers[$i]['id']);
                                    } else {
                                        $selected = ($order['submanagerId'] == $submanagers[$i]['id']?' selected="selected" ':'');                  
                                    }
                                }
                        ?>
                        <option value="<?php echo $submanagers[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo trim($submanagers[$i]['lastName'].", ".$submanagers[$i]['firstName']);?></option>
                        <?php
                            }
                        ?>
                    </select>  
                </div>   
            </div>      
            <?php } ?> 
            <?php 
                $paymentSectors = $order['paymentSectors'];
                $paymentSectorsCount = (isset($paymentSectors)?count($paymentSectors):0);             
            ?>
            <div class="form-group row">                  
                <label for="btnPaymentSectors" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Imputar Pagos
                        <button type="button" id="btnPaymentSectors" class="btn without-padding" onclick="changeBtnUpDown('btnPaymentSectors')" title="<?php echo ($paymentSectorsCount > 0?"ocultar":"mostrar"); ?>">
                            <i class="fas fa-chevron-circle-<?php echo ($paymentSectorsCount > 0?"up":"down"); ?>" id="icon_btnPaymentSectors"></i>
                        </button>                        
                </label>
                <?php if ($allowSave && $allowEditMinimunGeneralData) { ?>
                <div class="col-xs-10 col-sm-10 col-md-10 col-lg-10 container_btnPaymentSectors <?php echo ($paymentSectorsCount > 0?"":"hide"); ?>" style="margin-top:5px;">
                    <label for="paymentCompanyId" class="col-form-label col-form-label-sm text-left" style="width:60px;margin-left:5px;float:left;font-weight:normal;" >Empresa</label>                                                            
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <select id="paymentCompanyId" name="paymentCompanyId" class="form-control form-control-sm" style="width:auto;float:left;" onchange="selectPaymentCompanyOrderEdit()">                            
                            <option value="0">[Todas]</option>                            
                            <?php 
                                for($i=0; $i < count($paymentCompanies); $i++) {                                    
                            ?>
                            <option value="<?php echo $paymentCompanies[$i]['id']; ?>"><?php echo $paymentCompanies[$i]['description'];?></option>
                            <?php
                                }
                            ?>
                        </select>                     
                    </div>               
                    <label for="paymentBranchOfficeId" class="col-form-label col-form-label-sm text-left" style="width:60px;margin-left:20px;float:left;font-weight:normal;" >Sucursal</label>                                                            
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <select id="paymentBranchOfficeId" name="paymentBranchOfficeId" class="form-control form-control-sm" style="width:auto;float:left;" onchange="selectPaymentBranchOfficeOrderEdit()">                            
                            <option value="0">[Todas]</option>                                                        
                        </select>                     
                    </div> 
                    <label for="paymentSectorId" class="col-form-label col-form-label-sm text-left" style="width:46px;margin-left:20px;float:left;font-weight:normal;" >Sector</label>     
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <select id="paymentSectorId" name="paymentSectorId" class="form-control form-control-sm" style="width:auto;float:left;">                            
                            <option value="0">[Todos]</option>           
                        </select>                     
                    </div> 
                    <label for="paymentPercent" class="col-form-label col-form-label-sm text-left" style="width:5px;margin-left:20px;float:left;font-weight:normal;" >%</label>     
                    <input type="number" id="paymentPercent" name="paymentPercent" min="0" max="100" step="0.01" class="form-control form-control-sm noEnterMyApp text-right" autocomplete="off" value="0.00" style="width:75px;margin-left:15px;float:left;">
                    <button type="button" class="btn btn-default btn-sm" onclick="addPaymentSectorOrderEdit()" title="agregar" style="margin-left:10px;"><i class="fas fa-plus"></i></button>
                </div>  
                <?php } ?>
                <div class="offset-2 col-xs-10 col-sm-10 col-md-10 col-lg-10 container_btnPaymentSectors <?php echo ($paymentSectorsCount > 0?"":"hide"); ?>" style="margin-top:0px;">
                    <table class="table table-bordered table-sm" id="grPaymentSectors" style="margin-bottom:0px;margin-top:10px;width:auto;">
                        <thead>
                            <tr>                        
                                <?php if ($allowSave && $allowEditMinimunGeneralData) { ?>
                                <th width="35px" class="text-center"></th>
                                <?php } ?>                                
                                <th nowrap="nowrap" style="width:200px;" class="col-form-label-sm">Empresa</th>                        
                                <th nowrap="nowrap" style="width:200px;" class="col-form-label-sm">Sucursal</th>  
                                <th nowrap="nowrap" style="width:200px;" class="col-form-label-sm">Sector</th>  
                                <th nowrap="nowrap" style="width:60px;" class="col-form-label-sm text-center">%</th>
                            </tr>
                        </thead>                            
                        <tbody>
                        <?php                             
                            if (isset($paymentSectors)) {                                               
                        ?>                            
                            <?php                                    
                                for($i=0; $i < count($paymentSectors); $i++) {
                                    $row = $i + 1;                                       
                            ?>
                            <tr id="<?php echo $row; ?>">
                                <?php if ($allowSave && $allowEditMinimunGeneralData) {  ?>
                                <td class="text-center without-padding">                                                                                                                                                
                                    <button type="button" class="btn without-padding" onclick="deletePaymentSectorOrderEdit(<?php echo $row; ?>)" title="eliminar">
                                        <i class="far fa-minus-square"></i>
                                    </button>   
                                </td>
                                <?php } ?>                                    
                                <td class="without-padding"><input type="text" id="pCompany<?php echo $row; ?>" name="pCompany<?php echo $row; ?>" class="form-control form-control-sm" maxlength="100" autocomplete="off" value="<?php echo $paymentSectors[$i]['companyDescription']; ?>" readonly="readonly"></td>                                                    
                                <td class="without-padding"><input type="text" id="pBranchOffice<?php echo $row; ?>" name="pBranchOffice<?php echo $row; ?>" class="form-control form-control-sm" maxlength="100" autocomplete="off" value="<?php echo $paymentSectors[$i]['branchOfficeDescription']; ?>" readonly="readonly"></td>                                                                    
                                <td class="without-padding"><input type="text" id="pSector<?php echo $row; ?>" name="pSector<?php echo $row; ?>" class="form-control form-control-sm" maxlength="100" autocomplete="off" value="<?php echo $paymentSectors[$i]['sectorDescription']; ?>" readonly="readonly"></td>                                                                    
                                <td class="without-padding"><input type="text" id="pPercent<?php echo $row; ?>" name="pPercent<?php echo $row; ?>" class="form-control form-control-sm text-right" maxlength="6" autocomplete="off" value="<?php echo decimalFormat($paymentSectors[$i]['percent'],2); ?>" <?php echo $generalMinimuDataReadonly; ?> onfocus="this.select();"></td>                                
                                
                            </tr>
                            <?php } ?>                            
                        <?php } ?>
                        </tbody>                        
                    </table>
                    <div id="pSectors" class="hide">                              
                        <?php 
                            if (isset($paymentSectors)) {                                    
                                for($i=0; $i < count($paymentSectors); $i++) {
                                    $row = $i + 1;                                        
                        ?>
                        <div id="pSector<?php echo $row; ?>">
                            <input type="hidden" id="pId<?php echo $row; ?>" name="pId<?php echo $row; ?>" value="<?php echo $paymentSectors[$i]['id']; ?>">     
                            <input type="hidden" id="pCompanyId<?php echo $row; ?>" name="pCompanyId<?php echo $row; ?>" value="<?php echo $paymentSectors[$i]['companyId']; ?>">     
                            <input type="hidden" id="pBranchOfficeId<?php echo $row; ?>" name="pBranchOfficeId<?php echo $row; ?>" value="<?php echo $paymentSectors[$i]['branchOfficeId']; ?>">     
                            <input type="hidden" id="pSectorId<?php echo $row; ?>" name="pSectorId<?php echo $row; ?>" value="<?php echo $paymentSectors[$i]['sectorId']; ?>">     
                        </div>
                        <?php   } 
                            }
                        ?>
                        <input type="hidden" id="pSectorsCount" name="pSectorsCount" value="<?php echo $paymentSectorsCount; ?>">                                 
                    </div>
                </div>               
            </div>      
            <div class="form-group row" style="margin-bottom:30px;">
                <label class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">
                	Insumos:
                	<p style="font-size:10px;margin-bottom: 0px;">(F2 en Código</p>
                	<p style="font-size:10px;margin-bottom: 0px;">p/ buscar Art.)</p>                                    	
                </label>                
                <div class="col-xs-10 col-sm-10 col-md-10 col-lg-10">
                    <table class="table table-bordered table-sm" id="grDetails" style="margin-bottom:0px;margin-top:10px;">
                        <thead>
                            <tr>                        
                                <?php if ($allowEditDetail) { ?>
                                <th width="35px" class="text-center" style="">                                                       
                                    <button type="button" id="btnNewRow" name="btnNewRow" class="btn without-padding" onclick="newRowDetailOrderEdit()" title="nuevo">
                                        <i class="far fa-plus-square"></i>
                                    </button> 
                                </th>
                                <?php } ?>                                
                                <th nowrap="nowrap" style="width:100px;" class="col-form-label-sm">Código</th>                        
                                <th nowrap="nowrap" style="width:250px;" class="col-form-label-sm">Descripción</th>  
                                <?php if ($allowSeeImports) { ?>
                                <th nowrap="nowrap" style="width:100px;" class="col-form-label-sm">Costo s/IVA ($)</th>  
                                <?php } ?>                                 
                                <th nowrap="nowrap" style="width:60px;" class="col-form-label-sm" title="Cantidad">Cant.</th>  
                                <th nowrap="nowrap" style="width:40px;" class="col-form-label-sm" <?php echo ($allowSeeFreeQuantity || $allowSeeImports?' title="Habitual"':''); ?>><?php echo ($allowSeeFreeQuantity || $allowSeeImports?'Hab.':'Habitual'); ?></th>  
                                <?php if ($allowSeeFreeQuantity) { ?>
                                <th nowrap="nowrap" style="width:60px;" class="col-form-label-sm" title="Entregados">Ent.</th>  
                                <th nowrap="nowrap" style="width:60px;" class="col-form-label-sm" title="Para Entregar">P/Ent.</th>  
                                <th nowrap="nowrap" style="width:60px;" class="col-form-label-sm" title="Sin Stock">S/Stock</th>  
                                <?php } ?>                      
                                <th nowrap="nowrap" style="width:150px;" class="col-form-label-sm">Rubro</th>  
                                <?php if ($allowSeeImports) { ?>
                                <th nowrap="nowrap" style="width:100px;" class="col-form-label-sm">Total ($)</th>                                  
                                <?php } ?>             
                                <?php if ($allowSeeFreeQuantity || $allowSeeDetailsImports || $seeCancelItem)  { ?>                    
                                <th nowrap="nowrap" style="width:30px;" class="col-form-label-sm"></th>                                  
                                <?php } ?>             
                            </tr>
                        </thead>                            
                        <tbody>
                        <?php 
                            $details = $order['details'];

                            $detailsCount = 0;
                            if (isset($details)) { 
                                $detailsCount = count($details);
                        ?>                            
                            <?php                                    
                                for($i=0; $i < count($details); $i++) {
                                    $row = $i + 1;         

                                    $quantityClass = "";
                                    if ($seeCancelItem && $details[$i]['canceledQuantity'] > 0) {
                                        if ($details[$i]['quantity'] > 0)  {
                                            $quantityClass = "partialCanceledItem";
                                        } else {
                                            $quantityClass = "totalCanceledItem";
                                        }                                        
                                    }                                                          
                            ?>
                            <tr id="<?php echo $row; ?>">
                                <?php if ($allowEditDetail) { ?>
                                <td class="text-center without-padding">                                                                                                                                                
                                    <button type="button" class="btn without-padding" onclick="deleteRowDetailOrderEdit(<?php echo $row; ?>)" title="eliminar">
                                        <i class="far fa-minus-square"></i>
                                    </button>   
                                </td>
                                <?php } ?>                                    
                                <td class="without-padding"><input type="text" id="detailCode<?php echo $row; ?>" name="detailCode<?php echo $row; ?>" class="form-control form-control-sm detailColumn" maxlength="25" autocomplete="off" value="<?php echo $details[$i]['code']; ?>" <?php echo $detailReadonly; ?> onblur="searchArticleOrderEdit(<?php echo $row; ?>)"></td>                                                    
                                <td class="without-padding"><input type="text" id="detailDescription<?php echo $row; ?>" name="detailDescription<?php echo $row; ?>" class="form-control form-control-sm detailColumn" maxlength="100" autocomplete="off" value="<?php echo $details[$i]['description']; ?>" readonly="readonly"></td>                                    
                                <?php if ($allowSeeImports) { 
                                        $detailReadonlyImports = ($allowEditDetailImports && (int)$details[$i]['affectsStock'] == 0?'':' readonly="readonly" '); 
                                ?>
                                <td class="without-padding"><input type="number" min="0" step="0.01" id="detailUnitPrice<?php echo $row; ?>" name="detailUnitPrice<?php echo $row; ?>" class="form-control form-control-sm detailColumn text-right" maxlength="12" autocomplete="off" value="<?php echo decimalFormat($details[$i]['unitPrice'],2); ?>" <?php echo $detailReadonlyImports; ?> onchange="calculateTotalOrderEdit();" onfocus="this.select();"></td>
                                <?php } ?>   
                                <td class="without-padding"><input type="number" min="0" step="1" id="detailQuantity<?php echo $row; ?>" name="detailQuantity<?php echo $row; ?>" class="form-control form-control-sm detailColumn text-right <?php echo $quantityClass; ?>" maxlength="3" autocomplete="off" value="<?php echo (int)$details[$i]['quantity']; ?>" <?php echo $detailReadonly; ?> onchange="calculateTotalOrderEdit()" onfocus="this.select();"></td>
                                <td class="without-padding"><input type="text" id="detailUsual<?php echo $row; ?>" name="detailUsual<?php echo $row; ?>" class="form-control form-control-sm detailColumn text-center" autocomplete="off" value="<?php echo getBooleanToText($details[$i]['usual']); ?>"  readonly="readonly" onfocus="this.select();"></td>
                                <?php if ($allowSeeFreeQuantity) { ?>
                                <td class="without-padding"><input type="number" min="0" step="1" id="detailDeliveredQuantity<?php echo $row; ?>" name="detailDeliveredQuantity<?php echo $row; ?>" class="form-control form-control-sm detailColumn text-right" maxlength="3" autocomplete="off" value="<?php echo (int)$details[$i]['deliveredQuantity']; ?>"  readonly="readonly" onfocus="this.select();"></td>
                                <td class="without-padding"><input type="number" min="0" step="1" id="detailFreeQuantity<?php echo $row; ?>" name="detailFreeQuantity<?php echo $row; ?>" class="form-control form-control-sm detailColumn text-right" maxlength="3" autocomplete="off" value="<?php echo (int)$details[$i]['freeQuantity']; ?>"  readonly="readonly" onfocus="this.select();"></td>
                                <td class="without-padding"><input type="number" min="0" step="1" id="detailPendingQuantity<?php echo $row; ?>" name="detailPendingQuantity<?php echo $row; ?>" class="form-control form-control-sm detailColumn text-right <?php echo ((int)$details[$i]['pendingQuantity'] > 0?" outOfStock":""); ?>" maxlength="3" autocomplete="off" value="<?php echo (int)$details[$i]['pendingQuantity']; ?>"  readonly="readonly" onfocus="this.select();"></td>
                                <?php } ?>       
                                <td class="without-padding"><input type="text" id="detailFamily<?php echo $row; ?>" name="detailFamily<?php echo $row; ?>" class="form-control form-control-sm detailColumn" maxlength="100" autocomplete="off" value="<?php echo $details[$i]['familyDescription']; ?>" readonly="readonly"></td>                                    
                                <?php if ($allowSeeImports) { ?>
                                <td class="without-padding">
                                    <input type="number" min="0" step="0.01" id="detailTotal<?php echo $row; ?>" name="detailTotal<?php echo $row; ?>" class="form-control form-control-sm detailColumn text-right" maxlength="12" autocomplete="off" value="<?php echo decimalFormat($details[$i]['total'],2); ?>" readonly="readonly">
                                    <?php if (!$allowSeeFreeQuantity) { ?>                                        
                                        <input type="hidden" id="detailPendingQuantity<?php echo $row; ?>" name="detailPendingQuantity<?php echo $row; ?>" value="<?php echo (int)$details[$i]['pendingQuantity']; ?>">
                                    <?php } ?>              
                                </td>                                
                                <?php } ?>          
                                <?php if ((($allowSeeFreeQuantity || $allowSeeDetailsImports) && (int)$details[$i]['affectsStock'] == 1) || $seeCancelItem || $allowSeeBudgets)  { ?>                                                                             
                                <td nowrap="nowrap" class="without-padding text-center">                                
                                    <div class="dropleft">
                                        <button type="button" class="btn without-padding dropdown-toggle" data-toggle="dropdown"></button>
                                        <div class="dropdown-menu">
                                            <?php if (($allowSeeFreeQuantity || $allowSeeDetailsImports) && (int)$details[$i]['affectsStock'] == 1) { ?>
                                            <a class="dropdown-item" href="javascript:seeOrderItemData(<?php echo $row; ?>,'<?php echo ($allowSeeFreeQuantity || $allowSeeDetailsImports?"ALL":($allowSeeFreeQuantity?"IMP":"QUA")); ?>')">Detalle Stock</a>
                                            <?php } ?>                                            
                                            <?php if ($seeCancelItem) { ?>
                                            <a class="dropdown-item" href="javascript:seeCancelItemOrderEdit(<?php echo $row; ?>)">Cancelación Artículos</a>                                                                                    
                                            <?php } ?>                                            
                                            <?php if ($allowSeeBudgets && $details[$i]['id'] > 0) { ?>                                               
                                            <a class="dropdown-item" href="javascript:seeBudgetsItemOrderEdit(<?php echo $row; ?>)">Presupuestos Asociados</a>                                                                                    
                                            <?php } ?>                                                
                                        </div>
                                    </div>                              
                                </td>                 
                                <?php } ?>                                                    
                            </tr>
                            <?php } ?>                            
                        <?php } ?>
                        </tbody>
                    </table> 
                    <div id="details" class="hide">                              
                        <?php 
                            if (isset($details)) {                                    
                                for($i=0; $i < count($details); $i++) {
                                    $row = $i + 1;                                        
                        ?>
                        <div id="detail<?php echo $row; ?>">
                            <input type="hidden" id="detailId<?php echo $row; ?>" name="detailId<?php echo $row; ?>" value="<?php echo $details[$i]['id']; ?>">     
                            <input type="hidden" id="detailArticleId<?php echo $row; ?>" name="detailArticleId<?php echo $row; ?>" value="<?php echo $details[$i]['articleId']; ?>">     
                            <input type="hidden" id="detailOriginalCode<?php echo $row; ?>" name="detailOriginalCode<?php echo $row; ?>" value="<?php echo $details[$i]['code']; ?>">                                 
                            <?php if (!$allowSeeImports) { ?>
                            <input type="hidden" id="detailUnitPrice<?php echo $row; ?>" name="detailUnitPrice<?php echo $row; ?>" value="<?php echo $details[$i]['unitPrice']; ?>">                                 
                            <?php } ?>     
                            <input type="hidden" id="detailAffectsStock<?php echo $row; ?>" name="detailAffectsStock<?php echo $row; ?>" value="<?php echo $details[$i]['affectsStock']; ?>">     
                        </div>
                        <?php   } 
                            }
                        ?>
                        <input type="hidden" id="detailsCount" name="detailsCount" value="<?php echo $detailsCount; ?>">     
                        <input type="hidden" id="rowArticlesFinder" name="rowArticlesFinder" value="">      
                    </div>
                </div>                    
            </div>    
            <?php if ($allowSeeImports) { ?>
            <div class="form-group row">
                <label for="total" class="col-form-label col-form-label-sm col-xs-3 col-sm-3 col-md-2 col-lg-2 text-right">Total s/IVA: $</label>                                        
                <div class="col-xs-9 col-sm-9 col-md-10 col-lg-10">                                                                              
                    <input type="number" id="total" name="total" class="form-control form-control-sm noEnterMyApp text-right" autocomplete="off" value="<?php echo decimalFormat(set_value('total',$order['total']),2); ?>" style="width:110px;" disabled="disabled">                                                
                </div>                    
            </div>
            <?php } ?>
            <?php if ($allowSave || $allowSaveOnlyObservation) { ?>
            <?php
                if (validation_errors() != "" || (isset($error) && $error != "")) {
                    $userObservation = set_value('userObservation');                    
                } else {
                    $userObservation = "";
                }
            ?>
            <div class="form-group row">
                <label for="userObservation" class="col-form-label col-form-label-sm col-xs-3 col-sm-3 col-md-2 col-lg-2 text-right">Observación:</label>
                <div class="col-xs-8 col-sm-8 col-md-9 col-lg-9">                      
                    <textarea id="userObservation" name="userObservation" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("userObservation"); ?>" <?php echo errorTitle("userObservation"); ?> rows="2" maxlength="500" autocomplete="off" <?php echo ($order['stateId'] == "REJ"?' required="1" ':''); ?>><?php echo $userObservation; ?></textarea>
                    <div class="remaining-characters-label" id="userObservationRemaining"></div>                                                            
                </div>                    
            </div> 
            <?php } ?>
            <?php 
                $userObservations = $order['userObservations'];
                if (isset($userObservations)) { 
            ?>     
            <div class="form-group row" <?php echo ($allowSave?"":' style="margin-top:40px;" '); ?>>                    
                <label for="grUserObservations" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right"></label>                                    
                <div class="col-xs-10 col-sm-10 col-md-10 col-lg-10" style="margin-top:-10px;max-height:175px; overflow-y:auto;">                                              
                    <table class="table table-bordered table-sm" id="grUserObservations" style="margin-bottom:0px;margin-top:0px;width:auto;">
                        <thead>
                            <tr>                        
                                <th nowrap="nowrap" style="width:120px;" class="col-form-label-sm">Fecha</th>                        
                                <th nowrap="nowrap" style="width:350px;" class="col-form-label-sm">Observación</th>  
                                <th nowrap="nowrap" style="width:200px;" class="col-form-label-sm">Usuario</th>                                      
                            </tr>
                        </thead>                            
                        <tbody>
                        <?php                                    
                            for($i=0; $i < count($userObservations); $i++) {
                                $user = trim($userObservations[$i]['userLastName']);
                                if (trim($userObservations[$i]['userFirstName']) != "") {
                                    if ($user != "") $user .= ", ";
                                    $user .= trim($userObservations[$i]['userFirstName']);
                                }                                    
                        ?>
                            <tr id="<?php echo $userObservations[$i]['id']; ?>">                                                            
                                <td class="text-center col-form-label-sm"><?php echo dateFormat($userObservations[$i]['date'],true); ?></td>                                                                                        
                                <td class="col-form-label-sm"><?php echo str_replace(chr(10),"<br>",$userObservations[$i]['observation']); ?></td>        
                                <td class="col-form-label-sm"><?php echo $user; ?></td>        
                            </tr>                                                       
                        <?php 
                            } 
                        ?>
                        </tbody>
                    </table> 
                </div>                    
            </div> 
            <?php } ?>   
            <?php if ($allowSeeInternalObservations) { ?>   
            <?php if ($allowSave) { ?>
            <?php
                if (validation_errors() != "" || (isset($error) && $error != "")) {
                    $internalObservation = set_value('internalObservation');                    
                } else {
                    $internalObservation = "";
                }
            ?>
            <div class="form-group row">
                <label for="internalObservation" class="col-form-label col-form-label-sm col-xs-3 col-sm-3 col-md-2 col-lg-2 text-right">Observación Interna:</label>
                <div class="col-xs-8 col-sm-8 col-md-9 col-lg-9">                      
                    <textarea id="internalObservation" name="internalObservation" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("internalObservation"); ?>" <?php echo errorTitle("internalObservation"); ?> rows="2" maxlength="500" autocomplete="off"><?php echo $internalObservation; ?></textarea>
                    <div class="remaining-characters-label" id="internalObservationRemaining"></div>                                                            
                </div>                    
            </div>
            <?php } ?>   
            <?php 
                $internalObservations = $order['internalObservations'];
                if (isset($internalObservations)) { 
            ?>     
            <div class="form-group row" <?php echo ($allowSave?"":' style="margin-top:40px;" '); ?>>                    
                <label for="grInternalObservations" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right"></label>                                    
                <div class="col-xs-10 col-sm-10 col-md-10 col-lg-10" style="margin-top:-10px;max-height:175px; overflow-y:auto;">                                        
                    <table class="table table-bordered table-sm" id="grInternalObservations" style="margin-bottom:0px;margin-top:0px;width:auto;">
                        <thead>
                            <tr>                        
                                <th nowrap="nowrap" style="width:120px;" class="col-form-label-sm">Fecha</th>                        
                                <th nowrap="nowrap" style="width:350px;" class="col-form-label-sm">Observación Interna</th>  
                                <th nowrap="nowrap" style="width:200px;" class="col-form-label-sm">Usuario</th>                                      
                            </tr>
                        </thead>                            
                        <tbody>
                        <?php                                    
                            for($i=0; $i < count($internalObservations); $i++) {
                                $user = trim($internalObservations[$i]['userLastName']);
                                if (trim($internalObservations[$i]['userFirstName']) != "") {
                                    if ($user != "") $user .= ", ";
                                    $user .= trim($internalObservations[$i]['userFirstName']);
                                }                                    
                        ?>
                            <tr id="<?php echo $internalObservations[$i]['id']; ?>">                                                            
                                <td class="text-center col-form-label-sm"><?php echo dateFormat($internalObservations[$i]['date'],true); ?></td>                                                                                        
                                <td class="col-form-label-sm"><?php echo str_replace(chr(10),"<br>",$internalObservations[$i]['observation']); ?></td>        
                                <td class="col-form-label-sm"><?php echo $user; ?></td>        
                            </tr>                                                       
                        <?php 
                            } 
                        ?>
                        </tbody>
                    </table> 
                </div>                    
            </div> 
            <?php } ?> 
            <?php } ?>            
            <?php if ($allowSeeAttachments && !$allowSaveOnlyObservation) { ?>
            <div class="form-group row">
                <label class="col-form-label col-form-label-sm col-xs-3 col-sm-3 col-md-2 col-lg-2 text-right">Adjuntos:
                    <?php if ($allowSave) { ?>
                    <button type="button" class="btn without-padding" onclick="openAddFileOrderEdit()" title="agregar archivo(s)">
                        <i class="far fa-plus-square"></i>
                    </button>
                    <?php } ?>
                </label>
                <div class="col-xs-8 col-sm-8 col-md-9 col-lg-9">                                      
                    <div id="attachmentsShow" style="padding-bottom:10px!important;padding-top:5px!important;">
                        <?php 
                            $attachments = $order['attachments'];

                            if (isset($attachments)) {                                    
                                for($i=0; $i < count($attachments); $i++) {
                                    $idx = $i + 1;                                        
                        ?>
                        <div id="attachmentShow<?php echo $idx; ?>" class="btn-group" style="float:left;margin-right:15px;margin-bottom:15px;">
                            <button type="button" class="btn btn-default" onclick="downloadFileOrderEdit(<?php echo $idx; ?>)"><?php echo $attachments[$i]['filename']; ?></button>
                            <?php if ($allowSave && $attachments[$i]['userId'] == $this->session->userdata('userId')) { ?>
                            <button type="button" class="btn btn-default" onclick="confirmRemoveFileOrderEdit(<?php echo $idx; ?>)" title="eliminar archivo"><i class="fas fa-trash-alt"></i></button>
                            <?php } ?>
                        </div>
                        <?php 
                                }
                            } 
                        ?>
                    </div>                                                                                
                    <div id="attachmentsData">
                        <?php 
                            if (isset($attachments)) {                                    
                                for($i=0; $i < count($attachments); $i++) {
                                    $idx = $i + 1;                                        
                        ?>
                        <div id="attachmentData<?php echo $idx; ?>">
                            <input type="hidden" id="attachmentId<?php echo $idx; ?>" name="attachmentId<?php echo $idx; ?>" value="<?php echo $attachments[$i]['id']; ?>">
                            <input type="hidden" id="attachmentName<?php echo $idx; ?>" name="attachmentName<?php echo $idx; ?>" value="<?php echo $attachments[$i]['filename']; ?>">
                            <input type="hidden" id="attachmentFile<?php echo $idx; ?>" name="attachmentFile<?php echo $idx; ?>" value="<?php echo $attachments[$i]['internalFilename']; ?>">
                        </div>
                        <?php 
                                }
                            } 
                        ?>
                    </div>   
                    <input type="hidden" id="attachmentsCount" name="attachmentsCount" value="<?php echo (isset($attachments)?count($attachments):0); ?>">                                                           
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
            <div class="form-group row" style="margin-top:20px;">           
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
                    <?php if ($allowSave) { ?>       
                        <input type="submit" class="btn btn-success type-btn-save" value="Guardar" id="btnSave" onclick="selectActionOrderEdit('')">                                                                                                                                                                     
                        <?php if ($allowPrint) { ?>                        
                        <input type="submit" class="btn btn-success type-btn-save fa-input" value="&#xf022;" id="btnPrint" onclick="selectActionOrderEdit('P|O')" title="imprimir">                                                                                                                                                                                
                        <?php } ?>     
                        <?php if ($allowExport) { ?>
                        <?php if ($allowExportPdf) { ?>
                        <input type="submit" class="btn btn-success type-btn-save fa-input" value="&#xf1c1;" id="btnExportPDF" onclick="selectActionOrderEdit('EP|O')" title="exportar pdf">                                                                                                                                                        
                        <?php } ?>       
                        <input type="submit" class="btn btn-success type-btn-save fa-input" value="&#xf1c3;" id="btnExportExcel" onclick="selectActionOrderEdit('EE|O')" title="exportar excel">                                                                                                                                                        
                        <?php } ?>                                            
                        <button type="button" class="btn btn-danger type-btn-save" onclick="getUrl('<?php echo base_url(); ?>orders/listing/<?php echo $backGet; ?>')">Cancelar</button>                                            
                    <?php } else { ?>
                        <?php if ($allowPrint) { ?>                                                                                                                                                                                            
                        <input type="button" class="btn btn-success type-btn-save fa-input" value="Imprimir" onclick="printOrder(<?php echo $id; ?>,'O')" title="imprimir">
                        <?php } ?>       
                        <?php if ($allowExport) { ?>
                        <?php if ($allowExportPdf) { ?>
                        <input type="button" class="btn btn-success type-btn-save fa-input" value="&#xf1c1;" onclick="exportOrder(<?php echo $id; ?>,'O','P')" title="exportar pdf">                                                                                                                                                        
                        <?php } ?>       
                        <input type="button" class="btn btn-success type-btn-save fa-input" value="&#xf1c3;" onclick="exportOrder(<?php echo $id; ?>,'O','E')" title="exportar excel">                                                                                                                                                        
                        <?php } ?>                                          
                        <button type="button" class="btn btn-danger type-btn-save" onclick="getUrl('<?php echo base_url(); ?>orders/listing/<?php echo $backGet; ?>')">Volver</button>                                            
                    <?php } ?>                      
                </div>
            </div>
            <input type="hidden" id="id" name="id" value="<?php echo $id; ?>">  
            <input type="hidden" id="action" name="action" value="">       
            <input type="hidden" id="seeImports" name="seeImports" value="<?php echo ($allowSeeImports?"1":"0"); ?>">     
            <input type="hidden" id="editImports" name="editImports" value="<?php echo ($allowEditDetailImports?"1":"0"); ?>">                 
            <input type="hidden" id="backGet" name="backGet" value="<?php echo $backGet; ?>">            
        </form>
        <form class="form-horizontal hide" action="" method="post" accept-charset="utf-8" id="frmSearchArticle" name="frmSearchArticle" role="form" onsubmit="return false;">
            <input type="hidden" id="code" name="code" value="">                            
            <div id="resultSearchArticle"></div>                
        </form>
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card --> 
<div id="auxFinder" class="hide"></div>    
<?php 
/* End of file orders_edit_view.php */
/* Location: ./application/views/orders/orders_edit_view.php */
