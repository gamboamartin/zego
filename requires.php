<?php
require_once ('clases/my_pdf.php');
require_once ('clases/repositorio.php');
require_once ('clases/facturas.php');
require_once ('clases/xml_cfdi.php');
require_once ('clases/calculo.php');
require_once('config/conexion.php');
require_once('config/empresas.php');

require_once('consultas_base.php');

require_once('views/directivas/directivas.php');
require_once('views/templates.php');

require_once('modelos.php');
require_once('modelo_sobrecargado.php');


$directorio = opendir("modelos");
while ($archivo = readdir($directorio)){
    if (!is_dir($archivo)) {
        require_once('modelos/'.$archivo);
    }
}

require_once('controladores/controlador_base.php');

$directorio = opendir("controladores");
while ($archivo = readdir($directorio)){
    if (!(is_dir($archivo))) {

        $es_php = strpos($archivo, '.php');
        if($es_php!==false){
            if($archivo != 'controlador_base.php') {
                require_once('controladores/' . $archivo);
            }
        }
    }
}

