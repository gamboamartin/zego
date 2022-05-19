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

    $order = ' fecha_modifica DESC ';
    $clientes = $cliente_modelo_remota->registros_modificados(limit:100,n_dias:  5, order: $order,
        services: $services, tabla: 'cliente');
    if(errores::$error){
        $error = (new errores())->error('Error al obtener registros', $clientes);
        (new error_write())->out(error: $error,info:  $info,path_info:  $services->name_files->path_info);
    }


    foreach($clientes as $cliente){

        $cliente_id = $cliente['id'];

        $cliente_local = $cliente_modelo_local->registro_puro($cliente_id, 'cliente');
        if(errores::$error){
            $error = (new errores())->error('Error al obtener registro', $cliente_local);
            (new error_write())->out(error: $error,info:  $info,path_info:  $services->name_files->path_info);
        }

        $aplica_upd = false;
        $registro_upd = array();
        foreach($cliente_local as $campo=>$value_local){
            $value_local = trim($value_local);
            $value_remote = trim($cliente[$campo]);

            if($value_remote!==$value_local){
                $aplica_upd = true;
            }
        }
        $upd = array();
        if($aplica_upd){
            $upd = $cliente_modelo_local->modifica_bd($cliente, 'cliente', $cliente_id);
            if(errores::$error){
                $error = (new errores())->error('Error al modificar registro', $upd);
                (new error_write())->out(error: $error,info:  $info,path_info:  $services->name_files->path_info);
            }

        }


        var_dump($upd);

    }


}
$services->finaliza_servicio();