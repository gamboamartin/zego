<form action="index.php?seccion=nota_credito&accion=guarda_session_nota_credito&session_id=<?php echo SESSION_ID; ?>" method="POST">
<div class="row">
<?php
echo $directiva->input_select('cliente',false,3,false,false,false,$controlador->link,'required',false);
echo $directiva->input_select('forma_pago',false,3,false,false,false,$controlador->link,'required',false);
echo $directiva->genera_input_text('monto',3,false,'required',false,false,false,false);
echo $directiva->genera_input_text('porcentaje_iva',3,false,'required',false,false,false,false);
?>
</div>
<div class="row">
    <?php
    echo $directiva->input_select('moneda',100,3,false,false,false,$controlador->link,'required',false);
    echo $directiva->genera_input_text('tipo_cambio',3,1,'required',false,false,false,false);
    echo $directiva->fecha(date('Y-m-d'),3,'fecha');
    echo $directiva->genera_input_text('total',3,false,'required',false,false,false,'disabled');

?>
</div>
    <div class="row">
        <?php
        echo $directiva->genera_input_text('folio',3,false,'required',false,false,false,false);
        ?>
<div class="row">
    <?php
    echo $directiva->btn_enviar(12,'Guarda Nota de Credito','guarda-nota_credito');
    ?>
</div>
</form>
