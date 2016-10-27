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
require_model('ausencias.php');
require_model('cargos.php');
require_model('contratos.php');
require_model('dependientes.php');
require_model('hoja_vida.php');
require_model('movimientos_empleados.php');
require_model('categoriaempleado.php');
require_model('estadocivil.php');
require_model('formacion.php');
require_model('generaciones.php');
require_model('motivocese.php');
require_model('organizacion.php');
require_model('seguridadsocial.php');
require_model('sistemapension.php');
require_model('sindicalizacion.php');
require_model('tipoausencias.php');
require_model('tipocese.php');
require_model('tipoempleado.php');
require_model('tipodependientes.php');
require_model('tipomovimiento.php');
require_model('tipopago.php');
/**
 * Description of configuracion_nomina
 *
 * @author Joe Nilson <joenilson at gmail dot com>
 */
class configuracion_nomina extends fs_controller{
    public $agente;
    public $cargo;
    public $cargos;
    public $estadocivil;
    public $estadosciviles;
    public $generacion;
    public $generaciones;
    public $motivocese;
    public $tipoausencias;
    public $tipocese;
    public $tipoempleado;
    public $tipodependientes;
    public $tipomovimiento;
    public $tipopago;
    public $categoriaempleado;
    public $sindicalizacion;
    public $organizacion;
    public $organizacion_actual;
    public $formacion;
    public $seguridadsocial;
    public $sistemapension;
    public $existe;
    public $dir;
    public $creada;
    public $fix_cargos = '';
    public $nomina_setup;
    public $nomina_migracion_informacion;
    public $fsvar;
    public function __construct() {
        parent::__construct(__CLASS__, 'Configuracion Nomina', 'nomina', TRUE, TRUE, FALSE);
    }

