<?php echo $controlador->breadcrumbs; ?>
<div class='col-md-8 col-md-offset-2 alta'>
<?php
$directiva = new Directivas();
echo $directiva->encabezado_form_alta($accion,'asigna_tipo_cambio_bd');
echo $directiva->input_select('moneda',$_GET['moneda_id'],12,'disabled',false,false);
echo $directiva->genera_input_text('descripcion',6,'','required',false,false,false,false);
$hoy = date('Y-m-d');
echo $directiva->fecha($hoy,6,'fecha');
echo $directiva->genera_input_text('monto',12,'','required',false,false,false,false);
echo $directiva->textarea('',12,'observaciones');
echo $directiva->btn_enviar();
echo $directiva->input_hidden('moneda_id',$_GET['moneda_id']);
echo '</form>';
?>
</div>
