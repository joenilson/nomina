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
                   bootbox.alert('¡No hay una estructura asignada para este padre!');
                }
            },
            error: function(response) {
                bootbox.alert(response);
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
               bootbox.alert('¡No hay una estructura asignada para este padre!');
            }
        },
        error: function(response) {
            bootbox.alert(response);
        }
    });
    return listado;
}

function getSelectedRows(gridid) {
    var grid = $("#"+gridid);
    var rowKey = grid.getGridParam("selrow");
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
                    var data = response;
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
                    bootbox.alert(response);
                }
            },
            error: function(response) {
                bootbox.alert(response);
            }
        });
    }
    alert('Proceso concluido!');
    $('#b_cerrar_modal_estadistica_importacion').show();
}

function visualizarDocumento(documento){
    $('#modal_mostrar_documento').modal('show');
        $("#visor_documento").detach();
        $("<iframe id='visor_documento' />")
          .attr('src', documento)
          .attr('width', '100%')
          .attr('height', '500')
          .appendTo('#modal_body_mostrar_documento');
}

/**
 * Funcion para traer los datos para los distintos gráficos
 * @param {type} tipo
 * @returns {response|String|Boolean}
 */
function cargarGrafico(tipo,componente,tipo_grafico,options){
    /**
     * Hacemos la llamada AJAX, en la página origen del grafico se debe colocar
     * el valor de la variable url_graficos
     */
    $.ajax({
        type: 'GET',
        url : url_graficos,
        data : 'type=grafico&subtype='+tipo,
        async: false,
        success : function(response) {
            if(response.length !== 0){
                new Chart($(componente), {
                    type: tipo_grafico,
                    data: {
                        datasets: [{
                            data: response.datasets.data,
                            backgroundColor: response.backgroundColor,
                            borderColor: response.borderColor,
                            borderWidth: 1
                        }],
                        labels: response.labels
                    },
                    options: options

                });
            }else{
               return false;
            }
        },
        error: function(response) {
            bootbox.alert(response);
        }
    });
    //return listado;
}

/**
 * Funcion para generar el daterangepicker en modo rango de fechas
 * @param {string} f_rango es el componente donde se mostrará el calendario
 * @param {string} f_desde es el id del input donde grabaremos la fecha de inicio
 * @param {string} f_hasta es el id del input donde grabaremos la fecha de fin
 * @param {string} formato es el formato en que guardaremos y mostraremos la fecha
 * @param {boolean} rangos es un campo para saber si mostramos o no el selector de rangos predefinidos
 * @param {boolean} tiempos es un campo booleano para saber si mostramos el selector de tiempo hora, minuto
 * @returns {empty} devuelve el selector con el rango de fechas
 */
function rango_fechas(f_rango, f_desde, f_hasta, formato, rangos, tiempos){
    moment().format(formato);
    if(typeof($('#'+f_rango)) !== 'undefined'){
        $('#'+f_rango).daterangepicker({
            singleDatePicker: false,
            showDropdowns: true,
            timePicker: tiempos,
            timePickerIncrement: (tiempos)?5:0,
            ranges: (rangos)?{
                'Hoy': [moment(), moment()],
                'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Ultimos 7 Días': [moment().subtract(6, 'days'), moment()],
                'Ultimos 30 días': [moment().subtract(29, 'days'), moment()],
                'Este mes': [moment().startOf('month'), moment().endOf('month')],
                'Anterior Mes': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }:null,
            locale: {
                format: formato,
                separator: " - ",
                applyLabel: "Tomar",
                cancelLabel: "Cancelar",
                fromLabel: "Desde",
                toLabel: "Hasta",
                customRangeLabel: "Manual"
            },
            opens: "left",
            startDate: moment().startOf('month'),
            endDate: moment()
        });

        $('#'+f_rango).on('apply.daterangepicker', function(ev, picker) {
            $('#'+f_desde).val(picker.startDate.format(formato));
            $('#'+f_hasta).val(picker.endDate.format(formato));
        });

        if($('#'+f_desde).val()){
            $('#'+f_rango).data('daterangepicker').setStartDate($('#'+f_desde).val());
        }else{
            $('#'+f_desde).val($('#'+f_rango).data('daterangepicker').startDate.format(formato));
        }

        if($('#'+f_hasta).val()){
            $('#'+f_rango).data('daterangepicker').setEndDate($('#'+f_hasta).val());
        }else{
            $('#'+f_hasta).val($('#'+f_rango).data('daterangepicker').endDate.format(formato));
        }
    }
}

/*
 * Funcion para generar el daterangepicker en modo single
 * @param id_field es el id donde llamaremos al calendario
 * @param formato es el formato en que necesitamos la fecha
 * @param tiempos es un cambo boolean si necesitamos o no la parte de tiempo hora, minuto
 */
function fecha(id_field, formato, tiempos){
    moment().format(formato);
    if(typeof($('#'+id_field)) !== 'undefined'){
        $('#'+id_field).daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            timePicker: tiempos,
            timePickerIncrement: (tiempos)?5:0,
            locale: {
                format: formato,
                separator: " - ",
                applyLabel: "Tomar",
                cancelLabel: "Cancelar",
                fromLabel: "Desde",
                toLabel: "Hasta",
                customRangeLabel: "Manual"
            },
            opens: "left",
            startDate: moment()
        });
    }
}

if(typeof(Chart) !=='undefined'){
    Chart.pluginService.register({
        beforeDraw: function(chart) {
            var width = chart.chart.width,
                height = chart.chart.height,
                ctx = chart.chart.ctx,
                type = chart.config.type;
            ctx.restore();
            var fontSize = (height / 114).toFixed(2);
            ctx.font = fontSize + "em sans-serif";
            ctx.textBaseline = "middle";
            if (type == 'doughnut')
            {
                var total = 0;
                $.each(chart.config.data.datasets[0].data, function(data){
                    total += parseInt(this, 10);
                });

                var oldFill = ctx.fillStyle;
                var fontSize = ((height - chart.chartArea.top) / 100).toFixed(2);

                ctx.restore();
                ctx.font = fontSize + "em sans-serif";
                ctx.textBaseline = "middle";
                var text = total,
                    textX = Math.round((width - ctx.measureText(text).width) / 2),
                    textY = (height + chart.chartArea.top) / 2;

                ctx.fillText(text, textX, textY);
                ctx.fillStyle = oldFill;
                ctx.save();
            }
        }
    });
}

$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();
    if($('#modal_nuevo_agente').length === 1){
        $('#modal_nuevo_agente').html('');
    }
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
            message: "¿Esta seguro de haber revisado la información de los empleados?<br />"+
                    "Si decide subir esta información, no podrá eliminar ningun registro y se actualizará la información de empleados ya registrados.",
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

    $("#b_guardar_reportaa").click(function(event) {
        event.preventDefault();
        bootbox.dialog({
            message: "¿Esta seguro de haber revisado la información de los empleados y a quien reportan?",
            title: "Confirmar subir empleados reportan a",
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

    $("#b_guardar_dependientes").click(function(event) {
        event.preventDefault();
        bootbox.dialog({
            message: "¿Esta seguro de haber revisado la información de los dependientes?<br />"+
                    "Si decide subir esta información, tendrá que corregir luego manualmente la información faltante o errónea.",
            title: "Confirmar subir dependientes",
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
    }
});
