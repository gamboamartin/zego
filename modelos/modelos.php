<?php
namespace models;
use base\consultas_base;
use gamboamartin\errores\errores;
use gamboamartin\services\services;
use gamboamartin\validacion\validacion;
use stdClass;
use Throwable;

class modelos{
    public $tabla;
    public $foraneas_no_insertables;
    public $link;
    protected errores $error;
    public int $registro_id;

    public function __construct($link){
        $this->error = new errores();
        $this->link = $link;
        /*Aqui se declaran los campos que no seran insertados ni modificados en los
        formularios de alta u modifica*/

        $cliente = array('pais_id','estado_id');
        $partida_factura = array('importe_impuesto','importe_impuesto_retenido');
        $municipio = array('pais_id');
        $insumo = array('producto_sat','grupo_insumo_id');
        $this->foraneas_no_insertables = array(
            'cliente'=>$cliente,'municipio'=>$municipio,'insumo'=>$insumo,
            'partida_factura'=>$partida_factura);
    }

    /**
     * ERROR UNIT
     * @param string $tabla
     * @param int $id
     * @return array
     */
    public function activa_bd(string $tabla, int $id): array
    {
        $tabla = trim($tabla);
        if($tabla===''){
            return $this->error->error('Error la tabla no puede venir vacia', $tabla);
        }
        try {
            $this->link->query("UPDATE $tabla SET status = '1' WHERE id = $id");
        }
        catch (Throwable $e){
            return $this->error->error('Error al ejecutar sql', array($e, $this->link->error));
        }
        $registro_id = $id;
        return array(
                'mensaje'=>'Registro activado con éxito', 'error'=>False, 'registro_id'=>$registro_id);

    }

    /**
     *  ERROR
     * @param $registro
     * @param $tabla
     * @return array
     */
    public function alta_bd($registro, $tabla){
        $campos = "";
        $valores = "";
        $campos_no_insertables = array();
        if(array_key_exists($tabla,$this->foraneas_no_insertables)){
            $campos_no_insertables = $this->foraneas_no_insertables[$tabla];
        }

        foreach ($registro as $campo => $value) {
            if($campo == 'status'){
                if($value == '1'){
                    $value = 1;
                }
                else{
                    $value = 0;
                }
            }
            if($campo == 'visible'){
                if($value == 1){
                    $value = 1;
                }
                else{
                    $value = 0;
                }
            }

            if($campo == 'inicio'){
                if($value == 1){
                    $value = 1;
                }
                else{
                    $value = 0;
                }
            }


            $campo = addslashes($campo);


            if(!in_array($campo,$campos_no_insertables)) {
                if(is_null($value)){
                    $value = '';
                }
                $value = addslashes($value);
                $campos .= $campos === "" ? "$campo" : ",$campo";
                $valores .= $valores === "" ? "'$value'" : ",'$value'";
            }
        }
        $consulta_insercion = "INSERT INTO ". $tabla." (".$campos.") VALUES (".$valores.")";


        try {
            $this->link->query($consulta_insercion);
        }
        catch (Throwable $e){
            return $this->error->error('Error al insertar registro', $e);
        }

        $registro_id = $this->link->insert_id;
        $this->registro_id = (int)$registro_id;
        return array(
                'mensaje'=>'Registro insertado con éxito', 'error'=>False, 'registro_id'=>$registro_id);

    }

