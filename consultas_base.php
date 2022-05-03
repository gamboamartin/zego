<?php
class consultas_base{
    public $link;


    public $accion_columnas = array('seccion_menu'=>'accion', 'menu'=>'seccion_menu',
        'accion'=>false);
    public $anticipo_columnas = array('anticipo'=>false, 'cliente'=>'anticipo','forma_pago'=>'anticipo',
        'moneda'=>'anticipo','metodo_pago'=>'anticipo');

    public $banco_columnas = array('banco'=>false);

    public $factura_columnas = array('factura'=>false,'cliente'=>'factura','municipio'=>'cliente',
        'anticipo'=>'factura','tipo_relacion'=>'factura');

    public $factura_relacionada_columnas = array('factura_relacionada'=>false,'factura'=>'factura_relacionada',
        array(
            'tabla_base'=>'factura','tabla_renombrada'=>'factura_rel','tabla_enlace'=>'factura_relacionada',
            'obligatorio'=>false),
        );

    public $grupo_columnas = array('grupo'=>false);

    public $moneda_columnas = array('moneda'=>false);

    public $nota_credito_columnas = array('nota_credito'=>false, 'cliente'=>'nota_credito','forma_pago'=>'nota_credito',
        'moneda'=>'nota_credito','factura'=>'nota_credito','anticipo'=>'nota_credito');

    public $pais_columnas = array('pais'=>false);

    public $pago_cliente_columnas = array('pago_cliente'=>false,'cliente'=>'pago_cliente');
    public $pago_cliente_factura_columnas = array('pago_cliente_factura'=>false,'factura'=>'pago_cliente_factura',
        'pago_cliente'=>'pago_cliente_factura');

    public $pattern_cp = "[0-9]{5}";

    public $pattern_rfc = "([A-Za-z]{3}|[A-Za-z]{4})[0-9][0-9](0[1-9]|1[0-2])((0[1-9])|([1-2][0-9])|3[0-1])[A-Za-z0-9]{3}";
    public $tipo_cambio_columnas = array('tipo_cambio'=>false, 'moneda'=>'tipo_cambio');




    public $seccion_menu_columnas = array('seccion_menu'=>false, 'menu'=>'seccion_menu');
    public $estado_columnas = array('estado'=>false, 'pais'=>'estado');
    public $municipio_columnas = array('municipio'=>false,'estado'=>'municipio', 'pais'=>'estado');
    public $tipo_insumo_columnas = array('tipo_insumo'=>false,'grupo_insumo'=>'tipo_insumo');
    public $menu_columnas = array('menu'=>false);
    public $tipo_cliente_columnas = array('tipo_cliente'=>false);
    public $cliente_columnas = array('cliente'=>false, 'municipio'=>'cliente',
        'estado'=>'municipio','pais'=>'estado','uso_cfdi'=>'cliente',
        'moneda'=>'cliente','forma_pago'=>'cliente','metodo_pago'=>'cliente');
    public $grupo_insumo_columnas = array('grupo_insumo'=>false);
    public $usuario_columnas = array('usuario'=>false,'grupo'=>'usuario');
    public $accion_grupo_columnas = array('accion_grupo'=>false,'accion'=>'accion_grupo',
        'grupo'=>'accion_grupo','seccion_menu'=>'accion','menu'=>'seccion_menu');
    public $aduana_columnas = array('aduana'=>false);
    public $unidad_columnas = array('unidad'=>false);
    public $cuenta_bancaria_empresa_columnas = array('cuenta_bancaria_empresa'=>false,
                'banco'=>'cuenta_bancaria_empresa','moneda'=>'cuenta_bancaria_empresa');
    public $producto_sat_columnas = array('producto_sat'=>false);
    public $forma_pago_columnas = array('forma_pago'=>false);
    public $tipo_comprobante_columnas = array('tipo_comprobante'=>false);
    public $impuesto_columnas = array('impuesto'=>false);
    public $metodo_pago_columnas = array('metodo_pago'=>false);
    public $tipo_factor_columnas = array('tipo_factor'=>false);
    public $uso_cfdi_columnas = array('uso_cfdi'=>false);
    public $tipo_relacion_columnas = array('tipo_relacion'=>false);
    public $regimen_fiscal_columnas = array('regimen_fiscal'=>false);
    public $patente_aduanal_columnas = array('patente_aduanal'=>false);
    public $partida_informe_gasto_columnas = array('partida_informe_gasto'=>false);
    public $numero_pedimento_aduana_columnas = array('numero_pedimento_aduana'=>false);