    protected function private_core() {
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
        $this->share_extensions();
        $this->agente = new agente();
        $this->cargo = new cargos();
        $this->estadosciviles = new estadocivil();
        $this->formacion = new formacion();
        $this->generaciones = new generaciones();
        $this->motivocese = new motivocese();
        $this->tipoausencias = new tipoausencias();
        $this->tipocese = new tipocese();
        $this->tipoempleado = new tipoempleado();
        $this->tipodependientes = new tipodependientes();
        $this->tipomovimiento = new tipomovimiento();
        $this->categoriaempleado = new categoriaempleado();
        $this->sindicalizacion = new sindicalizacion();
        $this->seguridadsocial = new seguridadsocial();
        $this->sistemapension = new sistemapension();
        $this->organizacion = new organizacion();
        $this->tipopago = new tipopago();
        
        //Tablas de datos para los empleados
        new ausencias();
        new contratos();
        new dependientes();
        new hoja_vida();
        new movimientos_empleados();
        
        $this->fsvar = new fs_var();
        //Aqui almacenamos las variables del plugin
        $this->nomina_setup = $this->fsvar->array_get(
            array(
            'nomina_migracion_informacion' => 'FALSE',
            ), FALSE
        );
        
        $this->nomina_migracion_informacion = $this->nomina_setup['nomina_migracion_informacion'];
        
        if(isset($_GET['type'])){
            switch($_GET['type']){
                case "importar_cargos":
                        $this->importar_cargos();
                    break;
                case "cargos":
                    $this->template = 'configuracion/nomina_cargos';
                    if(isset($_POST['codcargo'])){
                        $this->tratar_cargos();
                    }
                    break;
                case "categorias":
                    $this->template = 'configuracion/nomina_categoriaempleado';
                    if(isset($_REQUEST['codcategoria'])){
                        $this->tratar_categorias();
                    }
                    break;
                case "dependientes":
                    $this->template = 'configuracion/nomina_dependientes';
                    if(isset($_POST['coddependiente'])){
                        $this->tratar_dependientes();
                    }
                    break;
                case "formacion":
                    $this->template = 'configuracion/nomina_formacion';
                    if(isset($_POST['codformacion'])){
                        $this->tratar_formaciones();
                    }
                    break;
                case "organizacion":
                    $this->template = 'configuracion/nomina_organizacion';
                    if(isset($_POST['codorganizacion'])){
                        $this->tratar_organizacion();
                    }
                    if(isset($_GET['subtype'])){
                        $this->tareas_organizacion();
                    }
                    break;
                case "seguridadsocial":
                    $this->template = 'configuracion/nomina_seguridadsocial';
                    if(isset($_POST['codseguridadsocial'])){
                        $this->tratar_seguridadsocial();
                    }
                    break;
                case "sistemapension":
                    $this->template = 'configuracion/nomina_sistemapension';
                    if(isset($_POST['codsistemapension'])){
                        $this->tratar_sistemapension();
                    }
                    break;
                case "sindicalizacion":
                    $this->template = 'configuracion/nomina_sindicalizacion';
                    if(isset($_POST['idsindicato'])){
                        $this->tratar_sindicalizacion();
                    }
                    break;
                case "tipoempleado":
                    $this->template = 'configuracion/nomina_tipoempleado';
                    if(isset($_POST['codtipo'])){
                        $this->tratar_tipoempleado();
                    }
                    break;
                case "ausencias":
                    $this->template = 'configuracion/nomina_ausencias';
                    if(isset($_POST['codausencia'])){
                        $this->tratar_ausencias();
                    }
                    break;
                case "generaciones":
                    $this->template = 'configuracion/nomina_generaciones';
                    if(isset($_POST['codgeneracion'])){
                        $this->tratar_generaciones();
                    }
                    break;
                case "movimientos":
                    $this->template = 'configuracion/nomina_movimientos';
                    if(isset($_POST['codmovimiento'])){
                        $this->tratar_movimientos();
                    }
                    break;
                case "estadocivil":
                    $this->template = 'configuracion/nomina_estadocivil';
                    if(isset($_POST['codestadocivil'])){
                        $this->tratar_estadosciviles();
                    }
                    break;
                case "pagos":
                    $this->template = 'configuracion/nomina_pagos';
                    if(isset($_POST['codpago'])){
                        $this->tratar_pagos();
                    }
                    break;
                case "motivocese":
                    $this->template = 'configuracion/nomina_motivocese';
                    if(isset($_POST['codmotivocese'])){
                        $this->tratar_motivocese();
                    }
                    break;
                case "tipocese":
                    $this->template = 'configuracion/nomina_tipocese';
                    if(isset($_POST['codtipocese'])){
                        $this->tratar_motivocese();
                    }
                default:
                    break;
            }
        }
        
        
        //Validamos si existen las carpetas de almacenamiento de datos
        // imagenes de empleados
        $this->creada = false;
        $basepath = dirname(dirname(dirname(__DIR__)));
        $this->dir_documentos = $basepath.FS_MYDOCS."/documentos";
        $this->dir_nomina = $this->dir_documentos.DIRECTORY_SEPARATOR."nomina";
        $this->dir_empleados = $this->dir_nomina.DIRECTORY_SEPARATOR.$this->empresa->id."/e/";
        $this->dir_documentos_empleados = $this->dir_nomina.DIRECTORY_SEPARATOR.$this->empresa->id."/d/";
        
        //Existe el almacen de documentos?
        if(!is_dir($this->dir_documentos)){
            mkdir($this->dir_documentos);
        }
        
        //Existe la carpeta de nomina?
        if(!is_dir($this->dir_nomina)){
            mkdir($this->dir_nomina);
        }
        
        //Existe la carpeta para los documentos de la empresa
        if(!is_dir($this->dir_nomina.DIRECTORY_SEPARATOR.$this->empresa->id)){
            mkdir($this->dir_nomina.DIRECTORY_SEPARATOR.$this->empresa->id);
        }
        
        //Validamos si existe el directorio para las imagenes de los empleados
        $this->creada = false;
        if(!is_dir($this->dir_empleados)){
            $this->existe = "NO";
            if(mkdir($this->dir_empleados)){
                $this->existe = "SI";
                $this->creada = true;
            }
        }else{
            $this->existe = "SI";
            $this->creada = true;
        }
        // archivos generados
        // $this->creada = false;
        if(!is_dir($this->dir_documentos_empleados)){
            $this->existe = "NO";
            if(mkdir($this->dir_documentos_empleados)){
                $this->existe = "SI";
                $this->creada = true;
            }
        }else{
            $this->existe = "SI";
            $this->creada = true;
        }
        // formatos de presentacion
        //@TODO

    }
    
    public function importar_cargos(){
        //Cargamos los datos por primera vez
        $this->fix_info();
        //Movemos Cargo a la tabla de cargos y banco al campo cuenta_banco
        $this->trasladar_datos();
        $nomina_setup = array(
               'nomina_migracion_informacion' => 'TRUE'
            );
        $this->fsvar->array_save($nomina_setup);
        
        //Aqui almacenamos las variables del plugin
        $this->nomina_setup = $this->fsvar->array_get(
            array(
            'nomina_migracion_informacion' => 'FALSE',
            ), FALSE
        );
        $this->nomina_migracion_informacion = $this->nomina_setup['nomina_migracion_informacion'];
    }
    
