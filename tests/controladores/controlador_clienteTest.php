<?php
namespace tests\base;

use controllers\controlador_cliente;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\modelos;


class controlador_clienteTest extends test {
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

    public function test_asigna_data_update_emisor(): void
    {
        errores::$error = false;


        $ctl = new controlador_cliente($this->link);
        $ctl = new liberator($ctl);

        $registro = array();
        $data = array();
        $update = array();
        $resultado = $ctl->asigna_data_update_emisor($data, $registro, $update);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        errores::$error = false;



        $registro = array();
        $data = array();
        $update = array();
        $data[] = '';
        $resultado = $ctl->asigna_data_update_emisor($data, $registro, $update);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al $data_value_upd debe ser un array',$resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $data = array();
        $update = array();
        $data[] = array();
        $resultado = $ctl->asigna_data_update_emisor($data, $registro, $update);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al asignar datos',$resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $data = array();
        $update = array();
        $data['a'] = array();
        $registro['a'] = 'z';
        $resultado = $ctl->asigna_data_update_emisor($data, $registro, $update);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);
        errores::$error = false;
    }

    public function test_asigna_datos_emisor(): void
    {
        errores::$error = false;


        $ctl = new controlador_cliente($this->link);
        $ctl = new liberator($ctl);

        $data_value_upd = array();
        $update = array();
        $resultado = $ctl->asigna_datos_emisor($data_value_upd, $update);

        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error no existe $data_value_upd[$key_upd]',$resultado['mensaje']);

        errores::$error = false;
        $data_value_upd = array();
        $update = array();
        $data_value_upd['x'] = 1;
        $resultado = $ctl->asigna_datos_emisor($data_value_upd, $update);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1',$resultado['x']);

        errores::$error = false;
    }





    public function test_init_array_update_emisor(): void
    {
        errores::$error = false;


        $ctl = new controlador_cliente($this->link);
        //$ctl = new liberator($ctl);

        $registro = array();
        $resultado = $ctl->init_array_update_emisor($registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al asignar datos',$resultado['mensaje']);

        errores::$error = false;


        $registro = array();
        $registro['factura_lugar_expedicion'] = '1';
        $registro['factura_calle_expedicion'] = '1';
        $registro['factura_exterior_expedicion'] = '1';
        $registro['factura_interior_expedicion'] = ' ';
        $registro['factura_colonia_expedicion'] = '1';
        $registro['factura_municipio_expedicion'] = '1';
        $registro['factura_estado_expedicion'] = '1';
        $registro['factura_pais_expedicion'] = '1';
        $registro['factura_nombre_emisor'] = '1';
        $registro['factura_regimen_fiscal_emisor_codigo'] = '1';
        $registro['factura_regimen_fiscal_emisor_descripcion'] = '1';
        $resultado = $ctl->init_array_update_emisor($registro);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_init_update(): void
    {
        errores::$error = false;


        $ctl = new controlador_cliente($this->link);
        $ctl = new liberator($ctl);

        $registro = array();
        $datos_empresa = array();
        $registro['factura_lugar_expedicion'] = 'a';
        $registro['factura_calle_expedicion'] = 'a';
        $registro['factura_exterior_expedicion'] = 'a';
        $registro['factura_colonia_expedicion'] = 'a';
        $registro['factura_municipio_expedicion'] = 'a';
        $registro['factura_estado_expedicion'] = 'a';
        $registro['factura_pais_expedicion'] = 'a';
        $registro['factura_nombre_emisor'] = 'a';
        $registro['factura_regimen_fiscal_emisor_codigo'] = 'a';
        $registro['factura_regimen_fiscal_emisor_descripcion'] = 'a';
        $datos_empresa['rfc'] = 'z';
        $resultado = $ctl->init_update($datos_empresa, $registro);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }


    public function test_init_update_emisor(): void
    {
        errores::$error = false;


        $ctl = new controlador_cliente($this->link);
        $ctl = new liberator($ctl);

        $update = array();
        $data_value_upd = array();
        $registro = array();
        $campo_upd = '';
        $resultado = $ctl->init_update_emisor($campo_upd, $data_value_upd, $registro, $update);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $campo_upd esta vacio',$resultado['mensaje']);

        errores::$error = false;


        $update = array();
        $data_value_upd = array();
        $registro = array();
        $campo_upd = 'a';
        $resultado = $ctl->init_update_emisor($campo_upd, $data_value_upd, $registro, $update);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar $registro',$resultado['mensaje']);

        errores::$error = false;


        $update = array();
        $data_value_upd = array();
        $registro = array();
        $campo_upd = 'a';
        $registro['a'] = 'zx';
        $resultado = $ctl->init_update_emisor($campo_upd, $data_value_upd, $registro, $update);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }





}