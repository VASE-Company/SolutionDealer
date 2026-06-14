<div class="card card-secondary">
    <div class="card-body">        
        <div class="form-group row">
            <label class="control-label col-xs-12 col-sm-12 col-md-12 col-lg-12" style="text-align: center;font-weight: normal;">                        
                <label>Total de Facturas pendientes de pago: <span><?php echo $pendigBills; ?></span></label><br />                      
                <label style="font-weight:normal;">Mails OK: <span style="font-weight:bold;"><?php echo $mailsOK; ?></span></label><br />
                <label style="font-weight:normal;">Mails ERROR: <span style="font-weight:bold;"><?php echo $mailsERROR; ?></span></label><br />                        
                <label style="font-weight:normal;">Mail Interno: <span style="font-weight:bold;"><?php echo $mailInternal; ?></span></label><br />                                       
            </label>                       
        </div>                                                                                                                                                                                
        <div class="form-group row" style="margin-top:20px;">           
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">                                                                
                <button type="button" class="btn btn-success type-btn-save" onclick="getUrl('<?php echo base_url(); ?>bills')">Volver</button>                                           
            </div>
        </div>           
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->     
<?php 
/* End of file bills_send_mail_pending_view.php */
/* Location: ./application/views/bills/bills_send_mail_pending_view.php */