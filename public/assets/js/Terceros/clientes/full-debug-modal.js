/**
 * Debugging completo para diagnosticar todos los problemas del modal
 */

console.log('🔧 Full Debug Modal cargado');

// Función de diagnóstico completo
window.fullModalDiagnosis = function() {
    console.log('🔧 === DIAGNÓSTICO COMPLETO DEL MODAL ===');

    // 1. Verificar que el modal existe
    const modal = document.getElementById('ModalCliente');
    console.log('1. Modal DOM:', modal ? '✅ EXISTE' : '❌ NO EXISTE');

    // 2. Verificar botones de navegación
    const nextBtn = document.getElementById('next-btn');
    const prevBtn = document.getElementById('prev-btn');
    console.log('2. Botón siguiente:', nextBtn ? '✅ EXISTE' : '❌ NO EXISTE');
    console.log('2. Botón anterior:', prevBtn ? '✅ EXISTE' : '❌ NO EXISTE');

    if (nextBtn) {
        console.log('   - Next visible:', !nextBtn.classList.contains('d-none') ? '✅' : '❌');
        console.log('   - Next onclick:', nextBtn.onclick ? '✅' : '❌');
    }
    if (prevBtn) {
        console.log('   - Prev visible:', !prevBtn.classList.contains('d-none') ? '✅' : '❌');
        console.log('   - Prev onclick:', prevBtn.onclick ? '✅' : '❌');
    }

    // 3. Verificar campos principales
    const campos = ['tipopersona_id', 'nombres', 'apellidos', 'nombre_establecimiento'];
    console.log('3. Campos del formulario:');
    campos.forEach(id => {
        const element = document.getElementById(id);
        console.log(`   - ${id}:`, element ? '✅ EXISTE' : '❌ NO EXISTE');
        if (element) {
            const formGroup = element.closest('.form-group');
            const isVisible = formGroup && formGroup.style.display !== 'none';
            console.log(`     Visible: ${isVisible ? '✅' : '❌'}`);
        }
    });

    // 4. Verificar funciones globales
    console.log('4. Funciones globales:');
    console.log('   - nextStepHandler:', typeof window.nextStepHandler);
    console.log('   - prevStepHandler:', typeof window.prevStepHandler);
    console.log('   - clienteModalSteps:', typeof window.clienteModalSteps);
    console.log('   - resetModal:', typeof window.resetModal);
    console.log('   - actualizarValidaciones:', typeof actualizarValidaciones);

    // 5. Verificar pasos
    console.log('5. Pasos del modal:');
    for (let i = 1; i <= 4; i++) {
        const step = document.getElementById(`step-${i}`);
        console.log(`   - Paso ${i}:`, step ? '✅ EXISTE' : '❌ NO EXISTE');
        if (step) {
            console.log(`     Visible: ${!step.classList.contains('d-none') ? '✅' : '❌'}`);
        }
    }

    // 6. Verificar instancia clienteModalSteps
    if (window.clienteModalSteps) {
        console.log('6. Estado clienteModalSteps:');
        console.log('   - Paso actual:', window.clienteModalSteps.currentStep);
        console.log('   - Total pasos:', window.clienteModalSteps.totalSteps);
        console.log('   - Modo edición:', window.clienteModalSteps.editMode);
        console.log('   - Navegación libre:', window.clienteModalSteps.allowFreeNavigation);
    } else {
        console.log('6. ❌ clienteModalSteps no existe');
    }

    // 7. Verificar tipo de modal (creación vs edición)
    console.log('7. Detección de modo:');
    const idField = document.getElementById('id');
    const identificacionField = document.getElementById('identificacion');
    const nombresField = document.getElementById('nombres');

    const hasId = idField?.value && idField.value.trim() !== '';
    const hasIdentificacion = identificacionField?.value && identificacionField.value.trim() !== '';
    const hasNombres = nombresField?.value && nombresField.value.trim() !== '';

    console.log(`   - Campo ID: "${idField?.value || ''}" - tiene valor: ${hasId}`);
    console.log(`   - Campo Identificación: "${identificacionField?.value || ''}" - tiene valor: ${hasIdentificacion}`);
    console.log(`   - Campo Nombres: "${nombresField?.value || ''}" - tiene valor: ${hasNombres}`);
    console.log(`   - Modo detectado: ${hasId || hasIdentificacion || hasNombres ? 'EDICIÓN' : 'CREACIÓN'}`);

    console.log('🔧 === FIN DIAGNÓSTICO COMPLETO ===');
};

// Función para forzar la inicialización del modal
window.forceModalInit = function() {
    console.log('🔧 Forzando inicialización del modal...');

    try {
        // Verificar que la clase esté disponible
        if (typeof ClienteModalSteps === 'undefined') {
            console.error('❌ ClienteModalSteps no está definida - problema de carga de script');
            return;
        }

        // Crear nueva instancia si no existe
        if (!window.clienteModalSteps) {
            window.clienteModalSteps = new ClienteModalSteps();
            console.log('✅ Nueva instancia ClienteModalSteps creada');
        } else {
            console.log('✅ Instancia ClienteModalSteps ya existe');
        }

        // Forzar actualización de botones
        if (window.clienteModalSteps && typeof window.clienteModalSteps.ensureNavigationButtons === 'function') {
            window.clienteModalSteps.ensureNavigationButtons();
            console.log('✅ Botones de navegación verificados');
        }

        if (window.clienteModalSteps && typeof window.clienteModalSteps.updateNavigationButtons === 'function') {
            window.clienteModalSteps.updateNavigationButtons();
            console.log('✅ Botones de navegación actualizados');
        }

        // Forzar actualización de validaciones
        setTimeout(() => {
            if (typeof actualizarValidaciones === 'function') {
                actualizarValidaciones();
                console.log('✅ Validaciones actualizadas');
            }
        }, 100);

    } catch (error) {
        console.error('❌ Error en inicialización forzada:', error);
        console.error('Stack:', error.stack);
    }
};

// Auto-ejecutar diagnóstico cuando se abra el modal
$(document).on('shown.bs.modal', '#ModalCliente', function() {
    console.log('🎯 Modal abierto - ejecutando diagnóstico completo...');
    setTimeout(() => {
        window.fullModalDiagnosis();
        window.forceModalInit();
    }, 500);
});

// También ejecutar al cargar la página
$(document).ready(function() {
    console.log('✅ Full Debug Modal listo');

    // Ejecutar diagnóstico inicial después de un delay
    setTimeout(() => {
        console.log('🔧 Ejecutando diagnóstico inicial...');
        window.fullModalDiagnosis();
    }, 2000);
});
