<?php
namespace controllers;
use config\empresas;
use facturas;
use FPDF;
use gamboamartin\errores\errores;
use models\cliente;
use models\factura;
use models\nota_credito;
use my_pdf;
use NumeroTexto;
use repositorio;
use SoapClient;
use SoapFault;
use xml_cfdi;

class controlador_nota_credito extends controlador_base {
    public $notas_credito = false;
    public $nota_credito = array();
    public $nota_credito_id = false;
    public $directorio_xml_sin_timbrar_completo;
    public $directorio_xml_timbrado_completo;
    public $factura = false;
    public $facturas = false;

    public function alta_bd(){
        $factura_modelo = new factura($this->link);
        $_POST['status'] = '1';


        $nota_credito = unserialize($_SESSION['nota_credito']);

        $_POST = array_merge($_POST,$nota_credito);


        $factura_id = $_POST['factura_id'];
        $monto = $_POST['monto'];
        $porcentaje_iva = $_POST['porcentaje_iva'];

        $r_factura = $factura_modelo->obten_por_id('factura',$factura_id);

        $factura = $r_factura['registros'][0];

        $saldo_anterior = round($factura['factura_saldo'],2);

        $monto_iva = round($monto * $porcentaje_iva,2);
        $total_nota_credito = round($monto + $monto_iva,2);

        $saldo_nuevo = round($saldo_anterior - $total_nota_credito);

        if($saldo_nuevo < 0){
            header('Location: index.php?seccion=nota_credito&accion=alta&session_id='
                .SESSION_ID.'&mensaje=La nota de credito no puede exceder el saldo&tipo_mensaje=error');
            exit;
        }

        $factura_ins['status'] = 'activo';
        $factura_ins['saldo'] = $saldo_nuevo;

        $factura_modelo->modifica_bd($factura_ins,'factura',$factura_id);


        parent::alta_bd();
    }

    public function aplica_nota_credito_factura(){
        $factura_id = $_GET['factura_id'];
        $factura_modelo = new factura($this->link);
        $r_factura = $factura_modelo->obten_por_id('factura',$factura_id);

        $this->factura = $r_factura['registros'][0];

        $nota_credito_modelo = new nota_credito($this->link);
        $filtro = array('cliente.id'=>$this->factura['cliente_id'],
            'nota_credito.aplicado_factura'=>'no',
            'nota_credito.status_nota_credito'=>'timbrado');


        $r_nota_credito = $nota_credito_modelo->filtro_and('nota_credito', $filtro);


        $this->notas_credito = $r_nota_credito['registros'];



    }

    public function asigna_nota_credito(){
        $factura_id = $_POST['factura_id'];
        $factura['nota_credito_id'] = $_POST['nota_credito_id'];
        $factura['status'] = 'activo';

        $factura_modelo = new factura($this->link);
        $factura_modelo->modifica_bd($factura,'factura',$factura_id);

        $nota_credito['aplicado_factura'] = 'si';
        $nota_credito['status'] = 'activo';

        $nota_credito_modelo = new nota_credito($this->link);
        $nota_credito_modelo->modifica_bd($nota_credito,'nota_credito',$_POST['nota_credito_id']);
        header('Location: index.php?seccion=cliente&accion=vista_preliminar_factura&factura_id=$factura_id');
        exit;

    }
    public function descarga_xml(){

        $nota_credito_id = $_GET['nota_credito_id'];

        $modelo_nota_credito = new nota_credito($this->link);
        $resultado = $modelo_nota_credito->obten_por_id('nota_credito',$nota_credito_id);

        $nota_credito = $resultado['registros'][0];

        $folio = $nota_credito['nota_credito_folio'];

        $name_file = 'A_'.$nota_credito['nota_credito_folio'];


        $numero_empresa = $_SESSION['numero_empresa'];
        $empresas = new empresas();
        $datos_empresa = $empresas->empresas[$numero_empresa];

        $ruta_base = $datos_empresa['nombre_base_datos'];

        $ruta_xml = $ruta_base.'/xml_timbrado/NC_'.$folio.'.xml';

        header("Content-disposition: attachment; filename=$name_file.xml");
        header('Content-type: "text/xml"; charset="utf8"');
        readfile($ruta_xml);
        exit;

    }


