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
                            <th nowrap="nowrap">Rubro</th>
                            <th nowrap="nowrap">Articulo</th>
                            <th nowrap="nowrap">Plazo</th>
                            <th nowrap="nowrap">Fecha Plazo</th>
                            <th nowrap="nowrap">Demora</th>
                            <th nowrap="nowrap">Estado Pedido</th>
                            <th nowrap="nowrap">Estado Entrega</th>
                            <th nowrap="nowrap">Cantidad</th>
                            <th nowrap="nowrap">Situacion</th>
                            <th nowrap="nowrap">Nro Orden</th>
                            <th nowrap="nowrap">Remitos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if (isset($data)) {
                                for ($i=0; $i < count($data); $i++) {
                                    $quantityDescription = (int)$data[$i]['deliveredQuantity']."/".(int)$data[$i]['requestedQuantity'];
                                    $maximumDate = (trim($data[$i]['maximumDate']) != ""?dateFormat($data[$i]['maximumDate'],false):"");
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
                            <td nowrap="nowrap"><?php echo $data[$i]['familyDescription']; ?></td>
                            <td nowrap="nowrap"><?php echo $data[$i]['articleDescription']; ?></td>
                            <td nowrap="nowrap" class="text-center"><?php echo $data[$i]['maximumDays']; ?></td>
                            <td nowrap="nowrap" class="text-center"><?php echo $maximumDate; ?></td>
                            <td nowrap="nowrap"><?php echo $data[$i]['delayDescription']; ?></td>
                            <td nowrap="nowrap"><?php echo $data[$i]['stateDescription']; ?></td>
                            <td nowrap="nowrap"><?php echo $data[$i]['deliveryStateDescription']; ?></td>
                            <td nowrap="nowrap" class="text-center"><?php echo $quantityDescription; ?></td>
                            <td nowrap="nowrap"><?php echo $data[$i]['dueSituationDescription']; ?></td>
                            <td nowrap="nowrap"><?php echo $data[$i]['purchaseOrderNumber']; ?></td>
                            <td nowrap="nowrap"><?php echo $data[$i]['deliveryNotes']; ?></td>
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
/* End of file reports_partialdeliveries_view.php */
/* Location: ./application/views/reports/reports_partialdeliveries_view.php */
