<?php
namespace controllers;
use gamboamartin\errores\errores;
use models\modelos;
use views\directivas\directivas;


class controlador_base{
	public $registros;
	public $mensaje;
	public $error;
	public $registro;
	public $registro_id;
	public $breadcrumbs;
	public $lista;
	public $modelo;
	public $directiva;
	public $tabla;
	public $campo_filtro;
	public $valor_filtro;
	public $selected = false;
	public $registro_padre_id;
    public $campo;
    public $link;
    public $campo_resultado=false;
    protected errores $error_;

	public function __construct($link){
        $this->error_ = new errores();
	    $this->link = $link;
        if(!defined('SECCION')){
            $error = $this->error_->error('Error no esta definida la SECCION', false);
            print_r($error);exit;
        }
	    $modelo = SECCION;
	    if($modelo === 'session'){
            $this->modelo = new modelos($this->link);
        }
        else {
            $name_model = 'models\\'.$modelo;
            $this->modelo = new $name_model($this->link);
        }
	    $this->directiva = new directivas();

	    if(isset($_GET['seccion'])){
	        $this->tabla = $_GET['seccion'];

        }
        if(isset($_GET['valor_filtro'])){
            $this->valor_filtro = $_GET['valor_filtro'];
        }
        if(isset($_GET['campo_filtro'])){
            $this->campo_filtro = $_GET['campo_filtro'];
        }
        if(isset($_GET['selected'])){
            $this->selected = $_GET['selected'];
        }
        if(isset($_GET['registro_id'])){
            $this->registro_id = $_GET['registro_id'];
        }
        if(isset($_GET['campo'])){
            $this->campo = $_GET['campo'];
        }
        if(isset($_GET['campo_resultado'])){
            $this->campo_resultado = $_GET['campo_resultado'];
        }
    }

    /**
     *  ERROR
     * @return void
     */
    public function activa_bd(): void
    {
        $registro_id = $_GET['registro_id'];
        $registro = $this->modelo->activa_bd(SECCION, $registro_id);
        if(errores::$error){
            $error = $this->error_->error('Error al activar', $registro);
            print_r($error);
            die('Error');
        }
        $r = $this->resultado($registro);
        if(errores::$error){
            $error = $this->error_->error('Error al retornar resultado', $r);
            print_r($error);
            die('Error');
        }
    }

    /**
     * ERROR
     * @return void
     */
    public function alta(){
        $breadcrumbs = array('lista');
        $this->breadcrumbs = $this->directiva->nav_breadcumbs(8, 2, $breadcrumbs);
        if(errores::$error){
            $error = $this->error_->error('Error al generar navs', $this->breadcrumbs);
            print_r($error);
            die('Error');
        }
    }

    public function modifica_bd(){
        $tabla = $_GET['seccion'];
        $this->registro_id = $_GET['registro_id'];
        $resultado = $this->modelo->modifica_bd($_POST, $tabla, $this->registro_id);

        if($resultado['error']){
            $mensaje = $resultado['mensaje'];
            header("Location: ./index.php?seccion=$tabla&accion=modifica&mensaje=$mensaje&tipo_mensaje=error&registro_id=$this->registro_id");
            exit;
        }
        header("Location: ./index.php?seccion=$tabla&accion=lista&mensaje=Registro modificado con éxito&tipo_mensaje=exito");
    }

    public function obten_dato_registro(){
        $resultado = $this->modelo->obten_por_id($this->tabla, $this->registro_id);
        $this->registro = $resultado['registros'][0];
        echo $this->registro[$this->tabla.'_'.$this->campo];
    }

    public function option_selected(){

        if($this->selected){

            $dato_arreglo = explode('_id',$this->campo_filtro);
            $tabla_hija = $dato_arreglo[0];

            $resultado = $this->modelo->obten_por_id($tabla_hija, $this->valor_filtro);
            $registro = $resultado['registros'][0];

            if($this->campo_resultado){
                $this->registro_padre_id = $registro[$this->campo_resultado];
            }
            else{
                $this->registro_padre_id = $registro[$this->tabla.'_id'];
            }

            $resultado = $this->modelo->obten_registros($this->tabla);
            $this->registros = $resultado['registros'];


        }
        else {
            $resultado = $this->modelo->filtro_and($this->tabla,
                array($this->tabla . "." . $this->campo_filtro => $this->valor_filtro, $this->tabla . '.status' => '1'));
            $this->registros = $resultado['registros'];

        }

    }

    /**
     * ERROR
     * @param $registro
     * @return void
     */
    public function resultado($registro){
        echo $registro['mensaje'];
        if($registro['error']){
            http_response_code(404);
        }
        else{
            http_response_code(200);
        }
    }


    /**
     * ALFABETICO
     */


    /**
     * ERROR
     * @return void
     */
    public function alta_bd(){
        $tabla = $_GET['seccion'];

        $registro = array();
        foreach ($_POST as $key => $value){
            if($value != ''){
                $registro[$key] = $value;
            }
        }

        $resultado = $this->modelo->alta_bd($registro, $tabla);
        if(errores::$error){
            $error = $this->error_->error('Error al insertar registro', $resultado);
            print_r($error);
            die('Error');
        }


        header("Location: ./index.php?seccion=$tabla&accion=lista&mensaje=Registro insertado con éxito&tipo_mensaje=exito");
    }

    public function datos_select(){
        $tabla = $_GET['seccion'];
        $campo_filtro = $_GET['campo_filtro'];
        $valor_filtro = $_GET['valor_filtro'];

        $resultado = $this->modelo->filtro_and($tabla,
            array($tabla.".".$campo_filtro=>$valor_filtro, $tabla.'.status'=>'1'));
        $this->registros = $resultado['registros'];

    }

    public function desactiva_bd(){
        $tabla = $_GET['seccion'];
        $registro_id = $_GET['registro_id'];
        $registro = $this->modelo->desactiva_bd($tabla, $registro_id);
        $this->resultado($registro);
    }

    public function elimina_bd(){
        $tabla = $_GET['seccion'];
        $registro_id = $_GET['registro_id'];
        $registro = $this->modelo->elimina_bd($tabla, $registro_id);
        $this->resultado($registro);
    }


    public function lista(){
		$breadcrumbs = array('alta');
		$this->breadcrumbs = $this->directiva->nav_breadcumbs(12, 0, $breadcrumbs);
		$resultado = $this->modelo->obten_registros($_GET['seccion']);
		$this->registros = $resultado['registros'];
		if(isset($resultado['mensaje'])) {
            $this->mensaje = $resultado['mensaje'];
        }
        if(isset($this->error)) {
            $this->error = $resultado['error'];
        }
	}
	public function lista_ajax(){
		$valor = $_POST['valor'];
		$resultado = $this->modelo->filtra_campos_base($valor, $_GET['seccion']);
		$this->registros = $resultado['registros'];
	}

    public function modifica(){
        $breadcrumbs = array('alta','lista');
        $this->breadcrumbs = $this->directiva->nav_breadcumbs_modifica(8, 2, $breadcrumbs);

        $tabla = $_GET['seccion'];
        $this->registro_id = $_GET['registro_id'];
        $resultado = $this->modelo->obten_por_id($tabla, $this->registro_id);

        //print_r($resultado);exit;

        if($resultado['n_registros'] == 0){
            header("Location: ./index.php?seccion=$tabla&accion=lista&mensaje=Ya no existe registro&tipo_mensaje=error");
            exit;
        }
        $this->registro = $resultado['registros'][0];
    }








}
