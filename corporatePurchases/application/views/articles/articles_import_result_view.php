<div class="card card-secondary">
    <div class="card-body">
        <form class="form-horizontal" action="" method="post" accept-charset="utf-8" id="frmData" name="frmData" role="form" enctype="multipart/form-data">
            <div class="form-group row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center" style="padding-top:20px;">
                    <?php if ($error != "") { ?>
                    <label style="color:red;font-weight:bold;"><?php echo $error; ?></label>                                                
                    <?php } else { ?>
                    <label style="color:green;font-weight:bold;">Se han actualizado los artículos correctamente</label>                                                                      
                    <?php } ?>
                    <br />
                </div>                
            </div>                                                                                                                                                                
            <div class="form-group row" style="margin-top:20px;">           
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">                                                                                       
                    <button type="button" class="btn btn-success type-btn-save" onclick="getUrl('<?php echo base_url(); ?>articles')">Cerrar</button>                                                          
                </div>
            </div>            
        </form>
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->     
<?php 
/* End of file articles_import_result_view.php */
/* Location: ./application/views/articles/articles_import_result_view.php */