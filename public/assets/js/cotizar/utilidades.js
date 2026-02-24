/**
 * Funcionalidad para gestionar utilidades en cotizaciones
 */

// Variables globales
let cotizacionIdActual = null;
let categoriasDisponibles = [];
let itemsPropiosDisponibles = [];

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
    const editIndex = urlParts.indexOf('admin.cotizaciones.edit');

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
        // Si no hay categoría seleccionada, limpiar items y ocultar controles
        $('#items_propios_container').html(`
            <div class="text-muted text-center p-4" id="placeholder_items">
                <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                Primero seleccione una categoría...
            </div>
        `);
        $('#filtroItems, #contadorSeleccionados, #totalItemsInfo, #botonesControles').hide();
        $('#btnLimpiarFiltro').hide();
        $('#resumenUtilidad').addClass('d-none');
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
        const container = $('#items_propios_container');

        // Mostrar placeholder de carga
        container.html('<div class="text-center text-muted p-4"><i class="fas fa-spinner fa-spin fa-2x mb-2"></i><br>Cargando items propios...</div>');

        const response = await fetch(`/admin/admin.cotizaciones.utilidades.items-categoria/${cotizacionIdActual}/${categoriaId}`);
        const result = await response.json();

        if (result.success && result.data.length > 0) {
            // Separar por tipos para mejor organización
            const itemsPropios = result.data.filter(item => !item.tipo || item.tipo !== 'cargo');
            const cargos = result.data.filter(item => item.tipo === 'cargo');

            let html = '<div class="p-3">';

            // Sección de Items Propios
            if (itemsPropios.length > 0) {
                html += `
                    <div class="seccion-header" data-seccion="items">
                        <i class="fas fa-cube mr-2"></i>Items Propios (${itemsPropios.length})
                    </div>
                `;

                itemsPropios.forEach(item => {
                    html += crearTarjetaItem(item, 'item');
                });
            }

            // Sección de Cargos
            if (cargos.length > 0) {
                html += `
                    <div class="seccion-header" data-seccion="cargos">
                        <i class="fas fa-users mr-2"></i>Cargos (${cargos.length})
                    </div>
                `;

                cargos.forEach(item => {
                    html += crearTarjetaItem(item, 'cargo');
                });
            }

            html += '</div>';
            container.html(html);

            // Mostrar controles
            $('#filtroItems, #contadorSeleccionados, #totalItemsInfo, #botonesControles').show();
            $('#totalItemsInfo').text(`${result.data.length} item(s) disponible(s)`);

            // Aplicar event listeners a las tarjetas
            $('.item-card').on('click', function(e) {
                if (e.target.type !== 'checkbox') {
                    const checkbox = $(this).find('.item-checkbox');
                    checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
                }
            });

            // Actualizar contador inicial
            actualizarContadorSeleccionados();

        } else {
            container.html(`
                <div class="text-center text-muted p-4">
                    <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                    No hay items propios en esta categoría para esta cotización
                </div>
            `);
            $('#filtroItems, #contadorSeleccionados, #totalItemsInfo, #botonesControles').hide();
        }

    } catch (error) {
        console.error('Error cargando items propios por categoría:', error);
        toastr.error('Error al cargar los items propios');
        $('#items_propios_container').html(`
            <div class="text-center text-danger p-4">
                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>
                Error cargando items propios
            </div>
        `);
        $('#filtroItems, #contadorSeleccionados, #totalItemsInfo, #botonesControles').hide();
    }
}

/**
 * Actualiza el estado del botón aplicar utilidad según validación de formulario
 */
