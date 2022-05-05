<?php
namespace models;
use base\consultas_base;

class modelo_sobrecargado extends modelos {
    public function obten_registros($tabla, $sql = ''): array
    {
        $consulta_base = $this->genera_consulta_base($tabla);
        $limit = ' LIMIT 50 ';
        $consulta_base .= " $sql " . $limit;
        return $this->ejecuta_consulta($consulta_base);
    }

    public function filtra_campos_base($valor, $tabla): array
    {
        $valor = addslashes($valor);
        $consultas_base = new consultas_base();
        $where = $consultas_base->genera_filtro_base($tabla, $valor);
        $consulta_base = $this->genera_consulta_base($tabla);
        $consulta = $consulta_base.$where;

        $limit = ' LIMIT 100 ';
        $consulta .= $limit;
        return $this->ejecuta_consulta($consulta);
    }

}