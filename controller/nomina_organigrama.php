<?php

/*
 * Copyright (C) 2016 Joe Nilson <joenilson at gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
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
require_model('organizacion.php');
/**
 * Description of nomina_organigrama
 *
 * @author Joe Nilson <joenilson at gmail.com>
 */
class nomina_organigrama extends fs_controller{
    public $agente;
    public $agentes;
    public $almacen;
    public $almacenes;
    public $gerencia;
    public $area;
    public $organizacion;
    public $organigrama;
    public $noimagen;
    public function __construct($name = '', $title = 'home', $folder = '', $admin = FALSE, $shmenu = TRUE, $important = FALSE) {
        parent::__construct(__CLASS__, 'Organigrama', 'nomina', FALSE, TRUE, FALSE);
    }
    
    protected function private_core() {
        $this->agente = new agente();
        $this->almacenes = new almacen();
        $this->organizacion = new organizacion();
        $this->noimagen = FS_PATH."plugins/nomina/view/imagenes/no_foto.jpg";
        $this->shared_extensions();
        
        $type= \filter_input(INPUT_GET, 'type');
        if($type){
            switch($type){
                case "organigrama-completo":
                    $this->generar_organigrama();
                    break;
                default:
                    break;
            }
        }
    }
    
    public function generar_organigrama(){
        $codalmacen = filter_input(INPUT_GET, 'almacen');
        $codgerencia = filter_input(INPUT_GET, 'gerencia');
        $codarea = filter_input(INPUT_GET, 'area');
        $opcion = array('almacen'=>$codalmacen,'gerencia'=>$codgerencia,'area'=>$codarea);
        $orgChart = $this->agente->organigrama($opcion);
        $resultado = array();
        $resultado['data'] = array('title'=>$this->empresa->nombrecorto,'name'=>'Empresa','children'=>$orgChart);
        $resultado['depth']=max(array_map('count', $orgChart))-1;
        $this->template = false;
        header('Content-Type: application/json');
        echo json_encode($resultado);
    }
    
    private function shared_extensions(){
        $extensiones_old = array(
            array(
                'name' => 'nomina_organigrama_general_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/nomina.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => 'nomina_organigrama_general_css',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<link rel="stylesheet" type="text/css" media="screen" href="'.FS_PATH.'plugins/nomina/view/css/nomina.css"/>',
                'params' => ''
            ),
            array(
                'name' => 'nomina_organigrama_html2canvas_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/5/html2canvas.min.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => 'nomina_organigrama_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/5/jquery.orgchart.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => 'nomina_organigrama_css',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<link rel="stylesheet" type="text/css" media="screen" href="'.FS_PATH.'plugins/nomina/view/css/jquery.orgchart.css"/>',
                'params' => ''
            ),
            array(
                'name' => 'nomina_organigrama_jquery3_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/0/jquery-3.1.1.min.js" type="text/javascript"></script>',
                'params' => ''
            )            
        );
        
        foreach ($extensiones_old as $ext) {
            $fsext0 = new fs_extension($ext);
            if (!$fsext0->delete()) {
                $this->new_error_msg('Imposible guardar los datos de la extensión ' . $ext['name'] . '.');
            }
        }
        
        $extensiones = array(
            array(
                'name' => '001_nomina_organigrama_css',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<link rel="stylesheet" type="text/css" media="screen" href="'.FS_PATH.'plugins/nomina/view/css/nomina.css"/>',
                'params' => ''
            ),
            array(
                'name' => '002_nomina_organigrama_css',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<link rel="stylesheet" type="text/css" media="screen" href="'.FS_PATH.'plugins/nomina/view/css/jquery.orgchart.css"/>',
                'params' => ''
            ),            
            array(
                'name' => '001_nomina_organigrama_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/5/html2canvas.min.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => '002_nomina_organigrama_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/5/jquery.orgchart.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => '003_nomina_organigrama_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/nomina.js" type="text/javascript"></script>',
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