function actualizarEstadoBotonAplicar() {
    const categoriaId = $('#categoria_id').val();
    const itemsSeleccionados = $('.item-checkbox:checked');
    const tipo = $('#utilidad_tipo').val();
    const valor = $('#utilidad_valor').val();

    // Verificar que se hayan seleccionado items y demás campos
    const formularioValido = categoriaId && itemsSeleccionados.length > 0 && tipo && valor;

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
    const itemsSeleccionados = $('.item-checkbox:checked');
    const tipo = $('#utilidad_tipo').val();
    const valor = parseFloat($('#utilidad_valor').val());

    // Obtener nombre de la categoría
    const categoria = categoriasDisponibles.find(c => c.id == categoriaId);
    const categoriaNombre = categoria ? categoria.nombre : 'Categoría seleccionada';

    // Obtener nombres de los items seleccionados
    const itemsNombres = [];
    itemsSeleccionados.each(function() {
        const label = $(`label[for="${this.id}"]`).text();
        itemsNombres.push(label);
    });

    const tipoTexto = tipo === 'porcentaje' ? `${valor}%` : `$${valor.toLocaleString()}`;

    let texto = '';
    if (itemsNombres.length === 1) {
        texto = `Se aplicará una utilidad de ${tipoTexto} a los productos de categoría "<strong>${categoriaNombre}</strong>" y item propio "<strong>${itemsNombres[0]}</strong>"`;
    } else {
        texto = `Se aplicará una utilidad de ${tipoTexto} a los productos de categoría "<strong>${categoriaNombre}</strong>" y a ${itemsNombres.length} items propios seleccionados:`;
        texto += '<ul class="mt-2 mb-0">';
        itemsNombres.forEach(nombre => {
            texto += `<li>${nombre}</li>`;
        });
        texto += '</ul>';
    }

    $('#textoResumen').html(texto);
    $('#resumenUtilidad').removeClass('d-none');
}

/**
 * Aplica la utilidad
 */
async function aplicarUtilidad() {
    try {
        $('#btnAplicarUtilidad').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Aplicando...');

        // Obtener datos del formulario
        const categoriaId = $('#categoria_id').val();
        const nombreCategoria = $('#categoria_id option:selected').text();
        const itemsSeleccionados = $('.item-checkbox:checked');
        const tipo = $('#utilidad_tipo').val();
        const valor = $('#utilidad_valor').val();

        // Preparar array de items seleccionados
        const itemsIds = [];
        itemsSeleccionados.each(function() {
            itemsIds.push(this.value);
        });

        // Crear formData con múltiples items
        const formData = new FormData();
        formData.append('cotizacion_id', cotizacionIdActual);
        formData.append('categoria_id', categoriaId);
        formData.append('tipo', tipo);
        formData.append('valor', valor);

        // Agregar cada item seleccionado como array
        itemsIds.forEach(itemId => {
            if (nombreCategoria=='Nomina' || nombreCategoria=='NOMINA' || nombreCategoria=='Nómina' || nombreCategoria=='NOM' || nombreCategoria=='Seguridad Social' || nombreCategoria=='Parafiscales' || nombreCategoria=='Prestaciones Sociales') {
                formData.append('cargo_ids[]', itemId);
            }else{
                formData.append('item_propio_ids[]', itemId);
            }
        });

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

            // Forzar actualización de totales múltiple
            setTimeout(async () => {
                await actualizarTotalesEnInterfaz();

                // También actualizar el sticky si existe
                if (typeof actualizarStickyAhora === 'function') {
                    actualizarStickyAhora();
                }

                // Mostrar mensaje de confirmación detallado
                toastr.success(`💰 Margen de utilidad aplicado a ${itemsIds.length} item(s)<br>📊 Descuentos e impuestos recalculados automáticamente`, '', {
                    timeOut: 4000,
                    allowHtml: true
                });
            }, 1000);

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
            console.log('📞 Llamando actualizarTotalesCotizacion...');
            await actualizarTotalesCotizacion();
        }

        if (typeof actualizarTotalesCompletos === 'function') {
            console.log('📞 Llamando actualizarTotalesCompletos...');
            await actualizarTotalesCompletos();
        }

        if (typeof cargarProductosGuardados === 'function') {
            console.log('📞 Llamando cargarProductosGuardados...');
            await cargarProductosGuardados();
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
 * Elimina una utilidad
 */
async function eliminarUtilidad(id) {
    // Mostrar SweetAlert de confirmación
    const result = await Swal.fire({
        title: '¿Está seguro?',
        text: 'Esta acción eliminará la utilidad aplicada y recalculará los totales automáticamente.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
        cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
        reverseButtons: true,
        allowOutsideClick: false,
        allowEscapeKey: false
    });

    console.log('result>>>>>>>>>>>>>', result);

    // Si el usuario cancela, no hacer nada
    if (!result.value) {
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
            // Mostrar SweetAlert de éxito
            await Swal.fire({
                title: '¡Eliminado!',
                text: 'La utilidad ha sido eliminada correctamente.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                allowOutsideClick: false
            });

            cargarUtilidadesExistentes();

            // Forzar actualización de totales múltiple
            setTimeout(async () => {
                await actualizarTotalesEnInterfaz();

                // También actualizar el sticky si existe
                if (typeof actualizarStickyAhora === 'function') {
                    actualizarStickyAhora();
                }

                // Mostrar mensaje de confirmación
                toastr.success('💰 Utilidad eliminada<br>📊 Descuentos e impuestos recalculados automáticamente', '', {
                    timeOut: 4000,
                    allowHtml: true
                });
            }, 1000);

        } else {
            // Mostrar SweetAlert de error
            await Swal.fire({
                title: 'Error',
                text: 'No se pudo eliminar la utilidad. Inténtelo nuevamente.',
                icon: 'error',
                confirmButtonText: 'Entendido',
                allowOutsideClick: false
            });
        }
    } catch (error) {
        console.error('Error eliminando utilidad:', error);

        // Mostrar SweetAlert de error técnico
        await Swal.fire({
            title: 'Error técnico',
            text: 'Ocurrió un problema al comunicarse con el servidor. Verifique su conexión e inténtelo nuevamente.',
            icon: 'error',
            confirmButtonText: 'Entendido',
            allowOutsideClick: false
        });
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
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarUtilidad(${utilidad.id})" title="Eliminar utilidad">
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
            fetch(`/admin/admin.cotizaciones.utilidades.obtener/${cotizacionIdActual}`, {
                headers: { 'Accept': 'application/json' }
            }).then(r => r.json()).then(data => {
                if (data.success && data.data.length > 0) {
                    console.log('  ✅ Utilidades aplicadas:', data.data.length);
                    console.log('  📊 Los descuentos se calculan sobre subtotal + utilidades');
                    data.data.forEach((ut, i) => {
                        console.log(`     - Utilidad ${i + 1}: ${ut.tipo} ${ut.valor}% → $${formatearNumero(ut.valor_calculado || 0)}`);
                    });
                } else {
                    console.log('  ❌ Sin utilidades aplicadas');
                    console.log('  📊 Los descuentos se calculan sobre subtotal base');
                }
            }).catch(e => console.log('  ⚠️ Error verificando utilidades:', e));
        }

        // Test de actualización inmediata
        if (cotizacionIdActual) {
            console.log('🚀 Ejecutando actualización de prueba...');
            actualizarTotalesEnInterfaz().then(() => {
                console.log('✅ Actualización de prueba completada');
            });
        }
    };

    window.actualizarTotalesRapido = function() {
        console.log('⚡ Actualización rápida de totales iniciada...');
        actualizarTotalesEnInterfaz();
    };

    window.testDescuentosConUtilidades = async function() {
        if (!cotizacionIdActual) {
            console.error('❌ No hay cotización ID disponible');
            return;
        }

        try {
            const response = await fetch(`/admin/cotizaciones/totales/obtener?cotizacion_id=${cotizacionIdActual}`, {
                headers: { 'Accept': 'application/json' }
            });

            const data = await response.json();

            if (data.success) {
                if (data.data.detalle && data.data.detalle.utilidades_aplicadas) {
                    data.data.detalle.utilidades_aplicadas.forEach((ut, i) => {
                        console.log(`   - ${i + 1}: ${ut.categoria} | ${ut.tipo} ${ut.valor}% → $${formatearNumero(ut.valor_calculado)}`);
                    });
                } else {
                    console.log('ℹ️ Sin utilidades aplicadas');
                }
            } else {
                console.error('❌ Error obteniendo totales:', data.error || 'Error desconocido');
            }
        } catch (error) {
            console.error('❌ Error en la prueba:', error);
        }
    };

    console.log('Sistema de utilidades cargado. Usar debugUtilidades() para debug.');
});

