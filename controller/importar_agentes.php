<?php

/*
 * Copyright (C) 2016 Joe Nilson <joenilson at gmail dot com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
require_model('agente.php');
require_model('almacen.php');
require_model('cargos.php');
require_model('bancos.php');
require_model('estadocivil.php');
require_model('seguridadsocial.php');
require_model('sistemapension.php');
require_model('tipoempleado.php');
require_model('categoriaempleado.php');
require_model('sindicalizacion.php');
require_model('formacion.php');
require_model('organizacion.php');
require_once 'helper_nomina.php';
require_once 'plugins/nomina/vendor/verot/class.upload.php';
require_once('plugins/nomina/vendor/PHPOffice/PHPExcel/IOFactory.php');

/**
 * Description of importar_agentes
 *
 * @author Joe Nilson <joenilson at gmail dot com>
 */
class importar_agentes extends fs_controller {

    public $agente;
    public $cargos;
    public $almacen;
    public $bancos;
    public $estadocivil;
    public $formacion;
    public $tipoempleado;
    public $categoriaempleado;
    public $sindicalizacion;
    public $organizacion;
    public $seguridadsocial;
    public $sistemapension;
    public $allow_delete;
    public $foto_empleado;
    public $noimagen = "plugins/nomina/view/imagenes/no_foto.jpg";
    public $archivo;
    public $resultado;
    public $arrayHeader =  array ('SEDE'=>'sede','EMPRESA' => 'empresa', 'DOCUMENTO_IDENTIDAD' => 'dnicif', 'NOMBRE_COMPLETO' => 'nombreap',
            'APELLIDO_PATERNO'=>'apellidos', 'APELLIDO_MATERNO' => 'segundo_apellido', 'NOMBRE' => 'nombre',  'SEXO'=>'sexo', 'ESTADO_CIVIL'=>'estado_civil',
            'FECHA_NACIMIENTO' => 'f_nacimiento', 'DIRECCION' => 'direccion', 'TELEFONO' => 'telefono', 'FECHA_INGRESO' => 'f_alta', 'FECHA_CESE' => 'f_baja',
            'GERENCIA' => 'gerencia', 'AREA' => 'area', 'DEPARTAMENTO' => 'departamento', 'CATEGORIA' => 'categoria', 'CUSSP' => 'cussp', 'CARGO' => 'cargo',
            'SISTEMA_PENSION'=>'codsistemapension','CODIGO_SISTEMA_PENSION'=>'codigo_pension','SEGURIDAD_SOCIAL'=>'codseguridadsocial',
            'NUMERO_SEGURIDAD_SOCIAL'=>'seg_social','NUMERO_HIJOS' => 'dependientes','NIVEL_FORMACION' => 'codformacion','CARRERA' => 'carrera',
            'CENTRO_ESTUDIOS' => 'centroestudios','SINDICATO' => 'idsindicato','TIPO_CONTRATO' => 'codtipo','EMAIL' => 'email','BANCO' => 'banco',
            'CUENTA_BANCO'=>'cuenta_banco','TIPO_CUENTA'=>'tipo_cuenta','TALLA_POLO'=>'talla_polo','PAGO_ASIGNACION_FAMILIAR'=>'pago_asignacion_familiar',
            'PAGO_BONO_CARGO'=>'pago_bono_cargo','PAGO_BONO_TIEMPO_SERVICIOS'=>'pago_bono_tiempo_servicios','PAGO_BONO_REEMPLAZO'=>'pago_bono_reemplazo',
            'PAGO_MOVILIDAD'=>'pago_movilidad','PAGO_BONO_ALIMENTO'=>'pago_bono_alimento','PAGO_SUELDO_BRUTO'=>'pago_total','PAGO_SUELDO_NETO'=>'pago_neto',
            'CODIGO_SUPERVISOR'=>'codsupervisor','DNICIF_SUPERVISOR'=>'dnicif_supervisor');
    public $arrayCabeceras = array('sede', 'empresa', 'dnicif', 'nombreap', 'apellidos',
        'segundo_apellido', 'nombre', 'sexo', 'estado_civil', 'f_nacimiento', 'direccion'
        , 'telefono', 'f_alta', 'f_baja', 'gerencia', 'area', 'departamento', 'cargo', 'codigo_pension'
        , 'codsistemapension', 'codseguridadsocial', 'seg_social', 'dependientes', 'codformacion', 'carrera'
        , 'centroestudios', 'idsindicato', 'codtipo', 'pago_total', 'pago_neto', 'email', 'codbanco', 'cuenta_banco', 'tipo_cuenta');

