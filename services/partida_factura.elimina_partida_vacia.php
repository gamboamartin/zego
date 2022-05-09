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

use config\conexion;
use gamboamartin\errores\errores;
use models\partida_factura;
use gamboamartin\calculo\calculo;
use config\empresas;
use services\services;

$file_lock = __FILE__.'.lock';

$servicio_corriendo = (new services())->verifica_servicio(path: $file_lock);
if(errores::$error){
    $error = (new errores())->error('Error al verificar servicio', $servicio_corriendo);
    print_r($error);
    die('Error');
}

if($servicio_corriendo){
    echo 'El servicio esta corriendo '.$file_lock;
    exit;
}


$calculo = new calculo();

$empresas = new empresas();

$empresas_data = $empresas->empresas;

foreach ($empresas_data as $empresa){

    $host = $empresa['host'];
    $user = $empresa['user'];
    $pass = $empresa['pass'];
    $nombre_base_datos = $empresa['nombre_base_datos'];


    $link_local = (new conexion())->conecta($host, $nombre_base_datos, $pass, $user);
    if(errores::$error){
        $error = (new errores())->error('Error al conectar local', $link_local);
        print_r($error);
        die('Error');
    }

    $host_r = $empresa['remote_host'];
    $user_r = $empresa['remote_user'];
    $pass_r = $empresa['remote_pass'];
    $nombre_base_datos_r = $empresa['remote_nombre_base_datos'];

    $link_thecloud = (new conexion())->conecta($host, $nombre_base_datos, $pass, $user);
    if(errores::$error){
        $error = (new errores())->error('Error al conectar thecloud', $link_thecloud);
        print_r($error);
        die('Error');
    }

    if(!$link_thecloud->error){

        $tabla = 'partida_factura';
        $hoy = date('Y-m-d 00:00:00');
        $tres_dias = (new gamboamartin\calculo\calculo())->obten_fecha_resta(fecha: $hoy, n_dias: 30,
            tipo_val:'fecha_hora_min_sec_esp' );
        if(errores::$error){
            $error = (new errores())->error('Error al obtener dias', $tres_dias);
            print_r($error);
            die('Error');
        }


        $ayer = (new gamboamartin\calculo\calculo())->obten_fecha_resta(fecha: $hoy, n_dias: 2,
            tipo_val:'fecha_hora_min_sec_esp' );

        if(errores::$error){
            $error = (new errores())->error('Error al obtener dias', $ayer);
            print_r($error);
            die('Error');
        }

        var_dump($ayer);

        $partida_factura_modelo = new partida_factura($link_thecloud);

        $campo = 'partida_factura.fecha_alta';
        $fecha_final = $ayer;
        $fecha_inicial = $tres_dias;
        $tipo_val = 'fecha_hora_min_sec_esp';
        $filtro_sql = 'partida_factura.insumo_id IS NULL';
        $limit_sql = 100;
        $r_partidas = $partida_factura_modelo->rows_entre_fechas(campo:$campo, fecha_final: $fecha_final,
            fecha_inicial: $fecha_inicial, filtro_sql: $filtro_sql, limit_sql: $limit_sql, tabla: $tabla,
            tipo_val: $tipo_val);

        if(errores::$error){
            $error = (new errores())->error('Error al obtener partidas', $r_partidas);
            print_r($error);
            die('Error');
        }

        var_dump($r_partidas);


        $partidas = $r_partidas['registros'];
        $keys = array();
        $keys[] = 'partida_factura_insumo_id';

        $contador = 0;
        foreach($partidas as $partida){
            $del = $partida_factura_modelo->elimina_partida_vacia(keys: $keys, partida: $partida);
            if(errores::$error){
                $error = (new errores())->error('Error al limpiar', $del);
                print_r($error);
                die('Error');
            }
            if($del->del){
                print_r($del);
                echo "<br><br>";
            }
            if($del->del){
                $contador++;
            }
            if($contador >=10){
                break;
            }
        }
        
    }

}

unlink($file_lock);