    public function elimina_nota_credito(){
        $nota_credito_id = $_GET['nota_credito_id'];

        $nota_credito_modelo = new nota_credito($this->link);

        $nota_credito_modelo->elimina_bd('nota_credito',$nota_credito_id);

        header('Location: index.php?seccion=nota_credito&accion=lista&session_id='.SESSION_ID);
        exit;

    }

    private function genera_datos_nota_credito(){

        $nota_credito_modelo = new nota_credito($this->link);
        $r_nota_credito = $nota_credito_modelo->obten_por_id('nota_credito',$this->nota_credito_id);
        $this->nota_credito = $r_nota_credito['registros'][0];

        $cliente_modelo = new factura($this->link);
        $registro_cl['cliente_id'] =  $this->nota_credito['cliente_id'];
        $registro_rf = $cliente_modelo->regimen_fiscal_receptor(registro: $registro_cl);


        $empresa = new empresas();
        $datos_empresa = $empresa->empresas[$_SESSION['numero_empresa']];
        $this->nota_credito['uso_cfdi_codigo'] = 'P01';
        $this->nota_credito['uso_cfdi_descripcion'] = 'Por Definir';
        $this->nota_credito['producto_codigo'] = '84111506';
        $this->nota_credito['producto_cantidad'] = '1';
        $this->nota_credito['unidad_codigo'] = 'ACT';
        $this->nota_credito['insumo_descripcion'] = 'Nota de Crédito';
        $this->nota_credito['impuesto_descripcion'] = 'IVA';
        $this->nota_credito['tipo_impuesto_descripcion'] = 'Traslado';
        $this->nota_credito['tipo_factor_descripcion'] = 'Tasa';
        $this->nota_credito['tipo_comprobante_codigo'] = 'E';
        $this->nota_credito['impuesto_codigo'] = '002';
        $this->nota_credito['metodo_pago_codigo'] = 'PUE';
        $this->nota_credito['obj_imp'] = '01';
        $this->nota_credito['cliente_rf'] = $registro_rf['cliente_rf'];
        $cliente_rfc = $this->nota_credito['cliente_rfc'];
        $this->nota_credito = array_merge($datos_empresa, $this->nota_credito);
    }

