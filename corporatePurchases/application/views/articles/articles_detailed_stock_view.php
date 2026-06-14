<div class="row without-padding">   
    <div class="col-sm-12" style="margin-bottom: 0px;margin-top: 0px;margin-left: 9px;"> 
        
    </div>  
    <div class="col-sm-12" style="max-height:250px;overflow-y:auto;">
        <table id="grHistory" class="table table-bordered table-sm">
            <thead>
                <tr>                                                         
                    <th nowrap="nowrap">Fecha</th>                                                                                                     
                    <th nowrap="nowrap">Cantidad</th>   
                    <th nowrap="nowrap">Costo</th>   
                    <th nowrap="nowrap">Origen</th>  
                </tr>
            </thead>
            <?php if (isset($details)) { ?>
            <tbody>
                <?php
                    for ($i=0; $i < count($details); $i++) {                                                                       
                ?>
                <tr>                                                                                           
                    <td nowrap="nowrap" class="text-center"><?php echo dateFormat($details[$i]['date'],false); ?></td>  
                    <td nowrap="nowrap" class="text-center"><?php echo (int)$details[$i]['quantity']; ?></td>  
                    <td nowrap="nowrap" class="text-center"><?php echo decimalFormat($details[$i]['unitPrice'],2); ?></td>                                                                                                                                                                                                                                                                                                                                              
                    <td nowrap="nowrap" class="text-center"><?php echo $details[$i]['observation']; ?></td>  
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
/* End of file articles_detailed_stock_view.php */
/* Location: ./application/views/articles/articles_detailed_stock_view.php */