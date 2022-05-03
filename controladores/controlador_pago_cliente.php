<?php
class Controlador_Pago_Cliente extends Controlador_Base{
    public $hoy;
    public $cp;
    public $serie;
    public $folio;
    public $cliente_id;
    public $fecha;
    public $forma_pago_id;
    public $moneda_id;
    public $options_cuenta_bancaria;
    public $options_cuenta_bancaria_empresa;
    public $monto;
    public $tipo_cambio;
    public $numero_operacion;
    public $cuenta_bancaria_id;
    public $cuenta_bancaria_empresa_id;
    public $facturas_saldo;
    public $fecha_pago;
    public $pago_cliente;
    public $status_factura;
    public $pago_cliente_factura;
    public $pago_cliente_id;
    public $pagos;
    public $uuid_relacionado;
    public $tipo_relacion;

    public function  alta(){
        $empresa = new Empresas();
        $pago_cliente = new Pago_Cliente($this->link);
        $datos_empresa = $empresa->empresas[$_SESSION['numero_empresa']];
        $this->cp = $datos_empresa['cp'];
        $this->serie = $datos_empresa['serie'];
        $this->hoy = date('Y-m-d');
        $this->folio = ($pago_cliente->obten_ultimo_id('pago_cliente'))+1;
        $breadcrumbs = array('lista');
        $this->breadcrumbs = $this->directiva->nav_breadcumbs(12, 0, $breadcrumbs);
    }

    public function alta_cfdi_relacionado(){
        $resultado = $this->asigna_cookie();

        if(!$resultado){
            $mensaje = "No existen valores de pago";
            header("Location: index.php?seccion=pago_cliente&accion=alta_partida&tipo_mensaje=error&mensaje=$mensaje&session_id=".SESSION_ID);
            exit;
        }

        $this->options_cuenta_bancaria = $this->genera_option_cuenta('cuenta_bancaria',$this->cuenta_bancaria_id,'selected');
        $this->options_cuenta_bancaria_empresa = $this->genera_option_cuenta('cuenta_bancaria_empresa',$this->cuenta_bancaria_empresa_id,'selected');
        $factura = new Factura($this->link);
        $resultado = $factura->obten_facturas_con_saldo($this->cliente_id);
        $facturas_saldo = $resultado['registros'];

        $pago_cliente_factura_modelo = new Pago_Cliente_Factura($this->link);



        $monto_restante = $this->monto;
        $this->facturas_saldo = array();
        $saldo_actual_total = 0;
        foreach ($facturas_saldo as $factura){
            $saldo_actual_total = $saldo_actual_total + $factura['factura_saldo'];
            if($monto_restante<$factura['factura_saldo']){
                $factura['monto_pagar'] = $monto_restante;
                $monto_restante = 0;
            }
            else{
                $factura['monto_pagar'] = $factura['factura_saldo'];
                $monto_restante = $monto_restante-$factura['factura_saldo'];
            }

            $filtro = array('factura.id'=>$factura['factura_id'], 'pago_cliente_factura.status'=>1);
            $resultado_pagos = $pago_cliente_factura_modelo->filtro_and('pago_cliente_factura',$filtro);

            $n_parcialidad = $resultado_pagos['n_registros']+1;

            $factura['n_parcialidad'] = $n_parcialidad;

            $this->facturas_saldo[] = $factura;
        }

        if($monto_restante > 0){
            $diferencia = $this->monto-$saldo_actual_total;
            $mensaje = "El monto de pago excede el saldo total de las facturas Saldo Total: ";
            $mensaje = $mensaje."$saldo_actual_total Monto de pago $this->monto diferencia $diferencia";
            header("Location: index.php?seccion=pago_cliente&accion=alta_partida&tipo_mensaje=error&mensaje=$mensaje&session_id=".SESSION_ID);
            exit;
        }
    }

    public function alta_partida(){
        $resultado = $this->asigna_cookie();

        if(!$resultado){
            $mensaje = "No existen valores de pago";
            header("Location: index.php?seccion=pago_cliente&accion=alta&tipo_mensaje=error&mensaje=$mensaje&session_id=".SESSION_ID);
            exit;
        }


        $factura = new Factura($this->link);
        $resultado = $factura->obten_facturas_con_saldo($this->cliente_id);


        $facturas_saldo = $resultado['registros'];

        $n_facturas = count($facturas_saldo);

        if($n_facturas==0){
            header('Location: index.php?seccion=pago_cliente&accion=alta&tipo_mensaje=error&mensaje=No existen facturas con saldo&session_id='.SESSION_ID);
            exit;
        }


        $cliente = new Cliente($this->link);
        $resultado = $cliente->obten_por_id('cliente',$this->cliente_id);
        $this->registro = $resultado['registros'][0];
        $this->forma_pago_id = $this->registro['forma_pago_id'];
        $this->moneda_id = $this->registro['moneda_id'];
        $cuenta_cliente = new Cuenta_Bancaria($this->link);
        $filtro = array('cliente.id'=>$this->cliente_id,'cuenta_bancaria.status');
        $resultado = $cuenta_cliente->filtro_and('cuenta_bancaria',$filtro);
        $cuentas = $resultado['registros'];
        $this->options_cuenta_bancaria = $this->genera_option($cuentas,'cuenta_bancaria',false);
        $cuenta_empresa = new Cuenta_Bancaria_Empresa($this->link);
        $resultado = $cuenta_empresa->obten_registros_activos('cuenta_bancaria_empresa');
        $cuentas = $resultado['registros'];
        $this->options_cuenta_bancaria_empresa = $this->genera_option($cuentas,'cuenta_bancaria_empresa',false);


    }

    public function aplica_pago(){
        $this->asigna_cookie();
        $this->link->query('SET AUTOCOMMIT=0');
        $this->link->query('START TRANSACTION');


        $registro = $this->asigna_elemento_insercion();



        $pago_cliente = new Pago_Cliente($this->link);
        $resultado = $pago_cliente->alta_bd($registro,'pago_cliente');


        $pago_cliente_id = $resultado['registro_id'];


        if($resultado['error']){
            $this->link->query('ROLLBACK');
            header('index.php?seccion=pago_cliente&accion=alta&mensaje=Error&tipo_mensaje=error&session_id='.SESSION_ID);
            exit;
        }


        $facturas_id = $_POST['factura_id'];
        $montos_pagar = $_POST['monto_pagar'];
        $parcialidades = $_POST['parcialidad'];

        $i = 0;

        $pago_cliente_factura = new Pago_Cliente_Factura($this->link);
        $factura = new Factura($this->link);

        $facturas_a_pagar = array();
        foreach ($montos_pagar as $monto){
            if($monto > 0){
                $factura_id = $facturas_id[$i];
                $resultado_factura = $factura->obten_por_id('factura',$factura_id);
                $factura_registro = $resultado_factura['registros'][0];


                $factura_insertar['factura_uuid'] = $factura_registro['factura_uuid'];
                $factura_insertar['monto'] = round($monto,2);
                $factura_insertar['factura_id'] = $factura_registro['factura_id'];
                $factura_insertar['cliente_id'] = $factura_registro['cliente_id'];
                $factura_insertar['pago_cliente_id'] = $pago_cliente_id;
                $factura_insertar['status'] = 1;
                $factura_insertar['importe_saldo_anterior'] = round($factura_registro['factura_saldo'],2);
                $factura_insertar['importe_saldo_insoluto'] = round($factura_registro['factura_saldo'],2)-round($monto,2);
                $factura_insertar['parcialidad'] = $parcialidades[$i];

                $resultado_insercion = $pago_cliente_factura->alta_bd($factura_insertar,'pago_cliente_factura');


                if($resultado_insercion['error']){
                    $this->link->query('ROLLBACK');
                    header('index.php?seccion=pago_cliente&accion=alta&mensaje=Error&tipo_mensaje=error&session_id='.SESSION_ID);
                    exit;
                }

                $saldo_factura = $factura_registro['factura_saldo']-$monto;
                if($saldo_factura == 0){
                    $status_factura = 'Timbrada/Pagada';
                }
                else{
                    $status_factura = 'Timbrada/Pago Parcial';
                }


                $factura_update_saldo['saldo'] = $saldo_factura;
                $factura_update_saldo['status'] = 1;
                $factura_update_saldo['status_factura'] = $status_factura;

                $resultado_update_factura = $factura->modifica_bd($factura_update_saldo,'factura',$factura_id);

                if($resultado_update_factura['error']){
                    $this->link->query('ROLLBACK');
                    header('index.php?seccion=pago_cliente&accion=alta&mensaje=Error&tipo_mensaje=error&session_id='.SESSION_ID);
                    exit;
                }
                $facturas_a_pagar[] = $factura_insertar;

            }
            $i++;
        }

        $_SESSION['pago_cliente'] = serialize($registro);

        $_SESSION['facturas_pagar'] = serialize($registro);


        $this->link->query('COMMIT');


        header('Location: index.php?seccion=pago_cliente&accion=vista_preliminar&pago_cliente_id='.$pago_cliente_id.'&session_id='.SESSION_ID);
        exit;
    }

