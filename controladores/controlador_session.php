<?php
namespace controllers;
use config\empresas;
use gamboamartin\errores\errores;
use models\accion;
use models\usuario;

class controlador_session extends controlador_base {
    public $empresas;
    public $acciones_permitidas;
	public function denegado(){
		
	}
	public function logout(){
		unset ($_SESSION['username']);
		session_destroy();
		header('Location: index.php?seccion=session&accion=login');
		exit;
	}
	public function loguea(){
        $numero_empresa = $_POST['numero_empresa'];
        $_SESSION['numero_empresa'] = $numero_empresa;

		$modelo_usuario = new usuario($this->link);
		$usuario = $_POST['user'];
		$password = $_POST['password'];



		$usuarios = $modelo_usuario->valida_usuario_password($usuario, $password);

		$datos_usuario = $usuarios['registros'];

		if($usuarios['error']){
			$mensaje = $usuarios['mensaje'];
			session_destroy();
			header("Location: ./index.php?seccion=session&accion=login&mensaje=$mensaje&tipo_mensaje=error");
			exit;
		}
		else{
            $_SESSION['activa'] = 1;
            $_SESSION['grupo_id'] = $datos_usuario[0]['grupo_id'];
            $mensaje = $usuarios['mensaje'];
            header("Location: ./index.php?seccion=session&accion=inicio&mensaje=Bienvenido&tipo_mensaje=exito");
            exit;
		}

	}
	public function login(){
        $empresa = new empresas();
        $this->empresas = $empresa->empresas;
	}
	public function inicio(){
        $empresa = new empresas();
        $this->empresas = $empresa->empresas;
        $accion_modelo = new accion($this->link);
        $resultado = $accion_modelo->obten_acciones_iniciales();
        if(errores::$error){
            $error = $this->error_->error('Error al obtener acciones', $resultado);
            print_r($error);
            die('Error');
        }

        $this->acciones_permitidas = $resultado['registros'];

	}	

}