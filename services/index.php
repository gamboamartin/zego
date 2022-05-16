<?php

use config\generales;
use gamboamartin\plugins\files;

include "init.php";
const PATH_BASE = '/var/www/html/zego/';
require PATH_BASE.'vendor/autoload.php';
$directorio = opendir('./');

$archivos = array();
while ($archivo = readdir($directorio)){
    if (!is_dir($archivo) && ($archivo!=='index.php')) {
        $archivos[] = $archivo;
        $es_lock = (new files())->es_lock_service(archivo: $archivo);
    }
}

asort($archivos);

foreach($archivos as $archivo){
    if (!is_dir($archivo) && ($archivo!=='index.php')) {
        $liga = '<a href="'.(new generales())->url_base.'/services/'.$archivo.'">';
        $liga .= $archivo;
        $liga .= '</a><br><br>';
        echo $liga;
    }
}
