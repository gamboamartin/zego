<?php
echo $directiva->input_select('insumo',$controlador->insumo_id,2,false,false,false,$controlador->link,'required',false);
echo $directiva->genera_input_text('cantidad',2,false,false,false,false,false,false);
echo $directiva->genera_input_text('valor_unitario',2,false,false,false,false,false,false);
echo $directiva->genera_input_text('traslados',2,false,false,false,false,false,'disabled');
echo $directiva->genera_input_text('retenciones',2,false,false,false,false,false,'disabled');
echo $directiva->genera_input_text('total',2,false,false,false,false,false,'disabled');
