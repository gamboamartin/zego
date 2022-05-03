<?php

class Controlador_Mensaje{

	function genera_mensaje($resultado,$operacion){
		if($resultado == 'correcto') {$color = 'success';}else{$color = 'danger';}
		if ($resultado == 'correcto') {$titulo = 'Registro '.$operacion.'do Correctamente';}
			else{$titulo = 'El Registro No Se Ha '.$operacion.'do';}
		$modal = "
				<div id='".$resultado."".$operacion."' class='modal fade' tabindex='-1' role='dialog'>
		  			<div class='modal-dialog modal-sm' role='document'>
		    			<div class='modal-content text-center alert-".$color."'>
							<div class='modal-header'>
		        				<h4 class='modal-title'><strong>".$titulo."</strong></h4>
		      				</div>
		      				<div class='modal-footer modal-pie'>
		        				<button type='button' class='btn btn-".$color."' data-dismiss='modal'>Aceptar
		        				</button>
		    				</div>
		    			</div>
		  			</div>
				</div>";
		echo $modal;
	}

}

$mensaje_controller = new Controlador_Mensaje();

?>



		      				