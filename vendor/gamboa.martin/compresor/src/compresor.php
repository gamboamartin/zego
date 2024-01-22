<?php //DEBUG

namespace gamboamartin\compresor;

use gamboamartin\errores\errores;
use Imagick;
use ImagickException;
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use Throwable;
use ZipArchive;

ini_set('max_execution_time', '300');
set_time_limit(300);

class compresor{
    public errores $error;
    public function __construct(){
        if (!defined('PATH_BASE')) {
            define("PATH_BASE",'');
        }
        $this->error = new errores();
    }

    /**
     *
     * @param string $origen
     * @param string $name_file
     * @param ZipArchive $zip
     * @return array|string
     */
    private function agrega_archivo_zip(string $origen, string $name_file, ZipArchive $zip): array|string{
        $errores = new errores();
        $origen = trim($origen);
        if($origen === ''){
            return $errores->error('Error el origen no puede venir vacio',$origen);
        }
        $ruta_origen = $this->ruta_origen($origen);
        if(errores::$error){
            return $errores->error('Error al validar',$ruta_origen);
        }
        $zip->addFile($ruta_origen, $name_file);
        return $ruta_origen;
    }

    /**
     *
     * @param array $archivos
     * @param ZipArchive $zip
     * @return array
     */
    private function agrega_archivos_zip(array $archivos, ZipArchive $zip): array
    {
        $datas = array();
        foreach($archivos as $origen =>$name_file){
            if(is_array($name_file)){
                return $this->error->error('Error al el nombre del archivo no puede ser un array',$name_file);
            }
            $data = $this->agrega_archivo_zip($origen,$name_file,$zip);
            if(errores::$error){
                return $this->error->error('Error al agregar archivo',$data);
            }
            $datas[] = $data;
        }
        return $datas;
    }

    /**
     *
     * @return array|string
     */
    private function crea_folder_temporal(): array|string
    {
        $errores = new errores();
        $carpeta_temporales = PATH_BASE.'archivos/temporales/';
        if(!file_exists($carpeta_temporales) && !mkdir($carpeta_temporales, 0777, true)) {
            return $errores->error('Error ar crear carpeta',$carpeta_temporales);
        }
        chmod($carpeta_temporales, 0777);
        return $carpeta_temporales;
    }

    /**
     *
     * @param string $carpeta_temporales
     * @return string|array
     */
    private function crea_ruta_zip(string $carpeta_temporales): string|array
    {
        $carpeta_temporales = trim($carpeta_temporales);
        if($carpeta_temporales === ''){
            return $this->error->error('Error $carpeta_temporales esta vacia',$carpeta_temporales);
        }
        $name_zip = time().mt_rand(1,9999).'.zip';
        return $carpeta_temporales.$name_zip;
    }

    /**
     *
     * @param string $ruta_destino
     * @param array $archivos
     * @return array|string
     */
    private function genera_zip_archivos(string $ruta_destino, array $archivos): array|string
    {
        $zip = new ZipArchive;
        if($zip->open($ruta_destino,ZipArchive::CREATE) === TRUE){
            $data = $this->agrega_archivos_zip($archivos, $zip);
            if(errores::$error){
                return $this->error->error('Error al agregar archivos',$data);
            }
        }
        $zip->close();
        return $ruta_destino;
    }

    /**
     *
     * Obtiene la ruta de origen con el PATH BASE
     * @param string $origen ruta relativa origen del archivo
     * @return string|array ruta absoluta o error
     * @throws $origen === ''
     * @throws !file_exists($ruta_origen)
     * @example
     *  $ruta_origen = $this->ruta_origen($origen);
     ***/
    private function ruta_origen(string $origen):array|string{
        $errores = new errores();
        if($origen === ''){
            return $errores->error('Error el origen no puede venir vacio',$origen);
        }

        $ruta_origen = PATH_BASE.$origen;
        if(!file_exists($ruta_origen)){
            return $errores->error('Error no existe el archivo',$ruta_origen);
        }
        return $ruta_origen;
    }

    /**
     *
     * @return array|string
     */
    private function genera_ruta_destino(): array|string
    {
        $errores = new errores();
        $carpeta_temporales = $this->crea_folder_temporal();
        if(errores::$error){
            return $errores->error('Error ar crear carpeta',$carpeta_temporales);
        }
        $ruta_destino = $this->crea_ruta_zip($carpeta_temporales);
        if(errores::$error){
            return $errores->error('Error ar crear destino',$ruta_destino);
        }
        return $ruta_destino;
    }