    public function __construct() {
        parent::__construct(__CLASS__, 'Importar Empleados', 'nomina');
    }

    protected function private_core() {
        $this->share_extensions();
        $this->agente = new agente();
        $this->almacen = new almacen();
        $this->bancos = new bancos();
        $this->cargos = new cargos();
        $this->estadocivil = new estadocivil();
        $this->formacion = new formacion();
        $this->tipoempleado = new tipoempleado();
        $this->categoriaempleado = new categoriaempleado();
        $this->sindicalizacion = new sindicalizacion();
        $this->organizacion = new organizacion();
        $this->seguridadsocial = new seguridadsocial();
        $this->sistemapension = new sistemapension();

        if (isset($_POST['importar'])) {
            $this->archivo = $_FILES['empleados'];
            $this->importar_empleados();
        } elseif (isset($_GET['guardar_empleados'])) {
            $this->guardar_empleados();
        }
    }

    private function importar_empleados() {
        $objPHPExcel = PHPExcel_IOFactory::load($this->archivo['tmp_name']);
        $l = 0;
        //Listado de Almacenes por descripcion
        $sedesArray = array();
        foreach ($this->almacen->all() as $vals) {
            $sedesArray[$vals->nombre] = $vals->codalmacen;
        }

        $worksheet = $objPHPExcel->getSheet(0);
        $highestRow = $worksheet->getHighestRow(); // ejemplo 10
        $highestColumn = $worksheet->getHighestColumn(); // ejemplo 'F'
        list($cabeceraRecibida, $qdadHeader) = $this->procesarCabeceraArchivo($worksheet, $highestColumn);

        for ($row = 1; $row <= $highestRow; ++$row) {
            if ($row != 1) {
                $lineas = $this->getLineaArchivoXLSX($worksheet, $sedesArray, $cabeceraRecibida, $row, $l, $qdadHeader);
                $this->resultado[] = $lineas;
            }
            $l++;
        }
        $this->template = false;
        header('Content-Type: application/json');
        echo json_encode($this->resultado);
    }

    /**
     * Procesamos el archivo Excel para validar los campos a procesar
     * @param object $worksheet
     * @param string $highestColumn
     * @return array&&integer
     */
    public function procesarCabeceraArchivo(&$worksheet,$highestColumn)
    {
        $highestColumn++;
        $cabeceraRecibida = array();
        for ($i = 'A'; $i !== $highestColumn; ++$i) {
            $pCoordinate = $i . '1';
            $cell = $worksheet->getCell($pCoordinate);
            $contenido = strtoupper($cell->getValue());
            if (!empty($this->arrayHeader[$contenido])) {
                $cabeceraRecibida[] = $this->arrayHeader[$contenido];
            } else {
                $cabeceraRecibida[] = "UNSELECTED";
            }
        }
        return array($cabeceraRecibida,  count($cabeceraRecibida));
    }

    public function getLineaArchivoXLSX(&$worksheet, $sedesArray, $cabeceraRecibida, $row, $l, $qdadHeader) {
        $linea = [];
        $linea['estado'] = 'Nuevo';
        for ($col = 0; $col < $qdadHeader; $col++) {
            $cell = $worksheet->getCellByColumnAndRow($col, $row);
            $val = trim($cell->getValue());
            $error = false;
            //Verificamos si tiene dnicif
            if ($cabeceraRecibida[$col] == 'dnicif') {
                $existe = $this->agente->get_by_dnicif($val);
                $linea['estado'] = ($existe) ? 'Existe' : $linea['estado'];
            }

            //Verificamos si tiene f_nacimiento
            if ($cabeceraRecibida[$col] == 'f_nacimiento' AND ( empty($val) OR $val == null)) {
                $error = true;
            }

            //Verificamos si tiene f_alta
            if ($cabeceraRecibida[$col] == 'f_alta' AND ( empty($val) OR $val == null)) {
                $error = true;
            }

            //le hacemos una revision del correo para que esté en minusculas
            if ($cabeceraRecibida[$col] === 'email') {
                $val = strtolower($val);
                $linea['EMAIL'] = $val;
            }

            if (PHPExcel_Shared_Date::isDateTime($cell)) {
                $val = (is_null($val) OR empty($val)) ? '' : \PHPExcel_Style_NumberFormat::toFormattedString($val, 'dd-mm-YYYY');
            }
            //Verificamos si la sede existe
            if ($cabeceraRecibida[$col] == 'sede' AND ( !empty($val) OR $val !== null)) {
                list($val, $error) = $this->verificarSede($val, $sedesArray);
            }
            if ($error) {
                $linea['estado'] = 'Incompleto';
            }

            $linea[$cabeceraRecibida[$col]] = $val;
        }
        $linea['rid'] = $l;
        return $linea;
    }

