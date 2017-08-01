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
 * Description of formacion
 *
 * @author Joe Nilson <joenilson at gmail dot com>
 */
class tipodependientes extends fs_model{
    /**
     * El codigo a generar del TipoDependiente
     * @var string $coddependiente TipoDependiente
     */
    public $coddependiente;

    /**
     * Se coloca la descripción del TipoDependiente
     * @var string $descripcion TipoDependiente
     */
    public $descripcion;

    /**
     * Si se va desactivar un tipo de dependiente se debe colocar aquí su estado
     * @var boolean $estado Boolean
     */
    public $estado;
    public function __construct($t = FALSE) {
        parent::__construct('hr_tipodependientes');
        if($t){
            $this->coddependiente = $t['coddependiente'];
            $this->descripcion = $t['descripcion'];
            $this->estado = $this->str2bool($t['estado']);
        }else{
            $this->coddependiente = NULL;
            $this->descripcion = NULL;
            $this->estado = FALSE;
        }
    }

    protected function install() {
        return "INSERT INTO ".$this->table_name.
                " (coddependiente, descripcion, estado) VALUES ".
                " ('1','CONYUGUE',TRUE),".
                " ('2','HIJO (A)',TRUE);";
    }

    public function url()
    {
        return "index.php?page=admin_formacion";
    }

    public function get_new_codigo()
    {
        $sql = "SELECT MAX(".$this->db->sql_to_int('coddependiente').") as cod FROM ".$this->table_name.";";
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
        if(is_null($this->coddependiente)){
            return false;
        }else{
            return $this->db->select("SELECT * FROM ".$this->table_name." WHERE coddependiente = ".$this->var2str($this->coddependiente).";");
        }
    }

    public function save() {
        if($this->exists()){
            return $this->update();
        }else{
            //INSERT DATA
            $sql = "INSERT INTO ".$this->table_name." (coddependiente, descripcion, estado) VALUES (".
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
            " WHERE coddependiente = ".$this->var2str($this->coddependiente).";";
        return $this->db->exec($sql);
    }

    public function get($coddependiente){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE coddependiente = ".$this->var2str($coddependiente).";");
        if($data){
            return new tipodependientes($data[0]);
        }else{
            return false;
        }
    }

    public function get_by_descripcion($descripcion){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE descripcion = ".$this->var2str($descripcion).";");
        if($data){
            return new tipodependientes($data[0]);
        }else{
            return false;
        }
    }

    public function all(){
        $lista = array();
        $data = $this->db->select("SELECT * FROM ".$this->table_name." ORDER BY descripcion;");
        if($data){
            foreach($data as $d){
                $lista[] = new tipodependientes($d);
            }
            return $lista;
        }else{
            return false;
        }
    }

    public function delete(){
        $sql = "DELETE FROM ".$this->table_name." WHERE coddependiente = ".$this->var2str($this->coddependiente).";";
        $data = $this->db->exec($sql);
        if($data){
            return true;
        }else{
            return false;
        }
    }

    public function corregir(){
        $sql = "SELECT coddependiente FROM ".$this->table_name." WHERE descripcion = ".$this->var2str($this->descripcion);
        $data = $this->db->select($sql);
        if($data){
            $this->update();
        }else{
            $this->save();
        }
    }

}
