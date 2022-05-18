<?php

use gamboamartin\errores\errores;
use gamboamartin\plugins\files;

include "init.php";
const PATH_BASE = '/var/www/html/zego/';
require PATH_BASE.'vendor/autoload.php';

$service = $_GET['service'];

$data_service = (new files())->get_data_service(PATH_BASE.'services', $service);
if(errores::$error){
    $error = (new errores())->error(mensaje: 'Error al obtener datos',data: $data_service );
    print_r($error);
    die('Error');
}
if($data_service['file_lock'] !=='') {
    unlink($data_service['file_lock']);
}
if($data_service['file_info'] !=='') {
    unlink($data_service['file_info']);
}

var_dump($data_service);