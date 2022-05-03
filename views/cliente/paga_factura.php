<?php echo $controlador->breadcrumbs; ?>
<br><br><br>
<div class="panel panel-default">
    <div class="panel-heading">Datos del Emisor</div>
    <div class="panel-body">
        <div class="col-md-4">
            <label>RFC: </label>
            <?php echo $controlador->rfc; ?>
        </div>
        <div class="col-md-4">
            <label>Razón Social: </label>
            <?php echo $controlador->razon_social; ?>
        </div>
        <div class="col-md-4">
            <label>Régimen Fiscal: </label>
            <?php echo $controlador->regimen_fiscal; ?>
            <?php echo $controlador->nombre_regimen_fiscal; ?>
        </div>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading">Datos del Receptor</div>
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
            <div class="panel-heading">Domicilio</div>
            <div class="panel-body">
                <div class="col-md-2">
                    <label>Pais: </label>
                    <?php echo $controlador->datos_cliente['pais_descripcion']; ?>
                </div>
                <div class="col-md-2">
                    <label>Estado: </label>
                    <?php echo $controlador->datos_cliente['estado_descripcion']; ?>
                </div>
                <div class="col-md-2">
                    <label>Municipio: </label>
                    <?php echo $controlador->datos_cliente['municipio_descripcion']; ?>
                </div>
                <div class="col-md-2">
                    <label>Colonia: </label>
                    <?php echo $controlador->datos_cliente['cliente_colonia']; ?>
                </div>
                <div class="col-md-2">
                    <label>CP: </label>
                    <?php echo $controlador->datos_cliente['cliente_cp']; ?>
                </div>
                <div class="col-md-2">
                    <label>Calle: </label>
                    <?php echo $controlador->datos_cliente['cliente_calle']; ?>
                </div>
                <div class="col-md-2">
                    <label>Número Exterior: </label>
                    <?php echo $controlador->datos_cliente['cliente_exterior']; ?>
                </div>
                <div class="col-md-2">
                    <label>Número Interior: </label>
                    <?php echo $controlador->datos_cliente['cliente_interior']; ?>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">Datos de Contacto</div>
            <div class="panel-body">
                <div class="col-md-6">
                    <label>Télefono: </label>
                    <?php echo $controlador->datos_cliente['cliente_telefono']; ?>
                </div>
                <div class="col-md-6">
                    <label>Email: </label>
                    <?php echo $controlador->datos_cliente['cliente_email']; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="panel panel-default">
    <form action="./index.php?seccion=cliente&accion=guarda_factura&cliente_id=<?php echo $_GET['cliente_id']; ?>" method="POST">
        <div class="panel-heading">Datos de Pago</div>
        <div class="panel-body">
            <div class="panel panel-default">
                <div class="panel-heading">Datos Generales</div>
                <div class="panel-body">
                    <?php echo $directiva->input_select(
                        'moneda',$controlador->moneda_id,4,false,false,'',$controlador->link); ?>
                    <?php echo $directiva->fecha(false,4,'fecha_pago'); ?>

                    <?php echo $directiva->input_select('forma_pago',$controlador->forma_pago_id,4,false,false,'',$controlador->link); ?>
                    <?php echo $directiva->genera_input_text('monto',4,$controlador->saldo_factura,'required',false,false,false,false); ?>
                    <?php echo $directiva->genera_input_text('numero_operacion',4,false,'required',false,false,false,false); ?>

                    <?php echo $directiva->input_select_filtro(
                        'cuenta_bancaria',false,4,false,
                        false,false,$controlador->cuentas_bancarias,'banco_descripcion','cuenta_bancaria_id'); ?>

                    <?php echo $directiva->input_select_basico(
                        'cuenta_empresa',4,$controlador->cuentas_empresa,'nombre','cuenta'); ?>

                    <?php echo $directiva->textarea('',12,'observaciones'); ?>

                </div>
            </div>
        </div>
    </form>
</div>