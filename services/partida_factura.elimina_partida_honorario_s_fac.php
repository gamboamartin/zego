<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
setlocale(LC_ALL, 'es_MX.utf8');
date_default_timezone_set('America/Mexico_City');
set_time_limit(60000);
ini_set('memory_limit', '-1');
ini_set('upload_max_filesize', '2048M');
ini_set('post_max_size', '2048M');
const PATH_BASE = '/var/www/html/zego/';


require PATH_BASE.'vendor/autoload.php';

use gamboamartin\errores\errores;
use gamboamartin\services\services;
use models\partida_factura;
use gamboamartin\calculo\calculo;
use config\empresas;



$data_service = (new services())->verifica_servicio(path: __FILE__);
if(errores::$error){
    $error = (new errores())->error('Error al verificar servicio', $data_service);
    print_r($error);
    die('Error');
}

if($data_service->corriendo){
    echo 'El servicio esta corriendo '.__FILE__;
    exit;
}

$calculo = new calculo();

$empresas = new empresas();

$empresas_data = $empresas->empresas;

foreach ($empresas_data as $empresa){

    $host_r = $empresa['remote_host'];
    $user_r = $empresa['remote_user'];
    $pass_r = $empresa['remote_pass'];
    $nombre_base_datos_r = $empresa['remote_nombre_base_datos'];

    /*
    $host_r = $empresa['host'];
    $user_r = $empresa['user'];
    $pass_r = $empresa['pass'];
    $nombre_base_datos_r = $empresa['nombre_base_datos'];
    */

    $link_thecloud = (new services)->conecta_mysqli(host: $host_r,
        nombre_base_datos:  $nombre_base_datos_r, pass: $pass_r,user:  $user_r);
    if(errores::$error){
        $error = (new errores())->error('Error al conectar remoto', $link_thecloud);
        print_r($error);
        die('Error');
    }


    $fechas = (new calculo())->rangos_fechas(n_dias_1:30, n_dias_2: 2, tipo_val: 'fecha_hora_min_sec_esp');
    if(errores::$error){
        $error = (new errores())->error('Error al obtener fechas', $fechas);
        print_r($error);
        die('Error');
    }

    var_dump($fechas);

    $partida_factura_modelo = new partida_factura($link_thecloud);
    $limit_sql = 1;
    $filtro_sql[] = 'partida_factura.factura_id IS NULL';
    $filtro_sql[] = "partida_factura.insumo_id = '233'";
    $filtro_sql[] = "partida_factura.valor_unitario = '0.01'";

    $partidas = $partida_factura_modelo->partidas_por_limpiar(fechas: $fechas, filtro_sql: $filtro_sql,
        limit_sql: $limit_sql);

    if(errores::$error){
        $error = (new errores())->error('Error al obtener partidas', $partidas);
        print_r($error);
        die('Error');
    }

    var_dump($partidas);

    $keys = array();
    $keys[] = 'partida_factura_factura_id';

    $dels = $partida_factura_modelo->elimina_partidas_vacias(keys: $keys,partidas:  $partidas);
    if(errores::$error){
        $error = (new errores())->error('Error al limpiar', $dels);
        print_r($error);
        die('Error');
    }
    var_dump($dels);

}

unlink($data_service->path_lock);
unlink($data_service->path_info);