    private function asigna_cookie(){
        if(!isset($_SESSION['pago_cliente'])){
            return false;
        }
        $pago_cliente = unserialize($_SESSION['pago_cliente']);
        $this->cliente_id = $pago_cliente['cliente_id'];
        $this->fecha = $pago_cliente['fecha'];
        $this->cp = $pago_cliente['cp'];
        $this->serie = $pago_cliente['serie'];
        $this->folio = $pago_cliente['folio'];
        $this->hoy = date('Y-m-d');
        $this->uuid_relacionado = $pago_cliente['uuid_relacionado'];
        $this->tipo_relacion = $pago_cliente['tipo_relacion'];
        if(isset($pago_cliente['forma_pago_id'])) {
            $this->forma_pago_id = $pago_cliente['forma_pago_id'];
        }
        if(isset($pago_cliente['moneda_id'])) {
            $this->moneda_id = $pago_cliente['moneda_id'];
        }
        if(isset($pago_cliente['tipo_cambio'])) {
            $this->tipo_cambio = $pago_cliente['tipo_cambio'];
        }
        if(isset($pago_cliente['numero_operacion'])) {
            $this->numero_operacion = $pago_cliente['numero_operacion'];
        }
        if(isset($pago_cliente['monto'])) {
            $this->monto = $pago_cliente['monto'];
        }
        if(isset($pago_cliente['cuenta_bancaria_id'])) {
            $this->cuenta_bancaria_id = $pago_cliente['cuenta_bancaria_id'];
        }
        if(isset($pago_cliente['cuenta_bancaria_empresa_id'])) {
            $this->cuenta_bancaria_empresa_id = $pago_cliente['cuenta_bancaria_empresa_id'];
        }
        if(isset($pago_cliente['fecha_pago'])) {
            $this->fecha_pago = $pago_cliente['fecha_pago'];
        }

        if($this->cuenta_bancaria_id == ''){
            $this->cuenta_bancaria_id = -1;
        }

        if($this->cuenta_bancaria_empresa_id == ''){
            $this->cuenta_bancaria_empresa_id = -1;
        }
        return true;

    }

    private function asigna_elemento_insercion(){
        $registro['cliente_id'] = $this->cliente_id;
        $cliente = $this->obten_datos('cliente',$this->cliente_id);

        $registro['cliente_rfc'] = $cliente['cliente_rfc'];
        $registro['cliente_razon_social'] = $cliente['cliente_razon_social'];

        $registro['fecha'] = $this->fecha;
        $registro['cp'] = $this->cp;
        $registro['serie'] = $this->serie;
        $registro['folio'] = $this->folio;
        $registro['fecha_pago'] = $this->fecha_pago;

        $registro['forma_pago_id'] = $this->forma_pago_id;
        $forma_pago = $this->obten_datos('forma_pago',$this->forma_pago_id);
        $registro['forma_pago_codigo'] = $forma_pago['forma_pago_codigo'];
        $registro['forma_pago_descripcion'] = $forma_pago['forma_pago_descripcion'];

        $registro['moneda_id'] = $this->moneda_id;
        $moneda = $this->obten_datos('moneda',$this->moneda_id);
        $registro['moneda_codigo'] = $moneda['moneda_codigo'];
        $registro['moneda_descripcion'] = $moneda['moneda_descripcion'];
        $registro['tipo_cambio'] = $this->tipo_cambio;
        $registro['monto'] = $this->monto;
        $registro['numero_operacion'] = $this->numero_operacion;
        $registro['uuid_relacionado'] = $this->uuid_relacionado;
        $registro['tipo_relacion'] = $this->tipo_relacion;



        $registro['cuenta_bancaria_id'] = $this->cuenta_bancaria_id;
        $cuenta_bancaria = $this->obten_datos('cuenta_bancaria',$this->cuenta_bancaria_id);

        if($registro['forma_pago_id'] == 2) {
            $registro['cuenta_bancaria_cuenta'] = $cuenta_bancaria['cuenta_bancaria_cuenta'];
        }


        if(!isset($registro['cuenta_bancaria_cuenta']) || $registro['cuenta_bancaria_cuenta'] == ''){
            $registro['cuenta_bancaria_cuenta'] = $cuenta_bancaria['cuenta_bancaria_clabe'];
        }


        if($registro['cuenta_bancaria_cuenta'] == ''){
            $registro['cuenta_bancaria_cuenta'] = $cuenta_bancaria['cuenta_bancaria_cheque'];
        }


        $registro['cuenta_bancaria_empresa_id'] = $this->cuenta_bancaria_empresa_id;
        $cuenta_bancaria_empresa = $this->obten_datos('cuenta_bancaria_empresa',$this->cuenta_bancaria_empresa_id);
        $registro['cuenta_bancaria_empresa_cuenta'] = $cuenta_bancaria_empresa['cuenta_bancaria_empresa_cuenta'];

        $registro['cliente_banco_id'] = $cuenta_bancaria['banco_id'];
        $registro['cliente_banco_rfc'] = $cuenta_bancaria['banco_rfc'];
        $registro['cliente_banco_descripcion'] = $cuenta_bancaria['banco_descripcion'];



        $registro['empresa_banco_id'] = $cuenta_bancaria_empresa['banco_id'];
        $registro['empresa_banco_rfc'] = $cuenta_bancaria_empresa['banco_rfc'];
        $registro['empresa_banco_descripcion'] = $cuenta_bancaria_empresa['banco_descripcion'];


        $empresa = new Empresas();

        $datos_empresa = $empresa->empresas[$_SESSION['numero_empresa']];

        $registro['empresa_rfc'] = $datos_empresa['rfc'];
        $registro['empresa_razon_social'] = $datos_empresa['razon_social'];

        $registro['regimen_fiscal_codigo'] = $datos_empresa['regimen_fiscal'];

        $regimen_fiscal = new Regimen_Fiscal($this->link);
        $filtro = array('regimen_fiscal.codigo'=>$registro['regimen_fiscal_codigo']);

        $resultado = $regimen_fiscal->filtro_and('regimen_fiscal',$filtro);

        $regimen_fiscal = $resultado['registros'][0];

        $registro['regimen_fiscal_descripcion'] = $regimen_fiscal['regimen_fiscal_descripcion'];
        $registro['regimen_fiscal_id'] = $regimen_fiscal['regimen_fiscal_id'];

        $registro['uso_cfdi_id'] = 22;

        $uso_cfdi = $this->obten_datos('uso_cfdi',22);
        $registro['uso_cfdi_codigo'] = $uso_cfdi['uso_cfdi_codigo'];
        $registro['uso_cfdi_descripcion'] = $uso_cfdi['uso_cfdi_descripcion'];
        return $registro;

    }

