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
require_model('tipodependientes.php');
require_model('formacion.php');
/**
 * Description of dependientes
 *
 * @author Joe Nilson <joenilson at gmail.com>
 */
class dependientes extends fs_model{
    /*
     * @type integer Id
     */
    public $id;
    /*
     * @type varchar(6)
     */
    public $codagente;
    /*
     * @type varchar(64)
     */
    public $nombres;
    /*
     * @type varchar(100)
     */
    public $apellido_paterno;
    /*
     * @type varchar(100)
     */
    public $apellido_materno;
    /*
     * @type varchar(6)
     */
    public $coddependiente;
    /*
     * @type varchar(16)
     */
    public $docidentidad;
    /*
     * @type timestamp YYYY-MM-DD H:i:s
     */
    public $f_nacimiento;
    /*
     * @type varchar(1) M / F
     */
    public $genero;
    /*
     * @type varchar(6)
     */
    public $grado_academico;
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

    public $tipodependientes;
    public $formacion;

    public function __construct($t = FALSE) {
        parent::__construct('hr_dependientes');
        if($t){
            $this->id = $t['id'];
            $this->codagente = $t['codagente'];
            $this->nombres = $t['nombres'];
            $this->apellido_paterno = $t['apellido_paterno'];
            $this->apellido_materno = $t['apellido_materno'];
            $this->coddependiente = $t['coddependiente'];
            $this->docidentidad = $t['docidentidad'];
            $this->f_nacimiento = $t['f_nacimiento'];
            $this->genero = $t['genero'];
            $this->grado_academico = $t['grado_academico'];
            $this->estado = $this->str2bool($t['estado']);
            $this->usuario_creacion = $t['usuario_creacion'];
            $this->usuario_modificacion = $t['usuario_modificacion'];
            $this->fecha_creacion = $t['fecha_creacion'];
            $this->fecha_modificacion = $t['fecha_modificacion'];
        }else{
            $this->id = NULL;
            $this->codagente = NULL;
            $this->nombres = NULL;
            $this->apellido_paterno = NULL;
            $this->apellido_materno = NULL;
            $this->coddependiente = NULL;
            $this->docidentidad = NULL;
            $this->f_nacimiento = NULL;
            $this->genero = NULL;
            $this->grado_academico = NULL;
            $this->estado = FALSE;
            $this->usuario_creacion = NULL;
            $this->usuario_modificacion = NULL;
            $this->fecha_creacion = NULL;
            $this->fecha_modificacion = NULL;
        }

        $this->tipodependientes = new tipodependientes();
        $this->formacion = new formacion();
    }

    protected function install() {
        return '';
    }

