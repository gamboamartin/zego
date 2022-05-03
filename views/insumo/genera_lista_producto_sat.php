<?php
    $productos = $controlador->registros;

    foreach ($productos as $producto){ ?>
        <div class="registro-linea-completa">
            <input
                    type = 'radio' name="producto_sat_id"
                    value = "<?php echo $producto['producto_sat_id']; ?>"
                    class = 'producto_sat_id'>
            <label><?php echo $producto['producto_sat_codigo']; ?></label>
            <?php echo $producto['producto_sat_descripcion']; ?>
        </div>
        <?php
    }