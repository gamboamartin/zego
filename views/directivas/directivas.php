<?php
namespace views\directivas;
use gamboamartin\errores\errores;
use models\accion;
use models\menu;
use models\modelos;
use models\seccion_menu;

class directivas{
    private errores $error;

    public function __construct(){
        $this->error = new errores();
    }

    /**
     * ERROR
     * @param $etiqueta
     * @param $accion
     * @return array|string
     */
    public function breadcrumb($etiqueta, $accion=""){
        $etiqueta = $this->genera_texto_etiqueta($etiqueta);
        if(errores::$error){
            return $this->error->error('Error al generar etiqueta', $etiqueta);
        }
        $etiqueta = str_replace('_', ' ', $etiqueta);
        if($accion == ""){
            $link = "#";
        }
        else{
            $link = "./index.php?seccion=".SECCION."&session_id=".SESSION_ID."&accion=$accion";
        }
        return "<a class='breadcrumb-item' href='$link'>$etiqueta</a>";
    }

    /**
     * ERROR
     * @param $etiqueta
     * @return array|string
     */
    public function breadcrumb_active($etiqueta){
        $etiqueta = $this->genera_texto_etiqueta($etiqueta);
        if(errores::$error){
            return $this->error->error('Error al generar texto', $etiqueta);
        }
        return "<span class='breadcrumb-item active'>$etiqueta</span>";
    }

    /**
     * ERROR
     * @param $breadcrumbs
     * @param $active
     * @return array|string
     */
    public function breadcrumbs($breadcrumbs, $active){
        $html = $this->breadcrumb(SECCION). " / ";
        if(errores::$error){
            return $this->error->error('Error al generar breadcrumbs', $html);
        }
        foreach ($breadcrumbs as $key => $value) {
            $link = strtolower($value);
            $etiqueta = $this->genera_texto_etiqueta($link);
            if(errores::$error){
                return $this->error->error('Error al generar ETIQUETA', $etiqueta);
            }
            $html .= $this->breadcrumb($etiqueta, $link) . ' / ';
            if(errores::$error){
                return $this->error->error('Error al generar breadcrumb', $html);
            }
        }
        $html .= $this->breadcrumb_active($active);
        if(errores::$error){
            return $this->error->error('Error al generar breadcrumb', $html);
        }
        return $html;
    }

    public function btn_enviar($cols=12, $label = '', $id=false, $class=false): string
    {
        $html = "<div class='form-group col-md-$cols'>";
        $html .= "<button type='submit' id='$id' class='btn btn-lg btn-primary btn-block btn-signin $class' >$label</button>";
        $html .= "</div>";
        return $html;
    }

    public function checkbox($checked=false, $cols=12, $campo=false, $aplica_etiqueta = True, $clase=false){
        $campo_capitalize = '';
        $class = '';
        if($aplica_etiqueta) {
            $campo_capitalize = $this->genera_texto_etiqueta($campo).' : ';
        }


        if($clase){
            $class = 'class="'.$clase.'"';
        }

        if((int)$checked === 1){
            $checked_text = "checked";
        }
        else{
            $checked = "";
        }
        $html = "<div class='form-group col-md-$cols'>
			<label for='$campo_capitalize'>
				$campo_capitalize <input type='checkbox' name='$campo' value='1' $class  $checked>
			</label>
		</div>";
        return $html;
    }

    public function encabezado_form_modifica($registro_id){
		$html = "
		<form
		id='form-".SECCION."-modifica' name='form-".SECCION."-modifica' 
		method='post' 
		action='./index.php?seccion=".SECCION."&accion=modifica_bd&session_id=".SESSION_ID."&registro_id=".$registro_id."'
		enctype='multipart/form-data'>
		";
	return $html;
	}

	public function div_busqueda(){
		if (isset($_GET['valor'])){
			$valor_filtro = $_GET['valor'];
		}
		else{
			$valor_filtro = "";
		}
		$html = "<div class='input-group col-md-6 col-md-offset-6 busqueda'>";
        $html = $html."<span class='input-group-addon'>";
        $html = $html."<span class='glyphicon glyphicon-search' aria-hidden='true'></span>";
		$html = $html."</span>";
        $html = $html."<input  type='text' class='form-control input-md busca_registros' placeholder='Ingresa Busqueda' value='$valor_filtro'>";
		$html = $html."</div>";
		return $html;
	}

    public function div_encabezado_registro_base($etiqueta, $contenido){
        $etiqueta = $this->genera_texto_etiqueta($etiqueta);
        $html = "<div class='panel-heading'>$etiqueta: <b class='registro_id'>$contenido</b></div>";
        return $html;
    }

	public function div_encabezado_registro_lista($contenido){
        $etiqueta = $this->genera_texto_etiqueta(SECCION);
		$html = "<div class='panel-heading'>$etiqueta: <b class='registro_id'>$contenido</b></div>";
		return $html;
	}

