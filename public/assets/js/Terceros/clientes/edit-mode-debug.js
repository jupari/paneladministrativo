/**
 * Debugging específico para problemas de modo edición
 */

console.log('🔧 Edit Mode Debug cargado');

// Función para diagnosticar específicamente el modo edición
window.debugEditMode = function() {
    console.log('🔍 === DEBUG MODO EDICIÓN ===');

    // 1. Verificar campos que determinan modo edición
    const idField = document.getElementById('id');
    const identificacionField = document.getElementById('identificacion');
    const nombresField = document.getElementById('nombres');

    console.log('1. Campos de detección de modo edición:');
    console.log(`   - id: "${idField?.value || ''}" (length: ${(idField?.value || '').length})`);
    console.log(`   - identificacion: "${identificacionField?.value || ''}" (length: ${(identificacionField?.value || '').length})`);
    console.log(`   - nombres: "${nombresField?.value || ''}" (length: ${(nombresField?.value || '').length})`);

    // 2. Verificar estado de botones específicamente
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');

    console.log('2. Estado detallado de botones:');
    if (prevBtn) {
        console.log('   - prev-btn:');
        console.log(`     * Existe: ✅`);
        console.log(`     * Classes: "${prevBtn.className}"`);
        console.log(`     * Display: "${prevBtn.style.display}"`);
        console.log(`     * Visible: ${!prevBtn.classList.contains('d-none') ? '✅' : '❌'}`);
        console.log(`     * Onclick: ${prevBtn.onclick ? '✅' : '❌'}`);
    } else {
        console.log('   - prev-btn: ❌ NO EXISTE');
    }

    if (nextBtn) {
        console.log('   - next-btn:');
        console.log(`     * Existe: ✅`);
        console.log(`     * Classes: "${nextBtn.className}"`);
        console.log(`     * Display: "${nextBtn.style.display}"`);
        console.log(`     * Visible: ${!nextBtn.classList.contains('d-none') ? '✅' : '❌'}`);
        console.log(`     * Onclick: ${nextBtn.onclick ? '✅' : '❌'}`);
    } else {
        console.log('   - next-btn: ❌ NO EXISTE');
    }

    // 3. Verificar campo nombre_establecimiento
    const nombreEstField = document.getElementById('nombre_establecimiento');
    console.log('3. Campo nombre_establecimiento:');
    if (nombreEstField) {
        const formGroup = nombreEstField.closest('.form-group');
        console.log(`   - Existe: ✅`);
        console.log(`   - Valor: "${nombreEstField.value}"`);
        console.log(`   - Display: "${nombreEstField.style.display}"`);
        console.log(`   - Form-group display: "${formGroup ? formGroup.style.display : 'N/A'}"`);
        console.log(`   - Visible: ${formGroup && formGroup.style.display !== 'none' ? '✅' : '❌'}`);
    } else {
        console.log('   - ❌ NO EXISTE');
    }

    // 4. Verificar tipo de persona
    const tipoPersonaSelect = document.getElementById('tipopersona_id');
    console.log('4. Tipo de persona:');
    if (tipoPersonaSelect) {
        const selectedOption = tipoPersonaSelect.options[tipoPersonaSelect.selectedIndex];
        console.log(`   - Existe: ✅`);
        console.log(`   - Valor seleccionado: "${tipoPersonaSelect.value}"`);
        console.log(`   - Texto seleccionado: "${selectedOption ? selectedOption.text : 'N/A'}"`);
        console.log(`   - Es jurídica: ${selectedOption && selectedOption.text.toLowerCase().includes('jurídica') ? '✅' : '❌'}`);
    } else {
        console.log('   - ❌ NO EXISTE');
    }

    console.log('🔍 === FIN DEBUG MODO EDICIÓN ===');
};

// Función para forzar visibilidad de elementos
window.forceShowElements = function() {
    console.log('💪 Forzando visibilidad de elementos...');

    // Forzar botones visibles
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');

    if (prevBtn) {
        prevBtn.classList.remove('d-none');
        prevBtn.style.display = '';
        console.log('✅ prev-btn forzado a visible');
    }

    if (nextBtn) {
        nextBtn.classList.remove('d-none');
        nextBtn.style.display = '';
        console.log('✅ next-btn forzado a visible');
    }

    // Forzar creación de botones si no existen
    if ((!prevBtn || !nextBtn) && window.clienteModalSteps) {
        console.log('🚑 Intentando recrear botones...');
        window.clienteModalSteps.ensureNavigationButtons();
    }

    // Forzar mostrar campo nombre_establecimiento si los datos indican persona jurídica
    const tipoPersonaSelect = document.getElementById('tipopersona_id');
    const nombreEstField = document.getElementById('nombre_establecimiento');

    if (tipoPersonaSelect && nombreEstField) {
        const selectedOption = tipoPersonaSelect.options[tipoPersonaSelect.selectedIndex];
        const isJuridica = selectedOption && selectedOption.text.toLowerCase().includes('jurídica');
        const nombreEstGroup = nombreEstField.closest('.form-group');

        console.log(`💪 Tipo persona: ${selectedOption?.text}, Es jurídica: ${isJuridica}`);

        if (isJuridica && nombreEstGroup) {
            nombreEstGroup.style.display = 'block';
            nombreEstField.setAttribute('required', 'required');
            console.log('✅ Forzando nombre_establecimiento visible para persona jurídica');
        }
    }

    // Forzar actualizarValidaciones con logging mejorado
    if (typeof actualizarValidaciones === 'function') {
        console.log('💪 Ejecutando actualizarValidaciones...');
        try {
            actualizarValidaciones();
            console.log('✅ actualizarValidaciones ejecutada correctamente');
        } catch (error) {
            console.error('❌ Error en actualizarValidaciones:', error);
        }
    }

    console.log('💪 Forzado de visibilidad completo');
};

