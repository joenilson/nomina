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
/**
 * Description of configuracion_nomina
 *
 * @author Joe Nilson <joenilson at gmail dot com>
 */
class configuracion_nomina extends fs_controller{
    public $agente;
    public $cargo;
    public $cargos;
    public function __construct() {
        parent::__construct(__CLASS__, 'Configuracion Nomina', 'nomina', TRUE, FALSE, FALSE);
    }

    protected function private_core() {
        $this->agente = new agente();
        $this->cargo = new cargos();
        //Cargamos los datos por primera vez
        $this->fix_info();
        //Movemos Cargo a la tabla de cargos y banco al campo cuenta_banco
        $this->trasladar_datos();
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
}
