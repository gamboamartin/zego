<?php
namespace controllers;
use config\empresas;
use facturas;
use gamboamartin\errores\errores;
use models\cliente;
use models\factura;
use models\factura_relacionada;
use models\insumo;
use models\metodo_pago;
use models\modelos;
use models\moneda;
use models\partida_factura;
use my_pdf;
use NumeroTexto;
use SoapClient;
use SoapFault;

class controlador_factura extends controlador_base {
    public $datos_guardar;
    public $datos_empresa;
    public $cliente_id;
    public $cliente_rfc;
    public $cliente_razon_social;
    public $cliente_cp;
    public $cliente_colonia;
    public $cliente_calle;
    public $cliente_exterior;
    public $cliente_interior;
    public $cliente_telefono;
    public $cliente_email;
    public $cliente_pais_id;
    public $cliente_estado_id;
    public $cliente_municipio_id;
    public $cliente_uso_cfdi_id;
    public $cliente_moneda_id;
    public $cliente_forma_pago_id;
    public $cliente_metodo_pago_id;
    public $cliente_condiciones_pago;
    public $insumo_id;
    public $partidas;
    public $fecha;
    public $datos_emisor;
    public $datos_receptor;
    public $datos_comprobante;
    public $datos_impuestos;
    public $numero_texto;
    public $datos_partidas;
    public $facturas;
    public $factura_id;
    public $factura;

    private function actualiza_saldos_factura($factura_id){
        $partida_factura_modelo = new Partida_Factura($this->link);
        $resultado = $partida_factura_modelo->sumatoria('importe_neto','partida_factura','factura_id',$factura_id);
        $total = round($resultado['registros'][0]['suma'],2);
        $total = number_format($total,2,'.','');

        $resultado = $partida_factura_modelo->sumatoria('importe','partida_factura','factura_id',$factura_id);
        $sub_total = round($resultado['registros'][0]['suma'],2);
        $sub_total = number_format($sub_total,2,'.','');


        $resultado = $partida_factura_modelo->sumatoria('total_impuestos_trasladados','partida_factura','factura_id',$factura_id);
        $total_impuestos_trasladados = round($resultado['registros'][0]['suma'],2);
        $total_impuestos_trasladados = number_format($total_impuestos_trasladados,2,'.','');


        $resultado = $partida_factura_modelo->sumatoria('total_impuestos_retenidos','partida_factura','factura_id',$factura_id);
        $total_impuestos_retenidos = round($resultado['registros'][0]['suma'],2);
        $total_impuestos_retenidos = number_format($total_impuestos_retenidos,2,'.','');

        $this->factura = array();
        $this->factura['total'] = $total;
        $this->factura['sub_total'] = $sub_total;
        $this->factura['total_impuestos_trasladados'] = $total_impuestos_trasladados;
        $this->factura['total_impuestos_retenidos'] = $total_impuestos_retenidos;

    }

    public function agrega_conceptos(){
        $this->cliente_id = $_COOKIE['cliente_id'];
        $this->cliente_uso_cfdi_id = $_COOKIE['cliente_uso_cfdi_id'];
        $this->cliente_moneda_id = $_COOKIE['cliente_moneda_id'];
        $this->cliente_forma_pago_id = $_COOKIE['cliente_forma_pago_id'];
        $this->cliente_metodo_pago_id = $_COOKIE['cliente_metodo_pago_id'];
        $this->cliente_condiciones_pago = $_COOKIE['cliente_condiciones_pago'];
        $this->fecha = $_COOKIE['fecha'];


    }

    public function alta_cliente(){


    }

    public function alta_insumo(){

    }

    public function a_cuenta_terceros(){

    }

    public function cancela_factura(){
        $modelo_factura = new factura($this->link);



        if(!isset($_GET['factura_id'])){
            $_GET['factura_id'] = $_COOKIE['factura_id'];
        }

        $resultado = $modelo_factura->obten_por_id('factura', $_GET['factura_id']);

        $factura = $resultado['registros'][0];

        /* Ruta del servicio de integracion*/

        $empresa = new empresas();
        $datos_empresa = $empresa->empresas[$_SESSION['numero_empresa']];

        $ws = $datos_empresa['ruta_pac'];

        $response = '';
        /* El servicio para cancelar un cfdi recibe 3 parámetros*/

        /*Usuario Integrador*/
        $usuarioIntegrador = $datos_empresa['usuario_integrador'];

        /*Rfc del Emisor que emitió el comprobante*/
        $rfcEmisor = $datos_empresa['rfc'];

        /*Folio fiscal(UUID) del comprobante a cancelar, deberá ser uno válido de los que hayamos timbrado previamente en pruebas*/

        $folioUUID = $factura['factura_uuid'];



        $params = array();
        /*Nombre del usuario integrador asignado, para efecto de pruebas utilizaremos 'mvpNUXmQfK8='*/
        $params['usuarioIntegrador'] = $usuarioIntegrador;
        /* Rfc emisor que emitió el comprobante*/
        $params['rfcEmisor'] = $rfcEmisor;
        /*Folio fiscal del comprobante a cancelar*/
        $params['folioUUID'] = $folioUUID;


        try {
            $params = array();
            /*Nombre del usuario integrador asignado, para efecto de pruebas utilizaremos 'mvpNUXmQfK8='*/
            $params['usuarioIntegrador'] = $usuarioIntegrador;
            /* Rfc emisor que emitió el comprobante*/
            $params['rfcEmisor'] = $rfcEmisor;
            /*Folio fiscal del comprobante a cancelar*/
            $params['folioUUID'] = $folioUUID;

            $client = new SoapClient($ws, $params);
            $response = $client->__soapCall('CancelaCFDI', array('parameters' => $params));
        } catch (SoapFault $fault) {
            echo "SOAPFault: " . $fault->faultcode . "-" . $fault->faultstring . "\n";
        }
        /*Obtenemos resultado del response*/
        $tipoExcepcion = $response->CancelaCFDIResult->anyType[0];
        $numeroExcepcion = $response->CancelaCFDIResult->anyType[1];
        $descripcionResultado = $response->CancelaCFDIResult->anyType[2];
        $xmlTimbrado = $response->CancelaCFDIResult->anyType[3];
        $codigoQr = $response->CancelaCFDIResult->anyType[4];
        $cadenaOriginal = $response->CancelaCFDIResult->anyType[5];

        $datos_uuid = json_decode($response->CancelaCFDIResult->anyType[8]);

        $uuid_cancelacion = $datos_uuid[0]->Value;

        if ($numeroExcepcion == "0") {
            print_r($response);
            $modelo_factura->modifica_bd(array('status'=>'0','uuid_cancelacion'=>$uuid_cancelacion,'status_factura'=>'cancelado','sello_cancelacion'=>$cadenaOriginal),'factura',$_GET['factura_id']);
            header("Location: ./index.php?seccion=factura&accion=lista&mensaje=cancelado_con_exito&tipo_mensaje=exito");
            exit;

        } else {
            $mensaje = $descripcionResultado;
            header("Location: ./index.php?seccion=factura&accion=lista&mensaje=$mensaje&tipo_mensaje=error");
            exit;
        }
    }

    private function carga_datos_empresa(){
        $this->datos_guardar['lugar_expedicion'] = $this->datos_empresa['cp'];
        $this->datos_guardar['calle_expedicion'] = $this->datos_empresa['calle'];
        $this->datos_guardar['exterior_expedicion'] = $this->datos_empresa['exterior'];
        $this->datos_guardar['interior_expedicion'] = $this->datos_empresa['interior'];
        $this->datos_guardar['colonia_expedicion'] = $this->datos_empresa['colonia'];
        $this->datos_guardar['municipio_expedicion'] = $this->datos_empresa['municipio'];
        $this->datos_guardar['estado_expedicion'] = $this->datos_empresa['estado'];
        $this->datos_guardar['pais_expedicion'] = $this->datos_empresa['pais'];
        $this->datos_guardar['serie'] = $this->datos_empresa['serie'];
        $this->datos_guardar['rfc_emisor'] = $this->datos_empresa['rfc'];
        $this->datos_guardar['nombre_emisor'] = $this->datos_empresa['razon_social'];
        $this->datos_guardar['regimen_fiscal_emisor_codigo'] = $this->datos_empresa['regimen_fiscal'];
        $this->datos_guardar['regimen_fiscal_emisor_descripcion'] = $this->datos_empresa['regimen_fiscal_descripcion'];


    }

    public function crea_factura(){
        $this->cliente_id = false;
        if(isset($_COOKIE['cliente_id'])){
            $this->cliente_id = $_COOKIE['cliente_id'];
        }
    }

    public function elimina_factura_completa(){
        if(!isset($_COOKIE['factura_id'])){
            $mensaje = 'Seleccione una factura';
            header("Location: index.php?seccion=factura&mensaje=$mensaje&tipo_mensaje=error&accion=ve_factura_cancelar&session_id=".SESSION_ID);
            exit;
        }
        $this->factura_id = $_COOKIE['factura_id'];

        $factura = new factura($this->link);
        $partida_factura = new partida_factura($this->link);

        $factura->elimina_bd('factura',$this->factura_id);

        $filtro = array('factura_id'=>$this->factura_id);

        $partida_factura->elimina_con_filtro_and('factura',$filtro);
        $mensaje = 'Eliminado con exito';
        header("Location: ./index.php?seccion=factura&session_id=".SESSION_ID."&accion=ve_factura_cancelar&mensaje=$mensaje&tipo_mensaje=exito");
        exit;

    }

