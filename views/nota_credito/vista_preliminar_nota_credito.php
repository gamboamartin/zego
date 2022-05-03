<?php echo $controlador->breadcrumbs; ?><br>

<div class="container">
    <div class='col-md-12'>
        <label>Nombre Emisor:</label>
        <?php echo $controlador->nota_credito['nombre_empresa']; ?>
        <label>Folio Fiscal:</label>
        Vista Preliminar
        <label>RFC Emisor:</label>
        <?php echo $controlador->nota_credito['rfc']; ?>
        <label>No. de serie del CSD:</label>
        Vista Preliminar
        <label>Nombre Receptor:</label>
        <?php echo $controlador->nota_credito['cliente_razon_social']; ?>
        <label>Lugar, fecha y hora de emisión:</label>
        <?php echo $controlador->nota_credito['cp']; ?>
        <?php echo $controlador->nota_credito['nota_credito_fecha']; ?>
        <label>RFC Receptor:</label>
        <?php echo $controlador->nota_credito['cliente_rfc']; ?>
        <label>Efecto de comprobante:</label>
        I Anticipo
        <label>Folio y serie:</label>
        <?php echo $controlador->nota_credito['nota_credito_folio']; ?>
        <?php echo $controlador->nota_credito['serie']; ?>
        <label>Uso CFDI:</label>
        <?php echo $controlador->nota_credito['uso_cfdi_codigo']; ?>
        <?php echo $controlador->nota_credito['uso_cfdi_descripcion']; ?>
        <label>Régimen fiscal:</label>
        <?php echo $controlador->nota_credito['regimen_fiscal']; ?>
        <?php echo $controlador->nota_credito['regimen_fiscal_descripcion']; ?>


        <?php
        if($controlador->nota_credito['factura_id'] !=''){ ?>
            <br>

            <label>CFDI Relacionado:</label>
            <?php echo $controlador->nota_credito['factura_id']; ?>
            <?php echo $controlador->nota_credito['factura_uuid']; ?>
            <label>CFDI Relacionado Monto:</label>
            <?php echo $controlador->nota_credito['factura_total']; ?>
            <label>Tipo Relacion:</label>
            01 CFDI Nota de Credito de los documentos relacionados

        <?php }
        else{ ?>
            <label>NO hay Anticipos Aplicados</label>
        <?php }
        ?>

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
                    <td><?php echo $controlador->nota_credito['producto_codigo']; ?></td>
                    <td></td>
                    <td><?php echo $controlador->nota_credito['producto_cantidad']; ?></td>
                    <td></td>
                    <td><?php echo $controlador->nota_credito['unidad_codigo']; ?></td>
                    <td><?php echo $controlador->nota_credito['insumo_descripcion']; ?></td>
                    <td><?php echo $controlador->nota_credito['nota_credito_monto']; ?></td>
                    <td><?php echo $controlador->nota_credito['nota_credito_monto']; ?></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <div class="col-md-12 table-responsive">
            <h5>Impuestos:</h5>
            <table class='table table-bordered'>
                <thead>
                <tr>
                    <th>Impuesto</th>
                    <th>Tipo</th>
                    <th>Base</th>
                    <th>Tipo Factor</th>
                    <th>Tasa o Cuota</th>
                    <th>Importe</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><?php echo $controlador->nota_credito['impuesto_descripcion']; ?></td>
                    <td><?php echo $controlador->nota_credito['tipo_impuesto_descripcion']; ?></td>
                    <td><?php echo $controlador->nota_credito['nota_credito_monto']; ?></td>
                    <td><?php echo $controlador->nota_credito['tipo_factor_descripcion']; ?></td>
                    <td><?php echo $controlador->nota_credito['nota_credito_porcentaje_iva']; ?></td>
                    <td><?php echo $controlador->nota_credito['nota_credito_monto_iva']; ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>



    <hr>
    <div class='col-md-12'>
        <label>Moneda:</label>
        <?php echo $controlador->nota_credito['moneda_codigo']; ?>
        <?php echo $controlador->nota_credito['moneda_descripcion']; ?>
    </div>
    <hr>
    <div class='col-md-4 col-md-offset-8 row'>
        <label>Subtotal:</label>
        <?php echo $controlador->nota_credito['nota_credito_monto']; ?>
    </div>
    <div class='col-md-4 col-md-offset-8 row'>
        <label>Impuestos Trasladados:</label>
        <?php echo $controlador->nota_credito['nota_credito_monto_iva']; ?>
    </div>
    <div class='col-md-4 col-md-offset-8 row'>
        <label>Total:</label>
        <?php echo $controlador->nota_credito['nota_credito_total']; ?>
    </div>
    <div class="col-md-12">
        <hr>
        <label><h4>Informacion del pago:</h4></label>
        <div class="col-md-12">
            <label>Forma de pago:</label>
            <?php echo $controlador->nota_credito['forma_pago_codigo']; ?>
            <?php echo $controlador->nota_credito['forma_pago_descripcion']; ?>

        </div>
    </div>
</div>

<div class="col-md-12">

        <a href="./index.php?seccion=nota_credito&accion=genera_xml&nota_credito_id=<?php echo $controlador->nota_credito_id; ?>">
            <button type="button" class="btn">Timbra Nota de Credito</button>
        </a>
        <?php

    if($controlador->nota_credito['nota_credito_status_nota_credito']=='timbrado'){
        ?>
        <a href="./index.php?seccion=nota_credito&accion=ve_pdf&nota_credito_id=<?php echo $controlador->nota_credito_id; ?>" download>
            <button type="button" class="btn">Descarga PDF</button>
        </a>
        <a href="./index.php?seccion=nota_credito&accion=descarga_xml&nota_credito_id=<?php echo $controlador->nota_credito_id; ?>" download>
            <button type="button" class="btn">Descarga XML</button>
        </a>
        <?php
    }
    ?>
</div>