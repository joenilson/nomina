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
require_once('plugins/nomina/extras/PHPExcel/PHPExcel/IOFactory.php');
/**
 * Description of importar_agentes
 *
 * @author Joe Nilson <joenilson at gmail dot com>
 */
class importar_agentes extends fs_controller
{
   public $agente;
   public $archivo;
   public $resultado;
    public function __construct() {
        parent::__construct(__CLASS__, 'Importar Empleados', 'admin', 'false', FALSE, FALSE);
    }

    protected function private_core() {
        $this->share_extensions();
        $this->agente = new agente();

        if( isset($_POST['importar']) )
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
        $l = 0;
        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $worksheetTitle     = $worksheet->getTitle();
            $highestRow         = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
            $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
            $nrColumns = ord($highestColumn) - 64;

            for ($row = 1; $row <= $highestRow; ++$row) {

                if($row!=1){
                    for ($col = 0; $col < count($arrayCabeceras); ++$col) {
                        $cell = $worksheet->getCellByColumnAndRow($col, $row);
                        $val = $cell->getValue();
                        if(PHPExcel_Shared_Date::isDateTime($cell)) {
                            $val = (is_null($val))?'':date('d-m-Y', PHPExcel_Shared_Date::ExcelToPHP($val));
                        }
                        $linea[$arrayCabeceras[$col]]=$val;

                    }
                    $linea['id']=$l;
                    $this->resultado[]=$linea;
                }
                $l++;
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
            'page_to' => 'importar_agentes',
            'type' => 'head',
            'text' => '<link rel="stylesheet" type="text/css" media="screen" href="plugins/nomina/view/css/ui.jqgrid-bootstrap.css"/>',
            'params' => ''
         ));
       $fext->save();
   }
}
