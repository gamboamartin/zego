<?php
namespace gamboamartin\services;
use base\orm\columnas;
use base\orm\modelo;
use base\orm\modelo_base;
use base\orm\validaciones;
use config\database;
use gamboamartin\calculo\calculo;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use JsonException;
use mysqli;
use PDO;
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
            $error = $this->error->error(mensaje: 'Error al verificar servicio',data:  $data_service);
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
     * @throws JsonException
     */
    final public function alta_por_host(stdClass $database, array $registros, string $tabla): bool|array
    {
        $data_remoto = $this->data_conexion_remota(conf_database: $database, name_model: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener datos remotos',data:  $data_remoto);
        }

        $modelo_remoto = (new modelo_base(link: $data_remoto->link))->genera_modelo(modelo: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar modelo',data:  $modelo_remoto);

        }

        $insersiones_data = $this->inserta_rows(modelo_remoto: $modelo_remoto,registros: $registros);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar registro', data: $insersiones_data);
        }
        return $insersiones_data;
    }

    /**
     * @throws JsonException
     */
    private function alta_row(modelo $modelo, array $registro): bool|array
    {
        $insertado = false;
        $existe_remoto = $modelo->existe_by_id(registro_id: $registro['id']);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al obtener pais remoto', data: $existe_remoto);
        }
        if(!$existe_remoto){
            $registro = $this->inserta_row_limpio(modelo: $modelo, registro: $registro);
            if(errores::$error){
                return (new errores())->error(mensaje: 'Error al insertar registro',data:  $registro);
            }
            $insertado = true;
        }
        return $insertado;
    }

    /**
     * @param array $columnas_remotas Conjunto de columnas remotas a comparar
     * @param array $local columna de registro local
     * @return array|stdClass
     */
    private function compara_estructura(array $columnas_remotas, array $local): array|stdClass
    {
        $val =$this->init_val_tabla();
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error inicializar datos',data:  $val);
        }

        $val = $this->compara_estructura_synk(columnas_remotas: $columnas_remotas, local: $local, val: $val);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error comparar datos', data: $val);

        }
        return $val;
    }

    /**
     * Compara las estructura de una tabla vs otra
     * @version 0.28.5
     * @param array $columnas_remotas Conjunto de columnas remotas a comparar
     * @param array $local columna de registro local
     * @param stdClass $val
     * @return array|stdClass
     */
    private function compara_estructura_synk(array $columnas_remotas, array $local, stdClass $val): array|stdClass
    {
        $val_ = $val;
        foreach ($columnas_remotas as $column_remoto){
            if(!is_array($column_remoto)){
                return (new errores())->error(mensaje: 'Error columns_remoto debe ser un array',data:  $column_remoto);
            }

            $keys = array('Field');
            $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $column_remoto);
            if(errores::$error){
                return (new errores())->error(mensaje: 'Error al validar columns_remoto',data:  $valida);
            }
            $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $local);
            if(errores::$error){
                return (new errores())->error(mensaje: 'Error al validar $local',data:  $valida);
            }

            if($column_remoto['Field'] === $local['Field']){

                $val_ = $this->compara_estructura_tabla(local:$column_remoto,remoto:  $local,val:  $val_);
                if(errores::$error){
                    return (new errores())->error(mensaje: 'Error comparar datos',data:  $val_);
                }
                break;
            }
        }
        return $val_;
    }

    /**
     * Compara que la estructura interna de dos tablas coincida
     * @param array $local Columnas en local
     * @param array $remoto Columnas en remoto
     * @param stdClass $val Validacion inicializada en false
     * @return stdClass|array
     * @version 0.23.1
     */
    private function compara_estructura_tabla(array $local, array $remoto,stdClass $val): stdClass|array
    {
        if(!isset($local['Default'])){
            $local['Default'] = '';
        }
        if(!isset($remoto['Default'])){
            $remoto['Default'] = '';
        }

        $keys = array('Type','Null','Key','Default','Extra');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $local, valida_vacio: false);
        if(errores::$error){
            return (new errores())->error('Error validar $local', $valida);
        }
        $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $remoto, valida_vacio: false);
        if(errores::$error){
            return (new errores())->error('Error validar $remoto', $valida);
        }

        foreach ($keys as $key){
            $remoto[$key] = trim($remoto[$key]);
            $local[$key] = trim($local[$key]);
        }

        $val->existe = true;
        if($remoto['Type'] === $local['Type']){
            $val->tipo_dato = true;
        }
        if($remoto['Null'] === $local['Null']){
            $val->null = true;
        }
        if($remoto['Key'] === $local['Key']){
            $val->key = true;
        }
        if($remoto['Default'] === $local['Default']){
            $val->default = true;
        }
        if($remoto['Extra'] === $local['Extra']){
            $val->extra = true;
        }

        return $val;
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
            return $this->error->error(mensaje: 'Error al conectar remoto', data: $link_remote);
        }
        $data->remote_host = $this->data_conexion->host;

        $link_local = $this->conecta_local_mysqli(empresa: $empresa);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al conectar remoto', data: $link_local);
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
            return $this->error->error(mensaje: 'Error al ajustar datos', data: $data);
        }

        $link = $this->conecta_mysqli(host: $data->host, nombre_base_datos:  $data->nombre_base_datos,
            pass: $data->pass,user:  $data->user);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al conectar',data:  $link);
        }

        return $link;
    }

    /**
     * Conexion a base de datos visa mysql con pdo
     * @version 0.4.0
     * @param stdClass|database $conf_database Debe tener db_host, db_name, db_user, db_password, set_name, sql_mode, time_out
     * @return PDO|array
     */
    public function conecta_pdo(stdClass|database $conf_database): PDO|array
    {

        $valida = $this->valida_data_conexion(conf_database:  $conf_database);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar conf',data:  $valida);
        }

        try {
            $link = new PDO("mysql:host=$conf_database->db_host;dbname=$conf_database->db_name",
                $conf_database->db_user, $conf_database->db_password);

            $link->query("SET NAMES '$conf_database->set_name'");
            $sql = "SET sql_mode = '$conf_database->sql_mode';";
            $link->query($sql);
            $sql = "SET innodb_lock_wait_timeout=$conf_database->time_out;";
            $link->query($sql);

        } catch (Throwable $e) {
            return $this->error->error(mensaje: 'Error al conectar', data: $e);
        }

        return $link;
    }



    public function conecta_remoto_mysqli(array $empresa): bool|array|mysqli
    {

        $data = $this->data_conecta(empresa: $empresa, tipo: 'remote');
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ajustar datos', data: $data);
        }

        $link = $this->conecta_mysqli(host: $data->host, nombre_base_datos:  $data->nombre_base_datos,
            pass: $data->pass,user:  $data->user);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al conectar remoto', data: $link);
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

    /**
     * Obtiene los datos de una conexion local link, modelo, columnas, n_columnas
     * @param string $name_model Nombre del modelo a obtener datos
     * @param string $namespace_model
     * @return array|stdClass
     * @version 0.5.0
     */
    public function data_conexion_local(string $name_model, string $namespace_model): array|stdClass
    {
        $db = new database();

        $valida = $this->valida_conexion_modelo(conf_database: $db, name_model: $name_model);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar conf',data:  $valida);
        }

        $data = $this->data_full_model(conf_database: $db, name_model: $name_model, namespace_model: $namespace_model);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al obtener datos de conexion', data: $data);

        }
        return $data;

    }

    /**
     * Genera los datos de una conexion remota
     * @param stdClass $conf_database Configuracion de conexion a la base de datos
     * @param string $name_model Nombre del modelo
     * @version 0.37.6
     * @verfuncion 0.1.0
     * @author mgamboa
     * @fecha 2022-07-25 16:14
     * @return array|stdClass
     */
    private function data_conexion_remota(stdClass $conf_database, string $name_model): array|stdClass
    {
        $valida = $this->valida_conexion_modelo(conf_database: $conf_database, name_model: $name_model);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar conf',data:  $valida);
        }

        $data = $this->data_full_model(conf_database: $conf_database, name_model: $name_model);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al obtener datos de conexion', data: $data);

        }

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
     * Genera y obtiene los datos de una conexion y un modelo a sincroniozar
     * @param stdClass|database $conf_database Configuracion de la base de datos
     * @param string $name_model Nombre del modelo a ejecutar la sincronizacion
     * @param string $namespace_model
     * @return array|stdClass
     * @version 0.36.6
     * @verfuncion 0.1.0
     * @author mgamboa
     * @fecha 2022-07-25 16:01
     */
    private function data_full_model(
        stdClass|database $conf_database, string $name_model, string $namespace_model): array|stdClass
    {
        $valida = $this->valida_data_conexion(conf_database:  $conf_database);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar conf',data:  $valida);
        }

        $name_model = trim($name_model);
        if($name_model === ''){
            return (new errores())->error(mensaje: 'Error name model esta vacio',data:  $name_model);
        }

        $link = $this->conecta_pdo(conf_database: $conf_database);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al conectar a base de datos',data:  $link);
        }

        $modelo = (new modelo_base(link: $link))->genera_modelo(modelo: $name_model, namespace_model: $namespace_model);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al generar modelo',data:  $modelo);
        }

        $columnas = (new columnas())->columnas_bd_native(modelo:$modelo, tabla_bd: $modelo->tabla);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al obtener columnas local', data: $columnas);

        }
        $n_columnas = count($columnas);

        $data = new stdClass();
        $data->link = $link;
        $data->modelo = $modelo;
        $data->columnas = $columnas;
        $data->n_columnas = $n_columnas;

        return $data;
    }


    /**
     *
     * Genera los archivos necesarios para el bloqueo de un servicio
     * @version 0.16.0
     * @param stdClass $name_files nombre de los archivos name_files->path_info, name_files->path_lock
     * name_files->path_info = path con fecha para informacion
     * name_files->path_lock = path de bloqueo de servicio
     * @return bool|array bool true si se generaron los archivos, array si hay error
     */
    private function crea_files(stdClass $name_files): bool|array
    {
        $keys = array('path_lock','path_info');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys, registro: $name_files);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $name_files ',data:  $valida);
        }

        $servicio_corriendo = false;
        if(file_exists($name_files->path_lock)){
            $servicio_corriendo = true;
        }

        if(!$servicio_corriendo){
            $files = $this->genera_files(path_info: $name_files->path_info, path_lock: $name_files->path_lock);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al crear archivos ',data:  $files);
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
     *
     * Se genera archivo lock en la ruta de path
     * @version 0.12.0
     * @param string $path ruta completa donde se creara archivo lock que se utilizara para verificar si el
     * servicio esta corriendo
     * @return bool|array bool = true si el archivo se genero con exito, array si existe error
     */
    private function genera_file_lock(string $path): bool|array
    {
        $path = trim($path);
        $valida = $this->valida_path(path: $path);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar path', data: $valida);
        }

        file_put_contents($path, '');
        if(!file_exists($path)){
            return $this->error->error(mensaje: 'Error al crear archivo lock', data: $path);
        }
        return true;
    }

    /**
     *
     * Genera los archivos para bloquear un servicio y uno con la fecha para informacion de ejecucion
     * @version 0.14.0
     * @param string $path_info Path info con fecha
     * @param string $path_lock Path para bloquear servicio
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
            return $this->error->error(mensaje: 'Error al validar $paths', data: $valida);
        }

        $genera_file_lock = $this->genera_file_lock(path: $path_lock);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al crear archivo lock',data:  $genera_file_lock);
        }

        $genera_file_info = $this->genera_file_lock(path: $path_info);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al crear archivo lock', data: $genera_file_info);
        }

        $data = new stdClass();
        $data->genera_file_lock = $genera_file_lock;
        $data->genera_file_info = $genera_file_info;
        return $data;
    }

    /**
     * Inicializa en falso los elementos a validar de una tabla de dos bases de datos
     * @version 0.20.1
     * @return stdClass existe, tipo_dato, null, key, default, extra
     */
    private function init_val_tabla(): stdClass
    {
        $data = new stdClass();
        $data->existe = false;
        $data->tipo_dato = false;
        $data->null = false;
        $data->key = false;
        $data->default = false;
        $data->extra = false;
        return $data;


    }

    /**
     * @throws JsonException
     */
    private function inserta_row_limpio(modelo $modelo, array $registro): array|stdClass
    {
        $registro = $this->limpia_row_alta(registro: $registro);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al limpiar registro', data: $registro);

        }
        $r_alta = $modelo->alta_registro(registro: $registro);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar registro', data: $r_alta);
        }
        return $r_alta;
    }

    /**
     * @throws JsonException
     */
    private function inserta_rows( modelo $modelo_remoto, array $registros): bool|array
    {
        $insersiones = 0;
        foreach ($registros as $registro){

            $insertado = $this->alta_row(modelo: $modelo_remoto, registro: $registro);
            if(errores::$error){
                return (new errores())->error(mensaje: 'Error al insertar registro', data: $insertado);
            }
            if($insertado){
                $insersiones++;
            }
            if($insersiones>=10){
                break;
            }
        }
        return true;
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
     * Limpia datos para alta bd
     * @param array $registro Registro  previo a insersion
     * @version 0.31.6
     * @verfuncion 0.1.0
     * @author mgamboa
     * @fecha 2022-07-25 15:32
     * @return array
     */
    private function limpia_row_alta(array $registro): array
    {
        unset($registro['usuario_alta_id'],$registro['usuario_update_id']);
        return $registro;
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
    private function name_files(string $path): array|stdClass
    {
        $path = trim($path);
        if($path === ''){
            return $this->error->error(mensaje: 'Error $path esta vacio', data: $path);
        }
        $path_lock = $path.'.lock';
        $path_info = $this->name_file_lock(file_base: $path);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar name file', data: $path_info);
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
            return $this->error->error(mensaje: 'Error al obtener fecha', data: $fecha);
        }
        return $fecha;
    }

    private function valida_columna(array $column_local, string $key, stdClass $val): bool|array
    {
        if(is_numeric($key)){
            return  (new errores())->error(mensaje: 'Error el key no puede ser un numero', data: $key);
        }
        if(!$val->$key){
            return  (new errores())->error(mensaje: 'Error no existe columna en remoto', data: $column_local);
        }
        return true;
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
     * Valida la conexion de una base de datos junto con la existencia del modelo
     * @param stdClass|database $conf_database Configuracion de conexion a la base de datos
     * @param string $name_model Nombre del modelo a verificar
     * @version 0.33.6
     * @verfuncion 0.1.0
     * @author mgamboa
     * @fecha 2022-07-22 15:44
     * @return bool|array
     */
    private function valida_conexion_modelo(stdClass|database $conf_database, string $name_model): bool|array
    {
        $valida = $this->valida_data_conexion(conf_database:  $conf_database);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar conf',data:  $valida);
        }
        $name_model = trim($name_model);
        if($name_model === ''){
            return (new errores())->error(mensaje: 'Error name model esta vacio',data:  $name_model);
        }

        return true;
    }

    /**
     * Valida los datos minimos de una conexion a base de datos
     * @version 0.7.0
     * @param stdClass|database $conf_database Configuracion de la base datos como params
     * @return bool|array
     */
    private function valida_data_conexion(stdClass|database $conf_database): bool|array
    {
        $keys = array('db_host','db_name','db_user','db_password','set_name','sql_mode','time_out');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $conf_database,valida_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar conf',data:  $valida);
        }
        $keys = array('db_host','db_name','db_user','db_password','set_name','time_out');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $conf_database);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar conf',data:  $valida);
        }

        return true;
    }

    private function valida_estructura(stdClass $data_local, stdClass $database, string $tabla): bool|array
    {
        $data_remoto = $this->data_conexion_remota(conf_database: $database, name_model: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener datos remotos', data: $data_remoto);
        }

        $valida = $this->verifica_tabla_synk(data_local: $data_local,data_remoto:  $data_remoto, database: $database,
            tabla:  $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error comparar datos ', data: $valida);
        }
        return $valida;
    }

    private function valida_estructuras_remotas(stdClass $data_local, array $servers_in_data, string $tabla): bool|array
    {
        foreach ($servers_in_data as $database){

            $valida = $this->valida_estructura(data_local: $data_local, database: $database, tabla: $tabla);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error comparar datos ', data: $valida);
            }

        }
        return true;
    }

    public function valida_init_services(stdClass $data_local, stdClass|database $db, string $tabla): bool|array
    {

        if(!isset($db->servers_in_data)){
            return $this->error->error(mensaje: 'Error no existe database->servers_in_data',data:  $db);
        }

        $valida = $this->valida_estructuras_remotas(data_local: $data_local, servers_in_data: $db->servers_in_data,
            tabla:  $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error comparar datos ', data: $valida);
        }
        return $valida;
    }

    /**
     *
     * Se verifica si el path esta vacio, o el archivo existe, el archivo no debe existir para retornar true
     * @version v0.2.0
     * @param string $path ruta a validar
     * @return bool|array bool = true si el path no esta vacio array si hay error o si existe el archivo
     */
    private function valida_path(string $path): bool|array
    {
        $path = trim($path);
        if($path === ''){
            return $this->error->error(mensaje: 'Error path esta vacio', data: $path);
        }
        if(file_exists($path)){
            return $this->error->error(mensaje: 'Error ya existe el path', data: $path);
        }
        return true;
    }

    /**
     *
     * Verifica si los paths no estan vacios y que no existe el archivo de cada path
     * @version 0.9.0
     * @param string $path_info Path info con fecha
     * @param string $path_lock Path para bloquear servicio
     * @return bool|array bool true si no hay errores
     */
    private function valida_paths(string $path_info, string $path_lock): bool|array
    {
        $path_info = trim($path_info);
        $valida = $this->valida_path(path: $path_info);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $path_info',data:  $valida);
        }

        $path_lock = trim($path_lock);
        $valida = $this->valida_path(path: $path_lock);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $path_lock',data:  $valida);
        }

        return true;
    }

    /**
     * @param array $columnas_local columnas de registro local
     * @param array $columnas_remotas Conjunto de columnas remotas a comparar
     * @return array|stdClass
     */
    private function verifica_columnas(array $columnas_local, array $columnas_remotas ): array|stdClass
    {
        $valida = new stdClass();
        foreach ($columnas_local as $column_local){
            $valida = $this->verifica_estructura_por_columna(column_local: $column_local, columnas_remotas: $columnas_remotas);
            if(errores::$error){
                return (new errores())->error(mensaje: 'Error comparar datos ', data: $valida);
            }
        }
        return $valida;
    }

    /**
     * @param array $column_local columna de registro local
     * @param array $columnas_remotas Conjunto de columnas remotas a comparar
     * @return array|stdClass
     */
    private function verifica_estructura_por_columna(array $column_local, array $columnas_remotas): array|stdClass
    {
        $val = $this->compara_estructura(columnas_remotas: $columnas_remotas, local: $column_local);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error comparar datos', data: $val);
        }

        foreach ($val as $columna_estructura=>$value){
            if(is_numeric($columna_estructura)){
                return  (new errores())->error(mensaje: 'Error $columna_estructura no puede ser un numero',
                    data: $columna_estructura);
            }
            $valida = $this->valida_columna(column_local: $column_local, key:$columna_estructura,val:  $val);
            if(errores::$error){
                return (new errores())->error(mensaje:'Error comparar datos '.$columna_estructura,data: $valida);
            }
        }
        return $val;
    }

    /**
     * @param stdClass $data_local Datos de conexion local
     * @param stdClass $data_remoto Datos de conexion remota
     * @return bool|array
     */
    private function verifica_numero_columnas(stdClass $data_local, stdClass $data_remoto): bool|array
    {
        if($data_remoto->n_columnas > $data_local->n_columnas){
            return (new errores())->error(mensaje: 'Error las columnas remotas son mayores a las columnas locales',
                data: array('remoto'=>$data_remoto->columnas,'local'=>$data_local->columnas));

        }
        if($data_remoto->n_columnas < $data_local->n_columnas){
            return (new errores())->error(mensaje: 'Error las columnas remotas son menores a las columnas locales',
                data: array('remoto'=>$data_remoto->columnas,'local'=>$data_local->columnas));
        }

        return true;
    }

    /**
     * Verifica si un servicio esta corriendo
     * @version 0.19.0
     * @param string $path Ruta de servicio en ejecucion
     * @return stdClass|array
     */
    final public function verifica_servicio(string $path): stdClass|array
    {
        $path = trim($path);
        if($path === ''){
            return $this->error->error(mensaje: 'Error $path esta vacio', data: $path);
        }

        $name_files = $this->name_files(path: $path);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar name files', data: $name_files);
        }

        $servicio_corriendo = $this->crea_files(name_files: $name_files);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al crear archivos ',data:  $servicio_corriendo);
        }

        $data = new stdClass();
        $data->path_lock = $name_files->path_lock;
        $data->path_info = $name_files->path_info;
        $data->corriendo = $servicio_corriendo;
        return $data;

    }

    /**
     * @param stdClass $data_local
     * @param stdClass $data_remoto
     * @param stdClass|database $database
     * @param string $tabla
     * @return bool|array
     */
    private function verifica_tabla_synk(stdClass $data_local,stdClass $data_remoto, stdClass|database $database, string $tabla): bool|array
    {
        $existe_tabla = (new validaciones())->existe_tabla(link:  $data_remoto->link, name_bd: $database->db_name,
            tabla: $tabla);
        if(!$existe_tabla){
            return  (new errores())->error(mensaje: 'Error no existe la tabla',data:  $tabla);
        }

        $valida = $this->verifica_numero_columnas(data_local: $data_local, data_remoto: $data_remoto);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error comparar datos '.$tabla,data:  $valida);
        }

        $valida = $this->verifica_columnas(columnas_local: $data_local->columnas,
            columnas_remotas:  $data_remoto->columnas);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error comparar datos '.$tabla, data: $valida);
        }

        return true;
    }
}
