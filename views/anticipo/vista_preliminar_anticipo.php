<?php echo $controlador->breadcrumbs; ?><br>

<div class="container">
    <div class='col-md-12'>
        <label>Nombre Emisor:</label>
        <?php echo $controlador->anticipo['nombre_empresa']; ?>
        <label>Folio Fiscal:</label>
        Vista Preliminar
        <label>RFC Emisor:</label>
        <?php echo $controlador->anticipo['rfc']; ?>
        <label>No. de serie del CSD:</label>
        Vista Preliminar
        <label>Nombre Receptor:</label>
        <?php echo $controlador->anticipo['cliente_razon_social']; ?>
        <label>Lugar, fecha y hora de emisión:</label>
        <?php echo $controlador->anticipo['cp']; ?>
        <?php echo $controlador->anticipo['anticipo_fecha']; ?>
        <label>RFC Receptor:</label>
        <?php echo $controlador->anticipo['cliente_rfc']; ?>
        <label>Efecto de comprobante:</label>
        I Anticipo
        <label>Folio y serie:</label>
        <?php echo $controlador->anticipo['anticipo_folio']; ?>
        <?php echo $controlador->anticipo['serie']; ?>
        <label>Uso CFDI:</label>
        <?php echo $controlador->anticipo['uso_cfdi_codigo']; ?>
        <?php echo $controlador->anticipo['uso_cfdi_descripcion']; ?>
        <label>Régimen fiscal:</label>
        <?php echo $controlador->anticipo['regimen_fiscal']; ?>
        <?php echo $controlador->anticipo['regimen_fiscal_descripcion']; ?>
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
                    <td><?php echo $controlador->anticipo['producto_codigo']; ?></td>
                    <td></td>
                    <td><?php echo $controlador->anticipo['producto_cantidad']; ?></td>
                    <td></td>
                    <td><?php echo $controlador->anticipo['unidad_codigo']; ?></td>
                    <td><?php echo $controlador->anticipo['insumo_descripcion']; ?></td>
                    <td><?php echo $controlador->anticipo['anticipo_monto']; ?></td>
                    <td><?php echo $controlador->anticipo['anticipo_monto']; ?></td>
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
                    <td><?php echo $controlador->anticipo['impuesto_descripcion']; ?></td>
                    <td><?php echo $controlador->anticipo['tipo_impuesto_descripcion']; ?></td>
                    <td><?php echo $controlador->anticipo['anticipo_monto']; ?></td>
                    <td><?php echo $controlador->anticipo['tipo_factor_descripcion']; ?></td>
                    <td><?php echo $controlador->anticipo['anticipo_porcentaje_iva']; ?></td>
                    <td><?php echo $controlador->anticipo['anticipo_monto_iva']; ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>



    <hr>
    <div class='col-md-12'>
        <label>Moneda:</label>
        <?php echo $controlador->anticipo['moneda_codigo']; ?>
        <?php echo $controlador->anticipo['moneda_descripcion']; ?>
    </div>
    <hr>
    <div class='col-md-4 col-md-offset-8 row'>
        <label>Subtotal:</label>
        <?php echo $controlador->anticipo['anticipo_monto']; ?>
    </div>
    <div class='col-md-4 col-md-offset-8 row'>
        <label>Impuestos Trasladados:</label>
        <?php echo $controlador->anticipo['anticipo_monto_iva']; ?>
    </div>
    <div class='col-md-4 col-md-offset-8 row'>
        <label>Total:</label>
        <?php echo $controlador->anticipo['anticipo_total']; ?>
    </div>
    <div class="col-md-12">
        <hr>
        <label><h4>Informacion del pago:</h4></label>
        <div class="col-md-12">
            <label>Forma de pago:</label>
            <?php echo $controlador->anticipo['forma_pago_codigo']; ?>
            <?php echo $controlador->anticipo['forma_pago_descripcion']; ?>

        </div>
    </div>
</div>

<div class="col-md-12">

        <a href="./index.php?seccion=anticipo&accion=genera_xml&anticipo_id=<?php echo $controlador->anticipo_id; ?>">
            <button type="button" class="btn">Timbra Anticipo</button>
        </a>
        <?php

    if($controlador->anticipo['anticipo_status_anticipo']=='timbrado'){
        ?>
        <a href="./index.php?seccion=anticipo&accion=ve_pdf&anticipo_id=<?php echo $controlador->anticipo_id; ?>" download>
            <button type="button" class="btn">Descarga PDF</button>
        </a>
        <a href="./index.php?seccion=anticipo&accion=descarga_xml&anticipo_id=<?php echo $controlador->anticipo_id; ?>" download>
            <button type="button" class="btn">Descarga XML</button>
        </a>
        <?php
    }
    ?>
</div>