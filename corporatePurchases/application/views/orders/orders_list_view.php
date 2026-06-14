<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary collapsed-card">
                <div class="card-header">
                    <h3 class="card-title">Buscar por...</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse" title="buscar según filtros aplicados" onclick="searchOrders()"><i class="fas fa-search"></i></button>              
                        <button type="button" class="btn btn-tool" data-card-widget="collapse" title="limpiar filtros" onclick="clearFilterOrders()"><i class="fa fa-undo"></i></button>              
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>                        
                    </div>
                    <!-- /.card-tools -->
                </div>
                <!-- /.card-header -->
                <div class="card-body" style="display: none;">
                    <div class="col-12"> 
                        <form action="<?php echo base_url(); ?>orders/search" method="post" accept-charset="utf-8" id="frmFilter" name="frmFilter" role="form">                                                            
                            <div class="row">
                                <div class="col-12">                                                                                                                 
                                    <div class="form-group" style="float:left;margin-right:10px;">
                                        <label for="dateFromFilter">Desde</label>
                                        <input type="date" id="dateFromFilter" name="dateFromFilter" class="form-control form-control-sm noEnterMyApp" autocomplete="off"  autofocus="1" value="<?php echo $dateFromFilter; ?>" original="<?php echo $dateFromFilter; ?>" style="width:140px;">
                                    </div>   
                                    <div class="form-group" style="float:left;margin-right:10px;">
                                        <label for="dateToFilter">Hasta</label>
                                        <input type="date" id="dateToFilter" name="dateToFilter" class="form-control form-control-sm noEnterMyApp" autocomplete="off" value="<?php echo $dateToFilter; ?>" original="<?php echo $dateFromFilter; ?>" style="width:140px;">                
                                    </div>                                                                        
                                    <div class="form-group" style="width:auto; float:left; margin-right:10px;">
                                        <label for="stateIdFilter">Estado</label>
                                        <select id="stateIdFilter" name="stateIdFilter" class="form-control form-control-sm">
                                            <?php 
                                                $stateIdFilter = set_value('stateIdFilter',$stateIdFilter);
                                                
                                                $selected = ($stateIdFilter == ""?' selected="selected" ':''); 
                                            ?>
                                            <option value="" <?php echo $selected; ?>>[Todos]</option> 
                                            <?php 
                                                $selected = ($stateIdFilter == "MAN"?' selected="selected" ':''); 
                                            ?>
                                            <option value="MAN" <?php echo $selected; ?>>[Gestionándose]</option> 
                                            <?php 
                                                $selected = ($stateIdFilter == "NOTFIN"?' selected="selected" ':''); 
                                            ?>
                                            <option value="NOTFIN" <?php echo $selected; ?>>[Pend. Finalizar]</option> 
                                            <?php                                                         
                                                for ($i=0; $i < count($states); $i++) {         
                                                    $selected = ($states[$i]['id'] == $stateIdFilter?' selected="selected" ':'');                                       
                                            ?>
                                            <option value="<?php echo $states[$i]['id']; ?>" <?php echo $selected; ?>><?php echo $states[$i]['description'];?></option>
                                            <?php
                                                }
                                            ?>
                                        </select>                      
                                    </div>                                                                          
                                    <div class="form-group" style="width:auto; float:left; margin-right:10px;">
                                        <label for="priorityIdFilter">Plazo</label>
                                        <select id="priorityIdFilter" name="priorityIdFilter" class="form-control form-control-sm">
                                            <?php 
                                                $priorityIdFilter = set_value('priorityIdFilter',$priorityIdFilter);
                                                
                                                $selected = ($priorityIdFilter == ""?' selected="selected" ':''); 
                                            ?>
                                            <option value="" <?php echo $selected; ?>>[Todos]</option> 
                                            <?php                                                         
                                                for ($i=0; $i < count($priorities); $i++) {         
                                                    $selected = ($priorities[$i]['id'] == $priorityIdFilter?' selected="selected" ':'');                                       
                                            ?>
                                            <option value="<?php echo $priorities[$i]['id']; ?>" <?php echo $selected; ?>><?php echo $priorities[$i]['description'];?></option>
                                            <?php
                                                }
                                            ?>
                                        </select>                      
                                    </div>                                         
                                    <div class="form-group" style="width:140px; float:left; margin-right:10px;">
                                        <label for="numberFilter">Número</label>
                                        <input type="text" class="form-control form-control-sm noEnterMyApp" id="numberFilter" name="numberFilter" placeholder="Número" title="Nº Pedido/Orden/Remito" autocomplete="off" value="<?php echo set_value('numberFilter',$numberFilter); ?>">
                                    </div>                                  
                                    <div class="form-group" style="width:140px; float:left; margin-right:10px;">
                                        <label for="articleFilter">Artículo</label>
                                        <input type="text" class="form-control form-control-sm noEnterMyApp" id="articleFilter" name="articleFilter" placeholder="Artículo" autocomplete="off" value="<?php echo set_value('articleFilter',$articleFilter); ?>">
                                    </div>  
                                    <div class="form-group" style="width:140px; float:left; margin-right:10px;">
                                        <label for="subjectFilter">Asunto</label>
                                        <input type="text" class="form-control form-control-sm noEnterMyApp" id="subjectFilter" name="subjectFilter" placeholder="Asunto" autocomplete="off" value="<?php echo set_value('subjectFilter',$subjectFilter); ?>">
                                    </div>                                      
                                </div>                                                        
                            </div>  
                            <div class="row">
                                <div class="col-12">                                                                                                                 
                                    <?php if ($allowCompanyFilter) { ?>
                                    <div class="form-group" style="float:left;margin-right:10px;width:140px;">
                                        <label for="companyIdsFilter">Empresas</label>                                        
                                        <select class="noEnterMyApp" multiple="multiple" id="companyIdsFilter" name="companyIdsFilter" required="1">
                                            <?php                                                                                                               
                                                if (isset($companies)) { 
                                                    if ($companyIdsFilter == 'all') {
                                                        $arrSelected = array('all');
                                                    } else {
                                                        $arrSelected = explode('|',$companyIdsFilter);
                                                    }
                                                    for($i=0; $i < count($companies); $i++) {
                                                        if (in_array('all',$arrSelected) || in_array($companies[$i]['id'],$arrSelected)) {
                                                            $selected = ' selected="selected" ';
                                                        } else {
                                                            $selected = '';
                                                        }
                                            ?>
                                            <option value="<?php echo $companies[$i]['id']; ?>" <?php echo $selected; ?>><?php echo $companies[$i]['description']; ?></option>                                
                                            <?php 
                                                    }
                                                }
                                            ?>                                
                                        </select>
                                        <input type="hidden" id="companyIdsFilterSelected" name="companyIdsFilterSelected" value="" />                   
                                    </div>  
                                    <?php } ?>
                                    <?php if ($allowManagerFilter) { ?>
                                    <div class="form-group" style="float:left;margin-right:10px;width:200px;">
                                        <label for="managerIdsFilter">Gestionado por</label>                                        
                                        <select class="noEnterMyApp" multiple="multiple" id="managerIdsFilter" name="managerIdsFilter" required="1">
                                            <?php                                                                                                               
                                                if (isset($managers)) { 
                                                    if ($managerIdsFilter == 'all') {
                                                        $arrSelected = array('all');
                                                    } else {
                                                        $arrSelected = explode('|',$managerIdsFilter);
                                                    }
                                                    for($i=0; $i < count($managers); $i++) {
                                                        if (in_array('all',$arrSelected) || in_array($managers[$i]['id'],$arrSelected)) {
                                                            $selected = ' selected="selected" ';
                                                        } else {
                                                            $selected = '';
                                                        }
                                            ?>
                                            <option value="<?php echo $managers[$i]['id']; ?>" <?php echo $selected; ?>><?php echo trim($managers[$i]['lastName'].", ".$managers[$i]['firstName']);?></option>                                
                                            <?php 
                                                    }
                                                }
                                            ?>                                
                                        </select>
                                        <input type="hidden" id="managerIdsFilterSelected" name="managerIdsFilterSelected" value="" />                   
                                    </div>  
                                    <?php } ?>
                                    <div class="form-group" style="float:left;margin-right:10px;width:200px;">
                                        <label for="familyIdsFilter">Rubros</label>                                        
                                        <select class="noEnterMyApp" multiple="multiple" id="familyIdsFilter" name="familyIdsFilter" required="1">
                                            <?php                                                                                                               
                                                if (isset($families)) { 
                                                    if ($familyIdsFilter == 'all') {
                                                        $arrSelected = array('all');
                                                    } else {
                                                        $arrSelected = explode('|',$familyIdsFilter);
                                                    }
                                                    for($i=0; $i < count($families); $i++) {
                                                        if (in_array('all',$arrSelected) || in_array($families[$i]['id'],$arrSelected)) {
                                                            $selected = ' selected="selected" ';
                                                        } else {
                                                            $selected = '';
                                                        }
                                            ?>
                                            <option value="<?php echo $families[$i]['id']; ?>" <?php echo $selected; ?>><?php echo $families[$i]['description'];?></option>                                
                                            <?php 
                                                    }
                                                }
                                            ?>                                
                                        </select>
                                        <input type="hidden" id="familyIdsFilterSelected" name="familyIdsFilterSelected" value="" />                   
                                    </div>  
                                </div>                                      
                            </div> 
                            <input type="hidden" id="fieldOrder" name="fieldOrder" value="<?php echo $fieldOrder; ?>" />
                            <input type="hidden" id="typeOrder" name="typeOrder" value="<?php echo $typeOrder; ?>" />                                
                        </form>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <div class="card">                       
                <div class="card-body" style="overflow-x:auto;">
                    <div class="row">
                        <div class="col-sm-12">
                            <table id="grOrders" class="table table-bordered table-hover table-sm">
                                <thead>
                                    <tr>                                
                                        <th class="text-center" nowrap="nowrap" width="<?php echo ($allowDelete?"55px":"35px"); ?>">
                                            <?php if ($allowInsert) { ?>
                                            <a href="<?php echo base_url(); ?>orders/edit/0<?php echo $backGet; ?>" class="btn without-padding" title="nuevo">
                                                <i class="far fa-plus-square"></i>
                                            </a>                                            
                                            <?php } ?>                                                                    
                                        </th>                                          
                                        <th nowrap="nowrap" style="min-width:75px;">Nº Pedido  
                                            <?php echo generateOrderButton(true,($fieldOrder == 'id' && $typeOrder == 'asc'),'orders/listing/1?fOrd=id&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'id' && $typeOrder == 'desc'),'orders/listing/1?fOrd=id&tOrd=desc'.$filterGet); ?>                       
                                        </th>                                        
                                        <th nowrap="nowrap" style="min-width:85px;">Fecha  
                                            <?php echo generateOrderButton(true,($fieldOrder == 'date' && $typeOrder == 'asc'),'orders/listing/1?fOrd=date&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'date' && $typeOrder == 'desc'),'orders/listing/1?fOrd=date&tOrd=desc'.$filterGet); ?>                       
                                        </th>
                                        <th nowrap="nowrap">Empresa  
                                            <?php echo generateOrderButton(true,($fieldOrder == 'com' && $typeOrder == 'asc'),'orders/listing/1?fOrd=com&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'com' && $typeOrder == 'desc'),'orders/listing/1?fOrd=com&tOrd=desc'.$filterGet); ?>                       
                                        </th>                                                       
                                        <th>Sucursal
                                            <?php echo generateOrderButton(true,($fieldOrder == 'bo' && $typeOrder == 'asc'),'orders/listing/1?fOrd=bo&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'bo' && $typeOrder == 'desc'),'orders/listing/1?fOrd=bo&tOrd=desc'.$filterGet); ?>                       
                                        </th>                      
                                        <th>Sector
                                            <?php echo generateOrderButton(true,($fieldOrder == 'sec' && $typeOrder == 'asc'),'orders/listing/1?fOrd=sec&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'sec' && $typeOrder == 'desc'),'orders/listing/1?fOrd=sec&tOrd=desc'.$filterGet); ?>                       
                                        </th>
                                        <th>Plazo</th>                       
                                        <?php if ($showColumnTotal) { ?>                                                                                     
                                        <th nowrap="nowrap">Total s/IVA</th>     
                                        <?php } ?>    
                                        <th nowrap="nowrap" style="min-width:120px;">Estado 
                                            <?php echo generateOrderButton(true,($fieldOrder == 'sta' && $typeOrder == 'asc'),'orders/listing/1?fOrd=sta&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'sta' && $typeOrder == 'desc'),'orders/listing/1?fOrd=sta&tOrd=desc'.$filterGet); ?>                       
                                        </th>                                                                                                        
                                        <th nowrap="nowrap" style="min-width:75px;">Nº Orden</th>
                                        <th nowrap="nowrap" style="min-width:75px;">Remitos</th>
                                    </tr>
                                </thead>
                                <?php if (isset($orders)) { ?>
                                <tbody>
                                    <?php
                                        for($i=0; $i < count($orders); $i++) {                                            
                                    ?>
                                    <tr ondblclick="onClick('btnEdit<?php echo $orders[$i]['id']; ?>')">                               
                                        <td class="text-center" nowrap="nowrap">                            
                                            <a href="<?php echo base_url(); ?>orders/edit/<?php echo $orders[$i]['id']; ?><?php echo $backGet; ?>" class="btn without-padding" title="<?php echo ($allowEdit?"editar":"ver"); ?>" id="btnEdit<?php echo $orders[$i]['id']; ?>">
                                                <i class="far fa-edit"></i>
                                            </a>
                                            <?php if ($allowDelete) { ?>
                                            <button type="button" class="btn without-padding" onclick="generalDelete(<?php echo $orders[$i]['id']; ?>,'orders/delete')" title="eliminar">
                                                <i class="far fa-minus-square"></i>
                                            </button>                                            
                                            <?php } ?>                       
                                        </td>                                          
                                        <td class="text-center"><?php echo $orders[$i]['id']; ?></td>
                                        <td><?php echo dateFormat($orders[$i]['date'],false); ?></td>                                                
                                        <td nowrap="nowrap"><?php echo $orders[$i]['companyDescription']; ?></td>
                                        <td nowrap="nowrap"><?php echo $orders[$i]['branchOfficeDescription']; ?></td>                        
                                        <td nowrap="nowrap"><?php echo $orders[$i]['sectorDescription']; ?></td>                                                                
                                        <td nowrap="nowrap" <?php echo (trim($orders[$i]['priorityColor']) == ""?"":' style="color:'.trim($orders[$i]['priorityColor']).';" ');?>><?php echo $orders[$i]['priorityDescription']; ?></td>                        
                                        <?php if ($showColumnTotal) { ?>
                                        <td class="text-right" nowrap="nowrap">$ <?php echo decimalFormat($orders[$i]['total'],2); ?></td>                                         
                                        <?php } ?>
                                        <td nowrap="nowrap"><?php echo $orders[$i]['stateDescription']; ?></td>                                                
                                        <td class="text-center"><?php echo trim($orders[$i]['purchaseOrderNumber']); ?></td>                                                                        
                                        <td class="text-center">
                                            <?php if (isset($orders[$i]['deliveryNotes']) && count($orders[$i]['deliveryNotes']) > 0) { ?>                   
                                            <button type="button" class="btn without-padding" onclick="seeDeliveryNotesOrderEdit(<?php echo $orders[$i]['id']; ?>)" title="ver remitos"><i class="fas fa-file"></i></button>
                                            <?php } ?>
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
                    <?php if (isset($orders) && count($orders) > 0 && ($allowExport || $allowFullExport || $pagination != "")) { ?>
                    <div class="row">
                        <div class="col-sm-12">
                            <?php
                                if ($allowExport || $allowFullExport) {
                            ?>            
                            <a href="javascript:generalExport('orders/<?php echo ($allowFullExport?"fullExport":"export"); ?>','<?php echo $filterGet.$orderGet; ?>');" class="pagination pagination-sm paginate_button page-item page-link" title="exportar" style="float:left; margin-right:5px;padding: 4px 8px 5px 8px;">                
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
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->    
</div>
<!-- /.container-fluid -->
<?php
/* End of file orders_list_view.php */
/* Location: ./application/views/orders/orders_list_view.php */