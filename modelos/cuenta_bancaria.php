<?php
namespace models;
use gamboamartin\errores\errores;

class cuenta_bancaria extends modelos {
    public function __construct($link)
    {
        $this->error = new errores();
        parent::__construct($link);
    }
}
