<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

if(!isset($_GET['session_id'])){
    define('SESSION_ID',time());
}
else{
    define('SESSION_ID',$_GET['session_id']);
}

session_start();

class seguridad{
    public $seccion;
    public $accion;
    public $menu;

    public function __construct()
    {
        if(isset($_GET['seccion'])){
            $this->seccion = $_GET['seccion'];
        }
        else{
            $this->seccion = False;
        }
        if(isset($_GET['accion'])){
            $this->accion = $_GET['accion'];
        }
        else{
            $this->accion = False;
        }
        $this->menu = False;


        if(!$this->seccion){
            if(!isset($_SESSION['activa'])){
                $this->seccion = 'session';
                $this->accion = "login";
            }
            else{
                $this->seccion = 'session';
                $this->accion = "inicio";
            }
        }

        if($this->seccion=='session'){
            if($this->accion == 'login'){
                if(isset($_SESSION['activa']) == 1){
                    $this->seccion = 'session';
                    $this->accion = 'inicio';
                }
            }
        }

        if(isset($_SESSION['activa'])){
            if($_SESSION['activa'] == 1){
                $this->menu = True;
            }
        }

        if(!isset($_SESSION['activa'])){
            if($this->seccion != 'session'){
                if($this->accion != 'loguea'){
                    $this->menu = False;
                    $this->seccion = "session";
                    $this->accion = "login";
                }
            }
        }

        if($this->seccion == 'session' && $this->accion == 'inicio'){
            if(isset($_SESSION['activa'])){
                $this->accion = 'inicio';
            }
            else{
                $this->accion = 'login';
            }
        }
    }

}
