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

    $conexiones = $services->conexiones(empresa: $empresa);
    if(errores::$error){
        $error = (new errores())->error('Error al conectar', $conexiones);
        print_r($error);
        die('Error');
    }

    var_dump($conexiones);

    $factura_modelo_remota = new factura(link: $conexiones->remote);
    $factura_modelo_local = new factura(link: $conexiones->local);



    $facturas = $factura_modelo_remota->facturas_sin_insertar(limit:100,n_dias:  5, services: $services);
    if(errores::$error){
        $error = (new errores())->error('Error al obtener registros', $facturas);
        print_r($error);
        die('Error');
    }

    foreach($facturas as $factura){
        $existe = $factura_modelo_local->existe_factura($factura['id']);
        if(errores::$error){
            $error = (new errores())->error('Error al verificar si existe', $existe);
            print_r($error);
            die('Error');
        }
        if(!$existe){
            $keys = array('lugar_expedicion','calle_expedicion','metodo_pago_codigo','total','sub_total',
                'forma_pago_codigo','fecha','folio','rfc_emisor','regimen_fiscal_emisor_codigo','cliente_id',
                'cliente_rfc','uso_cfdi_codigo');

            foreach($factura as $campo=>$value){
                $value = trim($value);
                $inserta = true;
                if($value===''){
                    $inserta = false;
                }

                if($inserta){
                    $r_ins_local = $factura_modelo_local->alta_bd($factura, 'factura');
                    if(errores::$error){
                        $error = (new errores())->error('Error al insertar en local', $r_ins_local);
                        print_r($error);
                        die('Error');
                    }
                    var_dump($r_ins_local);
                }
            }
        }
    }


}
$services->finaliza_servicio();