// Auto-ejecutar debug cuando se detecte modo edición
$(document).on('shown.bs.modal', '#ModalCliente', function() {
    setTimeout(() => {
        const idField = document.getElementById('id');
        const isEdit = idField && idField.value && idField.value.trim() !== '';

        if (isEdit) {
            console.log('🔍 Modo edición detectado - ejecutando debug automático...');
            window.debugEditMode();

            // Auto-forzar elementos después de un delay
            setTimeout(() => {
                console.log('💪 Auto-forzando elementos para modo edición...');
                window.forceShowElements();

                // Debug final después de forzar
                setTimeout(() => {
                    window.debugEditMode();
                }, 200);
            }, 500);
        }
    }, 800);
});

// Función para debuggear limpieza de validaciones
window.debugValidationCleanup = function() {
    console.log('🔍 === DEBUG LIMPIEZA DE VALIDACIONES ===');

    // Contar elementos con clases de validación
    const validFields = document.querySelectorAll('#ModalCliente .is-valid');
    const invalidFields = document.querySelectorAll('#ModalCliente .is-invalid');
    const wasValidatedFields = document.querySelectorAll('#ModalCliente .was-validated');
    const borderSuccessFields = document.querySelectorAll('#ModalCliente .border-success');
    const borderDangerFields = document.querySelectorAll('#ModalCliente .border-danger');
    const errorSpans = document.querySelectorAll('#ModalCliente [id^="error_"]:not(:empty)');

    console.log('1. Estado de validaciones:');
    console.log(`   - Campos .is-valid: ${validFields.length}`);
    console.log(`   - Campos .is-invalid: ${invalidFields.length}`);
    console.log(`   - Campos .was-validated: ${wasValidatedFields.length}`);
    console.log(`   - Campos .border-success: ${borderSuccessFields.length}`);
    console.log(`   - Campos .border-danger: ${borderDangerFields.length}`);
    console.log(`   - Error spans con contenido: ${errorSpans.length}`);

    if (validFields.length > 0) {
        console.log('   ⚠️ Campos con .is-valid restantes:');
        validFields.forEach(field => {
            console.log(`     - ${field.id || field.name || 'sin id'}: "${field.value}" - clases: ${field.className}`);
        });
    }

    if (invalidFields.length > 0) {
        console.log('   ⚠️ Campos con .is-invalid restantes:');
        invalidFields.forEach(field => {
            console.log(`     - ${field.id || field.name || 'sin id'}: "${field.value}" - clases: ${field.className}`);
        });
    }

    if (borderSuccessFields.length > 0) {
        console.log('   ⚠️ Campos con .border-success restantes:');
        borderSuccessFields.forEach(field => {
            console.log(`     - ${field.id || field.name || 'sin id'}`);
        });
    }

    if (borderDangerFields.length > 0) {
        console.log('   ⚠️ Campos con .border-danger restantes:');
        borderDangerFields.forEach(field => {
            console.log(`     - ${field.id || field.name || 'sin id'}`);
        });
    }

    if (errorSpans.length > 0) {
        console.log('   ⚠️ Error spans con contenido restante:');
        errorSpans.forEach(span => {
            console.log(`     - ${span.id}: "${span.textContent}"`);
        });
    }

    // Verificar formulario
    const form = document.querySelector('#ModalCliente form');
    console.log('2. Estado del formulario:');
    console.log(`   - Tiene .was-validated: ${form?.classList.contains('was-validated') ? 'SÍ' : 'NO'}`);

    // Resumen final
    const totalValidationIssues = validFields.length + invalidFields.length + wasValidatedFields.length + borderSuccessFields.length + borderDangerFields.length + errorSpans.length;
    console.log(`🎯 Total de problemas de validación encontrados: ${totalValidationIssues}`);

    if (totalValidationIssues === 0) {
        console.log('✅ Modal completamente limpio de validaciones');
    } else {
        console.log('⚠️ Aún hay validaciones pendientes de limpiar');
    }
};

// Interceptar resetModal para debug automático
const originalResetModal = window.resetModal;
if (originalResetModal) {
    window.resetModal = function() {
        originalResetModal();
        setTimeout(() => {
            console.log('🔍 Debug automático después de resetModal:');
            window.debugValidationCleanup();
        }, 200);
    };
}

console.log('✅ Edit Mode Debug listo');
