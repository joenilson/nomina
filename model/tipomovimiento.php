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
 * Description of tipomovimiento
 *
 * @author Joe Nilson <joenilson at gmail dot com>
 */
class tipomovimiento extends fs_model{
    /**
     * El codigo a generar del tipomovimiento
     * @var type $codmovimiento TipoMovimiento
     */
    public $codmovimiento;

    /**
     * Se coloca la descripción del tipomovimiento
     * @var type $descripcion TipoMovimiento
     */
    public $descripcion;

    /**
     * Si se va desactivar un tipo de movimiento se debe colocar aquí su estado
     * @var type $estado Boolean
     */
    public $estado;
    
    public function __construct($t = FALSE) {
        parent::__construct('hr_tipomovimiento');
        if($t){
            $this->codmovimiento = $t['codmovimiento'];
            $this->descripcion = $t['descripcion'];
            $this->estado = $this->str2bool($t['estado']);
        }else{
            $this->codmovimiento = NULL;
            $this->descripcion = NULL;
            $this->estado = FALSE;
        }
    }

    protected function install() {
        return "INSERT INTO ".$this->table_name.
                " (codmovimiento, descripcion, estado) VALUES".
                " ('1','CAMBIO DE PUESTO',TRUE),".
                " ('2','CAMBIO DE SEDE NACIONAL',TRUE),".
                " ('3','CAMBIO DE SEDE INTERNACIONAL',TRUE);";
    }

    public function url()
    {
        return "index.php?page=admin_tipomovimientos";
    }

    public function get_new_codigo()
    {
        $sql = "SELECT MAX(".$this->db->sql_to_int('codmovimiento').") as cod FROM ".$this->table_name.";";
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
        if(is_null($this->codmovimiento)){
            return false;
        }else{
            return $this->db->select("SELECT * FROM ".$this->table_name." WHERE codmovimiento = ".$this->var2str($this->codmovimiento).";");
        }
    }

    public function save() {
        if($this->exists()){
            $this->update();
            return true;
        }else{
            //INSERT DATA
            $sql = "INSERT INTO ".$this->table_name." (codmovimiento, descripcion, estado) VALUES (".
                $this->var2str($this->get_new_codigo()).", ".
                $this->var2str($this->descripcion).", ".
                $this->var2str($this->estado).");";
            return $this->db->exec($sql);
        }
    }

    public function update(){
        $sql = "UPDATE ".$this->table_name." SET ".
            " estado = ".$this->var2str($this->estado).
            ", descripcion = ".$this->var2str($this->descripcion).
            " WHERE codmovimiento = ".$this->var2str($this->codmovimiento).";";
        $this->db->exec($sql);
    }

    public function get($codmovimiento){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE codmovimiento = ".$this->var2str($codmovimiento).";");
        if($data){
            return new tipomovimiento($data[0]);
        }else{
            return false;
        }
    }

    public function get_by_descripcion($descripcion){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE descripcion = ".$this->var2str($descripcion).";");
        if($data){
            return new tipomovimiento($data[0]);
        }else{
            return false;
        }
    }

    public function all(){
        $lista = array();
        $data = $this->db->select("SELECT * FROM ".$this->table_name." ORDER BY descripcion;");
        if($data){
            foreach($data as $d){
                $lista[] = new tipomovimiento($d);
            }
            return $lista;
        }else{
            return false;
        }
    }

    public function delete(){
        $sql = "DELETE FROM ".$this->table_name." WHERE codmovimiento = ".$this->codmovimiento.";";
        $data = $this->db->exec($sql);
        if($data){
            return true;
        }else{
            return false;
        }
    }

    public function corregir(){
        $sql = "SELECT codmovimiento FROM ".$this->table_name." WHERE descripcion = ".$this->var2str($this->descripcion);
        $data = $this->db->select($sql);
        if($data){
            $this->update();
        }else{
            $this->save();
        }
    }

}
