<?php            
    $yAxis = ((isset($data) && isset($data['yAxis']))?$data['yAxis']:array());     
    $values = ((isset($data) && isset($data['values']))?$data['values']:array());       
    $yAxisTitle = ((isset($data) && isset($data['yAxisTitle']))?$data['yAxisTitle']:""); 
    $total = 0;    

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
            <a href="javascript:generateGraphicTotalizedGeneralReport();" class="btn without-padding" title="ver gráfico">                
                <i class="fas fa-chart-pie"></i>
            </a>                 
            <?php
                }
            ?> 
            </label>            
        </div> 
        <div class="row">
            <div class="col-sm-12" style="overflow-x: scroll;">
                <table id="grData" class="table table-bordered table-sm" style="width:auto" description="<?php echo $yAxisTitle; ?>" >                 
                    <thead>
                        <tr>
                            <th style="width:250px;"><?php echo $yAxisTitle; ?></th>                                                             
                            <th nowrap="nowrap" style="width:150px;" id="colTotal" description="Total">Total
                                <button type="button" class="btn btn-default btn-xs" onclick="showHideRowsReports('grData')" style="float:right;" title="mostrar/ocultar filas con valor 0">
                                    <i class="fas fa-eye" id="showRowsIcon"></i>
                                </button>
                            </th>                                                                       
                        </tr>
                    </thead>   
                    <tbody>        
                        <?php 
                            for ($y=0; $y < count($yAxis); $y++) {
                                $value = 0;
                                if (isset($values[$yAxis[$y]['id']])) {
                                    $value = $values[$yAxis[$y]['id']];                                    
                                }
                                $class = ((float)$value == 0?"rowToHide":"");   
                                $total += (float)$value;
                                if (isset($decimalValues) && $decimalValues) {
                                    $value = decimalFormat((float)$value);
                                } else {
                                    $value = (int)$value;
                                } 
                        ?>                                                                
                        <tr class="statRow <?php echo $class ?>" id="<?php echo $yAxis[$y]['id']; ?>"> 
                            <td nowrap="nowrap" id="title" class="statCol" description="<?php echo $yAxis[$y]['description']; ?>"><?php echo $yAxis[$y]['description']; ?></td>                                                         
                            <td nowrap="nowrap" class="text-center statCol" id="colTotal"><?php echo $value; ?></td>                                                         
                        </tr>       
                        <?php } ?>                        
                    </tbody> 
                    <tfoot>                        
                        <tr>
                            <th>Total General</th>     
                            <?php                                                                                           
                                if (isset($decimalValues) && $decimalValues) {
                                    $total = decimalFormat((float)$total);
                                } else {
                                    $total = (int)$total;
                                }  
                            ?>                                        
                            <th nowrap="nowrap" class="text-center"><?php echo $total; ?></th>                                       
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