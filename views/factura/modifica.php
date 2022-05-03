<?php echo $controlador->breadcrumbs; ?><br>
<div class="container">
    <div class='col-md-12'>
        <form action="index.php?seccion=factura&accion=modifica_generales_factura&factura_id=<?php echo $controlador->factura_id; ?>&session_id=<?php echo SESSION_ID; ?>"
                method="POST">
            <label>Nombre Emisor:</label>
            <?php echo $controlador->datos_emisor['nombre_empresa']; ?>
            <label>Folio Fiscal:</label>
            Vista Preliminar
            <label>RFC Emisor:</label>
            <?php echo $controlador->datos_emisor['rfc_emisor']; ?>
            <label>No. de serie del CSD:</label>
            Vista Preliminar
            <label>Nombre Receptor:</label>
            <?php echo $controlador->datos_receptor['razon_social']; ?>
            <label>Lugar, fecha y hora de emisión:</label>
            <?php echo $controlador->datos_comprobante['cp']; ?>
            <?php echo $controlador->datos_comprobante['fecha']; ?>
            <label>RFC Receptor:</label>
            <?php echo $controlador->datos_receptor['rfc']; ?>
            <label>Efecto de comprobante:</label>
            <?php echo $controlador->datos_comprobante['tipo_comprobante_codigo']; ?>
            <?php echo $controlador->datos_comprobante['tipo_comprobante_descripcion']; ?>
            <label>Folio y serie:</label>
            <?php echo $controlador->datos_comprobante['folio']; ?>
            <?php echo $controlador->datos_comprobante['serie']; ?>
            <label>Uso CFDI:</label>
            <?php echo $controlador->datos_receptor['uso_cfdi_codigo']; ?>
            <?php echo $controlador->datos_receptor['uso_cfdi_descripcion']; ?>
            <label>Régimen fiscal:</label>
            <?php echo $controlador->datos_emisor['regimen_fiscal_codigo']; ?>
            <?php echo $controlador->datos_emisor['regimen_fiscal_descripcion']; ?>
            <?php echo $directiva->textarea($controlador->datos_comprobante['observaciones'],12,'observaciones'); ?>
            <?php echo $directiva->btn_enviar(12,'Modifica Observaciones'); ?>
        </form>

    </div>
    <hr>
    <div class="col-md-12 table-responsive">
        <h4>Conceptos:</h4>
        <table class='table table-bordered'>
            <thead>
            <tr>
                <th>
                    Cve del producto/servicio
                </th>
                <th>No. identificación</th>
                <th>Cantidad</th>
                <th>Clave unidad</th>
                <th>Unidad</th>
                <th>Valor unitario</th>
                <th>Importe</th>
                <th>Descuento</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($controlador->partidas as $partida) {
                $partida_factura_id = $partida['partida_factura_id'];
                ?>
                <form action="index.php?seccion=factura&accion=modifica_partida&partida_factura_id=<?php echo $partida_factura_id; ?>&session_id=<?php echo SESSION_ID; ?>"
                method="POST">
                <tr>
                    <td>
                        <?php echo $partida['partida_factura_producto_sat_codigo']; ?>
                        <?php echo $partida['partida_factura_producto_sat_descripcion']; ?>
                    </td>
                    <td>
                        <?php echo $partida['partida_factura_no_identificacion']; ?>
                    </td>
                    <td>
                        <?php echo $partida['partida_factura_cantidad']; ?>
                    </td>
                    <td>
                        <?php echo $partida['partida_factura_unidad_codigo']; ?>
                        <?php echo $partida['partida_factura_unidad_descripcion']; ?>
                    </td>
                    <td>
                        <?php echo $partida['partida_factura_unidad']; ?>
                    </td>
                    <td>
                        $<?php echo number_format(floatval($partida['partida_factura_valor_unitario']),2,".",","); ?>
                    </td>
                    <td>
                        $<?php echo number_format(floatval($partida['partida_factura_importe']),2,".",","); ?>
                    </td>
                    <td>
                        $<?php echo number_format(floatval($partida['partida_factura_descuento']),2,".",","); ?>
                    </td>
                    <td>
                        <?php
                        if($controlador->status_factura == 'sin timbrar'){
                            ?>
                            <a href="./index.php?seccion=factura&session_id=<?php echo SESSION_ID; ?>&accion=elimina_partida_por_id&partida_factura_id=<?php echo $partida['partida_factura_id']; ?>">
                                <button type="button" class="btn">Eliminar Partida</button>
                            </a>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="9">
                        <?php echo $directiva->genera_input_text('descripcion',10,$partida['partida_factura_descripcion'],false,false,false,false,false); ?><br>
                        <?php echo $directiva->btn_enviar(2,'Modifica',false,'modifica_partida') ?>
                    </td>
                </tr>
                </form>
                <?php
                if($partida['partida_factura_total_impuestos_trasladados']>0) { ?>
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
                                    <tr>
                                        <td>
                                            $<?php echo number_format(floatval($partida['partida_factura_base']), 2, ".", ","); ?>
                                        </td>
                                        <td>
                                            <?php echo $partida['partida_factura_impuesto_codigo']; ?>
                                            <?php echo $partida['partida_factura_impuesto_descripcion']; ?>
                                        </td>
                                        <td>
                                            <?php echo $partida['partida_factura_tipo_factor_codigo']; ?>
                                        </td>
                                        <td>
                                            <?php echo $partida['partida_factura_tasa_cuota']; ?>
                                        </td>
                                        <td>
                                            $<?php echo number_format(floatval($partida['partida_factura_total_impuestos_trasladados']), 2, ".", ","); ?>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <?php
                }
                if($partida['partida_factura_total_impuestos_retenidos']>0) { ?>
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
                                            <tr>
                                                <td>
                                                    $<?php echo number_format(floatval($partida['partida_factura_base']), 2, ".", ","); ?>
                                                </td>
                                                <td>
                                                    <?php echo $partida['partida_factura_impuesto_retenido_codigo']; ?>
                                                    <?php echo $partida['partida_factura_impuesto_retenido_descripcion']; ?>
                                                </td>
                                                <td>
                                                    <?php echo $partida['partida_factura_tipo_factor_retenido_codigo']; ?>
                                                </td>
                                                <td>
                                                    <?php echo $partida['partida_factura_tasa_cuota_retenido']; ?>
                                                </td>
                                                <td>
                                                    $<?php echo number_format(floatval($partida['partida_factura_total_impuestos_retenidos']), 2, ".", ","); ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
            } ?>

            </tbody>
        </table>
        <div class="col-md-12">
            <hr>
            <label>Conceptos:</label><br>
            <div class="col-md-12 row" id="insumo-selector">
                <?php
                echo $directiva->input_select('insumo',false,2,false,false,false,$controlador->link,'required',false);
                echo $directiva->genera_input_text('cantidad',2,false,false,false,false,false,false);
                echo $directiva->genera_input_text('valor_unitario',2,false,false,false,false,false,false);
                echo $directiva->genera_input_text('traslados',2,false,false,false,false,false,'disabled');
                echo $directiva->genera_input_text('retenciones',2,false,false,false,false,false,'disabled');
                echo $directiva->genera_input_text('total',2,false,false,false,false,false,'disabled');
                ?>
            </div>
            <div class="col-md-12 row">
                <?php echo $directiva->genera_input_text('descripcion_nueva',12,false,false,false,false,false,false); ?>
            </div>
            <div class="col-md-12 row">
                <?php echo $directiva->btn_enviar(12,'Agregar a Factura','partida_nueva'); ?>
            </div>
        </div>
    </div>
    <hr>
    <div class='col-md-12'>
        <label>Moneda:</label>
        <?php echo $controlador->datos_comprobante['moneda_codigo']; ?>
        <?php echo $controlador->datos_comprobante['moneda_descripcion']; ?>
        <label>Forma de pago:</label>
        <?php echo $controlador->datos_comprobante['forma_pago_codigo']; ?>
        <?php echo $controlador->datos_comprobante['forma_pago_descripcion']; ?>
        <label>Método de pago:</label>
        <?php echo $controlador->datos_comprobante['metodo_pago_codigo']; ?>
        <?php echo $controlador->datos_comprobante['metodo_pago_descripcion']; ?>
    </div>
    <hr>
    <div class='col-md-4 col-md-offset-8 row'>
        <label>Subtotal:</label>
        $<?php echo number_format(floatval($controlador->datos_comprobante['sub_total']),2,".",","); ?>
    </div>

    <?php if($controlador->datos_impuestos['total_impuestos_trasladados']>0){ ?>
        <div class='col-md-4 col-md-offset-8 row'>
            <label>Impuestos Trasladados</label>
        </div>
        <div class='col-md-4 col-md-offset-8 row'>
            <label>
                <?php echo $controlador->datos_impuestos['impuesto_traslado_descripcion']; ?>
                <?php echo $controlador->datos_impuestos['tasa_cuota_trasladado']; ?>
            </label>
            $<?php echo number_format(floatval($controlador->datos_impuestos['total_impuestos_trasladados']),2,".",","); ?>
        </div>

    <?php } ?>

    <?php if($controlador->datos_impuestos['total_impuestos_retenidos']){ ?>
        <div class='col-md-4 col-md-offset-8 row'>
            <label>Impuestos Retenidos</label>
        </div>
        <div class='col-md-4 col-md-offset-8 row'>
            <label>
                <?php echo $controlador->datos_impuestos['impuesto_retenido_descripcion']; ?>
                <?php echo $controlador->datos_impuestos['tasa_cuota_retenido']; ?>
            </label>
            $<?php echo number_format(floatval($controlador->datos_impuestos['total_impuestos_retenidos']),2,".",",") ; ?>
        </div>
    <?php } ?>
    <div class='col-md-4 col-md-offset-8 row'>
        <label>Total:</label>
        $<?php echo number_format(floatval($controlador->datos_impuestos['total']),2,".",","); ?>
    </div>
    <div class='col-md-12 row'>
        <hr>
        <label>Total con Letra:</label>
        <?php echo $controlador->numero_texto.' '.$controlador->datos_comprobante['moneda_codigo']; ?>
    </div>
</div>

<div class="col-md-12">
    <?php
    if($controlador->status_factura=='sin timbrar'){
        ?>
        <a href="./index.php?seccion=factura&accion=timbra_cfdi&session_id=<?php echo SESSION_ID; ?>&factura_id=<?php echo $controlador->factura_id; ?>">
            <button type="button" class="btn">Timbra Factura</button>
        </a>
        <?php
    }
    if($controlador->status_factura == 'timbrada'){
        ?>
        <a href="./index.php?seccion=cliente&accion=descarga_factura_pdf&session_id=<?php echo SESSION_ID; ?>&factura_id=<?php echo $controlador->factura_id; ?>" download>
            <button type="button" class="btn">Descarga PDF</button>
        </a>
        <a href="./index.php?seccion=cliente&accion=descarga_factura_xml&session_id=<?php echo SESSION_ID; ?>&factura_id=<?php echo $controlador->factura_id; ?>" download>
            <button type="button" class="btn">Descarga XML</button>
        </a>
        <?php
    }
    ?>
</div>