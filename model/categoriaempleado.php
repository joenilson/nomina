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
 * Description of categoriaempleado
 *
 * @author Joe Nilson <joenilson at gmail dot com>
 */
class categoriaempleado extends fs_model{
    /**
     * El codigo a generar de la categoriaempleado
     * @var type $codcategoria CategoriaEmpleado
     */
    public $codcategoria;

    /**
     * Se coloca la descripción de la categoriaempleado
     * @var type $descripcion CategoriaEmpleado
     */
    public $descripcion;

    /**
     * Para poder sacar un listado con jerarquias se necesita un orden
     * @var type $orden Integer
     */
    public $orden;

    /**
     * Si se va desactivar una categoria de empleado se debe colocar aquí su estado
     * @var type $estado Boolean
     */
    public $estado;
    public function __construct($t = FALSE) {
        parent::__construct('hr_categoriaempleado');
        if($t){
            $this->codcategoria = $t['codcategoria'];
            $this->descripcion = $t['descripcion'];
            $this->orden = $t['orden'];
            $this->estado = $this->str2bool($t['estado']);
        }else{
            $this->codcategoria = NULL;
            $this->descripcion = NULL;
            $this->orden = NULL;
            $this->estado = FALSE;
        }
    }

    protected function install() {
        return "INSERT INTO ".$this->table_name.
                " (codcategoria, descripcion, orden, estado) VALUES".
                " ('1','GERENTE GENERAL',1,TRUE),".
                " ('2','GERENTE',2,TRUE),".
                " ('3','JEFE',3,TRUE),".
                " ('4','COORDINADOR',4,TRUE),".
                " ('5','SUPERVISOR / ENCARGADO',5,TRUE),".
                " ('6','ANALISTA',6,TRUE),".
                " ('7','ASISTENTE',7,TRUE),".
                " ('8','AUXILIAR / OPERARIO',8,TRUE),".
                " ('9','PRACTICANTE',9,TRUE);";
    }

    public function url()
    {
        return "index.php?page=admin_categoriaempleado";
    }

    public function get_new_codigo()
    {
        $sql = "SELECT MAX(".$this->db->sql_to_int('codcategoria').") as cod FROM ".$this->table_name.";";
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
        if(is_null($this->codcategoria)){
            return false;
        }else{
            return $this->db->select("SELECT * FROM ".$this->table_name." WHERE codcategoria = ".$this->var2str($this->codcategoria).";");
        }
    }

    public function save() {
        if($this->exists()){
            $this->update();
            return true;
        }else{
            //INSERT DATA
            $sql = "INSERT INTO ".$this->table_name." (codcategoria, descripcion, orden, estado) VALUES (".
                $this->var2str($this->get_new_codigo()).", ".
                $this->var2str($this->descripcion).", ".
                $this->intval($this->orden).", ".
                $this->var2str($this->estado).");";
            return $this->db->exec($sql);
        }
    }

    public function update(){
        $sql = "UPDATE ".$this->table_name." SET ".
            " estado = ".$this->var2str($this->estado).
            ", orden = ".$this->intval($this->orden).
            ", descripcion = ".$this->var2str($this->descripcion).
            " WHERE codcategoria = ".$this->var2str($this->codcategoria).";";
        $this->db->exec($sql);
    }

    public function get($codcategoria){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE codcategoria = ".$this->var2str($codcategoria).";");
        if($data){
            return new categoriaempleado($data[0]);
        }else{
            return false;
        }
    }

    public function get_by_descripcion($descripcion){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE descripcion = ".$this->var2str($descripcion).";");
        if($data){
            return new categoriaempleado($data[0]);
        }else{
            return false;
        }
    }

    public function all(){
        $lista = array();
        $data = $this->db->select("SELECT * FROM ".$this->table_name." ORDER BY orden;");
        if($data){
            foreach($data as $d){
                $lista[] = new categoriaempleado($d);
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
        $sql = "SELECT codcategoria FROM ".$this->table_name." WHERE descripcion = ".$this->var2str($this->descripcion);
        $data = $this->db->select($sql);
        if($data){
            $this->update();
        }else{
            $this->save();
        }
    }

    public function get_maxorden(){
        $sql = "SELECT max(orden) as orden FROM ".$this->table_name.";";
        $data = $this->db->select($sql);
        if($data){
            return $data[0]['orden']+1;
        }else{
            return 1;
        }
    }

    public function reordenar($reorden){
        if($this->orden != $reorden){
            $posicion_inicial = ($this->orden > $reorden)?$reorden:$this->orden;
            $sql = "SELECT * from ".$this->table_name." where orden >= ".$this->intval($posicion_inicial)." ORDER BY orden;";
            $data = $this->db->select($sql);
            if($data){
                $posicion_actual = $posicion_inicial;
                foreach($data as $d){
                    $value = new categoriaempleado($d);
                    $value->orden = $this->comparar($value, $posicion_actual, $reorden);
                    $value->update();
                    $posicion_actual++;
                }
            }
        }
    }

    private function comparar($value, $posicion, $reorden){
        if($value->codcategoria == $this->codcategoria){
            $valor = $reorden;
        }else{
            if($value->orden == $reorden){
                $valor = $this->orden;
            }else{
                $valor = $posicion;
            }
        }
        return $valor;
    }

}
