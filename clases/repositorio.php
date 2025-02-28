<?php

use config\empresas;
use gamboamartin\errores\errores;

class repositorio{
    public $directorio_repositorio_base;
    public $directorio_xml_sin_timbrar;
    public $directorio_xml_timbrado;
    private errores $error;
    private string $directorio_xml_sin_timbrar_completo;
    private string $directorio_xml_timbrado_completo;


    public function __construct(){
        $this->error = new errores();

        if(isset($_SESSION['numero_empresa'])){

            $empresa = new empresas();
            $datos_empresa = $empresa->empresas[$_SESSION['numero_empresa']];
            $this->directorio_repositorio_base = $datos_empresa['nombre_base_datos'];
            $this->directorio_xml_sin_timbrar = "xml_sin_timbrar";
            $this->directorio_xml_timbrado = "xml_timbrado";

            $this->directorio_xml_sin_timbrar_completo = $this->directorio_repositorio_base.'/'.$this->directorio_xml_sin_timbrar;
            $this->directorio_xml_timbrado_completo = $this->directorio_repositorio_base.'/'.$this->directorio_xml_timbrado;

            $dir = $this->crea_directorio($this->directorio_repositorio_base);
            if(errores::$error){
                $error =  $this->error->error('Error al crear directorio', $dir);
                print_r($error);
                die('Error');
            }

            $dir = $this->crea_directorio($this->directorio_xml_sin_timbrar_completo);
            if(errores::$error){
                $error =  $this->error->error('Error al crear directorio', $dir);
                print_r($error);
                die('Error');
            }
            $dir = $this->crea_directorio($this->directorio_xml_timbrado_completo);
            if(errores::$error){
                $error =  $this->error->error('Error al crear directorio', $dir);
                print_r($error);
                die('Error');
            }


        }
    }

