/**
 * Funcionalidad para gestionar utilidades en cotizaciones
 */

// Variables globales
let cotizacionIdActual = null;
let categoriasDisponibles = [];
let itemsPropiosDisponibles = [];

/**
 * Muestra el modal de utilidad
 */
function mostrarModalUtilidad() {
    // Obtener el ID de la cotización actual
    cotizacionIdActual = obtenerCotizacionId();

    if (!cotizacionIdActual) {
        toastr.error('No se pudo obtener el ID de la cotización');
        return;
    }

    // Establecer el ID en el formulario
    $('#cotizacionId').val(cotizacionIdActual);

    // Cargar categorías (requerido)
    cargarCategorias();

    // Cargar utilidades existentes
    cargarUtilidadesExistentes();

    // Mostrar el modal
    $('#modalUtilidad').modal('show');
}

/**
 * Obtiene el ID de la cotización actual desde la URL o elemento DOM
 */
function obtenerCotizacionId() {
    // Intentar obtener desde la URL
    const urlParts = window.location.pathname.split('/');
    const editIndex = urlParts.indexOf('edit');

    if (editIndex > -1 && urlParts[editIndex + 1]) {
        return urlParts[editIndex + 1];
    }

    // Intentar obtener desde un elemento hidden o data attribute
    const cotizacionEl = document.querySelector('[data-cotizacion-id]');
    if (cotizacionEl) {
        return cotizacionEl.dataset.cotizacionId;
    }

    // Intentar obtener desde input hidden
    const hiddenInput = document.querySelector('input[name="cotizacion_id"]');
    if (hiddenInput) {
        return hiddenInput.value;
    }

    return null;
}

/**
 * Cambia los items propios disponibles según la categoría seleccionada
 */
async function cambiarCategoria() {
    const categoriaId = $('#categoria_id').val();

    if (!categoriaId) {
        // Si no hay categoría seleccionada, limpiar items
        $('#item_propio_id').html('<option value="">Primero seleccione una categoría</option>');
        return;
    }

    // Cargar items de la categoría seleccionada
    await cargarItemsPorCategoria(categoriaId);

    // Actualizar estado del botón
    actualizarEstadoBotonAplicar();
}

/**
 * Actualiza el símbolo y ayuda según el tipo seleccionado
 */
function actualizarTipoUtilidad() {
    const tipo = $('#utilidad_tipo').val();

    if (tipo === 'porcentaje') {
        $('#simboloValor').text('%');
        $('#ayudaValor').text('Ingrese el porcentaje de utilidad (ej: 15 para 15%)');
    } else if (tipo === 'valor') {
        $('#simboloValor').text('$');
        $('#ayudaValor').text('Ingrese el valor fijo de utilidad en pesos');
    }

    actualizarEstadoBotonAplicar();
}

/**
 * Carga las categorías disponibles
 */
async function cargarCategorias() {
    try {
        $('#categoria_id').html('<option value="">Cargando categorías...</option>');

        const response = await fetch(`/admin/admin.cotizaciones.utilidades.categorias/${cotizacionIdActual}`);
        const result = await response.json();

        let options = '<option value="">Seleccione una categoría...</option>';

        if (result.success && result.data.length > 0) {
            result.data.forEach(categoria => {
                options += `<option value="${categoria.id}">${categoria.nombre}</option>`;
            });
            categoriasDisponibles = result.data;
        } else {
            options += '<option value="" disabled>No hay categorías con productos en esta cotización</option>';
        }

        $('#categoria_id').html(options);
    } catch (error) {
        console.error('Error cargando categorías:', error);
        toastr.error('Error al cargar las categorías');
        $('#categoria_id').html('<option value="">Error cargando categorías</option>');
    }
}

/**
 * Carga los items propios disponibles
 */
async function cargarItemsPropios() {
    try {
        $('#item_propio_id').html('<option value="">Cargando items propios...</option>');

        const response = await fetch(`/admin/admin.cotizaciones.utilidades.items-propios/${cotizacionIdActual}`);
        const result = await response.json();

        let options = '<option value="">Seleccione un item propio...</option>';

        if (result.success && result.data.length > 0) {
            result.data.forEach(item => {
                options += `<option value="${item.id}">${item.codigo} - ${item.nombre}</option>`;
            });
            itemsPropiosDisponibles = result.data;
        } else {
            options += '<option value="" disabled>No hay items propios con productos en esta cotización</option>';
        }

        $('#item_propio_id').html(options);
    } catch (error) {
        console.error('Error cargando items propios:', error);
        toastr.error('Error al cargar los items propios');
        $('#item_propio_id').html('<option value="">Error cargando items propios</option>');
    }
}

/**
 * Carga los items propios de una categoría específica
 */
