<?php
namespace gamboamartin\base_modelos;

use gamboamartin\validacion\validacion;


class base_modelos extends validacion
{

    /**
     *
     * Válida los datos de una lista de entrada, debe existir la clase y no pueden venir los elementos vacios
     * @version 0.1.0
     * También debe existe el namespace models
     * @param string $seccion Seccion en ejecucion
     * @param string $accion Accion en ejecucion
     * @return array|bool
     */
    final public function valida_datos_lista_entrada(string $accion, string $seccion): array|bool
    {
        $seccion = str_replace('models\\', '', $seccion);
        if ($seccion === '') {
            return $this->error->error(mensaje: 'Error seccion no puede venir vacio',data:  $seccion);
        }
        if ($accion === '') {
            return $this->error->error(mensaje:'Error no existe la accion', data:$accion);
        }

        return true;
    }


    /**
     *
     * Válida si una operacion en un registro está inactiva en su campo status data error
     * @version 1.0.0
     * @param bool $aplica_transaccion_inactivo recibe el valor verdadero o falso del estado de la transaccion
     * @param int $registro_id recibe el identificador del registro de la base de datos
     * @param string $tabla almacena el nombre de la tabla en la base de datos
     * @param array $registro almacena en un arreglo todos los datos del registro
     * @return array|bool indica que debe retornar un valor booleano, verdadero o falso si se cumplen las validaciones
     */
    final public function valida_transaccion_activa(bool  $aplica_transaccion_inactivo, array $registro,
                                              int $registro_id, string $tabla): array|bool
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error la tabla esta vacia', data: $tabla);
        }
        if (!$aplica_transaccion_inactivo) {
            if ($registro_id <= 0) {
                return $this->error->error(mensaje:'Error el id debe ser mayor a 0',data: $registro_id);
            }
            $key = $tabla . '_status';
            if (!isset($registro[$key])) {
                return $this->error->error(mensaje:'Error no existe el registro con el campo ' . $tabla . '_status',
                    data:$registro);
            }
            if ($registro[$tabla . '_status'] === 'inactivo') {
                return $this->error->error(mensaje:'Error el registro no puede ser manipulado',data: $registro);
            }
        }

        return true;
    }



}