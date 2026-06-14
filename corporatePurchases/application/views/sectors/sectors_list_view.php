<div class="container-fluid">
    <div class="row">
        <div class="col-xs-12 col-sm-10 col-md-9 col-lg-7">
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
                        <form action="<?php echo base_url(); ?>sectors/search" method="post" accept-charset="utf-8" id="frmFilter" name="frmFilter" role="form">                                                            
                            <div class="row">
                                <div class="col-12">                                                                        
                                    <div class="form-group" style="width:200px; float:left; margin-right:10px;">
                                        <label for="textFilter">Buscar</label>                                
                                        <input class="form-control form-control-sm" type="text" maxlength="15" placeholder="Buscar" id="textFilter" name="textFilter" autocomplete="off" value="<?php echo set_value('textFilter',$textFilter); ?>">
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
                            <table id="grSectors" class="table table-bordered table-hover table-sm">
                                <thead>
                                    <tr>                                
                                        <th class="text-center" nowrap="nowrap" width="<?php echo ($allowDelete?"55px":"35px"); ?>">
                                            <?php if ($allowInsert) { ?>
                                            <a href="<?php echo base_url(); ?>sectors/edit/0" class="btn without-padding" title="nuevo">
                                                <i class="far fa-plus-square"></i>
                                            </a>                                            
                                            <?php } ?>                                                                    
                                        </th>                                                                    
                                        <th nowrap="nowrap">Descripción  
                                            <?php echo generateOrderButton(true,($fieldOrder == 'des' && $typeOrder == 'asc'),'sectors/listing/1?fOrd=des&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'des' && $typeOrder == 'desc'),'sectors/listing/1?fOrd=des&tOrd=desc'.$filterGet); ?>                       
                                        </th>                                   
                                        <th nowrap="nowrap">Pedidos</th>     
                                        <th nowrap="nowrap">Pagos</th>     
                                        <th nowrap="nowrap">Activo</th>                                        
                                    </tr>
                                </thead>
                                <?php if (isset($sectors)) { ?>
                                <tbody>
                                    <?php
                                        for($i=0; $i < count($sectors); $i++) {                                            
                                    ?>
                                    <tr ondblclick="onClick('btnEdit<?php echo $sectors[$i]['id']; ?>')">                                
                                        <td class="text-center" nowrap="nowrap">                            
                                            <a href="<?php echo base_url(); ?>sectors/edit/<?php echo $sectors[$i]['id']; ?>" class="btn without-padding" title="<?php echo ($allowEdit?"editar":"ver"); ?>" id="btnEdit<?php echo $sectors[$i]['id']; ?>">
                                                <i class="far fa-edit"></i>
                                            </a>
                                            <?php if ($allowDelete) { ?>
                                            <button type="button" class="btn without-padding" onclick="generalDelete(<?php echo $sectors[$i]['id']; ?>,'sectors/delete')" title="eliminar">
                                                <i class="far fa-minus-square"></i>
                                            </button>                                            
                                            <?php } ?>                                    
                                        </td>  
                                        <td nowrap="nowrap"><?php echo $sectors[$i]['description']; ?></td>                                         
                                        <td nowrap="nowrap" class="text-center"><?php echo getBooleanToText($sectors[$i]['allowOrders']); ?></td>                                         
                                        <td nowrap="nowrap" class="text-center"><?php echo getBooleanToText($sectors[$i]['allowPayments']); ?></td>                                         
                                        <td nowrap="nowrap" class="text-center"><?php echo getBooleanToText($sectors[$i]['active']); ?></td>                                         
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
/* End of file sectors_list_view.php */
/* Location: ./application/views/sectors/sectors_list_view.php */