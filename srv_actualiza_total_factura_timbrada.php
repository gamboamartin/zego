<?php
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


        $factura_modelo = new factura($link);
        $partida_factura_modelo = new partida_factura($link);

        $filtro = array('factura.status_factura' =>'timbrada');

        $r_factura = $factura_modelo->filtro_and('factura',$filtro," AND factura.fecha >= '$fecha_consulta'");


        if(isset($r_factura['error'])){
            if($r_factura['error'] == 1){
                echo "<br><br>";
                print_r($empresa['nombre_base_datos']);
                echo "<br><br>";
                print_r($r_factura);
                echo "<br><br>";
                exit;
            }
        }

        $facturas = $r_factura['registros'];

        foreach($facturas as $factura){
            echo "-------------------Factura Folio: ---------------------";
            print_r($factura['factura_folio']);
            echo "<br><br>";
            echo "----Factura: id----";
            print_r($factura['factura_id']);
            echo "<br><br>";

            $factura_id = $factura['factura_id'];
            $folio = $factura['factura_folio'];
            $factura_total = round($factura['factura_total'],2);
            $factura_sub_total = $factura['factura_sub_total'];
            $factura_total_impuestos_trasladados = $factura['factura_total_impuestos_trasladados'];
            $factura_total_impuestos_retenidos = $factura['factura_total_impuestos_retenidos'];
            $factura_total_impuestos_trasladados_iva = $factura['factura_total_impuestos_trasladados_iva'];
            $descuentos_anteriores = $factura['factura_descuento'];


            echo "Factura Total: ";
            print_r($factura_total);
            echo "<br><br>";



            $filtro = array('factura.id'=>$factura_id,'factura.status_factura'=>'timbrada');

            echo "Filtro: ";
            print_r($filtro);
            echo "<br><br>";

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


                echo "Factura Importe: ";
                print_r($factura_sub_total_nuevo);
                echo "<br><br>";
                echo "Factura Sub Total Nuevo: ";
                print_r($factura_sub_total_nuevo);
                echo "<br><br>";
                echo "Factura Total Nuevo: ";
                print_r($factura_total);
                echo "<br><br>";


            }


            $factura_update = array();
            $factura_update['status'] = 'activo';
            $aplica_cambio = false;
            if($factura_total != $factura_total_nuevo){
                echo "<br>---Aplica cambio de total<br><br>";
                $aplica_cambio = true;
                $factura_update['total'] = $factura_total_nuevo;
                echo "<br>---Total anterior $factura_total Total Nuevo $factura_total_nuevo<br><br>";
            }
            if($factura_sub_total != $factura_sub_total_nuevo){
                echo "<br>---Aplica cambio de sub total<br><br>";
                $aplica_cambio = true;
                $factura_update['sub_total'] = $factura_sub_total_nuevo;
            }
            if($factura_total_impuestos_trasladados != $factura_total_impuestos_trasladados_nuevo){
                echo "<br>---Aplica cambio de impuestos trasladados<br><br>";
                $aplica_cambio = true;
                $factura_update['total_impuestos_trasladados'] = $factura_total_impuestos_trasladados_nuevo;
            }
            if($factura_total_impuestos_retenidos != $factura_total_impuestos_retenidos_nuevo){
                echo "<br>---Aplica cambio de impuestos trasladados<br><br>";
                $aplica_cambio = true;
                $factura_update['total_impuestos_retenidos'] = $factura_total_impuestos_retenidos_nuevo;
            }
            if($factura_total_impuestos_trasladados_iva_nuevo != $factura_total_impuestos_trasladados_iva){
                echo "<br>---Aplica cambio de impuestos trasladados iva<br><br>";
                $aplica_cambio = true;
                $factura_update['total_impuestos_trasladados_iva'] = $factura_total_impuestos_trasladados_iva_nuevo;
            }
            if($descuentos_anteriores != $total_descuentos_nuevo){
                echo "<br>---Aplica cambio de descuentos<br><br>";
                echo "<br>---Descuento anterior $descuentos_anteriores Descuento Nuevo $total_descuentos_nuevo<br><br>";
                $aplica_cambio = true;
                $factura_update['descuento'] = $total_descuentos_nuevo;
            }

            if($aplica_cambio){
                echo "<br><br>";
                print_r($empresa['nombre_base_datos']);
                echo "<br><br>Factura=>$factura_id<br><br>";
                echo "<br><br>Folio=>$folio<br><br>";
                echo"<br><br>";
                print_r($factura_update);
                echo"<br><br>";
                print_r($factura_modelo->modifica_bd($factura_update,'factura',$factura_id));
            }

        }


    }


}