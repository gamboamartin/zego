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
    </div>
</div>

<form action = "index.php?seccion=cliente&accion=actualiza_uso_cfdi&cliente_id=<?php echo $_GET['cliente_id']; ?>"
method="POST">
    <div class="panel panel-default">
        <div class="panel-heading">
            Datos de Generales de CFDI
        </div>
        <div class="panel-body">
            <div class="panel panel-default">
                <div class="panel-heading">Datos CFDI</div>
                <div class="panel-body">
                    <?php echo $directiva->input_select('uso_cfdi',$controlador->uso_cfdi_id,3,false,false,false,$controlador->link,false); ?>
                    <?php echo $directiva->input_select('moneda',$controlador->moneda_id,3,false ,false,false,$controlador->link,false); ?>
                    <?php echo $directiva->input_select('forma_pago',$controlador->forma_pago_id,3,false ,false,false,$controlador->link,false); ?>
                    <?php echo $directiva->input_select('metodo_pago',$controlador->metodo_pago_id,3,false ,false,false,$controlador->link,false); ?>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <?php echo $directiva->btn_enviar(12,'Guardar'); ?>
        </div>
    </div>
</form>