	public function div_subtitulo($link, $descripcion){
		$html = "
		<div class='panel-heading'>
			<h4 class='panel-title'>
				<a data-toggle='collapse' data-parent='#accordion' href='#$link'>
					$descripcion
				</a>
			</h4>
     	</div>";
     	return $html;		
	}

	public function div_titulo($titulo){
		$html = "
        <div class='text-center'>
            <h3>
            $titulo
            </h3>
        </div>";
        return $html;
	}

    public function email($value="", $cols = 12, $campo = ''): string
    {
        $campo_capitalize = $this->genera_texto_etiqueta($campo);
        return "
		<div class='form-group col-md-$cols'>
			<label for='$campo'>$campo_capitalize:</label>
			<input 
				type='email' class='form-control input-md' name='$campo' placeholder='Ingresa $campo_capitalize' 
				required title='Ingrese un $campo_capitalize' value='$value'>
		</div>";
    }

	public function encabezado_form_alta($accion, $accion_envio=False){
	    if(!$accion_envio){
	        $accion_envio = 'alta_bd';
        }
		$html = "
		<form 
			id='form-".SECCION."-$accion' name='form-".SECCION."-$accion' method='POST' 
			action='./index.php?seccion=".SECCION."&session_id=".SESSION_ID."&accion=$accion_envio' enctype='multipart/form-data'>";
		return $html;

	}

    public function fecha($value="", $cols = 12, $campo = ""): string
    {
        $campo_capitalize = $this->genera_texto_etiqueta($campo);
        return "
		<div class='form-group col-md-$cols'>
			<label for='$campo'>$campo_capitalize:</label>
			<input 
				type='date' class='form-control input-md' name='$campo' id='$campo' placeholder='Ingresa $campo_capitalize' 
				required title='Ingrese una $campo' value='$value'>
		</div>";
    }

    public function genera_contenedor_select($tabla,$cols,$disabled, $arreglo, $partida, $required, $pattern){
        $etiqueta_label = ucfirst(strtolower($tabla));
        $etiqueta_select_inicial = $this->genera_texto_etiqueta($etiqueta_label);

        $html = "<div class='form-group col-md-$cols' id='contenedor_select_$tabla'>";
        $html = $html . "<label for='$tabla'>$etiqueta_select_inicial:</label>";
        $html = $html . "<select name='" . $tabla . "_id$arreglo' $disabled class='selectpicker $tabla"."_id$partida' data-live-search='true' 
                   title='Seleccione un $etiqueta_select_inicial' data-width='100%' id='select_$tabla".$partida."' 
                   data-none-results-text='No se encontraron resultados' $required pattern='$pattern'>";
        return $html;
    }

	private function genera_form_group($cols, $campo){
        $html = "<div class='form-group col-md-$cols selector_$campo'>";
        $html = $html."|label|";
        $html = $html."|input|";
        $html = $html."</div>";
        return $html;
    }

    private function genera_label($campo){
        $campo_mostrable = $this->genera_texto_etiqueta($campo);
	    $html = "<label for='$campo'>$campo_mostrable:</label>";
	    return $html;
    }

    public function genera_input($campo, $value=False, $required=False, $pattern=False, $arreglo=False,$partida=False,$disabled=False, $tamano_input='md'){
        $campo_mostrable = $this->genera_texto_etiqueta($campo);

        $html_pattern = '';
        if($pattern){
            $html_pattern = "pattern='$pattern'";
        }

        $html = "<input 
				type='text' class='form-control input-$tamano_input $campo".$partida."' id='$campo".$partida."' name='".$campo.$arreglo."' 
				placeholder='Ingresa $campo_mostrable' 
				$required title='Ingrese una $campo_mostrable' value='$value'
				$html_pattern $disabled>";
        return $html;
    }

    private function genera_input_moneda($campo, $value, $required, $pattern, $arreglo,$partida,$disabled){
        $campo_mostrable = $this->genera_texto_etiqueta($campo);

        $html_pattern = '';
        if($pattern){
            $html_pattern = "pattern='$pattern'";
        }

        $html = "<input 
				type='number' step='.01' class='form-control input-md $campo".$partida."' id='$campo".$partida."' name='".$campo.$arreglo."' 
				placeholder='Ingresa $campo_mostrable' 
				$required title='Ingrese una $campo_mostrable' value='$value'
				$html_pattern $disabled>";
        return $html;
    }

    public function genera_input_text($campo, $cols, $value, $required, $pattern, $arreglo,$partida,$disabled){
        $html = $this->genera_form_group($cols,$campo);
        $label = $this->genera_label($campo);
        $input = $this->genera_input($campo, $value, $required, $pattern, $arreglo,$partida,$disabled);
        $html = str_replace('|label|',$label,$html);
        $html = str_replace('|input|',$input,$html);
        return $html;
    }

    public function genera_input_valor_moneda($campo, $cols, $value, $required, $pattern, $arreglo,$partida,$disabled){
        $html = $this->genera_form_group($cols, $campo);
        $label = $this->genera_label($campo);
        $input = $this->genera_input_moneda($campo, $value, $required, $pattern, $arreglo,$partida,$disabled);
        $html = str_replace('|label|',$label,$html);
        $html = str_replace('|input|',$input,$html);
        return $html;
    }

