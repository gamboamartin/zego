<form action="index.php?seccion=nota_credito&accion=alta_bd&session_id=<?php echo SESSION_ID; ?>" method="POST">
<div class="row">
<?php
echo $directiva->input_select_columnas('factura',false,4,false,
    array('factura_folio','factura_saldo','factura_uuid'),$controlador->link,'required',false,
    $controlador->facturas);

?>
</div>
<div class="row">
    <?php
    echo $directiva->btn_enviar(12,'Guarda Nota de Crédito','guarda-nota_credito');
    ?>
</div>
</form>
