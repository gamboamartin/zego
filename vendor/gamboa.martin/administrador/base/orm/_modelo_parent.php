<?php
namespace base\orm;


use gamboamartin\errores\errores;
use stdClass;

class _modelo_parent extends _base {

    /**
     * @param array $keys_integra_ds
     * @return array|stdClass
     * @final rev
     */
    public function alta_bd(array  $keys_integra_ds = array('codigo','descripcion')): array|stdClass
    {
        $this->registro = $this->campos_base(data:$this->registro,modelo: $this, keys_integra_ds: $keys_integra_ds);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar campo base',data: $this->registro);
        }

        /**
         * REFCATORIZAR
         */
        foreach ($this->registro as $campo=>$value){
            $existe_attr = false;
            $attrs = (array)$this->atributos;
            if(array_key_exists($campo, $attrs)){
                $existe_attr = true;
            }
            if(!$existe_attr){
                unset($this->registro[$campo]);
            }
        }


        $r_alta_bd =  parent::alta_bd();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar', data: $r_alta_bd);
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
                                array $keys_integra_ds = array('codigo','descripcion')): array|stdClass
    {

        $registro = $this->campos_base(data: $registro, modelo: $this, id: $id,keys_integra_ds:$keys_integra_ds );
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar campo base',data: $registro);
        }

        $r_modifica_bd = parent::modifica_bd(registro: $registro, id: $id,reactiva:  $reactiva); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al modificar registro '.$this->tabla,data:  $r_modifica_bd);
        }

        return $r_modifica_bd;
    }




}