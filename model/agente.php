<?php

/*
 * This file is part of FacturaScripts
 * Copyright (C) 2013-2016  Carlos Garcia Gomez  neorazorx@gmail.com
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
require_model('almacen.php');
require_model('cargos.php');
require_model('organizacion.php');
require_model('tipoempleado.php');
require_model('categoriaempleado.php');

/**
 * El agente/empleado es el que se asocia a un albarán, factura o caja.
 * Cada usuario puede estar asociado a un agente, y un agente puede
 * estar asociado a varios usuarios.
 */
class agente extends fs_model {

    /**
     * Clave primaria. Varchar (10).
     * @var integer
     */
    public $codagente;

    /**
     * Identificador fiscal (CIF/NIF).
     * @var string
     */
    public $dnicif;

    /**
     *
     * @var string
     */
    public $nombre;

    /**
     *
     * @var string
     */
    public $apellidos;

    /**
     *
     * @var string
     */
    public $segundo_apellido;

    /**
     *
     * @var string
     */
    public $email;

    /**
     *
     * @var string
     */
    public $foto;

    /**
     *
     * @var string
     */
    public $fax;

    /**
     *
     * @var string
     */
    public $telefono;

    /**
     *
     * @var string
     */
    public $codpostal;

    /**
     *
     * @var string
     */
    public $codpais;

    /**
     *
     * @var string
     */
    public $provincia;

    /**
     *
     * @var string
     */
    public $ciudad;

    /**
     *
     * @var string
     */
    public $direccion;

    /**
     *
     * @var string
     */
    public $estado_civil;

    /**
     * Variable que guarda los valores de Nombres, Apellidos
     * @var string $nombreap Nombrecompleto
     */
    public $nombreap;

    /**
     * Se coloca el número o código de seguridad social del empleado
     * @var string $seg_social
     */
    public $seg_social;

    /**
     * Se coloca el número o código de fondo de pensiones del empleado
     * @var string $codigo_pension
     */
    public $codigo_pension;

    /**
     * @var string
     */
    public $cargo;

    /**
     * @var string
     */
    public $banco;

    /**
     *
     * @var string Date
     */
    public $f_nacimiento;

    /**
     *
     * @var string Date
     */
    public $f_alta;

    /**
     *
     * @var string
     */
    public $f_baja;

    /**
     * Porcentaje de comisión del agente. Se utiliza en presupuestos, pedidos, albaranes y facturas.
     * @var float
     */
    public $porcomision;

    /**
     * Todavía sin uso.
     * @var float
     */
    public $irpf;

    /**
     * Codigo del almacén al que pertenece
     * @var string $codalmacen Almacen
     */
    public $codalmacen;

    /**
     * ID de la empresa, esto para prerarar cuando se active multiempresa
     * @var integer $idempresa Empresa
     */
    public $idempresa;

    /**
     * Tipo de empleado que es Por contrato, Fijo, Temporal, etc
     * @var string $codtipo TipoEmpleado
     */
    public $codtipo;

    /**
     * Sexo del empleado o la empleada
     * @var string $sexo varchar(1)
     */
    public $sexo;

    /**
     * Aqui se elige la gerencia a la que pertenece el empleado Administrativa, Comercial, etc
     * @var string $codgerencia Gerencias
     */
    public $codgerencia;

    /**
     * A que area pertenece el empleado
     * @var string $codarea AreaEmpresa
     */
    public $codarea;

    /**
     * A que departamento dentro del area si es que es así de complejo
     * Ahora si ya esta en uso
     * @var string $coddepartamento varchar(6)
     */
    public $coddepartamento;

    /**
     * Se reemplaza la variable cargo con esta nueva variable que enlaza a una tabla de Cargos
     * @var string $codcargo Cargos
     */
    public $codcargo;

    /**
     * Se configura el superior inmediato del empleado para poder armar un Organigrama
     * @var string $coduspervisor Agente
     */
    public $codsupervisor;

    /**
     * Se selecciona si el empleado esta afiliado a un sindicato
     * @var string $idsincato Sindicato
     */
    public $idsindicato;

    /**
     * Se elige la compañia que maneja el seguro social o el estado de ser así
     * @var string $codseguridadsocial Seguridadsocial
     */
    public $codseguridadsocial;

    /**
     * Se elige la compañia que maneja el fondo de pensiones o el estado o gobierno
     * @var string $codsistemapension SistemaPension
     */
    public $codsistemapension;

