function open_window(url_factura){
    window.open(url_factura,'Download');
}

function ejecuta_cambio_valor_unitario(elemento){
    var contenedor_valor_unitario = elemento;
    var contenedor_cantidad = elemento.parent().siblings('.selector_cantidad').children('.cantidad');
    var contenedor_sub_total = elemento.parent().siblings('.selector_sub_total').children('.sub_total');


    var contenedor_factor_ieps = elemento.parent().siblings('.factor-ieps-valor');
    var contenedor_monto_base_ieps= elemento.parent().siblings('.datos-insumo').children().children('.monto-base-ieps');
    var contenedor_monto_impuesto_ieps = elemento.parent().siblings('.datos-insumo').children().children('.monto-impuesto-ieps');


    var contenedor_factor_traslado = elemento.parent().siblings('.factor-traslado-valor');
    var contenedor_monto_base_traslado = elemento.parent().siblings('.datos-insumo').children().children('.monto-base-traslado');
    var contenedor_monto_impuesto_trasladado = elemento.parent().siblings('.datos-insumo').children().children('.monto-impuesto-trasladado');
    var contenedor_monto_impuesto_traslado_partida = elemento.parent().siblings('.datos-insumo').children('.impuestos-traslados-partida').children('.monto-impuesto-traslado-partida');
    var contenedor_monto_impuesto_ieps_partida = elemento.parent().siblings('.datos-insumo').children('.impuestos-ieps-partida').children('.monto-impuesto-ieps-partida');
    var contenedor_monto_impuesto_retenido_partida = elemento.parent().siblings('.datos-insumo').children('.impuestos-retenidos-partida').children('.monto-impuesto-retenido-partida');


    var contenedor_factor_retenido = elemento.parent().siblings('.factor-retenido-valor');
    var contenedor_monto_base_retenido = elemento.parent().siblings('.datos-insumo').children().children('.monto-base-retenido');
    var contenedor_monto_impuesto_retenido = elemento.parent().siblings('.datos-insumo').children().children('.monto-impuesto-retenido');


    var cantidad = parseFloat(contenedor_cantidad.val()).toFixed(2);
    var valor_unitario = parseFloat(contenedor_valor_unitario.val()).toFixed(2);

    var monto_base_ieps = parseFloat(cantidad * valor_unitario).toFixed(2);
    var factor_ieps = parseFloat(contenedor_factor_ieps.val()).toFixed(5);
    var monto_impuesto_ieps = parseFloat(monto_base_ieps * factor_ieps).toFixed(2);
    contenedor_monto_impuesto_ieps_partida.val(monto_impuesto_ieps);



    var monto_base_traslado = parseFloat(cantidad * valor_unitario + parseFloat(monto_impuesto_ieps)).toFixed(2);
    var factor_traslado = parseFloat(contenedor_factor_traslado.val()).toFixed(2);
    var monto_impuesto_trasladado = parseFloat(monto_base_traslado * factor_traslado).toFixed(2);
    contenedor_monto_impuesto_traslado_partida.val(monto_impuesto_trasladado);


    var monto_base_retenido = parseFloat(cantidad * valor_unitario + parseFloat(monto_impuesto_ieps)).toFixed(2);
    var factor_retenido = parseFloat(contenedor_factor_retenido.val()).toFixed(2);
    var monto_impuesto_retenido = parseFloat(monto_base_retenido * factor_retenido).toFixed(2);
    contenedor_monto_impuesto_retenido_partida.val(monto_impuesto_retenido);

    var sub_total = parseFloat(cantidad * valor_unitario);


    if(isNaN(monto_base_traslado)){
        monto_base_traslado = 0.00;
    }
    if(isNaN(monto_impuesto_trasladado)){
        monto_impuesto_trasladado = 0.00;
    }


    if(isNaN(monto_base_retenido)){
        monto_base_retenido = 0.00;
    }
    if(isNaN(monto_impuesto_retenido)){
        monto_impuesto_retenido = 0.00;
    }


    if(isNaN(monto_base_ieps)){
        monto_base_ieps = 0.00;
    }
    if(isNaN(monto_impuesto_ieps)){
        monto_impuesto_ieps = 0.00;
    }

    if(isNaN(sub_total)){
        sub_total = 0.00;
    }


    contenedor_monto_base_ieps.empty();
    contenedor_monto_base_ieps.append('$'+monto_base_ieps);


    contenedor_sub_total.empty();
    contenedor_sub_total.val(sub_total);

    contenedor_monto_impuesto_ieps.empty();
    contenedor_monto_impuesto_ieps.append('$'+monto_impuesto_ieps);

    contenedor_monto_base_traslado.empty();
    contenedor_monto_base_traslado.append('$'+monto_base_traslado);

    contenedor_monto_impuesto_trasladado.empty();
    contenedor_monto_impuesto_trasladado.append('$'+monto_impuesto_trasladado);


    contenedor_monto_base_retenido.empty();
    contenedor_monto_base_retenido.append('$'+monto_base_retenido);

    contenedor_monto_impuesto_retenido.empty();
    contenedor_monto_impuesto_retenido.append('$'+monto_impuesto_retenido);

    calcula_sub_total();
    calcula_traslados();
    calcula_retenciones();
    calcula_total();
}

