let detalles = [];
let tabla;
$(function () {

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
   Cargar();
});

//se declara la variable del modal
var myModal = new bootstrap.Modal(document.getElementById('ModalCargo'), {
    keyboard: false
})

function Cargar() {
    tabla = $('#tabla-detalles').DataTable({
        data: detalles,
        columns: [
            { data: null, render: (data, type, row, meta) => meta.row + 1 },
            { data: 'nombre' },
            {
                data: null,
                render: (data, type, row, meta) =>
                    `<button type="button" class="btn btn-danger btn-sm" onclick="eliminarDetalle(${meta.row})" data-toggle="tooltip" title="Eliminar"><i class="fas fa-trash"></i></button>`
            }
        ],
        language: {
                "url": "/assets/js/spanish.json"
            },
        responsive: true,
        dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
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
        order: [[1, "asc"]],
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "Todo(s)"]],
    });
}


function limpiarValidaciones() {
    const fields = [
        'nombre',
        'active'
    ];

    fields.forEach(field => {
        $('#error_' + field).text('');
    });
}

function addDetalle(){
    const formularioDetalle = $('#formDetalle')[0];
    const nombre = $('input[name="detalle_nombre"]').val();
    if (nombre.trim()) {
        detalles.push({ nombre });
        actualizarTabla();
        $('#modalDetalle').modal('hide');
        formularioDetalle.reset();
    }
}

function actualizarTabla() {
    tabla.clear().rows.add(detalles).draw();
}

function eliminarDetalle(index) {
    detalles.splice(index, 1);
    actualizarTabla();
}

function saveData() {

    let activo=$('#active').is(':checked')?1:0;
    $('#active').change(function(){
        if($(this).is(':checked')){
            activo=1;
        }else{
            activo=0;
        }
    })

    const formData = new FormData();
    formData.append('nombre', $('#nombre').val());
    formData.append('active', activo);
    formData.append("detalles", JSON.stringify(detalles));

    // Limpiar errores anteriores
    $('.text-danger').text('');
    $('.form-control').removeClass('is-invalid');

    $.ajax({
        url: '/admin/admin.novedad.store',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-HTTP-Method-Override': 'POST'
        },
        method: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function (res) {
            $('#modalNovedad').modal('hide'); // o myModal.toggle(); si usas instancia JS
            toastr.success(res.message); // ← era "res", no "response"
        },
        error: function (err) {
           if (err.status === 422) {
                const errors = err.responseJSON.errors;

                // Iterar y mostrar errores
                for (let field in errors) {
                    const mensaje = errors[field][0];
                    $(`.error-${field}`).text(mensaje);
                    $(`[name="${field}"]`).addClass('is-invalid');
                }
            } else {
                toastr.error('Error inesperado.');
            }
        }
    });
}




