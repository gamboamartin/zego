<?php
namespace controllers;

use config\empresas;
use facturas;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use models\cliente;
use models\cuenta_bancaria;
use models\factura;
use models\factura_relacionada;
use models\insumo;
use models\partida_factura;
use my_pdf;
use NumeroTexto;
use repositorio;
use SimpleXMLElement;
use SoapClient;
use SoapFault;

class controlador_cliente extends controlador_base{
    public $rfc;
    public $numero_empresa;
    public $razon_social;
    public $regimen_fiscal;
    public $nombre_regimen_fiscal;
    public $cliente_id;
    public $datos_cliente;
    public $cuentas_bancarias;
    public $tablas;
    public $campos_llenables;
    public $uso_cfdi_id;
    public $moneda_id;
    public $forma_pago_id;
    public $metodo_pago_id;
    public $condiciones_pago;
    public $script;
    public $partidas_html;
    public $tamano_letra = 7;
    public $saldo_factura;
    public $cuentas_empresa;
    public $clientes;
    public $datos_guardar;
    public $datos_guardar_update;
    public $encabezado_html;
    public $footer_html;
    public $datos_emisor;
    public $datos_receptor;
    public $datos_comprobante;
    public $datos_partidas;
    public $datos_impuestos;
    public $datos_impuestos_traslados;
    public $datos_impuestos_retenidos;
    public $numero_texto;
    public $factura_id;
    public $sufijo;
    public $folio_inicial;
    public $folio_muestra;
    public $insumo_automatico;
    public $status_factura;
    public $anticipo;
    public $factura;
    public $cantidad_automatico;
    public $facturas_relacionadas;

    public function actualiza_masiva_bd(){
        $cliente_modelo = new Cliente($this->link);
        $cliente_id = $_GET['cliente_id'];
        $_POST['status'] = 1;
        $cliente_modelo->modifica_bd($_POST,'cliente',$cliente_id);
    }

    public function actualiza_responsable_pago(){
        $tabla = $_GET['seccion'];
        $this->registro_id = $_GET['cliente_id'];
        $resultado = $this->modelo->modifica_bd($_POST, $tabla, $this->registro_id);

        if($resultado['error']){
            $mensaje = $resultado['mensaje'];
            header("Location: ./index.php?seccion=$tabla&accion=modifica&mensaje=$mensaje&tipo_mensaje=error&cliente_id=$this->registro_id");
            exit;
        }
        header("Location: ./index.php?seccion=$tabla&accion=lista&mensaje=Registro modificado con éxito&tipo_mensaje=exito");
    }

    public function actualiza_uso_cfdi(){
        $tabla = $_GET['seccion'];
        $this->registro_id = $_GET['cliente_id'];

        $_POST['status'] = 1;

        $resultado = $this->modelo->modifica_bd($_POST, $tabla, $this->registro_id);

        if($resultado['error']){
            $mensaje = $resultado['mensaje'];
            header("Location: ./index.php?seccion=$tabla&accion=carga_datos_cfdi&mensaje=$mensaje&tipo_mensaje=error&cliente_id=$this->registro_id");
            exit;
        }
        header("Location: ./index.php?seccion=$tabla&accion=carga_datos_cfdi&mensaje=Registro modificado con éxito&tipo_mensaje=exito&cliente_id=$this->registro_id");
        exit;
    }

    public function alta_cuenta_bd(){
        $tabla = 'cuenta_bancaria';
        $modelo = new cuenta_bancaria($this->link);
        $resultado = $modelo->alta_bd($_POST, $tabla);

        $cliente_id = $_POST['cliente_id'];

        if($resultado['error']){
            $mensaje = $resultado['mensaje'];
            header("Location: ./index.php?seccion=cliente&accion=asigna_cuenta_bancaria&mensaje=$mensaje&tipo_mensaje=error&cliente_id=$cliente_id");
            exit;
        }

        header("Location: ./index.php?seccion=cliente&accion=asigna_cuenta_bancaria&mensaje=Registro insertado con éxito&tipo_mensaje=exito&cliente_id=$cliente_id");
    }

    public function asigna_cuenta_bancaria(){
        $breadcrumbs = array('lista','alta');
        $this->breadcrumbs = $this->directiva->nav_breadcumbs(12, 0, $breadcrumbs);

        $this->cliente_id = $_GET['cliente_id'];
        $cliente = new cliente($this->link);
        $clientes = $cliente->obten_por_id('cliente', $this->cliente_id);
        $this->datos_cliente = $clientes['registros'][0];

        $cuenta_bancaria_modelo = new cuenta_bancaria($this->link);

        $filtros = array('cliente_id'=>$_GET['cliente_id']);
        $resultado_cuentas_bancarias = $cuenta_bancaria_modelo->filtro_and('cuenta_bancaria',$filtros);

        $this->cuentas_bancarias = $resultado_cuentas_bancarias['registros'];

    }

    public function captura_masiva(){
        $cliente_modelo = new cliente($this->link);
        $resultado = $cliente_modelo->obten_registros_activos('cliente');
        $this->clientes = $resultado['registros'];
    }

    public function carga_datos_cfdi(){
        $breadcrumbs = array('lista','alta');
        $this->breadcrumbs = $this->directiva->nav_breadcumbs(12, 0, $breadcrumbs);
        $this->cliente_id = $_GET['cliente_id'];

        $cliente = new cliente($this->link);
        $clientes = $cliente->obten_por_id('cliente', $this->cliente_id);
        $this->datos_cliente = $clientes['registros'][0];

        $this->uso_cfdi_id = $clientes['registros'][0]['uso_cfdi_id'];
        $this->moneda_id = $clientes['registros'][0]['moneda_id'];
        $this->forma_pago_id = $clientes['registros'][0]['forma_pago_id'];
        $this->metodo_pago_id = $clientes['registros'][0]['metodo_pago_id'];

    }

    public function carga_datos_servicio(){
        $this->cliente_id = $_GET['cliente_id'];
        $cliente = new cliente($this->link);
        $clientes = $cliente->obten_por_id('cliente', $this->cliente_id);
        $this->datos_cliente = $clientes['registros'][0];
    }

    public function crea_pdf_factura (){

        $factura_id = $_GET['factura_id'];
        $pdf = new my_pdf();
        $this->genera_pdf_factura($factura_id, $pdf);
        $pdf->Output('D');
    }

    public function descarga_factura_pdf(){

        $factura_id = $_GET['factura_id'];

        $modelo_factura = new factura($this->link);
        $resultado = $modelo_factura->obten_por_id('factura',$factura_id);

        $rg_upd = array('status_descarga'=>'descargada');
        $modelo_factura->modifica_bd($rg_upd,'factura',$factura_id);

        $factura = $resultado['registros'][0];

        $folio = $factura['factura_folio'];
        $referencia = $factura['factura_referencia'];

        $pdf = new my_pdf();
        $this->genera_pdf_factura($factura_id, $pdf);

        $empresas = new empresas();
        $datos_empresa = $empresas->empresas[$_SESSION['numero_empresa']];
        $referencia_factura = $datos_empresa['referencia_factura'];

        $pdf->Output('D',$referencia_factura.'_'.$referencia.'.pdf');

    }

    public function descarga_factura_xml(){
        $factura_id = $_GET['factura_id'];


        $modelo_factura = new Factura($this->link);
        $resultado = $modelo_factura->obten_por_id('factura',$factura_id);

        $factura = $resultado['registros'][0];

        $folio = $factura['factura_folio'];

        $name_file = 'CXM_'.$factura['factura_referencia'];

        $numero_empresa = $_SESSION['numero_empresa'];
        $empresas = new Empresas();
        $datos_empresa = $empresas->empresas[$numero_empresa];

        $ruta_base = $datos_empresa['nombre_base_datos'];

        $ruta_xml = $ruta_base.'/xml_timbrado/'.$folio.'.xml';

        header("Content-disposition: attachment; filename=$name_file.xml");
        header('Content-type: "text/xml"; charset="utf8"');
        readfile($ruta_xml);

        exit;

    }

    public function elimina_cuenta_bd(){
        $tabla = 'cuenta_bancaria';
        $modelo_cuenta_bancaria = new Cuenta_Bancaria($this->link);
        $registro_id = $_GET['registro_id'];

        $datos_cuenta_bancaria = $modelo_cuenta_bancaria->obten_por_id('cuenta_bancaria',$registro_id);

        $cliente_id = $datos_cuenta_bancaria['registros'][0]['cuenta_bancaria_cliente_id'];

        $resultado = $modelo_cuenta_bancaria->elimina_bd($tabla, $registro_id);
        $mensaje = $resultado['mensaje'];


        if($resultado['error']){
            header("Location: ./index.php?seccion=cliente&accion=asigna_cuenta_bancaria&mensaje=$mensaje&tipo_mensaje=exito&cliente_id=$cliente_id");
            exit;
        }
        header("Location: ./index.php?seccion=cliente&accion=asigna_cuenta_bancaria&mensaje=$mensaje&tipo_mensaje=exito&cliente_id=$cliente_id");
        exit;
    }

    private function encabezado($pdf, $y, $texto, $w){
        $x = $pdf->GetX();
        $texto = utf8_decode($texto);
        $pdf->SetY($y);
        $pdf->SetX($x);
        $pdf->SetFont('Arial','B',$this->tamano_letra);
        $pdf->Cell($w,5,$texto,1);
    }

    public function genera_factura(){
        setcookie('cliente_id',$_GET['cliente_id']);
        $facturas = new Facturas($this->link);
        $breadcrumbs = array('lista','alta');
        $this->breadcrumbs = $this->directiva->nav_breadcumbs(12, 0, $breadcrumbs);
        $this->numero_empresa = $_SESSION['numero_empresa'];
        $empresa = new Empresas();
        $datos_empresa = $empresa->empresas[$this->numero_empresa];

        if(!isset($datos_empresa['factor_automatico']) || trim($datos_empresa['factor_automatico']) === '' ){
            $datos_empresa['factor_automatico'] = 0;
        }
        if(!isset($datos_empresa['cantidad_automatico'])|| trim($datos_empresa['cantidad_automatico']) === '' ){
            $datos_empresa['cantidad_automatico'] = 0;
        }
        if(!isset($datos_empresa['valor_automatico']) || trim($datos_empresa['valor_automatico']) === ''){
            $datos_empresa['valor_automatico'] = 0;
        }

        $this->insumo_automatico = $datos_empresa['insumo_automatico'];
        $this->cantidad_automatico = $datos_empresa['cantidad_automatico'];
        $this->unidad_automatico = $datos_empresa['unidad_automatico'];
        $this->valor_automatico = $datos_empresa['valor_automatico'];
        $this->impuesto_automatico = $datos_empresa['impuesto_automatico'];
        $this->tipo_factor_automatico = $datos_empresa['tipo_factor_automatico'];
        $this->factor_automatico = $datos_empresa['factor_automatico'];

        $this->importe_impuesto_automatico = $this->cantidad_automatico *  $this->valor_automatico *  $this->factor_automatico;


        $this->descripcion_automatico = '';
        if($this->insumo_automatico!=''){
            $modelo_insumo = new Insumo($this->link);
            $resultado_insumo = $modelo_insumo->obten_por_id('insumo', $this->insumo_automatico);

            $rs_insumo = $resultado_insumo['registros'][0];

            $this->descripcion_automatico = $rs_insumo['insumo_descripcion'];

            setlocale(LC_ALL,'es_ES');
            $mes = date('m');
            $year = date('Y');

             $this->descripcion_automatico =  strtoupper($this->descripcion_automatico.' '.strftime("%B de %Y"));


        }

        

        $regimenes_fiscales = $empresa->regimenes_fiscales;
        $this->rfc = $datos_empresa['rfc'];
        $this->razon_social = $datos_empresa['razon_social'];
        $this->regimen_fiscal = $datos_empresa['regimen_fiscal'];
        $this->nombre_regimen_fiscal = $regimenes_fiscales[$this->regimen_fiscal];
        $this->cliente_id = $_GET['cliente_id'];
        $cliente = new Cliente($this->link);
        $clientes = $cliente->obten_por_id('cliente', $this->cliente_id);
        $this->datos_cliente = $clientes['registros'][0];

        $this->sufijo = $datos_empresa['sufijo_folio'];
        $this->folio_inicial = $datos_empresa['folio_inicial'];


        $this->uso_cfdi_id = $clientes['registros'][0]['uso_cfdi_id'];
        $this->moneda_id = $clientes['registros'][0]['moneda_id'];
        $this->forma_pago_id = $clientes['registros'][0]['forma_pago_id'];
        $this->metodo_pago_id = $clientes['registros'][0]['metodo_pago_id'];
        $condiciones_pago = $clientes['registros'][0]['cliente_dias_credito'];
        $this->condiciones_pago = 'Contado';

        $this->folio_muestra = $facturas->genera_folio($this->sufijo,'factura',$this->folio_inicial);

        if($condiciones_pago && $condiciones_pago !=''){
            $this->condiciones_pago = $condiciones_pago;
        }

    }