function ejecuta_cambio_cantidad(elemento){

    var contenedor_valor_unitario = elemento.parent().siblings('.selector_valor_unitario').children('.valor_unitario');


    var contenedor_cantidad = elemento;
    var contenedor_sub_total = elemento.parent().siblings('.selector_sub_total').children('.sub_total');



    var contenedor_factor_ieps = elemento.parent().siblings('.factor-ieps-valor');
    var contenedor_monto_base_ieps= elemento.parent().siblings('.datos-insumo').children().children('.monto-base-ieps');
    var contenedor_monto_impuesto_ieps = elemento.parent().siblings('.datos-insumo').children().children('.monto-impuesto-ieps');


    var contenedor_factor_traslado = elemento.parent().siblings('.factor-traslado-valor');
    var contenedor_monto_base_traslado = elemento.parent().siblings('.datos-insumo').children().children('.monto-base-traslado');
    var contenedor_monto_impuesto_trasladado = elemento.parent().siblings('.datos-insumo').children().children('.monto-impuesto-trasladado');
    var contenedor_monto_impuesto_traslado_partida =elemento.parent().siblings('.datos-insumo').children('.impuestos-traslados-partida').children('.monto-impuesto-traslado-partida');
    var contenedor_monto_impuesto_ieps_partida = elemento.parent().siblings('.datos-insumo').children('.impuestos-ieps-partida').children('.monto-impuesto-ieps-partida');
    var contenedor_monto_impuesto_retenido_partida = elemento.parent().siblings('.datos-insumo').children('.impuestos-retenidos-partida').children('.monto-impuesto-retenido-partida');


    var contenedor_factor_retenido = elemento.parent().siblings('.factor-retenido-valor');
    var contenedor_monto_base_retenido = elemento.parent().siblings('.datos-insumo').children().children('.monto-base-retenido');
    var contenedor_monto_impuesto_retenido = elemento.parent().siblings('.datos-insumo').children().children('.monto-impuesto-retenido');



    var cantidad = parseFloat(contenedor_cantidad.val()).toFixed(2);
    var valor_unitario = parseFloat(contenedor_valor_unitario.val()).toFixed(2);


    var monto_base_ieps = parseFloat(cantidad * valor_unitario).toFixed(2);
    var factor_ieps = parseFloat(contenedor_factor_ieps.val()).toFixed(5);
    var monto_impuesto_ieps = parseFloat(monto_base_ieps * factor_ieps).toFixed(2);
    contenedor_monto_impuesto_ieps_partida.val(monto_impuesto_ieps);



    var monto_base_traslado = parseFloat(cantidad * valor_unitario + parseFloat(monto_impuesto_ieps)).toFixed(2);
    var factor_traslado = parseFloat(contenedor_factor_traslado.val()).toFixed(2);
    var monto_impuesto_trasladado = parseFloat(monto_base_traslado * factor_traslado).toFixed(2);

    contenedor_monto_impuesto_traslado_partida.val(monto_impuesto_trasladado);



    var monto_base_retenido = parseFloat(cantidad * valor_unitario + parseFloat(monto_impuesto_ieps)).toFixed(2);
    var factor_retenido = parseFloat(contenedor_factor_retenido.val()).toFixed(2);
    var monto_impuesto_retenido = parseFloat(monto_base_retenido * factor_retenido).toFixed(2);
    contenedor_monto_impuesto_retenido_partida.val(monto_impuesto_retenido);


    var sub_total = parseFloat(cantidad * valor_unitario);




    if(isNaN(monto_base_traslado)){
        monto_base_traslado = 0.00;
    }
    if(isNaN(monto_impuesto_trasladado)){
        monto_impuesto_trasladado = 0.00;
    }


    if(isNaN(monto_base_retenido)){
        monto_base_retenido = 0.00;
    }
    if(isNaN(monto_impuesto_retenido)){
        monto_impuesto_retenido = 0.00;
    }

    if(isNaN(monto_base_ieps)){
        monto_base_ieps = 0.00;
    }
    if(isNaN(monto_impuesto_ieps)){
        monto_impuesto_ieps = 0.00;
    }

    if(isNaN(sub_total)){
        sub_total = 0.00;
    }


    contenedor_monto_base_ieps.empty();
    contenedor_monto_base_ieps.append('$'+monto_base_ieps);

    contenedor_sub_total.empty();
    contenedor_sub_total.val(sub_total);

    contenedor_monto_impuesto_ieps.empty();
    contenedor_monto_impuesto_ieps.append('$'+monto_impuesto_ieps);


    contenedor_monto_base_traslado.empty();
    contenedor_monto_base_traslado.append('$'+monto_base_traslado);

    contenedor_monto_impuesto_trasladado.empty();
    contenedor_monto_impuesto_trasladado.append('$'+monto_impuesto_trasladado);


    contenedor_monto_base_retenido.empty();
    contenedor_monto_base_retenido.append('$'+monto_base_retenido);


    contenedor_monto_impuesto_retenido.empty();
    contenedor_monto_impuesto_retenido.append('$'+monto_impuesto_retenido);



    calcula_sub_total();
    calcula_traslados();
    calcula_retenciones();
    calcula_total();

}

