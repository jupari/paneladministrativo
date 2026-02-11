/**
 * Script de debugging para modal de clientes
 * Usar en la consola del navegador para diagnosticar problemas
 */

window.debugModal = function() {
    console.log('🔍 === DEBUG MODAL DE CLIENTES ===');

    // Verificar elementos básicos
    const modal = document.getElementById('ModalCliente');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const finishBtn = document.getElementById('finish-btn');

    console.log('📋 Elementos del modal:');
    console.log('- Modal:', modal ? '✅ Encontrado' : '❌ No encontrado');
    console.log('- Botón Anterior:', prevBtn ? '✅ Encontrado' : '❌ No encontrado');
    console.log('- Botón Siguiente:', nextBtn ? '✅ Encontrado' : '❌ No encontrado');
    console.log('- Botón Finalizar:', finishBtn ? '✅ Encontrado' : '❌ No encontrado');

    // Verificar funciones
    console.log('🛠️ Funciones disponibles:');
    console.log('- ClienteModalSteps:', typeof ClienteModalSteps !== 'undefined' ? '✅ Disponible' : '❌ No disponible');
    console.log('- window.clienteModalSteps:', window.clienteModalSteps ? '✅ Instanciado' : '❌ No instanciado');
    console.log('- nextStepHandler:', typeof nextStepHandler !== 'undefined' ? '✅ Disponible' : '❌ No disponible');
    console.log('- prevStepHandler:', typeof prevStepHandler !== 'undefined' ? '✅ Disponible' : '❌ No disponible');
    console.log('- registerCliWithFeedback:', typeof registerCliWithFeedback !== 'undefined' ? '✅ Disponible' : '❌ No disponible');

    // Verificar dependencias
    console.log('🔗 Dependencias:');
    console.log('- jQuery:', typeof $ !== 'undefined' ? '✅ Disponible' : '❌ No disponible');
    console.log('- Bootstrap:', typeof $.fn.modal !== 'undefined' ? '✅ Disponible' : '❌ No disponible');
    console.log('- Toastr:', typeof toastr !== 'undefined' ? '✅ Disponible' : '❌ No disponible');

    // Verificar event listeners
    if (nextBtn) {
        const onclick = nextBtn.getAttribute('onclick');
        console.log('- Next Button onclick:', onclick || 'No onclick');
    }

    if (prevBtn) {
        const onclick = prevBtn.getAttribute('onclick');
        console.log('- Prev Button onclick:', onclick || 'No onclick');
    }

    // Estado actual
    if (window.clienteModalSteps) {
        console.log('📊 Estado actual:');
        console.log('- Paso actual:', window.clienteModalSteps.currentStep);
        console.log('- Total pasos:', window.clienteModalSteps.totalSteps);
    }

    // Probar funciones
    console.log('🧪 Pruebas automáticas:');

    try {
        if (typeof nextStepHandler === 'function') {
            console.log('- nextStepHandler: ✅ Es función');
        }
    } catch (e) {
        console.log('- nextStepHandler: ❌ Error:', e.message);
    }

    try {
        if (typeof prevStepHandler === 'function') {
            console.log('- prevStepHandler: ✅ Es función');
        }
    } catch (e) {
        console.log('- prevStepHandler: ❌ Error:', e.message);
    }

    console.log('🔍 === FIN DEBUG ===');
};

// Auto-ejecutar cuando el DOM esté listo
$(document).ready(function() {
    console.log('🚀 Debug modal cargado. Usar debugModal() para diagnóstico.');

    // Test inmediato de funciones
    setTimeout(() => {
        console.log('🧪 === TEST DE FUNCIONES ===');
        console.log('nextStepHandler:', typeof nextStepHandler === 'function' ? '✅ DISPONIBLE' : '❌ NO DISPONIBLE');
        console.log('prevStepHandler:', typeof prevStepHandler === 'function' ? '✅ DISPONIBLE' : '❌ NO DISPONIBLE');
        console.log('ClienteModalSteps:', typeof ClienteModalSteps === 'function' ? '✅ DISPONIBLE' : '❌ NO DISPONIBLE');
        console.log('window.clienteModalSteps:', window.clienteModalSteps ? '✅ INSTANCIADO' : '❌ NO INSTANCIADO');
        console.log('🧪 === FIN TEST ===');
    }, 500);
});
