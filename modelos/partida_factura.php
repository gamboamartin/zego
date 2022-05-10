<?php
namespace models;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;

class partida_factura extends modelos {

    /**
     * ERROR
     * @param $registro
     * @param $tabla
     * @return array
     */
    public function alta_bd($registro, $tabla): array
    {

        $r_alta_bd = parent::alta_bd($registro, $tabla); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error('Error al dar5 de alta partida', $r_alta_bd);
        }

        if(isset($registro['factura_id'])) {
            $init_partidas = $this->init_obj_partidas(factura_id: $registro['factura_id']);
            if (errores::$error) {
                return $this->error->error('Error al inicializar partidas', $init_partidas);
            }
        }


        return $r_alta_bd;
    }

    /**
     * ERROR
     * @param int $factura_id
     * @return array|bool
     */
    public function init_obj_partidas(int $factura_id): array|bool
    {

        $factura = (new factura($this->link))->registro(id:$factura_id,tabla: 'factura');
        if (errores::$error) {
            return $this->error->error('Error al obtener factura', $factura);
        }
        if($factura['factura_status_factura']==='sin timbrar') {

            $partidas = $this->partidas(factura_id: $factura_id);
            if (errores::$error) {
                return $this->error->error('Error al obtener partidas', $partidas);
            }
            $r_part_fact = $this->actualiza_partidas_obj(partidas: $partidas);
            if (errores::$error) {
                return $this->error->error('Error al actualizar partida insumo', $r_part_fact);
            }
        }

        return true;
    }

    /**
     * ERROR UNIT
     * @param array $partidas
     * @return bool|array
     */
    private function actualiza_partidas_obj(array $partidas): bool|array
    {
        foreach($partidas as $partida){
            $partida_factura_id = $partida['partida_factura_id'];
            if(isset($partida['insumo_id'])){
                $r_part_fact = $this->obj_upd_partida(partida: $partida,partida_factura_id: $partida_factura_id);
                if(errores::$error){
                    return $this->error->error('Error al actualizar partida insumo', $r_part_fact);
                }
            }
        }
        return true;
    }

    public function elimina_partida_vacia(array $keys, array $partida): stdClass|array
    {
        $data = array();
        $del = false;
        $existe_valor = $this->existe_algun_valor(keys: $keys,registro:  $partida);
        if(errores::$error){
            return $this->error->error('Error al validar si existe valor', $existe_valor);
        }

        if(!$existe_valor){
            $elimina = $this->elimina_bd('partida_factura', $partida['partida_factura_id']);
            if(errores::$error){
                return $this->error->error('Error al eliminar partida', $elimina);
            }
            $data['partida_factura_id'] = $partida['partida_factura_id'];
            $del = true;
        }
        $data_r = new stdClass();
        $data_r->data = $data;
        $data_r->del = $del;
        return $data_r;
    }

    /**
     * ERROR UNIT
     * @param int $factura_id
     * @return array
     */
    private function partidas(int $factura_id): array
    {
        $filtros['partida_factura.factura_id'] = $factura_id;
        $r_partida = (new partida_factura($this->link))->filtro_and('partida_factura', $filtros);
        if(errores::$error){
            return $this->error->error('Error al obtener partidas', $r_partida);
        }
        return $r_partida['registros'];
    }


    /**
     * ERROR UNIT
     * @param array $partida
     * @param int $partida_factura_id
     * @return bool|array
     */
    private function obj_upd_partida(array $partida, int $partida_factura_id): bool|array
    {
        $keys = array('insumo_id');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys, registro: $partida);
        if(errores::$error){
            return $this->error->error('Error al validar partida', $valida);
        }

        $insumo_id = $partida['insumo_id'];
        $insumo = (new insumo($this->link))->data_insumo(insumo_id: $insumo_id);
        if(errores::$error){
            return $this->error->error('Error al obtener insumo', $insumo);
        }

        $valida = $this->valida_obj(insumo: $insumo);
        if(errores::$error){
            return $this->error->error('Error al validar objeto de imp', $valida);
        }

        $r_part_fact = $this->actualiza_obj_diferente(insumo: $insumo,partida: $partida,partida_factura_id: $partida_factura_id);
        if(errores::$error){
            return $this->error->error('Error al actualizar partida insumo', $r_part_fact);
        }
        return $r_part_fact;
    }

    /**
     * ERROR UNIT
     * @param array $insumo
     * @return bool|array
     */
    private function valida_obj(array $insumo): bool|array
    {
        if(!isset($insumo['insumo_id'])){
            return $this->error->error(mensaje: 'Error no existe insumo_id en insumo', data: $insumo);
        }
        $insumo_id = $insumo['insumo_id'];
        if(!isset($insumo['insumo_obj_imp'])){
            return $this->error->error(mensaje: 'Error el insumo no tiene un insumo_obj_imp', data: $insumo,
                seccion_header: 'insumo',accion_header: 'modifica', registro_id: $insumo_id);
        }
        if(trim($insumo['insumo_obj_imp'])===''){
            return $this->error->error(mensaje: 'Error el insumo tiene vacio insumo_obj_imp',data:  $insumo,
                seccion_header: 'insumo', accion_header: 'modifica',registro_id: $insumo_id);
        }
        return true;
    }

    /**
     * ERROR UNIT
     * @param array $insumo
     * @param array $partida
     * @param int $partida_factura_id
     * @return bool|array
     */
    private function actualiza_obj_diferente(array $insumo, array $partida, int $partida_factura_id): bool|array
    {
        $diferente = $this->compare_obj_imp(insumo: $insumo,partida: $partida);
        if(errores::$error){
            return $this->error->error('Error al validar si es diferente el obj imp', $diferente);
        }

        if($diferente){
            $obj_imp = trim($insumo['insumo_obj_imp']);
            $r_part_fact = $this->upd_imp_obj(obj_imp: $obj_imp, partida_factura_id: $partida_factura_id);
            if(errores::$error){
                return $this->error->error('Error al actualizar partida insumo', $r_part_fact);
            }
        }
        return $diferente;
    }

    /**
     * ERROR UNIT
     * @param array $insumo
     * @param array $partida
     * @return bool|array
     */
    private function compare_obj_imp(array $insumo, array $partida): bool|array
    {
        if(!isset($insumo['insumo_obj_imp'])){
            return $this->error->error('Error el insumo no tiene insumo_obj_imp cargado', $insumo);
        }

        if(!isset($partida['partida_factura_obj_imp'])){
            $partida['partida_factura_obj_imp'] = '';
        }
        $obj_imp = trim($insumo['insumo_obj_imp']);
        if($obj_imp === ''){
            return $this->error->error('Error el $obj_imp en insumo esta vacio', $obj_imp);
        }

        $obj_imp_ant = trim($partida['partida_factura_obj_imp']);

        return $obj_imp!==$obj_imp_ant;

    }

    /**
     * ERROR UNIT
     * @param string $obj_imp
     * @param int $partida_factura_id
     * @return array
     */
    PUBLIC function upd_imp_obj(string $obj_imp, int $partida_factura_id ): array
    {
        $part_fact_upd['obj_imp'] = $obj_imp;
        $r_part_fact = $this->modifica_bd(registro: $part_fact_upd, tabla: 'partida_factura', id:$partida_factura_id);
        if(errores::$error){
            return $this->error->error('Error al actualizar partida insumo', $r_part_fact);
        }
        return $r_part_fact;
    }

}
