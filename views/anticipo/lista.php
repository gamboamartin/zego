<?php echo $controlador->breadcrumbs; ?>
<div class='row col-md-12'>
    <div class="col-md-2">
        <label>Fecha:</label>
        <input type="date" class="form-control input-sm" id="fecha">
    </div>  
    <div class="col-md-2">
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
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody id='contenido'>
        <?php
        foreach ($controlador->anticipos as $anticipo) {
            $status_anticipo = false;
            if($anticipo['anticipo_status_anticipo'] == 'sin timbrar') {
                $status_anticipo = 0;
            }
            elseif($anticipo['anticipo_status_anticipo'] == 'timbrada'){
                $status_anticipo = 1;
            }
            elseif($anticipo['anticipo_status_anticipo'] == 'cancelado'){
                $status_anticipo = 2;
            }

            $clase_status = '';
            if($status_anticipo == 2){
                $clase_status = 'class="danger"';
            }

            ?>
            <tr <?php echo $clase_status; ?>>
                <td><?php echo $anticipo['anticipo_folio']; ?></td>
                <td><?php echo $anticipo['anticipo_fecha']; ?></td>
                <td><?php echo $anticipo['cliente_rfc']; ?></td>
                <td><?php echo $anticipo['cliente_razon_social']; ?></td>
                <td><?php echo $anticipo['anticipo_uuid']; ?></td>
                <td>$<?php echo number_format($anticipo['anticipo_total'],2,'.',','); ?></td>
                <td>
                    <?php echo $anticipo['anticipo_status_anticipo']; ?>
                </td>
                <td><?php echo $anticipo['anticipo_status_descarga']; ?></td>
                <td>
                    <?php echo $directiva->link_ve_anticipo('anticipo',$anticipo['anticipo_id'],$status_anticipo); ?>
                    <?php if($status_anticipo != 0){ ?>
                    <?php echo $directiva->link_descarga_anticipo_pdf('cliente',$anticipo['anticipo_id']); ?>
                    <?php echo $directiva->link_descarga_anticipo_xml('cliente',$anticipo['anticipo_id'],'XML_'.$anticipo['anticipo_referencia']); ?>
                    <?php } ?>
                    <?php if($status_anticipo != 2){ ?>
                    <?php if($status_anticipo == 1){ ?>
                        <?php echo $directiva->link_ve_pagos($anticipo['anticipo_id']); ?>

                        <?php } ?>
                        <?php echo $directiva->link_modifica('anticipo',$anticipo['anticipo_id']); ?>
                        <?php echo $directiva->link_informe_gastos_pdf('anticipo', $anticipo['anticipo_id']); ?>

                        <input type="checkbox" class="anticipo" value="<?php echo $anticipo['anticipo_id']; ?>">
                    <?php } ?>
                    <?php if($status_anticipo == 0){ ?>
                        <?php echo $directiva->link_elimina_anticipo($anticipo['anticipo_id']); ?>
                    <?php } ?>
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</div>