    /**
     * ERROR UNIT
     * @param string $campo
     * @param array $row
     * @return array
     */
    private function asigna_0_to_vacio(string $campo, array $row): array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error('Error campo esta vacio', $campo);
        }
        $row = $this->limpia_campo_row_inexistente(campo: $campo, row: $row);
        if(errores::$error){
            return $this->error->error('Error al limpiar registro', $row,get_defined_vars());
        }
        if($row[$campo] === ''){
            $row[$campo] = 0;
        }
        return $row;
    }

    public function asigna_ceros_row(array $campos, array $registro): array
    {
        foreach ($campos as $campo){
            $campo = trim($campo);

            $registro = $this->asigna_0_to_vacio(campo:$campo , row: $registro);
            if(errores::$error){
                return $this->error->error('Error al limpiar registro', $registro,get_defined_vars());
            }
        }

        return $registro;
    }

    public function desactiva_bd($tabla, $id){
        $this->link->query("UPDATE $tabla SET status = '0' WHERE id = $id");
        if($this->link->error){
            return array('mensaje'=>'Error al desactivar', 'error'=>True);
        }
        else{
            $registro_id = $id;
            return array(
                'mensaje'=>'Registro desactivado con éxito', 'error'=>False, 'registro_id'=>$registro_id);
        }
    }

    /**
     * ERROR UNIT
     * @param $consulta
     * @return array
     */
    protected function ejecuta_consulta($consulta): array
    {
        $consulta = trim($consulta);
        if($consulta === ''){
            return $this->error->error('Error la consulta no puede venir vacia', $consulta);
        }
        try {
            $result = $this->link->query($consulta);
        }
        catch (Throwable $e){
            $data = new stdClass();
            $data->exception = $e;
            $data->sql = $consulta;
            $data->error_mysql = $this->link->error;
            return $this->error->error('Error al ejecutar sql', $data);
        }

        $n_registros = $result->num_rows;

        $new_array = array();
        while( $row = mysqli_fetch_assoc( $result)){
            $new_array[] = $row;
        }
        return array('registros' => $new_array, 'n_registros' => $n_registros);

    }

    /**
     * ERROR UNIT
     * @param string $tabla
     * @param int $id
     * @return array
     */
    public function elimina_bd(string $tabla, int $id): array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error('Error la tabla no puede venir vacia', $tabla);
        }

        $consulta = "DELETE FROM ".$tabla. " WHERE id = ".$id;

        try {
            $this->link->query($consulta);
        }
        catch (Throwable $e){
            return $this->error->error('Error al eliminar', $e);

        }
        $registro_id = $this->link->insert_id;
        return array(
            'mensaje'=>'Registro eliminado con éxito', 'error'=>false, 'registro_id'=>$registro_id);

    }

    public function elimina_con_filtro_and($tabla, $filtro){
        $sentencia = $this->genera_and($filtro);
        $consulta = "DELETE FROM $tabla WHERE $sentencia";

        $this->link->query($consulta);
        if($this->link->error){
            return array('mensaje'=>$this->link->error, 'error'=>True);
        }
        else{
            return array('mensaje'=>'Registro eliminado con éxito', 'error'=>False);
        }
    }

    public function existe_por_id(int $id, string $tabla): bool|array
    {

        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error('Error la tabla esta vacia', $tabla);
        }


        $sql = "SELECT COUNT(*) AS n_rows FROM $tabla WHERE id = $id";
        $result = $this->ejecuta_consulta($sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registros', data: $result);
        }
        $existe = false;
        if((int)$result['registros'][0]['n_rows']>0){
            $existe = true;
        }


        return $existe;
    }

    public function sql_filtro_inserta(string $fecha, int $insertado, string $tabla): string
    {
        $sql_fecha_alta = "$tabla.fecha_alta >= '$fecha'";
        $sql_fecha_alta .= " AND $tabla.insertado = $insertado ";
        return $sql_fecha_alta;
    }

    public function sql_filtro_insertado(int $insertado, int $n_dias, services $services, string $tabla, string $tipo_val): array|string
    {
        $fecha = $services->get_fecha_filtro_service(n_dias: $n_dias, tipo_val: $tipo_val);
        if(errores::$error){
            return $this->error->error('Error al obtener fecha', $fecha);
        }

        $sql_fecha_alta = $this->sql_filtro_inserta(fecha: $fecha,insertado:  $insertado,tabla: $tabla);
        if(errores::$error){
            return $this->error->error('Error al obtener filtro', $sql_fecha_alta);
        }
        return $sql_fecha_alta;
    }

    public function registros_sin_insertar(int $limit, int $n_dias, services $services, string $tabla){
        $sql_fecha_alta = $this->sql_filtro_insertado(insertado: 0, n_dias:$n_dias,services:  $services, tabla: $tabla,
            tipo_val:  'fecha_hora_min_sec_esp');
        if(errores::$error){
            return $this->error->error('Error al obtener filtro', $sql_fecha_alta);
        }

        $r_ = $this->registros_puros(limit:$limit, tabla: $tabla, where: $sql_fecha_alta);
        if(errores::$error){
            return $this->error->error('Error al obtener registros', $r_);
        }

        return $r_['registros'];
    }

    public function inserta_en_service_local(array $keys, array $registro, string $tabla): array
    {
        $r_ins_local = array();
        $inserta = $this->inserta_local($keys, $registro);
        if(errores::$error){
            return $this->error->error('Error al verificar si inserta', $inserta);

        }
        if($inserta){
            $r_ins_local = $this->alta_bd($registro, $tabla);
            if(errores::$error){
                return $this->error->error('Error al insertar en local', $r_ins_local);
            }
        }
        return $r_ins_local;
    }

    public function inserta_row_service(int $id, array $keys, array $registro, string $tabla): array
    {
        $inserta = array();
        $existe = $this->existe_por_id($id,$tabla);
        if(errores::$error){
            return $this->error->error('Error al verificar si existe', $existe);
        }
        if(!$existe){

            $inserta = $this->inserta_en_service_local($keys, $registro, $tabla);
            if(errores::$error){
                return $this->error->error('Error al verificar si inserta', $inserta);
            }


        }
        return $inserta;
    }

    public function inserta_local(array $keys, array $registro): bool
    {
        $inserta = true;
        foreach($keys as $key){
            $value = trim($registro[$key]);
            if($value === ''){
                $inserta = false;
            }
        }
        return $inserta;
    }

    /**
     * ERROR UNIT
     * @param array $keys
     * @param array $registro
     * @return bool|array
     */
    protected function existe_algun_valor(array $keys, array $registro): bool|array
    {
        $existe_valor = false;
        foreach ($keys as $key){
            $existe_valor = $this->existe_valor(key: $key,registro:  $registro);
            if(errores::$error){
                return $this->error->error('Error al validar si existe valor', $existe_valor);
            }
            if($existe_valor){
                break;
            }
        }
        return $existe_valor;
    }

    /**
     * ERROR UNIT
     * @param string $key
     * @param array $registro
     * @return bool
     */
    private function existe_valor(string $key, array $registro): bool
    {
        $existe = false;
        if(isset($registro[$key])){
            $value = trim($registro[$key]);
            if($value !==''){
                $existe = true;
            }

        }
        return $existe;
    }

    public function filtra_campos_base($valor, $tabla){
        $valor = addslashes($valor);
        $consultas_base = new consultas_base();
        $where = $consultas_base->genera_filtro_base($tabla, $valor);
        $consulta_base = $this->genera_consulta_base($tabla);
        $consulta = $consulta_base.$where;

        $result = $this->ejecuta_consulta($consulta);
        return $result;
    }

    /**
     * ERROR UNIT
     * @param $tabla
     * @param $filtros
     * @param string $sql
     * @return array
     */
    public function filtro_and($tabla, $filtros, string $sql = ''): array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error('Error la tabla esta vacia', $tabla);
        }
        if(count($filtros) === 0){
            return $this->error->error('Error el $filtros esta vacio', $filtros);
        }
        $sentencia = "";
        foreach ($filtros as $key => $value) {
            $key = addslashes($key);
            $value = addslashes($value);
            $sentencia .= $sentencia === ""?"$key = '$value'":" AND $key = '$value'";
        }

        $consulta = $this->genera_consulta_base($tabla);
        if(errores::$error){
            return $this->error->error('Error al generar consulta', $consulta);
        }

        $where = " WHERE $sentencia $sql";
        $consulta .= $where;


        $result = $this->ejecuta_consulta($consulta);
        if(errores::$error){
            return $this->error->error('Error al ejecutar consulta', $result);
        }
        return $result;
    }

    private function genera_and($filtros){
        $sentencia = '';
        foreach ($filtros as $key => $value) {
            $key = addslashes($key);
            $value = addslashes($value);
            $sentencia .= $sentencia == ""?"$key = '$value'":" AND $key = '$value'";
        }

        return $sentencia;

    }


    /**
     * ERROR UNIT
     * @param string $tabla
     * @param string|bool $tabla_renombrada
     * @return string|array
     */
    private function genera_columnas_consulta(string $tabla, string|bool|null $tabla_renombrada): string|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error('Error la tabla esta vacia', $tabla);
        }
        if(is_string($tabla_renombrada)){
            $tabla_renombrada = trim($tabla_renombrada);
            if($tabla_renombrada === ''){
                $tabla_renombrada = false;
            }
        }
        if(is_null($tabla_renombrada)){
            $tabla_renombrada = false;
        }
        $columnas_parseadas = $this->obten_columnas($tabla);
        if(errores::$error){
            return $this->error->error('Error al obtener columnas', $columnas_parseadas);
        }
        $columnas_sql = "";

        $consulta_base = new consultas_base();
        $subconsultas = $consulta_base->subconsultas($tabla);
        if(errores::$error){
            return $this->error->error('Error al obtener subquerys', $subconsultas);
        }

        if(is_string($tabla_renombrada) && $tabla_renombrada!==''){
            $tabla_nombre = $tabla_renombrada;
        }
        else{
            $tabla_nombre = $tabla;
        }

        foreach($columnas_parseadas as $columna_parseada){

            $columnas_sql .= $columnas_sql === ""?"$tabla_nombre.$columna_parseada AS $tabla_nombre"."_$columna_parseada":",$tabla_nombre.$columna_parseada AS $tabla_nombre"."_$columna_parseada";
        }
        if($subconsultas){
            $columnas_sql .= "," . $subconsultas;
        }
        return $columnas_sql;
    }

    /**
     * ERROR UNIT
     * @param $tabla
     * @return array|string
     */
    protected function genera_consulta_base($tabla): array|string
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error('Error la tabla esta vacia', $tabla);
        }

        $columnas = $this->obten_columnas_completas($tabla);
        if(errores::$error){
            return $this->error->error('Error al obtener columnas', $columnas);
        }
        $consulta_base = new consultas_base();
        $tablas = $consulta_base->obten_tablas_completas($tabla);
        if(errores::$error){
            return $this->error->error('Error al obtener tablas', $tablas);
        }
        return "SELECT $columnas FROM $tablas";
    }

    /**
     * ERROR UNIT
     * @param string $campo
     * @param array $row
     * @return array
     */
    protected function limpia_campo_row_inexistente(string $campo, array $row): array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error('Error campo esta vacio', $campo);
        }
        if(!isset($row[$campo])){
            $row[$campo] = '';
        }
        return $row;
    }

    /**
     * ERROR UNIT
     * @param $registro
     * @param $tabla
     * @param $id
     * @return array
     */
    public function modifica_bd($registro, $tabla, $id): array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error('Error tabla esta vacia', $tabla);
        }
        if(count($registro) === 0){
            return $this->error->error('Error el registro esta vacio', $registro);
        }
        $campos = "";

        $campos_no_insertables = array();
        if(array_key_exists($tabla,$this->foraneas_no_insertables)){
            $campos_no_insertables = $this->foraneas_no_insertables[$tabla];
        }

        $existe_status = false;
        foreach ($registro as $campo => $value) {
            if($campo === 'status'){
                $existe_status = true;
            }
            $campo = addslashes($campo);
            $value = addslashes($value);
            if(!in_array($campo, $campos_no_insertables, true)) {
                $campos .= $campos === "" ? "$campo = '$value'" : ", $campo = '$value'";
            }
        }
        if(!$existe_status){
            $campos .= " , status = '0' ";
        }

        $visible = "";
        if($tabla === 'accion'){
            if(array_key_exists('visible', $registro) && (int)$registro['visible'] === 1) {
                $visible = " , visible = '1' ";
            }
            else{
                $visible = " , visible = '0' ";
            }
        }


        $inicio = "";
        if($tabla === 'accion'){
            if(array_key_exists('inicio', $registro)){
                if((int)$registro['inicio']===1){
                    $inicio = " , inicio = '1' ";
                }
                else{
                    $inicio = " , inicio = '0' ";
                }
            }
            else{
                $inicio = " , inicio = '0' ";
            }
        }

        $consulta = "UPDATE ". $tabla." SET ".$campos." $visible $inicio WHERE id = $id";

        try {
            $this->link->query($consulta);
        }
        catch (Throwable $e){
            return $this->error->error('Error al actualizar', $e);
        }

        $registro_id = $id;
        return array(
            'mensaje'=>'Registro modificado con éxito', 'error'=>False, 'registro_id'=>$registro_id);

    }

    /**
     * ERROR UNIT
     * @param $tabla
     * @return array
     */
    private function obten_columnas($tabla): array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error('Error la tabla esta vacia', $tabla);
        }
        $consulta = "DESCRIBE $tabla";
        $result = $this->ejecuta_consulta($consulta);
        if(errores::$error){
            return $this->error->error('Error al ejecutar sql', $result);
        }
        $columnas = $result['registros'];
        $columnas_parseadas = array();
        foreach($columnas as $columna ){
            foreach($columna as $campo=>$atributo){
                if($campo == 'Field'){
                    $columnas_parseadas[] = $atributo;
                }
            }
        }
        return $columnas_parseadas;
    }

    /**
     * ERROR UNIT
     * @param $tabla
     * @return array|string
     */
    private function obten_columnas_completas($tabla): array|string
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error('Error la tabla esta vacia', $tabla);
        }
        $columnas = "";
        $consulta_base = new consultas_base();

        if(!isset($consulta_base->estructura_bd[$tabla]['columnas_select'])){
            return $this->error->error('Error no existe columna en estructura', $consulta_base->estructura_bd);
        }

        $tablas_select = $consulta_base->estructura_bd[$tabla]['columnas_select'];
        foreach ($tablas_select as $key=>$tabla_select){

            if(is_array($tabla_select)){
                $tabla_base = $tabla_select['tabla_base'];
                $tabla_renombrada = $tabla_select['tabla_renombrada'];
                $resultado_columnas = $this->genera_columnas_consulta($tabla_base, $tabla_renombrada);
                if(errores::$error){
                    return $this->error->error('Error al generar columnas con renombre', $resultado_columnas);
                }

            }
            else {
                $resultado_columnas = $this->genera_columnas_consulta($key,false);
                if(errores::$error){
                    return $this->error->error('Error al generar columnas', $resultado_columnas);
                }

            }
            $columnas .= $columnas === ""? (string)$resultado_columnas :" , $resultado_columnas";
        }

        return $columnas;
    }

    /**
     * ERROR UNIT
     * @param $tabla
     * @param $id
     * @return array
     */
    public function obten_por_id($tabla, $id): array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error('Error la tabla esta vacia', $tabla);
        }
        $consulta = $this->genera_consulta_base($tabla);
        if(errores::$error){
            return $this->error->error('Error al generar consulta', $consulta);
        }
        $where = " WHERE $tabla".".id = $id ";
        $consulta .= $where;
        $result = $this->ejecuta_consulta($consulta);
        if(errores::$error){
            return $this->error->error('Error al ejecutar consulta', $result);
        }
        return $result;
    }

    public function obten_registros($tabla, $sql=''){
        $consulta_base = $this->genera_consulta_base($tabla);
        $consulta_base .=' '.$sql;
        $result = $this->ejecuta_consulta($consulta_base);
        return $result;
    }

    public function obten_registros_activos($tabla){
        $consulta = $this->genera_consulta_base($tabla);
        $where = " WHERE $tabla.status=1 ";
        $consulta = $consulta.$where;
        $result = $this->ejecuta_consulta($consulta);
        return $result;
    }

    public function obten_ultimo_id($tabla){
        $result = $this->link->query("SELECT MAX(id) AS id FROM $tabla");
        while( $row = mysqli_fetch_assoc( $result)){
            $new_array[] = $row;
        }

        $ultimo_id = $new_array[0]['id'];
        return $ultimo_id;
    }

    /**
     * ERROR UNIT
     * @param int $id
     * @param string $tabla
     * @return array
     */
    public function registro(int $id, string $tabla): array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error('Error la tabla esta vacia', $tabla);
        }
        $result = $this->obten_por_id(tabla: $tabla,id: $id);
        if(errores::$error){
            return $this->error->error('Error al obtener registro', $result);
        }
        if(count($result['registros']) === 0){
            return $this->error->error('Error no existe registro', $result);
        }

        return $result['registros'][0];
    }

    /**
     * ERROR UNIT
     * @param int $limit
     * @param string $tabla
     * @param string $where
     * @return array
     */
    public function registros_puros(int $limit, string $tabla, string $where): array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error('Error la tabla esta vacia', $tabla);
        }
        $sql_where = '';
        $where = trim($where);
        if($where!==''){
            $sql_where = 'WHERE '.$where;
        }

        $limit_sql = '';
        if($limit>0){
            $limit_sql = " LIMIT $limit ";
        }

        $sql = "SELECT *FROM $tabla $sql_where $limit_sql";
        $result = $this->ejecuta_consulta($sql);
        if(errores::$error){

            return $this->error->error(mensaje: 'Error al obtener registros', data: $result);
        }
        return $result;
    }

    /**
     * ERROR UNIT
     * @param string $campo
     * @param string $fecha_final
     * @param string $fecha_inicial
     * @param array $filtro_sql
     * @param int $limit_sql
     * @param string $tabla
     * @param string $tipo_val
     * @return array
     */
    public function rows_entre_fechas(string $campo, string $fecha_final, string $fecha_inicial, array $filtro_sql,
                                      int $limit_sql, string $tabla, string $tipo_val): array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error la tabla esta vacia',data:  $tabla, params: get_defined_vars());
        }
        $fechas['fecha_inicial'] = $fecha_inicial;
        $fechas['fecha_final'] = $fecha_final;
        $valida = (new validacion())->valida_rango_fecha(fechas: $fechas,tipo_val: $tipo_val);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar fechas', data: $valida, params: get_defined_vars());
        }
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error el $campo esta vacia',data:  $campo, params: get_defined_vars());
        }

        $sql = $this->genera_consulta_base(tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener sql_base', data: $sql, params: get_defined_vars());
        }


        $and = '';
        if(count($filtro_sql)>0){
            $and = 'AND';
        }

        $filtro_sql_exec = '';

        foreach ($filtro_sql as $complemento){
            $filtro_sql_exec = trim($filtro_sql_exec);
            $and_complemento = '';
            if($filtro_sql_exec !==''){
                $and_complemento = 'AND';
            }
            $filtro_sql_exec.=' '.$and_complemento.' '.$complemento;
        }


        $limit = '';
        if($limit_sql>0){
            $limit = ' LIMIT '.$limit_sql;
        }

        $where = "WHERE ($campo BETWEEN '$fecha_inicial' AND '$fecha_final') $and $filtro_sql_exec $limit";

        $sql .= " $where ";
        $result = $this->ejecuta_consulta($sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registros',data:  $result, params: get_defined_vars());
        }
        return $result;
    }

    public function sumatoria($campo_suma, $tabla, $campo_filtro, $valor): array
    {
        $consulta = "SELECT IFNULL( SUM($campo_suma),0) AS suma FROM $tabla WHERE $campo_filtro = '$valor'  ";
        $result = $this->ejecuta_consulta(consulta: $consulta);
        if(errores::$error){
            return $this->error->error('Error al ejecutar SQL', $result);
        }
        return $result;
    }

}
