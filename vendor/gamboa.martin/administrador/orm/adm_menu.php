<?php
namespace gamboamartin\administrador\models;

use base\orm\_defaults;
use base\orm\_modelo_parent;
use base\orm\_modelo_parent_sin_codigo;
use config\generales;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class adm_menu extends _modelo_parent_sin_codigo {
    public function __construct(PDO $link, array $childrens = array()){
        $tabla = 'adm_menu';
        $columnas = array($tabla=>false);
        $campos_obligatorios = array('etiqueta_label');
        $childrens['adm_seccion'] ="gamboamartin\administrador\models";

        $columnas_extra['adm_menu_n_secciones'] = /** @lang sql */
            "(SELECT COUNT(*) FROM adm_seccion WHERE adm_seccion.adm_menu_id = adm_menu.id)";

        parent::__construct(link: $link,tabla:  $tabla,campos_obligatorios: $campos_obligatorios,
            columnas: $columnas, columnas_extra: $columnas_extra, childrens: $childrens);
        $this->NAMESPACE = __NAMESPACE__;

        $this->etiqueta = 'Menu';


    }

    private function adm_menus_out(array $adm_menus){
        $adm_menus_out = array();
        foreach ($adm_menus as $adm_menu){
            $adm_menus_out = $this->integra_secciones_existentes(adm_menu: $adm_menu,adm_menus_out:  $adm_menus_out);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar menus',data:  $adm_menus_out);
            }
        }
        return $adm_menus_out;
    }

    private function ajusta_menus(array $adm_menu, array $adm_menus_out, array $adm_seccion){
        foreach ($adm_menus_out as $key=>$adm_menu_aut){
            $adm_menus_out = $this->integra_menu_existente(adm_menu: $adm_menu,
                adm_menu_aut:  $adm_menu_aut,adm_menus_out:  $adm_menus_out,adm_seccion:  $adm_seccion,
                key: $key);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar menus',data:  $adm_menus_out);
            }
        }
        return $adm_menus_out;
    }

    public function alta_bd(array $keys_integra_ds = array('descripcion')): array|stdClass
    {
        if(!isset($this->registro['etiqueta_label'])){
            $etiqueta_label = $this->registro['descripcion'];
            $etiqueta_label = str_replace('_', ' ', $etiqueta_label);
            $etiqueta_label = ucwords($etiqueta_label);
            $this->registro['etiqueta_label'] = $etiqueta_label;
        }
        if(!isset($this->registro['icono'])){
            $icono = 'SI';
            $this->registro['icono'] = $icono;
        }
        if(!isset($this->registro['titulo'])){
            $titulo = $this->registro['descripcion'];
            $titulo = str_replace('_', ' ', $titulo);
            $titulo = ucwords('_', $titulo);
            $this->registro['titulo'] = $titulo;
        }


        $r_alta_bd = parent::alta_bd(keys_integra_ds: $keys_integra_ds); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta adm_menu',data:  $r_alta_bd);
        }
        return $r_alta_bd;
    }

    private function existe_adm_menu_out(array $adm_menu, array $adm_menus_out): bool
    {
        $existe_adm_menu_out = false;
        foreach ($adm_menus_out as $adm_menu_aut){
            if((int)$adm_menu_aut['adm_menu_id'] === (int)$adm_menu['adm_menu_id']){
                $existe_adm_menu_out = true;
                break;
            }
        }
        return $existe_adm_menu_out;
    }

    private function existe_en_sistema(array $adm_seccion){
        $sistema_en_ejecucion = (new generales())->sistema;
        $adm_seccion_id = $adm_seccion['adm_seccion_id'];
        $filtro['adm_seccion.id'] = $adm_seccion_id;
        $filtro['adm_sistema.descripcion'] = $sistema_en_ejecucion;
        $existe = (new adm_seccion_pertenece(link: $this->link))->existe(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar  si existe',data:  $existe);
        }
        return $existe;
    }

    private function genera_adm_menu(array $adm_menu, array $adm_menus_out, array $adm_seccion){
        $adm_menus_out = $this->init_adm_menu_out_existe(adm_menu: $adm_menu,adm_menus_out:  $adm_menus_out);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar menus',data:  $adm_menus_out);
        }

        $adm_menus_out = $this->ajusta_menus(adm_menu: $adm_menu,adm_menus_out:  $adm_menus_out,
            adm_seccion:  $adm_seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar menus',data:  $adm_menus_out);
        }
        return $adm_menus_out;
    }

    private function inicializa_adm_menu_out(array $adm_menu, array$adm_menus_out, bool $existe_adm_menu_out){
        if(!$existe_adm_menu_out){
            $adm_menus_out = $this->init_adm_menus_out(adm_menu: $adm_menu,adm_menus_out:  $adm_menus_out);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar menus',data:  $adm_menus_out);
            }
        }
        return $adm_menus_out;
    }

    private function init_adm_menus_out(array $adm_menu, array $adm_menus_out): array
    {
        $adm_menu_puro = $adm_menu;
        unset($adm_menu_puro['adm_secciones']);
        $adm_menus_out[] = $adm_menu_puro;
        return $adm_menus_out;
    }

    private function init_adm_menu_out_existe(array $adm_menu, array $adm_menus_out){
        $existe_adm_menu_out = $this->existe_adm_menu_out(adm_menu: $adm_menu,adm_menus_out:  $adm_menus_out);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar si existe',data:  $existe_adm_menu_out);
        }

        $adm_menus_out = $this->inicializa_adm_menu_out(adm_menu: $adm_menu,adm_menus_out:  $adm_menus_out,
            existe_adm_menu_out: $existe_adm_menu_out);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar menus',data:  $adm_menus_out);
        }
        return $adm_menus_out;
    }

    private function integra_adm_menu_out(array $adm_menus_out, array $adm_seccion, int $key): array
    {
        $adm_menus_out[$key]['adm_secciones'][] = $adm_seccion;
        return $adm_menus_out;
    }

    private function integra_menu_existente(array $adm_menu, array $adm_menu_aut, array $adm_menus_out, array $adm_seccion, int $key){
        if((int)$adm_menu_aut['adm_menu_id'] === (int)$adm_menu['adm_menu_id']){
            $adm_menus_out = $this->integra_adm_menu_out(
                adm_menus_out: $adm_menus_out, adm_seccion: $adm_seccion,key:  $key);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar menus',data:  $adm_menus_out);
            }
        }
        return $adm_menus_out;
    }

    private function integra_seccion_existente(array $adm_menu, array $adm_menus_out, array $adm_seccion){
        $existe = $this->existe_en_sistema(adm_seccion: $adm_seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar  si existe',data:  $existe);
        }
        if($existe){
            $adm_menus_out = $this->genera_adm_menu(adm_menu: $adm_menu, adm_menus_out: $adm_menus_out,adm_seccion:  $adm_seccion);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar menus',data:  $adm_menus_out);
            }

        }
        return $adm_menus_out;
    }

    private function integra_secciones_existentes(array $adm_menu, array $adm_menus_out){
        $secciones = $adm_menu['adm_secciones'];

        foreach ($secciones as $adm_seccion){
            $adm_menus_out = $this->integra_seccion_existente(adm_menu: $adm_menu, adm_menus_out: $adm_menus_out,adm_seccion:  $adm_seccion);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar menus',data:  $adm_menus_out);
            }
        }
        return $adm_menus_out;
    }

    public function menus_visibles_permitidos(){

        $menus = (new _base_accion())->menus_visibles_permitidos(link:$this->link, table: $this->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener menus',data:  $menus);
        }


        return $menus;

    }

    public function menus_visibles_permitidos_full(){

        $adm_menus = (new _base_accion())->menus_visibles_permitidos(link:$this->link, table: $this->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener menus',data:  $adm_menus);
        }

        $adm_menus = (new _base_accion())->asigna_secciones_a_menu(adm_menus: $adm_menus,link:  $this->link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener menus',data:  $adm_menus);
        }

        $adm_menus_out = $this->adm_menus_out(adm_menus: $adm_menus);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar menus',data:  $adm_menus_out);
        }


        return $adm_menus_out;

    }

    /**
     * Obtiene las secciones de un menu
     * @param int $adm_menu_id Menu identificador
     * @return array
     * @version 0.545.51
     */
    public function secciones(int $adm_menu_id): array
    {
        if($adm_menu_id <= 0){
            return $this->error->error(mensaje: 'Error adm_menu_id debe ser mayor a 0',data:  $adm_menu_id);
        }
        $filtro['adm_menu.id'] = $adm_menu_id;
        $r_adm_seccion = (new adm_seccion($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener secciones',data:  $r_adm_seccion);
        }
        return $r_adm_seccion->registros;
    }


}