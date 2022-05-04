<?php
namespace tests\base;

use base\consultas_base;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


class consultas_baseTest extends test {
    public errores $errores;
    private string $tipo_conexion = 'MYSQLI';
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct(name: $name, data: $data, dataName: $dataName, tipo_conexion: $this->tipo_conexion);
        $this->errores = new errores();
    }

    public function test_genera_join(): void
    {
        errores::$error = false;

        $cb = new consultas_base();
        $cb = new liberator($cb);


        $tabla = '';
        $tabla_enlace = '';
        $renombrada = '';

        $resultado = $cb->genera_join($tabla, $tabla_enlace, $renombrada);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error tabla esta vacia',$resultado['mensaje']);
        errores::$error = false;

        $tabla = 'a';
        $tabla_enlace = '';
        $renombrada = '';

        $resultado = $cb->genera_join($tabla, $tabla_enlace, $renombrada);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $tabla_enlace esta vacia',$resultado['mensaje']);

        errores::$error = false;

        $tabla = 'a';
        $tabla_enlace = 'b';
        $renombrada = '';

        $resultado = $cb->genera_join($tabla, $tabla_enlace, $renombrada);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase(' LEFT JOIN a AS a ON a.id = b.a_id',$resultado);

        errores::$error = false;

        $tabla = 'a';
        $tabla_enlace = 'b';
        $renombrada = 'c';

        $resultado = $cb->genera_join($tabla, $tabla_enlace, $renombrada);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase(' LEFT JOIN a AS c ON c.id = b.c_id',$resultado);
        errores::$error = false;
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