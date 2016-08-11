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
require_model('agente.php');
/**
 * Description of generaciones
 *
 * @author Joe Nilson <joenilson at gmail dot com>
 */
class generaciones extends fs_model{
    /**
     * El codigo a generar de la Generacion
     * @var type $codgeneracion Generacion
     */
    public $codgeneracion;

    /**
     * Se coloca la descripción de la generacion
     * @var type $descripcion Descripcion
     */
    public $descripcion;
    
    /**
     * Se coloca el año de inicio de la Genereracion en formato YYYY
     * @var type $inicio_generacion Date::Year
     */
    public $inicio_generacion;
    
    /**
     * Se coloca el año de fin de la Generacion en formato YYYY
     * @var type $fin_generacion Date::Year
     */
    public $fin_generacion;

    /**
     * Si se va desactivar una Generacion se debe colocar aquí su estado
     * @var type $estado Boolean
     */
    public $estado;
    
    public $agentes;
    
    public function __construct($t = FALSE) {
        parent::__construct('hr_generaciones');
        if($t){
            $this->codgeneracion = $t['codgeneracion'];
            $this->descripcion = $t['descripcion'];
            $this->inicio_generacion = intval($t['inicio_generacion']);
            $this->fin_generacion = intval($t['fin_generacion']);
            $this->estado = $this->str2bool($t['estado']);
        }else{
            $this->codgeneracion = NULL;
            $this->descripcion = NULL;
            $this->inicio_generacion = NULL;
            $this->fin_generacion = NULL;
            $this->estado = FALSE;
        }
        
        $this->agentes = new agente();
    }

    protected function install() {
        return "INSERT INTO ".$this->table_name.
                " (codgeneracion, descripcion, estado) VALUES".
                " ('1','Baby Boomers',1943,1960, true),".
                " ('2','X',1961,1981,true),".
                " ('3','Y',1982,2000,true),".
                " ('4','Z',2001,2016,true);";
    }

    public function url()
    {
        return "index.php?page=admin_generaciones";
    }

    public function get_new_codigo()
    {
        $sql = "SELECT MAX(".$this->db->sql_to_int('codgeneracion').") as cod FROM ".$this->table_name.";";
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
        if(is_null($this->codgeneracion)){
            return false;
        }else{
            return $this->db->select("SELECT * FROM ".$this->table_name." WHERE codgeneracion = ".$this->var2str($this->codgeneracion).";");
        }
    }

    public function save() {
        if($this->exists()){
            $this->update();
            return true;
        }else{
            //INSERT DATA
            $sql = "INSERT INTO ".$this->table_name." (codgeneracion, descripcion, inicio_generacion, fin_generacion, estado) VALUES (".
                $this->var2str($this->get_new_codigo()).", ".
                $this->var2str($this->descripcion).", ".
                $this->intval($this->inicio_generacion).", ".
                $this->intval($this->fin_generacion).", ".
                $this->var2str($this->estado).");";
            return $this->db->exec($sql);
        }
    }

    public function update(){
        $sql = "UPDATE ".$this->table_name." SET ".
            ", estado = ".$this->var2str($this->estado).
            ", inicio_generacion = ".$this->intval($this->inicio_generacion).
            ", fin_generacion = ".$this->intval($this->fin_generacion).
            ", descripcion = ".$this->var2str($this->descripcion).
            " WHERE codgeneracion = ".$this->var2str($this->codgeneracion).";";
        $this->db->exec($sql);
    }

    public function get($codgeneracion){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE codgeneracion = ".$this->var2str($codgeneracion).";");
        if($data){
            return new generaciones($data[0]);
        }else{
            return false;
        }
    }

    public function get_by_descripcion($descripcion){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE descripcion = ".$this->var2str($descripcion).";");
        if($data){
            return new generaciones($data[0]);
        }else{
            return false;
        }
    }
    
    public function get_by_year($year){
        $sql = "SELECT * FROM ".$this->table_name." WHERE inicio_generacion <= ".$this->intval($year)." AND fin_generacion >= ".$this->intval($year).";";
        $data = $this->db->select($sql);
        if($data){
            $linea = new generaciones($data[0]);
            return $linea;
        }else{
            return false;
        }
    }

    public function all(){
        $lista = array();
        $data = $this->db->select("SELECT * FROM ".$this->table_name.";");
        if($data){
            foreach($data as $d){
                $lista[] = new generaciones($d);
            }
            return $lista;
        }else{
            return false;
        }
    }

    public function delete(){
        return false;
    }
    
    public function resumen_generaciones(){
        $agentes = $this->agentes->all();
        $lista = array();
        foreach($agentes as $a){
            $dateEmpleado = new DateTime($a->f_nacimiento);
            $datos = $this->get_by_year($dateEmpleado->format('Y'));
            if(!isset($lista[$datos->codgeneracion])){
                $lista[$datos->codgeneracion] = new stdClass();
                $lista[$datos->codgeneracion]->cantidad = 0;
            }
            $lista[$datos->codgeneracion]->descripcion = $datos->descripcion;
            $lista[$datos->codgeneracion]->cantidad += 1;
        }
        return $lista;
    }

    public function corregir(){
        $sql = "SELECT codgeneracion FROM ".$this->table_name." WHERE descripcion = ".$this->var2str($this->descripcion);
        $data = $this->db->select($sql);
        if($data){
            $this->update();
        }else{
            $this->save();
        }
    }

}
