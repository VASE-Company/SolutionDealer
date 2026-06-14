<div class="row without-padding">   
    <div class="col-sm-12" style="margin-bottom: 0px;margin-top: 0px;margin-left: 9px;"> 
        
    </div>  
    <div class="col-sm-12" style="max-height:250px;overflow-y:auto;">
        <table id="grHistory" class="table table-bordered table-sm">
            <thead>
                <tr>                                                         
                    <th nowrap="nowrap">Depósito</th>  
                    <th nowrap="nowrap">Ubicación</th>  
                    <th nowrap="nowrap">Stock</th>                                                               
                    <th nowrap="nowrap">Reservado</th>   
                    <th nowrap="nowrap">Disponible</th>  
                    <th nowrap="nowrap">Faltante</th>                                                                                               
                </tr>
            </thead>
            <?php if (isset($warehouses)) { ?>
            <tbody>
                <?php
                    for ($i=0; $i < count($warehouses); $i++) {                                                                       
                ?>
                <tr>                                                                       
                    <td nowrap="nowrap"><?php echo $warehouses[$i]['description']; ?></td>  
                    <td nowrap="nowrap" class="text-center"><?php echo $warehouses[$i]['location']; ?></td>  
                    <td nowrap="nowrap" class="text-center"><?php echo (int)$warehouses[$i]['stock']; ?></td>                      
                    <td nowrap="nowrap" class="text-center">
                        <?php if ($warehouses[$i]['notAvailableStock'] > 0) { ?>
                            <a href="javascript:seeNotAvailableStockByArticle(<?php echo $articleId; ?>,<?php echo $warehouses[$i]['id']; ?>)"><?php echo (int)$warehouses[$i]['notAvailableStock']; ?></a>
                        <?php 
                              } else {  
                                echo (int)$warehouses[$i]['notAvailableStock']; 
                              }
                        ?>                                                
                    </td>                                                                                 
                    <td nowrap="nowrap" class="text-center"><?php echo (int)$warehouses[$i]['availableStock']; ?></td>   
                    <td nowrap="nowrap" class="text-center <?php echo ($warehouses[$i]['outOfStock'] > 0?"outOfStock":""); ?>">
                        <?php if ($warehouses[$i]['outOfStock'] > 0) { ?>
                            <a href="javascript:seeOutOfStockByArticle(<?php echo $articleId; ?>,<?php echo $warehouses[$i]['id']; ?>)"><?php echo (int)$warehouses[$i]['outOfStock']; ?></a>
                        <?php 
                              } else {  
                                echo (int)$warehouses[$i]['outOfStock']; 
                              }
                        ?>                                                
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
<?php 
/* End of file articles_stock_view.php */
/* Location: ./application/views/articles/articles_stock_view.php */