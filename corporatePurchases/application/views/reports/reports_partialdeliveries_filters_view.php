<div class="row">
    <div class="form-group" style="float:left;margin-right:10px;">
        <label for="dateFromFilter">Desde Plazo</label>
        <input type="date" id="dateFromFilter" name="dateFromFilter" class="form-control form-control-sm noEnterMyApp" autocomplete="off" autofocus="1" value="<?php echo (isset($dateFromFilter)?$dateFromFilter:""); ?>" original="<?php echo (isset($dateFromFilter)?$dateFromFilter:""); ?>" style="width:140px;">
    </div>
    <div class="form-group" style="float:left;margin-right:10px;">
        <label for="dateToFilter">Hasta Plazo</label>
        <input type="date" id="dateToFilter" name="dateToFilter" class="form-control form-control-sm noEnterMyApp" autocomplete="off" value="<?php echo (isset($dateToFilter)?$dateToFilter:""); ?>" original="<?php echo (isset($dateToFilter)?$dateToFilter:""); ?>" style="width:140px;">
    </div>
    <div class="form-group" style="float:left;margin-right:10px;">
        <label for="stateIdFilter">Estado</label>
        <select id="stateIdFilter" name="stateIdFilter" class="form-control form-control-sm" style="width:150px;">
            <option value="" selected="selected">[Todos]</option>
            <?php
                for ($i=0; $i < count($states); $i++) {
            ?>
            <option value="<?php echo $states[$i]['id']; ?>"><?php echo $states[$i]['description']; ?></option>
            <?php
                }
            ?>
        </select>
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
</div>
<div class="row">
    <div class="form-group" style="float:left;margin-right:10px;">
        <label for="familyIdFilter">Rubro</label>
        <select id="familyIdFilter" name="familyIdFilter" class="form-control form-control-sm" style="width:160px;">
            <option value="" selected="selected">[Todos]</option>
            <?php
                for ($i=0; $i < count($families); $i++) {
            ?>
            <option value="<?php echo $families[$i]['id']; ?>"><?php echo $families[$i]['description']; ?></option>
            <?php
                }
            ?>
        </select>
    </div>
    <div class="form-group" style="width:140px; float:left; margin-right:10px;">
        <label for="articleFilter">Cód./Artículo</label>
        <input type="text" class="form-control form-control-sm noEnterMyApp" id="articleFilter" name="articleFilter" placeholder="Artículo" autocomplete="off" value="">
    </div>
</div>
<?php
/* End of file reports_partialdeliveries_filters_view.php */
/* Location: ./application/views/reports/reports_partialdeliveries_filters_view.php */
