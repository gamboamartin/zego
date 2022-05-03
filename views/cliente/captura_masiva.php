<div class="table-responsive">
    <table class="table table-striped table_cliente">
        <thead>
        <tr>
            <th>#</th>
            <th>RFC</th>
            <th>Razon Social</th>
            <th>Representante Legal</th>
            <th>Pagina WEB</th>
            <th>CP</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($controlador->clientes as $cliente) {
            ?>
            <tr>
                <td class="td_id"><?php echo $cliente['cliente_id']; ?></td>
                <td class="td_rfc"><?php echo $directiva->genera_input('rfc',$cliente['cliente_rfc'],false,false,false,false,false);  ?></td>
                <td class="td_razon_social"><?php echo $directiva->genera_input('razon_social',$cliente['cliente_razon_social'],false,false,false,false,false);  ?></td>
                <td class="td_nombre_representante_legal"><?php echo $directiva->genera_input('nombre_representante_legal',$cliente['cliente_nombre_representante_legal'],false,false,false,false,false);  ?></td>
                <td class="td_pagina_web"><?php echo $directiva->genera_input('pagina_web',$cliente['cliente_pagina_web'],false,false,false,false,false);  ?></td>
                <td class="td_cp"><?php echo $directiva->genera_input('cp',$cliente['cliente_cp'],false,false,false,false,false);  ?></td>
                <td>
                    <input type="hidden" class="cliente_id" value="<?php echo $cliente['cliente_id']; ?>">
                    <?php echo $directiva->btn_actualiza(12,'actualiza_cliente'); ?>
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</div>