<h5>Código: <b><?php echo $controlador->moneda_codigo; ?></b></h5>
<h5>Moneda: <b><?php echo $controlador->moneda_descripcion; ?></b></h5>
<h5>Tipo de cambio Hoy: <b><?php echo $controlador->tipo_cambio_hoy; ?></b></h5>
<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Monto</th>
            </tr>
        </thead>
        <tbody>
<?php
foreach($controlador->registros as $registro){
?>
            <tr>
                <td><?php echo $registro['tipo_cambio_id']; ?></td>
                <td><?php echo $registro['tipo_cambio_fecha']; ?></td>
                <td><?php echo $registro['tipo_cambio_monto']; ?></td>
            </tr>
<?php
}
?>
        </tbody>
    </table>
</div>