    public function elimina_factura(){
        $factura_id = $_GET['factura_id'];
        $factura_modelo = new Factura($this->link);
        $factura_relacionada_modelo = new factura_relacionada($this->link);
        $partida_factura_modelo = new partida_factura($this->link);

        $r_factura = $factura_modelo->obten_por_id('factura', $factura_id);

        $factura = $r_factura['registros'][0];

        $filtro = array('factura_relacionada.factura_id'=>$factura_id);

        $r_factura_relacionada = $factura_relacionada_modelo->elimina_con_filtro_and('factura_relacionada',$filtro);
        if($this->link->error){
            print_r($this->link->error); exit;
        }

        $filtro = array('partida_factura.factura_id'=>$factura_id);
        $r_partida_factura = $partida_factura_modelo->elimina_con_filtro_and('partida_factura',$filtro);
        if($this->link->error){
            print_r($this->link->error); exit;
        }

        $sql = "DELETE FROM partida_informe_gasto WHERE folio = '$factura[factura_folio]'";
        $this->link->query($sql);
        if($this->link->error){
            print_r($this->link->error); exit;
        }


        $sql = "DELETE FROM factura WHERE folio = '$factura[factura_folio]'";
        $this->link->query($sql);
        if($this->link->error){
            print_r($this->link->error); exit;
        }

        header('Location: ./index.php?seccion=factura&accion=lista');
        exit;

    }

    public function elimina_partida(){
        $partidas = unserialize($_COOKIE['partidas']);
        unset($partidas[$_GET['partida']]);
        $partidas_nuevas = array();
        $i = 0;
        foreach ($partidas as $partida){
            $partidas_nuevas[$i] = $partida;
            $i++;
        }


        setcookie('partidas', serialize($partidas_nuevas));

        header('Location: ./index.php?seccion=factura&accion=siguiente_partida');
        exit;
    }

    public function elimina_partida_por_id(){
        $partida_factura = new Partida_Factura($this->link);
        $factura_id = $_COOKIE['factura_id'];
        $partida_factura_id = $_GET['partida_factura_id'];
        $partida_factura->elimina_bd('partida_factura', $partida_factura_id);

        $this->actualiza_saldos_factura($factura_id);
        $factura_modelo = new Factura($this->link);
        $resultado = $factura_modelo->modifica_bd($this->factura,'factura',$factura_id);



        header("Location: ./index.php?seccion=factura&accion=modifica&registro_id=$factura_id&session_id=".SESSION_ID);
        exit;
    }

    public function genera_folio($sufijo, $tabla,$folio_inicial){
        $modelo = new Factura($this->link);
        $ultimo_id = $modelo->obten_ultimo_id($tabla);
        $ultimo_folio = $ultimo_id + $folio_inicial;
        $ultimo_folio++;
        $folio = $sufijo.'_'.$ultimo_folio;
        return $folio;

    }

    public function genera_xml(){
        $controlador_cliente = new controlador_cliente($this->link);
        $controlador_cliente->genera_pdf_factura_sin_timbrar($_GET['factura_id']);
        header("Location: ./index.php?seccion=factura&accion=vista_preliminar&factura_id=".$_GET['factura_id']);
        exit;

    }

    public function guarda_cliente(){
        $cliente_modelo = new cliente($this->link);
        $resultado = $cliente_modelo->alta_bd($_POST,'cliente');
        echo json_encode($resultado);
        header('Content-Type: application/json');
    }

    public function guarda_cliente_session(){
        $this->cliente_id = $_POST['cliente_id'];
        setcookie('cliente_id',$_POST['cliente_id']);
    }

    public function guarda_datos_factura_session(){
        print_r($_POST);
        setcookie('cliente_uso_cfdi_id', $_POST['cliente_uso_cfdi_id']);
        setcookie('cliente_moneda_id', $_POST['cliente_moneda_id']);
        setcookie('cliente_forma_pago_id', $_POST['cliente_forma_pago_id']);
        setcookie('cliente_metodo_pago_id', $_POST['cliente_metodo_pago_id']);
        setcookie('cliente_condiciones_pago', $_POST['cliente_condiciones_pago']);
        setcookie('fecha', $_POST['fecha']);
    }

