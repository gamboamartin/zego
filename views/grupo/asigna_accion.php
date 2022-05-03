<?php echo $controlador->breadcrumbs; ?>

<div class="input-group col-md-6 col-md-offset-4">
				<span class="input-group-addon">
					<span class="glyphicon glyphicon-search" aria-hidden="true"></span>
				</span>
    <input type="text" class="form-control input-md busca_elemento" placeholder="Ingresa Busqueda" value="">
</div>
<br>
<div class='col-md-8 col-md-offset-2 alta'>
    <h4><?php echo $controlador->grupo_descripcion; ?></h4>
<?php
foreach ($controlador->menus as $menu){
    ?>
    <div
        class='well well-lg titulo-menu-lista'
        data-toggle="collapse"
        data-target="#selectormenu_<?php echo $menu['menu_id'] ?>">
        <span class="<?php echo $menu['menu_icono']; ?>"></span>
        <?php echo $menu['menu_descripcion']; ?>
    </div>
    <div class="collapse" id="selectormenu_<?php echo $menu['menu_id'] ?>">
    <?php
    foreach ($controlador->secciones as $seccion){
        if($seccion['menu_id'] == $menu['menu_id']) { ?>
            <div
                class='well well-sm titulo-seccion_menu-lista '
                data-toggle="collapse"
                data-target="#selector_<?php echo $seccion['seccion_menu_id'] ?>">
                <?php echo $seccion['seccion_menu_descripcion']; ?>
            </div>
            <div class="list-group collapse" id="selector_<?php echo $seccion['seccion_menu_id'] ?>">
            <?php
            foreach ($controlador->acciones_vista as $accion){
                if($accion['seccion_menu_id'] == $seccion['seccion_menu_id']){
                    $active = ' agrega_accion_bd ';
                    if($accion['aplicado'] == 1){
                        $active = 'btn-success elimina_accion_bd';
                    }
                    ?>
                        <button type="button" class="btn <?php echo $active; ?> elemento_accion">
                            <input type="hidden" name="grupo_id" class="grupo_id" value="<?php echo $controlador->grupo_id;?>">
                            <input type="hidden" name="accion_id" class="accion_id" value="<?php echo $accion['accion_id'];?>">
                            <?php echo $accion['accion_descripcion'];?>
                        </button>
                    <?php
                }
            }
            ?>
            </div>
            <?php
        }
    }
    ?>
    </div>
<?php
}
?>
</div>