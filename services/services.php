<?php
namespace services;
use gamboamartin\errores\errores;

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
    PUBLIC function genera_file_lock(string $path): bool|array
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

    public function verifica_servicio(string $path): bool|array
    {
        $servicio_corriendo = false;
        if(file_exists($path)){
            $servicio_corriendo = true;
        }

        if(!$servicio_corriendo){
            $genera_file = $this->genera_file_lock(path: $path);
            if(errores::$error){
                return $this->error->error('Error al crear archivo lock', $genera_file);
            }
        }
        return $servicio_corriendo;

    }
}
