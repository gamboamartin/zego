<?php
namespace gamboamartin\services;
use gamboamartin\calculo\calculo;
use gamboamartin\errores\errores;
use mysqli;
use stdClass;
use Throwable;

class services{
    private errores $error;
    public stdClass $data_conexion;
    public stdClass $name_files;
    public bool $corriendo;

    /**
     * @param string $path Ruta de servicio en ejecucion
     */
    public function __construct(string $path){
        $this->error = new errores();
        $data_service = $this->verifica_servicio(path: $path);
        if(errores::$error){
            $error = $this->error->error('Error al verificar servicio', $data_service);
            print_r($error);
            die('Error');
        }
        $this->corriendo = $data_service->corriendo;

        if($data_service->corriendo){
            echo 'El servicio esta corriendo '.$path;
            exit;
        }
    }



    /**
     * TODO
     * Crea un link de mysql con mysqli
     * @param string $host ruta de servidor
     * @param string $nombre_base_datos Nombre de la base de datos
     * @param string $pass password user
     * @param string $user user mysql
     * @return bool|array|mysqli
     */
    private function conecta_mysqli(string $host, string $nombre_base_datos, string $pass,
                                   string $user): bool|array|mysqli
    {
        $host = trim($host);
        $nombre_base_datos = trim($nombre_base_datos);
        $pass = trim($pass);
        $user = trim($user);

        $valida = $this->valida_conexion(host: $host,nombre_base_datos:  $nombre_base_datos,pass:  $pass,user:  $user);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos',data:  $valida);
        }

