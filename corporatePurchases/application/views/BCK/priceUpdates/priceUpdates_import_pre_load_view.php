<div class="card card-secondary">
    <div class="card-body">
        <form class="form-horizontal" action="<?php echo base_url(); ?>priceUpdates/importLoad" method="post" accept-charset="utf-8" id="frmData" name="frmData" role="form" onsubmit="return preSubmit();">
            <div class="form-group row">
                <label class="control-label col-xs-12 col-sm-12 col-md-12 col-lg-12" style="text-align: center;font-weight: normal;">                        
                    <label>Total de artículos: <span><?php echo $count; ?></span> (comparar con el total del excel original)</label><br />                      
                    <label style="font-weight:normal;">Aumentaron su precio: <span style="font-weight:bold;"><?php echo $increase; ?></span></label><br />
                    <label style="font-weight:normal;">Disminuyeron su precio: <span style="font-weight:bold;"><?php echo $decrease; ?></span></label><br />                        
                    <label style="font-weight:normal;">Mantienen su precio (+/- $ 1 de diferencia): <span style="font-weight:bold;"><?php echo $equal; ?></span></label><br />                       
                    <label style="font-weight:normal;">Nuevos: <span style="font-weight:bold;"><?php echo $news; ?></span></label><br /><br />
                    <label style="font-weight:normal;">Presione <span style="font-weight:bold;">Finalizar</span> para actualizar definitivamente los artículos.</label>
                </label>                       
            </div>                                                                                                                                                                                
            <div class="form-group row" style="margin-top:20px;">           
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">                                                
                    <button type="submit" id="btnSend" name="btnSend" class="btn btn-success type-btn-save">Finalizar</button>                                                      
                    <button type="button" class="btn btn-danger type-btn-save" onclick="getUrl('<?php echo base_url(); ?>priceUpdates/import')">Atrás</button>                                           
                </div>
            </div>            
            <input type="hidden" id="filename" name="filename" value="<?php echo $filename; ?>">   
            <input type="hidden" id="count" name="count" value="<?php echo $count; ?>">   
            <input type="hidden" id="increase" name="increase" value="<?php echo $increase; ?>">   
            <input type="hidden" id="decrease" name="decrease" value="<?php echo $decrease; ?>">   
            <input type="hidden" id="equal" name="equal" value="<?php echo $equal; ?>">   
            <input type="hidden" id="news" name="news" value="<?php echo $news; ?>">  
        </form>
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->     
<?php 
/* End of file priceUpdates_import_pre_load_view.php */
/* Location: ./application/views/priceUpdates/priceUpdates_import_pre_load_view.php */