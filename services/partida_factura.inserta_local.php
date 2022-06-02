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

    $partida_factura_modelo_remota = new partida_factura(link: $conexiones->remote);
    $partida_factura_modelo_local = new partida_factura(link: $conexiones->local);

    $order = 'id DESC';
    $partida_facturas = $partida_factura_modelo_remota->registros_sin_insertar(limit:1000,n_dias:  5, order: $order,
        services: $services, tabla: 'partida_factura');
    if(errores::$error){
        $error = (new errores())->error('Error al obtener registros', $partida_facturas);
        (new error_write())->out(error: $error,info:  $info,path_info:  $services->name_files->path_info);
    }


    $keys = array();
    $inserta = $partida_factura_modelo_local->servicio_insersiones($keys, $partida_facturas, 'partida_factura');
    if(errores::$error){
        $error = (new errores())->error('Error al verificar si inserta', $inserta);
        (new error_write())->out(error: $error,info:  $info,path_info:  $services->name_files->path_info);
    }
    var_dump($inserta);



}
$services->finaliza_servicio();