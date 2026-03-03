let detalles = [];
let tabla;
let detalleEditIndex = null;
let autoTotales = true;

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
    bindAutoTotales();
});
function Cargar() {
    tabla = $('#tabla-detalles').DataTable({
        data: detalles,
        columns: [
            { data: null, render: (data, type, row, meta) => meta.row + 1 },
            { data: 'nombre' },
            { data: 'valor_admon', render: (d) => formatMoney(d) },
            { data: 'valor_operativo', render: (d) => formatMoney(d) },
            {
                data: null,
                render: (data, type, row, meta) =>
                    `<div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="openDetalleModal(${meta.row})" data-toggle="tooltip" title="Editar"><i class="fas fa-pen"></i></button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="eliminarDetalle(${meta.row})" data-toggle="tooltip" title="Eliminar"><i class="fas fa-trash"></i></button>
                    </div>`
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
    applyAutoTotales();
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

function openDetalleModal(index = null) {
    detalleEditIndex = index;
    const isEdit = index !== null && detalles[index];

    $('#modalDetalleLabel').text(isEdit ? 'Editar detalle' : 'Agregar detalle');
    $('#btnDetalleSubmit').text(isEdit ? 'Actualizar' : 'Agregar');

    if (isEdit) {
        const item = detalles[index];
        $('#detalle_nombre').val(item.nombre || '');
        $('#detalle_valor_admon').val(item.valor_admon ?? '');
        $('#detalle_valor_operativo').val(item.valor_operativo ?? '');
        $('#detalle_index').val(index);
    } else {
        $('#formDetalle')[0].reset();
        $('#detalle_index').val('');
    }

    $('#modalDetalle').modal('show');
}

function saveDetalleFromModal() {
    const nombre = $('#detalle_nombre').val().trim();
    const valorAdmon = parseFloat($('#detalle_valor_admon').val()) || 0;
    const valorOperativo = parseFloat($('#detalle_valor_operativo').val()) || 0;

    if (!nombre) {
        toastr.warning('El nombre del detalle es obligatorio.');
        return;
    }

    const payload = {
        nombre,
        valor_admon: valorAdmon,
        valor_operativo: valorOperativo
    };

    if (detalleEditIndex !== null) {
        detalles[detalleEditIndex] = payload;
    } else {
        detalles.push(payload);
    }

    actualizarTabla();
    saveData();
    $('#modalDetalle').modal('hide');
    $('#formDetalle')[0].reset();
    detalleEditIndex = null;
}

function actualizarTabla() {
    tabla.clear().rows.add(detalles).draw();
    applyAutoTotales();
}

function eliminarDetalle(index) {
    detalles.splice(index, 1);
    actualizarTabla();
    saveData();
}

function formatMoney(value) {
    const num = parseFloat(value);
    if (isNaN(num)) return '0.00';
    return num.toFixed(2);
}

function saveData() {

    const novedadId = $('#novedad_id').val(); // Obtener el ID de la novedad creada
    if(novedadId>0) {
        updateData();
        return;
    }

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
    formData.append('total_admon', $('#total_valor_admon').val());
    formData.append('total_operativo', $('#total_valor_operativo').val());

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
            $('#novedad_id').val(res.novedad_id); // Guardar el ID de la novedad creada
            $('#modalNovedad').modal('hide'); // o myModal.modal('toggle'); si usas instancia JS
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

function updateData() {

    let activo=$('#active').is(':checked')?1:0;
    $('#active').change(function(){
        if($(this).is(':checked')){
            activo=1;
        }else{
            activo=0;
        }
    })

    const novedadId =  $('#novedad_id').val();
    const formData = new FormData();
    formData.append('nombre', $('#nombre').val());
    formData.append('active', activo);
    formData.append("detalles", JSON.stringify(detalles));
    formData.append('total_admon', $('#total_valor_admon').val());
    formData.append('total_operativo', $('#total_valor_operativo').val());
    formData.append('_method', 'PUT');

    // Limpiar errores anteriores
    $('.text-danger').text('');
    $('.form-control').removeClass('is-invalid');

    $.ajax({
        url: '/admin/admin.novedad.update/'+novedadId,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        },
        method: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function (res) {
            $('#modalNovedad').modal('hide');
            toastr.success(res.message);
        },
        error: function (err) {
           if (err.status === 422) {
                const errors = err.responseJSON.errors;

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

function bindAutoTotales() {
    autoTotales = $('#auto_totales').is(':checked');
    $('#auto_totales').on('change', function () {
        autoTotales = $(this).is(':checked');
        applyAutoTotales();
    });
    applyAutoTotales();
}

function applyAutoTotales() {
    const totalAdmon = detalles.reduce((acc, d) => acc + (parseFloat(d.valor_admon) || 0), 0);
    const totalOper = detalles.reduce((acc, d) => acc + (parseFloat(d.valor_operativo) || 0), 0);

    if (autoTotales) {
        $('#total_valor_admon').val(totalAdmon.toFixed(2)).prop('readonly', true);
        $('#total_valor_operativo').val(totalOper.toFixed(2)).prop('readonly', true);
    } else {
        $('#total_valor_admon').prop('readonly', false);
        $('#total_valor_operativo').prop('readonly', false);
    }
}




