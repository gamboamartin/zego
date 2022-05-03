<?php
    $tablas = $controlador->input['tablas'];
    $campos_llenables = $controlador->input['campos_llenables'];
    echo $directiva->registro_lista($tablas,$campos_llenables,'[]',false,false,false,true);

