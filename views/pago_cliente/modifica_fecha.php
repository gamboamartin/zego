<?php
echo $controlador->breadcrumbs;
?>
<form action="index.php?seccion=pago_cliente&accion=modifica_fecha_bd&registro_id=<?php echo $controlador->registro_id; ?>" method="POST">
    <div class="row col-md-12">
        <?php
        echo $directiva->fecha($controlador->registro['pago_cliente_fecha'],2,'fecha');
        ?>
    </div>
    <div class="row">
        <?php
            echo $directiva->btn_enviar(12,'Modifca Fecha','enviar');
        ?>
    </div>
</form>