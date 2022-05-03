    <div class="col-md-12" id="cliente-selector">
        <?php
        echo $directiva->input_select('cliente',$controlador->cliente_id,12,'disabled',false,false,$controlador->link,'required',false);
        ?>
    </div>


    <div class="col-md-12 table-responsive">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>RFC</th>
                <th>Razón Social</th>
                <th>Folio Fiscal</th>
                <th>Total</th>
                <th>Status</th>
                <th>Status Descarga</th>
                <th>Saldo</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody id='contenido'>
            <?php
            foreach ($controlador->facturas as $factura) {
                $status_factura = false;
                if($factura['factura_status_factura'] == 'sin timbrar') {
                    $status_factura = 0;
                }
                elseif($factura['factura_status_factura'] == 'timbrada'){
                    $status_factura = 1;
                }
                elseif($factura['factura_status_factura'] == 'cancelado'){
                    $status_factura = 2;
                }

                $clase_status = '';
                if($status_factura == 2){
                    $clase_status = 'class="danger"';
                }

                ?>
                <tr <?php echo $clase_status; ?>>
                    <td><?php echo $factura['factura_folio']; ?></td>
                    <td><?php echo $factura['factura_fecha']; ?></td>
                    <td><?php echo $factura['factura_cliente_rfc']; ?></td>
                    <td><?php echo $factura['factura_cliente_razon_social']; ?></td>
                    <td><?php echo $factura['factura_uuid']; ?></td>
                    <td>$<?php echo number_format($factura['factura_total'],2,'.',','); ?></td>
                    <td>
                        <?php echo $factura['factura_status_factura']; ?>
                    </td>
                    <td><?php echo $factura['factura_status_descarga']; ?></td>
                    <td><?php echo $factura['factura_saldo']?></td>
                    <td>
                        <?php if($status_factura != 2){ ?>
                            <?php echo $directiva->link_cancela_factura_opcion($factura['factura_id']); ?>
                        <?php } ?>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
    </div>
