<?php
namespace models;
use gamboamartin\errores\errores;

class uso_cfdi extends modelos {

    public function get_uso_cfdi(int $uso_cfdi_id): array
    {
        $uso_cfdi = $this->registro(id: $uso_cfdi_id, tabla: 'uso_cfdi');
        if(errores::$error){
            return $this->error->error('Error al al obtener uso_cfdi', $uso_cfdi);
        }
        return $uso_cfdi;
    }
}
