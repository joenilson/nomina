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
class tipopago extends fs_model{
    /**
     * El codigo a generar del tipopago
     * @var type $codpago TipoPago
     */
    public $codpago;

    /**
     * Se coloca la descripción del tipomovimiento
     * @var type $descripcion TipoMovimiento
     */
    public $descripcion;

    /**
     * Si el tipo de pago es uno por defecto se configura como TRUE
     * @var type $es_basico Boolean
     */
    public $es_basico;
    
    /**
     * Si se va desactivar un tipo de movimiento se debe colocar aquí su estado
     * @var type $estado Boolean
     */
    public $estado;
    
    public function __construct($t = FALSE) {
        parent::__construct('hr_tipopago');
        if($t){
            $this->codpago = $t['codpago'];
            $this->descripcion = $t['descripcion'];
            $this->es_basico = $this->str2bool($t['es_basico']);
            $this->estado = $this->str2bool($t['estado']);
        }else{
            $this->codpago = NULL;
            $this->descripcion = NULL;
            $this->es_basico = FALSE;
            $this->estado = FALSE;
        }
    }

    protected function install() {
        return "INSERT INTO ".$this->table_name.
                " (codpago, descripcion, es_basico, estado) VALUES".
                " ('1','SUELDO',TRUE,TRUE),".
                " ('2','COMISION',FALSE,TRUE),".
                " ('3','META',FALSE,TRUE),".
                " ('4','BONO',FALSE,TRUE),".
                " ('5','PREMIO',FALSE,TRUE),".
                " ('6','POR NACIMIENTO',FALSE,TRUE),".
                " ('7','POR CASAMIENTO',FALSE,TRUE),".
                " ('8','POR DIVORCIO',FALSE,TRUE);";
    }

    public function url()
    {
        return "index.php?page=admin_tipopagos";
    }

    public function get_new_codigo()
    {
        $sql = "SELECT MAX(".$this->db->sql_to_int('codpago').") as cod FROM ".$this->table_name.";";
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
        if(is_null($this->codpago)){
            return false;
        }else{
            return $this->db->select("SELECT * FROM ".$this->table_name." WHERE codpago = ".$this->var2str($this->codpago).";");
        }
    }

    public function save() {
        if($this->exists()){
            $this->update();
            return true;
        }else{
            //INSERT DATA
            $sql = "INSERT INTO ".$this->table_name." (codpago, descripcion, es_basico, estado) VALUES (".
                $this->var2str($this->get_new_codigo()).", ".
                $this->var2str($this->descripcion).", ".
                $this->var2str($this->es_basico).", ".
                $this->var2str($this->estado).");";
            return $this->db->exec($sql);
        }
    }

    public function update(){
        $sql = "UPDATE ".$this->table_name." SET ".
            " estado = ".$this->var2str($this->estado).
            ", es_basico = ".$this->var2str($this->es_basico).
            ", descripcion = ".$this->var2str($this->descripcion).
            " WHERE codpago = ".$this->var2str($this->codpago).";";
        $this->db->exec($sql);
    }

    public function get($codpago){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE codpago = ".$this->var2str($codpago).";");
        if($data){
            return new tipopago($data[0]);
        }else{
            return false;
        }
    }

    public function get_by_descripcion($descripcion){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE descripcion = ".$this->var2str($descripcion).";");
        if($data){
            return new tipopago($data[0]);
        }else{
            return false;
        }
    }

    public function all(){
        $lista = array();
        $data = $this->db->select("SELECT * FROM ".$this->table_name." ORDER BY codpago;");
        if($data){
            foreach($data as $d){
                $lista[] = new tipopago($d);
            }
            return $lista;
        }else{
            return false;
        }
    }

    public function delete(){
        $sql = "DELETE FROM ".$this->table_name." WHERE codpago = ".$this->codpago.";";
        $data = $this->db->exec($sql);
        if($data){
            return true;
        }else{
            return false;
        }
    }

    public function corregir(){
        $sql = "SELECT codpago FROM ".$this->table_name." WHERE descripcion = ".$this->var2str($this->descripcion);
        $data = $this->db->select($sql);
        if($data){
            $this->update();
        }else{
            $this->save();
        }
    }

}