    /**
     * @throws SoapFault
     */
    private function genera_pdf_factura($factura_id, $pdf){



        $factura_modelo = new factura($this->link);

        $partida_modelos = new partida_factura($this->link);
        $filtro_partidas = array('factura_id'=>$factura_id);

        $resultado = $partida_modelos->filtro_and('partida_factura',$filtro_partidas);
        if(errores::$error){
            return $this->error_->error('Error al obtener factura', $resultado);
        }
        $partidas = $resultado['registros'];

        $resultado = $factura_modelo->obten_por_id('factura',$factura_id);
        $factura = $resultado['registros'][0];



        $nombre_receptor = $factura['cliente_razon_social'];
        $rfc_receptor = $factura['cliente_rfc'];

        $numero_empresa = $_SESSION['numero_empresa'];
        $empresas = new Empresas();
        $datos_empresa = $empresas->empresas[$numero_empresa];
        $nombre_emisor = $datos_empresa['nombre_empresa'];
        $rfc_emisor = $datos_empresa['rfc'];

        $ruta_base = $datos_empresa['nombre_base_datos'];

        $ruta_file_qr = $ruta_base.'/xml_timbrado/'.$factura['factura_folio'].'.jpg';

        if(!file_exists($ruta_file_qr)){

            $directorio_base = $datos_empresa['nombre_base_datos'];
            $directorio_xml_sin_timbrar_completo = $directorio_base.'/xml_sin_timbrar'.$factura['factura_folio'].'.xml';
            $dir_xml_timbrado = $directorio_base.'/xml_timbrado/'.$factura['factura_folio'].'.xml';
            $dir_qr_timbrado = $directorio_base.'/xml_timbrado/'.$factura['factura_folio'].'.jpg';
            $dir_sello_timbrado = $directorio_base.'/xml_timbrado/'.$factura['factura_folio'].'.txt';


            $ws = $datos_empresa['ruta_pac'];
            $usuario_int = $datos_empresa['usuario_integrador'];
            $params = array();
            $params['usuarioIntegrador'] = $usuario_int;
            $params['rfcEmisor'] = $rfc_emisor;
            $params['folioUUID'] = $factura['factura_uuid'];
            $client = new SoapClient($ws,$params);
            $response = $client->__soapCall('ObtieneCFDI', array('parameters' => $params));



            $ejecucion = 'ObtieneCFDIResult';

            $xmlTimbrado = $response->$ejecucion->anyType[3];
            $codigoQr = $response->$ejecucion->anyType[4];
            $cadenaOriginal = $response->$ejecucion->anyType[5];

            file_put_contents($dir_xml_timbrado, $xmlTimbrado);
            file_put_contents($dir_qr_timbrado, $codigoQr);
            file_put_contents($dir_sello_timbrado, $cadenaOriginal);

        }

        $uso_cfdi_codigo = $factura['factura_uso_cfdi_codigo'];
        $uso_cfdi_descripcion = $factura['factura_uso_cfdi_descripcion'];

        $folio_fiscal = $factura['factura_uuid'];
        $serie_csd = $factura['factura_serie_csd'];
        $fecha = $factura['factura_fecha'];
        $lugar_expedicion = $factura['factura_lugar_expedicion'];
        $serie = $factura['factura_serie'];
        $folio = $factura['factura_folio'];
        $rfc_proveedor_timbrado = $factura['factura_rfc_proveedor_timbrado'];
        $fecha_timbrado = $factura['factura_fecha_timbrado'];
        $no_certificado_sat = $factura['factura_no_certificado_sat'];
        $regimen_fiscal = $factura['factura_regimen_fiscal_emisor_codigo'];
        $regimen_fiscal = $regimen_fiscal.' '.$factura['factura_regimen_fiscal_emisor_descripcion'];
        $moneda = $factura['factura_moneda_codigo'];
        $observaciones = $factura['factura_observaciones'];
        $moneda = $moneda.' '.$factura['factura_moneda_descripcion'];

        $moneda_codigo = $factura['factura_moneda_codigo'];

        $moneda_letra = array('MXN'=>'PESOS','USD'=>'DOLARES');

        $moneda_letra_enviar = $moneda_letra[$moneda_codigo];


        $metodo_pago = $factura['factura_metodo_pago_codigo'];
        $metodo_pago = $metodo_pago.' '.$factura['factura_metodo_pago_descripcion'];

        $sub_total = $factura['factura_sub_total'];
        $descuento_total = $factura['factura_descuento'];
        $total_impuestos_trasladados = $factura['factura_total_impuestos_trasladados'];
        $total_impuestos_retenidos = $factura['factura_total_impuestos_retenidos'];

        $total = $factura['factura_total'];


        $forma_pago = $factura['factura_forma_pago_codigo'];
        $forma_pago = $forma_pago.' '.$factura['factura_forma_pago_descripcion'];


        $sello_cfd = $factura['factura_sello_cfd'];
        $sello_sat = $factura['factura_sello_sat'];


        $pdf->AliasNbPages();
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();
        $y = $pdf->GetY()+10;
        $pdf->SetY($y);

        $empresa = new empresas();

        $datos_empresa = $empresa->empresas[$_SESSION['numero_empresa']];


        $nombre_empresa = $datos_empresa['nombre_empresa'];


        $encabezado_1 = $datos_empresa['encabezado_1'];
        $encabezado_2 = $datos_empresa['encabezado_2'];
        $encabezado_3 = $datos_empresa['encabezado_3'];

        //$leyenda_factura = $datos_empresa['leyenda_factura'];



        $ruta_logo = $ruta_base.'/logo.png';

        $pdf->Image($ruta_logo,18,7 ,25);
        $pdf->SetFont('Courier','B',12);

        $pdf->SetY(10);

        $pdf->Cell(190,6,$nombre_empresa,0,1,'C');
        $pdf->SetFont('Courier','',9);
        $pdf->Cell(190,6,$rfc_emisor,0,1,'C');
        $pdf->Cell(190,6,$encabezado_1,0,1,'C');
        $pdf->Cell(190,6,$encabezado_2,0,1,'C');
        $pdf->SetFont('Courier','',7);
        $pdf->Cell(190,6,$regimen_fiscal,0,1,'C');

        $pdf->SetFont('Courier','',8);

        $pdf->SetX(30);
        $pdf->MultiCell(150,6,utf8_decode($encabezado_3),0,'C');


        $y = $pdf->GetY();

        $pdf->SetY($y);

        $pdf->Line(10,$y,200,$y);

        $pdf->SetX(6);
        $this->imprime_dato($pdf,'Nombre Emisor:', $nombre_emisor);
        $this->imprime_dato($pdf,'RFC Emisor:', $rfc_emisor);
        $this->imprime_dato($pdf,'Nombre Receptor:', $nombre_receptor);
        $this->imprime_dato($pdf,'RFC Receptor:', $rfc_receptor);
        $this->imprime_dato($pdf,'Uso CFDI:', $uso_cfdi_codigo.' '.$uso_cfdi_descripcion);
        $this->imprime_dato($pdf,'Folio Fiscal:', $folio_fiscal);
        $this->imprime_dato($pdf,'No. de serie del CSD:', $serie_csd);
        $this->imprime_dato($pdf,'Fecha y Hora de Emisión:', $fecha);
        $this->imprime_dato($pdf,'Lugar de Emisión:', $lugar_expedicion);
        $this->imprime_dato($pdf,'Serie:', $serie);
        $this->imprime_dato($pdf,'Folio:', $folio);
        $this->imprime_dato($pdf,'Régimen Fiscal:', $regimen_fiscal);


        $anticipo_id = $factura['anticipo_id'];

        if($anticipo_id != ''){
            $y = $pdf->GetY()+4;

            $pdf->SetY($y);
            $this->imprime_dato($pdf,'Tipo Relacion 07 Aplicacion de anticipos:', $factura['anticipo_uuid']);
            $this->imprime_dato($pdf,'Folio Fiscal Relacionado:', $factura['anticipo_uuid']);
        }


        if($factura['tipo_relacion_id'] != ''){
            $y = $pdf->GetY()+4;

            $pdf->SetY($y);

            $factura_relacionada_modelo = new factura_relacionada($this->link);

            $filtro = array('factura.id'=>$factura['factura_id']);

            $r_factura_relacionada = $factura_relacionada_modelo->filtro_and('factura_relacionada', $filtro);

            $facturas_rel = $r_factura_relacionada['registros'];

            $rels = '';
            foreach($facturas_rel as $factura_rel){
                $rels.=$factura_rel['factura_rel_uuid'].' , ';
            }

            $this->imprime_dato($pdf,'Tipo Relacion '.$factura['tipo_relacion_codigo'].' '.$factura['tipo_relacion_descripcion'].':','');
            $this->imprime_dato($pdf,'Folios Fiscales Relacionados:', $rels);
        }


        $y = $pdf->GetY()+5;
        $pdf->SetY($y);

        $pdf->Line(10,$y,200,$y);
        $pdf->SetY($y);
        $pdf->SetFillColor(190);
        $pdf->MultiCell(190,4,'Conceptos:',0,'J',True);
        foreach ($partidas as $partida){
            $clave_producto_servicio = $partida['partida_factura_producto_sat_codigo'];
            $descripcion = $partida['partida_factura_descripcion'];
            $no_identificacion = $partida['partida_factura_no_identificacion'];
            $cantidad = $partida['partida_factura_cantidad'];
            $clave_unidad = $partida['partida_factura_unidad_codigo'];
            $valor_unitario = $partida['partida_factura_valor_unitario'];
            $importe = $partida['partida_factura_importe'];
            $descuento = (float)$partida['partida_factura_descuento'];
            $base = $partida['partida_factura_base'];
            $impuesto = $partida['partida_factura_impuesto_descripcion'];
            $tipo_factor = $partida['partida_factura_tipo_factor_descripcion'];
            $tasa_cuota = $partida['partida_factura_tasa_cuota'];
            $importe_impuesto = $tasa_cuota * $base;

            $y = $pdf->GetY();
            $x = 10;
            $pdf->SetXY($x,$y);
            $pdf->SetFillColor(230);
            $pdf->SetFont('Arial','B',$this->tamano_letra);
            $pdf->MultiCell(190,4,'Descripcion:',0,'J',True);
            $pdf->SetFont('Arial','',$this->tamano_letra);
            $pdf->SetX($x);

            $descripcion = utf8_decode($descripcion);
            $n_caracteres = strlen($descripcion);
            for ($i = 0; $i<$n_caracteres; $i++){
                $j = $i%100;
                $pdf->Write(5,$descripcion[$i]);
                if($j == 99){
                    $pdf->Ln(3);
                    $pdf->SetX($x);
                }
            }
            $y = $pdf->GetY()+4;
            $pdf->SetY($y);
            $pdf->SetX($x);


            $pdf->Cell(23,4,'Cve concepto SAT:',1,0,'C',true);
            $pdf->Cell(22,4,utf8_decode('No. identificación:'),1,0,'C',true);
            $pdf->Cell(20,4,utf8_decode('Cantidad:'),1,0,'C',true);
            $pdf->Cell(19,4,utf8_decode('Clave Unidad:'),1,0,'C',true);
            $pdf->Cell(30,4,utf8_decode('Valor Unitario:'),1,0,'C',true);
            $pdf->Cell(59,4,utf8_decode('Importe:'),1,0,'C',true);
            $pdf->Cell(17,4,utf8_decode('Descuento:'),1,0,'C',true);

            $y = $pdf->GetY()+4;
            $pdf->SetY($y);


            $pdf->SetX($x);
            $pdf->Cell(23,4,$clave_producto_servicio,1,0,'C',False);
            $pdf->Cell(22,4,$no_identificacion,1,0,'C',False);
            $pdf->Cell(20,4,$cantidad,1,0,'C',False);
            $pdf->Cell(19,4,$clave_unidad,1,0,'C',False);
            $pdf->Cell(30,4,'$'.number_format($valor_unitario,4,'.',','),1,0,'C',False);
            $pdf->Cell(59,4,'$'.number_format($importe,4,'.',','),1,0,'C',False);
            $pdf->Cell(17,4,'$'.number_format($descuento,2,'.',','),1,0,'C',False);

            $pdf->SetFont('Arial','B',$this->tamano_letra);

            $y = $pdf->GetY()+4;
            $pdf->SetY($y);
            $pdf->SetX($x);
            $pdf->Cell(23,5,'Traslados:',1);
            $pdf->SetFont('Arial','',$this->tamano_letra);
            $pdf->Cell(42,5,'Base: $'.number_format($base,2,'.',','),1);


            $pdf->Cell(19,5,'Impuesto: '.$impuesto,1);


            $pdf->Cell(30,5,'Tipo factor: '.$tipo_factor,1);


            $pdf->Cell(38,5,'Tasa o Cuota :'.$tasa_cuota,1);

            $x = $pdf->GetX();
            $pdf->SetXY($x,$y);
            $pdf->Cell(38,5,'Importe: $'.number_format($importe_impuesto,4,'.',','),1);


            /*Retenciones*/


            if(isset($partida['partida_factura_impuesto_retenido_descripcion'])) {

                $impuesto = $partida['partida_factura_impuesto_retenido_descripcion'];
                $tipo_factor = $partida['partida_factura_tipo_factor_retenido_descripcion'];
                $tasa_cuota = $partida['partida_factura_tasa_cuota_retenido'];
                $importe_impuesto = $tasa_cuota * $base;


                $pdf->SetFont('Arial', 'B', $this->tamano_letra);
                $pdf->SetX($x);
                $y = $pdf->GetY() + 5;
                $pdf->SetY($y);
                $pdf->Cell(23,5,'Retenciones:',1);

                $pdf->SetFont('Arial','',$this->tamano_letra);

                $pdf->Cell(42, 5, 'Base: $'.number_format($base,2,'.',','), 1);

                $pdf->Cell(19, 5, 'Impuesto: '.$impuesto, 1);

                $pdf->Cell(30, 5, 'Tipo factor: '.$tipo_factor, 1);

                $pdf->Cell(38, 5, 'Tasa o Cuota: '.$tasa_cuota, 1);

                $pdf->Cell(38, 5, 'Importe: $'.number_format($importe_impuesto,4,'.',','), 1);

            }

            $y = $pdf->GetY()+1;
            $pdf->SetY($y+6);
            $pdf->Line(10,$y+6,200,$y+6);

            $y = $pdf->GetY();
            if($y >= 240){
                $this->salto_linea($pdf);
                $this->salto_linea($pdf);
                $this->salto_linea($pdf);
                $this->salto_linea($pdf);
                $this->salto_linea($pdf);
                $this->salto_linea($pdf);
                $y = 1;

            }
        }

        $pdf->Cell(190, 5, 'Observaciones:', 0,1);
        $pdf->MultiCell(190, 5, $observaciones , 1 ,'L');


        $y = $pdf->GetY();
        if($y >= 250){
            $this->salto_linea($pdf);
            $this->salto_linea($pdf);
            $this->salto_linea($pdf);
            $this->salto_linea($pdf);
            $this->salto_linea($pdf);
            $this->salto_linea($pdf);
            $y = 1;
        }

        $this->imprime_dato($pdf,'Moneda:', $moneda);

        $y = $pdf->GetY();
        if($y >= 250){
            $this->salto_linea($pdf);
            $this->salto_linea($pdf);
            $this->salto_linea($pdf);
            $this->salto_linea($pdf);
            $this->salto_linea($pdf);
            $this->salto_linea($pdf);
            $y = 1;

        }

        $this->imprime_dato($pdf,'Forma de pago:', $forma_pago);


        $y = $pdf->GetY();
        if($y >= 250){
            $this->salto_linea($pdf);
            $this->salto_linea($pdf);
            $this->salto_linea($pdf);
            $this->salto_linea($pdf);
            $this->salto_linea($pdf);
            $this->salto_linea($pdf);
            $y = 1;
        }

        $this->imprime_dato($pdf,'Metodo de pago:', $metodo_pago);

        $x = $pdf->GetX();

        if($x >= 175){
            $this->salto_linea($pdf);
        }

        $y = $pdf->GetY();

        if($y >= 250){
            $this->salto_linea($pdf);
            $this->salto_linea($pdf);
            $this->salto_linea($pdf);
            $this->salto_linea($pdf);
            $this->salto_linea($pdf);
            $this->salto_linea($pdf);
            $y = 1;

        }

        $this->salto_linea($pdf);
        $x = 150;
        $pdf->SetX($x);
        $this->imprime_dato($pdf,'Sub Total:            $', number_format($sub_total,2,'.',','));
        $this->salto_linea($pdf);
        $x = 150;
        $pdf->SetX($x);
        $this->imprime_dato($pdf,'Descuento:            $', number_format($descuento_total,2,'.',','));
        $this->salto_linea($pdf);
        $x = 150;

        $pdf->SetX($x);
        $this->imprime_dato($pdf,'IVA Trasladado:   $', number_format($total_impuestos_trasladados,4,'.',','));

        $this->salto_linea($pdf);

        $pdf->SetX($x);
        $this->imprime_dato($pdf,'IVA Retenido:      $', number_format($total_impuestos_retenidos,2,'.',','));


        $this->salto_linea($pdf);
        $x = 150;
        $pdf->SetX($x);
        $this->imprime_dato($pdf,'Total:                   $', number_format($total,2,'.',','));

        $x = 15;
        $y = $y + 20;
        $pdf->SetXY($x, $y);
        $pdf->Cell(28,5,'Total con letra:');

        $pdf->SetX($x+30);

        $numeros = new NumeroTexto();
        $importe_texto = $numeros->to_word($total, $moneda_letra_enviar);



        $pdf->Cell(28,5,$importe_texto." $moneda_codigo");


        $this->salto_linea($pdf);
        $x = 15;
        $pdf->SetX($x);
        $pdf->Cell(28,5,'Sello digital del CFDI:');

        $pdf->SetFont('Arial','',$this->tamano_letra-2);
        $this->salto_linea($pdf);
        $x = 15;
        $pdf->SetX($x);

        $n_caracteres = strlen($sello_cfd);
        for ($i = 0; $i<$n_caracteres; $i++){
            $j = $i%145;
            $pdf->Cell(1.2,5,$sello_cfd[$i],0);
            if($j == 144){
                $pdf->Ln(2);
                $pdf->SetX($x);
            }
        }

        $pdf->SetFont('Arial','B',$this->tamano_letra);
        $this->salto_linea($pdf);
        $x = 15;
        $pdf->SetX($x);
        $pdf->Cell(28,5,'Sello digital del SAT:');

        $pdf->SetFont('Arial','',$this->tamano_letra-2);
        $this->salto_linea($pdf);
        $x = 15;
        $pdf->SetX($x);

        $n_caracteres = strlen($sello_sat);
        for ($i = 0; $i<$n_caracteres; $i++){
            $j = $i%145;
            $pdf->Cell(1.2,5,$sello_sat[$i],0);
            if($j == 144){
                $pdf->Ln(2);
                $pdf->SetX($x);
            }
        }


        $ruta_qr = $ruta_base.'/'.'xml_timbrado/'.$folio.'.jpg';

        $y = $pdf->GetY()+5;
        $x = 15;
        $pdf->Image($ruta_qr,$x,$y,30);

        $pdf->SetFont('Arial','B',$this->tamano_letra);

        $this->salto_linea($pdf);
        $x = $pdf->GetX()+40;
        $pdf->SetX($x);
        $pdf->Cell(28,5,utf8_decode('Cadena Original del complemento de certificación digital del SAT:'));

        $ruta_cadena_original = $ruta_base.'/xml_timbrado/'.$folio.'.txt';
        $cadena_original = file_get_contents($ruta_cadena_original);
        $this->salto_linea($pdf);
        $x = $pdf->GetX()+40;
        $pdf->SetX($x);
        $pdf->SetFont('Arial','',$this->tamano_letra-2);

        $n_caracteres = strlen($cadena_original);
        for ($i = 0; $i<$n_caracteres; $i++){
            $j = $i%120;
            $pdf->Cell(1.2,5,$cadena_original[$i],0);
            if($j == 119){
                $pdf->Ln(2);
                $pdf->SetX($x);
            }
        }
        $this->salto_linea($pdf);
        $pdf->SetX($x-4);
        $this->imprime_dato($pdf,'Folio Fiscal:', $folio_fiscal);

        $this->salto_linea($pdf);
        $pdf->SetX($x-4);
        $this->imprime_dato($pdf,'No. de serie del certificado SAT:', $no_certificado_sat);

        $this->salto_linea($pdf);
        $pdf->SetX($x-4);
        $this->imprime_dato($pdf,'Fecha y hora de certificación:', $fecha_timbrado);

        $this->salto_linea($pdf);
        $pdf->SetX($x-4);
        $this->imprime_dato($pdf,'RFC del proveedor de certificación:', $rfc_proveedor_timbrado);

        $ruta_logo = './img/iso.jpg';

        $pdf->Line(10,258,200,258);
        $pdf->Image($ruta_logo,10,260 ,25);
        $pdf->SetY(265);
        $pdf->Cell(190,5,'Certificado: 102613-2011-AQ-MEX-EMA ISO - 9001',0,1,'C');

        $pdf->Line(10,282,200,282);



    }

