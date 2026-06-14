<?php   
    if (!isset($data['summaryByState']) || (isset($data['summaryByState']) && count($data['summaryByState']) <= 0)) {     
        echo '<div class="error alert alert-success" style="margin-top:10px;">No hay datos para los filtros seleccionados.</div>';
        exit;
    }
?>
<div class="card">                       
    <div class="card-body">    
        <div class="row">
            <div class="col-md-6">
                <!-- GENERAL -->
                <div class="card card-danger">
                    <div class="card-header">
                        <h3 class="card-title">General</h3>  
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>                          
                        </div>             
                    </div>
                    <div class="card-body">
                        <canvas id="gralChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        <?php      
                            $summaryByState = $data['summaryByState'];
                            $count = (isset($summaryByState)?count($summaryByState):0);
                            for ($i=0; $i < $count; $i++) {
                                switch ($summaryByState[$i]['id']) {
                                    case 'SL':
                                        $color = '#dc3545';
                                    break;
                                    case 'SR':
                                        $color = '#ffc107';
                                    break;
                                    case 'CR':
                                        $color = '#28a745';
                                    break;
                                    default:
                                        $color = "";
                                    break;
                                }                                
                        ?>
                        <input type="hidden" id="gralData<?php echo ($i+1); ?>" value="<?php echo $summaryByState[$i]['quantity']; ?>">     
                        <input type="hidden" id="gralDescription<?php echo ($i+1); ?>" value="<?php echo $summaryByState[$i]['description']; ?>: <?php echo $summaryByState[$i]['quantity']; ?> pto(s)">                             
                        <input type="hidden" id="gralColor<?php echo ($i+1); ?>" value="<?php echo $color; ?>">                             
                        <?php
                            }
                        ?>
                        <input type="hidden" id="gralCount" value="<?php echo $count; ?>">  
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->

                <!-- EFICIENCIA POR FAMILIA -->
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">Eficiencia por Familia</h3>  
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>                          
                        </div>             
                    </div>
                    <div class="card-body">
                        <canvas id="effFamilyChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        <?php      
                            $summaryByFamilyQuantity = $data['summaryByFamilyQuantity'];
                            $count = (isset($summaryByFamilyQuantity)?count($summaryByFamilyQuantity):0);
                            for ($i=0; $i < $count; $i++) {                           
                        ?>
                        <input type="hidden" id="effFamilyData<?php echo ($i+1); ?>" value="<?php echo $summaryByFamilyQuantity[$i]['quantity']; ?>">     
                        <input type="hidden" id="effFamilyDescription<?php echo ($i+1); ?>" value="<?php echo $summaryByFamilyQuantity[$i]['description']; ?>: <?php echo $summaryByFamilyQuantity[$i]['quantity']; ?>">                             
                        <input type="hidden" id="effFamilyColor<?php echo ($i+1); ?>" value="<?php echo randomColor(); ?>">                             
                        <?php
                            }
                        ?>
                        <input type="hidden" id="effFamilyCount" value="<?php echo $count; ?>">  
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
          </div>
          <!-- /.col (LEFT) -->

          <div class="col-md-6">                
                <!-- PRESUPUESTOS SIN RESPUESTA -->
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">Presupuestado (Sin Respuesta)</h3>   
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>                          
                        </div>            
                    </div>
                    <div class="card-body">
                        <canvas id="unresponsedChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        <?php      
                            $summaryByFamilyAmount = $data['summaryByFamilyAmount'];
                            $count = (isset($summaryByFamilyAmount)?count($summaryByFamilyAmount):0);
                            for ($i=0; $i < $count; $i++) {                           
                        ?>
                        <input type="hidden" id="unresponsedData<?php echo ($i+1); ?>" value="<?php echo $summaryByFamilyAmount[$i]['amount']; ?>">     
                        <input type="hidden" id="unresponsedDescription<?php echo ($i+1); ?>" value="<?php echo $summaryByFamilyAmount[$i]['description']; ?>: $ <?php echo decimalFormat($summaryByFamilyAmount[$i]['amount']); ?>">                             
                        <input type="hidden" id="unresponsedColor<?php echo ($i+1); ?>" value="<?php echo randomColor(); ?>">                             
                        <?php
                            }
                        ?>
                        <input type="hidden" id="unresponsedCount" value="<?php echo $count; ?>">  
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->

                 <!-- PRESUPUESTOS RECHAZADOS -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Presupuestos Rechazados</h3>   
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>                          
                        </div>           
                    </div>
                    <div class="card-body">
                        <canvas id="rejectedChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        <?php      
                            $summaryRejected = $data['summaryRejected'];
                            $count = (isset($summaryRejected)?count($summaryRejected):0);
                            for ($i=0; $i < $count ; $i++) {                           
                        ?>
                        <input type="hidden" id="rejectedData<?php echo ($i+1); ?>" value="<?php echo $summaryRejected[$i]['amount']; ?>">     
                        <input type="hidden" id="rejectedDescription<?php echo ($i+1); ?>" value="<?php echo $summaryRejected[$i]['description']; ?>: $ <?php echo decimalFormat($summaryRejected[$i]['amount']); ?>">                             
                        <input type="hidden" id="rejectedColor<?php echo ($i+1); ?>" value="<?php echo randomColor(); ?>">                             
                        <?php
                            }
                        ?>
                        <input type="hidden" id="rejectedCount" value="<?php echo $count ; ?>">  
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
          </div>
          <!-- /.col (LEFT) -->
        </div>     
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->
<?php 
/* End of file reports_general_efficiency_view.php */
/* Location: ./application/views/reports/reports_general_efficiency_view.php */