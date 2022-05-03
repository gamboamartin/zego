<?php
namespace controllers;
use empresas;
use Fpdf\Fpdf;
use models\anticipo;
use models\factura;
use NumeroTexto;
use repositorio;
use SoapClient;
use SoapFault;
use xml_cfdi;

class controlador_anticipo extends controlador_base {
    public $anticipos = false;
    public $anticipo = false;
    public $anticipo_id = false;
    public $directorio_xml_sin_timbrar_completo;
    public $directorio_xml_timbrado_completo;
    public $factura = false;
    public function alta_bd(){
        $_POST['status'] = '1';
        parent::alta_bd();
    }

    public function aplica_anticipo_factura(){
        $factura_id = $_GET['factura_id'];
        $factura_modelo = new factura($this->link);
        $r_factura = $factura_modelo->obten_por_id('factura',$factura_id);

        $this->factura = $r_factura['registros'][0];

        $anticipo_modelo = new anticipo($this->link);
        $filtro = array('cliente.id'=>$this->factura['cliente_id'],
            'anticipo.aplicado_factura'=>'no',
            'anticipo.status_anticipo'=>'timbrado');


        $r_anticipo = $anticipo_modelo->filtro_and('anticipo', $filtro);


        $this->anticipos = $r_anticipo['registros'];



    }

    public function asigna_anticipo(){
        $factura_id = $_POST['factura_id'];
        $factura['anticipo_id'] = $_POST['anticipo_id'];
        $factura['status'] = 'activo';

        $factura_modelo = new factura($this->link);
        $factura_modelo->modifica_bd($factura,'factura',$factura_id);

        $anticipo['aplicado_factura'] = 'si';
        $anticipo['status'] = 'activo';

        $anticipo_modelo = new anticipo($this->link);
        $anticipo_modelo->modifica_bd($anticipo,'anticipo',$_POST['anticipo_id']);
        header('Location: index.php?seccion=cliente&accion=vista_preliminar_factura&factura_id='.$factura_id);
        exit;

    }

    public function elimina_bd(){
        $anticipo_id = $_GET['registro_id'];
        $anticipo_modelo = new anticipo($this->link);

        $r_anticipo = $anticipo_modelo->obten_por_id('anticipo', $anticipo_id);

        $anticipo = $r_anticipo['registros'][0];

        if($anticipo['anticipo_status_anticipo'] == 'timbrada'){
            header('Location: index.php?seccion=anticipo&accion=lista&mensaje=El anticipo no se puede eliminar por que esta timbrado&tipo_mensaje=error&session_id='.SESSION_ID);
            exit;
        }

        parent::elimina_bd();

        header('Location: index.php?seccion=anticipo&accion=lista&mensaje=Registro eliminado con exito&tipo_mensaje=exito&session_id='.SESSION_ID);
        exit;

    }

    private function genera_datos_anticipo(){

        $anticipo_modelo = new anticipo($this->link);
        $r_anticipo = $anticipo_modelo->obten_por_id('anticipo',$this->anticipo_id);
        $this->anticipo = $r_anticipo['registros'][0];

        $empresa = new empresas();
        $datos_empresa = $empresa->empresas[$_SESSION['numero_empresa']];
        $this->anticipo['uso_cfdi_codigo'] = 'P01';
        $this->anticipo['uso_cfdi_descripcion'] = 'Por Definir';
        $this->anticipo['producto_codigo'] = '84111506';
        $this->anticipo['producto_cantidad'] = '1';
        $this->anticipo['unidad_codigo'] = 'ACT';
        $this->anticipo['insumo_descripcion'] = 'Anticipo del Bien o Servicio';
        $this->anticipo['impuesto_descripcion'] = 'IVA';
        $this->anticipo['tipo_impuesto_descripcion'] = 'Traslado';
        $this->anticipo['tipo_factor_descripcion'] = 'Tasa';
        $this->anticipo['tipo_comprobante_codigo'] = 'I';
        $this->anticipo['impuesto_codigo'] = '002';
        $this->anticipo = array_merge($datos_empresa, $this->anticipo);
    }