    public function genera_xml(){

        $this->nota_credito_id = $_GET['nota_credito_id'];
        $this->genera_datos_nota_credito();

        $repositorio = New repositorio();

        $empresa = new empresas();
        $datos_empresa = $empresa->empresas[$_SESSION['numero_empresa']];
        $ruta_base = $datos_empresa['nombre_base_datos'];


        $LugarExpedicion = $this->nota_credito['cp'];
        $TipoComprobante = $this->nota_credito['tipo_comprobante_codigo'];

        $Fecha = $this->nota_credito['nota_credito_fecha'].'T06:00:00';
        $Folio = $this->nota_credito['nota_credito_folio'];
        //$Folio = rand(1,10000000);
        $Serie = $this->nota_credito['serie'];
        $RfcEmisor = $this->nota_credito['rfc'];
        $Moneda = $this->nota_credito['moneda_codigo'];
        $SubTotal = $this->nota_credito['nota_credito_monto'];
        $Total = $this->nota_credito['nota_credito_total'];
        $UsoCFDI = $this->nota_credito['uso_cfdi_codigo'];
        $NombreEmisor = $this->nota_credito['razon_social'];
        $RegimenFiscal = $this->nota_credito['regimen_fiscal'];
        $RfcReceptor = trim($this->nota_credito['cliente_rfc']);
        $NombreReceptor = $this->nota_credito['cliente_razon_social'];
        $ClienteCP = trim($this->nota_credito['cliente_cp']);
        $ClienteRF = trim($this->nota_credito['cliente_rf']);
        $ClaveProdServ = $this->nota_credito['producto_codigo'];
        $ObjImp = $this->nota_credito['obj_imp'];
        $Cantidad = $this->nota_credito['producto_cantidad'];
        $ClaveUnidad = $this->nota_credito['unidad_codigo'];
        $Descripcion = $this->nota_credito['insumo_descripcion'];
        $ValorUnitario = $this->nota_credito['nota_credito_monto'];
        $FormaPago = $this->nota_credito['forma_pago_codigo'];
        $Importe = $this->nota_credito['nota_credito_monto'];
        $Base = $this->nota_credito['nota_credito_monto'];
        $ImporteIVA = $this->nota_credito['nota_credito_monto_iva'];
        $Impuesto = $this->nota_credito['impuesto_codigo'];
        $Tasa = number_format(round($this->nota_credito['nota_credito_porcentaje_iva'],6),6,'.','');
        $TipoFactor = $this->nota_credito['tipo_factor_descripcion'];
        $MetodoPago = $this->nota_credito['metodo_pago_codigo'];
        $TipoCambio = number_format(round($this->nota_credito['nota_credito_tipo_cambio'],6),6,'.','');
        if($Moneda == 'MXN'){
            $TipoCambio = 1;
        }

        $plantilla = './plantillas_cfdi/nota_credito.xml';
        $xml  = file_get_contents($plantilla);


        $cfdis_relacionados = '';
        if($this->nota_credito['factura_id']){
            $cfdis_relacionados = '<cfdi:CfdiRelacionados TipoRelacion="01">
        <cfdi:CfdiRelacionado UUID="'.$this->nota_credito['factura_uuid'].'" />
    </cfdi:CfdiRelacionados>';
        }

        $xml = str_replace('|LugarExpedicion|',$LugarExpedicion,$xml);
        $xml = str_replace('|SubTotal|',$SubTotal,$xml);
        $xml = str_replace('|UsoCFDI|',$UsoCFDI,$xml);
        $xml = str_replace('|Total|',$Total,$xml);
        $xml = str_replace('|Moneda|',$Moneda,$xml);
        $xml = str_replace('|TipoComprobante|',$TipoComprobante,$xml);
        $xml = str_replace('|Fecha|',$Fecha,$xml);
        $xml = str_replace('|Folio|',$Folio,$xml);
        $xml = str_replace('|Serie|',$Serie,$xml);
        $xml = str_replace('|RfcEmisor|',$RfcEmisor,$xml);
        $xml = str_replace('|NombreEmisor|',$NombreEmisor,$xml);
        $xml = str_replace('|RegimenFiscal|',$RegimenFiscal,$xml);
        $xml = str_replace('|RfcReceptor|',$RfcReceptor,$xml);

        $xml = str_replace('|ClienteCP|',$ClienteCP,$xml);
        $xml = str_replace('|ClienteRF|',$ClienteRF,$xml);

        $xml = str_replace('|ClaveProdServ|',$ClaveProdServ,$xml);
        $xml = str_replace('|ObjImp|',$ObjImp,$xml);
        $xml = str_replace('|Cantidad|',$Cantidad,$xml);
        $xml = str_replace('|ClaveUnidad|',$ClaveUnidad,$xml);
        $xml = str_replace('|Descripcion|',$Descripcion,$xml);
        $xml = str_replace('|ValorUnitario|',$ValorUnitario,$xml);
        $xml = str_replace('|Importe|',$Importe,$xml);
        $xml = str_replace('|NombreReceptor|',$NombreReceptor,$xml);
        $xml = str_replace('|Base|',$Base,$xml);
        $xml = str_replace('|ImporteIVA|',$ImporteIVA,$xml);
        $xml = str_replace('|Impuesto|',$Impuesto,$xml);
        $xml = str_replace('|Tasa|',$Tasa,$xml);
        $xml = str_replace('|TipoFactor|',$TipoFactor,$xml);
        $xml = str_replace('|TipoCambio|',$TipoCambio,$xml);
        $xml = str_replace('|FormaPago|',$FormaPago,$xml);
        $xml = str_replace('|MetodoPago|',$MetodoPago,$xml);
        $xml = str_replace('|cdfis_relacionados|',$cfdis_relacionados,$xml);

        $repositorio->guarda_archivo($xml,'NC_'.$Folio, $repositorio->directorio_xml_sin_timbrar_completo, '.xml');

        $factura = new facturas($this->link);
        $response = $factura->timbra_cfdi_nota_credito($Folio);

        $mensaje = 'Exito';
        $tipo_mensaje='exito';
        if(isset($response['error'])){
            if($response['error'] == 1){
                $tipo_mensaje = "error";
                $mensaje = $response['mensaje'];
            }
        }

        header("Location: index.php?seccion=nota_credito&accion=vista_preliminar_nota_credito&mensaje=$mensaje&tipo_mensaje=$tipo_mensaje&nota_credito_id=$this->nota_credito_id&session_id=".SESSION_ID);
        exit;
    }

    public function guarda_session_nota_credito(){
        if(isset($_SESSION['nota_credito'])){
            unset($_SESSION['nota_credito']);
        }

        $_SESSION['nota_credito'] = serialize($_POST);
        header('Location: index.php?seccion=nota_credito&accion=selecciona_facturas');
        exit;
    }

