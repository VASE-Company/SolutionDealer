<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Buscar por...</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" title="buscar según filtros aplicados" onclick="loadResultsCRM()"><i class="fas fa-search"></i></button>                                      
                        <button type="button" class="btn btn-tool" title="limpiar filtros" onclick="clearFilterCRM()"><i class="fa fa-undo"></i></button>              
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>                        
                    </div>
                    <!-- /.card-tools -->
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="col-12"> 
                        <form action="" method="post" accept-charset="utf-8" id="frmFilter" name="frmFilter" role="form">                                                            
                            <div class="row">       
                                <div class="form-group" style="float:left;margin-right:10px;">
                                    <label for="dateFromFilter">Desde</label>
                                    <input type="date" id="dateFromFilter" name="dateFromFilter" class="form-control form-control-sm noEnterMyApp" autocomplete="off"  autofocus="1" value="<?php echo $dateFromFilter; ?>" original="<?php echo $dateFromFilter; ?>" style="width:140px;">
                                </div>   
                                <div class="form-group" style="float:left;margin-right:10px;">
                                    <label for="dateToFilter">Hasta</label>
                                    <input type="date" id="dateToFilter" name="dateToFilter" class="form-control form-control-sm noEnterMyApp" autocomplete="off" value="<?php echo $dateToFilter; ?>" original="<?php echo $dateToFilter; ?>" style="width:140px;">                
                                </div>  
                                <div class="form-group" style="width:auto; float:left; margin-right:10px;">
                                    <label for="stateFilter">Estado</label>
                                    <select id="stateFilter" name="stateFilter" class="form-control form-control-sm">
                                        <option value="" selected="selected">[Todos]</option>                       
                                        <?php                                                         
                                            foreach ($states as $stateId => $stateDescription) {                                                
                                        ?>
                                        <option value="<?php echo $stateId; ?>"><?php echo $stateDescription;?></option>
                                        <?php
                                            }
                                        ?>
                                    </select>                      
                                </div>  
                                <div class="form-group" style="width:auto; float:left; margin-right:10px;">
                                    <label for="workshopManagerIdFilter">Resp. Taller</label>
                                    <select id="workshopManagerIdFilter" name="workshopManagerIdFilter" class="form-control form-control-sm">
                                        <option value="" selected="selected">[Todos]</option>                       
                                        <?php                                                         
                                            for ($i=0; $i < count($workshopManagers); $i++) {                                                
                                        ?>
                                        <option value="<?php echo $workshopManagers[$i]['id']; ?>"><?php echo trim($workshopManagers[$i]['lastName'].", ".$workshopManagers[$i]['firstName']);?></option>
                                        <?php
                                            }
                                        ?>
                                    </select>                      
                                </div>
                                <div class="form-group" style="width:auto; float:left; margin-right:10px;">
                                    <label for="assessorIdFilter">Asesor</label>
                                    <select id="assessorIdFilter" name="assessorIdFilter" class="form-control form-control-sm">
                                        <option value="" selected="selected">[Todos]</option>                       
                                        <?php                                                         
                                            for ($i=0; $i < count($assessors); $i++) {                                                
                                        ?>
                                        <option value="<?php echo $assessors[$i]['id']; ?>"><?php echo trim($assessors[$i]['lastName'].", ".$assessors[$i]['firstName']);?></option>
                                        <?php
                                            }
                                        ?>
                                    </select>                      
                                </div>  
                                <div class="form-group" style="width:auto; float:left; margin-right:10px;">
                                    <label for="familyIdFilter">Familia</label>
                                    <select id="familyIdFilter" name="familyIdFilter" class="form-control form-control-sm">
                                        <option value="" selected="selected">[Todos]</option>                       
                                        <?php                                                         
                                            for ($i=0; $i < count($families); $i++) {                                                
                                        ?>
                                        <option value="<?php echo $families[$i]['id']; ?>"><?php echo $families[$i]['description'];?></option>
                                        <?php
                                            }
                                        ?>
                                    </select>                      
                                </div>    
                                <div class="form-group" style="width:auto; float:left; margin-right:10px;">
                                    <label for="branchOfficeIdFilter">Sucursal</label>
                                    <select id="branchOfficeIdFilter" name="branchOfficeIdFilter" class="form-control form-control-sm">
                                        <option value="" selected="selected">[Todos]</option>                       
                                        <?php                                                         
                                            for ($i=0; $i < count($branchOffices); $i++) {                                                
                                        ?>
                                        <option value="<?php echo $branchOffices[$i]['id']; ?>"><?php echo $branchOffices[$i]['description'];?></option>
                                        <?php
                                            }
                                        ?>
                                    </select>                      
                                </div>      
                                <div class="form-group" style="width:140px; float:left; margin-right:10px;">
                                    <label for="articleFilter">Artículo</label>
                                    <input type="text" class="form-control form-control-sm noEnterMyApp" id="articleFilter" name="articleFilter" placeholder="Artículo" autocomplete="off" value="">
                                </div>      
                                <div class="form-group" style="width:140px; float:left; margin-right:10px;">
                                    <label for="vehicleFilter" title="Puede ingresar varios valores separados por ';'">Vehículo (*)</label>
                                    <input type="text" class="form-control form-control-sm noEnterMyApp" id="vehicleFilter" name="vehicleFilter" placeholder="Vehículo" autocomplete="off" value="">
                                </div>  
                                <div class="form-group" style="width:80px; float:left; margin-right:10px;">
                                    <label for="yearFromFilter">Desde Año</label>
                                    <select id="yearFromFilter" name="yearFromFilter" class="form-control form-control-sm">
                                        <option value="" selected="selected">-------</option>                       
                                        <?php                                                         
                                            for ($i=0; $i < count($vehicleYears); $i++) {                                                
                                        ?>
                                        <option value="<?php echo $vehicleYears[$i]['id']; ?>"><?php echo $vehicleYears[$i]['description'];?></option>
                                        <?php
                                            }
                                        ?>
                                    </select> 
                                </div>                                                                                                                  
                                <div class="form-group" style="width:80px; float:left; margin-right:10px;">
                                    <label for="yearToFilter">Hasta Año</label>                    
                                    <select id="yearToFilter" name="yearToFilter" class="form-control form-control-sm">
                                        <option value="" selected="selected">-------</option>                       
                                        <?php                                                         
                                            for ($i=0; $i < count($vehicleYears); $i++) {                                                
                                        ?>
                                        <option value="<?php echo $vehicleYears[$i]['id']; ?>"><?php echo $vehicleYears[$i]['description'];?></option>
                                        <?php
                                            }
                                        ?>
                                    </select>      
                                </div>    
                                <div class="form-group" style="width:90px; float:left; margin-right:10px;">
                                    <label for="kmsFromFilter">Desde Kms</label>
                                    <input type="number" id="kmsFromFilter" name="kmsFromFilter" min="0" step="1" class="form-control form-control-sm noEnterMyApp" maxlength="7" autocomplete="off" value="" placeholder="" style="width:90px;">                        
                                </div> 
                                <div class="form-group" style="width:90px; float:left; margin-right:10px;">
                                    <label for="kmsToFilter">Hasta Kms</label>
                                    <input type="number" id="kmsToFilter" name="kmsToFilter" min="0" step="1" class="form-control form-control-sm noEnterMyApp" maxlength="7" autocomplete="off" value="" placeholder="" style="width:90px;">                        
                                </div>                                                     
                            </div>                                  
                        </form>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <div id="predata"></div>     
            <div style="margin-top:10px;" id="data"></div>                
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->    
</div>
<!-- /.container-fluid -->
<?php
/* End of file crm_view.php */
/* Location: ./application/views/crm/crm_view.php */