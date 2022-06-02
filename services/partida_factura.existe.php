<?php
include "init.php";
const PATH_BASE = '/var/www/html/zego/';


require PATH_BASE.'vendor/autoload.php';

use gamboamartin\errores\errores;
use gamboamartin\services\error_write\error_write;
use gamboamartin\services\services;
use gamboamartin\calculo\calculo;
use config\empresas;
use models\partida_factura;
use services_base\src;


$services = new services(path: __FILE__);
$calculo = new calculo();

$empresas = new empresas();
$empresas_data = $empresas->empresas;
$info = '';
$tabla = 'partida_factura';
foreach ($empresas_data as $empresa){


    $conexiones = $services->conexiones(empresa: $empresa);
    if(errores::$error){
        $error = (new errores())->error('Error al conectar', $conexiones);
        (new error_write())->out(error: $error,info:  $info,path_info:  $services->name_files->path_info);
    }

    var_dump($conexiones);

    $partida_factura_modelo_remota = new partida_factura(link: $conexiones->remote);
    $partida_factura_modelo_local = new partida_factura(link: $conexiones->local);

    $partida_facturas = $partida_factura_modelo_remota->registros_sin_insertar(limit:10000,n_dias:  5, services: $services, tabla: $tabla);
    if(errores::$error){
        $error = (new errores())->error('Error al obtener registros', $partida_facturas);
        (new error_write())->out(error: $error,info:  $info,path_info:  $services->name_files->path_info);
    }

    foreach($partida_facturas as $partida_factura){

        $result = (new src())->existe_local($partida_factura['id'], $partida_factura_modelo_local,
            $partida_factura_modelo_remota, $tabla);
        if(errores::$error){
            $error = (new errores())->error('Error al actualizar', $result);
            (new error_write())->out(error: $error,info:  $info,path_info:  $services->name_files->path_info);
        }
        var_dump($result);
    }


}
$services->finaliza_servicio();