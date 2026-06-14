<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2" style="margin-bottom: 0px !important;">
            <div class="col-12 col-sm-12 col-md-6">
                <h5 class="m-0 text-dark">Notificaciones</h5>
            </div><!-- /.col -->                
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<div class="container-fluid">
    <div class="row">
        <div class="col-12 col-sm-12 col-md-6">
            <div class="card card-primary collapsed-card" style="margin-bottom:0px;">
                <div class="card-header">
                    <h3 class="card-title">Buscar por...</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" title="buscar según filtros aplicados" onclick="loadNotifications()"><i class="fas fa-search"></i></button>                                      
                        <button type="button" class="btn btn-tool" title="limpiar filtros" onclick="clearFilter('frmFilter',false)"><i class="fa fa-undo"></i></button>              
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>                        
                    </div>
                    <!-- /.card-tools -->
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="col-12"> 
                        <form action="" method="post" accept-charset="utf-8" id="frmFilter" name="frmFilter" role="form">                                                            
                            <div class="row">  
                                <div class="form-group" style="width:140px; float:left; margin-right:10px;">
                                    <label for="articleFilter">Buscar</label>
                                    <input type="text" class="form-control form-control-sm noEnterMyApp" id="textFilter" name="textFilter" placeholder="Comp./Obs." autocomplete="off" value="">
                                </div>      
                                <div class="form-group" style="float:left;margin-right:10px;">
                                    <label for="dateFromFilter">Desde</label>
                                    <input type="date" id="dateFromFilter" name="dateFromFilter" class="form-control form-control-sm noEnterMyApp" autocomplete="off"  autofocus="1" value="" style="width:140px;">
                                </div>   
                                <div class="form-group" style="float:left;margin-right:10px;">
                                    <label for="dateToFilter">Hasta</label>
                                    <input type="date" id="dateToFilter" name="dateToFilter" class="form-control form-control-sm noEnterMyApp" autocomplete="off" value="" style="width:140px;">                
                                </div>                                   
                                <div class="form-group" style="width:auto; float:left; margin-right:10px;">
                                    <label for="typeIdFilter">Tipo</label>                                        
                                    <select id="typeIdFilter" name="typeIdFilter" class="form-control form-control-sm">                   
                                        <option value="" selected="selected">[Todos]</option>                       
                                        <?php                                                         
                                            for ($i=0; $i < count($notificationsTypes); $i++) {                                                                                           
                                        ?>
                                        <option value="<?php echo $notificationsTypes[$i]['id']; ?>"><?php echo $notificationsTypes[$i]['description'];?></option>
                                        <?php
                                            }
                                        ?>
                                    </select>                     
                                </div>  
                                <div class="form-group" style="width:auto; float:left; margin-right:10px;">
                                    <label for="readFilter">Leído</label>
                                    <select id="readFilter" name="readFilter" class="form-control form-control-sm">
                                        <?php 
                                            $readFilter = set_value('readFilter',$readFilter);
                                            
                                            $selected = ($readFilter == ""?' selected="selected" ':''); 
                                        ?>
                                        <option value="" <?php echo $selected; ?>>[Todos]</option>                       
                                        <?php                                                         
                                            for ($i=0; $i < count($readStates); $i++) {   
                                                $selected = ($readStates[$i]['id'] == $readFilter?' selected="selected" ':'');                                              
                                        ?>
                                        <option value="<?php echo $readStates[$i]['id']; ?>" <?php echo $selected; ?>><?php echo $readStates[$i]['description'];?></option>
                                        <?php
                                            }
                                        ?>
                                    </select>                 
                                </div>                                               
                            </div>                                  
                        </form>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <div id="prenotifications"></div>     
            <div style="margin-top:7px;" id="notifications"></div>                
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->    
</div>
<!-- /.container-fluid -->
<?php
/* End of file panel_notifications_view.php */
/* Location: ./application/views/panel/panel_notifications_view.php */