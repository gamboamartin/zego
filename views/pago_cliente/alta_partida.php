<?php
echo $controlador->breadcrumbs;
?>
<form action="index.php?seccion=pago_cliente&accion=guarda_partida_session&session_id=<?php echo SESSION_ID; ?>" method="POST">
    <div class="row">
        <?php
        echo $directiva->input_select('cliente',$controlador->cliente_id,4,'disabled',false,
                                    false,$controlador->link,'required',false);
        echo $directiva->fecha($controlador->fecha,2,'fecha');
        echo $directiva->genera_input_text('cp',2,$controlador->cp,
                            false,false,false,false,'disabled');
        echo $directiva->genera_input_text('serie',2,$controlador->serie,
            false,false,false,false,'disabled');
        echo $directiva->genera_input_text('folio',2,$controlador->folio,
            false,false,false,false,'disabled');
        ?>
    </div>
    <div class="row">
        <?php
            echo $directiva->fecha($controlador->hoy,3,'fecha_pago');
            echo $directiva->input_select('forma_pago',$controlador->forma_pago_id,
                3, false,false, false,
                $controlador->link,'required',false);
            echo $directiva->input_select('moneda',$controlador->moneda_id,3,
            false,false, false,$controlador->link,'required',false);
            echo $directiva->genera_input_text('tipo_cambio',3,1,
                false,false,false,false,false);
            ?>
    </div>
    <div class="row">
        <?php
            echo $directiva->genera_input_text('monto',2,false,
                            'required',false,false,false,false);
            echo $directiva->genera_input_text('numero_operacion',2,1,
                            'required',false,false,false,false);
        ?>
        <div class='form-group col-md-4' id='contenedor_select_cuenta_bancaria_id'>
            <label for='Cuenta Bancaria'>Cuenta Bancaria Ordenante:</label>
            <select name='cuenta_bancaria_id' class='selectpicker cuenta_bancaria_id'
                    data-live-search='true' title='Seleccione una Cuenta Bancaria'
                    data-width='100%' id='select_cuenta_bancaria_id'
                    data-none-results-text='No se encontraron resultados'>
                <?php echo $controlador->options_cuenta_bancaria; ?>
            </select>
        </div>

        <div class='form-group col-md-4' id='contenedor_select_cuenta_bancaria_empresa_id'>
            <label for='Cuenta Bancaria Empresa'>Cuenta Bancaria Beneficiaria:</label>
            <select name='cuenta_bancaria_empresa_id' class='selectpicker cuenta_bancaria_empresa_id'
                    data-live-search='true' title='Seleccione una Cuenta Bancaria'
                    data-width='100%' id='select_cuenta_bancaria_empresa_id'
                    data-none-results-text='No se encontraron resultados'>
                <?php echo $controlador->options_cuenta_bancaria_empresa; ?>
            </select>
        </div>
    </div>
    <div class="row">
        <?php
            echo $directiva->btn_enviar(12,'Siguiente','guarda_encabezado_pago');
        ?>
    </div>
</form>