    /**
     * ERROR UNIT
     * @param $texto
     * @return string
     */
    PUBLIC function genera_texto_etiqueta($texto): string
    {
        $texto = trim($texto);
        $campo_capitalize = ucfirst($texto);
        $campo_capitalize = str_replace('_', ' ', $campo_capitalize);
        return ucwords($campo_capitalize);
    }

    public function head_lista(){
        $etiqueta = str_replace('_', ' ', SECCION);
        $etiqueta = strtolower($etiqueta);
        $etiqueta = ucfirst($etiqueta);
        $html = "<div class='panel-heading'>$etiqueta</div>";
        return $html;
    }

    public function input_cambiable($name, $value,$dato_muestra,$cols){
        $campo_capitalize = $this->genera_texto_etiqueta($name);
        $html = "<div class='col-md-$cols' id='contenedor_$name'>";
        $html = $html."<label>$campo_capitalize:</label>";
        $html = $html."</div>";
        return $html;
    }

    public function input_hidden($name=false,$value=false, $clase=false){
        $class = '';
        if($clase){
            $class = 'class="'.$clase.'"';
        }

        if($name){
            $name = 'name="'.$name.'"';
        }

        $html = "<input type='hidden' $name value='$value' $class>";
        return $html;
    }

    public function link_elimina_anticipo($id){
        $html = "<a href='index.php?seccion=anticipo&accion=elimina_bd&registro_id=$id&session_id=".SESSION_ID."' 
            title='Elimina Anticipo'>
  					<span class='glyphicon glyphicon-remove' aria-hidden='true'></span>
  				</a>";
        return $html;
    }

    public function link_ve_anticipo($seccion, $id, $status_factura){
        $accion = 'vista_preliminar_anticipo';
        $html = "<a href='index.php?seccion=$seccion&accion=$accion&anticipo_id=$id&session_id=".SESSION_ID."' title='Ve Anticipo'>
  					<span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span>
  				</a>";
        return $html;
    }

    public function link_ve_factura_pdf($seccion, $id, $status_factura){
        $accion = 'vista_preliminar_factura';
        $html = "<a href='index.php?seccion=$seccion&accion=$accion&factura_id=$id&session_id=".SESSION_ID."' title='Ve factura PDF'>
  					<span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span>
  				</a>";
        return $html;
    }

    public function link_ve_nota_credito($seccion, $id, $status_factura){
        $accion = 'vista_preliminar_nota_credito';
        $html = "<a href='index.php?seccion=$seccion&accion=$accion&nota_credito_id=$id&session_id=".SESSION_ID."' title='Ve NOta Credito'>
  					<span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span>
  				</a>";
        return $html;
    }

    /**
     * ERROR
     * @param $link
     * @return array|string
     */
    public function menu($link){
        $modelo_menu = new menu($link);
        $registros = $modelo_menu->obten_menu_permitido();
        if(errores::$error){
            return $this->error->error('Error al obtener menu permitido', $registros);
        }
        $menus = $registros['registros'];
        $html = "";
        foreach ($menus as $key => $menu) {
            $etiqueta_menu = str_replace('_', ' ', $menu['descripcion']);

            $submenu = $this->submenu($menu['id'],$link);
            if(errores::$error){
                return $this->error->error('Error al generar submenu', $submenu);
            }

            $html = $html."
			<li>
              <a href='#' class='dropdown-toggle' data-toggle='dropdown'>
                <span class='".$menu['icono']."'></span> ".ucfirst($etiqueta_menu)." <b class='caret'></b>
              </a>
              <ul class='dropdown-menu'>
              ".$submenu."
              </ul>
            </li>";
        }

        return $html;
    }

    /**
     * ERROR
     * @param $cols
     * @param $offset
     * @param $breadcrumbs
     * @return array|string
     */
    public function nav_breadcumbs($cols, $offset, $breadcrumbs){
        $breadcrumbs = $this->breadcrumbs($breadcrumbs, ACCION);
        if(errores::$error){
            return $this->error->error('Error al generar breadcrumbs', $breadcrumbs);
        }
        $html = "<nav class='breadcrumb  col-md-$cols col-md-offset-$offset'>
  			$breadcrumbs
			</nav>";
        return $html;
    }

    public function nav_breadcumbs_modifica($cols, $offset, $breadcrumbs){
        $breadcrumbs = $this->breadcrumbs($breadcrumbs, ACCION);
        $html = "<nav class='breadcrumb col-md-$cols col-md-offset-$offset'>
  			$breadcrumbs
			</nav>";
        return $html;
    }

    public function link_modifica($seccion,$id){
        $html = "<a href='index.php?seccion=$seccion&accion=modifica&registro_id=$id&session_id=".SESSION_ID."' title='Modifica'>
  					<span class='glyphicon glyphicon-pencil' aria-hidden='true'></span>
  				</a>";
        return $html;
    }