    public function cancela_complemento_pago(){
        $pago_cliente_id = $_GET['pago_cliente_id'];


        $modelo_pago_cliente = new Pago_Cliente($this->link);
        $resultado = $modelo_pago_cliente->obten_por_id('pago_cliente',$pago_cliente_id);

        $pago = $resultado['registros'][0];

        $numero_empresa = $_SESSION['numero_empresa'];
        $empresas = new Empresas();
        $datos_empresa = $empresas->empresas[$numero_empresa];


        $ws = $datos_empresa['ruta_pac'];

        $response = '';
        /* El servicio para cancelar un cfdi recibe 3 parámetros*/

        /*Usuario Integrador*/
        $usuarioIntegrador = $datos_empresa['usuario_integrador'];

        /*Rfc del Emisor que emitió el comprobante*/
        $rfcEmisor = $datos_empresa['rfc'];

        /*Folio fiscal(UUID) del comprobante a cancelar, deberá ser uno válido de los que hayamos timbrado previamente en pruebas*/

        $folioUUID = $pago['pago_cliente_uuid'];



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

            $modelo_pago_cliente->modifica_bd(array('status'=>'0','status_factura'=>'cancelado'),'pago_cliente',$_GET['pago_cliente_id']);

            $this->elimina_facturas_relacionadas();


            header("Location: ./index.php?seccion=pago_cliente&pago_cliente_id=$pago_cliente_id&accion=vista_preliminar&mensaje=cancelado_con_exito&tipo_mensaje=exito");
            exit;

        } else {
            $mensaje = $descripcionResultado;
            header("Location: ./index.php?seccion=pago_cliente&accion=vista_preliminar&mensaje=$mensaje&tipo_mensaje=error&pago_cliente_id=$pago_cliente_id");
            exit;
        }



    }

    public function descarga_xml(){


        $pago_cliente_id = $_GET['pago_cliente_id'];

        $modelo_pago_cliente = new Pago_Cliente($this->link);
        $resultado = $modelo_pago_cliente->obten_por_id('pago_cliente',$pago_cliente_id);

        $pago = $resultado['registros'][0];

        $folio = $pago['pago_cliente_folio'];

        $name_file = 'P_'.$pago['pago_cliente_folio'];


        $numero_empresa = $_SESSION['numero_empresa'];
        $empresas = new Empresas();
        $datos_empresa = $empresas->empresas[$numero_empresa];

        $ruta_base = $datos_empresa['nombre_base_datos'];

        $ruta_xml = $ruta_base.'/xml_timbrado/P_'.$folio.'.xml';

        header("Content-disposition: attachment; filename=$name_file.xml");
        header('Content-type: "text/xml"; charset="utf8"');
        readfile($ruta_xml);
        exit;

    }

    public function elimina_facturas_relacionadas(){


        $pago_cliente_factura_modelo = new Pago_Cliente_Factura($this->link);
        $factura_modelo = new Factura($this->link);


        $r_pago_cliente_factura = $pago_cliente_factura_modelo->filtro_and('pago_cliente_factura',array('pago_cliente.id'=>$_GET['pago_cliente_id']));

        $pago_cliente_factura_modelo->elimina_con_filtro_and('pago_cliente_factura',array('pago_cliente_id'=>$_GET['pago_cliente_id']));


        $facturas_relacionadas = $r_pago_cliente_factura['registros'];

        foreach($facturas_relacionadas as $factura_relacionada){
            $factura_id = $factura_relacionada['factura_id'];
            $total_factura = $factura_relacionada['factura_total'];
            $r_pagos = $pago_cliente_factura_modelo->sumatoria('pago_cliente_factura.monto',
                'pago_cliente_factura','pago_cliente_factura.factura_id',$factura_id);

            $total_pagos  = $r_pagos['registros'][0]['suma'];
            $saldo_nuevo = round($total_factura - $total_pagos, 2);


            $factura_modelo->modifica_bd(array('saldo'=>$saldo_nuevo,'status'=>1),'factura', $factura_id);


        }


    }

    public function elimina_complemento(){

        $this->link->autocommit(false);
        $this->elimina_facturas_relacionadas();
        $pago_cliente_modelo = new Pago_Cliente($this->link);

        $pago_cliente_modelo->elimina_bd('pago_cliente', $_GET['pago_cliente_id']);
        $this->link->commit();

        header('Location: index.php?seccion=pago_cliente&accion=lista&session_id='.SESSION_ID.'&mensaje=');
        exit;

    }

    private function genera_option($cuentas,$tabla,$selected){
        $options_cuenta = '';
        foreach ($cuentas as $cuenta) {
            $cuenta_bancaria_id = $cuenta[$tabla.'_id'];
            $banco = $cuenta['banco_descripcion'];
            $cuenta_bancaria = $cuenta[$tabla.'_cuenta'];
            $cuenta_cheques = $cuenta[$tabla.'_cheque'];
            $cuenta_clabe = $cuenta[$tabla.'_clabe'];
            $moneda = $cuenta['moneda_descripcion'];

            if($cuenta_bancaria == ''){
                $cuenta_bancaria = $cuenta_cheques;
            }

            if($cuenta_bancaria == ''){
                $cuenta_bancaria = $cuenta_clabe;
            }

            $options_cuenta = $options_cuenta . "
                <option value='$cuenta_bancaria_id' $selected> $banco | $cuenta_bancaria | $moneda  </option>";
        }

        return $options_cuenta;
    }

    private function genera_option_cuenta($modelo,$id,$selected){
        $cuenta = new $modelo($this->link);
        $resultado = $cuenta->obten_por_id($modelo, $id);
        $cuentas = $resultado['registros'];
        $options_cuenta = $this->genera_option($cuentas,$modelo,$selected);
        return $options_cuenta;
    }

    public function genera_xml(){
        $pago_cliente_id = $_GET['pago_cliente_id'];
        $empresa = new Empresas();
        $repositorio = New Repositorio();
        $datos_empresa = $empresa->empresas[$_SESSION['numero_empresa']];
        $ruta_base = $datos_empresa['nombre_base_datos'];


        $pago_cliente_modelo = new Pago_Cliente($this->link);
        $resultado = $pago_cliente_modelo->obten_por_id('pago_cliente',$pago_cliente_id);
        $pago_cliente = $resultado['registros'][0];

        $pago_cliente_factura_modelo = new Pago_Cliente_Factura($this->link);
        $filtro = array('pago_cliente_id'=>$pago_cliente_id);
        $resultado = $pago_cliente_factura_modelo->filtro_and('pago_cliente_factura',$filtro);
        $pago_cliente_factura = $resultado['registros'];

        $LugarExpedicion = $pago_cliente['pago_cliente_cp'];
        $Fecha = $pago_cliente['pago_cliente_fecha'].'T'.date('07:m:00');
        $Folio = $pago_cliente['pago_cliente_folio'];
        $Serie = $pago_cliente['pago_cliente_serie'];
        $RfcEmisor = $pago_cliente['pago_cliente_empresa_rfc'];
        $NombreEmisor = $pago_cliente['pago_cliente_empresa_razon_social'];
        $RegimenFiscal = $pago_cliente['pago_cliente_regimen_fiscal_codigo'];
        $RfcReceptor = $pago_cliente['pago_cliente_cliente_rfc'];
        $RfcReceptor = str_replace('&','&amp;',$RfcReceptor);
        $NombreReceptor = $pago_cliente['pago_cliente_cliente_razon_social'];
        $NombreReceptor = str_replace('&','&amp;',$NombreReceptor);
        $FechaPago = $pago_cliente['pago_cliente_fecha_pago'].'T12:00:00';
        $FormaDePagoP = $pago_cliente['pago_cliente_forma_pago_codigo'];
        $MonedaP = $pago_cliente['pago_cliente_moneda_codigo'];
        $Monto = number_format(round($pago_cliente['pago_cliente_monto'],2),2,'.','');
        $NumOperacion = $pago_cliente['pago_cliente_numero_operacion'];

        $RfcEmisorCtaOrd = $pago_cliente['pago_cliente_cliente_banco_rfc'];

        $CtaOrdenante = $pago_cliente['pago_cliente_cuenta_bancaria_cuenta'];
        $RfcEmisorCtaBen = $pago_cliente['pago_cliente_empresa_banco_rfc'];
        $CtaBeneficiario = $pago_cliente['pago_cliente_cuenta_bancaria_empresa_cuenta'];


        $plantilla = './plantillas_cfdi/pago.xml';
        $xml  = file_get_contents($plantilla);

        if($RfcEmisorCtaOrd != ''){
            $xml = str_replace('|RfcEmisorCtaOrd|','RfcEmisorCtaOrd="'.$RfcEmisorCtaOrd.'"',$xml);
        }
        else{
            $xml = str_replace('|RfcEmisorCtaOrd|','',$xml);
        }


        if($CtaOrdenante != ''){
            $xml = str_replace('|CtaOrdenante|','CtaOrdenante="'.$CtaOrdenante.'"',$xml);
        }
        else{
            $xml = str_replace('|CtaOrdenante|','',$xml);
        }

        if($RfcEmisorCtaBen != ''){
            $xml = str_replace('|RfcEmisorCtaBen|','RfcEmisorCtaBen="'.$RfcEmisorCtaBen.'"',$xml);
        }
        else{
            $xml = str_replace('|RfcEmisorCtaBen|','',$xml);
        }

        if($CtaBeneficiario != ''){
            $xml = str_replace('|CtaBeneficiario|','CtaBeneficiario="'.$CtaBeneficiario.'"',$xml);
        }
        else{
            $xml = str_replace('|CtaBeneficiario|','',$xml);
        }

        $cfdis_relacionados = '';
        if((string)$pago_cliente['pago_cliente_uuid_relacionado']!==''){
            $cfdis_relacionados = '<cfdi:CfdiRelacionados TipoRelacion="'.$pago_cliente['pago_cliente_tipo_relacion'].'">
        <cfdi:CfdiRelacionado UUID="'.$pago_cliente['pago_cliente_uuid_relacionado'].'" />
    </cfdi:CfdiRelacionados>';
        }

        $xml = str_replace('|LugarExpedicion|',$LugarExpedicion,$xml);
        $xml = str_replace('|Fecha|',$Fecha,$xml);
        $xml = str_replace('|Folio|','P_'.$Folio,$xml);
        $xml = str_replace('|Serie|',$Serie,$xml);
        $xml = str_replace('|RfcEmisor|',$RfcEmisor,$xml);
        $xml = str_replace('|NombreEmisor|',$NombreEmisor,$xml);
        $xml = str_replace('|RegimenFiscal|',$RegimenFiscal,$xml);
        $xml = str_replace('|RfcReceptor|',$RfcReceptor,$xml);
        $xml = str_replace('|NombreReceptor|',$NombreReceptor,$xml);
        $xml = str_replace('|FechaPago|',$FechaPago,$xml);
        $xml = str_replace('|FormaDePagoP|',$FormaDePagoP,$xml);
        $xml = str_replace('|MonedaP|',$MonedaP,$xml);
        $xml = str_replace('|Monto|',$Monto,$xml);
        $xml = str_replace('|NumOperacion|',$NumOperacion,$xml);
        $xml = str_replace('|cdfis_relacionados|',$cfdis_relacionados,$xml);


        $partida='';
        foreach($pago_cliente_factura as $pago){
            $pago_base_xml = '<pago10:DoctoRelacionado IdDocumento="|IdDocumento|" Serie="|Serie|" Folio="|Folio|" MonedaDR="|MonedaDR|" MetodoDePagoDR="|MetodoDePagoDR|" NumParcialidad="|NumParcialidad|" ImpSaldoAnt="|ImpSaldoAnt|" ImpPagado="|ImpPagado|" ImpSaldoInsoluto="|ImpSaldoInsoluto|"></pago10:DoctoRelacionado>';
            $IdDocumento = $pago['pago_cliente_factura_factura_uuid'];
            $Serie = $pago['factura_serie'];
            $FolioFacturaPago = $pago['factura_folio'];
            $MonedaDR = $pago['factura_moneda_codigo'];
            $MetodoDePagoDR = $pago['factura_metodo_pago_codigo'];
            $NumParcialidad = $pago['pago_cliente_factura_parcialidad'];
            $ImpSaldoAnt = round($pago['pago_cliente_factura_importe_saldo_anterior'],2);
            $ImpPagado = round($pago['pago_cliente_factura_monto'],2);
            $ImpSaldoInsoluto = round($pago['pago_cliente_factura_importe_saldo_insoluto'],2);


            $pago_base_xml = str_replace('|IdDocumento|',$IdDocumento,$pago_base_xml);
            $pago_base_xml = str_replace('|Serie|',$Serie,$pago_base_xml);
            $pago_base_xml = str_replace('|Folio|',$FolioFacturaPago,$pago_base_xml);
            $pago_base_xml = str_replace('|MonedaDR|',$MonedaDR,$pago_base_xml);
            $pago_base_xml = str_replace('|MetodoDePagoDR|',$MetodoDePagoDR,$pago_base_xml);
            $pago_base_xml = str_replace('|NumParcialidad|',$NumParcialidad,$pago_base_xml);
            $pago_base_xml = str_replace('|ImpSaldoAnt|',$ImpSaldoAnt,$pago_base_xml);
            $pago_base_xml = str_replace('|ImpPagado|',$ImpPagado,$pago_base_xml);
            $pago_base_xml = str_replace('|ImpSaldoInsoluto|',$ImpSaldoInsoluto,$pago_base_xml);

            $partida = $partida.$pago_base_xml;
        }

        $xml = str_replace('|Pagos|',$partida,$xml);


        $repositorio->guarda_archivo($xml,'P_'.$Folio, $repositorio->directorio_xml_sin_timbrar_completo, '.xml');

        $factura = New Facturas($this->link);
        $resultado = $factura->timbra_cfdi_pago($Folio);


        $mensaje = 'Exito';
        $tipo_mensaje='exito';
        if(isset($resultado['error'])){
            if($resultado['error'] == 1){
                $tipo_mensaje = "error";
                $mensaje = $resultado['mensaje'];
            }
        }

        header("Location: index.php?seccion=pago_cliente&accion=vista_preliminar&mensaje=$mensaje&tipo_mensaje=$tipo_mensaje&pago_cliente_id=$pago_cliente_id&session_id=".SESSION_ID);
        exit;

    }

    public function guarda_pago_cliente_session(){
        $pago_cliente = array();
        $pago_cliente['cliente_id'] = $_POST['cliente_id'];
        $pago_cliente['fecha'] = $_POST['fecha'];
        $pago_cliente['cp'] = $_POST['cp'];
        $pago_cliente['serie'] = $_POST['serie'];
        $pago_cliente['folio'] = $_POST['folio'];

        $pago_cliente['uuid_relacionado'] = $_POST['uuid_relacionado'];
        $pago_cliente['tipo_relacion_id'] = '';
        if((string)$_POST['tipo_relacion_id'] !=='') {
            $pago_cliente['tipo_relacion'] = '0'.$_POST['tipo_relacion_id'];
        }

        $_SESSION['pago_cliente']  = serialize($pago_cliente);
        header('Location: index.php?seccion=pago_cliente&accion=alta_partida&session_id='.SESSION_ID);
        exit;
    }

    public function guarda_partida_session(){
        $pago_cliente = unserialize($_SESSION['pago_cliente']);
        $pago_cliente['fecha_pago'] = $_POST['fecha_pago'];
        $pago_cliente['forma_pago_id'] = $_POST['forma_pago_id'];
        $pago_cliente['cuenta_bancaria_id'] = $_POST['cuenta_bancaria_id'];
        $pago_cliente['cuenta_bancaria_empresa_id'] = $_POST['cuenta_bancaria_empresa_id'];


        if($pago_cliente['forma_pago_id'] == 1){
            if($pago_cliente['cuenta_bancaria_id'] != ''){
                header('Location: index.php?seccion=pago_cliente&mensaje=Es pagos en efectivo no se debe registrar cuenta ordenante&tipo_mensaje=error&accion=alta_partida&session_id='.SESSION_ID);
                exit;
            }
        }

        if($pago_cliente['forma_pago_id'] == 1){
            if($pago_cliente['cuenta_bancaria_empresa_id'] != ''){
                header('Location: index.php?seccion=pago_cliente&mensaje=Es pagos en efectivo no se debe registrar cuenta beneficiaria&tipo_mensaje=error&accion=alta_partida&session_id='.SESSION_ID);
                exit;
            }
        }

        $pago_cliente['moneda_id'] = $_POST['moneda_id'];
        $pago_cliente['tipo_cambio'] = $_POST['tipo_cambio'];
        $pago_cliente['monto'] = $_POST['monto'];
        $pago_cliente['numero_operacion'] = $_POST['numero_operacion'];


        $_SESSION['pago_cliente'] = serialize($pago_cliente);
        header('Location: index.php?seccion=pago_cliente&accion=alta_cfdi_relacionado&session_id='.SESSION_ID);
        exit;
    }

    public function lista(){
        $pago_cliente_modelo = new Pago_Cliente($this->link);
        $resultado = $pago_cliente_modelo->obten_registros('pago_cliente',' ORDER BY pago_cliente.fecha DESC');
        $this->pagos = $resultado['registros'];

    }

    public function modifica_fecha(){
        parent::modifica();
        if($this->registro['pago_cliente_uuid']!=''){
            header('Location: index.php?seccion=pago_cliente&accion=lista&mensaje=El pago no se puede modificar esta timbrado&tipo_mensaje=error&session_id='.SESSION_ID);
            exit;
        }
    }

    public function modifica_fecha_bd(){
        $pago_cliente_modelo = new Pago_Cliente($this->link);
        $tabla = $_GET['seccion'];
        $_POST['status'] = 1;
        $this->registro_id = $_GET['registro_id'];
        $resultado = $pago_cliente_modelo->modifica_bd($_POST, $tabla, $this->registro_id);

        if($resultado['error']){
            print_r($resultado);
            die('Error');
        }
        header("Location: ./index.php?seccion=$tabla&accion=lista&mensaje=Registro modificado con éxito&tipo_mensaje=exito");
        exit;
    }

    public function modifica_pago(){
        $_POST['status'] = 1;
        $pago_cliente_modelo = new Pago_Cliente($this->link);
        $pago_cliente_modelo->modifica_bd($_POST,'pago_cliente',$_GET['pago_cliente_id']);
        header('Location: index.php?seccion=pago_cliente&accion=vista_preliminar&session_id='.SESSION_ID.'&pago_cliente_id='.$_GET['pago_cliente_id']);
        exit;
    }

    public function modifica_pago_factura(){

        $_POST['importe_saldo_insoluto'] = $_POST['importe_saldo_anterior'] - $_POST['monto'];

        $_POST['status'] = 1;


        $pago_cliente_factura_modelo = new Pago_Cliente_Factura($this->link);

        $r = $pago_cliente_factura_modelo->modifica_bd($_POST,'pago_cliente_factura',$_GET['pago_cliente_factura_id']);

        header('Location: index.php?seccion=pago_cliente&accion=vista_preliminar&session_id='.SESSION_ID.'&pago_cliente_id='.$_GET['pago_cliente_id']);
        exit;

    }

    private function obten_datos($tabla,$id){

        $modelo = new $tabla($this->link);
        $resultado = $modelo->obten_por_id($tabla,$id);
        $registros = $resultado['registros'][0];
        return $registros;

    }

    public function set_font_info($pdf){
        $tamaño_texto_info = 8;
        $pdf->SetFont('Arial','',$tamaño_texto_info);
    }

    public function set_font_titulos($pdf){
        $tamaño_texto_titulos = 8;
        $pdf->$pdf->SetFont('Arial','B',$tamaño_texto_titulos);
    }

    public function ve_pdf(){
        $tamaño_texto_titulos = 7;
        $tamaño_texto_info = 7;
        $salto_de_linea = 4;
        $altura_celdas = 6;


        $pago_cliente_modelo = new Pago_Cliente($this->link);
        $resultado = $pago_cliente_modelo->obten_por_id('pago_cliente', $_GET['pago_cliente_id']);
        $pago_cliente = $resultado['registros'][0];


        $empresa = new Empresas();
        $datos_empresa=$empresa->empresas[$_SESSION['numero_empresa']];

        $encabezado_1 = $datos_empresa['encabezado_1'];
        $encabezado_2 = $datos_empresa['encabezado_2'];
        $encabezado_3 = $datos_empresa['encabezado_3'];
        $encabezado_4 = $datos_empresa['encabezado_4'];
        $leyenda_docto = $datos_empresa['leyenda_docto'];
        $nombre_empresa = $datos_empresa['razon_social'];

        $pago_cliente_factura = new Pago_Cliente($this->link);

        $pago_cliente_id = $_GET['pago_cliente_id'];
        $filtro = array('pago_cliente_id'=>$pago_cliente_id);
        $resultado_pago_cliente_factura = $pago_cliente_factura->filtro_and('pago_cliente_factura', $filtro);


        $RFC_emisor = utf8_decode($pago_cliente['pago_cliente_empresa_rfc']);
        $folio_fiscal = utf8_decode($pago_cliente['pago_cliente_uuid']);
        $nombre_emisor = utf8_decode($pago_cliente['pago_cliente_empresa_razon_social']);
        $No_serie_CSD = utf8_decode($pago_cliente['pago_cliente_sello_cfd']);
        $folio = utf8_decode($pago_cliente['pago_cliente_folio']);
        $serie = utf8_decode($pago_cliente['pago_cliente_serie']);
        $RFC_receptor = utf8_decode($pago_cliente['pago_cliente_cliente_rfc']);
        $codigo_postal = utf8_decode($pago_cliente['pago_cliente_cp']." ".$pago_cliente['pago_cliente_fecha']); //incluye fecha y hora
        $nombre_receptor = utf8_decode($pago_cliente['pago_cliente_cliente_razon_social']);
        $efecto_de_comprobante = utf8_decode("");
        $uso_CFDI = utf8_decode($pago_cliente['pago_cliente_uso_cfdi_descripcion']);
        $regimen_fiscal = utf8_decode($pago_cliente['pago_cliente_regimen_fiscal_descripcion']);


        $forma_de_pago = utf8_decode($pago_cliente['pago_cliente_forma_pago_descripcion']);
        $fecha_de_pago = utf8_decode($pago_cliente['pago_cliente_fecha_pago']);
        $RFC_emisor_cuenta_ordenante = utf8_decode($pago_cliente['pago_cliente_cliente_banco_rfc']);
        $moneda_de_pago = utf8_decode($pago_cliente['pago_cliente_moneda_codigo']." ".utf8_decode($pago_cliente['pago_cliente_moneda_descripcion']));
        $cuenta_ordenante = utf8_decode($pago_cliente['pago_cliente_cuenta_bancaria_cuenta']);

        $monto = number_format($pago_cliente['pago_cliente_monto'], 2);
        $numero_operacion = utf8_decode($pago_cliente['pago_cliente_numero_operacion']);
        $nombre_banco_ordenante = utf8_decode($pago_cliente['pago_cliente_cliente_banco_descripcion']);
        $RFC_emisor_cuenta_beneficiario = utf8_decode($pago_cliente['pago_cliente_empresa_banco_rfc']);
        $cuenta_beneficiario = utf8_decode($pago_cliente['pago_cliente_cuenta_bancaria_empresa_cuenta']);
        $tipo_cambio = $pago_cliente['pago_cliente_tipo_cambio'];
        $monto_pago = $pago_cliente['pago_cliente_monto'];

        $uuid_relacionado = $pago_cliente['pago_cliente_uuid_relacionado'];
        $tipo_relacion = $pago_cliente['pago_cliente_tipo_relacion'];

        if((string)$tipo_relacion === '04'){
            $tipo_relacion = 'Sustitución de los CFDI previos';
        }



        //Comienza el PDF
        $pdf = new FPDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();

        //Encabezado
        $pdf->SetFont('Courier','',$tamaño_texto_titulos);
        $pdf->Cell(190,7,utf8_decode($nombre_empresa),0,1,'C');
        $pdf->Cell(190,5,utf8_decode($encabezado_1),0,1,'C');
        $pdf->Cell(190,5,utf8_decode($encabezado_2),0,1,'C');
        $pdf->Ln();
        $pdf->SetFont('Courier','',$tamaño_texto_info);
        $pdf->MultiCell(90,5,utf8_decode($encabezado_3),'B','C');
        $pdf->SetXY(110,32);
        $pdf->MultiCell(90,5,utf8_decode($encabezado_4),'B','C');

        $y_inicial = 47;

        $w_etiqueta = 41;
        $w_texto = 54;

        $pdf->SetY($y_inicial);

        $pdf->SetFont('Arial','B',$tamaño_texto_titulos);
        $pdf->MultiCell($w_etiqueta, $altura_celdas, "RFC Emisor:");
        $y = $pdf->GetY()-6;
        $x_texto_derecho = $w_etiqueta+10;
        $pdf->SetXY($x_texto_derecho,$y);

        $pdf->SetFont('Arial','',$tamaño_texto_info);
        $pdf->MultiCell($w_texto, $altura_celdas, $RFC_emisor);


        $pdf->SetFont('Arial','B',$tamaño_texto_titulos);
        $pdf->MultiCell($w_etiqueta, $altura_celdas, "Nombre Emisor:");

        $y = $pdf->GetY()-6;
        $pdf->SetXY($x_texto_derecho,$y);

        $pdf->SetFont('Arial','',$tamaño_texto_info);
        $pdf->MultiCell($w_texto, $altura_celdas, $nombre_emisor);


        $pdf->SetFont('Arial','B',$tamaño_texto_titulos);
        $pdf->MultiCell($w_etiqueta, $altura_celdas, "Folio Fiscal:");

        $y = $pdf->GetY()-6;
        $pdf->SetXY($x_texto_derecho,$y);

        $pdf->SetFont('Arial','',$tamaño_texto_info);
        $pdf->MultiCell($w_texto, $altura_celdas, $folio_fiscal);


        $pdf->SetFont('Arial','B',$tamaño_texto_titulos);
        $pdf->MultiCell($w_etiqueta, $altura_celdas, "Folio:");


        $y = $pdf->GetY()-6;
        $pdf->SetXY($x_texto_derecho,$y);

        $pdf->SetFont('Arial','',$tamaño_texto_info);
        $pdf->MultiCell($w_texto, $altura_celdas, $folio);



        $pdf->SetFont('Arial','B',$tamaño_texto_titulos);
        $pdf->MultiCell($w_etiqueta, $altura_celdas, "Serie:");

        $y = $pdf->GetY()-6;
        $pdf->SetXY($x_texto_derecho,$y);

        $pdf->SetFont('Arial','',$tamaño_texto_info);
        $pdf->MultiCell($w_texto, $altura_celdas, $serie);




        $pdf->SetFont('Arial','B',$tamaño_texto_titulos);
        $pdf->MultiCell($w_etiqueta, $altura_celdas, "RFC receptor:");

        $y = $pdf->GetY()-6;
        $pdf->SetXY($x_texto_derecho,$y);

        $pdf->SetFont('Arial','',$tamaño_texto_info);
        $pdf->MultiCell($w_texto, $altura_celdas, $RFC_receptor);



        $pdf->SetFont('Arial','B',$tamaño_texto_titulos);
        $pdf->MultiCell($w_etiqueta, $altura_celdas, "Nombre Receptor:");

        $y = $pdf->GetY()-6;
        $pdf->SetXY($x_texto_derecho,$y);

        $pdf->SetFont('Arial','',$tamaño_texto_info);
        $pdf->MultiCell($w_texto,$altura_celdas,$nombre_receptor);



        $pdf->SetFont('Arial','B',$tamaño_texto_titulos);
        $pdf->MultiCell($w_etiqueta, $altura_celdas, utf8_decode("Código postal y hora de emisión:"));

        $y = $pdf->GetY()-6;
        $pdf->SetXY($x_texto_derecho,$y);

        $pdf->SetFont('Arial','',$tamaño_texto_info);
        $pdf->MultiCell($w_texto, $altura_celdas, $codigo_postal);


        $pdf->SetFont('Arial','B',$tamaño_texto_titulos);
        $pdf->MultiCell($w_etiqueta, $altura_celdas, "Uso CFDI:");


        $y = $pdf->GetY()-6;
        $pdf->SetXY($x_texto_derecho,$y);

        $pdf->SetFont('Arial','',$tamaño_texto_info);
        $pdf->MultiCell($w_texto, $altura_celdas, $uso_CFDI);


        $pdf->SetFont('Arial','B',$tamaño_texto_titulos);
        $pdf->MultiCell($w_etiqueta, $altura_celdas, utf8_decode("Régimen fiscal:"));

        $y = $pdf->GetY()-6;
        $pdf->SetXY($x_texto_derecho,$y);

        $pdf->SetFont('Arial','',$tamaño_texto_info);
        $pdf->MultiCell($w_texto, $altura_celdas, $regimen_fiscal);


        $pdf->SetFont('Arial','B',$tamaño_texto_titulos);
        $pdf->MultiCell($w_etiqueta, 8, utf8_decode("Tipo de Cambio:"));

        $y = $pdf->GetY()-6;
        $pdf->SetXY($x_texto_derecho,$y);

        $pdf->SetFont('Arial','',$tamaño_texto_info);
        $pdf->MultiCell($w_texto, $altura_celdas, number_format($tipo_cambio,2,'.',','));

        if(trim($uuid_relacionado) !=='') {
            $pdf->SetFont('Arial', '', $tamaño_texto_info);
            $pdf->MultiCell(200, $altura_celdas, 'Tipo Relacion 04 '.utf8_decode($tipo_relacion).' UUID Relacionado ' .$uuid_relacionado);
        }

        $pdf->SetFont('Arial','B',$tamaño_texto_titulos);
        $pdf->MultiCell($w_etiqueta, $altura_celdas, utf8_decode("Monto de Pago:"));

        $y = $pdf->GetY()-6;
        $pdf->SetXY($x_texto_derecho,$y);

        $pdf->SetFont('Arial','',$tamaño_texto_info);
        $pdf->MultiCell($w_texto, $altura_celdas, '$'.number_format($monto_pago,2,'.',','));



        $x_etiqueta_izq = 105;
        $x_texto_izq = 146;


        $pdf->SetXY($x_etiqueta_izq,$y_inicial);

        $pdf->SetFont('Arial','B',$tamaño_texto_titulos);
        $pdf->MultiCell($w_etiqueta, $altura_celdas, "No. de serie del CSD:");

        $pdf->SetXY($x_texto_izq,$y_inicial);

        $pdf->SetFont('Arial','',$tamaño_texto_info);
        $pdf->MultiCell($w_texto, $altura_celdas, $No_serie_CSD);

        $y = $pdf->GetY();
        $pdf->SetXY($x_etiqueta_izq,$y);

        $pdf->SetFont('Arial','B',$tamaño_texto_titulos);
        $pdf->MultiCell($w_etiqueta, $altura_celdas, "Efecto de comprobante:");

        $pdf->SetXY($x_texto_izq,$y);

        $pdf->SetFont('Arial','',$tamaño_texto_info);
        $pdf->MultiCell($w_texto, $altura_celdas, 'P Pago');



        $pdf->Ln(10);




        //Conceptos
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->MultiCell(20, 8, "Conceptos");
        $pdf->Ln(10);

        $pdf->SetFillColor(212, 212, 212);
        $pdf->SetFont('Arial', 'B', 5);
        $pdf->MultiCell(18, 3, "Clave del producto y/o servicio", 1, 'C', 1);

        $y = $pdf->GetY() - 6;
        $x = $pdf->GetX() + 18;

        $pdf->SetXY($x, $y);

        $pdf->Cell(23, 6, utf8_decode("No. Identificación"), 1, 0, 'C', 1);
        $pdf->Cell(16, 6, "Cantidad", 1, 0, 'C', 1);
        $pdf->MultiCell(14, 3, "Clave de unidad", 1, 'C', 1);

        $y = $pdf->GetY() - 6;
        $x = $pdf->GetX() + 71;

        $pdf->SetXY($x, $y);
        
        $pdf->Cell(19, 6, "Unidad", 1, 0, 'C', 1);
        $pdf->Cell(21, 6, "Valor Unitario", 1, 0, 'C', 1);
        $pdf->Cell(18, 6, "Importe", 1, 0, 'C', 1);
        $pdf->Cell(18, 6, "Descuento", 1, 0, 'C', 1);
        $pdf->Cell(21, 6, "No. de pedimento", 1, 0, 'C', 1);
        $pdf->Cell(21, 6, "No. de cuenta predial", 1, 0, 'C', 1);

        $pdf->Ln();

        $clave_producto_servicio = 84111506;
        $numero_identificacion = "";
        $cantidad = 1;
        $clave_unidad = 1;
        $unidad = "ACT";
        $valor_unitario = "Pago";
        $importe = "";
        $descuento = "";
        $numero_pedimento = "";
        $numero_cuenta_predial = "";

        $pdf->SetFont('Arial', '', 5);
        $pdf->Cell(18, 3, $clave_producto_servicio, 1, 0, 'C');
        $pdf->Cell(23, 3, $numero_identificacion, 1, 0, 'C');
        $pdf->Cell(16, 3, $cantidad, 1, 0, 'C');
        $pdf->Cell(14, 3, $clave_unidad, 1, 0, 'C');
        $pdf->Cell(19, 3, $unidad, 1, 0, 'C');
        $pdf->Cell(21, 3, $valor_unitario, 1, 0, 'C');
        $pdf->Cell(18, 3, $importe, 1, 0, 'C');
        $pdf->Cell(18, 3, $descuento, 1, 0, 'C');
        $pdf->Cell(21, 3, $numero_pedimento, 1, 0, 'C');
        $pdf->Cell(21, 3, $numero_cuenta_predial, 1, 0, 'C');

        $pdf->Ln();

        $pdf->Cell(18, 3, utf8_decode("Descripción"), 1, 0, 'C');
        $pdf->Cell(72, 3, "", 1, 0, 'C');

        $pdf->Ln(5);

        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(35, 6, "Moneda: ");
        $pdf->SetFont('Arial', '', 6);
        $pdf->MultiCell(56, 3, utf8_decode("Los códigos asignados para las transacciones en que intervenga ninguna moneda"));

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+95, $y-6);
        $pdf->SetFont('Arial', 'B', 6);  
        $pdf->Cell(80, 3, "Subtotal: ");
        $pdf->SetFont('Arial', '', 6);
        $pdf->MultiCell(10, 3, "$ ".number_format(0, 2));

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+95, $y);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(80, 3, "Total: ");
        $pdf->SetFont('Arial', '', 6);
        $pdf->MultiCell(80, 3, "$ ".number_format(0, 2));

        $pdf->Ln(10);


        //Información del pago
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(30, $altura_celdas, utf8_decode("Información del pago"));
        $pdf->Ln($salto_de_linea);

        //Primer Renglón I.P.
        $pdf->SetFont('Arial','B',$tamaño_texto_titulos);
        $pdf->Cell(45, $altura_celdas, "Forma de pago:");

        $pdf->SetFont('Arial','',$tamaño_texto_info);
        $pdf->Cell(60, $altura_celdas, $forma_de_pago);

        $pdf->SetFont('Arial','B',$tamaño_texto_titulos);
        $pdf->Cell(30, $altura_celdas, "Fecha de pago:");

        $pdf->SetFont('Arial','',$tamaño_texto_info);
        $pdf->Cell(30, $altura_celdas, $fecha_de_pago);

        $pdf->Ln($salto_de_linea);

        //Segundo Renglón I.P.

        if($RFC_emisor_cuenta_ordenante !='') {

            $pdf->SetFont('Arial', 'B', $tamaño_texto_titulos);
            $pdf->Cell(45, $altura_celdas, "RFC emisor cuenta ordenante:");

            $pdf->SetFont('Arial', '', $tamaño_texto_info);
            $pdf->Cell(60, $altura_celdas, $RFC_emisor_cuenta_ordenante);

            $pdf->SetFont('Arial', 'B', $tamaño_texto_titulos);
            $pdf->Cell(30, $altura_celdas, "Moneda de pago:");

            $pdf->SetFont('Arial', '', $tamaño_texto_info);
            $pdf->Cell(30, $altura_celdas, $moneda_de_pago);

            $pdf->Ln($salto_de_linea);
        }

        //Tercer Renglón I.P.

        if($cuenta_ordenante !='') {
            $pdf->SetFont('Arial', 'B', $tamaño_texto_titulos);
            $pdf->Cell(45, $altura_celdas, "Cuenta ordenante:");

            $pdf->SetFont('Arial', '', $tamaño_texto_info);
            $pdf->Cell(60, $altura_celdas, $cuenta_ordenante);

            $pdf->SetFont('Arial', 'B', $tamaño_texto_titulos);
            $pdf->Cell(30, $altura_celdas, "Monto:");

            $pdf->SetFont('Arial', '', $tamaño_texto_info);
            $pdf->Cell(30, $altura_celdas, $monto);

            $pdf->Ln($salto_de_linea);
        }

        //Cuarto-Septimo renglon I.P.
        $pdf->SetFont('Arial','B',$tamaño_texto_titulos);
        $pdf->Cell(45, $altura_celdas, utf8_decode("Número operación:"));
        $pdf->SetFont('Arial','',$tamaño_texto_info);
        $pdf->Cell(60, $altura_celdas, $numero_operacion);
        $pdf->Ln();

        if($nombre_banco_ordenante !='') {

            $pdf->SetFont('Arial', 'B', $tamaño_texto_titulos);
            $pdf->Cell(45, $altura_celdas, "Nombre banco ordenante:");
            $pdf->SetFont('Arial', '', $tamaño_texto_info);
            $pdf->Cell(60, $altura_celdas, $nombre_banco_ordenante);
            $pdf->Ln();
        }

        if($RFC_emisor_cuenta_beneficiario !='') {
            $pdf->SetFont('Arial', 'B', $tamaño_texto_titulos);
            $pdf->Cell(45, $altura_celdas, "RFC emisor cuenta beneficiario:");
            $pdf->SetFont('Arial', '', $tamaño_texto_info);
            $pdf->Cell(60, $altura_celdas, $RFC_emisor_cuenta_beneficiario);
            $pdf->Ln();
        }
        if($cuenta_beneficiario !='') {
            $pdf->SetFont('Arial', 'B', $tamaño_texto_titulos);
            $pdf->Cell(45, $altura_celdas, "Cuenta beneficiario:");
            $pdf->SetFont('Arial', '', $tamaño_texto_info);
            $pdf->Cell(60, $altura_celdas, $cuenta_beneficiario);
            $pdf->Ln(10);
        }


        $partidas = $resultado_pago_cliente_factura['registros'];

        foreach ($partidas as $documento){

            //print_r($documento);
            //Documento relacionado
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(30, $altura_celdas, "Documento relacionado");
            $pdf->Ln($salto_de_linea);
            $id_documento = utf8_decode($documento['pago_cliente_factura_factura_uuid']);
            $moneda_documento_relacionado = utf8_decode($documento['factura_moneda_descripcion']);
            $folio_documento = utf8_decode($documento['factura_folio']);
            $metodo_pago_documento = utf8_decode($documento['factura_metodo_pago_descripcion']);
            $serie_documento = utf8_decode($documento['factura_serie']);
            $importe_saldo_anterior = number_format($documento['pago_cliente_factura_importe_saldo_anterior'], 2);
            $numero_parcialidad = utf8_decode($documento['pago_cliente_factura_parcialidad']);
            $importe_pagado = number_format($documento['pago_cliente_factura_monto'], 2);
            $importe_saldo_insoluto = number_format($documento['pago_cliente_factura_importe_saldo_insoluto'], 2);

            //Primer renglon D.R.
            $pdf->SetFont('Arial','B',$tamaño_texto_titulos);
            $pdf->Cell(30, $altura_celdas, "Id Documento:");
            $pdf->SetFont('Arial','',$tamaño_texto_info);
            $pdf->Cell(52, $altura_celdas, $id_documento);
            $pdf->SetFont('Arial','B',$tamaño_texto_titulos);
            $pdf->Cell(60, $altura_celdas, "Moneda del documento relacionado:");
            $pdf->SetFont('Arial','',$tamaño_texto_info);
            $pdf->Cell(38, $altura_celdas, $moneda_documento_relacionado);
            $pdf->Ln($salto_de_linea);

            //Segundo renglon D.R.
            $pdf->SetFont('Arial','B',$tamaño_texto_titulos);
            $pdf->Cell(30, $altura_celdas, "Folio:");
            $pdf->SetFont('Arial','',$tamaño_texto_info);
            $pdf->Cell(52, $altura_celdas, $folio_documento);
            $pdf->SetFont('Arial','B',$tamaño_texto_titulos);;
            $pdf->Cell(60, $altura_celdas, utf8_decode("Método de pago del documento relacionado:"));
            $pdf->SetFont('Arial','',$tamaño_texto_info);
            $pdf->Cell(38, $altura_celdas, $metodo_pago_documento);
            $pdf->Ln($salto_de_linea);

            //Tercer renglon D.R.
            $pdf->SetFont('Arial','B',$tamaño_texto_titulos);
            $pdf->Cell(30, $altura_celdas, "Serie:");
            $pdf->SetFont('Arial','',$tamaño_texto_info);
            $pdf->Cell(52, $altura_celdas, $serie_documento);
            $pdf->SetFont('Arial','B',$tamaño_texto_titulos);
            $pdf->Cell(60, $altura_celdas, "Importe de saldo anterior:");
            $pdf->SetFont('Arial','',$tamaño_texto_info);
            $pdf->Cell(38, $altura_celdas, $importe_saldo_anterior);
            $pdf->Ln($salto_de_linea);

            //Cuarto renglon D.R.
            $pdf->SetFont('Arial','B',$tamaño_texto_titulos);
            $pdf->Cell(30, $altura_celdas, utf8_decode("Número de parcialidad:"));
            $pdf->SetFont('Arial','',$tamaño_texto_info);
            $pdf->Cell(52, $altura_celdas, $numero_parcialidad);
            $pdf->SetFont('Arial','B',$tamaño_texto_titulos);
            $pdf->Cell(60, $altura_celdas, "Importe pagado:");
            $pdf->SetFont('Arial','',$tamaño_texto_info);
            $pdf->Cell(38, $altura_celdas, $importe_pagado);
            $pdf->Ln($salto_de_linea);

            //Quinto renglon D.R.
            $pdf->Cell(82, $altura_celdas, "");
            $pdf->SetFont('Arial','B',$tamaño_texto_titulos);
            $pdf->Cell(60, $altura_celdas, "Importe de saldo insoluto:");
            $pdf->SetFont('Arial','',$tamaño_texto_info);
            $pdf->Cell(38, $altura_celdas, $importe_saldo_insoluto);
            $pdf->Ln(10);
        }

        $pdf->SetFillColor(51, 51, 51);
        $pdf->Cell(190, 1, " ", 0, 1, 'C', 1);

        $pdf->Output('D','P_'. $folio.'.pdf');
    }

    public function vista_preliminar(){
        $pago_cliente_id = $_GET['pago_cliente_id'];
        $this->pago_cliente_id = $pago_cliente_id;

        $pago_cliente_modelo = new Pago_Cliente($this->link);

        $resultado = $pago_cliente_modelo->obten_por_id('pago_cliente',$pago_cliente_id);

        $this->pago_cliente = $resultado['registros'][0];

        if($this->pago_cliente['pago_cliente_uuid'] == ''){
            $empresas = new Empresas();
            $empresa = $empresas->empresas[$_SESSION['numero_empresa']];
            $rfc_emisor = $empresa['rfc'];

            $pago_cliente_upd['status'] = 1;
            $pago_cliente_upd['status_factura'] = 'sin timbrar';
            $pago_cliente_upd['empresa_rfc'] =$rfc_emisor;

            $this->pago_cliente['pago_cliente_status_factura'] = 'sin timbrar';

            $pago_cliente_modelo->modifica_bd($pago_cliente_upd,'pago_cliente',$pago_cliente_id);
        }



        $pago_cliente_factura_modelo = new Pago_Cliente_Factura($this->link);

        $filtro = array('pago_cliente_id'=>$pago_cliente_id);
        $resultado = $pago_cliente_factura_modelo->filtro_and('pago_cliente_factura',$filtro);
        $this->pago_cliente_factura = $resultado['registros'];




    }





}