<div class="row">                                                                       
    <div class="form-group" style="float:left;margin-right:10px;">
        <label for="dateFromFilter">Desde</label>
        <input type="date" id="dateFromFilter" name="dateFromFilter" class="form-control form-control-sm noEnterMyApp" autocomplete="off"  autofocus="1" value="<?php echo (isset($dateToFilter)?$dateToFilter:""); ?>" original="<?php echo (isset($dateToFilter)?$dateToFilter:""); ?>" style="width:140px;">
    </div>                                       
    <div class="form-group" style="float:left;margin-right:10px;">
        <label for="dateToFilter">Hasta</label>
        <input type="date" id="dateToFilter" name="dateToFilter" class="form-control form-control-sm noEnterMyApp" autocomplete="off" value="<?php echo (isset($dateFromFilter)?$dateFromFilter:""); ?>" original="<?php echo (isset($dateFromFilter)?$dateFromFilter:""); ?>" style="width:140px;">                
    </div>    
    <div class="form-group" style="float:left;margin-right:10px;">
        <label for="companyIdFilter">Empresa</label>
        <select id="companyIdFilter" name="companyIdFilter" class="form-control form-control-sm" onchange="selectCompanyReports()" style="width:140px;">
            <option value="" selected="selected">[Todas]</option>                       
            <?php                                                         
                for ($i=0; $i < count($companies); $i++) {                                                
            ?>
            <option value="<?php echo $companies[$i]['id']; ?>"><?php echo $companies[$i]['description']; ?></option>
            <?php
                }
            ?>
        </select>   
    </div>  
    <div class="form-group" style="float:left;margin-right:10px;" onchange="selectBranchOfficeReports()">
        <label for="branchOfficeIdFilter">Sucursal</label>
        <select id="branchOfficeIdFilter" name="branchOfficeIdFilter" class="form-control form-control-sm" style="width:140px;">
            <option value="" selected="selected">[Todas]</option>                                   
        </select>   
    </div> 
    <div class="form-group" style="float:left;margin-right:10px;">
        <label for="sectorIdFilter">Sector</label>
        <select id="sectorIdFilter" name="sectorIdFilter" class="form-control form-control-sm" style="width:140px;">
            <option value="" selected="selected">[Todos]</option>                                   
        </select>   
    </div>   
</div> 
<div class="row">   
    <div class="form-group" style="float:left;margin-right:10px;">
        <label for="groupedByFilter">Agrupado por
        <select id="groupedByFilter" name="groupedByFilter" class="form-control form-control-sm" style="width:140px;">                                  
            <?php                                                         
                for ($i=0; $i < count($groupedBy); $i++) {                                                
            ?>
            <option value="<?php echo $groupedBy[$i]['id']; ?>" <?php echo ($i==0?' selected="selected" ':""); ?>><?php echo $groupedBy[$i]['description']; ?></option>
            <?php
                }
            ?>
        </select>
    </div> 
    <div class="form-group" style="float:left;margin-right:10px;">
        <label for="subtypeFilter">Tipo
        <select id="subtypeFilter" name="subtypeFilter" class="form-control form-control-sm">                                  
            <?php                                                         
                for ($i=0; $i < count($subtypes); $i++) {                                                
            ?>
            <option value="<?php echo $subtypes[$i]['id']; ?>" <?php echo ($i==0?' selected="selected" ':""); ?>><?php echo $subtypes[$i]['description']; ?></option>
            <?php
                }
            ?>
        </select>
    </div> 
    <div class="form-group" style="float:left;margin-right:10px;">
        <label for="valueTypeIdFilter">Valor
        <select id="valueTypeIdFilter" name="valueTypeIdFilter" class="form-control form-control-sm">                                  
            <?php                                                         
                for ($i=0; $i < count($valueTypes); $i++) {                                                
            ?>
            <option value="<?php echo $valueTypes[$i]['id']; ?>" <?php echo ($i==0?' selected="selected" ':""); ?>><?php echo $valueTypes[$i]['description']; ?></option>
            <?php
                }
            ?>
        </select>
    </div> 
</div> 
<?php
/* End of file reports_general_filters_view.php */
/* Location: ./application/views/reports/reports_general_filters_view.php */