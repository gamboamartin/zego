<?php
namespace models;
use gamboamartin\calculo\calculo;
use gamboamartin\errores\errores;
use gamboamartin\services\services;
use gamboamartin\validacion\validacion;

class factura extends modelos{

    public function alta_bd($registro, $tabla): array
    {


        $registro = $this->init_registro_receptor(registro: $registro);
        if(errores::$error){
            return $this->error->error('Error al asignar datos de receptor', $registro);
        }


        $r_alta = parent::alta_bd($registro, $tabla); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error('Error al dar de alta factura', $r_alta);
        }


        return $r_alta;
    }

    /**
     * ERROR UNIT
     * @param array $registro
     * @return array
     */
    private function init_registro_receptor(array $registro): array
    {
        $registro = $this->cp_receptor(registro: $registro);
        if(errores::$error){
            return $this->error->error('Error al asignar cp', $registro);
        }
        $registro = $this->regimen_fiscal_receptor(registro: $registro);
        if(errores::$error){
            return $this->error->error('Error al asignar regimen fiscal', $registro);
        }

        return $registro;
    }

    /**
     * ERROR UNIT
     * @param array $registro
     * @return array
     */
    public function regimen_fiscal_receptor(array $registro): array
    {
        if(isset($registro['cliente_id'])){
            $cliente_id = $registro['cliente_id'];
            $registro = $this->aplica_reg_fis_cte(cliente_id: $cliente_id, registro: $registro);
            if(errores::$error){
                return $this->error->error('Error al asignar regimen fiscal', $registro);
            }
        }

        return $registro;
    }

    public function uso_cfdi(array $registro): array
    {
        if(isset($registro['cliente_id'])){
            $cliente_id = $registro['cliente_id'];
            $registro = $this->aplica_uso_cfdi_cte(cliente_id: $cliente_id, registro: $registro);
            if(errores::$error){
                return $this->error->error('Error al asignar regimen fiscal', $registro);
            }
        }

        return $registro;
    }

    public function facturas_relacionadas(int $factura_id){
        $factura_relacionada_modelo = new factura_relacionada($this->link);

        $filtro = array('factura.id'=>$factura_id);
        $r_factura_relacionada = $factura_relacionada_modelo->filtro_and('factura_relacionada',$filtro);
        return $r_factura_relacionada['registros'];
    }

    /**
     * ERROR UNIT
     * @param int $cliente_id
     * @param array $registro
     * @return array
     */
    private function aplica_reg_fis_cte(int $cliente_id, array $registro): array
    {

        $cliente = (new cliente($this->link))->cliente(cliente_id: $cliente_id);
        if(errores::$error){
            return $this->error->error('Error al al obtener cliente', $cliente);
        }

        if(!isset($cliente['cliente_regimen_fiscal_id']) || trim($cliente['cliente_regimen_fiscal_id']) === ''){
            return $this->error->error('Error no existe regimen fiscal en cliente', $cliente);
        }

        $regimen_fiscal_id = $cliente['cliente_regimen_fiscal_id'];

        $registro = $this->asigna_regimen_fiscal_rec(regimen_fiscal_id: $regimen_fiscal_id, registro: $registro);
        if(errores::$error){
            return $this->error->error('Error al asignar regimen fiscal', $registro);
        }
        return $registro;
    }

    private function aplica_uso_cfdi_cte(int $cliente_id, array $registro): array
    {

        $cliente = (new cliente($this->link))->cliente(cliente_id: $cliente_id);
        if(errores::$error){
            return $this->error->error('Error al al obtener cliente', $cliente);
        }

        if(!isset($cliente['cliente_uso_cfdi_id']) || trim($cliente['cliente_uso_cfdi_id']) === ''){
            return $this->error->error('Error no existe regimen fiscal en cliente', $cliente);
        }

        $uso_cfdi_id = $cliente['cliente_uso_cfdi_id'];

        $registro = $this->asigna_uso_cfdi_rec(uso_cfdi_id: $uso_cfdi_id, registro: $registro);
        if(errores::$error){
            return $this->error->error('Error al asignar regimen fiscal', $registro);
        }
        return $registro;
    }

    /**
     * ERROR UNIT
     * @param int $regimen_fiscal_id
     * @param array $registro
     * @return array
     */
    private function asigna_regimen_fiscal_rec(int $regimen_fiscal_id, array $registro): array
    {

        $regimen_fiscal = (new regimen_fiscal($this->link))->get_regimen_fiscal(
            regimen_fiscal_id: $regimen_fiscal_id);
        if(errores::$error){
            return $this->error->error('Error al al obtener regimen_fiscal', array($regimen_fiscal, $registro));
        }
        $registro['cliente_rf'] = $regimen_fiscal['regimen_fiscal_codigo'];
        return $registro;
    }

    private function asigna_uso_cfdi_rec(int $uso_cfdi_id, array $registro): array
    {

        $uso_cfdi = (new uso_cfdi($this->link))->get_uso_cfdi(
            uso_cfdi_id: $uso_cfdi_id);
        if(errores::$error){
            return $this->error->error('Error al al obtener uso_cfdi', array($uso_cfdi, $registro));
        }
        $registro['uso_cfdi_codigo'] = $uso_cfdi['uso_cfdi_codigo'];
        $registro['uso_cfdi_descripcion'] = $uso_cfdi['uso_cfdi_descripcion'];
        return $registro;
    }

    /**
     * ERROR UNIT
     * @param int $factura_id
     * @return array
     */
    public function inicializa_data_receptor(int $factura_id): array
    {
        $upd = array();
        $factura = $this->registro(id:$factura_id, tabla: 'factura');
        if(errores::$error){
            return $this->error->error('Error al obtener factura', $factura);
        }

        if($factura['factura_status_factura'] === 'sin timbrar') {

            $registro = array();
            if (isset($factura['factura_cliente_id']) && $factura['factura_cliente_id'] !== '') {
                $registro['cliente_id'] = $factura['factura_cliente_id'];
            }

            $registro = $this->init_registro_receptor(registro: $registro);
            if (errores::$error) {
                return $this->error->error('Error al obtener datos factura', $registro);
            }
            $upd = $this->modifica_bd(registro: $registro, tabla: 'factura', id: $factura_id);
            if (errores::$error) {
                return $this->error->error('Error al actualizar factura', $upd);
            }
        }
        return $upd;
    }

    public function modifica_bd($registro, $tabla, $id): array
    {
        $registro = $this->init_registro_receptor(registro: $registro);
        if(errores::$error){
            return $this->error->error('Error al asignar datos de receptor', $registro);
        }
        $resultado =  parent::modifica_bd($registro, $tabla, $id); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error('Error al modificar factura', $resultado);
        }
        return $resultado;
    }

    /**
     * ERROR UNIT
     * @param array $registro
     * @return array
     */
    private function cp_receptor(array $registro): array
    {
        if(isset($registro['cliente_id']) && $registro['cliente_id']!==''){

            $registro = $this->asigna_cp_receptor(registro: $registro);
            if(errores::$error){
                return $this->error->error('Error al asignar cp', $registro);
            }
        }
        return $registro;
    }

    public function sql_filtro_factura(string $fecha, int $insertado): string
    {
        $sql_fecha_alta = "factura.fecha_alta >= '$fecha'";
        $sql_fecha_alta .= " AND factura.insertado = $insertado ";
        return $sql_fecha_alta;
    }



    /**
     * ERROR UNIT
     * @param array $registro
     * @return array
     */
    private function asigna_cp_receptor(array $registro): array
    {

        $keys = array('cliente_id');
        $valida = (new validacion())->valida_existencia_keys($keys, $registro);
        if(errores::$error){
            return $this->error->error('Error al al validar registro', $valida);
        }

        $cliente_id = $registro['cliente_id'];
        $cp = (new cliente($this->link))->cp($cliente_id);
        if(errores::$error){
            return $this->error->error('Error al obtener cp del cliente del cliente_id: '.$cliente_id, $cp);
        }
        $registro['cliente_cp'] = $cp;
        return $registro;
    }

    public function obten_facturas_con_saldo($cliente_id){
        $consulta = $this->genera_consulta_base('factura');
        #$where = " WHERE cliente.id = $cliente_id AND factura.saldo > 0.01 ORDER BY factura.folio DESC";
        $where = " WHERE cliente.id = $cliente_id AND factura.fecha BETWEEN '2023-10-01' AND '2023-12-31' AND factura.saldo > 0.01 ORDER BY factura.folio ASC";
        #$where = " WHERE cliente.id = $cliente_id  AND ((factura.fecha BETWEEN '2024-06-07' AND '2024-06-15') OR (factura.fecha BETWEEN '2024-09-01' AND '2024-11-15')) AND factura.saldo > 0.01 ORDER BY factura.folio ASC;";

        $consulta = $consulta.$where;


        $resultado = $this->ejecuta_consulta($consulta);
        return $resultado;
    }

    /**
     * ERROR
     * @param $tabla
     * @param $sql
     * @param $limit
     * @return array
     */
    public function obten_registros($tabla, $sql = '', $limit = ''): array
    {
	    $consulta_base = $this->genera_consulta_base($tabla);
        if(errores::$error){
            return $this->error->error('Error al generar sql', $consulta_base);
        }
	    $order = ' ORDER BY factura.fecha DESC ';
	    $consulta_base .= $order . ' ' . $limit;

        $result = $this->ejecuta_consulta($consulta_base);
        if(errores::$error){
            return $this->error->error('Error al obtener facturas', $result);
        }

        $result = $this->clean_rows_numeric(result: $result);
        if(errores::$error){
            return $this->error->error('Error al limpiar result', $result);
        }

        return $result;
	}

    /**
     * ERROR
     * @param array $result
     * @return array
     */
    private function clean_rows_numeric(array $result): array
    {
        $keys_numeric = array('factura_total');
        $result = $this->clean_result_numeric(keys_numeric: $keys_numeric,result: $result);
        if(errores::$error){
            return $this->error->error('Error al limpiar result', $result);
        }
        return $result;
    }

    /**
     * ERROR
     * @param array $keys_numeric
     * @param array $result
     * @return array
     */
    private function clean_result_numeric(array $keys_numeric, array $result): array
    {
        foreach ($result['registros'] as $indice=>$factura){
            $result = $this->clean_for_key_numeric(factura: $factura,indice: $indice,keys_numeric: $keys_numeric,
                result: $result);
            if(errores::$error){
                return $this->error->error('Error al limpiar result', $result);
            }
        }
        return $result;
    }

    /**
     * ERROR
     * @param array $factura
     * @param int $indice
     * @param array $keys_numeric
     * @param array $result
     * @return array
     */
    private function clean_for_key_numeric(array $factura, int $indice, array $keys_numeric, array $result): array
    {
        foreach($keys_numeric as $campo){
            $result = $this->asigna_numeric_limpio(campo: $campo,factura: $factura,indice: $indice,result: $result);
            if(errores::$error){
                return $this->error->error('Error al limpiar result', $result);
            }
        }
        return $result;
    }

    /**
     * ERROR
     * @param string $campo
     * @param array $factura
     * @param int $indice
     * @param array $result
     * @return array
     */
    private function asigna_numeric_limpio(string $campo, array $factura, int $indice, array $result): array
    {
        $campo = trim($campo);
        $factura = $this->limpia_campo_row_inexistente(campo: $campo,row:  $factura);
        if(errores::$error){
            return $this->error->error('Error al limpiar factura', $factura);
        }

        $result = $this->result_rows_numeric(campo: $campo,factura: $factura,indice: $indice,result: $result);
        if(errores::$error){
            return $this->error->error('Error al limpiar result', $result);
        }
        return $result;
    }

    /**
     * ERROR
     * @param string $campo
     * @param array $factura
     * @param int $indice
     * @param array $result
     * @return array
     */
    private function result_rows_numeric(string $campo, array $factura, int $indice, array $result): array
    {
        $campo = trim($campo);
        $factura[$campo] = trim($factura[$campo]);
        if($factura[$campo] === ''){
            $result['registros'][$indice][$campo] = 0;
        }
        return $result;
    }



    public function obten_saldo_real_factura($factura_id){
        $factura_modelo = new factura($this->link);
        $pago_cliente_factura_modelo = new pago_cliente_factura($this->link);
        $nota_credito_modelo = new nota_credito($this->link);


        $r_factura = $factura_modelo->obten_por_id('factura',$factura_id);



        $filtro = array('factura.id'=>$factura_id,'pago_cliente.status'=>1,'pago_cliente_factura.status'=>1);
        $r_pago_cliente_factura = $pago_cliente_factura_modelo->filtro_and('pago_cliente_factura',$filtro);



        $filtro = array('factura.id'=>$factura_id,'nota_credito.status'=>1);
        $r_nota_credito = $nota_credito_modelo->filtro_and('nota_credito',$filtro);


        $factura  = $r_factura['registros'][0];
        $pagos_cliente_factura  = $r_pago_cliente_factura['registros'];
        $notas_credito  = $r_nota_credito['registros'];


        $total_factura = round($factura['factura_total'],2);
        $total_pagos = 0;
        $total_notas_credito = 0;

        foreach($pagos_cliente_factura as $pago_cliente_factura){
            $total_pagos = round($total_pagos + round($pago_cliente_factura['pago_cliente_factura_monto']));
        }


        foreach($notas_credito as $nota_credito){
            $monto_iva = round($nota_credito['nota_credito_monto'] * $nota_credito['nota_credito_porcentaje_iva'],2);
            $total_nota_credito = round($nota_credito['nota_credito_monto'] + $monto_iva,2);
            $total_notas_credito = round($total_notas_credito + round($total_nota_credito));
        }



        $total_cargado_factura = round($total_notas_credito + $total_pagos,2);

        $saldo = $total_factura - $total_cargado_factura;

        return $saldo;






    }

    public function obten_facturas_activas($cliente_id){
        $consulta_base = $this->genera_consulta_base('factura');
        $where = " WHERE factura.status_factura<> 'cancelado' AND factura.cliente_id = $cliente_id ";
        $order = ' ORDER BY factura.fecha DESC ';
        $consulta_base = $consulta_base.$where.$order;



        $result = $this->ejecuta_consulta($consulta_base);
        return $result;
    }

	   public function obten_registros_filtrados($tabla,$fecha, $rfc, $razon_social, $status_factura, $status_descarga, $folio){
	    $consulta_base = $this->genera_consulta_base($tabla);
	    $where = '   ';

	    if($fecha || $rfc || $razon_social || $status_factura || $status_descarga || $folio){
	    	$where = $where.' WHERE ';
	    }

	    if($fecha){
	    	$where = $where."   factura.fecha LIKE '%$fecha%' " ;
	    }
	    if($rfc){
	    	$and = ' ';
	    	if($fecha){
	    		$and = ' AND ';
	    	}
	    	$where = $where." $and  factura.cliente_rfc LIKE '%$rfc%' " ;
	    }

		if($razon_social){
	    	$and = ' ';
	    	if($fecha || $rfc){
	    		$and = ' AND ';
	    	}
	    	$where = $where." $and  factura.cliente_razon_social LIKE '%$razon_social%' " ;
	    }
	    if($status_factura){
	    	$and = ' ';
	    	if($fecha || $rfc || $razon_social){
	    		$and = ' AND ';
	    	}
	    	$where = $where." $and  factura.status_factura LIKE '%$status_factura%' " ;
	    }

	    if($status_descarga){
	    	$and = ' ';
	    	if($fecha || $rfc || $razon_social || $status_factura){
	    		$and = ' AND ';
	    	}
	    	$where = $where." $and  factura.status_descarga LIKE '$status_descarga' " ;
	    }
           if($folio){
               $and = ' ';
               if($fecha || $rfc || $razon_social || $status_factura || $status_descarga){
                   $and = ' AND ';
               }
               $where = $where." $and  factura.folio = '$folio' " ;
           }



	    $consulta_base = $consulta_base.$where;
	    $order = ' ORDER BY factura.fecha DESC ';
	    $consulta_base = $consulta_base.$order;


        $result = $this->ejecuta_consulta($consulta_base);

        return $result;
	}

}