async function cargarItemsPorCategoria(categoriaId) {
    try {
        $('#item_propio_id').html('<option value="">Cargando items propios...</option>');

        const response = await fetch(`/admin/admin.cotizaciones.utilidades.items-categoria/${cotizacionIdActual}/${categoriaId}`);
        const result = await response.json();

        let options = '<option value="">Seleccione un item propio...</option>';

        if (result.success && result.data.length > 0) {
            result.data.forEach(item => {
                const tipoTexto = item.tipo === 'cargo' ? ' (Cargo)' : '';
                options += `<option value="${item.id}" data-tipo="${item.tipo || ''}">${item.codigo || ''} ${item.codigo ? '-' : ''} ${item.nombre}${tipoTexto}</option>`;
            });
        } else {
            options += '<option value="" disabled>No hay items propios en esta categoría para esta cotización</option>';
        }

        $('#item_propio_id').html(options);
    } catch (error) {
        console.error('Error cargando items propios por categoría:', error);
        toastr.error('Error al cargar los items propios');
        $('#item_propio_id').html('<option value="">Error cargando items propios</option>');
    }
}

/**
 * Actualiza el estado del botón aplicar
 */
function actualizarEstadoBotonAplicar() {
    const categoriaId = $('#categoria_id').val();
    const itemPropioId = $('#item_propio_id').val();
    const tipo = $('#utilidad_tipo').val();
    const valor = $('#utilidad_valor').val();

    // Ambos campos son obligatorios ahora
    const formularioValido = categoriaId && itemPropioId && tipo && valor;

    $('#btnAplicarUtilidad').prop('disabled', !formularioValido);

    // Mostrar resumen si es válido
    if (formularioValido) {
        mostrarResumenUtilidad();
    } else {
        $('#resumenUtilidad').addClass('d-none');
    }
}

/**
 * Muestra un resumen de la utilidad a aplicar
 */
function mostrarResumenUtilidad() {
    const categoriaId = $('#categoria_id').val();
    const itemPropioId = $('#item_propio_id').val();
    const tipo = $('#utilidad_tipo').val();
    const valor = parseFloat($('#utilidad_valor').val());

    // Obtener nombre de la categoría
    const categoria = categoriasDisponibles.find(c => c.id == categoriaId);
    const categoriaNombre = categoria ? categoria.nombre : 'Categoría seleccionada';

    // Obtener nombre del item propio
    const itemOption = $(`#item_propio_id option[value="${itemPropioId}"]`);
    const itemNombre = itemOption.text() || 'Item propio seleccionado';

    const tipoTexto = tipo === 'porcentaje' ? `${valor}%` : `$${valor.toLocaleString()}`;
    const texto = `Se aplicará una utilidad de ${tipoTexto} a los productos de categoría "<strong>${categoriaNombre}</strong>" y item propio "<strong>${itemNombre}</strong>"`;

    $('#textoResumen').html(texto);
    $('#resumenUtilidad').removeClass('d-none');
}

/**
 * Aplica la utilidad
 */
async function aplicarUtilidad() {
    const form = document.getElementById('formUtilidad');
    const formData = new FormData(form);

    try {
        $('#btnAplicarUtilidad').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Aplicando...');

        console.log('Enviando datos de utilidad:', Object.fromEntries(formData));

        const response = await fetch('/admin/admin.cotizaciones.utilidades.store', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        });

        const result = await response.json();
        console.log('Resultado aplicar utilidad:', result);

        if (result.success) {
            toastr.success(result.message);
            limpiarFormulario();
            cargarUtilidadesExistentes();

            // Forzar actualización de totales
            setTimeout(() => {
                actualizarTotalesEnInterfaz();
            }, 500);

        } else {
            toastr.error(result.message);
        }
    } catch (error) {
        console.error('Error aplicando utilidad:', error);
        toastr.error('Error al aplicar la utilidad');
    } finally {
        $('#btnAplicarUtilidad').prop('disabled', false).html('<i class="fas fa-check"></i> Aplicar Utilidad');
    }
}

/**
 * Actualiza los totales en la interfaz
 */