/**
 * Seleccionar todos los items propios
 */
function seleccionarTodosItems() {
    $('.item-checkbox:visible').prop('checked', true);
    $('.item-card:visible').addClass('selected');
    actualizarEstadoBotonAplicar();
    actualizarContadorSeleccionados();
}

/**
 * Deseleccionar todos los items propios
 */
function deseleccionarTodosItems() {
    $('.item-checkbox').prop('checked', false);
    $('.item-card').removeClass('selected');
    actualizarEstadoBotonAplicar();
    actualizarContadorSeleccionados();
}

/**
 * Seleccionar solo items propios (no cargos)
 */
function seleccionarSoloItemsPropios() {
    $('.item-checkbox').prop('checked', false);
    $('.item-card[data-tipo="item"] .item-checkbox:visible').prop('checked', true);
    $('.item-card').removeClass('selected');
    $('.item-card[data-tipo="item"]:visible').addClass('selected');
    actualizarEstadoBotonAplicar();
    actualizarContadorSeleccionados();
}

/**
 * Seleccionar solo cargos (no items propios)
 */
function seleccionarSoloCargos() {
    $('.item-checkbox').prop('checked', false);
    $('.item-card[data-tipo="cargo"] .item-checkbox:visible').prop('checked', true);
    $('.item-card').removeClass('selected');
    $('.item-card[data-tipo="cargo"]:visible').addClass('selected');
    actualizarEstadoBotonAplicar();
    actualizarContadorSeleccionados();
}

