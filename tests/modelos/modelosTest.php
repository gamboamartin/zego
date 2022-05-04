<?php
namespace tests\base;

use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\modelos;


class modelosTest extends test {
    public errores $errores;
    private string $tipo_conexion = 'MYSQLI';
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct(name: $name, data: $data, dataName: $dataName, tipo_conexion: $this->tipo_conexion);
        $this->errores = new errores();
    }

    public function test_activa_bd(): void
    {
        errores::$error = false;

        $modelo = new modelos($this->link);
        //$modelo = new liberator($modelo);


        $tabla = '';
        $id = -1;
        $resultado = $modelo->activa_bd($tabla, $id);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error la tabla no puede venir vacia',$resultado['mensaje']);

        errores::$error = false;

        $tabla = 'xxx';
        $id = -1;
        $resultado = $modelo->activa_bd($tabla, $id);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al ejecutar sql',$resultado['mensaje']);

        errores::$error = false;

        $tabla = 'factura';
        $id = -1;
        $resultado = $modelo->activa_bd($tabla, $id);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('Registro activado con éxito',$resultado['mensaje']);
        errores::$error = false;
    }


    public function test_ejecuta_consulta(): void
    {
        errores::$error = false;

        $modelo = new modelos($this->link);
        $modelo = new liberator($modelo);


        $consulta = '';
        $resultado = $modelo->ejecuta_consulta($consulta);

        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error la consulta no puede venir vacia',$resultado['mensaje']);

        errores::$error = false;

        $consulta = 'a';
        $resultado = $modelo->ejecuta_consulta($consulta);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al ejecutar sql',$resultado['mensaje']);

        errores::$error = false;

        $consulta = 'SELECT 1';
        $resultado = $modelo->ejecuta_consulta($consulta);

        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1',$resultado['registros'][0]['1']);



        errores::$error = false;
    }


}