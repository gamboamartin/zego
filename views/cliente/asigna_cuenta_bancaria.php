<?php echo $controlador->breadcrumbs; ?>
<br><br><br>
<div class="panel panel-default">
    <div class="panel-heading">
        Datos del Cliente
    </div>
    <div class="panel-body">
        <div class="panel panel-default">
            <div class="panel-heading">Datos Generales</div>
            <div class="panel-body">
                <div class="col-md-4">
                    <label>Id Cliente: </label>
                    <?php echo $controlador->cliente_id; ?>
                </div>
                <div class="col-md-4">
                    <label>RFC: </label>
                    <?php echo $controlador->datos_cliente['cliente_rfc']; ?>
                </div>
                <div class="col-md-4">
                    <label>Razón Social: </label>
                    <?php echo $controlador->datos_cliente['cliente_razon_social']; ?>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">Cuentas Bancarias</div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>Id</th>
                            <th>Banco</th>
                            <th>Moneda</th>
                            <th>Cuenta</th>
                            <th>Cuenta Cheques</th>
                            <th>Clabe</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($controlador->cuentas_bancarias as $cuenta_bancaria){?>
                            <tr>
                                <td><?php echo $cuenta_bancaria['cuenta_bancaria_id']; ?></td>
                                <td><?php echo $cuenta_bancaria['banco_descripcion']; ?></td>
                                <td><?php echo $cuenta_bancaria['moneda_descripcion']; ?></td>
                                <td><?php echo $cuenta_bancaria['cuenta_bancaria_cuenta']; ?></td>
                                <td><?php echo $cuenta_bancaria['cuenta_bancaria_cheque']; ?></td>
                                <td><?php echo $cuenta_bancaria['cuenta_bancaria_clabe']; ?></td>
                                <td>
                                    <?php echo $directiva->link_elimina_externa('cliente',$cuenta_bancaria['cuenta_bancaria_id']); ?>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-12">
                    <form action="./index.php?seccion=cliente&accion=alta_cuenta_bd&session_id=<?php echo SESSION_ID; ?>" method="POST">
                    <?php
                    echo $directiva->input_select('banco',false,3,false,false,false,$controlador->link,'required');
                    echo $directiva->input_select('moneda',false,2,false, false,false,$controlador->link,'required');
                    echo $directiva->genera_input_text('cuenta',2,'',false,'[0-9]{10}|[0-9]{16}|[0-9]{18}',false,false,false);
                    echo $directiva->genera_input_text('cheque',2,'',false,'[0-9]{11}|[0-9]{18}',false,false,false);
                    echo $directiva->genera_input_text('clabe',3,'',false,'[0-9]{18}',false,false,false);
                    echo $directiva->input_hidden('cliente_id',$_GET['cliente_id']);
                    echo $directiva->btn_enviar(12,'Enviar');
                    ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>