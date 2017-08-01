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
 * Description of HR Organizacion
 *
 * @author Joe Nilson <joenilson at gmail dot com>
 */
class organizacion extends fs_model{
    /**
     * El codigo a generar para la organizacion
     * @var string $codorganizacion Organizacion
     */
    public $codorganizacion;
    /**
     * Si este codigo tiene un codigo superior se coloca aquí
     * @var string $padre Organizacion
     */
    public $padre;

    /**
     * Se coloca la descripción de la Organizacion
     * @var string $descripcion Organizacion
     */
    public $descripcion;

    /**
     * Aqui se pone si es del siguiente tipo:
     * GERENCIA
     * AREA
     * DEPARTAMENTO
     * en ese orden en especifico seria lo deseable,
     * para otro tipo de jerarquias se puede seguir agregando aquí y generando selects
     * dinámicamente, pero ya pertenece a otra pelicula de Conan el barbaro
     * @var string $tipo Organizacion
     */
    public $tipo;

    /**
     * Si se va desactivar un punto de la Organizacion se debe colocar aquí su estado
     * teniendo cuidado de no desactivar una gerencia que tenga areas o departamentos
     * asignados, por lo que antes de cambiar el estado se hará una verificacion
     * @var boolean $estado Boolean
     */
    public $estado;

    public function __construct($t = FALSE) {
        parent::__construct('hr_organizacion');
        if($t){
            $this->codorganizacion = $t['codorganizacion'];
            $this->descripcion = $t['descripcion'];
            $this->padre = $t['padre'];
            $this->tipo = $t['tipo'];
            $this->estado = $this->str2bool($t['estado']);
        }else{
            $this->codorganizacion = NULL;
            $this->descripcion = NULL;
            $this->padre = NULL;
            $this->tipo = NULL;
            $this->estado = FALSE;
        }
    }

    protected function install() {
            return "INSERT INTO ".$this->table_name." (codorganizacion, descripcion, padre, tipo, estado) VALUES".
                " ('1','GERENCIA GENERAL','0','GERENCIA',TRUE),".
                " ('2','CONTABILIDAD','1','AREA',TRUE),".
                " ('3','VENTAS','1','AREA',TRUE), ".
                " ('4','ADMINISTRACION','1','AREA',TRUE);";
    }

    public function url()
    {
        if( is_null($this->codorganizacion) )
        {
            return "index.php?page=admin_organizacion";
        }
        else
        {
            return "index.php?page=admin_organizacion&cod=".$this->codorganizacion;
        }
    }

    public function get_new_codigo()
    {
        $sql = "SELECT MAX(".$this->db->sql_to_int('codorganizacion').") as cod FROM ".$this->table_name.";";
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
        if(is_null($this->codorganizacion)){
            return false;
        }else{
            return $this->db->select("SELECT * FROM ".$this->table_name." WHERE ".
                " codorganizacion = ".$this->var2str($this->codorganizacion).";");
        }
    }

    public function save() {
        if($this->exists()){
            return $this->update();
        }else{
            //INSERT DATA
            $sql = "INSERT INTO ".$this->table_name." (codorganizacion, descripcion, padre, tipo, estado) VALUES (".
                $this->var2str($this->get_new_codigo()).", ".
                $this->var2str($this->descripcion).", ".
                $this->var2str($this->padre).", ".
                $this->var2str($this->tipo).", ".
                $this->var2str($this->estado).");";
            return $this->db->exec($sql);
        }
    }

    public function update(){
        $sql = "UPDATE ".$this->table_name." SET ".
            " padre = ".$this->var2str($this->padre).
            ", estado = ".$this->var2str($this->estado).
            ", descripcion = ".$this->var2str($this->descripcion).
            ", tipo = ".$this->var2str($this->tipo).
            " WHERE codorganizacion = ".$this->var2str($this->codorganizacion).";";
        return $this->db->exec($sql);
    }

    public function get($codorganizacion){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE codorganizacion = ".$this->var2str($codorganizacion).";");
        if($data){
            return new organizacion($data[0]);
        }else{
            return false;
        }
    }

    public function get_by_padre($tipo, $codorganizacion){
        $lista = array();
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE tipo = ".$this->var2str($tipo)." AND padre = ".$this->var2str($codorganizacion).";");
        if($data){
            foreach($data as $d){
                $lista[] = new organizacion($d);
            }
            return $lista;
        }else{
            return false;
        }
    }

    public function get_by_descripcion($descripcion){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE descripcion = ".$this->var2str($descripcion).";");
        if($data){
            return new organizacion($data[0]);
        }else{
            return false;
        }
    }
    
