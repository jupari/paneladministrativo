// SCRIPT PARA LIMPIEZA AGRESIVA DE VALIDACIONES
// Este script resuelve el problema de validaciones persistentes entre modos creación/edición

window.superAggressiveValidationCleaner = function() {
    console.log('🧹 === INICIANDO LIMPIEZA SUPER AGRESIVA ===');

    // FASE 1: Limpiar todo el formulario
    const form = document.querySelector('#ModalCliente form');
    if (form) {
        form.classList.remove('was-validated', 'needs-validation');
        form.removeAttribute('novalidate');
        console.log('✅ Formulario base limpiado');
    }

    // FASE 2: Limpiar TODOS los campos
    const allFields = document.querySelectorAll('#ModalCliente input, #ModalCliente select, #ModalCliente textarea');
    allFields.forEach(field => {
        // Limpiar clases de Bootstrap
        field.classList.remove(
            'is-valid', 'is-invalid', 'was-validated',
            'border-success', 'border-danger',
            'form-control-success', 'form-control-danger'
        );

        // Limpiar estilos inline
        field.style.borderColor = '';
        field.style.border = '';
        field.style.boxShadow = '';
        field.style.backgroundColor = '';

        // Limpiar atributos de validación
        field.removeAttribute('aria-invalid');
        field.removeAttribute('aria-describedby');

        // Restaurar clases base
        if (!field.classList.contains('form-control') && !field.classList.contains('form-select')) {
            field.className = 'form-control';
        }

        console.log(`✅ Campo limpiado: ${field.id || field.name || 'sin id'}`);
    });

    // FASE 3: Limpiar mensajes de error
    const errorElements = document.querySelectorAll('#ModalCliente [id^="error_"], #ModalCliente .invalid-feedback, #ModalCliente .valid-feedback');
    errorElements.forEach(element => {
        element.textContent = '';
        element.innerHTML = '';
        element.style.display = 'none';
        console.log(`✅ Error element limpiado: ${element.id || element.className}`);
    });

    // FASE 4: Limpiar contenedores de validación
    const validationContainers = document.querySelectorAll('#ModalCliente .form-group, #ModalCliente .input-group');
    validationContainers.forEach(container => {
        container.classList.remove('has-success', 'has-error', 'has-feedback');
    });

    // FASE 5: Forzar recalculo de estilos
    const modal = document.querySelector('#ModalCliente');
    if (modal) {
        modal.style.display = 'none';
        modal.offsetHeight; // Trigger reflow
        modal.style.display = '';
    }

    // FASE 6: Limpiar cualquier evento de validación pendiente
    setTimeout(() => {
        allFields.forEach(field => {
            // Remover listeners de validación
            field.onblur = null;
            field.oninvalid = null;
            field.onchange = null;

            // Triggear evento para forzar limpieza
            field.dispatchEvent(new Event('input', { bubbles: true }));
        });

        console.log('✅ Eventos de validación limpiados');
    }, 50);

    console.log('🎯 === LIMPIEZA SUPER AGRESIVA COMPLETADA ===');
    return true;
};

// Función para interceptar la apertura del modal
window.interceptModalForValidationCleaning = function() {
    const originalShow = $('#ModalCliente').modal;

    $('#ModalCliente').on('show.bs.modal', function() {
        console.log('🔄 Modal abriéndose - ejecutando limpieza preventiva');
        setTimeout(() => {
            window.superAggressiveValidationCleaner();
        }, 100);
    });

    $('#ModalCliente').on('shown.bs.modal', function() {
        console.log('✅ Modal abierto - ejecutando limpieza final');
        setTimeout(() => {
            window.superAggressiveValidationCleaner();
        }, 200);
    });

    console.log('✅ Interceptor de modal configurado');
};

// Función para limpiar específicamente cuando se cambia de edición a creación
window.cleanTransitionFromEditToCreate = function() {
    console.log('🔄 Transición de EDICIÓN a CREACIÓN - limpieza específica');

    // Limpiar valores de campos
    const fieldsToClean = [
        'identificacion', 'nombres', 'apellidos', 'nombre_establecimiento',
        'telefono', 'celular', 'correo', 'correo_fe', 'direccion'
    ];

    fieldsToClean.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field && !['tipopersona_id', 'tipoidentificacion_id', 'vendedor_id'].includes(fieldId)) {
            field.value = '';
            console.log(`✅ Valor limpiado: ${fieldId}`);
        }
    });

    // Ejecutar limpieza super agresiva
    setTimeout(() => {
        window.superAggressiveValidationCleaner();
    }, 50);

    // Segunda limpieza tardía
    setTimeout(() => {
        window.superAggressiveValidationCleaner();
        console.log('🎯 Limpieza de transición completada');
    }, 300);
};

// Interceptar resetModal si existe
if (window.resetModal) {
    const originalResetModal = window.resetModal;
    window.resetModal = function() {
        console.log('🔄 resetModal interceptado - ejecutando limpieza agresiva');

        // Ejecutar resetModal original
        originalResetModal();

        // Ejecutar limpieza agresiva inmediata
        setTimeout(() => {
            window.superAggressiveValidationCleaner();
        }, 50);

        // Limpieza tardía para casos persistentes
        setTimeout(() => {
            window.cleanTransitionFromEditToCreate();
        }, 200);

        // Ultra limpieza tardía
        setTimeout(() => {
            window.superAggressiveValidationCleaner();
            console.log('💎 Ultra limpieza tardía completada');
        }, 500);
    };
}

// Auto-ejecutar al cargar
$(document).ready(function() {
    window.interceptModalForValidationCleaning();
    console.log('🚀 Validation Cleaner Aggressive cargado y configurado');
});
