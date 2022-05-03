<?php
namespace config;
use controllers\controlador_base;
use controllers\controlador_session;
use gamboamartin\errores\errores;
use mysqli;
use PDO;

class init{
    private errores $error;
    public function __construct(){
        $this->error = new errores();
    }
    public function controller(PDO|mysqli|bool $link, string $seccion):controlador_base|array{
        $name_ctl = $this->name_controler(seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener nombre de controlador', data: $name_ctl, params: get_defined_vars());

        }

        if($seccion === 'session') {
            $controlador = new controlador_session(link: $link);
        }
        else{
            $controlador = new $name_ctl(link:$link);
        }
        return $controlador;
    }
    private function name_controler(string $seccion): string|array
    {
        $name_ctl = 'controlador_'.$seccion;
        $name_ctl = str_replace('controllers\\','',$name_ctl);
        $name_ctl = 'controllers\\'.$name_ctl;

        if(!class_exists($name_ctl)){
            return $this->error->error('Error no existe la clase '.$name_ctl,$name_ctl);
        }

        return $name_ctl;
    }
}
