<div class="row without-padding">   
    <div class="col-sm-12" style="margin-bottom: 0px;margin-top: 0px;margin-left: 9px;"> 
        
    </div>  
    <div class="col-sm-12" style="max-height:250px;overflow-y:auto;">
        <table id="grHistory" class="table table-bordered table-sm">
            <thead>
                <tr>                                    
                    <?php if ($showQuantity) { ?>                                                                                                                                                   
                    <th nowrap="nowrap">Cantidad</th> 
                    <?php } ?>  
                    <th nowrap="nowrap">Costo</th>   
                    <th nowrap="nowrap">Fecha</th>  
                    <th nowrap="nowrap">Origen</th>  
                </tr>
            </thead>
            <?php if (isset($details)) { ?>
            <tbody>
                <?php
                    $quantityTotal = 0;
                    $unitPriceTotal = 0;

                    for ($i=0; $i < count($details); $i++) {                                                                       
                        $quantityTotal += (int)$details[$i]['quantity'];
                        $unitPriceTotal += ($details[$i]['unitPrice'] * (int)$details[$i]['quantity']);
                ?>
                <tr>                                    
                    <?php if ($showQuantity) { ?>                                                                                 
                    <td nowrap="nowrap" class="text-center"><?php echo (int)$details[$i]['quantity']; ?></td>  
                    <?php } ?>
                    <td nowrap="nowrap" class="text-center"><?php echo decimalFormat($details[$i]['unitPrice'],2); ?></td>                                                                                                                                                                                                                                                                                                                                              
                    <td nowrap="nowrap" class="text-center"><?php echo dateFormat($details[$i]['date'],false); ?></td>  
                    <td nowrap="nowrap" class="text-left"><?php echo $details[$i]['observation']; ?></td>  
                </tr>  
                <?php 
                    } 
                ?>                         
            </tbody>
            <?php if ($showQuantity) { ?>       
            <tfoot>
                <tr>                                    
                    <th nowrap="nowrap" class="text-center"><?php echo (int)$quantityTotal; ?></th>                    
                    <th nowrap="nowrap" class="text-center"><?php echo decimalFormat($unitPriceTotal,2); ?></th>                                                                                                                                                                                                                                                                                                                                              
                    <th></th>
                    <th class="text-left">                        
                        Costo Unitario: <?php echo decimalFormat($unitPriceTotal / (int)$quantityTotal,2); ?>                        
                    </th>
                <tr>
            </tfoot>
            <?php } ?>  
            <?php } ?>
        </table>
    </div>    
</div>   
<?php 
/* End of file articles_detailed_unit_price_view.php */
/* Location: ./application/views/articles/articles_detailed_unit_price_view.php */