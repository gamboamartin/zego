<div class="col-md-12">
<?php
foreach ($controlador->acciones_permitidas as $accion_permitida){
    $accion_descripcion = $accion_permitida['accion_descripcion'];
    $etiqueta_accion = str_replace('_',' ',$accion_descripcion);
    $etiqueta_accion = ucwords(strtolower($etiqueta_accion));
    ?>
    <div class="col-md-3 acciones-inicio">
        <label><?php echo $etiqueta_accion; ?></label>
        <br>
        <a href="index.php?seccion=<?php echo $accion_permitida['seccion_menu_descripcion']; ?>&accion=<?php echo $accion_descripcion; ?>">
            <span class="<?php echo $accion_permitida['accion_icono']; ?> icono-inicio"></span>
        </a>
    </div>
<?php } ?>
</div>
