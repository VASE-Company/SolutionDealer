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
                        <form action="<?php echo base_url(); ?>suppliers/search" method="post" accept-charset="utf-8" id="frmFilter" name="frmFilter" role="form">                                                            
                            <div class="row">
                                <div class="col-12">                                                                        
                                    <div class="form-group" style="width:300px; float:left; margin-right:10px;">
                                        <label for="textFilter">Buscar</label>                                
                                        <input class="form-control form-control-sm" type="text" maxlength="50" placeholder="Buscar" id="textFilter" name="textFilter" autocomplete="off" value="<?php echo set_value('textFilter',$textFilter); ?>">
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
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <table id="grSuppliers" class="table table-bordered table-hover table-sm">
                                <thead>
                                    <tr>                                                                                                                                                
                                        <th class="text-center" nowrap="nowrap" width="<?php echo ($allowDelete?"55px":"35px"); ?>">
                                            <?php if ($allowInsert) { ?>
                                            <a href="<?php echo base_url(); ?>suppliers/edit/0" class="btn without-padding" title="nuevo">
                                                <i class="far fa-plus-square"></i>
                                            </a>
                                            <?php } ?>                                                                                                                                       
                                        </th>                                                                    
                                        <th nowrap="nowrap">CUIT  
                                            <?php echo generateOrderButton(true,($fieldOrder == 'cod' && $typeOrder == 'asc'),'suppliers/listing/1?fOrd=cod&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'cod' && $typeOrder == 'desc'),'suppliers/listing/1?fOrd=cod&tOrd=desc'.$filterGet); ?>                       
                                        </th>
                                        <th nowrap="nowrap">Nombre Comercial  
                                            <?php echo generateOrderButton(true,($fieldOrder == 'tn' && $typeOrder == 'asc'),'suppliers/listing/1?fOrd=tn&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'tn' && $typeOrder == 'desc'),'suppliers/listing/1?fOrd=tn&tOrd=desc'.$filterGet); ?>                       
                                        </th>
                                        <th nowrap="nowrap">Razón Social  
                                            <?php echo generateOrderButton(true,($fieldOrder == 'bn' && $typeOrder == 'asc'),'suppliers/listing/1?fOrd=bn&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'bn' && $typeOrder == 'desc'),'suppliers/listing/1?fOrd=bn&tOrd=desc'.$filterGet); ?>                       
                                        </th>                                                                        
                                        <th nowrap="nowrap">Activo</th>                                                                                
                                    </tr>
                                </thead>
                                <?php if (isset($suppliers)) { ?>
                                <tbody>
                                    <?php
                                        for($i=0; $i < count($suppliers); $i++) {                                            
                                    ?>
                                    <tr ondblclick="onClick('btnEdit<?php echo $suppliers[$i]['id']; ?>')">                                
                                        <td class="text-center" nowrap="nowrap">                            
                                            <a href="<?php echo base_url(); ?>suppliers/edit/<?php echo $suppliers[$i]['id']; ?>" class="btn without-padding" title="<?php echo ($allowEdit?"editar":"ver"); ?>" id="btnEdit<?php echo $suppliers[$i]['id']; ?>">
                                                <i class="far fa-edit"></i>
                                            </a>
                                            <?php if ($allowDelete) { ?>
                                            <button type="button" class="btn without-padding" onclick="generalDelete(<?php echo $suppliers[$i]['id']; ?>,'suppliers/delete')" title="eliminar">
                                                <i class="far fa-minus-square"></i>
                                            </button>                                            
                                            <?php } ?>                                    
                                        </td>  
                                        <td nowrap="nowrap"><?php echo $suppliers[$i]['code']; ?></td> 
                                        <td nowrap="nowrap"><?php echo $suppliers[$i]['tradeName']; ?></td>                                                                                                                   
                                        <td nowrap="nowrap"><?php echo $suppliers[$i]['businessName']; ?></td>                                          
                                        <td nowrap="nowrap" class="text-center"><?php echo getBooleanToText($suppliers[$i]['active']); ?></td>                                                                                 
                                    </tr>  
                                    <?php 
                                        } 
                                    ?>                         
                                </tbody>
                                <?php } ?>
                            </table>
                        </div>
                    </div>         
                    <?php if (isset($suppliers) && count($suppliers) > 0 && ($allowExport || $pagination != "")) { ?>
                    <div class="row">
                        <div class="col-sm-12">
                            <?php
                                if ($allowExport) {
                            ?>            
                            <a href="javascript:generalExport('suppliers/export','<?php echo $filterGet.$orderGet; ?>');" class="pagination pagination-sm paginate_button page-item page-link" title="exportar" style="float:left; margin-right:5px;padding: 4px 8px 5px 8px;">                
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
/* End of file suppliers_list_view.php */
/* Location: ./application/views/suppliers/suppliers_list_view.php */