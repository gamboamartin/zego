<?php
include "init.php";
const PATH_BASE = '/var/www/html/zego/';


require PATH_BASE.'vendor/autoload.php';

use gamboamartin\errores\errores;
use gamboamartin\services\error_write\error_write;
use gamboamartin\services\services;
use gamboamartin\calculo\calculo;
use config\empresas;
use models\factura;
use services_base\src;


$services = new services(path: __FILE__);
$calculo = new calculo();

$empresas = new empresas();
$empresas_data = $empresas->empresas;
$info = '';
$tabla = 'factura';
foreach ($empresas_data as $empresa){


    $conexiones = $services->conexiones(empresa: $empresa);
    if(errores::$error){
        $error = (new errores())->error('Error al conectar', $conexiones);
        (new error_write())->out(error: $error,info:  $info,path_info:  $services->name_files->path_info);
    }

    var_dump($conexiones);

    $factura_modelo_remota = new factura(link: $conexiones->remote);
    $factura_modelo_local = new factura(link: $conexiones->local);



    $facturas = $factura_modelo_remota->registros_sin_insertar(limit:100,n_dias:  5, services: $services, tabla: $tabla);
    if(errores::$error){
        $error = (new errores())->error('Error al obtener registros', $facturas);
        (new error_write())->out(error: $error,info:  $info,path_info:  $services->name_files->path_info);
    }



    foreach($facturas as $factura){

        $result = (new src())->existe_local($factura['id'], $factura_modelo_local,
            $factura_modelo_remota, $tabla);
        if(errores::$error){
            $error = (new errores())->error('Error al actualizar', $result);
            (new error_write())->out(error: $error,info:  $info,path_info:  $services->name_files->path_info);
        }
        var_dump($result);

    }



}
$services->finaliza_servicio();