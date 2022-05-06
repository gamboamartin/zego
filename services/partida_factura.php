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
use models\partida_factura;
use gamboamartin\calculo\calculo;
use config\empresas;

$file_lock = __FILE__.'.lock';

if(file_exists($file_lock)){
    echo 'El servicio esta en proceso';
    exit;
}
file_put_contents($file_lock, '');
if(!file_exists($file_lock)){
    $error = (new errores())->error('Error al crear archivo lock', $file_lock);
    print_r($error);
    die('Error');
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
    }exit;

    if(!$link_local->error){

        mysqli_set_charset($link_local, "utf8");
        $sql = "SET sql_mode = '';";
        $link_local->query($sql);

        $tabla = 'partida_factura';
        $hoy = date('Y-m-d 00:00:00');
        $tres_dias = (new gamboamartin\calculo\calculo())->obten_fecha_resta(fecha: $hoy, n_dias: 2,
            tipo_val:'fecha_hora_min_sec_esp' );
        $ayer = (new gamboamartin\calculo\calculo())->obten_fecha_resta(fecha: $hoy, n_dias: 1,
            tipo_val:'fecha_hora_min_sec_esp' );

        $partida_factura_modelo = new partida_factura($link_local);

        $campo = 'partida_factura.fecha_alta';
        $fecha_final = $ayer;
        $fecha_inicial = $tres_dias;
        $tipo_val = 'fecha_hora_min_sec_esp';
        $r_partidas = $partida_factura_modelo->rows_entre_fechas(campo:$campo, fecha_final: $fecha_final,
            fecha_inicial: $fecha_inicial, tabla: $tabla, tipo_val: $tipo_val);

        if(errores::$error){
            $error = (new errores())->error('Error al obtener partidas', $r_partidas);
            print_r($error);
            die('Error');
        }

        $partidas = $r_partidas['registros'];
        $keys = array();
        $keys[] = 'partida_factura_insumo_id';
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
        }
        
    }

}

unlink($file_lock);