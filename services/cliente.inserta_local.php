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

$services = new services(path: __FILE__);
$calculo = new calculo();

$empresas = new empresas();
$empresas_data = $empresas->empresas;
$info = '';
foreach ($empresas_data as $empresa){

    $conexiones = $services->conexiones(empresa: $empresa);
    if(errores::$error){
        $error = (new errores())->error('Error al conectar', $conexiones);
        (new error_write())->out(error: $error,info:  $info,path_info:  $services->name_files->path_info);

    }

    var_dump($conexiones);

    $cliente_modelo_remota = new cliente(link: $conexiones->remote);
    $cliente_modelo_local = new cliente(link: $conexiones->local);


    $clientes = $cliente_modelo_remota->registros_sin_insertar(limit:1,n_dias:  5, services: $services, tabla: 'cliente');
    if(errores::$error){
        $error = (new errores())->error('Error al obtener registros', $clientes);
        (new error_write())->out(error: $error,info:  $info,path_info:  $services->name_files->path_info);
    }


    $keys = array();
    $inserta = $cliente_modelo_local->servicio_insersiones($keys, $clientes, 'cliente');
    if(errores::$error){
        $error = (new errores())->error('Error al verificar si inserta', $inserta);
        (new error_write())->out(error: $error,info:  $info,path_info:  $services->name_files->path_info);
    }
    var_dump($inserta);




}
$services->finaliza_servicio();