    /**
     * Se coloca la cantidad de dependientes para analisis posterior de carga familiar
     * @var integer $dependientes Integer
     */
    public $dependientes;

    /**
     * Se elige el tipo de formación del empleado Básica, Técnica, Universitaria, etc
     * @var string $codformacion Formacion
     */
    public $codformacion;

    /**
     * Se escribe la carrera que estudió, texto libre varchar(150)
     * @var string $carrera Descripcion
     */
    public $carrera;

    /**
     * Se elige un centro de estudios, se mostrará como opciones valores antes ingresados
     * @var string $centroestudios CentroEstudios
     */
    public $centroestudios;

    /**
     * Se elige de entre los bancos que tenemos configurados en el sistema
     * @var string $codbanco Bancos
     */
    public $codbanco;

    /**
     * Esta cuenta reemplaza a la cuenta banco por si se trae de otro sistema
     * @var string $cuenta_banco;
     */
    public $cuenta_banco;

    /**
     * Si el tipo de empleado es temporal o por contrato
     * se debe indicar el tiempo en meses de duración del contrato
     * @var integer $tiempo_contrato Integer
     */
    public $tiempo_contrato;

    /**
     * Aqui se consigna si el empleado esta:
     * A = Activo
     * V = Vacaciones
     * I = Inactivo
     * S = Suspendido
     * C = Cesado o Jubilado
     * P = Permiso médico
     * L = Litigio
     * @var string $estado Agente
     */
    public $estado;

    /**
     * @var float double precision $pago_total
     */
    public $pago_total;

    /**
     * @var float double precision $pago_neto
     */
    public $pago_neto;

    /**
     * Usuario que crea al empleado, para efectos de auditoria
     * @var string $usuario_creacion
     */
    public $usuario_creacion;

    /**
     * Fecha de ingreso de la ficha del empleado
     * @var string $fecha_creacion
     */
    public $fecha_creacion;

    /**
     * Usuario que modifica el registro
     * @var string $usuario_modificacion User->nick
     */
    public $usuario_modificacion;

    /**
     * Fecha en que modifican el registro
     * @var string $fecha_modificacion
     */
    public $fecha_modificacion;

    /**
     * Tipo de cuenta de banco si el pago es por banco
     * @var string varchar(4)
     */
    public $tipo_cuenta;
    /*
     * Campos auxliares externos
     */
    public $almacen;
    public $cargos;
    public $tipoempleado;
    public $organizacion;
    public $codcategoria;
    public $categoriaempleado;

