<?php
namespace tests\base;

use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\modelos;


class modelosTest extends test {
    public errores $errores;
    private string $tipo_conexion = 'MYSQLI';
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct(name: $name, data: $data, dataName: $dataName, tipo_conexion: $this->tipo_conexion);
        $this->errores = new errores();
    }

    public function test_activa_bd(): void
    {
        errores::$error = false;

        $modelo = new modelos($this->link);
        //$modelo = new liberator($modelo);


        $tabla = '';
        $id = -1;
        $resultado = $modelo->activa_bd($tabla, $id);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error la tabla no puede venir vacia',$resultado['mensaje']);

        errores::$error = false;

        $tabla = 'xxx';
        $id = -1;
        $resultado = $modelo->activa_bd($tabla, $id);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al ejecutar sql',$resultado['mensaje']);

        errores::$error = false;

        $tabla = 'factura';
        $id = -1;
        $resultado = $modelo->activa_bd($tabla, $id);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('Registro activado con éxito',$resultado['mensaje']);
        errores::$error = false;
    }

    public function test_asigna_0_to_vacio(): void
    {
        errores::$error = false;

        $modelo = new modelos($this->link);
        $modelo = new liberator($modelo);


        $campo = '';
        $row = array();
        $resultado = $modelo->asigna_0_to_vacio($campo, $row);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error campo esta vacio',$resultado['mensaje']);

        errores::$error = false;

        $campo = 'a';
        $row = array();
        $resultado = $modelo->asigna_0_to_vacio($campo, $row);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(0,$resultado['a']);
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

    public function test_elimina_bd(): void
    {
        errores::$error = false;

        $modelo = new modelos($this->link);
        //$modelo = new liberator($modelo);

        $tabla = '';
        $id = 1;
        $resultado = $modelo->elimina_bd($tabla, $id);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error la tabla no puede venir vacia',$resultado['mensaje']);

        errores::$error = false;

        $tabla = 'partida_factura';
        $id = 1;
        $resultado = $modelo->elimina_bd($tabla, $id);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_existe_algun_valor(): void
    {
        errores::$error = false;

        $modelo = new modelos($this->link);
        $modelo = new liberator($modelo);

        $registro = array();
        $keys = array();
        $resultado = $modelo->existe_algun_valor($keys, $registro);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado);

        errores::$error = false;


        $registro = array();
        $keys = array();
        $keys[] = 'a';
        $resultado = $modelo->existe_algun_valor($keys, $registro);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado);

        errores::$error = false;


        $registro = array();
        $keys = array();
        $keys[] = 'a';
        $registro['a'] ='a';
        $resultado = $modelo->existe_algun_valor($keys, $registro);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_existe_valor(): void
    {
        errores::$error = false;

        $modelo = new modelos($this->link);
        $modelo = new liberator($modelo);

        $key = '';
        $registro = array();
        $resultado = $modelo->existe_valor($key, $registro);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado);

        errores::$error = false;


        $key = 'a';
        $registro = array();
        $registro['a'] = '1';
        $resultado = $modelo->existe_valor($key, $registro);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_filtro_and(): void
    {
        errores::$error = false;

        $modelo = new modelos($this->link);
        //$modelo = new liberator($modelo);

        $tabla = '';
        $filtros = array();
        $resultado = $modelo->filtro_and($tabla, $filtros);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error la tabla esta vacia',$resultado['mensaje']);

        errores::$error = false;


        $tabla = 'insumo';
        $filtros = array();
        $resultado = $modelo->filtro_and($tabla, $filtros);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el $filtros esta vacio',$resultado['mensaje']);

        errores::$error = false;


        $tabla = 'insumo';
        $filtros = array();
        $filtros['a'] = 'x';
        $resultado = $modelo->filtro_and($tabla, $filtros);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al ejecutar consulta',$resultado['mensaje']);

        errores::$error = false;


        $tabla = 'insumo';
        $filtros = array();
        $filtros['insumo.id'] = '1';
        $resultado = $modelo->filtro_and($tabla, $filtros);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);

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

    public function test_limpia_campo_row_inexistente(): void
    {
        errores::$error = false;

        $modelo = new modelos($this->link);
        $modelo = new liberator($modelo);


        $campo = '';
        $row = array();
        $resultado = $modelo->limpia_campo_row_inexistente($campo, $row);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error campo esta vacio',$resultado['mensaje']);

        errores::$error = false;

        $campo = 'a';
        $row = array();
        $resultado = $modelo->limpia_campo_row_inexistente($campo, $row);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado['a']);
        errores::$error = false;
    }

    public function test_modifica_bd(): void
    {
        errores::$error = false;

        $modelo = new modelos($this->link);
        //$modelo = new liberator($modelo);

        $id = 1;
        $tabla = 'factura';
        $registro = array();
        $registro['status'] = 1;
        $resultado = $modelo->modifica_bd($registro, $tabla, $id);
        $this->assertIsArray( $resultado);
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

    public function test_registros_puros(): void
    {
        errores::$error = false;

        $modelo = new modelos($this->link);
        //$modelo = new liberator($modelo);

        $tabla = '';
        $resultado = $modelo->registros_puros($tabla, '');
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error la tabla esta vacia',$resultado['mensaje']);

        errores::$error = false;


        $tabla = 'a';
        $resultado = $modelo->registros_puros($tabla, '');
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al obtener registros',$resultado['mensaje']);

        errores::$error = false;


        $tabla = 'insumo';
        $resultado = $modelo->registros_puros($tabla,'');
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_rows_entre_fechas(): void
    {
        errores::$error = false;

        $modelo = new modelos($this->link);
        //$modelo = new liberator($modelo);

        $campo = '';
        $fecha_final = '';
        $fecha_inicial = '';
        $tabla = '';
        $tipo_val = '';
        $resultado = $modelo->rows_entre_fechas($campo, $fecha_final, $fecha_inicial,array(),-1, $tabla, $tipo_val);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error la tabla esta vacia',$resultado['mensaje']);

        errores::$error = false;


        $campo = '';
        $fecha_final = '';
        $fecha_inicial = '';
        $tabla = 'factura';
        $tipo_val = '';
        $resultado = $modelo->rows_entre_fechas($campo, $fecha_final, $fecha_inicial,array(),-1, $tabla, $tipo_val);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar fechas',$resultado['mensaje']);

        errores::$error = false;


        $campo = '';
        $fecha_final = '';
        $fecha_inicial = '2020-01-01';
        $tabla = 'factura';
        $tipo_val = '';
        $resultado = $modelo->rows_entre_fechas($campo, $fecha_final, $fecha_inicial,array(),-1, $tabla, $tipo_val);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar fechas',$resultado['mensaje']);

        errores::$error = false;


        $campo = '';
        $fecha_final = '2020-01-01';
        $fecha_inicial = '2020-01-01';
        $tabla = 'factura';
        $tipo_val = '';
        $resultado = $modelo->rows_entre_fechas($campo, $fecha_final, $fecha_inicial,array(),-1, $tabla, $tipo_val);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar fechas',$resultado['mensaje']);

        errores::$error = false;


        $campo = '';
        $fecha_final = '2020-01-01';
        $fecha_inicial = '2020-01-01';
        $tabla = 'factura';
        $tipo_val = 'fecha';
        $resultado = $modelo->rows_entre_fechas($campo, $fecha_final, $fecha_inicial,array(),-1, $tabla, $tipo_val);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el $campo esta vacia',$resultado['mensaje']);

        errores::$error = false;


        $campo = 'factura.fecha_timbrado';
        $fecha_final = '2022-02-05';
        $fecha_inicial = '2022-02-04';
        $tabla = 'factura';
        $tipo_val = 'fecha';
        $resultado = $modelo->rows_entre_fechas($campo, $fecha_final, $fecha_inicial,array(),-1, $tabla, $tipo_val);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }


}