    public function lista(){
        $nota_credito_modelo = new nota_credito($this->link);

        $r_nota_credito = $nota_credito_modelo->obten_registros('nota_credito');

        $this->notas_credito = $r_nota_credito['registros'];

    }

    public function modifica(){
        $this->nota_credito_id = $_GET['registro_id'];
        $this->genera_datos_nota_credito();
    }

    public function selecciona_facturas(){
        $nota_credito = unserialize($_SESSION['nota_credito']);
        $cliente_id = $nota_credito['cliente_id'];
        $monto = $nota_credito['monto'];

        $factura_modelo = new factura($this->link);
        $filtro = array('cliente.id'=>$cliente_id,'factura.status_factura'=>'timbrada');
        $r_factura = $factura_modelo->filtro_and('factura',$filtro," AND (factura.saldo != '0' )");

        $this->facturas = $r_factura['registros'];

    }

    public function vista_preliminar_nota_credito(){
        $this->nota_credito_id = $_GET['nota_credito_id'];
        $this->genera_datos_nota_credito();
    }



    public function timbra_cfdi($xml, $folio){
        $numero_empresa = $_SESSION['numero_empresa'];
        $empresa = new empresas();
        $repositorio = New repositorio();
        $datos_empresa = $empresa->empresas[$numero_empresa];

        $ws = $datos_empresa['ruta_pac'];

        $response = '';

        $base64Comprobante = base64_encode($xml);
        try {
            $params = array();
            $params['usuarioIntegrador'] = $datos_empresa['usuario_integrador'];
            $params['xmlComprobanteBase64'] = $base64Comprobante;
            $params['idComprobante'] = $folio;
            $client = new SoapClient($ws,$params);
            $response = $client->__soapCall('TimbraCFDI', array('parameters' => $params));
        }
        catch (SoapFault $fault) {
            echo "SOAPFault: ".$fault->faultcode."-".$fault->faultstring."\n";
            return false;
        }

        $error = $response->TimbraCFDIResult->anyType[1];

        if($error!='0'){
            print_r($response);
            echo $xml;
            echo $error;
            exit;
        }

        $r_xml_timbrado = $response->TimbraCFDIResult->anyType[3];
        $qr = $response->TimbraCFDIResult->anyType[4];

        $xml_timbrado = new xml_cfdi($r_xml_timbrado,$this->link,'I');

        $nota_credito['uuid'] = $xml_timbrado->get_folio_fiscal();
        $nota_credito['status'] = '1';
        $nota_credito['status_nota_credito'] = 'timbrado';
        $nota_credito['sello_cfd'] = $xml_timbrado->get_sello_cfdi();
        $nota_credito['sello_sat'] = $xml_timbrado->get_sello_sat();
        $nota_credito['serie_csd'] = $xml_timbrado->get_no_serie_csd();
        $nota_credito['serie_sat'] = $xml_timbrado->get_no_serie_sat();
        $nota_credito['fecha_hora_certificacion'] = $xml_timbrado->get_fecha_timbrado();
        $nota_credito['metodo_pago_codigo'] = $xml_timbrado->get_codigo_metodo_pago();
        $nota_credito['ruta'] = $repositorio->directorio_xml_timbrado_completo;

        $nota_credito['cadena_original'] = $response->TimbraCFDIResult->anyType[5];
        $nota_credito['xml'] = base64_encode($response->TimbraCFDIResult->anyType[3]);
        $nota_credito['qr'] = base64_encode($response->TimbraCFDIResult->anyType[4]);


        $nota_credito_modelo = new nota_credito($this->link);

        $nota_credito_modelo->modifica_bd($nota_credito,'nota_credito',$this->nota_credito_id);

        $repositorio->guarda_archivo($qr,'NC_'.$folio, $repositorio->directorio_xml_timbrado_completo, '.jpg');

        return $response;
    }

    private function genera_tag($pdf,$x,$y, $tamano_texto_titulos,$w_etiqueta,$altura_celdas, $w_texto,$etiqueta, $txt){

        $borde = 0;
        if($txt){
            $borde = 1;
        }
        else{
            $txt = '';

        }
        $pdf->SetXY($x,$y);
        $pdf->SetFont('Arial','B',$tamano_texto_titulos);
        $pdf->MultiCell($w_etiqueta, $altura_celdas, utf8_decode($etiqueta),1);


        $x = $w_etiqueta+$x;

        $pdf->SetXY($x,$y);
        $pdf->SetFont('Arial','',$tamano_texto_titulos);
        $pdf->MultiCell($w_texto, $altura_celdas, utf8_decode($txt),$borde,'C');

        $x = $x  + $w_texto;

        return $x;
    }


