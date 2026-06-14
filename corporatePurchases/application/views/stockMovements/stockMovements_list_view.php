<div class="container-fluid">
    <div class="row">
        <div class="col-xs-12 col-sm-120 col-md-12 col-lg-12">
            <div class="card card-primary collapsed-card">
                <div class="card-header">
                    <h3 class="card-title">Buscar por...</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse" title="buscar según filtros aplicados" onclick="generalSearch()"><i class="fas fa-search"></i></button>              
                        <button type="button" class="btn btn-tool" data-card-widget="collapse" title="limpiar filtros" onclick="clearFilter('frmFilter')"><i class="fa fa-undo"></i></button>              
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>                        
                    </div>
                    <!-- /.card-tools -->
                </div>
                <!-- /.card-header -->
                <div class="card-body" style="display: none;">
                    <div class="col-12"> 
                        <form action="<?php echo base_url(); ?>stockMovements/search" method="post" accept-charset="utf-8" id="frmFilter" name="frmFilter" role="form">                                                            
                            <div class="row">
                                <div class="col-12">                                                                        
                                    <div class="form-group" style="float:left;margin-right:10px;">
                                        <label for="dateFromFilter">Desde</label>
                                        <input type="date" id="dateFromFilter" name="dateFromFilter" class="form-control form-control-sm noEnterMyApp" autocomplete="off"  autofocus="1" value="<?php echo $dateFromFilter; ?>" original="<?php echo $dateFromFilter; ?>" style="width:140px;">
                                    </div>   
                                    <div class="form-group" style="float:left;margin-right:10px;">
                                        <label for="dateToFilter">Hasta</label>
                                        <input type="date" id="dateToFilter" name="dateToFilter" class="form-control form-control-sm noEnterMyApp" autocomplete="off" value="<?php echo $dateToFilter; ?>" original="<?php echo $dateFromFilter; ?>" style="width:140px;">                
                                    </div>     
                                    <div class="form-group" style="width:auto; float:left; margin-right:10px;">
                                        <label for="inputFilter">Tipo</label>
                                        <select id="inputFilter" name="inputFilter" class="form-control form-control-sm">
                                            <?php 
                                                $inputFilter = set_value('inputFilter',$inputFilter);
                                                
                                                $selected = ($inputFilter == ""?' selected="selected" ':''); 
                                            ?>
                                            <option value="" <?php echo $selected; ?>>[Todos]</option> 
                                            <?php                                                         
                                                for ($i=0; $i < count($types); $i++) {         
                                                    $selected = ($types[$i]['id'] == $inputFilter?' selected="selected" ':'');                                       
                                            ?>
                                            <option value="<?php echo $types[$i]['id']; ?>" <?php echo $selected; ?>><?php echo $types[$i]['description'];?></option>
                                            <?php
                                                }
                                            ?>
                                        </select>                      
                                    </div>                                                                          
                                    <div class="form-group" style="width:auto; float:left; margin-right:10px;">
                                        <label for="typeIdFilter">Concepto</label>
                                        <select id="typeIdFilter" name="typeIdFilter" class="form-control form-control-sm">
                                            <?php 
                                                $typeIdFilter = set_value('typeIdFilter',$typeIdFilter);
                                                
                                                $selected = ($typeIdFilter == ""?' selected="selected" ':''); 
                                            ?>
                                            <option value="" <?php echo $selected; ?>>[Todos]</option> 
                                            <?php                                                         
                                                for ($i=0; $i < count($movementsTypes); $i++) {         
                                                    $selected = ($movementsTypes[$i]['id'] == $typeIdFilter?' selected="selected" ':'');                                       
                                            ?>
                                            <option value="<?php echo $movementsTypes[$i]['id']; ?>" <?php echo $selected; ?>><?php echo $movementsTypes[$i]['description'];?></option>
                                            <?php
                                                }
                                            ?>
                                        </select>                      
                                    </div>     
                                    <div class="form-group" style="width:auto; float:left; margin-right:10px;">
                                        <label for="warehouseIdFilter">Depósito</label>
                                        <select id="warehouseIdFilter" name="warehouseIdFilter" class="form-control form-control-sm">
                                            <?php 
                                                $warehouseIdFilter = set_value('warehouseIdFilter',$warehouseIdFilter);
                                                
                                                $selected = ($warehouseIdFilter == ""?' selected="selected" ':''); 
                                            ?>
                                            <option value="" <?php echo $selected; ?>>[Todos]</option> 
                                            <?php                                                         
                                                for ($i=0; $i < count($warehouses); $i++) {         
                                                    $selected = ($warehouses[$i]['id'] == $warehouseIdFilter?' selected="selected" ':'');                                       
                                            ?>
                                            <option value="<?php echo $warehouses[$i]['id']; ?>" <?php echo $selected; ?>><?php echo $warehouses[$i]['description'];?></option>
                                            <?php
                                                }
                                            ?>
                                        </select>                      
                                    </div>     
                                    <div class="form-group" style="width:140px; float:left; margin-right:10px;">
                                        <label for="articleFilter">Cod. Artículo</label>
                                        <input type="text" class="form-control form-control-sm noEnterMyApp" id="articleFilter" name="articleFilter" placeholder="Cod. Artículo" autocomplete="off" value="<?php echo set_value('articleFilter',$articleFilter); ?>">
                                    </div> 
                                    <div class="form-group" style="width:140px; float:left; margin-right:10px;">
                                        <label for="observationFilter">Observación</label>
                                        <input type="text" class="form-control form-control-sm noEnterMyApp" id="observationFilter" name="observationFilter" placeholder="Observación" autocomplete="off" value="<?php echo set_value('observationFilter',$observationFilter); ?>">
                                    </div>                                                                                                    
                                </div>                                                        
                            </div>  
                            <input type="hidden" id="fieldOrder" name="fieldOrder" value="<?php echo $fieldOrder; ?>" />
                            <input type="hidden" id="typeOrder" name="typeOrder" value="<?php echo $typeOrder; ?>" />                                
                        </form>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <div class="card">                       
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <table id="grStockMovements" class="table table-bordered table-hover table-sm">
                                <thead>
                                    <tr>                                
                                        <th class="text-center" nowrap="nowrap" width="<?php echo ($allowDelete?"55px":"35px"); ?>">
                                            <?php if ($allowInsert) { ?>
                                            <a href="<?php echo base_url(); ?>stockMovements/edit/0<?php echo $backGet; ?>" class="btn without-padding" title="nuevo">
                                                <i class="far fa-plus-square"></i>
                                            </a>                                            
                                            <?php } ?>                                                                    
                                        </th>               
                                        <th nowrap="nowrap" style="min-width:120px;">Fecha  
                                            <?php echo generateOrderButton(true,($fieldOrder == 'date' && $typeOrder == 'asc'),'stockMovements/listing/1?fOrd=date&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'date' && $typeOrder == 'desc'),'stockMovements/listing/1?fOrd=date&tOrd=desc'.$filterGet); ?>                       
                                        </th>                                                     
                                        <th nowrap="nowrap">Tipo</th>                                   
                                        <th nowrap="nowrap">Concepto</th>                                        
                                        <th nowrap="nowrap">Despósito</th>                                        
                                        <th nowrap="nowrap">Artículo</th>     
                                        <th nowrap="nowrap">Cantidad</th> 
                                        <th nowrap="nowrap">Costo</th> 
                                        <th nowrap="nowrap">Observación</th> 
                                    </tr>
                                </thead>
                                <?php if (isset($stockMovements)) { ?>
                                <tbody>
                                    <?php
                                        for($i=0; $i < count($stockMovements); $i++) {                                            
                                    ?>
                                    <tr ondblclick="onClick('btnEdit<?php echo $stockMovements[$i]['id']; ?>')">                                
                                        <td class="text-center" nowrap="nowrap">                            
                                            <a href="<?php echo base_url(); ?>stockMovements/edit/<?php echo $stockMovements[$i]['id']; ?>/<?php echo $stockMovements[$i]['registerType']; ?><?php echo $backGet; ?>" class="btn without-padding" title="<?php echo ($allowEdit?"editar":"ver"); ?>" id="btnEdit<?php echo $stockMovements[$i]['id']; ?>">
                                                <i class="far fa-edit"></i>
                                            </a>
                                            <?php if ($allowDelete && $stockMovements[$i]['registerType'] == "SM" && (int)$stockMovements[$i]['input'] == 1) { ?>
                                            <button type="button" class="btn without-padding" onclick="generalDelete(<?php echo $stockMovements[$i]['id']; ?>,'stockMovements/delete')" title="eliminar">
                                                <i class="far fa-minus-square"></i>
                                            </button>                                            
                                            <?php } ?>                                    
                                        </td>  
                                        <td nowrap="nowrap"><?php echo dateFormat($stockMovements[$i]['date'],true); ?></td>     
                                        <td nowrap="nowrap"><?php echo $stockMovements[$i]['inputDescription']; ?></td>                                         
                                        <td nowrap="nowrap"><?php echo $stockMovements[$i]['typeDescription']; ?></td>                                         
                                        <td nowrap="nowrap"><?php echo $stockMovements[$i]['warehouseDescription']; ?></td>    
                                        <td><?php echo "[".$stockMovements[$i]['articleCode']."] ".$stockMovements[$i]['articleDescription']; ?></td>    
                                        <td class="text-center"><?php echo trim($stockMovements[$i]['quantity']); ?></td>     
                                        <td class="text-center" nowrap="nowrap"><?php echo ($stockMovements[$i]['input'] == 1?"$ ".decimalFormat($stockMovements[$i]['unitPrice'],2):"--"); ?></td>                                                                                 
                                        <td><?php echo $stockMovements[$i]['observation']; ?></td>  
                                        <td>
                                        <?php if ($stockMovements[$i]['registerType'] == 'SM') { ?>
                                            <button type="button" class="btn without-padding" onclick="seeStockMovementData(<?php echo $stockMovements[$i]['id']; ?>,'<?php echo "CÓD. ".$stockMovements[$i]['articleCode']." / ".str_replace('"','',$stockMovements[$i]['articleDescription']); ?>')" title="ver detalles"><i class="fa fa-list"></i></button>
                                        <?php } ?> 
                                        </td>                                   
                                    </tr>  
                                    <?php 
                                        } 
                                    ?>                         
                                </tbody>
                                <?php } ?>
                            </table>
                        </div>
                    </div>             
                    <?php if (isset($stockMovements) && count($stockMovements) > 0 && ($allowExport || $pagination != "")) { ?>
                    <div class="row">
                        <div class="col-sm-12">
                            <?php
                                if ($allowExport) {
                            ?>            
                            <a href="javascript:generalExport('stockMovements/export','<?php echo $filterGet.$orderGet; ?>');" class="pagination pagination-sm paginate_button page-item page-link" title="exportar" style="float:left; margin-right:5px;padding: 4px 8px 5px 8px;">                
                                <i class="fas fa-download"></i>
                            </a> 
                            <?php } ?>                          
                            <?php
                                if ($pagination != "") {
                            ?>            
                                <?php echo $pagination; ?>        
                       
                            <?php
                                }
                            ?>
                        </div>
                    </div>
                    <?php
                        }
                    ?>      
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->    
</div>
<!-- /.container-fluid -->
<?php
/* End of file stockMovements_list_view.php */
/* Location: ./application/views/stockMovements/stockMovements_list_view.php */