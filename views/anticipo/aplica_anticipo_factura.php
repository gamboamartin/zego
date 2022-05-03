<form action="index.php?seccion=anticipo&accion=asigna_anticipo&session_id=<?php echo SESSION_ID; ?>" method="POST">
<div class="row">
<?php
echo $directiva->input_select('cliente',$controlador->factura['cliente_id'],3,false,false,false,$controlador->link,'required',false);
echo $directiva->input_select_columnas('anticipo',false,4,false,
    array('anticipo_folio','anticipo_monto','anticipo_uuid'),$controlador->link,'required',false,
    $controlador->anticipos);
echo $directiva->input_hidden('factura_id',$controlador->factura['factura_id']);

?>
</div>
<div class="row">
    <?php
    echo $directiva->btn_enviar(12,'Guarda Anticipo','guarda-anticipo');
    ?>
</div>
</form>
