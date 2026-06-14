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
                        <form action="<?php echo base_url(); ?>systemMessages/search" method="post" accept-charset="utf-8" id="frmFilter" name="frmFilter" role="form">                                                            
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
                            <table id="grFamilies" class="table table-bordered table-hover table-sm">
                                <thead>
                                    <tr>                                
                                        <th class="text-center" nowrap="nowrap" width="35px">
                                            <?php if ($allowInsert) { ?>
                                            <a href="<?php echo base_url(); ?>systemMessages/edit/0" class="btn without-padding" title="nuevo">
                                                <i class="far fa-plus-square"></i>
                                            </a>                                            
                                            <?php } ?>                                                                    
                                        </th>    
                                        <th nowrap="nowrap" width="150px">Fecha  
                                            <?php echo generateOrderButton(true,($fieldOrder == 'date' && $typeOrder == 'asc'),'systemMessages/listing/1?fOrd=date&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'date' && $typeOrder == 'desc'),'systemMessages/listing/1?fOrd=date&tOrd=desc'.$filterGet); ?>                       
                                        </th>                                                                  
                                        <th nowrap="nowrap">Título  
                                            <?php echo generateOrderButton(true,($fieldOrder == 'tit' && $typeOrder == 'asc'),'systemMessages/listing/1?fOrd=tit&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'tit' && $typeOrder == 'desc'),'systemMessages/listing/1?fOrd=tit&tOrd=desc'.$filterGet); ?>                       
                                        </th>                                   
                                        <th nowrap="nowrap" width="65px">Notif.</th>                                        
                                        <th nowrap="nowrap" width="65px">Email</th> 
                                        <th width="35px"></th>                                       
                                    </tr>
                                </thead>
                                <?php if (isset($systemMessages)) { ?>
                                <tbody>
                                    <?php
                                        for($i=0; $i < count($systemMessages); $i++) {                                            
                                    ?>
                                    <tr ondblclick="onClick('btnEdit<?php echo $systemMessages[$i]['id']; ?>')">                                
                                        <td class="text-center" nowrap="nowrap">                            
                                            <a href="<?php echo base_url(); ?>systemMessages/edit/<?php echo $systemMessages[$i]['id']; ?>" class="btn without-padding" title="ver" id="btnEdit<?php echo $systemMessages[$i]['id']; ?>">
                                                <i class="far fa-edit"></i>
                                            </a>                                                           
                                        </td>  
                                        <td nowrap="nowrap"><?php echo dateFormat($systemMessages[$i]['date'],true); ?></td>                                         
                                        <td nowrap="nowrap"><?php echo $systemMessages[$i]['title']; ?></td>                                         
                                        <td nowrap="nowrap" class="text-center"><?php echo getBooleanToText($systemMessages[$i]['notification']); ?></td>                                         
                                        <td nowrap="nowrap" class="text-center"><?php echo getBooleanToText($systemMessages[$i]['email']); ?></td>                                         
                                        <td class="text-center" nowrap="nowrap">                            
                                            <a href="javascript:openPopupSystemMessage(<?php echo $systemMessages[$i]['id']; ?>);" class="btn without-padding" title="ver popup">
                                                <i class="far fa-window-maximize"></i>
                                            </a>                                                           
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
/* End of file systemMessages_list_view.php */
/* Location: ./application/views/systemMessages/systemMessages_list_view.php */