    public function verificarSede($value, $sedesArray){
        $val = strtoupper($value);
        $error = false;
        if (!isset($sedesArray[$value])) {
            $val = "Crear: " . $value;
            $error = true;
        }
        return array($val, $error);
    }

    public function guardar_empleados() {
        $this->template = false;
        $this->resultado = array();
        $age0 = new agente();
        if (isset($_POST['dnicif']) AND ! empty($_POST['dnicif'])) {
            if (!$age0->get_by_dnicif($_POST['dnicif'])) {
                if ($this->guardar_empleado()) {
                    $this->resultado['estado'] = 'ingresado';
                    $this->resultado['dnicif'] = $_POST['dnicif'];
                } else {
                    $this->resultado['estado'] = 'no_ingresado';
                    $this->resultado['dnicif'] = $_POST['dnicif'];
                }
            } else {
                if ($this->guardar_empleado()) {
                    $this->resultado['estado'] = 'existe';
                    $this->resultado['dnicif'] = $age0->get_by_dnicif($_POST['dnicif']);
                } else {
                    $this->resultado['estado'] = 'no_ingresado';
                    $this->resultado['dnicif'] = $_POST['dnicif'];
                }
            }
        } else {
            $this->resultado['estado'] = 'no_ingresado';
            $this->resultado['dnicif'] = $_POST['dnicif'];
        }

        header('Content-Type: application/json');
        echo json_encode($this->resultado);
    }

