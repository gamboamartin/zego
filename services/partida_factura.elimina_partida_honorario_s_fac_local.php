<?php
include "init.php";
const PATH_BASE = '/var/www/html/zego/';


require PATH_BASE.'vendor/autoload.php';

use gamboamartin\errores\errores;
use gamboamartin\services\services;
use models\partida_factura;
use gamboamartin\calculo\calculo;
use config\empresas;


$services = new services(path: __FILE__);

$calculo = new calculo();
$empresas = new empresas();
$empresas_data = $empresas->empresas;

foreach ($empresas_data as $empresa){

    $link = $services->conecta_local_mysqli(empresa: $empresa);
    if(errores::$error){
        $error = (new errores())->error('Error al conectar remoto', $link);
        print_r($error);
        die('Error');
    }

    $partida_factura_modelo = new partida_factura($link);
    $limit_sql = 10;

    $keys = array();
    $keys[] = 'partida_factura_insumo_id';
    $filtro_sql[] = 'partida_factura.factura_id IS NULL';
    $filtro_sql[] = "partida_factura.insumo_id = '233'";
    $filtro_sql[] = "partida_factura.valor_unitario = '0.01'";
    $dels = $partida_factura_modelo->elimina_partidas_por_key(filtros_sql: $filtro_sql,keys: $keys,
        limit_sql:  $limit_sql,n_dias_1:  30,n_dias_2:  1);
    if(errores::$error){
        $error = (new errores())->error('Error al limpiar', $dels);
        print_r($error);
        die('Error');
    }
    var_dump($dels);

}

$services->finaliza_servicio();