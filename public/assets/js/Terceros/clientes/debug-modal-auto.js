/**
 * Debugging automático para modal de clientes
 */

console.log('🚀 Iniciando debugging automático del modal...');

// Función para debug automático
window.autoDebugModal = function() {
    setTimeout(() => {
        console.log('🔍 === AUTO DEBUG MODAL ===');

        // Verificar que el modal existe
        const modal = document.getElementById('modalClientesSimple');
        console.log('Modal DOM element:', modal ? '✅ EXISTE' : '❌ NO EXISTE');

        if (modal) {
            console.log('Modal visible:', !modal.classList.contains('d-none') ? '✅ VISIBLE' : '❌ OCULTO');
            console.log('Modal clases:', modal.className);
        }

        // Verificar clienteModalSteps
        console.log('clienteModalSteps global:', window.clienteModalSteps ? '✅ EXISTE' : '❌ NO EXISTE');

        if (window.clienteModalSteps) {
            console.log('Paso actual:', window.clienteModalSteps.currentStep);
            console.log('Total pasos:', window.clienteModalSteps.totalSteps);
        }

        // Verificar pasos
        for (let i = 1; i <= 4; i++) {
            const stepElement = document.getElementById(`step-${i}`);
            console.log(`Paso ${i}:`, stepElement ? '✅ EXISTE' : '❌ NO EXISTE');
            if (stepElement) {
                console.log(`  Visible: ${!stepElement.classList.contains('d-none') ? '✅' : '❌'}`);
            }
        }

        // Verificar botones
        const nextBtn = document.querySelector('.btn-next-step');
        const prevBtn = document.querySelector('.btn-prev-step');
        console.log('Botón siguiente:', nextBtn ? '✅ EXISTE' : '❌ NO EXISTE');
        console.log('Botón anterior:', prevBtn ? '✅ EXISTE' : '❌ NO EXISTE');

        if (nextBtn) {
            console.log('Next button onclick:', nextBtn.onclick);
            console.log('Next button disabled:', nextBtn.disabled);
        }

        if (prevBtn) {
            console.log('Prev button onclick:', prevBtn.onclick);
            console.log('Prev button disabled:', prevBtn.disabled);
        }

        // Verificar handlers globales
        console.log('nextStepHandler global:', typeof window.nextStepHandler);
        console.log('prevStepHandler global:', typeof window.prevStepHandler);

        console.log('🔍 === FIN AUTO DEBUG ===');
    }, 1000);
};

// Ejecutar debug automático cuando la página cargue
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.autoDebugModal);
} else {
    window.autoDebugModal();
}

// También ejecutar cuando se abra el modal
$(document).on('shown.bs.modal', '#modalClientesSimple', function() {
    console.log('🎯 Modal mostrado - ejecutando debug...');
    window.autoDebugModal();

    // Debug adicional para elementos DOM requeridos
    window.debugDOMElements();
});

// Función para debuggear elementos DOM específicos
window.debugDOMElements = function() {
    console.log('🔍 === DEBUG ELEMENTOS DOM ===');

    const elementos = [
        'tipopersona_id',
        'nombres',
        'apellidos',
        'nombre_establecimiento'
    ];

    elementos.forEach(id => {
        const element = document.getElementById(id);
        console.log(`${id}:`, element ? '✅ EXISTE' : '❌ NO EXISTE');

        if (element) {
            const formGroup = element.closest('.form-group');
            console.log(`  form-group container:`, formGroup ? '✅ EXISTE' : '❌ NO EXISTE');
        }
    });

    console.log('🔍 === FIN DEBUG ELEMENTOS ===');
};
