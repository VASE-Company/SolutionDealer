<div class="row without-padding">   
    <div class="col-sm-12" style="margin-bottom: 0px;margin-top: 0px;margin-left: 9px;"> 
        
    </div>  
    <div class="col-sm-12" style="max-height:250px;overflow-y:auto;">
        <table id="grHistory" class="table table-bordered table-sm">
            <thead>
                <tr>                                                           
                    <th nowrap="nowrap" width="100px">Fecha</th>  
                    <?php if ($showQuantities) { ?>
                    <th nowrap="nowrap" width="150px">Cantidad</th>  
                    <?php } ?>
                    <?php if ($showImports) { ?>                                         
                    <th nowrap="nowrap" width="150px">Costo</th>                                                                                               
                    <?php } ?>
                    <th nowrap="nowrap">De</th>                                                                                               
                </tr>
            </thead>
            <?php if (isset($data)) { ?>
            <tbody>
                <?php
                    for ($i=0; $i < count($data); $i++) {                                                                    
                ?>
                <tr>                                                                      
                    <td nowrap="nowrap"><?php echo dateFormat($data[$i]['date'],false); ?></td> 
                    <?php if ($showQuantities) { ?>
                    <td nowrap="nowrap" class="text-center"><?php echo (int)$data[$i]['quantity']; ?></td>  
                    <?php } ?>
                    <?php if ($showImports) { ?>
                    <td nowrap="nowrap" class="text-center"><?php echo decimalFormat($data[$i]['unitPrice']); ?></td>                                                                                                                                                                    
                    <?php } ?>
                    <td nowrap="nowrap"><?php echo $data[$i]['description']; ?></td> 
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
/* End of file orders_itemData_view.php */
/* Location: ./application/views/orders/orders_itemData_view.php */