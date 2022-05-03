<?php echo $controlador->breadcrumbs; ?><br>

<div class="container">
    <div class='col-md-12'>
        <label>Nombre Emisor:</label>
        <?php echo $controlador->pago_cliente['pago_cliente_empresa_razon_social']; ?>
        <label>Folio Fiscal:</label>
        Vista Preliminar
        <label>RFC Emisor:</label>
        <?php echo $controlador->pago_cliente['pago_cliente_empresa_rfc']; ?>
        <label>No. de serie del CSD:</label>
        Vista Preliminar
        <label>Nombre Receptor:</label>
        <?php echo $controlador->pago_cliente['pago_cliente_cliente_razon_social']; ?>
        <label>Lugar, fecha y hora de emisión:</label>
        <?php echo $controlador->pago_cliente['pago_cliente_cp']; ?>
        <?php echo $controlador->pago_cliente['pago_cliente_fecha']; ?>
        <label>RFC Receptor:</label>
        <?php echo $controlador->pago_cliente['pago_cliente_cliente_rfc']; ?>
        <label>Efecto de comprobante:</label>
        P Pago
        <label>Folio y serie:</label>
        <?php echo $controlador->pago_cliente['pago_cliente_folio']; ?>
        <?php echo $controlador->pago_cliente['pago_cliente_serie']; ?>
        <label>Uso CFDI:</label>
        <?php echo $controlador->pago_cliente['pago_cliente_uso_cfdi_codigo']; ?>
        <?php echo $controlador->pago_cliente['pago_cliente_uso_cfdi_descripcion']; ?>
        <label>Régimen fiscal:</label>
        <?php echo $controlador->pago_cliente['pago_cliente_regimen_fiscal_codigo']; ?>
        <?php echo $controlador->pago_cliente['pago_cliente_regimen_fiscal_descripcion']; ?>
    </div>
    <hr>
    <div class="col-md-12 table-responsive">
        <h4>Conceptos:</h4>
        <table class='table table-bordered'>
            <thead>
            <tr>
                <th>Cve del producto/servicio</th>
                <th>No. identificación</th>
                <th>Cantidad</th>
                <th>Clave unidad</th>
                <th>Unidad</th>
                <th>Descripción</th>
                <th>Valor unitario</th>
                <th>Importe</th>
                <th>Descuento</th>
            </tr>
            </thead>
            <tbody>
                <tr>
                    <td>84111506</td>
                    <td></td>
                    <td>1</td>
                    <td>1</td>
                    <td>ACT</td>
                    <td>Pago</td>
                    <td>0</td>
                    <td>0</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
    <hr>
    <div class='col-md-12'>
        <label>Moneda:</label>
        Los codigos asignados para las transacciones en que intervenga ninguna moneda
    </div>
    <hr>
    <div class='col-md-4 col-md-offset-8 row'>
        <label>Subtotal:</label>
       0
    </div>
    <div class='col-md-4 col-md-offset-8 row'>
        <label>Total:</label>
       0
    </div>
    <div class="col-md-12">
        <hr>
        <label><h4>Informacion del pago:</h4></label>
        <div class="col-md-12">
            <form action="index.php?seccion=pago_cliente&accion=modifica_pago&session_id=<?php echo SESSION_ID; ?>&pago_cliente_id=<?php echo $controlador->pago_cliente_id; ?>" METHOD="POST">
            <label>Forma de pago:</label>
            <?php echo $controlador->pago_cliente['pago_cliente_forma_pago_codigo']; ?>
            <?php echo $controlador->pago_cliente['pago_cliente_forma_pago_descripcion']; ?>
            <label>RFC Emisor cuenta ordenante:</label>
            <?php echo $controlador->pago_cliente['pago_cliente_cliente_banco_rfc']; ?>
            <label>Moneda pago:</label>
            <?php echo $controlador->pago_cliente['pago_cliente_moneda_codigo']; ?>
            <?php echo $controlador->pago_cliente['pago_cliente_moneda_descripcion']; ?>
            <label>Cuenta ordenante:</label>
            <?php echo $controlador->pago_cliente['pago_cliente_cuenta_bancaria_cuenta']; ?>
            <label>Numero operacion:</label>
            <?php echo $controlador->pago_cliente['pago_cliente_numero_operacion']; ?>
            <label>Nombre banco cuenta ordenante:</label>
            <?php echo $controlador->pago_cliente['pago_cliente_cliente_banco_descripcion']; ?>
            <label>RFC Emisor cuenta beneficiario:</label>
            <?php echo $controlador->pago_cliente['pago_cliente_empresa_banco_rfc']; ?>
            <label>Cuenta beneficiario:</label>
            <?php echo $controlador->pago_cliente['pago_cliente_cuenta_bancaria_empresa_cuenta']; ?>
            <div class="col-md-12">
                <?php echo $directiva->fecha($controlador->pago_cliente['pago_cliente_fecha_pago'],4,'fecha_pago'); ?>
                <?php echo $directiva->genera_input_text('monto',4,
                    round($controlador->pago_cliente['pago_cliente_monto'],2),'required',
                    '',false,false,false); ?>
                <div class="col-md-12">
                    <input type="submit" class="btn" value="Modifica" />
                </div>

                </div>
            </form>

        </div>
        <div class="col-md-12">
            <hr>
            <label><h4>Documentos Relacionados:</h4></label><br>

            <?php
            foreach ($controlador->pago_cliente_factura as $pago){
                ?>
            <form action="index.php?seccion=pago_cliente&accion=modifica_pago_factura&pago_cliente_id=<?php echo $controlador->pago_cliente_id; ?>&session_id=<?php echo SESSION_ID; ?>&pago_cliente_factura_id=<?php echo $pago['pago_cliente_factura_id'] ?>" METHOD="POST">
                <label>Id documento:</label>
                <?php echo $pago['pago_cliente_factura_factura_uuid']; ?>
                <label>Folio:</label>
                <?php echo $pago['factura_folio']; ?>
                <label>Serie:</label>
                <?php echo $pago['factura_serie']; ?>
                <label>Numero de parcialidad:</label>
                <?php echo $pago['pago_cliente_factura_parcialidad']; ?>
                <label>Moneda del documento relacionado:</label>
                <?php echo $pago['factura_moneda_descripcion']; ?>
                <label>Metodo de pago del documento relacionado:</label>
                <?php echo $pago['factura_metodo_pago_descripcion']; ?>
                <label>Importe de saldo anterior:</label>
                <?php echo $pago['pago_cliente_factura_importe_saldo_anterior']; ?>
                <label>Importe de saldo insoluto:</label>
                <?php echo $pago['pago_cliente_factura_importe_saldo_insoluto']; ?>
                <br>
                <hr>
                <div class="col-md-12">
                    <?php echo $directiva->input_hidden('importe_saldo_anterior',$pago['pago_cliente_factura_importe_saldo_anterior']); ?>

                    <?php echo $directiva->genera_input_text('monto',4,
                        round($pago['pago_cliente_factura_monto'],2),'required',
                        '',false,false,false); ?>
                    <input type="submit" class="btn" value="Modifica" />
                </div>

            </form>
            <?php
            }
            ?>


        </div>
    </div>
