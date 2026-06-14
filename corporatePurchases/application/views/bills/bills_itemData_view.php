<div class="row without-padding">   
    <div class="col-sm-12" style="margin-bottom: 0px;margin-top: 0px;margin-left: 9px;"> 
        
    </div>  
    <div class="col-sm-12" style="max-height:250px;overflow-y:auto;">
        <table id="grHistory" class="table table-bordered table-sm">
            <thead>
                <tr>                                                           
                    <th nowrap="nowrap" width="100px">Fecha</th>                      
                    <th nowrap="nowrap" width="150px">Cantidad</th>                      
                    <th nowrap="nowrap">En</th>                                                                                               
                </tr>
            </thead>
            <?php if (isset($data)) { ?>
            <tbody>
                <?php
                    for ($i=0; $i < count($data); $i++) {                                                                    
                ?>
                <tr>                                                                      
                    <td nowrap="nowrap"><?php echo dateFormat($data[$i]['date'],false); ?></td>                     
                    <td nowrap="nowrap" class="text-center"><?php echo (int)$data[$i]['quantity']; ?></td>                                          
                    <td nowrap="nowrap"><?php echo $data[$i]['description']; ?></td> 
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
/* End of file bills_itemData_view.php */
/* Location: ./application/views/bills/bills_itemData_view.php */