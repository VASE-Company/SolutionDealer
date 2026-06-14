<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary collapsed-card">
                <div class="card-header">
                    <h3 class="card-title">Buscar por...</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse" title="buscar según filtros aplicados" onclick="generalSearch()"><i class="fas fa-search"></i></button>              
                        <button type="button" class="btn btn-tool" data-card-widget="collapse" title="limpiar filtros" onclick="clearFilter('frmFilter')"><i class="fa fa-undo"></i></button>              
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>                        
                    </div>
                    <!-- /.card-tools -->
                </div>
                <!-- /.card-header -->
                <div class="card-body" style="display: none;">
                    <div class="col-12"> 
                        <form action="<?php echo base_url(); ?>bills/search" method="post" accept-charset="utf-8" id="frmFilter" name="frmFilter" role="form">                                                            
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
                                        <label for="companyIdFilter">Empresa</label>
                                        <select id="companyIdFilter" name="companyIdFilter" class="form-control form-control-sm">
                                            <?php 
                                                $companyIdFilter = set_value('companyIdFilter',$companyIdFilter);
                                                
                                                $selected = ($companyIdFilter == ""?' selected="selected" ':''); 
                                            ?>
                                            <option value="" <?php echo $selected; ?>>[Todas]</option>                                                                                     
                                            <?php                                                         
                                                for ($i=0; $i < count($companies); $i++) {         
                                                    $selected = ($companies[$i]['id'] == $companyIdFilter?' selected="selected" ':'');                                       
                                            ?>
                                            <option value="<?php echo $companies[$i]['id']; ?>" <?php echo $selected; ?>><?php echo $companies[$i]['description'];?></option>
                                            <?php
                                                }
                                            ?>
                                        </select>                      
                                    </div>                                                                                                                                                  
                                    <div class="form-group" style="width:140px; float:left; margin-right:10px;">
                                        <label for="numberFilter">Nº Fact./Ref. Int.</label>
                                        <input type="text" class="form-control form-control-sm noEnterMyApp" id="numberFilter" name="numberFilter" placeholder="Número" title="Nº Pedido/Orden/Remito" autocomplete="off" value="<?php echo set_value('numberFilter',$numberFilter); ?>">
                                    </div>  
                                    <div class="form-group" style="width:140px; float:left; margin-right:10px;">
                                        <label for="supplierCodeFilter">CUIT</label>
                                        <input type="text" class="form-control form-control-sm noEnterMyApp" id="supplierCodeFilter" name="supplierCodeFilter" placeholder="CUIT" title="CUIT del Proveedor" autocomplete="off" value="<?php echo set_value('supplierCodeFilter',$supplierCodeFilter); ?>">
                                    </div>                                  
                                    <div class="form-group" style="width:140px; float:left; margin-right:10px;">
                                        <label for="articleFilter">Artículo</label>
                                        <input type="text" class="form-control form-control-sm noEnterMyApp" id="articleFilter" name="articleFilter" placeholder="Artículo" autocomplete="off" value="<?php echo set_value('articleFilter',$articleFilter); ?>">
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
                            <table id="grBills" class="table table-bbilled table-hover table-sm">
                                <thead>
                                    <tr>                                
                                        <th class="text-center" nowrap="nowrap" width="<?php echo ($allowDelete?"55px":"35px"); ?>">
                                            <?php if ($allowInsert) { ?>
                                            <a href="<?php echo base_url(); ?>bills/edit/0<?php echo $backGet; ?>" class="btn without-padding" title="nuevo">
                                                <i class="far fa-plus-square"></i>
                                            </a>                                            
                                            <?php } ?>                                                                    
                                        </th>
                                        <th nowrap="nowrap" style="min-width:85px;">Fecha Carga
                                            <?php echo generateOrderButton(true,($fieldOrder == 'udate' && $typeOrder == 'asc'),'bills/listing/1?fOrd=udate&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'udate' && $typeOrder == 'desc'),'bills/listing/1?fOrd=udate&tOrd=desc'.$filterGet); ?>                       
                                        </th> 
                                        <th nowrap="nowrap" style="min-width:85px;">Fecha Factura
                                            <?php echo generateOrderButton(true,($fieldOrder == 'bdate' && $typeOrder == 'asc'),'bills/listing/1?fOrd=bdate&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'bdate' && $typeOrder == 'desc'),'bills/listing/1?fOrd=bdate&tOrd=desc'.$filterGet); ?>                       
                                        </th>                                         
                                        <th nowrap="nowrap" style="min-width:75px;">Nº Factura  
                                            <?php echo generateOrderButton(true,($fieldOrder == 'num' && $typeOrder == 'asc'),'bills/listing/1?fOrd=num&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'num' && $typeOrder == 'desc'),'bills/listing/1?fOrd=num&tOrd=desc'.$filterGet); ?>                       
                                        </th> 
                                        <th nowrap="nowrap">CUIT</th>          
                                        <th nowrap="nowrap">Nombre Comercial    
                                            <?php echo generateOrderButton(true,($fieldOrder == 'sup' && $typeOrder == 'asc'),'bills/listing/1?fOrd=sup&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'sup' && $typeOrder == 'desc'),'bills/listing/1?fOrd=sup&tOrd=desc'.$filterGet); ?>                       
                                        </th>  
                                        <th nowrap="nowrap">Total s/IVA</th>       
                                        <th nowrap="nowrap">Empresa  
                                            <?php echo generateOrderButton(true,($fieldOrder == 'com' && $typeOrder == 'asc'),'bills/listing/1?fOrd=com&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'com' && $typeOrder == 'desc'),'bills/listing/1?fOrd=com&tOrd=desc'.$filterGet); ?>                       
                                        </th>                                                                                                                                                                                                                                                                                                                                                                       
                                        <th nowrap="nowrap" style="min-width:75px;">Cargado Por</th>                                        
                                        <th nowrap="nowrap" style="min-width:75px;">Ref. Int.</th>    
                                    </tr>
                                </thead>
                                <?php if (isset($bills)) { ?>
                                <tbody>
                                    <?php
                                        for($i=0; $i < count($bills); $i++) {                                            
                                    ?>
                                    <tr ondblclick="onClick('btnEdit<?php echo $bills[$i]['id']; ?>')" <?php echo ((int)$bills[$i]['deleted'] == 1?'class="deletedRegister"':''); ?>>                               
                                        <td class="text-center" nowrap="nowrap">                            
                                            <a href="<?php echo base_url(); ?>bills/edit/<?php echo $bills[$i]['id']; ?><?php echo $backGet; ?>" class="btn without-padding" title="<?php echo ($allowEdit?"editar":"ver"); ?>" id="btnEdit<?php echo $bills[$i]['id']; ?>">
                                                <i class="far fa-edit"></i>
                                            </a>
                                            <?php if ($allowDelete && (int)$bills[$i]['deleted'] == 0) { ?>
                                            <button type="button" class="btn without-padding" onclick="generalDelete(<?php echo $bills[$i]['id']; ?>,'bills/delete')" title="eliminar">
                                                <i class="far fa-minus-square"></i>
                                            </button>                                            
                                            <?php } ?>                       
                                        </td>                        
                                        <td><?php echo dateFormat($bills[$i]['date'],false); ?></td>                                                               
                                        <td><?php echo dateFormat($bills[$i]['billDate'],false); ?></td>                                                
                                        <td nowrap="nowrap"><?php echo $bills[$i]['fullNumber']; ?></td>
                                        <td nowrap="nowrap"><?php echo $bills[$i]['supplierCode']; ?></td>                        
                                        <td nowrap="nowrap"><?php echo $bills[$i]['supplierDescription']; ?></td>                                                                                                                                             
                                        <td class="text-right" nowrap="nowrap">$ <?php echo decimalFormat($bills[$i]['total'],2); ?></td>                                                                                 
                                        <td nowrap="nowrap"><?php echo $bills[$i]['companyDescription']; ?></td>  
                                        <td nowrap="nowrap"><?php echo $bills[$i]['userDescription']; ?></td>                                                                                                                        
                                        <td nowrap="nowrap"><?php echo $bills[$i]['id']; ?></td>   
                                    </tr>    
                                    <?php 
                                        } 
                                    ?>                      
                                </tbody>
                                <?php } ?>
                            </table>
                        </div>
                    </div>
                    <?php if (isset($bills) && count($bills) > 0 && ($allowExport || $allowFullExport || $pagination != "")) { ?>
                    <div class="row">
                        <div class="col-sm-12">
                            <?php
                                if ($allowExport || $allowFullExport) {
                            ?>            
                            <a href="javascript:generalExport('bills/export','<?php echo $filterGet.$orderGet; ?>');" class="pagination pagination-sm paginate_button page-item page-link" title="exportar" style="float:left; margin-right:5px;padding: 4px 8px 5px 8px;">                
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
/* End of file bills_list_view.php */
/* Location: ./application/views/bills/bills_list_view.php */