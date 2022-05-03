<?php
namespace controllers;
class controlador_moneda extends controlador_base {
    public $descripcion_moneda;
    public $moneda_codigo;
    public $moneda_descripcion;
    public $tipo_cambio_hoy;
    public function asigna_tipo_cambio(){
        $directiva = new Directivas();
		$breadcrumbs = array('alta','lista');
		$this->breadcrumbs = $directiva->nav_breadcumbs_modifica(8, 2, $breadcrumbs);
    }
    public function asigna_tipo_cambio_bd(){
        $modelo = new Tipo_cambio();
        $resultado = $modelo->alta_bd($_POST, 'tipo_cambio');

        if($resultado['error']){
            $mensaje = $resultado['mensaje'];
            header("Location: ./index.php?seccion=".SECCION."&accion=asigna_tipo_cambio&moneda_id=".$_POST['moneda_id']."&mensaje=$mensaje&tipo_mensaje=error");
            exit;
        }
        header("Location: ./index.php?seccion=".SECCION."&accion=lista&mensaje=Registro insertado con éxito&tipo_mensaje=exito");
    }
    public function obten_tipo_cambio(){
        $moneda_id = $_POST['moneda_id'];
        $model_tipo_cambio = new Tipo_cambio();
        $model_moneda = new Moneda();

        $resultado = $model_tipo_cambio->tipo_cambio_por_moneda($moneda_id);
        $datos_encabezado = $model_moneda->obten_por_id('moneda',$moneda_id);


        $this->registros = $resultado['registros'];
        $this->moneda_descripcion = $datos_encabezado['registros']['0']['moneda_descripcion'];
        $this->moneda_codigo = $datos_encabezado['registros']['0']['moneda_codigo'];
        $this->tipo_cambio_hoy = $datos_encabezado['registros']['0']['tipo_cambio_hoy'];
    }
}