    public function tratar_ausencias(){
        $accion = filter_input(INPUT_POST, 'accion');
        if($accion == 'agregar'){
            $au0 = new tipoausencias();
            $au0->codausencia = filter_input(INPUT_POST, 'codausencia');
            $au0->descripcion = $this->mayusculas(filter_input(INPUT_POST, 'descripcion'));
            $au0->aplicar_descuento = (isset($_POST['aplicar_descuento']))?filter_input(INPUT_POST, 'aplicar_descuento'):'false';
            $au0->estado = filter_input(INPUT_POST, 'estado');
            $estado = $au0->save();
            if($estado){
                $this->new_message("Datos guardados correctamente.");
            }else{
                $this->new_error_msg("La información con el Id ".$au0->codausencia." No pudo ser guardada, revise los datos e intente nuevamente. Error: ".$estado);
            }
        }elseif($accion=='eliminar'){
            $ausencia = $this->tipoausencias->get(\filter_input(INPUT_POST, 'codausencia'));
            if($ausencia->delete()){
                $this->new_message("Datos eliminados correctamente.");
            }else{
                $this->new_error_msg("La información no pudo ser eliminada, revise los datos e intente nuevamente");
            }
        }
    }

    public function tratar_cargos(){
        $accion = \filter_input(INPUT_POST, 'accion');
        if($accion == 'agregar'){
            $c0 = new cargos();
            $c0->codcargo = \filter_input(INPUT_POST, 'codcargo');
            $c0->codcategoria = \filter_input(INPUT_POST, 'codcategoria');
            $c0->descripcion = $this->mayusculas(filter_input(INPUT_POST, 'descripcion'));
            $c0->padre = \filter_input(INPUT_POST, 'padre');
            $c0->estado = \filter_input(INPUT_POST, 'estado');
            $estado = $c0->save();
            if($estado){
                $this->new_message("Datos guardados correctamente.");
            }else{
                $this->new_error_msg("La información con el Id ".$c0->codcargo." No pudo ser guardada, revise los datos e intente nuevamente. Error: ".$estado);
            }
        }elseif($accion=='eliminar'){
            $ausencia = $this->cargos->get(\filter_input(INPUT_POST, 'codcargo'));
            if($ausencia->delete()){
                $this->new_message("Datos eliminados correctamente.");
            }else{
                $this->new_error_msg("La información no pudo ser eliminada, revise los datos e intente nuevamente");
            }
        }
    }

    public function tratar_categorias(){
        $ca0 = new categoriaempleado();
        if(isset($_GET['reorden']) AND !empty($_GET['reorden'])){
            $categoria_reordenar = $ca0->get(filter_input(INPUT_GET, 'codcategoria'));
            $categoria_reordenar->reordenar(filter_input(INPUT_GET, 'reorden'));
        }else{
            $accion = \filter_input(INPUT_POST, 'accion');
            if($accion == 'agregar'){
                $ca0->codcategoria = filter_input(INPUT_POST, 'codcategoria');
                $ca0->descripcion = $this->mayusculas(filter_input(INPUT_POST, 'descripcion'));
                $ca0->orden = (filter_input(INPUT_POST, 'orden')==0)?$ca0->get_maxorden():filter_input(INPUT_POST, 'orden');
                $ca0->estado = filter_input(INPUT_POST, 'estado');
                $estado = $ca0->save();
                if($estado){
                    $this->new_message("Datos guardados correctamente.");
                }else{
                    $this->new_error_msg("La Categoria con el Id ".$ca0->codcategoria." No pudo ser guardada, revise los datos e intente nuevamente. Error: ".$estado);
                }
            }elseif($accion=='eliminar'){
                $categorias = $this->categoriaempleado->get(\filter_input(INPUT_POST, 'codcategoria'));
                if($categorias->delete()){
                    $this->new_message("Datos eliminados correctamente.");
                }else{
                    $this->new_error_msg("La información no pudo ser eliminada, revise los datos e intente nuevamente");
                }
            }
        }
    }
    
    public function tratar_generaciones(){
        $accion = \filter_input(INPUT_POST, 'accion');
        if($accion == 'agregar'){
            $gen0 = new generaciones();
            $gen0->codgeneracion = filter_input(INPUT_POST, 'codgeneracion');
            $gen0->descripcion = $this->mayusculas(filter_input(INPUT_POST, 'descripcion'));
            $gen0->inicio_generacion = filter_input(INPUT_POST, 'inicio_generacion');
            $gen0->fin_generacion = filter_input(INPUT_POST, 'fin_generacion');
            $gen0->estado = filter_input(INPUT_POST, 'estado');
            $estado = $gen0->save();
            if($estado){
                $this->new_message("Datos guardados correctamente.");
            }else{
                $this->new_error_msg("La información con el Id ".$gen0->codgeneracion." No pudo ser guardado, revise los datos e intente nuevamente. Error: ".$estado);
            }
        }elseif($accion=='eliminar'){
            $generaciones = $this->generaciones->get(\filter_input(INPUT_POST, 'codgeneracion'));
            if($generaciones->delete()){
                $this->new_message("Datos eliminados correctamente.");
            }else{
                $this->new_error_msg("La información no pudo ser eliminada, revise los datos e intente nuevamente");
            }
        }
    }
    