    public function genera_xml(){

        $this->anticipo_id = $_GET['anticipo_id'];
        $this->genera_datos_anticipo();

        $repositorio = New repositorio();

        $empresa = new empresas();
        $datos_empresa = $empresa->empresas[$_SESSION['numero_empresa']];
        $ruta_base = $datos_empresa['nombre_base_datos'];




        $LugarExpedicion = $this->anticipo['cp'];
        $TipoComprobante = $this->anticipo['tipo_comprobante_codigo'];

        $Fecha = $this->anticipo['anticipo_fecha'].'T12:00:00';
        $Folio = $this->anticipo['anticipo_folio'];
        //$Folio = rand(1,10000000);
        $Serie = $this->anticipo['serie'];
        $RfcEmisor = $this->anticipo['rfc'];
        $Moneda = $this->anticipo['moneda_codigo'];
        $SubTotal = $this->anticipo['anticipo_monto'];
        $Total = $this->anticipo['anticipo_total'];
        $UsoCFDI = $this->anticipo['uso_cfdi_codigo'];
        $NombreEmisor = $this->anticipo['razon_social'];
        $RegimenFiscal = $this->anticipo['regimen_fiscal'];
        $RfcReceptor = $this->anticipo['cliente_rfc'];
        $NombreReceptor = $this->anticipo['cliente_razon_social'];
        $ClaveProdServ = $this->anticipo['producto_codigo'];
        $Cantidad = $this->anticipo['producto_cantidad'];
        $ClaveUnidad = $this->anticipo['unidad_codigo'];
        $Descripcion = $this->anticipo['insumo_descripcion'];
        $ValorUnitario = $this->anticipo['anticipo_monto'];
        $FormaPago = $this->anticipo['forma_pago_codigo'];
        $Importe = $this->anticipo['anticipo_monto'];
        $Base = $this->anticipo['anticipo_monto'];
        $ImporteIVA = $this->anticipo['anticipo_monto_iva'];
        $Impuesto = $this->anticipo['impuesto_codigo'];
        $Tasa = number_format(round($this->anticipo['anticipo_porcentaje_iva'],6),6,'.','');
        $TipoFactor = $this->anticipo['tipo_factor_descripcion'];
        $MetodoPago = $this->anticipo['metodo_pago_codigo'];
        $TipoCambio = number_format(round($this->anticipo['anticipo_tipo_cambio'],6),6,'.','');
        if($Moneda == 'MXN'){
            $TipoCambio = 1;
        }




        $plantilla = './plantillas_cfdi/anticipo.xml';
        $xml  = file_get_contents($plantilla);


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
        $xml = str_replace('|ClaveProdServ|',$ClaveProdServ,$xml);
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




        $response = $this->timbra_cfdi($xml,$Folio);
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

        $anticipo['uuid'] = $xml_timbrado->get_folio_fiscal();
        $anticipo['status'] = '1';
        $anticipo['status_anticipo'] = 'timbrado';
        $anticipo['sello_cfd'] = $xml_timbrado->get_sello_cfdi();
        $anticipo['sello_sat'] = $xml_timbrado->get_sello_sat();
        $anticipo['serie_csd'] = $xml_timbrado->get_no_serie_csd();
        $anticipo['serie_sat'] = $xml_timbrado->get_no_serie_sat();
        $anticipo['fecha_hora_certificacion'] = $xml_timbrado->get_fecha_timbrado();
        $anticipo['ruta'] = $repositorio->directorio_xml_timbrado_completo;

        $anticipo['cadena_original'] = $response->TimbraCFDIResult->anyType[5];
        $anticipo['xml'] = base64_encode($response->TimbraCFDIResult->anyType[3]);
        $anticipo['qr'] = base64_encode($response->TimbraCFDIResult->anyType[4]);


        $anticipo_modelo = new anticipo($this->link);

        $anticipo_modelo->modifica_bd($anticipo,'anticipo',$this->anticipo_id);


        $repositorio->guarda_archivo($r_xml_timbrado,'A_'.$Folio, $repositorio->directorio_xml_timbrado_completo, '.xml');
        $repositorio->guarda_archivo($qr,'A_'.$Folio, $repositorio->directorio_xml_timbrado_completo, '.jpg');



        header("Location: index.php?seccion=anticipo&accion=vista_preliminar_anticipo&mensaje=a&tipo_mensaje=exito&anticipo_id=$this->anticipo_id&session_id=".SESSION_ID);
        exit;
    }

    public function lista(){
        $anticipo_modelo = new anticipo($this->link);

        $r_anticipo = $anticipo_modelo->obten_registros('anticipo');

        $this->anticipos = $r_anticipo['registros'];

    }

    public function vista_preliminar_anticipo(){
        $this->anticipo_id = $_GET['anticipo_id'];
        $this->genera_datos_anticipo();


    }

    public function timbra_cfdi($xml, $folio){
        $numero_empresa = $_SESSION['numero_empresa'];
        $empresa = new Empresas();
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


        return $response;
    }

    private function genera_tag($pdf,$x,$y, $tamano_texto_titulos,$w_etiqueta,$altura_celdas, $w_texto,$etiqueta, $txt){

        $pdf->SetXY($x,$y);
        $pdf->SetFont('Arial','B',$tamano_texto_titulos);
        $pdf->MultiCell($w_etiqueta, $altura_celdas, utf8_decode($etiqueta),1);


        $x = $w_etiqueta+$x;

        $pdf->SetXY($x,$y);
        $pdf->SetFont('Arial','',$tamano_texto_titulos);
        $pdf->MultiCell($w_texto, $altura_celdas, utf8_decode($txt),1,'C');

        $x = $x  + $w_texto;

        return $x;
    }

