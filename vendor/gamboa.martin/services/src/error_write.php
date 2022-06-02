<?php
namespace gamboamartin\services\error_write;


use JetBrains\PhpStorm\NoReturn;

class error_write{

    /**
     * Salida de error de servicio escribe eñ documento con el trazado de los errores
     * @param array $error Error resultante de la clase errores
     * @param string $info Infomacion a escribir del error previo
     * @param string $path_info Ruta del archivo info del servicio generado en el constructor de services
     * @return void
     */
    #[NoReturn] public function out(array $error, string $info, string $path_info): void
    {

        $this->write(error: $error,info:  $info,path_info:  $path_info);
        print_r($error);
        die('Error');
    }

    /**
     * Aqui se escribe al error identificado cuando se corre el servicio, esta funcion solo debe ser utilizada
     * desde donde se ejecuta el servicio
     * @param array $error Error resultante de la clase errores
     * @param string $info Infomacion a escribir del error previo
     * @param string $path_info Ruta del archivo info del servicio generado en el constructor de services
     * @return void
     */
    private function write(array $error, string $info, string $path_info): void
    {
        $data = print_r($error,true);
        $info .= file_get_contents($path_info).$data;
        file_put_contents($path_info,$info);
    }
}


