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
function llenar_organizacion(obj,padre,destino){
    if(padre !== undefined){
        var data = "";
        var iddestino = '#'+destino;
        $.ajax({
            type: 'GET',
            url : url_organizacion,
            data : padre+'='+obj.value,
            async: false,
            success : function(response) {
                if(response.length !== 0){
                    data = response;
                }else{
                   alert('¡No hay una estructura asignada para este padre!');
                }
            },
            error: function(response) {
                alert(response);
            }
        });
        var select = $(iddestino);
        select.empty();
        if(data.length !== 0){
            select.append(
                $('<option></option>').val('').html('--------------')
            );
            $.each(data, function() {
                select.append('<option value=' + this['codorganizacion'] + '>' + this['descripcion'] + '</option>');
            });
        }
    }
}

function cargarEstructura(){
    var listado = '';
    $.ajax({
        type: 'GET',
        url : url_estructura,
        data : 'subtype=arbol_estructura',
        async: false,
        success : function(response) {
            if(response.length !== 0){
                listado = response;
            }else{
               alert('¡No hay una estructura asignada para este padre!');
            }
        },
        error: function(response) {
            alert(response);
        }
    });
    return listado;
}

function getSelectedRows(gridid) {
    var grid = $("#"+gridid);
    var rowKey = grid.getGridParam("selrow");
    console.log(rowKey);
    if (!rowKey)
        return 0;
    else {
        var selectedIDs = grid.getGridParam("selarrrow");
        var result = [];
        for (var i = 0; i < selectedIDs.length; i++) {
            result.push(selectedIDs[i]);
        }
        return result;
    }
}

function procesar_seleccionados(){
    $('#modal_estadistica_importacion').show();
    var lista = getSelectedRows('grid_resultados_procesados');
    var ingreso = 1;
    var no_ingreso = 1;
    var existe = 1;
    var error = 1;
    for(var i = 0; i < lista.length; i++){
        var dataFromTheRow = $('#grid_resultados_procesados').getRowData(lista[i]);
        $.ajax({
            type: 'POST',
            url : url_import,
            data : dataFromTheRow,
            async: false,
            success : function(response) {
                if(response.length !== 0){
                    data = response;
                    if(data.estado === 'ingresado'){
                        var pcj = (ingreso/lista.length)*100;
                        document.getElementById("divProgress_ingreso").innerHTML = ingreso+' Registro(s) ingresados de '+lista.length;
                        document.getElementById("progressor_ingreso").innerHTML = pcj.toFixed() + "%";
                        document.getElementById('progressor_ingreso').style.width = pcj.toFixed() + "%";
                        ingreso++;
                    }else if(data.estado === 'no_ingresado'){
                        var pcj = (no_ingreso/lista.length)*100;
                        document.getElementById("divProgress_no_ingresado").innerHTML = no_ingreso+' Registro(s) no ingresados de '+lista.length;
                        document.getElementById("progressor_no_ingresado").innerHTML = pcj.toFixed() + "%";
                        document.getElementById('progressor_no_ingresado').style.width = pcj.toFixed() + "%";
                        no_ingreso++;
                    }else if(data.estado === 'existe'){
                        var pcj = (existe/lista.length)*100;
                        document.getElementById("divProgress_existe").innerHTML = existe+' Registro(s) ya existen de '+lista.length;
                        document.getElementById("progressor_existe").innerHTML = pcj.toFixed() + "%";
                        document.getElementById('progressor_existe').style.width = pcj.toFixed() + "%";
                        existe++;
                    }else {
                        var pcj = (error/lista.length)*100;
                        document.getElementById("divProgress_error").innerHTML = error+' Registro(s) con error de '+lista.length;
                        document.getElementById("progressor_error").innerHTML = pcj.toFixed() + "%";
                        document.getElementById('progressor_error').style.width = pcj.toFixed() + "%";
                        error++;
                    }
                }else{
                    alert(response);
                }
            },
            error: function(response) {
                alert(response);
            }
        });
    }
    alert('Proceso concluido!');
    $('#b_cerrar_modal_estadistica_importacion').show();
}

function visualizarDocumento(documento){
    $('#modal_mostrar_documento').modal('show');
        $("#visor_documento").detach();
        $("<iframe id='imprimir_ordencarga' />")
          .attr('src', documento)
          .attr('width', '100%')
          .attr('height', '500')
          .appendTo('#modal_body_mostrar_documento');
}

$(document).ready(function() {
    
    $('[data-toggle="tooltip"]').tooltip();
    
    if($('#modal_nuevo_agente').length === 1){
        $('#modal_nuevo_agente').html('');
    }
    
    $('#b_delete_agente').hide();
    $("#b_nuevo_agente").click(function(event) {
        event.preventDefault();
        window.location.href = 'index.php?page=admin_agente&type=nuevo';
    });
    $("#b_cerrar_modal_estadistica_importacion").click(function(event){
        event.preventDefault();
        $('#modal_estadistica_importacion').hide();
        window.location.href='index.php?page=admin_agentes';
    });
    $("#b_guardar_empleados").click(function(event) {
        event.preventDefault();
        bootbox.dialog({
            message: "Esta seguro de haber revisado la información de los empleados?<br />"+
                    "Si decide subir esta información, no podrá eliminar ningun registro.",
            title: "Confirmar subir empleados",
            buttons: {
                success: {
                    label: "Confirmar",
                    className: "btn-success",
                    callback: function() {
                        this.hide();
                        procesar_seleccionados();
                    }
                },
                danger: {
                    label: "Cancelar",
                    className: "btn-danger",
                    callback: function() {
                        this.hide();
                    }
                }
            }
        });
    });

    $(".image-input input:file").change(function (){
        var img = $('#imagen-empleado');
        var file = this.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            img.attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    });
    
    if(typeof(locale_user) !== 'undefined'){
        moment.locale(locale_user);
        if($('#f_rango') !== undefined){
            $('#f_rango').daterangepicker({
                singleDatePicker: false,
                showDropdowns: true,
                ranges: {
                    'Hoy': [moment(), moment()],
                    'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Ultimos 7 Días': [moment().subtract(6, 'days'), moment()],
                    'Ultimos 30 días': [moment().subtract(29, 'days'), moment()],
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Anterior Mes': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                locale: {
                    "format": "YYYY-MM-DD",
                    "separator": " - ",
                    "applyLabel": "Tomar",
                    "cancelLabel": "Cancelar",
                    "fromLabel": "Desde",
                    "toLabel": "Hasta",
                    "customRangeLabel": "Manual"
                },
                opens: "left",
                startDate: moment().startOf('month'),
                endDate: moment()
            });

            $('#f_rango').on('apply.daterangepicker', function(ev, picker) {
                $('#f_desde').val(picker.startDate.format('YYYY-MM-DD'));
                $('#f_hasta').val(picker.endDate.format('YYYY-MM-DD'));
            });
            if($('#f_desde').val()){
                $('#f_rango').data('daterangepicker').setStartDate($('#f_desde').val());
            }else{
                $('#f_desde').val($('#f_rango').data('daterangepicker').startDate.format('YYYY-MM-DD'));
            }

            if($('#f_hasta').val()){
                $('#f_rango').data('daterangepicker').setEndDate($('#f_hasta').val());
            }else{
                $('#f_hasta').val($('#f_rango').data('daterangepicker').endDate.format('YYYY-MM-DD'));
            }
        }
    }
});
