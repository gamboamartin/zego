<?php


use config\empresas;
use models\partida_factura;

require 'vendor/autoload.php';
require_once ('clases/numero_texto.php');
require_once('config/seguridad.php');
require_once('requires.php');

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
    $link = mysqli_connect($host, $user, $pass);

    $consulta = "USE ".$nombre_base_datos;
    $link->query($consulta);

    if(!$link->error){

        mysqli_set_charset($link, "utf8");
        $sql = "SET sql_mode = '';";
        $link->query($sql);


        $partida_factura_modelo = new partida_factura($link);

        $filtro = array('factura.status_factura' =>'sin timbrar');

        $r_partida_factura = $partida_factura_modelo->filtro_and('partida_factura',$filtro,
            " AND factura.fecha >= '$fecha_consulta'");


        if(isset($r_partida_factura['error'])){
            if($r_partida_factura['error'] == 1){
                echo "<br><br>";
                print_r($empresa['nombre_base_datos']);
                echo "<br><br>";
                print_r($r_partida_factura);
                echo "<br><br>";
                exit;
            }
        }

        $partidas_factura = $r_partida_factura['registros'];

        foreach($partidas_factura as $partida_factura){





            $partida_factura_id = $partida_factura['partida_factura_id'];
            $valor_unitario = round($partida_factura['partida_factura_valor_unitario'],2);
            $cantidad = round($partida_factura['partida_factura_cantidad'],2);
            $importe_anterior = $partida_factura['partida_factura_importe'];
            $base_anterior = $partida_factura['partida_factura_base'];
            $importe_neto_anterior = $partida_factura['partida_factura_importe_neto'];
            $total_impuestos_trasladados_anterior = $partida_factura['partida_factura_total_impuestos_trasladados'];
            $total_impuestos_retenidos_anterior = $partida_factura['partida_factura_total_impuestos_retenidos'];
            $descripcion_anterior = $partida_factura['partida_factura_insumo_descripcion'];


            if(!isset($partida_factura['partida_factura_descuento']) ){
                $partida_factura['partida_factura_descuento'] = 0;
            }


            $importe = round($valor_unitario * $cantidad,2);
            $base = round($valor_unitario * $cantidad,2);
            $traslado = round(round($base) * round($partida_factura['partida_factura_tasa_cuota'],2),2);
            $retenido = round(round($base) * round($partida_factura['partida_factura_tasa_cuota_retenido'],2),2);
            $importe_neto = round($importe + $traslado - $retenido - round($partida_factura['partida_factura_descuento']),2);


            $partida_factura_upd = array();
            $partida_factura_upd['status'] = 'activo';
            $aplica_cambio = false;
            if($importe_anterior != $importe){
                echo "<br>---Aplica cambio de importe<br><br>";
                $aplica_cambio = true;
                $partida_factura_upd['importe'] = $importe;
                echo "<br>---Importe anterior $importe_anterior Importe Nuevo $importe<br><br>";
            }


            if($base_anterior != $base){
                echo "<br>---Aplica cambio de base<br><br>";
                $aplica_cambio = true;
                $partida_factura_upd['base'] = $base;
                echo "<br>---Base anterior $base_anterior Importe Nuevo $base<br><br>";
            }

            if($importe_neto_anterior != $importe_neto){
                echo "<br>---Aplica cambio de Importe Neto<br><br>";
                $aplica_cambio = true;
                $partida_factura_upd['importe_neto'] = $importe_neto;
                echo "<br>---Importe neto anterior $importe_neto_anterior Importe neto Nuevo $importe_neto<br><br>";
            }

            if($total_impuestos_trasladados_anterior != $traslado){
                echo "<br>---Aplica cambio de Traslados<br><br>";
                $aplica_cambio = true;
                $partida_factura_upd['total_impuestos_trasladados'] = $traslado;
                echo "<br>---Traslados anterior $total_impuestos_trasladados_anterior Traslado  Nuevo $traslado<br><br>";
            }

            if($total_impuestos_retenidos_anterior != $retenido){
                echo "<br>---Aplica cambio de Retenidos<br><br>";
                $aplica_cambio = true;
                $partida_factura_upd['total_impuestos_retenidos'] = $retenido;
                echo "<br>---retenidos anterior $total_impuestos_retenidos_anterior Retenidos  Nuevo $retenido<br><br>";
            }

            if($aplica_cambio){
                echo "<br><br>";
                print_r($empresa['nombre_base_datos']);
                echo "<br>Partida factura id $partida_factura_id<br>";
                print_r($partida_factura_upd);
                echo"<br><br>";
                print_r($partida_factura_modelo->modifica_bd($partida_factura_upd,'partida_factura',$partida_factura_id));
            }

        }

    }


}