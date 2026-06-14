<div class="card card-secondary">
    <div class="card-body">
        <div class="row without-padding">    
            <div class="col-sm-12" style="overflow-x:scroll;">
                <table id="grSummary" class="table table-bordered table-sm">
                    <thead>
                        <tr>                     
                            <th colspan="2" class="text-center">Producto</th>    
                            <?php if (isset($budgets)) { ?>                
                            <?php
                                $itemNotSelected = false;
                                $bestOptionTotal = 0;
                                $worstOptionTotal = 0;
                                $selectedOptionTotal = 0;
                                for ($i=0; $i < count($budgets); $i++) {   
                                    $budgets[$i]['total'] = 0;                                                  
                            ?>
                            <th colspan="3" class="text-center" nowrap="nowrap"><?php echo $budgets[$i]['supplierDescription']; ?></th>    
                            <?php } ?>         
                            <th colspan="4" class="text-center">Mejor Opción</th>                                                                                    
                            <?php } ?>                                                                                         
                        </tr>
                        <tr>                     
                            <th class="text-center" width="50px">Cant.</th>    
                            <th class="text-center" width="300px">Descripción</th>    
                            <?php if (isset($budgets)) { ?>                
                            <?php
                                for ($i=0; $i < count($budgets); $i++) {                                                      
                            ?>
                            <th width="50px">Neto</th>    
                            <th width="50px">Iva</th>    
                            <th width="50px">Total</th>    
                            <?php } ?>         
                            <th width="300px">Proveedor</th> 
                            <th width="50px">Neto</th>    
                            <th width="50px">Iva</th>    
                            <th width="50px">Total</th>                                                                                                               
                            <?php } ?>                                                                                         
                        </tr>
                    </thead>
                    <?php if (isset($orderItems)) { ?>
                    <tbody>
                        <?php
                            for ($i=0; $i < count($orderItems); $i++) {                                                      
                        ?>
                        <tr>                                                    
                            <td class="text-center" nowrap="nowrap"><?php echo (int)$orderItems[$i]['quantity']; ?></td>                    
                            <td nowrap="nowrap"><?php echo $orderItems[$i]['description']; ?></td>                                                                                                                                                                    
                            <?php if (isset($budgets)) { ?>                
                            <?php
                                $bestOption = null;    
                                $worstOption = null;    
                                $hasSelectedItem = false;  
                                $hasBudgets = false;                          
                                for ($j=0; $j < count($budgets); $j++) {                                                      
                                    if (isset($budgetItems) && isset($budgetItems[$orderItems[$i]['id']][$budgets[$j]['id']])) {                                        
                                        $itemBudget = $budgetItems[$orderItems[$i]['id']][$budgets[$j]['id']];                                        
                                        $itemBudget['supplierDescription'] = $budgets[$j]['supplierDescription'];

                                        if (!isset($bestOption) || (isset($bestOption) && $itemBudget['unitPrice'] < $bestOption['unitPrice'])) {
                                            $bestOption = $itemBudget;
                                        }
                                        if (!isset($worstOption) || (isset($worstOption) && $itemBudget['unitPrice'] > $worstOption['unitPrice'])) {
                                            $worstOption = $itemBudget;
                                        } 
                                        if ((int)$itemBudget['selected'] == 1) {
                                            $selectedOptionTotal += $itemBudget['unitPrice'];
                                            $hasSelectedItem = true;
                                        }

                                        $classItemBudget = ((int)$itemBudget['selected'] == 1?"selBudgetItem":"");

                                        $hasBudgets = true;
                            ?>                            
                            <td class="text-right <?php echo $classItemBudget; ?>"><?php echo decimalFormat($itemBudget['unitPrice'],2); ?></td>                                                                                   
                            <td class="text-right <?php echo $classItemBudget; ?>"><?php echo decimalFormat($itemBudget['tax'],2); ?></td>         
                            <td class="text-right <?php echo $classItemBudget; ?>"><?php echo decimalFormat($itemBudget['total'],2); ?></td>  
                            <?php   } else { ?>     
                            <td class="text-center" colspan="3">no cotiza</td>    
                            <?php   } ?>     
                            <?php } 
                                if (!$hasSelectedItem && $hasBudgets) $itemNotSelected = true;
                            ?> 
                            <?php
                                if (!isset($bestOption)) $bestOption = array('unitPrice'=>0, 'tax'=>0, 'total'=>0,'supplierDescription'=>'no cotiza');                                
                                $bestOptionTotal += (float)decimalFormat($bestOption['unitPrice'],2);
                                if (isset($worstOption)) $worstOptionTotal += (float)decimalFormat($worstOption['unitPrice'],2);
                                for ($j=0; $j < count($budgets); $j++) {                                                                                          
                                    if (isset($budgetItems) && isset($budgetItems[$orderItems[$i]['id']][$budgets[$j]['id']])) {  
                                        $itemBudget = $budgetItems[$orderItems[$i]['id']][$budgets[$j]['id']];                                        
                                        $budgets[$j]['total'] += (float)decimalFormat($itemBudget['unitPrice'],2);  
                                    } else {
                                        $budgets[$j]['total'] += (float)decimalFormat($bestOption['unitPrice'],2);  
                                    }
                                }
                            ?>        
                            <td nowrap="nowrap"><?php echo $bestOption['supplierDescription']; ?></td> 
                            <td class="text-right"><?php echo decimalFormat($bestOption['unitPrice'],2); ?></td>                                                                                   
                            <td class="text-right"><?php echo decimalFormat($bestOption['tax'],2); ?></td>         
                            <td class="text-right"><?php echo decimalFormat($bestOption['total'],2); ?></td>                              
                            <?php } ?> 
                        </tr>  
                        <?php 
                            } 
                        ?>                                                   
                    </tbody>
                    <?php } ?>
                </table>
                <?php if (isset($budgets)) { ?>                                
                <table id="grSummaryTotal" class="table table-bordered table-sm" style="width:400px;margin-top:30px;">
                    <thead>
                        <tr>                     
                            <th class="text-center" width="300px">Proveedor</th>    
                            <th class="text-center" width="100px">Valor Final</th>                                                                                                                    
                        </tr>                        
                    </thead>
                    <tbody>                    
                    <?php
                        for ($i=0; $i < count($budgets); $i++) {                                                                                                      
                    ?>
                        <tr>                                                                                
                            <td nowrap="nowrap"><?php echo $budgets[$i]['supplierDescription']; ?></td>   
                            <td class="text-right"><?php echo decimalFormat($budgets[$i]['total'],2); ?></td>                                                                                                                                                                  
                        </tr>                                                                                
                    <?php } ?> 
                        <tr>                                                                                
                            <td nowrap="nowrap" class="font-weight-bold">Mejor Opción</td>   
                            <td class="text-right font-weight-bold"><?php echo decimalFormat($bestOptionTotal,2); ?></td>                                                                                                                                                                  
                        </tr>                                               
                    </tbody>                    
                </table>
                <table id="grSummaryResult" class="table table-bordered table-sm" style="width:400px;margin-top:30px;">
                    <tbody>
                        <?php if ($itemNotSelected) { ?>
                        <tr>                     
                            <td class="text-left font-weight-bold" width="400px">No se puede mostrar el resultado de la Gestión.<br>Faltan seleccionar items.</td>                                                                                                                                            
                        </tr> 
                        <?php } else { ?>
                        <tr>                     
                            <td class="text-left font-weight-bold" width="300px">Resultado de la Gestión</td>    
                            <td class="text-right" width="100px"><?php echo decimalFormat($worstOptionTotal - $selectedOptionTotal,2); ?></td>                                                                                                                    
                        </tr>   
                        <tr>                     
                            <td class="text-left font-weight-bold" width="300px">Porc. mejora c/techo o utilización</td>    
                            <td class="text-right" width="100px"><?php echo ($worstOptionTotal != 0?decimalFormat((($worstOptionTotal - $selectedOptionTotal) * 100) / $worstOptionTotal,2):"0.00"); ?> %</td>                                                                                                                    
                        </tr>            
                        <?php } ?>          
                    </tbody>                                
                </table>
                <?php } ?>
            </div>
        </div> 
    </div>
</div>
<?php 
/* End of file budgets_summary_by_order_view.php */
/* Location: ./application/views/budgets/budgets_summary_by_order_view.php */