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

    public function test_genera_file_lock(): void
    {
        errores::$error = false;

        $srv = new services();

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
    }

    public function test_valida_path(): void
    {
        errores::$error = false;
        if(file_exists('test.file')){
            unlink('test.file');
        }

        $srv = new services();
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
    }



}