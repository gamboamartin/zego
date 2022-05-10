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

    private function crea_files(stdClass $name_files): bool|array
    {
        $servicio_corriendo = false;
        if(file_exists($name_files->path_lock)){
            $servicio_corriendo = true;
        }

        if(!$servicio_corriendo){
            $files = $this->genera_files(path_info: $name_files->path_info, path_lock: $name_files->path_lock);
            if(errores::$error){
                return $this->error->error('Error al crear archivos ', $files);
            }
        }
        return $servicio_corriendo;
    }

    /**
     * ERROR UNIT DOC
     * Se genera archivo lock en la ruta de path
     * @param string $path ruta completa donde se creara archivo lock que se utilizara para verificar si el
     * servicio esta corriendo
     * @return bool|array bool = true si el archivo se genero con exito, array si existe error
     */
    private function genera_file_lock(string $path): bool|array
    {
        $path = trim($path);
        $valida = $this->valida_path(path: $path);
        if(errores::$error){
            return $this->error->error('Error al validar path', $valida);
        }

        file_put_contents($path, '');
        if(!file_exists($path)){
            return $this->error->error('Error al crear archivo lock', $path);
        }
        return true;
    }

    /**
     * ERROR DOC
     * Genera los archivos para bloquear un servicio y uno con la fecha para informacion de ejecucion
     * @param string $path_info Path info con fecha
     * @param string $path_lock Path para bloquear servcio
     * @return array|stdClass array si existe un error
     *  data = stdclass
     *  retorna un objeto obj->genera_file_lock = bool = true
     *  retorna un objeto obj->genera_file_info = bool = true
     *
     */
    private function genera_files(string $path_info, string $path_lock): array|stdClass
    {
        $path_info = trim($path_info);
        $path_lock = trim($path_lock);

        $valida = $this->valida_paths(path_info: $path_info, path_lock: $path_lock);
        if(errores::$error){
            return $this->error->error('Error al validar $paths', $valida);
        }

        $genera_file_lock = $this->genera_file_lock(path: $path_lock);
        if(errores::$error){
            return $this->error->error('Error al crear archivo lock', $genera_file_lock);
        }

        $genera_file_info = $this->genera_file_lock(path: $path_info);
        if(errores::$error){
            return $this->error->error('Error al crear archivo lock', $genera_file_info);
        }

        $data = new stdClass();
        $data->genera_file_lock = $genera_file_lock;
        $data->genera_file_info = $genera_file_info;
        return $data;
    }

    private function name_file_lock(string $file_base): string
    {
        return $file_base.'.'.date('Y-m-d.H:i:s');
    }

    private function name_files(string $path): array|stdClass
    {
        $path_lock = $path.'.lock';
        $path_info = $this->name_file_lock(file_base: $path);
        if(errores::$error){
            return $this->error->error('Error al generar name file', $path_info);
        }
        $data = new stdClass();
        $data->path_lock = $path_lock;
        $data->path_info = $path_info;
        return $data;
    }

    /**
     * FULL
     * Se verifica si el path esta vacio, o el archivo existe, el archivo no debe existir para retornar true
     * @param string $path ruta a validar
     * @return bool|array bool = true si el path no esta vacio array si hay error o si existe el archivo
     */
    PUBLIC function valida_path(string $path): bool|array
    {
        $path = trim($path);
        if($path === ''){
            return $this->error->error('Error path esta vacio', $path);
        }
        if(file_exists($path)){
            return $this->error->error('Error ya existe el path', $path);
        }
        return true;
    }

    /**
     * ERROR DOC
     * Verifica si los paths no estan vacios y que no existe el archivo de cada path
     * @param string $path_info Path info con fecha
     * @param string $path_lock Path para bloquear servcio
     * @return bool|array bool true si no hay errores
     */
    private function valida_paths(string $path_info, string $path_lock): bool|array
    {
        $path_info = trim($path_info);
        $valida = $this->valida_path(path: $path_info);
        if(errores::$error){
            return $this->error->error('Error al validar $path_info', $valida);
        }

        $path_lock = trim($path_lock);
        $valida = $this->valida_path(path: $path_lock);
        if(errores::$error){
            return $this->error->error('Error al validar $path_lock', $valida);
        }

        return true;
    }




    public function verifica_servicio(string $path): stdClass|array
    {

        $name_files = $this->name_files(path: $path);
        if(errores::$error){
            return $this->error->error('Error al generar name files', $name_files);
        }

        $servicio_corriendo = $this->crea_files(name_files: $name_files);
        if(errores::$error){
            return $this->error->error('Error al crear archivos ', $servicio_corriendo);
        }

        $data = new stdClass();
        $data->path_lock = $name_files->path_lock;
        $data->path_info = $name_files->path_info;
        $data->corriendo = $servicio_corriendo;
        return $data;

    }
}
