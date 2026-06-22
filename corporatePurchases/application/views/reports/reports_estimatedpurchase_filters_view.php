<div class="row">                                                                       
    <div class="form-group" style="float:left;margin-right:10px;">
        <label for="dateFromFilter">Desde</label>
        <input type="date" id="dateFromFilter" name="dateFromFilter" class="form-control form-control-sm noEnterMyApp" autocomplete="off"  autofocus="1" value="" style="width:140px;">
    </div>                                       
    <div class="form-group" style="float:left;margin-right:10px;">
        <label for="dateToFilter">Hasta</label>
        <input type="date" id="dateToFilter" name="dateToFilter" max="<?php echo getCurrentDate(false); ?>" class="form-control form-control-sm noEnterMyApp" autocomplete="off" value="" style="width:140px;">                
    </div>    
    <div class="form-group" style="float:left;margin-right:10px;width:140px;">
        <label for="companyIdsFilter">Empresas</label>
        <select class="noEnterMyApp" multiple="multiple" id="companyIdsFilter" name="companyIdsFilter">
            <?php                                                                                                               
                if (isset($companies)) { 
                    for($i=0; $i < count($companies); $i++) {                                                
            ?>
            <option value="<?php echo $companies[$i]['id']; ?>" selected="selected"><?php echo $companies[$i]['description']; ?></option>                                
            <?php 
                    }
                }
            ?>                                
        </select>
        <input type="hidden" id="companyIdsFilterSelected" name="companyIdsFilterSelected" value="" />                   
    </div>        
    <div class="form-group" style="width:140px; float:left; margin-right:10px;">
        <label for="articleFilter">Artículo</label>
        <input type="text" class="form-control form-control-sm noEnterMyApp" id="articleFilter" name="articleFilter" placeholder="Código" autocomplete="off" value="">
    </div>   
    <div class="form-group" style="width:auto; float:left; margin-right:10px;">
        <label for="articleStateIdFilter">Estado Art.</label>
        <select id="articleStateIdFilter" name="articleStateIdFilter" class="form-control form-control-sm">                                
            <?php                                                         
                for ($i=0; $i < count($articleStates); $i++) {                                                
            ?>
            <option value="<?php echo $articleStates[$i]['id']; ?>"><?php echo $articleStates[$i]['description']; ?></option>
            <?php
                }
            ?>
        </select>                      
    </div>
    <div class="form-group" style="float:left;margin-right:10px;width:200px;">
        <label for="familyIdsFilter">Rubros</label>                                        
        <select class="noEnterMyApp" multiple="multiple" id="familyIdsFilter" name="familyIdsFilter">
            <?php                                                                                                               
                if (isset($families)) {                    
                    for($i=0; $i < count($families); $i++) {                        
            ?>
            <option value="<?php echo $families[$i]['id']; ?>" selected="selected"><?php echo $families[$i]['description'];?></option>                                
            <?php 
                    }
                }
            ?>                                
        </select>
        <input type="hidden" id="familyIdsFilterSelected" name="familyIdsFilterSelected" value="" />                   
    </div> 
    <div class="form-group" style="float:left;margin-right:10px;">
        <label for="estimatedDays">Estimación</label>
        <input type="number" min="1" id="estimatedDays" name="estimatedDays" class="form-control form-control-sm noEnterMyApp" autocomplete="off" value="" placeholder="Días" style="width:100px;" required="1">
    </div>     
</div> 
<?php
/* End of file reports_estimatedpurchase_filters_view.php */
/* Location: ./application/views/reports/reports_estimatedpurchase_filters_view.php */