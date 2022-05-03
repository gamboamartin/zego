<div class="col-md-6" id="cliente-selector">
    <?php
    echo $directiva->input_select('cliente',$controlador->cliente_id,12,false,false,false,$controlador->link,'required',false);
    ?>
</div>
<div class="col-md-6">
    <label>Agrega un Cliente</label>
    <?php
    echo $directiva->btn_enviar(12,'Agrega Cliente','agrega_cliente'); ?>
</div>
<div class="col-md-12" id="alta_cliente"></div>
<?php
    echo $directiva->btn_enviar(12,'Siguiente','siguiente');
    ?>