    /**
     *
     * @param string $origen
     * @param string $name_file
     * @return array|string
     */
    private static function comprime_archivo(string $origen, string $name_file): array|string
    {
        $errores = new errores();
        if(!file_exists(PATH_BASE.$origen)){
            return $errores->error('Error el no existe el archivo',PATH_BASE.$origen);
        }

        if($origen === ''){
            return $errores->error('Error el origen no puede venir vacio',$origen);
        }

        $ruta_origen = (new compresor())->ruta_origen($origen);
        if(errores::$error){
            return $errores->error('Error al validar',$ruta_origen);
        }

        $ruta_destino = (new compresor())->genera_ruta_destino();
        if(errores::$error){
            return $errores->error('Error ar crear destino',$ruta_destino);
        }
        $zip = new ZipArchive;
        if($zip->open($ruta_destino,ZipArchive::CREATE) === TRUE){
            $zip->addFile($ruta_origen, $name_file);
            $zip->close();
        }
        else{
            return $errores->error('Error al crear ZIP',$ruta_origen);
        }
        chmod($ruta_destino,0777);
        return $ruta_destino;
    }

    /**
     *
     * @param array $archivos
     * @return array|string
     */
    private static function comprime_archivos(array $archivos): array|string
    {
        $errores = new errores();
        if(count($archivos) === 0){
            return $errores->error('Error el no existen archivos a comprimir',$archivos);
        }
        $ruta_destino = (new compresor())->genera_ruta_destino();
        if(errores::$error){
            return $errores->error('Error ar crear destino',$ruta_destino);
        }
        $data = (new compresor())->genera_zip_archivos($ruta_destino, $archivos);
        if(errores::$error){
            return $errores->error('Error al agregar archivos',$data);
        }
        chmod($ruta_destino,0777);
        return $ruta_destino;
    }

    /**
     *
     * @param string $origen
     * @return array|string
     */
    private function origen(string $origen): array|string
    {
        $origen = trim($origen);
        if(trim($origen) === ''){
            return $this->error->error('Error el origen no puede venir vacio',$origen);
        }

        $origen = str_replace(PATH_BASE, '', $origen);

        $origen = PATH_BASE . $origen;
        if(!file_exists($origen)){
            return $this->error->error('Error el origen no existe',$origen);
        }
        return $origen;
    }

    /**
     *
     * @param string $temporales
     * @param string $origen
     * @return array|string
     * @throws ImagickException
     */
    private function regenera_pages_pdf(string $temporales, string $origen): array|string
    {
        try {
            $pdf = new Mpdf(['tempDir' => $temporales]);
        }
        catch (Throwable $e){
            return $this->error->error('Error al generar obj pdf',$e);
        }
        try {
            $pagecount = $pdf->SetSourceFile($origen);
        }
        catch (Throwable){
            $im = new Imagick();
            $im->pingImage($origen);
            $pagecount =  $im->getNumberImages();
        }
        $refactoriza = (new compresor)->ejecuta_redimencion($origen,$temporales,$pagecount,$pdf);
        if(errores::$error){
            return $this->error->error('Error al refactorizar',$refactoriza);
        }
        return $refactoriza;
    }

    /**
     *
     * @param string $origen
     * @return array|string
     * @throws ImagickException
     */
    public static function comprime_pdf(string $origen): array|string
    {
        $errores = new errores();
        $origen = (new compresor)->origen($origen);
        if(errores::$error){
            return $errores->error('Error al maquetar origen',$origen);
        }
        $temporales = PATH_BASE . "archivos/temporales/";

        $refactoriza = (new compresor)->regenera_pages_pdf($temporales,$origen);
        if($errores::$error){
            return $errores->error('Error al refactorizar',$refactoriza);
        }

        return $refactoriza;

    }

    /**
     *
     * @param string $origen
     * @param string $temporales
     * @param int $n_paginas
     * @param Mpdf $pdf
     * @return array|string
     * @throws ImagickException
     */
    private function ejecuta_redimencion(string $origen, string $temporales, int $n_paginas, Mpdf $pdf): array|string
    {
        $refactoriza = (new compresor)->redimenciona_paginas($origen,$temporales,$n_paginas,$pdf);
        if(errores::$error){
            return $this->error->error('Error al refactorizar',$refactoriza);
        }
        $name_salida = strtolower($temporales.time().mt_rand(100000,999999).".pdf");
        try {
            $pdf->Output($name_salida, 'F');
        } catch (MpdfException $e) {
            return $this->error->error('Error dar salida de pdf',$e);
        }
        chmod($name_salida,0777);
        return $name_salida;
    }