    public function guarda_factura(){
        $_POST['tipo_comprobante_id'] = '1';
        $_POST['cliente_id'] = $_GET['cliente_id'];

        $facturas = new facturas($this->link);
        $this->datos_guardar = array();

        $facturas->asigna_datos_empresa($this);
        $facturas->obten_datos_ligados();
        $facturas->asigna_datos_ligados($this);

        $fecha_emision = $facturas->obten_fecha_hora();
        $folio = $facturas->genera_folio($this->sufijo,'factura',$this->folio_inicial);

        $this->datos_guardar['condiciones_pago'] = $_POST['condiciones_pago'];
        $this->datos_guardar['fecha'] = $fecha_emision;
        $this->datos_guardar['folio'] = $folio;

        $empresas = new empresas();
        $datos_empresa = $empresas->empresas[$_SESSION['numero_empresa']];

        if(isset($datos_empresa['ref_factura'])){
            if($datos_empresa['ref_factura'] === 'folio_factura'){
                $this->datos_guardar['referencia'] = $folio;
            }
        }


        $this->datos_guardar['cliente_id'] = $_POST['cliente_id'];
        $this->datos_guardar['status'] = 1;
        $this->datos_guardar['status_factura'] = 'sin timbrar';
        $this->datos_guardar['observaciones'] = $_POST['observaciones'];
        $this->datos_guardar['metodo_pago_id'] = $_POST['metodo_pago_id'];
        $this->datos_guardar['moneda_id'] = $_POST['moneda_id'];
        $this->datos_guardar['forma_pago_id'] = $_POST['forma_pago_id'];
        $this->datos_guardar['uso_cfdi_id'] = $_POST['uso_cfdi_id'];

        $this->link->query('SET AUTOCOMMIT=0');
        $this->link->query('START TRANSACTION');

        $factura_modelo = new factura($this->link);
        $resultado = $factura_modelo->alta_bd($this->datos_guardar, 'factura');

        if($resultado['error']){
            $this->link->query('ROLLBACK');
            $mensaje = $resultado['mensaje'];
            header("Location: ./index.php?seccion=cliente&accion=genera_factura&mensaje=$mensaje&tipo_mensaje=error");
            exit;
        }

        $factura_id = $resultado['registro_id'];

        $insumos = $_POST['insumo_id'];
        $descripciones = $_POST['descripcion'];
        $valores_unitarios = $_POST['valor_unitario'];
        $cantidades = $_POST['cantidad'];

        $modelo_insumo = new insumo($this->link);
        $partida = array();
        $i = 0;



        foreach ($insumos as $insumo_id){
            $descripcion = $descripciones[$i];
            $valor_unitario = number_format(round($valores_unitarios[$i],2),2,'.','');
            $cantidad = round($cantidades[$i],2);
            $importe = number_format(round($cantidad * $valor_unitario,2),2,'.','');
            $base = number_format(round($importe,2),2,'.','');
            $resultado_insumo = $modelo_insumo->obten_por_id('insumo',$insumo_id);
            $insumo_datos = $resultado_insumo['registros'][0];

            $resultado = $modelo_insumo->obten_por_id('insumo',$insumo_id);
            $insumo = $resultado['registros'][0];

            $partida[$i]['insumo_id'] = $insumo_id;
            $partida[$i]['unidad_codigo'] = $insumo['unidad_codigo'];
            $partida[$i]['unidad_descripcion'] = $insumo['unidad_descripcion'];
            $partida[$i]['unidad'] = $insumo['unidad_descripcion'];
            $partida[$i]['impuesto_codigo'] = $insumo['impuesto_codigo'];
            $partida[$i]['impuesto_retenido_codigo'] = $insumo['impuesto_retenido_codigo'];
            $partida[$i]['impuesto_retenido_descripcion'] = $insumo['impuesto_retenido_descripcion'];
            $partida[$i]['impuesto_descripcion'] = $insumo['impuesto_retenido_descripcion'];
            $partida[$i]['tipo_factor_codigo'] = $insumo['tipo_factor_codigo'];
            $partida[$i]['tipo_factor_retenido_codigo'] = $insumo['tipo_factor_retenido_codigo'];
            $partida[$i]['tipo_factor_descripcion'] = $insumo['tipo_factor_descripcion'];
            $partida[$i]['tipo_factor_retenido_descripcion'] = $insumo['tipo_factor_retenido_descripcion'];
            $partida[$i]['tasa_cuota'] = $insumo['insumo_factor'];
            $partida[$i]['tasa_cuota_retenido'] = $insumo['insumo_factor_retenido'];
            $partida[$i]['impuesto_trasladado_id'] = $insumo['impuesto_id'];
            $partida[$i]['impuesto_retenido_id'] = $insumo['impuesto_retenido_id'];
            $partida[$i]['tipo_factor_trasladado_id'] = $insumo['tipo_factor_id'];
            $partida[$i]['tipo_factor_retenido_id'] = $insumo['tipo_factor_retenido_id'];
            $partida[$i]['total_impuestos_trasladados'] = number_format(round($base * $partida[$i]['tasa_cuota'],4),4,'.','');
            $partida[$i]['total_impuestos_retenidos'] = number_format(round($base * $partida[$i]['tasa_cuota_retenido'],2),2,'.','');
            $partida[$i]['importe_neto'] = number_format(round($base+$partida[$i]['total_impuestos_trasladados']-$partida[$i]['total_impuestos_retenidos']),4,'.','');



            $partida[$i]['factura_id'] = $factura_id;
            $partida[$i]['insumo_descripcion'] = $insumo_datos['insumo_descripcion'];
            $partida[$i]['producto_sat_codigo'] = $insumo_datos['producto_sat_codigo'];
            $partida[$i]['producto_sat_descripcion'] = $insumo_datos['producto_sat_descripcion'];
            $partida[$i]['descripcion'] = $descripcion;
            $partida[$i]['no_identificacion'] = $insumo_id;
            $partida[$i]['valor_unitario'] = number_format($valor_unitario,2,'.','');
            $partida[$i]['cantidad'] = $cantidad;
            $partida[$i]['importe'] = number_format($importe,2,'.','');
            $partida[$i]['base'] = number_format($base,2,'.','');
            $status = 1;
            $partida[$i]['status'] = $status;
            $i++;
        }


        $modelo_partida = new Partida_Factura($this->link);
        foreach ($partida as $partida_insertar){
            $resultado = $modelo_partida->alta_bd($partida_insertar, 'partida_factura');
            if(errores::$error){
                $this->link->query('ROLLBACK');
                $error = $this->error_->error('Error al insertar partida', $resultado);
                print_r($error);
                die('Error');
            }

        }
        $this->link->query('COMMIT');
        header("Location: ./index.php?seccion=cliente&accion=vista_preliminar_factura&factura_id=$factura_id");
        exit;
    }

