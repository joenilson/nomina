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
require_model('dependientes.php');
require_model('tipodependientes.php');
require_model('formacion.php');
require_once 'helper_nomina.php';
require_once 'plugins/nomina/extras/verot/class.upload.php';
require_once('plugins/nomina/extras/PHPExcel/PHPExcel/IOFactory.php');

/**
 * Description of importar_agentes
 *
 * @author Joe Nilson <joenilson at gmail dot com>
 */
class importar_dependientes extends fs_controller {

    public $agente;
    public $dependientes;
    public $tipodependientes;
    public $formacion;
    public $allow_delete;
    public $documentos_dependientes;
    public $archivo;
    public $resultado;
    public $arrayCabeceras = array(
        'apellido_paterno', 'apellido_materno', 'codagente', 'coddependiente', 'docidentidad', 'estado', 'f_nacimiento', 'genero', 'grado_academico', 'nombres', 'codagente'
    );

    public function __construct() {
        parent::__construct(__CLASS__, 'Importar Dependientes', 'admin', 'false', FALSE, FALSE);
    }

    protected function private_core() {
        $this->share_extensions();
        $this->agente = new agente();
        $this->dependientes = new dependientes();
        $this->tipodependientes = new tipodependientes();
        $this->formacion = new formacion();

        if (isset($_POST['importar'])) {
            $this->archivo = $_FILES['dependientes'];
            $this->importar_dependientes();
        } elseif (isset($_GET['guardar_dependientes'])) {
            $this->guardar_dependientes();
        }
    }

    private function importar_dependientes() {

        $agentes = new agente();

        $objPHPExcel = PHPExcel_IOFactory::load($this->archivo['tmp_name']);
        $l = 0;

        $assoc_header['DNI_TITULAR'] = 'dnicif';
        $assoc_header['PARENTESCO'] = 'coddependiente';
        $assoc_header['NOMBRE_DH'] = 'nombres';
        $assoc_header['DNI_DH'] = 'docidentidad';
        $assoc_header['APELLIDO_PATERNO_DH'] = 'apellido_paterno';
        $assoc_header['APELLIDO_MATERNO_DH'] = 'apellido_materno';
        $assoc_header['SEXO_DH'] = 'genero';
        $assoc_header['F_NAC_DH'] = 'f_nacimiento';
        $assoc_header['FORMACION_DH'] = 'grado_academico';

        $worksheet = $objPHPExcel->getSheet(0);
        $highestRow = $worksheet->getHighestRow(); // e.g. 10
        $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
        $cabeceraRecibida = array();
        for ($col = 0; $col < count($this->arrayCabeceras); $col++) {
            $cell = $worksheet->getCellByColumnAndRow($col, 1);
            $contenido = strtoupper($cell->getValue());
            if (!empty($assoc_header[$contenido])) {
                $cabeceraRecibida[$col] = $assoc_header[$contenido];
            } else {
                $cabeceraRecibida[$col] = "UNSELECTED";
            }
        }

        for ($row = 1; $row <= $highestRow; ++$row) {
            if ($row != 1) {
                $linea['estado'] = 'Nuevo';
                $linea['max_column'] = $worksheet->getHighestColumn();
                for ($col = 0; $col < count($cabeceraRecibida); $col++) {
                    $cell = $worksheet->getCellByColumnAndRow($col, $row);
                    $contenido = strtoupper($cell->getValue());
                    $val = trim($cell->getValue());
                    $linea['fecha_excel'] = "";
                    $error = false;

                    if (PHPExcel_Shared_Date::isDateTime($cell)) {
                        $val =  (is_null($val) OR empty($val)) ? '' : \PHPExcel_Style_NumberFormat::toFormattedString($val, 'dd-mm-YYYY');
                    }

                    //Verificamos si el agente existe
                    if ($cabeceraRecibida[$col] == 'dnicif' AND ( !empty($val) AND $val != null)) {
                        //$val = ($agentes->get_by_dnicif($val)) ? null : $val;
                        $agente = $agentes->get_by_dnicif($val);
                        $linea['estado'] = ($agente) ? $linea['estado'] : 'Empleado No Encontrado';
                        $linea['codagente'] = ($agente) ? $agente->codagente : '';
                        $error_agente = ($agente) ? false : true;
                    }

                    //Verificamos si tiene genero
                    if ($cabeceraRecibida[$col] == 'genero' AND ( empty($val) OR $val == null)) {
                        $error = true;
                    }

                    //Verificamos si tiene coddependiente
                    if ($cabeceraRecibida[$col] == 'coddependiente' AND ( empty($val) OR $val == null)) {
                        $linea['tipo_dependiente'] = '';
                        $error = true;
                    }elseif($cabeceraRecibida[$col] == 'coddependiente'){
                        $tipo_dependiente = $this->tipodependientes->get_by_descripcion(trim($val));
                        $linea['tipo_dependiente'] = $tipo_dependiente->coddependiente;
                    }

                    //Verificamos si tiene grado_academico
                    if ($cabeceraRecibida[$col] == 'grado_academico' AND ( empty($val) OR $val == null)) {
                        $linea['codformacion'] = '';
                    }elseif($cabeceraRecibida[$col] == 'grado_academico'){
                        $formacion = $this->formacion->get_by_nombre(strtoupper(trim($val)));
                        $linea['codformacion'] = $formacion->codformacion;
                    }

                    //Verificamos que tenga un genero
                    if ($cabeceraRecibida[$col] == 'genero' AND ( !empty($val) OR $val !== null)) {
                        $val = strtoupper($val);
                    }

                    //Verificamos si tiene f_nacimiento
                    if ($cabeceraRecibida[$col] == 'f_nacimiento' AND (empty($val) OR is_null($val))) {
                        $error = true;
                    }
                    $linea[$cabeceraRecibida[$col]] = $val;
                    if ($error) {
                        $linea['estado'] = 'Incompleto';
                    }elseif($error_agente){
                        $linea['estado'] = 'No Empleado';
                    }
                }

                $linea['rid'] = $l;
                $this->resultado[] = $linea;
            }
            $l++;
        }
        $this->template = false;
        header('Content-Type: application/json');
        echo json_encode($this->resultado);
    }

