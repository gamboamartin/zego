


function agrega_accion_bd(elemento){
    var grupo_id_enviar = elemento.children('.grupo_id').val();
    var accion_id_enviar = elemento.children('.accion_id').val();
    elemento.removeClass('agrega_accion_bd');
    elemento.addClass('btn-success ');
    elemento.unbind('click');

    elemento.click(function () {
        elimina_accion_bd(elemento);
    });

    $.ajax({
        url: "./index_ajax.php?seccion=grupo&accion=agrega_accion_bd",
        type: "POST", //send it through get method
        data: {accion_id: accion_id_enviar, grupo_id: grupo_id_enviar},
        success: function(data) {
            alert('Registro Agregado');
        },
        error: function(xhr, status) {
            //Do Something to handle error
            //alert("no insertado correctamente");
        }
    });
}

function calcula_partida(elemento_cantidad, elemento_valor_unitario,elemento_factor,importe,importe_impuesto,elemento_factor_retenido,importe_impuesto_retenido){
    var cantidad = elemento_cantidad.val();
    var valor_unitario = elemento_valor_unitario.val();
    var factor = elemento_factor.val();
    var factor_retenido = elemento_factor_retenido.val();

    var total;
    var total_impuesto;
    var total_impuesto_retenido;
    if (cantidad=='') {
        cantidad = 0;
    }
    if (valor_unitario == '') {
        valor_unitario = 0;
    }
    if(factor == ''){
        factor = 0;
    }
    if(factor_retenido == ''){
        factor_retenido = 0;
    }

    total = cantidad*valor_unitario;
    total = total.toFixed(4);


    total_impuesto = total*factor;

    total_impuesto_retenido = total*factor_retenido;


    total_impuesto = total_impuesto.toFixed(4);
    total_impuesto_retenido = total_impuesto_retenido.toFixed(4);

    importe.val(total);
    importe_impuesto.val(total_impuesto);
    importe_impuesto_retenido.val(total_impuesto_retenido);





    var sub_total_general = 0;

    if($('#importe1').val()==''){
        $('#importe1').val(0);
    }

    if($('#importe2').val()==''){
        $('#importe2').val(0);
    }

    if($('#importe3').val()==''){
        $('#importe3').val(0);
    }

    if($('#importe4').val()==''){
        $('#importe4').val(0);
    }

    sub_total_general = parseFloat($('#importe1').val())
    sub_total_general = sub_total_general + parseFloat($('#importe2').val());
    sub_total_general = sub_total_general + parseFloat($('#importe3').val());
    sub_total_general = sub_total_general + parseFloat($('#importe4').val());

    $('#subtotal').empty();
    $('#subtotal').append('Subtotal:'+sub_total_general);


    var impuesto_general = 0;

    if($('#importe_impuesto1').val()==''){
        $('#importe_impuesto1').val(0);
    }

    if($('#importe_impuesto2').val()==''){
        $('#importe_impuesto2').val(0);
    }

    if($('#importe_impuesto3').val()==''){
        $('#importe_impuesto3').val(0);
    }

    if($('#importe_impuesto4').val()==''){
        $('#importe_impuesto4').val(0);
    }


    var impuesto_general_retenido = 0;


    if($('#importe_impuesto_retenido1').val()==''){
        $('#importe_impuesto_retenido1').val(0);
    }

    if($('#importe_impuesto_retenido2').val()==''){
        $('#importe_impuesto_retenido2').val(0);
    }

    if($('#importe_impuesto_retenido3').val()==''){
        $('#importe_impuesto_retenido3').val(0);
    }

    if($('#importe_impuesto_retenido4').val()==''){
        $('#importe_impuesto_retenido4').val(0);
    }

    impuesto_general = parseFloat($('#importe_impuesto1').val())
    impuesto_general = impuesto_general + parseFloat($('#importe_impuesto2').val());
    impuesto_general = impuesto_general + parseFloat($('#importe_impuesto3').val());
    impuesto_general = impuesto_general + parseFloat($('#importe_impuesto4').val());

    impuesto_general_retenido = parseFloat($('#importe_impuesto_retenido1').val())



    impuesto_general_retenido = impuesto_general_retenido + parseFloat($('#importe_impuesto_retenido2').val());


    impuesto_general_retenido = impuesto_general_retenido + parseFloat($('#importe_impuesto_retenido3').val());
    impuesto_general_retenido = impuesto_general_retenido + parseFloat($('#importe_impuesto_retenido4').val());



    $('#impuestos').empty();
    $('#impuestos').append('Impuestos trasladados:'+impuesto_general);

    $('#impuestos_retenidos').empty();
    $('#impuestos_retenidos').append('Impuestos Retenidos:'+impuesto_general_retenido);

    var total_general = (impuesto_general + sub_total_general)-impuesto_general_retenido;

    $('#total').empty();
    $('#total').append('Total:'+total_general);

}

function genera_option(elemento,tabla,campo,elemento_update,selected,campo_resultado){
    var id = elemento.val();
    var url = genera_parametro_select(elemento,tabla,id,campo,selected,campo_resultado);
    console.log(url);

    $.ajax({
        url: url,
        type: "POST", //send it through get method
        data: {},
        success: function (data) {
            elemento_update.selectpicker('destroy');
            elemento_update.empty();
            elemento_update.append(data);
            elemento_update
                .html(data)
                .selectpicker('refresh');
        },
        error: function (xhr, status) {

        }
    });
}