    private function imprime_dato($pdf,$etiqueta,$dato){
        $this->imprime_etiqueta($pdf,$etiqueta);
        $this->imprime_variable($pdf, $dato);
    }

    private function imprime_etiqueta($pdf, $etiqueta){
        $x = $pdf->GetX();
        $x = $x + 4;
        $pdf->SetX($x);
        $pdf->SetFont('Arial','',$this->tamano_letra);
        $pdf->WriteHTML(utf8_decode($etiqueta));
    }

    private function imprime_variable($pdf, $dato){
        if(is_null($dato)){
            $dato = '';
        }
        $pdf->SetFont('Arial','B',$this->tamano_letra);
        $x = $pdf->GetX();
        $x = $x + 2;
        $pdf->SetX($x);
        $pdf->WriteHTML(utf8_decode($dato));
    }

    public function obten_datos_unidad(){

        $insumo_id = $_GET['insumo_id'];
        $insumo_modelo = new insumo($this->link);
        $resultado = $insumo_modelo->obten_por_id('insumo',$insumo_id);
        $insumo = $resultado['registros']['0'];
        $json = json_encode($insumo);
        print_r($json);
        header('Content-Type: application/json');

    }

    public function persona_responsable_pago_cliente(){
        $breadcrumbs = array('lista','alta');
        $this->breadcrumbs = $this->directiva->nav_breadcumbs(12, 0, $breadcrumbs);

        $this->cliente_id = $_GET['cliente_id'];
        $cliente = new Cliente($this->link);
        $clientes = $cliente->obten_por_id('cliente', $this->cliente_id);
        $this->datos_cliente = $clientes['registros'][0];
    }

    private function salto_linea($pdf){
        $y = $pdf->GetY();
        $pdf->SetY($y + 5);
    }

    public function timbra_cfdi(){
        $factura_id = $_GET['factura_id'];
        $factura_modelo = new factura($this->link);
        $resultado = $factura_modelo->obten_por_id('factura',$factura_id);
        $registro = $resultado['registros'][0];
        $folio = $registro['factura_folio'];
        $factura = new facturas($this->link);
        $cliente_id = $registro['cliente_id'];

        $resultado_timbrado = $factura->timbra_cfdi($folio);

//        if($factura_id == 10){
//            exit;
//        }

        if($resultado_timbrado['error']){
            header("Location: ./index.php?seccion=cliente&accion=lista&mensaje=".$resultado_timbrado['mensaje']."&tipo_mensaje=error");
            exit;
        }
        else{
            header("Location: ./index.php?seccion=factura&accion=lista&mensaje=Exito&cliente_id=$cliente_id");
            exit;
        }
    }

    public function ve_factura_pdf(){
        $factura_id = $_GET['factura_id'];
        $pdf = new my_pdf();
        $this->genera_pdf_factura($factura_id, $pdf);
        $pdf->Output();
    }

    public function ve_facturas(){
        $breadcrumbs = array('lista','alta');
        $this->breadcrumbs = $this->directiva->nav_breadcumbs(12, 0, $breadcrumbs);
        $modelo_factura = new Factura($this->link);
        $cliente_id = $_GET['cliente_id'];
        $filtro = array('cliente.id'=>$cliente_id);
        $resultado = $modelo_factura->filtro_and('factura',$filtro);
        $this->registros = $resultado['registros'];
    }


