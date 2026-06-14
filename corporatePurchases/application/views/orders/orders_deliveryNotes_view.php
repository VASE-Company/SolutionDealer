<div class="row without-padding">   
    <div class="col-sm-12" style="margin-bottom: 0px;margin-top: 0px;margin-left: 9px;"> 
        
    </div>  
    <div class="col-sm-12" style="max-height:250px;overflow-y:auto;">
        <table id="grHistory" class="table table-bordered table-sm">
            <thead>
                <tr>                     
                    <th width="35px"></th>                    
                    <th nowrap="nowrap">Fecha</th>  
                    <th nowrap="nowrap">Número</th>                                           
                    <th nowrap="nowrap">Usuario</th>                                                                                               
                </tr>
            </thead>
            <?php if (isset($deliveryNotes)) { ?>
            <tbody>
                <?php
                    for ($i=0; $i < count($deliveryNotes); $i++) {    
                        if (trim($deliveryNotes[$i]['receivedDate']) == "" || trim($deliveryNotes[$i]['receivedBy']) == "") {
                            $classTr = ' class="dnIncomplete" ';
                        } else {
                            $classTr = ' class="dnComplete" ';
                        }                                                  
                ?>
                <tr <?php echo  $classTr; ?>>                                                    
                    <th class="text-center" nowrap="nowrap">   
                        <button type="button" class="btn without-padding" onclick="seeDeliveryNoteOrderEdit(<?php echo $deliveryNotes[$i]['orderId']; ?>,<?php echo $deliveryNotes[$i]['id']; ?>,'<?php echo $deliveryNotes[$i]['number']; ?>')" title="ver detalle del remito">
                            <i class="far fa-edit"></i>
                        </button>
                        <?php if ($allowPrint) { ?>                                                    
                        <button type="button" class="btn without-padding" id="btnPrintDN<?php echo $deliveryNotes[$i]['id']; ?>" onclick="printOrder(<?php echo $deliveryNotes[$i]['id']; ?>,'DN')" title="<?php echo ($deliveryNotes[$i]['printed'] == 1?"el remito ya fue impreso":"imprimir"); ?>" <?php echo ($deliveryNotes[$i]['printed'] == 1?' disabled="disabled" ':''); ?>>
                            <i class="fa fa-print"></i>
                        </button> 
                        <?php } ?>
                    </th>                    
                    <td nowrap="nowrap"><?php echo dateFormat($deliveryNotes[$i]['date'],false); ?></td> 
                    <td nowrap="nowrap"><?php echo $deliveryNotes[$i]['number']; ?></td>  
                    <td nowrap="nowrap"><?php echo $deliveryNotes[$i]['userDescription']; ?></td>                                                                                                                                                                    
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
/* End of file orders_deliveryNotes_view.php */
/* Location: ./application/views/orders/orders_deliveryNotes_view.php */