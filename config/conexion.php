<?php

use gamboamartin\errores\errores;

class conexion{
	public $link;
	public $nombre_base_datos;
    private errores $error;
	function __construct(){
	    if (isset($_SESSION['numero_empresa'])) {
            $this->error = new errores();
            $empresa = new empresas();
            $empresas = $empresa->empresas;
            $empresa_activa = $empresas[$_SESSION['numero_empresa']];
            $host = $empresa_activa['host'];
            $user = $empresa_activa['user'];
            $pass = $empresa_activa['pass'];
            $this->nombre_base_datos = $empresa_activa['nombre_base_datos'];
            $this->link = mysqli_connect($host, $user, $pass);
            mysqli_set_charset($this->link, 'utf8');
            $sql = "SET sql_mode = '';";
            $this->link->query($sql);
        }
        else{
            $this->link = false;
        }
	}
	public function selecciona_base_datos($nombre_base_datos=false){
		if($nombre_base_datos){
			$this->nombre_base_datos = $nombre_base_datos;
		}
		$consulta = 'USE '.$this->nombre_base_datos;
		if(isset($_SESSION['numero_empresa'])) {
            try{
                $this->link->query($consulta);
            }
            catch (Throwable $e){
                return $this->error->error('Error al ejecutar sql', $e);
            }
        }
	}
}