    public function vista_preliminar_factura(){
        
        $breadcrumbs = array('lista','alta');
        $this->breadcrumbs = $this->directiva->nav_breadcumbs(12, 0, $breadcrumbs);
        if(errores::$error){
            $error = $this->error_->error('Error al GENERAR BREADS', $this->breadcrumbs);
            print_r($error);
            die('Error');
        }
        $this->factura_id = $_GET['factura_id'];

        $init_receptor = (new factura($this->link))->inicializa_data_receptor(factura_id: $this->factura_id);
        if(errores::$error){
            $error = $this->error_->error('Error al inicializar receptor', $init_receptor);
            print_r($error);
            die('Error');
        }
        $init_partidas = (new partida_factura($this->link))->init_obj_partidas(factura_id: $this->factura_id);
        if(errores::$error){
            $error = $this->error_->error('Error al inicializar partidas', $init_partidas);
            print_r($error);
            die('Error');
        }

        $partida_factura_modelo = new partida_factura($this->link);
        $filtro = array('factura_id'=>$this->factura_id);
        $resultado = $partida_factura_modelo->filtro_and('partida_factura',$filtro);
        if(errores::$error){
            $error = $this->error_->error('Error al obtener partidas', $resultado);
            print_r($error);
            die('Error');
        }

        $partidas = $resultado['registros'];

        foreach ($partidas as $partida) {
        	if((string)$partida['partida_factura_producto_sat_codigo'] === ''){
                $upd['factura_id'] = $this->factura_id;
                $partida_factura_id = $partida['partida_factura_id'];
        		$r_pf = $partida_factura_modelo->modifica_bd($upd,'partida_factura',$partida_factura_id);
                if(errores::$error){
                    $error = $this->error_->error('Error al modificar partidas', $r_pf);
                    print_r($error);
                    die('Error');
                }
        	}

        }
        $pdf = $this->genera_pdf_factura_sin_timbrar(factura_id: $this->factura_id);
        if(errores::$error){
            $error = $this->error_->error('Error al generar pdf', $pdf);
            print_r($error);
            die('Error');
        }


        $factura_modelo = new factura($this->link);
        $resultado = $factura_modelo->obten_por_id('factura',$this->factura_id);
        if(errores::$error){
            $error = $this->error_->error('Error al obtener registro', $resultado);
            print_r($error);
            die('Error');
        }
        $this->factura = $resultado['registros'][0];


        $factura_relacionada_modelo= new factura_relacionada($this->link);
        $filtro = array('factura.id'=>$this->factura_id);
        $r_factura_relacionada = $factura_relacionada_modelo->filtro_and('factura_relacionada', $filtro);



        $this->facturas_relacionadas = $r_factura_relacionada['registros'];

       if(!isset($_GET['intento'])){
            header('Location: ./index.php?seccion=cliente&accion=vista_preliminar_factura&factura_id='.$this->factura_id.'&intento=1');
        }

    }

