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

    $factura_modelo_remota = new factura(link: $conexiones->remote);
    $factura_modelo_local = new factura(link: $conexiones->local);



    $facturas = $factura_modelo_remota->registros_sin_insertar(limit:100,n_dias:  5, services: $services, tabla: 'factura');
    if(errores::$error){
        $error = (new errores())->error('Error al obtener registros', $facturas);
        (new error_write())->out(error: $error,info:  $info,path_info:  $services->name_files->path_info);
    }



    foreach($facturas as $factura){
        $existe = $factura_modelo_local->existe_por_id($factura['id'],'factura');
        if(errores::$error){
            $error = (new errores())->error('Error al verificar si existe', $existe);
            (new error_write())->out(error: $error,info:  $info,path_info:  $services->name_files->path_info);
        }
        if($existe){
            $r_factura_remota = $factura_modelo_remota->upd_factura_ins($factura['id']);
            if(errores::$error){
                $error = (new errores())->error('Error al actualizar', $r_factura_remota);
                (new error_write())->out(error: $error,info:  $info,path_info:  $services->name_files->path_info);
            }
            var_dump($r_factura_remota);
        }

    }



}
$services->finaliza_servicio();