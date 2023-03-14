<?php
namespace tests\src;


use gamboamartin\base_modelos\base_modelos;
use gamboamartin\errores\errores;
use gamboamartin\test\test;


class base_modelosTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_valida_datos_lista_entrada(): void
    {
        errores::$error = false;
        $base_modelo = new base_modelos();
        $seccion = "";
        $accion = "";
        $resultado = $base_modelo->valida_datos_lista_entrada(accion: $accion, seccion: $seccion);

        $this->assertIsArray( $resultado);
        $this->assertStringContainsStringIgnoringCase('Error seccion no puede venir vacio', $resultado['mensaje']);
        $this->assertTrue(errores::$error);

        errores::$error = false;

        $seccion = "a";
        $accion = "";
        $resultado = $base_modelo->valida_datos_lista_entrada(accion: $accion, seccion: $seccion);
        $this->assertIsArray( $resultado);
        $this->assertStringContainsStringIgnoringCase('Error no existe la accion', $resultado['mensaje']);
        $this->assertTrue(errores::$error);
        errores::$error = false;

        $seccion = "prueba";
        $accion = "";

        $resultado = $base_modelo->valida_datos_lista_entrada(accion: $accion, seccion: $seccion);
        $this->assertIsArray( $resultado);
        $this->assertStringContainsStringIgnoringCase('Error no existe la accion', $resultado['mensaje']);
        $this->assertTrue(errores::$error);

        errores::$error = false;

        $seccion = "prueba";
        $accion = "x";

        $resultado = $base_modelo->valida_datos_lista_entrada(accion: $accion, seccion: $seccion);
        $this->assertIsBool( $resultado);
        $this->assertTrue($resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;
    }

    

    public function test_valida_transaccion_activa(): void
    {
        errores::$error = false;
        $base_modelo = new base_modelos();
        $aplica_transaccion_inactivo = false;
        $registro_id = -1;
        $tabla = '';
        $registro = array();
        $resultado = $base_modelo->valida_transaccion_activa(aplica_transaccion_inactivo: $aplica_transaccion_inactivo,
            registro: $registro, registro_id: $registro_id, tabla: $tabla);
        $this->assertIsArray( $resultado);
        $this->assertStringContainsStringIgnoringCase('Error la tabla esta vacia', $resultado['mensaje']);
        $this->assertTrue(errores::$error);

        errores::$error = false;

        $aplica_transaccion_inactivo = false;
        $registro_id = -1;
        $tabla = 'a';
        $registro = array();
        $resultado = $base_modelo->valida_transaccion_activa(aplica_transaccion_inactivo: $aplica_transaccion_inactivo,
            registro: $registro, registro_id: $registro_id, tabla: $tabla);
        $this->assertIsArray( $resultado);
        $this->assertStringContainsStringIgnoringCase('Error el id debe ser mayor a 0', $resultado['mensaje']);
        $this->assertTrue(errores::$error);

        errores::$error = false;

        $aplica_transaccion_inactivo = false;
        $registro_id = 1;
        $tabla = 'a';
        $registro = array();
        $resultado = $base_modelo->valida_transaccion_activa(aplica_transaccion_inactivo: $aplica_transaccion_inactivo,
            registro: $registro, registro_id: $registro_id, tabla: $tabla);
        $this->assertIsArray( $resultado);
        $this->assertStringContainsStringIgnoringCase('Error no existe el registro con el campo a_status', $resultado['mensaje']);
        $this->assertTrue(errores::$error);

        errores::$error = false;

        $aplica_transaccion_inactivo = false;
        $registro_id = 1;
        $tabla = 'a';
        $registro = array();
        $registro['a_status'] = '';
        $resultado = $base_modelo->valida_transaccion_activa(aplica_transaccion_inactivo: $aplica_transaccion_inactivo,
            registro: $registro, registro_id: $registro_id, tabla: $tabla);

        $this->assertIsBool( $resultado);
        $this->assertTrue($resultado);
        $this->assertNotTrue(errores::$error);

    }






}

