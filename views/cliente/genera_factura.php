<?php echo $controlador->breadcrumbs; ?>
<br><br><br>

<div class="fixed-bottom folio-factura">Factura Ingreso Folio: <br>
    <b><?php echo $controlador->folio_muestra; ?></b></div>
<div class="panel panel-default">
    <div class="panel-heading">Datos del Emisor</div>
    <div class="panel-body">
        <div class="col-md-4">
            <label>RFC: </label>
            <?php echo $controlador->rfc; ?>
        </div>
        <div class="col-md-4">
            <label>Razón Social: </label>
            <?php echo $controlador->razon_social; ?>
        </div>
        <div class="col-md-4">
            <label>Régimen Fiscal: </label>
            <?php echo $controlador->regimen_fiscal; ?>
            <?php echo $controlador->nombre_regimen_fiscal; ?>
        </div>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading">Datos del Receptor</div>
    <div class="panel-body">
        <div class="panel panel-default">
            <div class="panel-heading">Datos Generales</div>
            <div class="panel-body">
                <div class="col-md-4">
                    <label>Id Cliente: </label>
                    <?php echo $controlador->cliente_id; ?>
                </div>
                <div class="col-md-4">
                    <label>RFC: </label>
                    <?php echo $controlador->datos_cliente['cliente_rfc']; ?>
                </div>
                <div class="col-md-4">
                    <label>Razón Social: </label>
                    <?php echo $controlador->datos_cliente['cliente_razon_social']; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="panel panel-default">
    <form action="./index.php?seccion=cliente&accion=guarda_factura&cliente_id=<?php echo $_GET['cliente_id']; ?>" method="POST">
        <div class="panel-heading">Datos Factura</div>
        <div class="panel-body">
            <div class="panel panel-default">
                <div class="panel-heading">Datos Generales</div>
                <div class="panel-body">
                    <?php echo $directiva->input_select(
                        'uso_cfdi',$controlador->uso_cfdi_id,4,false,
                        false,'',$controlador->link,'required',false); ?>
                    <?php echo $directiva->input_select(
                        'moneda',$controlador->moneda_id,4,false,false,'',
                        $controlador->link,'required',false); ?>
                    <?php echo $directiva->input_select(
                        'forma_pago',$controlador->forma_pago_id,4,false,
                        false,'',$controlador->link,'required',false); ?>
                    <?php echo $directiva->input_select(
                        'metodo_pago',$controlador->metodo_pago_id,6,false,
                        false,'',$controlador->link,'required',false); ?>
                    <?php echo $directiva->genera_input_text(
                'condiciones_pago',6,$controlador->condiciones_pago,'required',
                false,false,false,false); ?>
                    <?php echo $directiva->textarea('',12,'observaciones'); ?>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">Conceptos</div>
                <div class="panel-body partidas">
                    <div class='row'>
                        <?php
                        echo  $directiva->input_select('insumo',false,3,false,'[]',false,$controlador->link,'required',false);
                        echo  $directiva->genera_input_text('cantidad',1,false,'required',false,'[]',false,false);
                        echo  $directiva->genera_input_text('valor_unitario',2,false,'required',false,'[]',false,false);
                        echo  $directiva->genera_input_text('descripcion',4,false,'required',false,'[]',false,false);
                        echo  $directiva->genera_input_text('sub_total',2,false,false,false,false,false,'disabled');
                        ?>
                        <input type='hidden' name='factor_traslado' class='factor-traslado-valor' value='0'>
                        <input type='hidden' name='factor_retenido' class='factor-retenido-valor' value='0'>
                        <input type='hidden' name='factor_ieps' class='factor-ieps-valor' value='0'>
                        <div class='row col-md-12 datos-insumo'>
                            <div class='row col-md-12 datos-insumo-partida'>
                                <label>Unidad de Medida:</label>
                                <span class='unidad-medida'></span>
                                <label>Codigo de Unidad:</label>
                                <span class='unidad-codigo'></span>
                                <label>Concepto SAT:</label>
                                <span class='concepto-sat'></span>
                                <label>Codigo SAT:</label>
                                <span class='codigo-sat'></span>

                            </div>
                            <div class='row col-md-12 impuestos-ieps-partida'>
                                <label>Codigo IEPS:</label>
                                <span class='codigo-impuesto-ieps'></span>
                                <label>Descripcion Impuesto:</label>
                                <span class='impuesto-ieps'></span>
                                <label>Factor ieps:</label>
                                <span class='factor-ieps'></span>
                                <label>Monto Base ieps:</label>
                                <span class='monto-base-ieps'></span>
                                <label>Monto Impuesto ieps:</label>
                                <span class='monto-impuesto-ieps'></span>
                                <input type='hidden' class='monto-impuesto-ieps-partida' value='0'>

                            </div>

                            <div class='row col-md-12 impuestos-traslados-partida'>
                                <label>Codigo Impuesto traslado:</label>
                                <span class='codigo-impuesto-traslado'></span>
                                <label>Impuesto traslado:</label>
                                <span class='impuesto-traslado'></span>
                                <label>Factor traslado:</label>
                                <span class='factor-traslado'></span>
                                <label>Monto Base Traslado:</label>
                                <span class='monto-base-traslado'></span>
                                <label>Monto Impuesto Trasladado:</label>
                                <span class='monto-impuesto-trasladado'></span>
                                <input type='hidden' class='monto-impuesto-traslado-partida' value='0'>

                            </div>

                            <div class='row col-md-12 impuestos-retenidos-partida'>
                                <label>Codigo Impuesto retenido:</label>
                                <span class='codigo-impuesto-retenido'></span>
                                <label>Impuesto retenido:</label>
                                <span class='impuesto-retenido'></span>
                                <label>Factor retenido:</label>
                                <span class='factor-retenido'></span>
                                <label>Monto Base Retenido:</label>
                                <span class='monto-base-retenido'></span>
                                <label>Monto Impuesto Retenido:</label>
                                <span class='monto-impuesto-retenido'></span>
                                <input type='hidden' class='monto-impuesto-retenido-partida' value='0'>
                                <hr>
                            </div>

                        </div>

                    </div>
                </div>
                <div class="row col-md-12">
                    <button type="button" class="btn btn-primary col-md-12" id="partida-nueva">Otra Partida</button>
                </div>
            </div>
            <div class="col-md-12">
                <input type="hidden" id="subtotal_general" value="0">
                <input type="hidden" id="impuestos_trasladados_general" value="0">
                <input type="hidden" id="impuestos_retenidos_general" value="0">
                <div id="subtotal">Sub total: 0.00</div>
                <div id="impuestos_trasladados">Impuestos trasladados: 0.00</div>
                <div id="impuestos_retenidos">Impuestos retenidos: 0.00</div>
                <div id="total">Total 0.00</div>
            </div>
            <div class="col-md-12">
                <div class="radio disabled">
                    <label>
                        <input type="radio" value="vista_preliminar" checked name="opcion_factura">
                        Vista Preliminar
                    </label>
                </div>
            </div>
            <?php echo $directiva->btn_enviar(3,'Factura','guarda'); ?>
        </div>
    </form>
</div>
