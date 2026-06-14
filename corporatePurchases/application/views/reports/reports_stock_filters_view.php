<div class="row">                                                                       
    <div class="form-group" style="width:auto; float:left; margin-right:10px;">
        <label for="warehouseIdFilter">Depósito</label>
        <select id="warehouseIdFilter" name="warehouseIdFilter" class="form-control form-control-sm">
            <option value="" selected="selected">[Todos]</option>                       
            <?php                                                         
                for ($i=0; $i < count($warehouses); $i++) {                                                
            ?>
            <option value="<?php echo $warehouses[$i]['id']; ?>"><?php echo $warehouses[$i]['description']; ?></option>
            <?php
                }
            ?>
        </select>                      
    </div>        
    <div class="form-group" style="width:auto; float:left; margin-right:10px;">
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
    <div class="form-group" style="width:140px; float:left; margin-right:10px;">
        <label for="articleFilter">Cod. Artículo</label>
        <input type="text" class="form-control form-control-sm noEnterMyApp" id="articleFilter" name="articleFilter" placeholder="Artículo" autocomplete="off" value="">
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
    <div class="form-group" style="width:auto; float:left; margin-right:10px;">
        <label for="stockTypeIdFilter">Tipo de Stock</label>
        <select id="stockTypeIdFilter" name="stockTypeIdFilter" class="form-control form-control-sm" onchange="selectTypeStockReport()">                                
            <?php                                                         
                for ($i=0; $i < count($stockTypes); $i++) {                                                
            ?>
            <option value="<?php echo $stockTypes[$i]['id']; ?>"><?php echo $stockTypes[$i]['description']; ?></option>
            <?php
                }
            ?>
        </select>                      
    </div> 
    <div class="form-group" style="width:auto; float:left; margin-right:10px;">
        <label for="haveStockFilter">Con Stock?</label>
        <select id="haveStockFilter" name="haveStockFilter" class="form-control form-control-sm">                                
            <?php                                                         
                for ($i=0; $i < count($haveStock); $i++) {                                                
            ?>
            <option value="<?php echo $haveStock[$i]['id']; ?>"><?php echo $haveStock[$i]['description']; ?></option>
            <?php
                }
            ?>
        </select>                      
    </div>      
    <div class="form-group" style="float:left;margin-right:10px;"  id="divDateToFilter">
        <label for="dateToFilter">Hasta</label>
        <input type="date" id="dateToFilter" name="dateToFilter" class="form-control form-control-sm noEnterMyApp" autocomplete="off" value="<?php echo (isset($dateToFilter)?$dateToFilter:""); ?>" original="<?php echo (isset($dateToFilter)?$dateToFilter:""); ?>" style="width:140px;">                
    </div>   
</div> 
<?php
/* End of file reports_stock_filters_view.php */
/* Location: ./application/views/reports/reports_stock_filters_view.php */