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
 * Description of seguridadsocial
 *
 * @author Joe Nilson <joenilson at gmail dot com>
 */
class seguridadsocial extends fs_model{
    /**
     * El codigo a generar del Banco
     * @var type $codseguridadsocial Banco
     */
    public $codseguridadsocial;

    /**
     * Se coloca el nombre del Banco
     * @var type $nombre Banco
     */
    public $nombre;

    /**
     * Aqui ponemos el tipo de Banco, puede ser
     * 1 Banco
     * 2 Cooperativa de Ahorro
     * 3 Asoc. de Ahorro y Prestamo
     * @var type $tipo Banco
     */
    public $tipo;

    /**
     * Si se va desactivar un banco se debe colocar aquí su estado
     * @var type $estado Boolean
     */
    public $estado;
    public function __construct($t = FALSE) {
        parent::__construct('hr_seguridadsocial');
        if($t){
            $this->codseguridadsocial = $t['codseguridadsocial'];
            $this->nombre = $t['nombre'];
            $this->tipo = $t['tipo'];
            $this->estado = $this->str2bool($t['estado']);
        }else{
            $this->codseguridadsocial = NULL;
            $this->nombre = NULL;
            $this->tipo = NULL;
            $this->estado = FALSE;
        }
    }

    protected function install() {
        return "INSERT INTO ".$this->table_name.
                " (codseguridadsocial, nombre, tipo, estado) VALUES".
                " ('1','SEGURIDAD SOCIAL PUBLICA','GOBIERNO',TRUE),".
                " ('2','SEGURO PRIVADO','PRIVADO',TRUE);";
    }

    public function url()
    {
        return "index.php?page=admin_seguridadsocial";
    }

    public function get_new_codigo()
    {
        $sql = "SELECT MAX(".$this->db->sql_to_int('codseguridadsocial').") as cod FROM ".$this->table_name.";";
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
        if(is_null($this->codseguridadsocial)){
            return false;
        }else{
            return $this->db->select("SELECT * FROM ".$this->table_name." WHERE codseguridadsocial = ".$this->var2str($this->codseguridadsocial).";");
        }
    }

    public function save() {
        if($this->exists()){
            $this->update();
        }else{
            //INSERT DATA
            $sql = "INSERT INTO ".$this->table_name." (codseguridadsocial, nombre, tipo, estado) VALUES (".
                $this->var2str($this->get_new_codigo()).", ".
                $this->var2str($this->nombre).", ".
                $this->var2str($this->tipo).", ".
                $this->var2str($this->estado).");";
            return $this->db->exec($sql);
        }
    }

    public function update(){
        $sql = "UPDATE ".$this->table_name." SET ".
            ", estado = ".$this->var2str($this->estado).
            ", tipo = ".$this->var2str($this->tipo).
            ", nombre = ".$this->intval($this->nombre).
            " WHERE codseguridadsocial = ".$this->var2str($this->codseguridadsocial).";";
        return $this->db->exec($sql);
    }

    public function get($codseguridadsocial){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE codseguridadsocial = ".$this->var2str($codseguridadsocial).";");
        if($data){
            return new seguridadsocial($data[0]);
        }else{
            return false;
        }
    }

    public function get_by_nombre($nombre){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE nombre = ".$this->var2str($nombre).";");
        if($data){
            return new seguridadsocial($data[0]);
        }else{
            return false;
        }
    }

    public function get_by_tipo($tipo){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE tipo = ".$this->var2str($tipo).";");
        if($data){
            return new seguridadsocial($data[0]);
        }else{
            return false;
        }
    }

    public function all(){
        $lista = array();
        $data = $this->db->select("SELECT * FROM ".$this->table_name.";");
        if($data){
            foreach($data as $d){
                $lista[] = new seguridadsocial($d);
            }
            return $lista;
        }else{
            return false;
        }
    }

    public function all_activos(){
        $lista = array();
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE estado = TRUE;");
        if($data){
            foreach($data as $d){
                $lista[] = new seguridadsocial($d);
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
        $sql = "SELECT codseguridadsocial FROM ".$this->table_name." WHERE nombre = ".$this->var2str($this->nombre);
        $data = $this->db->select($sql);
        if($data){
            $this->update();
        }else{
            $this->save();
        }
    }

}
