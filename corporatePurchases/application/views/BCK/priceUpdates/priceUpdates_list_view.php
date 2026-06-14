<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">                       
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <table id="grPriceUpdates" class="table table-bordered table-hover table-sm">
                                <thead>
                                    <tr>                                                                                  
                                        <th class="text-center" nowrap="nowrap" width="<?php echo (($allowInsert && $allowDownloadTemplate)?"55px":"35px"); ?>">                                                                
                                            <?php if ($allowInsert) { ?>
                                            <a href="<?php echo base_url(); ?>priceUpdates/import" class="btn without-padding" title="nuevo">
                                                <i class="far fa-plus-square"></i>
                                            </a>                                            
                                            <?php } ?>                                                
                                            <?php if ($allowDownloadTemplate) { ?>                                            
                                            <a href="<?php echo base_url(); ?>priceUpdates/downloadTemplate" class="btn without-padding" title="descargar plantilla para actualizar los precios" alt="_blank">
                                                <i class="fas fa-download"></i>
                                            </a> 
                                            <?php } ?>                                                                                        
                                        </th>                                                                    
                                        <th nowrap="nowrap">Fecha  
                                            <?php echo generateOrderButton(true,($fieldOrder == 'date' && $typeOrder == 'asc'),'priceUpdates/listing/1?fOrd=date&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'date' && $typeOrder == 'desc'),'priceUpdates/listing/1?fOrd=date&tOrd=desc'.$filterGet); ?>                       
                                        </th>                                        
                                        <th nowrap="nowrap">Total</th>                                     
                                        <th nowrap="nowrap" class="text-center" title="aumentaron su precio">+ $</th>                                        
                                        <th nowrap="nowrap" class="text-center" title="disminuyeron su precio">- $</th>                                        
                                        <th nowrap="nowrap" class="text-center" title="mantuvieron su precio">= $</th>                                        
                                        <th nowrap="nowrap">Nuevos</th>  
                                        <th nowrap="nowrap">Estado</th>
                                        <th nowrap="nowrap">Observación</th>
                                        <?php if ($allowInsert) { ?>
                                        <th class="text-center" nowrap="nowrap" width="35px"></th>  
                                        <?php } ?>  
                                    </tr>
                                </thead>
                                <?php if (isset($priceUpdates)) { ?>
                                <tbody>
                                    <?php
                                        for($i=0; $i < count($priceUpdates); $i++) {                                            
                                    ?>
                                    <tr ondblclick="onClick('btnEdit<?php echo $priceUpdates[$i]['id']; ?>')">                                
                                        <td class="text-center" nowrap="nowrap">                            
                                            <?php if ($allowDelete) { ?>
                                            <button type="button" class="btn without-padding" onclick="generalDelete(<?php echo $priceUpdates[$i]['id']; ?>,'priceUpdates/delete')" title="eliminar">
                                                <i class="far fa-minus-square"></i>
                                            </button>                                            
                                            <?php } ?>                                    
                                        </td>  
                                        <td nowrap="nowrap"><?php echo dateFormat($priceUpdates[$i]['date'],true); ?></td> 
                                        <td nowrap="nowrap" class="text-center"><?php echo $priceUpdates[$i]['count']; ?></td>  
                                        <td nowrap="nowrap" class="text-center"><?php echo $priceUpdates[$i]['increase']; ?></td>  
                                        <td nowrap="nowrap" class="text-center"><?php echo $priceUpdates[$i]['decrease']; ?></td>  
                                        <td nowrap="nowrap" class="text-center"><?php echo $priceUpdates[$i]['equal']; ?></td>  
                                        <td nowrap="nowrap" class="text-center"><?php echo $priceUpdates[$i]['news']; ?></td>  
                                        <td nowrap="nowrap" class="text-center"><?php echo $priceUpdates[$i]['state']; ?></td>  
                                        <td><?php echo $priceUpdates[$i]['observation']; ?></td>                                          
                                        <?php if ($allowInsert) { ?>                              
                                        <td nowrap="nowrap" class="text-center">           
                                            <?php
                                                $path = $this->config->item('files').$this->session->userdata('companyId').'/articles/priceUpdates_'.$priceUpdates[$i]['id'].'.csv';                                                                                                
                                                if (file_exists($path)) {
                                            ?>
                                            <a href="<?php echo base_url(); ?>priceUpdates/download/<?php echo $priceUpdates[$i]['id']; ?>" class="btn without-padding" title="descargar" alt="_blank">
                                                <i class="fas fa-download"></i>
                                            </a> 
                                            <?php                                                    
                                                }
                                            ?>   
                                        </td>    
                                        <?php } ?>                                                                                                                                                                                                                                                                 
                                    </tr>  
                                    <?php 
                                        } 
                                    ?>                         
                                </tbody>
                                <?php } ?>
                            </table>
                        </div>
                    </div>
                    <?php
                        if ($pagination != "") {
                    ?>
                    <div class="row">
                        <div class="col-sm-12">
                        <?php echo $pagination; ?>        
                        </div>
                    </div>
                    <?php
                        }
                    ?>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->    
</div>
<!-- /.container-fluid -->
<?php
/* End of file priceUpdates_list_view.php */
/* Location: ./application/views/priceUpdates/priceUpdates_list_view.php */