    public function guarda_factura(){


        $factura_modelo = new factura($this->link);


        $datos_guardar = array();

        $empresa = new Empresas();
        $datos_empresa = $empresa->empresas[$_SESSION['numero_empresa']];

        $datos_guardar['lugar_expedicion'] = $datos_empresa['cp'];
        $datos_guardar['calle_expedicion'] = $datos_empresa['calle'];
        $datos_guardar['exterior_expedicion'] = $datos_empresa['exterior'];
        $datos_guardar['interior_expedicion'] = $datos_empresa['interior'];
        $datos_guardar['colonia_expedicion'] = $datos_empresa['colonia'];
        $datos_guardar['municipio_expedicion'] = $datos_empresa['municipio'];
        $datos_guardar['estado_expedicion'] = $datos_empresa['estado'];
        $datos_guardar['pais_expedicion'] = $datos_empresa['pais'];

        $datos_guardar['metodo_pago_id'] = $_COOKIE['cliente_metodo_pago_id'];
        $metodo_pago_modelo = new metodo_pago($this->link);
        $resultado = $metodo_pago_modelo->obten_por_id('metodo_pago',$_COOKIE['cliente_metodo_pago_id']);
        $metodo_pago = $resultado['registros'][0];
        $datos_guardar['metodo_pago_codigo'] = $metodo_pago['metodo_pago_codigo'];
        $datos_guardar['metodo_pago_descripcion'] = $metodo_pago['metodo_pago_descripcion'];
        $partidas = unserialize($_COOKIE['partidas']);
        $total = 0;
        $sub_total = 0;
        $total_impuesto_trasladados = 0;
        $total_impuesto_retenidos = 0;
        $tipo_impuesto_trasladado = false;
        $tipo_impuesto_retenido = false;
        $impuesto_trasladado_codigo = false;
        $impuesto_retenido_codigo = false;
        $tipo_factor_trasladado = false;
        $tipo_factor_retenido = false;
        $factor_trasladado = 0;
        $factor_retenido = 0;
        $total_impuesto_trasladados_iva = 0;


        foreach ($partidas as $partida){
            $valor_unitario = $partida['valor_unitario'];
            $cantidad = $partida['cantidad'];
            $insumo_id = $partida['insumo_id'];
            $subtotal_partida = $valor_unitario * $cantidad;
            $insumo_modelo = new insumo($this->link);
            $resultado = $insumo_modelo->obten_por_id('insumo',$insumo_id);
            $insumo = $resultado['registros'][0];
            $impuesto_trasladado_factor = $insumo['insumo_factor'];
            $impuesto_retenido_factor = $insumo['insumo_factor_retenido'];
            $monto_impuesto_trasladado = $impuesto_trasladado_factor * $subtotal_partida;
            $monto_impuesto_retenido = $impuesto_retenido_factor * $subtotal_partida;
            $total_partida = round($subtotal_partida + $monto_impuesto_trasladado - $monto_impuesto_retenido,2);
            $total = round($total,2) + round($total_partida,2);
            $sub_total = round($sub_total,2) + round($subtotal_partida,2);

            $total_impuesto_trasladados = $total_impuesto_trasladados + $monto_impuesto_trasladado;
            $total_impuesto_retenidos = $total_impuesto_retenidos + $monto_impuesto_retenido;


            if($insumo['impuesto_descripcion'] == 'IVA'){

                $total_impuesto_trasladados_iva = $total_impuesto_trasladados_iva + $monto_impuesto_trasladado;
            }

            if(!$tipo_impuesto_trasladado) {
                $tipo_impuesto_trasladado = $insumo['impuesto_descripcion'];
                $impuesto_trasladado_codigo = $insumo['impuesto_codigo'];
                $tipo_factor_trasladado = $insumo['tipo_factor_codigo'];
                $factor_trasladado = $insumo['insumo_factor'];



            }
            if(!$tipo_impuesto_retenido) {
                $tipo_impuesto_retenido = $insumo['impuesto_retenido_descripcion'];
                $impuesto_retenido_codigo = $insumo['impuesto_retenido_codigo'];
                $tipo_factor_retenido = $insumo['tipo_factor_retenido_codigo'];
                $factor_retenido = $insumo['insumo_factor_retenido'];
                if(!$factor_retenido){
                    $factor_retenido = 0;
                }
            }

        }



        $total = number_format($total,2,'.','');
        $total_impuesto_trasladados = number_format($total_impuesto_trasladados,2,'.','');
        $datos_guardar['total'] = $total;

        $datos_guardar['moneda_id'] = $_COOKIE['cliente_moneda_id'];
        $moneda_modelo = new moneda($this->link);
        $resultado = $moneda_modelo->obten_por_id('moneda',$_COOKIE['cliente_moneda_id']);
        $moneda = $resultado['registros'][0];
        $datos_guardar['moneda_codigo'] = $moneda['moneda_codigo'];
        $datos_guardar['moneda_descripcion'] = $moneda['moneda_descripcion'];
        $datos_guardar['sub_total'] = $sub_total;
        $datos_guardar['condiciones_pago'] = $_COOKIE['cliente_condiciones_pago'];


        $datos_guardar['forma_pago_id'] = $_COOKIE['cliente_forma_pago_id'];
        $forma_pago_modelo = new metodo_pago($this->link);
        $resultado = $forma_pago_modelo->obten_por_id('forma_pago',$_COOKIE['cliente_forma_pago_id']);
        $forma_pago = $resultado['registros'][0];
        $datos_guardar['forma_pago_codigo'] = $forma_pago['forma_pago_codigo'];
        $datos_guardar['forma_pago_descripcion'] = $forma_pago['forma_pago_descripcion'];



        $fecha = $_COOKIE['fecha'];
        $hora = date('H:i:s');
        $fecha_emision = $fecha.'T'.$hora;

        $datos_guardar['fecha'] = $fecha_emision;

        $datos_guardar['rfc_emisor'] = $datos_empresa['rfc'];
        $datos_guardar['nombre_emisor'] = $datos_empresa['razon_social'];
        $datos_guardar['regimen_fiscal_emisor_codigo'] = $datos_empresa['regimen_fiscal'];
        $datos_guardar['regimen_fiscal_emisor_descripcion'] = $datos_empresa['regimen_fiscal_descripcion'];
        $datos_guardar['cliente_id'] = $_COOKIE['cliente_id'];


        $cliente_modelo = new cliente($this->link);
        $datos_actualiza_cliente = array();
        $datos_actualiza_cliente['uso_cfdi_id'] = $_COOKIE['cliente_uso_cfdi_id'];
        $datos_actualiza_cliente['moneda_id'] = $_COOKIE['cliente_moneda_id'];
        $datos_actualiza_cliente['forma_pago_id'] = $_COOKIE['cliente_forma_pago_id'];
        $datos_actualiza_cliente['metodo_pago_id'] = $_COOKIE['cliente_metodo_pago_id'];
        $datos_actualiza_cliente['status'] = 1;

        $cliente_modelo->modifica_bd($datos_actualiza_cliente,'cliente',$_COOKIE['cliente_id']);

        $resultado = $cliente_modelo->obten_por_id('cliente',$_COOKIE['cliente_id']);
        $cliente = $resultado['registros'][0];

        $datos_guardar['cliente_rfc'] = $cliente['cliente_rfc'];
        $datos_guardar['cliente_razon_social'] = $cliente['cliente_razon_social'];


        $datos_guardar['uso_cfdi_id'] = $_COOKIE['cliente_uso_cfdi_id'];
        $uso_cfdi_modelo = new metodo_pago($this->link);
        $resultado = $uso_cfdi_modelo->obten_por_id('uso_cfdi',$_COOKIE['cliente_uso_cfdi_id']);
        $uso_cfdi = $resultado['registros'][0];
        $datos_guardar['uso_cfdi_codigo'] = $uso_cfdi['uso_cfdi_codigo'];
        $datos_guardar['uso_cfdi_descripcion'] = $uso_cfdi['uso_cfdi_descripcion'];

        $datos_guardar['total_impuestos_trasladados'] = $total_impuesto_trasladados;
        $datos_guardar['total_impuestos_retenidos'] = $total_impuesto_retenidos;
        $datos_guardar['tipo_impuesto_trasladado'] = $tipo_impuesto_trasladado;
        $datos_guardar['tipo_impuesto_retenido'] = $tipo_impuesto_retenido;
        $datos_guardar['status'] = 1;
        $datos_guardar['status_factura'] = 'sin timbrar';
        $datos_guardar['saldo'] = number_format(round($total,2),2,'.','');

        $datos_guardar['impuesto_trasladado_codigo'] = $impuesto_trasladado_codigo;
        $datos_guardar['impuesto_retenido_codigo'] = $impuesto_retenido_codigo;
        $datos_guardar['impuesto_trasladado_descripcion'] = $tipo_impuesto_trasladado;
        $datos_guardar['impuesto_retenido_descripcion'] = $tipo_impuesto_retenido;
        $datos_guardar['tipo_factor_trasladado'] = $tipo_factor_trasladado;
        $datos_guardar['tipo_factor_retenido'] = $tipo_factor_retenido;

        $datos_guardar['factor_trasladado'] = $factor_trasladado;


        $datos_guardar['factor_retenido'] = $factor_retenido;

        $facturas = new facturas($this->link);
        $folio = $facturas->genera_folio($datos_empresa['sufijo_folio'],'factura',$datos_empresa['folio_inicial']);

        $datos_guardar['referencia'] = $folio;
        $datos_guardar['folio'] = $folio;

        if(isset($datos_empresa['ref_factura'])){
            if($datos_empresa['ref_factura'] =='folio_factura'){
                $datos_guardar['referencia'] = $folio;
            }
        }

        $datos_guardar['total_impuestos_trasladados_iva'] = number_format(round($total_impuesto_trasladados_iva,2),2,'.','');


        $resultado = $factura_modelo->alta_bd($datos_guardar,'factura');
        $factura_id = $resultado['registro_id'];



        foreach ($partidas as $partida) {
            $valor_unitario = $partida['valor_unitario'];
            $cantidad = $partida['cantidad'];
            $insumo_id = $partida['insumo_id'];
            $subtotal_partida = $valor_unitario * $cantidad;
            $insumo_modelo = new Insumo($this->link);
            $resultado = $insumo_modelo->obten_por_id('insumo', $insumo_id);
            $insumo = $resultado['registros'][0];
            $impuesto_trasladado_factor = $insumo['insumo_factor'];
            $impuesto_retenido_factor = $insumo['insumo_factor_retenido'];
            $monto_impuesto_trasladado = $impuesto_trasladado_factor * $subtotal_partida;
            $monto_impuesto_retenido = $impuesto_retenido_factor * $subtotal_partida;
            $total_partida = round($subtotal_partida + $monto_impuesto_trasladado - $monto_impuesto_retenido, 2);
            $total = round($total, 2) + round($total_partida, 2);
            $sub_total = round($sub_total, 2) + round($subtotal_partida, 2);
            $total_impuesto_retenidos = $total_impuesto_retenidos + $monto_impuesto_retenido;

            $partida_modelo = new Partida_Factura($this->link);

            $datos_guarda_partida = array();
            $datos_guarda_partida['insumo_id'] = $insumo_id;
            $datos_guarda_partida['factura_id'] = $factura_id;
            $datos_guarda_partida['insumo_descripcion'] = $insumo['insumo_descripcion'];
            $datos_guarda_partida['producto_sat_codigo'] = $insumo['producto_sat_codigo'];
            $datos_guarda_partida['producto_sat_descripcion'] = $insumo['producto_sat_descripcion'];
            $datos_guarda_partida['unidad_codigo'] = $insumo['unidad_codigo'];
            $datos_guarda_partida['unidad_descripcion'] = $insumo['unidad_descripcion'];
            $datos_guarda_partida['unidad'] = $insumo['unidad_descripcion'];
            $datos_guarda_partida['descripcion'] = $insumo['insumo_descripcion'];
            $datos_guarda_partida['no_identificacion'] = $insumo['insumo_id'];
            $datos_guarda_partida['valor_unitario'] = $valor_unitario;
            $datos_guarda_partida['cantidad'] = $cantidad;
            $datos_guarda_partida['importe'] = number_format(round($subtotal_partida,2),2,'.','');
            $datos_guarda_partida['base'] = number_format(round($subtotal_partida,2),2,'.','');
            $datos_guarda_partida['base_ieps'] = number_format(round($subtotal_partida,2),2,'.','');


            $resultado = $partida_modelo->alta_bd($datos_guarda_partida,'partida_factura');

        }
        setcookie('partidas','');
        header("Location: ./index.php?seccion=factura&accion=genera_xml&factura_id=$factura_id");
        exit;
    }

    public function guarda_insumo(){
        $insumo_modelo = new Insumo($this->link);

        if($_POST['impuesto_retenido_id'] == ''){
            unset($_POST['impuesto_retenido_id']);
        }
        if($_POST['tipo_factor_retenido_id'] == ''){
            unset($_POST['tipo_factor_retenido_id']);
        }
        if($_POST['factor_retenido'] == ''){
            unset($_POST['factor_retenido']);
        }
        $_POST['status'] = 1;

        $resultado = $insumo_modelo->alta_bd($_POST,'insumo');
        echo json_encode($resultado);
        header('Content-Type: application/json');
    }

    public function guarda_partida(){

        $partidas = array();

        if(!isset($_COOKIE['partidas'])){
            $partidas[] = $_POST;
            setcookie('partidas',serialize($partidas));
        }
        else{
            $partidas = unserialize($_COOKIE['partidas']);
            $partidas[] = $_POST;
            setcookie('partidas', serialize($partidas));
        }

    }

