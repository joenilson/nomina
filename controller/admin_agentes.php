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
require_model('cargos.php');
require_model('organizacion.php');
require_once 'helper_nomina.php';
require_once('plugins/nomina/extras/PHPExcel/PHPExcel/IOFactory.php');
require_once 'plugins/nomina/extras/verot/class.upload.php';
class admin_agentes extends fs_controller
{
   public $agente;
   public $archivo;
   public $resultado;
   public $almacen;
   public $cargos;
   public $organizacion;
   public $foto_empleado;
   public $noimagen = "plugins/nomina/view/imagenes/no_foto.jpg";
   private $upload_photo;
   private $dir_empleados = "tmp/{FS_TMP_NAME}/nomina/empleados/";
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Empleados', 'admin', TRUE, TRUE);
   }

   protected function private_core()
   {
      $this->share_extensions();
      $this->agente = new agente();
      $this->almacen = new almacen();
      $this->cargos = new cargos();
      $this->organizacion = new organizacion();
      if( isset($_GET['type']) ){
          $type = filter_input(INPUT_GET, 'type');
          switch ($type){
              case "organizacion";
                $this->template = false;
                $helper = new helper_nomina();
                $helper->buscar_organizacion();
                break;
              case "nuevo":
                  $this->agente = new agente();
                  $this->template = 'contenido/nuevo_agente';
                  break;
              default:
                break;
          }
      }

      if( isset($_POST['nuevo']) AND $_POST['nuevo'] == 1 )
      {
        if($_POST['codcargo'] != ''){
            $cargo = $this->cargos->get($_POST['codcargo']);
        }
        $age0 = new agente();
        $age0->codalmacen = $_POST['codalmacen'];
        $age0->idempresa = $this->empresa->id;
        $age0->codagente = $age0->get_new_codigo();
        $age0->nombre = $this->mayusculas($_POST['nombre']);
        $age0->apellidos = $this->mayusculas($_POST['apellidos']);
        $age0->segundo_apellido = $this->mayusculas($_POST['segundo_apellido']);
        $age0->dnicif = $_POST['dnicif'];
        $age0->telefono = $_POST['telefono'];
        $age0->email = $this->minusculas($_POST['email']);
        $age0->provincia = $_POST['provincia'];
        $age0->ciudad = $this->mayusculas($_POST['ciudad']);
        $age0->direccion = $this->mayusculas($_POST['direccion']);
        $age0->sexo = $_POST['sexo'];
        $age0->f_nacimiento = $_POST['f_nacimiento'];
        $age0->f_alta = (!empty($_POST['f_alta']))?$_POST['f_alta']:NULL;
        $age0->f_baja = (!empty($_POST['f_baja']))?$_POST['f_baja']:NULL;
        $age0->codcategoria = $_POST['codcategoria'];
        $age0->codtipo = $_POST['codtipo'];
        $age0->codsupervisor = $_POST['codsupervisor'];
        $age0->codgerencia = $_POST['codgerencia'];
        $age0->codcargo = $_POST['codcargo'];
        $age0->cargo = $cargo->descripcion;
        $age0->coddepartamento = $_POST['coddepartamento'];
        $age0->codformacion = $_POST['codformacion'];
        $age0->carrera = $this->mayusculas($_POST['carrera']);
        $age0->centroestudios = $this->mayusculas($_POST['centroestudios']);
        $age0->codseguridadsocial = $_POST['codseguridadsocial'];
        $age0->seg_social = $_POST['seg_social'];
        $age0->codbanco = $_POST['codbanco'];
        $age0->cuenta_banco = $_POST['cuenta_banco'];
        $age0->porcomision = floatval($_POST['porcomision']);
        $age0->dependientes = $_POST['dependientes'];
        $age0->idsindicato = $_POST['idsindicalizado'];
        $age0->estado = $_POST['estado'];
        $age0->estado_civil = $_POST['estado_civil'];
        $age0->fecha_creacion = date('d-m-y H:m:s');
        $age0->usuario_creacion = $this->user->nick;
         if( $age0->save() )
         {
            $this->upload_photo = new Upload($_FILES['foto']);
            if ($this->upload_photo->uploaded) {
                $this->guardar_foto();
            }
            $this->new_message("Empleado ".$age0->codagente." guardado correctamente.");
            header('location: '.$age0->url());
         }
         else
            $this->new_error_msg("¡Imposible guardar el empleado!");
      }
      else if( isset($_GET['delete']) )
      {
         $age0 = $this->agente->get($_GET['delete']);
         if($age0)
         {
            if( FS_DEMO )
            {
               $this->new_error_msg('En el modo <b>demo</b> no se pueden eliminar empleados. Otro usuario podría estar usándolo.');
            }
            else if( $age0->delete() )
            {
               $this->new_message("Empleado ".$age0->codagente." eliminado correctamente.");
            }
            else
               $this->new_error_msg("¡Imposible eliminar el empleado!");
         }
         else
            $this->new_error_msg("¡Empleado no encontrado!");
      }
      elseif( isset($_POST['importar']) )
      {
          $this->archivo = $_FILES['empleados'];
          $this->importar_empleados();
      }
   }

   private function importar_empleados(){
        $objPHPExcel = PHPExcel_IOFactory::load($this->archivo['tmp_name']);
        $arrayCabeceras = array('sede','empresa','cifnif','nombreap','apellidos',
            'segundo_apellido','nombre','sexo','estado_civil','f_nacimiento','direccion'
            ,'telefono','f_alta','f_baja','gerencia','area','departamento','cargo','categoria'
            ,'codseguridadsocial','seg_social','dependientes','codformacion','carrera'
            ,'centroestudios','idsindicato','codtipo','pago_total','pago_neto');
        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $worksheetTitle     = $worksheet->getTitle();
            $highestRow         = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
            $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
            $nrColumns = ord($highestColumn) - 64;
            for ($row = 1; $row <= $highestRow; ++ $row) {
                $celda = ($row==1)?'th':'td';
                if($row!=1){
                    for ($col = 0; $col < count($arrayCabeceras); ++$col) {
                        $cell = $worksheet->getCellByColumnAndRow($col, $row);
                        $val = $cell->getValue();
                        if(PHPExcel_Shared_Date::isDateTime($cell)) {
                            $val = date('d-m-Y', PHPExcel_Shared_Date::ExcelToPHP($val));
                        }

                        //$dataType = PHPExcel_Cell_DataType::dataTypeForValue($val);
                        $linea[$arrayCabeceras[$col]]=$val;
                    }
                    $this->resultado[]=$linea;
                }
            }
        }
        $this->template = false;
        header('Content-Type: application/json');
        echo json_encode($this->resultado);
   }
   
   //Para guardar la foto hacemos uso de la libreria de class.upload.php que esta en extras/verot/
   //Con esta libreria estandarizamos todas las imagenes en PNG y les hacemos un resize a 120px
   public function guardar_foto(){
      $this->foto_empleado = $this->agente->get_foto();
      if(file_exists($this->foto_empleado)){
         unlink($this->foto_empleado);
      }
      $newname = str_pad($this->agente->codagente,6,0,STR_PAD_LEFT);

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
         $this->agente->set_foto($newname.".png");
      }else{
         $this->new_error_msg('error : ' . $$this->upload_photo->error);
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
            'page_to' => 'admin_agentes',
            'type' => 'head',
            'text' => '<link rel="stylesheet" type="text/css" media="screen" href="plugins/nomina/view/css/ui.jqgrid-bootstrap.css"/>',
            'params' => ''
         ));
       $fext->save();
   }
}
