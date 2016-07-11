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
require_model('cargos.php');
require_model('bancos.php');
require_model('seguridadsocial.php');
require_model('tipoempleado.php');
require_model('categoriaempleado.php');
require_model('sindicalizacion.php');
require_model('formacion.php');
require_model('organizacion.php');
require_once 'plugins/nomina/extras/verot/class.upload.php';

class admin_agente extends fs_controller
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
   private $dir_empleados = "tmp/".\FS_TMP_NAME."/nomina/empleados/";

   /*
    * Esta página está en la carpeta admin, pero no se necesita ser admin para usarla.
    * Está en la carpeta admin porque su antecesora también lo está (y debe estarlo).
    */
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Empleado', 'admin', FALSE, FALSE);
   }

   protected function private_core()
   {
      $this->ppage = $this->page->get('admin_agentes');

      /// ¿El usuario tiene permiso para eliminar en esta página?
      $this->allow_delete = $this->user->allow_delete_on(__CLASS__);

      $this->almacen = new almacen();
      $this->bancos = new bancos();
      $this->cargos = new cargos();
      $this->formacion = new formacion();
      $this->tipoempleado = new tipoempleado();
      $this->categoriaempleado = new categoriaempleado();
      $this->sindicalizacion = new sindicalizacion();
      $this->organizacion = new organizacion();
      $this->seguridadsocial = new seguridadsocial();

      $this->agente = FALSE;
      if( isset($_GET['cod']) )
      {
         $agente = new agente();
         $this->agente = $agente->get($_GET['cod']);
      }

      if( isset($_GET['type']) ){
          $type = filter_input(INPUT_GET, 'type');
          switch ($type){
              case "organizacion";
                $this->buscar_organizacion();
                break;
              default:
                break;
          }
      }

      if($this->agente)
      {
         $this->page->title .= ' ' . $this->agente->codagente;

         if( isset($_POST['nombre']) )
         {
            //En la edición solo se permite campos no sensibles genéricos
            if( $this->user_can_edit() )
            {
               $this->agente->nombre = $_POST['nombre'];
               $this->agente->apellidos = $_POST['apellidos'];
               $this->agente->segundo_apellido = $_POST['segundo_apellido'];
               $this->agente->dnicif = $_POST['dnicif'];
               $this->agente->telefono = $_POST['telefono'];
               $this->agente->email = $_POST['email'];
               $this->agente->provincia = $_POST['provincia'];
               $this->agente->ciudad = $_POST['ciudad'];
               $this->agente->direccion = $_POST['direccion'];
               $this->agente->sexo = $_POST['sexo'];

               $this->agente->f_nacimiento = NULL;
               if($_POST['f_nacimiento'] != '')
               {
                  $this->agente->f_nacimiento = $_POST['f_nacimiento'];
               }

               $this->agente->f_alta = NULL;
               if($_POST['f_alta'] != '')
               {
                  $this->agente->f_alta = $_POST['f_alta'];
               }

               $this->agente->f_baja = NULL;
               if($_POST['f_baja'] != '')
               {
                  $this->agente->f_baja = $_POST['f_baja'];
               }

               $this->agente->codcategoria = $_POST['codcategoria'];
               $this->agente->codtipo = $_POST['codtipo'];
               $this->agente->codsupervisor = $_POST['codsupervisor'];
               $this->agente->codgerencia = $_POST['codgerencia'];
               $this->agente->codcargo = $_POST['codcargo'];
               $this->agente->codarea = $_POST['codarea'];
               $this->agente->coddepartamento = $_POST['coddepartamento'];
               $this->agente->codformacion = $_POST['codformacion'];
               $this->agente->codseguridadsocial = $_POST['codseguridadsocial'];
               $this->agente->seg_social = $_POST['seg_social'];
               $this->agente->codbanco = $_POST['codbanco'];
               $this->agente->cuenta_banco = $_POST['cuenta_banco'];
               $this->agente->porcomision = floatval($_POST['porcomision']);
               $this->agente->dependientes = $_POST['dependientes'];
               $this->agente->idsindicato = $_POST['idsindicalizado'];
               $this->agente->estado = $_POST['estado'];
               $this->agente->estado_civil = $_POST['estado_civil'];

               if( $this->agente->save() )
               {
                  $this->upload_photo = new Upload($_FILES['foto']);
                  if ($this->upload_photo->uploaded) {
                      $this->guardar_foto();
                  }
                  $this->new_message("Datos del empleado guardados correctamente.");
               }
               else
                  $this->new_error_msg("¡Imposible guardar los datos del empleado!");
            }
            else
               $this->new_error_msg('No tienes permiso para modificar estos datos.');
         }
      }
      else
         $this->new_error_msg("Empleado no encontrado.");
   }

   private function user_can_edit()
   {
      if(FS_DEMO)
      {
         return ($this->user->codagente == $this->agente->codagente);
      }
      else
      {
         return TRUE;
      }
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

   public function buscar_organizacion(){
        $tipo = false;
        if(isset($_GET['codgerencia'])){
            $codigo = filter_input(INPUT_GET, 'codgerencia');
            $tipo = 'AREA';
        }elseif(isset($_GET['codarea'])){
            $codigo = filter_input(INPUT_GET, 'codarea');
            $tipo = 'DEPARTAMENTO';
        }
        $resultado = ($tipo) ? $this->organizacion->get_by_padre($tipo, $codigo):false;
        $this->template = FALSE;
        header('Content-Type: application/json');
        echo json_encode($resultado);
   }

   public function url()
   {
      if( !isset($this->agente) )
      {
         return parent::url();
      }
      else if($this->agente)
      {
         return $this->agente->url();
      }
      else
         return $this->page->url();
   }
}
