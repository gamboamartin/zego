<?php
namespace tests\base;

use base\consultas_base;
use gamboamartin\errores\errores;
use gamboamartin\test\test;


class consultas_baseTest extends test {
    public errores $errores;
    private string $tipo_conexion = 'MYSQLI';
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct(name: $name, data: $data, dataName: $dataName, tipo_conexion: $this->tipo_conexion);
        $this->errores = new errores();
    }

    public function test_subconsultas(): void
    {
        errores::$error = false;

        $cb = new consultas_base();
        //$cb = new liberator($cb);


        $tabla = '';

        $resultado = $cb->subconsultas($tabla);

        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error tabla esta vacia',$resultado['mensaje']);

        errores::$error = false;


        $tabla = 'x';

        $resultado = $cb->subconsultas($tabla);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado);

        errores::$error = false;


        $tabla = 'moneda';

        $resultado = $cb->subconsultas($tabla);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('(SELECT 
                            tipo_cambio.monto 
                          FROM 
                            tipo_cambio ',$resultado);

        errores::$error = false;
    }



}