    public function __construct($a = FALSE) {
        parent::__construct('agentes');
        if ($a) {
            $this->codalmacen = $a['codalmacen'];
            $this->idempresa = $a['idempresa'];
            $this->codagente = $a['codagente'];
            $this->nombre = $a['nombre'];
            $this->apellidos = $a['apellidos'];
            $this->segundo_apellido = $a['segundo_apellido'];
            $this->nombreap = $a['apellidos'] . " " . $a['segundo_apellido'] . ", " . $a['nombre'];
            $this->dnicif = $a['dnicif'];
            $this->coddepartamento = $a['coddepartamento'];
            $this->email = $a['email'];
            $this->foto = $a['foto'];
            $this->fax = $a['fax'];
            $this->telefono = $a['telefono'];
            $this->codpostal = $a['codpostal'];
            $this->codpais = $a['codpais'];
            $this->provincia = $a['provincia'];
            $this->ciudad = $a['ciudad'];
            $this->direccion = $a['direccion'];
            $this->porcomision = floatval($a['porcomision']);
            $this->pago_total = floatval($a['pago_total']);
            $this->pago_neto = floatval($a['pago_neto']);
            $this->irpf = floatval($a['irpf']);
            $this->seg_social = $a['seg_social'];
            $this->codigo_pension = $a['codigo_pension'];
            $this->banco = $a['banco'];
            $this->cargo = $a['cargo'];
            $this->codtipo = $a['codtipo'];
            $this->codgerencia = $a['codgerencia'];
            $this->codarea = $a['codarea'];
            $this->codcargo = $a['codcargo'];
            $this->codsupervisor = $a['codsupervisor'];
            $this->codseguridadsocial = $a['codseguridadsocial'];
            $this->codsistemapension = $a['codsistemapension'];
            $this->idsindicato = $a['idsindicato'];
            $this->dependientes = $a['dependientes'];
            $this->codformacion = $a['codformacion'];
            $this->carrera = $a['carrera'];
            $this->centroestudios = $a['centroestudios'];
            $this->codbanco = $a['codbanco'];
            $this->cuenta_banco = $a['cuenta_banco'];
            $this->tipo_cuenta = $a['tipo_cuenta'];
            $this->estado = $a['estado'];
            $this->estado_civil = $a['estado_civil'];

            $this->f_alta = NULL;
            if ($a['f_alta'] != '') {
                $this->f_alta = Date('d-m-Y', strtotime($a['f_alta']));
            }

            $this->f_baja = NULL;
            if ($a['f_baja'] != '') {
                $this->f_baja = Date('d-m-Y', strtotime($a['f_baja']));
            }

            $this->f_nacimiento = NULL;
            if ($a['f_nacimiento'] != '') {
                $this->f_nacimiento = Date('d-m-Y', strtotime($a['f_nacimiento']));
            }

            $this->sexo = $a['sexo'];

            $this->fecha_creacion = NULL;
            if ($a['fecha_creacion'] != '') {
                $this->fecha_creacion = Date('d-m-Y H:i:s', strtotime($a['fecha_creacion']));
            }
            $this->usuario_creacion = $a['usuario_creacion'];

            $this->fecha_modificacion = NULL;
            if ($a['fecha_modificacion'] != '') {
                $this->fecha_modificacion = Date('d-m-Y H:i:s', strtotime($a['fecha_modificacion']));
            }

            $this->usuario_modificacion = $a['usuario_modificacion'];
        } else {
            $this->codalmacen = NULL;
            $this->idempresa = NULL;
            $this->codagente = NULL;
            $this->nombre = '';
            $this->apellidos = '';
            $this->segundo_apellido = '';
            $this->nombreap = '';
            $this->dnicif = '';
            $this->coddepartamento = NULL;
            $this->email = NULL;
            $this->foto = NULL;
            $this->fax = NULL;
            $this->telefono = NULL;
            $this->codpostal = NULL;
            $this->codpais = NULL;
            $this->provincia = NULL;
            $this->ciudad = NULL;
            $this->direccion = NULL;
            $this->porcomision = floatval(0);
            $this->pago_total = floatval(0);
            $this->pago_neto = floatval(0);
            $this->irpf = floatval(0);
            $this->seg_social = NULL;
            $this->codigo_pension = NULL;
            $this->banco = NULL;
            $this->cargo = NULL;
            $this->f_alta = Date('d-m-Y');
            $this->f_baja = NULL;
            $this->f_nacimiento = Date('d-m-Y');
            $this->sexo = NULL;
            $this->codtipo = NULL;
            $this->codgerencia = NULL;
            $this->codarea = NULL;
            $this->codcargo = NULL;
            $this->codsupervisor = NULL;
            $this->codseguridadsocial = NULL;
            $this->codsistemapension = NULL;
            $this->idsindicato = NULL;
            $this->dependientes = 0;
            $this->codformacion = NULL;
            $this->carrera = NULL;
            $this->centroestudios = NULL;
            $this->codbanco = NULL;
            $this->cuenta_banco = NULL;
            $this->tipo_cuenta = NULL;
            $this->estado = NULL;
            $this->estado_civil = NULL;
            $this->fecha_creacion = Date('d-m-Y H:i:s');
            $this->usuario_creacion = NULL;
            $this->fecha_modificacion = NULL;
            $this->usuario_modificacion = NULL;
        }
    }

    protected function install() {
        $this->clean_cache();
        return "INSERT INTO " . $this->table_name . " (codagente,nombre,apellidos,segundo_apellido,dnicif,sexo,estado,estado_civil)
         VALUES ('1','Juan','Perez','Prado','00000014Z','M','A','S')," .
                "('1','Maria','Ruiz','Diaz','00000019Z','F','A','D');";
    }

