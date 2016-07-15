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
require_model('seguridadsocial.php');
require_model('tipoempleado.php');
require_model('categoriaempleado.php');
require_model('sindicalizacion.php');
require_model('formacion.php');
require_model('organizacion.php');
require_once 'helper_nomina.php';
require_once 'plugins/nomina/extras/verot/class.upload.php';
require_once('plugins/nomina/extras/PHPExcel/PHPExcel/IOFactory.php');
/**
 * Description of importar_agentes
 *
 * @author Joe Nilson <joenilson at gmail dot com>
 */
class importar_agentes extends fs_controller
{
   public $agente;
   public $cargos;
   public $almacen;
   public $bancos;
   public $formacion;
   public $tipoempleado;
   public $categoriaempleado;
   public $sindicalizacion;
   public $organizacion;
   public $seguridadsocial;
   public $allow_delete;
   public $foto_empleado;
   public $noimagen = "plugins/nomina/view/imagenes/no_foto.jpg";
   private $upload_photo;
   public $archivo;
   public $resultado;
   public $arrayCabeceras = array('sede','empresa','dnicif','nombreap','apellidos',
            'segundo_apellido','nombre','sexo','estado_civil','f_nacimiento','direccion'
            ,'telefono','f_alta','f_baja','gerencia','area','departamento','cargo','categoria'
            ,'codseguridadsocial','seg_social','dependientes','codformacion','carrera'
            ,'centroestudios','idsindicato','codtipo','pago_total','pago_neto','email');
    public function __construct() {
        parent::__construct(__CLASS__, 'Importar Empleados', 'admin', 'false', FALSE, FALSE);
    }

    protected function private_core() {
        $this->share_extensions();
        $this->agente = new agente();
        $this->almacen = new almacen();
        $this->bancos = new bancos();
        $this->cargos = new cargos();
        $this->formacion = new formacion();
        $this->tipoempleado = new tipoempleado();
        $this->categoriaempleado = new categoriaempleado();
        $this->sindicalizacion = new sindicalizacion();
        $this->organizacion = new organizacion();
        $this->seguridadsocial = new seguridadsocial();

        if( isset($_POST['importar']) )
        {
            $this->archivo = $_FILES['empleados'];
            $this->importar_empleados();
        }
        elseif(isset($_GET['guardar_empleados']))
        {
            $this->guardar_empleados();
        }
    }

    private function importar_empleados(){

        $agentes = new agente();
        $objPHPExcel = PHPExcel_IOFactory::load($this->archivo['tmp_name']);
        $l = 0;
        //foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $worksheet = $objPHPExcel->getSheet(0);
            $worksheetTitle     = $worksheet->getTitle();
            $highestRow         = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
            $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
            $nrColumns = ord($highestColumn) - 64;

            for ($row = 1; $row <= $highestRow; ++$row) {
                if($row!=1){
                    for ($col = 0; $col < count($this->arrayCabeceras); ++$col) {
                        $cell = $worksheet->getCellByColumnAndRow($col, $row);
                        $val = $cell->getValue();
                        //Verificamos si tiene dnicif
                        if($col==2 AND (!empty($val) AND $val != null)){
                            $val = ($agentes->get_by_dnicif($val))?null:$val;
                            $linea['estado']=($val)?'Nuevo':'Ya existe';
                        }
                        //Verificamos si tiene f_nacimiento
                        if($col==9 AND (empty($val) OR $val == null)){
                            $linea['estado']='Incompleto';
                        }
                        if(PHPExcel_Shared_Date::isDateTime($cell)) {
                            $val = (is_null($val))?'':date('d-m-Y', PHPExcel_Shared_Date::ExcelToPHP($val));
                        }
                        $linea[$this->arrayCabeceras[$col]]=$val;

                    }
                    $linea['rid']=$l;
                    $this->resultado[]=$linea;
                }
                $l++;
            }
        //}
        $this->template = false;
        header('Content-Type: application/json');
        echo json_encode($this->resultado);
   }