    public function informe_gastos_pdf(){
        $tamano_letra_normal = 7;
        $tamano_letra_titulo = 9;

        $factura_modelo = new Factura($this->link);
        $pdf = new my_pdf();
        $factura_id = $_GET['factura_id'];
        $resultado = $factura_modelo->obten_por_id('factura',$factura_id);
        $factura = $resultado['registros'][0];

        $nombre_empresa = $factura['factura_nombre_emisor'];
        $folio = 'I-'.$factura['factura_folio'];
        $fecha = $factura['factura_fecha'];
        $referencia = $factura['factura_referencia'];
        $pedimento = $factura['factura_pedimento'];

        $tipo_cambio = $factura['factura_tipo_cambio'];



        $cliente_razon_social = $factura['cliente_razon_social'];
        $cliente_direccion = $factura['cliente_direccion'];
        $cliente_rfc = $factura['cliente_rfc'];
        $cliente_colonia = $factura['cliente_colonia'];
        $cliente_cp = $factura['cliente_cp'];
        $cliente_zica_ciudad = $factura['cliente_zica_cuidad'];
        $marcas = $factura['factura_marcas'];
        $bultos = $factura['factura_bultos'];
        $descuento = $factura['factura_descuento'];
        $clase_descripcion = $factura['factura_clase_descripcion'];
        $peso = $factura['factura_peso'];
        $ref_facturas = $factura['factura_ref_facturas'];
        $proveedor_razon_social = $factura['factura_proveedor_razon_social'];



        $total_ph = $factura['factura_total_ph'];
        $valor_aduana = $factura['factura_valor_aduana'];





        $honorarios = $factura['factura_honorarios'];

        if($tipo_cambio!=0){
            $total_ph = round($total_ph/$tipo_cambio,2);
            $valor_aduana = round($valor_aduana/$tipo_cambio,2);
            $honorarios = round($honorarios/$tipo_cambio,2);
        }

        $sub_total = $factura['factura_sub_total'];
        $iva = $factura['factura_total_impuestos_trasladados'];
        $total_neto = $factura['factura_total'];
        $anticipo = $factura['factura_anticipo'];
        $observaciones = $factura['factura_observaciones'];
        $phonorarios = $factura['factura_phonorarios'];


        $total_cuenta = $total_neto+$total_ph;

        $saldo_cuenta = $total_cuenta - $anticipo;

        $empresa = new Empresas();
        $datos_empresa=$empresa->empresas[$_SESSION['numero_empresa']];

        $encabezado_1 = $datos_empresa['encabezado_1'];
        $encabezado_2 = $datos_empresa['encabezado_2'];
        $encabezado_3 = $datos_empresa['encabezado_3'];
        $encabezado_4 = $datos_empresa['encabezado_4'];
        $leyenda_docto = $datos_empresa['leyenda_docto'];


        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Courier','',$tamano_letra_titulo);
        $pdf->Cell(190,7,utf8_decode($nombre_empresa),0,1,'C');
        $pdf->Cell(190,5,utf8_decode($encabezado_1),0,1,'C');
        $pdf->Cell(190,5,utf8_decode($encabezado_2),0,1,'C');

        $pdf->Ln();
        $pdf->SetFont('Courier','',$tamano_letra_normal);
        $pdf->MultiCell(90,5,utf8_decode($encabezado_3),'B','C');
        $pdf->SetXY(110,32);
        $pdf->MultiCell(90,5,utf8_decode($encabezado_4),'B','C');
        $pdf->Ln();

        $ruta_base = $datos_empresa['nombre_base_datos'];
        $ruta_logo = $ruta_base.'/logo.png';

        $pdf->Image($ruta_logo,18,7 ,15);


        $pdf->SetFont('Courier','',$tamano_letra_normal);
        $pdf->MultiCell(190,5,utf8_decode($leyenda_docto),'B','C');

        $txt = 'Informe de Gastos: '.$folio;
        $txt = $txt.' | Fecha: '.$fecha;
        $txt = $txt.' | Referencia: '.$referencia;
        $txt = $txt.' | Pedimento: '.$pedimento;

        $pdf->MultiCell(190,5,utf8_decode($txt),0,'L');


        $pdf->SetFillColor(190);
        $pdf->SetFont('Courier','',$tamano_letra_titulo);
        $pdf->Cell(190,5,'Datos del cliente:',0,1,'C',true);

        $pdf->SetFont('Courier','',$tamano_letra_normal);
        $txt = 'Razón Social: '.$cliente_razon_social;
        $txt = $txt.' | Dirección: '.$cliente_direccion.' '.$cliente_colonia.', '.$cliente_cp;
        $txt = $txt.', '.$cliente_zica_ciudad;
        $txt = $txt.' | RFC: '.$cliente_rfc;
        $pdf->MultiCell(190,5,utf8_decode($txt),0,'L');

        $pdf->SetFont('Courier','',$tamano_letra_titulo);
        $pdf->Cell(190,5,utf8_decode('Datos de la mercancía:'),0,1,'C',true);


        $pdf->SetFont('Courier','',$tamano_letra_normal);

        $txt = 'Marcas y Números: '.$marcas;
        $txt = $txt.' | Bultos: '.$bultos;
        $txt = $txt.' | Clase y Descripción: '.$clase_descripcion;
        $txt = $txt.' | Peso: '.$peso;
        $txt = $txt.' | Facturas: '.$ref_facturas;
        $txt = $txt.' | Proveedor: '.$proveedor_razon_social;

        $pdf->MultiCell(190,5,utf8_decode($txt),0,'L');


        $pdf->SetFont('Courier','',$tamano_letra_titulo);
        $pdf->Cell(190,5,utf8_decode('Pagos Hechos por su Cuenta:'),0,1,'C',true);

        $pdf->SetFont('Courier','',$tamano_letra_normal);
        $pdf->Cell(150,5,utf8_decode('Descripción:'),1,0,'L');
        $pdf->Cell(40,5,utf8_decode('Importe:'),1,1,'L');


        $partida_gasto_modelo = new modelos($this->link);

        $filtro_partida_gasto = array('folio'=>$factura['factura_folio']);
        $resultado_partida_gasto = $partida_gasto_modelo->filtro_and('partida_informe_gasto',$filtro_partida_gasto);

        $partidas_gastos = $resultado_partida_gasto['registros'];

        $pdf->SetFont('Courier','',$tamano_letra_normal);
        foreach ($partidas_gastos as $partida_gasto){
            $partida_informe_gasto_importe = round($partida_gasto['partida_informe_gasto_importe'],2);

            if($tipo_cambio!=0){
                $partida_informe_gasto_importe = round($partida_informe_gasto_importe/$tipo_cambio,2);
            }


            $pdf->Cell(150,5,utf8_decode($partida_gasto['partida_informe_gasto_descripcion']),'B',0,'L');
            $pdf->Cell(40,5,utf8_decode('$'.number_format($partida_informe_gasto_importe,2,'.',',')),'B',1,'L');
        }
        $pdf->Cell(150,5,utf8_decode('Total PH(1): '),'B',0,'R');
        $pdf->Cell(40,5,utf8_decode('$'.number_format($total_ph,2,'.',',')),'B',1,'L');


        $pdf->MultiCell(190,5,'Observaciones:',0,'C');
        $pdf->Ln();
        $pdf->MultiCell(190,5,$observaciones,1,'L');
        $pdf->Ln();

        $pdf->SetFont('Courier','',$tamano_letra_titulo);
        $pdf->Cell(190,5,utf8_decode('Servicios de Agente Aduanal:'),0,1,'C',true);

        $pdf->SetFont('Courier','B',$tamano_letra_normal);

        $pdf->Cell(190,5,utf8_decode('Cálculo de Honorarios:'),'B',1,'L');

        $pdf->SetFont('Courier','',$tamano_letra_normal);

        $pdf->Cell(120,5,utf8_decode('Valor Normal:'),0,0,'L');
        $pdf->Cell(70,5,utf8_decode('$'.number_format($valor_aduana,2,'.',',')),0,1,'L');

        $pdf->Cell(120,5,utf8_decode('Total de Pagos Hechos por su Cuenta:'),0,0,'L');
        $pdf->Cell(70,5,utf8_decode('$'.number_format($total_ph,2,'.',',')),0,1,'L');

        $base_calculo = $total_ph + $valor_aduana;

        $pdf->Cell(120,5,utf8_decode('Base para el Cálculo:'),0,0,'L');
        $pdf->Cell(70,5,utf8_decode('$'.number_format($base_calculo,2,'.',',')),0,1,'L');



        $pdf->Cell(120,5,utf8_decode("Honorarios al $phonorarios %:"),0,0,'L');
        $pdf->Cell(70,5,utf8_decode('$'.number_format($honorarios,2,'.',',')),0,1,'L');

        $pdf->Cell(120,5,utf8_decode("Descuento:"),0,0,'L');
        $pdf->Cell(70,5,utf8_decode('$'.number_format($descuento,2,'.',',')),0,1,'L');



        $pdf->Cell(20,5,utf8_decode('Cant'),1,0,'C');
        $pdf->Cell(30,5,utf8_decode('U.M.'),1,0,'C');
        $pdf->Cell(65,5,utf8_decode('Descripcion'),1,0,'C');
        $pdf->Cell(30,5,utf8_decode('Precio Unitario'),1,0,'C');
        $pdf->Cell(15,5,utf8_decode('Desc'),1,0,'C');
        $pdf->Cell(30,5,utf8_decode('Importe'),1,1,'C');


        $partidas_modelos = new modelos($this->link);

        $filtro_partida = array('folio'=>$factura['factura_folio']);
        $resultado_partida = $partidas_modelos->filtro_and('partida_factura',$filtro_partida);

        $partidas = $resultado_partida['registros'];

        $pdf->SetFont('Courier','',$tamano_letra_normal);
        foreach ($partidas as $partida){
            $pdf->Cell(20,5,utf8_decode($partida['partida_factura_cantidad']),'B',0,'C');
            $pdf->Cell(30,5,utf8_decode($partida['partida_factura_unidad_descripcion']),'B',0,'C');
            $pdf->Cell(65,5,utf8_decode($partida['partida_factura_descripcion']),'B',0);
            $pdf->Cell(30,5,utf8_decode('$'.number_format($partida['partida_factura_valor_unitario'],2,'.',',')),'B',0,'L');
            $pdf->Cell(15,5,utf8_decode('$'.number_format((float)$partida['partida_factura_descuento'],2,'.',',')),'B',0,'L');
            $pdf->Cell(30,5,utf8_decode('$'.number_format($partida['partida_factura_importe']-$partida['partida_factura_descuento'],2,'.',',')),'B',1,'L');
        }
        $pdf->SetFont('Courier','B',$tamano_letra_normal);
        $pdf->Cell(160,5,utf8_decode('TOTAL HONORARIOS / SERVICIOS COMPLEMENTARIOS: '),'B',0,'R');
        $pdf->Cell(30,5,utf8_decode('$'.number_format($sub_total-$descuento,2,'.',',')),'B',1,'L');
        $pdf->Cell(160,5,utf8_decode('IVA 16%: '),'B',0,'R');
        $pdf->Cell(30,5,utf8_decode('$'.number_format($iva,2,'.',',')),'B',1,'L');
        $pdf->Cell(160,5,utf8_decode('SUMA(2): '),'B',0,'R');
        $pdf->Cell(30,5,utf8_decode('$'.number_format($total_neto,2,'.',',')),'B',1,'L');
        $pdf->Cell(160,5,utf8_decode('TOTAL DE ESTA CUENTA (1) + (2):'),'B',0,'R');
        $pdf->Cell(30,5,utf8_decode('$'.number_format($total_cuenta,2,'.',',')),'B',1,'L');
        $pdf->Cell(160,5,utf8_decode('ANTICIPOS A ESTA CUENTA:'),'B',0,'R');
        $pdf->Cell(30,5,utf8_decode('$'.number_format($anticipo,2,'.',',')),'B',1,'L');
        $pdf->Cell(160,5,utf8_decode('SALDO DE ESTA CUENTA:'),'B',0,'R');
        $pdf->Cell(30,5,utf8_decode('$'.number_format($saldo_cuenta,2,'.',',')),'B',1,'L');

        $numeros = new NumeroTexto();
        $moneda_codigo = $factura['factura_moneda_codigo'];
        $moneda_letra = array('MXN'=>'PESOS','USD'=>'DOLARES');
        $moneda_letra_enviar = $moneda_letra[$moneda_codigo];

        $importe_texto = $numeros->to_word(($saldo_cuenta-$descuento), $moneda_letra_enviar);

        $pdf->MultiCell(190,5,utf8_decode('Cantidad con letra: '.$importe_texto.' '.$moneda_codigo),'B','C');


        $ruta_logo = './img/iso.jpg';

        $pdf->Line(10,258,200,258);
        $pdf->Image($ruta_logo,10,260 ,25);
        $pdf->SetY(265);
        $pdf->Cell(190,5,'Certificado: 102613-2011-AQ-MEX-EMA ISO - 9001',0,1,'C');

        $pdf->Line(10,282,200,282);

        $empresas = new Empresas();
        $datos_empresa = $empresas->empresas[$_SESSION['numero_empresa']];
        $referencia_ig = $datos_empresa['referencia_if'];

        $referencia = $factura['factura_referencia'];


        $pdf->Output('D', $referencia_ig.'_'.$referencia.'.pdf');

    }

