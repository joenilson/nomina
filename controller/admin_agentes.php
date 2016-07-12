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
require_once 'helper_nomina.php';
require_once('plugins/nomina/extras/PHPExcel/PHPExcel/IOFactory.php');

class admin_agentes extends fs_controller
{
   public $agente;
   public $archivo;
   public $resultado;
   public $almacen;
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
      
      
      if( isset($_POST['sdnicif']) )
      {
         $age0 = new agente();
         $age0->codagente = $age0->get_new_codigo();
         $age0->nombre = $_POST['snombre'];
         $age0->apellidos = $_POST['sapellidos'];
         $age0->dnicif = $_POST['sdnicif'];
         $age0->telefono = $_POST['stelefono'];
         $age0->email = $_POST['semail'];
         if( $age0->save() )
         {
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
