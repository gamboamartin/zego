<?php
namespace tests\base;

use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\modelos;
use models\partida_factura;


class partida_facturaTest extends test {
    public errores $errores;
    private string $tipo_conexion = 'MYSQLI';
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct(name: $name, data: $data, dataName: $dataName, tipo_conexion: $this->tipo_conexion);
        $this->errores = new errores();
    }

    public function test_compare_obj_imp(): void
    {
        errores::$error = false;

        $modelo = new partida_factura($this->link);
        $modelo = new liberator($modelo);


        $insumo = array();
        $partida = array();
        $resultado = $modelo->compare_obj_imp($insumo, $partida);

        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el insumo no tiene insumo_obj_imp cargado',$resultado['mensaje']);

        errores::$error = false;


        $insumo = array();
        $partida = array();
        $insumo['insumo_obj_imp'] = '';
        $resultado = $modelo->compare_obj_imp($insumo, $partida);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el $obj_imp en insumo esta vacio',$resultado['mensaje']);

        errores::$error = false;


        $insumo = array();
        $partida = array();
        $insumo['insumo_obj_imp'] = 'a';
        $resultado = $modelo->compare_obj_imp($insumo, $partida);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

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



    public function test_genera_columnas_consulta(): void
    {
        errores::$error = false;

        $modelo = new modelos($this->link);
        $modelo = new liberator($modelo);


        $tabla = '';
        $tabla_renombrada = '';
        $resultado = $modelo->genera_columnas_consulta($tabla, $tabla_renombrada);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error la tabla esta vacia',$resultado['mensaje']);

        errores::$error = false;
        $tabla = 'a';
        $tabla_renombrada = '';
        $resultado = $modelo->genera_columnas_consulta($tabla, $tabla_renombrada);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al obtener columnas',$resultado['mensaje']);

        errores::$error = false;
        $tabla = 'factura';
        $tabla_renombrada = '';
        $resultado = $modelo->genera_columnas_consulta($tabla, $tabla_renombrada);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('factura_id,factura.lugar_expedicion AS factura_lugar_ex',$resultado);
        errores::$error = false;
    }

    public function test_obten_columnas(): void
    {
        errores::$error = false;

        $modelo = new modelos($this->link);
        $modelo = new liberator($modelo);


        $tabla = '';
        $resultado = $modelo->obten_columnas($tabla);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error la tabla esta vacia',$resultado['mensaje']);

        errores::$error = false;


        $tabla = 'A';
        $resultado = $modelo->obten_columnas($tabla);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al ejecutar sql',$resultado['mensaje']);

        errores::$error = false;


        $tabla = 'factura';
        $resultado = $modelo->obten_columnas($tabla);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('id',$resultado[0]);
        $this->assertEquals('lugar_expedicion',$resultado[1]);
        $this->assertEquals('sello',$resultado[22]);
        $this->assertEquals('uuid',$resultado[40]);
        $this->assertEquals('zica_cliente_id',$resultado[51]);
        $this->assertEquals('bultos',$resultado[68]);
        $this->assertEquals('cliente_cp',$resultado[89]);
        errores::$error = false;
    }

    public function test_genera_columnas_completas(): void
    {
        errores::$error = false;

        $modelo = new modelos($this->link);
        $modelo = new liberator($modelo);


        $tabla = '';

        $resultado = $modelo->obten_columnas_completas($tabla);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error la tabla esta vacia',$resultado['mensaje']);

        errores::$error = false;
        $tabla = 'A';

        $resultado = $modelo->obten_columnas_completas($tabla);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error no existe columna en estructura',$resultado['mensaje']);

        errores::$error = false;
        $tabla = 'partida_factura';

        $resultado = $modelo->obten_columnas_completas($tabla);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('factura.nombre_emisor AS factura_nombre_emisor',$resultado);
        errores::$error = false;
    }

    public function test_partidas(): void
    {
        errores::$error = false;

        $modelo = new partida_factura($this->link);
        $modelo = new liberator($modelo);


        $factura_id = -1;
        $resultado = $modelo->partidas($factura_id);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        errores::$error = false;


        $factura_id = 1;
        $resultado = $modelo->partidas($factura_id);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotEmpty($resultado);
        errores::$error = false;
    }

    public function test_valida_obj(): void
    {
        errores::$error = false;

        $modelo = new partida_factura($this->link);
        $modelo = new liberator($modelo);


        $insumo = array();
        $resultado = $modelo->valida_obj($insumo);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error no existe insumo_id en insumo',$resultado['mensaje']);

        errores::$error = false;

        $insumo = array();
        $insumo['insumo_id'] = 1;
        $resultado = $modelo->valida_obj($insumo);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el insumo no tiene un insumo_obj_imp',$resultado['mensaje']);

        errores::$error = false;

        $insumo = array();
        $insumo['insumo_id'] = 1;
        $insumo['insumo_obj_imp'] = 'a';
        $resultado = $modelo->valida_obj($insumo);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }


}