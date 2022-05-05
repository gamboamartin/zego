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

    public function test_genera_consulta_base(): void
    {
        errores::$error = false;

        $modelo = new modelos($this->link);
        $modelo = new liberator($modelo);


        $tabla = '';

        $resultado = $modelo->genera_consulta_base($tabla);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error la tabla esta vacia',$resultado['mensaje']);

        errores::$error = false;

        $tabla = 'a';

        $resultado = $modelo->genera_consulta_base($tabla);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al obtener columnas',$resultado['mensaje']);

        errores::$error = false;

        $tabla = 'factura';

        $resultado = $modelo->genera_consulta_base($tabla);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('zica_entidad_id AS cliente_zica_entidad_id,',$resultado);
        errores::$error = false;
    }

    public function test_obten_por_id(): void
    {
        errores::$error = false;

        $modelo = new modelos($this->link);
        //$modelo = new liberator($modelo);


        $tabla = '';
        $id = -1;
        $resultado = $modelo->obten_por_id($tabla, $id);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error la tabla esta vacia',$resultado['mensaje']);

        errores::$error = false;

        $tabla = 'a';
        $id = -1;
        $resultado = $modelo->obten_por_id($tabla, $id);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al generar consulta',$resultado['mensaje']);

        errores::$error = false;

        $tabla = 'cliente';
        $id = -1;
        $resultado = $modelo->obten_por_id($tabla, $id);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_registro(): void
    {
        errores::$error = false;

        $modelo = new modelos($this->link);
        //$modelo = new liberator($modelo);
        
        $tabla = '';
        $id = -1;
        $resultado = $modelo->registro($id, $tabla);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error la tabla esta vacia',$resultado['mensaje']);

        errores::$error = false;

        $tabla = 'a';
        $id = -1;
        $resultado = $modelo->registro($id, $tabla);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al obtener registro',$resultado['mensaje']);

        errores::$error = false;

        $tabla = 'cliente';
        $id = -1;
        $resultado = $modelo->registro($id, $tabla);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error no existe registro',$resultado['mensaje']);

        errores::$error = false;


    }


}