    /**
     *
     * @param string $origen
     * @param string $temporal
     * @param int $n_paginas
     * @param Mpdf $pdf
     * @return bool|array
     * @throws ImagickException
     */
    private function redimenciona_paginas(string $origen, string $temporal, int $n_paginas,Mpdf $pdf): bool|array
    {
        $extension_imagen = 'jpg';
        $calidad = 20;

        $refactoriza = (new compresor)->refactoriza_paginas($n_paginas,$pdf,$extension_imagen,$temporal,
            $origen,$calidad);
        if(errores::$error){
            return $this->error->error('Error al refactorizar',$refactoriza);
        }
        return true;
    }

    /**
     *
     * @param int $pagecount
     * @param Mpdf $pdf
     * @param string $extension_imagen
     * @param string $folder_imagen_base
     * @param string $origen_archivo
     * @param int $calidad
     * @return bool|array
     * @throws ImagickException
     */
    private function refactoriza_paginas(int $pagecount, Mpdf $pdf,string $extension_imagen,
                                        string $folder_imagen_base, string $origen_archivo, int $calidad): bool|array
    {
        for($i = 0; $i<$pagecount; $i++){
            $refactoriza = (new compresor)->refactoriza_pagina($pdf,$i,$extension_imagen,$folder_imagen_base,
                $origen_archivo,$calidad);
            if(errores::$error){
                return $this->error->error('Error al refactorizar',$refactoriza);
            }
        }
        return true;
    }

    /**
     *
     * @param int $i
     * @param string $extension_imagen
     * @param string $folder_imagen_base
     * @return string|array
     */
    private function ruta_temp(int $i, string $extension_imagen, string $folder_imagen_base): string|array
    {
        if($i<0){
            return $this->error->error('Error $i debe ser mayor o igual a 0',$i);
        }
        $extension_imagen = trim($extension_imagen);
        if($extension_imagen === ''){
            return $this->error->error('Error $extension_imagen no puede venir vacia',$extension_imagen);
        }
        $name_img = $i."_".time().mt_rand(100000,999999).mt_rand(100000,999999).mt_rand(100000,999999);
        $name_img .= mt_rand(100,999).mt_rand(10,99).mt_rand(0,9).mt_rand(1000,9999);
        $name_img .= mt_rand(0,9).mt_rand(0,9).mt_rand(0,9).mt_rand(0,9).".$extension_imagen";
        return $folder_imagen_base.$name_img;
    }

    /**
     *
     * @param string $origen_archivo
     * @param int $i
     * @param string $extension_imagen
     * @param int $calidad
     * @return array|string
     * @throws ImagickException
     */
    private function bin_img(string $origen_archivo, int $i, string $extension_imagen, int $calidad): array|string
    {
        /**
         * REFACTORIZAR
         */
        $valida = $this->valida_data_compresion($i, $extension_imagen, $origen_archivo, $calidad);
        if(errores::$error){
            return $this->error->error('Error al validar compresion',$valida);
        }

        $extensiones_validas = array('jpg','jpeg','png');
        if(!in_array($extension_imagen,$extensiones_validas)){
            return $this->error->error('Error la extension es invalida',$extension_imagen);
        }

        
        $img = new Imagick();
        $img->setResolution(220,220);
        $img->setBackgroundColor('white');

        try {
            $img->readImage($origen_archivo . '[' . $i . ']');
        } catch (ImagickException $e) {
            return $this->error->error('Error al leer imagen',$e);
        }
        $img->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
        $img->setImageFormat($extension_imagen);
        $img->setImageCompression(imagick::COMPRESSION_JPEG);
        $img->setImageCompressionQuality($calidad);

        return trim($img->getImageBlob());

    }

    /**
     *
     * @param int $i
     * @param string $extension_imagen
     * @param string $origen_archivo
     * @param int $calidad
     * @return array|bool
     */
    private function valida_data_compresion(int $i, string $extension_imagen, string $origen_archivo, int $calidad): bool|array
    {
        if($i<0){
            return $this->error->error('Error $i debe ser mayor o igual a 0',$i);
        }
        $extension_imagen = trim($extension_imagen);
        if($extension_imagen === ''){
            return $this->error->error('Error $extension_imagen no puede venir vacia',$extension_imagen);
        }
        $origen_archivo = trim($origen_archivo);
        if($origen_archivo === ''){
            return $this->error->error('Error $origen_archivo no puede venir vacia',$origen_archivo);
        }
        if(!file_exists($origen_archivo)){
            return $this->error->error('Error no existe el archivo',$origen_archivo);
        }
        if($calidad<=0){
            return $this->error->error('Error calidad deb ser mayor a 0',$calidad);
        }
        return true;
    }

