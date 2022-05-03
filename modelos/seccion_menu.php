<?php

class Seccion_Menu extends Modelos{

	public function obten_submenu_permitido($menu_id){
                $conexion = new Conexion();
                $conexion->selecciona_base_datos();
                $link = $conexion->link;	
                
                $grupo_id = $_SESSION['grupo_id'];

                $consulta = "SELECT 
                		seccion_menu.id AS id ,
                		seccion_menu.icono AS icono,
                		seccion_menu.descripcion AS descripcion 
                		FROM seccion_menu AS seccion_menu
                	INNER JOIN accion AS accion ON accion.seccion_menu_id = seccion_menu.id
                	INNER JOIN accion_grupo AS permiso ON permiso.accion_id = accion.id
                	INNER JOIN grupo AS grupo ON grupo.id = permiso.grupo_id
                WHERE 
                	seccion_menu.status = 1 
                	AND accion.status = 1 
                	AND grupo.status = 1 
                	AND permiso.grupo_id = $grupo_id AND seccion_menu.menu_id = $menu_id
                        AND accion.visible = 1
                GROUP BY seccion_menu.id
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