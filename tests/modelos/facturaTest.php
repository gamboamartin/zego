<?php
namespace tests\base;

use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\factura;


class facturaTest extends test {
    public errores $errores;
    private string $tipo_conexion = 'MYSQLI';
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct(name: $name, data: $data, dataName: $dataName, tipo_conexion: $this->tipo_conexion);
        $this->errores = new errores();
    }

    public function test_aplica_reg_fis_cte(): void
    {
        errores::$error = false;

        $modelo = new factura($this->link);
        $modelo = new liberator($modelo);


        $registro = array();
        $cliente_id = -1;
        $resultado = $modelo->aplica_reg_fis_cte($cliente_id, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al al obtener cliente',$resultado['mensaje']);

        errores::$error = false;


        $registro = array();
        $cliente_id = 1;
        $resultado = $modelo->aplica_reg_fis_cte($cliente_id, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error no existe regimen fiscal en cliente',$resultado['mensaje']);
        errores::$error = false;
    }

    public function test_asigna_regimen_fiscal_rec(): void
    {
        errores::$error = false;

        $modelo = new factura($this->link);
        $modelo = new liberator($modelo);


        $registro = array();
        $regimen_fiscal_id = -1;
        $resultado = $modelo->asigna_regimen_fiscal_rec($regimen_fiscal_id, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al al obtener regimen_fiscal',$resultado['mensaje']);

        errores::$error = false;


        $registro = array();
        $regimen_fiscal_id = 1;
        $resultado = $modelo->asigna_regimen_fiscal_rec($regimen_fiscal_id, $registro);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('601',$resultado['cliente_rf']);
        errores::$error = false;
    }

    public function test_asigna_cp_receptor(): void
    {
        errores::$error = false;

        $modelo = new factura($this->link);
        $modelo = new liberator($modelo);


        $registro = array();

        $resultado = $modelo->asigna_cp_receptor($registro);

        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al al validar registro',$resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $registro['cliente_id'] = 1;

        $resultado = $modelo->asigna_cp_receptor($registro);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_cp_receptor(): void
    {
        errores::$error = false;

        $modelo = new factura($this->link);
        $modelo = new liberator($modelo);


        $registro = array();

        $resultado = $modelo->cp_receptor($registro);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);
        errores::$error = false;

        $registro = array();
        $registro['cliente_id'] = 1;

        $resultado = $modelo->cp_receptor($registro);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotEmpty($resultado);
        errores::$error = false;
    }



}