async function actualizarTotalesEnInterfaz() {
    try {
        console.log('Actualizando totales en la interfaz...');

        // Intentar llamar las funciones existentes de actualización
        if (typeof actualizarTotalesCotizacion === 'function') {
            console.log('Llamando actualizarTotalesCotizacion...');
            actualizarTotalesCotizacion();
        }

        if (typeof cargarProductosGuardados === 'function') {
            console.log('Llamando cargarProductosGuardados...');
            cargarProductosGuardados();
        }

        // Si no existen las funciones, intentar obtener totales directamente
        if (cotizacionIdActual) {
            const response = await fetch(`/admin/admin.cotizaciones/${cotizacionIdActual}/totales`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            if (response.ok) {
                const result = await response.json();
                console.log('Totales obtenidos:', result);

                if (result.success && result.data) {
                    // Actualizar elementos de la interfaz si existen
                    const totales = result.data;

                    if ($('#sticky-subtotal').length) {
                        $('#sticky-subtotal').text(`$${formatearNumero(totales.subtotal || 0)}`);
                    }

                    if ($('#sticky-total').length) {
                        $('#sticky-total').text(`$${formatearNumero(totales.total || 0)}`);
                    }

                    toastr.success('Totales actualizados correctamente');
                }
            }
        }

    } catch (error) {
        console.error('Error actualizando totales:', error);
    }
}

/**
 * Formatea un número para mostrar en la interfaz
 */
function formatearNumero(numero) {
    return parseFloat(numero || 0).toLocaleString('es-CO', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
}

/**
 * Elimina una utilidad
 */
async function eliminarUtilidad(id) {
    if (!confirm('¿Está seguro de eliminar esta utilidad?')) {
        return;
    }

    try {
        const response = await fetch(`/admin/admin.cotizaciones.utilidades.destroy/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (result.success) {
            toastr.success(result.message);
            cargarUtilidadesExistentes();

            // Forzar actualización de totales
            setTimeout(() => {
                actualizarTotalesEnInterfaz();
            }, 500);

        } else {
            toastr.error('Error al eliminar la utilidad');
        }
    } catch (error) {
        console.error('Error eliminando utilidad:', error);
        toastr.error('Error al eliminar la utilidad');
    }
}

/**
 * Carga las utilidades existentes para mostrar en el modal
 */
async function cargarUtilidadesExistentes() {
    try {
        console.log('Cargando utilidades para cotización:', cotizacionIdActual);

        const response = await fetch(`/admin/admin.cotizaciones.utilidades.obtener/${cotizacionIdActual}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers.get('content-type'));

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Error response:', errorText);
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const result = await response.json();
        console.log('Response data:', result);

        let html = '';

        if (result.success && result.data.length > 0) {
            result.data.forEach(utilidad => {
                const elemento = utilidad.categoria?.nombre || `${utilidad.item_propio?.codigo} - ${utilidad.item_propio?.nombre}` || 'Sin especificar';
                const tipo = utilidad.tipo === 'porcentaje' ? '%' : '$';
                const valorFormateado = formatearValor(utilidad.valor, utilidad.tipo);
                const calculadoFormateado = formatearValor(utilidad.valor_calculado || 0, 'valor');

                html += `
                    <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2 bg-white">
                        <div>
                            <strong>${elemento}</strong><br>
                            <small class="text-muted">
                                ${tipo}${valorFormateado}
                                ${utilidad.valor_calculado ? `→ ${calculadoFormateado}` : ''}
                            </small>
                        </div>
                        <button class="btn btn-sm btn-outline-danger" onclick="eliminarUtilidad(${utilidad.id})" title="Eliminar utilidad">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
            });
        } else {
            html = `
                <div class="text-muted text-center">
                    <i class="fas fa-percentage fa-2x opacity-50"></i>
                    <p class="mb-0 mt-2">No hay utilidades aplicadas</p>
                </div>
            `;
        }

        $('#listaUtilidades').html(html);
    } catch (error) {
        console.error('Error cargando utilidades:', error);
        $('#listaUtilidades').html('<div class="text-danger">Error cargando utilidades: ' + error.message + '</div>');
    }
}

/**
 * Formatea un valor según el tipo
 */
function formatearValor(valor, tipo) {
    const numero = parseFloat(valor) || 0;

    if (tipo === 'porcentaje') {
        return numero.toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    } else {
        return numero.toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }
}

/**
 * Limpia el formulario
 */
function limpiarFormulario() {
    $('#formUtilidad')[0].reset();
    $('#divCategorias, #divItemsPropios, #resumenUtilidad').addClass('d-none');
    $('#btnAplicarUtilidad').prop('disabled', true);
}

// Event listeners
$(document).ready(function() {
    // Listeners para campos del formulario
    $('#categoria_id').on('change', cambiarCategoria);
    $('#utilidad_tipo').on('change', actualizarTipoUtilidad);
    $('#utilidad_valor, #item_propio_id').on('input change', actualizarEstadoBotonAplicar);

    // Listener para cerrar modal
    $('#modalUtilidad').on('hidden.bs.modal', function() {
        limpiarFormulario();
    });

    // Agregar función de debug temporal (remover en producción)
    window.debugUtilidades = function() {
        console.log('=== DEBUG UTILIDADES ===');
        console.log('Cotización ID actual:', cotizacionIdActual);
        console.log('Categorías disponibles:', categoriasDisponibles);
        console.log('Items propios disponibles:', itemsPropiosDisponibles);

        // Mostrar funciones disponibles
        console.log('Funciones disponibles:');
        console.log('- actualizarTotalesCotizacion:', typeof actualizarTotalesCotizacion);
        console.log('- cargarProductosGuardados:', typeof cargarProductosGuardados);

        // Verificar elementos de totales en DOM
        console.log('Elementos de totales encontrados:');
        console.log('- sticky-subtotal:', $('#sticky-subtotal').length ? 'SÍ' : 'NO');
        console.log('- sticky-total:', $('#sticky-total').length ? 'SÍ' : 'NO');

        // Intentar obtener totales actuales
        if (cotizacionIdActual) {
            actualizarTotalesEnInterfaz();
        }
    };

    console.log('Sistema de utilidades cargado. Usar debugUtilidades() para debug.');
});
