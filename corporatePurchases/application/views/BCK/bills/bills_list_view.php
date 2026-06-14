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
                        <form action="<?php echo base_url(); ?>bills/search" method="post" accept-charset="utf-8" id="frmFilter" name="frmFilter" role="form">                                                                                        
                            <div class="row">
                                <div class="col-12">                                                                        
                                    <div class="form-group" style="width:200px; float:left; margin-right:10px;">
                                        <label for="textFilter">Buscar</label>                                
                                        <input class="form-control form-control-sm" type="text" maxlength="15" placeholder="Buscar" id="textFilter" name="textFilter" autocomplete="off" value="<?php echo set_value('textFilter',$textFilter); ?>">
                                    </div>     
                                    <div class="form-group" style="width:auto; float:left; margin-right:10px;">
                                        <label for="stateIdFilter">Estado</label>                                        
                                        <select id="stateIdFilter" name="stateIdFilter" class="form-control form-control-sm">
                                            <?php 
                                                $stateIdFilter = set_value('stateIdFilter',$stateIdFilter);
                                                
                                                $selected = ($stateIdFilter == ""?' selected="selected" ':''); 
                                            ?>
                                            <option value="" <?php echo $selected; ?>>[Todos]</option>                       
                                            <?php                                                         
                                                for ($i=0; $i < count($states); $i++) {   
                                                    $selected = ($states[$i]['id'] == $stateIdFilter?' selected="selected" ':'');                                              
                                            ?>
                                            <option value="<?php echo $states[$i]['id']; ?>" <?php echo $selected; ?>><?php echo $states[$i]['description'];?></option>
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
                    <?php if ($allowInsert) { ?>
                    <div class="row">
                        <div class="col-12">                                                                        
                            <button type="button" class="btn btn-sm btn-success type-btn-save" onclick="sendMailPendingBills()" title="envia mail de aviso de facturas pendientes de pago" style="margin-bottom:10px;"><i class="fa fa-envelope" style="margin-right:5px;"></i> Pagos Pendientes</button>                                            
                        </div>
                    </div>
                    <?php } ?>
                    <div class="row">
                        <div class="col-sm-12">
                            <table id="grBills" class="table table-bordered table-hover table-sm">
                                <thead>
                                    <tr>                                
                                        <th class="text-center" nowrap="nowrap" width="<?php echo ($allowDelete?"55px":"35px"); ?>">
                                            <?php if ($allowInsert) { ?>
                                            <a href="<?php echo base_url(); ?>bills/edit/0" class="btn without-padding" title="nuevo">
                                                <i class="far fa-plus-square"></i>
                                            </a>                                                                                      
                                            <?php } ?>                                                                    
                                        </th>    
                                        <th nowrap="nowrap">Fecha  
                                            <?php echo generateOrderButton(true,($fieldOrder == 'date' && $typeOrder == 'asc'),'bills/listing/1?fOrd=date&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'date' && $typeOrder == 'desc'),'bills/listing/1?fOrd=date&tOrd=desc'.$filterGet); ?>                       
                                        </th>    
                                        <th nowrap="nowrap">Tipo</th>                                         
                                        <th nowrap="nowrap">Comprobante  
                                            <?php echo generateOrderButton(true,($fieldOrder == 'num' && $typeOrder == 'asc'),'bills/listing/1?fOrd=num&tOrd=asc'.$filterGet); ?>
                                            <?php echo generateOrderButton(false,($fieldOrder == 'num' && $typeOrder == 'desc'),'bills/listing/1?fOrd=num&tOrd=desc'.$filterGet); ?>                       
                                        </th>  
                                        <th nowrap="nowrap">Descripción</th>                                                                          
                                        <th nowrap="nowrap">Importe</th>  
                                        <th nowrap="nowrap">Estado</th> 
                                        <th class="text-center" nowrap="nowrap" width="35px"></th>                                      
                                    </tr>
                                </thead>
                                <?php if (isset($bills)) { ?>
                                <tbody>
                                    <?php
                                        for($i=0; $i < count($bills); $i++) {                                            
                                    ?>
                                    <tr ondblclick="onClick('btnEdit<?php echo $bills[$i]['id']; ?>')">                                
                                        <td class="text-center" nowrap="nowrap">                            
                                            <a href="<?php echo base_url(); ?>bills/edit/<?php echo $bills[$i]['id']; ?>" class="btn without-padding" title="<?php echo ($allowEdit?"editar":"ver"); ?>" id="btnEdit<?php echo $bills[$i]['id']; ?>">
                                                <i class="far fa-edit"></i>
                                            </a>
                                            <?php if ($allowDelete) { ?>
                                            <button type="button" class="btn without-padding" onclick="generalDelete(<?php echo $bills[$i]['id']; ?>,'bills/delete')" title="eliminar">
                                                <i class="far fa-minus-square"></i>
                                            </button>                                            
                                            <?php } ?>                                    
                                        </td>  
                                        <td><?php echo dateFormat($bills[$i]['date'],false); ?></td> 
                                        <td nowrap="nowrap"><?php echo $bills[$i]['typeDescription']; ?></td>   
                                        <td nowrap="nowrap"><?php echo $bills[$i]['letter']." ".$bills[$i]['serie']." ".$bills[$i]['number']; ?></td>   
                                        <td nowrap="nowrap"><?php echo $bills[$i]['description']; ?></td> 
                                        <td class="text-right" nowrap="nowrap">$ <?php echo decimalFormat($bills[$i]['amount'],2); ?></td>                                         
                                        <td nowrap="nowrap">
                                            <?php 
                                                echo $bills[$i]['stateDescription']; 
                                                if ($bills[$i]['stateId'] == 'PAY' && dateFormat($bills[$i]['paymentDate'],false) != "") {
                                                    echo " (".dateFormat($bills[$i]['paymentDate'],false).")"; 
                                                }
                                            ?>                                                
                                        </td> 
                                        <td nowrap="nowrap" class="text-center">   
                                            <?php
                                                $path = $this->config->item('files').$this->session->userdata('companyId').'/bills/bill_'.$bills[$i]['id'].'.pdf';                                                                                                
                                                if (file_exists($path)) {
                                            ?>
                                            <a href="<?php echo base_url(); ?>bills/download/<?php echo $bills[$i]['id']; ?>" class="btn without-padding" title="descargar" alt="_blank">
                                                <i class="fas fa-download"></i>
                                            </a> 
                                            <?php                                                    
                                                }
                                            ?>                                                                                 
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
                    <?php
                        if ($pagination != "") {
                    ?>
                    <div class="row">
                        <div class="col-sm-12">
                        <?php echo $pagination; ?>        
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
/* End of file bills_list_view.php */
/* Location: ./application/views/bills/bills_list_view.php */