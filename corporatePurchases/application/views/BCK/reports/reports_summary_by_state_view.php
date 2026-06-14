<?php
    $hasDetails = false;
    for($i=0; $i < count($data); $i++) { 
        if (isset($data[$i]['details']) && count($data[$i]['details']) > 0) {
            $hasDetails = true;
        }
    }
?>
<div class="card">                       
    <div class="card-body">    
        <div class="row">
            <div class="col-sm-12">
                <table id="grData" class="table table-bordered table-sm">                 
                    <thead>
                        <tr>            
                            <th class="text-center">
                                <?php if (isset($data) && count($data) > 0 && $allowExport) { ?>        
                                <a href="javascript:generalExport('reports/export','<?php echo $filterExportGet; ?>');" class="btn without-padding" title="exportar">                
                                    <i class="fas fa-download"></i>
                                </a>                 
                                <?php
                                    }
                                ?> 
                            </th>                                                            
                            <th nowrap="nowrap" <?php echo ($hasDetails?' colspan="4" ':''); ?>>Descripción</th>                                                
                            <th nowrap="nowrap">Cantidad</th>              
                            <th nowrap="nowrap">Importe Total ($)</th>                                                            
                        </tr>
                    </thead>    
                    <tbody>        
                        <?php            
                            $generalQuantity = 0;                    
                            $generalAmount = 0;                           
                        
                            if (isset($data)) { 
                                for($i=0; $i < count($data); $i++) {                                
                                    $generalQuantity += $data[$i]['quantity'];                                
                                    $generalAmount += $data[$i]['amount'];              

                                    if ($data[$i]['id'] == "SL") {
                                        $color = "red";
                                    } else {
                                        if ($data[$i]['id'] == "SR") {                                    
                                            $color = "yellow";
                                        } else {
                                            $color = "green";
                                        }                        
                                    }     

                                    if (!isset($data[$i]['details']) || (isset($data[$i]['details']) && count($data[$i]['details']) <= 0)) {
                                        $detailed = false;

                                        $details = NULL;
                                        $details[0] = array('id'=>0,
                                                            'description'=>'',
                                                            'quantity'=>$data[$i]['quantity'],
                                                            'amount'=>$data[$i]['amount']);
                                        
                                        $rowSpan = '';    
                                        if ($hasDetails) {
                                            $colSpan = ' colspan="4" ';
                                        } else {
                                            $colSpan = '';
                                        }
                                    } else {
                                        $detailed = true;
                                        $details = $data[$i]['details'];

                                        $rowSpan = ' rowspan="'.count($details).'"';                
                                        if (count($details) > 1) {
                                            $colSpan = '';
                                        } else {
                                            $colSpan = ' colspan="3" ';
                                        }
                                    }
                                    
                                    for ($j=0; $j < count($details); $j++) {                                                                                             
                        ?>
                        <tr>    
                            <?php if ($j == 0) { ?>          
                            <td width="35px" class="text-center" valign="middle" <?php echo $rowSpan; ?> style="vertical-align:middle;"><img src="<?php echo base_url(); ?>assets/images/<?php echo $color; ?>Circle.png" border="0" /></td>                                            
                            <td nowrap="nowrap" <?php echo $rowSpan; ?><?php echo $colSpan; ?> style="vertical-align:middle;">                
                                <?php echo $data[$i]['description']; ?>
                                <?php if ($data[$i]['quantity'] > 0) { ?> 
                                <a href="<?php echo base_url()."budgets?sta=".$data[$i]['id'].$filterBudgetsGet; ?>" title="ver presupuestos" target="_blank" class="btn btn-sm without-padding"><i class="fas fa-search"></i></span></a>
                                <?php } ?>                                  
                            </td>                        
                            <?php } ?>  
                            <?php if ($detailed) { ?>
                            <td nowrap="nowrap">                   
                                <?php echo $details[$j]['description']; ?>
                                <a href="<?php echo base_url()."budgets?sta=".$data[$i]['id']."|".$details[$j]['id']."|-1".$filterBudgetsGet; ?>" title="ver presupuestos" target="_blank" class="btn btn-sm without-padding"><i class="fas fa-search"></i></span></a>                                                 
                            </td>       
                            <?php if (count($details) > 1) { ?>    
                            <td nowrap="nowrap" class="text-right"><?php echo $details[$j]['quantity']; ?></td>                 
                            <td nowrap="nowrap" class="text-right"><?php echo decimalFormat($details[$j]['amount'],2); ?></td>                                 
                            <?php } ?>  
                            <?php } ?>  
                            <?php if ($j == 0) { ?>             
                            <td nowrap="nowrap" class="text-right" <?php echo $rowSpan; ?> style="vertical-align:middle;"><?php echo $data[$i]['quantity']; ?></td>                 
                            <td nowrap="nowrap" class="text-right" <?php echo $rowSpan; ?> style="vertical-align:middle;"><?php echo decimalFormat($data[$i]['amount'],2); ?></td>                             
                            <?php } ?>  
                        </tr>
                        <?php 
                                    }
                                }            
                            }
                        ?>        
                        <tr>                                           
                            <td nowrap="nowrap"></td>            
                            <td nowrap="nowrap" <?php echo ($hasDetails?' colspan="4" ':''); ?>>
                                Total General
                                <?php if ($generalQuantity > 0) { ?>
                                <a href="<?php echo base_url()."budgets?".$filterFullBudgetsGet; ?>" title="ver presupuestos" target="_blank" class="btn btn-sm without-padding" <?php echo ($generalQuantity <= 0?' disabled="disabled" ':''); ?>><i class="fas fa-search"></i></a> 
                                <?php } ?>                                
                            </td>                        
                            <td nowrap="nowrap" class="text-right"><?php echo $generalQuantity; ?></td>     
                            <td nowrap="nowrap" class="text-right"><?php echo decimalFormat($generalAmount,2); ?></td>                 
                        </tr>                              
                    </tbody>    
                </table>                   
            </div>
        </div>     
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->
<?php 
/* End of file reports_general_summary_view.php */
/* Location: ./application/views/reports/reports_general_summary_view.php */