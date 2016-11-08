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
 * Description of estadocivil
 *
 * @author Joe Nilson <joenilson at gmail dot com>
 */
class estadocivil extends fs_model{
    /**
     * El codigo a generar del estadocivil
     * @var type $codestadocivil EstadoCivil
     */
    public $codestadocivil;

    /**
     * Se coloca la descripción del estadocivil
     * @var type $descripcion EstadoCivil
     */
    public $descripcion;

    public function __construct($t = FALSE) {
        parent::__construct('hr_estadocivil');
        if($t){
            $this->codestadocivil = $t['codestadocivil'];
            $this->descripcion = $t['descripcion'];
        }else{
            $this->codestadocivil = NULL;
            $this->descripcion = NULL;
        }
    }

    protected function install() {
        return "INSERT INTO ".$this->table_name.
                " (codestadocivil, descripcion) VALUES".
                " ('S','SOLTERO (A)'),".
                " ('CO','CONVIVIENTE'),".
                " ('C','CASADO (A)'),".
                " ('D','DIVORCIADO (A)'),".
                " ('V','VIUDO (A)');";
    }

    public function url()
    {
        return "index.php?page=admin_estadocivil";
    }

    public function get_new_codigo()
    {
        $sql = "SELECT MAX(".$this->db->sql_to_int('codestadocivil').") as cod FROM ".$this->table_name.";";
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
        if(is_null($this->codestadocivil)){
            return false;
        }else{
            return $this->db->select("SELECT * FROM ".$this->table_name." WHERE codestadocivil = ".$this->var2str($this->codestadocivil).";");
        }
    }

    public function save() {
        if($this->exists()){
            return $this->update();
        }else{
            //INSERT DATA
            $sql = "INSERT INTO ".$this->table_name." (codestadocivil, descripcion) VALUES (".
                $this->var2str($this->codestadocivil).", ".
                $this->var2str($this->descripcion).");";
            return $this->db->exec($sql);
        }
    }

    public function update(){
        $sql = "UPDATE ".$this->table_name." SET ".
            " descripcion = ".$this->var2str($this->descripcion).
            " WHERE codestadocivil = ".$this->var2str($this->codestadocivil).";";
        return $this->db->exec($sql);
    }

    public function get($codestadocivil){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE codestadocivil = ".$this->var2str($codestadocivil).";");
        if($data){
            return new estadocivil($data[0]);
        }else{
            return false;
        }
    }

    public function get_by_descripcion($descripcion){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE descripcion = ".$this->var2str($descripcion).";");
        if($data){
            return new estadocivil($data[0]);
        }else{
            return false;
        }
    }

    public function all(){
        $lista = array();
        $data = $this->db->select("SELECT * FROM ".$this->table_name." ORDER BY descripcion;");
        if($data){
            foreach($data as $d){
                $lista[] = new estadocivil($d);
            }
            return $lista;
        }else{
            return false;
        }
    }

    public function delete(){
        $sql = "DELETE FROM ".$this->table_name." WHERE codestadocivil = ".$this->var2str($this->codestadocivil).";";
        return $this->db->exec($sql);
    }

    public function corregir(){
        $sql = "SELECT codestadocivil FROM ".$this->table_name." WHERE descripcion = ".$this->var2str($this->descripcion);
        $data = $this->db->select($sql);
        if($data){
            $this->update();
        }else{
            $this->save();
        }
    }

}
