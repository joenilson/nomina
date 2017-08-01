<?php

/*
 * Copyright (C) 2017 Joe Nilson <joenilson at gmail.com>
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
require_model('bancos.php');
require_model('cuenta_banco.php');
require_model('divisa.php');
require_model('ejercicio.php');
require_model('subcuenta.php');
require_model('tipocuenta.php');

/**
 * Clase para colocar las funciones básicas de los controladores
 * del plugin de nomina_cb
 *
 * @author Joe Nilson <joenilson at gmail.com>
 */
class nomina_controller extends fs_controller {

    /**
     * TRUE si el usuario tiene permisos para eliminar en la página.
     * @var type
     */
    public $allow_delete;

    /**
     * TRUE si hay más de un almacén.
     * @var type
     */
    public $multi_almacen;
    
    public $agente;
    public $bancos;
    public $cuentas_banco;
    public $divisa;
    public $periodos;
    public $meses = array(1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Setiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre');
    public $tipocuenta;
    public $cuenta_banco;
    protected function private_core() 
    {
        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on($this->class_name);

        //Datos de empleado
        $this->agente = new agente();
        
        //Datos de banco
        $this->bancos = new bancos();        
        $this->cuenta_banco = new cuenta_banco();
        $this->divisa = new divisa();
        $this->tipocuenta = new tipocuenta();
        /// ¿Hay más de un almacén?
        $fsvar = new fs_var();
        $this->multi_almacen = $fsvar->simple_get('multi_almacen');
        $this->periodos = range(2016,\date('Y'));
    }
    
    private function buscar_empleado()
    {
        /// desactivamos la plantilla HTML
        $this->template = FALSE;
        $query = filter_input(INPUT_GET, 'buscar_empleado');
        $json = array();
        foreach($this->agente->search($query) as $cli)
        {
            $json[] = array('value' => $cli->nombreap, 'codigo' => $cli->codagente);
        }

        header('Content-Type: application/json');
        echo json_encode( array('query' => $query, 'suggestions' => $json) );
   }

    /**
     * Función para devolver el valor de una variable pasada ya sea por POST o GET
     * @param type string
     * @return type string
     */
    private function filter_request($nombre) {
        $nombre_post = \filter_input(INPUT_POST, $nombre);
        $nombre_get = \filter_input(INPUT_GET, $nombre);
        return ($nombre_post) ? $nombre_post : $nombre_get;
    }

    public function get_subcuentas_pago() {
        $subcuentas_pago = array();

        $eje0 = new ejercicio();
        $ejercicio = $eje0->get_by_fecha($this->today());
        if ($ejercicio) {
            /// añadimos todas las subcuentas de caja
            $sql = "SELECT * FROM co_subcuentas WHERE idcuenta IN "
                    . "(SELECT idcuenta FROM co_cuentas WHERE codejercicio = "
                    . $ejercicio->var2str($ejercicio->codejercicio) . " AND idcuentaesp = 'CAJA');";
            $data = $this->db->select($sql);
            if ($data) {
                foreach ($data as $d) {
                    $subcuentas_pago[] = new subcuenta($d);
                }
            }
        }

        return $subcuentas_pago;
    }

}
