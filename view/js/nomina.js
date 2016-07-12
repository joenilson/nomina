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

$(document).ready(function() {
    $("#b_nuevo_agente").click(function(event) {
        event.preventDefault();
        window.location.href = 'index.php?page=admin_agente&type=nuevo';
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
});
