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
 * Description of sistemapension
 *
 * @author Joe Nilson <joenilson at gmail dot com>
 */
class sistemapension extends fs_model{
    /**
     * El codigo a generar de Sistema de Pensión o Jubilación
     * @var type $codsistemapension SistemaPension
     */
    public $codsistemapension;

    /**
     * Se coloca el nombre de la Sistema de Pension
     * @var type $nombre Sistema de Pension
     */
    public $nombre;

    /**
     * Se coloca la abreviatura de la Sistema de Pension
     * @var type $nombre_corto
     */
    public $nombre_corto;

    /**
     * Aqui ponemos el tipo de Sistema de Pension, puede ser
     * PRIVADO
     * PUBLICO
     * @var type $tipo Sistema de Pension
     */
    public $tipo;

    /**
     * Si se va desactivar un registro se debe colocar aquí su estado
     * @var type $estado Boolean
     */
    public $estado;
    public function __construct($t = FALSE) {
        parent::__construct('hr_sistemapension');
        if($t){
            $this->codsistemapension = $t['codsistemapension'];
            $this->nombre = $t['nombre'];
            $this->nombre_corto = $t['nombre_corto'];
            $this->tipo = $t['tipo'];
            $this->estado = $this->str2bool($t['estado']);
        }else{
            $this->codsistemapension = NULL;
            $this->nombre = NULL;
            $this->nombre_corto = NULL;
            $this->tipo = NULL;
            $this->estado = FALSE;
        }
    }

    protected function install() {
        return "INSERT INTO ".$this->table_name.
            " (codsistemapension, nombre, tipo, estado, nombre_corto) VALUES".
            " ('1','SISTEMA NACIONAL DE PENSIONES','PUBLICO',TRUE, '0'),".
            " ('2','ASEGURADORA DE FONDO DE PENSIONES','PRIVADO',TRUE, '0');";
    }

    public function url()
    {
        return "index.php?page=admin_sistemapension";
    }

    public function get_new_codigo()
    {
        $sql = "SELECT MAX(".$this->db->sql_to_int('codsistemapension').") as cod FROM ".$this->table_name.";";
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
        if(is_null($this->codsistemapension)){
            return false;
        }else{
            return $this->db->select("SELECT * FROM ".$this->table_name." WHERE codsistemapension = ".$this->var2str($this->codsistemapension).";");
        }
    }

    public function save() {
        if($this->exists()){
            return $this->update();
        }else{
            //INSERT DATA
            $sql = "INSERT INTO ".$this->table_name." (codsistemapension, nombre, nombre_corto, tipo, estado) VALUES (".
                $this->var2str($this->get_new_codigo()).", ".
                $this->var2str($this->nombre).", ".
                $this->var2str($this->nombre_corto).", ".
                $this->var2str($this->tipo).", ".
                $this->var2str($this->estado).");";
            return $this->db->exec($sql);
        }
    }

    public function update(){
        $sql = "UPDATE ".$this->table_name." SET ".
            " estado = ".$this->var2str($this->estado).
            ", tipo = ".$this->var2str($this->tipo).
            ", nombre = ".$this->var2str($this->nombre).
            ", nombre_corto = ".$this->var2str($this->nombre_corto).
            " WHERE codsistemapension = ".$this->var2str($this->codsistemapension).";";
        return $this->db->exec($sql);
    }

    public function get($codsistemapension){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE codsistemapension = ".$this->var2str($codsistemapension).";");
        if($data){
            return new sistemapension($data[0]);
        }else{
            return false;
        }
    }

    public function get_by_nombre($nombre){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE nombre = ".$this->var2str($nombre).";");
        if($data){
            return new sistemapension($data[0]);
        }else{
            return false;
        }
    }

    public function get_by_nombre_corto($nombre_corto){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE nombre_corto = ".$this->var2str($nombre_corto).";");
        if($data){
            return new sistemapension($data[0]);
        }else{
            return false;
        }
    }
    
    public function get_by_tipo($tipo){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE tipo = ".$this->var2str($tipo).";");
        if($data){
            return new sistemapension($data[0]);
        }else{
            return false;
        }
    }

    public function all(){
        $lista = array();
        $data = $this->db->select("SELECT * FROM ".$this->table_name." ORDER BY nombre;");
        if($data){
            foreach($data as $d){
                $lista[] = new sistemapension($d);
            }
            return $lista;
        }else{
            return false;
        }
    }

    public function all_activos(){
        $lista = array();
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE estado = TRUE ORDER BY nombre;");
        if($data){
            foreach($data as $d){
                $lista[] = new sistemapension($d);
            }
            return $lista;
        }else{
            return false;
        }
    }

    public function delete(){
        $sql = "DELETE FROM ".$this->table_name." WHERE codsistemapension = ".$this->var2str($this->codsistemapension);
        if($this->db->exec($sql)){
            return true;
        }else{
            return false;
        }
    }

    public function corregir(){
        $sql = "SELECT codsistemapension FROM ".$this->table_name." WHERE nombre = ".$this->var2str($this->nombre);
        $data = $this->db->select($sql);
        if($data){
            $this->update();
        }else{
            $this->save();
        }
    }

}
