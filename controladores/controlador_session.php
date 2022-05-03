<?php 
class Controlador_Session extends Controlador_Base{
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

		$modelo_usuario = new Usuario($this->link);
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
        $empresa = new Empresas();
        $this->empresas = $empresa->empresas;
	}
	public function inicio(){
        $empresa = new Empresas();
        $this->empresas = $empresa->empresas;
        $accion_modelo = new Accion($this->link);
        $resultado = $accion_modelo->obten_acciones_iniciales();

        $this->acciones_permitidas = $resultado['registros'];


	}	

}