function ejecuta_cambio_insumo(elemento){

    var insumo_id = elemento.val();



    var unidad_descripcion = elemento.parent().parent().siblings('.datos-insumo').children().children('.unidad-medida');

    var unidad_codigo = elemento.parent().parent().siblings('.datos-insumo').children().children('.unidad-codigo');
    var concepto_sat = elemento.parent().parent().siblings('.datos-insumo').children().children('.concepto-sat');
    var codigo_sat = elemento.parent().parent().siblings('.datos-insumo').children().children('.codigo-sat');


    var codigo_impuesto_ieps = elemento.parent().parent().siblings('.datos-insumo').children().children('.codigo-impuesto-ieps');
    var descripcion_ieps = elemento.parent().parent().siblings('.datos-insumo').children().children('.impuesto-ieps');
    var factor_ieps = elemento.parent().parent().siblings('.datos-insumo').children().children('.factor-ieps');
    var factor_ieps_valor = elemento.parent().siblings('.factor-ieps-valor');



    var codigo_impuesto_traslado = elemento.parent().parent().siblings('.datos-insumo').children().children('.codigo-impuesto-traslado');
    var impuesto_traslado = elemento.parent().parent().siblings('.datos-insumo').children().children('.impuesto-traslado');
    var factor_traslado = elemento.parent().parent().siblings('.datos-insumo').children().children('.factor-traslado');
    var factor_traslado_valor = elemento.parent().parent().siblings('.factor-traslado-valor');


    var codigo_impuesto_retenido = elemento.parent().parent().parent().siblings('.datos-insumo').children().children('.codigo-impuesto-retenido');
    var impuesto_retenido = elemento.parent().parent().parent().siblings('.datos-insumo').children().children('.impuesto-retenido');
    var factor_retenido = elemento.parent().parent().parent().siblings('.datos-insumo').children().children('.factor-retenido');
    var factor_retenido_valor = elemento.parent().parent().siblings('.factor-retenido-valor');




    var contenedor_valor_unitario = elemento.parent().parent().siblings('.selector_valor_unitario').children('.valor_unitario');
    var contenedor_cantidad = elemento.parent().parent().siblings('.selector_cantidad').children('.cantidad');


    var contenedor_monto_base_retenido = elemento.parent().parent().siblings('.datos-insumo').children().children('.monto-base-retenido');
    var contenedor_monto_base_traslado = elemento.parent().parent().siblings('.datos-insumo').children().children('.monto-base-traslado');
    var contenedor_monto_base_ieps = elemento.parent().parent().siblings('.datos-insumo').children().children('.monto-base-ieps');
    var contenedor_monto_impuesto_ieps = elemento.parent().parent().siblings('.datos-insumo').children().children('.monto-impuesto-ieps');


    var contenedor_monto_impuesto_retenido = elemento.parent().parent().siblings('.datos-insumo').children().children('.monto-impuesto-retenido');
    var contenedor_monto_impuesto_traslado = elemento.parent().parent().siblings('.datos-insumo').children().children('.monto-impuesto-trasladado');



    var contenedor_monto_impuesto_traslado_partida = elemento.parent().parent().siblings('.datos-insumo').children('.impuestos-traslados-partida').children('.monto-impuesto-traslado-partida');
    var contenedor_monto_impuesto_ieps_partida = elemento.parent().parent().siblings('.datos-insumo').children('.impuestos-ieps-partida').children('.monto-impuesto-ieps-partida');
    var contenedor_monto_impuesto_retencion_partida = elemento.parent().parent().siblings('.datos-insumo').children('.impuestos-retenidos-partida').children('.monto-impuesto-retenido-partida');


    var cantidad = contenedor_cantidad.val();
    var valor_unitario = contenedor_valor_unitario.val();

    var contenedor_ieps_completo = elemento.parent().parent().siblings('.datos-insumo').children('.impuestos-ieps-partida');
    var contenedor_traslado_completo = elemento.parent().parent().siblings('.datos-insumo').children('.impuestos-traslados-partida');
    var contenedor_retencion_completo = elemento.parent().parent().siblings('.datos-insumo').children('.impuestos-retenidos-partida');


    $.ajax({
        url: "./index_ajax.php?seccion=cliente&accion=obten_datos_unidad&insumo_id="+insumo_id,
        type: "POST", //send it through get method
        data: {},
        success: function(data) {

            var codigo_ieps_dato = data['insumo_ieps_codigo'];
            codigo_impuesto_ieps.empty();
            codigo_impuesto_ieps.append(codigo_ieps_dato);


            if(codigo_ieps_dato == -1){
                contenedor_ieps_completo.hide();
            }
            else{
                contenedor_ieps_completo.show();
            }


            var descripcion_ieps_dato = data['insumo_ieps_descripcion'];
            descripcion_ieps.empty();
            descripcion_ieps.append(descripcion_ieps_dato);

            var factor_ieps_dato = data['insumo_ieps_factor'];
            factor_ieps.empty();
            factor_ieps.append(factor_ieps_dato);


            var unidad_descripcion_dato = data['unidad_descripcion'];
            unidad_descripcion.empty();
            unidad_descripcion.append(unidad_descripcion_dato);

            var unidad_codigo_dato = data['unidad_codigo'];
            unidad_codigo.empty();
            unidad_codigo.append(unidad_codigo_dato);

            var codigo_impuesto_traslado_dato = data['impuesto_codigo'];
            codigo_impuesto_traslado.empty();
            codigo_impuesto_traslado.append(codigo_impuesto_traslado_dato);

            var impuesto_id = data['insumo_impuesto_id'];

            if(impuesto_id == 0){
                contenedor_traslado_completo.hide();
            }
            else{
                contenedor_traslado_completo.show();
            }


            var codigo_impuesto_retenido_dato = data['impuesto_retenido_codigo'];
            codigo_impuesto_retenido.empty();
            codigo_impuesto_retenido.append(codigo_impuesto_retenido_dato);


            var impuesto_retenido_id = data['insumo_impuesto_retenido_id'];

            if(impuesto_retenido_id == -1){
                contenedor_retencion_completo.hide();
            }
            else{
                contenedor_retencion_completo.show();
            }

            var impuesto_traslado_dato = data['impuesto_descripcion'];
            impuesto_traslado.empty();
            impuesto_traslado.append(impuesto_traslado_dato);

            var impuesto_retenido_dato = data['impuesto_retenido_descripcion'];
            impuesto_retenido.empty();
            impuesto_retenido.append(impuesto_retenido_dato);



            var factor_traslado_dato = data['insumo_factor'];
            factor_traslado.empty();
            factor_traslado.append(factor_traslado_dato);


            var factor_retenido_dato = data['insumo_factor_retenido'];
            factor_retenido.empty();
            factor_retenido.append(factor_retenido_dato);

            var concepto_sat_dato = data['producto_sat_descripcion'];
            concepto_sat.empty();
            concepto_sat.append(concepto_sat_dato);

            var codigo_sat_dato = data['producto_sat_codigo'];
            codigo_sat.empty();
            codigo_sat.append(codigo_sat_dato);


            factor_traslado_valor.val(data['insumo_factor']);


            factor_retenido_valor.val(data['insumo_factor_retenido']);
            factor_ieps_valor.val(data['insumo_ieps_factor']);



            factor_retenido = parseFloat(factor_retenido_valor.val()).toFixed(2);
            factor_traslado = parseFloat(factor_traslado_valor.val()).toFixed(2);



            if(isNaN(factor_retenido)){
                factor_retenido = 0.00;
            }
            if(isNaN(factor_traslado)){
                factor_traslado = 0.00;
            }


            var monto_base_ieps = parseFloat(cantidad * valor_unitario).toFixed(2);
            var monto_impuesto_ieps = parseFloat(monto_base_ieps * factor_ieps_dato).toFixed(2);




            var monto_base_traslado = parseFloat(cantidad * valor_unitario + parseFloat(monto_impuesto_ieps)).toFixed(2);
            var monto_base_retenido = parseFloat(cantidad * valor_unitario + parseFloat(monto_impuesto_ieps)).toFixed(2);





            var monto_impuesto_traslado = parseFloat(monto_base_traslado * factor_traslado).toFixed(2);



            var monto_impuesto_retenido = parseFloat(monto_base_retenido * factor_retenido).toFixed(2);


            contenedor_monto_impuesto_traslado_partida.val(monto_impuesto_traslado);
            contenedor_monto_impuesto_ieps_partida.val(monto_impuesto_ieps);
            contenedor_monto_impuesto_retencion_partida.val(monto_impuesto_retenido);

            contenedor_monto_base_ieps.empty();
            contenedor_monto_base_ieps.append('$'+monto_base_ieps);

            contenedor_monto_impuesto_ieps.empty();
            contenedor_monto_impuesto_ieps.append('$'+monto_impuesto_ieps);




            contenedor_monto_base_traslado.empty();
            contenedor_monto_base_traslado.append('$'+monto_base_traslado);
            contenedor_monto_impuesto_traslado.empty();
            contenedor_monto_impuesto_traslado.append('$'+monto_impuesto_traslado)

            contenedor_monto_base_retenido.empty();
            contenedor_monto_base_retenido.append('$'+monto_base_retenido)

            contenedor_monto_impuesto_retenido.empty();
            contenedor_monto_impuesto_retenido.append('$'+monto_impuesto_retenido);


            calcula_sub_total();
            calcula_traslados();
            calcula_retenciones();
            calcula_total();


        },
        error: function(xhr, status) {
            //Do Something to handle error
            //alert("no insertado correctamente");
        }
    });
}



function calcula_sub_total(){
    var sub_total_general;
    sub_total_general = 0;
    $( ".sub_total" ).each(function() {
        var sub_total_partida = parseFloat($(this).val()).toFixed(2);
        sub_total_general = parseFloat(parseFloat(sub_total_general) + parseFloat(sub_total_partida)).toFixed(2);
    });

    if(isNaN(sub_total_general)){
        sub_total_general = 0;
    }

    $( "#subtotal" ).empty();
    $( "#subtotal" ).append('Sub Total: '+sub_total_general);

    $( "#subtotal_general" ).val(sub_total_general);

}

function calcula_total(){
    var ret_general = parseFloat($( "#impuestos_retenidos_general" ).val()).toFixed(2);
    var trs_general = parseFloat($( "#impuestos_trasladados_general" ).val()).toFixed(2);
    var st_general = parseFloat($( "#subtotal_general" ).val()).toFixed(2);
    var tot_general = parseFloat(parseFloat(st_general)+parseFloat(trs_general)-parseFloat(ret_general));

    $( "#total" ).empty();
    $( "#total" ).append('Total: $'+tot_general);
}

