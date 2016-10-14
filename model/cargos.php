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
 * Description of cargos
 *
 * @author Joe Nilson <joenilson at gmail dot com>
 */
class cargos extends fs_model{
    /**
     * El codigo a generar del cargo
     * @var type $codcargo Cargos
     */
    public $codcargo;
    /**
     * Si este cargo tiene un superior se coloca aquí
     * @var type $padre Cargo
     */
    public $padre;

    /**
     * Se coloca la descripción del cargo
     * @var type $descripcion Cargo
     */
    public $descripcion;

    /**
     * Un cargo debe pertenecer a una categoria
     * @var type $codcategoria Categoria
     */
    public $codcategoria;

    /**
     * Si se va desactivar un cargo se debe colocar aquí su estado
     * @var type $estado Boolean
     */
    public $estado;
    public function __construct($t = FALSE) {
        parent::__construct('hr_cargos');
        if($t){
            $this->codcargo = $t['codcargo'];
            $this->descripcion = $t['descripcion'];
            $this->padre = $t['padre'];
            $this->codcategoria = $t['codcategoria'];
            $this->estado = $this->str2bool($t['estado']);
        }else{
            $this->codcargo = NULL;
            $this->descripcion = NULL;
            $this->padre = NULL;
            $this->codcategoria = NULL;
            $this->estado = FALSE;
        }
    }

    protected function install() {
        return "INSERT INTO ".$this->table_name." (codcargo, descripcion, padre, codcategoria, estado) VALUES ('1','GERENTE GENERAL',NULL,'1',TRUE)";
    }

    public function url()
    {
        if( is_null($this->codcargo) )
        {
            return "index.php?page=admin_cargos";
        }
        else
        {
            return "index.php?page=admin_cargos&cod=".$this->codcargo;
        }
    }

    public function get_new_codigo()
    {
        $sql = "SELECT MAX(".$this->db->sql_to_int('codcargo').") as cod FROM ".$this->table_name.";";
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
        if(is_null($this->codcargo)){
            return false;
        }else{
            return $this->db->select("SELECT * FROM ".$this->table_name." WHERE codcargo = ".$this->var2str($this->codcargo).";");
        }
    }

    public function save() {
        if($this->exists()){
            return $this->update();
        }else{
            //INSERT DATA
            $sql = "INSERT INTO ".$this->table_name." (codcargo, descripcion, padre, codcategoria, estado) VALUES (".
                $this->var2str($this->get_new_codigo()).", ".
                $this->var2str($this->descripcion).", ".
                $this->var2str($this->padre).", ".
                $this->var2str($this->codcategoria).", ".
                $this->var2str($this->estado).");";
            return $this->db->exec($sql);
        }
    }

    public function update(){
        $sql = "UPDATE ".$this->table_name." SET ".
            " codcategoria = ".$this->var2str($this->codcategoria).
            ", padre = ".$this->var2str($this->padre).
            ", estado = ".$this->var2str($this->estado).
            ", descripcion = ".$this->var2str($this->descripcion).
            " WHERE codcargo = ".$this->var2str($this->codcargo).";";
        return $this->db->exec($sql);
    }

    public function get($codcargo){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE codcargo = ".$this->var2str($codcargo).";");
        if($data){
            return new cargos($data[0]);
        }else{
            return false;
        }
    }

    public function get_by_descripcion($descripcion){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE descripcion = ".$this->var2str($descripcion).";");
        if($data){
            return new cargos($data[0]);
        }else{
            return false;
        }
    }

    public function get_by_categoria($codcategoria){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE codcategoria = ".$this->var2str($codcategoria).";");
        if($data){
            return new cargos($data[0]);
        }else{
            return false;
        }
    }

    public function all(){
        $lista = array();
        $data = $this->db->select("SELECT * FROM ".$this->table_name." ORDER BY descripcion;");
        if($data){
            foreach($data as $d){
                $lista[] = new cargos($d);
            }
            return $lista;
        }else{
            return false;
        }
    }

    public function delete(){
        $sql = "DELETE FROM ".$this->table_name." WHERE codcargo = ".$this->var2str($this->codcargo).";";
        $data = $this->db->exec($sql);
        if($data){
            return true;
        }else{
            return false;
        }
    }

    public function corregir(){
        $sql = "SELECT codcargo FROM ".$this->table_name." WHERE descripcion = ".$this->var2str($this->descripcion);
        $data = $this->db->select($sql);
        if($data){
            $this->update();
        }else{
            $this->save();
        }
    }
    
    public function en_uso(){
        $sql = "SELECT count(codagente) as cantidad from agentes where codcargo = ".$this->var2str($this->codcargo).";";
        $data = $this->db->select($sql);
        if($data){
            return $data[0]['cantidad'];
        }else{
            return false;
        }
    }

}
