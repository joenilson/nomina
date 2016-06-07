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
 * Description of sindicalizacion
 *
 * @author Joe Nilson <joenilson at gmail dot com>
 */
class sindicalizacion extends fs_model{
    /**
     * El codigo a generar del sindicalizacion
     * @var type $idsindicato TipoEmpleado
     */
    public $idsindicato;

    /**
     * Se coloca la descripción del sindicalizacion
     * @var type $descripcion TipoEmpleado
     */
    public $descripcion;

    /**
     * Si se va desactivar un tipo de empleado se debe colocar aquí su estado
     * @var type $estado Boolean
     */
    public $estado;
    public function __construct($t = FALSE) {
        parent::__construct('hr_sindicalizacion');
        if($t){
            $this->idsindicato = $t['idsindicato'];
            $this->descripcion = $t['descripcion'];
            $this->estado = $this->str2bool($t['estado']);
        }else{
            $this->idsindicato = NULL;
            $this->descripcion = NULL;
            $this->estado = FALSE;
        }
    }

    protected function install() {
        return "INSERT INTO ".$this->table_name.
                " (idsindicato, descripcion, estado) VALUES".
                " ('1','NO AFILIADO',TRUE),".
                " ('2','UNIFICADO',TRUE);";
    }

    public function url()
    {
        return "index.php?page=admin_sindicalizacion";
    }

    public function get_new_codigo()
    {
        $sql = "SELECT MAX(".$this->db->sql_to_int('idsindicato').") as cod FROM ".$this->table_name.";";
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
        if(is_null($this->idsindicato)){
            return false;
        }else{
            return $this->db->select("SELECT * FROM ".$this->table_name." WHERE idsindicato = ".$this->var2str($this->idsindicato).";");
        }
    }

    public function save() {
        if($this->exists()){
            $this->update();
        }else{
            //INSERT DATA
            $sql = "INSERT INTO ".$this->table_name." (idsindicato, descripcion, estado) VALUES (".
                $this->var2str($this->get_new_codigo()).", ".
                $this->var2str($this->descripcion).", ".
                $this->var2str($this->estado).");";
            return $this->db->exec($sql);
        }
    }

    public function update(){
        $sql = "UPDATE ".$this->table_name." SET ".
            ", estado = ".$this->var2str($this->estado).
            ", descripcion = ".$this->intval($this->descripcion).
            " WHERE idsindicato = ".$this->var2str($this->idsindicato).";";
        return $this->db->exec($sql);
    }

    public function get($idsindicato){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE idsindicato = ".$this->var2str($idsindicato).";");
        if($data){
            return new sindicalizacion($data[0]);
        }else{
            return false;
        }
    }

    public function get_by_descripcion($descripcion){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE descripcion = ".$this->var2str($descripcion).";");
        if($data){
            return new sindicalizacion($data[0]);
        }else{
            return false;
        }
    }

    public function all(){
        $lista = array();
        $data = $this->db->select("SELECT * FROM ".$this->table_name.";");
        if($data){
            foreach($data as $d){
                $lista[] = new sindicalizacion($d);
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
        $sql = "SELECT idsindicato FROM ".$this->table_name." WHERE descripcion = ".$this->var2str($this->descripcion);
        $data = $this->db->select($sql);
        if($data){
            $this->update();
        }else{
            $this->save();
        }
    }

}
