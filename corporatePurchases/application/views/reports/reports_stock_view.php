<?php    
   $warehouses = $data['warehouses'];
   $classWithValue = (isset($data['classWithValue'])?$data['classWithValue']:"");
   $showLocation = (isset($data['showLocation']) && $data['showLocation'] == true);  
   $showUnitPrice = (isset($data['showUnitPrice']) && $data['showUnitPrice'] == true);  
   $typeStock = (isset($data['typeStock'])?$data['typeStock']:"");
   $data = $data['data'];  
?>
<div class="card">                       
    <div class="card-body">    
        <div class="row">
            <label class="col-form-label col-form-label-sm col-xs-12 col-sm-12 col-md-12 col-lg-12">
            * Se mostrarán los primeros 50 resultados, para ver el informe completo debe exportarlo.
            <?php if (isset($data) && count($data) > 0 && $allowExport) { ?>        
            <a href="javascript:generalExport('reports/export','<?php echo $filterExportGet; ?>');" class="btn without-padding" title="exportar">                
                <i class="fas fa-download"></i>
            </a>                 
            <?php
                }
            ?> 
            </label>            
        </div> 
        <div class="row">
            <div class="col-sm-12" style="overflow-x: scroll;">
                <table id="grData" class="table table-bordered table-sm">                 
                    <thead>
                        <tr>                                        
                            <th nowrap="nowrap">Código</th>                                                
                            <th nowrap="nowrap">Descripción</th>                                                                            
                            <th nowrap="nowrap">Rubro</th>   
                            <?php if ($showLocation) { ?>
                            <th nowrap="nowrap">Ubicación</th>
                            <?php } ?>                               
                            <?php   
                                if (isset($warehouses)) {                                                     
                                    for ($i=0; $i < count($warehouses); $i++) {                                                                                      
                            ?>
                            <th nowrap="nowrap"><?php echo $warehouses[$i]['description']; ?></th>                             
                            <?php
                                    }
                                }
                            ?>
                            <?php if ($showUnitPrice) { ?>
                            <th nowrap="nowrap">Costo Histórico</th>
                            <th nowrap="nowrap">Costo Actualizado</th>
                            <?php } ?>
                        </tr>
                    </thead>   
                    <tbody>        
                        <?php                                    
                            if (isset($data)) { 

                                $i = 0;
                                
                                while ($i < count($data)) {

                                    $articleStock['id'] = $data[$i]['id'];
                                    $articleStock['code'] = $data[$i]['code'];
                                    $articleStock['description'] = $data[$i]['description'];
                                    $articleStock['familyDescription'] = $data[$i]['familyDescription'];                                    
                                    $articleStock['location'] = "";
                                    if ($showLocation) { 
                                        if (trim($data[$i]['corridor']) != "") {
                                            if ($articleStock['location'] != "") $articleStock['location'] .= " - ";
                                            $articleStock['location'] .= trim($data[$i]['corridor']);
                                        }
                                        if (trim($data[$i]['shelf']) != "") {
                                            if ($articleStock['location'] != "") $articleStock['location'] .= " - ";
                                            $articleStock['location'] .= trim($data[$i]['shelf']);
                                        }
                                        if ($articleStock['location'] == "") $articleStock['location'] = "-";
                                    }           
                                    $articleStock['unitPrice'] = (float)$data[$i]['unitPrice'];                                       
                                    $articleStock['lastUnitPrice'] = (float)$data[$i]['lastUnitPrice'];                                               

                                    for ($j=0; $j < count($warehouses); $j++) {      
                                        $articleStock['stocks'][$warehouses[$j]['id']] = 0; 
                                    }                                    

                                    while ($i < count($data) && $data[$i]['id'] == $articleStock['id']) {
                                        $articleStock['stocks'][$data[$i]['warehouseId']] = $data[$i]['stock'];

                                        $i++;
                                    }

                                    $warehouseId = (count($warehouses) == 1?$warehouses[0]['id']:-1); 
                                    $articleStock['totalPrice'] = ($warehouseId > 0?(int)$articleStock['stocks'][$warehouseId] * (float)$articleStock['unitPrice']:0);                                
                                    $articleStock['lastUnitPrice'] = ($warehouseId > 0?(int)$articleStock['stocks'][$warehouseId] * (float)$articleStock['lastUnitPrice']:0);                                
                        ?>
                        <tr> 
                            <td nowrap="nowrap"><?php echo $articleStock['code']; ?></td> 
                            <td nowrap="nowrap"><?php echo $articleStock['description']; ?></td> 
                            <td nowrap="nowrap"><?php echo $articleStock['familyDescription']; ?></td> 
                            <?php if ($showLocation) { ?>
                            <td nowrap="nowrap" class="text-center"><?php echo $articleStock['location']; ?></td> 
                            <?php } ?>                            
                            <?php
                                for ($j=0; $j < count($warehouses); $j++) {   
                            ?>
                            <td nowrap="nowrap" class="text-center <?php echo ($classWithValue <> "" && (int)$articleStock['stocks'][$warehouses[$j]['id']] <> 0?$classWithValue:""); ?>">                                                                
                                <?php if (($typeStock == '-REAL-' || $typeStock == 'AVAILABLE') && (int)$articleStock['stocks'][$warehouses[$j]['id']] >0 ) { ?>                                    
                                    <a href="javascript:seeArticlePriceInStockReport(<?php echo $articleStock['id']; ?>,<?php echo $warehouses[$j]['id']; ?>,'<?php echo $typeStock; ?>')"><?php echo (int)$articleStock['stocks'][$warehouses[$j]['id']]; ?></a>
                                <?php } else { ?>
                                    <?php echo (int)$articleStock['stocks'][$warehouses[$j]['id']]; ?>
                                <?php } ?>
                            </td> 
                            <?php
                                }
                            ?>
                            <?php if ($showUnitPrice) { ?>
                            <td nowrap="nowrap" class="text-center">                                
                                <?php if ($articleStock['totalPrice'] > 0) { ?>                                    
                                    <a href="javascript:seeArticleUnitPriceInStockReport(<?php echo $articleStock['id']; ?>,<?php echo $warehouseId; ?>)"><?php echo decimalFormat($articleStock['totalPrice'],2); ?></a>
                                <?php } else { ?>
                                    <?php echo decimalFormat($articleStock['totalPrice'],2); ?>
                                <?php } ?>                                    
                            </td> 
                            <td nowrap="nowrap" class="text-center">        
                                <?php if ($articleStock['lastUnitPrice'] > 0) { ?>                                    
                                    <a href="javascript:seeArticleLastUnitPriceInStockReport(<?php echo $articleStock['id']; ?>,<?php echo ($warehouseId > 0?(int)$articleStock['stocks'][$warehouseId]:0); ?>)"><?php echo decimalFormat($articleStock['lastUnitPrice'],2); ?></a>
                                <?php } else { ?>
                                    <?php echo decimalFormat($articleStock['lastUnitPrice'],2); ?>
                                <?php } ?>                                                        
                            </td> 
                            <?php } ?>
                        </tr>
                        <?php
                                } 
                            }
                        ?>                         
                    </tbody> 
                </table>                   
            </div>
        </div>     
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->
<?php 
/* End of file reports_stock_view.php */
/* Location: ./application/views/reports/reports_stock_view.php */