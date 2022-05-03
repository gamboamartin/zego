<div class="panel panel-default">
    <div class="panel-heading">Datos del Cliente</div>
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
            <div class="panel-heading">Datos de Servicio</div>
            <div class="panel-body">
                <form action="./index.php?seccion=cliente&accion=actualiza_datos_servicio&cliente_id=<?php echo $_GET['cliente_id']; ?>"
                      method="POST">
                    <?php

                    ?>
                </form>
            </div>
        </div>
    </div>
</div>