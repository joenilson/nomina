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
class formacion extends fs_model{
    /**
     * El codigo a generar del formacion
     * @var type $codformacion Formacion
     */
    public $codformacion;

    /**
     * Se coloca la descripción del formacion
     * @var type $nombre Formacion
     */
    public $nombre;

    /**
     * Si se va desactivar un tipo de formacion se debe colocar aquí su estado
     * @var type $estado Boolean
     */
    public $estado;
    public function __construct($t = FALSE) {
        parent::__construct('hr_formacion');
        if($t){
            $this->codformacion = $t['codformacion'];
            $this->nombre = $t['nombre'];
            $this->estado = $this->str2bool($t['estado']);
        }else{
            $this->codformacion = NULL;
            $this->nombre = NULL;
            $this->estado = FALSE;
        }
    }

    protected function install() {
        return "INSERT INTO ".$this->table_name.
                " (codformacion, nombre, estado) VALUES".
                " ('1','BASICA',TRUE),".
                " ('2','MEDIA',TRUE),".
                " ('3','TECNICA',TRUE),".
                " ('4','UNIVERSITARIA',TRUE),".
                " ('5','MAESTRIA',TRUE),".
                " ('6','DOCTORADO',TRUE);";
    }

    public function url()
    {
        return "index.php?page=admin_formacion";
    }

    public function get_new_codigo()
    {
        $sql = "SELECT MAX(".$this->db->sql_to_int('codformacion').") as cod FROM ".$this->table_name.";";
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
        if(is_null($this->codformacion)){
            return false;
        }else{
            return $this->db->select("SELECT * FROM ".$this->table_name." WHERE codformacion = ".$this->var2str($this->codformacion).";");
        }
    }

    public function save() {
        if($this->exists()){
            $this->update();
            return true;
        }else{
            //INSERT DATA
            $sql = "INSERT INTO ".$this->table_name." (codformacion, nombre, estado) VALUES (".
                $this->var2str($this->get_new_codigo()).", ".
                $this->var2str($this->nombre).", ".
                $this->var2str($this->estado).");";
            return $this->db->exec($sql);
        }
    }

    public function update(){
        $sql = "UPDATE ".$this->table_name." SET ".
            " estado = ".$this->var2str($this->estado).
            ", nombre = ".$this->var2str($this->nombre).
            " WHERE codformacion = ".$this->var2str($this->codformacion).";";
        $this->db->exec($sql);
    }

    public function get($codformacion){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE codformacion = ".$this->var2str($codformacion).";");
        if($data){
            return new formacion($data[0]);
        }else{
            return false;
        }
    }

    public function get_by_nombre($nombre){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE nombre = ".$this->var2str($nombre).";");
        if($data){
            return new formacion($data[0]);
        }else{
            return false;
        }
    }

    public function all(){
        $lista = array();
        $data = $this->db->select("SELECT * FROM ".$this->table_name." ORDER BY nombre;");
        if($data){
            foreach($data as $d){
                $lista[] = new formacion($d);
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
        $sql = "SELECT codformacion FROM ".$this->table_name." WHERE nombre = ".$this->var2str($this->nombre);
        $data = $this->db->select($sql);
        if($data){
            $this->update();
        }else{
            $this->save();
        }
    }

}
