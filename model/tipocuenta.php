<?php

/*
 * Copyright (C) 2017 Joe Nilson <joenilson at gmail dot com>
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
 * Description of tipocuenta
 *
 * @author Joe Nilson <joenilson at gmail dot com>
 */
class tipocuenta extends fs_model{
    /**
     * El codigo a generar del tipocuenta
     * @var varchar(4) $codtipo TipoCuenta
     */
    public $codtipo;

    /**
     * Se coloca la descripción del tipocuenta
     * @var varchar(100) $descripcion TipoCuenta
     */
    public $descripcion;
    
    /**
     * Se coloca el codigo para el banco pagador
     * @var varchar(20) $descripcion TipoCuenta
     */
    public $codigo_banco;

    /**
     * Si se va desactivar un tipo de cuenta se debe colocar aquí su estado
     * @var boolean $estado Boolean
     */
    public $estado;
    public function __construct($t = FALSE) {
        parent::__construct('hr_tipocuenta');
        if($t){
            $this->codtipo = $t['codtipo'];
            $this->descripcion = $t['descripcion'];
            $this->codigo_banco = $t['codigo_banco'];
            $this->estado = $this->str2bool($t['estado']);
        }else{
            $this->codtipo = NULL;
            $this->descripcion = NULL;
            $this->codigo_banco = NULL;
            $this->estado = FALSE;
        }
    }

    protected function install() {
        return "INSERT INTO ".$this->table_name.
                " (codtipo, descripcion, estado) VALUES".
                " ('0001','CUENTA DE AHORRROS',TRUE),".
                " ('0002','CUENTA CORRIENTE',TRUE),".
                " ('0003','CUENTA NOMINA',TRUE),".
                " ('9999','NO APLICA',TRUE);";
    }

    public function url()
    {
        return "index.php?page=configuracion_nomina&type=tipocuenta";
    }

    public function get_new_codigo()
    {
        $sql = "SELECT MAX(".$this->db->sql_to_int('codtipo').") as cod FROM ".$this->table_name.";";
        $cod = $this->db->select($sql);
        if($cod)
        {
            return $this->sanitize_codigo($cod[0]['cod']);
        }
        else
        {
            return $this->sanitize_codigo(1);
        }
    }

    public function exists() {
        if(is_null($this->codtipo)){
            return false;
        }else{
            return $this->db->select("SELECT * FROM ".$this->table_name." WHERE codtipo = ".$this->var2str($this->codtipo).";");
        }
    }

    private function sanitize_codigo($codigo){
        return str_pad($codigo, 4, '0', STR_PAD_LEFT);
    }

    public function save() {
        if($this->exists()){
            return $this->update();
        }else{
            //INSERT DATA
            $this->codtipo = ($this->codtipo)?$this->sanitize_codigo($this->codtipo):$this->get_new_codigo();
            $sql = "INSERT INTO ".$this->table_name." (codtipo, descripcion, codigo_banco, estado) VALUES (".
                $this->var2str($this->codtipo).", ".
                $this->var2str($this->descripcion).", ".
                $this->var2str($this->codigo_banco).", ".
                $this->var2str($this->estado).");";
            return $this->db->exec($sql);
        }
    }

    public function update(){
        $sql = "UPDATE ".$this->table_name." SET ".
            " estado = ".$this->var2str($this->estado).
            ", descripcion = ".$this->var2str($this->descripcion).
            ", codigo_banco = ".$this->var2str($this->codigo_banco).
            " WHERE codtipo = ".$this->var2str($this->codtipo).";";
        return $this->db->exec($sql);
    }

    public function get($codtipo){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE codtipo = ".$this->var2str($codtipo).";");
        if($data){
            return new tipocuenta($data[0]);
        }else{
            return false;
        }
    }

    public function get_by_descripcion($descripcion){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE descripcion = ".$this->var2str($descripcion).";");
        if($data){
            return new tipocuenta($data[0]);
        }else{
            return false;
        }
    }

    public function all(){
        $lista = array();
        $data = $this->db->select("SELECT * FROM ".$this->table_name." ORDER BY descripcion;");
        if($data){
            foreach($data as $d){
                $lista[] = new tipocuenta($d);
            }
            return $lista;
        }else{
            return false;
        }
    }

    public function delete(){
        $sql = "DELETE FROM ".$this->table_name." WHERE codtipo = ".$this->var2str($this->codtipo).";";
        $data = $this->db->exec($sql);
        if($data){
            return true;
        }else{
            return false;
        }
    }

    public function corregir(){
        $sql = "SELECT codtipo FROM ".$this->table_name." WHERE descripcion = ".$this->var2str($this->descripcion);
        $data = $this->db->select($sql);
        if($data){
            $this->update();
        }else{
            $this->save();
        }
    }

}
