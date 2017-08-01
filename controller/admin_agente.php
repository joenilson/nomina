<?php
/*
 * This file is part of FacturaScripts
 * Copyright (C) 2014-2016  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
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
require_model('ausencias.php');
require_model('cargos.php');
require_model('contratos.php');
require_model('dependientes.php');
require_model('bancos.php');
require_model('estadocivil.php');
require_model('seguridadsocial.php');
require_model('tipocuenta.php');
require_model('tipodependientes.php');
require_model('tipoempleado.php');
require_model('tipoausencias.php');
require_model('tipomovimiento.php');
require_model('categoriaempleado.php');
require_model('sindicalizacion.php');
require_model('sistemapension.php');
require_model('formacion.php');
require_model('organizacion.php');
require_model('hoja_vida.php');
require_model('movimientos_empleados.php');
require_once 'helper_nomina.php';
require_once 'plugins/nomina/vendor/verot/class.upload.php';

class admin_agente extends fs_controller
{
    public $agente;
    public $agentes;
    public $cargos;
    public $ausencias;
    public $contratos;
    public $dependientes;
    public $almacen;
    public $bancos;
    public $estadocivil;
    public $formacion;
    public $tipocuenta;
    public $tipoempleado;
    public $tipodependientes;
    public $tipoausencias;
    public $tipomovimiento;
    public $categoriaempleado;
    public $sindicalizacion;
    public $organizacion;
    public $seguridadsocial;
    public $sistemapension;
    public $hoja_vida;
    public $movimientos_empleados;
    public $allow_delete;
    public $foto_empleado;
    public $noimagen;
    public $desde;
    public $hasta;
    public $rango;
    public $resultados;
    public $total_resultados;
    private $upload_photo;
    private $upload_documento;
    private $dir_empleados;
    public $dir_documentos_empleados;
    public $campos_obligatorios;
    /*
     * Esta página está en la carpeta admin, pero no se necesita ser admin para usarla.
     * Está en la carpeta admin porque su antecesora también lo está (y debe estarlo).
     */
    public function __construct() {
        parent::__construct(__CLASS__, 'Empleado', 'admin', FALSE, FALSE);
    }

    protected function private_core() {
        $this->ppage = $this->page->get('admin_agentes');
        $basepath = dirname(dirname(dirname(__DIR__)));
        $this->dir_documentos = $basepath.DIRECTORY_SEPARATOR.FS_MYDOCS."documentos/nomina/".$this->empresa->id."/";
        $this->dir_empleados = FS_PATH.FS_MYDOCS."documentos/nomina/".$this->empresa->id."/e/";
        $this->dir_documentos_empleados = FS_PATH.FS_MYDOCS."documentos/nomina/".$this->empresa->id."/d/";
        $this->noimagen = FS_PATH."plugins/nomina/view/imagenes/no_foto.jpg";
        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
        $this->share_extensions();
        $this->almacen = new almacen();
        $this->agentes = new agente();
        $this->bancos = new bancos();
        $this->cargos = new cargos();
        $this->formacion = new formacion();
        $this->tipoempleado = new tipoempleado();
        $this->categoriaempleado = new categoriaempleado();
        $this->sindicalizacion = new sindicalizacion();
        $this->organizacion = new organizacion();
        $this->seguridadsocial = new seguridadsocial();
        $this->sistemapension = new sistemapension();
        $this->estadocivil = new estadocivil();
        $this->tipoausencias = new tipoausencias();
        $this->tipocuenta = new tipocuenta();
        $this->tipodependientes = new tipodependientes();
        $this->tipomovimiento = new tipomovimiento();
        //Aqui se configurarán los campos obligatorios bajo demanda del usuario
        $this->campos_obligatorios = array(
            'nombre'=>'Nombre',
            'apellidos'=>'Primer Apellido',
            'dnicif'=>FS_CIFNIF,
            'f_nacimiento'=>'Fecha de nacimiento',
            'sexo'=>'Sexo',
            'estado_civil'=>'Estado Civil',
            'codalmacen'=>'Almacén',
            'codcargo'=>'Cargo',
            'codtipo'=>'Tipo de Contrato',
            'codgerencia'=>'Gerencia',
            'codarea'=>'Area',
            'f_alta'=>'Fecha de Alta',
            'estado'=>'Estado',
            'idsindicalizado'=>'Sindicalización',
            'codformacion'=>'Formación',
            'codseguridadsocial'=>'Seguridad Social',
            'codsistemapension'=>'Sistema de Pensión',
            'codbanco'=>'Banco',
            'tipo_cuenta'=>'Tipo Cuenta'
        );

        $this->agente = FALSE;

        $codagente = \filter_input(INPUT_GET, 'cod');
        if (isset($codagente)) {
            $agente = new agente();
            $this->agente = $agente->get($codagente);
        }

        if(filter_input(INPUT_GET, 'buscar_empleado')){
            $this->buscar_empleado();
        }

        if (isset($_GET['type'])) {
            $type = filter_input(INPUT_GET, 'type');
            switch ($type) {
                case "nuevo":
                    $this->agente = new agente();
                    $this->template = 'contenido/nuevo_agente';
                    break;
                default:
                    break;
            }
        }

        if ($this->agente) {
            $this->page->title .= ' ' . $this->agente->codagente;
            $accion = \filter_input(INPUT_POST, 'accion');
            if($accion=='agregar_agente'){
                $this->tratar_agente();
            }
        } else {
            $this->new_error_msg("Empleado no encontrado.");
        }
    }

    private function buscar_empleado()
    {
        /// desactivamos la plantilla HTML
        $this->template = FALSE;
        $query = filter_input(INPUT_GET, 'buscar_empleado');
        $json = array();
        foreach($this->agente->search($query) as $cli)
        {
            $json[] = array('value' => $cli->nombreap, 'codigo' => $cli->codagente);
        }

        header('Content-Type: application/json');
        echo json_encode( array('query' => $query, 'suggestions' => $json) );
   }

    public function tratar_agente(){
        if (\filter_input(INPUT_POST, 'nombre')) {
            //En la edición solo se permite campos no sensibles genéricos
            if ($this->user_can_edit()) {
                if (\filter_input(INPUT_POST, 'codcargo') != '') {
                    $cargo = $this->cargos->get(\filter_input(INPUT_POST, 'codcargo'));
                }
                $this->agente->nombre = $this->mayusculas(\filter_input(INPUT_POST, 'nombre'));
                $this->agente->apellidos = $this->mayusculas(\filter_input(INPUT_POST, 'apellidos'));
                $this->agente->segundo_apellido = $this->mayusculas(\filter_input(INPUT_POST, 'segundo_apellido'));
                $this->agente->dnicif = \filter_input(INPUT_POST, 'dnicif');
                $this->agente->telefono = \filter_input(INPUT_POST, 'telefono');
                $this->agente->email = $this->minusculas(\filter_input(INPUT_POST, 'email'));
                $this->agente->provincia = \filter_input(INPUT_POST, 'provincia');
                $this->agente->ciudad = $this->mayusculas(\filter_input(INPUT_POST, 'ciudad'));
                $this->agente->direccion = $this->mayusculas(\filter_input(INPUT_POST, 'direccion'));
                $this->agente->sexo = \filter_input(INPUT_POST, 'sexo');

                $this->agente->f_nacimiento = NULL;
                if (\filter_input(INPUT_POST, 'f_nacimiento') != '') {
                    $this->agente->f_nacimiento = \filter_input(INPUT_POST, 'f_nacimiento');
                }

                $this->agente->f_alta = NULL;
                if (\filter_input(INPUT_POST, 'f_alta') != '') {
                    $this->agente->f_alta = \filter_input(INPUT_POST, 'f_alta');
                }

                $this->agente->f_baja = NULL;
                $inactivo = false;
                if (\filter_input(INPUT_POST, 'f_baja') != '') {
                    $this->agente->f_baja = \filter_input(INPUT_POST, 'f_baja');
                    $inactivo = true;
                }

                $this->agente->codalmacen = \filter_input(INPUT_POST, 'codalmacen');
                $this->agente->codtipo = \filter_input(INPUT_POST, 'codtipo');
                $this->agente->codsupervisor = \filter_input(INPUT_POST, 'codsupervisor');
                $this->agente->codgerencia = \filter_input(INPUT_POST, 'codgerencia');
                $this->agente->codcargo = \filter_input(INPUT_POST, 'codcargo');
                $this->agente->cargo = $cargo->descripcion;
                $this->agente->codarea = \filter_input(INPUT_POST, 'codarea');
                $this->agente->coddepartamento = \filter_input(INPUT_POST, 'coddepartamento');
                $this->agente->codformacion = \filter_input(INPUT_POST, 'codformacion');
                $this->agente->carrera = $this->mayusculas(\filter_input(INPUT_POST, 'carrera'));
                $this->agente->centroestudios = $this->mayusculas(\filter_input(INPUT_POST, 'centroestudios'));
                $this->agente->codseguridadsocial = \filter_input(INPUT_POST, 'codseguridadsocial');
                $this->agente->codsistemapension = \filter_input(INPUT_POST, 'codsistemapension');
                $this->agente->seg_social = \filter_input(INPUT_POST, 'seg_social');
                $this->agente->codigo_pension = \filter_input(INPUT_POST, 'codigo_pension');
                $this->agente->codbanco = \filter_input(INPUT_POST, 'codbanco');
                $this->agente->cuenta_banco = \filter_input(INPUT_POST, 'cuenta_banco');
                $this->agente->tipo_cuenta = \filter_input(INPUT_POST, 'tipo_cuenta');
                $this->agente->porcomision = floatval(\filter_input(INPUT_POST, 'porcomision'));
                $this->agente->dependientes = \filter_input(INPUT_POST, 'dependientes');
                $this->agente->idsindicato = \filter_input(INPUT_POST, 'idsindicalizado');
                $this->agente->estado = ($inactivo)?'I':\filter_input(INPUT_POST, 'estado');
                $this->agente->estado_civil = \filter_input(INPUT_POST, 'estado_civil');

                if ($this->agente->save()) {
                    $this->upload_photo = new Upload($_FILES['foto']);
                    if ($this->upload_photo->uploaded) {
                        $this->guardar_foto();
                    }
                    $this->agente = $this->agente->get(\filter_input(INPUT_GET, 'cod'));
                    $this->new_message("Datos del empleado guardados correctamente.");
                } else {
                    $this->new_error_msg("¡Imposible guardar los datos del empleado!");
                }
            } else {
                $this->new_error_msg('No tienes permiso para modificar estos datos.');
            }
        }
    }

    private function user_can_edit() {
        if (FS_DEMO) {
            return ($this->user->codagente == $this->agente->codagente);
        } else {
            return TRUE;
        }
    }

    //Para guardar la foto hacemos uso de la libreria de class.upload.php que esta en vendor/verot/
    //Con esta libreria estandarizamos todas las imagenes en PNG y les hacemos un resize a 120px
    public function guardar_foto() {
        $this->foto_empleado = $this->agente->get_foto();
        if (file_exists($this->foto_empleado)) {
            unlink($this->foto_empleado);
        }
        $newname = str_pad($this->agente->codagente, 6, 0, STR_PAD_LEFT);

        // Grabar la imagen con un nuevo nombre y con un resize de 120px
        $this->upload_photo->file_new_name_body = $newname;
        $this->upload_photo->image_resize = true;
        $this->upload_photo->image_convert = 'png';
        $this->upload_photo->image_x = 120;
        $this->upload_photo->file_overwrite = true;
        $this->upload_photo->image_ratio_y = true;
        $this->upload_photo->Process($this->dir_documentos."e/");
        if ($this->upload_photo->processed) {
            $this->upload_photo->clean();
            $this->agente->set_foto($newname . ".png");
            $this->agente->foto = $newname . ".png";
        } else {
            $this->new_error_msg('error : ' . $$this->upload_photo->error);
        }
    }

    private function mayusculas($string) {
        return strtoupper(trim(strip_tags(stripslashes($string))));
    }

    private function minusculas($string) {
        return strtolower(trim(strip_tags(stripslashes($string))));
    }

    public function url() {
        if (!isset($this->agente)) {
            return parent::url();
        } else if ($this->agente) {
            return $this->agente->url();
        } else {
            return $this->page->url();
        }
    }

    public function share_extensions() {
        $extensiones_old = array(
            array(
                'name' => 'pace_loader_admin_agente_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/pace.min.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => 'bootstrap_switch_admin_agente_css',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<link href="'.FS_PATH.'plugins/nomina/view/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css"/>',
                'params' => ''
            ),
            array(
                'name' => 'daterangepicker_admin_agente_css',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<link href="'.FS_PATH.'plugins/nomina/view/css/daterangepicker.css" rel="stylesheet" type="text/css"/>',
                'params' => ''
            ),
            array(
                'name' => 'bootstrap_switch_admin_agente_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/3/bootstrap-switch.min.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => 'daterangepicker_admin_agente_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/2/daterangepicker.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => 'moment_admin_agente_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/1/moment-with-locales.min.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => 'nomina_admin_agente_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/nomina.js?build=' . rand(1, 1000) . '" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => 'nomina_admin_agente_css',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<link href="'.FS_PATH.'plugins/nomina/view/css/nomina.css?build=' . rand(1, 1000) . '" rel="stylesheet" type="text/css"/>',
                'params' => ''
            ),
            array(
                'name' => 'movimientos_empleado',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'tab',
                'text' => '<span class="fa fa-code-fork" aria-hidden="true"></span> &nbsp; Movimientos',
                'params' => '&type=movimientos'
            ),
            array(
                'name' => 'contratos_empleado',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'tab',
                'text' => '<span class="fa fa-archive" aria-hidden="true"></span> &nbsp; Contratos',
                'params' => '&type=contratos'
            ),
            array(
                'name' => 'ausencias_empleado',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'tab',
                'text' => '<span class="fa fa-calendar-minus-o" aria-hidden="true"></span> &nbsp; Ausencias',
                'params' => '&type=ausencias'
            ),
            array(
                'name' => 'carga_familiar_empleado',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'tab',
                'text' => '<span class="fa fa-group" aria-hidden="true"></span> &nbsp; Carga Familiar',
                'params' => '&type=carga_familiar'
            ),
            array(
                'name' => 'hoja_vida_empleado',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'tab',
                'text' => '<span class="fa fa-suitcase" aria-hidden="true"></span> &nbsp; Hoja de Vida',
                'params' => '&type=hoja_vida'
            ),
            array(
                'name' => 'pagos_incentivos_empleado',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'tab',
                'text' => '<span class="fa fa-money" aria-hidden="true"></span> &nbsp; Pagos e Incentivos',
                'params' => '&type=pagos_incentivos'
            ),
            array(
                'name' => 'control_horas_empleado',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'tab',
                'text' => '<span class="fa fa-clock-o" aria-hidden="true"></span> &nbsp; Control de Horas',
                'params' => '&type=control_horas'
            ),
        );

        foreach ($extensiones_old as $ext) {
            $fsext0 = new fs_extension($ext);
            if (!$fsext0->delete()) {
                $this->new_error_msg('Imposible guardar los datos de la extensión ' . $ext['name'] . '.');
            }
        }

        $extensiones = array(
            array(
                'name' => '001_admin_agente_css',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<link href="'.FS_PATH.'plugins/nomina/view/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css"/>',
                'params' => ''
            ),
            array(
                'name' => '002_admin_agente_css',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<link href="'.FS_PATH.'plugins/nomina/view/css/daterangepicker.css" rel="stylesheet" type="text/css"/>',
                'params' => ''
            ),
            array(
                'name' => '003_admin_agente_css',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<link href="'.FS_PATH.'plugins/nomina/view/css/nomina.css?build=' . rand(1, 1000) . '" rel="stylesheet" type="text/css"/>',
                'params' => ''
            ),
            array(
                'name' => '001_admin_agente_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/pace.min.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => '002_admin_agente_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/3/bootstrap-switch.min.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => '003_admin_agente_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/1/moment-with-locales.min.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => '004_admin_agente_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/2/daterangepicker.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => '005_admin_agente_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/nomina.js?build=' . rand(1, 1000) . '" type="text/javascript"></script>',
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
