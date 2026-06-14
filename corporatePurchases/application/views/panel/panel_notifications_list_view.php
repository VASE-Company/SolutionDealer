<div class="card">                       
    <div class="card-body" style="padding:10px;">    
        <div class="row">
            <div class="col-sm-12">
                <table id="grNotifications" class="table table-bordered table-hover table-sm">
                    <thead>
                        <tr>                                                                                         
                            <th nowrap="nowrap">Fecha  
                                <?php echo generateOrderButton(true,($fieldOrder == 'date' && $typeOrder == 'asc'),'panel/notifications/1?fOrd=date&tOrd=asc'.$filterGet,'reloadNotifications'); ?>
                                <?php echo generateOrderButton(false,($fieldOrder == 'date' && $typeOrder == 'desc'),'panel/notifications/1?fOrd=date&tOrd=des'.$filterGet,'reloadNotifications'); ?>                       
                            </th>                                                   
                            <th nowrap="nowrap">Notificación</th>   
                            <th nowrap="nowrap">Leído</th>       
                            <th class="text-center" nowrap="nowrap" width="35px"></th>                           
                        </tr>
                    </thead>
                    <?php if (isset($notifications)) { ?>
                    <tbody>
                        <?php
                            for($i=0; $i < count($notifications); $i++) {                                            
                        ?>
                        <tr>                                
                            <td nowrap="nowrap"><?php echo dateFormat($notifications[$i]['date'],true); ?></td>                                                         
                            <?php                                 
                                if (strlen($notifications[$i]['observation']) > 43) {
                                    $attr = ' title="'.$notifications[$i]['observation'].'"'; 
                                    $observation = substr($notifications[$i]['observation'],0,40)."...";
                                } else {
                                    $attr = ""; 
                                    $observation = $notifications[$i]['observation'];
                                }                                
                            ?> 
                            <td nowrap="nowrap" <?php echo $attr; ?>><?php echo $observation; ?></td>                                                                                                             
                            <td nowrap="nowrap" class="text-center"><?php echo getBooleanToText($notifications[$i]['readByUser']); ?></td>                                         
                            <td nowrap="nowrap" class="text-center">   
                                <?php                                    
                                    if ($notifications[$i]['link'] != "") {
                                ?>
                                <a href="<?php echo $notifications[$i]['link']; ?>" class="btn without-padding" title="ir">
                                    <i class="fas fa-arrow-circle-right" style="color:#007bff"></i>
                                </a> 
                                <?php                                                    
                                    }
                                ?>                                                                                 
                            </td> 
                        </tr>  
                        <?php 
                            } 
                        ?>                         
                    </tbody>
                    <?php } ?>
                </table>
            </div>
        </div>
        <?php if (isset($notifications) && count($notifications) > 0 && $pagination != "") { ?>
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
<?php 
/* End of file panel_notifications_list_view.php */
/* Location: ./application/views/panel/panel_notifications_list_view.php */