    public function link_modifica_fecha($seccion,$id){
        $html = "<a href='index.php?seccion=$seccion&accion=modifica_fecha&registro_id=$id&session_id=".SESSION_ID."' title='Modifica Fecha'>
  					<span class='glyphicon glyphicon-pencil' aria-hidden='true'></span>
  				</a>";
        return $html;
    }

	public function password($value="", $cols = 12, $campo = ""): string
    {
        $campo_capitalize = $this->genera_texto_etiqueta($campo);
        return "
        <div class='form-group col-md-$cols'>
            <label for='$campo'>$campo_capitalize:</label>
            <input 
                type='password' class='form-control input-md' name='$campo' placeholder='$campo_capitalize' 
                required title='Ingrese un $campo' value='$value'>
        </div>";
	}

    public function textarea($value="", $cols = 12, $campo = ""){
        $campo_capitalize = $this->genera_texto_etiqueta($campo);
        return "<div class='form-group col-md-$cols'>		
			<label for='$campo'>$campo_capitalize:</label>
			<textarea 
				class='form-control noresize' name='$campo' placeholder='Ingresa $campo_capitalize' 
				title='Ingrese $campo_capitalize'>$value</textarea>
		</div>";
    }





    public function genera_contenedor_select_personalizado($tabla,$cols,$disabled, $arreglo, $partida,$etiqueta, $nombre_contenedor,$requerido,$nombre_campo){
        $etiqueta_label = ucfirst(strtolower($etiqueta));
        $etiqueta_select_inicial = $this->genera_texto_etiqueta($etiqueta_label);

        $html = "<div class='form-group col-md-$cols' id='contenedor_select_$nombre_contenedor'>";
        $html = $html . "<label for='$tabla'>$etiqueta_select_inicial:</label>";
        $html = $html . "<select name='" . $nombre_campo . "$arreglo' $disabled class='selectpicker $nombre_campo"."$partida' data-live-search='true' 
                   title='Seleccione un $etiqueta_select_inicial' data-width='100%' id='select_$nombre_contenedor".$partida."' 
                   data-none-results-text='No se encontraron resultados' $requerido>";
        return $html;
    }

    public function genera_contenedor_select_normal($tabla,$cols,$disabled, $required, $arreglo){
        $etiqueta_label = ucfirst(strtolower($tabla));
        $etiqueta_select_inicial = $this->genera_texto_etiqueta($etiqueta_label);

        $html = "<div class='form-group col-md-$cols' id='contenedor_select_$tabla'>";
        $html = $html . "<label for='$tabla'>$etiqueta_select_inicial:</label>";
        $html = $html . "<select name='" . $tabla . "_id$arreglo' $disabled class='form-control input-md' 
                   title='Seleccione un $etiqueta_select_inicial'  
                   id='select_$tabla' $required>";
        return $html;
    }



	public function valida_selected($value,$tabla,$id){

        if ($value[$tabla . '_id'] == $id) {
            $selected = "selected";
        } else {
            $selected = "";
        }
        return $selected;
    }

    public function input_select($tabla, $id, $cols, $disabled,$arreglo, $partida,$link,$required,$pattern=False){

        $modelo = new modelos($link);
        $resultado = $modelo->obten_registros_activos($tabla);
        $registros = $resultado['registros'];

        if($tabla == 'cliente'){
            $campo = $tabla . '_razon_social';
        }
        else{
            $campo = $tabla . '_descripcion';
        }

        $html = $this->genera_contenedor_select($tabla,$cols,$disabled,$arreglo, $partida, $required, $pattern);
        foreach ($registros as $key => $value) {
            $selected = $this->valida_selected($value,$tabla,$id);
         

            $html = $html . "<option value='" . $value[$tabla . '_id'] . "' $selected>" . $value[$campo] . "</option>";
        }
        $html = $html . "</select></div>";
        return $html;
    }

    public function input_select_personalizado($tabla, $id, $cols, $disabled,$arreglo, $partida,$link,$etiqueta, $nombre_contenedor,$requerido,$nombre_campo){

        $modelo = new modelos($link);
        $resultado = $modelo->obten_registros_activos($tabla);
        $registros = $resultado['registros'];

        if($tabla == 'cliente'){
            $campo = $tabla . '_razon_social';
        }
        else{
            $campo = $tabla . '_descripcion';
        }

        $html = $this->genera_contenedor_select_personalizado($tabla,$cols,$disabled,$arreglo, $partida,$etiqueta, $nombre_contenedor,$requerido,$nombre_campo);
        foreach ($registros as $key => $value) {
            $selected = $this->valida_selected($value,$tabla,$id);
            $html = $html . "<option value='" . $value[$tabla . '_id'] . "' $selected>" . $value[$campo] . "</option>";
        }
        $html = $html . "</select></div>";
        return $html;
    }


