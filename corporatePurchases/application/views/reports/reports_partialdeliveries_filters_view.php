<!-- Control de Entregas: filtros ordenados en dos filas para separar plazo/empresa de estados/articulo. -->
<div class="row">
    <div class="form-group col-sm-6 col-md-3 col-lg-2">
        <label for="dateFromFilter">Desde Plazo</label>
        <input type="date" id="dateFromFilter" name="dateFromFilter" class="form-control form-control-sm noEnterMyApp" autocomplete="off" autofocus="1" value="<?php echo (isset($dateFromFilter)?$dateFromFilter:""); ?>" original="<?php echo (isset($dateFromFilter)?$dateFromFilter:""); ?>">
    </div>
    <div class="form-group col-sm-6 col-md-3 col-lg-2">
        <label for="dateToFilter">Hasta Plazo</label>
        <input type="date" id="dateToFilter" name="dateToFilter" class="form-control form-control-sm noEnterMyApp" autocomplete="off" value="<?php echo (isset($dateToFilter)?$dateToFilter:""); ?>" original="<?php echo (isset($dateToFilter)?$dateToFilter:""); ?>">
    </div>
    <div class="form-group col-sm-6 col-md-3 col-lg-3">
        <label for="companyIdFilter">Empresa</label>
        <select id="companyIdFilter" name="companyIdFilter" class="form-control form-control-sm" onchange="selectCompanyReports()">
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
    <div class="form-group col-sm-6 col-md-3 col-lg-3">
        <label for="branchOfficeIdFilter">Sucursal</label>
        <select id="branchOfficeIdFilter" name="branchOfficeIdFilter" class="form-control form-control-sm" onchange="selectBranchOfficeReports()">
            <option value="" selected="selected">[Todas]</option>
        </select>
    </div>
</div>
<div class="row">
    <div class="form-group col-sm-6 col-md-3 col-lg-2">
        <label for="stateIdFilter">Estado Pedido</label>
        <select id="stateIdFilter" name="stateIdFilter" class="form-control form-control-sm">
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
    <div class="form-group col-sm-6 col-md-3 col-lg-2">
        <label for="deliveryStateIdFilter">Estado Entrega</label>
        <select id="deliveryStateIdFilter" name="deliveryStateIdFilter" class="form-control form-control-sm">
            <option value="" selected="selected">[Todos]</option>
            <option value="PENDING">Sin Entregar A&uacute;n</option>
            <option value="PARTIAL">Entrega Parcial</option>
            <option value="COMPLETE">Entrega Completa</option>
        </select>
    </div>
    <div class="form-group col-sm-6 col-md-3 col-lg-3">
        <label for="familyIdFilter">Rubro</label>
        <select id="familyIdFilter" name="familyIdFilter" class="form-control form-control-sm">
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
    <div class="form-group col-sm-6 col-md-3 col-lg-3">
        <label for="articleFilter">C&oacute;d./Art&iacute;culo</label>
        <input type="text" class="form-control form-control-sm noEnterMyApp" id="articleFilter" name="articleFilter" placeholder="Art&iacute;culo" autocomplete="off" value="">
    </div>
</div>
<?php
/* End of file reports_partialdeliveries_filters_view.php */
/* Location: ./application/views/reports/reports_partialdeliveries_filters_view.php */