    public function info_adicional($res) {
        $this->almacen = new almacen();
        $this->cargos = new cargos();
        $this->organizacion = new organizacion();
        $this->tipoempleado = new tipoempleado();
        $this->categoriaempleado = new categoriaempleado();
        $res->gerencia = (!empty($res->codgerencia)) ? $this->organizacion->get($res->codgerencia)->descripcion : "";
        $res->area = (!empty($res->codarea)) ? $this->organizacion->get($res->codarea)->descripcion : "";
        $res->departamento = ($res->coddepartamento != '') ? $this->organizacion->get($res->coddepartamento)->descripcion : '';
        $res->almacen_nombre = (!empty($res->codalmacen)) ? $this->almacen->get($res->codalmacen)->nombre : "";
        $res->tipo = (!empty($res->codtipo)) ? $this->tipoempleado->get($res->codtipo)->descripcion : "";
        $res->codcategoria = '';
        $res->categoria = '';
        $res->nombre_supervisor = '';
        if ($res->codcargo) {
            $info_cargos = $this->cargos->get($res->codcargo);
            $info_categoria = ($info_cargos->codcategoria) ? $this->categoriaempleado->get($info_cargos->codcategoria) : false;
            $res->codcategoria = $info_cargos->codcategoria;
            $res->categoria = ($info_categoria) ? $info_categoria->descripcion : false;
        }

        if (isset($res->codsupervisor)) {
            $nombre = $this->supervisor($res->codsupervisor);
            if (!empty($nombre)) {
                $res->nombre_supervisor = $nombre->nombreap;
            }
        }
        $res->edad = $this->edad($res->f_nacimiento);
        $res->permanencia = $this->permanencia($res->f_alta, $res->f_baja);
        return $res;
    }

    public function edad($f_nac) {
        $from = new DateTime($f_nac);
        $to = new DateTime('today');
        $edad = $from->diff($to)->y;
        return $edad;
    }

    public function supervisor($codsupervisor) {
        $a = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE codagente = " . $this->var2str($codsupervisor) . ";");
        if ($a) {
            $valor = new agente($a[0]);
            return $valor;
        } else
            return FALSE;
    }

    public function permanencia($f_alta, $f_baja) {
        $f_fin = (!empty($f_baja)) ? $f_baja : 'today';
        $from = new DateTime($f_alta);
        $to = new DateTime($f_fin);
        $dateDiff = $from->diff($to);
        $years = $dateDiff->y;
        $months = $dateDiff->m;
        $days = $dateDiff->d;
        $permanencia = "";
        if ($years != 0) {
            $string = ($years > 1) ? " años " : " año ";
            $permanencia .= $years . $string;
        }

        if ($months != 0) {
            $string = ($months > 1) ? " meses " : " mes ";
            $permanencia .= $months . $string;
        }

        if ($days != 0) {
            $string = ($days > 1) ? " dias " : " dia ";
            $permanencia .= $days . $string;
        }

        return $permanencia;
    }

    public function get_fullname() {
        return $this->nombre . " " . $this->apellidos . " " . $this->segundo_apellido;
    }

    public function get_foto() {
        if ($this->foto) {
            return FS_PATH . FS_MYDOCS . 'documentos/nomina/' . $this->idempresa . '/e/' . $this->foto;
        } else {
            return FS_PATH . "plugins/nomina/view/imagenes/no_foto.jpg";
        }
    }

    public function set_foto($nombre_foto) {
        if ($nombre_foto) {
            $sql = "UPDATE " . $this->table_name . " SET FOTO = " . $this->var2str($nombre_foto) . " WHERE codagente = " . $this->var2str($this->codagente) . ";";
            return $this->db->exec($sql);
        } else {
            return false;
        }
    }

    public function search($value_orig) {
        $value = strtoupper(trim($value_orig));
        $select = "SELECT * FROM " . $this->table_name . " WHERE ";
        $where = " nombre LIKE '%" . $value . "%' " .
                " OR apellidos LIKE '%" . $value . "%' " .
                " OR segundo_apellido LIKE '%" . $value . "%' " .
                " OR codagente LIKE '%" . $value . "%' ";
        $order = " ORDER BY apellidos, segundo_apellido, nombre";
        $sql = $select . $where . $order;
        $data = $this->db->select($sql);
        if ($data) {
            $lista = array();
            foreach ($data as $d) {
                $item = new agente($d);
                $value = $this->info_adicional($item);
                $lista[] = $value;
            }
            return $lista;
        } else {
            return false;
        }
    }

    public function get_new_codigo() {
        $sql = "SELECT MAX(" . $this->db->sql_to_int('codagente') . ") as cod FROM " . $this->table_name . ";";
        $cod = $this->db->select($sql);
        if ($cod) {
            return 1 + intval($cod[0]['cod']);
        } else
            return 1;
    }

