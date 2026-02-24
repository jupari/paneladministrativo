/**
 * Sistema de Resumen Sticky para Cotizaciones
 * Maneja la actualización y sincronización del resumen financiero flotante
 */

// Variables para controlar actualizaciones
let stickyInterval = null;
let intentosActualizacion = 0;
let maxIntentos = 10;

/**
 * Debug de elementos del sistema sticky
 */
function debugElementos() {

    // Elementos hidden
    const subtotalHidden = document.getElementById('subtotal');
    const descuentoHidden = document.getElementById('descuento');
    const impuestoHidden = document.getElementById('total_impuesto');
    const totalHidden = document.getElementById('total');

    // console.log('Elementos Hidden:', {
    //     subtotal: subtotalHidden ? subtotalHidden.value : 'NO ENCONTRADO',
    //     descuento: descuentoHidden ? descuentoHidden.value : 'NO ENCONTRADO',
    //     impuesto: impuestoHidden ? impuestoHidden.value : 'NO ENCONTRADO',
    //     total: totalHidden ? totalHidden.value : 'NO ENCONTRADO'
    // });

    // Elementos display
    const displaySubtotal = document.getElementById('display-subtotal-valor');
    const displayDescuento = document.getElementById('display-descuento-valor');
    const displayImpuesto = document.getElementById('display-impuesto-valor');
    const displayTotal = document.getElementById('display-total-valor');

    // console.log('Elementos Display:', {
    //     subtotal: displaySubtotal ? displaySubtotal.textContent : 'NO ENCONTRADO',
    //     descuento: displayDescuento ? displayDescuento.textContent : 'NO ENCONTRADO',
    //     impuesto: displayImpuesto ? displayImpuesto.textContent : 'NO ENCONTRADO',
    //     total: displayTotal ? displayTotal.textContent : 'NO ENCONTRADO'
    // });

    // Elementos sticky
    const stickySubtotal = document.getElementById('sticky-subtotal');
    const stickyDescuentos = document.getElementById('sticky-descuentos');
    const stickyImpuestos = document.getElementById('sticky-impuestos');
    const stickyTotal = document.getElementById('sticky-total');

    // console.log('Elementos Sticky:', {
    //     subtotal: stickySubtotal ? stickySubtotal.textContent : 'NO ENCONTRADO',
    //     descuento: stickyDescuentos ? stickyDescuentos.textContent : 'NO ENCONTRADO',
    //     impuesto: stickyImpuestos ? stickyImpuestos.textContent : 'NO ENCONTRADO',
    //     total: stickyTotal ? stickyTotal.textContent : 'NO ENCONTRADO'
    // });

}

/**
 * Parsear formato de moneda colombiano a número
 * @param {string} text - Texto con formato 2.142.500,00
 * @returns {number} - Número parseado
 */
function parseColombianCurrency(text) {
    if (!text || text === '') return 0;

    // Limpiar todo excepto números, puntos y comas
    let clean = text.replace(/[^0-9.,]/g, '');

    // Si tiene coma, es decimal colombiano
    if (clean.includes(',')) {
        // Dividir en parte entera y decimal
        let parts = clean.split(',');
        let wholePart = parts[0].replace(/\./g, ''); // Quitar puntos (separadores de miles)
        let decimalPart = parts[1] || '00';
        return parseFloat(wholePart + '.' + decimalPart);
    }

    // Si solo tiene puntos, quitar para números enteros grandes
    if (clean.includes('.')) {
        let dotCount = (clean.match(/\./g) || []).length;
        if (dotCount > 1) {
            // Múltiples puntos = separadores de miles
            return parseFloat(clean.replace(/\./g, ''));
        }
        // Un solo punto podría ser decimal
        return parseFloat(clean);
    }

    return parseFloat(clean) || 0;
}

/**
 * Formatear número como moneda colombiana
 * @param {number} amount - Cantidad a formatear
 * @returns {string} - Texto formateado
 */
