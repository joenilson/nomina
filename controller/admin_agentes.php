<?php

/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2014-2016  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
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
require_model('cargos.php');
require_model('organizacion.php');
require_model('tipoempleado.php');
require_model('categoriaempleado.php');

require_once 'helper_nomina.php';
require_once('plugins/nomina/extras/PHPExcel/PHPExcel/IOFactory.php');
require_once 'plugins/nomina/extras/verot/class.upload.php';

class admin_agentes extends fs_controller {

    public $agente;
    public $categoria;
    public $tipo;
    public $ciudad;
    public $offset;
    public $orden;
    public $provincia;
    public $resultados;
    public $total_resultados;
    public $archivo;
    public $resultado;
    public $almacen;
    public $codalmacen;
    public $almacenes;
    public $cargos;
    public $organizacion;
    public $tipoempleado;
    public $categoriaempleado;
    public $foto_empleado;
    public $noimagen;
    private $upload_photo;
    private $dir_empleados;

    public function __construct() {
        parent::__construct(__CLASS__, 'Empleados', 'nomina', FALSE, TRUE);
    }

    protected function private_core() {
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
        $this->dir_empleados = FS_PATH.FS_MYDOCS."documentos/nomina/".$this->empresa->id."/e/";
        $this->noimagen = FS_PATH."plugins/nomina/view/imagenes/no_foto.jpg";
        $this->share_extensions();
        $this->agente = new agente();
        $this->almacen = new almacen();
        $this->cargos = new cargos();
        $this->organizacion = new organizacion();
        $this->tipoempleado = new tipoempleado();
        $this->categoriaempleado = new categoriaempleado();
        $this->cache->delete('m_agente_all');

        if (isset($_GET['type'])) {
            $type = filter_input(INPUT_GET, 'type');
            switch ($type) {
                case "organizacion";
                    $this->template = false;
                    $helper = new helper_nomina();
                    $helper->buscar_organizacion();
                    break;
                case "nuevo":
                    $this->agente = new agente();
                    $this->template = 'contenido/nuevo_agente';
                    break;
                default:
                    break;
            }
        }

        if (isset($_POST['nuevo']) AND $_POST['nuevo'] == 1) {
            if ($_POST['codcargo'] != '') {
                $cargo = $this->cargos->get($_POST['codcargo']);
            }
            $age0 = new agente();
            $age0->codalmacen = $_POST['codalmacen'];
            $age0->idempresa = $this->empresa->id;
            $age0->codagente = $age0->get_new_codigo();
            $age0->nombre = $this->mayusculas($_POST['nombre']);
            $age0->apellidos = $this->mayusculas($_POST['apellidos']);
            $age0->segundo_apellido = $this->mayusculas($_POST['segundo_apellido']);
            $age0->dnicif = $_POST['dnicif'];
            $age0->telefono = $_POST['telefono'];
            $age0->email = $this->minusculas($_POST['email']);
            $age0->provincia = $_POST['provincia'];
            $age0->ciudad = $this->mayusculas($_POST['ciudad']);
            $age0->direccion = $this->mayusculas($_POST['direccion']);
            $age0->sexo = $_POST['sexo'];
            $age0->f_nacimiento = $_POST['f_nacimiento'];
            $age0->f_alta = (!empty($_POST['f_alta'])) ? $_POST['f_alta'] : NULL;
            $age0->f_baja = (!empty($_POST['f_baja'])) ? $_POST['f_baja'] : NULL;
            $age0->codtipo = $_POST['codtipo'];
            $age0->codsupervisor = $_POST['codsupervisor'];
            $age0->codgerencia = $_POST['codgerencia'];
            $age0->codcargo = $_POST['codcargo'];
            $age0->cargo = $cargo->descripcion;
            $age0->codarea = $_POST['codarea'];
            $age0->coddepartamento = $_POST['coddepartamento'];
            $age0->codformacion = $_POST['codformacion'];
            $age0->carrera = $this->mayusculas($_POST['carrera']);
            $age0->centroestudios = $this->mayusculas($_POST['centroestudios']);
            $age0->codseguridadsocial = $_POST['codseguridadsocial'];
            $age0->seg_social = $_POST['seg_social'];
            $age0->codbanco = $_POST['codbanco'];
            $age0->cuenta_banco = $_POST['cuenta_banco'];
            $age0->porcomision = floatval($_POST['porcomision']);
            $age0->dependientes = $_POST['dependientes'];
            $age0->idsindicato = $_POST['idsindicalizado'];
            $age0->estado = $_POST['estado'];
            $age0->estado_civil = $_POST['estado_civil'];
            $age0->fecha_creacion = date('d-m-y H:m:s');
            $age0->usuario_creacion = $this->user->nick;
            if ($age0->save()) {
                $this->upload_photo = new Upload($_FILES['foto']);
                if ($this->upload_photo->uploaded) {
                    $this->guardar_foto();
                }
                $this->new_message("Empleado " . $age0->codagente . " guardado correctamente.");
                header('location: ' . $age0->url());
            } else
                $this->new_error_msg("¡Imposible guardar el empleado!");
        }
        else if (isset($_GET['delete'])) {
            $age0 = $this->agente->get($_GET['delete']);
            if ($age0) {
                if (FS_DEMO) {
                    $this->new_error_msg('En el modo <b>demo</b> no se pueden eliminar empleados. Otro usuario podría estar usándolo.');
                } else if ($age0->delete()) {
                    $this->new_message("Empleado " . $age0->codagente . " eliminado correctamente.");
                } else
                    $this->new_error_msg("¡Imposible eliminar el empleado!");
            } else
                $this->new_error_msg("¡Empleado no encontrado!");
        }
        elseif (isset($_POST['importar'])) {
            $this->archivo = $_FILES['empleados'];
            $this->importar_empleados();
        }

        $this->offset = 0;
        if (isset($_GET['offset'])) {
            $this->offset = intval($_GET['offset']);
        }

        $this->ciudad = '';
        if (isset($_REQUEST['ciudad'])) {
            $this->ciudad = $_REQUEST['ciudad'];
        }

        $this->provincia = '';
        if (isset($_REQUEST['provincia'])) {
            $this->provincia = $_REQUEST['provincia'];
        }

        $this->codalmacen = '';
        if (isset($_REQUEST['almacen'])) {
            $this->codalmacen = $_REQUEST['almacen'];
        }

        $this->tipo = '';
        if (isset($_REQUEST['codtipo'])) {
            $this->tipo = $_REQUEST['codtipo'];
        }

        $this->categoria = '';
        if (isset($_REQUEST['codcategoria'])) {
            $this->categoria = $_REQUEST['codcategoria'];
        }

        $this->orden = 'nombre ASC';
        if (isset($_REQUEST['orden'])) {
            $this->orden = $_REQUEST['orden'];
        }

        $this->buscar();
    }