   public function guardar_empleados(){
       $this->template = false;
       $this->resultado = array();
       $age0 = new agente();
       if(isset($_POST['dnicif']) AND !empty($_POST['dnicif'])){
           if(!$age0->get_by_dnicif($_POST['dnicif'])){
               if($this->guardar_empleado()){
                   $this->resultado['estado']='ingresado';
                   $this->resultado['dnicif']=$_POST['dnicif'];
               }else{
                   $this->resultado['estado']='no_ingresado';
                   $this->resultado['dnicif']=$_POST['dnicif'];
               }
           }else{
               $this->resultado['estado']='existe';
               $this->resultado['dnicif']=$age0->get_by_dnicif($_POST['dnicif']);
           }
       }

       header('Content-Type: application/json');
       echo json_encode($this->resultado);
   }

   public function guardar_empleado(){
        if($_POST['sede'] != ''){
            $sede = false;
            foreach($this->almacen->all() as $cod=>$val){
                //print_r(array('sede'=>$val->nombre));
                if($val->nombre == $this->mayusculas($_POST['sede'])){
                    $sede = $val;
                }
            }
            if(!$sede){
                return false;
            }
        }
        if($_POST['cargo'] != ''){
            if($this->cargos->get_by_descripcion($_POST['cargo'])){
                $cargo = $this->cargos->get_by_descripcion(strtoupper($_POST['cargo']));
            }else{
                $cargo->codcargo = NULL;
                $cargo->descripcion = NULL;
            }
        }

        if($_POST['estado_civil'] != ''){
            foreach($this->agente->estado_civil_agente() as $key=>$val){
                if(strtoupper($val) == strtoupper($_POST['estado_civil'])){
                    $estado_civil = $key;
                }
            }
        }else{
            $estado_civil = NULL;
        }

        if($_POST['codseguridadsocial'] != ''){
            $codseguridadsocial = (strlen($_POST['codseguridadsocial'])<=4)?$this->seguridadsocial->get_by_nombre_corto($_POST['codseguridadsocial'])->codseguridadsocial:$this->seguridadsocial->get_by_nombre(strtoupper($_POST['codseguridadsocial']))->codseguridadsocial;
        }

        if($_POST['codformacion'] != ''){
            if(!$this->formacion->get_by_nombre($this->mayusculas($_POST['codformacion']))){
                $datos = $this->formacion->get_by_nombre($this->mayusculas($_POST['codformacion']));
                if($datos){
                    $codformacion = $datos->codformacion;
                }else{
                    $codformacion = NULL;
                }
            }else{
                $codformacion = NULL;
            }
        }else{
            $codformacion = NULL;
        }

        if($_POST['categoria'] != ''){
            if(!$this->categoriaempleado->get_by_descripcion($this->mayusculas($_POST['categoria']))){
                $datos = $this->categoriaempleado->get_by_descripcion($this->mayusculas($_POST['categoria']));
                if($datos){
                    $codcategoria = $datos->codcategoria;
                }else{
                    $codcategoria = NULL;
                }
            }else{
                $codcategoria = NULL;
            }
        }

        if($_POST['codtipo'] != ''){
            if(!$this->tipoempleado->get_by_descripcion($this->mayusculas($_POST['codtipo']))){
                $codtipo = $this->tipoempleado->get_by_descripcion($this->mayusculas($_POST['codtipo']))->codtipo;
            }else{
                $codtipo = NULL;
            }
        }

        if($_POST['gerencia'] != ''){
            if(!$this->organizacion->get_by_descripcion_tipo($this->mayusculas($_POST['gerencia']),'GERENCIA')){
                $codgerencia = $this->organizacion->get_by_descripcion_tipo($this->mayusculas($_POST['gerencia']),'GERENCIA')->codorganizacion;
            }else{
                $codgerencia = NULL;
            }
        }

        if($_POST['area'] != ''){
            if(!$this->organizacion->get_by_descripcion_tipo($this->mayusculas($_POST['area']),'AREA')){
                $codarea = $this->organizacion->get_by_descripcion_tipo($this->mayusculas($_POST['area']),'AREA')->codorganizacion;
            }else{
                $codarea = NULL;
            }
        }

        if($_POST['departamento'] != ''){
            if(!$this->organizacion->get_by_descripcion_tipo($this->mayusculas($_POST['area']),'DEPARTAMENTO')){
                $coddepartamento = $this->organizacion->get_by_descripcion_tipo($this->mayusculas($_POST['area']),'DEPARTAMENTO')->codorganizacion;
            }else{
                $coddepartamento = NULL;
            }
        }else{
            $coddepartamento = NULL;
        }

        if($_POST['idsindicato'] != ''){
            if(!$this->sindicalizacion->get_by_descripcion($this->mayusculas($_POST['idsindicato']))){
                $idsindicato = $this->sindicalizacion->get_by_descripcion($this->mayusculas($_POST['idsindicato']))->idsindicato;
            }else{
                $idsindicato = NULL;
            }
        }
        $age0 = new agente();
        $age0->codalmacen = $sede->codalmacen;
        $age0->idempresa = $this->empresa->id;
        $age0->codagente = $age0->get_new_codigo();
        $age0->nombre = $this->mayusculas($_POST['nombre']);
        $age0->apellidos = $this->mayusculas($_POST['apellidos']);
        $age0->segundo_apellido = $this->mayusculas($_POST['segundo_apellido']);
        $age0->dnicif = $_POST['dnicif'];
        $age0->telefono = $_POST['telefono'];
        $age0->email = $this->minusculas($_POST['email']);
        $age0->provincia = (isset($_POST['provincia']))?$_POST['provincia']:$sede->provincia;
        $age0->ciudad = (isset($_POST['ciudad']))?$this->mayusculas($_POST['ciudad']):$sede->poblacion;
        $age0->direccion = (isset($_POST['direccion']))?$this->mayusculas($_POST['direccion']):$sede->direccion;
        $age0->sexo = $this->mayusculas($_POST['sexo']);
        $age0->f_nacimiento = $_POST['f_nacimiento'];
        $age0->f_alta = (!empty($_POST['f_alta']))?$_POST['f_alta']:NULL;
        $age0->f_baja = (!empty($_POST['f_baja']))?$_POST['f_baja']:NULL;
        $age0->codcategoria = $codcategoria;
        $age0->codtipo = $codtipo;
        //$age0->codsupervisor = $_POST['codsupervisor'];
        $age0->codgerencia = $codgerencia;
        $age0->codcargo = $cargo->codcargo;
        $age0->cargo = $cargo->descripcion;
        $age0->coddepartamento = $coddepartamento;
        $age0->codformacion = $codformacion;
        $age0->carrera = $this->mayusculas($_POST['carrera']);
        $age0->centroestudios = $this->mayusculas($_POST['centroestudios']);
        $age0->codseguridadsocial = $codseguridadsocial;
        $age0->seg_social = trim($_POST['seg_social']);
        //$age0->codbanco = $_POST['codbanco'];
        //$age0->cuenta_banco = $_POST['cuenta_banco'];
        //$age0->porcomision = floatval($_POST['porcomision']);
        $age0->dependientes = ($_POST['dependientes']!='')?$_POST['dependientes']:0;
        $age0->idsindicato = $idsindicato;
        $age0->estado = 'A';
        $age0->estado_civil = $estado_civil;
        $age0->fecha_creacion = date('d-m-y H:m:s');
        $age0->usuario_creacion = $this->user->nick;
         if( $age0->save() )
         {
             return true;
         }
         else
         {
            return false;
         }
   }

   private function mayusculas($string){
       return strtoupper(trim(strip_tags(stripslashes($string))));
   }

   private function minusculas($string){
       return strtolower(trim(strip_tags(stripslashes($string))));
   }

    private function share_extensions(){
       $fext = new fs_extension(array(
            'name' => 'nomina_jgrid_css',
            'page_from' => __CLASS__,
            'page_to' => 'importar_agentes',
            'type' => 'head',
            'text' => '<link rel="stylesheet" type="text/css" media="screen" href="plugins/nomina/view/css/ui.jqgrid-bootstrap.css"/>',
            'params' => ''
         ));
       $fext->save();
   }
}