    public function genera_pdf_factura_sin_timbrar($factura_id){
        $empresa = new Empresas();
        $factura = new factura($this->link);
        $resultado = $factura->obten_por_id('factura',$factura_id);
        if(errores::$error){
            return $this->error_->error('Error al obtener factura', $resultado);
        }

        $registro = $resultado['registros'][0];
        $folio = $registro['factura_folio'];
        $uuid = $registro['factura_uuid'];
        $datos_empresa = $empresa->empresas[$_SESSION['numero_empresa']];
        $ruta_base = $datos_empresa['nombre_base_datos'];
        $ruta_xml_sin_timbrar = $ruta_base.'/xml_sin_timbrar/'.$folio.'.xml';
        
        $status_timbre = $registro['factura_status_factura'];

        if((string)$uuid ===''){
            $status_timbre = 'sin timbrar';
        }

        $this->status_factura = $status_timbre;

        $existe_xml_sin_timbrar = true;

        if(!file_exists($ruta_xml_sin_timbrar)){
            $existe_xml_sin_timbrar = false;
        }


        if($status_timbre!=='timbrada' || !$existe_xml_sin_timbrar){

            $repositorio = new repositorio();

            $update = $this->init_update(datos_empresa: $datos_empresa,registro: $registro);
            if(errores::$error){
                return $this->error_->error('Error al genera data update', $update);
            }

            if(count($update)>0){
                $r_upd = $factura->modifica_bd($update,'factura',$factura_id);
                if(errores::$error){
                    return $this->error_->error('Error al modificar factura', $r_upd);
                }
            }
            $resultado = $factura->obten_por_id('factura',$factura_id);
            $registro = $resultado['registros'][0];


            $lugar_expedicion = $registro['factura_lugar_expedicion'];
            $metodo_pago = $registro['factura_metodo_pago_codigo'];
            $tipo_comprobante = $registro['factura_tipo_comprobante_codigo'];

            $campos = array('factura_sub_total','factura_total_impuestos_trasladados',
                'factura_total_impuestos_retenidos','factura_factor_trasladado',
                'factura_total_impuestos_trasladados_iva');
            $registro = $factura->asigna_ceros_row(campos:$campos, registro: $registro);
            if(errores::$error){
                return $this->error_->error('Error al limpiar registro', $registro,get_defined_vars());
            }


            $total = number_format(round($registro['factura_sub_total'],2),2,'.','');

            $total = number_format($total+round($registro['factura_total_impuestos_trasladados'],4),2,'.','');
            $total = number_format($total-round($registro['factura_total_impuestos_retenidos'],2),2,'.','');
            $total = number_format(round($total-$registro['factura_descuento'],2),2,'.','');

            $moneda = $registro['factura_moneda_codigo'];
            $sub_total = number_format($registro['factura_sub_total'],2,'.','');
            $forma_pago = $registro['factura_forma_pago_codigo'];
            $fecha = $registro['factura_fecha'];
            $fecha_split = explode(' ', $fecha);

            if(count($fecha_split)===2){
                $fecha = $fecha_split[0].'T'.$fecha_split[1];
            }

            $folio = $registro['factura_folio'];
            $serie = $registro['factura_serie'];

            $rfc_emisor = $registro['factura_rfc_emisor'];
            $nombre_emisor = $registro['factura_nombre_emisor'];
            $regimen_fiscal = $registro['factura_regimen_fiscal_emisor_codigo'];


            $rfc_receptor = str_replace('&','&amp;',$registro['factura_cliente_rfc']);

            $razon_social_cliente = str_replace('&','&amp;',$registro['factura_cliente_razon_social']);
            $uso_cfdi = $registro['factura_uso_cfdi_codigo'];

            $total_impuestos_trasladados = '"'.number_format(round($registro['factura_total_impuestos_trasladados'],4),2,'.','').'"';



            foreach($registro as $campo=>$value){
                if(is_null($value)){
                    $registro[$campo] = '';
                }
            }


            $impuesto_traslado_codigo = $registro['factura_impuesto_trasladado_codigo'];
            $impuesto_retenido_codigo = $registro['factura_impuesto_retenido_codigo'];
            $impuesto_trasladado = '"'.$registro['factura_impuesto_trasladado_codigo'].'"';
            $impuesto_trasladado_ieps = '"'.$registro['factura_codigo_ieps'].'"';

            $tipo_factor_trasladado = '"'.$registro['factura_tipo_factor_trasladado'].'"';
            $tasa_cuota_traslado = '"'.number_format($registro['factura_factor_trasladado'],6,'.','').'"';
            $importe_impuesto_traslado_iva = '"'.number_format(round($registro['factura_total_impuestos_trasladados_iva'],2),2,'.','').'"';

            $total_impuestos_retenidos = number_format(round($registro['factura_total_impuestos_retenidos'],2),2,'.','');

            $impuesto_traslado_codigo_ieps = $registro['factura_codigo_ieps'];

            $descuento = $registro['factura_descuento'];


            $plantilla = './plantillas_cfdi/factura.xml';
            $xml  = file_get_contents($plantilla);


            if((float)$descuento>0.0){
                $xml = str_replace('|Descuento|','Descuento="'.$descuento.'"',$xml);
            }
            else{
                $xml = str_replace('|Descuento|','',$xml);
            }


            $cfdis_relacionados = $this->cfdis_relacionados_full(factura_id: $factura_id,registro: $registro);
            if(errores::$error){
                return $this->error_->error(mensaje:  'Error al asignar relaciones',data: $cfdis_relacionados,
                    params: get_defined_vars());
            }


            $xml = str_replace('|lugar_expedicion|',$lugar_expedicion,$xml);
            $xml = str_replace('|metodo_pago|',$metodo_pago,$xml);
            $xml = str_replace('|tipo_comprobante|',$tipo_comprobante,$xml);
            $xml = str_replace('|total|',$total,$xml);
            $xml = str_replace('|moneda|',$moneda,$xml);
            $xml = str_replace('|sub_total|',$sub_total,$xml);
            $xml = str_replace('|forma_pago|',$forma_pago,$xml);
            $xml = str_replace('|fecha|',$fecha,$xml);
            $xml = str_replace('|folio|',$folio,$xml);
            $xml = str_replace('|serie|',$serie,$xml);
            $xml = str_replace('|rfc_emisor|',$rfc_emisor,$xml);
            $xml = str_replace('|nombre_emisor|',$nombre_emisor,$xml);
            $xml = str_replace('|regimen_fiscal|',$regimen_fiscal,$xml);
            $xml = str_replace('|uso_cfdi|',$uso_cfdi,$xml);
            $xml = str_replace('|cdfis_relacionados|',$cfdis_relacionados,$xml);


            /**
             * RECEPTOR
             */

            if(!isset($registro['factura_cliente_rf']) || trim($registro['factura_cliente_rf'])===''){
                if($registro['factura_status_factura']!=='timbrada') {
                    return $this->error_->error('Error factura_cliente_rf no existe en el registro', $registro);
                }
            }

            $xml = str_replace('|rfc_receptor|',$rfc_receptor,$xml);
            $xml = str_replace('|razon_social_cliente|',$razon_social_cliente,$xml);
            $xml = str_replace('|cliente_cp|',$registro['factura_cliente_cp'],$xml);
            $xml = str_replace('|cliente_rf|',$registro['factura_cliente_rf'],$xml);


            if($impuesto_traslado_codigo!=''){

                $total_impuestos_trasladados_imp = $total_impuestos_trasladados;
                $total_impuestos_trasladados_imp = str_replace('"', '', $total_impuestos_trasladados_imp);
                $total_impuestos_trasladados_imp = str_replace("'", '', $total_impuestos_trasladados_imp);
                $total_impuestos_trasladados_imp = (float)$total_impuestos_trasladados_imp;
                $total_impuestos_trasladados_imp = round($total_impuestos_trasladados_imp,2);
                $total_impuestos_trasladados_imp = number_format($total_impuestos_trasladados_imp,2);
                $total_impuestos_trasladados_imp ='"'.$total_impuestos_trasladados_imp.'"';
                $total_impuestos_trasladados_imp = str_replace(",", '', $total_impuestos_trasladados_imp);


                $base_total_importe_traslado = round($sub_total, 2);
                $base_total_importe_traslado = number_format($base_total_importe_traslado,2,'.','');
                $base_total_importe_traslado = '"'.$base_total_importe_traslado.'"';


                $xml = str_replace('|tag_base_importe_traslado|','Base=',$xml);
                $xml = str_replace('|base_total_importe_traslado|',$base_total_importe_traslado,$xml);
                $xml = str_replace('|total_impuestos_trasladados|',$total_impuestos_trasladados_imp,$xml);
                $xml = str_replace('|tag_total_impuestos_trasladados|','TotalImpuestosTrasladados=',$xml);
                $xml = str_replace('|tag_impuesto_trasladado|','Impuesto=',$xml);
                $xml = str_replace('|impuesto_trasladado|',$impuesto_trasladado,$xml);
                $xml = str_replace('|tag_tipo_factor_traslado|','TipoFactor=',$xml);
                $xml = str_replace('|tipo_factor_trasladado|',$tipo_factor_trasladado,$xml);
                $xml = str_replace('|tag_tasa_cuota_traslado|','TasaOCuota=',$xml);
                $xml = str_replace('|tasa_cuota_trasladado|',$tasa_cuota_traslado,$xml);
                $xml = str_replace('|tag_importe_traslado|','Importe=',$xml);
                $xml = str_replace('|importe_impuesto_trasladado|',$importe_impuesto_traslado_iva,$xml);
                $xml = str_replace('|tag_traslado_ini|','<cfdi:Traslado ',$xml);
                $xml = str_replace('|tag_traslado_fin|','/>',$xml);
                $xml = str_replace('|tag_traslados_ini|','<cfdi:Traslados>',$xml);
                $xml = str_replace('|tag_traslados_fin|','</cfdi:Traslados>',$xml);


            }
            else{
                $xml = str_replace('|tag_base_importe_traslado|','',$xml);
                $xml = str_replace('|base_total_importe_traslado|','',$xml);
                $xml = str_replace('|total_impuestos_trasladados|','',$xml);
                $xml = str_replace('|tag_total_impuestos_trasladados|','',$xml);
                $xml = str_replace('|tag_impuesto_trasladado|','',$xml);
                $xml = str_replace('|impuesto_trasladado|','',$xml);
                $xml = str_replace('|tag_tipo_factor_traslado|','',$xml);
                $xml = str_replace('|tipo_factor_trasladado|','',$xml);
                $xml = str_replace('|tag_tasa_cuota_traslado|','',$xml);
                $xml = str_replace('|tasa_cuota_trasladado|','',$xml);
                $xml = str_replace('|tag_importe_traslado|','',$xml);
                $xml = str_replace('|importe_impuesto_trasladado|','',$xml);
                $xml = str_replace('|tag_traslado_ini|','',$xml);
                $xml = str_replace('|tag_traslado_fin|','',$xml);
                $xml = str_replace('|tag_traslados_ini|','',$xml);
                $xml = str_replace('|tag_traslados_fin|','',$xml);
            }
            if($impuesto_traslado_codigo_ieps!=-1){

                $tasa_cuota_traslado_ieps = '"'.number_format($registro['factura_factor_ieps'],6,'.','').'"';
                $importe_impuesto_traslado_ieps = '"'.number_format(round($registro['factura_monto_ieps'],4),4,'.','').'"';

                $xml = str_replace('|tag_impuesto_trasladado_ieps|','Impuesto=',$xml);
                $xml = str_replace('|impuesto_trasladado_ieps|',$impuesto_trasladado_ieps,$xml);
                $xml = str_replace('|tag_tipo_factor_traslado_ieps|','TipoFactor=',$xml);
                $xml = str_replace('|tipo_factor_trasladado_ieps|','"Tasa"',$xml);
                $xml = str_replace('|tag_tasa_cuota_traslado_ieps|','TasaOCuota=',$xml);
                $xml = str_replace('|tasa_cuota_trasladado_ieps|',$tasa_cuota_traslado_ieps,$xml);
                $xml = str_replace('|tag_importe_traslado_ieps|','Importe=',$xml);
                $xml = str_replace('|importe_impuesto_trasladado_ieps|',$importe_impuesto_traslado_ieps,$xml);
                $xml = str_replace('|tag_traslado_ini_ieps|','<cfdi:Traslado ',$xml);
                $xml = str_replace('|tag_traslado_fin_ieps|','/>',$xml);
            }
            else{
                $xml = str_replace('|tag_impuesto_trasladado_ieps|','',$xml);
                $xml = str_replace('|impuesto_trasladado_ieps|','',$xml);
                $xml = str_replace('|tag_tipo_factor_traslado_ieps|','',$xml);
                $xml = str_replace('|tipo_factor_trasladado_ieps|','',$xml);
                $xml = str_replace('|tag_tasa_cuota_traslado_ieps|','',$xml);
                $xml = str_replace('|tasa_cuota_trasladado_ieps|','',$xml);
                $xml = str_replace('|tag_importe_traslado_ieps|','',$xml);
                $xml = str_replace('|importe_impuesto_trasladado_ieps|','',$xml);
                $xml = str_replace('|tag_traslado_ini_ieps|','',$xml);
                $xml = str_replace('|tag_traslado_fin_ieps|','',$xml);
                $xml = str_replace('|tag_traslados_ini_ieps|','',$xml);
                $xml = str_replace('|tag_traslados_fin_ieps|','',$xml);
            }
            if($registro['factura_total_impuestos_retenidos']>0) {
                $remplazo = 'TotalImpuestosRetenidos="'.$total_impuestos_retenidos.'"';
                $xml = str_replace('|total_impuestos_retenidos|', $remplazo,$xml);
            }
            else{
                $xml = str_replace('|total_impuestos_retenidos|', '',$xml);
            }
            $impuesto_retenido = $registro['factura_impuesto_retenido_codigo'];
            if($impuesto_retenido!='') {
                $remplazo = 'Impuesto="'.$impuesto_retenido.'"';
                $xml = str_replace('|impuesto_retenido|', $remplazo,$xml);
            }
            else{
                $xml = str_replace('|impuesto_retenido|', '',$xml);
            }
            if($registro['factura_impuesto_retenido_codigo']!='') {
                $remplazo = 'Impuesto="' . $registro['factura_impuesto_retenido_codigo'] . '"';
                $xml = str_replace('|tipo_factor_retenido|', $remplazo,$xml);
            }
            if($registro['factura_moneda_codigo'] !== 'MXN'){
                $remplazo = 'TipoCambio="' . $registro['factura_tipo_cambio'] . '"';
                $xml = str_replace('|tipo_cambio|', $remplazo,$xml);
            }
            else{
                $remplazo = '';
                $xml = str_replace('|tipo_cambio|', $remplazo,$xml);
            }
            if($registro['factura_factor_retenido']!='') {
                $xml = str_replace('|tasa_cuota_retenido|', number_format($registro['factura_factor_retenido'],6,'.',''),$xml);
            }
            if($registro['factura_total_impuestos_retenidos'] > 0) {
                $remplazo = 'Importe="'.number_format(round($registro['factura_total_impuestos_retenidos'],4),4,'.','').'"';
                $xml = str_replace('|importe_impuesto_retenido|', $remplazo,$xml);

                $remplazo = '<cfdi:Retencion';
                $xml = str_replace('|tag_retencion_inicial|', $remplazo,$xml);

                $remplazo = '/>';
                $xml = str_replace('|tag_retencion_final|', $remplazo,$xml);

                $remplazo = '<cfdi:Retenciones>';
                $xml = str_replace('|tag_retenciones_inicial|', $remplazo,$xml);

                $remplazo = '</cfdi:Retenciones>';
                $xml = str_replace('|tag_retenciones_final|', $remplazo,$xml);
            }
            else{
                $xml = str_replace('|importe_impuesto_retenido|', '',$xml);
                $xml = str_replace('|tag_retencion_inicial|', '',$xml);
                $xml = str_replace('|tag_retencion_final|', '',$xml);
                $xml = str_replace('|tag_retenciones_inicial|', '',$xml);
                $xml = str_replace('|tag_retenciones_final|', '',$xml);
            }
            if(($impuesto_traslado_codigo!='' || $impuesto_retenido_codigo!='')){
                $xml = str_replace('|tag_impuestos_ini|', '<cfdi:Impuestos',$xml);
                $xml = str_replace('|tag_impuestos_ini_cierre|', '>',$xml);
                $xml = str_replace('|tag_impuestos_fin|', '</cfdi:Impuestos>',$xml);
            }
            else{
                $xml = str_replace('|tag_impuestos_ini|', '',$xml);
                $xml = str_replace('|tag_impuestos_fin|', '',$xml);
                $xml = str_replace('|tag_impuestos_ini_cierre|', '',$xml);
            }
            $plantilla_partida = './plantillas_cfdi/partida.xml';

            /*Inicia Partidas*/

            $partidas_modelo = new Partida_Factura($this->link);
            $filtro = array('factura_id'=>$factura_id);
            $resultado = $partidas_modelo->filtro_and('partida_factura',$filtro);
            $partidas = $resultado['registros'];

            $xml_partida = '';
            foreach ($partidas as $partida){
                if(!isset($partida['partida_factura_descuento'])){
                    $partida['partida_factura_descuento'] = 0.0;
                }

                $impuesto_trasladado_id = $partida['partida_factura_impuesto_trasladado_id'];
                $impuesto_retenido_id = $partida['partida_factura_impuesto_retenido_id'];
                $descuento = $partida['partida_factura_descuento'];
                $impuesto_ieps = $partida['partida_factura_codigo_ieps'];



                $pt  = file_get_contents($plantilla_partida);
                $importe_base_traslado_sin_parsear = $partida['partida_factura_base'];
                $importe_base_traslado = '"'.number_format(round($partida['partida_factura_base'],2),2,'.','').'"';
                $partida['partida_factura_valor_unitario'] = number_format(round($partida['partida_factura_valor_unitario'],2),2,'.','');
                $impuesto_codigo = '"'.$partida['partida_factura_impuesto_codigo'].'"';
                $tipo_factor_codigo = '"'.$partida['partida_factura_tipo_factor_codigo'].'"';
                $tasa_cuota = '';
                if(isset($partida['partida_factura_tasa_cuota'])) {
                    $tasa_cuota = '"' . number_format(round($partida['partida_factura_tasa_cuota'], 6), 6, '.', '') . '"';
                }
                $total_impuestos_trasladados = '"'.number_format(round($partida['partida_factura_total_impuestos_trasladados'],2),2,'.','').'"';


                $tags = array();
                $tags['partida_factura_producto_sat_codigo'] = 'clave_producto_servicio';
                $tags['partida_factura_no_identificacion'] = 'no_identificacion';

                $pt = $this->reemplaza_tags(data: $pt,registro: $partida,tags: $tags);
                if(errores::$error){
                    return $this->error_->error('Error al reemplazar dato', $pt);
                }


                $pt = $this->descuento(descuento: $descuento,pt: $pt);
                if(errores::$error){
                    return $this->error_->error('Error al reemplazar dato', $pt);
                }

                $tags = array();
                $tags['partida_factura_cantidad'] = 'cantidad';
                $tags['partida_factura_unidad_codigo'] = 'clave_unidad';
                $tags['partida_factura_unidad'] = 'unidad';
                $tags['partida_factura_descripcion'] = 'descripcion';
                $tags['partida_factura_obj_imp'] = 'obj_imp';

                $pt = $this->reemplaza_tags(data: $pt,registro: $partida,tags: $tags);
                if(errores::$error){
                    return $this->error_->error('Error al reemplazar dato', $pt);
                }

                $tags = array();
                $tags['partida_factura_valor_unitario'] = 'valor_unitario';
                $tags['partida_factura_importe'] = 'importe';

                $pt = $this->reemplaza_tags_0(data: $pt,registro: $partida,tags: $tags);
                if(errores::$error){
                    return $this->error_->error('Error al reemplazar dato', $pt);
                }



                $importe_base_ieps = '"'.number_format(round($partida['partida_factura_importe'],4),4,'.','').'"';
                $impuesto_codigo_ieps = '"'.$partida['partida_factura_codigo_ieps'].'"';
                $tipo_factor_codigo_ieps = '"Tasa"';
                $tasa_cuota_ieps = '"'.number_format(round($partida['partida_factura_factor_ieps'],6),6,'.','').'"';
                $importe_impuesto_ieps = '"'.number_format(round($partida['partida_factura_monto_ieps'],4),4,'.','').'"';

                $tags = array();
                $tags[] = 'importe_base_ieps';
                $tags[] = 'etiqueta_base_ieps';
                $tags[] = 'etiqueta_impuesto_ieps';
                $tags[] = 'impuesto_codigo_ieps';
                $tags[] = 'etiqueta_tipo_factor_ieps';
                $tags[] = 'tag_inicio_traslado_ieps';
                $tags[] = 'tipo_factor_codigo_ieps';
                $tags[] = 'tipo_factor_codigo_ieps';
                $tags[] = 'etiqueta_tasa_cuota_ieps';
                $tags[] = 'tasa_cuota_ieps';
                $tags[] = 'etiqueta_importe_impuesto_ieps';
                $tags[] = 'importe_impuesto_ieps';
                $tags[] = 'tag_fin_traslado_ieps';
                if($impuesto_ieps !=-1){

                    $pt = str_replace('|importe_base_ieps|',$importe_base_ieps,$pt);
                    $pt = str_replace('|etiqueta_base_ieps|','Base=',$pt);
                    $pt = str_replace('|etiqueta_impuesto_ieps|','Impuesto=',$pt);
                    $pt = str_replace('|impuesto_codigo_ieps|',$impuesto_codigo_ieps,$pt);
                    $pt = str_replace('|etiqueta_tipo_factor_ieps|','TipoFactor=',$pt);
                    $pt = str_replace('|tag_inicio_traslado_ieps|','<cfdi:Traslado',$pt);
                    $pt = str_replace('|tipo_factor_codigo_ieps|',$tipo_factor_codigo_ieps,$pt);
                    $pt = str_replace('|etiqueta_tasa_cuota_ieps|','TasaOCuota=',$pt);
                    $pt = str_replace('|tasa_cuota_ieps|',$tasa_cuota_ieps,$pt);
                    $pt = str_replace('|etiqueta_importe_impuesto_ieps|','Importe=',$pt);
                    $pt = str_replace('|importe_impuesto_ieps|',$importe_impuesto_ieps,$pt);
                    $pt = str_replace('|tag_fin_traslado_ieps|','/>',$pt);
                }
                else{
                    $pt = $this->vacia_tags(pt: $pt,tags: $tags);
                    if(errores::$error){
                        return $this->error_->error('Error al reemplazar dato', $pt);
                    }

                }


                $tags = array();
                $tags[] = 'importe_base';
                $tags[] = 'etiqueta_base';
                $tags[] = 'etiqueta_impuesto';
                $tags[] = 'impuesto_codigo';
                $tags[] = 'tipo_factor_codigo';
                $tags[] = 'etiqueta_tipo_factor';
                $tags[] = 'etiqueta_tasa_cuota';
                $tags[] = 'tasa_cuota';
                $tags[] = 'importe_impuesto';
                $tags[] = 'etiqueta_importe_impuesto';
                $tags[] = 'tag_inicio_traslado';
                $tags[] = 'tag_fin_traslado';
                $tags[] = 'tag_inicio_traslados';
                $tags[] = 'tag_fin_traslados';

                if($impuesto_trasladado_id !=0){

                    $pt = str_replace('|importe_base|',$importe_base_traslado,$pt);
                    $pt = str_replace('|etiqueta_base|','Base=',$pt);
                    $pt = str_replace('|etiqueta_impuesto|','Impuesto=',$pt);
                    $pt = str_replace('|impuesto_codigo|',$impuesto_codigo,$pt);
                    $pt = str_replace('|etiqueta_tipo_factor|','TipoFactor=',$pt);
                    $pt = str_replace('|tipo_factor_codigo|',$tipo_factor_codigo,$pt);
                    $pt = str_replace('|etiqueta_tasa_cuota|','TasaOCuota=',$pt);
                    $pt = str_replace('|tasa_cuota|',$tasa_cuota,$pt);
                    $pt = str_replace('|importe_impuesto|',$total_impuestos_trasladados,$pt);
                    $pt = str_replace('|etiqueta_importe_impuesto|','Importe=',$pt);
                    $pt = str_replace('|tag_inicio_traslado|','<cfdi:Traslado',$pt);
                    $pt = str_replace('|tag_fin_traslado|','/>',$pt);
                    $pt = str_replace('|tag_inicio_traslados|','<cfdi:Traslados>',$pt);
                    $pt = str_replace('|tag_fin_traslados|','</cfdi:Traslados>',$pt);

                }
                else{
                    $pt = $this->vacia_tags(pt: $pt,tags: $tags);
                    if(errores::$error){
                        return $this->error_->error('Error al reemplazar dato', $pt);
                    }

                }


                $tags = array();
                $tags[] = 'tag_inicio_retenciones';
                $tags[] = 'tag_fin_retenciones';
                $tags[] = 'tag_inicio_retencion';
                $tags[] = 'tag_fin_retencion';
                $tags[] = 'etiqueta_base_retencion';
                $tags[] = 'importe_base_retencion';
                $tags[] = 'etiqueta_impuesto_retencion';
                $tags[] = 'impuesto_codigo_retencion';
                $tags[] = 'etiqueta_tipo_factor_retencion';
                $tags[] = 'tipo_factor_codigo_retencion';
                $tags[] = 'etiqueta_tasa_cuota_retencion';
                $tags[] = 'tasa_cuota_retencion';
                $tags[] = 'importe_impuesto_retencion';
                $tags[] = 'etiqueta_importe_impuesto_retencion';
                if($impuesto_retenido_id !=0 && $impuesto_retenido_id!=''){
                    $importe_base_retencion = '"'.number_format(round($partida['partida_factura_base'],2),2,'.','').'"';
                    $impuesto_codigo_retencion = '"'.$partida['partida_factura_impuesto_retenido_codigo'].'"';
                    $tipo_factor_codigo_retencion = '"'.$partida['partida_factura_tipo_factor_retenido_codigo'].'"';
                    $tasa_cuota_retencion = '"'.number_format(round($partida['partida_factura_tasa_cuota_retenido'],6),6,'.','').'"';
                    $total_impuestos_retenidos = '"'.number_format(round($partida['partida_factura_total_impuestos_retenidos'],2),2,'.','').'"';


                    $pt = str_replace('|tag_inicio_retenciones|','<cfdi:Retenciones>',$pt);
                    $pt = str_replace('|tag_fin_retenciones|','</cfdi:Retenciones>',$pt);
                    $pt = str_replace('|tag_inicio_retencion|','<cfdi:Retencion',$pt);
                    $pt = str_replace('|tag_fin_retencion|','/>',$pt);
                    $pt = str_replace('|etiqueta_base_retencion|','Base=',$pt);
                    $pt = str_replace('|importe_base_retencion|',$importe_base_retencion,$pt);
                    $pt = str_replace('|etiqueta_impuesto_retencion|','Impuesto=',$pt);
                    $pt = str_replace('|impuesto_codigo_retencion|',$impuesto_codigo_retencion,$pt);


                    $pt = str_replace('|etiqueta_impuesto_retencion|','Impuesto=',$pt);
                    $pt = str_replace('|etiqueta_tipo_factor_retencion|','TipoFactor=',$pt);
                    $pt = str_replace('|tipo_factor_codigo_retencion|',$tipo_factor_codigo_retencion,$pt);
                    $pt = str_replace('|etiqueta_tasa_cuota_retencion|','TasaOCuota=',$pt);
                    $pt = str_replace('|tasa_cuota_retencion|',$tasa_cuota_retencion,$pt);
                    $pt = str_replace('|importe_impuesto_retencion|',$total_impuestos_retenidos,$pt);
                    $pt = str_replace('|etiqueta_importe_impuesto_retencion|','Importe=',$pt);


                }
                else{

                    $pt = $this->vacia_tags(pt: $pt,tags: $tags);
                    if(errores::$error){
                        return $this->error_->error('Error al reemplazar dato', $pt);
                    }

                }

                $tags = array();
                $tags[] = 'tag_impuestos_inicial';
                $tags[] = 'tag_impuestos_final';

                if(($impuesto_trasladado_id !=0 || ($impuesto_retenido_id !=0 || $impuesto_retenido_id!='')) && $importe_base_traslado_sin_parsear>0 ){
                    $pt = str_replace('|tag_impuestos_inicial|','<cfdi:Impuestos>',$pt);
                    $pt = str_replace('|tag_impuestos_final|','</cfdi:Impuestos>',$pt);
                }
                else{
                    $pt = $this->vacia_tags(pt: $pt,tags: $tags);
                    if(errores::$error){
                        return $this->error_->error('Error al reemplazar dato', $pt);
                    }
                }



                $xml_partida = $xml_partida.$pt;
            }
            $xml = str_replace('|conceptos|',$xml_partida,$xml);

            /*
            $haiga = true;
            while($haiga){
                $encuentra = strpos($xml, '  ');

                if($encuentra === false){
                    $haiga = false;
                }
                $xml = str_replace('  ', ' ', $xml);

            }

            $un_espacio[' <cfdi:'] = '<cfdi:';
            $un_espacio[' </cfdi:'] = '</cfdi:';
            foreach ($un_espacio as $search=>$replace){
                $xml = str_replace($search, $replace, $xml);
            }

            $xml = str_replace('
 ', '', $xml);
            $xml = str_replace('

', '
', $xml);
            $xml = str_replace('<cfdi:CfdiRelacionados T', '    <cfdi:CfdiRelacionados T', $xml);
            $xml = str_replace('</cfdi:CfdiRelacionados>', '    </cfdi:CfdiRelacionados>', $xml);

            $xml = str_replace('<cfdi:CfdiRelacionado U', '        <cfdi:CfdiRelacionado U', $xml);
            $xml = str_replace('<cfdi:Emisor', '    <cfdi:Emisor', $xml);
            $xml = str_replace('<cfdi:Receptor', '    <cfdi:Receptor', $xml);
            $xml = str_replace('<cfdi:Conceptos>', '    <cfdi:Conceptos>', $xml);
            $xml = str_replace('</cfdi:Conceptos>', '    </cfdi:Conceptos>', $xml);
            $xml = str_replace('<cfdi:Concepto C', '        <cfdi:Concepto C', $xml);
            $xml = str_replace('</cfdi:Concepto>', '        </cfdi:Concepto>', $xml);
            $xml = str_replace('<cfdi:Impuestos>', '            <cfdi:Impuestos>', $xml);
            $xml = str_replace('</cfdi:Impuestos>', '            </cfdi:Impuestos>', $xml);
            $xml = str_replace('<cfdi:Traslados>', '                <cfdi:Traslados>', $xml);
            $xml = str_replace('</cfdi:Traslados>', '                </cfdi:Traslados>', $xml);
            $xml = str_replace('<cfdi:Traslado I', '                    <cfdi:Traslado I', $xml);
            $xml = str_replace('<cfdi:Impuestos', '    <cfdi:Impuestos', $xml);
            $xml = str_replace('            </cfdi:Impuestos>', '    </cfdi:Impuestos>', $xml);*/


            $repositorio->guarda_archivo($xml,$folio, $repositorio->directorio_xml_sin_timbrar_completo, '.xml');
        }


        $xml_data = new SimpleXMLElement ($ruta_xml_sin_timbrar,0,true);

        $facturas = new facturas($this->link);
        $this->datos_comprobante = $facturas->obten_datos_comprobante($xml_data);
        $this->datos_emisor = $facturas->obten_datos_emisor($xml_data);
        $this->datos_receptor = $facturas->obten_datos_receptor($xml_data);
        setlocale(LC_MONETARY, 'en_US');
        $this->encabezado_html =$this->encabezado_html."<hr>";


        $this->datos_partidas = $facturas->obten_datos_partidas(xml: $xml_data);


        $this->datos_impuestos = $facturas->obten_datos_impuestos($xml_data);
        $this->datos_impuestos_traslados = $facturas->obten_datos_impuestos_trasladados($xml_data);
        $this->datos_impuestos_retenidos = $facturas->obten_datos_impuestos_retenidos($xml_data);
        $numero_text = new NumeroTexto();

        $moneda_mostrar = '';
        if($this->datos_comprobante['Moneda'] == 'MXN'){
            $moneda_mostrar = 'PESOS';
        }
        elseif($this->datos_comprobante['Moneda'] == 'USD'){
            $moneda_mostrar = 'DOLARES';
        }

        $this->numero_texto = $numero_text->to_word($this->datos_comprobante['Total'],$this->datos_comprobante['Moneda']);
    }

