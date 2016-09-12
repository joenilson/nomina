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
 * Description of motivocese
 *
 * @author Joe Nilson <joenilson at gmail dot com>
 */
class motivocese extends fs_model{
    /**
     * El codigo a generar de Motivo Cese
     * @var type $codmotivocese MotivoCese
     */
    public $codmotivocese;
    
    /**
     * Aqui ponemos el tipo de Motivo de Cese, el cual esta en la tabla
     * hr_tipocese
     * @var type $codtipocese TipoCese
     */
    public $codtipocese;    

    /**
     * Se coloca la descripcion del Motivo Cese
     * @var type $descripcion Motivo Cese
     */
    public $descripcion;

    /**
     * Si se va desactivar un registro se debe colocar aquí su estado
     * @var type $estado Boolean
     */
    public $estado;
    public function __construct($t = FALSE) {
        parent::__construct('hr_motivocese');
        if($t){
            $this->codmotivocese = $t['codmotivocese'];
            $this->descripcion = $t['descripcion'];
            $this->codtipocese = $t['codtipocese'];
            $this->estado = $this->str2bool($t['estado']);
        }else{
            $this->codmotivocese = NULL;
            $this->descripcion = NULL;
            $this->codtipocese = NULL;
            $this->estado = FALSE;
        }
    }

    protected function install() {
        return "INSERT INTO ".$this->table_name.
            " (codmotivocese, descripcion, codtipocese, estado) VALUES".
            " ('1','FALTA DE DESARROLLO DE CARRERA','1',true),".
            " ('2','NO CONFORMIDAD CON RMEUNERACION','1',true),".
            " ('3','PROBLEMAS CON EL JEFE','1',true),".
            " ('4','INTERES EN OTRAS AREAS','1',true),".
            " ('5','ASUNTOS DE INDOLE FAMILIAR','1',true),".
            " ('6','ABANDONO DE TRABAJO','2',true),".
            " ('7','ACTOS DESHONESTOS','2',true),".
            " ('8','TERMINO DE CONTRATO','3',true);";
    }

    public function url()
    {
        return "index.php?page=admin_motivocese";
    }

    public function get_new_codigo()
    {
        $sql = "SELECT MAX(".$this->db->sql_to_int('codmotivocese').") as cod FROM ".$this->table_name.";";
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
        if(is_null($this->codmotivocese)){
            return false;
        }else{
            return $this->db->select("SELECT * FROM ".$this->table_name." WHERE codmotivocese = ".$this->var2str($this->codmotivocese).";");
        }
    }

    public function save() {
        if($this->exists()){
            return $this->update();
        }else{
            //INSERT DATA
            $sql = "INSERT INTO ".$this->table_name." (codmotivocese, codtipocese, descripcion, estado) VALUES (".
                $this->var2str($this->get_new_codigo()).", ".
                $this->var2str($this->codtipocese).", ".
                $this->var2str($this->descripcion).", ".
                $this->var2str($this->estado).");";
            return $this->db->exec($sql);
        }
    }

    public function update(){
        $sql = "UPDATE ".$this->table_name." SET ".
            " estado = ".$this->var2str($this->estado).
            ", codtipocese = ".$this->var2str($this->codtipocese).
            ", descripcion = ".$this->var2str($this->descripcion).
            " WHERE codmotivocese = ".$this->var2str($this->codmotivocese).";";
        return $this->db->exec($sql);
    }

    public function get($codmotivocese){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE codmotivocese = ".$this->var2str($codmotivocese).";");
        if($data){
            return new motivocese($data[0]);
        }else{
            return false;
        }
    }

    public function get_by_descripcion($descripcion){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE descripcion = ".$this->var2str($descripcion).";");
        if($data){
            return new motivocese($data[0]);
        }else{
            return false;
        }
    }
   
    public function get_by_tipo($tipo){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE codtipocese = ".$this->var2str($tipo).";");
        if($data){
            return new motivocese($data[0]);
        }else{
            return false;
        }
    }

    public function all(){
        $lista = array();
        $data = $this->db->select("SELECT * FROM ".$this->table_name." ORDER BY descripcion;");
        if($data){
            foreach($data as $d){
                $lista[] = new motivocese($d);
            }
            return $lista;
        }else{
            return false;
        }
    }

    public function all_activos(){
        $lista = array();
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE estado = TRUE ORDER BY descripcion;");
        if($data){
            foreach($data as $d){
                $lista[] = new motivocese($d);
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
        $sql = "SELECT codmotivocese FROM ".$this->table_name." WHERE descripcion = ".$this->var2str($this->descripcion);
        $data = $this->db->select($sql);
        if($data){
            $this->update();
        }else{
            $this->save();
        }
    }

}
