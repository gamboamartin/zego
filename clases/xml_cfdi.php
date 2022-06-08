<?php

use config\empresas;

class xml_cfdi{
    private $link;
    private $comprobante = false;
    private $datos_emisor = false;
    private $datos_comprobante = false;
    private $timbre_fiscal_digital = false;
    private $tipos_comprobante = false;
    private $datos_receptor = false;
    private $datos_nomina = false;
    private $datos_comprobante_receptor = false;
    private $cfdi_nomina = array();
    private $cfdi_factura = false;
    private $datos_percepciones = false;
    private $datos_deducciones = false;
    private $datos_deducciones_totales = false;
    private $datos_percepciones_totales = false;

    public function __construct($xml, $link, $tipo_cfdi){
        if($tipo_cfdi == 'N'){
            $this->cfdi_nomina = true;
        }
        if($tipo_cfdi == 'I'){
            $this->cfdi_factura = true;
        }

        $this->tipos_comprobante = array('N'=>'Nómina','I'=>'Ingreso');

        $this->link = $link;
        $this->comprobante= new SimpleXMLElement($xml);
        $namespaces = $this->comprobante->getNamespaces(True);

        $this->comprobante->registerXPathNamespace('cfdi', $namespaces['cfdi']);
        $this->comprobante->registerXPathNamespace('tfd', $namespaces['tfd']);


        if($this->cfdi_nomina) {
            $this->comprobante->registerXPathNamespace('nomina12', $namespaces['nomina12']);
            $this->datos_comprobante_receptor = $this->comprobante->xpath('//cfdi:Receptor')[0];
            $this->datos_receptor = $this->comprobante->xpath('//nomina12:Receptor')[0];
            $this->datos_nomina = $this->comprobante->xpath('//nomina12:Nomina')[0];
            $this->datos_nomina = $this->comprobante->xpath('//nomina12:Nomina')[0];
            $this->datos_percepciones = $this->comprobante->xpath('//nomina12:Percepcion');
            $this->datos_deducciones = $this->comprobante->xpath('//nomina12:Deduccion');
            $this->datos_deducciones_totales = $this->comprobante->xpath('//nomina12:Deducciones');
            $this->datos_percepciones_totales = $this->comprobante->xpath('//nomina12:Percepciones')[0];

            $this->obten_datos_periodicidad_pago_bd();
            $this->obten_datos_tipo_regimen_bd();
        }


        $this->timbre_fiscal_digital = $this->comprobante->xpath('//tfd:TimbreFiscalDigital')[0];
        $this->datos_emisor = $this->comprobante->xpath('//cfdi:Emisor')[0];


        $this->datos_comprobante = $this->comprobante->xpath('//cfdi:Comprobante')[0];



        $this->obten_datos_emisor_bd();
        $this->obten_datos_lugar_emision();


    }

    public function get_rfc_emisor(){
       return trim($this->datos_emisor['Rfc']);
    }
    public function get_cp_emisor(){
        return trim($this->datos_comprobante['LugarExpedicion']);
    }
    public function get_calle_emisor(){
        return trim($this->datos_emisor['calle']);
    }
    public function get_numero_exterior_emisor(){
        return trim($this->datos_emisor['numero_exterior']);
    }
    public function get_localidad_emisor(){
        return trim($this->datos_emisor['localidad']);
    }
    public function get_municipio_emisor(){
        return trim($this->datos_emisor['municipio']);
    }
    public function get_estado_emisor(){
        return trim($this->datos_emisor['estado']);
    }
    public function get_pais_emisor(){
        return trim($this->datos_emisor['pais']);
    }
    public function get_folio_comprobante(){
        return trim($this->datos_comprobante['Folio']);
    }
    public function get_razon_social_emisor(){
        return trim($this->datos_emisor['Nombre']);
    }
    public function get_codigo_tipo_comprobante(){
        $codigo_tipo_comprobante = $this->datos_comprobante['TipoDeComprobante'][0];

        return trim($codigo_tipo_comprobante);
    }
    public function get_tipo_comprobante(){
        $codigo_tipo_comprobante = trim($this->get_codigo_tipo_comprobante());
        return $this->tipos_comprobante[$codigo_tipo_comprobante];
    }
    public function get_folio_fiscal(){
        return trim($this->timbre_fiscal_digital['UUID']);
    }
    public function get_no_serie_csd(){
        return trim($this->datos_comprobante['NoCertificado']);
    }
    public function get_municipio_emision(){
        return trim($this->datos_emisor['municipio_emision']);
    }
    public function get_estado_emision(){
        return trim($this->datos_emisor['estado_emision']);
    }
    public function get_fecha_emision(){
        return trim($this->datos_comprobante['Fecha']);
    }

    public function get_codigo_peridicidad_pago(){
        return trim($this->datos_receptor['PeriodicidadPago']);
    }

    public function get_peridicidad_pago(){
        return trim($this->datos_receptor['periodicidad_pago']);
    }
    public function get_fecha_inicial_pago(){
        return trim($this->datos_nomina['FechaInicialPago']);
    }
    public function get_fecha_final_pago(){
        return trim($this->datos_nomina['FechaFinalPago']);
    }
    public function get_fecha_pago(){
        return trim($this->datos_nomina['FechaPago']);
    }