    /**
     * ERROR UNIT
     * @param array $datos_empresa
     * @param array $registro
     * @return array
     */
    private function init_update(array $datos_empresa, array $registro): array
    {
        if(!isset($registro['factura_interior_expedicion']) ||$registro['factura_interior_expedicion'] === '' ){
            $registro['factura_interior_expedicion'] = ' ';
        }
        if(!isset($datos_empresa['rfc']) ||$datos_empresa['rfc'] === '' ){
            return $this->error_->error('Error no existe rfc en $datos_empresa', $datos_empresa);
        }
        $update = $this->init_array_update_emisor(registro: $registro);
        if(errores::$error){
            return $this->error_->error('Error al asignar datos', $update);
        }

        $update['rfc_emisor'] = $datos_empresa['rfc'];
        return $update;
    }

    /**
     * ERROR UNIT
     * @param array $registro
     * @return array
     */
    PUBLIC function init_array_update_emisor(array $registro): array
    {
        $update = array();

        $data = $this->keys_init_emisor();
        if(errores::$error){
            return $this->error_->error('Error al asignar datos', $data);
        }
        $update = $this->asigna_data_update_emisor(data: $data,registro: $registro,update: $update);
        if(errores::$error){
            return $this->error_->error('Error al asignar datos', $update);
        }
        return $update;
    }

