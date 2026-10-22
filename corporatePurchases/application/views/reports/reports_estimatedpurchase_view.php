<?php       
    $pagination = (isset($data['pagination'])?$data['pagination']:"");
    if (!isset($fieldOrder) || !isset($typeOrder)) {
        $fieldOrder = "buy";
        $typeOrder = "desc";
    }    
    $warehouses = $data['warehouses'];        
    $totalRecords = $data['totalRecords'];         
    $data = $data['data'];         
?>
<div class="card">                       
    <div class="card-body">    
        <?php if (isset($data) && count($data) > 0 && $allowExport) { ?>        
        <div class="row">
            <div class="col-12 d-flex align-items-center">
                <a href="javascript:estPurchaseExport('reports/export','<?php echo $filterExportGet; ?>');" class="btn without-padding" title="exportar">
                    <i class="fas fa-download"></i>
                </a>
                <small class="text-muted ml-2">
                    Seleccionados: <span id="selectedCount"><?php echo (int)$totalRecords; ?></span>
                    &nbsp;|&nbsp;
                    <a href="javascript:estPurchaseSel.selectAll();">Todos</a>
                    &nbsp;/&nbsp;
                    <a href="javascript:estPurchaseSel.selectNone();">Ninguno</a>
                </small>
            </div>        
        </div> 
        <?php
            }
        ?> 
        <div class="row">
            <div class="col-sm-12" style="overflow-x: scroll;">                
                <table id="grData" class="table table-bordered table-sm" data-total="<?php echo (int)$totalRecords; ?>">              
                    <thead>
                        <tr>                                        
                            <th nowrap="nowrap" class="text-center table-border-strong" colspan="4">Artículo</th>                                                                                                                                                                        
                            <?php                                                                                                               
                                if (isset($warehouses)) { 
                                    for($i=0; $i < count($warehouses); $i++) {                                                
                            ?>
                            <th nowrap="nowrap" class="text-center table-border-strong" colspan="3"><?php echo $warehouses[$i]['description']; ?></th>                                                                                                                                                                        
                            <?php 
                                    }
                                    if (count($warehouses) > 1) {
                            ?>
                            <th nowrap="nowrap" class="text-center table-border-strong" colspan="3" style="border-right:none;border-top:none;"></th>                                                                                                                                                                        
                            <?php
                                    }
                                }
                            ?>                              
                        </tr>
                        <tr>                                        
                            <th class="table-border-strong text-center">
                                <input type="checkbox" id="chkAllRows" onchange="estPurchaseSel.toggleAll(this)" title="Seleccionar todos los de la página">
                            </th>
                            <th nowrap="nowrap" class="table-border-strong">Código</th>                                                
                            <th nowrap="nowrap" class="table-border-strong" style="min-width: 130px;">Descripción 
                                <?php echo generateOrderButton(true,($fieldOrder == 'des' && $typeOrder == 'asc'),'reports/result?fOrd=des&tOrd=asc'.$filterGet,'reloadResults'); ?>
                                <?php echo generateOrderButton(false,($fieldOrder == 'des' && $typeOrder == 'desc'),'reports/result?fOrd=des&tOrd=desc'.$filterGet,'reloadResults'); ?>                       
                            </th>                                                                                                        
                            <th nowrap="nowrap" class="table-border-strong" style="min-width: 90px;">Rubro 
                                <?php echo generateOrderButton(true,($fieldOrder == 'fam' && $typeOrder == 'asc'),'reports/result?fOrd=fam&tOrd=asc'.$filterGet,'reloadResults'); ?>
                                <?php echo generateOrderButton(false,($fieldOrder == 'fam' && $typeOrder == 'desc'),'reports/result?fOrd=fam&tOrd=desc'.$filterGet,'reloadResults'); ?>                       
                            </th>             
                            <?php                                                                                                               
                                if (isset($warehouses)) { 
                                    for($i=0; $i < count($warehouses); $i++) {                                                
                            ?>                            
                            <th nowrap="nowrap" class="table-border-strong">Tasa de Uso</th>                                                                                                                                    
                            <th nowrap="nowrap" class="table-border-strong">Stock</th>                                                                                                                                                                
                            <th nowrap="nowrap" class="table-border-strong">Compra</th>                                                                                                                                                                
                            <?php 
                                    }
                            ?>                                                        
                            <th nowrap="nowrap" class="table-border-strong" style="min-width: 165px;">COMPRA GLOBAL                                
                                <?php echo generateOrderButton(true,($fieldOrder == 'buy' && $typeOrder == 'asc'),'reports/result?fOrd=buy&tOrd=asc'.$filterGet,'reloadResults'); ?>
                                <?php echo generateOrderButton(false,($fieldOrder == 'buy' && $typeOrder == 'desc'),'reports/result?fOrd=buy&tOrd=desc'.$filterGet,'reloadResults'); ?>                       
                            </th>                                                                                                                                                                                                   
                            <?php                            
                                }
                            ?>                              
                        </tr>
                    </thead>   
                    <tbody>        
                        <?php                                    
                            if (isset($data)) {                                                                                                 
                                for ($i=0; $i < count($data); $i++) {                                                        
                        ?>
                        <tr> 
                            <td class="text-center table-border-strong">
                                <input type="checkbox" class="chkRow" value="<?php echo $data[$i]['id']; ?>" onchange="estPurchaseSel.toggle(this)">
                            </td>
                            <td nowrap="nowrap" class="table-border-strong"><?php echo $data[$i]['code']; ?></td> 
                            <td nowrap="nowrap" class="table-border-strong"><?php echo $data[$i]['description']; ?></td>                             
                            <td nowrap="nowrap" class="table-border-strong"><?php echo $data[$i]['familyDescription']; ?></td>      
                            <?php                                                                                                               
                                if (isset($warehouses)) { 
                                    for($j=0; $j < count($warehouses); $j++) {                                                                                                                                                                                                             
                                        if (isset($data[$i]['estimatedQuantity'.$warehouses[$j]['id']])) {
                                            $estimatedQuantity = (int)$data[$i]['estimatedQuantity'.$warehouses[$j]['id']];                                            
                                        } else {                                            
                                            $estimatedQuantity = 0;                                            
                                        }                                 
                                        if (isset($data[$i]['stock'.$warehouses[$j]['id']])) {
                                            $stock = (int)$data[$i]['stock'.$warehouses[$j]['id']];                                            
                                        } else {                                            
                                            $stock = 0;                                            
                                        } 
                                        if (isset($data[$i]['quantityToBuy'.$warehouses[$j]['id']])) {
                                            $quantityToBuy = (int)$data[$i]['quantityToBuy'.$warehouses[$j]['id']];                                            
                                        } else {                                            
                                            $quantityToBuy = 0;                                            
                                        }                                         
                            ?>                                                                                    
                            <td nowrap="nowrap" class="text-center table-border-strong"><?php echo $estimatedQuantity; ?></td>                                                                                                   
                            <td nowrap="nowrap" class="text-center table-border-strong"><?php echo $stock; ?></td>
                            <td nowrap="nowrap" class="text-center table-border-strong bg-light font-weight-bold <?php if ($quantityToBuy < 0) echo "text-red"; ?>"><?php echo $quantityToBuy; ?></td>
                            <?php 
                                    }
                            ?>                                                                                                                                                                                                                                                                                                                                                    
                            <td nowrap="nowrap" class="text-center table-border-strong bg-light-strong font-weight-bold"><?php echo (int)$data[$i]['quantityToBuy']; ?></td>                                                                                                                                    
                            <?php                                    
                                }
                            ?>                              
                        </tr>
                        <?php
                                } 
                            }
                        ?>                         
                    </tbody> 
                </table>                   
            </div>
        </div>   
        <?php if ($pagination != "") { ?>
        <div class="row">
            <div class="col-sm-12">
                <?php echo $pagination; ?>
            </div>
        </div>
        <?php } ?>  
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->
<?php 
/* End of file reports_estimatedpurchase_view.php */
/* Location: ./application/views/reports/reports_estimatedpurchase_view.php */