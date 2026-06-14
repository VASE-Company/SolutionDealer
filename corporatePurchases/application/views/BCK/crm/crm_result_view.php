<div class="card">                       
    <div class="card-body">    
        <div class="row">
            <div class="col-sm-12">
                <table id="grData" class="table table-bordered table-hover table-sm">
                    <thead>
                        <tr>                                                                                         
                            <th nowrap="nowrap">Apellido y Nombres  
                                <?php echo generateOrderButton(true,($fieldOrder == 'cli' && $typeOrder == 'asc'),'crm/result/1?fOrd=cli&tOrd=asc'.$filterGet,'reloadResultsCRM'); ?>
                                <?php echo generateOrderButton(false,($fieldOrder == 'cli' && $typeOrder == 'desc'),'crm/result/1?fOrd=cli&tOrd=desc'.$filterGet,'reloadResultsCRM'); ?>                       
                            </th>
                            <th nowrap="nowrap">Email</th>   
                            <th nowrap="nowrap">Teléfonos</th>                       
                            <th nowrap="nowrap">Vehículo  
                                <?php echo generateOrderButton(true,($fieldOrder == 'veh' && $typeOrder == 'asc'),'crm/result/1?fOrd=veh&tOrd=asc'.$filterGet,'reloadResultsCRM'); ?>
                                <?php echo generateOrderButton(false,($fieldOrder == 'veh' && $typeOrder == 'desc'),'crm/result/1?fOrd=veh&tOrd=desc'.$filterGet,'reloadResultsCRM'); ?>                       
                            </th>
                            <th nowrap="nowrap">Año 
                                <?php echo generateOrderButton(true,($fieldOrder == 'year' && $typeOrder == 'asc'),'crm/result/1?fOrd=year&tOrd=asc'.$filterGet,'reloadResultsCRM'); ?>
                                <?php echo generateOrderButton(false,($fieldOrder == 'year' && $typeOrder == 'desc'),'crm/result/1?fOrd=year&tOrd=desc'.$filterGet,'reloadResultsCRM'); ?>                       
                            </th>                                   
                            <th nowrap="nowrap">Kms   
                                <?php echo generateOrderButton(true,($fieldOrder == 'kms' && $typeOrder == 'asc'),'crm/result/1?fOrd=kms&tOrd=asc'.$filterGet,'reloadResultsCRM'); ?>
                                <?php echo generateOrderButton(false,($fieldOrder == 'kms' && $typeOrder == 'desc'),'crm/result/1?fOrd=kms&tOrd=desc'.$filterGet,'reloadResultsCRM'); ?>                       
                            </th>                                      
                        </tr>
                    </thead>
                    <?php if (isset($data)) { ?>
                    <tbody>
                        <?php
                            for($i=0; $i < count($data); $i++) {                                            
                        ?>
                        <tr>                                
                            <td nowrap="nowrap"><?php echo $data[$i]['clientName']; ?></td> 
                            <td nowrap="nowrap"><?php echo $data[$i]['clientEmail']; ?></td>                                                                                                                   
                            <td nowrap="nowrap"><?php echo $data[$i]['clientPhones']; ?></td>  
                            <td nowrap="nowrap"><?php echo $data[$i]['vehicleDescription']; ?></td>  
                            <td nowrap="nowrap" class="text-center"><?php echo ((float)$data[$i]['vehicleYear'] > 0?$data[$i]['vehicleYear']:""); ?></td>                                         
                            <td nowrap="nowrap" class="text-right"><?php echo ((float)$data[$i]['vehiclekms'] > 0?$data[$i]['vehiclekms']:""); ?></td>                      
                        </tr>  
                        <?php 
                            } 
                        ?>                         
                    </tbody>
                    <?php } ?>
                </table>
            </div>
        </div>
        <?php if (isset($data) && count($data) > 0 && ($allowExport != "" || $pagination != "")) { ?>
            <div class="row">
                <div class="col-sm-12">
                    <?php
                        if ($allowExport != "") {
                    ?>            
                    <a href="javascript:generalExport('crm/export','<?php echo $filterGet.$orderGet; ?>');" class="pagination pagination-sm paginate_button page-item page-link" title="exportar" style="float:left; margin-right:5px;padding: 4px 8px 5px 8px;">                
                        <i class="fas fa-download"></i>
                    </a> 
                    <?php } ?>
                    <?php
                        if ($pagination != "") {
                    ?>            
                        <?php echo $pagination; ?>        
               
                    <?php
                        }
                    ?>
                </div>
            </div>
        <?php
            }
        ?>      
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->
<?php 
/* End of file crm_result_view.php */
/* Location: ./application/views/crm/crm_result_view.php */