<div class="col-md-12" id="cliente-selector">
    <?php
    echo $directiva->input_select('cliente',$controlador->cliente_id,12,'disabled',false,false,$controlador->link,'required',false);
    ?>
</div>

<div class="col-md-12" id="datos_facturacion">
    <?php
    echo $directiva->input_select('uso_cfdi',$controlador->cliente_uso_cfdi_id,2,'disabled',false,false,$controlador->link,'required',false);
    echo $directiva->input_select('moneda',$controlador->cliente_moneda_id,2,'disabled',false,false,$controlador->link,'required',false);
    echo $directiva->input_select('forma_pago',$controlador->cliente_forma_pago_id,2,'disabled',false,false,$controlador->link,'required',false);
    echo $directiva->input_select('metodo_pago',$controlador->cliente_metodo_pago_id,2,'disabled',false,false,$controlador->link,'required',false);
    echo $directiva->genera_input_text('condiciones_pago',2,$controlador->cliente_condiciones_pago,false,false,false,false,'disabled');
    echo $directiva->fecha($controlador->fecha,2,'fecha');
    ?>
</div>

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
        <?php echo $directiva->btn_enviar(6,'Agregar a Factura','partida_nueva'); ?>
    <?php echo $directiva->btn_enviar(6,'Crear Producto','agrega_producto'); ?>
    </div>
</div>
<div class="col-md-12" id="alta-insumo"></div>
<input type="hidden" id="factor_trasladado" value="0">
<input type="hidden" id="factor_retenido" value="0">
<hr>