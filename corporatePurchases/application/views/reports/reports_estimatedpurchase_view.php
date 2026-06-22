<?php        
    $companies = $data['companies'];
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
                            <th nowrap="nowrap" class="text-center" colspan="3">Artículo</th>                                                                                                                                                                        
                            <?php                                                                                                               
                                if (isset($companies)) { 
                                    for($i=0; $i < count($companies); $i++) {                                                
                            ?>
                            <th nowrap="nowrap" class="text-center" colspan="3"><?php echo $companies[$i]['description']; ?></th>                                                                                                                                                                        
                            <?php 
                                    }
                                    if (count($companies) > 1) {
                            ?>
                            <th nowrap="nowrap" class="text-center" colspan="3">General</th>                                                                                                                                                                        
                            <?php
                                    }
                                }
                            ?>                              
                        </tr>
                        <tr>                                        
                            <th nowrap="nowrap">Código</th>                                                
                            <th nowrap="nowrap">Descripción</th>                                                                                                        
                            <th nowrap="nowrap">Rubro</th>             
                            <?php                                                                                                               
                                if (isset($companies)) { 
                                    for($i=0; $i < count($companies); $i++) {                                                
                            ?>                            
                            <th nowrap="nowrap">Stock</th>                                                                                                                                    
                            <th nowrap="nowrap">Estimado</th>                                                                                                                                    
                            <th nowrap="nowrap">Comprar</th>                                                                                                                                    
                            <?php 
                                    }
                                    if (count($companies) > 1) {
                            ?>
                            <th nowrap="nowrap">Total Comprar</th>                                                                                                                                                                
                            <?php
                                    }
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
                            <td nowrap="nowrap"><?php echo $data[$i]['code']; ?></td> 
                            <td nowrap="nowrap"><?php echo $data[$i]['description']; ?></td>                             
                            <td nowrap="nowrap"><?php echo $data[$i]['familyDescription']; ?></td>      
                            <?php                                                                                                               
                                if (isset($companies)) { 
                                    for($j=0; $j < count($companies); $j++) {                                                                                            
                                        if (isset($data[$i]['companies'][$companies[$j]['id']])) {
                                            $stock = $data[$i]['companies'][$companies[$j]['id']]['stock'];
                                            $estimatedQuantity = $data[$i]['companies'][$companies[$j]['id']]['estimatedQuantity'];
                                            $quantityToBuy = $data[$i]['companies'][$companies[$j]['id']]['quantityToBuy'];
                                        } else {
                                            $stock = 0;
                                            $estimatedQuantity = 0;
                                            $quantityToBuy = 0;
                                        }                                       
                            ?>                            
                            <td nowrap="nowrap" class="text-center"><?php echo $stock; ?></td>                                                                                                   
                            <td nowrap="nowrap" class="text-center"><?php echo $estimatedQuantity; ?></td>
                            <td nowrap="nowrap" class="text-center"><?php echo $quantityToBuy; ?></td>                                                                                                   
                            <?php 
                                    }
                                    if (count($companies) > 1) {
                                        if (isset($data[$i]['companies'][0])) {                                            
                                            $quantityToBuy = $data[$i]['companies'][0]['quantityToBuy'];
                                        } else {                                            
                                            $quantityToBuy = 0;
                                        } 
                            ?>                            
                            <td nowrap="nowrap" class="text-center"><?php echo $quantityToBuy; ?></td>                                                                                                                                    
                            <?php
                                    }
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
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->
<?php 
/* End of file reports_estimatedpurchase_view.php */
/* Location: ./application/views/reports/reports_estimatedpurchase_view.php */