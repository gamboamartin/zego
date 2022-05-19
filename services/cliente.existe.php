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

    $where = 'cliente.insertado = 0';
    $r_clientes = $cliente_modelo_remota->registros_puros(limit: 100,order:'', tabla: 'cliente', where: $where);
    if(errores::$error){
        $error = (new errores())->error('Error al obtener registros', $r_clientes);
        (new error_write())->out(error: $error,info:  $info,path_info:  $services->name_files->path_info);
    }
    $clientes = $r_clientes['registros'];
    foreach($clientes as $cliente){
        $cliente_id = $cliente['id'];
        $existe_en_local = $cliente_modelo_local->existe_por_id($cliente_id, 'cliente');
        if(errores::$error){
            $error = (new errores())->error('Error al obtener cliente local', $existe_en_local);
            (new error_write())->out(error: $error,info:  $info,path_info:  $services->name_files->path_info);
        }

        if($existe_en_local){
            $cliente_remoto_upd['insertado'] = 1;
            $cliente_remoto_upd['status'] = 1;
            $upd = $cliente_modelo_remota->modifica_bd($cliente_remoto_upd, 'cliente', $cliente_id);
            if(errores::$error){
                $error = (new errores())->error('Error al actualizar cliente remoto', $upd);
                (new error_write())->out(error: $error,info:  $info,path_info:  $services->name_files->path_info);
            }
            var_dump($upd);
        }
    }


}
$services->finaliza_servicio();