function obten_dato_registro(elemento,id,tabla,campo){
    var url = "./index_ajax.php?seccion="+tabla+"&accion=obten_dato_registro&registro_id="+id+"&campo="+campo;
    $.ajax({
        url: url,
        type: "POST", //send it through get method
        data: {},
        success: function (data) {
            elemento.empty();
            elemento.val(data);
        },
        error: function (xhr, status) {

        }
    });
}

function genera_parametro_select(elemento,seccion_select,valor_filtro,campo_filtro,selected,campo_resultado){
    var get_selected = '';
    var campo_resultado_url = '';
    if(selected=='selected'){
        get_selected = '&selected=selected';
    }
    if(campo_resultado!=''){
        campo_resultado_url = '&campo_resultado='+campo_resultado;
    }
    var url_ejecucion = "./index_ajax.php?seccion="+seccion_select+"&accion=option_selected&valor_filtro="+valor_filtro+"&campo_filtro="+campo_filtro+get_selected+campo_resultado_url;

    return url_ejecucion;
}
function obten_elemento_status(elemento){
    var elemento_status = elemento.parents().parents().children('.panel-body').children('.tag_status').children('.resultado-status');
    return elemento_status;
}
function elimina_accion_bd(elemento){
    var accion_grupo_id_enviar = elemento.children('.accion_grupo_id').val();
    var grupo_id_enviar = elemento.children('.grupo_id').val();
    var accion_id_enviar = elemento.children('.accion_id').val();

    elemento.removeClass('btn-success elimina_accion_bd');
    elemento.unbind('click');

    elemento.click(function () {
        agrega_accion_bd(elemento);
    });

    $.ajax({
        url: "./index_ajax.php?seccion=grupo&accion=elimina_accion_bd",
        type: "POST", //send it through get method
        data: {
            accion_grupo_id: accion_grupo_id_enviar,
            grupo_id:grupo_id_enviar,
            accion_id:accion_id_enviar},
        success: function(data) {
            alert('Registro Eliminado');
        },
        error: function(xhr, status) {
            //Do Something to handle error
            //alert("no insertado correctamente");
        }
    });
}
function elimina(elemento){
    var registro_id = elemento.parent().parent().find('.registro_id').html();
    var container = elemento.parent().parent().parent();
    var url = new URL(window.location.href);
    var seccion = url.searchParams.get("seccion");
    var valor_consulta = elemento.val();
    var result = confirm("Estás seguro de eliminar el registro ?");
    var url_ejecucion = "./index_ajax.php?seccion="+seccion+"&accion=elimina_bd&registro_id="+registro_id;
    if(result == true){
        $.ajax({
            url: url_ejecucion,
            type: "POST", //send it through get method
            data: {valor: valor_consulta},
            success: function() {
                container.remove();
                alert('Registro eliminado con éxito');
            },
            error: function() {
                alert('Error: '+url_ejecucion);
            }
        });
    }
}
function activa_desactiva(elemento, accion){
    var etiqueta_alert = '';
    var accion_url = '';
    var etiqueta_nuevo_status = '';
    var tipo_panel_nuevo = '';
    var tipo_panel_anterior = '';
    var icono = '';
    if(accion == 'desactivar'){
        etiqueta_alert = 'desactivar';
        accion_url = 'desactiva_bd';
        etiqueta_nuevo_status = 'Inactivo';
        tipo_panel_nuevo = 'panel-danger';
        tipo_panel_anterior = 'panel-info';
        icono = 'glyphicon glyphicon-ok';
    }
    else{
        if(accion == 'activar'){
            etiqueta_alert = 'activar';
            accion_url = 'activa_bd';
            etiqueta_nuevo_status = 'Activo';
            tipo_panel_nuevo = 'panel-info';
            tipo_panel_anterior = 'panel-danger';
            icono = 'glyphicon glyphicon-minus';
        }
    }
    var registro_id = elemento.parent().parent().find('.registro_id').html();
    var url = new URL(window.location.href);
    var seccion = url.searchParams.get("seccion");
    var valor_consulta = $('.busca_registros').val();
    var result = confirm("Estás seguro de "+etiqueta_alert+" el registro ?");
    var url_ejecucion = "./index_ajax.php?seccion="+seccion+"&accion="+accion_url+"&registro_id="+registro_id;
    if(result == true){
        $.ajax({
            url: url_ejecucion,
            type: "POST", //send it through get method
            data: {valor: valor_consulta},
            success: function() {
                elemento.parent().parent().removeClass(tipo_panel_anterior);
                elemento.parent().parent().addClass(tipo_panel_nuevo);
                elemento.empty();
                elemento.append("<span class='"+icono+"' aria-hidden='true'></span>");
                var resultado_status = obten_elemento_status(elemento);
                resultado_status.empty();
                resultado_status.append(etiqueta_nuevo_status);
                elemento.unbind('click');
                if(accion == 'activar'){
                    alert('Registro activado con éxito');
                    elemento.click(function () {
                        activa_desactiva(elemento,'desactivar');
                    });
                }
                else{
                    if(accion=='desactivar'){
                        alert('Registro desactivado con éxito');
                        elemento.click(function () {
                            activa_desactiva(elemento,'activar');
                        });
                    }
                }
            },
            error: function() {
                alert('Error: '+ url_ejecucion);
            }
        });
    }
}
