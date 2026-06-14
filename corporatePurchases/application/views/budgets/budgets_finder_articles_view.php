<div class="row without-padding">   
    <div class="col-sm-12" style="max-height:400px;overflow-y:scroll;">
        <table id="grArticles" class="table table-bordered table-sm">
            <thead>
                <tr>                                                                                         
                    <th nowrap="nowrap">Código</th>  
                    <th nowrap="nowrap">Descripción</th>                                           
                    <th nowrap="nowrap">Rubro</th>    
                    <th style="width:30px;"></th>                                 
                </tr>
            </thead>
            <?php if (isset($articles)) { ?>
            <tbody>
                <?php
                    for($i=0; $i < count($articles); $i++) {                                            
                ?>
                <tr>                                
                    <td><?php echo $articles[$i]['code']; ?></td> 
                    <td><?php echo $articles[$i]['description']; ?></td>                                                                                                                   
                    <td><?php echo $articles[$i]['familyDescription']; ?></td> 
                    <td class="text-center without-padding">                                                                                                                                                
                        <button type="button" class="btn btn-sm btn-info" onclick="acceptArticlesFinderBudgetEdit(<?php echo ($i + 1); ?>)" title="aceptar">
                            OK
                        </button>  
                        <input type="hidden" id="afCode<?php echo ($i + 1); ?>" name="afCode<?php echo ($i + 1); ?>" value="<?php echo $articles[$i]['code']; ?>" /> 
                        <input type="hidden" id="afDescription<?php echo ($i + 1); ?>" name="afDescription<?php echo ($i + 1); ?>" value="<?php echo $articles[$i]['description']; ?>" /> 
                        <input type="hidden" id="afFamily<?php echo ($i + 1); ?>" name="afFamily<?php echo ($i + 1); ?>" value="<?php echo $articles[$i]['familyDescription']; ?>" />
                        <input type="hidden" id="afDetailQuantity<?php echo ($i + 1); ?>" name="afDetailQuantity<?php echo ($i + 1); ?>" value="<?php echo (int)$articles[$i]['quantity']; ?>" />  
                        <input type="hidden" id="afDetailOrderId<?php echo ($i + 1); ?>" name="afDetailOrderId<?php echo ($i + 1); ?>" value="<?php echo $articles[$i]['id']; ?>" />                         
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
/* End of file budgets_finder_articles_view.php */
/* Location: ./application/views/budgets/budgets_finder_articles_view.php */