    public function input_select_filtro($tabla, $id, $cols, $disabled,$arreglo, $partida, $registros,$campo,$campo_valor){

        $html = $this->genera_contenedor_select($tabla,$cols,$disabled,$arreglo, $partida);
        foreach ($registros as $key => $value) {
            $selected = $this->valida_selected($value,$tabla,$id);
            $html = $html . "<option value='" . $value[$campo_valor] . "' $selected>" . $value[$campo] . "</option>";
        }
        $html = $html . "</select></div>";
        return $html;
    }

    public function input_select_basico($name, $cols, $registros,$campo,$campo_valor){
        $disabled = false;
        $arreglo = false;
        $partida = false;

        $html = $this->genera_contenedor_select($name,$cols,$disabled,$arreglo, $partida);
        foreach ($registros as $key => $value) {
            $html = $html . "<option value='" . $value[$campo_valor] . "' >" . $value[$campo] . "</option>";
        }
        $html = $html . "</select></div>";
        return $html;
    }

    public function input_select_normal($tabla, $id, $cols, $disabled, $required, $arreglo){

        $modelo = new modelos();
        $resultado = $modelo->obten_registros_activos($tabla);
        $registros = $resultado['registros'];

        $html = $this->genera_contenedor_select_normal($tabla,$cols,$disabled, $required, $arreglo);
        foreach ($registros as $key => $value) {
            $selected = $this->valida_selected($value,$tabla,$id);
            $html = $html . "<option value='" . $value[$tabla . '_id'] . "' $selected>" . $value[$tabla . '_descripcion'] . "</option>";
        }
        $html = $html . "</select></div>";
        return $html;
    }

    public function input_select_columnas($tabla, $id, $cols, $disabled, $columnas,$link,$required,$pattern, $registros=array()){

        $modelo = new modelos($link);
        $n_registros = count($registros);
        if($n_registros == 0) {
            $resultado = $modelo->obten_registros_activos($tabla);
            $registros = $resultado['registros'];
        }
        $html = $this->genera_contenedor_select($tabla,$cols,$disabled,false,false, $required,$pattern);

        foreach ($registros as $key => $value) {
            $selected = $this->valida_selected($value,$tabla,$id);
            $html = $html . "<option value='" . $value[$tabla . '_id'] . "' $selected>";
            foreach ($columnas as $columna){
                $html = $html . $columna.' : '.$value[$columna].' ';
            }
            $html = $html."</option>";
        }
        $html = $html . "</select></div>";
        return $html;
    }

    public function btn_agrega($cols, $btn_id_name){
        $html = "<div class='form-group col-md-$cols'>
			<button type='button' 
			class='btn btn-lg btn-primary btn-block btn-signin $btn_id_name' >
			    Agrega
			</button>
		</div>";
        return $html;
    }

    public function btn_actualiza($cols, $btn_id_name){
        $html = "<div class='form-group col-md-$cols'>
			<button type='button' 
			class='btn btn-lg btn-primary btn-block btn-signin $btn_id_name' >
			    Actualiza
			</button>
		</div>";
        return $html;
    }

    public function inicializa_registro(
        $tablas, $registro, $tabla_relacionada, $arreglo, $campos_llenables, $clase_elimina){
        $html = "";
        $html = $html . "<div class='un_registro col-md-12'>";
        foreach ($tablas as $tabla) {

            $tabla_id = $registro[$tabla_relacionada . '_' . $tabla . '_id'];

            $html = $html . $this->input_select_normal($tabla, $tabla_id, 3,
                    false, 'required', $arreglo);
        }
        foreach ($campos_llenables as $campo_texto) {
            $value = $registro[$tabla_relacionada . '_'.$campo_texto];
            $html = $html . $this->genera_input_text(
                    $campo_texto, 3, $value, 'required',
                    false, $arreglo);
        }

        if($clase_elimina) {
            $html = $html . "<div class='col-md-3 btn_elimina_registro_lista'><br>";
            $html = $html . "<button type='button' class='btn btn-default' ";
            $html = $html . "aria-label='Left Align' title='Elimina'>";
            $html = $html . "<span class='glyphicon glyphicon-minus' aria-hidden='true'></span>";
            $html = $html . "</button></div>";
        }
        $html = $html . "</div>";
        return $html;
    }

    public function registro_lista($tablas,$campos_llenables,$arreglo,
                                   $tabla_relacionada, $id, $campo_busqueda, $clase_elimina){

        $resultado = false;
        $registros = false;
        if($tabla_relacionada) {
            $modelo = new $tabla_relacionada();
            $filtro = array($campo_busqueda=>$id);
            $resultado = $modelo->filtro_and($tabla_relacionada,$filtro);
            $registros = $resultado['registros'];
        }


        $html = "";

        if($registros) {
            foreach ($registros as $registro) {
                $html = $html.$this->inicializa_registro($tablas,$registro,$tabla_relacionada,
                        $arreglo,$campos_llenables, $clase_elimina);
            }
        }
        else{
            $html = $html.$this->inicializa_registro($tablas,false,$tabla_relacionada,$arreglo,
                    $campos_llenables, $clase_elimina);
        }

        return $html;
    }

