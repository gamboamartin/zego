<?php
namespace gamboamartin\test;

use config\database;
use mysqli;
use PDO;
use PHPUnit\Framework\TestCase;



class test extends TestCase{
    public  PDO|mysqli $link;

    public function __construct(?string $name = null, array $data = [], string $dataName = '', string $tipo_conexion = 'PDO')
    {
        parent::__construct(name: $name,data:  $data,dataName:  $dataName);

        $db = new database();
        if($tipo_conexion === 'PDO') {

            $link = new PDO("mysql:host=$db->db_host;dbname=$db->db_name", $db->db_user, $db->db_password);

            $link->query("SET NAMES 'utf8'");
            $sql = "SET sql_mode = '';";
            $link->query($sql);
            $consulta = 'USE ' . $db->db_name;
            $link->query($consulta);

            $this->link = $link;
        }
        if($tipo_conexion === 'MYSQLI'){

            $this->link = mysqli_connect($db->db_host, $db->db_user, $db->db_password);
            mysqli_set_charset($this->link, 'utf8');
            $sql = "SET sql_mode = '';";
            $this->link->query($sql);
            $consulta = 'USE ' . $db->db_name;
            $this->link->query($consulta);
        }


        if(!defined('UT')) {
            define('UT', true);
        }

    }


}
