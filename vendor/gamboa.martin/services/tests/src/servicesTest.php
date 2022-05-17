<?php
namespace tests\base;

use gamboamartin\errores\errores;
use gamboamartin\services\services;
use gamboamartin\test\liberator;
use gamboamartin\test\test;



class servicesTest extends test {
    public errores $errores;
    private string $tipo_conexion = 'MYSQLI';
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct(name: $name, data: $data, dataName: $dataName, tipo_conexion: $this->tipo_conexion);
        $this->errores = new errores();
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



}