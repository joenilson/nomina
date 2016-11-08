<?php

/*
 * Copyright (C) 2016 Joe Nilson <joenilson at gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
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
 * Description of hoja_vida
 *
 * @author Joe Nilson <joenilson at gmail.com>
 */
class hoja_vida extends fs_model{
    /*
     * @type integer Id
     */
    public $id;
    /*
     * @type varchar(6)
     */
    public $codagente;
    /*
     * @type varchar(120)
     */
    public $documento;
    /*
     * @type varchar(32)
     */
    public $tipo_documento;
    /*
     * @type varchar(120)
     */
    public $autor_documento;
    /*
     * @type date YYYY-MM-DD
     */
    public $fecha_documento;
    /*
     * @type boolean
     */
    public $estado;
    /*
     * @type varchar(12)
     */
    public $usuario_creacion;
    /*
     * @type varchar(12)
     */
    public $usuario_modificacion;
    /*
     * @type timestamp YYYY-MM-DD h:i:s
     */
    public $fecha_creacion;
    /*
     * @type timestamp YYYY-MM-DD h:i:s
     */
    public $fecha_modificacion;

    public function __construct($t = FALSE) {
        parent::__construct('hr_hoja_vida');
        if($t){
            $this->id = $t['id'];
            $this->codagente = $t['codagente'];
            $this->documento = $t['documento'];
            $this->tipo_documento = $t['tipo_documento'];
            $this->autor_documento = $t['autor_documento'];
            $this->fecha_documento = $t['fecha_documento'];
            $this->estado = $this->str2bool($t['estado']);
            $this->usuario_creacion = $t['usuario_creacion'];
            $this->usuario_modificacion = $t['usuario_modificacion'];
            $this->fecha_creacion = $t['fecha_creacion'];
            $this->fecha_modificacion = $t['fecha_modificacion'];
        }else{
            $this->id = NULL;
            $this->codagente = NULL;
            $this->documento = NULL;
            $this->tipo_documento = NULL;
            $this->autor_documento = NULL;
            $this->fecha_documento = NULL;
            $this->estado = FALSE;
            $this->usuario_creacion = NULL;
            $this->usuario_modificacion = NULL;
            $this->fecha_creacion = NULL;
            $this->fecha_modificacion = NULL;
        }
        
        $this->agentes = new agente();
    }
    
    protected function install() {
        return '';
    }
    
    public function exists() {
        if(is_null($this->id)){
            return false;
        }else{
            return $this->get($this->id);
        }
    }
    
    public function save() {
        if($this->exists()){
            return $this->update();
        }else{
            $sql = "INSERT INTO ".$this->table_name." (codagente, documento, tipo_documento, autor_documento, fecha_documento, estado, usuario_creacion, fecha_creacion ) VALUES (".
                $this->var2str($this->codagente).", ".
                $this->var2str($this->documento).", ".
                $this->var2str($this->tipo_documento).", ".
                $this->var2str($this->autor_documento).", ".
                $this->var2str($this->fecha_documento).", ".
                $this->var2str($this->estado).", ".
                $this->var2str($this->usuario_creacion).", ".
                $this->var2str($this->fecha_creacion).");";
            return $this->db->exec($sql);
        }
    }
    
    public function update() {
        $sql = "UPDATE ".$this->table_name." SET ".
            " estado = ".$this->var2str($this->estado).
            ", tipo_documento = ".$this->var2str($this->tipo_documento).
            ", fecha_documento = ".$this->var2str($this->fecha_documento).
            ", autor_documento = ".$this->var2str($this->autor_documento).
            ", documento = ".$this->var2str($this->documento).
            ", usuario_modificacion = ".$this->var2str($this->usuario_modificacion).
            ", fecha_modificacion = ".$this->var2str($this->fecha_modificacion).
            " WHERE id = ".$this->intval($this->id)." AND codagente = ".$this->var2str($this->codagente).";";
        return $this->db->exec($sql);
    }
    
    public function delete() {
        $sql = "DELETE FROM ".$this->table_name." WHERE ".
                " id = ".$this->intval($this->id)." AND codagente = ".$this->var2str($this->codagente).";";
        return $this->db->exec($sql);
    }
    
    public function get($id){
        $sql = "SELECT * FROM ".$this->table_name." WHERE ".
            " id = ".$this->intval($id).";";
        $data = $this->db->select($sql);
        if($data){
            $d = new hoja_vida($data[0]);
            return $d;
        }else{
            return false;
        }
    }
    
    public function all(){
        $sql = "SELECT * FROM ".$this->table_name." ORDER BY codagente,fecha_documento,tipo_documento;";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach($data as $d){
                $lista[] = new hoja_vida($d);
            }
            return $lista;
        }else{
            return false;
        }
    }
    
    public function activos(){
        $sql = "SELECT * FROM ".$this->table_name." WHERE estado = TRUE ORDER BY codagente,fecha_documento,tipo_documento;";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach($data as $d){
                $lista[] = new hoja_vida($d);
            }
            return $lista;
        }else{
            return false;
        }
    }
    
    public function tipo_documento($tipo_documento){
        $sql = "SELECT * FROM ".$this->table_name." WHERE tipo_documento = ".$this->var2str($tipo_documento)." AND estado = TRUE ORDER BY codagente,fecha_documento,tipo_documento;";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach($data as $d){
                $lista[] = new hoja_vida($d);
            }
            return $lista;
        }else{
            return false;
        }
    }
    
    public function all_agente($codagente){
        $sql = "SELECT * FROM ".$this->table_name." WHERE codagente = ".$this->var2str($codagente)." ORDER BY codagente,fecha_documento,tipo_documento;";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach($data as $d){
                $lista[] = new hoja_vida($d);
            }
            return $lista;
        }else{
            return false;
        }
    }
    
    public function activos_agente($codagente){
        $sql = "SELECT * FROM ".$this->table_name." WHERE codagente = ".$this->var2str($codagente)." AND estado = TRUE ORDER BY codagente,fecha_documento,tipo_documento;";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach($data as $d){
                $lista[] = new hoja_vida($d);
            }
            return $lista;
        }else{
            return false;
        }
    }
    
    public function tipo_documento_agente($tipo_documento,$codagente){
        $sql = "SELECT * FROM ".$this->table_name." WHERE tipo_documento = ".$this->var2str($tipo_documento)." AND codagente = ".$this->var2str($codagente)." AND estado = TRUE ORDER BY codagente,fecha_documento,tipo_documento;";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach($data as $d){
                $lista[] = new hoja_vida($d);
            }
            return $lista;
        }else{
            return false;
        }
    }
    
    
}
