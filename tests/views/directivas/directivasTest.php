<?php
namespace tests\views\directivas;

use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use views\directivas\directivas;


class directivasTest extends test {
    public errores $errores;
    private string $tipo_conexion = 'MYSQLI';
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct(name: $name, data: $data, dataName: $dataName, tipo_conexion: $this->tipo_conexion);
        $this->errores = new errores();
    }

    public function test_breadcrumb(): void
    {
        errores::$error = false;

        $dir = new directivas();
        $dir = new liberator($dir);


        $etiqueta = '';

        $resultado = $dir->breadcrumb($etiqueta);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<a class='breadcrumb-item' href='#'></a>",$resultado);

        errores::$error = false;

        $etiqueta = 'a';

        $resultado = $dir->breadcrumb($etiqueta);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<a class='breadcrumb-item' href='#'>A</a>",$resultado);
        errores::$error = false;
    }

    public function test_genera_texto_etiqueta(): void
    {
        errores::$error = false;

        $dir = new directivas();
        $dir = new liberator($dir);


        $texto = '';

        $resultado = $dir->genera_texto_etiqueta($texto);

        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado);

        errores::$error = false;


        $texto = 'a';

        $resultado = $dir->genera_texto_etiqueta($texto);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('A',$resultado);

        errores::$error = false;
    }





}