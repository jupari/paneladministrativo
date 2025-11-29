$(function() {

    $('#estado-table').DataTable({
        "language": {
                "url": "/assets/js/spanish.json"
        },
        processing: false,
        responsive: true,
        serverSide: true,
        dom:  "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
           "<'row'<'col-sm-12'ltr>>" +
           "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
        {
            extend: 'excel',
            className: 'btn btn-success',
            exportOptions: {
                columns: ':not(.exclude)'
            },
            text: '<i class="far fa-file-excel"></i>',
            titleAttr: 'Exportar a Excel',
            filename: 'reporte_excel'
        }],
        ajax: '/admin/admin.estado.index',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'exclude', orderable: false,searchable: false},
            { data: 'estado', name: 'estado'},
            { data: 'descripcion', name: 'descripcion'},
            { data: 'color', name: 'color', className:'text-center'},
            { data: 'acciones', name: 'acciones', className: 'exclude text-center'},
        ],
        order: [[ 1, "asc" ]],
        pageLength: 8,
        lengthMenu: [[2, 4, 6, 8, 10, -1], [2, 4, 6, 8, 10, "Todo(s)"]],
    });

    // Toast
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": false,
        "progressBar": false,
        "positionClass": "toast-bottom-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    }

     // Escucha el evento de cierre de la ventana modal
    $('#myModal').on('hidden.bs.modal', function () {
        // Activa la primera pestaña al cerrar la ventana modal
        $('#custom-content-below-home-tab').tab('show');
    });


});

//se declara la variable del modal
var myModal = new bootstrap.Modal(document.getElementById('myModal'), {
    keyboard: false
})

function Cargar()
{
    let table = $('#estado-table').DataTable();
    table.ajax.reload();
}

// Limpiar inputs
function cleanInput() {

    // Campos usuario
    const Fields = ['estado','color','descripcion'];
    Fields.forEach(field => {
        $('#' + field).val('');
    });

}

function showEstado(btn) {

     // LIMPIAR CAMPOS
    cleanInput();

    $.get("/admin/admin.estado/" + btn, (response) => {

        const estado = response.estado;

        const campos = ['estado','color','descripcion'];

        campos.forEach(field => {
            $('#' + field).val(estado[field]);
        });

    });

}

//Registrar usuario
function regEstado()
{
    myModal.show()
    $('#exampleModalLabel').html('Crear Estado');

    // LIMPIAR CAMPOS
    cleanInput();
    limpiarValidaciones();
    // FIN LIMPIAR CAMPOS

    let r = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>'+
            '<button id="registro" class="btn btn-primary" onclick="registerEstado()">Agregar</button>';

    $(".modal-footer").html(r);

}

function registerEstado()
{

    const route = "/admin/admin.estado";

    let ajax_data = {

        estado:$('#estado').val(),
        color:$('#color').val(),
        descripcion:$('#descripcion').val()
    };

    $.ajax({
        url: route,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        type: 'POST',
        dataType: 'json',
        data: ajax_data,
    }).then(response => {

        Cargar();

        $('#myModal').modal('toggle');
        toastr.success(response.message);

    })
    .catch(e => {
        limpiarValidaciones();
        const arr = e.responseJSON;
        const toast = arr.errors;

        if (e.status == 422) {


            $.each(toast, function(key, value) {
                $('#error_'+key).text(value[0]);
           });


        }

        else if(e.status == 403){

            $('#myModal').modal('toggle');
            toastr.warning(arr.error);

        }

    });

}

// Actualizar usuario
function upEstado(btn)
{

    myModal.show();
    $('#exampleModalLabel').html('Editar Estado');

    // LIMPIAR CAMPOS
    cleanInput();
    limpiarValidaciones();
    showEstado(btn);
    // FIN LIMPIAR CAMPOS

    let u = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>'+
            '<button id="editar" class="btn btn-primary" onclick="updateEstado('+btn+')">Guardar</button>';

    $(".modal-footer").html(u);

}

function updateEstado(btn)
{
    const route = "/admin/admin.estado/"+btn;

    // const selectedPermissions = [];

    // // Recorre los checkboxes y agrega los seleccionados al array
    // $('.permissions:checked').each(function() {
    //     selectedPermissions.push($(this).val());
    // });

    let ajax_data = {

        // Datos formulario
        estado:$('#estado').val(),
        color:$('#color').val(),
        descripcion:$('#descripcion').val()
    };

    $.ajax({
        url: route,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'X-HTTP-Method-Override': 'PUT' },
        type: 'PUT',
        dataType: 'json',
        data: ajax_data,
    }).then(response => {

        // myModal.toggle();
        myModal.toggle();
        toastr.success(response.message);
        Cargar();


    })
    .catch(e => {
        limpiarValidaciones();
        const arr = e.responseJSON;
        const toast = arr.errors;

        if (e.status == 422) {

            $.each(toast, function(key, value) {
                $('#error_'+key).text(value[0]);
            });
            // for (const key in toast) {
            //     if (toast.hasOwnProperty(key) && toast[key] != null) {
            //         toastr.error(toast[key][0]);
            //     }
            // }

        }

        else if(e.status == 403){

            myModal.hide();
            toastr.warning(arr.message);

        }

    });

}

function limpiarValidaciones(){
    $('#error_estado').text('');
    $('#error_color').text('');
}