    public function lista(){

        $factura_modelo = new factura($this->link);
        $breadcrumbs = array('alta');
        $this->breadcrumbs = $this->directiva->nav_breadcumbs(12, 0, $breadcrumbs);
        $resultado = $factura_modelo->obten_registros(tabla: $_GET['seccion'], limit:' LIMIT 300 ' );
        if(errores::$error){
            $error = $this->error_->error('Error al obtener registros', $resultado);
            print_r($error);
            die('Error');
        }
        $this->registros = $resultado['registros'];
        if(isset($resultado['mensaje'])) {
            $this->mensaje = $resultado['mensaje'];
        }
        if(isset($this->error)) {
            $this->error = $resultado['error'];
        }
    }

    public function lista_filtro(){
        $fecha = false;
        if(isset($_POST['fecha'])){
            $fecha = $_POST['fecha'];
        }
        if($fecha == ''){
            $fecha = false;
        }


        $rfc = false;
        if(isset($_POST['rfc'])){
            $rfc = $_POST['rfc'];
        }
        if($rfc == ''){
            $rfc = false;
        }

        $folio = false;
        if(isset($_POST['folio'])){
            $folio = $_POST['folio'];
        }
        if($folio == ''){
            $folio = false;
        }




        $razon_social = false;
        if(isset($_POST['razon_social'])){
            $razon_social = $_POST['razon_social'];
        }
        if($razon_social == ''){
            $razon_social = false;
        }



        $status_factura = false;
        if(isset($_POST['status_factura'])){
            $status_factura = $_POST['status_factura'];
        }
        if($status_factura == ''){
            $status_factura = false;
        }

        $status_descarga = false;
        if(isset($_POST['status_descarga'])){
            $status_descarga = $_POST['status_descarga'];
        }
        if($status_descarga == ''){
            $status_descarga = false;
        }


        $modelo_factura = new Factura($this->link);
        $resultado = $modelo_factura->obten_registros_filtrados('factura', $fecha, $rfc, $razon_social,
            $status_factura,$status_descarga, $folio);

        $this->registros = $resultado['registros'];


    }

    public function modifica(){
        $factura_id = $_GET['registro_id'];
        setcookie('url_ejecucion', "./index.php?seccion=factura&accion=modifica&registro_id=$factura_id&session_id=".SESSION_ID);
        $this->obten_datos_emisor();
        $this->obten_datos_receptor($_GET['registro_id']);
        $this->obten_datos_comprobante($_GET['registro_id']);
        $this->obten_partidas($_GET['registro_id']);
        $this->obten_impuestos($_GET['registro_id']);
        $numero = new NumeroTexto();
        $this->numero_texto = $numero->to_word($this->datos_impuestos['total']);
        $this->factura_id = $factura_id;
        setcookie('factura_id', $this->factura_id);
    }

    public function relaciona_factura_bd(){
        $tipo_relacion_id = $_POST['tipo_relacion_id'];
        $factura_id = $_POST['factura_id'];

        $facturas_ids = $_POST['facturas_id'];

        $factura_modelo = new factura($this->link);

        $factura_upd['status'] = 1;
        $factura_upd['tipo_relacion_id'] = $tipo_relacion_id;

        $factura_modelo->modifica_bd($factura_upd,'factura',$factura_id);

        $factura_relacionada_modelo = new factura_relacionada($this->link);

        $filtro = array('factura_id'=>$factura_id);

        $factura_relacionada_modelo->elimina_con_filtro_and('factura_relacionada',$filtro);

        foreach($facturas_ids as $factura_relacionada_id){

            $factura_ins = array();
            $factura_ins['factura_id'] = $factura_id;
            $factura_ins['factura_rel_id'] = $factura_relacionada_id;


            $r = $factura_relacionada_modelo->alta_bd($factura_ins,'factura_relacionada');



        }

        header('Location: index.php?seccion=cliente&accion=vista_preliminar_factura&tipo_mensaje=exito&mensaje=Se han relacioonado las facturas&factura_id='.$factura_id.'&session_id='.SESSION_ID);
        exit;

    }

    public function relaciona_facturas(){
        $factura_id = $_GET['factura_id'];
        $factura_modelo = new Factura($this->link);


        $r_factura = $factura_modelo->obten_por_id('factura',$factura_id);

        $this->factura = $r_factura['registros'][0];

        $cliente_id = $this->factura['cliente_id'];


        $filtro = array('cliente.id'=>$cliente_id);

        $r_factura = $factura_modelo->filtro_and('factura',$filtro);

        $this->facturas = $r_factura['registros'];


    }

    public function modifica_generales_factura(){
        $factura_modelo = new Factura($this->link);
        $_POST['status'] = 1;
        $factura_modelo->modifica_bd($_POST,'factura',$_GET['factura_id']);
        $mensaje = 'Modificado con exito';
        header('Location: index.php?seccion=factura&accion=modifica&tipo_mensaje=exito&mensaje='.$mensaje.'&registro_id='.$_GET['factura_id'].'&session_id='.SESSION_ID);
        exit;
    }

