<?php

class Accion extends Modelos{
    public function obten_acciones_iniciales(){
        $grupo_id = $_SESSION['grupo_id'];
        $consulta = "SELECT 
                      seccion_menu.descripcion AS seccion_menu_descripcion,
                      accion.descripcion AS accion_descripcion,
                      accion.icono as accion_icono
                    FROM accion 
                      INNER JOIN accion_grupo ON accion_grupo.accion_id = accion.id
                      INNER JOIN seccion_menu ON seccion_menu.id = accion.seccion_menu_id
                      WHERE accion_grupo.grupo_id = $grupo_id AND accion.inicio = 1";

        $resultado = $this->ejecuta_consulta($consulta);

        return $resultado;
    }
    public function obten_accion_permitida_session($seccion, $accion){
        $accion = strtolower($accion);
        $consulta = "
                    SELECT COUNT(*) AS n_registros
                    FROM 
                      accion_grupo 
                    INNER JOIN accion ON accion.id = accion_grupo.accion_id
                    INNER JOIN seccion_menu ON seccion_menu.id = accion.seccion_menu_id
                    INNER JOIN grupo ON grupo.id = accion_grupo.grupo_id";
        $grupo_id = $_SESSION['grupo_id'];
        $where = " WHERE LOWER(seccion_menu.descripcion) = '$seccion' AND grupo_id = $grupo_id ";
        $where = $where." AND IFNULL(accion.visible,0) = 0 AND LOWER(accion.descripcion) = '$accion'";
        $where = $where." AND accion.status = 1 AND seccion_menu.status = 1 AND grupo.status = 1";

        $consulta = $consulta.$where;


        $resultado = $this->ejecuta_consulta($consulta);
        return $resultado;

    }

    public function obten_accion_permitida($seccion_menu_id){
        $grupo_id = $_SESSION['grupo_id'];
        $consulta = $this->genera_consulta_base('accion_grupo');
        $where = "
             WHERE
                accion.status = 1 
                AND grupo.status = 1 
                AND accion_grupo.grupo_id = $grupo_id 
                AND accion.seccion_menu_id = $seccion_menu_id
                AND accion.visible = 1 
                ";
        $consulta = $consulta.$where;
        $group_by = " GROUP BY accion.id ";
        $consulta = $consulta.$group_by;

        $resultado = $this->ejecuta_consulta($consulta);
        return $resultado;
	}

	public function cuenta_acciones(){
        $conexion = new Conexion();
        $conexion->selecciona_base_datos();
        $link = $conexion->link;

        $grupo_id = $_SESSION['grupo_id'];

        $consulta = "SELECT count(*) AS n_permisos
              		  FROM accion AS accion
              	      INNER JOIN seccion_menu AS seccion_menu ON seccion_menu.id = accion.seccion_menu_id
              	      INNER JOIN accion_grupo AS permiso ON permiso.accion_id = accion.id
                      INNER JOIN grupo AS grupo ON grupo.id = permiso.grupo_id
                    WHERE  
                	 accion.status = 1 
                	AND grupo.status = 1
                	AND seccion_menu.status = 1         	
                	AND permiso.grupo_id = $grupo_id
                ";

        $result = $link->query($consulta);
        if($link->error){
            return array('mensaje'=>$link->error.' '.$consulta, 'error'=>True);
        }
        $row = mysqli_fetch_assoc( $result);
        $n_permisos = $row['n_permisos'];
        return $n_permisos;
    }

	public function valida_permiso($seccion,$accion){

        $conexion = new Conexion();
        $conexion->selecciona_base_datos();
        $link = $conexion->link;
                
        $grupo_id = $_SESSION['grupo_id'];

        $consulta = "SELECT count(*) AS permiso
              		  FROM accion AS accion
              	      INNER JOIN seccion_menu AS seccion_menu ON seccion_menu.id = accion.seccion_menu_id
              	      INNER JOIN accion_grupo AS permiso ON permiso.accion_id = accion.id
                      INNER JOIN grupo AS grupo ON grupo.id = permiso.grupo_id
                    WHERE  
                	 accion.status = 1 
                	AND grupo.status = 1
                	AND seccion_menu.status = 1         	
                	AND permiso.grupo_id = $grupo_id 
                	AND seccion_menu.descripcion = '$seccion' AND accion.descripcion = '$accion'
                ";

        $result = $link->query($consulta);
        if($link->error){
            return array('mensaje'=>$link->error.' '.$consulta, 'error'=>True);
        }
        $row = mysqli_fetch_assoc( $result);
        $permiso = $row['permiso'];
        if($permiso == 1){
            return True;
        }
        else{
            return False;
        }
	}
    public function obten_acciones_activas(){
	    $sql = new consultas_base();
	    $consulta = $sql->genera_consulta_base('accion');
        $where = " WHERE accion.status = 1";
        $consulta = $consulta.$where;
        $resultado = $this->ejecuta_consulta($consulta);
        return $resultado;
    }
}