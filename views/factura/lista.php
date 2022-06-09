<?php echo $controlador->breadcrumbs; ?>
<div class='row col-md-12'>
    <div class="col-md-1">
        <label>Folio:</label>
        <input type="text" class="form-control" id="folio">
    </div>
    <div class="col-md-2">
        <label>Fecha:</label>
        <input type="date" class="form-control input-sm" id="fecha">
    </div>  
    <div class="col-md-1">
        <label>RFC:</label>
        <input type="text" class="form-control input-sm" id="rfc">
    </div>   
    <div class="col-md-2">
        <label>Razón Social:</label>
        <input type="text" class="form-control input-sm" id="razon_social">
    </div> 
    <div class="col-md-2">
        <label>Status:</label>
        <select class="form-control input-sm" id="status_factura">
            <option value=''>Selecciona un status</option>
            <option value='timbrada'>Timbrada</option>
            <option value='sin timbrar'>Sin timbrar</option>
            <option value='cancelado'>Cancelada</option>
        </select>
    </div>           

	<div class="col-md-2">
        <label>Status de descarga:</label>
        <select class="form-control input-sm" id="status_descarga">
            <option value=''>Selecciona un status descarga</option>
            <option value='sin descargar'>sin descargar</option>
            <option value='descargada'>descargada</option>
        </select>
    </div>   
    <div class="col-md-2">
        <label>Descargar:</label>
        <button type="button" id="descarga" class="btn btn-default btn-sm">Descarga Seleccionados</button>
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
            <th>Total</th>
            <th>Status</th>
            <th>Status Descarga</th>
            <th>Saldo</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody id='contenido'>
        <?php
        foreach ($controlador->registros as $factura) {
            $status_factura = false;
            if($factura['factura_status_factura'] === 'sin timbrar') {
                $status_factura = 0;
            }
            elseif($factura['factura_status_factura'] === 'timbrada'){
                $status_factura = 1;
            }
            elseif($factura['factura_status_factura'] === 'cancelado'){
                $status_factura = 2;
            }
            elseif($factura['factura_status_factura'] === 'Timbrada/Pagada'){
                $status_factura = 3;
            }

            $clase_status = '';
            if($status_factura == 2){
                $clase_status = 'class="danger"';
            }

            ?>
            <tr <?php echo $clase_status; ?>>
                <td><?php echo $factura['factura_folio']; ?></td>
                <td><?php echo $factura['factura_fecha']; ?></td>
                <td><?php echo $factura['factura_cliente_rfc']; ?></td>
                <td><?php echo $factura['factura_cliente_razon_social']; ?></td>
                <td><?php echo $factura['factura_uuid']; ?></td>
                <td>$<?php echo number_format($factura['factura_total'],2,'.',','); ?></td>
                <td>
                    <?php echo $factura['factura_status_factura']; ?>
                    <?php if($factura['factura_uuid_cancelacion']!=-1){ ?>
                    <a href="./index.php?seccion=factura&accion=obten_pdf_cancelado&factura_id=<?php echo $factura['factura_id']; ?>">
                        <?php echo $factura['factura_uuid_cancelacion']; ?>
                    </a>
                    <?php } ?>
                </td>
                <td><?php echo $factura['factura_status_descarga']; ?></td>
                <td><?php echo $factura['factura_saldo']?></td>
                <td>
                    <?php echo $directiva->link_ve_factura_pdf('cliente',$factura['factura_id'],$status_factura); ?>
                    <?php if($status_factura != 0){ ?>
                    <?php echo $directiva->link_descarga_factura_pdf('cliente',$factura['factura_id']); ?>
                    <?php echo $directiva->link_descarga_factura_xml('cliente',$factura['factura_id'],'XML_'.$factura['factura_referencia']); ?>
                    <?php echo $directiva->link_a_cuenta_terceros('factura',$factura['factura_id']); ?>
                    <?php } ?>
                    <?php if($status_factura != 2){ ?>
                    <?php if($status_factura == 1){ ?>
                        <?php echo $directiva->link_ve_pagos($factura['factura_id']); ?>

                        <?php } ?>
                        <?php echo $directiva->link_modifica('factura',$factura['factura_id']); ?>
                        <?php echo $directiva->link_informe_gastos_pdf('factura', $factura['factura_id']); ?>

                        <input type="checkbox" class="factura" value="<?php echo $factura['factura_id']; ?>">
                    <?php } ?>
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</div>
