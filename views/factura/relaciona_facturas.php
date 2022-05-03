<form action="index.php?seccion=factura&accion=relaciona_factura_bd&session_id=<?php echo SESSION_ID; ?>" method="POST">
<div class="row">
<?php
echo $directiva->input_select('cliente',$controlador->factura['cliente_id'],6,'disabled',false,
    false,$controlador->link,'',false);
echo $directiva->input_select('tipo_relacion',false,6,false,false,
    false,$controlador->link,'',false);
echo $directiva->input_hidden('factura_id',$controlador->factura['factura_id']);

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
                        <?php if($factura['factura_uuid_cancelacion']!=-1){ ?>
                            <a href="./index.php?seccion=factura&accion=obten_pdf_cancelado&factura_id=<?php echo $factura['factura_id']; ?>">
                                <?php echo $factura['factura_uuid_cancelacion']; ?>
                            </a>
                        <?php } ?>
                    </td>

                    <td><?php echo $factura['factura_saldo']?></td>
                    <td>

                        <input type="checkbox" class="factura" name="facturas_id[]" value="<?php echo $factura['factura_id']; ?>">

                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
    </div>

    <div class="row">
        <?php
        echo $directiva->btn_enviar(12,'Relaciona Facturas','relaciona-factura');
        ?>
    </div>
</form>