    public function paginas() {
        $url = $this->url() . "&query=" . $this->query
                . "&codcategoria=" . $this->categoria
                . "&codtipo=" . $this->tipo
                . "&codalmacen=" . $this->codalmacen
                . "&ciudad=" . $this->ciudad
                . "&provincia=" . $this->provincia
                . "&offset=" . ($this->offset + FS_ITEM_LIMIT)
                . "&orden=" . $this->orden;

        $paginas = array();
        $i = 0;
        $num = 0;
        $actual = 1;

        /// añadimos todas la página
        while ($num < $this->total_resultados) {
            $paginas[$i] = array(
                'url' => $url . "&offset=" . ($i * FS_ITEM_LIMIT),
                'num' => $i + 1,
                'actual' => ($num == $this->offset)
            );

            if ($num == $this->offset) {
                $actual = $i;
            }

            $i++;
            $num += FS_ITEM_LIMIT;
        }

        /// ahora descartamos
        foreach ($paginas as $j => $value) {
            $enmedio = intval($i / 2);

            /**
             * descartamos todo excepto la primera, la última, la de enmedio,
             * la actual, las 5 anteriores y las 5 siguientes
             */
            if (($j > 1 AND $j < $actual - 5 AND $j != $enmedio) OR ( $j > $actual + 5 AND $j < $i - 1 AND $j != $enmedio)) {
                unset($paginas[$j]);
            }
        }

        if (count($paginas) > 1) {
            return $paginas;
        } else {
            return array();
        }
    }

    public function ciudades() {
        $final = array();

        if ($this->db->table_exists('agentes')) {
            $ciudades = array();
            $sql = "SELECT DISTINCT ciudad FROM agentes ORDER BY ciudad ASC;";
            if ($this->provincia != '') {
                $sql = "SELECT DISTINCT ciudad FROM agentes WHERE lower(provincia) = "
                        . $this->agente->var2str($this->provincia) . " ORDER BY ciudad ASC;";
            }

            $data = $this->db->select($sql);
            if ($data) {
                foreach ($data as $d) {
                    $ciudades[] = $d['ciudad'];
                }
            }

            /// usamos las minúsculas para filtrar
            foreach ($ciudades as $ciu) {
                if ($ciu != '') {
                    $final[mb_strtolower($ciu, 'UTF8')] = $ciu;
                }
            }
        }

        return $final;
    }

    public function provincias() {
        $final = array();

        if ($this->db->table_exists('agentes')) {
            $provincias = array();
            $sql = "SELECT DISTINCT provincia FROM agentes ORDER BY provincia ASC;";

            $data = $this->db->select($sql);
            if ($data) {
                foreach ($data as $d) {
                    $provincias[] = $d['provincia'];
                }
            }

            foreach ($provincias as $pro) {
                if ($pro != '') {
                    $final[mb_strtolower($pro, 'UTF8')] = $pro;
                }
            }
        }

        return $final;
    }

