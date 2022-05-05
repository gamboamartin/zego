<?php

use models\accion;

require 'vendor/autoload.php';
require_once('config/seguridad.php');
require_once('requires.php');

$conexion = new conexion();
$conexion->selecciona_base_datos();
$link = $conexion->link;

$seguridad = new Seguridad();
$seccion = $seguridad->seccion;
$accion = $seguridad->accion;

define('SECCION',$seccion);
define('ACCION',$accion);

$modelo_accion = new accion($link);

$permiso = $modelo_accion->valida_permiso(SECCION, ACCION);


if(!$permiso){
    $seccion = 'session';
    $accion = 'denegado';
    $_GET['tipo_mensaje'] = 'error';
    $_GET['mensaje'] = 'Permiso denegado';
}

$directiva = new Directivas();
$template = new templates($link);
$name_ctl = 'controllers\\controlador_'.$seccion;
$controlador = new $name_ctl($link);

$controlador->$accion();

    $include = './views/'.$seccion.'/'.$accion.'.php';
    if(file_exists($include)){
        include($include);
    }
    elseif(ACCION == 'lista_ajax') {
        include('./views/vista_base/lista_ajax.php');
    }
    elseif (ACCION == 'desactiva_bd'){
        include('./views/vista_base/desactiva_bd.php');
    }
    elseif (ACCION == 'activa_bd'){
        include('./views/vista_base/activa_bd.php');
    }
    elseif (ACCION == 'elimina_bd'){
        include('./views/vista_base/elimina_bd.php');
    }
    elseif (ACCION == 'datos_select'){
        include('./views/vista_base/datos_select.php');
    }
    elseif (ACCION == 'option_selected'){
        include('./views/vista_base/option_selected.php');
    }