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
        $this->fix_cargo();
    }

    protected function fix_cargo(){
        $agentes = $this->agente->all();
        foreach($agentes as $agente){
            if(is_null($agente->codcargo)){
                //$this->agente->seg_social;
            }
        }
    }
}