    /**
     * Devuelve la url donde se pueden ver/modificar estos datos
     * @return string
     */
    public function url() {
        if (is_null($this->codagente)) {
            return "index.php?page=admin_agentes";
        } else
            return "index.php?page=admin_agente&cod=" . $this->codagente;
    }

    /**
     * Devuelve el empleado/agente con codagente = $cod
     * @param type $cod
     * @return \agente|boolean
     */
    public function get($cod) {
        $a = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE codagente = " . $this->var2str($cod) . ";");
        if ($a) {
            $valor = new agente($a[0]);
            $res = $this->info_adicional($valor);
            return $res;
        } else
            return FALSE;
    }

    public function get_by_dnicif($dnicif) {
        $a = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE dnicif = " . $this->var2str($dnicif) . ";");
        if ($a) {
            $valor = new agente($a[0]);
            $res = $this->info_adicional($valor);
            return $res;
        } else
            return FALSE;
    }

    public function get_by_almacen($codalmacen) {
        $a = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE codalmacen = " . $this->var2str($codalmacen) . ";");
        if ($a) {
            $lista = array();
            foreach ($a as $d) {
                $item = new agente($d);
                $value = $this->info_adicional($item);
                $lista[] = $value;
            }
            return $lista;
        } else
            return FALSE;
    }

    public function get_by_gerencia($codgerencia) {
        $a = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE codgerencia = " . $this->var2str($codgerencia) . ";");
        if ($a) {
            $lista = array();
            foreach ($a as $d) {
                $item = new agente($d);
                $value = $this->info_adicional($item);
                $lista[] = $value;
            }
            return $lista;
        } else
            return FALSE;
    }

    public function get_by_area($codarea) {
        $a = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE codarea = " . $this->var2str($codarea) . ";");
        if ($a) {
            $lista = array();
            foreach ($a as $d) {
                $item = new agente($d);
                $value = $this->info_adicional($item);
                $lista[] = $value;
            }
            return $lista;
        } else
            return FALSE;
    }

    public function get_by_departamento($codepartamento) {
        $a = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE codepartamento = " . $this->var2str($codepartamento) . ";");
        if ($a) {
            $lista = array();
            foreach ($a as $d) {
                $item = new agente($d);
                $value = $this->info_adicional($item);
                $lista[] = $value;
            }
            return $lista;
        } else
            return FALSE;
    }

    public function get_by_supervisor($codsupervisor) {
        $a = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE codsupervisor = " . $this->var2str($codsupervisor) . ";");
        if ($a) {
            $lista = array();
            foreach ($a as $d) {
                $item = new agente($d);
                $value = $this->info_adicional($item);
                $lista[] = $value;
            }
            return $lista;
        } else
            return FALSE;
    }

    public function get_supervisor() {
        $arrayLista = array();
        $c = $this->cargos->get($this->codcargo);
        $cargos = $c->get_superiores();
        foreach ($cargos as $cargo) {
            $arrayLista[] = $cargo->codcargo;
        }
        $supervisor = ($this->codsupervisor) ? " codsupervisor = " . $this->var2str($this->codsupervisor) : " codcargo IN ('" . implode("','", $arrayLista) . "')";
        $sql = "SELECT * FROM " . $this->table_name . " WHERE " . $supervisor . ";";
        $a = $this->db->select($sql);
        if ($a) {
            $lista = array();
            foreach ($a as $d) {
                $item = new agente($d);
                $value = $this->info_adicional($item);
                $lista[] = $value;
            }
            return $lista;
        } else {
            return FALSE;
        }
    }

    public function exists() {
        if (is_null($this->codagente)) {
            return FALSE;
        } else
            return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE codagente = " . $this->var2str($this->codagente) . ";");
    }

