let detalles=[];
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
    //carga de la datatable
    detalles = novedad.detalles;
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

function eliminarDetalle(index) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción eliminará el detalle de la novedad.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.value) {
            detalles.splice(index, 1);
            actualizarTabla();
            updateData();
            toastr.success('Detalle eliminado correctamente.');
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

    const grupoCotiza = $('#grupo_cotiza').is(':checked') ? 1 : 0;
    const novedadId =  $('#id').val();
    const formData = new FormData();
    formData.append('nombre', $('#nombre').val());
    formData.append('active', activo);
    formData.append('grupo_cotiza', grupoCotiza);
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

function actualizarTabla() {
    tabla.clear().rows.add(detalles).draw();
    applyAutoTotales();
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
        // Guardar el id del detalle si existe
        $('#detalle_id').remove();
        if (item.id) {
            $('<input>').attr({type:'hidden',id:'detalle_id',name:'detalle_id'}).val(item.id).appendTo('#formDetalle');
        }
    } else {
        $('#formDetalle')[0].reset();
        $('#detalle_index').val('');
        $('#detalle_id').remove();
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


    // Si hay id, mantenerlo en el payload
    const id = $('#detalle_id').val();
    const payload = {
        nombre,
        valor_admon: valorAdmon,
        valor_operativo: valorOperativo
    };
    if (id) payload.id = parseInt(id);

    if (detalleEditIndex !== null) {
        // Mantener el id si ya existía
        detalles[detalleEditIndex] = Object.assign({}, detalles[detalleEditIndex], payload);
    } else {
        detalles.push(payload);
    }

    actualizarTabla();
    updateData();
    $('#modalDetalle').modal('hide');
    $('#formDetalle')[0].reset();
    detalleEditIndex = null;
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

function formatMoney(value) {
    const num = parseFloat(value);
    if (isNaN(num)) return '0.00';
    return num.toFixed(2);
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