    public function tratar_estadosciviles(){
        $accion = \filter_input(INPUT_POST, 'accion');
        if($accion == 'agregar'){        
            $estciv0 = new estadocivil();
            $estciv0->codestadocivil = filter_input(INPUT_POST, 'codestadocivil');
            $estciv0->descripcion = $this->mayusculas(filter_input(INPUT_POST, 'descripcion'));
            $estado = $estciv0->save();
            if($estado){
                $this->new_message("Datos guardados correctamente.");
            }else{
                $this->new_error_msg("La información con el Id ".$estciv0->codestadocivil." No pudo ser guardado, revise los datos e intente nuevamente. Error: ".$estado);
            }
        }elseif($accion=='eliminar'){
            $estadocivil = $this->estadosciviles->get(\filter_input(INPUT_POST, 'codestadocivil'));
            if($estadocivil->delete()){
                $this->new_message("Datos eliminados correctamente.");
            }else{
                $this->new_error_msg("La información no pudo ser eliminada, revise los datos e intente nuevamente");
            }
        }
    }
    
    public function tratar_motivocese(){
        $accion = \filter_input(INPUT_POST, 'accion');
        if($accion == 'agregar'){         
            $ing0 = new motivocese();
            $ing0->codmotivocese = filter_input(INPUT_POST, 'codmotivocese');
            $ing0->codtipocese = filter_input(INPUT_POST, 'codtipocese');
            $ing0->descripcion = $this->mayusculas(filter_input(INPUT_POST, 'descripcion'));
            $ing0->estado = filter_input(INPUT_POST, 'estado');
            $estado = $ing0->save();
            if($estado){
                $this->new_message("Datos guardados correctamente.");
            }else{
                $this->new_error_msg("La información con el Id ".$ing0->codmotivocese." No pudo ser guardado, revise los datos e intente nuevamente. Error: ".$estado);
            }
        }elseif($accion=='eliminar'){
            $motivocese = $this->motivocese->get(\filter_input(INPUT_POST, 'codmotivocese'));
            if($motivocese->delete()){
                $this->new_message("Datos eliminados correctamente.");
            }else{
                $this->new_error_msg("La información no pudo ser eliminada, revise los datos e intente nuevamente");
            }
        }
    }
    
    public function tratar_tipocese(){
        $accion = \filter_input(INPUT_POST, 'accion');
        if($accion == 'agregar'){          
            $ing0 = new tipocese();
            $ing0->codtipocese = filter_input(INPUT_POST, 'codtipocese');
            $ing0->descripcion = $this->mayusculas(filter_input(INPUT_POST, 'descripcion'));
            $ing0->estado = filter_input(INPUT_POST, 'estado');
            $estado = $ing0->save();
            if($estado){
                $this->new_message("Datos guardados correctamente.");
            }else{
                $this->new_error_msg("La información con el Id ".$ing0->codtipocese." No pudo ser guardado, revise los datos e intente nuevamente. Error: ".$estado);
            }
        }elseif($accion=='eliminar'){
            $tipocese = $this->tipocese->get(\filter_input(INPUT_POST, 'codtipocese'));
            if($tipocese->delete()){
                $this->new_message("Datos eliminados correctamente.");
            }else{
                $this->new_error_msg("La información no pudo ser eliminada, revise los datos e intente nuevamente");
            }
        }            
    }
    
    public function tratar_movimientos(){
        $accion = \filter_input(INPUT_POST, 'accion');
        if($accion == 'agregar'){         
            $tmov0 = new tipomovimiento();
            $tmov0->codmovimiento = filter_input(INPUT_POST, 'codmovimiento');
            $tmov0->descripcion = $this->mayusculas(filter_input(INPUT_POST, 'descripcion'));
            $tmov0->estado = filter_input(INPUT_POST, 'estado');
            $estado = $tmov0->save();
            if($estado){
                $this->new_message("Datos guardados correctamente.");
            }else{
                $this->new_error_msg("La información con el Id ".$tmov0->codmovimiento." No pudo ser guardado, revise los datos e intente nuevamente. Error: ".$estado);
            }
        }elseif($accion=='eliminar'){
            $tipomovimiento = $this->tipomovimiento->get(\filter_input(INPUT_POST, 'codmovimiento'));
            if($tipomovimiento->delete()){
                $this->new_message("Datos eliminados correctamente.");
            }else{
                $this->new_error_msg("La información no pudo ser eliminada, revise los datos e intente nuevamente");
            }
        }            
    }
    
