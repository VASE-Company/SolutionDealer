<div class="row without-padding">   
    <div class="col-sm-12" style="max-height:250px;overflow-y:auto;">
        <table id="grBudgetsItem" class="table table-bordered table-sm">
            <thead>
                <tr>                     
                    <th width="35px"></th>                    
                    <th nowrap="nowrap" width="100px">Fecha</th>  
                    <th nowrap="nowrap">Proveedor</th>                                           
                    <th nowrap="nowrap">Costo s/IVA ($)</th>    
                    <th width="35px"></th>                                        
                </tr>
            </thead>
            <?php if (isset($budgets)) { ?>
            <tbody>
                <?php
                    for ($i=0; $i < count($budgets); $i++) {                                                      
                ?>
                <tr class="itemBudget <?php echo ((int)$budgets[$i]['selected'] == 1)?"selBudgetItem":""; ?>" id="itemBudget<?php echo $budgets[$i]['budgetItemId']; ?>">                                                    
                    <td class="text-center" nowrap="nowrap">   
                        <button type="button" class="btn without-padding" onclick="seeBudgetOrderEdit(<?php echo $budgets[$i]['id']; ?>)" title="ver detalle del presupuesto">
                            <i class="far fa-edit"></i>
                        </button>                                                
                    </td>                    
                    <td nowrap="nowrap"><?php echo dateFormat($budgets[$i]['budgetDate'],false); ?></td> 
                    <td nowrap="nowrap"><?php echo $budgets[$i]['supplierDescription']; ?></td>  
                    <td nowrap="nowrap" class="text-right"><?php echo decimalFormat($budgets[$i]['unitPrice'],2); ?></td>                                                                                                                                                                    
                    <td class="text-center" nowrap="nowrap">   
                    <?php if ($allowSave) { ?>                        
                        <input type="radio" id="budgetItemSelected" name="budgetItemSelected" value="<?php echo $budgets[$i]['budgetItemId']; ?>" class="noEnterMyApp" <?php echo ((int)$budgets[$i]['selected'] == 1?"checked":""); ?> onclick="selectItemBudgetOrderEdit(<?php echo $budgets[$i]['budgetItemId']; ?>)"/>
                    <?php } else { ?>                      
                        <?php if ((int)$budgets[$i]['selected'] == 1) { ?>                                                               
                        <i class="fas fa-check"></i>
                        <?php } ?>                                                                           
                    <?php } ?>                                                           
                    </td>        
                </tr>  
                <?php 
                    } 
                ?>                         
            </tbody>
            <?php } ?>
        </table>
    </div>
    <?php if ($allowSave) { ?>
    <div class="error alert alert-danger text-center hide" id="errorItemBudgetSelect"></div>
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">                  
        <button type="button" id="btnSend" name="btnSend" class="btn btn-success type-btn-save" onclick="sendItemBudgetSelectedOrderEdit()">Guardar</button>                                             
        <button type="button" class="btn btn-danger type-btn-save" onclick="closeModalMessage()">Cerrar</button>                                                        
    </div>     
    <?php 
        } 
    ?>   
</div> 
<?php 
/* End of file budgets_by_detailOrder_view.php */
/* Location: ./application/views/budgets/budgets_by_detailOrder_view.php */