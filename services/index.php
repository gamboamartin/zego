<?php

use config\generales;
use gamboamartin\errores\errores;
use gamboamartin\plugins\files;

include "init.php";
const PATH_BASE = '/var/www/html/zego/';
require PATH_BASE.'vendor/autoload.php';
$directorio = opendir('./');

$archivos = (new files())->files_services(directorio: $directorio);
if(errores::$error){
    $error = (new gamboamartin\errores\errores())->error(mensaje:  'Error al asignar files',data: $archivos);
    print_r($error);
    die('Error');
}

$servicios = array();
foreach($archivos as $archivo){

    if($archivo->es_service){
        $servicios[$archivo->name_service]['file'] =  $archivo->file;
    }
    if($archivo->es_lock){
        $servicios[$archivo->name_service]['file_lock'] =  $archivo->file;
    }
    if($archivo->es_info){
        $servicios[$archivo->name_service]['file_info'] =  $archivo->file;
    }

}


foreach($servicios as $name_service =>$servicio){

    $liga = '<a href="'.(new generales())->url_base.'/services/'.$servicio['file'].'">';
    $liga .= $name_service;
    $liga .= '</a><br><br>';
    echo $liga;

}
