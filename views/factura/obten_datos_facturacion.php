<div class="col-md-12" id="cliente-selector">
    <?php
    echo $directiva->input_select('cliente',$controlador->cliente_id,12,'disabled',false,false,$controlador->link,'required',false);
    ?>
</div>

<div class="col-md-12" id="datos_facturacion">
    <?php
    echo $directiva->input_select('uso_cfdi',$controlador->cliente_uso_cfdi_id,2,false,false,false,$controlador->link,'required',false);
    echo $directiva->input_select('moneda',$controlador->cliente_moneda_id,2,false,false,false,$controlador->link,'required',false);
    echo $directiva->input_select('forma_pago',$controlador->cliente_forma_pago_id,2,false,false,false,$controlador->link,'required',false);
    echo $directiva->input_select('metodo_pago',$controlador->cliente_metodo_pago_id,2,false,false,false,$controlador->link,'required',false);
    echo $directiva->genera_input_text('condiciones_pago',2,$controlador->cliente_condiciones_pago,false,false,false,false,false);
    echo $directiva->fecha($controlador->fecha,2,'fecha');
    ?>
</div>
<?php
echo $directiva->btn_enviar(12,'Siguiente','siguiente');
?>