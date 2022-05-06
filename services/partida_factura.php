<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
setlocale(LC_ALL, 'es_MX.utf8');
date_default_timezone_set('America/Mexico_City');
set_time_limit(60000);
ini_set('memory_limit', '-1');
ini_set('upload_max_filesize', '2048M');
ini_set('post_max_size', '2048M');

define('PATH_BASE','/var/www/html/zego/');

require PATH_BASE.'vendor/autoload.php';
require_once (PATH_BASE.'clases/numero_texto.php');
require_once(PATH_BASE.'config/seguridad.php');
require_once(PATH_BASE.'requires.php');

use models\partida_factura;

$calculo = new calculo();

$hoy = date('Y-m-d');

$fecha_consulta = $calculo->obten_fecha_resta($hoy,5);


$empresas = new empresas();

$empresas_data = $empresas->empresas;

foreach ($empresas_data as $empresa){

    $host = $empresa['host'];
    $user = $empresa['user'];
    $pass = $empresa['pass'];
    $nombre_base_datos = $empresa['nombre_base_datos'];
    $link_local = mysqli_connect($host, $user, $pass);

    $consulta = "USE ".$nombre_base_datos;
    $link_local->query($consulta);

    if(!$link_local->error){

        mysqli_set_charset($link_local, "utf8");
        $sql = "SET sql_mode = '';";
        $link_local->query($sql);

        $tabla = 'partida_factura';
        $hoy = date('Y-m-d 00:00:00');
        $tres_dias = (new gamboamartin\calculo\calculo())->obten_fecha_resta(fecha: $hoy, n_dias: 3,
            tipo_val:'fecha_hora_min_sec_esp' );
        $ayer = (new gamboamartin\calculo\calculo())->obten_fecha_resta(fecha: $hoy, n_dias: 1,
            tipo_val:'fecha_hora_min_sec_esp' );


        $where = " partida_factura.fecha_alta < '$hoy' and partida_factura";
        $partida_factura_modelo = new partida_factura($link_local);

        $campo = 'partida_factura.fecha_alta';
        $fecha_final = $ayer;
        $fecha_inicial = $tres_dias;
        $tipo_val = 'fecha_hora_min_sec_esp';
        $r_partidas = $partida_factura_modelo->rows_entre_fechas(campo:$campo, fecha_final: $fecha_final,
            fecha_inicial: $fecha_inicial, tabla: $tabla, tipo_val: $tipo_val);
        print_r($r_partidas);exit;


    }


}