    /**
     * ERROR UNIT
     * @param array $data
     * @param array $registro
     * @param array $update
     * @return array
     */
    private function asigna_data_update_emisor(array $data, array $registro, array $update): array
    {
        foreach($data as $campo_upd=>$data_value_upd){
            if(!is_array($data_value_upd)){
                return $this->error_->error('Error al $data_value_upd debe ser un array', $data_value_upd);
            }
            $update = $this->init_update_emisor(campo_upd: $campo_upd,data_value_upd: $data_value_upd,
                registro: $registro,update: $update);
            if(errores::$error){
                return $this->error_->error('Error al asignar datos', $update);
            }
        }
        return $update;
    }

    /**
     * ERROR UNIT
     * @param string $campo_upd
     * @param array $data_value_upd
     * @param array $registro
     * @param array $update
     * @return array
     */
    private function init_update_emisor(string $campo_upd, array $data_value_upd, array $registro, array $update): array
    {
        $campo_upd = trim($campo_upd);
        if($campo_upd === ''){
            return $this->error_->error('Error $campo_upd esta vacio', $campo_upd);
        }
        $keys = array($campo_upd);
        $valida = (new validacion())->valida_existencia_keys($keys, $registro);
        if(errores::$error){
            return $this->error_->error('Error al validar $registro', $valida);
        }

        if((string)$registro[$campo_upd]===''){
            $update = $this->asigna_datos_emisor(data_value_upd: $data_value_upd,update: $update);
            if(errores::$error){
                return $this->error_->error('Error al asignar datos', $update);
            }
        }
        return $update;
    }

    /**
     * UNIT ERROR
     * @return array
     */
    PUBLIC function keys_init_emisor(): array
    {
        $data['factura_lugar_expedicion'] = array('lugar_expedicion'=>'cp');
        $data['factura_calle_expedicion'] = array('calle_expedicion'=>'calle');
        $data['factura_exterior_expedicion'] = array('exterior_expedicion'=>'exterior');
        $data['factura_interior_expedicion'] = array('interior_expedicion'=>'interior');
        $data['factura_colonia_expedicion'] = array('colonia_expedicion'=>'colonia');
        $data['factura_municipio_expedicion'] = array('municipio_expedicion'=>'municipio');
        $data['factura_estado_expedicion'] = array('estado_expedicion'=>'estado');
        $data['factura_pais_expedicion'] = array('pais_expedicion'=>'pais');
        $data['factura_nombre_emisor'] = array('nombre_emisor'=>'razon_social');
        $data['factura_regimen_fiscal_emisor_codigo'] = array('regimen_fiscal_emisor_codigo'=>'regimen_fiscal');
        $data['factura_regimen_fiscal_emisor_descripcion'] = array('regimen_fiscal_emisor_descripcion'=>'regimen_fiscal_descripcion');
        return $data;
    }

    /**
     * ERROR UNIT
     * @param array $data_value_upd
     * @param array $update
     * @return array
     */
    private function asigna_datos_emisor(array $data_value_upd, array $update): array
    {
        $key_upd = key($data_value_upd);
        if(!isset($data_value_upd[$key_upd])){
            return $this->error_->error('Error no existe $data_value_upd[$key_upd]', $data_value_upd);
        }
        $value_upd = trim($data_value_upd[$key_upd]);
        $update[$key_upd] = $value_upd;

        return $update;
    }

    private function vacia_tags(string $pt, array $tags): array|string
    {
        $pt_r = $pt;
        foreach ($tags as $tag){
            $pt_r = $this->vacia_tag(pt: $pt_r,tag: $tag);
            if(errores::$error){
                return $this->error_->error('Error al reemplazar dato', $pt);
            }
        }
        return $pt_r;
    }

    private function vacia_tag(string $pt, string $tag): array|string
    {
        $tag_rp = "|$tag|";
        return str_replace($tag_rp,'',$pt);
    }

    private function descuento(float $descuento, string $pt): array|string
    {

        if($descuento > 0.0){
            $descuento_xml = '"'.number_format($descuento,2,'.','').'"';
            $pt = str_replace('|Descuento|','Descuento='.$descuento_xml,$pt);
        }
        else{
            $pt = str_replace('|Descuento|','',$pt);
        }
        return $pt;
    }

    private function cfdis_relacionados_full(int $factura_id, array $registro): array|string
    {
        $cfdis_relacionados = $this->relacion_anticipo(registro: $registro);
        if(errores::$error){
            return $this->error_->error(mensaje:  'Error al asignar relaciones',data: $cfdis_relacionados,
                params: get_defined_vars());
        }

        if($registro['tipo_relacion_id'] !== ''){

            $cfdis_relacionados = $this->cfdis_rel_directo(factura_id: $factura_id,registro: $registro);
            if(errores::$error){
                return $this->error_->error(mensaje:  'Error al asignar relaciones',data: $cfdis_relacionados,
                    params: get_defined_vars());
            }

        }

        return $cfdis_relacionados;
    }

    private function relacion_anticipo(array $registro): string
    {
        $relaciones = '';
        if(isset($registro['anticipo_uuid']) && trim($registro['anticipo_uuid'])!=='') {
            $relaciones = '<cfdi:CfdiRelacionados TipoRelacion="07">
                    <cfdi:CfdiRelacionado UUID="' . $registro['anticipo_uuid'] . '" />
                </cfdi:CfdiRelacionados>';
        }
        return $relaciones;
    }

    private function cfdis_rel_directo(int $factura_id, array $registro): array|string
    {
        $cfdis_relacionados = '';
        if($registro['tipo_relacion_id'] !== ''){

            $facturas_relacionadas = (new factura($this->link))->facturas_relacionadas(factura_id: $factura_id);
            if(errores::$error){
                return $this->error_->error(
                    mensaje:  'Error al obtener facturas relacionadas',data: $facturas_relacionadas,
                    params: get_defined_vars());
            }

            $cfdis_relacionados = $this->cfdis_relacionados(
                facturas_relacionadas: $facturas_relacionadas,registro: $registro);

            if(errores::$error){
                return $this->error_->error(mensaje:  'Error al asignar relaciones',data: $cfdis_relacionados,
                    params: get_defined_vars());
            }

        }
        return $cfdis_relacionados;
    }

    private function cfdis_relacionados(array $facturas_relacionadas, array $registro): array|string
    {
        $cfdis_relacionados = '<cfdi:CfdiRelacionados TipoRelacion="'.$registro['tipo_relacion_codigo'].'">';

        $facturas_relacionadas_xml = (new facturas($this->link))->facturas_relacionadas(
            facturas_relacionadas: $facturas_relacionadas);

        if(errores::$error){
            return $this->error_->error(mensaje:  'Error al asignar relaciones',data: $facturas_relacionadas_xml,
                params: get_defined_vars());
        }

        $cfdis_relacionados.=$facturas_relacionadas_xml;
        $cfdis_relacionados.='</cfdi:CfdiRelacionados>';
        return $cfdis_relacionados;
    }

    private function reemplaza_tags(string $data, array $registro, array $tags): array|string
    {
        $data_r = $data;
        foreach($tags as $key=>$tag){

            $data_r = $this->tag_replace(data: $data_r, key: $key,registro: $registro,tag: $tag);
            if(errores::$error){
                return $this->error_->error('Error al reemplazar dato', $data);
            }

        }
        return $data_r;
    }

    private function reemplaza_tags_0(string $data, array $registro, array $tags): array|string
    {
        $data_r = $data;
        foreach($tags as $key=>$tag){
            $data_r = $this->tag_replace_0(data: $data_r,key: $key,registro: $registro,tag: $tag);
            if(errores::$error){
                return $this->error_->error('Error al reemplazar dato', $data);
            }

        }
        return $data_r;
    }

    private function tag_replace(string $data, string $key , array $registro, string $tag): array|string
    {
        if(!isset($registro[$key])){
            $registro[$key] = '';
        }

        $tag_rmp = "|$tag|";
        return str_replace($tag_rmp,$registro[$key],$data);
    }

    private function tag_replace_0(string $data, string $key , array $registro, string $tag): array|string
    {

        $tag_rmp = "|$tag|";
        $value = round($registro[$key],4);
        $value_str = number_format($value,2,'.','');
        return str_replace($tag_rmp,$value_str,$data);
    }



    public function paga_factura(){
        $breadcrumbs = array('lista','alta');
        $this->breadcrumbs = $this->directiva->nav_breadcumbs(12, 0, $breadcrumbs);
        $this->numero_empresa = $_SESSION['numero_empresa'];
        $empresa = new Empresas();

        $datos_empresa = $empresa->empresas[$this->numero_empresa];

        $this->cuentas_empresa = $datos_empresa['cuentas'];

        $regimenes_fiscales = $empresa->regimenes_fiscales;
        $this->rfc = $datos_empresa['rfc'];
        $this->razon_social = $datos_empresa['razon_social'];
        $this->regimen_fiscal = $datos_empresa['regimen_fiscal'];
        $this->nombre_regimen_fiscal = $regimenes_fiscales[$this->regimen_fiscal];


        $factura_id = $_GET['factura_id'];
        $factura_modelo = new Factura($this->link);

        $resultado = $factura_modelo->obten_por_id('factura',$factura_id);

        $factura = $resultado['registros'][0];


        $this->cliente_id = $factura['cliente_id'];
        $cliente = new Cliente($this->link);
        $clientes = $cliente->obten_por_id('cliente', $this->cliente_id);
        $this->datos_cliente = $clientes['registros'][0];


        $cuenta_bancaria_modelo = new Cuenta_Bancaria($this->link);

        $filtro = array('cliente_id'=>$this->cliente_id);

        $resultado = $cuenta_bancaria_modelo->filtro_and('cuenta_bancaria',$filtro);

        $this->cuentas_bancarias = $resultado['registros'];


        $this->forma_pago_id = $this->datos_cliente['forma_pago_id'];
        $this->moneda_id = $this->datos_cliente['moneda_id'];

        $this->saldo_factura = $factura['factura_total'];


    }

    public function partida_ajax(){


    }



}