<?php
class Usuario extends Modelos{

    public function valida_usuario_password($usuario, $password){
        $conexion = new Conexion();
        $conexion->selecciona_base_datos();
        $link = $conexion->link;

		$consulta = "SELECT *FROM 
                      usuario WHERE user = '$usuario' AND password = '$password' AND status = 1";
        $usuarios = $link->query($consulta);
        $n_registros = $usuarios->num_rows;
        if($link->error){
        	return array('mensaje'=>$link->error, 'error'=>True);
        }
        $new_array = array();
        while( $usuario = mysqli_fetch_assoc( $usuarios)){
		    $new_array[] = $usuario; 
		}
		if($n_registros == 1){
			return array('mensaje'=>'', 'registros'=>$new_array, 'n_registros'=>$n_registros, 'error'=>False);
		}
		else{
			return array('mensaje'=>'Usuario/Contraseña inválidos', 'error'=>True);
		}
	}
}