function calcula_traslados(){
    var traslados_general;
    traslados_general = 0;
    $( ".monto-impuesto-traslado-partida" ).each(function() {
        var traslados_partida = parseFloat($(this).val()).toFixed(2);
        traslados_general = parseFloat(parseFloat(traslados_general) + parseFloat(traslados_partida)).toFixed(2);
    });


    $( ".monto-impuesto-ieps-partida" ).each(function() {
        var traslados_partida = parseFloat($(this).val()).toFixed(2);
        traslados_general = parseFloat(parseFloat(traslados_general) + parseFloat(traslados_partida)).toFixed(2);
    });


    if(isNaN(traslados_general)){
        traslados_general = 0;
    }

    $( "#impuestos_trasladados" ).empty();
    $( "#impuestos_trasladados" ).append('Impuestos Trasladados: '+traslados_general);
    $( "#impuestos_trasladados_general" ).val(traslados_general);

}

function calcula_retenciones(){
    var retenciones_general;
    retenciones_general = 0;
    $( ".monto-impuesto-retenido-partida" ).each(function() {
        var retenciones_partida = parseFloat($(this).val()).toFixed(2);
        retenciones_general = parseFloat(parseFloat(retenciones_general) + parseFloat(retenciones_partida)).toFixed(2);
    });


    if(isNaN(retenciones_general)){
        retenciones_general = 0;
    }

    $( "#impuestos_retenidos" ).empty();
    $( "#impuestos_retenidos" ).append('Impuestos Retenidos: '+retenciones_general);
    $( "#impuestos_retenidos_general" ).val(retenciones_general);

}

(function($) {
    $.get = function(key)   {
        key = key.replace(/[\[]/, '\\[');
        key = key.replace(/[\]]/, '\\]');
        var pattern = "[\\?&]" + key + "=([^&#]*)";
        var regex = new RegExp(pattern);
        var url = unescape(window.location.href);
        var results = regex.exec(url);
        if (results === null) {
            return null;
        } else {
            return results[1];
        }
    }
})(jQuery);

function actualiza_por_aplicar(por_aplicar){
    $('#por-aplicar').val(por_aplicar);
    $('#monto-por-aplicar').empty();
    $('#monto-por-aplicar').append(por_aplicar);
}

function ejecuta_aplicacion_pago(elemento){
    var total_pago = $('#total-pago').val();
    var por_aplicar = $('#por-aplicar').val();
    var contenedor_monto = elemento.parent().parent().parent().siblings('.td-monto-pagar').children('#monto_pagar');
    var contenedor_saldo = elemento.parent().parent().parent().siblings('.td-saldo-factura').children('.saldo-factura');


    if(!elemento.prop('checked')){
        por_aplicar = parseFloat(por_aplicar)+parseFloat(contenedor_monto.val());
        contenedor_monto.val(0);
        actualiza_por_aplicar(por_aplicar);
    }
    else{
        if(por_aplicar>0){
            if(parseFloat(por_aplicar)<=parseFloat(contenedor_saldo.val())){
                contenedor_monto.val(por_aplicar);
                por_aplicar = parseFloat(por_aplicar)-parseFloat(contenedor_monto.val());
                actualiza_por_aplicar(por_aplicar);
            }
            else{
                var saldo = parseFloat(contenedor_saldo.val());
                contenedor_monto.val(saldo);
                por_aplicar = parseFloat(por_aplicar)-parseFloat(contenedor_monto.val());
                actualiza_por_aplicar(por_aplicar);
            }
        }
        else{
            alert('No puedes aplicar mas pagos');
            elemento.prop('checked', false);
            return false;
        }
    }
}

function obtener_monto_aplicado(){
    var monto_aplicado = 0;
    $(".monto_pagar").each(function() {
        monto_aplicado = monto_aplicado + parseFloat($(this).val());
    });
    return monto_aplicado;
}

