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

foreach ($empresas_data as $empresa) {

    $host = $empresa['host'];
    $user = $empresa['user'];
    $pass = $empresa['pass'];
    $nombre_base_datos = $empresa['nombre_base_datos'];
    $link = mysqli_connect($host, $user, $pass);

    $consulta = "USE " . $nombre_base_datos;
    $link->query($consulta);

    if (!$link->error) {

        mysqli_set_charset($link, "utf8");
        $sql = "SET sql_mode = '';";
        $link->query($sql);


        $nota_credito_modelo = new nota_credito($link);
        $pago_ = new nota_credito($link);
        $factura_modelo = new factura($link);


        $r_nota_credito = $nota_credito_modelo->obten_registros('nota_credito', " AND nota_credito.fecha >= '$fecha_consulta'");


        if (isset($r_nota_credito['error'])) {
            if ($r_nota_credito['error'] == 1) {
                echo "<br><br>";
                print_r($empresa['nombre_base_datos']);
                echo "<br><br>";
                print_r($r_nota_credito);
                echo "<br><br>";
                exit;
            }
        }

        $notas_credito = $r_nota_credito['registros'];

        foreach ($notas_credito as $nota_credito) {
            $factura_id = $nota_credito['factura_id'];
            $saldo_anterior = round($nota_credito['factura_saldo'],2);
            $saldo_real = round($factura_modelo->obten_saldo_real_factura($factura_id),2);



            if($saldo_anterior != $saldo_real){
                echo "<br><br>";
                print_r($empresa['nombre_base_datos']);
                echo "<br><br> Cambia saldo <br><br>";
                echo "Factura $factura_id<br>";
                echo "Saldo anterior ".$saldo_anterior;
                echo "<br><br>";
                echo "Saldo real ".$saldo_real;
                echo "<br><br>";

                if($saldo_real < 0){
                    echo "<br>Error no ajusta saldo<br>";
                }
                else{
                    $factura_nueva['status'] = '1';
                    $factura_nueva['saldo'] = "$saldo_real";

                    $r_factura = $factura_modelo->modifica_bd($factura_nueva,'factura',$factura_id);
                    print_r($r_factura);
                }

            }

        }
    }
}