    public function get_no_serie_sat(){
        return trim($this->timbre_fiscal_digital['NoCertificadoSAT']);
    }
    public function get_fecha_timbrado(){
        return trim($this->timbre_fiscal_digital['FechaTimbrado']);
    }
    public function get_num_empleado(){
        return trim($this->datos_receptor['NumEmpleado']);
    }

    public function get_nombre_receptor(){
        return trim($this->datos_comprobante_receptor['Nombre']);
    }
    public function get_rfc_receptor(){
        return trim($this->datos_comprobante_receptor['Rfc']);
    }
    public function get_curp_receptor(){
        return trim($this->datos_receptor['Curp']);
    }
    public function get_domicilio_fiscal_receptor(){
        return trim($this->cfdi_nomina['asociado_domicilio_fiscal']);
    }
    public function get_municipio_fiscal_receptor(){

        return trim($this->cfdi_nomina['municipio_fiscal_descripcion']);
    }

    public function get_estado_fiscal_receptor(){
        return trim($this->cfdi_nomina['estado_fiscal_descripcion']);
    }
    public function get_pais_fiscal_receptor(){
        return trim($this->cfdi_nomina['pais_fiscal_descripcion']);
    }
    public function get_cp_fiscal_receptor(){
        return trim($this->cfdi_nomina['cp_fiscal_codigo_postal']);
    }

    public function get_codigo_tipo_regimen_receptor(){
        return trim($this->datos_receptor['TipoRegimen']);
    }

    public function get_codigo_metodo_pago(){
        return trim($this->datos_comprobante['MetodoPago']);
    }
    public function get_tipo_regimen_receptor(){
        return trim($this->datos_receptor['tipo_regimen']);
    }
    public function get_nss_receptor(){
        return trim($this->datos_receptor['NumSeguridadSocial']);
    }

    public function get_percepciones(){
        return $this->datos_percepciones;
    }
    public function get_deducciones(){
        return $this->datos_deducciones;
    }

    public function get_total_deducciones(){
        return trim($this->datos_comprobante['Descuento']);
    }
    public function get_total_percepciones(){
        return trim($this->datos_comprobante['SubTotal']);
    }

    public function get_total_gravable(){
        return trim($this->datos_percepciones_totales['TotalGravado']);
    }
    public function get_total_excento(){
        return trim($this->datos_percepciones_totales['TotalExento']);
    }

    public function get_total(){
        return trim($this->datos_comprobante['Total']);
    }

    public function get_sello_cfdi(){
        return trim($this->timbre_fiscal_digital['SelloCFD']);
    }
    public function get_sello_sat(){
        return trim($this->timbre_fiscal_digital['SelloSAT']);
    }
    public function get_cadena_original(){
        return trim($this->cfdi_nomina['cfdi_nomina_cadena_original']);
    }
    public function get_qr(){
        $registro_id = $this->cfdi_nomina['cfdi_nomina_id'];
        $qr_64  = base64_decode($this->cfdi_nomina['cfdi_nomina_qr']);
        $ruta = 'views/cfdi_nomina/'.$registro_id.'.jpg';
        $qr = fopen($ruta, "w+");
        fwrite($qr,$qr_64);
        fclose($qr);
        return URL_BASE.$ruta;
    }

    private function obten_datos_emisor_bd(){

        $empresas = new empresas();
        $datos_empresa = $empresas->empresas[$_SESSION['numero_empresa']];

        $this->datos_emisor['calle'] = $datos_empresa['calle'];
        $this->datos_emisor['numero_exterior'] = $datos_empresa['exterior'];
        $this->datos_emisor['localidad'] = $datos_empresa['municipio'];
        $this->datos_emisor['municipio'] = $datos_empresa['municipio'];
        $this->datos_emisor['estado'] = $datos_empresa['estado'];
        $this->datos_emisor['pais'] = $datos_empresa['pais'];

    }
    private function obten_datos_lugar_emision(){
        $empresas = new empresas();
        $datos_empresa = $empresas->empresas[$_SESSION['numero_empresa']];
        $this->datos_emisor['municipio_emision'] = trim($datos_empresa['municipio']);
        $this->datos_emisor['estado_emision'] = trim($datos_empresa['estado']);

    }
    private function obten_datos_periodicidad_pago_bd(){
        $periodicidad_pago = new periodicidad_pago($this->link,'periodicidad_pago');
        $filtro = array('periodicidad_pago.codigo'=>$this->get_codigo_peridicidad_pago());
        $r_periodicidad_pago = $periodicidad_pago->filtro_and($filtro);
        $periodicidad_pago = $r_periodicidad_pago['registros'][0];
        $this->datos_receptor['periodicidad_pago'] = trim($periodicidad_pago['periodicidad_pago_descripcion']);
    }

    private function obten_datos_tipo_regimen_bd(){
        $tipo_regimen = new tipo_regimen($this->link,'tipo_regimen');
        $filtro = array('tipo_regimen.codigo'=>$this->get_codigo_tipo_regimen_receptor());
        $r_tipo_regimen = $tipo_regimen->filtro_and($filtro);
        $tipo_regimen = $r_tipo_regimen['registros'][0];
        $this->datos_receptor['tipo_regimen'] = trim($tipo_regimen['tipo_regimen_descripcion']);
    }

}