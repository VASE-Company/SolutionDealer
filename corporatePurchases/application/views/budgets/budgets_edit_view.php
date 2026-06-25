<?php     
    $id = $budget['id'];            
    
    $disabled = ($allowSave?'':' disabled="disabled" ');            
    $detailReadonly = ($allowEditDetail?'':' readonly="readonly" ');     
?>
<div class="card card-secondary">
    <div class="card-body">
        <form class="form-horizontal" action="<?php echo base_url(); ?>budgets/save" method="post" accept-charset="utf-8" id="frmData" name="frmData" role="form" onsubmit="return preSaveBudget();">
            <div class="form-group row">                    
                <label for="date" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Fecha Carga:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
                    <input type="date" id="date" name="date" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("date"); ?>" placeholder="--/--/----" <?php echo errorTitle("date"); ?> autocomplete="off" value="<?php echo set_value('date',dateFormatMysql(dateFormat($budget['date']))); ?>" style="width:120px;float:left;" readonly="readonly">                    
                </div> 
                <label for="userDescription" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Usuario:</label>
                <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3"> 
                    <input id="userDescription" name="userDescription" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("userDescription"); ?>" <?php echo errorTitle("userDescription"); ?> autocomplete="off" maxlength="50"  value="<?php echo set_value('userDescription',$budget['userDescription']); ?>" readonly="readonly">                    
                </div> 
            </div>               
            <div class="form-group row">                    
                <label for="budgetDate" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Fecha Presupuesto:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
                    <input type="date" id="budgetDate" name="budgetDate" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("budgetDate"); ?>" placeholder="--/--/----" <?php echo errorTitle("budgetDate"); ?> autocomplete="off" required="1" value="<?php echo set_value('budgetDate',$budget['budgetDate']); ?>" style="width:120px;float:left;" <?php echo $disabled; ?>>                    
                </div> 
                <label for="originId" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Origen:</label>
                <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">                                                             
                    <select id="originId" name="originId" class="form-control form-control-sm <?php echo errorClass("originId"); ?>" <?php echo errorTitle("originId"); ?> required="1" style="width:auto;" <?php echo (count($origins) <= 1?' readonly="readonly"':''); ?> <?php echo $disabled; ?> >                            
                        <?php if (count($origins) != 1) { ?>
                        <option value="" selected="selected">[Seleccionar]</option>
                        <?php } ?>
                        <?php 
                            for($i=0; $i < count($origins); $i++) {
                                if (count($origins) == 1) {
                                    $selected = ' selected="selected" ';                  
                                } else {
                                    if (validation_errors() != "" || (isset($error) && $error != "")) {
                                        $selected = set_select('originId', $origins[$i]['id']);
                                    } else {
                                        $selected = ($budget['originId'] == $origins[$i]['id']?' selected="selected" ':'');                  
                                    }
                                }
                        ?>
                        <option value="<?php echo $origins[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo $origins[$i]['description'];?></option>
                        <?php
                            }
                        ?>
                    </select>                  
                </div> 
            </div>  
            <div class="form-group row">                    
                <label for="supplierCode" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">CUIT (F2 buscar):</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
                    <input id="supplierCode" name="supplierCode" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("supplierCode"); ?>" placeholder="CUIT" <?php echo errorTitle("supplierCode"); ?> autocomplete="off" value="<?php echo set_value('supplierCode',$budget['supplierCode']); ?>" style="width:120px;float:left;" <?php echo $disabled; ?> onblur="searchSupplierBudgetEdit()" required="1">                    
                    <input type="hidden" id="supplierCodeOriginal" name="supplierCodeOriginal" value="<?php echo set_value('supplierCode',$budget['supplierCode']); ?>"> 
                    <input type="hidden" id="supplierId" name="supplierId" value="<?php echo set_value('supplierId',$budget['supplierId']); ?>"> 
                </div> 
                <label for="supplierDescription" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Nombre Comercial:</label>
                <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3"> 
                    <input id="supplierDescription" name="supplierDescription" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("supplierDescription"); ?>" <?php echo errorTitle("supplierDescription"); ?> autocomplete="off" maxlength="100"  value="<?php echo set_value('supplierDescription',$budget['supplierDescription']); ?>" readonly="readonly">                    
                </div> 
            </div>   
            <div class="form-group row">                    
                <label for="billLetter" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Factura:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
                    <select id="billLetter" name="billLetter" class="form-control form-control-sm <?php echo errorClass("billLetter"); ?>" <?php echo errorTitle("billLetter"); ?> required="1" style="width:auto;" <?php echo $disabled; ?> >                            
                        <?php 
                            if (validation_errors() != "" || (isset($error) && $error != "")) {
                                $selected = set_select('billLetter', $budget['billLetter']);
                            } else {
                                $selected = ($budget['billLetter'] == ""?' selected="selected" ':'');                  
                            }
                        ?>
                        <option value="" <?php echo $selected; ?>>--</option>                                                
                        <?php 
                            for ($i=0; $i < count($billLetters); $i++) {
                                if (validation_errors() != "" || (isset($billLetters) && $error != "")) {
                                    $selected = set_select('billLetter', $billLetters[$i]['id']);
                                } else {
                                    $selected = ($budget['billLetter'] == $billLetters[$i]['id']?' selected="selected" ':'');                  
                                }
                        ?>
                        <option value="<?php echo $billLetters[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo $billLetters[$i]['description'];?></option>
                        <?php
                            }
                        ?>
                    </select>  
                </div> 
                <label for="paymentPlan" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Forma de Pago:</label>
                <div class="col-xs-6 col-sm-6 col-md-6 col-lg-5"> 
                    <select id="paymentMethodId" name="paymentMethodId" class="form-control form-control-sm <?php echo errorClass("paymentMethodId"); ?>" <?php echo errorTitle("paymentMethodId"); ?> required="1" style="width:auto;float:left;" <?php echo (count($paymentMethods) <= 1?' readonly="readonly"':''); ?> <?php echo $disabled; ?> >                            
                        <?php if (count($paymentMethods) != 1) { ?>
                        <option value="" selected="selected">[Seleccionar]</option>
                        <?php } ?>
                        <?php 
                            for($i=0; $i < count($paymentMethods); $i++) {
                                if (count($paymentMethods) == 1) {
                                    $selected = ' selected="selected" ';                  
                                } else {
                                    if (validation_errors() != "" || (isset($error) && $error != "")) {
                                        $selected = set_select('paymentMethodId', $paymentMethods[$i]['id']);
                                    } else {
                                        $selected = ($budget['paymentMethodId'] == $paymentMethods[$i]['id']?' selected="selected" ':'');                  
                                    }
                                }
                        ?>
                        <option value="<?php echo $paymentMethods[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo $paymentMethods[$i]['description'];?></option>
                        <?php
                            }
                        ?>
                    </select> 
                    <input id="paymentPlan" name="paymentPlan" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("paymentPlan"); ?>" <?php echo errorTitle("paymentPlan"); ?> autocomplete="off" maxlength="100"  value="<?php echo set_value('paymentPlan',$budget['paymentPlan']); ?>" placeholder="Plan de Pago" required="1" <?php echo $disabled; ?> style="float:left;width:300px;margin-left:5px;">                    
                </div>              
            </div>   
            <div class="form-group row" style="margin-bottom:30px;">
                <label class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">
                    Insumos:                                    
                </label>
                <div class="col-xs-10 col-sm-10 col-md-10 col-lg-10">
                    <table class="table table-bordered table-sm" id="grDetails" style="margin-bottom:0px;margin-top:10px;">
                        <thead>
                            <tr>                        
                                <?php if ($allowEditDetail) { ?>
                                <th width="35px" class="text-center" style="">                                                       
                                    <button type="button" id="btnAddArticle" name="btnNewRow" class="btn without-padding" onclick="openArticlesFinderBudgetEdit()" title="agregar">
                                        <i class="far fa-plus-square"></i>
                                    </button> 
                                </th>
                                <?php } ?>                                
                                <th nowrap="nowrap" style="width:100px;" class="col-form-label-sm">Código</th>                        
                                <th nowrap="nowrap" style="width:250px;" class="col-form-label-sm">Descripción</th>                                  
                                <th nowrap="nowrap" style="width:100px;" class="col-form-label-sm">Costo s/IVA ($)</th>                                  
                                <th nowrap="nowrap" style="width:60px;" class="col-form-label-sm">IVA</th>
                                <th nowrap="nowrap" style="width:60px;" class="col-form-label-sm">Cant.</th>                                  
                                <th nowrap="nowrap" style="width:150px;" class="col-form-label-sm">Rubro</th>                                  
                                <th nowrap="nowrap" style="width:100px;" class="col-form-label-sm">Total</th>                                                               
                            </tr>
                        </thead>                            
                        <tbody>
                        <?php 
                            $details = $budget['details'];                            

                            $detailsCount = 0;
                            if (isset($details)) { 
                                $detailsCount = count($details);
                        ?>                            
                            <?php                                    
                                for($i=0; $i < count($details); $i++) {
                                    $row = $i + 1;                                       
                            ?>
                            <tr id="<?php echo $row; ?>">
                                <?php if ($allowEditDetail) { ?>
                                <td class="text-center without-padding">                                                                                                                                                
                                    <button type="button" class="btn without-padding" onclick="deleteRowDetailBudgetEdit(<?php echo $row; ?>)" title="eliminar">
                                        <i class="far fa-minus-square"></i>
                                    </button>   
                                </td>
                                <?php } ?>                                    
                                <td class="without-padding"><input type="text" id="detailCode<?php echo $row; ?>" name="detailCode<?php echo $row; ?>" class="form-control form-control-sm detailColumn" maxlength="25" autocomplete="off" value="<?php echo $details[$i]['code']; ?>" readonly="readonly"></td>                                                    
                                <td class="without-padding"><input type="text" id="detailDescription<?php echo $row; ?>" name="detailDescription<?php echo $row; ?>" class="form-control form-control-sm detailColumn" maxlength="100" autocomplete="off" value="<?php echo $details[$i]['description']; ?>" readonly="readonly"></td>                                                                    
                                <td class="without-padding"><input type="number" min="0" step="0.01" id="detailUnitPrice<?php echo $row; ?>" name="detailUnitPrice<?php echo $row; ?>" class="form-control form-control-sm detailColumn text-right" maxlength="12" autocomplete="off" value="<?php echo decimalFormat($details[$i]['unitPrice'],2); ?>" <?php echo $detailReadonly; ?> onchange="calculateTotalBudgetEdit();" onfocus="this.select();"></td>                                
                                <td class="without-padding">
                                    <?php if ($allowEditDetail) { ?>
                                    <select id="detailTaxPercentage<?php echo $row; ?>" name="detailTaxPercentage<?php echo $row; ?>" class="form-control form-control-sm detailColumn" onchange="calculateTotalBudgetEdit();" >                            
                                    <?php 
                                        for ($i=0; $i < count($taxPercentages); $i++) {
                                            if (validation_errors() != "" || $error != "") {
                                                $selected = set_select('detailTaxPercentage'.$row, $taxPercentages[$i]['id']);                                                
                                            } else {
                                                $selected = ($i == 0?' selected="selected" ':'');                  
                                            }
                                    ?>
                                    <option value="<?php echo $taxPercentages[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo $taxPercentages[$i]['description'];?></option>
                                    <?php
                                        }
                                    ?>
                                    </select>
                                    <?php } else { ?>                          
                                    <input type="number" min="0" step="0.01" id="detailTaxPercentage<?php echo $row; ?>" name="detailTaxPercentage<?php echo $row; ?>" class="form-control form-control-sm detailColumn text-right" maxlength="5" autocomplete="off" value="<?php echo decimalFormat((float)$details[$i]['taxPercentage'],2); ?>" readonly="readonly" onchange="calculateTotalBudgetEdit()" onfocus="this.select();">
                                    <?php } ?>                          
                                </td>
                                <td class="without-padding"><input type="number" min="1" step="1" id="detailQuantity<?php echo $row; ?>" name="detailQuantity<?php echo $row; ?>" class="form-control form-control-sm detailColumn text-right" maxlength="3" autocomplete="off" value="<?php echo (int)$details[$i]['quantity']; ?>" readonly="readonly" onchange="calculateTotalBudgetEdit()" onfocus="this.select();"></td>
                                <td class="without-padding"><input type="text" id="detailFamily<?php echo $row; ?>" name="detailFamily<?php echo $row; ?>" class="form-control form-control-sm detailColumn" maxlength="100" autocomplete="off" value="<?php echo $details[$i]['familyDescription']; ?>" readonly="readonly"></td>                                    
                                <td class="without-padding"><input type="number" min="0" step="0.01" id="detailTotal<?php echo $row; ?>" name="detailTotal<?php echo $row; ?>" class="form-control form-control-sm detailColumn text-right" maxlength="12" autocomplete="off" value="<?php echo decimalFormat($details[$i]['total'],2); ?>" readonly="readonly"></td>                                                                   
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
                            <input type="hidden" id="detailItemOrderId<?php echo $row; ?>" name="detailItemOrderId<?php echo $row; ?>" value="<?php echo $details[$i]['detailOrderId']; ?>">                                 
                        </div>
                        <?php   } 
                            }
                        ?>
                        <input type="hidden" id="detailsCount" name="detailsCount" value="<?php echo $detailsCount; ?>">                             
                    </div>
                </div>                    
            </div>              
            <div class="form-group row">
                <label for="total" class="col-form-label col-form-label-sm col-xs-3 col-sm-3 col-md-2 col-lg-2 text-right">Total s/IVA: $</label>                                        
                <div class="col-xs-9 col-sm-9 col-md-10 col-lg-10">                    
                    <input type="number" id="total" name="total" class="form-control form-control-sm noEnterMyApp text-right" autocomplete="off" value="<?php echo decimalFormat(set_value('total',$budget['total']),2); ?>" style="width:110px;" disabled="disabled">                                                
                </div>                    
            </div>
            <div class="form-group row">
                <label for="observation" class="col-form-label col-form-label-sm col-xs-3 col-sm-3 col-md-2 col-lg-2 text-right">Observación:</label>
                <div class="col-xs-8 col-sm-8 col-md-9 col-lg-9">                      
                    <textarea id="observation" name="observation" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("observation"); ?>" <?php echo errorTitle("observation"); ?> rows="4" maxlength="1000" autocomplete="off" <?php echo $disabled; ?>><?php echo set_value('observation',$budget['observation']); ?></textarea>
                    <div class="remaining-characters-label" id="observationRemaining"></div>                                                            
                </div>                    
            </div>                 
            <div class="form-group row">
                <label class="col-form-label col-form-label-sm col-xs-3 col-sm-3 col-md-2 col-lg-2 text-right">Adjuntos:
                    <?php if ($allowSave) { ?>
                    <button type="button" class="btn without-padding" onclick="openAddFileBudgetEdit()" title="agregar archivo(s)">
                        <i class="far fa-plus-square"></i>
                    </button>
                    <?php } ?>
                </label>
                <div class="col-xs-8 col-sm-8 col-md-9 col-lg-9">                                      
                    <div id="attachmentsShow" style="padding-bottom:10px!important;padding-top:5px!important;">
                        <?php 
                            $attachments = $budget['attachments'];

                            if (isset($attachments)) {                                    
                                for($i=0; $i < count($attachments); $i++) {
                                    $idx = $i + 1;                                        
                        ?>
                        <div id="attachmentShow<?php echo $idx; ?>" class="btn-group" style="float:left;margin-right:15px;margin-bottom:15px;">
                            <button type="button" class="btn btn-default" onclick="downloadFileBudgetEdit(<?php echo $idx; ?>)"><?php echo $attachments[$i]['filename']; ?></button>
                            <?php if ($allowSave && $attachments[$i]['userId'] == $this->session->userdata('userId')) { ?>
                            <button type="button" class="btn btn-default" onclick="confirmRemoveFileBudgetEdit(<?php echo $idx; ?>)" title="eliminar archivo"><i class="fas fa-trash-alt"></i></button>
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
                        <input type="submit" class="btn btn-success type-btn-save" value="Guardar" id="btnSave">                                                                                                                                                                                                                               
                        <button type="button" class="btn btn-danger type-btn-save" onclick="getUrl('<?php echo base_url(); ?>orders/edit/<?php echo $budget['orderId']; ?>')">Cancelar</button>                                            
                    <?php } else { ?>                                                    
                        <button type="button" class="btn btn-danger type-btn-save" onclick="window.close();">Cerrar</button>                                            
                    <?php } ?>                      
                </div>
            </div>
            <input type="hidden" id="id" name="id" value="<?php echo $id; ?>">                          
            <input type="hidden" id="orderId" name="orderId" value="<?php echo $budget['orderId']; ?>">       
        </form>
        <form class="form-horizontal hide" action="" method="post" accept-charset="utf-8" id="frmSearchAux" name="frmSearchAux" role="form" onsubmit="return false;">
            <input type="hidden" id="code" name="code" value="">                
            <div id="resultSearch"></div>                
        </form>
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card --> 
<div id="auxFinder" class="hide"></div>
<select id="taxPercentage" name="taxPercentage" class="hide">                            
<?php 
    for ($i=0; $i < count($taxPercentages); $i++) {        
        $selected = ($i == 0?' selected="selected" ':'');   
?>
<option value="<?php echo $taxPercentages[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo $taxPercentages[$i]['description'];?></option>
<?php
    }
?>
</select>    
<?php 
/* End of file budgets_edit_view.php */
/* Location: ./application/views/budgets/budgets_edit_view.php */