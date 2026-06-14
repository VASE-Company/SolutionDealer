<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Filtrar por...</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" title="mostrar según filtros aplicados" onclick="loadResultsReports()"><i class="fas fa-search"></i></button>                                      
                        <button type="button" class="btn btn-tool" title="limpiar filtros" onclick="clearFilterReports()"><i class="fa fa-undo"></i></button>              
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>                        
                    </div>
                    <!-- /.card-tools -->
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="col-12"> 
                        <form action="" method="post" accept-charset="utf-8" id="frmFilter" name="frmFilter" role="form">                                                            
                            <?php include_once($filtersPath); ?>
                            <input type="hidden" id="type" name="type" value="<?php echo $type; ?>">    
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
/* End of file reports_view.php */
/* Location: ./application/views/reports/reports_view.php */