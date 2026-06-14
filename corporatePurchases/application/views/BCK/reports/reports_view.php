<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Buscar por...</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" title="mostrar según filtros aplicados" onclick="loadResultsReports()"><i class="fas fa-search"></i></button>                                      
                        <button type="button" class="btn btn-tool" title="limpiar filtros" onclick="clearFilterReports()"><i class="fa fa-undo"></i></button>              
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
                                    <label for="type">Tipo</label>
                                    <select id="type" name="type" class="form-control form-control-sm" onchange="showHideFiltersReports()">                                            
                                        <option value="">[Seleccionar]</option>                                    
                                        <?php 
                                            for($i=0; $i < count($reports); $i++) { 
                                                $attributes = "";    
                                                if (isset($reports[$i]['callback']) && trim($reports[$i]['callback']) != "") {
                                                    $attributes .= 'callback="'.trim($reports[$i]['callback']).'" ';
                                                }
                                                if (isset($reports[$i]['stateFilter']) && (int)$reports[$i]['stateFilter'] == 1) {
                                                    $attributes .= 'fsta="1" ';
                                                }
                                                if (isset($reports[$i]['workshopManagerFilter']) && (int)$reports[$i]['workshopManagerFilter'] == 1) {
                                                    $attributes .= 'fwm="1" ';
                                                }
                                                if (isset($reports[$i]['assessorFilter']) && (int)$reports[$i]['assessorFilter'] == 1) {
                                                    $attributes .= 'fass="1" ';
                                                }
                                                if (isset($reports[$i]['familyFilter']) && (int)$reports[$i]['familyFilter'] == 1) {
                                                    $attributes .= 'ffam="1" ';
                                                }
                                                if (isset($reports[$i]['branchOfficeFilter']) && (int)$reports[$i]['branchOfficeFilter'] == 1) {
                                                    $attributes .= 'fbo="1" ';
                                                } 
                                                if (isset($reports[$i]['articleFilter']) && (int)$reports[$i]['articleFilter'] == 1) {
                                                    $attributes .= 'art="1" ';
                                                } 
                                                if (isset($reports[$i]['statistics']) && (int)$reports[$i]['statistics'] == 1) {
                                                    $attributes .= 'stat="1" ';
                                                } else {
                                                    $attributes .= 'stat="0" ';
                                                } 
                                                if (isset($reports[$i]['reassignedFilter']) && (int)$reports[$i]['reassignedFilter'] == 1) {
                                                    $attributes .= 'reas="1" ';
                                                } else {
                                                    $attributes .= 'reas="0" ';
                                                } 
                                        ?>
                                        <option value="<?php echo $reports[$i]['id']; ?>" <?php if ($i == 0) echo ' selected="selected" '; ?> <?php echo $attributes; ?>><?php echo $reports[$i]['description']; ?></option>                
                                        <?php } ?>                    
                                    </select>  
                                </div>   
                                <div class="form-group" style="float:left;margin-right:10px;">
                                    <label for="dateFromFilter">Desde</label>
                                    <input type="date" id="dateFromFilter" name="dateFromFilter" class="form-control form-control-sm noEnterMyApp" autocomplete="off"  autofocus="1" value="<?php echo $dateFromFilter; ?>" original="<?php echo $dateFromFilter; ?>" style="width:140px;">
                                </div>   
                                <div class="form-group" style="float:left;margin-right:10px;">
                                    <label for="dateToFilter">Hasta</label>
                                    <input type="date" id="dateToFilter" name="dateToFilter" class="form-control form-control-sm noEnterMyApp" autocomplete="off" value="<?php echo $dateToFilter; ?>" original="<?php echo $dateFromFilter; ?>" style="width:140px;">                
                                </div>  
                                <div class="form-group hide" style="width:auto; float:left; margin-right:10px;" id="stateFilterContainer">
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
                                <?php if ($allowSearchWorkshopManager) { ?>
                                <div class="form-group hide" style="width:auto; float:left; margin-right:10px;" id="workshopManagerIdFilterContainer">
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
                                <?php } ?>
                                <?php if ($allowSearchAssessor) { ?>
                                <div class="form-group hide" style="width:auto; float:left; margin-right:10px;" id="assessorIdFilterContainer">
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
                                <?php } ?>
                                <?php if ($allowSearchFamily) { ?>
                                <div class="form-group hide" style="width:auto; float:left; margin-right:10px;" id="familyIdFilterContainer">
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
                                <?php } ?>
                                <?php if ($allowSearchBranchOffice) { ?>
                                <div class="form-group hide" style="width:auto; float:left; margin-right:10px;" id="branchOfficeIdFilterContainer">
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
                                <?php } ?>
                                <div class="form-group hide" style="width:140px; float:left; margin-right:10px;" id="articleFilterContainer">
                                    <label for="articleFilter">Artículo</label>
                                    <input type="text" class="form-control form-control-sm noEnterMyApp" id="articleFilter" name="articleFilter" placeholder="Artículo" autocomplete="off" value="">
                                </div>   
                                <?php if ($allowSearchReassigned) { ?>
                                <div class="form-group hide" style="width:80px; float:left; margin-right:10px;" id="reassignedFilterContainer">
                                    <label for="reassignedFilter">Reasig.</label>
                                    <select id="reassignedFilter" name="reassignedFilter" class="form-control form-control-sm">
                                        <option value="" selected="selected">[Todos]</option>                       
                                        <?php                                                         
                                            for ($i=0; $i < count($reassignedStates); $i++) {                                                
                                        ?>
                                        <option value="<?php echo $reassignedStates[$i]['id']; ?>"><?php echo $reassignedStates[$i]['description'];?></option>
                                        <?php
                                            }
                                        ?>
                                    </select>  
                                </div>  
                                <?php } ?>                                                                                 
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
/* End of file reports_view.php */
/* Location: ./application/views/reports/reports_view.php */