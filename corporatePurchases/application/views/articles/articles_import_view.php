<div class="card card-secondary">
    <div class="card-body">
        <form class="form-horizontal" action="<?php echo base_url(); ?>articles/importUploadFile" method="post" accept-charset="utf-8" id="frmData" name="frmData" role="form" enctype="multipart/form-data"  onsubmit="return preSubmit();">
            <div class="form-group row">
                <label class="control-label col-xs-12 col-sm-12 col-md-12 col-lg-12" style="text-align: center;font-weight: normal;">                        
                    <span style="font-weight: bold;">IMPORTANTE:</span>
                    <br />El archivo .CSV que debe subir para actualizar los artículos debe ser previamente validado por la aplicación
                    <br />que puede descargar desde aquí <a href="<?php echo base_url(); ?>articles/downloadManual" class="btn without-padding" title="descargar validador de lista de precios y manual de instrucciones" alt="_blank">
                                                         <i class="fas fa-download"></i>
                                                         </a>.
                    <br />En el mismo link encontrá un manual con las indicaciones para realizar el proceso de validación del archivo.
                    <br />Tenga en cuenta que si se sube un archivo sin ser validado corre el riesgo de que se actualicen incorrectamente los datos de los artículos.
                    <br />
                    <br />
                </label>                       
            </div>
            <div class="form-group row">
                <label for="importFile" class="col-form-label col-form-label-sm col-xs-3 col-sm-3 col-md-3 col-lg-3 text-right" style="margin-top:5px;">Archivo .CSV:</label>
                <div class="col-xs-9 col-sm-9 col-md-7 col-lg-5">                                     
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="importFile" name="importFile" autofocus="1">
                        <label class="custom-file-label" for="importFile">Seleccione el archivo</label>
                    </div>                                                 
                </div>                                
            </div>      
            <?php if (isset($error) && $error <> "")  { ?>
            <div class="form-group row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
                <?php echo errorFormat($error); ?>  
                </div>                                        
            </div>
            <?php } ?>                                                                                                                                                                  
            <div class="form-group row" style="margin-top:20px;">           
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">                          
                    <button type="submit" id="btnSend" name="btnSend" class="btn btn-success type-btn-save">Siguiente</button>                                        
                    <button type="button" class="btn btn-danger type-btn-save" onclick="getUrl('<?php echo base_url(); ?>articles')">Cancelar</button>                                                               
                </div>
            </div>            
        </form>
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->     
<?php 
/* End of file articles_import_view.php */
/* Location: ./application/views/articles/articles_import_view.php */