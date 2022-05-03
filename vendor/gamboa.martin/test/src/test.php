<?php
namespace gamboamartin\test;

use config\database;
use PDO;
use PHPUnit\Framework\TestCase;



class test extends TestCase{
    public  PDO $link;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $db = new database();

        $link = new PDO("mysql:host=$db->db_host;dbname=$db->db_name", $db->db_user, $db->db_password);

        $link->query("SET NAMES 'utf8'");
        $sql = "SET sql_mode = '';";
        $link->query($sql);
        $consulta = 'USE '.$db->db_name;
        $link->query($consulta);

        $this->link = $link;


        if(!defined('UT')) {
            define('UT', true);
        }

    }


}
