<?php

class Menu extends Modelos{

	public function obten_menu_permitido(){
        $conexion = new Conexion();
        $conexion->selecciona_base_datos();
        $link = $conexion->link;	
        
        $grupo_id = $_SESSION['grupo_id'];	

        $consulta = "SELECT 
        		menu.id AS id ,
        		menu.icono AS icono,
        		menu.descripcion AS descripcion 
        		FROM menu AS menu
        	INNER JOIN seccion_menu AS seccion_menu ON seccion_menu.menu_id = menu.id
        	INNER JOIN accion AS accion ON accion.seccion_menu_id = seccion_menu.id
        	INNER JOIN accion_grupo AS permiso ON permiso.accion_id = accion.id
        	INNER JOIN grupo AS grupo ON grupo.id = permiso.grupo_id
        WHERE 
        	menu.status = 1
        	AND seccion_menu.status = 1 
        	AND accion.status = 1 
        	AND grupo.status = 1 
        	AND permiso.grupo_id = $grupo_id 
                AND accion.visible = 1
        GROUP BY menu.id
        ";
        $result = $link->query($consulta);
        $n_registros = $result->num_rows;

        if($link->error){
        	return array('mensaje'=>$link->error.' '.$consulta, 'error'=>True);
        }

        $new_array = array();
        while( $row = mysqli_fetch_assoc( $result)){
		    $new_array[] = $row; 
		}
		return array('registros' => $new_array, 'n_registros' => $n_registros);

	}
}

?>