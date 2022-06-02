<?php
include "init.php";
const PATH_BASE = '/var/www/html/zego/';


require PATH_BASE.'vendor/autoload.php';

use gamboamartin\errores\errores;
use gamboamartin\services\error_write\error_write;
use gamboamartin\services\services;
use gamboamartin\calculo\calculo;
use config\empresas;
use models\insumo;
use services_base\src;

$services = new services(path: __FILE__);
$calculo = new calculo();

$empresas = new empresas();
$empresas_data = $empresas->empresas;
$info = '';
$tabla = 'insumo';
foreach ($empresas_data as $empresa){


    $conexiones = $services->conexiones(empresa: $empresa);
    if(errores::$error){
        $error = (new errores())->error('Error al conectar', $conexiones);
        (new error_write())->out(error: $error,info:  $info,path_info:  $services->name_files->path_info);
    }

    var_dump($conexiones);

    $insumo_modelo_remota = new insumo(link: $conexiones->remote);
    $insumo_modelo_local = new insumo(link: $conexiones->local);

    $insumos = $insumo_modelo_remota->registros_sin_insertar(limit:1000,n_dias:  5, services: $services, tabla: $tabla);
    if(errores::$error){
        $error = (new errores())->error('Error al obtener registros', $insumos);
        (new error_write())->out(error: $error,info:  $info,path_info:  $services->name_files->path_info);
    }

    foreach($insumos as $insumo){
        $result = (new src())->existe_local($insumo['id'], $insumo_modelo_local,
            $insumo_modelo_remota, $tabla);
        if(errores::$error){
            $error = (new errores())->error('Error al actualizar', $result);
            (new error_write())->out(error: $error,info:  $info,path_info:  $services->name_files->path_info);
        }
        var_dump($result);
    }


}
$services->finaliza_servicio();