    /**
     * ERROR
     * @param string $ruta_directorio
     * @return bool|array
     */
    private function crea_directorio(string $ruta_directorio): bool|array
    {
        if(!file_exists($ruta_directorio) && !mkdir($ruta_directorio) && !is_dir($ruta_directorio)) {
            return $this->error->error('Error al crear directorio', $ruta_directorio);
        }
        return true;
    }
    public function crea_partidas_xml_base($partidas){
        $plantilla = './plantillas_cfdi/partida.xml';
        if(!file_exists($plantilla)){
            return false;
        }

        $resultado_xml = '';

        foreach ($partidas as $partida){
            $contenido_xml_plantilla  = file_get_contents($plantilla);

            $contenido_xml_plantilla = str_replace(
                '|clave_producto_servicio|',$partida['producto_sat_codigo'],
                $contenido_xml_plantilla);
            $contenido_xml_plantilla = str_replace(
                '|no_identificacion|',$partida['no_identificacion'],
                $contenido_xml_plantilla);
            $contenido_xml_plantilla = str_replace(
                '|cantidad|',$partida['cantidad'],
                $contenido_xml_plantilla);
            $contenido_xml_plantilla = str_replace(
                '|clave_unidad|',$partida['unidad_codigo'],
                $contenido_xml_plantilla);
            $contenido_xml_plantilla = str_replace(
                '|unidad|',$partida['unidad'],
                $contenido_xml_plantilla);
            $contenido_xml_plantilla = str_replace(
                '|descripcion|',$partida['descripcion'],
                $contenido_xml_plantilla);
            $contenido_xml_plantilla = str_replace(
                '|valor_unitario|',$partida['valor_unitario'],
                $contenido_xml_plantilla);
            $contenido_xml_plantilla = str_replace(
                '|importe|',$partida['importe'],
                $contenido_xml_plantilla);


            $contenido_xml_plantilla = str_replace(
                '|importe_base|',$partida['base'],
                $contenido_xml_plantilla);

            if(isset($partida['importe_impuesto_retenido']) && $partida['importe_impuesto_retenido']>0 ){
                $remplazo = 'Base="'.$partida['base'].'"';
                $contenido_xml_plantilla = str_replace(
                    '|importe_base_retencion|', $remplazo,
                    $contenido_xml_plantilla);
            }
            else{
                $contenido_xml_plantilla = str_replace(
                    '|importe_base_retencion|', '',
                    $contenido_xml_plantilla);
            }

            $contenido_xml_plantilla = str_replace(
                '|impuesto_codigo|',$partida['impuesto_codigo'],
                $contenido_xml_plantilla);

            if(isset($partida['impuesto_retenido_codigo']) && $partida['importe_impuesto_retenido']>0) {

                $remplazo = 'Impuesto="'.$partida['impuesto_retenido_codigo'].'"';
                $contenido_xml_plantilla = str_replace(
                    '|impuesto_retenido_codigo_partida|', $remplazo,
                    $contenido_xml_plantilla);
            }
            else{
                $contenido_xml_plantilla = str_replace(
                    '|impuesto_retenido_codigo_partida|', '',
                    $contenido_xml_plantilla);
            }

            if(isset($partida['tipo_factor_retenido_codigo']) && $partida['importe_impuesto_retenido']>0) {
                $remplazo = 'TipoFactor="' . $partida['tipo_factor_retenido_codigo'] . '"';
                $contenido_xml_plantilla = str_replace(
                    '|tipo_factor_retenido_codigo|', $remplazo,
                    $contenido_xml_plantilla);
            }
            else{
                $contenido_xml_plantilla = str_replace(
                    '|tipo_factor_retenido_codigo|', '',
                    $contenido_xml_plantilla);
            }

            if(isset($partida['importe_impuesto_retenido']) && $partida['importe_impuesto_retenido']>0 ) {
                $remplazo = 'TasaOCuota="'.$partida['tasa_cuota_retenido'].'"';
                $contenido_xml_plantilla = str_replace(
                    '|tasa_cuota_retenido|', $remplazo,
                    $contenido_xml_plantilla);
            }
            else{
                $contenido_xml_plantilla = str_replace(
                    '|tasa_cuota_retenido|', '',
                    $contenido_xml_plantilla);
            }

            if(isset($partida['importe_impuesto_retenido']) && $partida['importe_impuesto_retenido']>0 ) {
                $remplazo = 'Importe="'.$partida['importe_impuesto_retenido'].'"';
                $contenido_xml_plantilla = str_replace(
                    '|importe_impuesto_retenido|', $remplazo,
                    $contenido_xml_plantilla);
            }
            else{
                $contenido_xml_plantilla = str_replace(
                    '|importe_impuesto_retenido|', '',
                    $contenido_xml_plantilla);
            }

            if(isset($partida['importe_impuesto_retenido']) && $partida['importe_impuesto_retenido']>0 ) {
                $remplazo = '<cfdi:Retencion';

                $contenido_xml_plantilla = str_replace(
                    '|tag_retencion_inicial|', $remplazo,
                    $contenido_xml_plantilla);

                $remplazo = '/>';
                $contenido_xml_plantilla = str_replace(
                    '|tag_retencion_final|', $remplazo,
                    $contenido_xml_plantilla);


                $remplazo = '<cfdi:Retenciones>';

                $contenido_xml_plantilla = str_replace(
                    '|tag_retenciones_inicial_partida|', $remplazo,
                    $contenido_xml_plantilla);

                $remplazo = '</cfdi:Retenciones>';
                $contenido_xml_plantilla = str_replace(
                    '|tag_retenciones_final_partida|', $remplazo,
                    $contenido_xml_plantilla);
            }
            else{
                $contenido_xml_plantilla = str_replace(
                    '|tag_retencion_inicial|', '',
                    $contenido_xml_plantilla);
                $contenido_xml_plantilla = str_replace(
                    '|tag_retencion_final|', '',
                    $contenido_xml_plantilla);
                $contenido_xml_plantilla = str_replace(
                    '|tag_retenciones_inicial_partida|', '',
                    $contenido_xml_plantilla);
                $contenido_xml_plantilla = str_replace(
                    '|tag_retenciones_final_partida|', '',
                    $contenido_xml_plantilla);
            }

            $contenido_xml_plantilla = str_replace(
                '|tipo_factor_codigo|',$partida['tipo_factor_codigo'],
                $contenido_xml_plantilla);
            $contenido_xml_plantilla = str_replace(
                '|tasa_cuota|',$partida['tasa_cuota'],
                $contenido_xml_plantilla);
            $contenido_xml_plantilla = str_replace(
                '|importe_impuesto|',$partida['importe_impuesto'],
                $contenido_xml_plantilla);

            $resultado_xml = $resultado_xml.$contenido_xml_plantilla;
        }


        return $resultado_xml;

    }
    public function guarda_archivo($texto,$nombre,$ruta,$extension){
        $archivo = fopen($ruta.'/'.$nombre.$extension, "w+");
        fwrite($archivo, $texto);
        fclose($archivo);

    }







