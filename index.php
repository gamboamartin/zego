<?php



require 'vendor/autoload.php';

use config\empresas;
use gamboamartin\errores\errores;
use config\init;
use models\accion;
use views\directivas\directivas;
use views\templates;


require_once ('clases/numero_texto.php');
require_once('config/seguridad.php');
require_once('requires.php');

$repositorio = new Repositorio();

$conexion = new Conexion();
$conexion->selecciona_base_datos();
$link = $conexion->link;

$seguridad = new Seguridad();
$seccion = $seguridad->seccion;
$accion = $seguridad->accion;

define('SECCION',$seccion);
define('ACCION',$accion);

if($link) {
    $modelo_accion = new accion($link);
    if (isset($_SESSION['grupo_id'])) {
        $permiso = $modelo_accion->valida_permiso(SECCION, ACCION);
        if (!$permiso) {
            $seccion = 'session';
            $accion = 'denegado';
            $_GET['tipo_mensaje'] = 'error';
            $_GET['mensaje'] = 'Permiso denegado';
        }
        $n_acciones = $modelo_accion->cuenta_acciones();

        if ($n_acciones == 0) {
            session_destroy();
        }
    }
}

if(isset($_SESSION['seccion_header']) && $_SESSION['seccion_header']!==''){
    ob_clean();
    $seccion_header = $_SESSION['seccion_header'];
    unset($_SESSION['seccion_header']);
    if(isset($_SESSION['accion_header']) && $_SESSION['accion_header']!==''){
        $accion_header = $_SESSION['accion_header'];
        $registro_id = $_SESSION['registro_id_header'];
        unset($_SESSION['accion_header']);
        $header = "Location: index.php?seccion=$seccion_header&accion=$accion_header&registro_id=$registro_id&session_id=".SESSION_ID;
        unset($_SESSION['seccion_header'], $_SESSION['accion_header'], $_SESSION['registro_id_header']);
        header($header);
        exit;
    }
}




$directiva = new directivas();
if($link) {
    $template = new templates($link);
}
$name_ctl = 'controlador_'.$seccion;

$controlador = (new init())->controller(link:  $link,seccion:  $seguridad->seccion);
if(errores::$error){
    $error =  (new errores())->error(mensaje: 'Error al generar controlador', data: $controlador,
        params: get_defined_vars());
    print_r($error);exit;

}

//$controlador = new $name_ctl($link);

$controlador->$accion();
$directivas = new directivas();

$empresa = new empresas();

$nombre_empresa = '';
if(isset($_SESSION['numero_empresa'])){
    $datos_generales_empresa = $empresa->empresas[$_SESSION['numero_empresa']];
    $nombre_empresa = $datos_generales_empresa['nombre_empresa'];
}



?>
<!DOCTYPE html>
<html>
	<head>
        <meta charset="utf-8" name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width">
		<title>Sistema Administración</title>
  		<script src="./views/js/jquery.min.js"></script>	
  		<script src="./views/js/bootstrap.min.js"></script>
  		<script src="./views/js/bootstrap-select.js"></script>

        <script type="text/javascript" charset="utf8" src="./views/js/dataTables.js"></script>
        <script type="text/javascript" src="./views/js/funciones_base.js"></script>
        <script type="text/javascript" src="./views/js/funciones.js"></script>
        <script type="text/javascript" src="./views/js/bootstrap-filestyle.js"></script>
		<link rel="stylesheet" href="./views/css/bootstrap.min.css">
		<link href="./views/css/bootstrap-select.min.css" rel="stylesheet"/>
		<link rel="stylesheet" href="./views/css/layout.css">
		<link rel="stylesheet" href="./views/css/layout.css" media="print">
	</head>
	<body>
        <div class="navbar" role="navigation">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <h2>Sistema Administración</h2>
                </div>
                <?php if($seguridad->menu){ ?>
                <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav navbar-right">
                        <?php
                        $menu = $directiva->menu($link);
                        if(errores::$error){
                            $error = (new errores())->error('Error al generar menu', $menu);
                            print_r($error);
                            die('Error');
                        }
                        ?>
                    <?php  echo $directiva->menu($link); ?>
                    </ul>
                </div>
                <?php } ?>
            </div>
        </div>
		<div class="container contenido">
            <hr>
            <h3><?php echo $nombre_empresa; ?></h3>
            <hr>
            <?php
            $class_mensaje = "";
            if(isset($_GET['tipo_mensaje'])){
                $tipo_mensaje = $_GET['tipo_mensaje'];
                if($tipo_mensaje == 'error'){
                    $class_mensaje = 'alert alert-danger';
                }
                else{
                    if($tipo_mensaje == 'exito'){
                        $class_mensaje = 'alert alert-success';
                    }
                }
            }
            if(isset($controlador->error)){
                if($controlador->error == 1){
                    $class_mensaje = 'alert alert-danger';
                    $mensaje = $controlador->mensaje;
                    ?>
                    <div class="<?php echo $class_mensaje; ?> mensaje" ><?php echo $mensaje; ?></div>
                <?php
                }
            }
            if(isset($_GET['mensaje'])){
                $mensaje = $_GET['mensaje'];
            }
            else{
                $mensaje = "";
            } ?>
            <div class="<?php echo $class_mensaje; ?> mensaje" ><?php echo $mensaje; ?></div>
            <?php
            $include = './views/'.$seccion.'/'.$accion.'.php';
            if(file_exists($include)){
                include($include);
            }
            elseif(ACCION == 'lista') {
                include('./views/vista_base/lista.php');
            }
            elseif (ACCION=='modifica'){
                include('./views/vista_base/modifica.php');
            }
            elseif (ACCION=='alta'){
                include('./views/vista_base/alta.php');
            } ?>
		</div>	
	</body>
</html>