    public $partida_factura_columnas = array(
        'partida_factura'=>false,'insumo'=>'partida_factura','factura'=>'partida_factura');
    public $insumo_columnas = array('insumo'=>false,'tipo_insumo'=>'insumo',
        'grupo_insumo'=>'tipo_insumo','producto_sat'=>'insumo','unidad'=>'insumo',
        'impuesto'=>'insumo','tipo_factor'=>'insumo',
        array(
            'tabla_base'=>'impuesto','tabla_renombrada'=>'impuesto_retenido','tabla_enlace'=>'insumo','obligatorio'=>false),
        array(
            'tabla_base'=>'tipo_factor','tabla_renombrada'=>'tipo_factor_retenido','tabla_enlace'=>'insumo','obligatorio'=>false));
    public $cuenta_bancaria_columnas = array('cuenta_bancaria'=>false,
        'cliente'=>'cuenta_bancaria', 'banco'=>'cuenta_bancaria','moneda'=>'cuenta_bancaria');
    public $elemento_lista_columnas = array('elemento_lista'=>false,'seccion_menu'=>'elemento_lista',
        'menu'=>'seccion_menu');


    public $estructura_bd;
    public function __construct(){
        $this->estructura_bd['anticipo']['columnas_select'] = $this->anticipo_columnas;
        $this->estructura_bd['nota_credito']['columnas_select'] = $this->nota_credito_columnas;
        $this->estructura_bd['pago_cliente']['columnas_select'] = $this->pago_cliente_columnas;
        $this->estructura_bd['partida_informe_gasto']['columnas_select'] = $this->partida_informe_gasto_columnas;
        $this->estructura_bd['pago_cliente_factura']['columnas_select'] = $this->pago_cliente_factura_columnas;


        $this->estructura_bd['accion']['columnas_select'] = $this->accion_columnas;
        $this->estructura_bd['accion']['where_filtro_or'] = true;
        $this->estructura_bd['accion']['genera_or_descripcion'] = array('seccion_menu','menu');
        $this->estructura_bd['accion']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'seccion_menu_id'=>array(
                'tipo'=>'select','cols'=>6,'requerido'=>'required',
                'tabla_foranea'=>'seccion_menu','vista'=>array('alta','modifica')),
            'icono'=>array('tipo'=>'text','cols'=>12,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>4,'vista'=>array('alta','modifica')),
            'visible'=>array('tipo'=>'checkbox','cols'=>4,'vista'=>array('alta','modifica')),
            'inicio'=>array('tipo'=>'checkbox','cols'=>4,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta','modifica')));


        $this->estructura_bd['cuenta_bancaria_empresa']['columnas_select'] = $this->cuenta_bancaria_empresa_columnas;
        $this->estructura_bd['cuenta_bancaria_empresa']['where_filtro_or'] = true;
        $this->estructura_bd['cuenta_bancaria_empresa']['genera_or_descripcion'] = array('banco');
        $this->estructura_bd['cuenta_bancaria_empresa']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>4,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'cuenta'=>array('tipo'=>'text','cols'=>4,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'clabe'=>array('tipo'=>'text','cols'=>4,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'banco_id'=>array(
                'tipo'=>'select','cols'=>6,'requerido'=>'required',
                'tabla_foranea'=>'banco','vista'=>array('alta','modifica')),
            'moneda_id'=>array(
                'tipo'=>'select','cols'=>6,'requerido'=>'required',
                'tabla_foranea'=>'moneda','vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>6,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta','modifica')));


        $this->estructura_bd['factura']['columnas_select'] = $this->factura_columnas;
        $this->estructura_bd['factura']['where_filtro_or'] = true;
        $this->estructura_bd['factura']['genera_or_descripcion'] = array('seccion_menu','menu');
        $this->estructura_bd['factura']['campos'] = array(
            'metodo_pago_id'=>array(
                'tipo'=>'select','cols'=>4,'requerido'=>'required',
                'tabla_foranea'=>'metodo_pago','vista'=>array('alta','modifica')),
            'tipo_comprobante_id'=>array(
                'tipo'=>'select','cols'=>4,'requerido'=>'required',
                'tabla_foranea'=>'tipo_comprobante','vista'=>array('alta','modifica')),
            'moneda_id'=>array(
                'tipo'=>'select','cols'=>4,'requerido'=>'required',
                'tabla_foranea'=>'moneda','vista'=>array('alta','modifica')),
            'condiciones_pago'=>array('tipo'=>'text','cols'=>12,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'forma_pago_id'=>array(
                'tipo'=>'select','cols'=>4,'requerido'=>'required',
                'tabla_foranea'=>'forma_pago','vista'=>array('alta','modifica')),
            'cliente_id'=>array(
                'tipo'=>'select','cols'=>4,'requerido'=>'required',
                'tabla_foranea'=>'cliente','vista'=>array('alta','modifica')),
            'uso_cfdi_id'=>array(
                'tipo'=>'select','cols'=>4,'requerido'=>'required',
                'tabla_foranea'=>'uso_cfdi','vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>12,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta','modifica')));

        $this->estructura_bd['partida_factura']['columnas_select'] = $this->partida_factura_columnas;
        $this->estructura_bd['partida_factura']['where_filtro_or'] = true;
        $this->estructura_bd['partida_factura']['genera_or_descripcion'] = array('seccion_menu','menu');
        $this->estructura_bd['partida_factura']['campos'] = array(
            'factura_id'=>array(
                'tipo'=>'select','cols'=>4,'requerido'=>'required',
                'tabla_foranea'=>'factura','vista'=>array('alta','modifica')),
            'tipo_comprobante_id'=>array(
                'tipo'=>'select','cols'=>4,'requerido'=>'required',
                'tabla_foranea'=>'tipo_comprobante','vista'=>array('alta','modifica')),
            'moneda_id'=>array(
                'tipo'=>'select','cols'=>4,'requerido'=>'required',
                'tabla_foranea'=>'moneda','vista'=>array('alta','modifica')),
            'condiciones_pago'=>array('tipo'=>'text','cols'=>12,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'forma_pago_id'=>array(
                'tipo'=>'select','cols'=>4,'requerido'=>'required',
                'tabla_foranea'=>'forma_pago','vista'=>array('alta','modifica')),
            'cliente_id'=>array(
                'tipo'=>'select','cols'=>4,'requerido'=>'required',
                'tabla_foranea'=>'cliente','vista'=>array('alta','modifica')),
            'uso_cfdi_id'=>array(
                'tipo'=>'select','cols'=>4,'requerido'=>'required',
                'tabla_foranea'=>'uso_cfdi','vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>12,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta','modifica')));


        $this->estructura_bd['cuenta_bancaria']['columnas_select'] = $this->cuenta_bancaria_columnas;
        $this->estructura_bd['cuenta_bancaria']['where_filtro_or'] = true;
        $this->estructura_bd['cuenta_bancaria']['genera_or_descripcion'] = array('banco','moneda');

        $this->estructura_bd['cuenta_bancaria']['genera_filtro_especial'] = array(
            'cliente'=>array('razon_social','rfc'),'moneda'=>array('codigo'));


        $this->estructura_bd['cuenta_bancaria']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'cuenta'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'clabe'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'cliente_id'=>array(
                'tipo'=>'select','cols'=>6,'requerido'=>'required',
                'tabla_foranea'=>'cliente','vista'=>array('alta','modifica')),
            'banco_id'=>array(
                'tipo'=>'select','cols'=>6,'requerido'=>'required',
                'tabla_foranea'=>'banco','vista'=>array('alta','modifica')),
            'moneda_id'=>array(
                'tipo'=>'select','cols'=>6,'requerido'=>'required',
                'tabla_foranea'=>'moneda','vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>6,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta','modifica')));


