<?php
namespace tests\base;

use controlador_cliente;
use gamboamartin\errores\errores;
use gamboamartin\test\test;


class controlador_cliente_test extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }
    public function test_keys_init_emisor(): void
    {
        errores::$error = false;

        $ctl = new controlador_cliente($this->link);


        $resultado = $ctl->keys_init_emisor();
        print_r($resultado);exit;

        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar conf_database',$resultado['mensaje']);

        errores::$error = false;

        $conf_database->db_user = 'x';
        $conf_database = new database();
        $conf_database->db_user = '';
        $resultado = $cnx->conecta($conf_database);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar conf_database',$resultado['mensaje']);

        errores::$error = false;

        $conf_database = new database();

        $resultado = $cnx->conecta($conf_database);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);


        errores::$error = false;
    }


}