$(document).ready(function () {
    var seccion = $.get("seccion");
    var accion = $.get("accion");
    var session_id = $.get("session_id");

    if(seccion == 'pago_cliente' && accion == 'alta_cfdi_relacionado'){
        $('.aplica_pago').click(function (){
            ejecuta_aplicacion_pago($(this));
        });

        $('.monto_pagar').change(function (){
            var contenedor_aplica_pago = $(this).parent().siblings('.td-aplica-pago').children().children().children('.aplica_pago');
            var contenedor_saldo = $(this).parent().siblings('.td-saldo-factura').children('.saldo-factura');
            var monto_por_aplicar = parseFloat($(this).val());
            var saldo_factura = parseFloat(contenedor_saldo.val());

            var total_pago = parseFloat($('#total-pago').val());
            var monto_aplicado = parseFloat(obtener_monto_aplicado());
            var por_aplicar = total_pago - monto_aplicado;


            if(por_aplicar<0){
                alert('Se excede el monto de pago favor verifique');
                contenedor_aplica_pago.prop('checked', false);
                $(this).val(0);
                monto_aplicado = parseFloat(obtener_monto_aplicado());
                por_aplicar = total_pago - monto_aplicado;
                actualiza_por_aplicar(por_aplicar);
                return false;
            }

            if(monto_por_aplicar>saldo_factura){
                alert('El monto por aplicar excede el saldo de la factura');
                contenedor_aplica_pago.prop('checked', false);
                $(this).val(0);
                monto_aplicado = parseFloat(obtener_monto_aplicado());
                actualiza_por_aplicar(por_aplicar);
                return false;
            }

            contenedor_aplica_pago.prop('checked', true);
            if(monto_por_aplicar == 0){

                contenedor_aplica_pago.prop('checked', false);
            }
            actualiza_por_aplicar(por_aplicar);


        });

    }

    if(seccion == 'factura' && (accion == 'agrega_conceptos' || accion =='siguiente_partida') ){
        $('#siguiente').click(function (){
            var insumo_id = $("#select_insumo").val();
            var cantidad = $("#cantidad").val();
            var valor_unitario = $("#valor_unitario").val();

            if(insumo_id != '' && cantidad!='' && valor_unitario!='') {
                var txt;
                var r = confirm("Tienes por agregar un producto a la factura, da aceptar si deseas agregarlo");
                if (r == true) {
                    $.post("index_ajax.php?seccion=factura&accion=guarda_partida&session_id="+session_id, {
                            insumo_id: insumo_id, cantidad: cantidad, valor_unitario: valor_unitario
                        },
                        function () {
                            location.href = 'index.php?seccion=factura&accion=siguiente_partida&session_id='+session_id;
                            location.href = 'index.php?seccion=factura&accion=guarda_factura&session_id='+session_id;
                            return false;
                        });

                } else {

                    location.href = 'index.php?seccion=factura&accion=guarda_factura&session_id='+session_id;
                    return false;
                }
            }

            location.href = 'index.php?seccion=factura&accion=guarda_factura&session_id='+session_id;
        });

        $('#partida_nueva').click(function (){
            var insumo_id = $('#select_insumo').val();
            var cantidad = $('#cantidad').val();
            var valor_unitario = $('#valor_unitario').val();

            if(insumo_id == ''){
                alert('Selecciona un insumo');
                return false;
            }

            if(cantidad == ''){
                alert('la cantidad no puede ir vacia');
                return false;
            }

            if(valor_unitario == ''){
                alert('el valor unitario no puede ir vacio');
                return false;
            }

            $.post("index_ajax.php?seccion=factura&accion=guarda_partida&session_id="+session_id, {
                insumo_id: insumo_id, cantidad: cantidad, valor_unitario: valor_unitario
                },
                function () {
                    location.href = 'index.php?seccion=factura&accion=siguiente_partida&session_id='+session_id;
                });
        });


        $('#select_insumo').change(function (){
            var insumo_id = $(this).val();
            $.post("index_ajax.php?seccion=factura&accion=obten_datos_insumo&session_id="+session_id+"&insumo_id="+insumo_id, {},
                function (data) {
                    var factor_trasladado = data['insumo_factor'];
                    var factor_retenido = data['insumo_factor_retenido'];
                    $('#factor_trasladado').val(factor_trasladado);
                    $('#factor_retenido').val(factor_retenido);

                });
        });

        $('#cantidad').change(function (){
            var cantidad = $(this).val();
            var valor_unitario = $('#valor_unitario').val();
            var subtotal = cantidad * valor_unitario;
            var traslados = subtotal * $('#factor_trasladado').val();
            var retenciones = subtotal * $('#factor_retenido').val();

            var total = subtotal + traslados - retenciones;

            $('#traslados').val(traslados);
            $('#retenciones').val(retenciones);
            $('#total').val(total);
        });

        $('#valor_unitario').change(function (){
            var cantidad = $('#cantidad').val();
            var valor_unitario = $(this).val();
            var subtotal = cantidad * valor_unitario;
            var traslados = subtotal * $('#factor_trasladado').val();
            var retenciones = subtotal * $('#factor_retenido').val();

            var total = subtotal + traslados - retenciones;

            $('#traslados').val(traslados);
            $('#retenciones').val(retenciones);
            $('#total').val(total);
        });

        $('#agrega_producto').click(function (){

            $.post("index_ajax.php?seccion=factura&accion=alta_insumo&session_id="+session_id, {},
                function (data) {
                    $('#insumo-selector').hide('slow');
                    $('#siguiente').hide('slow');
                    $('#agrega_producto').hide('slow');
                    $('#partida_nueva').hide('slow');

                    $('#alta-insumo').empty();
                    $('#alta-insumo').append(data);

                    $('#select_unidad').selectpicker('destroy');
                    $('#select_unidad').selectpicker('refresh');

                    $('#select_tipo_insumo').selectpicker('destroy');
                    $('#select_tipo_insumo').selectpicker('refresh');

                    $('#select_impuesto').selectpicker('destroy');
                    $('#select_impuesto').selectpicker('refresh');

                    $('#select_impuesto_retenido').selectpicker('destroy');
                    $('#select_impuesto_retenido').selectpicker('refresh');

                    $('#select_tipo_factor').selectpicker('destroy');
                    $('#select_tipo_factor').selectpicker('refresh');

                    $('#select_tipo_factor_retenido').selectpicker('destroy');
                    $('#select_tipo_factor_retenido').selectpicker('refresh');

                    $('#producto_sat_id').keyup(function (){
                        var descripcion_busqueda = $(this).val();
                        var url_ejecucion = "./index_ajax.php?seccion=insumo&accion=genera_lista_producto_sat&session_id="+session_id;
                        $("#producto_sat_datos").empty();
                        $("#producto_sat_datos" ).load( url_ejecucion ,{valor:descripcion_busqueda}, function() {
                            $(".producto_sat_id").unbind('click');
                            $('.producto_sat_id').click(function (){
                                $('.producto_sat_id').parent().hide();
                                $(this).parent().show();
                                var producto_sat_id = $(this).val();
                                $("#producto_sat_id_seleccionado").val(producto_sat_id);
                            });

                        });
                    });


                    $('#cancela-producto').click(function () {
                        $('#alta-insumo').empty();
                        $('#insumo-selector').show('slow');
                        $('#siguiente').show('slow');
                        $('#agrega_producto').show('slow');
                        $('#partida_nueva').show('slow');
                    });

                    $('#guarda-producto').click(function () {

                        var descripcion = $("#descripcion").val();
                        var unidad_id = $("#select_unidad").val();
                        var tipo_insumo_id = $("#select_tipo_insumo").val();

                        var impuesto_id = $("#select_impuesto").val();
                        var tipo_factor_id = $("#select_tipo_factor").val();
                        var factor = $("#factor").val();


                        var impuesto_retenido_id = $("#select_impuesto_retenido").val();
                        var tipo_factor_retenido_id = $("#select_tipo_factor_retenido").val();
                        var factor_retenido = $("#factor_retenido").val();
                        var producto_sat_id = $("#producto_sat_id_seleccionado").val();


                        $.post("index_ajax.php?seccion=factura&accion=guarda_insumo&session_id="+session_id, {
                            descripcion: descripcion, unidad_id: unidad_id,
                            tipo_insumo_id: tipo_insumo_id, impuesto_id: impuesto_id,
                            tipo_factor_id: tipo_factor_id, factor: factor,
                                impuesto_retenido_id: impuesto_retenido_id,
                                tipo_factor_retenido_id: tipo_factor_retenido_id,
                                factor_retenido: factor_retenido,
                                producto_sat_id: producto_sat_id
                            },
                            function (data) {

                                var insumo_id = data['registro_id'];
                                var error = data['error'];
                                alert(data['mensaje']);

                                $("#alta-insumo").empty();
                                $('#insumo-selector').show('slow');
                                $('#siguiente').show('slow');
                                $('#agrega_producto').show('slow');
                                $('#partida_nueva').show('slow');

                                if(error == false){
                                    $.post("index_ajax.php?seccion=factura&accion=obten_insumos&session_id="+session_id+"&insumo_id="+insumo_id, {},
                                        function (data) {
                                            $('#insumo-selector').empty();
                                            $('#insumo-selector').append(data);
                                            $('.insumo_id').selectpicker('refresh');

                                            $('#factor_trasladado').val(factor);
                                            $('#factor_retenido').val(factor_retenido);

                                            $('#select_insumo').change(function (){
                                                var insumo_id = $(this).val();
                                                $.post("index_ajax.php?seccion=factura&accion=obten_datos_insumo&session_id="+session_id+"&insumo_id="+insumo_id, {},
                                                    function (data) {
                                                        var factor_trasladado = data['insumo_factor'];

                                                        var factor_retenido = data['insumo_factor_retenido'];
                                                        $('#factor_trasladado').val(factor_trasladado);
                                                        $('#factor_retenido').val(factor_retenido);

                                                    });
                                            });

                                            $('#cantidad').change(function (){
                                                var cantidad = $(this).val();
                                                var valor_unitario = $('#valor_unitario').val();
                                                var subtotal = cantidad * valor_unitario;
                                                var traslados = subtotal * $('#factor_trasladado').val();
                                                var retenciones = subtotal * $('#factor_retenido').val();

                                                var total = subtotal + traslados - retenciones;

                                                $('#traslados').val(traslados);
                                                $('#retenciones').val(retenciones);
                                                $('#total').val(total);
                                            });

                                            $('#valor_unitario').change(function (){
                                                var cantidad = $('#cantidad').val();
                                                var valor_unitario = $(this).val();
                                                var subtotal = cantidad * valor_unitario;
                                                var traslados = subtotal * $('#factor_trasladado').val();
                                                var retenciones = subtotal * $('#factor_retenido').val();

                                                var total = subtotal + traslados - retenciones;

                                                $('#traslados').val(traslados);
                                                $('#retenciones').val(retenciones);
                                                $('#total').val(total);
                                            });

                                    });
                                }

                            }
                        );
                    });

                }
            );


        });
    }
    
    if(seccion == 'pago_cliente' && accion == 'alta_partida'){
        $('#guarda_encabezado_pago').click(function (){
            if($('#select_forma_pago').val()=='21'){
                alert('Ingrese una forma de pago valida');
                return false;
            }
            
        });
    }
    if(seccion == 'factura' && accion == 'obten_datos_facturacion'){
        $('#siguiente').click(function () {

            var cliente_uso_cfdi_id = $('#select_uso_cfdi').val();
            var cliente_moneda_id = $('#select_moneda').val();
            var cliente_forma_pago_id = $('#select_forma_pago').val();
            var cliente_metodo_pago_id = $('#select_metodo_pago').val();
            var cliente_condiciones_pago = $('#condiciones_pago').val();
            var fecha = $('#fecha').val();

            $.post("index_ajax.php?seccion=factura&accion=guarda_datos_factura_session&session_id="+session_id, {
                    cliente_uso_cfdi_id: cliente_uso_cfdi_id,
                    cliente_moneda_id: cliente_moneda_id,
                    cliente_forma_pago_id: cliente_forma_pago_id,
                    cliente_metodo_pago_id: cliente_metodo_pago_id,
                    cliente_condiciones_pago: cliente_condiciones_pago,
                    fecha: fecha
                },
                function (data) {
                    location.href = './index.php?seccion=factura&accion=agrega_conceptos&session_id='+session_id;
                }
            );
        });
    }

    if(seccion == 'factura' && accion == 'crea_factura'){
        $('#siguiente').click(function () {
            var cliente_id = $('#select_cliente').val();
            $.post("index_ajax.php?seccion=factura&accion=guarda_cliente_session&session_id="+session_id, {cliente_id: cliente_id},
                function () {
                    location.href = './index.php?seccion=factura&accion=obten_datos_facturacion&session_id='+session_id;
                }
            );
        });


        $('#agrega_cliente').click(function () {
            $.post("index_ajax.php?seccion=factura&accion=alta_cliente&session_id="+session_id, {},
                function (data) {
                    $('#agrega_cliente').hide('slow');

                    $('#siguiente').hide('slow');
                    $("#alta_cliente").hide();
                    $('#alta_cliente').empty();
                    $('#alta_cliente').append(data);
                    $("#alta_cliente").show('slow');


                    $('.pais_id').selectpicker('destroy');

                    $('.estado_id').selectpicker('destroy');

                    $('.municipio_id').selectpicker('destroy');


                    $('#select_pais').unbind('change');
                    $('#select_pais').change(function (){
                        var id = $(this).val();
                        genera_option($(this),'estado','pais_id',$('.estado_id'),'','');
                    });

                    $('#select_estado').unbind('change');
                    $('#select_estado').change(function (){
                        var id = $(this).val();
                        genera_option($(this),'municipio','estado_id',$('.municipio_id'),'','');
                    });

                    $('.pais_id').selectpicker('reset');
                    $('.estado_id').selectpicker('reset');
                    $('.municipio_id').selectpicker('reset');


                    $('#guarda_cliente').unbind('click');
                    $('#guarda_cliente').click(function () {

                        var rfc = $('#rfc').val();
                        var razon_social = $('#razon_social').val();
                        var cp = $('#cp').val();
                        var colonia = $('#colonia').val();
                        var calle = $('#calle').val();
                        var exterior = $('#exterior').val();
                        var interior = $('#interior').val();
                        var telefono = $('#telefono').val();
                        var email = $('#email').val();
                        var municipio_id = $('#municipio_id').val();


                        if(rfc==''){
                            alert('RFC Requerido');
                            return false;
                        }
                        if(razon_social==''){
                            alert('Razon Social Requerido');
                            return false;
                        }
                        if(cp==''){
                            alert('CP Requerido');
                            return false;
                        }

                        $.post("index_ajax.php?seccion=factura&accion=guarda_cliente&session_id"+session_id, {
                            rfc: rfc, razon_social: razon_social, cp: cp, colonia: colonia,
                            calle: calle, exterior:exterior, interior: interior, telefono: telefono,
                            email: email, municipio_id: municipio_id, status: 1
                            },
                            function (data) {
                                $('#agrega_cliente').show('slow');
                                $("#alta_cliente").hide('slow');
                                $('#siguiente').show('slow');

                                var mensaje = data['mensaje'];
                                var error = data['error'];
                                var cliente_id = data['registro_id'];

                                if(error == false){
                                    alert(mensaje);
                                    $.post("index_ajax.php?seccion=factura&accion=obten_clientes&session_id="+session_id+"&cliente_id="+cliente_id, {},
                                        function (data) {
                                            $('#cliente-selector').empty();
                                            $('#cliente-selector').append(data);
                                            $('.cliente_id').selectpicker('refresh');

                                        });
                                }
                            });
                    });

                    $('#cancela_cliente').click(function () {
                        $('#agrega_cliente').show('slow');

                        $("#alta_cliente").hide();
                        $('#alta_cliente').empty();
                        $('#siguiente').show('slow');
                    });

                });
        });
    }

    if(seccion == 'factura' && accion == 'lista'){
        $('#fecha').change(function (){
            var fecha = $(this).val();
            var rfc = $("#rfc").val();
            var razon_social = $("#razon_social").val();
            var status_factura = $("#status_factura").val();
            var status_descarga = $("#status_descarga").val();
            $.ajax({
                url: "./index_ajax.php?seccion=factura&accion=lista_filtro&session_id="+session_id,
                type: "POST", //send it through get method
                data: {
                    fecha: fecha,
                    razon_social: razon_social,
                    rfc:rfc,
                    status_factura:status_factura,
                    status_descarga:status_descarga
                },
                success: function(data) {
                    $("#contenido").empty();
                    $("#contenido").append(data);
                },
                error: function(xhr, status) {
                    //Do Something to handle error
                    //alert("no insertado correctamente");
                }
            });
        });

        $('#rfc').keyup(function (){
            var fecha = $("#fecha").val();
            var rfc = $(this).val();
            var razon_social = $("#razon_social").val();
            var status_factura = $("#status_factura").val();
            var status_descarga = $("#status_descarga").val();
            $.ajax({
                url: "./index_ajax.php?seccion=factura&accion=lista_filtro&session_id="+session_id,
                type: "POST", //send it through get method
                data: {
                    fecha: fecha,
                    razon_social: razon_social,
                    rfc: rfc,
                    status_factura: status_factura,
                    status_descarga:status_descarga
                },
                success: function(data) {
                    $("#contenido").empty();
                    $("#contenido").append(data);
                },
                error: function(xhr, status) {
                    //Do Something to handle error
                    //alert("no insertado correctamente");
                }
            });
        });


        $('#razon_social').keyup(function (){
            var fecha = $("#fecha").val();
            var rfc = $("#rfc").val();
            var razon_social = $(this).val();
            var status_factura = $("#status_factura").val();
            var status_descarga = $("#status_descarga").val();
            $.ajax({
                url: "./index_ajax.php?seccion=factura&accion=lista_filtro&session_id="+session_id,
                type: "POST", //send it through get method
                data: {
                    fecha: fecha,
                    rfc: rfc,
                    razon_social: razon_social,
                    status_factura:status_factura,
                    status_descarga:status_descarga
                },
                success: function(data) {
                    $("#contenido").empty();
                    $("#contenido").append(data);
                },
                error: function(xhr, status) {
                    //Do Something to handle error
                    //alert("no insertado correctamente");
                }
            });
        });

        $('#status_factura').change(function (){
            var fecha = $("#fecha").val();
            var rfc = $("#rfc").val();
            var razon_social = $("#razon_social").val();
            var status_factura = $(this).val();
            var status_descarga = $("#status_descarga").val();
            $.ajax({
                url: "./index_ajax.php?seccion=factura&accion=lista_filtro&session_id="+session_id,
                type: "POST", //send it through get method
                data: {
                    fecha: fecha,
                    rfc: rfc,
                    razon_social: razon_social,
                    status_factura:status_factura,
                    status_descarga:status_descarga
                },
                success: function(data) {
                    $("#contenido").empty();
                    $("#contenido").append(data);
                },
                error: function(xhr, status) {
                    //Do Something to handle error
                    //alert("no insertado correctamente");
                }
            });
        });


        $('#status_descarga').change(function (){
            var fecha = $("#fecha").val();
            var rfc = $("#rfc").val();
            var razon_social = $("#razon_social").val();
            var status_factura = $("#status_factura").val();
            var status_descarga = $(this).val();
            $.ajax({
                url: "./index_ajax.php?seccion=factura&accion=lista_filtro&session_id="+session_id,
                type: "POST", //send it through get method
                data: {
                    fecha: fecha,
                    rfc: rfc,
                    razon_social: razon_social,
                    status_factura:status_factura,
                    status_descarga:status_descarga
                },
                success: function(data) {
                    $("#contenido").empty();
                    $("#contenido").append(data);
                },
                error: function(xhr, status) {
                    //Do Something to handle error
                    //alert("no insertado correctamente");
                }
            });
        });

        $('#descarga').click(function () {
            var time = 1000;
            $( ".factura" ).each(function( index ) {
                var factura_id = $(this).val();
                if( $(this).prop('checked') ) {
                    time = time + 1000;
                    var funcion_abre = "open_window('./index.php?seccion=cliente&accion=descarga_factura_pdf&session_id="+session_id+"&factura_id="+factura_id+"');";
                    window.setTimeout(funcion_abre , time );
                    time = time + 1000;
                    var funcion_abre = "open_window('./index.php?seccion=cliente&accion=descarga_factura_xml&session_id="+session_id+"&factura_id="+factura_id+"');";
                    window.setTimeout(funcion_abre , time );
                    time = time + 1000;
                    var funcion_abre = "open_window('./index.php?seccion=cliente&accion=informe_gastos_pdf&session_id="+session_id+"&factura_id="+factura_id+"');";
                    window.setTimeout(funcion_abre , time );
                }
            });
        });

    }



    if(seccion == 'cliente' && accion == 'genera_factura') {
        $('.cantidad').change(function () {

            ejecuta_cambio_cantidad($(this));


        });

        $('.valor_unitario').change(function () {
            ejecuta_cambio_valor_unitario($(this));
        });


        $('.impuestos-ieps-partida').hide();
        $('.impuestos-traslados-partida').hide();
        $('.impuestos-retenidos-partida').hide();



        $('.insumo_id').change(function (){
            ejecuta_cambio_insumo($(this));

        });

        $('#partida-nueva').click(function (){

            $.ajax({
                url: "./index_ajax.php?seccion=cliente&accion=partida_ajax&session_id"+session_id,
                type: "POST", //send it through get method
                data: {},
                success: function(data) {
                    $('.partidas').append(data);
                    $('.insumo_id').selectpicker('destroy');
                    $('.insumo_id').unbind('change');
                    $('.cantidad').unbind('change');
                    $('.valor_unitario').unbind('change');


                    $('.insumo_id').change(function (){
                        ejecuta_cambio_insumo($(this));
                    });

                    $('.cantidad').change(function () {
                        ejecuta_cambio_cantidad($(this));
                    });

                    $('.valor_unitario').change(function () {
                        ejecuta_cambio_valor_unitario($(this));
                    });

                    $('.insumo_id').selectpicker('reset');
                },
                error: function(xhr, status) {
                }
            });
        });
    }


    $('.nombre_representante_legal').keyup(function (){
        var valor = $(this).val();
        if(valor != ''){
            $(this).removeClass('error_input');
        }
        else{
            $(this).addClass('error_input');
        }
    });

    $('.pagina_web').keyup(function (){
        var valor = $(this).val();
        if(valor != ''){
            $(this).removeClass('error_input');
        }
        else{
            $(this).addClass('error_input');
        }
    });

    $('.cp').keyup(function (){
        var valor = $(this).val();
        if(valor != ''){
            $(this).removeClass('error_input');
        }
        else{
            $(this).addClass('error_input');
        }
    });



    $( ".nombre_representante_legal" ).each(function( index ) {
        var valor = $(this).val();
        if(valor == ''){
            $(this).addClass('error_input');
        }
    });

    $( ".pagina_web" ).each(function( index ) {
        var valor = $(this).val();
        if(valor == ''){
            $(this).addClass('error_input');
        }
    });

    $( ".cp" ).each(function( index ) {
        var valor = $(this).val();
        if(valor == ''){
            $(this).addClass('error_input');
        }
    });



    $('.busca_registros').keyup(function (){
        var url = new URL(window.location.href);
        var seccion = url.searchParams.get("seccion");
        var valor_consulta = $(this).val();
        $.ajax({
            url: "./index_ajax.php?seccion="+seccion+"&accion=lista_ajax&session_id="+session_id,
            type: "POST", //send it through get method
            data: {valor: valor_consulta},
            success: function(data) {
                $('#contenido_lista').empty();
                $('#contenido_lista').append(data);

                $('.desactiva').unbind('click');

                $('.desactiva').click(function (){
                    activa_desactiva($(this), 'desactivar', session_id);
                });

                $('.activa').unbind('click');

                $('.activa').click(function (){
                    activa_desactiva($(this), 'activar', session_id);
                });

                $('.elimina').click(function (){
                    elimina($(this), session_id);
                });
            },
            error: function(xhr, status) {
                //Do Something to handle error
                //alert("no insertado correctamente");
            }
        });
    });


    $('.agrega_accion_bd').on("click", function () {
        agrega_accion_bd($(this), session_id);
    });

    $('.actualiza_cliente').on("click", function () {
        var elemento = $(this);
        var cliente_id = elemento.parents().siblings('.cliente_id').val();
        var rfc_envio = elemento.parents().parents().siblings('.td_rfc').children('.rfc').val();
        var cp_envio = elemento.parents().parents().siblings('.td_cp').children('.cp').val();
        var nombre_representante_legal_envio = elemento.parents().parents().siblings('.td_nombre_representante_legal').children('.nombre_representante_legal').val();
        var pagina_web_envio = elemento.parents().parents().siblings('.td_pagina_web').children('.pagina_web').val();

        $.ajax({
            url: "./index_ajax.php?seccion=cliente&accion=actualiza_masiva_bd&session_id="+session_id+"&cliente_id="+cliente_id,
            type: "POST", //send it through get method
            data: {
                rfc: rfc_envio, cp: cp_envio,
                nombre_representante_legal: nombre_representante_legal_envio,
                pagina_web: pagina_web_envio
            },
            success: function(data) {
                alert('Actualizado con éxito');
            },
            error: function(xhr, status) {
                //Do Something to handle error
                //alert("no insertado correctamente");
            }
        });
    });


    $('.busca_elemento').keyup(function (){
        var valor_consulta = $(this).val().toUpperCase();
        $(".elemento_accion").each(function (index) {
            var contenido = $(this).html().toUpperCase();
            var existe = contenido.indexOf(valor_consulta);
            if(existe > -1){
                $(this).show('slow');
            }
            else{
                $(this).hide('slow');
            }
        });
        $(".panel").each(function (index)
        {
            var contenido = $(this).html().toUpperCase();

            var existe = contenido.indexOf(valor_consulta);

            if(existe > -1){
                $(this).show('slow');
            }
            else{
                $(this).hide('slow');
            }
        });

    });

    $('.elimina_accion_bd').on("click", function () {
        elimina_accion_bd($(this));
    });


    $('.ve_tipo_cambio').click(function (){
        var moneda_id_enviar = $(this).children('.moneda_id').val();

        $(".modal-title").empty();
        $(".modal-title").append('Moneda Id: '+moneda_id_enviar);

        $.ajax({
            url: "./index_ajax.php?seccion=moneda&accion=obten_tipo_cambio&session_id="+session_id,
            type: "POST", //send it through get method
            data: {moneda_id: moneda_id_enviar},
            success: function(data) {
                $('.modal-body').empty();
                $('.modal-body').append(data);
            },
            error: function(xhr, status) {
                //Do Something to handle error
                //alert("no insertado correctamente");
            }
        });
    });
    $('.navbar a.dropdown-toggle').unbind('click');

    $('.navbar a.dropdown-toggle').on('click', function(e) {
        var $el = $(this);
        var $parent = $(this).offsetParent(".dropdown-menu");
        $(this).parent("li").toggleClass('open');

        if(!$parent.parent().hasClass('nav')) {
            $el.next().css({"top": $el[0].offsetTop, "left": $parent.outerWidth() - 4});
        }
        $('.nav li.open').not($(this).parents("li")).removeClass("open");
        return false;
    });


    $('.elimina').click(function (){
        elimina($(this), session_id);
    });

    $('.desactiva').click(function (){
        activa_desactiva($(this), 'desactivar', session_id);
    });

    $('.activa').click(function (){
        activa_desactiva($(this), 'activar', session_id);
    });

    if ($("#select_pais").length) {

        if ($("#contenedor_select_estado").length) {
            $('#select_pais').change(function (){
                var id = $(this).val();
                genera_option($(this),'estado','pais_id',$('.estado_id'),'','');
            });
        }
    }

    if ($("#select_estado").length) {

        if ($("#contenedor_select_municipio").length) {
            $('#select_estado').change(function (){
                var id = $(this).val();
                genera_option($(this),'municipio','estado_id',$('.municipio_id'),'','');
            });
        }
    }

    if ($("#producto_sat_id").length) {
        $('#producto_sat_id').keyup(function (){
            var descripcion_busqueda = $(this).val();
            var url_ejecucion = "./index_ajax.php?seccion=insumo&accion=genera_lista_producto_sat&session_id="+session_id;
            $("#producto_sat_datos").empty();
            $("#producto_sat_datos" ).load( url_ejecucion ,{valor:descripcion_busqueda}, function() {
                $(".producto_sat_id").unbind('click');
                $('.producto_sat_id').click(function (){
                    $('.producto_sat_id').parent().hide();
                    $(this).parent().show();
                });
            });
        });
    }

    if ($("#select_grupo_insumo").length) {

        if ($("#contenedor_select_tipo_insumo").length) {

            $('#select_grupo_insumo').change(function () {
                genera_option($(this),'tipo_insumo','grupo_insumo_id',$('#select_tipo_insumo'),'','tipo_insumo_descripcion');
            });
        }
    }

    if ($(".btn_impuestos").length) {
        $('.btn_impuestos').click(function (){

            $.ajax({
                url: "./index_ajax.php?seccion=insumo&accion=carga_registro_lista&session_id="+session_id,
                type: "POST", //send it through get method
                data: {},
                success: function(data) {
                    $('.contenedor_registros').append(data);
                    $(".btn_elimina_registro_lista").unbind('click');
                    $('.btn_elimina_registro_lista').click(function (){
                        var elemento = $(this).parent();
                        elemento.empty();
                    });
                },
                error: function(xhr, status) {
                    //Do Something to handle error
                    //alert("no insertado correctamente");
                }
            });

        });
    }

    if ($(".btn_elimina_registro_lista").length) {
        $('.btn_elimina_registro_lista').click(function (){
            var elemento = $(this).parent();
            elemento.empty();
        });
    }

    if(seccion == 'insumo'){
        if(accion == 'alta'){
            $('.btn-signin').click(function () {
                var impuesto_retenido_id = $('#select_impuesto_retenido').val();
                if(impuesto_retenido_id!=''){
                    var tipo_factor_retenido_id = $('#select_tipo_factor_retenido').val();
                    var factor = $('#factor_retenido').val();
                    if(tipo_factor_retenido_id == ''){
                        alert('Asigne una tasa o cuota de retencion');
                        $('#select_tipo_factor_retenido').focus();
                        return false;
                    }
                    if(factor == ''){
                        alert('Asigne un factor de retencion');
                        $('#factor_retenido').focus();
                        return false;
                    }
                }


                var impuesto_id = $('#select_impuesto').val();
                if(impuesto_id!=''){
                    var tipo_factor_id = $('#select_tipo_factor').val();
                    var factor = $('#factor').val();
                    if(tipo_factor_id == ''){
                        alert('Asigne una tasa o cuota');
                        $('#select_tipo_factor').focus();
                        return false;
                    }
                    if(factor == ''){
                        alert('Asigne un factor');
                        $('#factor').focus();
                        return false;
                    }
                }
            });
        }
    }

});
