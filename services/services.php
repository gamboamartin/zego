<?php
namespace services;
use gamboamartin\errores\errores;
use gamboamartin\plugins\files;
use stdClass;

class services{
    private errores $error;
    public function __construct(){
        $this->error = new errores();
    }

    /**
     * ERROR UNIT
     * @param string $path
     * @return bool|array
     */
    private function genera_file_lock(string $path): bool|array
    {
        $path = trim($path);
        if($path === ''){
            return $this->error->error('Error path esta vacio', $path);
        }
        if(file_exists($path)){
            return $this->error->error('Error ya existe el path', $path);
        }
        file_put_contents($path, '');
        if(!file_exists($path)){
            return $this->error->error('Error al crear archivo lock', $path);
        }
        return true;
    }

    private function name_file_lock(string $file_base): string
    {
        return $file_base.'.'.date('Y-m-d.H:i:s').'.lock';
    }

    public function verifica_servicio(string $path): stdClass|array
    {

        $path_lock = $path.'.lock';
        $path_info = $path.'.'.date('Y-m-d.H.i.s');


        $servicio_corriendo = false;
        if(file_exists($path_lock)){
            $servicio_corriendo = true;
        }



        if(!$servicio_corriendo){

            $genera_file_lock = $this->genera_file_lock(path: $path_lock);
            if(errores::$error){
                return $this->error->error('Error al crear archivo lock', $genera_file_lock);
            }

            $genera_file_info = $this->genera_file_lock(path: $path_info);
            if(errores::$error){
                return $this->error->error('Error al crear archivo lock', $genera_file_info);
            }
        }
        $data = new stdClass();
        $data->path_lock = $path_lock;
        $data->path_info = $path_info;
        $data->corriendo = $servicio_corriendo;
        return $data;

    }
}
