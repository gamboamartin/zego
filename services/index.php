<?php

use config\generales;
use gamboamartin\errores\errores;
use gamboamartin\plugins\files;

include "init.php";
const PATH_BASE = '/var/www/html/zego/';
require PATH_BASE.'vendor/autoload.php';
$directorio = opendir('./');

$servicios = (new files())->get_files_services(directorio: $directorio);
if(errores::$error){
    $error = (new gamboamartin\errores\errores())->error(mensaje:  'Error al maquetar files',data: $servicios);
    print_r($error);
    die('Error');
}

foreach($servicios as $name_service =>$servicio){

    $liga = 'SERVICIO: ';
    $liga .= '<a href="'.(new generales())->url_base.'/services/'.$servicio['file'].'">';
    $liga .= $name_service;
    $liga .= '</a>';
    $liga.= ' LOCK: '.$servicio['file_lock'];
    $liga .= ' INFO <a href="'.(new generales())->url_base.'/services/'.$servicio['file_info'].'">';
    $liga .= $servicio['file_info'];
    $liga .= "</a>";

    $liga.= '<br><br>';

    echo $liga;
}
