<?php 
    $id = $stockMovement['id'];        
    $backGet = set_value('backGet',$backGet);         
    
    $disabled = ($allowSave?'':' disabled="disabled" ');   
    $detailReadonly = ($allowSave && $id <= 0?'':' readonly="readonly" ');   
?>
<div class="card card-secondary">
    <div class="card-body">
        <form class="form-horizontal" action="<?php echo base_url(); ?>stockMovements/save" method="post" accept-charset="utf-8" id="frmData" name="frmData" role="form" onsubmit="return preSaveStockMovement();">
            <?php if ($id > 0) { ?>
            <div class="form-group row">                    
                <label for="date" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Fecha:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
                    <input id="date" name="date" class="form-control form-control-sm noEnterMyApp" autocomplete="off" value="<?php echo dateFormat($stockMovement['date'],($stockMovement['registerType']!="BILL")); ?>" style="width:120px;float:left;" readonly="readonly">                    
                </div>    
                <label for="userDescription" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Usuario:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
                    <input id="userDescription" name="userDescription" class="form-control form-control-sm noEnterMyApp" value="<?php echo $stockMovement['userDescription']; ?>" style="float:left;" readonly="readonly">                    
                </div>                                     
            </div>      
            <?php } ?>       
            <div class="form-group row"> 
                <label for="typeId" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Concepto:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">                        
                    <select id="typeId" name="typeId" class="form-control form-control-sm <?php echo errorClass("typeId"); ?>" <?php echo errorTitle("typeId"); ?> required="1" style="width:auto;" <?php echo (count($types) <= 1?' readonly="readonly"':''); ?> <?php echo $disabled; ?> onchange="selectTypeStockMovementEdit()">                            
                        <?php if (count($types) != 1) { ?>
                        <option value="" selected="selected" isInput="0">[Seleccionar]</option>
                        <?php } ?>
                        <?php 
                            for($i=0; $i < count($types); $i++) {
                                if (count($types) == 1) {
                                    $selected = ' selected="selected" ';                  
                                } else {
                                    if (validation_errors() != "" || (isset($error) && $error != "")) {
                                        $selected = set_select('typeId', $types[$i]['id']);
                                    } else {
                                        $selected = ($stockMovement['typeId'] == $types[$i]['id']?' selected="selected" ':'');                  
                                    }
                                }
                        ?>
                        <option value="<?php echo $types[$i]['id']; ?>" isInput="<?php echo $types[$i]['input']; ?>" <?php echo $selected; ?> ><?php echo $types[$i]['description'];?></option>
                        <?php
                            }
                        ?>
                    </select>  
                </div> 
                <label for="companyId" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Empresa:</label>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">                        
                    <select id="companyId" name="companyId" class="form-control form-control-sm <?php echo errorClass("companyId"); ?>" <?php echo errorTitle("companyId"); ?> required="1" style="width:auto;" <?php echo (count($companies) <= 1?' readonly="readonly"':''); ?> <?php echo $disabled; ?> onchange="selectCompanyStockMovementEdit()" >                            
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
                                        $selected = ($stockMovement['companyId'] == $companies[$i]['id']?' selected="selected" ':'');                  
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
                    <select id="warehouseId" name="warehouseId" class="form-control form-control-sm <?php echo errorClass("warehouseId"); ?>" <?php echo errorTitle("warehouseId"); ?> required="1" selValue="<?php echo set_value('warehouseId',$stockMovement['warehouseId']); ?>" style="width:auto;" <?php echo $disabled; ?>>                                                    
                        <option value="" selected="selected">Cargando...</option>                        
                    </select>  
                </div>      
            </div>   
            <div class="form-group row"> 
                <label for="observation" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">Observación:</label>
                <div class="col-xs-10 col-sm-9 col-md-7 col-lg-7"> 
                    <input id="observation" name="observation" class="form-control form-control-sm noEnterMyApp <?php echo errorClass("observation"); ?>" <?php echo errorTitle("observation"); ?> autocomplete="off" maxlength="100"  value="<?php echo set_value('observation',$stockMovement['observation']); ?>" <?php echo $disabled; ?>>                    
                </div>                          
            </div>   
            <div class="form-group row" style="margin-bottom:30px;">
                <label for="details" class="col-form-label col-form-label-sm col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right">
                	Artículos:
                    <?php if ($allowSave) { ?>
                	<p style="font-size:10px;margin-bottom: 0px;">(F2 en Código</p>
                	<p style="font-size:10px;margin-bottom: 0px;">p/ buscar Art.)</p>   
                    <?php } ?>                                     	
                </label>
                <div class="col-xs-10 col-sm-10 col-md-10 col-lg-10">
                    <table class="table table-bstockMovemented table-sm" id="grDetails" style="margin-bottom:0px;margin-top:10px;">
                        <thead>
                            <tr>                        
                                <?php if ($allowSave && $id <= 0) { ?>
                                <th width="35px" class="text-center" style="">                                                       
                                    <button type="button" id="btnNewRow" name="btnNewRow" class="btn without-padding" onclick="newRowDetailStockMovementEdit()" title="nuevo">
                                        <i class="far fa-plus-square"></i>
                                    </button> 
                                </th>
                                <?php } ?>                                
                                <th nowrap="nowrap" style="width:100px;" class="col-form-label-sm">Código</th>                        
                                <th nowrap="nowrap" style="width:250px;" class="col-form-label-sm">Descripción</th>  
                                <th nowrap="nowrap" style="width:150px;" class="col-form-label-sm">Rubro</th>  
                                <th nowrap="nowrap" style="width:60px;" class="col-form-label-sm">Cant.</th>                                
                                <th nowrap="nowrap" style="width:60px;" class="col-form-label-sm colUnitPrice <?php echo ((int)$stockMovement['input'] == 1?"":"hide"); ?>">Costo Unit.</th>  
                                <?php if ($allowSave && $id <= 0) { ?>
                                <th nowrap="nowrap" style="width:60px;" class="col-form-label-sm">Stock</th>  
                                <?php } ?>                                
                            </tr>
                        </thead>                            
                        <tbody>
                        <?php 
                            $details = $stockMovement['details'];                                

                            $detailsCount = 0;
                            if (isset($details)) { 
                                $detailsCount = count($details);
                        ?>                            
                            <?php                                    
                                for($i=0; $i < count($details); $i++) {
                                    $row = $i + 1;                                       
                            ?>
                            <tr id="<?php echo $row; ?>">
                                <?php 
                                    $quantityClass = "";
                                    if ($allowSave && $id <= 0) {                                         
                                ?>
                                <td class="text-center without-padding">                                                                                                                                                
                                    <button type="button" class="btn without-padding" onclick="deleteRowDetailStockMovementEdit(<?php echo $row; ?>)" title="eliminar">
                                        <i class="far fa-minus-square"></i>
                                    </button>   
                                </td>
                                <?php                                    
                                        if (isset($details[$i]['outOfStock']) && $details[$i]['outOfStock'] == true) {
                                            $quantityClass = "outOfStock";
                                        }
                                    }
                                ?>                                                                  
                                <td class="without-padding"><input type="text" id="detailCode<?php echo $row; ?>" name="detailCode<?php echo $row; ?>" class="form-control form-control-sm detailColumn" maxlength="25" autocomplete="off" value="<?php echo $details[$i]['code']; ?>" <?php echo $detailReadonly; ?> onblur="searchArticleStockMovementEdit(<?php echo $row; ?>)"></td>                                                    
                                <td class="without-padding"><input type="text" id="detailDescription<?php echo $row; ?>" name="detailDescription<?php echo $row; ?>" class="form-control form-control-sm detailColumn" maxlength="100" autocomplete="off" value="<?php echo $details[$i]['description']; ?>" readonly="readonly"></td>                                    
                                <td class="without-padding"><input type="text" id="detailFamily<?php echo $row; ?>" name="detailFamily<?php echo $row; ?>" class="form-control form-control-sm detailColumn" maxlength="100" autocomplete="off" value="<?php echo $details[$i]['familyDescription']; ?>" readonly="readonly"></td>                                                                  
                                <td class="without-padding"><input type="number" min="0" step="1" id="detailQuantity<?php echo $row; ?>" name="detailQuantity<?php echo $row; ?>" class="form-control form-control-sm detailColumn text-right <?php echo $quantityClass; ?>" maxlength="3" autocomplete="off" value="<?php echo $details[$i]['quantity']; ?>" <?php echo $detailReadonly; ?> onfocus="this.select();"></td>                                
                                <td class="without-padding colUnitPrice <?php echo ((int)$stockMovement['input'] == 1?"":"hide"); ?>"><input type="number" min="0.01" step="0.01" id="detailUnitPrice<?php echo $row; ?>" name="detailUnitPrice<?php echo $row; ?>" class="form-control form-control-sm detailColumn text-right inputUnitPrice" maxlength="12" autocomplete="off" value="<?php echo decimalFormat($details[$i]['unitPrice'],2); ?>" <?php echo $detailReadonly; ?> onfocus="this.select();"></td>                                                                 
                                <?php if ($allowSave && $id <= 0) { ?>
                                <td class="without-padding text-center">                                            
                                    <button type="button" class="btn without-padding" onclick="seeStockOfStockMovementEdit(<?php echo $row; ?>)" title="ver stock"><i class="fa fa-chart-bar"></i></button>
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
                        <button type="button" class="btn btn-danger type-btn-save" onclick="getUrl('<?php echo base_url(); ?>stockMovements/listing/<?php echo $backGet; ?>')">Cancelar</button>                                            
                    <?php } else { ?>                                                            
                        <button type="button" class="btn btn-danger type-btn-save" onclick="getUrl('<?php echo base_url(); ?>stockMovements/listing/<?php echo $backGet; ?>')">Volver</button>                                            
                    <?php } ?>                      
                </div>
            </div>
            <input type="hidden" id="id" name="id" value="<?php echo $id; ?>">              
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
/* End of file stockMovements_edit_view.php */
/* Location: ./application/views/stockMovements/stockMovements_edit_view.php */