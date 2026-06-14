<div class="row without-padding">   
    <div class="col-sm-12" style="margin-bottom: 0px;margin-top: 0px;margin-left: 9px;"> 
        <form action="" method="post" accept-charset="utf-8" id="frmFinderFilter" name="frmFinderFilter" role="form">                                                            
            <div class="row">
                <div>                                                                        
                    <div class="form-group">                                                                        
                        <div class="input-group input-group-sm">
                            <input class="form-control form-control-sm noEnterMyApp" type="text" maxlength="25" placeholder="Buscar Código/Descripción" id="textFilter" name="textFilter" autocomplete="off" value="<?php echo set_value('textFilter',$textFilter); ?>" style="width:200px;">
                            <span class="input-group-append">
                                <button type="button" class="btn btn-default btn-flat" onclick="searchFinderOrderEdit('articles')" title="buscar"><i class="fas fa-search"></i></button>
                                <button type="button" class="btn btn-default btn-flat" onclick="searchFinderOrderEdit('articles',true)" title="limpiar filtros"><i class="fa fa-undo"></i></button>
                            </span>                            
                        </div>
                    </div>                                                                                                     
                </div>                                                        
            </div>                               
        </form>
    </div>  
    <div class="col-sm-12">
        <table id="grArticles" class="table table-bordered table-sm">
            <thead>
                <tr>                                                                                         
                    <th nowrap="nowrap">Código</th>  
                    <th nowrap="nowrap">Descripción</th>                                                               
                    <th nowrap="nowrap">Rubro</th>    
                    <th style="width:30px;"></th>                                 
                </tr>
            </thead>
            <?php if (isset($articles)) { ?>
            <tbody>
                <?php
                    for($i=0; $i < count($articles); $i++) {                                            
                ?>
                <tr>                                
                    <td><?php echo $articles[$i]['code']; ?></td> 
                    <td><?php echo $articles[$i]['description']; ?></td>                              
                    <td><?php echo $articles[$i]['familyDescription']; ?></td>                                                                                                                          
                    <td class="text-center without-padding">                                                                                                                                                
                        <button type="button" class="btn btn-sm btn-info" onclick="acceptArticlesFinderOrderEdit(<?php echo ($i + 1); ?>)" title="aceptar">
                            OK
                        </button>  
                        <input type="hidden" id="afCode<?php echo ($i + 1); ?>" name="afCode<?php echo ($i + 1); ?>" value="<?php echo $articles[$i]['code']; ?>" /> 
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
<?php if (isset($articles) && count($articles) > 0 && $pagination != "") { ?>
    <div class="row">
        <div class="col-sm-12">           
                <?php echo $pagination; ?>        
        </div>
    </div>
<?php
    }
?>      
<?php 
/* End of file orders_finder_articles_view.php */
/* Location: ./application/views/orders/orders_finder_articles_view.php */