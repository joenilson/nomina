<?php
/*
 * This file is part of FacturaSctipts
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
require_model('tipodependientes.php');
require_model('tipoempleado.php');
require_model('tipoausencias.php');
require_model('categoriaempleado.php');
require_model('sindicalizacion.php');
require_model('sistemapension.php');
require_model('formacion.php');
require_model('organizacion.php');
require_model('hoja_vida.php');
require_model('movimientos_empleados.php');
require_once 'helper_nomina.php';
require_once 'plugins/nomina/extras/verot/class.upload.php';

class admin_agente extends fs_controller {

    public $agente;
    public $cargos;
    public $ausencias;
    public $contratos;
    public $dependientes;
    public $almacen;
    public $bancos;
    public $estadocivil;
    public $formacion;
    public $tipoempleado;
    public $tipodependientes;
    public $tipoausencias;
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

    /*
     * Esta página está en la carpeta admin, pero no se necesita ser admin para usarla.
     * Está en la carpeta admin porque su antecesora también lo está (y debe estarlo).
     */

    public function __construct() {
        parent::__construct(__CLASS__, 'Empleado', 'admin', FALSE, FALSE);
    }

    protected function private_core() {
        $this->ppage = $this->page->get('admin_agentes');
        $this->dir_empleados = FS_PATH.FS_MYDOCS."documentos/nomina/".$this->empresa->id."/e/";
        $this->dir_documentos_empleados = FS_PATH.FS_MYDOCS."documentos/nomina/".$this->empresa->id."/d/";
        $this->noimagen = FS_PATH."plugins/nomina/view/imagenes/no_foto.jpg";
       
        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
        $this->share_extensions();
        $this->almacen = new almacen();
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
        $this->tipodependientes = new tipodependientes();
        $this->agente = FALSE;
        if (isset($_GET['cod'])) {
            $agente = new agente();
            $this->agente = $agente->get($_GET['cod']);
        }

        if (isset($_GET['type'])) {
            $type = filter_input(INPUT_GET, 'type');
            switch ($type) {
                case "organizacion";
                    $this->template = false;
                    $helper = new helper_nomina();
                    $helper->buscar_organizacion();
                    break;
                case "nuevo":
                    $this->agente = new agente();
                    $this->template = 'contenido/nuevo_agente';
                    break;
                case "ausencias":
                    $this->ausencias = new ausencias();
                    if (isset($_REQUEST['accion'])) {
                        $this->tratar_ausencias();
                    }
                    $this->agente->ausencias = $this->ausencias->all_agente($this->agente->codagente);
                    $this->total_resultados = count($this->agente->ausencias);
                    $this->template = 'contenido/ausencias';
                    break;
                case "carga_familiar":
                    $this->dependientes = new dependientes();
                    if (isset($_REQUEST['accion'])) {
                        $this->tratar_dependientes();
                    }
                    $this->agente->dependientes = $this->dependientes->all_agente($this->agente->codagente);
                    $this->total_resultados = count($this->agente->dependientes);
                    $this->template = 'contenido/carga_familiar';
                    break;                    
                case "contratos":
                    $this->contratos = new contratos();
                    if (isset($_REQUEST['mostrar'])) {
                        $this->mostrar_informacion($_REQUEST['mostrar']);
                    } elseif (isset($_REQUEST['accion'])) {
                        $this->tratar_contratos();
                    }
                    $this->agente->contratos = $this->contratos->all_agente($this->agente->codagente);
                    $this->total_resultados = count($this->agente->contratos);
                    $this->template = 'contenido/contratos';
                    break;
                case "control_horas":
                    $this->template = 'contenido/control_horas';
                    break;
                case "pagos_incentivos":
                    $this->template = 'contenido/pagos_incentivos';
                    break;
                case "hoja_vida":
                    $this->hoja_vida = new hoja_vida();
                    if (isset($_REQUEST['mostrar'])) {
                        $this->mostrar_informacion($_REQUEST['mostrar']);
                    } elseif (isset($_REQUEST['accion'])) {
                        $this->tratar_hoja_vida();
                    }
                    $this->agente->hoja_vida = $this->hoja_vida->all_agente($this->agente->codagente);
                    $this->total_resultados = count($this->agente->hoja_vida);
                    $this->template = 'contenido/hoja_vida';
                    break;
                case "movimientos":
                    $this->movimientos_empleados = new movimientos_empleados();
                    if (isset($_REQUEST['accion'])) {
                        $this->tratar_movimientos();
                    }
                    $this->agente->movimientos = $this->movimientos_empleados->all_agente($this->agente->codagente);
                    $this->total_resultados = count($this->agente->movimientos);
                    $this->template = 'contenido/movimientos';
                    break;
                default:
                    break;
            }
        }

        if ($this->agente) {
            $this->page->title .= ' ' . $this->agente->codagente;

            if (isset($_POST['nombre'])) {
                //En la edición solo se permite campos no sensibles genéricos
                if ($this->user_can_edit()) {
                    if ($_POST['codcargo'] != '') {
                        $cargo = $this->cargos->get($_POST['codcargo']);
                    }
                    $this->agente->nombre = $this->mayusculas($_POST['nombre']);
                    $this->agente->apellidos = $this->mayusculas($_POST['apellidos']);
                    $this->agente->segundo_apellido = $this->mayusculas($_POST['segundo_apellido']);
                    $this->agente->dnicif = $_POST['dnicif'];
                    $this->agente->telefono = $_POST['telefono'];
                    $this->agente->email = $this->minusculas($_POST['email']);
                    $this->agente->provincia = $_POST['provincia'];
                    $this->agente->ciudad = $this->mayusculas($_POST['ciudad']);
                    $this->agente->direccion = $this->mayusculas($_POST['direccion']);
                    $this->agente->sexo = $_POST['sexo'];

                    $this->agente->f_nacimiento = NULL;
                    if ($_POST['f_nacimiento'] != '') {
                        $this->agente->f_nacimiento = $_POST['f_nacimiento'];
                    }

                    $this->agente->f_alta = NULL;
                    if ($_POST['f_alta'] != '') {
                        $this->agente->f_alta = $_POST['f_alta'];
                    }

                    $this->agente->f_baja = NULL;
                    if ($_POST['f_baja'] != '') {
                        $this->agente->f_baja = $_POST['f_baja'];
                    }

                    $this->agente->codtipo = $_POST['codtipo'];
                    $this->agente->codsupervisor = $_POST['codsupervisor'];
                    $this->agente->codgerencia = $_POST['codgerencia'];
                    $this->agente->codcargo = $_POST['codcargo'];
                    $this->agente->cargo = $cargo->descripcion;
                    $this->agente->codarea = $_POST['codarea'];
                    $this->agente->coddepartamento = $_POST['coddepartamento'];
                    $this->agente->codformacion = $_POST['codformacion'];
                    $this->agente->carrera = $this->mayusculas($_POST['carrera']);
                    $this->agente->centroestudios = $this->mayusculas($_POST['centroestudios']);
                    $this->agente->codseguridadsocial = $_POST['codseguridadsocial'];
                    $this->agente->codsistemapension = $_POST['codsistemapension'];
                    $this->agente->seg_social = $_POST['seg_social'];
                    $this->agente->codigo_pension = $_POST['codigo_pension'];
                    $this->agente->codbanco = $_POST['codbanco'];
                    $this->agente->cuenta_banco = $_POST['cuenta_banco'];
                    $this->agente->porcomision = floatval($_POST['porcomision']);
                    $this->agente->dependientes = $_POST['dependientes'];
                    $this->agente->idsindicato = $_POST['idsindicalizado'];
                    $this->agente->estado = $_POST['estado'];
                    $this->agente->estado_civil = $_POST['estado_civil'];

                    if ($this->agente->save()) {
                        $this->upload_photo = new Upload($_FILES['foto']);
                        if ($this->upload_photo->uploaded) {
                            $this->guardar_foto();
                        }
                        $this->new_message("Datos del empleado guardados correctamente.");
                    } else
                        $this->new_error_msg("¡Imposible guardar los datos del empleado!");
                } else
                    $this->new_error_msg('No tienes permiso para modificar estos datos.');
            }
        } else
            $this->new_error_msg("Empleado no encontrado.");
    }

    public function mostrar_informacion($solicitud) {
        if ($solicitud == 'buscar') {
            $this->desde = \filter_input(INPUT_POST, 'f_desde');
            $this->hasta = \filter_input(INPUT_POST, 'f_hasta');
            $this->resultados = 0;
        }
    }
    
    public function tratar_ausencias(){
        $accion = \filter_input(INPUT_POST, 'accion');
        $id = \filter_input(INPUT_POST, 'id');
        $tipo_ausencia = \filter_input(INPUT_POST, 'tipo_ausencia');
        $fecha_desde = \filter_input(INPUT_POST, 'f_desde');
        $fecha_hasta = \filter_input(INPUT_POST, 'f_hasta');
        $justificada = \filter_input(INPUT_POST, 'justificada');
        $estado = \filter_input(INPUT_POST, 'estado');
        $this->upload_documento = (isset($_FILES['documento']))?new Upload($_FILES['documento']):false;
        if ($accion == 'agregar') {
            $hv0 = new ausencias();
            $hv0->documento = ($this->upload_documento->uploaded)?$this->guardar_documento('ausencia'):NULL;
            $hv0->tipo_ausencia = $tipo_ausencia;
            $hv0->f_desde = $fecha_desde;
            $hv0->f_hasta = $fecha_hasta;
            $hv0->codagente = $this->agente->codagente;
            $hv0->justificada = ($justificada)?'TRUE':'FALSE';
            $hv0->estado = ($estado)?'TRUE':'FALSE';
            $hv0->usuario_creacion = $this->user->nick;
            $hv0->fecha_creacion = \Date('Y-m-d H:i:s');
            if ($hv0->save()) {
                $this->new_message('Ausencia agregada al empleado correctamente!');
            } else {
                $this->new_error_msg('Ocurrió un error con la información suministrada, por favor confirmar revisar los datos e intente de nuevo.');
            }
        } elseif ($accion == 'eliminar' and ($this->allow_delete)) {
            $ausencia = $this->ausencias->get($id);
            $doc = $ausencia->documento;
            if ($ausencia->delete()) {
                if(file_exists($doc)){
                    unlink($this->dir_documentos_empleados.$doc);
                }
                $this->new_message('Ausencia eliminada de las ausencias del empleado correctamente!');
            } else {
                $this->new_error_msg('Ocurrió un error intentando eliminar la informacion, por favor confirmar revisar los datos e intente de nuevo.');
            }
        }elseif($accion == 'eliminar' and !$this->allow_delete){
            $this->new_error_msg('No tiene permisos para eliminar ausencias!.');
        }
    }
    
    public function tratar_contratos(){
        $accion = \filter_input(INPUT_POST, 'accion');
        $id = \filter_input(INPUT_POST, 'id');
        $tipo_contrato = \filter_input(INPUT_POST, 'tipo_contrato');
        $fecha_inicio = \filter_input(INPUT_POST, 'f_desde');
        $fecha_fin = \filter_input(INPUT_POST, 'f_hasta');
        $estado = \filter_input(INPUT_POST, 'estado');
        $this->upload_documento = (isset($_FILES['documento']))?new Upload($_FILES['documento']):false;
        if ($accion == 'agregar' AND $this->upload_documento->uploaded) {
            $hv0 = new contratos();
            $hv0->contrato = $this->guardar_documento('contrato');
            $hv0->tipo_contrato = $tipo_contrato;
            $hv0->fecha_inicio = $fecha_inicio;
            $hv0->fecha_fin = $fecha_fin;
            $hv0->codagente = $this->agente->codagente;
            $hv0->estado = ($estado)?'TRUE':'FALSE';
            $hv0->usuario_creacion = $this->user->nick;
            $hv0->fecha_creacion = \Date('Y-m-d H:i:s');
            if ($hv0->save()) {
                $this->new_message('Documento agregado a los contratos del empleado correctamente!');
            } else {
                $this->new_error_msg('Ocurrió un error con la información suministrada, por favor confirmar revisar los datos e intente de nuevo.');
            }
        } elseif ($accion == 'eliminar' and ( $this->allow_delete)) {
            $contrato = $this->contratos->get($id);
            $doc = $contrato->contrato;
            if ($contrato->delete()) {
                unlink($this->dir_documentos_empleados.$doc);
                $this->new_message('Documento eliminado de los contratos del empleado correctamente!');
            } else {
                $this->new_error_msg('Ocurrió un error intentando eliminar la informacion, por favor confirmar revisar los datos e intente de nuevo.');
            }
        }elseif($accion == 'agregar' AND !$this->upload_documento->uploaded){
            $this->new_error_msg('No se adjuntó un documento valido para agregar, por favor intentelo nuevamente.');
        }elseif($accion == 'eliminar' and !$this->allow_delete){
            $this->new_error_msg('No tiene permisos para eliminar documentos!.');
        }
    }
    
    public function tratar_dependientes(){
        $accion = \filter_input(INPUT_POST, 'accion');
        $id = \filter_input(INPUT_POST, 'id');
        $tipo_dependiente = \filter_input(INPUT_POST, 'tipo_dependiente');
        $docidentidad = \trim(\filter_input(INPUT_POST, 'doc_identidad'));
        $f_nacimiento = \filter_input(INPUT_POST, 'f_nacimiento');
        $nombres = $this->mayusculas(\filter_input(INPUT_POST, 'nombres'));
        $apellido_paterno = $this->mayusculas(\filter_input(INPUT_POST, 'apellido_paterno'));
        $apellido_materno = $this->mayusculas(\filter_input(INPUT_POST, 'apellido_materno'));
        $genero = $this->mayusculas(\filter_input(INPUT_POST, 'genero'));
        $grado_academico = \filter_input(INPUT_POST, 'grado_academico');
        $estado = \filter_input(INPUT_POST, 'estado');
        if ($accion == 'agregar') {
            $hv0 = new dependientes();
            $hv0->id = $id;
            $hv0->codagente = $this->agente->codagente;
            $hv0->coddependiente = $tipo_dependiente;
            $hv0->nombres = $nombres;
            $hv0->apellido_paterno = $apellido_paterno;
            $hv0->apellido_materno = $apellido_materno;
            $hv0->docidentidad = $docidentidad;            
            $hv0->f_nacimiento = $f_nacimiento;            
            $hv0->genero = $genero;
            $hv0->grado_academico = $grado_academico;
            $hv0->estado = ($estado)?'TRUE':'FALSE';
            $hv0->usuario_creacion = $this->user->nick;
            $hv0->fecha_creacion = \Date('Y-m-d H:i:s');
            if ($hv0->save()) {
                $this->new_message('Dependiente agregado al empleado correctamente!');
            } else {
                $this->new_error_msg('Ocurrió un error con la información suministrada, por favor confirmar revisar los datos e intente de nuevo.');
            }
        } elseif ($accion == 'eliminar' and ($this->allow_delete)) {
            $dependiente = $this->dependientes->get($id);
            if ($dependiente->delete()) {
                $this->new_message('Dependiente eliminado del empleado correctamente!');
            } else {
                $this->new_error_msg('Ocurrió un error intentando eliminar la informacion, por favor confirmar revisar los datos e intente de nuevo.');
            }
        }elseif($accion == 'eliminar' and !$this->allow_delete){
            $this->new_error_msg('No tiene permisos para eliminar dependientes!.');
        }
    }

    public function tratar_hoja_vida() {
        $accion = \filter_input(INPUT_POST, 'accion');
        $id = \filter_input(INPUT_POST, 'id');
        $tipo_documento = \filter_input(INPUT_POST, 'tipo_documento');
        $autor_documento = \filter_input(INPUT_POST, 'autor_documento');
        $fecha_documento = \filter_input(INPUT_POST, 'fecha_documento');
        $this->upload_documento = (isset($_FILES['documento']))?new Upload($_FILES['documento']):false;
        if ($accion == 'agregar' AND $this->upload_documento->uploaded) {
            $hv0 = new hoja_vida();
            $hv0->documento = $this->guardar_documento('hoja_vida');
            $hv0->tipo_documento = $tipo_documento;
            $hv0->fecha_documento = $fecha_documento;
            $hv0->autor_documento = $autor_documento;
            $hv0->codagente = $this->agente->codagente;
            $hv0->estado = 'TRUE';
            $hv0->usuario_creacion = $this->user->nick;
            $hv0->fecha_creacion = \Date('Y-m-d H:i:s');
            if ($hv0->save()) {
                $this->new_message('Documento agregado a la hoja de vida correctamente!');
            } else {
                $this->new_error_msg('Ocurrió un error con la información suministrada, por favor confirmar revisar los datos e intente de nuevo.');
            }
        } elseif ($accion == 'eliminar' and ( $this->allow_delete)) {
            $hoja_vida = $this->hoja_vida->get($id);
            $doc = $hoja_vida->documento;
            if ($hoja_vida->delete()) {
                unlink($this->dir_documentos_empleados.$doc);
                $this->new_message('Documento eliminado de la hoja de vida del empleado correctamente!');
            } else {
                $this->new_error_msg('Ocurrió un error intentando eliminar la informacion, por favor confirmar revisar los datos e intente de nuevo.');
            }
        }elseif($accion == 'agregar' AND !$this->upload_documento->uploaded){
            $this->new_error_msg('No se adjuntó un documento valido para agregar, por favor intentelo nuevamente.');
        }elseif($accion == 'eliminar' and !$this->allow_delete){
            $this->new_error_msg('No tiene permisos para eliminar documentos!.');
        }
    }
    
    public function tratar_movimientos(){
        $accion = \filter_input(INPUT_POST, 'accion');
        $id = \filter_input(INPUT_POST, 'id');
        $codmovimiento = \filter_input(INPUT_POST, 'codmovimiento');
        $codautoriza = \filter_input(INPUT_POST, 'codautoriza');
        $observaciones = trim(\filter_input(INPUT_POST, 'observaciones'));
        $f_desde = \filter_input(INPUT_POST, 'f_desde');
        $f_hasta = \filter_input(INPUT_POST, 'f_hasta');
        $estado = \filter_input(INPUT_POST, 'estado');
        $this->upload_documento = (isset($_FILES['documento']))?new Upload($_FILES['documento']):false;
        if ($accion == 'agregar' AND $this->upload_documento->uploaded) {
            $hv0 = new movimientos_empleados();
            $hv0->id = $id;
            $hv0->documento = $this->guardar_documento('movimiento_empleado');
            $hv0->codmovimiento = $codmovimiento;
            $hv0->codautoriza = $codautoriza;
            $hv0->observaciones = $observaciones;
            $hv0->f_desde = $f_desde;
            $hv0->f_hasta = $f_hasta;
            $hv0->codagente = $this->agente->codagente;
            $hv0->estado = ($estado)?'TRUE':'FALSE';
            $hv0->usuario_creacion = $this->user->nick;
            $hv0->fecha_creacion = \Date('Y-m-d H:i:s');
            if ($hv0->save()) {
                $this->new_message('Movimiento agregado al empleado correctamente!');
            } else {
                $this->new_error_msg('Ocurrió un error con la información suministrada, por favor confirmar revisar los datos e intente de nuevo.');
            }
        } elseif ($accion == 'eliminar' and ( $this->allow_delete)) {
            $movimiento = $this->movimientos_empleados->get($id);
            $doc = $movimiento->documento;
            if ($movimiento->delete()) {
                unlink($this->dir_documentos_empleados.$doc);
                $this->new_message('Movimiento eliminado del empleado correctamente!');
            } else {
                $this->new_error_msg('Ocurrió un error intentando eliminar la informacion, por favor confirmar revisar los datos e intente de nuevo.');
            }
        }elseif($accion == 'agregar' AND !$this->upload_documento->uploaded){
            $this->new_error_msg('No se adjuntó un documento valido para agregar, por favor intentelo nuevamente.');
        }elseif($accion == 'eliminar' and !$this->allow_delete){
            $this->new_error_msg('No tiene permisos para eliminar documentos!.');
        }
    }    

    private function user_can_edit() {
        if (FS_DEMO) {
            return ($this->user->codagente == $this->agente->codagente);
        } else {
            return TRUE;
        }
    }

    //Con esta funcion guardamos los documentos dependiendo de donde vengan por cada modulo 
    public function guardar_documento($destino) {
        $nombre = \date('dmYhis').str_pad($this->agente->codagente, 6, 0, STR_PAD_LEFT) . "_" . $destino;
        // Grabar la imagen con un nuevo nombre y con un resize de 120px
        $this->upload_documento->file_new_name_body = $nombre;
        $this->upload_documento->file_new_name_ext = 'pdf';
        $this->upload_documento->Process($this->dir_documentos_empleados);
        if ($this->upload_documento->processed) {
            $this->upload_documento->clean();
            return $nombre.'.pdf';
        } else {
            return false;
        }
    }

    //Para guardar la foto hacemos uso de la libreria de class.upload.php que esta en extras/verot/
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
        $this->upload_photo->Process($this->dir_empleados);
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
        } else
            return $this->page->url();
    }

    public function share_extensions() {
        $extensiones = array(
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
            )
        );

        foreach ($extensiones as $ext) {
            $fsext0 = new fs_extension($ext);
            if (!$fsext0->save()) {
                $this->new_error_msg('Imposible guardar los datos de la extensión ' . $ext['name'] . '.');
            }
        }
    }

}