    /**
     * Comprueba los datos del empleado/agente, devuelve TRUE si son correctos
     * @return boolean
     */
    public function test() {
        $status = FALSE;

        $this->codagente = trim($this->codagente);
        $this->nombre = $this->no_html($this->nombre);
        $this->apellidos = $this->no_html($this->apellidos);
        $this->dnicif = $this->no_html($this->dnicif);
        $this->telefono = $this->no_html($this->telefono);
        $this->email = $this->no_html($this->email);

        if (strlen($this->codagente) < 1 OR strlen($this->codagente) > 10) {
            $this->new_error_msg("Código de agente no válido. Debe tener entre 1 y 10 caracteres.");
        } else if (strlen($this->nombre) < 1 OR strlen($this->nombre) > 50) {
            $this->new_error_msg("El nombre de empleado no puede superar los 50 caracteres.");
        } else if (strlen($this->apellidos) < 1 OR strlen($this->apellidos) > 200) {
            $this->new_error_msg("Los apellidos del empleado no pueden superar los 200 caracteres.");
        } else
            $status = TRUE;

        return $status;
    }

    public function save() {
        if ($this->test()) {
            $this->clean_cache();

            if ($this->exists()) {
                $sql = "UPDATE " . $this->table_name . " SET nombre = " . $this->var2str($this->nombre) .
                        ", apellidos = " . $this->var2str($this->apellidos) .
                        ", segundo_apellido = " . $this->var2str($this->segundo_apellido) .
                        ", codalmacen = " . $this->var2str($this->codalmacen) .
                        ", idempresa = " . $this->intval($this->idempresa) .
                        ", dnicif = " . $this->var2str($this->dnicif) .
                        ", telefono = " . $this->var2str($this->telefono) .
                        ", email = " . $this->var2str($this->email) .
                        ", codcargo = " . $this->var2str($this->codcargo) .
                        ", cargo = " . $this->var2str($this->cargo) .
                        ", codsupervisor = " . $this->var2str($this->codsupervisor) .
                        ", codgerencia = " . $this->var2str($this->codgerencia) .
                        ", codtipo = " . $this->var2str($this->codtipo) .
                        ", codarea = " . $this->var2str($this->codarea) .
                        ", coddepartamento = " . $this->var2str($this->coddepartamento) .
                        ", provincia = " . $this->var2str($this->provincia) .
                        ", ciudad = " . $this->var2str($this->ciudad) .
                        ", direccion = " . $this->var2str($this->direccion) .
                        ", f_nacimiento = " . $this->var2str($this->f_nacimiento) .
                        ", f_alta = " . $this->var2str($this->f_alta) .
                        ", f_baja = " . $this->var2str($this->f_baja) .
                        ", sexo = " . $this->var2str($this->sexo) .
                        ", idsindicato = " . $this->var2str($this->idsindicato) .
                        ", codseguridadsocial = " . $this->var2str($this->codseguridadsocial) .
                        ", codsistemapension = " . $this->var2str($this->codsistemapension) .
                        ", seg_social = " . $this->var2str($this->seg_social) .
                        ", codigo_pension = " . $this->var2str($this->codigo_pension) .
                        ", cuenta_banco = " . $this->var2str($this->cuenta_banco) .
                        ", tipo_cuenta = " . $this->var2str($this->tipo_cuenta) .
                        ", codbanco = " . $this->var2str($this->codbanco) .
                        ", codformacion = " . $this->var2str($this->codformacion) .
                        ", carrera = " . $this->var2str($this->carrera) .
                        ", centroestudios = " . $this->var2str($this->centroestudios) .
                        ", dependientes = " . $this->intval($this->dependientes) .
                        ", estado = " . $this->var2str($this->estado) .
                        ", estado_civil = " . $this->var2str($this->estado_civil) .
                        ", banco = " . $this->var2str($this->banco) .
                        ", porcomision = " . $this->var2str($this->porcomision) .
                        ", pago_total = " . $this->var2str($this->pago_total) .
                        ", pago_neto = " . $this->var2str($this->pago_neto) .
                        ", fecha_modificacion = " . $this->var2str($this->fecha_modificacion) .
                        ", usuario_modificacion = " . $this->var2str($this->usuario_modificacion) .
                        "  WHERE codagente = " . $this->var2str($this->codagente) . ";";
            } else {
                if (is_null($this->codagente)) {
                    $this->codagente = $this->get_new_codigo();
                }
                $sql = "INSERT INTO " . $this->table_name . " (codalmacen,idempresa,codagente,nombre,apellidos,segundo_apellido,nombreap,dnicif,telefono,
               email,codcargo,cargo,codsupervisor,codgerencia,codtipo,codarea,coddepartamento,provincia,ciudad,direccion,f_nacimiento,
               f_alta,f_baja,sexo,idsindicato,codseguridadsocial,seg_social,codsistemapension,codigo_pension,cuenta_banco,codbanco,tipo_cuenta,codformacion,carrera,centroestudios,dependientes,estado,estado_civil,banco,
               porcomision,pago_total,pago_neto,fecha_creacion,usuario_creacion)
               VALUES (" . $this->var2str($this->codalmacen) .
                        "," . $this->intval($this->idempresa) .
                        "," . $this->var2str($this->codagente) .
                        "," . $this->var2str($this->nombre) .
                        "," . $this->var2str($this->apellidos) .
                        "," . $this->var2str($this->segundo_apellido) .
                        "," . $this->var2str($this->nombreap) .
                        "," . $this->var2str($this->dnicif) .
                        "," . $this->var2str($this->telefono) .
                        "," . $this->var2str($this->email) .
                        "," . $this->var2str($this->codcargo) .
                        "," . $this->var2str($this->cargo) .
                        "," . $this->var2str($this->codsupervisor) .
                        "," . $this->var2str($this->codgerencia) .
                        "," . $this->var2str($this->codtipo) .
                        "," . $this->var2str($this->codarea) .
                        "," . $this->var2str($this->coddepartamento) .
                        "," . $this->var2str($this->provincia) .
                        "," . $this->var2str($this->ciudad) .
                        "," . $this->var2str($this->direccion) .
                        "," . $this->var2str($this->f_nacimiento) .
                        "," . $this->var2str($this->f_alta) .
                        "," . $this->var2str($this->f_baja) .
                        "," . $this->var2str($this->sexo) .
                        "," . $this->var2str($this->idsindicato) .
                        "," . $this->var2str($this->codseguridadsocial) .
                        "," . $this->var2str($this->seg_social) .
                        "," . $this->var2str($this->codsistemapension) .
                        "," . $this->var2str($this->codigo_pension) .
                        "," . $this->var2str($this->cuenta_banco) .
                        "," . $this->var2str($this->codbanco) .
                        "," . $this->var2str($this->tipo_cuenta) .
                        "," . $this->var2str($this->codformacion) .
                        "," . $this->var2str($this->carrera) .
                        "," . $this->var2str($this->centroestudios) .
                        "," . $this->var2str($this->dependientes) .
                        "," . $this->var2str($this->estado) .
                        "," . $this->var2str($this->estado_civil) .
                        "," . $this->var2str($this->banco) .
                        "," . $this->var2str($this->porcomision) .
                        "," . $this->var2str($this->pago_total) .
                        "," . $this->var2str($this->pago_neto) .
                        "," . $this->var2str($this->fecha_creacion) .
                        "," . $this->var2str($this->usuario_creacion)
                        . ");";
            }

            return $this->db->exec($sql);
        } else
            return FALSE;
    }