    public function get_by_descripcion_tipo($descripcion,$tipo){
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE descripcion = ".$this->var2str($descripcion)." AND tipo = ".$this->var2str($tipo).";");
        if($data){
            return new organizacion($data[0]);
        }else{
            return false;
        }
    }
    
    public function en_uso(){
        $where = ($this->tipo=='GERENCIA')?"codgerencia":"";
        $where = ($this->tipo=='AREA')?"codarea":$where;
        $where = ($this->tipo=='DEPARTAMENTO')?"coddepartamento":$where;
        $sql = "SELECT count(*) as cantidad FROM agentes WHERE $where = ".$this->var2str($this->codorganizacion).";";
        $data = $this->db->select($sql);
        if($data){
            return $data[0]['cantidad'];
        }else{
            return false;
        }
    }
    
    public function get_estructura($padre){
        $sql = "SELECT * FROM ".$this->table_name." WHERE padre = ".$this->var2str($padre)." ORDER BY descripcion";
        $data = $this->db->select($sql);
        $estructura = array();
        if($data){
            foreach($data as $d){
                $linea = new organizacion($d);
                $valores = new stdClass();
                $valores->id = $linea->codorganizacion;
                $valores->padre = $linea->padre;
                $valores->tipo = $linea->tipo;
                $valores->estado = $linea->estado;
                $valores->text = $linea->descripcion;
                $valores->en_uso = $linea->en_uso();
                $valores->tags = array(ucfirst(strtolower($linea->tipo)),(!$linea->estado)?"Inactivo":"");
                $estructura[] = $valores;
            }
            return $estructura;
        }else{
            return false;
        }
    }
    
    public function all_estructura(){
        $inicio = $this->get_estructura('0');
        $org = array();
        if($inicio){
            foreach ($inicio as $i){
                $gerencias = $this->get_estructura($i->id);
                if($gerencias){
                    $i->nodes = array();
                    foreach($gerencias as $g){
                        $areas = $this->get_estructura($g->id);
                        if($areas){
                            $g->nodes = array();
                            foreach($areas as $a){
                                $departamentos = $this->get_estructura($a->id);
                                if($departamentos){
                                    $a->nodes = array();
                                    foreach($departamentos as $d){
                                        $a->nodes[] = $d;
                                    }   
                                }
                                $g->nodes[] = $a;
                            }
                        }
                        $i->nodes[] = $g;
                        
                    }
                    $org[] = $i;
                }
                
            }
            return $org;
        }
        
    }
    
    public function tiene_hijos($padre){
        $sql = "SELECT * FROM ".$this->table_name." WHERE padre = ".$this->var2str($padre).";";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach ($data as $d){
                $linea = new organizacion($d);
                $lista[] = $linea;
            }
            return $lista;
        }else{
            return false;
        }
              
    }

    public function all(){
        $lista = array();
        $data = $this->db->select("SELECT * FROM ".$this->table_name." order by padre;");
        if($data){
            foreach($data as $d){
                $lista[] = new organizacion($d);
            }
            return $lista;
        }else{
            return false;
        }
    }

    public function all_tipo($tipo){
        $lista = array();
        $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE tipo = ".$this->var2str($tipo)." ORDER BY padre DESC;");
        if($data){
            foreach($data as $d){
                $lista[] = new organizacion($d);
            }
            return $lista;
        }else{
            return false;
        }
    }

    public function delete(){
        $sql = "DELETE FROM ".$this->table_name." WHERE codorganizacion = ".$this->var2str($this->codorganizacion);
        if($this->db->exec($sql)){
            return true;
        }else{
            return false;
        }
    }

    public function corregir(){
        $sql = "SELECT codorganizacion FROM ".$this->table_name." WHERE descripcion = ".$this->var2str($this->descripcion);
        $data = $this->db->select($sql);
        if($data){
            $this->update();
        }else{
            $this->save();
        }
    }
    
    public function estadisticas_tipo($tipo){
        $codigo = ($tipo=='GERENCIA')?'codgerencia':'codarea';
        $codigo = ($tipo=='AREA')?'codarea':$codigo;
        $codigo = ($tipo=='DEPARTAMENTO')?'coddepartamento':$codigo;
        $sql = "select o.codorganizacion, descripcion, count(codagente) as total from ".$this->table_name." as o, agentes as a ".
                "where o.tipo = ".$this->var2str($tipo)." and o.codorganizacion = a.$codigo and a.estado = 'A'".
                "GROUP BY o.codorganizacion, descripcion ORDER BY descripcion ASC; ";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach($data as $d){
                $valor = new stdClass();
                $valor->codorganizacion = $d['codorganizacion'];
                $valor->descripcion = $d['descripcion'];
                $valor->total = $d['total'];
                $lista[] = $valor;
            }
            return $lista;
        }else{
            return false;
        }
    }

}
