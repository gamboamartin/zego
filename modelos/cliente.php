<?php
namespace models;
use gamboamartin\errores\errores;

class cliente extends modelo_sobrecargado{

    public function __construct($link)
    {
        $this->error = new errores();
        parent::__construct($link);
    }


    /**
     * ERROR UNIT
     * @param int $cliente_id
     * @return array
     */
    public function cliente(int $cliente_id): array
    {

        $cliente = (new cliente($this->link))->registro(id:$cliente_id, tabla: 'cliente');
        if(errores::$error){
            return $this->error->error('Error al obtener cliente', $cliente);
        }
        return $cliente;

    }

    /**
     * ERROR UNIT
     * @param int $cliente_id
     * @return array|string
     */
    public function cp(int $cliente_id): array|string
    {

        $cliente = (new cliente($this->link))->cliente(cliente_id: $cliente_id);
        if(errores::$error){
            return $this->error->error('Error al obtener cliente', $cliente);
        }

        return  $cliente['cliente_cp'];
    }
}