        try {
            $link = mysqli_connect($host, $user, $pass);
            mysqli_set_charset($link, 'utf8');
            $sql = "SET sql_mode = '';";
            $link->query($sql);

            $consulta = 'USE '.$nombre_base_datos;
            $link->query($consulta);
            return $link;

        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al conectarse',data:  $e);
        }
    }

    public function conexiones(array $empresa): array|stdClass
    {
        $data = new stdClass();
        $link_remote = $this->conecta_remoto_mysqli(empresa: $empresa);
        if(errores::$error){
            return $this->error->error('Error al conectar remoto', $link_remote);
        }
        $data->remote_host = $this->data_conexion->host;

        $link_local = $this->conecta_local_mysqli(empresa: $empresa);
        if(errores::$error){
            return $this->error->error('Error al conectar remoto', $link_local);
        }
        $data->local_host = $this->data_conexion->host;
        $data->remote = $link_remote;
        $data->local = $link_local;
        return $data;

    }

    public function conecta_local_mysqli(array $empresa): bool|array|mysqli
    {

        $data = $this->data_conecta(empresa: $empresa, tipo: '');
        if(errores::$error){
            return $this->error->error('Error al ajustar datos', $data);
        }

        $link = $this->conecta_mysqli(host: $data->host, nombre_base_datos:  $data->nombre_base_datos,
            pass: $data->pass,user:  $data->user);
        if(errores::$error){
            return $this->error->error('Error al conectar', $link);
        }

        return $link;
    }

    public function conecta_remoto_mysqli(array $empresa): bool|array|mysqli
    {

        $data = $this->data_conecta(empresa: $empresa, tipo: 'remote');
        if(errores::$error){
            return $this->error->error('Error al ajustar datos', $data);
        }

        $link = $this->conecta_mysqli(host: $data->host, nombre_base_datos:  $data->nombre_base_datos,
            pass: $data->pass,user:  $data->user);
        if(errores::$error){
            return $this->error->error('Error al conectar remoto', $link);
        }

        return $link;
    }

    /**
     * TODO
     * Genera los datos necesarios para la conexion a una bd de mysql, si remote, ajusta los datos de empresa
     * remote conexion
     * @param array $empresa arreglo de empresa
     * @param string $tipo tipo de conexion si remota o local
     * @return stdClass|array obj->host, obj->user, obj->pass, obj->nombre_base_datos
     */
    private function data_conecta(array $empresa, string $tipo): stdClass|array
    {

        $data = new stdClass();
        $keys_base = array('host','user','pass','nombre_base_datos');
        foreach($keys_base as $key_base){
            $data = $this->data_empresa(data: $data,empresa: $empresa,key_base: $key_base,tipo: $tipo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar datos', data:$data);
            }
        }

        $this->data_conexion = $data;

        return $data;
    }

    private function data_empresa(stdClass $data, array $empresa, string $key_base, string $tipo): array|stdClass
    {
        $key_empresa = $this->key_empresa_base(key_base: $key_base,tipo: $tipo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener key', data:$key_empresa);
        }

        if(!isset($empresa[$key_empresa])){
            return $this->error->error(mensaje: 'Error no existe key ['.$key_empresa.']', data:$empresa);
        }

        $data->$key_base = $empresa[$key_empresa];
        return $data;
    }


    /**
     * DOC ERROR
     * Genera los archivos necesarios para el bloqueo de un servicio
     * @param stdClass $name_files nombre de los archivos name_files->path_info, name_files->path_lock
     * name_files->path_info = path con fecha para informacion
     * name_files->path_lock = path de bloqueo de servicio
     * @return bool|array bool true si se generaron los archivos, array si hay error
     */
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

    public function finaliza_servicio(): stdClass
    {
        unlink($this->name_files->path_info);
        unlink($this->name_files->path_lock);
        return $this->name_files;
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

    /**
     * Genera el key de busqueda de una empresa, puede ser remote o vacio para local
     * @param string $tipo puede ser remote o vacio remote para conexion remota, vacio para conexion local
     * @return string con el key a buscar para empresas
     */
    private function key_empresa(string $tipo): string
    {
        $key = '';
        $tipo = trim($tipo);
        if($tipo === 'remote'){
            $key = $tipo.'_';
        }
        return $key;
    }

    private function key_empresa_base(string $key_base, string $tipo): array|string
    {
        $key = $this->key_empresa(tipo: $tipo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener key', data:$key);
        }
        return $key.$key_base;
    }

    /**
     * Genera el nombre de file para info de un servicio para poder identificar a que hora se ejecuto
     * @version 1.0.0
     * @param string $file_base Nombre del path del servicio en ejecucion
     * @return string|array
     */
    private function name_file_lock(string $file_base): string|array
    {
        $file_base = trim($file_base);
        if($file_base === ''){
            return $this->error->error(mensaje: 'Error file_base esta vacio', data: $file_base);
        }
        return $file_base.'.'.date('Y-m-d.H:i:s').'.info';
    }

    /**
     * Genera los nombres de los archivos para la ejecucion de un servicio genera un .lock y un .info
     * @version 1.0.0
     * @param string $path Ruta de servicio en ejecucion
     * @return array|stdClass
     */
    PUBLIC function name_files(string $path): array|stdClass
    {
        $path = trim($path);
        if($path === ''){
            return $this->error->error(mensaje: 'Error $path esta vacio', data: $path);
        }
        $path_lock = $path.'.lock';
        $path_info = $this->name_file_lock(file_base: $path);
        if(errores::$error){
            return $this->error->error('Error al generar name file', $path_info);
        }
        $data = new stdClass();
        $data->path_lock = $path_lock;
        $data->path_info = $path_info;

        $this->name_files = $data;

        return $data;
    }

    /**
     * Funcion para obtener la fecha de hoy menos n_dias
     * @param int $n_dias Numero de dias a restar a la fecha
     * @param string $tipo_val utiliza los patterns de las siguientes formas
     *          fecha=yyyy-mm-dd
     *          fecha_hora_min_sec_esp = yyyy-mm-dd hh-mm-ss
     *          fecha_hora_min_sec_t = yyyy-mm-ddThh-mm-ss
     * @return array|string
     */
    public function get_fecha_filtro_service(int $n_dias, string $tipo_val): array|string
    {
        $calculo = new calculo();
        $hoy = date($calculo->formats_fecha[$tipo_val]);

        $fecha = $calculo->obten_fecha_resta(fecha: $hoy,n_dias: $n_dias,tipo_val: $tipo_val);
        if(errores::$error){
            return $this->error->error('Error al obtener fecha', $fecha);
        }
        return $fecha;
    }

    /**
     *  TODO
     *  Verifica los datos necesarios para conectarse a una base de datos mysql
     * @param string $host Ruta servidor
     * @param string $nombre_base_datos nombre de base de datos
     * @param string $pass password de base de datos
     * @param string $user usuario de base de datos
     * @return bool|array bool true si todo es correcto
     */
    private function valida_conexion(string $host, string $nombre_base_datos, string $pass, string $user): bool|array
    {
        $host = trim($host);
        if($host === ''){
            return $this->error->error(mensaje: 'Error el host esta vacio', data: $host);
        }
        $nombre_base_datos = trim($nombre_base_datos);
        if($nombre_base_datos === ''){
            return $this->error->error(mensaje:'Error el $nombre_base_datos esta vacio',data: $nombre_base_datos);
        }
        $pass = trim($pass);
        if($pass === ''){
            return $this->error->error(mensaje:'Error el $pass esta vacio',data: $pass);
        }
        $user = trim($user);
        if($user === ''){
            return $this->error->error(mensaje:'Error el $pass esta vacio', data:$user);
        }
        return true;
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

    /**
     * @param string $path Ruta de servicio en ejecucion
     * @return stdClass|array
     */
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