    /**
     *
     * @param int $i
     * @param string $extension_imagen
     * @param string $folder_imagen_base
     * @param string $origen_archivo
     * @param int $calidad
     * @return array|string
     * @throws ImagickException
     */
    private function img_pdf(int $i, string $extension_imagen,string $folder_imagen_base, string $origen_archivo, int $calidad): array|string
    {

        $valida = $this->valida_data_compresion($i, $extension_imagen, $origen_archivo, 100);
        if(errores::$error){
            return $this->error->error('Error al validar compresion',$valida);
        }

        $ruta_doc = $this->ruta_temp($i,$extension_imagen,$folder_imagen_base);
        if(errores::$error){
            return $this->error->error('Error al generar ruta',$ruta_doc);
        }
        $data = $this->bin_img($origen_archivo,$i,$extension_imagen,$calidad);
        if(errores::$error){
            return $this->error->error('Error generar bin',$data);
        }
        file_put_contents($ruta_doc, $data);
        chmod($ruta_doc,0777);
        return $ruta_doc;
    }

    /**
     *
     * @param Mpdf $pdf
     * @param int $i
     * @param string $extension_imagen
     * @param string $folder_imagen_base
     * @param string $origen_archivo
     * @param int $calidad
     * @return bool|array
     * @throws ImagickException
     */
    private function refactoriza_pagina(Mpdf $pdf, int $i, string $extension_imagen, string $folder_imagen_base,
                                       string $origen_archivo, int $calidad):bool|array{

        $pdf->AddPageByArray(['sheet-size'=>'Legal','mgr'=>0,'mgl'=>0,'mgt'=>0,'mgb'=>0,'mgh'=>0,'mgf'=>0]);
        $ruta_doc = $this->img_pdf($i,$extension_imagen,$folder_imagen_base,$origen_archivo,$calidad);
        if(errores::$error){
            return $this->error->error('Error generar imagen',$ruta_doc);
        }
        $pdf->Image($ruta_doc,0,0,215.7,354.5);
        unlink($ruta_doc);
        return true;
    }

    /**
     *
     * @param string $origen
     * @param string $name_file_sin_extension
     * @param string $extension
     * @param bool $header
     * @return array|string
     */
    public static function descarga_zip(string $origen, string $name_file_sin_extension, string $extension, bool $header=true): array|string
    {

        $errores = new errores();

        if($origen === ''){
            return $errores->error('Error el origen no puede venir vacio',$origen);
        }
        if($name_file_sin_extension === ''){
            return $errores->error('Error el $name_file_sin_extension no puede venir vacio',$name_file_sin_extension);
        }
        if($extension === ''){
            return $errores->error('Error el $extension no puede venir vacio',$extension);
        }
        
        $origen_normalizado = str_replace(PATH_BASE, '', $origen);

        if(!file_exists(PATH_BASE.$origen_normalizado)){
            return $errores->error('Error el no existe el archivo',PATH_BASE.$origen);
        }

        $name_file = $name_file_sin_extension.'.'.$extension;
        $name_file_zip = $name_file_sin_extension.'.zip';
        $zip = compresor::comprime_archivo($origen_normalizado,$name_file);
        if(errores::$error){
            return $errores->error('Error al comprimir archivo',$zip);
        }
        if($header) {
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=$name_file_zip");
            header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: binary");
            readfile($zip);
            unlink($zip);
            exit;
        }
        $content = file_get_contents($zip);
        unlink($zip);
        return $content;
    }

    /**
     *
     * @param string $origen
     * @param string $name_file_sin_extension
     * @param bool $descarga
     * @param bool $guarda
     * @param string $destino
     * @return array|string
     * @throws ImagickException
     */
    public static function descarga_pdf_comprimido(string $origen, string $name_file_sin_extension,
                                                   bool $descarga = false, bool $guarda = true,
                                                   string $destino = ''): array|string
    {
        if(!file_exists($destino)) {
            $documento = compresor::comprime_pdf($origen);
            if (errores::$error) {
                return (new errores())->error('Error al comprimir archivo', $documento);
            }
            if ($guarda) {
                copy($documento, $destino);
                unlink($documento);
                chmod($destino,0777);
            }
       }
        if($descarga) {
            $name_file_doc = $name_file_sin_extension.'.pdf';
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=$name_file_doc");
            header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: binary");
            readfile($destino);
            exit;
        }
        return $destino;
    }

    /**
     *
     * @param array $archivos
     * @param string $name_zip
     * @return array
     */
    public static function descarga_zip_multiple(array $archivos, string $name_zip): array
    {
        $errores = new errores();
        $name_file_zip = $name_zip.'.zip';
        $zip = compresor::comprime_archivos($archivos);
        if(errores::$error){
            return $errores->error('Error al obtener zip',$zip);
        }

        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$name_file_zip");
        header("Content-Type: application/zip");
        header("Content-Transfer-Encoding: binary");
        readfile($zip);
        unlink($zip);
        exit;
    }



}
