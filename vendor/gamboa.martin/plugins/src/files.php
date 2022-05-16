<?php
namespace gamboamartin\plugins;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use SplFileInfo;

class files{
    private errores $error;
    #[Pure] public function __construct(){
        $this->error = new errores();
    }

    /**
     * Te dice el archivo es un lock del paquete servicios
     * @version 1.0.0
     * @param string $archivo Path o nombre del archivo
     * @return bool|array verdadero si es lock falso si no, array error
     */
    public function es_lock_service(string $archivo): bool|array
    {
        $valida = $this->valida_extension(archivo: $archivo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar extension', data: $valida);
        }

        $extension = $this->extension(archivo: $archivo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener extension', data: $extension);
        }
        $es_lock = false;
        if($extension === 'lock'){
            $es_lock = true;
        }
        return $es_lock;
    }

    /**
     * Obtiene la extension de un archivo mandando solamente el nombre del doc
     * @param string $archivo Path o nombre del archivo
     * @return string|array string = extension del archivo array error
     * @version 1.0.0
     */
    public function extension(string $archivo): string|array
    {
        $valida = $this->valida_extension(archivo: $archivo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar extension', data: $valida);
        }

        return (new SplFileInfo($archivo))->getExtension();

    }

    /**
     * P ORDER P INT
     * Funcion guarda el documento en la ruta definida
     *
     * @param string $ruta_file Ruta fisica donde está guardado el documento en el server
     * @param string $contenido_file
     *
     * @example
     *      $guarda = $controlador->guarda_archivo_fisico('./archivos/factura/'.$prefijo.$opciones['folio'].'.xml' ,trim($data_xml));
     *
     * @return string|array ruta de guardado
     * @uses formato_valuador
     * @uses todo el sistema
     */
    public function guarda_archivo_fisico(string $contenido_file, string $ruta_file):string|array{
        if($ruta_file === ''){
            return $this->error->error('Error $ruta_file esta vacia', $ruta_file);
        }
        if($contenido_file === ''){
            return $this->error->error('Error $contenido_file esta vacio', $contenido_file);
        }
        $ruta_file = strtolower($ruta_file);
        if(!file_put_contents($ruta_file, $contenido_file) || !file_exists($ruta_file)){
            return $this->error->error('Error al guardar archivo', $ruta_file);
        }
        chmod($ruta_file, 0777);
        return $ruta_file;
    }

    /**
     * P ORDER P INT
     * @param string $ruta
     * @param array $datas
     * @return array
     */
    public function listar_archivos(string $ruta, array $datas = array()):array{
        if (is_dir($ruta)) {
            if ($dh = opendir($ruta)) {
                while (($file = readdir($dh)) !== false) {
                    if (is_dir($ruta . $file) && $file !== "." && $file !== ".."){
                        $datas = $this->listar_archivos(ruta: $ruta . $file . "/",datas:  $datas);
                        if(errores::$error){
                            return $this->error->error('Error al listar archivos', $datas);
                        }
                    }
                    if(($file !== "." && $file !== "..")){
                        $datas[] = $ruta.'/'.$file;
                    }
                }
                closedir($dh);
            }
        }
        else {
            return $this->error->error('Error directorio invalido',$ruta);
        }
        return $datas;
    }

    /**
     * P ORDER P INT
     * @param string $dir
     * @param array $data
     * @param bool $mismo
     * @return array|mixed
     */
    public function rmdir_recursive(string $dir, array $data = array(), bool $mismo = false): mixed
    {
        $files = scandir($dir);
        array_shift($files);    // remove '.' from array
        array_shift($files);    // remove '..' from array

        foreach ($files as $file) {
            $file = $dir . '/' . $file;
            if (is_dir($file)) {
                $data = $this->rmdir_recursive(dir: $file, data: $data);
                rmdir($file);
            } else {
                unlink($file);
                $data[] = $file;
            }
        }
        if($mismo){
            rmdir($dir);
        }
        return $data;
    }

    /**
     * Valida los datos de un archivo para obtener una extension
     * @param string $archivo
     * @return bool|array
     */
    private function valida_extension(string $archivo): bool|array
    {
        $archivo = trim($archivo);
        if($archivo === ''){
            return $this->error->error(mensaje: 'Error archivo no puede venir vacio', data: $archivo);
        }
        $explode = explode('.', $archivo);
        if(count($explode) === 1){
            return $this->error->error(mensaje: 'Error el archivo no tiene extension', data: $explode);
        }

        $todo_vacio = true;
        foreach ($explode as $parte){
            $parte = trim($parte);
            if($parte !== ''){
                $todo_vacio = false;
            }
        }
        if($todo_vacio){
            return $this->error->error(mensaje: 'Error el archivo solo tiene puntos', data: $archivo);
        }


        return true;
    }
}
