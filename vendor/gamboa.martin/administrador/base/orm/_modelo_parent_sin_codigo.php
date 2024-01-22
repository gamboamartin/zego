<?php
namespace base\orm;


use gamboamartin\errores\errores;
use stdClass;

class _modelo_parent_sin_codigo extends _modelo_parent {

    /**
     * @param array $keys_integra_ds
     * @return array|stdClass
     * @final rev
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
     * @param array $registro
     * @param int $id
     * @param bool $reactiva
     * @param array $keys_integra_ds
     * @return array|stdClass
     * @final rev
     */
    public function modifica_bd(array $registro, int $id, bool $reactiva = false,
                                array $keys_integra_ds = array('descripcion')): array|stdClass
    {
        $r_modifica_bd = parent::modifica_bd(registro :$registro,id: $id,reactiva:  $reactiva,keys_integra_ds:  $keys_integra_ds ); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al modificar registro '.$this->tabla,data:  $r_modifica_bd);
        }
        return $r_modifica_bd;
    }


}