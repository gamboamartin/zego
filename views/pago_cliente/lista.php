<?php echo $controlador->breadcrumbs; ?>
<div class='row col-md-12'>

    <div class="col-md-2">
        <label>Fecha:</label>
        <input type="date" class="form-control" id="fecha">
    </div>  
    <div class="col-md-2">
        <label>RFC:</label>
        <input type="text" class="form-control" id="rfc">
    </div>   
    <div class="col-md-2">
        <label>Razón Social:</label>
        <input type="text" class="form-control" id="razon_social">
    </div> 
    <div class="col-md-2">
        <label>Status:</label>
        <select class="form-control" id="status_factura">
            <option value=''>Selecciona un status</option>
            <option value='timbrado'>Timbrada</option>
            <option value='sin timbrar'>Sin timbrar</option>
        </select>
    </div>           

	<div class="col-md-2">
        <label>Status de descarga:</label>
        <select class="form-control" id="status_descarga">
            <option value=''>Selecciona un status descarga</option>
            <option value='sin descargar'>sin descargar</option>
            <option value='descargada'>descargada</option>
        </select>
    </div>   
    <div class="col-md-2">
        <label>Descargar:</label>
        <button type="button" id="descarga" class="btn btn-default">Descarga Seleccionados</button>
    </div>          

</div>
<div class="col-md-12 table-responsive">
    <table class="table table-striped">
        <thead>
        <tr>
            <th>#</th>
            <th>Fecha</th>
            <th>RFC</th>
            <th>Razón Social</th>
            <th>Folio Fiscal</th>
            <th>Total Pago</th>
            <th>Status</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody id='contenido'>
        <?php
        foreach ($controlador->pagos as $pago) {
            $status_factura = false;
            if($pago['pago_cliente_status_factura'] == 'sin timbrar') {
                $status_factura = 0;
            }
            elseif($pago['pago_cliente_status_factura'] == 'timbrado'){
                $status_factura = 1;
            }
            $class = '';
            if($pago['pago_cliente_status'] == 0){
                $class = 'danger';
            }

            ?>
            <tr class="<?php echo $class; ?>">
                <td><?php echo $pago['pago_cliente_folio']; ?></td>
                <td><?php echo $pago['pago_cliente_fecha']; ?></td>
                <td><?php echo $pago['pago_cliente_cliente_rfc']; ?></td>
                <td><?php echo $pago['pago_cliente_cliente_razon_social']; ?></td>
                <td><?php echo $pago['pago_cliente_uuid']; ?></td>
                <td>$<?php echo number_format($pago['pago_cliente_monto'],2,'.',","); ?></td>
                <td><?php echo $pago['pago_cliente_status_factura']; ?></td>
                <td>
                    <?php echo $directiva->link_ve_pago('pago_cliente',$pago['pago_cliente_id'],$status_factura,'vista_preliminar'); ?>
                    <?php echo $directiva->link_descarga_pago_pdf('pago_cliente',$pago['pago_cliente_id']); ?>
                    <?php echo $directiva->link_descarga_pago_xml('pago_cliente',$pago['pago_cliente_id']); ?>
                    <?php echo $directiva->link_modifica_fecha('pago_cliente',$pago['pago_cliente_id']); ?>
                    <input type="checkbox" class="pago_cliente" value="<?php echo $pago['pago_cliente_id']; ?>">
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</div>
