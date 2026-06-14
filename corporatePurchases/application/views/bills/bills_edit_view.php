<?php     
    $id = $bill['id'];        
    $backGet = set_value('backGet',$backGet);         
    
    $disabled = ($allowSave?'':' disabled="disabled" ');      
    $numerationDataReadonly = ($allowSave && $allowEditNumerationData?'':' readonly="readonly" ');      
    $generalDataReadonly = ($allowSave && $allowEditGeneralData?'':' readonly="readonly" ');      
    $detailReadonly = ($allowEditDetail?'':' readonly="readonly" '); 
    //$detailReadonlyImports = ($allowEditDetailImports?'':' readonly="readonly" '); 
?>
<div class="card card-secondary">
    <div class="card-body">
        <form class="form-horizontal" action="<?php echo base_url(); ?>bills/save" method="post" accept-charset="utf-8" id="frmData" name="frmData" role="form" onsubmit="return preSaveBill();">
            <div class="form-group row">                    
                <label for="date" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Fecha Carga:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
                    <input type="date" id="date" name="date" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("date"); ?>" placeholder="--/--/----" <?php echo errorTitle("date"); ?> autocomplete="off" value="<?php echo set_value('date',dateFormatMysql(dateFormat($bill['date']))); ?>" style="width:120px;float:left;" readonly="readonly">                    
                </div> 
                <label for="userDescription" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Usuario:</label>
                <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3"> 
                    <input id="userDescription" name="userDescription" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("userDescription"); ?>" <?php echo errorTitle("userDescription"); ?> autocomplete="off" maxlength="50"  value="<?php echo set_value('userDescription',$bill['userDescription']); ?>" readonly="readonly">                    
                </div> 
            </div>   
            <div class="form-group row">
                <label for="companyId" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Empresa:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">                        
                    <select id="companyId" name="companyId" class="form-control form-control-sm <?php echo errorClass("companyId"); ?>" <?php echo errorTitle("companyId"); ?> required="1" style="width:auto;" <?php echo (count($companies) <= 1?' readonly="readonly"':''); ?> <?php echo $generalDataReadonly; ?> onchange="selectCompanyBillEdit()" >                            
                        <?php if (count($companies) != 1) { ?>
                        <option value="" selected="selected">[Seleccionar]</option>
                        <?php } ?>
                        <?php 
                            for($i=0; $i < count($companies); $i++) {
                                if (count($companies) == 1) {
                                    $selected = ' selected="selected" ';                  
                                } else {
                                    if (validation_errors() != "" || (isset($error) && $error != "")) {
                                        $selected = set_select('companyId', $companies[$i]['id']);
                                    } else {
                                        $selected = ($bill['companyId'] == $companies[$i]['id']?' selected="selected" ':'');                  
                                    }
                                }
                        ?>
                        <option value="<?php echo $companies[$i]['id']; ?>" <?php echo $selected; ?> ><?php echo $companies[$i]['description'];?></option>
                        <?php
                            }
                        ?>
                    </select>  
                </div>
                <label for="warehouseId" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Depósito:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">                        
                    <select id="warehouseId" name="warehouseId" class="form-control form-control-sm <?php echo errorClass("warehouseId"); ?>" <?php echo errorTitle("warehouseId"); ?> required="1" selValue="<?php echo set_value('warehouseId',$bill['warehouseId']); ?>" style="width:auto;" <?php echo $generalDataReadonly; ?>>                                                    
                        <option value="" selected="selected">Cargando...</option>                        
                    </select>  
                </div>
            </div>
            <div class="form-group row">                    
                <label for="billDate" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Fecha Factura:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
                    <input type="date" id="billDate" name="billDate" <?php echo ($id <= 0?' min="'.getCurrentDate(false,-30).'" ':""); ?> <?php echo ($id <= 0?' max="'.getCurrentDate(false).'" ':""); ?> class="form-control form-control-sm noEnterMyApp <?php echo errorClass("billDate"); ?>" placeholder="--/--/----" <?php echo errorTitle("billDate"); ?> autocomplete="off" required="1" value="<?php echo set_value('billDate',$bill['billDate']); ?>" style="width:150px;float:left;" <?php echo $numerationDataReadonly; ?>>                    
                </div> 
                <label for="letter" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Nº Factura:</label>
                <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">                     
                    <input id="letter" name="letter" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("letter"); ?>" <?php echo errorTitle("letter"); ?> autocomplete="1" maxlength="1" placeholder="X" required="1" value="<?php echo set_value('letter',$bill['letter']); ?>" <?php echo $numerationDataReadonly; ?> style="width:30px;float:left;">    
                    <input id="serie" name="serie" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("serie"); ?>" <?php echo errorTitle("serie"); ?> autocomplete="1" maxlength="5" placeholder="00000" required="1" value="<?php echo set_value('serie',$bill['serie']); ?>" <?php echo $numerationDataReadonly; ?> style="width:60px;float:left;margin-left:5px;">    
                    <input id="number" name="number" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("number"); ?>" <?php echo errorTitle("number"); ?> autocomplete="1" maxlength="8" placeholder="00000000" required="1" value="<?php echo set_value('number',$bill['number']); ?>" <?php echo $numerationDataReadonly; ?> style="width:80px;float:left;margin-left:5px;">    
                </div> 
            </div>  
            <div class="form-group row">                    
                <label for="supplierCode" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">CUIT:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
                    <input id="supplierCode" name="supplierCode" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("supplierCode"); ?>" placeholder="CUIT" <?php echo errorTitle("supplierCode"); ?> autocomplete="off" value="<?php echo set_value('supplierCode',$bill['supplierCode']); ?>" style="width:120px;float:left;" <?php echo $generalDataReadonly; ?> onblur="searchSupplierBillEdit()" required="1">                    
                    <input type="hidden" id="supplierCodeOriginal" name="supplierCodeOriginal" value="<?php echo set_value('supplierCode',$bill['supplierCode']); ?>"> 
                    <input type="hidden" id="supplierId" name="supplierId" value="<?php echo set_value('supplierId',$bill['supplierId']); ?>"> 
                </div> 
                <label for="supplierDescription" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Nombre Comercial:</label>
                <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3"> 
                    <input id="supplierDescription" name="supplierDescription" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("supplierDescription"); ?>" <?php echo errorTitle("supplierDescription"); ?> autocomplete="off" maxlength="100"  value="<?php echo set_value('supplierDescription',$bill['supplierDescription']); ?>" readonly="readonly">                    
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
                                    <button type="button" id="btnNewRow" name="btnNewRow" class="btn without-padding" onclick="newRowDetailBillEdit()" title="nuevo">
                                        <i class="far fa-plus-square"></i>
                                    </button> 
                                </th>
                                <?php } ?>                                
                                <th nowrap="nowrap" style="width:100px;" class="col-form-label-sm">Código</th>                        
                                <th nowrap="nowrap" style="width:250px;" class="col-form-label-sm">Descripción</th>                                  
                                <th nowrap="nowrap" style="width:100px;" class="col-form-label-sm">Costo s/IVA ($)</th>                                  
                                <th nowrap="nowrap" style="width:60px;" class="col-form-label-sm">Cant.</th>                                  
                                <th nowrap="nowrap" style="width:150px;" class="col-form-label-sm">Rubro</th>                                  
                                <th nowrap="nowrap" style="width:100px;" class="col-form-label-sm">Total</th>  
                                <?php if (!$allowEditDetail) { ?>  
                                <th nowrap="nowrap" style="width:30px;" class="col-form-label-sm"></th>    
                                <?php } ?>                                                             
                            </tr>
                        </thead>                            
                        <tbody>
                        <?php 
                            $details = $bill['details'];

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
                                    <button type="button" class="btn without-padding" onclick="deleteRowDetailBillEdit(<?php echo $row; ?>)" title="eliminar">
                                        <i class="far fa-minus-square"></i>
                                    </button>   
                                </td>
                                <?php } ?>                                    
                                <td class="without-padding"><input type="text" id="detailCode<?php echo $row; ?>" name="detailCode<?php echo $row; ?>" class="form-control form-control-sm detailColumn" maxlength="25" autocomplete="off" value="<?php echo $details[$i]['code']; ?>" <?php echo $detailReadonly; ?> onblur="searchArticleBillEdit(<?php echo $row; ?>)"></td>                                                    
                                <td class="without-padding"><input type="text" id="detailDescription<?php echo $row; ?>" name="detailDescription<?php echo $row; ?>" class="form-control form-control-sm detailColumn" maxlength="100" autocomplete="off" value="<?php echo $details[$i]['description']; ?>" readonly="readonly"></td>                                                                    
                                <td class="without-padding"><input type="number" min="0.01" step="0.01" id="detailUnitPrice<?php echo $row; ?>" name="detailUnitPrice<?php echo $row; ?>" class="form-control form-control-sm detailColumn text-right" maxlength="12" autocomplete="off" value="<?php echo decimalFormat($details[$i]['unitPrice'],2); ?>" <?php echo $detailReadonly; ?> onchange="calculateTotalBillEdit();" onfocus="this.select();"></td>                                
                                <td class="without-padding"><input type="number" min="1" step="1" id="detailQuantity<?php echo $row; ?>" name="detailQuantity<?php echo $row; ?>" class="form-control form-control-sm detailColumn text-right" maxlength="3" autocomplete="off" value="<?php echo (int)$details[$i]['quantity']; ?>" <?php echo $detailReadonly; ?> onchange="calculateTotalBillEdit()" onfocus="this.select();"></td>
                                <td class="without-padding"><input type="text" id="detailFamily<?php echo $row; ?>" name="detailFamily<?php echo $row; ?>" class="form-control form-control-sm detailColumn" maxlength="100" autocomplete="off" value="<?php echo $details[$i]['familyDescription']; ?>" readonly="readonly"></td>                                    
                                <td class="without-padding"><input type="number" min="0" step="0.01" id="detailTotal<?php echo $row; ?>" name="detailTotal<?php echo $row; ?>" class="form-control form-control-sm detailColumn text-right" maxlength="12" autocomplete="off" value="<?php echo decimalFormat($details[$i]['total'],2); ?>" readonly="readonly"></td>                                                                
                                <?php if (!$allowEditDetail) { ?> 
                                <td nowrap="nowrap" class="without-padding text-center">
                                    <button type="button" class="btn without-padding" onclick="seeBillItemData(<?php echo $row; ?>)" title="ver detalles"><i class="fa fa-list"></i></button>
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
                        </div>
                        <?php   } 
                            }
                        ?>
                        <input type="hidden" id="detailsCount" name="detailsCount" value="<?php echo $detailsCount; ?>">     
                        <input type="hidden" id="rowArticlesFinder" name="rowArticlesFinder" value="">      
                    </div>
                </div>                    
            </div>              
            <div class="form-group row">
                <label for="total" class="col-form-label col-form-label-sm col-xs-3 col-sm-3 col-md-2 col-lg-2 text-right">Total s/IVA: $</label>                                        
                <div class="col-xs-9 col-sm-9 col-md-10 col-lg-10">                    
                    <input type="number" id="total" name="total" class="form-control form-control-sm noEnterMyApp text-right" autocomplete="off" value="<?php echo decimalFormat(set_value('total',$bill['total']),2); ?>" style="width:110px;" disabled="disabled">                                                
                </div>                    
            </div>
            <div class="form-group row">
                <label for="observation" class="col-form-label col-form-label-sm col-xs-3 col-sm-3 col-md-2 col-lg-2 text-right">Observación:</label>
                <div class="col-xs-8 col-sm-8 col-md-9 col-lg-9">                      
                    <textarea id="observation" name="observation" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("observation"); ?>" <?php echo errorTitle("observation"); ?> rows="4" maxlength="1000" autocomplete="off" <?php echo $generalDataReadonly; ?>><?php echo set_value('observation',$bill['observation']); ?></textarea>
                    <div class="remaining-characters-label" id="observationRemaining"></div>                                                            
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
                        <button type="button" class="btn btn-danger type-btn-save" onclick="getUrl('<?php echo base_url(); ?>bills/listing/<?php echo $backGet; ?>')">Cancelar</button>                                            
                    <?php } else { ?>                                                    
                        <button type="button" class="btn btn-danger type-btn-save" onclick="getUrl('<?php echo base_url(); ?>bills/listing/<?php echo $backGet; ?>')">Volver</button>                                            
                    <?php } ?>                      
                </div>
            </div>
            <input type="hidden" id="id" name="id" value="<?php echo $id; ?>">              
            <input type="hidden" id="backGet" name="backGet" value="<?php echo $backGet; ?>">            
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
<?php 
/* End of file bills_edit_view.php */
/* Location: ./application/views/bills/bills_edit_view.php */