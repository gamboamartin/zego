<?php
namespace tests\base;

use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\factura;
use services\services;


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

        //$srv = new liberator($srv);

        $path = '';
        $resultado = $srv->genera_file_lock($path);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error path esta vacio',$resultado['mensaje']);

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
        $this->assertStringContainsStringIgnoringCase('Error ya existe el path',$resultado['mensaje']);
        if(file_exists($path)){
            unlink($path);
        }
        errores::$error = false;
    }



}