    public function modifica_partida(){
        $partida_factura_id = $_GET['partida_factura_id'];

        $partida_factura_modelo = new Partida_Factura($this->link);
        $_POST['status'] = 1;
        $partida_factura_modelo->modifica_bd($_POST,'partida_factura', $partida_factura_id);

        $resultado = $partida_factura_modelo->obten_por_id('partida_factura', $partida_factura_id);

        $factura_id = $resultado['registros'][0]['factura_id'];


        header('Location: ./index.php?seccion=factura&accion=modifica&registro_id='.$factura_id.'&mensaje=Modifcado con exito&tipo_mensaje=exito');
        exit;
    }

    public function obten_clientes(){
        $this->cliente_id = $_GET['cliente_id'];
    }

    public function obten_datos($tabla){
        $modelo = new $tabla($this->link);
        $campo_id = $tabla.'_id';
        $registro_id = $_POST[$campo_id];
        $resultado = $modelo->obten_por_id($tabla,$registro_id);
        $datos = $resultado['registros'][0];
        return $datos;
    }
    
    private function obten_datos_comprobante($factura_id=false){
        if(!$factura_id){
            $factura_id = $this->factura_id;
        }
        $factura_modelo = New Factura($this->link);
        $resultado = $factura_modelo->obten_por_id('factura',$factura_id);
        $factura = $resultado['registros'][0];
        $this->datos_comprobante['cp'] = $factura['factura_lugar_expedicion'];
        $this->datos_comprobante['observaciones'] = $factura['factura_observaciones'];
        $this->datos_comprobante['fecha'] = $factura['factura_fecha'];
        $this->datos_comprobante['tipo_comprobante_codigo'] = $factura['factura_tipo_comprobante_codigo'];
        $this->datos_comprobante['tipo_comprobante_descripcion'] = $factura['factura_tipo_comprobante_descripcion'];
        $this->datos_comprobante['folio'] = $factura['factura_folio'];
        $this->datos_comprobante['serie'] = $factura['factura_serie'];
        $this->datos_comprobante['moneda_codigo'] = $factura['factura_moneda_codigo'];
        $this->datos_comprobante['moneda_descripcion'] = $factura['factura_moneda_descripcion'];

        $this->datos_comprobante['forma_pago_codigo'] = $factura['factura_forma_pago_codigo'];
        $this->datos_comprobante['forma_pago_descripcion'] = $factura['factura_forma_pago_descripcion'];

        $this->datos_comprobante['metodo_pago_codigo'] = $factura['factura_metodo_pago_codigo'];
        $this->datos_comprobante['metodo_pago_descripcion'] = $factura['factura_metodo_pago_descripcion'];

        $this->datos_comprobante['sub_total'] = $factura['factura_sub_total'];
        $this->status_factura = $factura['factura_status_factura'];
    }

    private function obten_datos_emisor(){
        $empresa = New Empresas();
        $datos_empresa = $empresa->empresas[$_SESSION['numero_empresa']];
        $this->datos_emisor['nombre_empresa'] = $datos_empresa['nombre_empresa'];
        $this->datos_emisor['rfc_emisor'] = $datos_empresa['rfc'];
        $this->datos_emisor['uso_cfdi_codigo'] = $datos_empresa['rfc'];
        $this->datos_emisor['regimen_fiscal_codigo'] = $datos_empresa['regimen_fiscal'];
        $this->datos_emisor['regimen_fiscal_descripcion'] = $datos_empresa['regimen_fiscal_descripcion'];
    }

    public function obten_datos_facturacion(){
        $this->cliente_id = false;
        if(isset($_COOKIE['cliente_id'])){
            $this->cliente_id = $_COOKIE['cliente_id'];
        }

        $cliente_modelo = new Cliente($this->link);
        $resultado = $cliente_modelo->obten_por_id('cliente',$this->cliente_id);

        $cliente = $resultado['registros'][0];

        $this->cliente_uso_cfdi_id = $cliente['uso_cfdi_id'];
        $this->cliente_moneda_id = $cliente['moneda_id'];
        $this->cliente_forma_pago_id = $cliente['forma_pago_id'];
        $this->cliente_metodo_pago_id = $cliente['metodo_pago_id'];
        $this->cliente_condiciones_pago = $cliente['cliente_dias_credito'];
        if(isset($_COOKIE['fecha'])){
            $this->fecha = $_COOKIE['fecha'];
        }
        else{
            $this->fecha = date('Y-m-d');
        }


        if($this->cliente_condiciones_pago == ''){
            $this->cliente_condiciones_pago = 'Contado';
        }

    }

    private function obten_datos_receptor($factura_id=false){
        if(!$factura_id){
            $factura_id = $this->factura_id;
        }
        $factura_modelo = New Factura($this->link);
        $resultado = $factura_modelo->obten_por_id('factura',$factura_id);
        $factura = $resultado['registros'][0];
        $this->datos_receptor['razon_social'] = $factura['cliente_razon_social'];
        $this->datos_receptor['rfc'] = $factura['cliente_rfc'];
        $this->datos_receptor['uso_cfdi_codigo'] = $factura['factura_uso_cfdi_codigo'];
        $this->datos_receptor['uso_cfdi_descripcion'] = $factura['factura_uso_cfdi_descripcion'];
    }

    public function obten_datos_insumo(){
        $insumo_id = $_GET['insumo_id'];

        $insumo_modelo = new Insumo($this->link);
        $resultado = $insumo_modelo->obten_por_id('insumo',$insumo_id);
        $insumos = $resultado['registros'][0];
        echo json_encode($insumos);
        header('Content-Type: application/json');
    }

    private function obten_impuestos($factura_id=false){
        if(!$factura_id){
            $factura_id = $this->factura_id;
        }

        $factura_modelo = New Factura($this->link);
        $resultado = $factura_modelo->obten_por_id('factura',$factura_id);
        $factura = $resultado['registros'][0];
        $this->datos_impuestos['total_impuestos_trasladados'] = $factura['factura_total_impuestos_trasladados'];
        $this->datos_impuestos['impuesto_traslado_descripcion'] = $factura['factura_tipo_impuesto_trasladado'];
        $this->datos_impuestos['tasa_cuota_trasladado'] = $factura['factura_factor_trasladado'];

        $this->datos_impuestos['total_impuestos_retenidos'] = $factura['factura_total_impuestos_retenidos'];
        $this->datos_impuestos['impuesto_retenido_descripcion'] = $factura['factura_tipo_impuesto_retenido'];
        $this->datos_impuestos['tasa_cuota_retenido'] = $factura['factura_factor_retenido'];

        $this->datos_impuestos['total'] = $factura['factura_total'];

    }

    public function obten_insumos(){
        $this->insumo_id = $_GET['insumo_id'];
    }

    private function obten_partidas($factura_id=false){
        
        if(!$factura_id){
            $factura_id = $this->factura_id;
        }
        $modelo_partida = new Partida_Factura($this->link);
        $filtro = array('factura_id'=>$factura_id);
        $resultado = $modelo_partida->filtro_and('partida_factura', $filtro);
        $this->partidas = $resultado['registros'];
    }