        $this->estructura_bd['insumo']['columnas_select'] = $this->insumo_columnas;
        $this->estructura_bd['insumo']['where_filtro_or'] = true;
        $this->estructura_bd['insumo']['genera_or_descripcion'] = array('tipo_insumo','grupo_insumo');
        $this->estructura_bd['insumo']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'unidad_id'=>array(
                'tipo'=>'select_columnas','columnas'=>array('unidad_codigo','unidad_descripcion'),'cols'=>6,'requerido'=>'required',
                'tabla_foranea'=>'unidad','vista'=>array('alta','modifica')),
            'producto_sat_id'=>array(
                'tipo'=>'autocomplete','columnas'=>array('producto_sat_codigo','producto_sat_descripcion'),'cols'=>12,'requerido'=>'required',
                'tabla_foranea'=>'producto_sat','vista'=>array('alta','modifica')),
            'grupo_insumo_id'=>array(
                'tipo'=>'select','cols'=>6,'requerido'=>'required',
                'tabla_foranea'=>'grupo_insumo','vista'=>array('alta','modifica'),'externa'=>true),
            'tipo_insumo_id'=>array(
                'tipo'=>'select','cols'=>6,'requerido'=>false,
                'tabla_foranea'=>'tipo_insumo','vista'=>array('alta','modifica')),
            'impuesto_id'=>array(
                'tipo'=>'select','cols'=>12,'requerido'=>false,
                'tabla_foranea'=>'impuesto','vista'=>array('alta','modifica'),'externa'=>true),
            'tipo_factor_id'=>array(
                'tipo'=>'select','cols'=>6,'requerido'=>false,
                'tabla_foranea'=>'tipo_factor','vista'=>array('alta','modifica'),'externa'=>true),
            'factor'=>array('tipo'=>'text','cols'=>6,'requerido'=>false,
                'vista'=>array('alta','modifica')),
            'impuesto_retenido_id'=>array(
                'tipo'=>'select_personalizado','cols'=>12,'requerido'=>'',
                'tabla_foranea'=>'impuesto','vista'=>array('alta','modifica'),'externa'=>true,
                'etiqueta'=>'Impuesto Retenido', 'nombre_contenedor'=>'impuesto_retenido',
                'nombre_campo'=>'impuesto_retenido_id'),
            'tipo_factor_retenido_id'=>array(
                'tipo'=>'select_personalizado','cols'=>6,'requerido'=>'',
                'tabla_foranea'=>'tipo_factor','vista'=>array('alta','modifica'),'externa'=>true,
                'etiqueta'=>'Tipo Factor de Retención',
                'nombre_contenedor'=>'tipo_factor_retenido','nombre_campo'=>'tipo_factor_retenido_id'),
            'factor_retenido'=>array('tipo'=>'text','cols'=>6,'requerido'=>'',
                'vista'=>array('alta','modifica')),
            'ieps_factor'=>array('tipo'=>'text','cols'=>12,'requerido'=>false,
                'vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>12,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta','modifica')));



        $this->estructura_bd['impuesto']['columnas_select'] = $this->impuesto_columnas;
        $this->estructura_bd['impuesto']['where_filtro_or'] = true;
        $this->estructura_bd['impuesto']['genera_or_like'] = array('codigo');
        $this->estructura_bd['impuesto']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'codigo'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>6,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta','modifica')));


        $this->estructura_bd['elemento_lista']['columnas_select'] = $this->elemento_lista_columnas;
        $this->estructura_bd['elemento_lista']['where_filtro_or'] = true;
        $this->estructura_bd['elemento_lista']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'seccion_menu_id'=>array(
                'tipo'=>'select','cols'=>6,'requerido'=>'required',
                'tabla_foranea'=>'seccion_menu','vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>6,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta','modifica')));


        $this->estructura_bd['uso_cfdi']['columnas_select'] = $this->uso_cfdi_columnas;
        $this->estructura_bd['uso_cfdi']['where_filtro_or'] = true;
        $this->estructura_bd['uso_cfdi']['genera_or_like'] = array('codigo');
        $this->estructura_bd['uso_cfdi']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'codigo'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>6,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta','modifica')));

        $this->estructura_bd['tipo_relacion']['columnas_select'] = $this->tipo_relacion_columnas;
        $this->estructura_bd['tipo_relacion']['where_filtro_or'] = true;
        $this->estructura_bd['tipo_relacion']['genera_or_like'] = array('codigo');
        $this->estructura_bd['tipo_relacion']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'codigo'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>6,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta','modifica')));


        $this->estructura_bd['tipo_comprobante']['columnas_select'] = $this->tipo_comprobante_columnas;
        $this->estructura_bd['tipo_comprobante']['where_filtro_or'] = true;
        $this->estructura_bd['tipo_comprobante']['genera_or_like'] = array('codigo');
        $this->estructura_bd['tipo_comprobante']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'codigo'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>6,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta','modifica')));


        $this->estructura_bd['regimen_fiscal']['columnas_select'] = $this->regimen_fiscal_columnas;
        $this->estructura_bd['regimen_fiscal']['where_filtro_or'] = true;
        $this->estructura_bd['regimen_fiscal']['genera_or_like'] = array('codigo');
        $this->estructura_bd['regimen_fiscal']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'codigo'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>6,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta','modifica')));



        $this->estructura_bd['tipo_factor']['columnas_select'] = $this->tipo_factor_columnas;
        $this->estructura_bd['tipo_factor']['where_filtro_or'] = true;
        $this->estructura_bd['tipo_factor']['genera_or_like'] = array('codigo');
        $this->estructura_bd['tipo_factor']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'codigo'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>6,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta','modifica')));

        $this->estructura_bd['patente_aduanal']['columnas_select'] = $this->patente_aduanal_columnas;
        $this->estructura_bd['patente_aduanal']['where_filtro_or'] = true;
        $this->estructura_bd['patente_aduanal']['genera_or_like'] = array('codigo');
        $this->estructura_bd['patente_aduanal']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'codigo'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>6,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta','modifica')));


        $this->estructura_bd['numero_pedimento_aduana']['columnas_select'] = $this->numero_pedimento_aduana_columnas;
        $this->estructura_bd['numero_pedimento_aduana']['where_filtro_or'] = true;
        $this->estructura_bd['numero_pedimento_aduana']['genera_or_like'] = array('codigo');
        $this->estructura_bd['numero_pedimento_aduana']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'codigo'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>6,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta','modifica')));




        $this->estructura_bd['unidad']['columnas_select'] = $this->unidad_columnas;
        $this->estructura_bd['unidad']['where_filtro_or'] = true;
        $this->estructura_bd['unidad']['genera_or_like'] = array('codigo');
        $this->estructura_bd['unidad']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'codigo'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>6,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta','modifica')));


        $this->estructura_bd['metodo_pago']['columnas_select'] = $this->metodo_pago_columnas;
        $this->estructura_bd['metodo_pago']['where_filtro_or'] = true;
        $this->estructura_bd['metodo_pago']['genera_or_like'] = array('codigo');
        $this->estructura_bd['metodo_pago']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'codigo'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>6,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta','modifica')));

        $this->estructura_bd['producto_sat']['columnas_select'] = $this->producto_sat_columnas;
        $this->estructura_bd['producto_sat']['where_filtro_or'] = true;
        $this->estructura_bd['producto_sat']['genera_or_like'] = array('codigo');
        $this->estructura_bd['producto_sat']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'codigo'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>6,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta','modifica')));


        $this->estructura_bd['tipo_cambio']['columnas_select'] = $this->tipo_cambio_columnas;
        $this->estructura_bd['tipo_cambio']['where_filtro_or'] = true;
        $this->estructura_bd['tipo_cambio']['genera_or_descripcion'] = array('moneda');
        $this->estructura_bd['tipo_cambio']['genera_or_like'] = array('fecha');
        $this->estructura_bd['tipo_cambio']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta')),
            'fecha'=>array('tipo'=>'fecha','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta')),
            'moneda_id'=>array(
                'tipo'=>'select','cols'=>6,'requerido'=>'required',
                'tabla_foranea'=>'moneda','vista'=>array('alta','modifica')),
            'monto'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta')),
            'status'=>array('tipo'=>'checkbox','cols'=>12,'vista'=>array('alta')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta')));


        $this->estructura_bd['grupo']['columnas_select'] = $this->grupo_columnas;
        $this->estructura_bd['grupo']['where_filtro_or'] = true;
        $this->estructura_bd['grupo']['campos'] = array(
            'descripcion'=>array(
                'tipo'=>'text','cols'=>12,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>6,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,
                'vista'=>array('alta','modifica')));

        $this->estructura_bd['banco']['columnas_select'] = $this->banco_columnas;
        $this->estructura_bd['banco']['where_filtro_or'] = true;
        $this->estructura_bd['banco']['genera_or_like'] = array('rfc','razon_social');
        $this->estructura_bd['banco']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'rfc'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'pattern'=>$this->pattern_rfc,'vista'=>array('alta','modifica')),
            'razon_social'=>array(
                'tipo'=>'text','cols'=>12,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>12,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,
                'vista'=>array('alta','modifica')));


        $this->estructura_bd['pais']['columnas_select'] = $this->pais_columnas;
        $this->estructura_bd['pais']['where_filtro_or'] = true;
        $this->estructura_bd['pais']['genera_or_like'] = array('codigo');
        $this->estructura_bd['pais']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'codigo'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>6,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta','modifica')));


        $this->estructura_bd['moneda']['columnas_select'] = $this->moneda_columnas;
        $this->estructura_bd['moneda']['where_filtro_or'] = true;
        $this->estructura_bd['moneda']['genera_or_like'] = array('codigo');
        $this->estructura_bd['moneda']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'codigo'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>12,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,
                'vista'=>array('alta','modifica')));


        $this->estructura_bd['forma_pago']['columnas_select'] = $this->forma_pago_columnas;
        $this->estructura_bd['forma_pago']['where_filtro_or'] = true;
        $this->estructura_bd['forma_pago']['genera_or_like'] = array('codigo');
        $this->estructura_bd['forma_pago']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'codigo'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>12,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,
                'vista'=>array('alta','modifica')));


        $this->estructura_bd['seccion_menu']['columnas_select'] = $this->seccion_menu_columnas;
        $this->estructura_bd['seccion_menu']['where_filtro_or'] = true;
        $this->estructura_bd['seccion_menu']['genera_or_descripcion'] = array('menu');
        $this->estructura_bd['seccion_menu']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'menu_id'=>array(
                'tipo'=>'select','cols'=>6,'requerido'=>'required',
                'tabla_foranea'=>'menu','vista'=>array('alta','modifica')),
            'icono'=>array('tipo'=>'text','cols'=>12,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>6,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta','modifica')));


        $this->estructura_bd['estado']['columnas_select'] = $this->estado_columnas;
        $this->estructura_bd['estado']['where_filtro_or'] = true;
        $this->estructura_bd['estado']['genera_or_descripcion'] = array('pais');
        $this->estructura_bd['estado']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'pais_id'=>array(
                'tipo'=>'select','cols'=>6,'requerido'=>'required',
                'tabla_foranea'=>'pais','vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>12,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta','modifica')));


        $this->estructura_bd['municipio']['columnas_select'] = $this->municipio_columnas;
        $this->estructura_bd['municipio']['where_filtro_or'] = true;
        $this->estructura_bd['municipio']['genera_or_descripcion'] = array('pais','estado');
        $this->estructura_bd['municipio']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'pais_id'=>array(
                'tipo'=>'select','cols'=>6,'requerido'=>'required',
                'tabla_foranea'=>'pais','vista'=>array('alta','modifica'),'externa'=>true),
            'estado_id'=>array(
                'tipo'=>'select','cols'=>12,'requerido'=>'required',
                'tabla_foranea'=>'estado','vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>12,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta','modifica')));


        $this->estructura_bd['tipo_insumo']['columnas_select'] = $this->tipo_insumo_columnas;
        $this->estructura_bd['tipo_insumo']['where_filtro_or'] = true;
        $this->estructura_bd['tipo_insumo']['genera_or_descripcion'] = array('grupo_insumo');
        $this->estructura_bd['tipo_insumo']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'grupo_insumo_id'=>array(
                'tipo'=>'select','cols'=>6,'requerido'=>'required',
                'tabla_foranea'=>'grupo_insumo','vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>12,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta','modifica')));


        $this->estructura_bd['menu']['columnas_select'] = $this->menu_columnas;
        $this->estructura_bd['menu']['where_filtro_or'] = true;
        $this->estructura_bd['menu']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'icono'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>6,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta','modifica')));


        $this->estructura_bd['tipo_cliente']['columnas_select'] = $this->tipo_cliente_columnas;
        $this->estructura_bd['tipo_cliente']['where_filtro_or'] = true;
        $this->estructura_bd['tipo_cliente']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>12,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>12,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta','modifica')));



        $this->estructura_bd['cliente']['columnas_select'] = $this->cliente_columnas;
        $this->estructura_bd['cliente']['genera_where_base'] = 'rfc';
        $this->estructura_bd['cliente']['genera_or_descripcion'] = array('pais','estado','municipio');
        $this->estructura_bd['cliente']['genera_or_like'] = array('razon_social',
            'telefono','email');
        $this->estructura_bd['cliente']['campos'] = array(
            'razon_social'=>array(
                'tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'rfc'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'pattern'=>$this->pattern_rfc,'vista'=>array('alta','modifica')),
            'regimen_fiscal_id'=>array('tipo'=>'select','cols'=>6,'requerido'=>'required',
                'tabla_foranea'=>'regimen_fiscal','vista'=>array('alta','modifica'),'externa'=>true),
            'pais_id'=>array(
                'tipo'=>'select','cols'=>6,'requerido'=>'required',
                'tabla_foranea'=>'pais','vista'=>array('alta','modifica'),'externa'=>true),
            'estado_id'=>array(
                'tipo'=>'select','cols'=>6,'requerido'=>'required',
                'tabla_foranea'=>'estado','vista'=>array('alta','modifica'),'externa'=>true),
            'municipio_id'=>array(
                'tipo'=>'select','cols'=>12,'requerido'=>'required',
                'tabla_foranea'=>'municipio','vista'=>array('alta','modifica')),
            'cp'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'pattern'=>$this->pattern_cp,'vista'=>array('alta','modifica')),
            'colonia'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'calle'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'exterior'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'interior'=>array('tipo'=>'text','cols'=>6,
                'vista'=>array('alta','modifica')),
            'telefono'=>array('tipo'=>'text','cols'=>6,
                'vista'=>array('alta','modifica'),'requerido'=>'required'),
            'nombre_representante_legal'=>array('tipo'=>'text','cols'=>6,
                'vista'=>array('alta','modifica'), 'requerido'=>'required'),
            'email'=>array('tipo'=>'email','cols'=>6,'vista'=>array('alta','modifica')),
            'pagina_web'=>array('tipo'=>'text','cols'=>12,'vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>6,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta','modifica')));




        $this->estructura_bd['grupo_insumo']['columnas_select'] = $this->grupo_insumo_columnas;
        $this->estructura_bd['grupo_insumo']['where_filtro_or'] = true;
        $this->estructura_bd['grupo_insumo']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>12,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>12,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,'vista'=>array('alta','modifica')));


        $this->estructura_bd['usuario']['columnas_select'] = $this->usuario_columnas;
        $this->estructura_bd['usuario']['genera_or_descripcion'] = array('grupo');
        $this->estructura_bd['usuario']['genera_where_base'] = 'user';
        $this->estructura_bd['usuario']['genera_or_like'] = array('email');
        $this->estructura_bd['usuario']['campos'] = array(
            'user'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'password'=>array('tipo'=>'password','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'email'=>array('tipo'=>'email','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'grupo_id'=>array(
                'tipo'=>'select','cols'=>6,'requerido'=>'required',
                'tabla_foranea'=>'grupo','vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>6,'vista'=>array('alta','modifica')));


        $this->estructura_bd['accion_grupo']['columnas_select'] = $this->accion_grupo_columnas;

        $this->estructura_bd['aduana']['columnas_select'] = $this->aduana_columnas;
        $this->estructura_bd['aduana']['where_filtro_or'] = true;
        $this->estructura_bd['aduana']['genera_or_like'] = array('codigo');
        $this->estructura_bd['aduana']['campos'] = array(
            'descripcion'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'codigo'=>array('tipo'=>'text','cols'=>6,'requerido'=>'required',
                'vista'=>array('alta','modifica')),
            'status'=>array('tipo'=>'checkbox','cols'=>12,'vista'=>array('alta','modifica')),
            'observaciones'=>array('tipo'=>'textarea','cols'=>12,
                'vista'=>array('alta','modifica')));

        $this->estructura_bd['factura_relacionada']['columnas_select'] = $this->factura_relacionada_columnas;
    }

    public function subconsultas($tabla){
        $consulta = False;

        if($tabla == 'moneda'){
            $hoy = date('Y-m-d');
            $consulta = "
                          (SELECT 
                            tipo_cambio.monto 
                          FROM 
                            tipo_cambio 
                          WHERE 
                            tipo_cambio.moneda_id = moneda.id 
                          AND tipo_cambio.fecha = '$hoy' ) AS tipo_cambio_hoy";
        }
        if($tabla == 'anticipo'){

            $total_iva = "(IFNULL((anticipo.monto * anticipo.porcentaje_iva),0))";
            $total = "($total_iva + anticipo.monto)";

            $consulta = "($total) AS anticipo_total, ";
            $consulta .= "($total_iva) AS anticipo_monto_iva ";
        }

        if($tabla == 'nota_credito'){

            $total_iva = "(IFNULL((nota_credito.monto * nota_credito.porcentaje_iva),0))";
            $total = "($total_iva + nota_credito.monto)";

            $consulta = "($total) AS nota_credito_total, ";
            $consulta .= "($total_iva) AS nota_credito_monto_iva ";
        }
        return $consulta;

    }


    private function genera_join($tabla, $tabla_enlace, $renombrada,$obligatorio){
        if($obligatorio){
            $join = 'LEFT';
        }
        else{
            $join = 'LEFT';
        }
        if($renombrada){
            $sql = ' '.$join.' JOIN '.$tabla.' AS '.$renombrada.' ON '.$renombrada.'.id = '.$tabla_enlace.'.'.$renombrada.'_id';
        }
        else {
            $sql = ' '.$join.' JOIN ' . $tabla . ' AS ' . $tabla . ' ON ' . $tabla . '.id = ' . $tabla_enlace . '.' . $tabla . '_id';
        }
        return $sql;
    }
    public function obten_tablas_completas($tabla){

        $tablas = $tabla.' AS '.$tabla;
        $tablas_join = $this->estructura_bd[$tabla]['columnas_select'];

        foreach ($tablas_join as $key=>$tabla_join){
            if(is_array($tabla_join)){
                $tabla_base = $tabla_join['tabla_base'];
                $tabla_enlace = $tabla_join['tabla_enlace'];
                $tabla_renombre = $tabla_join['tabla_renombrada'];
                $obligatorio = $tabla_join['obligatorio'];
                $tablas = $tablas . $this->genera_join($tabla_base, $tabla_enlace,$tabla_renombre,$obligatorio);
            }
            else {
                if ($tabla_join) {
                    $tablas = $tablas . $this->genera_join($key, $tabla_join,false,true);
                }
            }
        }
        return $tablas;
    }
    private function genera_or_like($tabla, $campo, $valor){
        $sql = " OR $tabla.$campo LIKE '%$valor%'  ";
        return $sql;
    }
    private function  genera_where_base($tabla, $campo, $valor){

        $sql = " WHERE $tabla.$campo LIKE '%$valor%'  ";
        return $sql;
    }
    private function where_filtro_or($tabla,$valor){
        $sql = $this->genera_where_base($tabla,'descripcion',$valor);
        $sql = $sql.$this->genera_or_like($tabla, 'observaciones', $valor);
        return $sql;
    }
    private function genera_or_descripcion($tablas, $valor){
        $sql = '';
        foreach ($tablas as $tabla){
            $sql = $sql.$this->genera_or_like($tabla, 'descripcion', $valor);
        }
        return $sql;
    }

    public function genera_filtro_base($tabla, $valor){
        $valor = strtoupper($valor);
        $where = '';

        if(isset($this->estructura_bd[$tabla]['genera_where_base'])){
            $campo_base = $this->estructura_bd[$tabla]['genera_where_base'];
            $where = $where.$this->genera_where_base($tabla,$campo_base,$valor);
        }

        if(isset($this->estructura_bd[$tabla]['where_filtro_or'])) {
            if ($this->estructura_bd[$tabla]['where_filtro_or']) {
                $where = $where . $this->where_filtro_or($tabla, $valor);
            }
        }

        if(isset($this->estructura_bd[$tabla]['genera_or_descripcion'])) {
            if (is_array($this->estructura_bd[$tabla]['genera_or_descripcion'])) {
                $tablas_descripcion = $this->estructura_bd[$tabla]['genera_or_descripcion'];
                $where = $where . $this->genera_or_descripcion($tablas_descripcion, $valor);
            }
        }

        if(isset($this->estructura_bd[$tabla]['genera_or_like'])) {
            if (is_array($this->estructura_bd[$tabla]['genera_or_like'])) {
                $campos_like = $this->estructura_bd[$tabla]['genera_or_like'];
                foreach ($campos_like as $campo) {
                    $where = $where . $this->genera_or_like($tabla, $campo, $valor);
                }
            }
        }

        if(isset($this->estructura_bd[$tabla]['genera_filtro_especial'])) {
            if (is_array($this->estructura_bd[$tabla]['genera_filtro_especial'])) {
                $campos_like = $this->estructura_bd[$tabla]['genera_filtro_especial'];
                foreach ($campos_like as $tabla=>$campos) {
                    foreach ($campos as $campo) {
                        $where = $where . $this->genera_or_like($tabla, $campo, $valor);
                    }
                }
            }
        }

        return $where;
    }


}