    public function obten_serie_csd($folio){
        $dom = new DOMDocument('1.0','utf-8'); // Creamos el Objeto DOM
            $dom->load($this->directorio_xml_timbrado_completo.'/'.$folio.'.xml');

        $NoCertificado=False;
        foreach ($dom->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', '*') as $elemento) {
            $NoCertificado    = $elemento->getAttribute('NoCertificado');
            if($NoCertificado){
                return $NoCertificado;
            }
        }
    }

    public function crea_xml_base($datos_cfdi, $partidas){
        $plantilla = './plantillas_cfdi/factura.xml';
        if(!file_exists($plantilla)){
            return false;
        }
        $contenido_xml_plantilla  = file_get_contents($plantilla);

        $contenido_xml_plantilla = str_replace(
            '|lugar_expedicion|',$datos_cfdi['lugar_expedicion'],$contenido_xml_plantilla);
        $contenido_xml_plantilla = str_replace(
            '|metodo_pago|',$datos_cfdi['metodo_pago_codigo'],$contenido_xml_plantilla);
        $contenido_xml_plantilla = str_replace(
            '|tipo_comprobante|',$datos_cfdi['tipo_comprobante_codigo'],
            $contenido_xml_plantilla);
        $contenido_xml_plantilla = str_replace(
            '|total|',$datos_cfdi['total'], $contenido_xml_plantilla);

        $contenido_xml_plantilla = str_replace(
            '|moneda|',$datos_cfdi['moneda_codigo'], $contenido_xml_plantilla);
        $contenido_xml_plantilla = str_replace(
            '|sub_total|',$datos_cfdi['sub_total'], $contenido_xml_plantilla);
        $contenido_xml_plantilla = str_replace(
            '|forma_pago|',$datos_cfdi['forma_pago_codigo'], $contenido_xml_plantilla);
        $contenido_xml_plantilla = str_replace(
            '|fecha|',$datos_cfdi['fecha'], $contenido_xml_plantilla);
        $contenido_xml_plantilla = str_replace(
            '|folio|',$datos_cfdi['folio'], $contenido_xml_plantilla);
        $contenido_xml_plantilla = str_replace(
            '|serie|',$datos_cfdi['serie'], $contenido_xml_plantilla);
        $contenido_xml_plantilla = str_replace(
            '|rfc_emisor|',$datos_cfdi['rfc_emisor'], $contenido_xml_plantilla);
        $contenido_xml_plantilla = str_replace(
            '|nombre_emisor|',$datos_cfdi['nombre_emisor'], $contenido_xml_plantilla);
        $contenido_xml_plantilla = str_replace(
            '|regimen_fiscal|',$datos_cfdi['regimen_fiscal_emisor_codigo'],
            $contenido_xml_plantilla);
        $contenido_xml_plantilla = str_replace(
            '|rfc_receptor|',$datos_cfdi['cliente_rfc'],
            $contenido_xml_plantilla);
        $contenido_xml_plantilla = str_replace(
            '|razon_social_cliente|',$datos_cfdi['cliente_razon_social'],
            $contenido_xml_plantilla);
        $contenido_xml_plantilla = str_replace(
            '|uso_cfdi|',$datos_cfdi['uso_cfdi_codigo'],
            $contenido_xml_plantilla);

        $contenido_xml_plantilla = str_replace(
            '|tipo_cambio|','',$contenido_xml_plantilla);

        $contenido_xml_plantilla = str_replace(
            '|total_impuestos_trasladados|',$datos_cfdi['total_impuestos_trasladados'],
            $contenido_xml_plantilla);

        if($datos_cfdi['total_impuestos_retenidos']>0) {
            $remplazo = 'TotalImpuestosRetenidos="'.$datos_cfdi['total_impuestos_retenidos'].'"';
            $contenido_xml_plantilla = str_replace(
                '|total_impuestos_retenidos|', $remplazo,
                $contenido_xml_plantilla);
        }
        else{
            $contenido_xml_plantilla = str_replace(
                '|total_impuestos_retenidos|', '',
                $contenido_xml_plantilla);
        }

        $contenido_xml_plantilla = str_replace(
            '|impuesto_trasladado|',$datos_cfdi['impuesto_codigo'],
            $contenido_xml_plantilla);

        if(isset($datos_cfdi['impuesto_retenido_codigo'])) {
            $remplazo = 'Impuesto="'.$datos_cfdi['impuesto_retenido_codigo'].'"';
            $contenido_xml_plantilla = str_replace(
                '|impuesto_retenido|', $remplazo,
                $contenido_xml_plantilla);
        }
        else{
            $contenido_xml_plantilla = str_replace(
                '|impuesto_retenido|', '',
                $contenido_xml_plantilla);
        }
        $contenido_xml_plantilla = str_replace(
            '|tipo_factor_trasladado|',$datos_cfdi['tipo_factor_trasladado'],
            $contenido_xml_plantilla);

        if(isset($datos_cfdi['impuesto_retenido_codigo'])) {
            $remplazo = 'Impuesto="' . $datos_cfdi['impuesto_retenido_codigo'] . '"';
            $contenido_xml_plantilla = str_replace(
                '|tipo_factor_retenido|', $remplazo,
                $contenido_xml_plantilla);
        }

        $contenido_xml_plantilla = str_replace(
            '|tasa_cuota_trasladado|',$datos_cfdi['tasa_cuota_trasladado'],
            $contenido_xml_plantilla);

        if(isset($datos_cfdi['tasa_cuota_retenido'])) {
            $contenido_xml_plantilla = str_replace(
                '|tasa_cuota_retenido|', $datos_cfdi['tasa_cuota_retenido'],
                $contenido_xml_plantilla);
        }


        $contenido_xml_plantilla = str_replace(
            '|importe_impuesto_trasladado|',$datos_cfdi['importe_impuesto_trasladado'],
            $contenido_xml_plantilla);

        if($datos_cfdi['importe_impuesto_retenido'] > 0) {
            $remplazo = 'Importe="'.$datos_cfdi['importe_impuesto_retenido'].'"';
            $contenido_xml_plantilla = str_replace(
                '|importe_impuesto_retenido|', $remplazo,
                $contenido_xml_plantilla);

            $remplazo = '<cfdi:Retencion';
            $contenido_xml_plantilla = str_replace(
                '|tag_retencion_inicial|', $remplazo,
                $contenido_xml_plantilla);

            $remplazo = '/>';
            $contenido_xml_plantilla = str_replace(
                '|tag_retencion_final|', $remplazo,
                $contenido_xml_plantilla);

            $remplazo = '<cfdi:Retenciones>';
            $contenido_xml_plantilla = str_replace(
                '|tag_retenciones_inicial|', $remplazo,
                $contenido_xml_plantilla);

            $remplazo = '</cfdi:Retenciones>';
            $contenido_xml_plantilla = str_replace(
                '|tag_retenciones_final|', $remplazo,
                $contenido_xml_plantilla);
        }
        else{
            $contenido_xml_plantilla = str_replace(
                '|importe_impuesto_retenido|', '',
                $contenido_xml_plantilla);
            $contenido_xml_plantilla = str_replace(
                '|tag_retencion_inicial|', '',
                $contenido_xml_plantilla);
            $contenido_xml_plantilla = str_replace(
                '|tag_retencion_final|', '',
                $contenido_xml_plantilla);
            $contenido_xml_plantilla = str_replace(
                '|tag_retenciones_inicial|', '',
                $contenido_xml_plantilla);
            $contenido_xml_plantilla = str_replace(
                '|tag_retenciones_final|', '',
                $contenido_xml_plantilla);

        }


        $partidas_ = $this->crea_partidas_xml_base($partidas);

        $contenido_xml_plantilla = str_replace(
            '|conceptos|',$partidas_,
            $contenido_xml_plantilla);

        $this->guarda_archivo($contenido_xml_plantilla,$datos_cfdi['folio'],
            $this->directorio_xml_sin_timbrar_completo, '.xml');

        return True;

    }

}