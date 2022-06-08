<?php

use config\empresas;
use gamboamartin\errores\errores;
use models\factura;
use models\forma_pago;
use models\impuesto;
use models\metodo_pago;
use models\moneda;
use models\nota_credito;
use models\pago_cliente;
use models\producto_sat;
use models\regimen_fiscal;
use models\tipo_comprobante;
use models\unidad;
use models\uso_cfdi;

class facturas{
    public $partidas_html;
    public $link;
    public $datos_metodo_pago;
    public $datos_tipo_comprobante;
    public $datos_moneda;
    public $datos_forma_pago;
    public $datos_uso_cfdi;
    public $datos_cliente;
    public $factura_id;
    public $directorio_xml_sin_timbrar_completo;
    public $directorio_xml_timbrado_completo;
    private errores $error;

    public function __construct($link){
        $this->error = new errores;
        $this->link = $link;
        $empresas = new empresas();
        $datos_empresa = $empresas->empresas[$_SESSION['numero_empresa']];

        $directorio_base = $datos_empresa['nombre_base_datos'];
        $this->directorio_xml_sin_timbrar_completo = $directorio_base.'/xml_sin_timbrar';
        $this->directorio_xml_timbrado_completo = $directorio_base.'/xml_timbrado';


    }
    public function asigna_datos_empresa($factura){
        $modelo_empresa = new Empresas();
        $numero_empresa = $_SESSION['numero_empresa'];
        $datos_empresa = $modelo_empresa->empresas[$numero_empresa];
        $factura->datos_guardar['lugar_expedicion'] = $datos_empresa['cp'];
        $factura->datos_guardar['calle_expedicion'] = $datos_empresa['calle'];
        $factura->datos_guardar['exterior_expedicion'] = $datos_empresa['exterior'];
        $factura->datos_guardar['interior_expedicion'] = $datos_empresa['interior'];
        $factura->datos_guardar['colonia_expedicion'] = $datos_empresa['colonia'];
        $factura->datos_guardar['municipio_expedicion'] = $datos_empresa['municipio'];
        $factura->datos_guardar['estado_expedicion'] = $datos_empresa['estado'];
        $factura->datos_guardar['pais_expedicion'] = $datos_empresa['pais'];
        $factura->datos_guardar['serie'] = $datos_empresa['serie'];
        $factura->datos_guardar['rfc_emisor'] = $datos_empresa['rfc'];
        $factura->datos_guardar['nombre_emisor'] = $datos_empresa['razon_social'];
        $factura->datos_guardar['regimen_fiscal_emisor_codigo'] = $datos_empresa['regimen_fiscal'];
        $factura->datos_guardar['regimen_fiscal_emisor_descripcion'] = $datos_empresa['regimen_fiscal_descripcion'];
        $factura->sufijo = $datos_empresa['sufijo_folio'];
        $factura->folio_inicial = $datos_empresa['folio_inicial'];
    }
    public function asigna_datos_ligados($factura){
        $factura->datos_guardar['metodo_pago_codigo'] = $this->datos_metodo_pago['metodo_pago_codigo'];
        $factura->datos_guardar['metodo_pago_descripcion'] = $this->datos_metodo_pago['metodo_pago_descripcion'];
        $factura->datos_guardar['tipo_comprobante_codigo'] = $this->datos_tipo_comprobante['tipo_comprobante_codigo'];
        $factura->datos_guardar['tipo_comprobante_descripcion'] = $this->datos_tipo_comprobante['tipo_comprobante_descripcion'];
        $factura->datos_guardar['moneda_codigo'] = $this->datos_moneda['moneda_codigo'];
        $factura->datos_guardar['moneda_descripcion'] = $this->datos_moneda['moneda_descripcion'];
        $factura->datos_guardar['forma_pago_codigo'] = $this->datos_forma_pago['forma_pago_codigo'];
        $factura->datos_guardar['forma_pago_descripcion'] = $this->datos_forma_pago['forma_pago_descripcion'];
        $factura->datos_guardar['cliente_rfc'] = $this->datos_cliente['cliente_rfc'];
        $factura->datos_guardar['cliente_razon_social'] = $this->datos_cliente['cliente_razon_social'];
        $factura->datos_guardar['uso_cfdi_codigo'] = $this->datos_uso_cfdi['uso_cfdi_codigo'];
        $factura->datos_guardar['uso_cfdi_descripcion'] = $this->datos_uso_cfdi['uso_cfdi_descripcion'];
    }
    public function genera_folio($sufijo, $tabla,$folio_inicial){
        $modelo = new factura($this->link);
        $ultimo_id = $modelo->obten_ultimo_id($tabla);
        $ultimo_folio = $ultimo_id + $folio_inicial;
        $ultimo_folio++;
        $folio = $sufijo.'_'.$ultimo_folio;
        return $folio;

    }
    public function genera_partida($i, $insumo_id = '', $cantidad = '', $unidad='', $valor='', $impuesto_id='', 
        $tipo_factor_id='',$factor='', $importe_impuesto = '', $descripcion = ''){
        $disabled = false;
        if($i > 1){
            $disabled = 'disabled';
        }
        $directiva = new Directivas();
        $this->partidas_html = '';
        $this->partidas_html = $this->partidas_html.'<div class="partida" id="partida'.$i.'">';
        $this->partidas_html = $this->partidas_html.'<div class="row">';
        $this->partidas_html = $this->partidas_html.$directiva->input_select(
                'insumo',$insumo_id,3,$disabled,'[]', $i,$this->link);
        $this->partidas_html = $this->partidas_html.$directiva->genera_input_valor_moneda(
                'cantidad',2,$cantidad,'required',false,'[]',
                $i,$disabled);
        $this->partidas_html = $this->partidas_html.$directiva->input_select(
                'unidad',$unidad,2,$disabled,'[]',$i,$this->link);
        $this->partidas_html = $this->partidas_html.$directiva->genera_input_valor_moneda(
                'valor_unitario',2,$valor,'required',false,
                '[]',$i,$disabled);
        $this->partidas_html = $this->partidas_html.$directiva->genera_input_text(
                'importe',3,$valor,'required',false,
                '[]',$i,'disabled');
        $this->partidas_html = $this->partidas_html.'</div>';
        $this->partidas_html = $this->partidas_html.'<div class="row">';
        $this->partidas_html = $this->partidas_html.'<div class="well">';
        $this->partidas_html = $this->partidas_html.'<label>Impuestos Trasladados:</label>';
        $this->partidas_html = $this->partidas_html.'</div>';
        $this->partidas_html = $this->partidas_html.$directiva->input_select(
                'impuesto',$impuesto_id,5,$disabled,'[]',$i,$this->link);
        $this->partidas_html = $this->partidas_html.$directiva->input_select(
                'tipo_factor',$tipo_factor_id,5,$disabled,'[]',$i,$this->link);
        $this->partidas_html = $this->partidas_html.$directiva->genera_input_text(
                'factor',4,$factor,'required',false,'[]',
                $i,$disabled);
        $this->partidas_html = $this->partidas_html.$directiva->genera_input_text(
                'importe_impuesto',4,$importe_impuesto,'required',false,
                '[]',$i,'disabled');
        $this->partidas_html = $this->partidas_html.'</div>';
        $this->partidas_html = $this->partidas_html.'<div class="row">';
        $this->partidas_html = $this->partidas_html.'<div class="well">';
        $this->partidas_html = $this->partidas_html.'<label>Impuestos Retenidos:</label>';
        $this->partidas_html = $this->partidas_html.'</div>';
        $this->partidas_html = $this->partidas_html.$directiva->input_select_personalizado(
                'impuesto',false,5,$disabled,'[]',$i,
                $this->link,'Impuesto Retenido','impuesto_retenido',
                false,'impuesto_retenido_id');
        $this->partidas_html = $this->partidas_html.$directiva->input_select_personalizado(
                'tipo_factor',false,5,$disabled,'[]',$i,$this->link,
                'Tipo Factor Requerido', 'tipo_factor_retenido',
                false,'tipo_factor_retenido_id');
        $this->partidas_html = $this->partidas_html.$directiva->genera_input_text(
                'factor_retenido',4,false,'',false,
                '[]',$i,$disabled);
        $this->partidas_html = $this->partidas_html.$directiva->genera_input_text(
                'importe_impuesto_retenido',4,false,'',
                false,'[]',$i,'disabled');
        $this->partidas_html = $this->partidas_html.$directiva->genera_input_text(
                'descripcion',12,$descripcion,'required',false,
                '[]',$i,$disabled);
        $this->partidas_html = $this->partidas_html."<div class='btn btn-primary col-md-3' id='otra-partida$i'>Otra Partida</div>";
        $this->partidas_html = $this->partidas_html.'</div>';
        $this->partidas_html = $this->partidas_html.'</div>';
    }
    public function obten_datos($tabla){
        $modelo = new $tabla($this->link);
        $campo_id = $tabla.'_id';
        $registro_id = $_POST[$campo_id];
        $resultado = $modelo->obten_por_id($tabla,$registro_id);
        $datos = $resultado['registros'][0];
        return $datos;
    }
    public function obten_datos_comprobante($xml){
        $tipo_comprobante_modelo = new tipo_comprobante($this->link);
        $moneda_modelo = new moneda($this->link);
        $forma_pago_modelo = new forma_pago($this->link);
        $metodo_pago_modelo = new metodo_pago($this->link);

        $datos_comprobante = array();
        $namespaces = $xml->getDocNamespaces();
        foreach ($xml->xpath('//cfdi:Comprobante') as $comprobante){
            $datos_comprobante['LugarExpedicion'] = $comprobante['LugarExpedicion'];
            $datos_comprobante['MetodoPago'] = $comprobante['MetodoPago'];
            $datos_comprobante['TipoDeComprobante'] = $comprobante['TipoDeComprobante'];
            $datos_comprobante['Total'] = $comprobante['Total'];
            $datos_comprobante['Moneda'] = $comprobante['Moneda'];
            $datos_comprobante['SubTotal'] = $comprobante['SubTotal'];
            $datos_comprobante['FormaPago'] = $comprobante['FormaPago'];
            $datos_comprobante['Fecha'] = $comprobante['Fecha'];
            $datos_comprobante['Folio'] = $comprobante['Folio'];
            $datos_comprobante['Serie'] = $comprobante['Serie'];
            $datos_comprobante['Version'] = $comprobante['Version'];

            $filtro_tipo_comprobante = array('codigo'=>$datos_comprobante['TipoDeComprobante']);
            $resultado = $tipo_comprobante_modelo->filtro_and('tipo_comprobante',$filtro_tipo_comprobante);
            if(errores::$error){
                return $this->error->error('Error al obtener tipo de comprobante', $resultado);
            }
            $registro = $resultado['registros'][0];
            $datos_comprobante['TipoDeComprobanteDescripcion'] = $registro['tipo_comprobante_descripcion'];

            $filtro_moneda = array('codigo'=>$datos_comprobante['Moneda']);
            $resultado = $moneda_modelo->filtro_and('moneda',$filtro_moneda);
            $registro = $resultado['registros'][0];
            $datos_comprobante['MonedaDescripcion'] = $registro['moneda_descripcion'];

            $filtro_forma_pago = array('codigo'=>$datos_comprobante['FormaPago']);
            $resultado = $forma_pago_modelo->filtro_and('forma_pago',$filtro_forma_pago);
            $registro = $resultado['registros'][0];
            $datos_comprobante['FormaPagoDescripcion'] = $registro['forma_pago_descripcion'];

            $filtro_metodo_pago = array('codigo'=>$datos_comprobante['MetodoPago']);
            $resultado = $metodo_pago_modelo->filtro_and('metodo_pago',$filtro_metodo_pago);
            $registro = $resultado['registros'][0];
            $datos_comprobante['MetodoPagoDescripcion'] = $registro['metodo_pago_descripcion'];

        }
        return $datos_comprobante;
    }
    public function obten_datos_emisor($xml){
        $regimen_fiscal_modelo = new regimen_fiscal($this->link);
        $datos_emisor = array();
        $namespaces = $xml->getDocNamespaces();
        foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Emisor') as $emisor){  // SECCION EMISOR
            $datos_emisor['Rfc'] = $emisor['Rfc'];
            $datos_emisor['Nombre'] = $emisor['Nombre'];
            $datos_emisor['RegimenFiscal'] = $emisor['RegimenFiscal'];

            $filtro_regimen_fiscal = array('codigo'=>$datos_emisor['RegimenFiscal']);
            $resultado = $regimen_fiscal_modelo->filtro_and('regimen_fiscal',$filtro_regimen_fiscal);
            $registro = $resultado['registros'][0];
            $datos_emisor['RegimenFiscalDescripcion'] = $registro['regimen_fiscal_descripcion'];

        }
        return $datos_emisor;
    }
    public function obten_fecha_hora(){
        $fecha = date('Y-m-d');
        $hora = date('H:i:s');
        $fecha_emision = $fecha.'T'.$hora;
        return $fecha_emision;
    }
    public function obten_datos_impuestos($xml){
        $datos_impuestos = array();
        $namespaces = $xml->getDocNamespaces();
        foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Impuestos') as $impuesto){

            $datos_impuestos['TotalImpuestosTrasladados'] = $impuesto['TotalImpuestosTrasladados'];
            $datos_impuestos['TotalImpuestosRetenidos'] = $impuesto['TotalImpuestosRetenidos'];

        }
        if(count($datos_impuestos)==0){
            $datos_impuestos['TotalImpuestosTrasladados'] = 0;
            $datos_impuestos['TotalImpuestosRetenidos'] = 0;
        }
        return $datos_impuestos;
    }
    public function obten_datos_impuestos_retenidos($xml){
        $impuesto_modelo = new impuesto($this->link);
        $datos_impuestos_retencion = array();
        $namespaces = $xml->getDocNamespaces();
        foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Impuestos//cfdi:Retenciones//cfdi:Retencion') as $impuesto_retencion){
            $datos_impuestos_retencion['Impuesto'] = $impuesto_retencion['Impuesto'];
            $datos_impuestos_retencion['Importe'] = $impuesto_retencion['Importe'];

            $filtro_impuesto = array('codigo'=>$impuesto_retencion['Impuesto']);
            $resultado = $impuesto_modelo->filtro_and('impuesto',$filtro_impuesto);
            $registro = $resultado['registros'][0];
            $datos_impuestos_retencion['ImpuestoDescripcion'] = $registro['impuesto_descripcion'];

        }
        return $datos_impuestos_retencion;
    }
    public function obten_datos_impuestos_trasladados($xml){
        $impuesto_modelo = new Impuesto($this->link);
        $datos_impuestos_traslados = array();
        $namespaces = $xml->getDocNamespaces();
        foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Impuestos//cfdi:Traslados//cfdi:Traslado') as $impuesto_traslado){
            $datos_impuestos_traslados['Impuesto'] = $impuesto_traslado['Impuesto'];
            $datos_impuestos_traslados['TipoFactor'] = $impuesto_traslado['TipoFactor'];
            $datos_impuestos_traslados['TasaOCuota'] = $impuesto_traslado['TasaOCuota'];
            $datos_impuestos_traslados['Importe'] = $impuesto_traslado['Importe'];

            $filtro_impuesto = array('codigo'=>$impuesto_traslado['Impuesto']);
            $resultado = $impuesto_modelo->filtro_and('impuesto',$filtro_impuesto);
            $registro = $resultado['registros'][0];
            $datos_impuestos_traslados['ImpuestoDescripcion'] = $registro['impuesto_descripcion'];

        }
        return $datos_impuestos_traslados;
    }
    public function obten_datos_ligados(){
        $this->datos_metodo_pago = $this->obten_datos('metodo_pago');
        $this->datos_tipo_comprobante = $this->obten_datos('tipo_comprobante');
        $this->datos_moneda = $this->obten_datos('moneda');
        $this->datos_forma_pago = $this->obten_datos('forma_pago');
        $this->datos_cliente = $this->obten_datos('cliente');
        $this->datos_uso_cfdi = $this->obten_datos('uso_cfdi');
    }
    public function obten_datos_partidas($xml){
        $producto_sat_modelo = new producto_sat($this->link);
        $unidad_modelo = new unidad($this->link);

        $datos_partidas = array();
        $namespaces = $xml->getDocNamespaces();
        $i = 0;

        foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Conceptos//cfdi:Concepto') as $concepto){
            $j = 0;
            foreach ($concepto->xpath('cfdi:Impuestos//cfdi:Traslados//cfdi:Traslado') as $traslado){
                $impuesto_modelo = new Impuesto($this->link);
                $datos_partidas[$i]['traslados'][$j]['Base'] = $traslado['Base'];
                $datos_partidas[$i]['traslados'][$j]['Impuesto'] = $traslado['Impuesto'];
                $datos_partidas[$i]['traslados'][$j]['TipoFactor'] = $traslado['TipoFactor'];
                $datos_partidas[$i]['traslados'][$j]['TasaOCuota'] = $traslado['TasaOCuota'];
                $datos_partidas[$i]['traslados'][$j]['Importe'] = $traslado['Importe'];

                $filtro_impuesto = array('codigo'=>$traslado['Impuesto']);

                $resultado = $impuesto_modelo->filtro_and('impuesto',$filtro_impuesto);



                $registro = $resultado['registros'][0];
                $datos_partidas[$i]['traslados'][$j]['ImpuestoDescripcion'] = $registro['impuesto_descripcion'];
            }


            $j = 0;
            foreach ($concepto->xpath('cfdi:Impuestos//cfdi:Retenciones//cfdi:Retencion') as $retencion){
                $impuesto_modelo = new Impuesto($this->link);
                $datos_partidas[$i]['retenciones'][$j]['Base'] = $retencion['Base'];
                $datos_partidas[$i]['retenciones'][$j]['Impuesto'] = $retencion['Impuesto'];
                $datos_partidas[$i]['retenciones'][$j]['TipoFactor'] = $retencion['TipoFactor'];
                $datos_partidas[$i]['retenciones'][$j]['TasaOCuota'] = $retencion['TasaOCuota'];
                $datos_partidas[$i]['retenciones'][$j]['Importe'] = $retencion['Importe'];

                $filtro_impuesto = array('codigo'=>$retencion['Impuesto']);
                $resultado = $impuesto_modelo->filtro_and('impuesto',$filtro_impuesto);
                $registro = $resultado['registros'][0];
                $datos_partidas[$i]['retenciones'][$j]['ImpuestoDescripcion'] = $registro['impuesto_descripcion'];
            }

            $datos_partidas[$i]['ClaveProdServ'] = $concepto['ClaveProdServ'];
            $datos_partidas[$i]['NoIdentificacion'] = $concepto['NoIdentificacion'];
            $datos_partidas[$i]['Cantidad'] = $concepto['Cantidad'];
            $datos_partidas[$i]['ClaveUnidad'] = $concepto['ClaveUnidad'];
            $datos_partidas[$i]['Unidad'] = $concepto['Unidad'];
            $datos_partidas[$i]['Descripcion'] = $concepto['Descripcion'];
            $datos_partidas[$i]['ValorUnitario'] = $concepto['ValorUnitario'];
            $datos_partidas[$i]['Importe'] = $concepto['Importe'];
            $datos_partidas[$i]['Descuento'] = $concepto['Descuento'];

            $datos_partidas[$i]['ClaveUnidadDescripcion'] = '';
            $datos_partidas[$i]['ClaveProdServDescripcion'] = '';

            $filtro_producto_sat = array('codigo'=>$concepto['ClaveProdServ']);
            $resultado = $producto_sat_modelo->filtro_and('producto_sat',$filtro_producto_sat);
            if(errores::$error){
                return $this->error->error('Error al obtener producto sat', $resultado);
            }
            if(count($resultado['registros'])>0){
                $registro = $resultado['registros'][0];
                $datos_partidas[$i]['ClaveProdServDescripcion'] = $registro['producto_sat_descripcion'];
            }


            $filtro_unidad = array('codigo'=>$concepto['ClaveUnidad']);
            $resultado = $unidad_modelo->filtro_and('unidad',$filtro_unidad);
            if(count($resultado['registros'])>0) {
                $registro = $resultado['registros'][0];
                $datos_partidas[$i]['ClaveUnidadDescripcion'] = $registro['unidad_descripcion'];
            }
            $i++;
        }

        return $datos_partidas;
    }
    public function obten_datos_receptor($xml){
        $uso_cfdi_modelo = new uso_cfdi($this->link);
        $datos_receptor = array();
        $namespaces = $xml->getDocNamespaces();
        foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Receptor') as $receptor){  // SECCION EMISOR
            $datos_receptor['Rfc'] = $receptor['Rfc'];
            $datos_receptor['Nombre'] = $receptor['Nombre'];
            $datos_receptor['UsoCFDI'] = $receptor['UsoCFDI'];

            $filtro_uso_cfdi = array('codigo'=>$datos_receptor['UsoCFDI']);
            $resultado = $uso_cfdi_modelo->filtro_and('uso_cfdi',$filtro_uso_cfdi);
            $registro = $resultado['registros'][0];
            $datos_receptor['UsoCFDIDescripcion'] = $registro['uso_cfdi_descripcion'];
        }
        return $datos_receptor;
    }

    private function relacion_factura_uuid(string $uuid): array|string
    {
        $uuid = trim($uuid);
        if($uuid === ''){
            return $this->error->error(mensaje: 'Error uuid no puede venir vacio', data: $uuid,
                params: get_defined_vars());
        }
       return '<cfdi:CfdiRelacionado UUID="' . $uuid . '" />';
    }

    public function facturas_relacionadas(array $facturas_relacionadas): array|string
    {
        $relaciones = '';
        foreach($facturas_relacionadas as $fac_rel) {
            $relacion = $this->relacion_factura_uuid(uuid: $fac_rel['factura_uuid']);
            if(errores::$error){
                return $this->error->error(mensaje:  'Error al asignar relacion',data: $relacion,
                    params: get_defined_vars());
            }
            $relaciones .= $relacion;
        }

        return $relaciones;
    }


    public function obten_uuid($folio){
        $dom = new DOMDocument('1.0','utf-8'); // Creamos el Objeto DOM
        $dom->load($this->directorio_xml_timbrado_completo.'/'.$folio.'.xml');
        $UUID=False;
        foreach ($dom->getElementsByTagNameNS('http://www.sat.gob.mx/TimbreFiscalDigital', '*') as $elemento) {
            $UUID    = $elemento->getAttribute('UUID');
        }
        return $UUID;
    }

    public function obten_sello_cfd($folio){
        $dom = new DOMDocument('1.0','utf-8'); // Creamos el Objeto DOM
        $dom->load($this->directorio_xml_timbrado_completo.'/'.$folio.'.xml');

        $SelloCFD=False;
        foreach ($dom->getElementsByTagNameNS('http://www.sat.gob.mx/TimbreFiscalDigital', '*') as $elemento) {
            $SelloCFD    = $elemento->getAttribute('SelloCFD');
        }
        return $SelloCFD;
    }

    public function obten_sello_sat($folio){
        $dom = new DOMDocument('1.0','utf-8'); // Creamos el Objeto DOM
        $dom->load($this->directorio_xml_timbrado_completo.'/'.$folio.'.xml');

        $SelloSAT=False;
        foreach ($dom->getElementsByTagNameNS('http://www.sat.gob.mx/TimbreFiscalDigital', '*') as $elemento) {
            $SelloSAT    = $elemento->getAttribute('SelloSAT');
        }
        return $SelloSAT;
    }

    public function obten_no_certificado_sat($folio){
        $dom = new DOMDocument('1.0','utf-8'); // Creamos el Objeto DOM
        $dom->load($this->directorio_xml_timbrado_completo.'/'.$folio.'.xml');

        $NoCertificadoSAT=False;
        foreach ($dom->getElementsByTagNameNS('http://www.sat.gob.mx/TimbreFiscalDigital', '*') as $elemento) {
            $NoCertificadoSAT    = $elemento->getAttribute('NoCertificadoSAT');
        }
        return $NoCertificadoSAT;
    }


    public function obten_fecha_timbrado($folio){
        $dom = new DOMDocument('1.0','utf-8'); // Creamos el Objeto DOM
        $dom->load($this->directorio_xml_timbrado_completo.'/'.$folio.'.xml');

        $FechaTimbrado=False;
        foreach ($dom->getElementsByTagNameNS('http://www.sat.gob.mx/TimbreFiscalDigital', '*') as $elemento) {
            $FechaTimbrado    = $elemento->getAttribute('FechaTimbrado');
        }
        return $FechaTimbrado;
    }

    public function obten_rfc_pac($folio){
        $dom = new DOMDocument('1.0','utf-8'); // Creamos el Objeto DOM
        $dom->load($this->directorio_xml_timbrado_completo.'/'.$folio.'.xml');

        $FechaTimbrado=False;
        foreach ($dom->getElementsByTagNameNS('http://www.sat.gob.mx/TimbreFiscalDigital', '*') as $elemento) {
            $FechaTimbrado    = $elemento->getAttribute('RfcProvCertif');
        }
        return $FechaTimbrado;
    }
    public function timbra_cfdi_pago($folio){
        $numero_empresa = $_SESSION['numero_empresa'];
        $empresa = new empresas();
        $datos_empresa = $empresa->empresas[$numero_empresa];
        $ws = $datos_empresa['ruta_pac'];
        $xml_sin_timbrar = $this->directorio_xml_sin_timbrar_completo.'/'.'P_'.$folio.'.xml';



        $xml_timbrado = $this->directorio_xml_timbrado_completo.'/'.'P_'.$folio.'.xml';
        $qr = $this->directorio_xml_timbrado_completo.'/'.'P_'.$folio.'.jpg';
        $sello = $this->directorio_xml_timbrado_completo.'/'.'P_'.$folio.'.txt';
        $response = '';
        $rutaArchivo = $xml_sin_timbrar;
        $base64Comprobante = file_get_contents($rutaArchivo);


        $base64Comprobante = base64_encode($base64Comprobante);
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
        $tipoExcepcion = $response->TimbraCFDIResult->anyType[0];
        $numeroExcepcion = $response->TimbraCFDIResult->anyType[1];
        $descripcionResultado = $response->TimbraCFDIResult->anyType[2];
        $xmlTimbrado = $response->TimbraCFDIResult->anyType[3];
        $codigoQr = $response->TimbraCFDIResult->anyType[4];
        $cadenaOriginal = $response->TimbraCFDIResult->anyType[5];

        $status = $response->TimbraCFDIResult->anyType[2];



        $ejecucion = 'TimbraCFDIResult';
        $uuid = false;

        if(strpos($status,"ya ha sido timbrado con UUID")){
            try {
                $elementos = explode(':',$status);
                $uuid = trim($elementos[1]);
                $empresa = new empresas();
                $datos_empresa = $empresa->empresas[$_SESSION['numero_empresa']];

                $rfc_emisor = $datos_empresa['rfc'];

                $ws = $datos_empresa['ruta_pac'];
                $usuario_int = $datos_empresa['usuario_integrador'];
                $params = array();
                $params['usuarioIntegrador'] = $usuario_int;
                $params['rfcEmisor'] = $rfc_emisor;
                $params['folioUUID'] = $uuid;
                $client = new SoapClient($ws,$params);
                $response = $client->__soapCall('ObtieneCFDI', array('parameters' => $params));


                $ejecucion = 'ObtieneCFDIResult';
                $tipoExcepcion = $response->$ejecucion->anyType[0];
                $numeroExcepcion = $response->$ejecucion->anyType[1];
                $descripcionResultado = $response->$ejecucion->anyType[2];
                $xmlTimbrado = $response->$ejecucion->anyType[3];
                $codigoQr = $response->$ejecucion->anyType[4];
                $cadenaOriginal = $response->$ejecucion->anyType[5];
            }
            catch (SoapFault $fault) {
                return array('numero_excepcion'=>-1, 'descripcion'=>'Error de conexion PAC',
                    'tipo_excepcion'=>'Error de conexion');
            }
        }



        if($status == '') {
            file_put_contents($xml_timbrado, $xmlTimbrado);
            file_put_contents($qr, $codigoQr);
            file_put_contents($sello, $cadenaOriginal);
            $pago_cliente_modelo = new pago_cliente($this->link);
            $filtro_pago_cliente = array('folio'=>$folio);
            $resultado = $pago_cliente_modelo->filtro_and('pago_cliente',$filtro_pago_cliente);
            $registro = $resultado['registros'][0];
            $this->pago_cliente_id = $registro['pago_cliente_id'];

            $UUID = $this->obten_uuid('P_'.$folio);
            $SelloCFD = $this->obten_sello_cfd('P_'.$folio);

            $SelloSAT = $this->obten_sello_sat('P_'.$folio);
            $FechaTimbrado = $this->obten_fecha_timbrado('P_'.$folio);

            $RfcProvCertif = $this->obten_rfc_pac('P_'.$folio);


            $NoCertificadoSAT = $this->obten_no_certificado_sat('P_'.$folio);
            $registro_update = array(
                'status_factura'=>'timbrado', 'uuid'=>$UUID,'sello_cfd'=>$SelloCFD,
                'sello_sat'=>$SelloSAT,'no_certificado_sat'=>$NoCertificadoSAT,
                'fecha_timbrado'=>$FechaTimbrado,'rfc_proveedor_timbrado'=>$RfcProvCertif,'status'=>1);
            $resultado_modelo = $pago_cliente_modelo->modifica_bd($registro_update,'pago_cliente',$this->pago_cliente_id);
            if(errores::$error){
                return $this->error->error('Error al actualizar pago_cliente', $resultado_modelo);
            }
            return true;
        }
        else{
            $msj_detalle = $response->$ejecucion->anyType[8];
            return array(
                'mensaje'=>$descripcionResultado.' '.$tipoExcepcion.' '.$numeroExcepcion.' '.$msj_detalle, 'error'=>True,
                'response'=>serialize($response));
        }
    }


    public function timbra_cfdi($folio){
        $numero_empresa = $_SESSION['numero_empresa'];
        $empresa = new Empresas();
        $datos_empresa = $empresa->empresas[$numero_empresa];
        $ws = $datos_empresa['ruta_pac'];
        $xml_sin_timbrar = $this->directorio_xml_sin_timbrar_completo.'/'.$folio.'.xml';


        $xml_timbrado = $this->directorio_xml_timbrado_completo.'/'.$folio.'.xml';
        $qr = $this->directorio_xml_timbrado_completo.'/'.$folio.'.jpg';
        $sello = $this->directorio_xml_timbrado_completo.'/'.$folio.'.txt';
        $response = '';
        $rutaArchivo = $xml_sin_timbrar;
        $base64Comprobante = file_get_contents($rutaArchivo);
        $base64Comprobante = base64_encode($base64Comprobante);
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



        $tipoExcepcion = $response->TimbraCFDIResult->anyType[0];
        $numeroExcepcion = $response->TimbraCFDIResult->anyType[1];
        $descripcionResultado = $response->TimbraCFDIResult->anyType[2];
        $xmlTimbrado = $response->TimbraCFDIResult->anyType[3];
        $codigoQr = $response->TimbraCFDIResult->anyType[4];
        $cadenaOriginal = $response->TimbraCFDIResult->anyType[5];


        $status = $response->TimbraCFDIResult->anyType[2];

        $ejecucion = 'TimbraCFDIResult';
        $uuid = false;


        if(strpos($status,"ya ha sido timbrado con UUID")){
            try {
                $elementos = explode(':',$status);
                $uuid = trim($elementos[1]);
                $empresa = new empresas();
                $datos_empresa = $empresa->empresas[$_SESSION['numero_empresa']];

                $rfc_emisor = $datos_empresa['rfc'];

                $ws = $datos_empresa['ruta_pac'];
                $usuario_int = $datos_empresa['usuario_integrador'];
                $params = array();
                $params['usuarioIntegrador'] = $usuario_int;
                $params['rfcEmisor'] = $rfc_emisor;
                $params['folioUUID'] = $uuid;
                $client = new SoapClient($ws,$params);
                $response = $client->__soapCall('ObtieneCFDI', array('parameters' => $params));


                $ejecucion = 'ObtieneCFDIResult';
                $tipoExcepcion = $response->$ejecucion->anyType[0];
                $numeroExcepcion = $response->$ejecucion->anyType[1];
                $descripcionResultado = $response->$ejecucion->anyType[2];
                $xmlTimbrado = $response->$ejecucion->anyType[3];
                $codigoQr = $response->$ejecucion->anyType[4];
                $cadenaOriginal = $response->$ejecucion->anyType[5];
            }
            catch (SoapFault $fault) {
                return array('numero_excepcion'=>-1, 'descripcion'=>'Error de conexion PAC',
                    'tipo_excepcion'=>'Error de conexion');
            }

        }



        if(!$tipoExcepcion) {
            file_put_contents($xml_timbrado, $xmlTimbrado);
            file_put_contents($qr, $codigoQr);
            file_put_contents($sello, $cadenaOriginal);
            $factura_modelo = new factura($this->link);
            $filtro_factura = array('factura.folio'=>$folio);
            $resultado = $factura_modelo->filtro_and('factura',$filtro_factura);
            $registro = $resultado['registros'][0];
            $this->factura_id = $registro['factura_id'];

            $UUID = $this->obten_uuid($folio);
            $SelloCFD = $this->obten_sello_cfd($folio);

            $SelloSAT = $this->obten_sello_sat($folio);
            $FechaTimbrado = $this->obten_fecha_timbrado($folio);

            $RfcProvCertif = $this->obten_rfc_pac($folio);


            $NoCertificadoSAT = $this->obten_no_certificado_sat($folio);
            $registro_update = array(
                'status_factura'=>'timbrada', 'uuid'=>$UUID,'sello_cfd'=>$SelloCFD,
                'sello_sat'=>$SelloSAT,'no_certificado_sat'=>$NoCertificadoSAT,
                'fecha_timbrado'=>$FechaTimbrado,'rfc_proveedor_timbrado'=>$RfcProvCertif);
            $resultado_modelo = $factura_modelo->modifica_bd($registro_update,'factura',$this->factura_id);
            return true;
        }
        else{
            return array('mensaje'=>$descripcionResultado.' '.$tipoExcepcion.' '.$numeroExcepcion, 'error'=>True);
        }

    }

    public function timbra_cfdi_nota_credito($folio){
        $numero_empresa = $_SESSION['numero_empresa'];
        $empresa = new empresas();
        $datos_empresa = $empresa->empresas[$numero_empresa];
        $ws = $datos_empresa['ruta_pac'];
        $xml_sin_timbrar = $this->directorio_xml_sin_timbrar_completo.'/'.'NC_'.$folio.'.xml';



        $xml_timbrado = $this->directorio_xml_timbrado_completo.'/'.'NC_'.$folio.'.xml';
        $qr = $this->directorio_xml_timbrado_completo.'/'.'NC_'.$folio.'.jpg';
        $sello = $this->directorio_xml_timbrado_completo.'/'.'NC_'.$folio.'.txt';
        $response = '';
        $rutaArchivo = $xml_sin_timbrar;
        $base64Comprobante = file_get_contents($rutaArchivo);


        $base64Comprobante = base64_encode($base64Comprobante);
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
        $tipoExcepcion = $response->TimbraCFDIResult->anyType[0];
        $numeroExcepcion = $response->TimbraCFDIResult->anyType[1];
        $descripcionResultado = $response->TimbraCFDIResult->anyType[2];
        $xmlTimbrado = $response->TimbraCFDIResult->anyType[3];
        $codigoQr = $response->TimbraCFDIResult->anyType[4];
        $cadenaOriginal = $response->TimbraCFDIResult->anyType[5];

        $status = $response->TimbraCFDIResult->anyType[2];



        $ejecucion = 'TimbraCFDIResult';
        $uuid = false;

        if(strpos($status,"ya ha sido timbrado con UUID")){
            try {
                $elementos = explode(':',$status);
                $uuid = trim($elementos[1]);
                $empresa = new empresas();
                $datos_empresa = $empresa->empresas[$_SESSION['numero_empresa']];

                $rfc_emisor = $datos_empresa['rfc'];

                $ws = $datos_empresa['ruta_pac'];
                $usuario_int = $datos_empresa['usuario_integrador'];
                $params = array();
                $params['usuarioIntegrador'] = $usuario_int;
                $params['rfcEmisor'] = $rfc_emisor;
                $params['folioUUID'] = $uuid;
                $client = new SoapClient($ws,$params);
                $response = $client->__soapCall('ObtieneCFDI', array('parameters' => $params));

                $ejecucion = 'ObtieneCFDIResult';
                $tipoExcepcion = $response->$ejecucion->anyType[0];
                $numeroExcepcion = $response->$ejecucion->anyType[1];
                $descripcionResultado = $response->$ejecucion->anyType[2];
                $xmlTimbrado = $response->$ejecucion->anyType[3];
                $codigoQr = $response->$ejecucion->anyType[4];
                $cadenaOriginal = $response->$ejecucion->anyType[5];
            }
            catch (SoapFault $fault) {
                return array('numero_excepcion'=>-1, 'descripcion'=>'Error de conexion PAC',
                    'tipo_excepcion'=>'Error de conexion');
            }
        }


        if($status == '') {
            file_put_contents($xml_timbrado, $xmlTimbrado);
            file_put_contents($qr, $codigoQr);
            file_put_contents($sello, $cadenaOriginal);

            $nota_credito_modelo = new nota_credito($this->link);
            $filtro_nota_credito = array('nota_credito.folio'=>$folio);
            $resultado = $nota_credito_modelo->filtro_and('nota_credito',$filtro_nota_credito);
            $registro = $resultado['registros'][0];
            $this->nota_credito_id = $registro['nota_credito_id'];

            $xml_timbrado = new xml_cfdi($xmlTimbrado,$this->link,'I');

            $nota_credito['uuid'] = $xml_timbrado->get_folio_fiscal();
            $nota_credito['status'] = '1';
            $nota_credito['status_nota_credito'] = 'timbrado';
            $nota_credito['sello_cfd'] = $xml_timbrado->get_sello_cfdi();
            $nota_credito['sello_sat'] = $xml_timbrado->get_sello_sat();
            $nota_credito['serie_csd'] = $xml_timbrado->get_no_serie_csd();
            $nota_credito['serie_sat'] = $xml_timbrado->get_no_serie_sat();
            $nota_credito['fecha_hora_certificacion'] = $xml_timbrado->get_fecha_timbrado();
            $nota_credito['metodo_pago_codigo'] = $xml_timbrado->get_codigo_metodo_pago();
            $nota_credito['ruta'] = $this->directorio_xml_timbrado_completo;

            $nota_credito['cadena_original'] = $response->TimbraCFDIResult->anyType[5];
            $nota_credito['xml'] = base64_encode($response->TimbraCFDIResult->anyType[3]);
            $nota_credito['qr'] = base64_encode($response->TimbraCFDIResult->anyType[4]);

            $nota_credito_modelo = new nota_credito($this->link);
            $nota_credito_modelo->modifica_bd($nota_credito,'nota_credito',$this->nota_credito_id);

            return true;
        }
        else{
            $msj_detalle = $response->$ejecucion->anyType[8];
            return array(
                'mensaje'=>$descripcionResultado.' '.$tipoExcepcion.' '.$numeroExcepcion.' '.$msj_detalle, 'error'=>True,
                'response'=>serialize($response));
        }
    }
}