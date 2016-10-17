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
require_model('tipoempleado.php');
/**
 * Description of contratos
 *
 * @author Joe Nilson <joenilson at gmail.com>
 */
class contratos extends fs_model{
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
    public $contrato;
    /*
     * @type varchar(6)
     */
    public $tipo_contrato;
    /*
     * @type date YYYY-MM-DD
     */
    public $fecha_inicio;
    /*
     * @type date YYYY-MM-DD
     */
    public $fecha_fin;
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

    public $tipoempleado;
    
    public function __construct($t = FALSE) {
        parent::__construct('hr_contratos');
        if($t){
            $this->id = $t['id'];
            $this->codagente = $t['codagente'];
            $this->contrato = $t['contrato'];
            $this->tipo_contrato = $t['tipo_contrato'];
            $this->fecha_inicio = $t['fecha_inicio'];
            $this->fecha_fin = $t['fecha_fin'];
            $this->estado = $this->str2bool($t['estado']);
            $this->usuario_creacion = $t['usuario_creacion'];
            $this->usuario_modificacion = $t['usuario_modificacion'];
            $this->fecha_creacion = $t['fecha_creacion'];
            $this->fecha_modificacion = $t['fecha_modificacion'];
        }else{
            $this->id = NULL;
            $this->codagente = NULL;
            $this->contrato = NULL;
            $this->tipo_contrato = NULL;
            $this->fecha_inicio = NULL;
            $this->fecha_fin = NULL;
            $this->estado = FALSE;
            $this->usuario_creacion = NULL;
            $this->usuario_modificacion = NULL;
            $this->fecha_creacion = NULL;
            $this->fecha_modificacion = NULL;
        }
        
        $this->tipoempleado = new tipoempleado();
    }
    
    protected function install() {
        return '';
    }
    
    protected function info_adicional($val){
        $val->desc_tipocontrato = $this->tipoempleado->get($val->tipo_contrato)->descripcion;
        return $val;
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
            $sql = "INSERT INTO ".$this->table_name." (codagente, contrato, tipo_contrato, fecha_inicio, fecha_fin, estado, usuario_creacion, fecha_creacion ) VALUES (".
                $this->var2str($this->codagente).", ".
                $this->var2str($this->contrato).", ".
                $this->var2str($this->tipo_contrato).", ".
                $this->var2str($this->fecha_inicio).", ".
                $this->var2str($this->fecha_fin).", ".
                $this->var2str($this->estado).", ".
                $this->var2str($this->usuario_creacion).", ".
                $this->var2str($this->fecha_creacion).");";
            return $this->db->exec($sql);
        }
    }
    
    public function update() {
        $sql = "UPDATE ".$this->table_name." SET ".
            " estado = ".$this->var2str($this->estado).
            ", tipo_contrato = ".$this->var2str($this->tipo_contrato).
            ", fecha_inicio = ".$this->var2str($this->fecha_inicio).
            ", fecha_fin = ".$this->var2str($this->fecha_fin).
            ", contrato = ".$this->var2str($this->contrato).
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
            $linea = new contratos($data[0]);
            $d = $this->info_adicional($linea);
            return $d;
        }else{
            return false;
        }
    }
    
    public function all(){
        $sql = "SELECT * FROM ".$this->table_name." ORDER BY codagente,fecha_inicio,tipo_contrato;";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach($data as $d){
                $linea = new contratos($d);
                $lista[] = $this->info_adicional($linea);
            }
            return $lista;
        }else{
            return false;
        }
    }
    
    public function activos(){
        $sql = "SELECT * FROM ".$this->table_name." WHERE estado = TRUE ORDER BY codagente,fecha_inicio,tipo_contrato;";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach($data as $d){
                $linea = new contratos($d);
                $lista[] = $this->info_adicional($linea);
            }
            return $lista;
        }else{
            return false;
        }
    }
    
    public function tipo_contrato($tipo_contrato){
        $sql = "SELECT * FROM ".$this->table_name." WHERE tipo_contrato = ".$this->var2str($tipo_contrato)." AND estado = TRUE ORDER BY codagente,fecha_inicio,tipo_contrato;";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach($data as $d){
                $linea = new contratos($d);
                $lista[] = $this->info_adicional($linea);
            }
            return $lista;
        }else{
            return false;
        }
    }
    
    public function all_agente($codagente){
        $sql = "SELECT * FROM ".$this->table_name." WHERE codagente = ".$this->var2str($codagente)." ORDER BY codagente,fecha_inicio,tipo_contrato;";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach($data as $d){
                $linea = new contratos($d);
                $lista[] = $this->info_adicional($linea);
            }
            return $lista;
        }else{
            return false;
        }
    }
    
    public function activos_agente($codagente){
        $sql = "SELECT * FROM ".$this->table_name." WHERE codagente = ".$this->var2str($codagente)." AND estado = TRUE ORDER BY codagente,fecha_inicio,tipo_contrato;";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach($data as $d){
                $linea = new contratos($d);
                $lista[] = $this->info_adicional($linea);
            }
            return $lista;
        }else{
            return false;
        }
    }
    
    public function tipo_contrato_agente($tipo_contrato,$codagente){
        $sql = "SELECT * FROM ".$this->table_name." WHERE tipo_contrato = ".$this->var2str($tipo_contrato)." AND codagente = ".$this->var2str($codagente)." AND estado = TRUE ORDER BY codagente,fecha_inicio,tipo_contrato;";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach($data as $d){
                $linea = new contratos($d);
                $lista[] = $this->info_adicional($linea);
            }
            return $lista;
        }else{
            return false;
        }
    }
    
    public function duracion_contrato(){
        if(!is_null($this->fecha_fin)){
            $from = new DateTime($this->fecha_inicio);
            $to   = new DateTime($this->fecha_fin);
            $duracion = $this->duracion($from->diff($to));
        }else{
            $duracion = "Indefinido";
        }
        return $duracion;
    }
    
    public function tiempo_restante(){
        if(!is_null($this->fecha_fin)){
            $from = new DateTime($this->fecha_inicio);
            $to   = new DateTime('today');
            $duracion = $this->duracion($from->diff($to));
        }else{
            $duracion = "Indefinido";
        }
        return $duracion;
    }
    
    public function duracion($fecha_diff){
        $years = $fecha_diff->y;
        $months = $fecha_diff->m;
        $days = $fecha_diff->d;
        $fecha = "";
        if(!empty($years)){
            $fecha.="$years ";
            $fecha.=($years>1)?"años ":"año ";
        }
        if(!empty($months)){
            $fecha.="$months ";
            $fecha.=($months>1)?"meses ":"mes ";
        }
        if(!empty($days)){
            $fecha.="$days ";
            $fecha.=($days>1)?"días ":"día ";
        }
        return $fecha;
    }
}
