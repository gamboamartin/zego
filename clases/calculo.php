<?php

class calculo{

    public $costo_promedio;
    private $meses_espaniol;
    private $parametros;

    public function __construct(){
        $this->meses_espaniol = array('enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre',
            'octubre','noviembre','diciembre');
        if(isset($_SESSION['parametros'])){
            $this->parametros = $_SESSION['parametros'];
        }
    }



    public function obten_fecha_suma($fecha,$dias){
        $nueva_fecha = strtotime ( '+'.$dias.' day' , strtotime ( $fecha ) ) ;
        $nueva_fecha = date ( 'Y-m-d' , $nueva_fecha );

        return $nueva_fecha;

    }
    public function obten_fecha_resta($fecha,$dias){
        $nueva_fecha = strtotime ( '-'.$dias.' day' , strtotime ( $fecha ) ) ;
        $nueva_fecha = date ( 'Y-m-d' , $nueva_fecha );

        return $nueva_fecha;

    }
    public function impuesto_trasladado($cantidad,$monto_unitario,$tasa_cuota,$tipo){ // finallizadas


        return $this->calcula_impuesto($cantidad, $monto_unitario,$tasa_cuota,$tipo);


    }

    public function calcula_importe_partida($cantidad,$monto_unitario,$traslados,$retenciones,$descuento){

        $importe = $this->valida_cantidad_monto($cantidad, $monto_unitario);
        if(isset($importe['error'])){
            return $importe;
        }
        $importe = round($importe + $traslados,2);
        $importe = round($importe - $retenciones,2);
        $importe = round($importe - $descuento,2);

        return $importe;

    }

    public function valida_cantidad_monto($cantidad, $monto_unitario){
        if($cantidad <=0){
            return array('error'=>1,'mensaje'=>'La cantidad tiene que ser mayor a 0');
        }
        if($monto_unitario <=0){
            return array('error'=>1,'mensaje'=>'El monto tiene que ser mayor a 0');
        }
        $importe = round($cantidad * $monto_unitario,2);
        return $importe;
    }

    public function calcula_impuesto($cantidad, $monto_unitario,$tasa_cuota,$tipo){

        if($tasa_cuota <0){
            return array('error'=>1,'mensaje'=>'La tasa_cuota tiene que ser mayor a 0');
        }
        if($tipo !='tasa' && $tipo !='cuota'){
            return array('error'=>1,'mensaje'=>'No hay tipo definido para la operacion debe ser tasa o cuota');
        }

        $importe = $this->valida_cantidad_monto($cantidad, $monto_unitario);

        if(isset($importe['error'])){
            return $importe;
        }
        if($tipo == 'tasa'){
            $importe = round($importe * $tasa_cuota,2);
        }
        if($tipo == 'cuota'){
            $importe = round($tasa_cuota,2);
        }
        return $importe;
    }

    public function impuesto_retenido($cantidad,$monto_unitario,$tasa_cuota,$tipo){ // finallizadas

        $impuesto_retenido = $this->calcula_impuesto($cantidad, $monto_unitario,$tasa_cuota,$tipo);

        return $impuesto_retenido;


    }

    public function calcula_isr($monto, $fecha_pago, $n_dias,$link )
    {
        $salario_diario = round($monto / $n_dias, 2);
        $salario_mensual = round($salario_diario * DIAS_MES, 2);

        $isr_modelo = new isr($link, 'isr');

        $filtro = "$monto >= isr.limite_inferior AND $monto <= isr.limite_superior AND '$fecha_pago' >= isr.fecha_inicio AND '$fecha_pago' <= isr.fecha_fin ";
        $r_isr = $isr_modelo->filtro_and(array(), 'numeros', $filtro);

        $isr = $r_isr['registros'][0];


        $lim_inf = $isr['isr_limite_inferior'];
        $cuota_fija = $isr['isr_cuota_fija'];
        $excedente = $salario_mensual - $lim_inf;
        $porcentaje_excedente = $isr['isr_porcentaje_exedente'];

        $isr_excedente = round($excedente * $porcentaje_excedente / 100, 2);

        $isr_mensual = $isr_excedente + $cuota_fija;

        $isr_retorno = round($isr_mensual / DIAS_MES * $n_dias, 2);

        return $isr_retorno;
    }

    public function genera_folio_recibo($desarrollo_id , $link){

        $recibo_modelo = new recibo($link, 'recibo');

        $filtros = array('sucursal.desarrollo_id' => $desarrollo_id);
        //$filtro['desarrollo.id'] = $desarrollo_id;
        $r_recibo = $recibo_modelo->filtro_and($filtros,$tipo_filtro='numeros',$filtro_especial = false,$line=__LINE__,$file=__FILE__,
            $order=' ORDER BY recibo.folio DESC', $limit = ' LIMIT 1', $group_by=false);


        $desarrollo_id = str_pad($r_recibo['registros'][0]['recibo_folio'] + 1, 3, 0, STR_PAD_LEFT);

        return $desarrollo_id;
    }


    public function obten_antiguedad_fechas($fecha_inicial, $fecha_final){
        $fecha_inicial = date_create($fecha_inicial);
        $fecha_final = date_create($fecha_final);

        $diferencia = date_diff($fecha_inicial, $fecha_final,'%a');

        return array('n_years'=>$diferencia->y,'n_meses'=>$diferencia->m,'n_dias'=>$diferencia->d);

    }


    public function obten_year($fecha){
        $year = date("Y", strtotime($fecha));
        return $year;
    }
    public function obten_numero_mes($fecha){

        $mes = intval((date("m", strtotime($fecha))));
        return $mes;
    }
    public function obten_mes_espaniol($fecha){
        $numero_mes = $this->obten_numero_mes($fecha);
        $mes = $this->meses_espaniol[$numero_mes-1];
        return $mes;
    }

    /*public function multiplicacion(){
        if(isset($this->parametros['operando1'])) {
            $operando1 = $this->parametros['operando1'];
            $operando2 = $this->parametros['operando2'];
            return $operando1 * $operando2;
        }
        return '';
    }*/
}