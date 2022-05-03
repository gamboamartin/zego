<?php
namespace controllers;
class controlador_insumo extends controlador_base {
    public $input;
    public $tablas;
    public $campos_llenables;
    public function genera_lista_producto_sat(){
        $modelo = new Producto_Sat($this->link);
        $valor = $_POST['valor'];
        $resultado = $modelo->filtra_campos_base($valor, 'producto_sat');
        $this->registros = $resultado['registros'];
    }
}