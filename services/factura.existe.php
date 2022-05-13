<?php
include "init.php";
const PATH_BASE = '/var/www/html/zego/';


require PATH_BASE.'vendor/autoload.php';

use gamboamartin\errores\errores;
use gamboamartin\services\services;
use gamboamartin\calculo\calculo;
use config\empresas;
use models\factura;

$services = new services(path: __FILE__);
$calculo = new calculo();

$empresas = new empresas();
$empresas_data = $empresas->empresas;

foreach ($empresas_data as $empresa){

    $link_remote = $services->conecta_remoto_mysqli(empresa: $empresa);
    if(errores::$error){
        $error = (new errores())->error('Error al conectar remoto', $link_remote);
        print_r($error);
        die('Error');
    }
    var_dump($services->data_conexion->host);

    $link_local = $services->conecta_local_mysqli(empresa: $empresa);
    if(errores::$error){
        $error = (new errores())->error('Error al conectar remoto', $link_local);
        print_r($error);
        die('Error');
    }
    var_dump($services->data_conexion->host);


    $factura_modelo_remota = new factura(link: $link_remote);
    $factura_modelo_local = new factura(link: $link_local);

    $hoy = date('Y-m-d 23:59:59');

    $fecha = (new calculo())->obten_fecha_resta(fecha: $hoy,n_dias: 5,tipo_val: 'fecha_hora_min_sec_esp');

    $sql_fecha_alta = "factura.fecha_alta >= '$fecha'";
    $sql_fecha_alta .= " AND factura.insertado = 0 ";

    $r_factura = $factura_modelo_remota->registros_puros(limit:100, tabla: 'factura', where: $sql_fecha_alta);
    if(errores::$error){
        $error = (new errores())->error('Error al obtener registros', $r_factura);
        print_r($error);
        die('Error');
    }

    $facturas = $r_factura['registros'];

    foreach($facturas as $factura){
        $existe = $factura_modelo_local->existe_por_id(id: $factura['id'], tabla: 'factura');
        if(errores::$error){
            $error = (new errores())->error('Error al verificar si existe', $existe);
            print_r($error);
            die('Error');
        }
        if($existe){
            $factura_remota['insertado'] = 1;
            $r_factura_remota = $factura_modelo_remota->modifica_bd($factura_remota, 'factura', $factura['id']);
            if(errores::$error){
                $error = (new errores())->error('Error al actualizar', $r_factura_remota);
                print_r($error);
                die('Error');
            }
            var_dump($r_factura_remota);
        }

    }



}
$services->finaliza_servicio();