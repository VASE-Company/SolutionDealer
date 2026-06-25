<?php
   $data = $data['data'];
?>
<div class="card">
    <div class="card-body">
        <div class="row">
            <label class="col-form-label col-form-label-sm col-xs-12 col-sm-12 col-md-12 col-lg-12">
            * Se mostraran los primeros 50 resultados, para ver el informe completo debe exportarlo.
            <?php if (isset($data) && count($data) > 0 && $allowExport) { ?>
            <a href="javascript:generalExport('reports/export','<?php echo $filterExportGet; ?>');" class="btn without-padding" title="exportar">
                <i class="fas fa-download"></i>
            </a>
            <?php
                }
            ?>
            </label>
        </div>
        <div class="row">
            <div class="col-sm-12" style="overflow:auto;">
                <table id="grData" class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th nowrap="nowrap"></th>
                            <th nowrap="nowrap">Fecha Pedido</th>
                            <th nowrap="nowrap">Empresa</th>
                            <th nowrap="nowrap">Sucursal</th>
                            <th nowrap="nowrap">Sector</th>
                            <th nowrap="nowrap">Estado</th>
                            <th nowrap="nowrap">Rubro</th>
                            <th nowrap="nowrap">Codigo</th>
                            <th nowrap="nowrap">Articulo</th>
                            <th nowrap="nowrap">Cantidad</th>
                            <th nowrap="nowrap">Entregado</th>
                            <th nowrap="nowrap">Cancelado</th>
                            <th nowrap="nowrap">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if (isset($data)) {
                                for ($i=0; $i < count($data); $i++) {
                        ?>
                        <tr ondblclick="onClick('btnEditReportOrder<?php echo $data[$i]['orderId']; ?>_<?php echo $data[$i]['detailOrderId']; ?>')">
                            <td nowrap="nowrap" class="text-center">
                                <a id="btnEditReportOrder<?php echo $data[$i]['orderId']; ?>_<?php echo $data[$i]['detailOrderId']; ?>" href="<?php echo base_url(); ?>orders/edit/<?php echo $data[$i]['orderId']; ?>" title="editar/ver pedido">
                                    <i class="far fa-edit"></i>
                                </a>
                            </td>
                            <td nowrap="nowrap"><?php echo dateFormat($data[$i]['orderDate'],false); ?></td>
                            <td nowrap="nowrap"><?php echo $data[$i]['companyDescription']; ?></td>
                            <td nowrap="nowrap"><?php echo $data[$i]['branchOfficeDescription']; ?></td>
                            <td nowrap="nowrap"><?php echo $data[$i]['sectorDescription']; ?></td>
                            <td nowrap="nowrap"><?php echo $data[$i]['stateDescription']; ?></td>
                            <td nowrap="nowrap"><?php echo $data[$i]['familyDescription']; ?></td>
                            <td nowrap="nowrap"><?php echo $data[$i]['articleCode']; ?></td>
                            <td nowrap="nowrap"><?php echo $data[$i]['articleDescription']; ?></td>
                            <td nowrap="nowrap" class="text-center"><?php echo (float)$data[$i]['quantity']; ?></td>
                            <td nowrap="nowrap" class="text-center"><?php echo (float)$data[$i]['deliveredQuantity']; ?></td>
                            <td nowrap="nowrap" class="text-center"><?php echo (float)$data[$i]['canceledQuantity']; ?></td>
                            <td nowrap="nowrap" class="text-right"><?php echo decimalFormat($data[$i]['total'],2); ?></td>
                        </tr>
                        <?php
                                }
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->
<?php
/* End of file reports_articlesbyorder_view.php */
/* Location: ./application/views/reports/reports_articlesbyorder_view.php */
