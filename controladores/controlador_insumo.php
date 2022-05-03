<?php
class Controlador_Insumo extends Controlador_Base{
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