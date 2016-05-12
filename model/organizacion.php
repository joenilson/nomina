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

/**
 * Description of HR Organizacion
 *
 * @author Joe Nilson <joenilson at gmail dot com>
 */
class organizacion extends fs_model{
    /**
     * El codigo a generar para la organizacion
     * @var type $codorganizacion Organizacion
     */
    public $codorganizacion;
    /**
     * Si este codigo tiene un codigo superior se coloca aquí
     * @var type $padre Organizacion
     */
    public $padre;

    /**
     * Se coloca la descripción de la Organizacion
     * @var type $descripcion Organizacion
     */
    public $descripcion;

    /**
     * Aqui se pone si es del siguiente tipo:
     * GERENCIA
     * AREA
     * DEPARTAMENTO
     * en ese orden en especifico seria lo deseable,
     * para otro tipo de jerarquias se puede seguir agregando aquí y generando selects
     * dinámicamente, pero ya pertenece a otra pelicula de Conan el barbaro
     * @var type $tipo Organizacion
     */
    public $tipo;

    /**
     * Si se va desactivar un punto de la Organizacion se debe colocar aquí su estado
     * teniendo cuidado de no desactivar una gerencia que tenga areas o departamentos
     * asignados, por lo que antes de cambiar el estado se hará una verificacion
     * @var type $estado Boolean
     */
    public $estado;

    public function __construct($t = FALSE) {
        parent::__construct('hr_organizacion');
        if($t){
            $this->codorganizacion = $t['codorganizacion'];
            $this->descripcion = $t['descripcion'];
            $this->padre = $t['padre'];
            $this->tipo = $t['tipo'];
            $this->estado = $this->str2bool($t['estado']);
        }else{
            $this->codorganizacion = NULL;
            $this->descripcion = NULL;
            $this->padre = NULL;
            $this->tipo = NULL;
            $this->estado = FALSE;
        }
    }

    protected function install() {
            return "INSERT INTO ".$this->table_name." (codorganizacion, descripcion, padre, tipo, estado) VALUES".
                " ('1','GERENCIA GENERAL',NULL,'GERENCIA',TRUE),".
                " ('2','GERENCIA DE ADM Y FINANZAS','1','GERENCIA',TRUE),".
                " ('3','GERENCIA DE COMERCIAL','1','GERENCIA',TRUE),".
                " ('4','CONTABILIDAD','2','AREA',TRUE),".
                " ('5','VENTAS','3','AREA',TRUE), ".
                " ('6','ADMINISTRACION','2','AREA',TRUE), ".
                " ('7','TESORERIA','4','DEPARTAMENTO',TRUE), ".
                " ('8','CUENTAS POR PAGAR','4','DEPARTAMENTO',TRUE), ".
                " ('9','DESPACHO','5','DEPARTAMENTO',TRUE), ".
                " ('10','CUENTAS POR COBRAR','5','DEPARTAMENTO',TRUE), ".
                " ('11','RECEPCION','6','DEPARTAMENTO',TRUE), ".
                " ('12','MENSAJERIA','6','DEPARTAMENTO',TRUE), ".
                " ('13','COMPRAS','6','DEPARTAMENTO',TRUE);";
    }

    public function url()
    {
        if( is_null($this->codorganizacion) )
        {
            return "index.php?page=admin_organizacion";
        }
        else
        {
            return "index.php?page=admin_organizacion&cod=".$this->codorganizacion;
        }
    }

    public function get_new_codigo()
    {
        $sql = "SELECT MAX(".$this->db->sql_to_int('codorganizacion').") as cod FROM ".$this->table_name.";";
        $cod = $this->db->select($sql);
        if($cod)
        {
            return 1 + intval($cod[0]['cod']);
        }
        else
        {
            return 1;
        }
    }

    public function exists() {
        if(is_null($this->codorganizacion)){
            return false;
        }else{
            return $this->db->select("SELECT * FROM ".$this->table_name." WHERE ".
                " codorganizacion = ".$this->var2str($this->codorganizacion).
                "AND tipo = ".$this->var2str($this->tipo).";");
        }
    }

    public function save() {
        if($this->exists()){
            $this->update();
        }else{
            //INSERT DATA
            $sql = "INSERT INTO ".$this->table_name." (codorganizacion, descripcion, padre, tipo, estado) VALUES (".
                $this->var2str($this->get_new_codigo()).", ".
                $this->var2str($this->descripcion).", ".
                $this->var2str($this->padre).", ".
                $this->var2str($this->tipo).", ".
                $this->var2str($this->estado).");";
            return $this->db->exec($sql);
        }
    }

    public function update(){
        $sql = "UPDATE ".$this->table_name." SET ".
            " padre = ".$this->var2str($this->padre).
            ", estado = ".$this->var2str($this->estado).
            ", descripcion = ".$this->intval($this->descripcion).
            " WHERE codorganizacion = ".$this->var2str($this->codorganizacion)." AND tipo = ".$this->var2str($this->tipo).";";
        return $this->db->exec($sql);
    }

    public function get($codorganizacion){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE codorganizacion = ".$this->var2str($codorganizacion).";");
        if($data){
            return new organizacion($data[0]);
        }else{
            return false;
        }
    }

    public function get_by_descripcion($descripcion){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE descripcion = ".$this->var2str($descripcion).";");
        if($data){
            return new organizacion($data[0]);
        }else{
            return false;
        }
    }

    public function all(){
        $lista = array();
        $data = $this->db->select("SELECT * FROM ".$this->table_name.";");
        if($data){
            foreach($data as $d){
                $lista[] = new organizacion($d);
            }
            return $lista;
        }else{
            return false;
        }
    }

    public function all_tipo($tipo){
        $lista = array();
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE tipo = ".$this->var2str($tipo).";");
        if($data){
            foreach($data as $d){
                $lista[] = new organizacion($d);
            }
            return $lista;
        }else{
            return false;
        }
    }

    public function delete(){
        return false;
    }

    public function corregir(){
        $sql = "SELECT codorganizacion FROM ".$this->table_name." WHERE descripcion = ".$this->var2str($this->descripcion);
        $data = $this->db->select($sql);
        if($data){
            $this->update();
        }else{
            $this->save();
        }
    }

}
