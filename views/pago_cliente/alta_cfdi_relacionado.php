<?php
echo $controlador->breadcrumbs;
?>
<form action="index.php?seccion=pago_cliente&accion=aplica_pago&session_id=<?php echo SESSION_ID; ?>" method="POST">
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
            echo $directiva->input_select('forma_pago',$controlador->forma_pago_id,3,
            'disabled',false, false,$controlador->link,'required',false);
            echo $directiva->input_select('moneda',$controlador->moneda_id,3,
            'disabled',false, false,$controlador->link,'required',false);
            echo $directiva->genera_input_text('tipo_cambio',3,$controlador->tipo_cambio,
                false,false,false,false,'disabled');
            ?>
    </div>
    <div class="row">
        <?php
            echo $directiva->genera_input_text('monto',2,$controlador->monto,
                            false,false,false,false,'disabled');
            echo $directiva->genera_input_text('numero_operacion',2,1,
                            false,false,false,false,'disabled');
        ?>
        <div class='form-group col-md-4' id='contenedor_select_cuenta_bancaria_id'>
            <label for='Cuenta Bancaria'>Cuenta Bancaria Ordenante:</label>
            <select name='cuenta_bancaria_id' class='selectpicker cuenta_bancaria_id'
                    data-live-search='true' title='Seleccione una Cuenta Bancaria'
                    data-width='100%' id='select_cuenta_bancaria_id'
                    data-none-results-text='No se encontraron resultados' disabled>
                <?php echo $controlador->options_cuenta_bancaria; ?>
            </select>
        </div>

        <div class='form-group col-md-4' id='contenedor_select_cuenta_bancaria_empresa_id'>
            <label for='Cuenta Bancaria Empresa'>Cuenta Bancaria Beneficiaria:</label>
            <select name='cuenta_bancaria_empresa_id' class='selectpicker cuenta_bancaria_empresa_id'
                    data-live-search='true' title='Seleccione una Cuenta Bancaria'
                    data-width='100%' id='select_cuenta_bancaria_empresa_id'
                    data-none-results-text='No se encontraron resultados' disabled>
                <?php echo $controlador->options_cuenta_bancaria_empresa; ?>
            </select>
        </div>
    </div>
    <div class="row">

        <div class="col-md-12 table-responsive">
            <div class="well">
                <label>Monto por aplicar:</label>
                <span class="badge" id="monto-por-aplicar">$0.00</span>
                <input type="hidden" id="total-pago" value="<?php echo $controlador->monto; ?>">
                <input type="hidden" id="por-aplicar" value="0">
            </div>
            <table class="table table-striped" id="cliente-ve-facturas">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Folio</th>
                        <th>Fecha</th>
                        <th>Folio Fiscal</th>
                        <th>Total</th>
                        <th>Saldo</th>
                        <th>Status</th>
                        <th>Parcialidad</th>
                        <th>Monto Pagar</th>
                        <th>Aplica Pago</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                foreach ($controlador->facturas_saldo as $factura){
                ?>
                    <tr>
                        <td><?php echo $factura['factura_id']; ?></td>
                        <td><?php echo $factura['factura_folio']; ?></td>
                        <td><?php echo $factura['factura_fecha']; ?></td>
                        <td><?php echo $factura['factura_uuid']; ?></td>
                        <td>$<?php echo number_format($factura['factura_total'],2,'.',','); ?></td>
                        <td class="td-saldo-factura">
                            $<?php echo number_format($factura['factura_saldo'],2,'.',','); ?>
                            <?php echo $directiva->input_hidden(false,$factura['factura_saldo'],'saldo-factura'); ?>
                        </td>
                        <td><?php echo $factura['factura_status_factura']; ?></td>
                        <td>
                            <?php
                            echo $directiva->genera_input('parcialidad',$factura['n_parcialidad'],false,false,'[]',false,'disabled');
                            echo $directiva->input_hidden('parcialidad[]',$factura['n_parcialidad']);
                        ?>
                        </td>
                        <td class="td-monto-pagar">
                            <?php
                            echo $directiva->input_hidden('factura_id[]',$factura['factura_id']);
                            echo $directiva->genera_input('monto_pagar',$factura['monto_pagar'],false,false,'[]',false,false);
                            ?>
                        </td>
                        <td class="td-aplica-pago"><?php
                            $checked = false;
                            if($factura['monto_pagar'] > 0){
                                $checked = true;
                            }
                            echo $directiva->checkbox($checked,1,'aplica_pago',false,'aplica_pago'); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>

        </div>

    </div>
    <div class="row">
        <?php
            echo $directiva->btn_enviar(12,'Siguiente','boton');

        ?>
    </div>
</form>