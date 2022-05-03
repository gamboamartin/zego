<form action="./index.php?seccion=factura&accion=guarda_cliente_cancela&session_id=<?php echo SESSION_ID; ?>" method="POST">
<div class="col-md-12" id="cliente-selector">
    <?php
    echo $directiva->input_select('cliente',$controlador->cliente_id,12,false,false,false,$controlador->link,'required',false);
    ?>
</div>
<div class="col-md-12" id="alta_cliente"></div>
<?php
echo $directiva->btn_enviar(12,'Siguiente','siguiente');
?>
</form>