    public function tratar_pagos(){
        $accion = \filter_input(INPUT_POST, 'accion');
        if($accion == 'agregar'){   
            $tp0 = new tipopago();
            $tp0->codpago = filter_input(INPUT_POST, 'codpago');
            $tp0->descripcion = $this->mayusculas(filter_input(INPUT_POST, 'descripcion'));
            $tp0->es_basico = (isset($_POST['es_basico']))?filter_input(INPUT_POST, 'es_basico'):'false';
            $tp0->estado = filter_input(INPUT_POST, 'estado');
            $estado = $tp0->save();
            if($estado){
                $this->new_message("Datos guardados correctamente.");
            }else{
                $this->new_error_msg("La información con el Id ".$tp0->codpago." No pudo ser guardado, revise los datos e intente nuevamente. Error: ".$estado);
            }
        }elseif($accion=='eliminar'){
            $tipopago = $this->tipopago->get(\filter_input(INPUT_POST, 'codpago'));
            if($tipopago->delete()){
                $this->new_message("Datos eliminados correctamente.");
            }else{
                $this->new_error_msg("La información no pudo ser eliminada, revise los datos e intente nuevamente");
            }
        }   
    }
    
    public function tratar_formaciones(){
        $accion = \filter_input(INPUT_POST, 'accion');
        if($accion == 'agregar'){         
            $form0 = new formacion();
            $form0->codformacion = filter_input(INPUT_POST, 'codformacion');
            $form0->nombre = $this->mayusculas(filter_input(INPUT_POST, 'nombre'));
            $form0->estado = filter_input(INPUT_POST, 'estado');
            $estado = $form0->save();
            if($estado){
                $this->new_message("Datos guardados correctamente.");
            }else{
                $this->new_error_msg("La información con el Id ".$form0->codformacion." No pudo ser guardado, revise los datos e intente nuevamente. Error: ".$estado);
            }
        }elseif($accion=='eliminar'){
            $formacion = $this->formacion->get(\filter_input(INPUT_POST, 'codformacion'));
            if($formacion->delete()){
                $this->new_message("Datos eliminados correctamente.");
            }else{
                $this->new_error_msg("La información no pudo ser eliminada, revise los datos e intente nuevamente");
            }
        }              
    }
    
    public function tratar_dependientes(){
        $accion = \filter_input(INPUT_POST, 'accion');
        if($accion == 'agregar'){        
            $dep0 = new tipodependientes();
            $dep0->coddependiente = filter_input(INPUT_POST, 'coddependiente');
            $dep0->descripcion = $this->mayusculas(filter_input(INPUT_POST, 'descripcion'));
            $dep0->estado = filter_input(INPUT_POST, 'estado');
            $estado = $dep0->save();
            if($estado){
                $this->new_message("Datos guardados correctamente.");
            }else{
                $this->new_error_msg("La información con el Id ".$dep0->coddependiente." No pudo ser guardado, revise los datos e intente nuevamente. Error: ".$estado);
            }
        }elseif($accion=='eliminar'){
            $formacion = $this->formacion->get(\filter_input(INPUT_POST, 'codformacion'));
            if($formacion->delete()){
                $this->new_message("Datos eliminados correctamente.");
            }else{
                $this->new_error_msg("La información no pudo ser eliminada, revise los datos e intente nuevamente");
            }
        }            
    }
    
    public function tratar_seguridadsocial(){
        $accion = filter_input(INPUT_POST, 'accion');
        if($accion == 'agregar'){
            $ss0 = new seguridadsocial();
            $ss0->codseguridadsocial = filter_input(INPUT_POST, 'codseguridadsocial');
            $ss0->nombre = $this->mayusculas(filter_input(INPUT_POST, 'nombre'));
            $ss0->nombre_corto = $this->mayusculas(filter_input(INPUT_POST, 'nombre_corto'));
            $ss0->tipo = $this->mayusculas(filter_input(INPUT_POST, 'tipo'));
            $ss0->estado = filter_input(INPUT_POST, 'estado');
            $estado = $ss0->save();
            if($estado){
                $this->new_message("Datos guardados correctamente.");
            }else{
                $this->new_error_msg("La información con el Id ".$ss0->codseguridadsocial." No pudo ser guardado, revise los datos e intente nuevamente. Error: ".$estado);
            }
        }elseif($accion=='eliminar'){
            $segsoc = $this->seguridadsocial->get(\filter_input(INPUT_POST, 'codseguridadsocial'));
            if($segsoc->delete()){
                $this->new_message("Datos eliminados correctamente.");
            }else{
                $this->new_error_msg("La información no pudo ser eliminada, revise los datos e intente nuevamente");
            }
        }
    }
    
