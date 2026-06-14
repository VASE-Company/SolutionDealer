<div class="row without-padding">   
    <div class="col-sm-12" style="margin-bottom: 0px;margin-top: 0px;margin-left: 9px;"> 
        
    </div>  
    <div class="col-sm-12" style="max-height:250px;overflow-y:auto;">
        <table id="grHistory" class="table table-bordered table-sm">
            <thead>
                <tr>                                                         
                    <th nowrap="nowrap">Nº Pedido</th>  
                    <th nowrap="nowrap">Fecha</th>                                                               
                    <th nowrap="nowrap">Faltante</th>   
                </tr>
            </thead>
            <?php if (isset($details)) { ?>
            <tbody>
                <?php
                    for ($i=0; $i < count($details); $i++) {                                                                       
                ?>
                <tr>                                                                       
                    <td nowrap="nowrap" class="text-center"><?php echo $details[$i]['orderId']; ?></td>  
                    <td nowrap="nowrap" class="text-center"><?php echo dateFormat($details[$i]['orderDate'],false); ?></td>   
                    <td nowrap="nowrap" class="text-center outOfStock"><?php echo (int)$details[$i]['outOfStock']; ?></td>                                                                                                                                                                                                                                                                                                                                              
                </tr>  
                <?php 
                    } 
                ?>                         
            </tbody>
            <?php } ?>
        </table>
    </div>    
</div> 
<div class="row without-padding">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">      
        <button type="button" class="btn btn-success type-btn-save" onclick="seeStockByArticle(<?php echo $articleId; ?>)">Volver</button>                                            
    </div>   
</div>  
<?php 
/* End of file articles_stock_view.php */
/* Location: ./application/views/articles/articles_stock_view.php */