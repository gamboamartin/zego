<?php
namespace tests\base;

use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\factura;
use models\insumo;


class insumoTest extends test {
    public errores $errores;
    private string $tipo_conexion = 'MYSQLI';
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct(name: $name, data: $data, dataName: $dataName, tipo_conexion: $this->tipo_conexion);
        $this->errores = new errores();
    }

    public function test_data_insumo(): void
    {
        errores::$error = false;

        $modelo = new insumo($this->link);
        //$modelo = new liberator($modelo);

        $insumo_id = -1;
        $resultado = $modelo->data_insumo($insumo_id);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al obtener insumo',$resultado['mensaje']);

        errores::$error = false;


        $insumo_id = 1;
        $resultado = $modelo->data_insumo($insumo_id);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }



}