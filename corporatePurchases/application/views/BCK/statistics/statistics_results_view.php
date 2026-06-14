<?php
    $columnsByRow = 2;
?>
<div class="card">             
    <div class="card-body">    
    <?php
        $lstEntities = NULL;
        if ($entities) {
            $column = 0;
            for ($i=0; $i < count($entities); $i++) {
                $lstEntities[$i] = $entities[$i]['id'];

                $column++;
                if ($column == 1) {
    ?>
        <div class="row">
    <?php
                }
    ?>
            <div class="col-sm-<?php echo (12 / $columnsByRow); ?>">
                <div class="card">
                    <div class="card-header" style="color:#FFFFFF;background-color:<?php echo $entities[$i]['color']; ?>">
                        <h3 class="card-title"><?php echo $entities[$i]['title']; ?></h3>   
                        <div class="card-tools">                                                        
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>                          
                        </div>           
                    </div>
                    <div class="card-body">  
                        <table class="table table-bordered table-sm statTable" id="<?php echo $entities[$i]['id']; ?>" description="<?php echo $entities[$i]['gridTitle']; ?>" sectionTitle="<?php echo $entities[$i]['title']; ?>">                 
                            <thead>                                                              
                                <tr id="header">                                                                                                        
                                    <th nowrap="nowrap" description="<?php echo $entities[$i]['gridTitle']; ?>" class="statColHeader">
                                        <input type="checkbox" id="selectAll" value="1" class="noEnterMyApp checkboxStat" onclick="selectAllByEntityStatistics('<?php echo $entities[$i]['id']; ?>')" checked="checked"/>                                        
                                        <?php echo $entities[$i]['gridTitle']; ?>     
                                        <button type="button" class="btn btn-default btn-xs" onclick="clickBtnShowRowsStatistics('<?php echo $entities[$i]['id']; ?>')" style="float:right;">
                                            <i class="fas fa-eye" id="showRowsIcon"></i>
                                        </button>                                                                            
                                    </th>    
                                    <?php                                                                                                                                           
                                        if (isset($columns)) { 
                                            for($j=0; $j < count($columns); $j++) {                                                                                                                                                                                                                                                                     
                                    ?>                                            
                                    <th nowrap="nowrap" width="120px" id="<?php echo $columns[$j]['id']; ?>" description="<?php echo $columns[$j]['title']; ?>" class="statColHeader">
                                        <?php echo $columns[$j]['title']; ?>     
                                        <?php
                                                $graphFunction = (isset($columns[$j]['graph'])?$columns[$j]['graph']:"");
                                                $graphFunction = str_replace('[@entityId]', $entities[$i]['id'], $graphFunction);                                                

                                                if ($graphFunction != "") {
                                        ?>
                                        <button type="button" class="btn btn-default btn-xs" onclick="<?php echo $graphFunction; ?>" style="float:right;">
                                            <i class="fas fa-chart-pie"></i>
                                        </button>                                               
                                        <?php       
                                                } 
                                        ?>                            
                                    </th>                                     
                                    <?php
                                            }            
                                        }
                                    ?>                                                          
                                </tr>
                            </thead>    
                            <tbody>        
                                <?php            
                                    $items = $entities[$i]['list'];                                                            
                                
                                    if (isset($items)) { 
                                        for($j=0; $j < count($items); $j++) {                                                                                                                                                                                                                                                                     
                                ?>
                                <tr class="statRow" id="<?php echo $items[$j]['id']; ?>">    
                                    <td nowrap="nowrap" id="title" description="<?php echo $items[$j]['description']; ?>" class="statCol">
                                        <input type="checkbox" id="cb<?php echo $items[$j]['id']; ?>" value="1" class="noEnterMyApp checkboxStat" onclick="refreshResultsStatistics()" checked="checked" />                                        
                                        <?php echo $items[$j]['description']; ?>                                        
                                    </td>   
                                    <?php                                                                                                                                           
                                        if (isset($columns)) { 
                                            for($k=0; $k < count($columns); $k++) {                                                                                                                                                                                                                                                                                                                    
                                    ?>                                            
                                    <td nowrap="nowrap" class="text-right statCol" id="<?php echo $columns[$k]['id']; ?>" colType="<?php echo (isset($columns[$k]['type'])?$columns[$k]['type']:""); ?>"><?php echo formatValue(0,(isset($columns[$k]['type'])?$columns[$k]['type']:"")); ?></td>             
                                    <?php                                             
                                            }            
                                        }
                                    ?>                                                               
                                </tr>
                                <?php                                             
                                        }            
                                    }
                                ?>                                                                     
                            </tbody>   
                            <footer>    
                                <tr class="statRow statRowFooter" id="-1">                                                                                                                       
                                    <th nowrap="nowrap" class="statCol">Total</th>                                         
                                    <?php                                                                                                                                           
                                        if (isset($columns)) { 
                                            for($j=0; $j < count($columns); $j++) {                                                                                                                                                                                                                                                                     
                                    ?>                                            
                                    <th nowrap="nowrap" class="text-right statCol" id="<?php echo $columns[$j]['id']; ?>" colType="<?php echo (isset($columns[$j]['type'])?$columns[$j]['type']:""); ?>"><?php echo formatValue(0,(isset($columns[$j]['type'])?$columns[$j]['type']:"")); ?></th>              
                                    <?php                                             
                                            }            
                                        }
                                    ?>                    
                                </tr> 
                            </footer> 
                        </table> 
                        <?php if ($allowExport) { ?>
                        <button type="button" class="btn btn-default btn-sm" onclick="exportStatistics('<?php echo $entities[$i]['id']; ?>')" style="margin-top:10px;margin-bottom:0px;">                            
                            <i class="fas fa-download"></i>
                        </button>                   
                        <?php } ?>                                                            
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->                   
            </div> 
            <!-- /.col -->                   
    <?php
                if ($column == $columnsByRow || ($i == count($entities) - 1)) {
                    $column = 0;
    ?>                    
        </div>     
        <!-- /.row -->   
    <?php
                }
    ?>                 
    <?php
            }
        } else {
    ?>
        <div class="text-center">
        No se encontraron resultados para el período solicitado.
        </div> 
    <?php         
        }
    ?>
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->
<div class="hide" id="jsonData"> 
    <div id="resultData"><?php echo (isset($data)?json_encode($data):""); ?></div>                             
    <div id="entitiesData"><?php echo (isset($lstEntities)?json_encode($lstEntities):""); ?></div>   
</div>
<!-- EXPORT -->
<form class="hide" action="<?php echo base_url(); ?>statistics/export" method="post" accept-charset="utf-8" id="frmDataExport" name="frmDataExport" role="form" target="_blank">
    <input type="hidden" id="data" name="data" value="">
    <input type="hidden" id="title" name="title" value="">
</form>
<div id="divExport" class="hide"></div>  
<!-- /EXPORT -->
<?php 
/* End of file statistics_results_view.php */
/* Location: ./application/views/statistics/statistics_results_view.php */