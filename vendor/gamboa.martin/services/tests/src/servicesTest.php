<?php
namespace tests\base;

use gamboamartin\errores\errores;
use gamboamartin\services\services;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class servicesTest extends test {
    public errores $errores;
    private string $tipo_conexion = 'MYSQLI';
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct(name: $name, data: $data, dataName: $dataName, tipo_conexion: $this->tipo_conexion);
        $this->errores = new errores();
    }

    public function test_compara_estructura_synk(): void
    {
        errores::$error = false;

        $srv = new services(__FILE__);
        $srv->finaliza_servicio();

        $srv = new liberator($srv);
        $columnas_remotas = array();
        $local = array();
        $val = new stdClass();

        $columnas_remotas[0] = array();
        $columnas_remotas[0]['Field'] = 'x';
        $columnas_remotas[0]['Type'] = 'x';
        $columnas_remotas[0]['Null'] = 'x';
        $columnas_remotas[0]['Key'] = 'x';
        $columnas_remotas[0]['Extra'] = 'x';
        $local['Field'] =   'x';
        $local['Type'] =   'x';
        $local['Null'] =   'x';
        $local['Extra'] =   'x';
        $local['Key'] =   'x';

        $resultado = $srv->compara_estructura_synk($columnas_remotas, $local, $val);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
    }

    public function test_compara_estructura_tabla(): void
    {
        errores::$error = false;

        $srv = new services(__FILE__);
        $srv->finaliza_servicio();

        $srv = new liberator($srv);
        $local = array();
        $remoto = array();
        $val = new stdClass();
        $local['Type'] = '';
        $local['Null'] = '';
        $local['Key'] = '';
        $local['Default'] = '';
        $local['Extra'] = '';

        $remoto['Type'] = '';
        $remoto['Null'] = '';
        $remoto['Key'] = '';
        $remoto['Default'] = '';
        $remoto['Extra'] = '';



        $resultado = $srv->compara_estructura_tabla($local, $remoto, $val);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }


    public function test_conecta_mysqli(): void
    {
        errores::$error = false;

        $srv = new services(__FILE__);

        $srv = new liberator($srv);

        $host = '';
        $nombre_base_datos_r = '';
        $pass = '';
        $user = '';
        $resultado = $srv->conecta_mysqli($host, $nombre_base_datos_r, $pass, $user);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar datos',$resultado['mensaje']);
        errores::$error = false;

        $host = 'a';
        $nombre_base_datos_r = 'b';
        $pass = 'c';
        $user = 'd';
        $resultado = $srv->conecta_mysqli($host, $nombre_base_datos_r, $pass, $user);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al conectarse',$resultado['mensaje']);
        errores::$error = false;

        $srv->finaliza_servicio();

    }

    public function test_conecta_pdo(): void
    {
        errores::$error = false;

        $srv = new services(__FILE__);
        $srv->finaliza_servicio();

        //$srv = new liberator($srv);


        $conf_database = new stdClass();
        $conf_database->db_host = 'localhost';
        $conf_database->db_name = 'administrador';
        $conf_database->db_user = 'root';
        $conf_database->db_password = 'moro58';
        $conf_database->set_name = 'UTF8';
        $conf_database->sql_mode = '';
        $conf_database->time_out = '10';

        $resultado = $srv->conecta_pdo(conf_database: $conf_database);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);


    }

    public function test_crea_files(): void
    {
        errores::$error = false;

        $srv = new services(__FILE__);
        $srv->finaliza_servicio();
        $srv = new liberator($srv);
        if(file_exists('1')){
            unlink('1');
        }
        if(file_exists('2')){
            unlink('2');
        }
        if(file_exists('a')){
            unlink('a');
        }
        $path_info = '1';
        $path_lock = '2';
        $name_files = new stdClass();
        $name_files->path_lock = $path_lock;
        $name_files->path_info = $path_info;
        $resultado = $srv->crea_files($name_files);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertFileExists('1');
        $this->assertFileExists('2');


        if(file_exists('1')){
            unlink('1');
        }
        if(file_exists('2')){
            unlink('2');
        }
    }



    public function test_data_conecta(): void
    {
        errores::$error = false;

        $srv = new services(__FILE__);
        $srv = new liberator($srv);


        $tipo = '';
        $empresa = array();
        $empresa['host'] = 'a';
        $resultado = $srv->data_conecta($empresa, $tipo);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al generar datos',$resultado['mensaje']);

        errores::$error = false;


        $tipo = '';
        $empresa = array();
        $empresa['host'] = 'a';
        $empresa['user'] = 'b';
        $empresa['pass'] = 'b';
        $empresa['nombre_base_datos'] = 'b';
        $resultado = $srv->data_conecta($empresa, $tipo);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado->host);


        $srv->finaliza_servicio();
        errores::$error = false;
    }

    public function test_data_conexion_local(): void
    {
        errores::$error = false;

        $srv = new services(__FILE__);
        $srv->finaliza_servicio();

        //$srv = new liberator($srv);

        $name_model= 'adm_seccion';
        $namespace_model= 'gamboamartin\\administrador\\models';

        $resultado = $srv->data_conexion_local(name_model:$name_model, namespace_model:$namespace_model );

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;
    }

    public function test_data_conexion_remota(): void
    {
        errores::$error = false;

        $srv = new services(__FILE__);
        $srv->finaliza_servicio();

        $srv = new liberator($srv);

        $name_model= 'adm_seccion';
        $conf_database = new stdClass();
        $conf_database->db_host = 'localhost';
        $resultado = $srv->data_conexion_remota($conf_database, $name_model);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        errores::$error = false;
    }

    public function test_data_full_model(): void
    {
        errores::$error = false;

        $srv = new services(__FILE__);
        $srv->finaliza_servicio();

        $srv = new liberator($srv);


        $conf_database= new stdClass();
        $conf_database->db_host = 'localhost';
        $conf_database->db_name = 'a';
        $conf_database->db_user = 'a';
        $conf_database->db_password = 'a';
        $conf_database->set_name = 'a';
        $conf_database->sql_mode = '';
        $conf_database->time_out = 'a';
        $name_model = 'x';
        $resultado = $srv->data_full_model($conf_database, $name_model);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        errores::$error = false;
    }

    public function test_genera_files(): void
    {
        errores::$error = false;

        $srv = new services(__FILE__);
        $srv->finaliza_servicio();
        $srv = new liberator($srv);
        if(file_exists('1')){
            unlink('1');
        }
        if(file_exists('2')){
            unlink('2');
        }
        if(file_exists('a')){
            unlink('a');
        }
        $path_info = '1';
        $path_lock = '2';
        $resultado = $srv->genera_files($path_info, $path_lock);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);

        if(file_exists('1')){
            unlink('1');
        }
        if(file_exists('2')){
            unlink('2');
        }


    }

    public function test_genera_file_lock(): void
    {
        errores::$error = false;

        $srv = new services(__FILE__);


        $srv = new liberator($srv);

        $path = '';
        $resultado = $srv->genera_file_lock($path);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar path',$resultado['mensaje']);

        errores::$error = false;

        $path = 'x';
        if(file_exists($path)){
            unlink($path);
        }
        $resultado = $srv->genera_file_lock($path);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        $this->assertFileExists($path);

        errores::$error = false;

        $path = 'x';
        $resultado = $srv->genera_file_lock($path);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar path',$resultado['mensaje']);
        if(file_exists($path)){
            unlink($path);
        }
        errores::$error = false;
        $srv->finaliza_servicio();
    }

    public function test_init_tabla(): void
    {
        errores::$error = false;

        $srv = new services(__FILE__);
        $srv->finaliza_servicio();

        $srv = new liberator($srv);

        $resultado = $srv->init_val_tabla();
        $this->assertNotTrue( $resultado->existe);
        $this->assertNotTrue( $resultado->tipo_dato);
        $this->assertNotTrue( $resultado->null);
        $this->assertNotTrue( $resultado->key);
        $this->assertNotTrue( $resultado->default);
        $this->assertNotTrue( $resultado->extra);
        errores::$error = false;
    }

    public function test_limpia_row_alta(): void
    {
        errores::$error = false;

        $srv = new services(__FILE__);
        $srv->finaliza_servicio();

        $srv = new liberator($srv);


        $registro =   array();

        $resultado = $srv->limpia_row_alta($registro);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_name_file_lock(): void
    {
        errores::$error = false;

        $srv = new services(path: __FILE__);

        $srv = new liberator($srv);

        $file_base = '';
        $resultado = $srv->name_file_lock($file_base);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error file_base esta vacio',$resultado['mensaje']);

        errores::$error = false;

        $file_base = 'z';
        $resultado = $srv->name_file_lock($file_base);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('z', $resultado);
        $srv->finaliza_servicio();
        errores::$error = false;
    }

    public function test_name_files(): void
    {
        errores::$error = false;
        if(file_exists('test.file')){
            unlink('test.file');
        }

        $srv = new services(__FILE__);
        $srv = new liberator($srv);

        $path = '';
        $resultado = $srv->name_files($path);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $path esta vacio',$resultado['mensaje']);
        $srv->finaliza_servicio();

        errores::$error = false;
        if(file_exists('test.file')){
            unlink('test.file');
        }
        $path = 'test.file';
        $srv = new services($path);
        $srv = new liberator($srv);
        $resultado = $srv->name_files($path);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('test.file.lock',$resultado->path_lock);
        $this->assertStringContainsStringIgnoringCase('.info',$resultado->path_info);
        $this->assertStringContainsStringIgnoringCase('test.file.',$resultado->path_info);
        $srv->finaliza_servicio();
        errores::$error = false;
    }

    public function test_valida_conexion(): void
    {
        errores::$error = false;

        $srv = new services(__FILE__);

        $srv = new liberator($srv);

        $host = '';
        $nombre_base_datos = '';
        $pass = '';
        $user = '';
        $resultado = $srv->valida_conexion($host, $nombre_base_datos, $pass, $user);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el host esta vacio',$resultado['mensaje']);

        errores::$error = false;


        $host = 'a';
        $nombre_base_datos = 'b';
        $pass = 'c';
        $user = 'd';
        $resultado = $srv->valida_conexion($host, $nombre_base_datos, $pass, $user);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
        $srv->finaliza_servicio();
    }

    public function test_valida_conexion_modelo(): void
    {
        errores::$error = false;

        $srv = new services(__FILE__);
        $srv->finaliza_servicio();

        $srv = new liberator($srv);


        $conf_database= new stdClass();
        $conf_database->db_host = 'a';
        $conf_database->db_name = 'a';
        $conf_database->db_user = 'a';
        $conf_database->db_password = 'a';
        $conf_database->set_name = 'a';
        $conf_database->sql_mode = '';
        $conf_database->time_out = 'a';
        $name_model = 'x';
        $resultado = $srv->valida_conexion_modelo($conf_database, $name_model);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_valida_data_conexion(): void
    {
        errores::$error = false;

        $srv = new services(__FILE__);
        $srv->finaliza_servicio();

        $srv = new liberator($srv);

        $conf_database= new stdClass();


        $resultado = $srv->valida_data_conexion($conf_database);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar conf',$resultado['mensaje']);

        errores::$error = false;

        $conf_database= new stdClass();
        $conf_database->db_host = '';

        $resultado = $srv->valida_data_conexion($conf_database);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar conf',$resultado['mensaje']);

        errores::$error = false;

        $conf_database= new stdClass();
        $conf_database->db_host = '';
        $conf_database->db_name = '';

        $resultado = $srv->valida_data_conexion($conf_database);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar conf',$resultado['mensaje']);

        errores::$error = false;

        $conf_database= new stdClass();
        $conf_database->db_host = '';
        $conf_database->db_name = '';
        $conf_database->db_user = '';

        $resultado = $srv->valida_data_conexion($conf_database);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar conf',$resultado['mensaje']);

        errores::$error = false;

        $conf_database= new stdClass();
        $conf_database->db_host = '';
        $conf_database->db_name = '';
        $conf_database->db_user = '';
        $conf_database->db_password = '';

        $resultado = $srv->valida_data_conexion($conf_database);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar conf',$resultado['mensaje']);

        errores::$error = false;

        $conf_database= new stdClass();
        $conf_database->db_host = '';
        $conf_database->db_name = '';
        $conf_database->db_user = '';
        $conf_database->db_password = '';
        $conf_database->set_name = '';

        $resultado = $srv->valida_data_conexion($conf_database);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar conf',$resultado['mensaje']);

        errores::$error = false;

        $conf_database= new stdClass();
        $conf_database->db_host = '';
        $conf_database->db_name = '';
        $conf_database->db_user = '';
        $conf_database->db_password = '';
        $conf_database->set_name = '';
        $conf_database->sql_mode = '';
        $conf_database->time_out = '';

        $resultado = $srv->valida_data_conexion($conf_database);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar conf',$resultado['mensaje']);

        errores::$error = false;

        $conf_database= new stdClass();
        $conf_database->db_host = 'a';
        $conf_database->db_name = 'a';
        $conf_database->db_user = 'a';
        $conf_database->db_password = 'a';
        $conf_database->set_name = 'a';
        $conf_database->sql_mode = '';
        $conf_database->time_out = 'a';

        $resultado = $srv->valida_data_conexion($conf_database);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_valida_path(): void
    {
        errores::$error = false;
        if(file_exists('test.file')){
            unlink('test.file');
        }

        $srv = new services(__FILE__);
        $srv = new liberator($srv);

        $path = '';
        $resultado = $srv->valida_path($path);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error path esta vacio',$resultado['mensaje']);

        errores::$error = false;

        $path = 'a';
        $resultado = $srv->valida_path($path);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;

        $path = 'test.file';
        file_put_contents($path, '');
        $resultado = $srv->valida_path($path);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error ya existe el path',$resultado['mensaje']);

        unlink($path);
        errores::$error = false;
        $srv->finaliza_servicio();
    }

    public function test_valida_paths(): void
    {
        errores::$error = false;

        $srv = new services(__FILE__);
        $srv->finaliza_servicio();

        $srv = new liberator($srv);

        $path_info= '';
        $path_lock= '';


        $resultado = $srv->valida_paths($path_info,$path_lock);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar $path_info',$resultado['mensaje']);

        errores::$error = false;


        $path_info= 'a';
        $path_lock= '';


        $resultado = $srv->valida_paths($path_info,$path_lock);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar $path_lock',$resultado['mensaje']);

        errores::$error = false;


        $path_info= 'a';
        $path_lock= 'b';


        $resultado = $srv->valida_paths($path_info,$path_lock);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_verifica_servicio(): void
    {
        errores::$error = false;

        $srv = new services(__FILE__);
        $srv->finaliza_servicio();

        //$srv = new liberator($srv);

        $path = 'a';

        $resultado = $srv->verifica_servicio($path);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }



}