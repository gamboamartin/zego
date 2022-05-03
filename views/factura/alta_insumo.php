<?php
echo $directiva->genera_input_text('descripcion',4,false,'required',false,false,false,false);
echo $directiva->input_select('unidad',false,4,false,false,false,$controlador->link,'required',false);
echo $directiva->input_select('tipo_insumo',false,4,false,false,false,$controlador->link,'required',false);

?>
<hr>
<label>Traslados:</label>
<div class="row">
<?php
echo $directiva->input_select('impuesto',false,4,false,false,false,$controlador->link,'required',false);
echo $directiva->input_select('tipo_factor',false,4,false,false,false,$controlador->link,'required',false);
echo $directiva->genera_input_text('factor',4,false,'required',false,false,false,false);

?>
</div>
    <hr>
<label>Retenciones:</label>
    <div class="row">
<?php

echo $directiva->input_select_personalizado('impuesto',false,4,false,false,false,$controlador->link,'impuesto_retenido_id','impuesto_retenido',false,'impuesto_retenido_id');
echo $directiva->input_select_personalizado('tipo_factor',false,4,false,false,false,$controlador->link,'tipo_factor_retenido_id','tipo_factor_retenido',false,'tipo_factor_retenido_id');
echo $directiva->genera_input_text('factor_retenido',4,false,'required',false,false,false,false);

?></div>
<hr>
<div class="row">
<?php
echo $directiva->autocomplete('producto_sat',false,12);
?>
    <input type="hidden" id="producto_sat_id_seleccionado" value="-1">
</div>

<div class="row">
    <?php
    echo $directiva->btn_enviar(6,'Guarda Producto','guarda-producto');
    echo $directiva->btn_enviar(6,'Cancelar','cancela-producto');
    ?>
</div>
