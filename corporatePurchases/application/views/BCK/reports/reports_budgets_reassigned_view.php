<div class="card">                       
    <div class="card-body">    
        <div class="row">
            <div class="col-sm-12">
                <table id="grData" class="table table-bordered table-sm">                 
                    <thead>
                        <tr>                                                                      
                            <th nowrap="nowrap">Compañia</th>                                                
                            <th nowrap="nowrap">Asesor</th>              
                            <th nowrap="nowrap">Cantidad</th>                                                            
                        </tr>
                    </thead>    
                    <tbody>        
                        <?php                                 
                            $generalQuantity = 0;                    
                            $generalCompany = 0;                           
                        
                            if (isset($data)) { 
                                for($i=0; $i < count($data); $i++) {                                
                                    $generalQuantity += $data[$i]['quantity'];                                                                                                                                                                             
                        ?>
                        <tr>                                                                                                            
                            <td nowrap="nowrap"><?php echo $data[$i]['companyDescription']; ?></td>                                            
                            <td nowrap="nowrap"><?php echo $data[$i]['assessorDescription']; ?></td>                                            
                            <td nowrap="nowrap" class="text-center"><?php echo $data[$i]['quantity']; ?></td>                                            
                        </tr>
                        <?php                                     
                                }            
                            }
                        ?>        
                        <tr style="font-weight:bold;">                                           
                            <td nowrap="nowrap">Total General</td>            
                            <td nowrap="nowrap"></td>                                                            
                            <td nowrap="nowrap" class="text-center"><?php echo $generalQuantity; ?></td>                                 
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
/* End of file reports_budgets_reassigned_view.php */
/* Location: ./application/views/reports/reports_budgets_reassigned_view.php */