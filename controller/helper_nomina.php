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
require_model('seguridadsocial.php');
require_model('tipoempleado.php');
require_model('categoriaempleado.php');
require_model('sindicalizacion.php');
require_model('formacion.php');
require_model('organizacion.php');
/**
 * Description of helper_nomina
 *
 * @author Joe Nilson <joenilson at gmail.com>
 */
class helper_nomina extends fs_controller{
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
    
    public function __construct() {
        parent::__construct(__CLASS__, 'Helper Nomina', 'admin', FALSE, FALSE, FALSE);
    }
    
    protected function private_core() {
        $this->almacen = new almacen();
        $this->bancos = new bancos();
        $this->cargos = new cargos();
        $this->formacion = new formacion();
        $this->tipoempleado = new tipoempleado();
        $this->categoriaempleado = new categoriaempleado();
        $this->sindicalizacion = new sindicalizacion();
        $this->organizacion = new organizacion();
        $this->seguridadsocial = new seguridadsocial();
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
}