    public function guardar_empleado() {
        if ($_POST['sede'] != '') {
            $sede = false;
            foreach ($this->almacen->all() as $cod => $val) {
                if ($val->nombre == $this->mayusculas($_POST['sede'])) {
                    $sede = $val;
                }
            }
            if (!$sede) {
                return false;
            }
        }
        if ($_POST['cargo'] != '') {
            if ($this->cargos->get_by_descripcion($_POST['cargo'])) {
                $cargo = $this->cargos->get_by_descripcion(strtoupper($_POST['cargo']));
            } else {
                $cargo = new stdClass();
                $cargo->codcargo = NULL;
                $cargo->descripcion = NULL;
            }
        }

        $estado_civil = NULL;
        if ($_POST['estado_civil'] != '') {
            $ec0 = $this->estadocivil->get_by_descripcion(strtoupper(trim($_POST['estado_civil'])));
            $estado_civil = $ec0->codestadocivil;
        }

        $codbanco = NULL;
        if ($_POST['codbanco'] != '') {
            if ($this->bancos->get_by_nombre($this->mayusculas($_POST['codbanco']))) {
                $codbanco = $this->bancos->get_by_nombre($this->mayusculas($_POST['codbanco']))->codbanco;
            } else {
                $codbanco = NULL;
            }
        }

        $codseguridadsocial = NULL;
        if ($_POST['codseguridadsocial'] != '') {
            $codseguridadsocial = (strlen($_POST['codseguridadsocial']) <= 4) ? $this->seguridadsocial->get_by_nombre_corto($_POST['codseguridadsocial'])->codseguridadsocial : $this->seguridadsocial->get_by_nombre(strtoupper($_POST['codseguridadsocial']))->codseguridadsocial;
        }

        $codsistemapension = NULL;
        if ($_POST['codsistemapension'] != '') {
            $codsistemapension = (strlen($_POST['codsistemapension']) <= 4) ? $this->sistemapension->get_by_nombre_corto($_POST['codsistemapension'])->codsistemapension : $this->sistemapension->get_by_nombre(strtoupper($_POST['codsistemapension']))->codsistemapension;
        }

        if ($_POST['codformacion'] != '') {
            if ($this->formacion->get_by_nombre($this->mayusculas($_POST['codformacion']))) {
                $datos = $this->formacion->get_by_nombre($this->mayusculas($_POST['codformacion']));
                if ($datos) {
                    $codformacion = $datos->codformacion;
                } else {
                    $codformacion = NULL;
                }
            } else {
                $codformacion = NULL;
            }
        } else {
            $codformacion = NULL;
        }

        $codtipo = NULL;
        if ($_POST['codtipo'] != '') {
            if ($this->tipoempleado->get_by_descripcion($this->mayusculas($_POST['codtipo']))) {
                $codtipo = $this->tipoempleado->get_by_descripcion($this->mayusculas($_POST['codtipo']))->codtipo;
            } else {
                $codtipo = NULL;
            }
        }

        $codgerencia = NULL;
        if ($_POST['gerencia'] != '') {
            if ($this->organizacion->get_by_descripcion_tipo($this->mayusculas($_POST['gerencia']), 'GERENCIA')) {
                $codgerencia = $this->organizacion->get_by_descripcion_tipo($this->mayusculas($_POST['gerencia']), 'GERENCIA')->codorganizacion;
            } else {
                $codgerencia = NULL;
            }
        }

        $codarea = NULL;
        if ($_POST['area'] != '') {
            if ($this->organizacion->get_by_descripcion_tipo($this->mayusculas($_POST['area']), 'AREA')) {
                $codarea = $this->organizacion->get_by_descripcion_tipo($this->mayusculas($_POST['area']), 'AREA')->codorganizacion;
            } else {
                $codarea = NULL;
            }
        }

        $coddepartamento = NULL;
        if ($_POST['departamento'] != '') {
            if ($this->organizacion->get_by_descripcion_tipo($this->mayusculas($_POST['departamento']), 'DEPARTAMENTO')) {
                $coddepartamento = $this->organizacion->get_by_descripcion_tipo($this->mayusculas($_POST['departamento']), 'DEPARTAMENTO')->codorganizacion;
            } else {
                $coddepartamento = NULL;
            }
        } else {
            $coddepartamento = NULL;
        }

        if ($_POST['idsindicato'] != '') {
            if ($this->sindicalizacion->get_by_descripcion($this->mayusculas($_POST['idsindicato']))) {
                $idsindicato = $this->sindicalizacion->get_by_descripcion($this->mayusculas($_POST['idsindicato']))->idsindicato;
            } else {
                $idsindicato = NULL;
            }
        }

        $existe = $this->agente->get_by_dnicif($_POST['dnicif']);

        $age0 = new agente();
        $age0->codalmacen = $sede->codalmacen;
        $age0->idempresa = $this->empresa->id;
        $age0->codagente = ($existe)?$existe->codagente:$age0->get_new_codigo();
        $age0->nombre = $this->mayusculas($_POST['nombre']);
        $age0->apellidos = $this->mayusculas($_POST['apellidos']);
        $age0->segundo_apellido = $this->mayusculas($_POST['segundo_apellido']);
        $age0->dnicif = $_POST['dnicif'];
        $age0->telefono = $_POST['telefono'];
        $age0->email = $this->minusculas($_POST['email']);
        $age0->provincia = (isset($_POST['provincia'])) ? $_POST['provincia'] : $sede->provincia;
        $age0->ciudad = (isset($_POST['ciudad'])) ? $this->mayusculas($_POST['ciudad']) : $sede->poblacion;
        $age0->direccion = (isset($_POST['direccion'])) ? $this->mayusculas($_POST['direccion']) : $sede->direccion;
        $age0->sexo = $this->mayusculas($_POST['sexo']);
        $age0->f_nacimiento = $_POST['f_nacimiento'];
        $age0->f_alta = (!empty($_POST['f_alta'])) ? $_POST['f_alta'] : NULL;
        $age0->f_baja = (!empty($_POST['f_baja'])) ? $_POST['f_baja'] : NULL;
        $age0->codtipo = $codtipo;
        $age0->codgerencia = $codgerencia;
        $age0->codarea = $codarea;
        $age0->coddepartamento = $coddepartamento;
        $age0->codcargo = $cargo->codcargo;
        $age0->cargo = $cargo->descripcion;
        $age0->codbanco = $codbanco;
        $age0->cuenta_banco = trim($_POST['cuenta_banco']);
        $age0->codformacion = $codformacion;
        $age0->carrera = $this->mayusculas($_POST['carrera']);
        $age0->centroestudios = $this->mayusculas($_POST['centroestudios']);
        $age0->codseguridadsocial = $codseguridadsocial;
        $age0->seg_social = trim($_POST['seg_social']);
        $age0->codsistemapension = $codsistemapension;
        $age0->codigo_pension = trim($_POST['sistemapension']);
        $age0->dependientes = ($_POST['dependientes'] != '') ? $_POST['dependientes'] : 0;
        $age0->idsindicato = $idsindicato;
        $age0->estado = 'A';
        $age0->estado_civil = $estado_civil;
        $age0->fecha_creacion = \Date('d-m-y H:i:s');
        $age0->usuario_creacion = $this->user->nick;
        $age0->fecha_modificacion = \Date('d-m-y H:i:s');
        $age0->usuario_modificacion = $this->user->nick;
        try {
            $age0->save();
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    private function mayusculas($string) {
        return strtoupper(trim(strip_tags(stripslashes($string))));
    }

    private function minusculas($string) {
        return strtolower(trim(strip_tags(stripslashes($string))));
    }

        /**
     * Función para devolver el valor de una variable pasada ya sea por POST o GET
     * @param type string
     * @return type string
     */
    public function filter_request($nombre)
    {
        $nombre_post = \filter_input(INPUT_POST, $nombre);
        $nombre_get = \filter_input(INPUT_GET, $nombre);
        return ($nombre_post) ? $nombre_post : $nombre_get;
    }

    public function filter_request_array($nombre)
    {
        $nombre_post = \filter_input(INPUT_POST, $nombre, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $nombre_get = \filter_input(INPUT_GET, $nombre, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        return ($nombre_post) ? $nombre_post : $nombre_get;
    }

    private function share_extensions() {
        $extensiones = array(
            array(
                'name' => 'nomina_jgrid_css',
                'page_from' => __CLASS__,
                'page_to' => 'importar_agentes',
                'type' => 'head',
                'text' => '<link rel="stylesheet" type="text/css" media="screen" href="' . FS_PATH . 'plugins/nomina/view/css/ui.jqgrid-bootstrap.min.css"/>',
                'params' => ''
            ),
            array(
                'name' => 'importar_agentes_js',
                'page_from' => __CLASS__,
                'page_to' => 'importar_agentes',
                'type' => 'head',
                'text' => '<script src="' . FS_PATH . 'plugins/nomina/view/js/nomina.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => 'importar_agentes_css',
                'page_from' => __CLASS__,
                'page_to' => 'importar_agentes',
                'type' => 'head',
                'text' => '<link rel="stylesheet" type="text/css" media="screen" href="' . FS_PATH . 'plugins/nomina/view/css/nomina.min.css"/>',
                'params' => ''
            ),
            array(
                'name' => 'importar_agentes_button',
                'page_from' => __CLASS__,
                'page_to' => 'admin_agentes',
                'type' => 'button',
                'text' => '<span class="fa fa-upload" aria-hidden="true"></span> &nbsp; Cargar Empleados',
                'params' => ''
            ),
        );
        foreach ($extensiones as $ext) {
            $fsext0 = new fs_extension($ext);
            if (!$fsext0->save()) {
                $this->new_error_msg('Imposible guardar los datos de la extensión ' . $ext['name'] . '.');
            }
        }

        $extensiones2 = array(
            array(
                'name' => 'cargar_empleados_button',
                'page_from' => __CLASS__,
                'page_to' => 'admin_agentes',
                'type' => 'button',
                'text' => '<span class="fa fa-upload" aria-hidden="true"></span> &nbsp; Cargar Empleados',
                'params' => ''
            ),
        );
        foreach ($extensiones2 as $ext) {
            $fsext0 = new fs_extension($ext);
            if (!$fsext0->delete()) {
                $this->new_error_msg('Imposible guardar los datos de la extensión ' . $ext['name'] . '.');
            }
        }
    }

}