/**
 * Actualizar contador de items seleccionados
 */
function actualizarContadorSeleccionados() {
    const totalSeleccionados = $('.item-checkbox:checked').length;
    const totalVisible = $('.item-checkbox:visible').length;

    $('#numSeleccionados').text(totalSeleccionados);

    const contador = $('#contadorSeleccionados');
    if (totalSeleccionados > 0) {
        contador.removeClass('bg-secondary').addClass('bg-primary');
        contador.find('i').removeClass('fa-minus').addClass('fa-check');
    } else {
        contador.removeClass('bg-primary').addClass('bg-secondary');
        contador.find('i').removeClass('fa-check').addClass('fa-minus');
    }
}

/**
 * Toggle visual de tarjeta seleccionada
 */
function toggleTarjetaSeleccionada(checkbox) {
    const tarjeta = $(checkbox).closest('.item-card');
    if (checkbox.checked) {
        tarjeta.addClass('selected');
    } else {
        tarjeta.removeClass('selected');
    }
}

/**
 * Filtrar items en tiempo real
 */
function filtrarItems() {
    const filtro = $('#filtroItems').val().toLowerCase().trim();
    const tarjetas = $('.item-card');
    let visibles = 0;

    if (!filtro) {
        // Sin filtro - mostrar todo
        tarjetas.removeClass('item-oculto');
        $('.seccion-header').show();
        $('#btnLimpiarFiltro').hide();
        visibles = tarjetas.length;
    } else {
        // Con filtro - filtrar por texto
        $('#btnLimpiarFiltro').show();

        tarjetas.each(function() {
            const tarjeta = $(this);
            const textoSearchable = tarjeta.attr('data-searchable') || '';

            if (textoSearchable.includes(filtro)) {
                tarjeta.removeClass('item-oculto');

                // Resaltar coincidencias
                const nombre = tarjeta.find('.item-nombre');
                const codigo = tarjeta.find('.item-codigo');

                nombre.html(resaltarTexto(nombre.text(), filtro));
                if (codigo.length) {
                    codigo.html(resaltarTexto(codigo.text(), filtro));
                }

                visibles++;
            } else {
                tarjeta.addClass('item-oculto');
            }
        });

        // Mostrar/ocultar headers de sección según contenido
        $('.seccion-header').each(function() {
            const seccion = $(this).attr('data-seccion');
            const tipoTarjeta = seccion === 'items' ? 'item' : 'cargo';
            const tieneVisibles = $(`.item-card[data-tipo="${tipoTarjeta}"]:not(.item-oculto)`).length > 0;
            $(this).toggle(tieneVisibles);
        });
    }

    // Mostrar mensaje si no hay resultados
    if (visibles === 0 && filtro) {
        if ($('.no-resultados').length === 0) {
            $('#items_propios_container').append(`
                <div class="no-resultados">
                    <i class="fas fa-search fa-2x mb-2"></i><br>
                    No se encontraron items que coincidan con "${filtro}"
                </div>
            `);
        }
    } else {
        $('.no-resultados').remove();
    }

    actualizarContadorSeleccionados();
}

/**
 * Resaltar coincidencias en el texto
 */
function resaltarTexto(texto, filtro) {
    if (!filtro) return texto;
    const regex = new RegExp(`(${filtro})`, 'gi');
    return texto.replace(regex, '<span class="filtro-resaltado">$1</span>');
}

/**
 * Limpiar filtro de búsqueda
 */
function limpiarFiltro() {
    $('#filtroItems').val('').trigger('keyup');
}

/**
 * Limpiar formulario y resetear selecciones múltiples
 */
function limpiarFormulario() {
    document.getElementById('formUtilidad').reset();
    $('#items_propios_container').html(`
        <div class="text-muted text-center p-4" id="placeholder_items">
            <i class="fas fa-info-circle fa-2x mb-2"></i><br>
            Primero seleccione una categoría...
        </div>
    `);
    $('#filtroItems, #contadorSeleccionados, #totalItemsInfo, #botonesControles').hide();
    $('#btnLimpiarFiltro').hide();
    $('#resumenUtilidad').addClass('d-none');
    actualizarTipoUtilidad();
}
