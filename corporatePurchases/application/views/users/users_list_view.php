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
                        <form action="<?php echo base_url(); ?>users/search" method="post" accept-charset="utf-8" id="frmFilter" name="frmFilter" role="form">                                                            
                            <div class="row">
                                <div class="col-12">                                                                        
                                    <div class="form-group" style="width:200px; float:left; margin-right:10px;">
                                        <label for="textFilter">Buscar</label>                                
                                        <input class="form-control form-control-sm" type="text" maxlength="15" placeholder="Buscar" id="textFilter" name="textFilter" autocomplete="off" value="<?php echo set_value('textFilter',$textFilter); ?>">
                                    </div> 
                                    <div class="form-group" style="width:auto; float:left; margin-right:10px;">
                                        <label for="rolesIdFilter">Rol</label>                                        
                                        <select id="rolesIdFilter" name="rolesIdFilter" class="form-control form-control-sm">
                                            <?php 
                                                $rolesIdFilter = set_value('rolesIdFilter',$rolesIdFilter);
                                                
                                                $selected = ($rolesIdFilter == ""?' selected="selected" ':''); 
                                            ?>
                                            <option value="" <?php echo $selected; ?>>[Todos]</option>                       
                                            <?php                                                         
                                                for ($i=0; $i < count($roles); $i++) {   
                                                    $selected = ($roles[$i]['id'] == $rolesIdFilter?' selected="selected" ':'');                                              
                                            ?>
                                            <option value="<?php echo $roles[$i]['id']; ?>" <?php echo $selected; ?>><?php echo $roles[$i]['description'];?></option>
                                            <?php
                                                }
                                            ?>
                                        </select>                     
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
                            <table id="grUsers" class="table table-bordered table-hover table-sm">
                                <thead>
                                    <tr>                                
                                        <th class="text-center" nowrap="nowrap" width="<?php echo ($allowDelete?"55px":"35px"); ?>">
                                            <?php if ($allowInsert) { ?>
                                            <a href="<?php echo base_url(); ?>users/edit/0" class="btn without-padding" title="nuevo">
                                                <i class="far fa-plus-square"></i>
                                            </a>                                            
                                            <?php } ?>                                                                    
                                        </th>                                                                    
                                        <th nowrap="nowrap">Apellido  
                                            <?php echo generateOrderButton(true,($fieldOrder == 'ln' && $typeOrder == 'asc'),'users/listing/1?fOrd=ln&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'ln' && $typeOrder == 'desc'),'users/listing/1?fOrd=ln&tOrd=desc'.$filterGet); ?>                       
                                        </th>
                                        <th nowrap="nowrap">Nombres  
                                            <?php echo generateOrderButton(true,($fieldOrder == 'fn' && $typeOrder == 'asc'),'users/listing/1?fOrd=fn&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'fn' && $typeOrder == 'desc'),'users/listing/1?fOrd=fn&tOrd=desc'.$filterGet); ?>                       
                                        </th>
                                        <th nowrap="nowrap">Usuario</th> 
                                        <th nowrap="nowrap">Empresa
                                            <?php echo generateOrderButton(true,($fieldOrder == 'com' && $typeOrder == 'asc'),'users/listing/1?fOrd=com&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'com' && $typeOrder == 'desc'),'users/listing/1?fOrd=com&tOrd=desc'.$filterGet); ?>                       
                                        </th>           
                                        <th nowrap="nowrap">Sucursal
                                            <?php echo generateOrderButton(true,($fieldOrder == 'bo' && $typeOrder == 'asc'),'users/listing/1?fOrd=bo&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'bo' && $typeOrder == 'desc'),'users/listing/1?fOrd=bo&tOrd=desc'.$filterGet); ?>                       
                                        </th> 
                                        <th nowrap="nowrap">Sector</th>                                         
                                        <th nowrap="nowrap">Activo</th>                                        
                                    </tr>
                                </thead>
                                <?php if (isset($users)) { ?>
                                <tbody>
                                    <?php
                                        for($i=0; $i < count($users); $i++) {                                            
                                    ?>
                                    <tr ondblclick="onClick('btnEdit<?php echo $users[$i]['id']; ?>')">                                
                                        <td class="text-center" nowrap="nowrap">                            
                                            <a href="<?php echo base_url(); ?>users/edit/<?php echo $users[$i]['id']; ?>" class="btn without-padding" title="<?php echo ($allowEdit?"editar":"ver"); ?>" id="btnEdit<?php echo $users[$i]['id']; ?>">
                                                <i class="far fa-edit"></i>
                                            </a>
                                            <?php if ($allowDelete) { ?>
                                            <button type="button" class="btn without-padding" onclick="generalDelete(<?php echo $users[$i]['id']; ?>,'users/delete')" title="eliminar">
                                                <i class="far fa-minus-square"></i>
                                            </button>                                            
                                            <?php } ?>                                    
                                        </td>  
                                        <td nowrap="nowrap"><?php echo $users[$i]['lastName']; ?></td> 
                                        <td nowrap="nowrap"><?php echo $users[$i]['firstName']; ?></td>   
                                        <td nowrap="nowrap"><?php echo $users[$i]['username']; ?></td>                                                                               
                                        <td nowrap="nowrap"><?php echo $users[$i]['companyDescription']; ?></td>                                         
                                        <td nowrap="nowrap"><?php echo $users[$i]['branchOfficeDescription']; ?></td>                                         
                                        <td nowrap="nowrap"><?php echo $users[$i]['sectorDescription']; ?></td>                                         
                                        <td nowrap="nowrap" class="text-center"><?php echo getBooleanToText($users[$i]['active']); ?></td>                                         
                                    </tr>  
                                    <?php 
                                        } 
                                    ?>                         
                                </tbody>
                                <?php } ?>
                            </table>
                        </div>
                    </div>
                    <?php if (isset($users) && count($users) > 0 && ($allowExport || $pagination != "")) { ?>
                    <div class="row">
                        <div class="col-sm-12">                        
                        <?php
                            if ($allowExport) {
                        ?>            
                        <a href="javascript:generalExport('users/export','<?php echo $filterGet.$orderGet; ?>');" class="pagination pagination-sm paginate_button page-item page-link" title="exportar" style="float:left; margin-right:5px;padding: 4px 8px 5px 8px;">                
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
/* End of file users_list_view.php */
/* Location: ./application/views/users/users_list_view.php */