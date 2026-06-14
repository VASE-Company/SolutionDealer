<div class="container-fluid">
    <!-- Info boxes -->
    <div class="row">
        <?php if (isset($ordersSummary)) { ?>

        <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box">
                <span class="info-box-icon elevation-1" style="background-color:<?php echo $ordersSummary['autorizateOrders']['color']; ?>"><i class="<?php echo $ordersSummary['autorizateOrders']['icon']; ?>"></i></span>

                <div class="info-box-content">
                    <span class="info-box-text">PEDIDOS PEND. AUTORIZAR</span>               
                    <span class="info-box-number">
                        <span style="font-weight:normal;">Total:</span> 
                        <?php echo $ordersSummary['autorizateOrders']['totalCount']; ?>                   
                    </span>                    
                    <span class="info-box-number">
                        <span style="font-weight:normal;">Pendiente Validar:</span> 
                        <?php echo $ordersSummary['autorizateOrders']['toAutorizateCount']; ?>
                        <?php if ($ordersSummary['autorizateOrders']['toAutorizateCount'] > 0) { ?>
                        <a href="<?php echo $ordersSummary['autorizateOrders']['toAutorizateLink']; ?>" class="small-box-footer" style="color:<?php echo $ordersSummary['autorizateOrders']['color']; ?>;margin-left:5px;"><i class="fas fa-arrow-circle-right" style="color:<?php echo $ordersSummary['autorizateOrders']['color']; ?>"></i></a>
                        <?php } ?>
                    </span> 
                    <span class="info-box-number">
                        <span style="font-weight:normal;">En Validación:</span> 
                        <?php echo $ordersSummary['autorizateOrders']['validatingAutorizateCount']; ?>
                        <?php if ($ordersSummary['autorizateOrders']['validatingAutorizateLink'] > 0) { ?>
                        <a href="<?php echo $ordersSummary['autorizateOrders']['validatingAutorizateLink']; ?>" class="small-box-footer" style="color:<?php echo $ordersSummary['autorizateOrders']['color']; ?>;margin-left:5px;"><i class="fas fa-arrow-circle-right" style="color:<?php echo $ordersSummary['autorizateOrders']['color']; ?>"></i></a>
                        <?php } ?>
                    </span> 
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
        <!-- /.col -->

        <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box">
                <span class="info-box-icon elevation-1" style="background-color:<?php echo $ordersSummary['managerOrders']['color']; ?>"><i class="<?php echo $ordersSummary['managerOrders']['icon']; ?>"></i></span>

                <div class="info-box-content">
                    <span class="info-box-text">PEDIDOS PEND. RESOLUCIÓN</span>               
                    <span class="info-box-number">
                        <span style="font-weight:normal;">Total:</span> 
                        <?php echo $ordersSummary['managerOrders']['totalCount']; ?>
                    </span>                    
                    <span class="info-box-number">
                        <span style="font-weight:normal;">Pendiente Gestión:</span> 
                        <?php echo $ordersSummary['managerOrders']['pendingManagerCount']; ?>
                        <?php if ($ordersSummary['managerOrders']['pendingManagerCount'] > 0) { ?>
                        <a href="<?php echo $ordersSummary['managerOrders']['pendingManagerLink']; ?>" class="small-box-footer" style="color:<?php echo $ordersSummary['managerOrders']['color']; ?>;margin-left:5px;"><i class="fas fa-arrow-circle-right" style="color:<?php echo $ordersSummary['managerOrders']['color']; ?>"></i></a>
                        <?php } ?>
                    </span> 
                    <span class="info-box-number">
                        <span style="font-weight:normal;">Gestionándose:</span> 
                        <?php echo $ordersSummary['managerOrders']['managingCount']; ?>
                        <?php if ($ordersSummary['managerOrders']['managingCount'] > 0) { ?>
                        <a href="<?php echo $ordersSummary['managerOrders']['managingLink']; ?>" class="small-box-footer" style="color:<?php echo $ordersSummary['managerOrders']['color']; ?>;margin-left:5px;"><i class="fas fa-arrow-circle-right" style="color:<?php echo $ordersSummary['managerOrders']['color']; ?>"></i></a>
                        <?php } ?>
                    </span> 
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
        <!-- /.col -->        

        <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box">
                <span class="info-box-icon elevation-1" style="background-color:<?php echo $ordersSummary['notFinalizeOrders']['color']; ?>"><i class="<?php echo $ordersSummary['notFinalizeOrders']['icon']; ?>"></i></span>

                <div class="info-box-content">
                    <span class="info-box-text">PEDIDOS PEND. FINALIZAR</span>               
                    <span class="info-box-number">
                        <span style="font-weight:normal;">Total:</span> 
                        <?php echo $ordersSummary['notFinalizeOrders']['totalCount']; ?>
                        <?php if ($ordersSummary['notFinalizeOrders']['totalCount'] > 0) { ?>
                        <a href="<?php echo $ordersSummary['notFinalizeOrders']['totalLink']; ?>" class="small-box-footer" style="color:<?php echo $ordersSummary['notFinalizeOrders']['color']; ?>;margin-left:5px;"><i class="fas fa-arrow-circle-right" style="color:<?php echo $ordersSummary['notFinalizeOrders']['color']; ?>"></i></a>
                        <?php } ?>
                    </span>                               
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
        <!-- /.col -->
        
        <?php } ?>        
    </div>
    <!-- /.row --> 
</div>
<?php 
    //include_once("panel_notifications_view.php");
?>
<?php
/* End of file panel_view.php */
/* Location: ./application/views/panel/panel_view.php */