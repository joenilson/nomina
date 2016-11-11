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
require_model('agente.php');
require_model('tipomovimiento.php');
/**
 * Description of movimientos_empleados
 *
 * @author Joe Nilson <joenilson at gmail.com>
 */
class movimientos_empleados extends fs_model{
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
     * @type varchar(180)
     */
    public $observaciones;
    /*
     * @type varchar(6)
     */
    public $codmovimiento;
    /*
     * @type date YYYY-MM-DD
     */
    public $f_desde;
    /*
     * @type date YYYY-MM-DD
     */
    public $f_hasta;
    /*
     * @type varchar(6)
     */
    public $codautoriza;
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

    public $tipomovimiento;
    
    public function __construct($t = FALSE) {
        parent::__construct('hr_movimientos_empleados');
        if($t){
            $this->id = $t['id'];
            $this->codagente = $t['codagente'];
            $this->documento = $t['documento'];
            $this->codmovimiento = $t['codmovimiento'];
            $this->observaciones = $t['observaciones'];
            $this->f_desde = $t['f_desde'];
            $this->f_hasta = $t['f_hasta'];
            $this->codautoriza = $t['codautoriza'];
            $this->estado = $this->str2bool($t['estado']);
            $this->usuario_creacion = $t['usuario_creacion'];
            $this->usuario_modificacion = $t['usuario_modificacion'];
            $this->fecha_creacion = $t['fecha_creacion'];
            $this->fecha_modificacion = $t['fecha_modificacion'];
        }else{
            $this->id = NULL;
            $this->codagente = NULL;
            $this->documento = NULL;
            $this->codmovimiento = NULL;
            $this->observaciones = NULL;
            $this->f_desde = NULL;
            $this->f_hasta = NULL;
            $this->codautoriza = FALSE;
            $this->estado = FALSE;
            $this->usuario_creacion = NULL;
            $this->usuario_modificacion = NULL;
            $this->fecha_creacion = NULL;
            $this->fecha_modificacion = NULL;
        }
        
        $this->tipomovimiento = new tipomovimiento();
        $this->agente = new agente();
    }
    
    protected function install() {
        return '';
    }
    
    protected function info_adicional($val){
        $val->desc_movimiento = $this->tipomovimiento->get($val->codmovimiento)->descripcion;
        $val->desc_autoriza = (!empty($this->codautoriza))?$this->agente->get($this->codautoriza)->nombreap:null;
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
            $sql = "INSERT INTO ".$this->table_name." (codagente, documento, codmovimiento, observaciones, f_desde, f_hasta, codautoriza, estado, usuario_creacion, fecha_creacion ) VALUES (".
                $this->var2str($this->codagente).", ".
                $this->var2str($this->documento).", ".
                $this->var2str($this->codmovimiento).", ".
                $this->var2str($this->observaciones).", ".
                $this->var2str($this->f_desde).", ".
                $this->var2str($this->f_hasta).", ".
                $this->var2str($this->codautoriza).", ".
                $this->var2str($this->estado).", ".
                $this->var2str($this->usuario_creacion).", ".
                $this->var2str($this->fecha_creacion).");";
            return $this->db->exec($sql);
        }
    }
    
    public function update() {
        $sql = "UPDATE ".$this->table_name." SET ".
            " codautoriza = ".$this->var2str($this->codautoriza).
            " estado = ".$this->var2str($this->estado).
            ", observaciones = ".$this->var2str($this->observaciones).
            ", codmovimiento = ".$this->var2str($this->codmovimiento).
            ", f_desde = ".$this->var2str($this->f_desde).
            ", f_hasta = ".$this->var2str($this->f_hasta).
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
            $linea = new movimientos_empleados($data[0]);
            $d = $this->info_adicional($linea);
            return $d;
        }else{
            return false;
        }
    }
    
    public function all(){
        $sql = "SELECT * FROM ".$this->table_name." ORDER BY codagente,f_desde,codmovimiento;";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach($data as $d){
                $linea = new movimientos_empleados($d);
                $lista[] = $this->info_adicional($linea);
            }
            return $lista;
        }else{
            return false;
        }
    }
    
    public function activos(){
        $sql = "SELECT * FROM ".$this->table_name." WHERE estado = TRUE ORDER BY codagente,f_desde,codmovimiento;";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach($data as $d){
                $linea = new movimientos_empleados($d);
                $lista[] = $this->info_adicional($linea);
            }
            return $lista;
        }else{
            return false;
        }
    }
    
    public function tipo_movimiento($codmovimiento){
        $sql = "SELECT * FROM ".$this->table_name." WHERE codmovimiento = ".$this->var2str($codmovimiento)." AND estado = TRUE ORDER BY codagente,f_desde,codmovimiento;";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach($data as $d){
                $linea = new movimientos_empleados($d);
                $lista[] = $this->info_adicional($linea);
            }
            return $lista;
        }else{
            return false;
        }
    }
    
    public function all_agente($codagente){
        $sql = "SELECT * FROM ".$this->table_name." WHERE codagente = ".$this->var2str($codagente)." ORDER BY codagente,f_desde,codmovimiento;";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach($data as $d){
                $linea = new movimientos_empleados($d);
                $lista[] = $this->info_adicional($linea);
            }
            return $lista;
        }else{
            return false;
        }
    }
    
    public function activos_agente($codagente){
        $sql = "SELECT * FROM ".$this->table_name." WHERE codagente = ".$this->var2str($codagente)." AND estado = TRUE ORDER BY codagente,f_desde,codmovimiento;";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach($data as $d){
                $linea = new movimientos_empleados($d);
                $lista[] = $this->info_adicional($linea);
            }
            return $lista;
        }else{
            return false;
        }
    }
    
    public function tipo_movimiento_agente($codmovimiento,$codagente){
        $sql = "SELECT * FROM ".$this->table_name." WHERE codmovimiento = ".$this->var2str($codmovimiento)." AND codagente = ".$this->var2str($codagente)." ORDER BY codagente,f_desde,codmovimiento;";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach($data as $d){
                $linea = new movimientos_empleados($d);
                $lista[] = $this->info_adicional($linea);
            }
            return $lista;
        }else{
            return false;
        }
    }
    
    public function duracion_movimiento(){
        if(!is_null($this->f_hasta)){
            $from = new DateTime($this->f_desde);
            $to   = new DateTime($this->f_hasta);
            $duracion = $this->duracion($from->diff($to));
        }else{
            $duracion = "Indeterminado";
        }
        return $duracion;
    }
    
    public function tiempo_restante(){
        if(!is_null($this->f_hasta)){
            $from = new DateTime($this->f_desde);
            $to   = new DateTime('today');
            $duracion = $this->duracion($from->diff($to));
        }else{
            $duracion = "Indeterminado";
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