    /**
     * Elimina este empleado/agente
     * @return type
     */
    public function delete() {
        $this->clean_cache();
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE codagente = " . $this->var2str($this->codagente) . ";");
    }

    /**
     * Limpiamos la caché
     */
    private function clean_cache() {
        $this->cache->delete('m_agente_all');
    }

    /**
     * Devuelve un array con todos los agentes/empleados.
     * @return \agente
     */
    public function all() {
        /// leemos esta lista de la caché
        $listagentes = $this->cache->get_array('m_agente_all');
        if (!$listagentes) {
            $agentes = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY nombre ASC;");
            if ($agentes) {
                foreach ($agentes as $a) {
                    $valor = new agente($a);
                    $res = $this->info_adicional($valor);
                    $listagentes[] = $res;
                }
            }
            $this->cache->set('m_agente_all', $listagentes);
        }

        return $listagentes;
    }

    public function all_activos() {
        $listagentes = $this->cache->get_array('m_agente_all');
        if (!$listagentes) {
            $agentes = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE f_baja IS NULL ORDER BY nombre ASC;");
            if ($agentes) {
                foreach ($agentes as $a) {
                    $valor = new agente($a);
                    $res = $this->info_adicional($valor);
                    $listagentes[] = $res;
                }
            }
            $this->cache->set('m_agente_all', $listagentes);
        }

        return $listagentes;
    }