function formatCurrency(amount) {
    const num = Number(amount) || 0;
    // Para números grandes sin decimales, no usar fractions
    if (num >= 1000 && num % 1 === 0) {
        return '$' + num.toLocaleString('es-CO');
    }
    // Para números pequeños o con decimales, usar 2 decimales
    return '$' + num.toLocaleString('es-CO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/**
 * Actualizar el resumen sticky inmediatamente
 */
function actualizarStickyAhora() {
    debugElementos(); // Primero hacer debug

    // Función simple para actualizar inmediatamente
    const subtotalElement = document.getElementById('subtotal');
    const descuentoElement = document.getElementById('descuento');
    const impuestoElement = document.getElementById('total_impuesto');
    const totalElement = document.getElementById('total');

    const subtotal = subtotalElement ? parseFloat(subtotalElement.value) || 0 : 0;
    const descuento = descuentoElement ? parseFloat(descuentoElement.value) || 0 : 0;
    const impuesto = impuestoElement ? parseFloat(impuestoElement.value) || 0 : 0;
    const total = totalElement ? parseFloat(totalElement.value) || 0 : 0;

    // También intentar leer desde los elementos display
    const displaySubtotal = document.getElementById('display-subtotal-valor');
    const displayDescuento = document.getElementById('display-descuento-valor');
    const displayImpuesto = document.getElementById('display-impuesto-valor');
    const displayTotal = document.getElementById('display-total-valor');

    let finalSubtotal = subtotal;
    let finalDescuento = descuento;
    let finalImpuesto = impuesto;
    let finalTotal = total;

    if (displaySubtotal && displaySubtotal.textContent !== '$0.00') {
        const cleanText = displaySubtotal.textContent.replace(/[$\s]/g, '').trim();
        finalSubtotal = parseColombianCurrency(cleanText) || subtotal;
    }
    if (displayDescuento && displayDescuento.textContent !== '$0.00') {
        const cleanText = displayDescuento.textContent.replace(/[$\s]/g, '').trim();
        finalDescuento = parseColombianCurrency(cleanText) || descuento;
    }
    if (displayImpuesto && displayImpuesto.textContent !== '$0.00') {
        const cleanText = displayImpuesto.textContent.replace(/[$\s]/g, '').trim();
        finalImpuesto = parseColombianCurrency(cleanText) || impuesto;
    }
    if (displayTotal && displayTotal.textContent !== '$0.00') {
        const cleanText = displayTotal.textContent.replace(/[$\s]/g, '').trim();
        finalTotal = parseColombianCurrency(cleanText) || total;
    }

    // Actualizar sticky
    const stickySubtotal = document.getElementById('sticky-subtotal');
    const stickyDescuentos = document.getElementById('sticky-descuentos');
    const stickyImpuestos = document.getElementById('sticky-impuestos');
    const stickyTotal = document.getElementById('sticky-total');

    if (stickySubtotal) stickySubtotal.textContent = formatCurrency(finalSubtotal);
    if (stickyDescuentos) stickyDescuentos.textContent = formatCurrency(finalDescuento);
    if (stickyImpuestos) stickyImpuestos.textContent = formatCurrency(finalImpuesto);
    if (stickyTotal) stickyTotal.textContent = formatCurrency(finalTotal);

}

/**
 * Iniciar sistema de actualizaciones inteligente
 */
function iniciarActualizacionSticky() {
    if (stickyInterval) {
        clearInterval(stickyInterval);
    }

    intentosActualizacion = 0;
    stickyInterval = setInterval(() => {
        actualizarStickyAhora();

        // Verificar si los valores ya no son ceros
        const stickySubtotal = document.getElementById('sticky-subtotal');
        const valorActual = stickySubtotal ? stickySubtotal.textContent : '$0,00';

        if (valorActual !== '$0,00' && valorActual !== '$0.00') {
            clearInterval(stickyInterval);
            stickyInterval = null;
        }

        // Parar después de intentos máximos
        intentosActualizacion++;
        if (intentosActualizacion >= maxIntentos) {
            clearInterval(stickyInterval);
            stickyInterval = null;
        }
    }, 2000);
}

// Función global para detener todas las actualizaciones sticky
window.detenerActualizacionesSticky = function() {
    if (stickyInterval) {
        clearInterval(stickyInterval);
        stickyInterval = null;
    }
};

// Función global para reiniciar actualizaciones si es necesario
window.reiniciarActualizacionesSticky = function() {
    iniciarActualizacionSticky();
};

// NOTA: La inicialización automática ha sido deshabilitada
// Ahora es manejada por documento-coordinator.js para evitar conflictos
// Las funciones están disponibles para ser llamadas en el momento correcto
