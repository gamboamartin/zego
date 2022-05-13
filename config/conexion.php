<?php
namespace config;
use gamboamartin\errores\errores;
use mysqli;
use Throwable;

class conexion{
	public mysqli|false $link;
	public string $nombre_base_datos = '';
    private errores $error;

    /** @noinspection ForgottenDebugOutputInspection */
    public function __construct(string $host = '', string $name_bd = '', string $pass = '', string $user = ''){
	    if (isset($_SESSION['numero_empresa'])) {
            $this->error = new errores();
            $empresa = new empresas();
            $empresas = $empresa->empresas;
            $empresa_activa = $empresas[$_SESSION['numero_empresa']];

            if($host==='') {

                $host = $empresa_activa['host'];
                $user = $empresa_activa['user'];
                $pass = $empresa_activa['pass'];
                $this->nombre_base_datos = $empresa_activa['nombre_base_datos'];
            }
            else{
                $this->nombre_base_datos = $name_bd;
            }

            try {
                $this->link = mysqli_connect($host, $user, $pass);
                mysqli_set_charset($this->link, 'utf8');
                $sql = "SET sql_mode = '';";
                $this->link->query($sql);
            }
            catch (Throwable $e){
                $error = $this->error->error('Error al conectarse', $e);
                /** @noinspection ForgottenDebugOutputInspection */
                print_r($error);
                die('Error');
            }
        }
        else{
            $this->link = false;
        }
	}

    /**
     * ERROR UNIT
     * @param string $host
     * @param string $name_bd
     * @param string $pass
     * @param string $user
     * @return bool|array|mysqli
     */
    public function conecta(string $host, string $name_bd, string $pass, string $user): bool|array|mysqli
    {
        $this->error = new errores();

        $host = trim($host);
        if($host === ''){
            return $this->error->error('Error el host esta vacio', $host);
        }
        $name_bd = trim($name_bd);
        if($name_bd === ''){
            return $this->error->error('Error el $name_bd esta vacio', $name_bd);
        }
        $pass = trim($pass);
        if($pass === ''){
            return $this->error->error('Error el $pass esta vacio', $pass);
        }
        $user = trim($user);
        if($user === ''){
            return $this->error->error('Error el $user esta vacio', $user);
        }

        try {
            $link = mysqli_connect($host, $user, $pass);
            mysqli_set_charset($link, 'utf8');
            $sql = "SET sql_mode = '';";
            $link->query($sql);

            $consulta = 'USE '.$name_bd;
            $link->query($consulta);

        }
        catch (Throwable $e){
            return $this->error->error('Error al conectarse', $e);
        }
        return $link;
    }

    /**
     * ERROR
     * @param bool $nombre_base_datos
     * @return array|void
     */

	public function selecciona_base_datos(bool|string $nombre_base_datos=false){
        if($nombre_base_datos===''){
            $nombre_base_datos = false;
        }
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