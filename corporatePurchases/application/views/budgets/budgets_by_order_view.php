<div class="row without-padding">   
    <div class="col-sm-12" style="margin-bottom: 0px;margin-top: 0px;margin-left: 9px;"> 
        
    </div>  
    <div class="col-sm-12" style="max-height:250px;overflow-y:auto;">
        <table id="grHistory" class="table table-bordered table-sm">
            <thead>
                <tr>                     
                    <th width="35px"></th>                    
                    <th nowrap="nowrap" width="100px">Fecha</th>  
                    <th nowrap="nowrap">Proveedor</th>                                           
                    <th nowrap="nowrap">Usuario</th>                                                                                               
                </tr>
            </thead>
            <?php if (isset($budgets)) { ?>
            <tbody>
                <?php
                    for ($i=0; $i < count($budgets); $i++) {                                                      
                ?>
                <tr>                                                    
                    <th class="text-center" nowrap="nowrap">   
                        <button type="button" class="btn without-padding" onclick="seeBudgetOrderEdit(<?php echo $budgets[$i]['id']; ?>)" title="ver detalle del presupuesto">
                            <i class="far fa-edit"></i>
                        </button>  
                        <?php if ($allowDelete) { ?>
                        <button type="button" class="btn without-padding" onclick="generalDelete(<?php echo $budgets[$i]['id']; ?>,'budgets/delete')" title="eliminar">
                            <i class="far fa-minus-square"></i>
                        </button>                                            
                        <?php } ?>                       
                    </th>                    
                    <td nowrap="nowrap"><?php echo dateFormat($budgets[$i]['budgetDate'],false); ?></td> 
                    <td nowrap="nowrap"><?php echo $budgets[$i]['supplierDescription']; ?></td>  
                    <td nowrap="nowrap"><?php echo $budgets[$i]['userDescription']; ?></td>                                                                                                                                                                    
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
/* End of file budgets_by_order_view.php */
/* Location: ./application/views/budgets/budgets_by_order_view.php */