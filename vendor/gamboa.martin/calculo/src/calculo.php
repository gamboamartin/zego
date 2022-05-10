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
    private array $formats_fecha = array();

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

    /**
     * DOC
     * Obtiene las fechas restando el dia de hoy hasta el numero de dias y el formato dependiendo del tipo
     * @param int $n_dias_1 numero de dias a restar a partir de hoy
     * @param int $n_dias_2 numero de dias a restar a partir de hoy
     * @param string $tipo_val Formato de salida de date fecha = Y-m-d, fecha_hora_min_sec_esp = Y-m-d H:i:s
     *                          fecha_hora_min_sec_t = Y-m-dTH:i:s
     * @return array|stdClass
     */
    public function rangos_fechas(int $n_dias_1, int $n_dias_2, string $tipo_val): array|stdClass
    {
        $hoy = date($this->formats_fecha[$tipo_val]);
        $fecha_1 = $this->obten_fecha_resta(fecha: $hoy, n_dias: $n_dias_1,
            tipo_val:$tipo_val );
        if(errores::$error){
            return $this->error->error('Error al obtener dias', $fecha_1);
        }

        $fecha_2 = $this->obten_fecha_resta(fecha: $hoy, n_dias: $n_dias_2,
            tipo_val:$tipo_val );
        if(errores::$error){
            return $this->error->error('Error al obtener dias', $fecha_2);
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
     * FULL
     * @param int $n_dias
     * @param string $fecha
     * @param string $tipo_val
     * @return string|array
     */
    public function obten_fecha_resta(string $fecha, int $n_dias, string $tipo_val = 'fecha'):string|array{
        $valida = $this->validaciones->valida_fecha(fecha: $fecha, tipo_val: $tipo_val);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar fecha',data:  $valida ,params: get_defined_vars());
        }
        if($n_dias<0){
            return $this->error->error(mensaje: 'Error $n_dias debe ser mayor o igual a 0', data: $n_dias,
                params: get_defined_vars());
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

}