    private function buscar() {
        $this->total_resultados = 0;
        $query = mb_strtolower($this->agente->no_html($this->query), 'UTF8');
        $sql = " FROM agentes, hr_cargos ";
        $and = ' WHERE ';

        if (is_numeric($query)) {
            $sql .= $and . "(codagente LIKE '%" . $query . "%'"
                    . " OR dnicif LIKE '%" . $query . "%'"
                    . " OR telefono LIKE '" . $query . "%')";
            $and = ' AND ';
        } else {
            $buscar = str_replace(' ', '%', $query);
            $sql .= $and . "(lower(nombre) LIKE '%" . $buscar . "%'"
                    . " OR lower(apellidos) LIKE '%" . $buscar . "%'"
                    . " OR lower(dnicif) LIKE '%" . $buscar . "%'"
                    . " OR lower(email) LIKE '%" . $buscar . "%')";
            $and = ' AND ';
        }

        if ($this->ciudad != '') {
            $sql .= $and . "lower(ciudad) = " . $this->agente->var2str(mb_strtolower($this->ciudad, 'UTF8'));
            $and = ' AND ';
        }

        if ($this->provincia != '') {
            $sql .= $and . "lower(provincia) = " . $this->agente->var2str(mb_strtolower($this->provincia, 'UTF8'));
            $and = ' AND ';
        }

        if ($this->codalmacen != '') {
            $sql .= $and . "codalmacen = " . $this->agente->var2str($this->codalmacen);
            $and = ' AND ';
        }

        if ($this->categoria != '') {
            $sql .= $and . "hr_cargos.codcargo = agentes.codcargo AND hr_cargos.codcategoria = " . $this->agente->var2str($this->categoria);
            $and = ' AND ';
        } else {
            $sql .= $and . "hr_cargos.codcargo = agentes.codcargo ";
            $and = ' AND ';
        }

        if ($this->tipo != '') {
            $sql .= $and . "codtipo = " . $this->agente->var2str($this->tipo);
            $and = ' AND ';
        }

        $data = $this->db->select("SELECT COUNT(codagente) as total" . $sql . ';');
        if ($data) {
            $this->total_resultados = intval($data[0]['total']);

            $data2 = $this->db->select_limit("SELECT *" . $sql . " ORDER BY " . $this->orden, FS_ITEM_LIMIT, $this->offset);
            if ($data2) {
                foreach ($data2 as $d) {
                    $valor = new agente($d);
                    $res = $this->agente->info_adicional($valor);
                    $this->resultados[] = $res;
                }
            }
        }
    }

    //Para guardar la foto hacemos uso de la libreria de class.upload.php que esta en extras/verot/
    //Con esta libreria estandarizamos todas las imagenes en PNG y les hacemos un resize a 120px
    public function guardar_foto() {
        $this->foto_empleado = $this->agente->get_foto();
        if (file_exists($this->foto_empleado)) {
            unlink($this->foto_empleado);
        }
        $newname = str_pad($this->agente->codagente, 6, 0, STR_PAD_LEFT);

        // Grabar la imagen con un nuevo nombre y con un resize de 120px
        $this->upload_photo->file_new_name_body = $newname;
        $this->upload_photo->image_resize = true;
        $this->upload_photo->image_convert = 'png';
        $this->upload_photo->image_x = 120;
        $this->upload_photo->file_overwrite = true;
        $this->upload_photo->image_ratio_y = true;
        $this->upload_photo->Process($this->dir_empleados);
        if ($this->upload_photo->processed) {
            $this->upload_photo->clean();
            $this->agente->set_foto($newname . ".png");
        } else {
            $this->new_error_msg('error : ' . $$this->upload_photo->error);
        }
    }

    private function mayusculas($string) {
        return strtoupper(trim(strip_tags(stripslashes($string))));
    }

    private function minusculas($string) {
        return strtolower(trim(strip_tags(stripslashes($string))));
    }

    private function share_extensions() {
        $extensiones = array(
            array(
                'name' => 'nomina_jgrid_css',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<link rel="stylesheet" type="text/css" media="screen" href="'.FS_PATH.'plugins/nomina/view/css/ui.jqgrid-bootstrap.css"/>',
                'params' => ''
            ),
            array(
                'name' => 'cargar_empleados_button',
                'page_from' => __CLASS__,
                'page_to' => 'importar_agentes',
                'type' => 'button',
                'text' => '<span class="fa fa-upload" aria-hidden="true"></span> &nbsp; Cargar Empleados',
                'params' => ''
            ),
            array(
                'name' => 'nuevo_empleado_js',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<script src="'.FS_PATH.'plugins/nomina/view/js/nomina.js" type="text/javascript"></script>',
                'params' => ''
            ),
            array(
                'name' => 'nuevo_empleado_css',
                'page_from' => __CLASS__,
                'page_to' => __CLASS__,
                'type' => 'head',
                'text' => '<link rel="stylesheet" type="text/css" media="screen" href="'.FS_PATH.'plugins/nomina/view/css/nomina.css"/>',
                'params' => ''
            ),
        );
        foreach($extensiones as $ext){
            $fsext0 = new fs_extension($ext);
            if (!$fsext0->save()) {
                $this->new_error_msg('Imposible guardar los datos de la extensión ' . $ext['name'] . '.');
            }
        }
        
    }

}
