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
require_once 'helper_nomina.php';
require_once 'plugins/nomina/vendor/verot/class.upload.php';
require_once('plugins/nomina/vendor/PHPOffice/PHPExcel/IOFactory.php');

/**
 * Description of importar_agentes
 *
 * @author Joe Nilson <joenilson at gmail dot com>
 */
class importar_reportaa extends fs_controller {

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
    public $archivo;
    public $resultado;
    public $arrayHeader =  array ('DOCUMENTO_IDENTIDAD' => 'dnicif','APELLIDO_PATERNO'=>'apellidos',
        'APELLIDO_MATERNO' => 'segundo_apellido', 'NOMBRE' => 'nombre',
        'DOCUMENTO_IDENTIDAD_SUP' => 'dnicif_sup','APELLIDO_PATERNO_SUP'=>'apellidos_sup',
        'APELLIDO_MATERNO_SUP' => 'segundo_apellido_sup', 'NOMBRE_SUP' => 'nombre_sup');
    public $arrayCabeceras = array('dnicif', 'apellidos','segundo_apellido', 'nombre',
        'dnicif_sup','apellidos_sup','segundo_apellido_sup', 'nombre_sup');

    /**
     * Construimos el nombre de la página y su template
     */
    public function __construct() {
        parent::__construct(__CLASS__, 'Importar Reporta A', 'nomina');
    }

    /**
     * Esta es la llamada a la parte privada de la página
     */
    protected function private_core() {
        $this->share_extensions();
        $this->init_models();

        if (filter_input(INPUT_POST, 'importar')) {
            $this->archivo = $_FILES['empleados'];
            $this->importar_empleados();
        } elseif (filter_input(INPUT_GET,'guardar_reportaa')) {
            $this->guardar_empleados();
        }
    }

    /**
     * Inicializamos todas las variables para llamadas a base de datos
     */
    private function init_models()
    {
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
    }

    /**
     * Teniendo el archivo cargado por el formulario procedemos a tratarlo con
     * la librería PHPExcel para obtener su contenido
     */
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

    /**
    * Procesamos una linea del archivo excel y verificamos su contenido
    * @param object $worksheet
    * @param array $sedesArray
    * @param array $cabeceraRecibida
    * @param array $row
    * @param integer $l
    * @param integer $qdadHeader
    * @return array
    */
    public function getLineaArchivoXLSX(&$worksheet, $sedesArray, $cabeceraRecibida, $row, $l, $qdadHeader) {
        $sup = new agente();
        $linea = [];
        $linea['estado'] = 'Nuevo';
        for ($col = 0; $col < $qdadHeader; $col++) {
            $cell = $worksheet->getCellByColumnAndRow($col, $row);
            $val = trim($cell->getValue());
            $error = false;
            //Verificamos si tiene dnicif
            if ($cabeceraRecibida[$col] == 'dnicif') {
                $existe = $this->agente->get_by_dnicif($val);
                $linea['codagente'] = NULL;
                $linea['dnicif'] = NULL;
                $linea['apellidos'] = NULL;
                $linea['segundo_apellido'] = NULL;
                $linea['nombre'] = NULL;
                if($existe){
                    $linea['codagente'] = $existe->codagente;
                    $linea['dnicif'] = $existe->dnicif;
                    $linea['apellidos'] = $existe->apellidos;
                    $linea['segundo_apellido'] = $existe->segundo_apellido;
                    $linea['nombre'] = $existe->nombre;
                }
            }

            if ($cabeceraRecibida[$col] == 'dnicif_sup') {
                $existe_sup = $this->agente->get_by_dnicif($val);
                $linea['codsupervisor'] = NULL;
                $linea['dnicif_sup'] = NULL;
                $linea['apellidos_sup'] = NULL;
                $linea['segundo_apellido_sup'] = NULL;
                $linea['nombre_sup'] = NULL;
                if($existe_sup){
                    $linea['codsupervisor'] = $existe_sup->codagente;
                    $linea['dnicif_sup'] = $existe_sup->dnicif;
                    $linea['apellidos_sup'] = $existe_sup->apellidos;
                    $linea['segundo_apellido_sup'] = $existe_sup->segundo_apellido;
                    $linea['nombre_sup'] = $existe_sup->nombre;
                }
            }
            //$linea[$cabeceraRecibida[$col]] = $val;
        }
        $linea['rid'] = $l;
        return $linea;
    }

    public function guardar_empleados() {
        $this->template = false;
        $this->resultado = array();
        $this->resultado['estado'] = 'no_ingresado';
        $this->resultado['dnicif'] = filter_input(INPUT_POST, 'dnicif');
        $age0 = new agente();
        if(filter_input(INPUT_POST, 'codagente') AND filter_input(INPUT_POST, 'codsupervisor')){
            $empleado = $age0->get(filter_input(INPUT_POST, 'codagente'));
            $supervisor = $age0->get(filter_input(INPUT_POST, 'codsupervisor'));
            if($supervisor){
                $empleado->codsupervisor = $supervisor->codagente;
                $empleado->fecha_modificacion = \date('d-m-Y H:i:s');
                $empleado->usuario_modificacion = $this->user->nick;
                try {
                    $empleado->save();
                    $this->resultado['estado'] = 'ingresado';
                    $this->resultado['dnicif'] = filter_input(INPUT_POST, 'dnicif');
                } catch (Exception $ex) {
                    $this->resultado['estado'] = 'no_ingresado';
                    $this->resultado['dnicif'] = filter_input(INPUT_POST, 'dnicif');
                }
            }else{
                $this->resultado['estado'] = 'no_ingresado';
                $this->resultado['dnicif'] = filter_input(INPUT_POST, 'dnicif');
            }
        }
        header('Content-Type: application/json');
        echo json_encode($this->resultado);
    }

    private function mayusculas($string) {
        return strtoupper(trim(strip_tags(stripslashes($string))));
    }

    private function minusculas($string) {
        return strtolower(trim(strip_tags(stripslashes($string))));
    }

    /**
     * Función para devolver el valor de una variable pasada ya sea por POST o GET
     * @param string
     * @return string
     */
    public function filter_request($nombre)
    {
        $nombre_post = \filter_input(INPUT_POST, $nombre);
        $nombre_get = \filter_input(INPUT_GET, $nombre);
        return ($nombre_post) ? $nombre_post : $nombre_get;
    }

    /**
     * Verifica la información de un campo enviado por POST o GET
     * @param array $nombre
     * @return array
     */
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
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<link rel="stylesheet" type="text/css" media="screen" href="' . FS_PATH . 'plugins/nomina/view/css/ui.jqgrid-bootstrap.min.css"/>',
                'params' => ''
            ),
            array(
                'name' => 'importar_agentes_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="' . FS_PATH . 'plugins/nomina/view/js/nomina.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => 'importar_agentes_css',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<link rel="stylesheet" type="text/css" media="screen" href="' . FS_PATH . 'plugins/nomina/view/css/nomina.min.css"/>',
                'params' => ''
            ),
            array(
                'name' => 'importar_reportaa_button',
                'page_from' => __CLASS__,
                'page_to' => 'admin_agentes',
                'type' => 'button',
                'text' => '<span class="fa fa-upload" aria-hidden="true"></span> &nbsp; Reporta a...',
                'params' => ''
            ),
        );
        foreach ($extensiones as $ext) {
            $fsext0 = new fs_extension($ext);
            if (!$fsext0->save()) {
                $this->new_error_msg('Imposible guardar los datos de la extensión ' . $ext['name'] . '.');
            }
        }
    }

}
