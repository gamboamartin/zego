<?php
class Controlador_Grupo extends Controlador_Base{
    public $grupo_descripcion;
    public $menus;
    public $secciones;
    public $acciones_vista;
    private $acciones;
    private $acciones_grupos;
    private $filtro;
    public $grupo_id;
    private $accion;
    private $resultado;
    private $accion_grupo;
    public function asigna_accion(){

        $directiva = new Directivas();
        $breadcrumbs = array('alta','lista');
        $this->breadcrumbs = $directiva->nav_breadcumbs_modifica(8, 2, $breadcrumbs);


        $this->grupo_id = $_GET['grupo_id'];
        $model_menu = new menu($this->link);
        $model_grupo = new grupo($this->link);
        $model_seccion = new Seccion_Menu($this->link);
        $model_accion = new Accion($this->link);
        $model_accion_grupo = new Accion_Grupo($this->link);

        $this->filtro = array('grupo_id'=>$this->grupo_id);
        $this->resultado = $model_accion_grupo->filtro_and(
            'accion_grupo',$this->filtro);
        $this->acciones_grupos = $this->resultado['registros'];

        $resultado = $model_grupo->obten_por_id('grupo',$this->grupo_id);
        $this->grupo_descripcion = $resultado['registros'][0]['grupo_descripcion'];
        $resultado = $model_menu->obten_registros_activos('menu');
        $this->menus = $resultado['registros'];
        $resultado = $model_seccion->obten_registros_activos('seccion_menu');
        $this->secciones = $resultado['registros'];
        $resultado = $model_accion->obten_registros_activos('accion');
        $this->acciones = $resultado['registros'];

        $this->acciones_vista = array();
        foreach($this->acciones as $this->accion){
            $aplicado = 0;
            $accion_grupo_id = -1;
            foreach ($this->acciones_grupos as $this->accion_grupo){
                if($this->accion['accion_id'] == $this->accion_grupo['accion_id']) {
                    $aplicado = 1;
                    $accion_grupo_id = $this->accion_grupo['accion_grupo_id'];
                    break;
                }
            }
            $this->accion['aplicado'] = $aplicado;
            $this->accion['accion_grupo_id'] = $accion_grupo_id;
            $this->acciones_vista[] = $this->accion;
        }
    }

    public function elimina_accion_bd(){
        $modelo = new Accion_Grupo($this->link);
        $tabla = 'accion_grupo';
        $grupo_id = $_POST['grupo_id'];
        $accion_id = $_POST['accion_id'];
        $filtros = array('grupo_id'=>$grupo_id,'accion_id'=>$accion_id);
        $resultado = $modelo->filtro_and('accion_grupo',$filtros);
        $registro = $resultado['registros'];

        $registro_id = $registro[0]['accion_grupo_id'];


        $modelo->elimina_bd($tabla, $registro_id);
    }

    public function agrega_accion_bd(){
        $tabla = 'accion_grupo';
        $modelo = new Modelos($this->link);
        $resultado = $modelo->alta_bd($_POST, $tabla);
    }

}
