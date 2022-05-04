<?php
namespace models;
use gamboamartin\errores\errores;

class insumo extends modelos {

    /**
     * ERROR
     * @param int $insumo_id
     * @return array
     */
    public function data_insumo(int $insumo_id): array
    {
        $insumo = $this->registro(id:$insumo_id, tabla: 'insumo');
        if(errores::$error){
            return $this->error->error('Error al obtener insumo', $insumo);
        }
        return $insumo;
    }

    public function modifica_bd($registro, $tabla, $id){
        $campos = "";


        if(!$registro['impuesto_retenido_id']){
            $this->foraneas_no_insertables['insumo'][]='impuesto_retenido_id';
            $this->foraneas_no_insertables['insumo'][]='tipo_factor_retenido_id';
            $this->foraneas_no_insertables['insumo'][]='factor_retenido';
        }
        else{
            if(!$registro['tipo_factor_retenido_id']){
                return array('mensaje'=>'Si tiene impuesto retenido tiene que seleccionar un impuesto de factor retenido',
                    'error'=>True);
            }
        }

        $campos_no_insertables = array();
        if(array_key_exists($tabla,$this->foraneas_no_insertables)){
            $campos_no_insertables = $this->foraneas_no_insertables[$tabla];
        }


        $existe_status = false;
        foreach ($registro as $campo => $value) {
            if($campo == 'status'){
                $existe_status = true;
            }
            $campo = addslashes($campo);
            $value = addslashes($value);
            if(!in_array($campo,$campos_no_insertables)) {
                $campos .= $campos == "" ? "$campo = '$value'" : ", $campo = '$value'";
            }
        }
        if(!$existe_status){
            $campos = $campos." , status = '0' ";
        }

        $visible = "";
        if($tabla == 'accion'){
            if(array_key_exists('visible', $registro)){
                if($registro['visible']==1){
                    $visible = " , visible = '1' ";
                }
                else{
                    $visible = " , visible = '0' ";
                }
            }
            else{
                $visible = " , visible = '0' ";
            }
        }

        $consulta = "UPDATE ". $tabla." SET ".$campos." $visible WHERE id = $id";


        $this->link->query($consulta);
        if($this->link->error){
            return array('mensaje'=>$this->link->error.' '.$consulta, 'error'=>True);
        }
        else{
            $registro_id = $id;
            return array(
                'mensaje'=>'Registro modificado con éxito', 'error'=>False, 'registro_id'=>$registro_id);
        }
    }

}