    public function corregir() {
        if ($this->exists()) {
            $sql = "UPDATE " . $this->table_name . " SET nombre = " . $this->var2str($this->nombre) .
                    ", apellidos = " . $this->var2str($this->apellidos) .
                    ", codalmacen = " . $this->var2str($this->codalmacen) .
                    ", idempresa = " . $this->intval($this->idempresa) .
                    ", dnicif = " . $this->var2str($this->dnicif) .
                    ", telefono = " . $this->var2str($this->telefono) .
                    ", email = " . $this->var2str($this->email) .
                    ", cargo = " . $this->var2str($this->cargo) .
                    ", provincia = " . $this->var2str($this->provincia) .
                    ", ciudad = " . $this->var2str($this->ciudad) .
                    ", direccion = " . $this->var2str($this->direccion) .
                    ", f_nacimiento = " . $this->var2str($this->f_nacimiento) .
                    ", f_alta = " . $this->var2str($this->f_alta) .
                    ", f_baja = " . $this->var2str($this->f_baja) .
                    ", sexo = " . $this->var2str($this->sexo) .
                    ", seg_social = " . $this->var2str($this->seg_social) .
                    ", banco = " . $this->var2str($this->banco) .
                    ", cuenta_banco = " . $this->var2str($this->cuenta_banco) .
                    ", codcargo = " . $this->var2str($this->codcargo) .
                    ", porcomision = " . $this->var2str($this->porcomision) .
                    ", fecha_creacion = " . $this->var2str($this->fecha_creacion) .
                    ", usuario_creacion = " . $this->var2str($this->usuario_creacion) .
                    "  WHERE codagente = " . $this->var2str($this->codagente) . ";";
            return $this->db->exec($sql);
        } else {
            return false;
        }
    }

    public function estados_agente() {
        $estados = array();
        $estados['A'] = 'Activo';
        $estados['V'] = 'Vacaciones';
        $estados['I'] = 'Inactivo';
        $estados['S'] = 'Suspendido';
        $estados['C'] = 'Cesado o Jubilado';
        $estados['P'] = 'Permiso médico';
        $estados['L'] = 'Litigio';
        return $estados;
    }

    public function estadistica_sexo($sexo) {
        $sql = "select count(sexo) as total from " . $this->table_name . " where sexo = " . $this->var2str($sexo) . " AND f_baja IS NULL;";
        $data = $this->db->select($sql);
        if ($data) {
            $valor = new stdClass();
            $valor->total = $data[0]['total'];
            return $valor;
        }
    }

    public function estadistica_almacen() {
        $sql = "select codalmacen, count(codagente) as total from " . $this->table_name . " WHERE f_baja IS NULL GROUP BY codalmacen;";
        $data = $this->db->select($sql);
        if ($data) {
            $lista = array();
            foreach ($data as $d) {
                $valor = new stdClass();
                $valor->codalmacen = $d['codalmacen'];
                $valor->descripcion = $this->almacen->get($d['codalmacen'])->nombre;
                $valor->total = $d['total'];
                $lista[] = $valor;
            }
            return $lista;
        } else {
            return false;
        }
    }

    public function organigrama($opciones = null) {
        $where = "";
        if ($opciones) {
            if (!empty($opciones['almacen'])) {
                $where .= " AND codalmacen = " . $this->var2str($opciones['almacen']) . " ";
            }
            if (!empty($opciones['gerencia'])) {
                $where .= " AND codgerencia = " . $this->var2str($opciones['gerencia']) . " ";
            }
            if (!empty($opciones['area'])) {
                $where .= " AND codarea = " . $this->var2str($opciones['area']) . " ";
            }
        }
        $sql = "SELECT * FROM " . $this->table_name . " WHERE  f_baja IS NULL $where ORDER BY codsupervisor ASC";
        $data = $this->db->select($sql);
        if ($data) {
            $lista = array();
            foreach ($data as $d) {
                $item = new agente($d);
                $value = $this->info_adicional($item);
                $lista[] = $value;
            }
            return $this->estructura($lista);
        }
    }

    public function estructura($lista, $raiz = null) {
        $estructura = array();
        foreach ($lista as $key => $item) {
            if ($item->codsupervisor == $raiz) {
                unset($lista[$key]);
                $estructura[] = array(
                    'id' => $item->codagente,
                    'name' => $item->nombreap,
                    'title' => $item->cargo,
                    'foto' => $item->get_foto(),
                    'children' => $this->estructura($lista, $item->codagente)
                );
            }
        }
        return empty($estructura) ? null : $estructura;
    }

}
