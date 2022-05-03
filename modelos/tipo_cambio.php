<?php
namespace models;
class tipo_cambio extends modelos {
    public function tipo_cambio_por_moneda($moneda_id){
        $sql = new consultas_base();
        $consulta = $sql->genera_consulta_base('tipo_cambio');

        $where = " WHERE tipo_cambio.moneda_id = $moneda_id ";

        $consulta = $consulta.$where;

        $order = " ORDER BY tipo_cambio.fecha DESC LIMIT 20";

        $consulta = $consulta.$order;

        $resultado = $this->ejecuta_consulta($consulta);

        return $resultado;
    }

}
