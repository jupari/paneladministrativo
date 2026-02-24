/**
 * Funciones base y configuración inicial para documento de cotización
 * Maneja variables globales, funciones básicas y configuración inicial
 */

// Variables globales de la aplicación
let variable = null;
let clientes = [];
let estados = [];
let consecutivo = '';
let cotizacion = null;

/**
 * Inicializar configuración de la aplicación
 * @param {object} config - Configuración desde el servidor
 */
function inicializarConfiguracion(config) {
    variable = config.variable;

    if (variable === 'crear') {
        document.title = 'Crear Cotización';
        clientes = config.clientes || [];
        estados = config.estados || [];
        consecutivo = config.consecutivo || '';
    } else if (variable === 'editar') {
        document.title = 'Editar Cotización';
        clientes = config.clientes || [];
        estados = config.estados || [];
        cotizacion = config.cotizacion || null;
        consecutivo = cotizacion ? cotizacion.num_documento : '';
        showSkeleton();
    } else if (variable === 'ver') {
        document.title = 'Ver Cotización';
        clientes = config.clientes || [];
        estados = config.estados || [];
        cotizacion = config.cotizacion || null;
        consecutivo = cotizacion ? cotizacion.num_documento : '';
        showSkeleton();
    }
}

/**
 * Calcular total de items (solo conteo)
 */
function calcularTotalItems() {
    try {
        const tabla = document.getElementById('tbody_items');
        const rows = tabla ? tabla.querySelectorAll('tr:not(#no_items_row)') : [];
        const contador = document.getElementById('total_items_count');

        if (contador) {
            contador.textContent = rows.length;
        }
    } catch (error) {
        console.error('Error al calcular total de items:', error);
    }
}

/**
 * Actualizar contador de caracteres en textarea de observación
 */
function actualizarContadorObservacion() {
    try {
        const textarea = document.getElementById('subitem_observacion');
        const contador = document.getElementById('subitem_observacion_count');

        if (textarea && contador) {
            contador.textContent = textarea.value.length;
        }
    } catch (error) {
        console.error('Error al actualizar contador de observación:', error);
    }
}

/**
 * Limpiar todos los items con confirmación
 */
function limpiarTodosItems() {
    if (confirm('¿Está seguro de que desea eliminar todos los items?')) {
        try {
            // Esta función debería estar en documento.js
            if (typeof limpiarTodosItemsCompleto === 'function') {
                limpiarTodosItemsCompleto();
            } else {
                console.warn('Función limpiarTodosItemsCompleto no encontrada en documento.js');
                // Implementación básica de fallback
                const tabla = document.getElementById('tbody_items');
                if (tabla) {
                    tabla.innerHTML = '<tr id="no_items_row"><td colspan="100%" class="text-center text-muted">No hay items agregados</td></tr>';
                }
                calcularTotalItems();
            }
        } catch (error) {
            console.error('Error al limpiar todos los items:', error);
        }
    }
}

/**
 * Toggle para seleccionar/deseleccionar todos los items
 */
function toggleSelectAllItems() {
    try {
        const selectAll = document.getElementById('select_all_items');
        const checkboxes = document.querySelectorAll('.item-checkbox');

        if (selectAll && checkboxes.length > 0) {
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });

            // Actualizar estado del botón eliminar
            if (typeof toggleEliminarItemsSeleccionados === 'function') {
                toggleEliminarItemsSeleccionados();
            }
        }
    } catch (error) {
        console.error('Error al toggle select all items:', error);
    }
}

/**
 * Mostrar skeleton loader
 */
function showSkeleton() {
    try {
        const skeletonLoader = document.getElementById('skeleton-loader');
        const mainContent = document.getElementById('main-content');

        if (skeletonLoader) skeletonLoader.style.display = 'block';
        if (mainContent) mainContent.style.display = 'none';
    } catch (error) {
        console.error('Error al mostrar skeleton:', error);
    }
}

/**
 * Ocultar skeleton loader
 */
function hideSkeleton() {
    try {
        const skeletonLoader = document.getElementById('skeleton-loader');
        const mainContent = document.getElementById('main-content');

        if (skeletonLoader) skeletonLoader.style.display = 'none';
        if (mainContent) mainContent.style.display = 'block';
    } catch (error) {
        console.error('Error al ocultar skeleton:', error);
    }
}

/**
 * Mostrar estado de carga
 */
function showLoadingState() {
    try {
        const mainContent = document.getElementById('main-content');
        if (mainContent) {
            mainContent.classList.add('loading');
        }
    } catch (error) {
        console.error('Error al mostrar loading state:', error);
    }
}

/**
 * Ocultar estado de carga
 */
function hideLoadingState() {
    try {
        const mainContent = document.getElementById('main-content');
        if (mainContent) {
            mainContent.classList.remove('loading');
        }
    } catch (error) {
        console.error('Error al ocultar loading state:', error);
    }
}

// Exportar funciones para uso global
window.cotizacionApp = {
    inicializarConfiguracion,
    calcularTotalItems,
    actualizarContadorObservacion,
    limpiarTodosItems,
    toggleSelectAllItems,
    showSkeleton,
    hideSkeleton,
    showLoadingState,
    hideLoadingState,
    // Variables globales
    getVariable: () => variable,
    getClientes: () => clientes,
    getEstados: () => estados,
    getConsecutivo: () => consecutivo,
    getCotizacion: () => cotizacion
};