    public function lista($cols, $campo,$tablas, $campos_llenables,$arreglo,
                          $tabla_relacionada, $id, $campo_busqueda, $clase_elimina){
        $campo_capitalize = $this->genera_texto_etiqueta($campo);

        $html = "<div class='form-group col-md-$cols'>";
        $html = $html."<label>$campo_capitalize:</label>";
        $html = $html."<div class='col-md-12 contenedor_registros'>";

        $html = $html.$this->registro_lista($tablas,$campos_llenables,$arreglo,
                $tabla_relacionada, $id, $campo_busqueda, $clase_elimina);


        $html = $html."</div>";
        $html = $html.$this->btn_agrega(12,'btn_'.$campo);
        $html = $html."</div>";
        return $html;
    }

    public function autocomplete($tabla,$value,$cols){

        $campo = $tabla;
        $campo_capitalize = $this->genera_texto_etiqueta($campo);
        $html = "
		<div class='form-group col-md-$cols'>
			<label for='$campo'>$campo_capitalize:</label>
			<input 
				type='text' class='form-control input-md' name='$campo' placeholder='$campo_capitalize' 
				 title='Ingrese un $campo' value='', id='$campo"."_id"."'>
		</div>";
        $html = $html."<div class='form-group col-md-$cols'>
			<label for='$campo'>$campo_capitalize:</label>
			<div id='$campo"."_datos"."'>$value</div>
		</div>";
        return $html;
    }

    public function input_select_filtrado($tabla, $id=-1, $registros = array()): string
    {
        $etiqueta_label = ucfirst(strtolower($tabla));
        $etiqueta_select_inicial = $this->genera_texto_etiqueta($etiqueta_label);

        $html = "<label for='$tabla'>$etiqueta_select_inicial:</label>";
        $html .= "<select name='" . $tabla . "_id' class='form-control'
                   title='Seleccione un $etiqueta_select_inicial'  id='select_$tabla' 
                    required>";

        $html .= "<option value=''> Seleccione un " . $etiqueta_select_inicial . "</option>";

        foreach ($registros as $key => $value) {
            $selected = $this->valida_selected($value,$tabla,$id);
            $html .= "<option value='" . $value[$tabla . '_id'] . "' $selected>" . $value[$tabla . '_descripcion'] . "</option>";
        }

        return $html;
    }



	public function link_activa($id){
		$html = "<a href='#registro_$id' class='link_accion activa' title='Activa registro' title='activa registro'>
  					<span class='glyphicon glyphicon-ok' aria-hidden='true'></span>
  				</a>";
  		return $html;
	}

    public function link_asigna_tipo_cambio($seccion, $id){
        $html = "<a href='index.php?seccion=$seccion&accion=asigna_tipo_cambio&".$seccion."_id=$id' title='Asigna tipo de cambio'>
  					<span class='glyphicon glyphicon-usd' aria-hidden='true'></span>
  				</a>";
        return $html;
    }

    public function link_ve_tipo_cambio($modal_id){
        $html = "<a href='#' class='ve_tipo_cambio' title='ve historial tipo de cambio' data-toggle='modal' data-target='#modal'>
                    <input type='hidden' class='moneda_id' value='$modal_id'>
  					<span class='glyphicon glyphicon-th-list' aria-hidden='true'></span>
  				</a>";
        return $html;
    }

	public function link_asigna_accion($seccion,$id){
		$html = "<a href='index.php?seccion=$seccion&session_id=".SESSION_ID."&accion=asigna_accion&".$seccion."_id=$id' title='asigna accion'>
  					<span class='glyphicon glyphicon-lock' aria-hidden='true'></span>
  				</a>";
  		return $html;		
	}

    public function link_genera_factura($seccion,$id){
        $html = "<a href='index.php?seccion=$seccion&accion=genera_factura&".$seccion."_id=$id&session_id=".SESSION_ID."' title='genera factura'>
  					<span class='glyphicon glyphicon-modal-window' aria-hidden='true'></span>
  				</a>";
        return $html;
    }

    public function link_persona_responsable_pago_cliente($seccion,$id){
        $html = "<a href='index.php?seccion=$seccion&accion=persona_responsable_pago_cliente&".$seccion."_id=$id&session_id=".SESSION_ID."' title='persona responsable pago cliente'>
  					<span class='glyphicon glyphicon-plus' aria-hidden='true'></span>
  				</a>";
        return $html;
    }
    public function link_asigna_cuenta_bancaria($seccion,$id){
        $html = "<a href='index.php?seccion=$seccion&accion=asigna_cuenta_bancaria&".$seccion."_id=$id&session_id=".SESSION_ID."' title='Asigna Cuenta Bancaria'>
  					<span class='glyphicon glyphicon-barcode' aria-hidden='true'></span>
  				</a>";
        return $html;
    }

    public function link_carga_datos_cfdi($seccion,$id){
        $html = "<a href='index.php?seccion=$seccion&accion=carga_datos_cfdi&".$seccion."_id=$id&session_id=".SESSION_ID."' title='Carga Datos CFDI'>
  					<span class='glyphicon glyphicon-qrcode' aria-hidden='true'></span>
  				</a>";
        return $html;
    }

