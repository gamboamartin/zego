<?php
include "init.php";
const PATH_BASE = '/var/www/html/zego/';


require PATH_BASE.'vendor/autoload.php';

use gamboamartin\errores\errores;
use gamboamartin\services\error_write\error_write;
use gamboamartin\services\services;
use gamboamartin\calculo\calculo;
use config\empresas;
use models\cliente;
use services_base\src;

$services = new services(path: __FILE__);
$calculo = new calculo();

$empresas = new empresas();
$empresas_data = $empresas->empresas;
$info = '';
$tabla = 'cliente';
foreach ($empresas_data as $empresa){


    $conexiones = $services->conexiones(empresa: $empresa);
    if(errores::$error){
        $error = (new errores())->error('Error al conectar', $conexiones);
        (new error_write())->out(error: $error,info:  $info,path_info:  $services->name_files->path_info);
    }

    var_dump($conexiones);

    $cliente_modelo_remota = new cliente(link: $conexiones->remote);
    $cliente_modelo_local = new cliente(link: $conexiones->local);
    $order = 'id DESC';
    $clientes = $cliente_modelo_remota->registros_sin_insertar(limit:1000,n_dias:  5, order: $order, services: $services, tabla: $tabla);
    if(errores::$error){
        $error = (new errores())->error('Error al obtener registros', $clientes);
        (new error_write())->out(error: $error,info:  $info,path_info:  $services->name_files->path_info);
    }



    foreach($clientes as $cliente){
        $result = (new src())->existe_local($cliente['id'], $cliente_modelo_local,
            $cliente_modelo_remota, $tabla);
        if(errores::$error){
            $error = (new errores())->error('Error al actualizar', $result);
            (new error_write())->out(error: $error,info:  $info,path_info:  $services->name_files->path_info);
        }
        var_dump($result);
    }


}
$services->finaliza_servicio();