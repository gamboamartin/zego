<?php
namespace gamboamartin\calculo;
use DateTime;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use JetBrains\PhpStorm\Pure;
use stdClass;
use Throwable;


class calculo{
    private array $meses_espaniol;
    public validacion $validaciones;
    public errores $error;
    public array $formats_fecha = array();

    /**
     *
     * calculo constructor.
     */
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validaciones = new validacion();
        $this->meses_espaniol = array('enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre',
            'octubre','noviembre','diciembre');

        $this->formats_fecha['fecha'] = 'Y-m-d';
        $this->formats_fecha['fecha_hora_min_sec_esp'] = 'Y-m-d H:i:s';
        $this->formats_fecha['fecha_hora_min_sec_t'] = 'Y-m-dTH:i:s';

    }

    final public function edad_hoy(string $fecha_nacimiento): array|int
    {
        try {
            $ahora = new DateTime(date("Y-m-d"));
            $fecha_nacimiento =  new DateTime($fecha_nacimiento);
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al obtener ahora',data:  $e);
        }
        $diferencia = $ahora->diff($fecha_nacimiento);
        return (int)$diferencia->format("%y");
    }

    /**
     * DOC UNIT
     * Obtiene las fechas restando el dia de hoy hasta el numero de dias y el formato dependiendo del tipo
     * @param int $n_dias_1 numero de dias a restar a partir de hoy
     * @param int $n_dias_2 numero de dias a restar a partir de hoy
     * @param string $tipo_val Formato de salida de date fecha = Y-m-d, fecha_hora_min_sec_esp = Y-m-d H:i:s
     *                          fecha_hora_min_sec_t = Y-m-dTH:i:s
     * @return array|stdClass stdclass
     *      string obj->fecha_1 resta de dias de n_dias_1  string obj->fecha_2 resta de dias de n_dias_2
     *      string obj->hoy fecha de hoy array si hay error
     */
    public function rangos_fechas(int $n_dias_1, int $n_dias_2, string $tipo_val): array|stdClass
    {
        $tipo_val = trim($tipo_val);
        $valida = $this->valida_tipo_val(tipo_val:$tipo_val);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tipo_val', data: $valida, params: get_defined_vars());
        }


        $hoy = date($this->formats_fecha[$tipo_val]);
        $fecha_1 = $this->obten_fecha_resta(fecha: $hoy, n_dias: $n_dias_1,
            tipo_val:$tipo_val );
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener dias', data: $fecha_1, params: get_defined_vars());
        }

        $fecha_2 = $this->obten_fecha_resta(fecha: $hoy, n_dias: $n_dias_2,
            tipo_val:$tipo_val );
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener dias',data:  $fecha_2, params: get_defined_vars());
        }
        $data = new stdClass();
        $data->fecha_1 = $fecha_1;
        $data->fecha_2 = $fecha_2;
        $data->hoy = $hoy;

        return $data;
    }

    /**
     * FULL
     * Funcion el tiempo actual en microsegundos
     *
     *
     * @example
     *      $tiempo_inicio = $calculo_main->microtime_float();
     *
     * @return int tiempo
     * @uses index
     */
    public function microtime_float():int{
        
        return time();
    }

    /**
     * P ORDER P INT ERROR
     * Funcion para obtener el mes en espaniol
     *
     * @param string $fecha
     *
     * @example
     *      $mes_letra = $calculo->obten_mes_espaniol(date('Y-m-d'));
     *
     * @return string|array mes en espaniol
     * @internal $this->validaciones->valida_fecha($fecha);
     * @internal $this->obten_numero_mes($fecha);
     * @uses formatos_valuador
     * @uses controlador_cliente
     */
    public function obten_mes_espaniol(string $fecha):string|array{
        $valida_fecha = $this->validaciones->valida_fecha(fecha: $fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar fecha', data: $valida_fecha,
                params: get_defined_vars());
        }
        $numero_mes = $this->obten_numero_mes(fecha: $fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener mes',data:  $numero_mes, params: get_defined_vars());
        }
        return $this->meses_espaniol[$numero_mes-1];
    }

    /**
     * P ORDER P INT ERROR
     * Funcion para obtener el numero de un mes
     *
     * @param string $fecha
     *
     * @example
     *      $numero_mes = $this->obten_numero_mes($fecha);
     *
     * @return string|array numero mes entero
     * @internal $this->validaciones->valida_fecha($fecha);
     * @uses calculo
     */
    private function obten_numero_mes(string $fecha):string|array{
        $valida_fecha = $this->validaciones->valida_fecha(fecha: $fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar fecha', data: $valida_fecha,
                params: get_defined_vars());
        }
        return (int)(date("m", strtotime($fecha)));
    }

    /**
     * TODO
     * Obtiene la fecha restando el numero de dias basado en la fecha del parametro
     * @param int $n_dias Numero de dias a restar a la fecha
     * @param string $fecha Fecha a la que se le estaran los dias
     * @param string $tipo_val
     *          utiliza los patterns de las siguientes formas
     *          fecha=yyyy-mm-dd
     *          fecha_hora_min_sec_esp = yyyy-mm-dd hh-mm-ss
     *          fecha_hora_min_sec_t = yyyy-mm-ddThh-mm-ss
     * @return string|array string con la fecha del resultado de la resta en dias array si error
     */
    public function obten_fecha_resta(string $fecha, int $n_dias, string $tipo_val = 'fecha'):string|array{
        $valida = $this->validaciones->valida_fecha(fecha: $fecha, tipo_val: $tipo_val);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar fecha',data:  $valida);
        }
        if($n_dias<0){
            return $this->error->error(mensaje: 'Error $n_dias debe ser mayor o igual a 0', data: $n_dias);
        }

        $format = $this->formats_fecha[$tipo_val];
        return date($format,strtotime($fecha."- $n_dias days"));
    }

    /**
     *
     * @param string $fecha_inicio
     * @param string $fecha_fin
     * @return int|array
     */
    public function n_dias_entre_fechas(string $fecha_inicio, string $fecha_fin): int|array
    {
        $valida = $this->validaciones->valida_fecha($fecha_inicio);
        if(errores::$error){
            return $this->error->error('$fecha_inicio invalida '.$fecha_inicio, $valida);
        }
        $valida = $this->validaciones->valida_fecha($fecha_fin);
        if(errores::$error){
            return $this->error->error('$fecha_fin invalida '.$fecha_fin, $valida);
        }
        try {
            $fecha_inicio_date = new DateTime($fecha_inicio);
            $fecha_fin_base = new DateTime($fecha_fin);
            $diff = $fecha_inicio_date->diff($fecha_fin_base);
        }
        catch (Throwable $e){
            $data = new stdClass();
            $data->parametros = new stdClass();
            $data->e = $e;
            $data->parametros->fecha_inicio = $fecha_inicio;
            $data->parametros->fecha_fin = $fecha_fin;
            return $this->error->error("Error al calcular diferencia de fechas", $data);
        }
        return (int)$diff->days + 1;
    }

    /**
     * Funcion para validar los parametros de uso de un tipo val el tipo val es relacionado al formato de fecha
     * @version 1.0.0
     * @param string $tipo_val
     *          utiliza los patterns de las siguientes formas
     *          fecha=yyyy-mm-dd
     *          fecha_hora_min_sec_esp = yyyy-mm-dd hh-mm-ss
     *          fecha_hora_min_sec_t = yyyy-mm-ddThh-mm-ss
     * @return bool|array bool true si no hay error
     */
    private function valida_tipo_val(string $tipo_val): bool|array
    {
        $tipo_val = trim($tipo_val);
        if($tipo_val === ''){
            return $this->error->error(mensaje: 'Error $tipo_val esta vacio', data: $tipo_val);
        }
        if(!isset($this->formats_fecha[$tipo_val])){
            return $this->error->error(mensaje: 'Error $tipo_val invalido', data: $tipo_val);
        }
        return true;
    }

}