    public function obten_pdf_cancelado(){

        $tamano_letra_normal = 7;
        $tamano_letra_titulo = 9;

        $factura_modelo = new Factura($this->link);
        $pdf = new PDF_HTML();
        $factura_id = $_GET['factura_id'];
        $resultado = $factura_modelo->obten_por_id('factura',$factura_id);
        $factura = $resultado['registros'][0];

        $nombre_empresa = $factura['factura_nombre_emisor'];
        $folio = $factura['factura_folio'];
        $fecha = $factura['factura_fecha'];
        $uuid_cancelacion = $factura['factura_uuid_cancelacion'];



        $cliente_razon_social = $factura['cliente_razon_social'];
        $cliente_direccion = $factura['cliente_calle'];
        $cliente_rfc = $factura['cliente_rfc'];
        $cliente_colonia = $factura['cliente_colonia'];
        $cliente_cp = $factura['cliente_cp'];
        $cliente_municipio = $factura['municipio_descripcion'];
        $ref_facturas = $factura['factura_uuid'];



        $sub_total = $factura['factura_sub_total'];
        $iva = $factura['factura_total_impuestos_trasladados'];
        $total_neto = $factura['factura_total'];
        $anticipo = $factura['factura_anticipo'];
        $observaciones = $factura['factura_observaciones'];


        $empresa = new Empresas();
        $datos_empresa=$empresa->empresas[$_SESSION['numero_empresa']];

        $encabezado_1 = $datos_empresa['encabezado_1'];
        $encabezado_2 = $datos_empresa['encabezado_2'];
        $encabezado_3 = $datos_empresa['encabezado_3'];
        $encabezado_4 = $datos_empresa['encabezado_4'];
        $leyenda_docto = $datos_empresa['leyenda_docto'];


        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Courier','',$tamano_letra_titulo);
        $pdf->Cell(190,7,utf8_decode($nombre_empresa),0,1,'C');
        $pdf->Cell(190,5,utf8_decode($encabezado_1),0,1,'C');
        $pdf->Cell(190,5,utf8_decode($encabezado_2),0,1,'C');

        $pdf->Ln();
        $pdf->SetFont('Courier','',$tamano_letra_normal);
        $pdf->MultiCell(90,5,utf8_decode($encabezado_3),'B','C');
        $pdf->SetXY(110,32);
        $pdf->MultiCell(90,5,utf8_decode($encabezado_4),'B','C');
        $pdf->Ln();

        $ruta_base = $datos_empresa['nombre_base_datos'];
        $ruta_logo = $ruta_base.'/logo.png';

        $pdf->Image($ruta_logo,18,7 ,15);


        $pdf->SetFont('Courier','',$tamano_letra_normal);
        $pdf->MultiCell(190,5,utf8_decode($leyenda_docto),'B','C');

        $pdf->SetFont('Courier','',$tamano_letra_titulo);
        $txt = 'Cancelacion de factura: '.$folio;
        $txt = $txt.' | Fecha: '.$fecha;

        $pdf->MultiCell(190,5,utf8_decode($txt),0,'L');
        $txt = ' | Folio Fiscal de Factura Cancelada: '.$ref_facturas;
        $pdf->MultiCell(190,5,utf8_decode($txt),0,'L');


        $txt = ' | Folio Fiscal de Cancelacion: '.$uuid_cancelacion;
        $pdf->MultiCell(190,5,utf8_decode($txt),0,'L');


        $pdf->SetFillColor(190);
        $pdf->SetFont('Courier','',$tamano_letra_titulo);
        $pdf->Cell(190,5,'Datos del cliente:',0,1,'C',true);

        $pdf->SetFont('Courier','',$tamano_letra_normal);
        $txt = 'Razón Social: '.$cliente_razon_social;
        $txt = $txt.' | Dirección: '.$cliente_direccion.' '.$cliente_colonia.', '.$cliente_cp;
        $txt = $txt.', '.$cliente_municipio;
        $txt = $txt.' | RFC: '.$cliente_rfc;
        $pdf->MultiCell(190,5,utf8_decode($txt),0,'L');






        $pdf->SetFont('Courier','',$tamano_letra_titulo);
        $pdf->Cell(190,5,utf8_decode('Pagos Hechos por su Cuenta:'),0,1,'C',true);

        $pdf->SetFont('Courier','',$tamano_letra_normal);
        $pdf->Cell(150,5,utf8_decode('Descripción:'),1,0,'L');
        $pdf->Cell(40,5,utf8_decode('Importe:'),1,1,'L');






        $pdf->SetFont('Courier','',$tamano_letra_normal);

        $pdf->MultiCell(190,5,'Observaciones:',0,'C');
        $pdf->Ln();
        $pdf->MultiCell(190,5,$observaciones,1,'L');
        $pdf->Ln();

        $pdf->SetFont('Courier','',$tamano_letra_titulo);
        $pdf->Cell(190,5,utf8_decode('Servicios de Agente Aduanal:'),0,1,'C',true);

        $pdf->SetFont('Courier','B',$tamano_letra_normal);

        $pdf->Cell(190,5,utf8_decode('Cálculo de Honorarios:'),'B',1,'L');

        $pdf->SetFont('Courier','',$tamano_letra_normal);





        $partidas_modelos = new Modelos($this->link);

        $filtro_partida = array('folio'=>$factura['factura_folio']);
        $resultado_partida = $partidas_modelos->filtro_and('partida_factura',$filtro_partida);

        $partidas = $resultado_partida['registros'];

        $pdf->SetFont('Courier','',$tamano_letra_normal);
        foreach ($partidas as $partida){
            $pdf->Cell(20,5,utf8_decode($partida['partida_factura_cantidad']),'B',0,'C');
            $pdf->Cell(30,5,utf8_decode($partida['partida_factura_unidad_descripcion']),'B',0,'C');
            $pdf->Cell(80,5,utf8_decode($partida['partida_factura_descripcion']),'B',0);
            $pdf->Cell(30,5,utf8_decode('$'.number_format($partida['partida_factura_valor_unitario'],2,'.',',')),'B',0,'L');
            $pdf->Cell(30,5,utf8_decode('$'.number_format($partida['partida_factura_importe'],2,'.',',')),'B',1,'L');
        }
        $pdf->SetFont('Courier','B',$tamano_letra_normal);
        $pdf->Cell(160,5,utf8_decode('TOTAL HONORARIOS / SERVICIOS COMPLEMENTARIOS: '),'B',0,'R');
        $pdf->Cell(30,5,utf8_decode('$'.number_format($sub_total,2,'.',',')),'B',1,'L');
        $pdf->Cell(160,5,utf8_decode('IVA 16%: '),'B',0,'R');
        $pdf->Cell(30,5,utf8_decode('$'.number_format($iva,2,'.',',')),'B',1,'L');
        $pdf->Cell(160,5,utf8_decode('SUMA(2): '),'B',0,'R');
        $pdf->Cell(30,5,utf8_decode('$'.number_format($total_neto,2,'.',',')),'B',1,'L');
        $pdf->Cell(160,5,utf8_decode('ANTICIPOS A ESTA CUENTA:'),'B',0,'R');
        $pdf->Cell(30,5,utf8_decode('$'.number_format($anticipo,2,'.',',')),'B',1,'L');



        $empresas = new Empresas();
        $datos_empresa = $empresas->empresas[$_SESSION['numero_empresa']];
        $referencia_ig = $datos_empresa['referencia_if'];

        $referencia = $factura['factura_referencia'];


        $pdf->Output('D', $referencia_ig.'_'.$referencia.'.pdf');

    }


    public function siguiente_partida(){
        $this->cliente_id = $_COOKIE['cliente_id'];
        $this->cliente_uso_cfdi_id = $_COOKIE['cliente_uso_cfdi_id'];
        $this->cliente_moneda_id = $_COOKIE['cliente_moneda_id'];
        $this->cliente_forma_pago_id = $_COOKIE['cliente_forma_pago_id'];
        $this->cliente_metodo_pago_id = $_COOKIE['cliente_metodo_pago_id'];
        $this->cliente_condiciones_pago = $_COOKIE['cliente_condiciones_pago'];
        $this->fecha = $_COOKIE['fecha'];

        $partidas = unserialize($_COOKIE['partidas']);
        $insumo_modelo = new Insumo($this->link);

        $this->partidas = array();
        $i = 0;
        foreach ($partidas as $partida){
            $insumo_id = $partida['insumo_id'];
            $cantidad = $partida['cantidad'];
            $valor_unitario = $partida['valor_unitario'];
            $subtotal = $cantidad * $valor_unitario;

            $resultado = $insumo_modelo->obten_por_id('insumo',$insumo_id);
            $insumo = $resultado['registros'][0];



            $this->partidas[$i]['insumo_id'] = $insumo_id;
            $this->partidas[$i]['insumo_descripcion'] = $insumo['insumo_descripcion'];
            $this->partidas[$i]['cantidad'] = $cantidad;
            $this->partidas[$i]['valor_unitario'] = $valor_unitario;
            $this->partidas[$i]['unidad_descripcion'] = $insumo['unidad_descripcion'];
            $this->partidas[$i]['impuesto_descripcion_trasladado'] = $insumo['impuesto_descripcion'];
            $this->partidas[$i]['insumo_factor_trasladado'] = $insumo['insumo_factor'];
            $this->partidas[$i]['monto_impuesto_trasladado'] = $subtotal * $insumo['insumo_factor'];

            $this->partidas[$i]['impuesto_descripcion_retenido'] = $insumo['impuesto_retenido_descripcion'];
            $this->partidas[$i]['insumo_factor_retenido'] = $insumo['insumo_factor_retenido'];
            $this->partidas[$i]['monto_impuesto_retenido'] = $subtotal * $insumo['insumo_factor_retenido'];
            $this->partidas[$i]['total'] = $subtotal + $this->partidas[$i]['monto_impuesto_trasladado'] - $this->partidas[$i]['monto_impuesto_retenido'];
            $this->partidas[$i]['producto_sat_codigo'] = $insumo['producto_sat_codigo'];

            $i++;


        }


    }

    public function timbra_cfdi(){
        $factura_id = $_GET['factura_id'];
        $factura_modelo = new Factura($this->link);
        $resultado = $factura_modelo->obten_por_id('factura',$factura_id);
        $registro = $resultado['registros'][0];
        $folio = $registro['factura_folio'];
        $factura = new Facturas($this->link);
        $cliente_id = $registro['cliente_id'];

        $resultado_timbrado = $factura->timbra_cfdi($folio);

//        if($factura_id == 10){
//            exit;
//        }

        if($resultado_timbrado['error']){
            header("Location: ./index.php?seccion=factura&accion=vista_preliminar&mensaje=".$resultado_timbrado['mensaje']."&tipo_mensaje=error");
            exit;
        }
        else{
            header("Location: ./index.php?seccion=factura&accion=vista_preliminar&mensaje=Exito&factura_id=$factura_id");
            exit;
        }
    }