    public function tratar_sistemapension(){
        $accion = filter_input(INPUT_POST, 'accion');
        if($accion == 'agregar'){
            $ss0 = new sistemapension();
            $ss0->codsistemapension = filter_input(INPUT_POST, 'codsistemapension');
            $ss0->nombre = $this->mayusculas(filter_input(INPUT_POST, 'nombre'));
            $ss0->nombre_corto = $this->mayusculas(filter_input(INPUT_POST, 'nombre_corto'));
            $ss0->tipo = $this->mayusculas(filter_input(INPUT_POST, 'tipo'));
            $ss0->estado = filter_input(INPUT_POST, 'estado');
            $estado = $ss0->save();
            if($estado){
                $this->new_message("Datos guardados correctamente.");
            }else{
                $this->new_error_msg("La información con el Id ".$ss0->codsistemapension." No pudo ser guardado, revise los datos e intente nuevamente. Error: ".$estado);
            }
        }elseif($accion=='eliminar'){
            $sispen = $this->sistemapension->get(\filter_input(INPUT_POST, 'codsistemapension'));
            if($sispen->delete()){
                $this->new_message("Datos eliminados correctamente.");
            }else{
                $this->new_error_msg("La información no pudo ser eliminada, revise los datos e intente nuevamente");
            }
        }
    }
    
    public function tratar_sindicalizacion(){
        $accion = filter_input(INPUT_POST, 'accion');
        if($accion == 'agregar'){
            $sind0 = new sindicalizacion();
            $sind0->idsindicato = filter_input(INPUT_POST, 'idsindicato');
            $sind0->descripcion = $this->mayusculas(filter_input(INPUT_POST, 'descripcion'));
            $sind0->estado = filter_input(INPUT_POST, 'estado');
            $estado = $sind0->save();
            if($estado){
                $this->new_message("Datos guardados correctamente.");
            }else{
                $this->new_error_msg("La información con el Id ".$sind0->idsindicato." No pudo ser guardado, revise los datos e intente nuevamente. Error: ".$estado);
            }
        }elseif($accion=='eliminar'){
            $sindicalizacion = $this->sindicalizacion->get(\filter_input(INPUT_POST, 'idsindicato'));
            if($sindicalizacion->delete()){
                $this->new_message("Datos eliminados correctamente.");
            }else{
                $this->new_error_msg("La información no pudo ser eliminada, revise los datos e intente nuevamente");
            }
        }
    }
    
    public function tratar_tipoempleado(){
        $accion = filter_input(INPUT_POST, 'accion');
        if($accion == 'agregar'){
            $te0 = new tipoempleado();
            $te0->codtipo = filter_input(INPUT_POST, 'codtipo');
            $te0->descripcion = $this->mayusculas(filter_input(INPUT_POST, 'descripcion'));
            $te0->estado = filter_input(INPUT_POST, 'estado');
            $estado = $te0->save();
            if($estado){
                $this->new_message("Datos guardados correctamente.");
            }else{
                $this->new_error_msg("La información con el Id ".$te0->codtipo." No pudo ser guardado, revise los datos e intente nuevamente. Error: ".$estado);
            }
        }elseif($accion=='eliminar'){
            $tipoempleado = $this->tipoempleado->get(\filter_input(INPUT_POST, 'codtipo'));
            if($tipoempleado->delete()){
                $this->new_message("Datos eliminados correctamente.");
            }else{
                $this->new_error_msg("La información no pudo ser eliminada, revise los datos e intente nuevamente");
            }
        }
    }
    
    public function tratar_organizacion(){
        $accion = filter_input(INPUT_POST, 'accion');
        if($accion == 'agregar'){        
            $org0 = new organizacion();
            $org0->codorganizacion = filter_input(INPUT_POST, 'codorganizacion');
            $org0->padre = filter_input(INPUT_POST, 'padre');
            $org0->descripcion = $this->mayusculas(filter_input(INPUT_POST, 'descripcion'));
            $org0->tipo = $this->mayusculas(filter_input(INPUT_POST, 'tipo'));
            $org0->estado = filter_input(INPUT_POST, 'estado');
            $estado = $org0->save();
            if($estado){
                $this->new_message("Datos guardados correctamente.");
            }else{
                $this->new_error_msg("La información con el Id ".$org0->codorganizacion." No pudo ser guardado, revise los datos e intente nuevamente. Error: ".$estado);
            }
        }elseif($accion=='eliminar'){
            $organizacion = $this->organizacion->get(\filter_input(INPUT_POST, 'codorganizacion'));
            if($organizacion->delete()){
                $this->new_message("Datos eliminados correctamente.");
            }else{
                $this->new_error_msg("La información no pudo ser eliminada, revise los datos e intente nuevamente");
            }
        }            
        
    }
    
    public function tareas_organizacion(){
        $subtype = filter_input(INPUT_GET, 'subtype');
        if($subtype == 'arbol_estructura'){
            $estructura = $this->organizacion->all_estructura();
            $this->template = false;
            header('Content-Type: application/json');
            echo json_encode($estructura);
        }
    }

