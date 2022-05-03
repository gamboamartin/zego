<?php
echo $controlador->breadcrumbs;
?>
<form action="index.php?seccion=pago_cliente&accion=guarda_pago_cliente_session" method="POST">
    <div class="row">
        <?php
        echo $directiva->input_select('cliente',false,4,false,false,
                                    false,$controlador->link,'required',false);
        echo $directiva->fecha($controlador->hoy,2,'fecha');
        echo $directiva->genera_input_text('cp',2,$controlador->cp,
                            false,false,false,false,false);
        echo $directiva->genera_input_text('serie',2,$controlador->serie,
            false,false,false,false,false);
        echo $directiva->genera_input_text('folio',2,$controlador->folio,
            false,false,false,false,false);
        ?>
        <div class="col-md-12"></div>
            <?php
        echo $directiva->genera_input_text('uuid_relacionado',2,'',
            false,false,false,false,false);
        echo $directiva->input_select('tipo_relacion',false,4,false,false,
            false,$controlador->link,false,false);
        ?>
    </div>
    <div class="row">
        <?php
            echo $directiva->btn_enviar(12,'Siguiente','enviar');
        ?>
    </div>
</form>