    public function guardar_dependientes() {
        $this->template = false;
        $this->resultado = array();
        if (isset($_POST['codagente']) AND ! empty($_POST['codagente']) AND $_POST['f_nacimiento'] AND ! empty($_POST['f_nacimiento'] AND $_POST['genero']) AND ! empty($_POST['genero'])) {
            if ($this->guardar_dependiente()) {
                $this->resultado['estado'] = 'ingresado';
                $this->resultado['codagente'] = $_POST['codagente'];
            } else {
                $this->resultado['estado'] = 'no_ingresado';
                $this->resultado['codagente'] = $_POST['codagente'];
            }
        }else{
            $this->resultado['estado'] = 'no_ingresado';
            $this->resultado['codagente'] = $_POST['codagente'];
        }
        header('Content-Type: application/json');
        echo json_encode($this->resultado);
    }

    public function guardar_dependiente() {
        if ($_POST['codagente'] != '') {
            $codagente = \filter_input(INPUT_POST, 'codagente');
        }else{
            return false;
        }

        if ($_POST['genero'] != '') {
            $genero = substr(strtoupper(trim($_POST['genero'])),0,1);
        } else {
            $genero = NULL;
        }

        $f_nacimiento = \filter_input(INPUT_POST, 'f_nacimiento');
        if(!$f_nacimiento){
            return false;
        }

        $coddependiente = (isset($_POST['tipo_dependiente']))?$_POST['tipo_dependiente']:null;
        $grado_academico = (isset($_POST['codformacion']))?$_POST['codformacion']:null;

        $docidentidad = \trim(\filter_input(INPUT_POST, 'docidentidad'));

        $nombres = $this->mayusculas(\filter_input(INPUT_POST, 'nombres'));
        $apellido_paterno = $this->mayusculas(\filter_input(INPUT_POST, 'apellido_paterno'));
        $apellido_materno = $this->mayusculas(\filter_input(INPUT_POST, 'apellido_materno'));

        $dep0 = new dependientes();
        $dep0->codagente = $codagente;
        $dep0->coddependiente = $coddependiente;
        $dep0->nombres = $nombres;
        $dep0->apellido_paterno = $apellido_paterno;
        $dep0->apellido_materno = $apellido_materno;
        $dep0->docidentidad = $docidentidad;
        $dep0->f_nacimiento = $f_nacimiento;
        $dep0->genero = $genero;
        $dep0->grado_academico = $grado_academico;
        $dep0->estado = TRUE;
        $dep0->usuario_creacion = $this->user->nick;
        $dep0->fecha_creacion = \Date('d-m-y H:i:s');
        try {
            $dep0->save();
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

    private function share_extensions() {
        $extensiones = array(
            array(
                'name' => 'nomina_impdep_jgrid_css',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<link rel="stylesheet" type="text/css" media="screen" href="' . FS_PATH . 'plugins/nomina/view/css/ui.jqgrid-bootstrap.css"/>',
                'params' => ''
            ),
            array(
                'name' => 'importar_impdep_agentes_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="' . FS_PATH . 'plugins/nomina/view/js/nomina.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => 'importar_dependientes_css',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<link rel="stylesheet" type="text/css" media="screen" href="' . FS_PATH . 'plugins/nomina/view/css/nomina.css"/>',
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