    private function mayusculas($string){
        return strtoupper(trim(strip_tags(stripslashes($string))));
    }

    private function minusculas($string){
        return strtolower(trim(strip_tags(stripslashes($string))));
    }

    protected function fix_info(){
        $agentes = $this->agente->all();
        foreach($agentes as $agente){
            if(is_null($agente->idempresa)){
                $agente->codalmacen = 'ALG';
                $agente->idempresa = $this->empresa->id;
                $agente->fecha_creacion = $agente->f_alta;
                $agente->usuario_creacion = $this->user->nick;
                $agente->corregir();
            }
        }
    }

    protected function trasladar_datos(){
        $agentes = $this->agente->all();
        $this->cargos = array();
        foreach($agentes as $agente){
            $agente->cargo = (empty($agente->cargo))?'EMPLEADO':$agente->cargo;
            $this->cargos[$agente->cargo] = $agente->cargo;
            if(empty($agente->cuenta_banco)){
                $agente->codalmacen = 'ALG';
                $agente->idempresa = $this->empresa->id;
                $agente->cuenta_banco = $agente->banco;
                $agente->fecha_creacion = $agente->f_alta;
                $agente->usuario_creacion = $this->user->nick;
                $agente->corregir();
            }
        }

        foreach($this->cargos as $cargo){
            if($cargo){
                $c0 = new cargos();
                $c0->descripcion = strtoupper(trim($cargo));
                $c0->padre = NULL;
                $c0->codcategoria = '7';
                $c0->estado = TRUE;
                $c0->corregir();
            }
        }

        foreach ($agentes as $agente){
            $c0 = $this->cargo->get_by_descripcion(strtoupper(trim($agente->cargo)));
            if(is_null($agente->codcargo) AND ($c0)){
                $agente->codcargo = $c0->codcargo;
                $agente->corregir();
            }
        }
    }

