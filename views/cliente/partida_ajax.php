<?php
$html = "<div class='row'>";
    $html = $html.$directiva->input_select('insumo',false,3,false,'[]',false,$controlador->link,'required',false);
    $html = $html.$directiva->genera_input_text('cantidad',1,false,'required',false,'[]',false,false);
    $html = $html.$directiva->genera_input_text('valor_unitario',2,false,'required',false,'[]',false,false);
    $html = $html.$directiva->genera_input_text('descripcion',4,false,'required',false,'[]',false,false);
    $html = $html.$directiva->genera_input_text('sub_total',2,false,false,false,false,false,'disabled');
    $html = $html."<input type='hidden' name='factor_traslado' class='factor-traslado-valor' value='0'>
    <input type='hidden' name='factor_retenido' class='factor-retenido-valor' value='0'>
    <input type='hidden' name='factor_ieps' class='factor-ieps-valor' value='0'>
    <div class='row col-md-12 datos-insumo'>
        <div class='row col-md-12 datos-insumo-partida'>
            <label>Unidad de Medida:</label>
            <span class='unidad-medida'></span>
            <label>Codigo de Unidad:</label>
            <span class='unidad-codigo'></span>
            <label>Concepto SAT:</label>
            <span class='concepto-sat'></span>
            <label>Codigo SAT:</label>
            <span class='codigo-sat'></span>
        </div>
        
        <div class='row col-md-12 impuestos-ieps-partida'>
           <label>Codigo IEPS:</label>
           <span class='codigo-impuesto-ieps'></span>
           <label>Descripcion Impuesto:</label>
           <span class='impuesto-ieps'></span>
           <label>Factor ieps:</label>
           <span class='factor-ieps'></span>
           <label>Monto Base ieps:</label>
           <span class='monto-base-ieps'></span>
           <label>Monto Impuesto ieps:</label>
           <span class='monto-impuesto-ieps'></span>
           <input type='hidden' class='monto-impuesto-ieps-partida' value='0'>
           
        </div>
        
        <div class='row col-md-12 impuestos-traslados-partida'>
           <label>Codigo Impuesto traslado:</label>
              <span class='codigo-impuesto-traslado'></span>
                        <label>Impuesto traslado:</label>
                        <span class='impuesto-traslado'></span>
                        <label>Factor traslado:</label>
                        <span class='factor-traslado'></span>
                        <label>Monto Base Traslado:</label>
                        <span class='monto-base-traslado'></span>
                        <label>Monto Impuesto Trasladado:</label>
                        <span class='monto-impuesto-trasladado'></span>
                        <input type='hidden' class='monto-impuesto-traslado-partida' value='0'>
                        
        </div>
        
        <div class='row col-md-12 impuestos-retenidos-partida'>
                        <label>Codigo Impuesto retenido:</label>
                        <span class='codigo-impuesto-retenido'></span>
                        <label>Impuesto retenido:</label>
                        <span class='impuesto-retenido'></span>
                        <label>Factor retenido:</label>
                        <span class='factor-retenido'></span>
                        <label>Monto Base Retenido:</label>
                        <span class='monto-base-retenido'></span>
                        <label>Monto Impuesto Retenido:</label>
                        <span class='monto-impuesto-retenido'></span>
                        <input type='hidden' class='monto-impuesto-retenido-partida' value='0'>
                        <hr>
                    </div>
        
    </div>
</div>";

    echo $html;