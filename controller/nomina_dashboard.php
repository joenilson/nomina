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
require_model('cargos.php');
require_model('bancos.php');
require_model('dependientes.php');
require_model('generaciones.php');
require_model('seguridadsocial.php');
require_model('tipoempleado.php');
require_model('categoriaempleado.php');
require_model('sindicalizacion.php');
require_model('formacion.php');
require_model('organizacion.php');
/**
 * Description of nomina_dashboard
 *
 * @author Joe Nilson <joenilson at gmail.com>
 */
class nomina_dashboard extends fs_controller{

    public $logo_empresa;
    public $agentes;
    public $almacen;
    public $cargos;
    public $dependientes;
    public $generaciones;
    public $seguridadsocial;
    public $tipoempleado;
    public $categoriaempleado;
    public $sindicalizacion;
    public $organizacion;
    public $lista_anual_total;
    public $lista_anual_alta;
    public $lista_anual_baja;
    public function __construct() {
        parent::__construct(__CLASS__, 'Nomina Dashboard', 'nomina', FALSE, TRUE, FALSE);
    }

    protected function private_core() {
        $this->share_extensions();
        $this->agentes = new agente();
        $this->almacen = new organizacion();
        $this->cargos = new cargos();
        $this->dependientes = new dependientes();
        $this->seguridadsocial = new seguridadsocial();
        $this->tipoempleado = new tipoempleado();
        $this->categoriaempleado = new categoriaempleado();
        $this->sindicalizacion = new sindicalizacion();
        $this->organizacion = new organizacion();
        $this->generaciones = new generaciones();
        if(isset($_REQUEST['type'])){
            $type_g = filter_input(INPUT_GET, 'type');
            $type_p = filter_input(INPUT_POST, 'type');
            $type = (!empty($type_g))?$type_g:$type_p;
            switch ($type){
                case 'grafico':
                    $this->generar_grafico();
                    break;
                default:
                    break;
            }

        }
        $this->logo_empresa = FS_PATH.FS_MYDOCS."images/logo.png";
    }

    public function generar_grafico(){
        $backgroundColor = array(
        'rgba(255, 99, 132, 0.2)',
        'rgba(54, 162, 235, 0.2)',
        'rgba(255, 206, 86, 0.2)',
        'rgba(75, 192, 192, 0.2)',
        'rgba(153, 102, 255, 0.2)',
        'rgba(255, 159, 64, 0.2)');
        $borderColor = array(
        'rgba(255,99,132,1)',
        'rgba(54, 162, 235, 1)',
        'rgba(255, 206, 86, 1)',
        'rgba(75, 192, 192, 1)',
        'rgba(153, 102, 255, 1)',
        'rgba(255, 159, 64, 1)');
        $subtype_g = filter_input(INPUT_GET, 'subtype');
        $subtype_p = filter_input(INPUT_POST, 'subtype');
        $subtype = (!empty($subtype_g))?$subtype_g:$subtype_p;
        switch ($subtype){
            case "resumen-empleados":
                /**
                 * @todo Generar el resumen de empleados
                 */
                break;
            case "resumen-dependientes":
                $res = $this->dependientes->resumen_dependientes();
                $data = array();
                $labels = array();
                if($res){
                    foreach($res as $values){
                        $labels[] = $values->descripcion." - ".$values->genero;
                        $data[] = $values->cantidad;
                    }
                }
                $resultado =array('datasets'=>array('data'=>$data,'backgroundColor'=>array("#FF6384","#008CBA","#FF6384"),'hoverBackgroundColor'=>array("#FF6384",
                            "#008CBA","#FF6384")),'labels'=>$labels,'backgroundColor'=>$backgroundColor,'borderColor'=>$borderColor);
                break;
                break;
            case "resumen-generacion":
                $res = $this->generaciones->resumen_generaciones();
                $data = "";
                $labels = "";
                foreach($res as $values){
                    $labels[] = $values->descripcion;
                    $data[] = $values->cantidad;
                }
                $resultado =array('datasets'=>array('data'=>$data,'backgroundColor'=>array("#FF6384","#008CBA","#FF6384"),'hoverBackgroundColor'=>array("#FF6384",
                            "#008CBA","#FF6384")),'labels'=>$labels,'backgroundColor'=>$backgroundColor,'borderColor'=>$borderColor);
                break;
        }

        $this->template = false;
        header('Content-Type: application/json');
        echo json_encode($resultado,JSON_NUMERIC_CHECK);
    }

    //Empleados por Año
    public function estadistica_anual(){
        $lista_anual_total = array();
        $lista_anual_alta = array();
        $lista_anual_baja = array();
        foreach($this->agentes->all() as $dato){
            $year = date('Y', strtotime($dato->f_alta));
            $lista_anual_total[$year]+=1;
            if($dato->estado == 'A'){
                $lista_anual_alta[$year]+=1;
            }elseif($dato->f_baja != null){
                $year_b = date('Y', strtotime($dato->f_baja));
                $lista_anual_baja[$year_b]+=1;
            }
        }
    }

    public function share_extensions(){
        $extensiones_old = array(
            array(
                'name' => 'nomina_empleado_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/nomina.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => 'nomina_empleado_css',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<link rel="stylesheet" type="text/css" media="screen" href="'.FS_PATH.'plugins/nomina/view/css/nomina.css"/>',
                'params' => ''
            ),
            array(
                'name' => 'chartist_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/chartist.min.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => 'chartist_css',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<link rel="stylesheet" type="text/css" media="screen" href="'.FS_PATH.'plugins/nomina/view/css/chartist.min.css"/>',
                'params' => ''
            ),
            array(
                'name' => 'chartjs_css',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/4/Chart.min.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => '003_nomina_dashboard_css',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<link rel="stylesheet" type="text/css" media="screen" href="'.FS_PATH.'plugins/nomina/view/css/nomina.css"/>',
                'params' => ''
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
                'name' => '001_nomina_dashboard_css',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<link rel="stylesheet" type="text/css" media="screen" href="'.FS_PATH.'plugins/nomina/view/css/chartist.min.css"/>',
                'params' => ''
            ),
            array(
                'name' => '002_nomina_dashboard_css',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<link rel="stylesheet" type="text/css" media="screen" href="'.FS_PATH.'plugins/nomina/view/css/nomina.css"/>',
                'params' => ''
            ),
            array(
                'name' => '001_nomina_dashboard_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/chartist.min.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => '002_nomina_dashboard_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/4/Chart.min.js" type="text/javascript"></script>',
                'params' => ''
            ),            
            array(
                'name' => '003_nomina_dashboard_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/nomina.js" type="text/javascript"></script>',
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