    public function descarga_xml(){

        $anticipo_id = $_GET['anticipo_id'];

        $modelo_anticipo = new anticipo($this->link);
        $resultado = $modelo_anticipo->obten_por_id('anticipo',$anticipo_id);

        $anticipo = $resultado['registros'][0];

        $folio = $anticipo['anticipo_folio'];

        $name_file = 'A_'.$anticipo['anticipo_folio'];


        $numero_empresa = $_SESSION['numero_empresa'];
        $empresas = new Empresas();
        $datos_empresa = $empresas->empresas[$numero_empresa];

        $ruta_base = $datos_empresa['nombre_base_datos'];

        $ruta_xml = $ruta_base.'/xml_timbrado/A_'.$folio.'.xml';

        header("Content-disposition: attachment; filename=$name_file.xml");
        header('Content-type: "text/xml"; charset="utf8"');
        readfile($ruta_xml);
        exit;

    }



    public function ve_pdf(){
        $tamano_texto_titulos = 7;
        $tamano_texto_info = 7;
        $salto_de_linea = 4;


        $this->anticipo_id = $_GET['anticipo_id'];
        $this->genera_datos_anticipo();



        $encabezado_1 = $this->anticipo['encabezado_1'];
        $encabezado_2 = $this->anticipo['encabezado_2'];
        $encabezado_3 = $this->anticipo['encabezado_3'];
        $encabezado_4 = $this->anticipo['encabezado_4'];
        $leyenda_docto = $this->anticipo['leyenda_docto'];
        $nombre_empresa = $this->anticipo['razon_social'];



        $RFC_emisor = utf8_decode($this->anticipo['rfc']);
        $folio_fiscal = utf8_decode($this->anticipo['anticipo_uuid']);
        $nombre_emisor = utf8_decode($this->anticipo['razon_social']);
        $No_serie_CSD = utf8_decode($this->anticipo['anticipo_serie_csd']);
        $folio = utf8_decode($this->anticipo['anticipo_folio']);
        $serie = utf8_decode($this->anticipo['serie']);
        $RFC_receptor = utf8_decode($this->anticipo['cliente_rfc']);
        $codigo_postal = utf8_decode($this->anticipo['cp']." ".$this->anticipo['anticipo_fecha']); //incluye fecha y hora
        $nombre_receptor = utf8_decode($this->anticipo['cliente_razon_social']);
        $efecto_de_comprobante = utf8_decode("");
        $uso_CFDI = utf8_decode($this->anticipo['uso_cfdi_descripcion']);
        $regimen_fiscal = utf8_decode($this->anticipo['regimen_fiscal_descripcion']);


        $forma_de_pago = utf8_decode($this->anticipo['forma_pago_descripcion']);

        $monto = number_format($this->anticipo['anticipo_monto'], 2);

        $tipo_cambio = $this->anticipo['anticipo_tipo_cambio'];


        //Comienza el PDF
        $pdf = new FPDF();
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
        $x = $this->genera_tag($pdf,10,$y,$tamano_texto_titulos,$w_etiqueta,$altura_celdas,64,'Folio Fiscal:',$folio_fiscal);
        $x = $this->genera_tag($pdf,$x,$y,$tamano_texto_titulos,$w_etiqueta,$altura_celdas,65,'Folio:',$folio);
        $x = $this->genera_tag($pdf,$x,$y,$tamano_texto_titulos,$w_etiqueta,$altura_celdas,10,'Serie:',$serie);
        $y = $pdf->GetY();
        $x = 10;
        $this->genera_tag($pdf,$x,$y,$tamano_texto_titulos,41,$altura_celdas,23,'Código postal y hora de emisión:',$codigo_postal);


        $y = $pdf->GetY();
        $x = $this->genera_tag($pdf,10,$y,$tamano_texto_titulos,20,$altura_celdas,25,'Uso CFDI:',$uso_CFDI);
        $x = $this->genera_tag($pdf,$x,$y,$tamano_texto_titulos,20,$altura_celdas,21,'RFC receptor:',$RFC_receptor);
        $x = $this->genera_tag($pdf,$x,$y,$tamano_texto_titulos,21,$altura_celdas,83,'Régimen fiscal:',$regimen_fiscal);


        $y = $pdf->GetY();

        $x = $this->genera_tag($pdf,10,$y,$tamano_texto_titulos,190,$altura_celdas,false,'Nombre Receptor:',false);
        $y = $pdf->GetY();
        $x = $this->genera_tag($pdf,10,$y,$tamano_texto_titulos,false,$altura_celdas,190,False,$nombre_receptor);
        $x_texto_derecho = $w_etiqueta+10;

        $y = $pdf->GetY();
        $x = $this->genera_tag($pdf,10,$y,$tamano_texto_titulos,26,$altura_celdas,28,'Tipo de Cambio:',number_format($tipo_cambio,2,'.',','));
        $x = $this->genera_tag($pdf,$x,$y,$tamano_texto_titulos,35,$altura_celdas,27,'Efecto de comprobante:','I Anticipo');
        $x = $this->genera_tag($pdf,$x,$y,$tamano_texto_titulos,23,$altura_celdas,51,'Moneda:',$this->anticipo['moneda_codigo'].' '.$this->anticipo['moneda_descripcion']);


        $y = $pdf->GetY();

        $x = $this->genera_tag($pdf,10,$y,$tamano_texto_titulos,33,$altura_celdas,30,'Serie CSD:',$this->anticipo['anticipo_serie_csd']);
        $x = $this->genera_tag($pdf,$x,$y,$tamano_texto_titulos,33,$altura_celdas,30,'Serie SAT:',$this->anticipo['anticipo_serie_sat']);
        $x = $this->genera_tag($pdf,$x,$y,$tamano_texto_titulos,34,$altura_celdas,30,'Fecha y Hora Certificación:',$this->anticipo['anticipo_fecha_hora_certificacion']);




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





        $clave_producto_servicio = $this->anticipo['producto_codigo'];
        $precio_unitario = $this->anticipo['anticipo_monto'];
        $cantidad = 1;
        $clave_unidad = 'ACT';

       $concepto ='Anticipo del Bien o Servicio';

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
        $x = $this->genera_tag($pdf,$x,$y,6,17,$altura_celdas,15,'Tasa',$this->anticipo['anticipo_porcentaje_iva']);
        $x = $this->genera_tag($pdf,$x,$y,6,20,$altura_celdas,20,'Total Traslados',$this->anticipo['anticipo_monto_iva']);


        $numero_txt = new NumeroTexto();
        $total_letra = $numero_txt->to_word($this->anticipo['anticipo_total'],$this->anticipo['moneda_codigo']);

        $y = $pdf->GetY()+3;


        $x = $this->genera_tag($pdf,10,$y,6,17,$altura_celdas,121,'Total Letra:',$total_letra);
        $x = $this->genera_tag($pdf,$x,$y,6,17,$altura_celdas,35,'Sub Total:',$this->anticipo['anticipo_monto']);

        $x = $x - 35-17;
        $y = $pdf->GetY();

        $x = $this->genera_tag($pdf,$x,$y,6,17,$altura_celdas,35,'IVA '. $this->anticipo['anticipo_porcentaje_iva'],$this->anticipo['anticipo_monto_iva']);
        $y = $pdf->GetY();
        $x = $x - 35-17;
        $x = $this->genera_tag($pdf,$x,$y,6,17,$altura_celdas,35,'Descuentos','0.00');
        $y = $pdf->GetY();
        $x = $x - 35-17;
        $x = $this->genera_tag($pdf,$x,$y,6,17,$altura_celdas,35,'Total',$this->anticipo['anticipo_total']);


        $y = $pdf->GetY()+1;
        $x = 10;

        $x = $this->genera_tag($pdf,$x,$y,6,20,$altura_celdas,75,'Metodo de Pago',$this->anticipo['metodo_pago_codigo'].' '.$this->anticipo['metodo_pago_descripcion']);
        $x = $this->genera_tag($pdf,$x,$y,6,20,$altura_celdas,75,'Forma de Pago',$this->anticipo['forma_pago_codigo'].' '.$this->anticipo['forma_pago_descripcion']);

        $y = $pdf->GetY()+3;
        $pdf->Image($this->anticipo['anticipo_ruta'].'/A_'.$this->anticipo['anticipo_folio'].'.jpg',131+17+10,$y,42);


        $y = $pdf->GetY()+3;
        $x = 10;

        $x = $this->genera_tag($pdf,$x,$y,6,17,2.8,130,'Sello CFDI',$this->anticipo['anticipo_sello_cfd']);

        $y = $pdf->GetY()+3;
        $x = 10;

        $x = $this->genera_tag($pdf,$x,$y,6,17,2.8,130,'Sello SAT',$this->anticipo['anticipo_sello_sat']);

        $y = $pdf->GetY()+3;
        $x = 10;

        $x = $this->genera_tag($pdf,$x,$y,6,17,2.8,130,'Cadena Original',$this->anticipo['anticipo_cadena_original']);




        $pdf->Output('','A_'. $folio.'.pdf');
    }


}