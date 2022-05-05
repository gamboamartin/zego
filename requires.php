<?php
$path_base = '';
if(defined('PATH_BASE')){
    $path_base = PATH_BASE;
}
require_once ($path_base.'clases/my_pdf.php');
require_once ($path_base.'clases/repositorio.php');
require_once ($path_base.'clases/facturas.php');
require_once ($path_base.'clases/xml_cfdi.php');
require_once ($path_base.'clases/calculo.php');
require_once($path_base.'config/conexion.php');
require_once($path_base.'config/empresas.php');

require_once($path_base.'base/consultas_base.php');

require_once($path_base.'views/directivas/directivas.php');
require_once($path_base.'views/templates.php');

require_once($path_base.'modelos/modelos.php');
require_once($path_base.'modelos/modelo_sobrecargado.php');


$directorio = opendir($path_base."modelos");
while ($archivo = readdir($directorio)){
    if (!is_dir($archivo)) {
        require_once($path_base.'modelos/'.$archivo);
    }
}

require_once($path_base.'controladores/controlador_base.php');

$directorio = opendir($path_base."controladores");
while ($archivo = readdir($directorio)){
    if (!(is_dir($archivo))) {

        $es_php = strpos($archivo, '.php');
        if($es_php!==false){
            if($archivo != 'controlador_base.php') {
                require_once($path_base.'controladores/' . $archivo);
            }
        }
    }
}

