<?php
namespace base\orm;


use gamboamartin\errores\errores;
use stdClass;

class _modelo_parent_sin_codigo extends _modelo_parent {

    /**
     * Inserta un registro solo con la descripcion
     * @param array $keys_integra_ds Key para integrar la descripcion select
     * @return array|stdClass
     * @finalrev rev
     * @version 10.49.2
     */
    public function alta_bd(array $keys_integra_ds = array('descripcion')): array|stdClass
    {

        $r_alta_bd = parent::alta_bd(keys_integra_ds: $keys_integra_ds); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta registro '.$this->tabla,data:  $r_alta_bd);
        }
        return $r_alta_bd;
    }

    /**
     * Ejecuta la modificacion de un registro dejando default la descripcion select basada en la descripcion
     * @param array $registro Registro en proceso
     * @param int $id Identificador del registro
     * @param bool $reactiva valida la reactivacion
     * @param array $keys_integra_ds Keys default para la descripcion select
     * @return array|stdClass
     * @finalrev
     */
    public function modifica_bd(array $registro, int $id, bool $reactiva = false,
                                array $keys_integra_ds = array('descripcion')): array|stdClass
    {
        $r_modifica_bd = parent::modifica_bd(registro :$registro,id: $id,reactiva:  $reactiva,
            keys_integra_ds:  $keys_integra_ds ); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al modificar registro '.$this->tabla,data:  $r_modifica_bd);
        }
        return $r_modifica_bd;
    }


}