    public function ve_facturas($seccion,$id){
        $html = "<a href='index.php?seccion=$seccion&accion=ve_facturas&".$seccion."_id=$id&session_id=".SESSION_ID."' title='Ve facturas'>
  					<span class='glyphicon glyphicon-tasks' aria-hidden='true'></span>
  				</a>";
        return $html;
    }



    public function link_ve_factura_pago_pdf($seccion, $id, $status_factura){
        $accion = 'vista_preliminar';
        $html = "<a href='index.php?seccion=$seccion&accion=$accion&pago_cliente_id=$id&session_id=".SESSION_ID."' title='Ve factura PDF'>
  					<span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span>
  				</a>";
        return $html;
    }

    public function link_ve_pago($seccion, $id, $status_factura, $accion){
        $html = "<a href='index.php?seccion=$seccion&accion=$accion&pago_cliente_id=$id&session_id=".SESSION_ID."' title='Ve factura PDF'>
                    <span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span>
                </a>";
        return $html;
    }

    public function link_descarga_factura_pdf($seccion, $id){
        $html = "<a href='index.php?seccion=$seccion&accion=descarga_factura_pdf&factura_id=$id&session_id=".SESSION_ID."' title='Descarga factura PDF'>
                    <span class='glyphicon glyphicon-save-file' aria-hidden='true'></span>
                </a>";
        return $html;
    }

    public function link_descarga_pago_pdf($seccion, $id){
        $html = "<a href='index.php?seccion=$seccion&accion=ve_pdf&pago_cliente_id=$id&session_id=".SESSION_ID."' title='Descarga factura PDF'>
                    <span class='glyphicon glyphicon-save-file' aria-hidden='true'></span>
                </a>";
        return $html;
    }

    public function link_informe_gastos_pdf($seccion, $id){
        $html = "<a href='index.php?seccion=$seccion&accion=informe_gastos_pdf&session_id=".SESSION_ID."&factura_id=$id' title='Descarga Informe de Gastos PDF'>
  					<span class='glyphicon glyphicon-info-sign' aria-hidden='true'></span>
  				</a>";
        return $html;
    }

    public function link_paga_factura($seccion, $id){
        $html = "<a href='index.php?seccion=$seccion&accion=paga_factura&factura_id=$id&session_id=".SESSION_ID."' title='Paga Factura'>
  					<span class='glyphicon glyphicon-usd' aria-hidden='true'></span>
  				</a>";
        return $html;
    }

    public function link_descarga_factura_xml($seccion, $id, $referencia=''){
        $html = "<a href='index.php?seccion=$seccion&session_id=".SESSION_ID."&accion=descarga_factura_xml&factura_id=$id' 
            title='Descarga factura XML'>
  					<span class='glyphicon glyphicon-file' aria-hidden='true'></span>
  				</a>";
        return $html;
    }

    public function link_descarga_pago_xml($seccion, $id){
        $html = "<a href='index.php?seccion=$seccion&accion=descarga_xml&pago_cliente_id=$id&session_id=".SESSION_ID."' 
            title='Descarga factura XML'>
  					<span class='glyphicon glyphicon-file' aria-hidden='true'></span>
  				</a>";
        return $html;
    }

    public function link_cancela_factura($id){
        $html = "<a href='index.php?seccion=factura&accion=cancela_factura&factura_id=$id&session_id=".SESSION_ID."' 
            title='Cancela Factura'>
  					<span class='glyphicon glyphicon-remove' aria-hidden='true'></span>
  				</a>";
        return $html;
    }


    public function link_carga_datos_servicio($seccion,$id){
        $html = "<a href='index.php?seccion=$seccion&accion=carga_datos_servicio&".$seccion."_id=$id&session_id=".SESSION_ID."' title='Carga datos de servicio'>
  					<span class='glyphicon glyphicon-cog' aria-hidden='true'></span>
  				</a>";
        return $html;
    }

	public function link_asigna_permiso($seccion,$id){
		$html = "<a href='index.php?seccion=$seccion&accion=asigna_accion&".$seccion."_id=$id&session_id=".SESSION_ID."' title='asigna permiso'>
  					<span class='glyphicon glyphicon-lock' aria-hidden='true'></span>
  				</a>";
  		return $html;		
	}

    public function link_ve_pagos($id){
        $html = "<a href='index.php?seccion=factura&accion=ve_pagos&factura_id=$id&session_id=".SESSION_ID."' title='ve_pagos'>
                    <span class='glyphicon glyphicon-th-list' aria-hidden='true'></span>
                </a>";
        return $html;
    }

    public function link_cancela_factura_opcion($id){
        $html = "<a href='index.php?seccion=factura&accion=guarda_factura_cancelar&factura_id=$id&session_id=".SESSION_ID."' title='Opciones de cancelacion'>
                    <span class='glyphicon glyphicon-remove' aria-hidden='true'></span>
                </a>";
        return $html;
    }


