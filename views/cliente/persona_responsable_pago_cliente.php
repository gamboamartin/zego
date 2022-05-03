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
            <div class="panel-heading">Datos Persona Responsable de Pagos</div>
            <div class="panel-body">
                <form action="./index.php?seccion=cliente&accion=actualiza_responsable_pago&cliente_id=<?php echo $_GET['cliente_id']; ?>"
                      method="POST">
                    <?php
                    $nombre_responsable_pagos = $controlador->datos_cliente['cliente_nombre_responsable_pagos'];
                    $puesto_responsable_pagos = $controlador->datos_cliente['cliente_puesto_responsable_pagos'];
                    $telefono_responsable_pagos = $controlador->datos_cliente['cliente_telefono_responsable_pagos'];
                    $extension_responsable_pagos = $controlador->datos_cliente['cliente_extension_responsable_pagos'];
                    $correo_responsable_pagos = $controlador->datos_cliente['cliente_correo_responsable_pagos'];
                    $dias_credito = $controlador->datos_cliente['cliente_dias_credito'];
                    $status = $controlador->datos_cliente['cliente_status'];
                    echo $directiva->genera_input_text(
                        'nombre_responsable_pagos',4,$nombre_responsable_pagos,
                        'required',false,false,false,false);
                    echo $directiva->genera_input_text(
                    'puesto_responsable_pagos',4,$puesto_responsable_pagos,
                    'required',false,false,false,false);
                    echo $directiva->genera_input_text(
                    'telefono_responsable_pagos',4,$telefono_responsable_pagos,
                    'required',false, false,false,false);
                    echo $directiva->genera_input_text(
                    'extension_responsable_pagos',4,$extension_responsable_pagos,
                    'required',false,false,false,false);
                    echo $directiva->email($correo_responsable_pagos,4,'correo_responsable_pagos',
                    'required',false);
                    echo $directiva->genera_input_text('dias_credito',4,$dias_credito,'required',false,false,false,false);
                    echo $directiva->btn_enviar(12,'Guardar');
                    echo $directiva->input_hidden('status',$status);
                    ?>
                </form>
            </div>
        </div>
    </div>
</div>