</div>

<div class="col-md-12">
    <?php
    if($controlador->pago_cliente['pago_cliente_status_factura']=='sin timbrar' || $controlador->pago_cliente['pago_cliente_status_factura']=='' ){
        ?>
        <a href="./index.php?seccion=pago_cliente&accion=genera_xml&pago_cliente_id=<?php echo $controlador->pago_cliente_id; ?>">
            <button type="button" class="btn">Timbra Complemento</button>
        </a>

        <a href="./index.php?seccion=pago_cliente&accion=elimina_complemento&pago_cliente_id=<?php echo $controlador->pago_cliente_id; ?>">
            <button type="button" class="btn">Elimina Complemento</button>
        </a>

        <?php
    }
    if($controlador->pago_cliente['pago_cliente_status_factura']=='timbrado'){
        ?>
        <a href="./index.php?seccion=pago_cliente&accion=ve_pdf&pago_cliente_id=<?php echo $controlador->pago_cliente_id; ?>" download>
            <button type="button" class="btn">Descarga PDF</button>
        </a>
        <a href="./index.php?seccion=pago_cliente&accion=descarga_xml&pago_cliente_id=<?php echo $controlador->pago_cliente_id; ?>" download>
            <button type="button" class="btn">Descarga XML</button>
        </a>

        <a href="./index.php?seccion=pago_cliente&accion=cancela_complemento_pago&pago_cliente_id=<?php echo $controlador->pago_cliente_id; ?>">
            <button type="button" class="btn">Cancela Complemento</button>
        </a>

        <?php
    }
    ?>
</div>