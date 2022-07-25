<form action="index.php?seccion=factura&accion=a_cuenta_terceros_bd&factura_id=<?php echo $_GET['factura_id']; ?>&session_id=<?php echo SESSION_ID; ?>" method="POST">
    <div class="panel panel-default">
        <div class="panel-heading">Datos Generales</div>
        <div class="panel-body">
            <?php
            echo $directiva->input_select('moneda',$controlador->cliente_moneda_id,3,false,false,false,$controlador->link,false,false);
            echo $directiva->input_select('forma_pago',$controlador->cliente_forma_pago_id,3,false,false,false,$controlador->link,false,false);
            echo $directiva->input_select('metodo_pago',$controlador->cliente_metodo_pago_id,3,false,false,false,$controlador->link,false,false);
            echo $directiva->fecha($controlador->fecha,3,'fecha');
            ?>
        </div>
    </div>
    <hr>
    <div class="panel panel-default">
        <div class="panel-heading">Conceptos</div>
        <div class="panel-body partidas">
            <div class='row'>
                <?php
                echo  $directiva->input_select('insumo_a_cuenta_tercero',false,4,false,false,false,$controlador->link,false,false);
                echo  $directiva->genera_input_text('cantidad',2,false,false,false,false,false,false);
                echo  $directiva->genera_input_text('valor_unitario',3,false,false,false,false,false,false);
                echo  $directiva->genera_input_text('sub_total',3,false,false,false,false,false,'disabled');
                echo  $directiva->input_select('recep_a_cuenta_tercero',false,12,false,false,false,$controlador->link,false,false);
                ?>
            </div>
        </div>
        <div class="row col-md-10 col-md-offset-1">
            <br>
            <button type="button" class="btn btn-primary col-md-12" id="partida_nueva_act">Otra Partida</button>
        </div>
    </div>
    <div class="col-md-12">
        <br>
        <?php echo $directiva->btn_enviar(12,'Agregar a A Cuenta de Terceros','a_cuenta_tercero_nueva'); ?>
    </div>
</form>