    public function ve_pdf(){
        $tamano_texto_titulos = 7;
        $tamano_texto_info = 7;
        $salto_de_linea = 4;


        $this->nota_credito_id = $_GET['nota_credito_id'];
        $this->genera_datos_nota_credito();


        $encabezado_1 = $this->nota_credito['encabezado_1'];
        $encabezado_2 = $this->nota_credito['encabezado_2'];
        $encabezado_3 = $this->nota_credito['encabezado_3'];
        $encabezado_4 = $this->nota_credito['encabezado_4'];
        $leyenda_docto = $this->nota_credito['leyenda_docto'];
        $nombre_empresa = $this->nota_credito['razon_social'];



        $RFC_emisor = utf8_decode($this->nota_credito['rfc']);
        $folio_fiscal = utf8_decode($this->nota_credito['nota_credito_uuid']);
        $nombre_emisor = utf8_decode($this->nota_credito['razon_social']);
        $No_serie_CSD = utf8_decode($this->nota_credito['nota_credito_serie_csd']);
        $folio = utf8_decode($this->nota_credito['nota_credito_folio']);
        $serie = utf8_decode($this->nota_credito['serie']);
        $RFC_receptor = utf8_decode(trim($this->nota_credito['cliente_rfc']));
        $codigo_postal = utf8_decode($this->nota_credito['cp']." ".$this->nota_credito['nota_credito_fecha']); //incluye fecha y hora
        $nombre_receptor = utf8_decode($this->nota_credito['cliente_razon_social']);
        $efecto_de_comprobante = utf8_decode("");
        $uso_CFDI = utf8_decode($this->nota_credito['uso_cfdi_descripcion']);
        $regimen_fiscal = utf8_decode($this->nota_credito['regimen_fiscal_descripcion']);


        $forma_de_pago = utf8_decode($this->nota_credito['forma_pago_descripcion']);

        $monto = number_format($this->nota_credito['nota_credito_monto'], 2);

        $tipo_cambio = $this->nota_credito['nota_credito_tipo_cambio'];


        //Comienza el PDF
        $pdf = new my_pdf();
        $pdf->AliasNbPages();
        $pdf->AddPage();

        //Encabezado
        $pdf->SetFont('Arial','',$tamano_texto_titulos);
        $pdf->Cell(190,4,utf8_decode($nombre_empresa),1,1,'C');
        $pdf->Cell(190,4,utf8_decode($encabezado_1),1,1,'C');
        $pdf->Cell(190,4,utf8_decode($encabezado_2),1,1,'C');

        $y = $pdf->GetY();

        $pdf->SetXY(10,$y);

        $pdf->MultiCell(95,4,utf8_decode($encabezado_3),1,1);
        $pdf->SetXY(105,$y);
        $pdf->MultiCell(95,4,utf8_decode($encabezado_4),1,1);

        $w_etiqueta = 17;
        $w_texto = 30;
        $altura_celdas = 5;

        $y = $pdf->GetY();
        $x = $this->genera_tag($pdf,10,$y,$tamano_texto_titulos,$w_etiqueta,$altura_celdas,$w_texto,'RFC Emisor:',$RFC_emisor);
        $x = $this->genera_tag($pdf,$x,$y,$tamano_texto_titulos,25,$altura_celdas,118,'Nombre Emisor:',$nombre_emisor);



        $y = $pdf->GetY();
        $x = $this->genera_tag($pdf,10,$y,$tamano_texto_titulos,$w_etiqueta,$altura_celdas,55,'Folio Fiscal:',$folio_fiscal);
        $x = $this->genera_tag($pdf,$x,$y,$tamano_texto_titulos,$w_etiqueta,$altura_celdas,10,'Folio:',$folio);
        $x = $this->genera_tag($pdf,$x,$y,$tamano_texto_titulos,$w_etiqueta,$altura_celdas,10,'Serie:',$serie);
        $this->genera_tag($pdf,$x,$y,$tamano_texto_titulos,41,$altura_celdas,23,'Código postal y hora de emisión:',$codigo_postal);


        $y = $pdf->GetY();
        $x = $this->genera_tag($pdf,10,$y,$tamano_texto_titulos,20,$altura_celdas,25,'Uso CFDI:',$uso_CFDI);
        $x = $this->genera_tag($pdf,$x,$y,$tamano_texto_titulos,20,$altura_celdas,25,'RFC receptor:',$RFC_receptor);
        $x = $this->genera_tag($pdf,$x,$y,$tamano_texto_titulos,21,$altura_celdas,79,'Régimen fiscal:',$regimen_fiscal);


        $y = $pdf->GetY();

        $x = $this->genera_tag($pdf,10,$y,$tamano_texto_titulos,25,$altura_celdas,165,'Nombre Receptor:',$nombre_receptor);


        $y = $pdf->GetY();
        $x = $this->genera_tag($pdf,10,$y,$tamano_texto_titulos,26,$altura_celdas,28,'Tipo de Cambio:',number_format($tipo_cambio,2,'.',','));
        $x = $this->genera_tag($pdf,$x,$y,$tamano_texto_titulos,35,$altura_celdas,27,'Efecto de comprobante:','E Nota Credito');
        $x = $this->genera_tag($pdf,$x,$y,$tamano_texto_titulos,23,$altura_celdas,51,'Moneda:',$this->nota_credito['moneda_codigo'].' '.$this->nota_credito['moneda_descripcion']);


        $y = $pdf->GetY();

        $x = $this->genera_tag($pdf,10,$y,$tamano_texto_titulos,33,$altura_celdas,30,'Serie CSD:',$this->nota_credito['nota_credito_serie_csd']);
        $x = $this->genera_tag($pdf,$x,$y,$tamano_texto_titulos,33,$altura_celdas,30,'Serie SAT:',$this->nota_credito['nota_credito_serie_sat']);
        $x = $this->genera_tag($pdf,$x,$y,$tamano_texto_titulos,34,$altura_celdas,30,'Fecha y Hora Certificación:',$this->nota_credito['nota_credito_fecha_hora_certificacion']);







        $pdf->SetFont('Arial', 'B', 8);
        $pdf->MultiCell(50, 8, "Documentos Relacionados");

        $y = $pdf->GetY();


        $pdf->SetFillColor(212, 212, 212);
        $pdf->SetFont('Arial', 'B', 5);
        $pdf->MultiCell(50, 6, utf8_decode("Tipo Relación"), 1, 'C', true);
        $x = 50 + 10;
        $y = $pdf->GetY()-6;
        $pdf->SetXY($x, $y);
        $pdf->MultiCell(70, 6, "Folio Fiscal Relacionado", 1, 'C', true);



        $tipo_relacion = utf8_decode('01 Nota de crédito de los documentos relacionados');
        $folio_factura_relacionada = $this->nota_credito['factura_uuid'];

        $y = $pdf->GetY();
        $x = 50;

        $pdf->SetFont('Arial', '', 5);
        $pdf->MultiCell(50, 6, $tipo_relacion, 1, 'C');


        $x = $x+10;
        $pdf->SetXY($x, $y);


        $pdf->MultiCell(70, 6, $folio_factura_relacionada, 1, 'C');




        $pdf->SetFont('Arial', 'B', 8);
        $pdf->MultiCell(20, 8, "Conceptos");

        $y = $pdf->GetY();


        $pdf->SetFillColor(212, 212, 212);
        $pdf->SetFont('Arial', 'B', 5);
        $pdf->MultiCell(18, 3, "Clave producto / servicio", 1, 'C', true);
        $x = 18 + 10;
        $y = $pdf->GetY()-6;
        $pdf->SetXY($x, $y);
        $pdf->MultiCell(16, 6, "Cantidad", 1, 'C', true);

        $x = $x +16;
        $pdf->SetXY($x, $y);
        $pdf->MultiCell(14, 3, "Clave de unidad", 1, 'C', true);

        $x = $x +14;
        $pdf->SetXY($x, $y);
        $pdf->MultiCell(90, 6, "Concepto", 1, 'C', true);
        $x = $x +90;
        $pdf->SetXY($x, $y);
        $pdf->MultiCell(20, 6, "Precio Unitario", 1, 'C', true);
        $x = $x +20;
        $pdf->SetXY($x, $y);
        $pdf->MultiCell(15, 6, "Descuento", 1, 'C', true);
        $x = $x +15;
        $pdf->SetXY($x, $y);
        $pdf->MultiCell(17, 6, "Importe", 1, 'C', true);





        $clave_producto_servicio = $this->nota_credito['producto_codigo'];
        $precio_unitario = $this->nota_credito['nota_credito_monto'];
        $cantidad = 1;
        $clave_unidad = 'ACT';

       $concepto ='Nota de Crédito';

        $y = $pdf->GetY();
        $x = 10;

        $pdf->SetFont('Arial', '', 5);
        $pdf->MultiCell(18, 6, $clave_producto_servicio, 1, 'C');


        $x = $x +18;
        $pdf->SetXY($x, $y);


        $pdf->MultiCell(16, 6, $cantidad, 1, 'C');

        $x = $x +16;
        $pdf->SetXY($x, $y);

        $pdf->MultiCell(14, 6, $clave_unidad, 1, 'C');


        $x = $x +14;
        $pdf->SetXY($x, $y);

        $pdf->MultiCell(90, 6, $concepto, 1, 'C');

        $x = $x +90;
        $pdf->SetXY($x, $y);

        $pdf->MultiCell(20, 6, $precio_unitario, 1, 'C');

        $x = $x +20;
        $pdf->SetXY($x, $y);

        $pdf->MultiCell(15, 6, '0.00', 1, 'C');

        $x = $x +15;
        $pdf->SetXY($x, $y);

        $pdf->MultiCell(17, 6, $precio_unitario, 1, 'C');






        $y = $pdf->GetY();

        $x = $this->genera_tag($pdf,10,$y,6,17,$altura_celdas,17,'Impuesto:','IVA');
        $x = $this->genera_tag($pdf,$x,$y,6,17,$altura_celdas,15,'Tipo:','Traslado');
        $x = $this->genera_tag($pdf,$x,$y,6,17,$altura_celdas,15,'Tasa',$this->nota_credito['nota_credito_porcentaje_iva']);
        $x = $this->genera_tag($pdf,$x,$y,6,20,$altura_celdas,20,'Total Traslados',$this->nota_credito['nota_credito_monto_iva']);


        $numero_txt = new NumeroTexto();
        $total_letra = $numero_txt->to_word($this->nota_credito['nota_credito_total'],$this->nota_credito['moneda_codigo']);

        $y = $pdf->GetY()+3;


        $x = $this->genera_tag($pdf,10,$y,6,17,$altura_celdas,121,'Total Letra:',$total_letra);
        $x = $this->genera_tag($pdf,$x,$y,6,17,$altura_celdas,35,'Sub Total:',$this->nota_credito['nota_credito_monto']);

        $x = $x - 35-17;
        $y = $pdf->GetY();

        $x = $this->genera_tag($pdf,$x,$y,6,17,$altura_celdas,35,'IVA '. $this->nota_credito['nota_credito_porcentaje_iva'],$this->nota_credito['nota_credito_monto_iva']);
        $y = $pdf->GetY();
        $x = $x - 35-17;
        $x = $this->genera_tag($pdf,$x,$y,6,17,$altura_celdas,35,'Descuentos','0.00');
        $y = $pdf->GetY();
        $x = $x - 35-17;
        $x = $this->genera_tag($pdf,$x,$y,6,17,$altura_celdas,35,'Total',$this->nota_credito['nota_credito_total']);


        $y = $pdf->GetY()+1;
        $x = 10;


        $y = $pdf->GetY()+3;
        $pdf->Image($this->nota_credito['nota_credito_ruta'].'/NC_'.$this->nota_credito['nota_credito_folio'].'.jpg',131+17+10,$y,42);


        $y = $pdf->GetY()+3;
        $x = 10;

        $x = $this->genera_tag($pdf,$x,$y,6,17,2.8,130,'Sello CFDI',$this->nota_credito['nota_credito_sello_cfd']);

        $y = $pdf->GetY()+3;
        $x = 10;

        $x = $this->genera_tag($pdf,$x,$y,6,17,2.8,130,'Sello SAT',$this->nota_credito['nota_credito_sello_sat']);

        $y = $pdf->GetY()+3;
        $x = 10;

        $x = $this->genera_tag($pdf,$x,$y,6,17,2.8,130,'Cadena Original',$this->nota_credito['nota_credito_cadena_original']);




        $pdf->Output('','NC_'. $folio.'.pdf');
    }


}