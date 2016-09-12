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
 * Description of tipoausencias
 *
 * @author Joe Nilson <joenilson at gmail dot com>
 */
class tipoausencias extends fs_model{
    /**
     * El codigo a generar del tipoausencias
     * @var type $codausencia TipoAusencia
     */
    public $codausencia;

    /**
     * Se coloca la descripción del tipoausencias
     * @var type $descripcion TipoAusencia
     */
    public $descripcion;
    
    /**
     * Se coloca si aplica descontar por la ausencia
     * @var type $aplicar_descuento Boolean
     */
    public $aplicar_descuento;

    /**
     * Si se va desactivar un tipo de ausencia se debe colocar aquí su estado
     * @var type $estado Boolean
     */
    public $estado;
    
    public function __construct($t = FALSE) {
        parent::__construct('hr_tipoausencias');
        if($t){
            $this->codausencia = $t['codausencia'];
            $this->descripcion = $t['descripcion'];
            $this->aplicar_descuento = $this->str2bool($t['aplicar_descuento']);
            $this->estado = $this->str2bool($t['estado']);
        }else{
            $this->codausencia = NULL;
            $this->descripcion = NULL;
            $this->aplicar_descuento = FALSE;
            $this->estado = FALSE;
        }
    }

    protected function install() {
        return "INSERT INTO ".$this->table_name.
                " (codausencia, descripcion, aplicar_descuento, estado) VALUES".
                " ('1','COMISION',FALSE,TRUE),".
                " ('2','LICENCIA SINDICAL',FALSE,TRUE),".
                " ('3','SUSPENSIONES',TRUE,TRUE),".
                " ('4','VACACIONES',FALSE,TRUE);";
    }

    public function url()
    {
        return "index.php?page=admin_tipoausenciass";
    }

    public function get_new_codigo()
    {
        $sql = "SELECT MAX(".$this->db->sql_to_int('codausencia').") as cod FROM ".$this->table_name.";";
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
        if(is_null($this->codausencia)){
            return false;
        }else{
            return $this->db->select("SELECT * FROM ".$this->table_name." WHERE codausencia = ".$this->var2str($this->codausencia).";");
        }
    }

    public function save() {
        if($this->exists()){
            return $this->update();
        }else{
            //INSERT DATA
            $sql = "INSERT INTO ".$this->table_name." (codausencia, descripcion, aplicar_descuento, estado) VALUES (".
                $this->var2str($this->get_new_codigo()).", ".
                $this->var2str($this->descripcion).", ".
                $this->var2str($this->aplicar_descuento).", ".
                $this->var2str($this->estado).");";
            return $this->db->exec($sql);
        }
    }

    public function update(){
        $sql = "UPDATE ".$this->table_name." SET ".
            " estado = ".$this->var2str($this->estado).
            ", aplicar_descuento = ".$this->var2str($this->aplicar_descuento).
            ", descripcion = ".$this->var2str($this->descripcion).
            " WHERE codausencia = ".$this->var2str($this->codausencia).";";
        return $this->db->exec($sql);
    }

    public function get($codausencia){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE codausencia = ".$this->var2str($codausencia).";");
        if($data){
            return new tipoausencias($data[0]);
        }else{
            return false;
        }
    }

    public function get_by_descripcion($descripcion){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE descripcion = ".$this->var2str($descripcion).";");
        if($data){
            return new tipoausencias($data[0]);
        }else{
            return false;
        }
    }
    
    public function get_by_aplicar_descuento($aplica){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE aplicar_descuento = ".$this->var2str($aplica).";");
        if($data){
            return new tipoausencias($data[0]);
        }else{
            return false;
        }
    }

    public function all(){
        $lista = array();
        $data = $this->db->select("SELECT * FROM ".$this->table_name." ORDER BY descripcion;");
        if($data){
            foreach($data as $d){
                $lista[] = new tipoausencias($d);
            }
            return $lista;
        }else{
            return false;
        }
    }

    public function delete(){
        $sql = "DELETE FROM ".$this->table_name." WHERE codausencia = ".$this->codausencia.";";
        $data = $this->db->exec($sql);
        if($data){
            return true;
        }else{
            return false;
        }
    }

    public function corregir(){
        $sql = "SELECT codausencia FROM ".$this->table_name." WHERE descripcion = ".$this->var2str($this->descripcion);
        $data = $this->db->select($sql);
        if($data){
            $this->update();
        }else{
            $this->save();
        }
    }

}
