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
                        <form action="<?php echo base_url(); ?>articles/search" method="post" accept-charset="utf-8" id="frmFilter" name="frmFilter" role="form">                                                            
                            <div class="row">
                                <div class="col-12">                                                                        
                                    <div class="form-group" style="width:300px; float:left; margin-right:10px;">
                                        <label for="textFilter">Buscar</label>                                
                                        <input class="form-control form-control-sm" type="text" maxlength="50" placeholder="Buscar" id="textFilter" name="textFilter" autocomplete="off" value="<?php echo set_value('textFilter',$textFilter); ?>">
                                    </div>                                                                                                     
                                    <div class="form-group" style="width:auto; float:left; margin-right:10px;">
                                        <label for="familyIdFilter">Rubro</label>
                                        <select id="familyIdFilter" name="familyIdFilter" class="form-control form-control-sm">
                                            <?php 
                                                $familyIdFilter = set_value('familyIdFilter',$familyIdFilter);
                                                
                                                $selected = ($familyIdFilter == ""?' selected="selected" ':''); 
                                            ?>
                                            <option value="" <?php echo $selected; ?>>[Todos]</option>                                                                 
                                            <?php                                                         
                                                for ($i=0; $i < count($families); $i++) {         
                                                    $selected = ($families[$i]['id'] == $familyIdFilter?' selected="selected" ':'');                                       
                                            ?>
                                            <option value="<?php echo $families[$i]['id']; ?>" <?php echo $selected; ?>><?php echo $families[$i]['description'];?></option>
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
                            <table id="grArticles" class="table table-bordered table-hover table-sm">
                                <thead>
                                    <tr>                                                                                                                                                
                                        <th class="text-center" nowrap="nowrap" width="<?php echo ($allowDelete?"55px":"35px"); ?>">
                                            <?php if ($allowInsert) { ?>
                                            <a href="<?php echo base_url(); ?>articles/edit/0" class="btn without-padding" title="nuevo">
                                                <i class="far fa-plus-square"></i>
                                            </a>
                                            <?php } ?>       
                                            <?php if ($allowImport) { ?>       
                                            <a href="<?php echo base_url(); ?>articles/import" class="btn without-padding" title="importar">
                                                <i class="fas fa-upload"></i>
                                            </a>                                                                         
                                            <?php } ?>                                                                                                                       
                                        </th>                                                                    
                                        <th nowrap="nowrap">Código  
                                            <?php echo generateOrderButton(true,($fieldOrder == 'cod' && $typeOrder == 'asc'),'articles/listing/1?fOrd=cod&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'cod' && $typeOrder == 'desc'),'articles/listing/1?fOrd=cod&tOrd=desc'.$filterGet); ?>                       
                                        </th>
                                        <th nowrap="nowrap">Descripción  
                                            <?php echo generateOrderButton(true,($fieldOrder == 'des' && $typeOrder == 'asc'),'articles/listing/1?fOrd=des&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'des' && $typeOrder == 'desc'),'articles/listing/1?fOrd=des&tOrd=desc'.$filterGet); ?>                       
                                        </th>
                                        <th nowrap="nowrap">Rubro  
                                            <?php echo generateOrderButton(true,($fieldOrder == 'fam' && $typeOrder == 'asc'),'articles/listing/1?fOrd=fam&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'fam' && $typeOrder == 'desc'),'articles/listing/1?fOrd=fam&tOrd=desc'.$filterGet); ?>                       
                                        </th>                                                                    
                                        <th nowrap="nowrap">Habitual</th> 
                                        <th nowrap="nowrap">Activo</th>                                       
                                        <th nowrap="nowrap">Últ. Precio</th> 
                                        <th nowrap="nowrap">Stock</th>                                                                                
                                    </tr>
                                </thead>
                                <?php if (isset($articles)) { ?>
                                <tbody>
                                    <?php
                                        for($i=0; $i < count($articles); $i++) {                                            
                                    ?>
                                    <tr ondblclick="onClick('btnEdit<?php echo $articles[$i]['id']; ?>')">                                
                                        <td class="text-center" nowrap="nowrap">                            
                                            <a href="<?php echo base_url(); ?>articles/edit/<?php echo $articles[$i]['id']; ?>" class="btn without-padding" title="<?php echo ($allowEdit?"editar":"ver"); ?>" id="btnEdit<?php echo $articles[$i]['id']; ?>">
                                                <i class="far fa-edit"></i>
                                            </a>
                                            <?php if ($allowDelete) { ?>
                                            <button type="button" class="btn without-padding" onclick="generalDelete(<?php echo $articles[$i]['id']; ?>,'articles/delete')" title="eliminar">
                                                <i class="far fa-minus-square"></i>
                                            </button>                                            
                                            <?php } ?>                                    
                                        </td>  
                                        <td nowrap="nowrap"><?php echo $articles[$i]['code']; ?></td> 
                                        <td nowrap="nowrap"><?php echo $articles[$i]['description']; ?></td>                                                                                                                   
                                        <td nowrap="nowrap"><?php echo $articles[$i]['familyDescription']; ?></td>                                          
                                        <td nowrap="nowrap" class="text-center"><?php echo getBooleanToText($articles[$i]['usual']); ?></td>                                         
                                        <td nowrap="nowrap" class="text-center"><?php echo getBooleanToText($articles[$i]['active']); ?></td>                                         
                                        <td nowrap="nowrap" class="text-center">
                                            <?php if ($articles[$i]['lastUnitPrice'] > 0) { ?>                                    
                                                <a href="javascript:seeArticleLastUnitPrice(<?php echo $articles[$i]['id']; ?>)"><?php echo decimalFormat($articles[$i]['lastUnitPrice'],2); ?></a>
                                            <?php } else { ?>
                                                <?php echo decimalFormat($articles[$i]['lastUnitPrice'],2); ?>
                                            <?php } ?> 
                                        </td> 
                                        <td class="text-center">       
                                            <?php if ((int)$articles[$i]['affectsStock'] == 1) { ?>                                     
                                            <button type="button" class="btn without-padding" onclick="seeStockByArticle(<?php echo $articles[$i]['id']; ?>)" title="ver stock"><i class="fa fa-chart-bar"></i></button>
                                            <?php } else { ?>
                                            ----
                                            <?php } ?>                                          
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
                    <?php if (isset($articles) && count($articles) > 0 && ($allowExport || $pagination != "")) { ?>
                    <div class="row">
                        <div class="col-sm-12">
                            <?php
                                if ($allowExport) {
                            ?>            
                            <a href="javascript:generalExport('articles/export','<?php echo $filterGet.$orderGet; ?>');" class="pagination pagination-sm paginate_button page-item page-link" title="exportar" style="float:left; margin-right:5px;padding: 4px 8px 5px 8px;">                
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
/* End of file articles_list_view.php */
/* Location: ./application/views/articles/articles_list_view.php */