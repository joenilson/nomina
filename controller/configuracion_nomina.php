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
require_model('cargos.php');
require_model('organizacion.php');
require_model('tipoempleado.php');
require_model('categoriaempleado.php');
require_model('sindicalizacion.php');
/**
 * Description of configuracion_nomina
 *
 * @author Joe Nilson <joenilson at gmail dot com>
 */
class configuracion_nomina extends fs_controller{
    public $agente;
    public $cargo;
    public $cargos;
    public $tipoempleado;
    public $categoriaempleado;
    public $sindicalizacion;
    public $organizacion;
    public function __construct() {
        parent::__construct(__CLASS__, 'Configuracion Nomina', 'nomina', TRUE, FALSE, FALSE);
    }

    protected function private_core() {
        $this->share_extensions();
        $this->agente = new agente();
        $this->cargo = new cargos();
        $this->tipoempleado = new tipoempleado();
        $this->categoriaempleado = new categoriaempleado();
        $this->sindicalizacion = new sindicalizacion();
        $this->organizacion = new organizacion();
        //Cargamos los datos por primera vez
        $this->fix_info();
        //Movemos Cargo a la tabla de cargos y banco al campo cuenta_banco
        $this->trasladar_datos();
        //Validamos si existen las carpetas de almacenamiento de datos
        // imagenes de empleados
        // archivos generados
        // formatos de presentacion
        //@TODO
    }

    protected function fix_info(){
        $agentes = $this->agente->all();
        foreach($agentes as $agente){
            if(is_null($agente->idempresa)){
                $agente->codalmacen = 'ALG';
                $agente->idempresa = $this->empresa->id;
                $agente->fecha_creacion = $agente->f_alta;
                $agente->usuario_creacion = $this->user->nick;
                $agente->corregir();
            }
        }
    }

    protected function trasladar_datos(){
        $agentes = $this->agente->all();
        $this->cargos = array();
        foreach($agentes as $agente){
            $this->cargos[$agente->cargo] = $agente->cargo;
            if(empty($agente->cuenta_banco)){
                $agente->codalmacen = 'ALG';
                $agente->idempresa = $this->empresa->id;
                $agente->cuenta_banco = $agente->banco;
                $agente->fecha_creacion = $agente->f_alta;
                $agente->usuario_creacion = $this->user->nick;
                $agente->corregir();
            }
        }

        foreach($this->cargos as $cargo){
            if($cargo){
                $c0 = new cargos();
                $c0->descripcion = strtoupper(trim($cargo));
                $c0->padre = NULL;
                $c0->estado = TRUE;
                $c0->corregir();
            }
        }

        $lista_cargos = $this->cargo->all();
        foreach ($agentes as $agente){
            $c0 = $this->cargo->get_by_descripcion(strtoupper(trim($agente->cargo)));
            if(is_null($agente->codcargo) AND ($c0)){
                $agente->codcargo = $c0->codcargo;
                $agente->corregir();
            }
        }
    }
    
    public function share_extensions(){
        $fsext1 = new fs_extension(array(
            'name' => 'cargar_empleados_button',
            'page_from' => __CLASS__,
            'page_to' => 'admin_agentes',
            'type' => 'button',
            'text' => '<span class="fa fa-upload" aria-hidden="true"></span> &nbsp; Cargar Empleados',
            'params' => ''
        ));
        $fsext1->delete();
        $extensiones = array(
            array(
                'name' => 'cargar_empleados_button',
                'page_from' => 'importar_agentes',
                'page_to' => 'admin_agentes',
                'type' => 'button',
                'text' => '<span class="fa fa-upload" aria-hidden="true"></span> &nbsp; Cargar Empleados',
                'params' => ''
            ),
            array(
                'name' => 'nuevo_agente_js',
                'page_from' => __CLASS__,
                'page_to' => 'admin_agente',
                'type' => 'head',
                'text' => '<script src="plugins/nomina/view/js/nomina.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => 'nuevo_agente_css',
                'page_from' => __CLASS__,
                'page_to' => 'admin_agente',
                'type' => 'head',
                'text' => '<link rel="stylesheet" type="text/css" media="screen" href="plugins/nomina/view/css/nomina.css"/>',
                'params' => ''
            ),
            array(
                'name' => 'nuevo_empleado_js',
                'page_from' => __CLASS__,
                'page_to' => 'admin_agentes',
                'type' => 'head',
                'text' => '<script src="plugins/nomina/view/js/nomina.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => 'nuevo_empleado_css',
                'page_from' => __CLASS__,
                'page_to' => 'admin_agentes',
                'type' => 'head',
                'text' => '<link rel="stylesheet" type="text/css" media="screen" href="plugins/nomina/view/css/nomina.css"/>',
                'params' => ''
            ),
            array(
                'name' => 'movimientos_empleado',
                'page_from' => 'admin_agente',
                'page_to' => 'admin_agente',
                'type' => 'tab',
                'text' => '<span class="fa fa-code-fork" aria-hidden="true"></span> &nbsp; Movimientos',
                'params' => '&type=movimientos'
            ),
            array(
                'name' => 'contratos_empleado',
                'page_from' => 'admin_agente',
                'page_to' => 'admin_agente',
                'type' => 'tab',
                'text' => '<span class="fa fa-archive" aria-hidden="true"></span> &nbsp; Contratos',
                'params' => '&type=contratos'
            ),
            array(
                'name' => 'ausencias_empleado',
                'page_from' => 'admin_agente',
                'page_to' => 'admin_agente',
                'type' => 'tab',
                'text' => '<span class="fa fa-calendar-minus-o" aria-hidden="true"></span> &nbsp; Ausencias',
                'params' => '&type=ausencias'
            ),
            array(
                'name' => 'carga_familiar_empleado',
                'page_from' => 'admin_agente',
                'page_to' => 'admin_agente',
                'type' => 'tab',
                'text' => '<span class="fa fa-group" aria-hidden="true"></span> &nbsp; Carga Familiar',
                'params' => '&type=carga_familiar'
            ),
            array(
                'name' => 'hoja_vida_empleado',
                'page_from' => 'admin_agente',
                'page_to' => 'admin_agente',
                'type' => 'tab',
                'text' => '<span class="fa fa-suitcase" aria-hidden="true"></span> &nbsp; Hoja de Vida',
                'params' => '&type=hoja_vida'
            ),
            array(
                'name' => 'pagos_incentivos_empleado',
                'page_from' => 'admin_agente',
                'page_to' => 'admin_agente',
                'type' => 'tab',
                'text' => '<span class="fa fa-money" aria-hidden="true"></span> &nbsp; Pagos e Incentivos',
                'params' => '&type=pagos_incentivos'
            ),
            array(
                'name' => 'control_horas_empleado',
                'page_from' => 'admin_agente',
                'page_to' => 'admin_agente',
                'type' => 'tab',
                'text' => '<span class="fa fa-clock-o" aria-hidden="true"></span> &nbsp; Control de Horas',
                'params' => '&type=control_horas'
            ),
            array(
                'name' => 'importar_agentes_js',
                'page_from' => __CLASS__,
                'page_to' => 'importar_agentes',
                'type' => 'head',
                'text' => '<script src="plugins/nomina/view/js/nomina.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => 'importar_agentes_css',
                'page_from' => __CLASS__,
                'page_to' => 'importar_agentes',
                'type' => 'head',
                'text' => '<link rel="stylesheet" type="text/css" media="screen" href="plugins/nomina/view/css/nomina.css"/>',
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
