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
 * Description of bancos
 *
 * @author Joe Nilson <joenilson at gmail dot com>
 */
class bancos extends fs_model {
    /**
     * El codigo a generar del Banco
     * @var type $codbanco Banco
     */
    public $codbanco;

    /**
     * Se coloca el nombre del Banco
     * @var type $nombre Banco
     */
    public $nombre;

    /**
     * Este es un codigo auxiliar que se puede usar para generar un codigo de nómina
     * @var varchar(60)
     */
    public $codigo_alterno;
    /**
     * Aqui ponemos el tipo de Banco, puede ser
     * 1 BANCO
     * 2 COOPERATIVA de Ahorro
     * 3 ASOCIACION de Ahorro y Prestamo
     * 4 CAJA si el pago va ser interno
     * @var type $tipo Banco
     */
    public $tipo;

    /**
     * Si se va desactivar un banco se debe colocar aquí su estado
     * @var type $estado Boolean
     */
    public $estado;
    
    public function __construct($t = FALSE) {
        parent::__construct('bancos');
        if($t){
            $this->codbanco = $t['codbanco'];
            $this->nombre = $t['nombre'];
            $this->codigo_alterno = $t['codigo_alterno'];
            $this->tipo = $t['tipo'];
            $this->estado = $this->str2bool($t['estado']);
        }else{
            $this->codbanco = NULL;
            $this->nombre = NULL;
            $this->codigo_alterno = NULL;
            $this->tipo = NULL;
            $this->estado = FALSE;
        }
    }

    protected function install() {
        return "INSERT INTO ".$this->table_name.
                " (codbanco, nombre, tipo, estado) VALUES".
                " ('1','BANCO','BANCO',TRUE),".
                " ('2','COOPERATIVA','COOPERATIVA',TRUE),".
                " ('3','ASOCIACION','ASOCIACION',TRUE),".
                " ('4','CAJA','CAJA',TRUE);";
    }

    public function url()
    {
        return "index.php?page=admin_bancos";
    }

    public function get_new_codigo()
    {
        $sql = "SELECT MAX(".$this->db->sql_to_int('codbanco').") as cod FROM ".$this->table_name.";";
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
        if(is_null($this->codbanco)){
            return false;
        }else{
            return $this->db->select("SELECT * FROM ".$this->table_name." WHERE codbanco = ".$this->var2str($this->codbanco).";");
        }
    }

    public function save() {
        if($this->exists()){
            return $this->update();
        }else{
            //INSERT DATA
            $sql = "INSERT INTO ".$this->table_name." (codbanco, nombre, codigo_alterno, tipo, estado) VALUES (".
                $this->var2str($this->get_new_codigo()).", ".
                $this->var2str($this->nombre).", ".
                $this->var2str($this->codigo_alterno).", ".
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
            ", codigo_alterno = ".$this->var2str($this->codigo_alterno).
            " WHERE codbanco = ".$this->var2str($this->codbanco).";";
        return $this->db->exec($sql);
    }

    public function get($codbanco){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE codbanco = ".$this->var2str($codbanco).";");
        if($data){
            return new bancos($data[0]);
        }else{
            return false;
        }
    }

    public function get_by_nombre($nombre){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE nombre = ".$this->var2str($nombre).";");
        if($data){
            return new bancos($data[0]);
        }else{
            return false;
        }
    }

    public function get_by_tipo($tipo){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE tipo = ".$this->var2str($tipo).";");
        if($data){
            return new bancos($data[0]);
        }else{
            return false;
        }
    }

    public function all(){
        $lista = array();
        $data = $this->db->select("SELECT * FROM ".$this->table_name." ORDER BY nombre;");
        if($data){
            foreach($data as $d){
                $lista[] = new bancos($d);
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
                $lista[] = new bancos($d);
            }
            return $lista;
        }else{
            return false;
        }
    }

    public function delete(){
        $sql = "DELETE FROM ".$this->table_name." WHERE codbanco = ".$this->var2str($this->codbanco).";";
        return $this->db->exec($sql);
    }

}