    public function share_extensions(){
        $extensiones = array(
            array(
                'name' => 'nuevo_agente_js',
                'page_from' => __CLASS__,
                'page_to' => 'admin_agente',
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/nomina.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => 'nuevo_agente_css',
                'page_from' => __CLASS__,
                'page_to' => 'admin_agente',
                'type' => 'head',
                'text' => '<link rel="stylesheet" type="text/css" media="screen" href="'.FS_PATH.'plugins/nomina/view/css/nomina.css"/>',
                'params' => ''
            ),
            array(
                'name' => 'movimientos_empleado',
                'page_from' => 'admin_agente',
                'page_to' => 'admin_agente',
                'type' => 'tab',
                'text' => '<span class="fa fa-code-fork" aria-hidden="true"></span> &nbsp; Movimientos',
                'params' => '&type=movimientos'
            ),
            array(
                'name' => 'contratos_empleado',
                'page_from' => 'admin_agente',
                'page_to' => 'admin_agente',
                'type' => 'tab',
                'text' => '<span class="fa fa-archive" aria-hidden="true"></span> &nbsp; Contratos',
                'params' => '&type=contratos'
            ),
            array(
                'name' => 'ausencias_empleado',
                'page_from' => 'admin_agente',
                'page_to' => 'admin_agente',
                'type' => 'tab',
                'text' => '<span class="fa fa-calendar-minus-o" aria-hidden="true"></span> &nbsp; Ausencias',
                'params' => '&type=ausencias'
            ),
            array(
                'name' => 'carga_familiar_empleado',
                'page_from' => 'admin_agente',
                'page_to' => 'admin_agente',
                'type' => 'tab',
                'text' => '<span class="fa fa-group" aria-hidden="true"></span> &nbsp; Carga Familiar',
                'params' => '&type=carga_familiar'
            ),
            array(
                'name' => 'hoja_vida_empleado',
                'page_from' => 'admin_agente',
                'page_to' => 'admin_agente',
                'type' => 'tab',
                'text' => '<span class="fa fa-suitcase" aria-hidden="true"></span> &nbsp; Hoja de Vida',
                'params' => '&type=hoja_vida'
            ),
            array(
                'name' => 'pagos_incentivos_empleado',
                'page_from' => 'admin_agente',
                'page_to' => 'admin_agente',
                'type' => 'tab',
                'text' => '<span class="fa fa-money" aria-hidden="true"></span> &nbsp; Pagos e Incentivos',
                'params' => '&type=pagos_incentivos'
            ),
            array(
                'name' => 'control_horas_empleado',
                'page_from' => 'admin_agente',
                'page_to' => 'admin_agente',
                'type' => 'tab',
                'text' => '<span class="fa fa-clock-o" aria-hidden="true"></span> &nbsp; Control de Horas',
                'params' => '&type=control_horas'
            ),
            //Tabs de Configuracion
            array(
                'name' => 'config_nomina_cargos',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'tab',
                'text' => '<span class="fa fa-gear" aria-hidden="true"></span> &nbsp; Cargos',
                'params' => '&type=cargos'
            ),
            array(
                'name' => 'config_nomina_categorias',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'tab',
                'text' => '<span class="fa fa-gear" aria-hidden="true"></span> &nbsp; Categorias',
                'params' => '&type=categorias'
            ),
            array(
                'name' => 'config_nomina_dependientes',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'tab',
                'text' => '<span class="fa fa-gear" aria-hidden="true"></span> &nbsp; Dependientes',
                'params' => '&type=dependientes'
            ),
            array(
                'name' => 'config_nomina_formacion',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'tab',
                'text' => '<span class="fa fa-gear" aria-hidden="true"></span> &nbsp; Formacion',
                'params' => '&type=formacion'
            ),
            array(
                'name' => 'config_nomina_organizacion',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'tab',
                'text' => '<span class="fa fa-gear" aria-hidden="true"></span> &nbsp; Estructura Org.',
                'params' => '&type=organizacion'
            ),
            array(
                'name' => 'config_nomina_seguridadsocial',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'tab',
                'text' => '<span class="fa fa-gear" aria-hidden="true"></span> &nbsp; Seguridad Social',
                'params' => '&type=seguridadsocial'
            ),
            array(
                'name' => 'config_nomina_sistemapension',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'tab',
                'text' => '<span class="fa fa-gear" aria-hidden="true"></span> &nbsp; Sistema de Pensión',
                'params' => '&type=sistemapension'
            ),
            array(
                'name' => 'config_nomina_sindicalizacion',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'tab',
                'text' => '<span class="fa fa-gear" aria-hidden="true"></span> &nbsp; Sindicalizacion',
                'params' => '&type=sindicalizacion'
            ),
            array(
                'name' => 'config_nomina_tipoempleado',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'tab',
                'text' => '<span class="fa fa-gear" aria-hidden="true"></span> &nbsp; Tipo de Contratos',
                'params' => '&type=tipoempleado'
            ),
            array(
                'name' => 'config_nomina_generaciones',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'tab',
                'text' => '<span class="fa fa-gear" aria-hidden="true"></span> &nbsp; Generaciones',
                'params' => '&type=generaciones'
            ),
            array(
                'name' => 'config_nomina_movimientos',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'tab',
                'text' => '<span class="fa fa-gear" aria-hidden="true"></span> &nbsp; Tipo de Movimientos',
                'params' => '&type=movimientos'
            ),
            array(
                'name' => 'config_nomina_ausencias',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'tab',
                'text' => '<span class="fa fa-gear" aria-hidden="true"></span> &nbsp; Tipo de Ausencias',
                'params' => '&type=ausencias'
            ),            
            array(
                'name' => 'config_nomina_estadocivil',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'tab',
                'text' => '<span class="fa fa-gear" aria-hidden="true"></span> &nbsp; Estados Civiles',
                'params' => '&type=estadocivil'
            ),            
            array(
                'name' => 'config_nomina_pagos',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'tab',
                'text' => '<span class="fa fa-gear" aria-hidden="true"></span> &nbsp; Tipos de Pago',
                'params' => '&type=pagos'
            ),            
            array(
                'name' => 'config_nomina_motivocese',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'tab',
                'text' => '<span class="fa fa-gear" aria-hidden="true"></span> &nbsp; Motivos de Cese',
                'params' => '&type=motivocese'
            ),            
            array(
                'name' => 'config_nomina_tipocese',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'tab',
                'text' => '<span class="fa fa-gear" aria-hidden="true"></span> &nbsp; Tipo de Ceses',
                'params' => '&type=tipocese'
            ),            
            array(
                'name' => 'configurar_nomina_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/nomina.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => 'configurar_nomina_css',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<link rel="stylesheet" type="text/css" media="screen" href="'.FS_PATH.'plugins/nomina/view/css/nomina.css"/>',
                'params' => ''
            ),
            array(
                'name' => 'treeview_nomina_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/bootstrap-treeview.min.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => 'treeview_nomina_css',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<link rel="stylesheet" type="text/css" media="screen" href="'.FS_PATH.'plugins/nomina/view/css/bootstrap-treeview.min.css"/>',
                'params' => ''
            ),
            array(
                'name' => 'pace_loader_nomina_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/pace.min.js" type="text/javascript"></script>',
                'params' => ''
            ),
        );

        foreach ($extensiones as $ext) {
            $fsext0 = new fs_extension($ext);
            if (!$fsext0->save()) {
                $this->new_error_msg('Imposible guardar los datos de la extensión ' . $ext['name'] . '.');
            }
        }
    }
}
