<div class="row without-padding">   
    <div class="col-sm-12" style="margin-bottom: 0px;margin-top: 0px;margin-left: 9px;"> 
        <form action="" method="post" accept-charset="utf-8" id="frmFinderFilter" name="frmFinderFilter" role="form">                                                            
            <div class="row">
                <div>                                                                        
                    <div class="form-group">                                                                        
                        <div class="input-group input-group-sm">
                            <input class="form-control form-control-sm noEnterMyApp" type="text" maxlength="25" placeholder="Buscar Nombre Comercial/Razón Social" id="textFilter" name="textFilter" autocomplete="off" value="<?php echo set_value('textFilter',$textFilter); ?>" style="width:200px;">
                            <span class="input-group-append">
                                <button type="button" class="btn btn-default btn-flat" onclick="searchFinderBudgetEdit('suppliers')" title="buscar"><i class="fas fa-search"></i></button>
                                <button type="button" class="btn btn-default btn-flat" onclick="searchFinderBudgetEdit('suppliers',true)" title="limpiar filtros"><i class="fa fa-undo"></i></button>
                            </span>
                        </div>
                    </div>                                                                                                     
                </div>                                                        
            </div>                               
        </form>
    </div>  
    <div class="col-sm-12">
        <table id="grSuppliers" class="table table-bordered table-sm">
            <thead>
                <tr>                                                                                         
                    <th nowrap="nowrap">CUIT</th>  
                    <th nowrap="nowrap">Nombre Comercial</th>                                           
                    <th nowrap="nowrap">Razón Social</th>    
                    <th style="width:30px;"></th>                                 
                </tr>
            </thead>
            <?php if (isset($suppliers)) { ?>
            <tbody>
                <?php
                    for($i=0; $i < count($suppliers); $i++) {                                            
                ?>
                <tr>                                
                    <td><?php echo $suppliers[$i]['code']; ?></td> 
                    <td><?php echo $suppliers[$i]['tradeName']; ?></td>                                                                                                                   
                    <td><?php echo $suppliers[$i]['businessName']; ?></td>    
                    <td class="text-center without-padding">                                                                                                                                                
                        <button type="button" class="btn btn-sm btn-info" onclick="acceptSuppliersFinderBudgetEdit(<?php echo ($i + 1); ?>)" title="aceptar">
                            OK
                        </button>  
                        <input type="hidden" id="sfCode<?php echo ($i + 1); ?>" name="sfCode<?php echo ($i + 1); ?>" value="<?php echo $suppliers[$i]['code']; ?>" /> 
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
<?php if (isset($suppliers) && count($suppliers) > 0 && $pagination != "") { ?>
    <div class="row">
        <div class="col-sm-12">           
                <?php echo $pagination; ?>        
        </div>
    </div>
<?php
    }
?>      
<?php 
/* End of file budgets_finder_suppliers_view.php */
/* Location: ./application/views/budgets/budgets_finder_suppliers_view.php */