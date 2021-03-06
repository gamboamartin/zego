<?php
namespace models;
use gamboamartin\errores\errores;

class regimen_fiscal extends modelos {
    /**
     * ERROR UNIT
     * @param int $regimen_fiscal_id
     * @return array
     */
    public function get_regimen_fiscal(int $regimen_fiscal_id): array
    {
        $regimen_fiscal = $this->registro(id: $regimen_fiscal_id, tabla: 'regimen_fiscal');
        if(errores::$error){
            return $this->error->error('Error al al obtener regimen_fiscal', $regimen_fiscal);
        }
        return $regimen_fiscal;
    }

}