    public function vista_preliminar(){
        $breadcrumbs = array('lista','alta');
        $this->breadcrumbs = $this->directiva->nav_breadcumbs(12, 0, $breadcrumbs);
        $factura_id = $_GET['factura_id'];

        $partida_factura_modelo = new Partida_Factura($this->link);
        $filtro = array('factura_id'=>$factura_id);
        $resultado = $partida_factura_modelo->filtro_and('partida_factura',$filtro);

        $partidas = $resultado['registros'];

        foreach ($partidas as $partida) {
            if($partida['partida_factura_producto_sat_codigo'] == ''){
                $partida_factura_modelo->modifica_bd(array('factura_id'=>$factura_id),'partida_factura',$partida['partida_factura_id']);
            }
        }
        $controlador_cliente = new Controlador_Cliente($this->link);
        $controlador_cliente->genera_pdf_factura_sin_timbrar($factura_id);

        $factura = new Factura($this->link);
        $resultado = $factura->obten_por_id('factura',$factura_id);
        $registro = $resultado['registros'][0];
        $folio = $registro['factura_folio'];
        $empresa = new Empresas();
        $datos_empresa = $empresa->empresas[$_SESSION['numero_empresa']];
        $ruta_base = $datos_empresa['nombre_base_datos'];
        $ruta_xml_sin_timbrar = $ruta_base.'/xml_sin_timbrar/'.$folio.'.xml';
        $xml = new SimpleXMLElement ($ruta_xml_sin_timbrar,null,true);
        $facturas = new Facturas($this->link);
        $this->datos_emisor = $facturas->obten_datos_emisor($xml);
        $this->datos_comprobante = $facturas->obten_datos_comprobante($xml);
        $this->datos_receptor = $facturas->obten_datos_receptor($xml);
        $this->datos_partidas = $facturas->obten_datos_partidas($xml);
        $this->datos_impuestos = $facturas->obten_datos_impuestos($xml);
        $this->datos_impuestos_traslados = $facturas->obten_datos_impuestos_trasladados($xml);
        $this->datos_impuestos_retenidos = $facturas->obten_datos_impuestos_retenidos($xml);
        $this->status_factura = $registro['factura_status_factura'];
        $this->factura_id = $_GET['factura_id'];

        $numero_text = new NumeroTexto();

        $moneda_mostrar = '';
        if($this->datos_comprobante['Moneda'] == 'MXN'){
            $moneda_mostrar = 'PESOS';
        }
        elseif($this->datos_comprobante['Moneda'] == 'USD'){
            $moneda_mostrar = 'DOLARES';
        }

        $this->numero_texto = $numero_text->to_word($this->datos_comprobante['Total'],$moneda_mostrar);


    }







    public function alta_bd(){
        $modelo_empresa = new Empresas();
        $numero_empresa = $_SESSION['numero_empresa'];
        $this->datos_empresa = $modelo_empresa->empresas[$numero_empresa];


        $datos_metodo_pago = $this->obten_datos('metodo_pago');
        $datos_tipo_comprobante = $this->obten_datos('tipo_comprobante');
        $datos_moneda = $this->obten_datos('moneda');
        $datos_forma_pago = $this->obten_datos('forma_pago');
        $datos_cliente = $this->obten_datos('cliente');
        $datos_uso_cfdi = $this->obten_datos('uso_cfdi');
        $fecha = date('Y-m-d');
        $hora = date('H:m:00');
        $fecha_emision = $fecha.'T'.$hora;
        $sufijo = $this->datos_empresa['sufijo_folio'];
        $folio_inicial = $this->datos_empresa['folio_inicial'];
        $folio = $this->genera_folio($sufijo,'factura',$folio_inicial);
        $this->datos_guardar = array();

        $this->carga_datos_empresa();

        $this->datos_guardar['metodo_pago_codigo'] = $datos_metodo_pago['metodo_pago_codigo'];
        $this->datos_guardar['metodo_pago_descripcion'] = $datos_metodo_pago['metodo_pago_descripcion'];

        $this->datos_guardar['tipo_comprobante_codigo'] = $datos_tipo_comprobante['tipo_comprobante_codigo'];
        $this->datos_guardar['tipo_comprobante_descripcion'] = $datos_tipo_comprobante['tipo_comprobante_descripcion'];

        $this->datos_guardar['moneda_codigo'] = $datos_moneda['moneda_codigo'];
        $datos_guardar['moneda_descripcion'] = $datos_moneda['moneda_descripcion'];

        $this->datos_guardar['condiciones_pago'] = $_POST['condiciones_pago'];

        $this->datos_guardar['forma_pago_codigo'] = $datos_forma_pago['forma_pago_codigo'];
        $this->datos_guardar['forma_pago_descripcion'] = $datos_forma_pago['forma_pago_descripcion'];
        $this->datos_guardar['fecha'] = $fecha_emision;
        $this->datos_guardar['folio'] = $folio;
        $this->datos_guardar['cliente_id'] = $_POST['cliente_id'];

        $this->datos_guardar['cliente_rfc'] = $datos_cliente['cliente_rfc'];
        $this->datos_guardar['cliente_razon_social'] = $datos_cliente['cliente_razon_social'];

        $this->datos_guardar['uso_cfdi_codigo'] = $datos_uso_cfdi['uso_cfdi_codigo'];
        $this->datos_guardar['uso_cfdi_descripcion'] = $datos_uso_cfdi['uso_cfdi_descripcion'];
        $this->datos_guardar['status'] = 1;

        $this->datos_guardar['status_factura'] = 'sin timbrar';

        $this->datos_guardar['observaciones'] = $_POST['observaciones'];

        $this->tabla = $_GET['seccion'];
        $resultado = $this->modelo->alta_bd($datos_guardar, $tabla);

        if($resultado['error']){
            $mensaje = $resultado['mensaje'];
            header("Location: ./index.php?seccion=$tabla&accion=alta&mensaje=$mensaje&tipo_mensaje=error");
            exit;
        }

        header("Location: ./index.php?seccion=$tabla&accion=lista&mensaje=Registro insertado con éxito&tipo_mensaje=exito");
    }

    public function descarga_masiva(){
        $facturas = $_GET['factura'];
        foreach ($facturas as $factura_id) {
            header('Location: ./index.php?seccion=cliente&accion=descarga_factura_pdf&factura_id='.$factura_id);
            header('Location: ./index.php?seccion=cliente&accion=descarga_factura_pdf&factura_id='.$factura_id+1);
        }

    }

    public function cancela(){
        if(isset($_COOKIE['cliente_id'])){
            $this->cliente_id = $_COOKIE['cliente_id'];
        }
    }

    public function guarda_cliente_cancela(){
        $cliente_id = $_POST['cliente_id'];
        setcookie('cliente_id', $cliente_id);
        header('location: ./index.php?seccion=factura&accion=ve_factura_cancelar&session_id='.SESSION_ID);
        exit;

    }

    public function cancela_factura_directa(){
        print_r($_COOKIE); exit;
    }

    public function opciones_cancela_factura(){

        if(!isset($_COOKIE['cliente_id'])){
            $mensaje = 'No tienes cliente seleccionado';
            header('location: ./index.php?seccion=factura&accion=cancela&session_id='.SESSION_ID.'&mensaje='-$mensaje.'&tipo_mensaje=error');
            exit;
        }
        $this->cliente_id = $_COOKIE['cliente_id'];
        if(!isset($_COOKIE['factura_id'])){
            $mensaje = 'No tienes factura seleccionada';
            header('location: ./index.php?seccion=factura&accion=ve_factura_cancelar&session_id='.SESSION_ID.'&mensaje='-$mensaje.'&tipo_mensaje=error');
            exit;
        }
        $this->factura_id = $_COOKIE['factura_id'];

        $factura = new Factura($this->link);

        $resultado = $factura->obten_por_id('factura', $this->factura_id);

        $this->factura = $resultado['registros'][0];

        $this->obten_datos_emisor();
        $this->obten_datos_receptor();
        $this->obten_datos_comprobante();
        $this->obten_impuestos();

        $numero = new NumeroTexto();
        $this->numero_texto = $numero->to_word($this->datos_impuestos['total']).' '.$this->datos_comprobante['moneda_codigo'];

        $partida_factura = new Partida_Factura($this->link);
        $filtro = array('factura_id'=>$this->factura_id);
        $resultado = $partida_factura->filtro_and('partida_factura', $filtro);

        $this->datos_partidas = $resultado['registros'];

    }

    public function guarda_factura_cancelar(){
        if(!isset($_COOKIE['cliente_id'])){
            $mensaje = 'No tienes cliente seleccionado';
            header('location: ./index.php?seccion=factura&accion=cancela&session_id='.SESSION_ID.'&mensaje='-$mensaje.'&tipo_mensaje=error');
            exit;
        }


        $this->cliente_id = $_COOKIE['cliente_id'];
        $this->factura_id = $_GET['factura_id'];

        setcookie('factura_id',$this->factura_id);

        header('location: ./index.php?seccion=factura&accion=opciones_cancela_factura&session_id='.SESSION_ID);
        exit;

    }

    public function ve_factura_cancelar(){
        if(!isset($_COOKIE['cliente_id'])){
            $mensaje = 'No tienes cliente seleccionado';
            header('location: ./index.php?seccion=factura&accion=cancela&session_id='.SESSION_ID.'&mensaje='-$mensaje.'&tipo_mensaje=error');
            exit;
        }
        $this->cliente_id = $_COOKIE['cliente_id'];
        $factura = new Factura($this->link);
        $resultado = $factura->obten_facturas_activas($this->cliente_id);
        $this->facturas = $resultado['registros'];
    }




}