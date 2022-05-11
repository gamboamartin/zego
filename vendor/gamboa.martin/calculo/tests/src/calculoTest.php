<?php
namespace tests\src;

use gamboamartin\calculo\calculo;
use gamboamartin\errores\errores;
use gamboamartin\test\test;



class calculoTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_microtime_float(){
        errores::$error = false;
        $calculo = new calculo();
        $resultado = $calculo->microtime_float();
        $this->assertIsInt( $resultado);
        $this->assertNotTrue(errores::$error);

    }

    public function test_obten_fecha_resta(){
        errores::$error = false;
        $calculo = new calculo();
        $fecha = '';
        $n_dias = -1;
        $resultado = $calculo->obten_fecha_resta($fecha, $n_dias);
        $this->assertIsArray( $resultado);
        $this->assertStringContainsStringIgnoringCase('Error al validar fecha', $resultado['mensaje']);
        $this->assertTrue(errores::$error);

        errores::$error = false;
        $fecha = '2021-01-01';
        $n_dias = -1;
        $resultado = $calculo->obten_fecha_resta($fecha, $n_dias);
        $this->assertIsArray( $resultado);
        $this->assertStringContainsStringIgnoringCase('Error $n_dias debe ser mayor o igual a 0', $resultado['mensaje']);
        $this->assertTrue(errores::$error);

        errores::$error = false;
        $fecha = '2021-01-01';
        $n_dias = 0;
        $resultado = $calculo->obten_fecha_resta($fecha, $n_dias);
        $this->assertIsString( $resultado);
        $this->assertEquals('2021-01-01', $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
        $fecha = '2021-01-01';
        $n_dias = 0;
        $resultado = $calculo->obten_fecha_resta($fecha, $n_dias,'fecha_hora_min_sec_esp');
        $this->assertIsArray( $resultado);
        $this->assertStringContainsStringIgnoringCase('Error al validar fecha', $resultado['mensaje']);
        $this->assertTrue(errores::$error);

        errores::$error = false;
        $fecha = '2021-01-01 56:01:22';
        $n_dias = 0;
        $resultado = $calculo->obten_fecha_resta($fecha, $n_dias,'fecha_hora_min_sec_esp');
        $this->assertIsArray( $resultado);
        $this->assertStringContainsStringIgnoringCase('Error al validar fecha', $resultado['mensaje']);
        $this->assertTrue(errores::$error);

        errores::$error = false;
        $fecha = '2021-01-01 23:01:22';
        $n_dias = 1;
        $resultado = $calculo->obten_fecha_resta($fecha, $n_dias,'fecha_hora_min_sec_esp');
        $this->assertIsString( $resultado);
        $this->assertEquals('2020-12-31 23:01:22', $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_obten_mes_espaniol(){
        errores::$error = false;
        $calculo = new calculo();
        $resultado = $calculo->obten_mes_espaniol('');
        $this->assertIsArray( $resultado);
        $this->assertStringContainsStringIgnoringCase('Error al validar fecha', $resultado['mensaje']);
        $this->assertTrue(errores::$error);

        errores::$error = false;
        $resultado = $calculo->obten_mes_espaniol('2019-01-01');
        $this->assertIsString( $resultado);
        $this->assertStringContainsStringIgnoringCase('enero', $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_rangos_fechas(){
        errores::$error = false;
        $calculo = new calculo();
        $n_dias_2 = 1;
        $n_dias_1 = 1;
        $tipo_val = '';
        $resultado = $calculo->rangos_fechas($n_dias_1, $n_dias_2, $tipo_val);
        $this->assertIsArray( $resultado);
        $this->assertStringContainsStringIgnoringCase('Error al validar tipo_val', $resultado['mensaje']);
        $this->assertTrue(errores::$error);

        errores::$error = false;
        $n_dias_2 = 1;
        $n_dias_1 = 1;
        $tipo_val = 'x';
        $resultado = $calculo->rangos_fechas($n_dias_1, $n_dias_2, $tipo_val);
        $this->assertIsArray( $resultado);
        $this->assertStringContainsStringIgnoringCase('Error al validar tipo_val', $resultado['mensaje']);
        $this->assertTrue(errores::$error);

        errores::$error = false;
        $n_dias_2 = 1;
        $n_dias_1 = 1;
        $tipo_val = 'fecha';
        $resultado = $calculo->rangos_fechas($n_dias_1, $n_dias_2, $tipo_val);
        $this->assertIsObject( $resultado);
        $this->assertEquals(date('Y-m-d'),$resultado->hoy);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }




}