    public function link_cambia_status($seccion, $id, $status){
		$html = "";
		if($status==1){
  			$html = $html.$this->link_desactiva($seccion, $id); 
  		}
  		else{ 
  			$html = $html.$this->link_activa($seccion, $id);
  		} 
  		return $html;		
	}


	public function link_desactiva($id){
		$html = "
		<a href='#registro_$id' class='link_accion desactiva' title='Desactiva registro'>
  			<span class='glyphicon glyphicon-minus' aria-hidden='true'></span>
  		</a>";
  		return $html;
	}

	public function link_elimina($id){
		$html = "
		<a href='#registro_$id' class='link_accion elimina' title='Elimina'>
			<span class='glyphicon glyphicon-trash' aria-hidden='true'></span>
  		</a>";
  		return $html;
	}

	public function link_elimina_externa($seccion,$id){
        $html = "<a href='index.php?seccion=$seccion&accion=elimina_cuenta_bd&registro_id=$id&session_id=".SESSION_ID."' title='Elimina Cuenta'>
  					<span class='glyphicon glyphicon-trash' aria-hidden='true'></span>
  				</a>";
        return $html;
    }

    /**
     * ERROR
     * @param $seccion_menu_id
     * @param $seccion_menu_descripcion
     * @param $link
     * @return array|string
     */
    public function link_menu($seccion_menu_id, $seccion_menu_descripcion,$link){
        $link_seccion_menu_descripcion = strtolower($seccion_menu_descripcion);
        $modelo_accion = new accion($link);
        $resultado = $modelo_accion->obten_accion_permitida($seccion_menu_id);
        if(errores::$error){
            return $this->error->error('Error al obtener accion', $resultado);
        }

        if(isset($resultado['error'])) {
            if ($resultado['error'] == 1) {

                return $resultado;
            }
        }

        $menus = $resultado['registros'];

        $html = "";
        foreach ($menus as $key => $menu) {
            $link_accion = strtolower($menu['accion_descripcion']);
            $etiqueta_accion = ucfirst($link_accion);
            $html = $html."<li><a href='index.php?seccion=$link_seccion_menu_descripcion&accion=$link_accion&session_id=".SESSION_ID."'>$etiqueta_accion</a></li>";
        }
        return $html;
    }

    public function reporte_campo(){

    }

    public function span_etiqueta($etiqueta, $valor){
        $etiqueta = strtolower($etiqueta);
        $etiqueta = ucfirst($etiqueta);
        $html = " <span><label>$etiqueta: </label> $valor </span> ";
        return $html;
    }

    public function span_lista_descripcion($contenido){
        $html = "<span><label>Descripcion:</label>$contenido</span>";
        return $html;
    }

    public function span_lista_descripcion_padre($contenido){
        $html = "<span><label>Grupo:</label>$contenido</span>";
        return $html;
    }

    public function span_lista_status($status){
        $html = "<span class='tag_status'><label>Status: </label>";
        if($status==1){
            $html = $html.'<span class="resultado-status"> Activo </span>  ';
        }
        else{
            $html = $html.'<span class="resultado-status"> Inactivo </span> ';
        }
        $html = $html."</span>";
        return $html;
    }

    public function span_lista_visible($visible){
        $html = "<span><label>Visible:</label>";
        if($visible==1){
            $html = $html.'Si';
        }
        else{
            $html = $html.'No';
        }
        $html = $html."</span>";
        return $html;
    }

    public function span_registro_general($descripcion, $status){
        $html = "";
        $html = $html.$this->span_lista_descripcion($descripcion);
        $html = $html." ";
        $html = $html.$this->span_lista_status($status);
        return $html;
    }

    public function span_registro_general_join($descripcion, $status, $descripcion_padre){
        $html = "";
        $html = $html.$this->span_lista_descripcion($descripcion);
        $html = $html." ";
        $html = $html.$this->span_lista_descripcion_padre($descripcion_padre);
        $html = $html." ";
        $html = $html.$this->span_lista_status($status);
        return $html;
    }

    /**
     * ERROR
     * @param $menu_id
     * @param $link
     * @return array|string
     */
	public function submenu($menu_id,$link){
		$modelo_seccion_menu = new seccion_menu($link);
		$resultado = $modelo_seccion_menu->obten_submenu_permitido($menu_id);
		$menus = $resultado['registros'];

		$html = "";
		foreach ($menus as $key => $menu) {

			$etiqueta_menu = str_replace('_', ' ', $menu['descripcion']);

			$seccion_menu_descripcion = $menu['descripcion'];
			$submenu = $this->link_menu($menu['id'],$seccion_menu_descripcion,$link);
            if(errores::$error){
                return $this->error->error('Error al generar link de submenu', $submenu);
            }
			$html = $html."<li>
                      <a href='#' class='dropdown-toggle' data-toggle='dropdown'>
                        ".ucfirst($etiqueta_menu)."<b class='caret'></b>
                      </a>
                      <ul class='dropdown-menu evento'>
                      	".$submenu."
                      </ul>
                    </li>";
		}
		return $html;

	}



}