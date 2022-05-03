<?php echo $controlador->breadcrumbs; ?><br>
<div class="col-md-12 table-responsive">
    <table class="table table-striped" id="cliente-ve-facturas">
        <thead>
        <tr>
            <th>#</th>
            <th>Fecha</th>
            <th>RFC</th>
            <th>Razón Social</th>
            <th>Folio Fiscal</th>
            <th>Sub Total</th>
            <th>Impuestos Trasladados</th>
            <th>Impuestos Retenidos</th>
            <th>Total</th>
            <th>Status</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($controlador->registros as $factura) {
            $status_factura = false;
            if($factura['factura_status_factura'] == 'sin timbrar') {
                $status_factura = 0;
            }
            elseif($factura['factura_status_factura'] == 'timbrado'){
                $status_factura = 1;
            }

            ?>
            <tr>
                <td><?php echo $factura['factura_folio']; ?></td>
                <td><?php echo $factura['factura_fecha']; ?></td>
                <td><?php echo $factura['factura_cliente_rfc']; ?></td>
                <td><?php echo $factura['factura_cliente_razon_social']; ?></td>
                <td><?php echo $factura['factura_uuid']; ?></td>
                <td>$<?php echo number_format($factura['factura_sub_total'],2,'.',","); ?></td>
                <td>$<?php echo number_format($factura['factura_total_impuestos_trasladados'],2,".",","); ?></td>
                <td>$<?php echo number_format($factura['factura_total_impuestos_retenidos'],2,".",","); ?></td>
                <td>$<?php echo number_format($factura['factura_total'],2,'.',','); ?></td>
                <td><?php echo $factura['factura_status_factura']; ?></td>
                <td>
                    <?php echo $directiva->link_ve_factura_pdf('cliente',$factura['factura_id'],$status_factura); ?>
                    <?php echo $directiva->link_descarga_factura_pdf('cliente',$factura['factura_id']); ?>
                    <?php echo $directiva->link_descarga_factura_xml('cliente',$factura['factura_id']); ?>
                    <?php echo $directiva->link_paga_factura('cliente',$factura['factura_id']); ?>
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</div>