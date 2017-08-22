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
require_model('organizacion.php');
/**
 * Description of nomina_empleado
 *
 * @author Joe Nilson <joenilson at gmail.com>
 */
class nomina_empleado extends fs_controller{
    public $agente;
    public $agentes;
    public $organizacion;
    public $resultados;
    public $noimagen;
    public function __construct() {
        parent::__construct(__CLASS__, 'Mi Puesto', 'nomina', FALSE, TRUE, TRUE);
    }
    
    protected function private_core() {
        $this->noimagen = FS_PATH."plugins/nomina/view/imagenes/no_foto.jpg";
        $this->agentes = new agente();
        $this->agente = $this->agentes->get($this->user->codagente);
        $this->share_extensions();
    }
    
    public function share_extensions(){
        $extensiones = array(
            array(
                'name' => 'nomina_empleado_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/nomina.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => 'nomina_empleado_css',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<link rel="stylesheet" type="text/css" media="screen" href="'.FS_PATH.'plugins/nomina/view/css/nomina.min.css"/>',
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
