/**
 * Gestión de modales para Categorías
 * Compatible con Bootstrap 4.6.2 y AdminLTE
 */

// Inicialización cuando el DOM está listo
$(document).ready(function() {
    // Inicializar tooltips
    inicializarTooltips();

    // Verificar librerías (solo en desarrollo)
    verificarLibrerias();
});

/**
 * Inicializa los tooltips de Bootstrap
 */
function inicializarTooltips() {
    $('[data-toggle="tooltip"]').tooltip();
}

/**
 * Verifica las librerías disponibles (solo para desarrollo)
 */
function verificarLibrerias() {
    if (typeof console !== 'undefined') {
        console.log('jQuery version:', $().jquery);
        console.log('Bootstrap modal disponible:', typeof $.fn.modal !== 'undefined');
        console.log('Modal categorías existe:', $('#ModalCargo').length > 0);
    }
}

/**
 * Abre el modal para registrar una nueva categoría
 */
function regCargo() {
    // Limpiar el formulario
    limpiarFormularioCategoria();

    // Cambiar título del modal
    $('#exampleModalLabel').text('Registrar Categoría');

    // Mostrar el modal
    $('#ModalCargo').modal('show');
}

/**
 * Abre el modal para editar una categoría existente
 * @param {number} id - ID de la categoría
 */
function editarCategoria(id) {
    // Cargar datos de la categoría
    $.get(`/contratos/categorias/${id}`)
        .done(function(data) {
            $('#id').val(data.id);
            $('#nombre').val(data.nombre);
            $('#active').prop('checked', data.active);

            $('#exampleModalLabel').text('Editar Categoría');
            $('#ModalCargo').modal('show');
        })
        .fail(function() {
            Swal.fire('Error', 'No se pudo cargar la categoría', 'error');
        });
}

/**
 * Guarda la categoría (crear o actualizar)
 */
function guardarCategoria() {
    // Limpiar errores previos
    limpiarErrores();

    const datos = {
        id: $('#id').val(),
        nombre: $('#nombre').val(),
        active: $('#active').is(':checked')
    };

    const url = datos.id ? `/contratos/categorias/${datos.id}` : '/contratos/categorias';
    const method = datos.id ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        method: method,
        data: datos,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })
    .done(function(response) {
        $('#ModalCargo').modal('hide');
        Swal.fire('Éxito', 'Categoría guardada correctamente', 'success');

        // Recargar la tabla si existe
        if (typeof table !== 'undefined' && table.ajax) {
            table.ajax.reload();
        }
    })
    .fail(function(xhr) {
        if (xhr.status === 422) {
            mostrarErroresValidacion(xhr.responseJSON.errors);
        } else {
            Swal.fire('Error', 'No se pudo guardar la categoría', 'error');
        }
    });
}

/**
 * Limpia el formulario del modal
 */
function limpiarFormularioCategoria() {
    $('#id').val('');
    $('#nombre').val('');
    $('#active').prop('checked', true);
    limpiarErrores();
}

/**
 * Limpia los mensajes de error del formulario
 */
function limpiarErrores() {
    $('.text-danger').text('');
}

/**
 * Muestra los errores de validación en el formulario
 * @param {Object} errores - Objeto con los errores de validación
 */
function mostrarErroresValidacion(errores) {
    for (const campo in errores) {
        $(`#error_${campo}`).text(errores[campo][0]);
    }
}

/**
 * Elimina una categoría
 * @param {number} id - ID de la categoría a eliminar
 */
function eliminarCategoria(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/contratos/categorias/${id}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })
            .done(function() {
                Swal.fire('Eliminado', 'La categoría ha sido eliminada', 'success');

                // Recargar la tabla si existe
                if (typeof table !== 'undefined' && table.ajax) {
                    table.ajax.reload();
                }
            })
            .fail(function() {
                Swal.fire('Error', 'No se pudo eliminar la categoría', 'error');
            });
        }
    });
}