    protected function info_adicional($val){
        $val->desc_tipodependiente = $this->tipodependientes->get($val->coddependiente)->descripcion;
        $val->desc_grado_academico = $this->formacion->get($val->grado_academico)->nombre;
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
            $sql = "INSERT INTO ".$this->table_name." (codagente, nombres, apellido_paterno, apellido_materno, coddependiente, docidentidad, f_nacimiento, genero, grado_academico, estado, usuario_creacion, fecha_creacion ) VALUES (".
                $this->var2str($this->codagente).", ".
                $this->var2str($this->nombres).", ".
                $this->var2str($this->apellido_paterno).", ".
                $this->var2str($this->apellido_materno).", ".
                $this->var2str($this->coddependiente).", ".
                $this->var2str($this->docidentidad).", ".
                $this->var2str($this->f_nacimiento).", ".
                $this->var2str($this->genero).", ".
                $this->var2str($this->grado_academico).", ".
                $this->var2str($this->estado).", ".
                $this->var2str($this->usuario_creacion).", ".
                $this->var2str($this->fecha_creacion).");";
            return $this->db->exec($sql);
        }
    }

    public function nombreap(){
        return $this->nombres.' '.$this->apellido_paterno.' '.$this->apellido_materno;
    }

    public function update() {
        $sql = "UPDATE ".$this->table_name." SET ".
            " grado_academico = ".$this->var2str($this->grado_academico).
            " estado = ".$this->var2str($this->estado).
            ", coddependiente = ".$this->var2str($this->coddependiente).
            ", docidentidad = ".$this->var2str($this->docidentidad).
            ", f_nacimiento = ".$this->var2str($this->f_nacimiento).
            ", genero = ".$this->var2str($this->genero).
            ", nombres = ".$this->var2str($this->nombres).
            ", apellido_paterno = ".$this->var2str($this->apellido_paterno).
            ", apellido_materno = ".$this->var2str($this->apellido_materno).
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
            $linea = new dependientes($data[0]);
            $d = $this->info_adicional($linea);
            return $d;
        }else{
            return false;
        }
    }

    public function all(){
        $sql = "SELECT * FROM ".$this->table_name." ORDER BY codagente,f_nacimiento,coddependiente;";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach($data as $d){
                $linea = new dependientes($d);
                $lista[] = $this->info_adicional($linea);
            }
            return $lista;
        }else{
            return false;
        }
    }

    public function activos(){
        $sql = "SELECT * FROM ".$this->table_name." WHERE estado = TRUE ORDER BY codagente,f_nacimiento,coddependiente;";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach($data as $d){
                $linea = new dependientes($d);
                $lista[] = $this->info_adicional($linea);
            }
            return $lista;
        }else{
            return false;
        }
    }

    public function tipo_dependiente($coddependiente){
        $sql = "SELECT * FROM ".$this->table_name." WHERE coddependiente = ".$this->var2str($coddependiente)." AND estado = TRUE ORDER BY codagente,f_nacimiento,coddependiente;";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach($data as $d){
                $linea = new dependientes($d);
                $lista[] = $this->info_adicional($linea);
            }
            return $lista;
        }else{
            return false;
        }
    }

    public function all_agente($codagente){
        $sql = "SELECT * FROM ".$this->table_name." WHERE codagente = ".$this->var2str($codagente)." ORDER BY codagente,f_nacimiento,coddependiente;";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach($data as $d){
                $linea = new dependientes($d);
                $lista[] = $this->info_adicional($linea);
            }
            return $lista;
        }else{
            return false;
        }
    }

    public function activos_agente($codagente){
        $sql = "SELECT * FROM ".$this->table_name." WHERE codagente = ".$this->var2str($codagente)." AND estado = TRUE ORDER BY codagente,f_nacimiento,coddependiente;";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach($data as $d){
                $linea = new dependientes($d);
                $lista[] = $this->info_adicional($linea);
            }
            return $lista;
        }else{
            return false;
        }
    }

    public function tipo_dependiente_agente($coddependiente,$codagente){
        $sql = "SELECT * FROM ".$this->table_name." WHERE coddependiente = ".$this->var2str($coddependiente)." AND codagente = ".$this->var2str($codagente)." AND estado = TRUE ORDER BY codagente,f_nacimiento,coddependiente;";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach($data as $d){
                $linea = new dependientes($d);
                $lista[] = $this->info_adicional($linea);
            }
            return $lista;
        }else{
            return false;
        }
    }

    public function edad_dependiente(){
        $from = new DateTime($this->f_nacimiento);
        $to   = new DateTime($this->genero);
        $edad = $this->calculo_edad($from->diff($to));
        return $edad;
    }

    public function calculo_edad($fecha_diff){
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

    public function resumen_dependientes(){
        $sql = "SELECT coddependiente,genero,count(id) as cantidad FROM ".$this->table_name." WHERE estado = TRUE group BY coddependiente,genero";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach($data as $d){
                $linea = new stdClass();
                $linea->coddependiente = $d['coddependiente'];
                $linea->descripcion = $this->tipodependientes->get($d['coddependiente'])->descripcion;
                $linea->genero = $d['genero'];
                $linea->cantidad = $d['cantidad'];
                $lista[] = $linea;
            }
            return $lista;
        }else{
            return false;
        }
    }
}
