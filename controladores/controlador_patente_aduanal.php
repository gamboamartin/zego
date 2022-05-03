<?php
class Controlador_Patente_Aduanal extends Controlador_Base{
    public function lista(){
        $modelo = new Patente_Aduanal();
        $breadcrumbs = array('alta');
        $this->breadcrumbs = $this->directiva->nav_breadcumbs(12, 0, $breadcrumbs);
        $resultado = $modelo->obten_registros($_GET['seccion']);
        $this->registros = $resultado['registros'];
    }
    public function lista_ajax(){
        $modelo = new Patente_Aduanal();
        $valor = $_POST['valor'];
        $resultado = $modelo->filtra_campos_base($valor, $_GET['seccion']);
        $this->registros = $resultado['registros'];
    }

}