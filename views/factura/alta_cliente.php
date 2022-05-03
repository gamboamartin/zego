<?php
echo $directiva->genera_input_text('rfc',4,$controlador->cliente_rfc,'required',false,false,false,false);
echo $directiva->genera_input_text('razon_social',4,$controlador->cliente_razon_social,'required',false,false,false,false);
echo $directiva->genera_input_text('cp',4,$controlador->cliente_cp,'required',false,false,false,false);
echo $directiva->genera_input_text('colonia',4,$controlador->cliente_colonia,false,false,false,false,false);
echo $directiva->genera_input_text('calle',4,$controlador->cliente_calle,false,false,false,false,false);
echo $directiva->genera_input_text('exterior',4,$controlador->cliente_exterior,false,false,false,false,false);
echo $directiva->genera_input_text('interior',4,$controlador->cliente_interior,false,false,false,false,false);
echo $directiva->genera_input_text('telefono',4,$controlador->cliente_telefono,false,false,false,false,false);
echo $directiva->genera_input_text('email',4,$controlador->cliente_email,false,false,false,false,false);
echo $directiva->input_select('pais',$controlador->cliente_pais_id,4,false,false,false,$controlador->link,false,false);
echo $directiva->input_select('estado',$controlador->cliente_estado_id,4,false,false,false,$controlador->link,false,false);
echo $directiva->input_select('municipio',$controlador->cliente_municipio_id,4,false,false,false,$controlador->link,false,false);
echo $directiva->btn_enviar(6,'Guarda Datos de Cliente','guarda_cliente');
echo $directiva->btn_enviar(6,'Cancela Guarda Datos de Cliente','cancela_cliente');