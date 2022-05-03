<?php
require_once ('clases/fpdf/html2pdf.php');
require_once ('clases/numero_texto.php');
require_once('config/seguridad.php');
require_once('requires.php');

function saldos($factura_id, $link){

    $partida_factura_modelo = new Partida_Factura($link);

    $filtro = array('factura.id'=>$factura_id);

    $r_partida_factura = $partida_factura_modelo->filtro_and('partida_factura',$filtro);

    $partidas_factura = $r_partida_factura['registros'];

    $factura_total_nuevo = 0;
    $factura_sub_total_nuevo = 0;
    $factura_total_impuestos_trasladados_nuevo = 0;
    $factura_total_impuestos_retenidos_nuevo = 0;
    $factura_total_impuestos_trasladados_iva_nuevo = 0;
    $total_descuentos_nuevo = 0;


    foreach($partidas_factura as $partida_factura){
        $cantidad = round($partida_factura['partida_factura_cantidad'],2);
        $valor_unitario = round($partida_factura['partida_factura_valor_unitario'],2);
        $factor_iva_trasladado = round($partida_factura['partida_factura_tasa_cuota'],2);
        $factor_iva_retenido = round($partida_factura['partida_factura_tasa_cuota_retenido'],2);
        $descuentos = round($partida_factura['partida_factura_descuento'],2);


        $importe = round($cantidad * $valor_unitario,2);
        $iva_trasladado = round($importe * $factor_iva_trasladado,2);
        $iva_retenido = round($importe * $factor_iva_retenido,2);

        $total_partida = round($importe + $iva_trasladado - $iva_retenido - $descuentos,2);
        $factura_total_nuevo = round($factura_total_nuevo + $total_partida,2);
        $factura_sub_total_nuevo = $factura_sub_total_nuevo + $importe;
        $factura_total_impuestos_trasladados_nuevo=$factura_total_impuestos_trasladados_nuevo+$iva_trasladado;
        $factura_total_impuestos_retenidos_nuevo=$factura_total_impuestos_retenidos_nuevo+$iva_retenido;
        $factura_total_impuestos_trasladados_iva_nuevo=$factura_total_impuestos_trasladados_iva_nuevo+$iva_trasladado;
        $total_descuentos_nuevo = $total_descuentos_nuevo+$descuentos;

    }

    return array('total_factura'=>$factura_total_nuevo,'descuento'=>$total_descuentos_nuevo);
}

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

    $consulta = 'USE '.$nombre_base_datos;
    $link->query($consulta);

    if(!$link->error){

        mysqli_set_charset($link, 'utf8');
        $sql = "SET sql_mode = '';";
        $link->query($sql);


        $partida_factura_modelo = new partida_factura($link);
        $factura_modelo = new factura($link);

        $filtro = array('factura.status_factura' =>'sin timbrar');

        $r_partida_factura = $partida_factura_modelo->filtro_and('partida_factura',$filtro," AND factura.fecha >= '$fecha_consulta'");


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

        foreach($partidas_factura as $partida_factura) {
            if((int)$partida_factura['partida_factura_insumo_id'] ===275  || (int)$partida_factura['partida_factura_insumo_id'] ===233){
                print_r($partida_factura);
                if((float)$partida_factura['factura_total'] > 1.0 && (float)$partida_factura['partida_factura_base'] === 0.0){
                    $partida_factura_modelo->elimina_bd('partida_factura',$partida_factura['partida_factura_id']);
                    $totales  = saldos($partida_factura['factura_id'],$link);
                    $factura_modelo->modifica_bd(
                        array('status'=>1,'total'=>$totales['total_factura'],'descuento'=>$totales['descuento']),
                        'factura',$partida_factura['factura_id']);
                }
            }
        }

    }

}