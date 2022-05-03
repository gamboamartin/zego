<?php
foreach ($controlador->registros as $registro){
    $id = $registro[$controlador->tabla.'_id'];
    $descripcion = $registro[$controlador->tabla.'_descripcion'];
    $selected = '';

    if($id == $controlador->registro_padre_id){
        $selected = 'selected';
    }

    ?>
    <option value="<?php echo $id; ?>" <?php echo $selected; ?>>
        <?php echo $descripcion; ?>
    </option>
<?php
}
