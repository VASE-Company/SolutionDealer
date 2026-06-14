<div class="row without-padding">   
    <div class="col-sm-12" style="margin-bottom: 0px;margin-top: 0px;margin-left: 9px;"> 
        
    </div>  
    <div class="col-sm-12" style="max-height:250px;overflow-y:auto;">
        <table id="grHistory" class="table table-bordered table-sm">
            <thead>
                <tr>                                                                                         
                    <th nowrap="nowrap">Fecha</th>  
                    <th nowrap="nowrap">Descripción</th>                                           
                    <th nowrap="nowrap">Usuario</th>                                                       
                </tr>
            </thead>
            <?php if (isset($history)) { ?>
            <tbody>
                <?php
                    for($i=0; $i < count($history); $i++) {                                            
                ?>
                <tr>                                
                    <td nowrap="nowrap"><?php echo dateFormat($history[$i]['date'],true); ?></td> 
                    <td nowrap="nowrap"><?php echo $history[$i]['description']; ?></td>  
                    <?php 
                        if ($history[$i]['userLastName'] != "" || $history[$i]['userFirstName'] != "") {
                            if ($history[$i]['userLastName'] != "" && $history[$i]['userFirstName'] != "") {
                                $user = $history[$i]['userLastName'].", ".$history[$i]['userFirstName'];
                            } else {
                                $user = $history[$i]['userLastName'].$history[$i]['userFirstName'];
                            }
                        } else {
                            $user = "";
                        }
                    ?>
                    <td><?php echo $user; ?></td>                                                                                                                               
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
/* End of file orders_history_view.php */
/* Location: ./application/views/orders/orders_history_view.php */