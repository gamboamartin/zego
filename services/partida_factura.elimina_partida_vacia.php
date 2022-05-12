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

    $link = $services->conecta_remoto_mysqli(empresa: $empresa);
    if(errores::$error){
        $error = (new errores())->error('Error al conectar remoto', $link);
        print_r($error);
        die('Error');
    }
    var_dump($services->data_conexion->host);

    $fechas = (new calculo())->rangos_fechas(n_dias_1:30, n_dias_2: 1, tipo_val: 'fecha_hora_min_sec_esp');
    if(errores::$error){
        $error = (new errores())->error('Error al obtener fechas', $fechas);
        print_r($error);
        die('Error');
    }

    var_dump($fechas);

    $partida_factura_modelo = new partida_factura($link);
    $limit_sql = 2000;
    $filtro_sql[] = 'partida_factura.insumo_id IS NULL';

    $partidas = $partida_factura_modelo->partidas_por_limpiar(fechas: $fechas, filtro_sql: $filtro_sql,
        limit_sql: $limit_sql);

    if(errores::$error){
        $error = (new errores())->error('Error al obtener partidas', $partidas);
        print_r($error);
        die('Error');
    }

    var_dump($partidas);

    $keys = array();
    $keys[] = 'partida_factura_insumo_id';

    $dels = $partida_factura_modelo->elimina_partidas_vacias(keys: $keys,partidas:  $partidas);
    if(errores::$error){
        $error = (new errores())->error('Error al limpiar', $dels);
        print_r($error);
        die('Error');
    }
    var_dump($dels);

}
$services->finaliza_servicio();