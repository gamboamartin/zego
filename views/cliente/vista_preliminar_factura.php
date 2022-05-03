<?php echo $controlador->breadcrumbs; ?><br>
<div class="container">
    <div class='col-md-12'>
        <label>Nombre Emisor:</label>
        <?php echo $controlador->datos_emisor['Nombre']; ?>
        <label>Folio Fiscal:</label>
        Vista Preliminar
        <label>RFC Emisor:</label>
        <?php echo $controlador->datos_emisor['Rfc']; ?>
        <label>No. de serie del CSD:</label>
        Vista Preliminar
        <label>Nombre Receptor:</label>
        <?php echo $controlador->datos_receptor['Nombre']; ?>
        <label>Lugar, fecha y hora de emisión:</label>
        <?php echo $controlador->datos_comprobante['LugarExpedicion']; ?>
        <?php echo $controlador->datos_comprobante['Fecha']; ?>
        <label>RFC Receptor:</label>
        <?php echo $controlador->datos_receptor['Rfc']; ?>
        <label>Efecto de comprobante:</label>
        <?php echo $controlador->datos_comprobante['TipoDeComprobante']; ?>
        <?php echo $controlador->datos_comprobante['TipoDeComprobanteDescripcion']; ?>
        <label>Folio y serie:</label>
        <?php echo $controlador->datos_comprobante['Folio']; ?>
        <?php echo $controlador->datos_comprobante['Serie']; ?>
        <label>Uso CFDI:</label>
        <?php echo $controlador->datos_receptor['UsoCFDI']; ?>
        <?php echo $controlador->datos_receptor['UsoCFDIDescripcion']; ?>
        <label>Régimen fiscal:</label>
        <?php echo $controlador->datos_emisor['RegimenFiscal']; ?>
        <?php echo $controlador->datos_emisor['RegimenFiscalDescripcion']; ?>
        <?php
        if($controlador->factura['anticipo_id'] !=''){ ?>
        <br>
        <label>CFDI Relacionado:</label>
        <?php echo $controlador->factura['anticipo_id']; ?>
        <?php echo $controlador->factura['anticipo_uuid']; ?>
        <label>CFDI Relacionado Monto:</label>
        <?php echo $controlador->factura['anticipo_monto']; ?>
        <label>Tipo Relacion:</label>
        07 CFDI Por Aplicacion de anticipo

        <?php }
        else{ ?>
            <label>No hay Anticipos Aplicados</label>
        <?php }


        if($controlador->factura['tipo_relacion_id'] !=''){ ?>
            <br>
            <label>Tipo Relacion:</label>
            <?php echo $controlador->factura['tipo_relacion_codigo']; ?>
            <?php echo $controlador->factura['tipo_relacion_descripcion']; ?><br>
            <label>CFDIS Relacionados:</label>

            <?php

            foreach($controlador->facturas_relacionadas as $factura_rel){
                echo '<br>'.$factura_rel['factura_rel_uuid'].' ,';
            }

            ?>


        <?php }
        else{ ?>
            <label>No hay Facturas Relacionadas</label>
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
            <?php
            foreach ($controlador->datos_partidas as $partida) { ?>
                <tr>
                    <td>
                        <?php echo $partida['ClaveProdServ']; ?>
                        <?php echo $partida['ClaveProdServDescripcion']; ?>
                    </td>
                    <td>
                        <?php echo $partida['NoIdentificacion']; ?>
                    </td>
                    <td>
                        <?php echo $partida['Cantidad']; ?>
                    </td>
                    <td>
                        <?php echo $partida['ClaveUnidad']; ?>
                        <?php echo $partida['ClaveUnidadDescripcion']; ?>
                    </td>
                    <td>
                        <?php echo $partida['Unidad']; ?>
                    </td>
                    <td>
                        <?php echo $partida['Descripcion']; ?>
                    </td>
                    <td>
                        $<?php echo number_format(floatval($partida['ValorUnitario']),2,".",","); ?>
                    </td>
                    <td>
                        $<?php echo number_format(floatval($partida['Importe']),2,".",","); ?>
                    </td>
                    <td>
                        $<?php echo number_format(floatval($partida['Descuento']),2,".",","); ?>
                    </td>
                </tr>
                <?php
                if(isset($partida['traslados'])) {
                    if (count($partida['traslados']) >= 1) {
                        ?>
                        <tr>
                            <td colspan='9'>
                                <div class='col-md-12'>
                                    <h5>Traslados</h5>
                                    <table class='table table-bordered'>
                                        <thead>
                                            <tr>
                                                <th>Base</th>
                                                <th>Impuesto</th>
                                                <th>Tipo factor</th>
                                                <th>Tasa o cuota</th>
                                                <th>Importe</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        <?php
                        foreach ($partida['traslados'] as $traslado) { ?>
                                        <tr>
                                            <td>
                                                $<?php echo number_format(floatval($traslado['Base']),2,".",","); ?>
                                            </td>
                                            <td>
                                                <?php echo $traslado['Impuesto']; ?>
                                                <?php echo $traslado['ImpuestoDescripcion']; ?>
                                            </td>
                                            <td>
                                                <?php echo $traslado['TipoFactor']; ?>
                                            </td>
                                            <td>
                                                <?php echo $traslado['TasaOCuota']; ?>
                                            </td>
                                            <td>
                                                $<?php echo number_format(floatval($traslado['Importe']),2,".",","); ?>
                                            </td>
                                        </tr>
                            <?php
                        } ?>

                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
            <?php
                    }
                }
                if(isset($partida['retenciones'])) {
                    if (count($partida['retenciones']) >= 1) {
                        ?>
                        <tr>
                            <td colspan='9'>
                                <div class='col-md-12'>
                                    <h5>Retenciones</h5>
                                    <table class='table table-bordered'>
                                        <thead>
                                        <tr>
                                            <th>Base</th>
                                            <th>Impuesto</th>
                                            <th>Tipo factor</th>
                                            <th>Tasa o cuota</th>
                                            <th>Importe</th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        <?php
                                        foreach ($partida['retenciones'] as $retencion) { ?>
                                            <tr>
                                                <td>
                                                    $<?php echo number_format(floatval($retencion['Base']),2,".",","); ?>
                                                </td>
                                                <td>
                                                    <?php echo $retencion['Impuesto']; ?>
                                                    <?php echo $retencion['ImpuestoDescripcion']; ?>
                                                </td>
                                                <td>
                                                    <?php echo $retencion['TipoFactor']; ?>
                                                </td>
                                                <td>
                                                    <?php echo $retencion['TasaOCuota']; ?>
                                                </td>
                                                <td>
                                                    $<?php echo number_format(floatval($retencion['Importe']),2,".",","); ?>
                                                </td>
                                            </tr>
                                            <?php
                                        } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                }
            } ?>
            </tbody>
        </table>
    </div>
    <hr>
    <div class='col-md-12'>
        <label>Moneda:</label>
        <?php echo $controlador->datos_comprobante['Moneda']; ?>
        <?php echo $controlador->datos_comprobante['MonedaDescripcion']; ?>
        <label>Forma de pago:</label>
        <?php echo $controlador->datos_comprobante['FormaPago']; ?>
        <?php echo $controlador->datos_comprobante['FormaPagoDescripcion']; ?>
        <label>Método de pago:</label>
        <?php echo $controlador->datos_comprobante['MetodoPago']; ?>
        <?php echo $controlador->datos_comprobante['MetodoPagoDescripcion']; ?>
    </div>
    <hr>
    <div class='col-md-4 col-md-offset-8 row'>
        <label>Subtotal:</label>
        $<?php echo number_format(floatval($controlador->datos_comprobante['SubTotal']),2,".",","); ?>
    </div>

    <?php if($controlador->datos_impuestos['TotalImpuestosTrasladados']){ ?>
    <div class='col-md-4 col-md-offset-8 row'>
        <label>Impuestos Trasladados</label>
    </div>
    <div class='col-md-4 col-md-offset-8 row'>
        <label>
            <?php echo $controlador->datos_impuestos_traslados['ImpuestoDescripcion']; ?>
            <?php echo $controlador->datos_impuestos_traslados['TasaOCuota']; ?>
        </label>
            $<?php echo number_format(floatval($controlador->datos_impuestos_traslados['Importe']),2,".",","); ?>
    </div>

    <?php } ?>

    <?php if($controlador->datos_impuestos['TotalImpuestosRetenidos']){ ?>
    <div class='col-md-4 col-md-offset-8 row'>
        <label>Impuestos Retenidos</label>
    </div>
    <div class='col-md-4 col-md-offset-8 row'>
        <label>
            <?php echo $controlador->datos_impuestos_retenidos['ImpuestoDescripcion']; ?>
        </label>
            $<?php echo number_format(floatval($controlador->datos_impuestos_retenidos['Importe']),2,".",",") ; ?>
    </div>
    <?php } ?>
    <div class='col-md-4 col-md-offset-8 row'>
        <label>Total:</label>
        $<?php echo number_format(floatval($controlador->datos_comprobante['Total']),2,".",","); ?>
    </div>
    <div class='col-md-12 row'>
        <hr>
        <label>Total con Letra:</label>
        <?php echo $controlador->numero_texto; ?>
    </div>
</div>

<div class="col-md-12">
    <?php 
        if($controlador->status_factura=='sin timbrar'){
    ?>
    <a href="./index.php?seccion=cliente&accion=timbra_cfdi&factura_id=<?php echo $controlador->factura_id; ?>">
        <button type="button" class="btn">Timbra Factura</button>
    </a>
            <a href="./index.php?seccion=anticipo&accion=aplica_anticipo_factura&factura_id=<?php echo $controlador->factura_id; ?>">
                <button type="button" class="btn">Aplica Anticipo</button>
            </a>
            <a href="./index.php?seccion=factura&accion=relaciona_facturas&factura_id=<?php echo $controlador->factura_id; ?>">
                <button type="button" class="btn">Relaciona Facturas</button>
            </a>

            <a href="./index.php?seccion=factura&accion=elimina_factura&factura_id=<?php echo $controlador->factura_id; ?>">
                <button type="button" class="btn">Elimina Factura</button>
            </a>
    <?php
    }
    if($controlador->status_factura == 'timbrada' || $controlador->status_factura == 'Timbrada/Pagada' ){
        ?>
    <a href="./index.php?seccion=cliente&accion=descarga_factura_pdf&factura_id=<?php echo $controlador->factura_id; ?>" download>
        <button type="button" class="btn">Descarga PDF</button>
    </a>
    <a href="./index.php?seccion=cliente&accion=descarga_factura_xml&factura_id=<?php echo $controlador->factura_id; ?>" download>
        <button type="button" class="btn">Descarga XML</button>
    </a>
    <a href="./index.php?seccion=factura&accion=informe_gastos_pdf&factura_id=<?php echo $controlador->factura_id; ?>" download>
        <button type="button" class="btn">Descarga Informe de Gastos</button>
    </a>
    <?php
    }
    ?>
</div>