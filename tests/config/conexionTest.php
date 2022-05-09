<?php
namespace tests\base;

use config\conexion;
use controllers\controlador_cliente;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\modelos;


class conexionTest extends test {
    public errores $errores;
    private string $tipo_conexion = 'MYSQLI';
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct(name: $name, data: $data, dataName: $dataName, tipo_conexion: $this->tipo_conexion);
        $this->errores = new errores();
        if(!defined('SECCION')){
            define('SECCION', 'cliente');
        }
    }

    public function test_conecta(): void
    {
        errores::$error = false;


        $cnx = new conexion();
        //$cnx = new liberator($cnx);

        $host = '';
        $name_bd = '';
        $pass = '';
        $user = '';
        $resultado = $cnx->conecta($host, $name_bd, $pass, $user);


        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el host esta vacio',$resultado['mensaje']);

        errores::$error = false;

        $host = 'a';
        $name_bd = '';
        $pass = '';
        $user = '';
        $resultado = $cnx->conecta($host, $name_bd, $pass, $user);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el $name_bd esta vacio',$resultado['mensaje']);

        errores::$error = false;

        $host = 'a';
        $name_bd = 'z';
        $pass = '';
        $user = '';
        $resultado = $cnx->conecta($host, $name_bd, $pass, $user);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el $pass esta vacio',$resultado['mensaje']);

        errores::$error = false;

        $host = 'a';
        $name_bd = 'z';
        $pass = 'd';
        $user = '';
        $resultado = $cnx->conecta($host, $name_bd, $pass, $user);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el $user esta vacio',$resultado['mensaje']);

        errores::$error = false;

        $host = 'a';
        $name_bd = 'z';
        $pass = 'd';
        $user = 's';
        $resultado = $cnx->conecta($host, $name_bd, $pass, $user);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al conectarse',$resultado['mensaje']);

        errores::$error = false;

    }


}