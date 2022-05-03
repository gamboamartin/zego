<?php

use gamboamartin\errores\errores;

require 'vendor/autoload.php';
require_once ('clases/fpdf/html2pdf.php');
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

            $obj_imp_anterior = $partida_factura['partida_factura_obj_imp'];


            $partida_factura_upd = array();
            $partida_factura_upd['status'] = 'activo';
            $aplica_cambio = false;
            if($obj_imp_anterior !== $obj_imp){
                echo "<br>---Aplica cambio de obj imp<br><br>";
                $aplica_cambio = true;
                $partida_factura_upd['obj_imp'] = $obj_imp;
                echo "<br>---<br><br>";
            }


        }

    }


}