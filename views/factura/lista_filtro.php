<?php
        foreach ($controlador->registros as $factura) {
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
            elseif($factura['factura_status_factura'] === 'Timbrada/Pagada'){
                $status_factura = 3;
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
                <td><?php echo $factura['factura_status_factura']; ?></td>
                <td><?php echo $factura['factura_status_descarga']; ?></td>
                <td><?php echo $factura['factura_saldo']?></td>
                <td>
                    <?php echo $directiva->link_ve_factura_pdf('cliente',$factura['factura_id'],$status_factura); ?>
                    <?php if($status_factura != 0){ ?>
                        <?php echo $directiva->link_descarga_factura_pdf('cliente',$factura['factura_id']); ?>
                        <?php echo $directiva->link_descarga_factura_xml('cliente',$factura['factura_id'],'XML_'.$factura['factura_referencia']); ?>
                    <?php } ?>
                    <?php if($status_factura != 2){ ?>
                        <?php if($status_factura == 1){ ?>
                            <?php echo $directiva->link_ve_pagos($factura['factura_id']); ?>
                        <?php } ?>
                        <input type="checkbox" class="factura" value="<?php echo $factura['factura_id']; ?>">

                    <?php } ?>
                </td>
            </tr>
            <?php
        }
        ?>