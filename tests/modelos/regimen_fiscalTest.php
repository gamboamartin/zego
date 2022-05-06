<?php
namespace tests\base;

use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\cliente;
use models\regimen_fiscal;


class regimen_fiscalTest extends test {
    public errores $errores;
    private string $tipo_conexion = 'MYSQLI';
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct(name: $name, data: $data, dataName: $dataName, tipo_conexion: $this->tipo_conexion);
        $this->errores = new errores();
    }

    public function test_get_regimen_fiscal(): void
    {
        errores::$error = false;

        $modelo = new regimen_fiscal($this->link);
        //$modelo = new liberator($modelo);


        $regimen_fiscal_id = '-1';

        $resultado = $modelo->get_regimen_fiscal($regimen_fiscal_id);

        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al al obtener regimen_fiscal',$resultado['mensaje']);

        errores::$error = false;
        $regimen_fiscal_id = '1';

        $resultado = $modelo->get_regimen_fiscal($regimen_fiscal_id);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1',$resultado['regimen_fiscal_id']);

        errores::$error = false;
    }

    public function test_cp(): void
    {
        errores::$error = false;

        $modelo = new cliente($this->link);
        //$modelo = new liberator($modelo);


        $cliente_id = '-1';

        $resultado = $modelo->cp($cliente_id);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al obtener cliente',$resultado['mensaje']);

        errores::$error = false;

        $cliente_id = '1';

        $resultado = $modelo->cp($cliente_id);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);

    }


}