<?php
namespace models;
use gamboamartin\errores\errores;

class cliente extends modelo_sobrecargado{

    public function __construct($link)
    {
        $this->error = new errores();
        parent::__construct($link);
    }


    public function cliente(int $cliente_id){

        $cliente = (new cliente($this->link))->registro(id:$cliente_id, tabla: 'cliente');
        if(errores::$error){
            return $this->error->error('Error al obtener cliente', $cliente);
        }
        return $cliente;

    }

    public function cp(int $cliente_id){

        $cliente = (new cliente($this->link))->cliente(cliente_id: $cliente_id);
        if(errores::$error){
            return $this->error->error('Error al obtener cliente', $cliente);
        }

        return  $cliente['cliente_cp'];
    }
}
