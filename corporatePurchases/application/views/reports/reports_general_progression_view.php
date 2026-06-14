<?php        
    $xAxis = ((isset($data) && isset($data['xAxis']))?$data['xAxis']:array()); 
    $yAxis = ((isset($data) && isset($data['yAxis']))?$data['yAxis']:array());     
    $values = ((isset($data) && isset($data['values']))?$data['values']:array());       
    $yAxisTitle = ((isset($data) && isset($data['yAxisTitle']))?$data['yAxisTitle']:"");    

    $allowGraphic = (isset($data) && isset($data['values']) && count($data['values']) > 0);
?>
<div class="card">                       
    <div class="card-body">  
        <div class="row">
            <label class="col-form-label col-form-label-sm col-xs-12 col-sm-12 col-md-12 col-lg-12">            
            <?php if ($allowExport) { ?>        
            <a href="javascript:generalExport('reports/export','<?php echo $filterExportGet; ?>');" class="btn without-padding" title="exportar" style="margin-right:10px;">                
                <i class="fas fa-download"></i>
            </a>                 
            <?php
                }
            ?> 
            <?php if ($allowExport) { ?>        
            <a href="javascript:generateGraphicProgressionGeneralReport();" class="btn without-padding" title="ver gráfico">                
                <i class="fas fa-chart-bar"></i>
            </a>                 
            <?php
                }
            ?> 
            </label>            
        </div>   
        <div class="row">
            <div class="col-sm-12" style="overflow-x: scroll;">
                <table id="grData" class="table table-bordered table-sm" columns="<?php echo count($xAxis); ?>" description="Progresión: <?php echo $yAxisTitle; ?>" xTitle="Mes" yTitle="Total">                 
                    <thead>
                        <tr>
                            <th><?php echo $yAxisTitle; ?>
                                <button type="button" class="btn btn-default btn-xs" onclick="showHideRowsReports('grData')" style="float:right;" title="mostrar/ocultar filas con valor 0">
                                    <i class="fas fa-eye" id="showRowsIcon"></i>
                                </button>
                            </th>     
                            <?php 
                                for ($x=0; $x < count($xAxis); $x++) {
                                    $xAxis[$x]['total'] = 0;
                            ?>                                        
                            <th nowrap="nowrap" id="colX<?php echo ($x + 1); ?>" description="<?php echo $xAxis[$x]['description']; ?>"><?php echo $xAxis[$x]['description']; ?></th>           
                            <?php } ?>                                     
                            <th>Total</th>  
                        </tr>
                    </thead>   
                    <tbody>        
                        <?php 
                            for ($y=0; $y < count($yAxis); $y++) {
                                $rowTotal = 0;
                                for ($x=0; $x < count($xAxis); $x++) {
                                    $value = 0;
                                    if (isset($values[$yAxis[$y]['id']][(int)$xAxis[$x]['month']][(int)$xAxis[$x]['year']])) {
                                        $value = $values[$yAxis[$y]['id']][(int)$xAxis[$x]['month']][(int)$xAxis[$x]['year']];                                    
                                    }
                                    $xAxis[$x]['total'] += (float)$value;
                                    $rowTotal += (float)$value;
                                }
                                $class = ((float)$rowTotal == 0?"rowToHide":""); 
                        ?>                                                                
                        <tr class="statRow <?php echo $class ?>" description="<?php echo $yAxis[$y]['description']; ?>" id="<?php echo $yAxis[$y]['id']; ?>"> 
                            <td nowrap="nowrap"><?php echo $yAxis[$y]['description']; ?></td>   
                            <?php                                 
                                for ($x=0; $x < count($xAxis); $x++) {
                                    $value = 0;
                                    if (isset($values[$yAxis[$y]['id']][(int)$xAxis[$x]['month']][(int)$xAxis[$x]['year']])) {
                                        $value = $values[$yAxis[$y]['id']][(int)$xAxis[$x]['month']][(int)$xAxis[$x]['year']];                                    
                                    }                                    
                                    if (isset($decimalValues) && $decimalValues) {
                                        $value = decimalFormat((float)$value);
                                    } else {
                                        $value = (int)$value;
                                    }                                                                      
                            ?>                                        
                            <td nowrap="nowrap" class="text-center" id="colX<?php echo ($x + 1); ?>"><?php echo $value; ?></td>                                     
                            <?php } ?>  
                            <?php
                                if (isset($decimalValues) && $decimalValues) {
                                    $rowTotal = decimalFormat((float)$rowTotal);
                                } else {
                                    $rowTotal = (int)$rowTotal;
                                } 
                            ?>                         
                            <td nowrap="nowrap" class="text-center"><?php echo $rowTotal; ?></td>  
                        </tr>       
                        <?php } ?>                        
                    </tbody> 
                    <tfoot>                        
                        <tr>
                            <th>Total</th>     
                            <?php 
                                $rowTotal = 0;
                                for ($x=0; $x < count($xAxis); $x++) {                                    
                                    $rowTotal += $xAxis[$x]['total'];
                                    if (isset($decimalValues) && $decimalValues) {
                                        $total = decimalFormat((float)$xAxis[$x]['total']);
                                    } else {
                                        $total = (int)$xAxis[$x]['total'];
                                    }                                      
                            ?>                                        
                            <th nowrap="nowrap" class="text-center"><?php echo $total; ?></th>           
                            <?php } ?> 
                            <?php
                                if (isset($decimalValues) && $decimalValues) {
                                    $rowTotal = decimalFormat((float)$rowTotal);
                                } else {
                                    $rowTotal = (int)$rowTotal;
                                } 
                            ?>                         
                            <th nowrap="nowrap" class="text-center"><?php echo $rowTotal; ?></th>                                      
                        </tr>
                    </tfoot> 
                </table>                   
            </div>
        </div>     
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->
<?php 
/* End of file reports_general_progression_view.php */
/* Location: ./application/views/reports/reports_general_progression_view.php */