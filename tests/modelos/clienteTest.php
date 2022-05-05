<?php
namespace tests\base;

use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\cliente;



class clienteTest extends test {
    public errores $errores;
    private string $tipo_conexion = 'MYSQLI';
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct(name: $name, data: $data, dataName: $dataName, tipo_conexion: $this->tipo_conexion);
        $this->errores = new errores();
    }

    public function test_cliente(): void
    {
        errores::$error = false;

        $modelo = new cliente($this->link);
        //$modelo = new liberator($modelo);


        $cliente_id = '-1';

        $resultado = $modelo->cliente($cliente_id);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al obtener cliente',$resultado['mensaje']);

        errores::$error = false;



        $cliente_id = '1';

        $resultado = $modelo->cliente($cliente_id);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }


}