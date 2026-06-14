<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Buscar por...</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" title="mostrar según filtros aplicados" onclick="loadResultsStatistics()"><i class="fas fa-search"></i></button>                                      
                        <button type="button" class="btn btn-tool" title="limpiar filtros" onclick="clearFilterStatistics()"><i class="fa fa-undo"></i></button>              
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>                        
                    </div>
                    <!-- /.card-tools -->
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="col-12"> 
                        <form action="" method="post" accept-charset="utf-8" id="frmFilter" name="frmFilter" role="form">                                                            
                            <div class="row">       
                                <div class="form-group" style="float:left;margin-right:10px;">
                                    <label for="dateFromFilter">Desde</label>
                                    <input type="date" id="dateFromFilter" name="dateFromFilter" class="form-control form-control-sm noEnterMyApp" autocomplete="off"  autofocus="1" value="<?php echo $dateFromFilter; ?>" original="<?php echo $dateFromFilter; ?>" style="width:140px;">
                                </div>   
                                <div class="form-group" style="float:left;margin-right:10px;">
                                    <label for="dateToFilter">Hasta</label>
                                    <input type="date" id="dateToFilter" name="dateToFilter" class="form-control form-control-sm noEnterMyApp" autocomplete="off" value="<?php echo $dateToFilter; ?>" original="<?php echo $dateFromFilter; ?>" style="width:140px;">                
                                </div>                                  
                                <div class="form-group" style="float:left;margin-right:10px;">
                                    <label for="type">Tipo</label>
                                    <select id="type" name="type" class="form-control form-control-sm">                                            
                                        <option value="">[Seleccionar]</option>                                    
                                        <?php 
                                            for($i=0; $i < count($reports); $i++) { 
                                                $attributes = "";    
                                                if (isset($reports[$i]['callback']) && trim($reports[$i]['callback']) != "") {
                                                    $attributes .= 'callback="'.trim($reports[$i]['callback']).'" ';
                                                }                                                
                                        ?>
                                        <option value="<?php echo $reports[$i]['id']; ?>" <?php if ($i == 0) echo ' selected="selected" '; ?> <?php echo $attributes; ?>><?php echo $reports[$i]['description']; ?></option>                
                                        <?php } ?>                    
                                    </select>  
                                </div>                                                      
                            </div>                                  
                        </form>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <div id="predata"></div>     
            <div style="margin-top:10px;" id="data"></div>                
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->    
</div>
<!-- /.container-fluid -->
<?php
/* End of file statistics_view.php */
/* Location: ./application/views/statistics/statistics_view.php */