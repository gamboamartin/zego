<?php
namespace services_base;
use gamboamartin\errores\errores;
use models\modelos;

class src{
    private errores $error;
    public function __construct(){
        $this->error = new errores();
    }
    public function existe_local(int $id, modelos $modelo_local, modelos $modelo_remota, string $tabla): array
    {

        $r_remota = array('id'=>$id, 'tabla'=>$tabla, 'no se aplico');
        $existe_en_local = $modelo_local->existe_por_id($id, $tabla);
        if(errores::$error){
            return $this->error->error('Error al obtener cliente local', $existe_en_local);
        }

        if($existe_en_local){
            $r_remota = $modelo_remota->upd_ins_existe_remoto($id, $tabla);
            if(errores::$error){
                return  $this->error->error('Error al actualizar', $existe_en_local);
            }
        }



        return $r_remota;

    }
}
